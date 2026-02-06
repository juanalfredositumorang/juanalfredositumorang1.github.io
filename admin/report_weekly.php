<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Custom range atau default 7 hari terakhir
if(isset($_GET['start']) && isset($_GET['end'])){
   $start = $_GET['start'];
   $end = $_GET['end'];
} else {
   $start = date('Y-m-d', strtotime('-6 days'));
   $end = date('Y-m-d');
}

$select_orders = $conn->prepare("
   SELECT * FROM `orders`
   WHERE DATE(placed_on) BETWEEN ? AND ?
   ORDER BY placed_on DESC
");
$select_orders->execute([$start, $end]);
$orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

// Inisialisasi variabel
$total_income = 0;
$completed_income = 0;
$pending_income = 0;
$completed_count = 0;
$pending_count = 0;
$cancelled_count = 0;
$daily_stats = [];
$payment_methods = [];

foreach($orders as $o){
   $total_income += $o['total_price'];
   
   // Per hari
   $day = date('Y-m-d', strtotime($o['placed_on']));
   $day_name = date('l', strtotime($o['placed_on']));
   if(!isset($daily_stats[$day])){
      $daily_stats[$day] = ['count' => 0, 'total' => 0, 'completed' => 0, 'name' => $day_name];
   }
   $daily_stats[$day]['count']++;
   $daily_stats[$day]['total'] += $o['total_price'];
   
   // Status
   if($o['payment_status'] == 'completed'){
      $completed_income += $o['total_price'];
      $completed_count++;
      $daily_stats[$day]['completed'] += $o['total_price'];
   } elseif($o['payment_status'] == 'pending'){
      $pending_income += $o['total_price'];
      $pending_count++;
   } elseif($o['payment_status'] == 'cancelled'){
      $cancelled_count++;
   }
   
   // Metode pembayaran
   $method = $o['method'];
   if(!isset($payment_methods[$method])){
      $payment_methods[$method] = ['count' => 0, 'total' => 0];
   }
   $payment_methods[$method]['count']++;
   $payment_methods[$method]['total'] += $o['total_price'];
}

ksort($daily_stats);

// Hitung growth vs minggu sebelumnya
$prev_start = date('Y-m-d', strtotime($start . ' -7 days'));
$prev_end = date('Y-m-d', strtotime($end . ' -7 days'));

$prev_orders = $conn->prepare("
   SELECT COUNT(*) as count, SUM(total_price) as total FROM `orders`
   WHERE DATE(placed_on) BETWEEN ? AND ? AND payment_status = 'completed'
");
$prev_orders->execute([$prev_start, $prev_end]);
$prev_data = $prev_orders->fetch(PDO::FETCH_ASSOC);

$prev_income = $prev_data['total'] ?? 0;
$prev_count = $prev_data['count'] ?? 0;

$growth_income = $prev_income > 0 ? (($completed_income - $prev_income) / $prev_income) * 100 : 0;
$growth_count = $prev_count > 0 ? (($completed_count - $prev_count) / $prev_count) * 100 : 0;

// Rata-rata
$avg_transaction = $completed_count > 0 ? $completed_income / $completed_count : 0;
$avg_daily = count($daily_stats) > 0 ? $completed_income / count($daily_stats) : 0;

// Nama hari Indonesia
$day_names_id = [
   'Monday' => 'Senin',
   'Tuesday' => 'Selasa', 
   'Wednesday' => 'Rabu',
   'Thursday' => 'Kamis',
   'Friday' => 'Jumat',
   'Saturday' => 'Sabtu',
   'Sunday' => 'Minggu'
];
?>

<?php include '../components/admin_header.php'; ?>

<style>
.reports {
   padding: 25px;
   max-width: 1600px;
   margin: 0 auto;
   background: #f5f7fa;
}

.page-header {
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   color: white;
   padding: 30px;
   border-radius: 15px;
   margin-bottom: 30px;
   box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.page-header h1 {
   font-size: 32px;
   margin-bottom: 10px;
   font-weight: 700;
}

.filter-section {
   background: white;
   padding: 25px;
   border-radius: 12px;
   margin-bottom: 30px;
   box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.filter-form {
   display: flex;
   align-items: flex-end;
   gap: 15px;
   flex-wrap: wrap;
}

.filter-group {
   display: flex;
   flex-direction: column;
   gap: 8px;
}

.filter-group label {
   font-weight: 600;
   color: #333;
   font-size: 14px;
}

.filter-group input {
   padding: 10px 15px;
   border: 2px solid #e0e0e0;
   border-radius: 8px;
   font-size: 15px;
   min-width: 180px;
}

.btn-filter {
   padding: 10px 30px;
   background: #667eea;
   color: white;
   border: none;
   border-radius: 8px;
   cursor: pointer;
   font-size: 15px;
   font-weight: 600;
   height: 44px;
}

.quick-filters {
   display: flex;
   gap: 10px;
   margin-top: 15px;
}

.btn-quick {
   padding: 8px 16px;
   background: #f0f0f0;
   border: none;
   border-radius: 6px;
   cursor: pointer;
   font-size: 13px;
}

.stats-grid {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
   gap: 20px;
   margin-bottom: 30px;
}

.stat-card {
   background: white;
   padding: 25px;
   border-radius: 12px;
   box-shadow: 0 2px 15px rgba(0,0,0,0.08);
   position: relative;
}

.stat-card::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   width: 4px;
   height: 100%;
}

.stat-card.green::before { background: linear-gradient(180deg, #11998e, #38ef7d); }
.stat-card.orange::before { background: linear-gradient(180deg, #f093fb, #f5576c); }
.stat-card.blue::before { background: linear-gradient(180deg, #4facfe, #00f2fe); }

.stat-title {
   font-size: 14px;
   color: #666;
   margin-bottom: 8px;
}

.stat-value {
   font-size: 28px;
   font-weight: 700;
   color: #333;
   margin-bottom: 8px;
}

.stat-growth {
   font-size: 13px;
}

.stat-growth.positive { color: #27ae60; }
.stat-growth.negative { color: #e74c3c; }

.charts-section {
   display: grid;
   grid-template-columns: 2fr 1fr;
   gap: 20px;
   margin-bottom: 30px;
}

.chart-card {
   background: white;
   padding: 25px;
   border-radius: 12px;
   box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.chart-title {
   font-size: 18px;
   font-weight: 700;
   margin-bottom: 20px;
}

.day-item {
   display: flex;
   align-items: center;
   gap: 15px;
   padding: 12px;
   background: #f8f9fa;
   border-radius: 8px;
   margin-bottom: 10px;
}

.day-label {
   min-width: 80px;
   font-weight: 600;
}

.day-bar-container {
   flex: 1;
   height: 30px;
   background: #e0e0e0;
   border-radius: 15px;
   overflow: hidden;
}

.day-bar {
   height: 100%;
   background: linear-gradient(90deg, #667eea, #764ba2);
   display: flex;
   align-items: center;
   justify-content: flex-end;
   padding-right: 10px;
   color: white;
   font-size: 12px;
   font-weight: 600;
}

.method-item {
   display: flex;
   justify-content: space-between;
   padding: 15px;
   background: #f8f9fa;
   border-radius: 8px;
   margin-bottom: 10px;
}

.section-header {
   display: flex;
   justify-content: space-between;
   margin-bottom: 20px;
}

.export-buttons {
   display: flex;
   gap: 10px;
}

.btn-export {
   padding: 10px 20px;
   border: none;
   border-radius: 8px;
   cursor: pointer;
   font-weight: 600;
   text-decoration: none;
}

.btn-export.excel {
   background: #27ae60;
   color: white;
}

.btn-export.print {
   background: #3498db;
   color: white;
}

.table-wrapper {
   background: white;
   border-radius: 12px;
   overflow: hidden;
   box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.table {
   width: 100%;
   border-collapse: collapse;
}

.table thead {
   background: linear-gradient(135deg, #667eea, #764ba2);
   color: white;
}

.table th {
   padding: 18px 15px;
   text-align: left;
   font-weight: 600;
}

.table td {
   padding: 15px;
   border-bottom: 1px solid #f0f0f0;
}

.status-completed {
   background: #d4edda;
   color: #155724;
   padding: 6px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
}

.status-pending {
   background: #fff3cd;
   color: #856404;
   padding: 6px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
}

.status-cancelled {
   background: #f8d7da;
   color: #721c24;
   padding: 6px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
}

@media (max-width: 1200px) {
   .charts-section {
      grid-template-columns: 1fr;
   }
}
</style>

<section class="reports">
   
   <div class="page-header">
      <h1>📅 Laporan Keuangan Mingguan</h1>
      <p>Periode: <?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?></p>
   </div>

   <div class="filter-section">
      <form method="GET" class="filter-form">
         <div class="filter-group">
            <label>Tanggal Mulai</label>
            <input type="date" name="start" value="<?= $start ?>">
         </div>
         <div class="filter-group">
            <label>Tanggal Akhir</label>
            <input type="date" name="end" value="<?= $end ?>">
         </div>
         <button type="submit" class="btn-filter">Tampilkan</button>
      </form>
      <div class="quick-filters">
         <button class="btn-quick" onclick="location.href='?start=<?= date('Y-m-d', strtotime('-6 days')) ?>&end=<?= date('Y-m-d') ?>'">Minggu Ini</button>
         <button class="btn-quick" onclick="location.href='?start=<?= date('Y-m-d', strtotime('-13 days')) ?>&end=<?= date('Y-m-d', strtotime('-7 days')) ?>'">Minggu Lalu</button>
      </div>
   </div>

   <div class="stats-grid">
      <div class="stat-card">
         <div class="stat-title">🛒 Total Transaksi</div>
         <div class="stat-value"><?= count($orders) ?></div>
         <div class="stat-growth <?= $growth_count >= 0 ? 'positive' : 'negative' ?>">
            <?= $growth_count >= 0 ? '↗' : '↘' ?> <?= abs(number_format($growth_count, 1)) ?>% vs minggu lalu
         </div>
      </div>

      <div class="stat-card green">
         <div class="stat-title">💰 Pendapatan Bersih</div>
         <div class="stat-value">Rp<?= number_format($completed_income, 0, ',', '.') ?></div>
         <div class="stat-growth <?= $growth_income >= 0 ? 'positive' : 'negative' ?>">
            <?= $growth_income >= 0 ? '↗' : '↘' ?> <?= abs(number_format($growth_income, 1)) ?>% vs minggu lalu
         </div>
      </div>

      <div class="stat-card orange">
         <div class="stat-title">⏳ Pendapatan Pending</div>
         <div class="stat-value">Rp<?= number_format($pending_income, 0, ',', '.') ?></div>
         <div class="stat-growth"><?= $pending_count ?> transaksi menunggu</div>
      </div>

      <div class="stat-card blue">
         <div class="stat-title">📈 Rata-rata Transaksi</div>
         <div class="stat-value">Rp<?= number_format($avg_transaction, 0, ',', '.') ?></div>
      </div>

      <div class="stat-card blue">
         <div class="stat-title">📅 Rata-rata Harian</div>
         <div class="stat-value">Rp<?= number_format($avg_daily, 0, ',', '.') ?></div>
      </div>

      <div class="stat-card">
         <div class="stat-title">❌ Dibatalkan</div>
         <div class="stat-value"><?= $cancelled_count ?></div>
      </div>
   </div>

   <div class="charts-section">
      <div class="chart-card">
         <div class="chart-title">📊 Pendapatan per Hari</div>
         <?php 
         $max_daily = 0;
         foreach($daily_stats as $stats){
            if($stats['completed'] > $max_daily) $max_daily = $stats['completed'];
         }
         
         foreach($daily_stats as $day => $stats){ 
            $percentage = $max_daily > 0 ? ($stats['completed'] / $max_daily) * 100 : 0;
            $day_id = $day_names_id[$stats['name']] ?? $stats['name'];
         ?>
         <div class="day-item">
            <div class="day-label"><?= $day_id ?></div>
            <div class="day-bar-container">
               <div class="day-bar" style="width: <?= $percentage ?>%">
                  <?php if($percentage > 30){ ?>
                     Rp<?= number_format($stats['completed'], 0, ',', '.') ?>
                  <?php } ?>
               </div>
            </div>
            <div><?= $stats['count'] ?> transaksi</div>
         </div>
         <?php } ?>
      </div>

      <div class="chart-card">
         <div class="chart-title">💳 Metode Pembayaran</div>
         <?php foreach($payment_methods as $method => $data){ ?>
         <div class="method-item">
            <div>
               <strong><?= htmlspecialchars($method) ?></strong><br>
               <small><?= $data['count'] ?> transaksi</small>
            </div>
            <strong style="color: #667eea;">Rp<?= number_format($data['total'], 0, ',', '.') ?></strong>
         </div>
         <?php } ?>
      </div>
   </div>

   <div class="section-header">
      <h2>Detail Transaksi</h2>
      <div class="export-buttons">
         <a href="export.php?type=weekly&start=<?= $start ?>&end=<?= $end ?>" class="btn-export excel">📊 Excel</a>
         <button onclick="window.print()" class="btn-export print">🖨️ Print</button>
      </div>
   </div>

   <div class="table-wrapper">
      <table class="table">
         <thead>
            <tr>
               <th>ID</th>
               <th>Tanggal</th>
               <th>Nama</th>
               <th>Email</th>
               <th>Metode</th>
               <th>Total Harga</th>
               <th>Status</th>
            </tr>
         </thead>
         <tbody>
         <?php if(count($orders) > 0){ 
            foreach($orders as $o){ ?>
            <tr>
               <td><strong>#<?= $o['id'] ?></strong></td>
               <td><?= date('d/m/Y H:i', strtotime($o['placed_on'])) ?></td>
               <td><?= htmlspecialchars($o['name']) ?></td>
               <td><?= htmlspecialchars($o['email']) ?></td>
               <td><?= htmlspecialchars($o['method']) ?></td>
               <td><strong>Rp<?= number_format($o['total_price'], 0, ',', '.') ?></strong></td>
               <td><span class="status-<?= $o['payment_status'] ?>"><?= ucfirst($o['payment_status']) ?></span></td>
            </tr>
         <?php } 
         } else { ?>
            <tr><td colspan="7" style="text-align:center;padding:40px">Tidak ada transaksi</td></tr>
         <?php } ?>
         </tbody>
      </table>
   </div>

</section>
