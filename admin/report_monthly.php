<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Bulan dan tahun saat ini
$current_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Tanggal awal dan akhir bulan
$start = $current_year . '-' . $current_month . '-01';
$end = date('Y-m-t', strtotime($start));

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
   if(!isset($daily_stats[$day])){
      $daily_stats[$day] = ['count' => 0, 'total' => 0, 'completed' => 0];
   }
   $daily_stats[$day]['count']++;
   $daily_stats[$day]['total'] += $o['total_price'];
   
   // Per status
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
   
   // Per metode pembayaran
   $method = $o['method'];
   if(!isset($payment_methods[$method])){
      $payment_methods[$method] = ['count' => 0, 'total' => 0];
   }
   $payment_methods[$method]['count']++;
   $payment_methods[$method]['total'] += $o['total_price'];
}

ksort($daily_stats);

// Nama bulan
$month_names = [
   '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
   '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
   '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$month_name = $month_names[$current_month];

// Rata-rata
$avg_transaction = $completed_count > 0 ? $completed_income / $completed_count : 0;
$avg_daily = count($daily_stats) > 0 ? $completed_income / count($daily_stats) : 0;
?>

<?php include '../components/admin_header.php'; ?>

<style>
* {
   margin: 0;
   padding: 0;
   box-sizing: border-box;
}

.reports {
   padding: 25px;
   max-width: 1600px;
   margin: 0 auto;
   background: #f5f7fa;
   min-height: 100vh;
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

.page-header p {
   opacity: 0.9;
   font-size: 16px;
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
   align-items: center;
   gap: 20px;
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

.filter-group select {
   padding: 10px 15px;
   border: 2px solid #e0e0e0;
   border-radius: 8px;
   font-size: 15px;
   min-width: 150px;
   transition: all 0.3s;
}

.filter-group select:focus {
   border-color: #667eea;
   outline: none;
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
   margin-top: 22px;
   transition: all 0.3s;
}

.btn-filter:hover {
   background: #5568d3;
   transform: translateY(-2px);
   box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
   overflow: hidden;
   transition: all 0.3s;
}

.stat-card:hover {
   transform: translateY(-5px);
   box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.stat-card::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   width: 4px;
   height: 100%;
   background: linear-gradient(180deg, #667eea, #764ba2);
}

.stat-card.green::before {
   background: linear-gradient(180deg, #11998e, #38ef7d);
}

.stat-card.orange::before {
   background: linear-gradient(180deg, #f093fb, #f5576c);
}

.stat-card.blue::before {
   background: linear-gradient(180deg, #4facfe, #00f2fe);
}

.stat-card.red::before {
   background: linear-gradient(180deg, #fa709a, #fee140);
}

.stat-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 15px;
}

.stat-icon {
   width: 50px;
   height: 50px;
   border-radius: 12px;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 24px;
   opacity: 0.9;
}

.stat-card.green .stat-icon {
   background: linear-gradient(135deg, #11998e, #38ef7d);
   color: white;
}

.stat-card.orange .stat-icon {
   background: linear-gradient(135deg, #f093fb, #f5576c);
   color: white;
}

.stat-card.blue .stat-icon {
   background: linear-gradient(135deg, #4facfe, #00f2fe);
   color: white;
}

.stat-card.red .stat-icon {
   background: linear-gradient(135deg, #fa709a, #fee140);
   color: white;
}

.stat-title {
   font-size: 14px;
   color: #666;
   margin-bottom: 8px;
   font-weight: 500;
}

.stat-value {
   font-size: 28px;
   font-weight: 700;
   color: #333;
}

.stat-subtitle {
   font-size: 13px;
   color: #999;
   margin-top: 8px;
}

.charts-grid {
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
   color: #333;
   margin-bottom: 20px;
   display: flex;
   align-items: center;
   gap: 10px;
}

.method-item {
   display: flex;
   justify-content: space-between;
   align-items: center;
   padding: 15px;
   border-bottom: 1px solid #f0f0f0;
   transition: all 0.3s;
}

.method-item:hover {
   background: #f8f9fa;
}

.method-info {
   display: flex;
   flex-direction: column;
   gap: 5px;
}

.method-name {
   font-weight: 600;
   color: #333;
}

.method-count {
   font-size: 13px;
   color: #666;
}

.method-amount {
   font-weight: 700;
   color: #667eea;
   font-size: 16px;
}

.section-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 20px;
}

.section-title {
   font-size: 22px;
   font-weight: 700;
   color: #333;
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
   font-size: 14px;
   font-weight: 600;
   display: flex;
   align-items: center;
   gap: 8px;
   transition: all 0.3s;
   text-decoration: none;
}

.btn-export.excel {
   background: #27ae60;
   color: white;
}

.btn-export.excel:hover {
   background: #229954;
   transform: translateY(-2px);
}

.btn-export.print {
   background: #3498db;
   color: white;
}

.btn-export.print:hover {
   background: #2980b9;
   transform: translateY(-2px);
}

.table-wrapper {
   background: white;
   border-radius: 12px;
   overflow: hidden;
   box-shadow: 0 2px 15px rgba(0,0,0,0.08);
   margin-bottom: 30px;
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
   font-size: 14px;
}

.table td {
   padding: 15px;
   border-bottom: 1px solid #f0f0f0;
   font-size: 14px;
   color: #555;
}

.table tbody tr {
   transition: all 0.3s;
}

.table tbody tr:hover {
   background: #f8f9fa;
}

.status-badge {
   padding: 6px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
   display: inline-block;
}

.status-completed {
   background: #d4edda;
   color: #155724;
}

.status-pending {
   background: #fff3cd;
   color: #856404;
}

.status-cancelled {
   background: #f8d7da;
   color: #721c24;
}

.btn-detail {
   padding: 6px 15px;
   background: #667eea;
   color: white;
   text-decoration: none;
   border-radius: 6px;
   font-size: 13px;
   font-weight: 600;
   transition: all 0.3s;
}

.btn-detail:hover {
   background: #5568d3;
}

.empty-state {
   text-align: center;
   padding: 60px 20px;
   color: #999;
}

.empty-icon {
   font-size: 64px;
   margin-bottom: 20px;
   opacity: 0.3;
}

.daily-chart {
   max-height: 300px;
   overflow-y: auto;
}

.daily-item {
   display: flex;
   justify-content: space-between;
   align-items: center;
   padding: 12px 0;
   border-bottom: 1px solid #f0f0f0;
}

.daily-date {
   font-weight: 600;
   color: #333;
   font-size: 14px;
}

.daily-stats {
   display: flex;
   gap: 20px;
   font-size: 13px;
}

.daily-stats span {
   color: #666;
}

.daily-stats strong {
   color: #667eea;
}

@media (max-width: 1200px) {
   .charts-grid {
      grid-template-columns: 1fr;
   }
}

@media (max-width: 768px) {
   .stats-grid {
      grid-template-columns: 1fr;
   }
   
   .filter-form {
      flex-direction: column;
      align-items: stretch;
   }
   
   .btn-filter {
      margin-top: 10px;
   }
   
   .export-buttons {
      flex-direction: column;
   }
   
   .table-wrapper {
      overflow-x: auto;
   }
}

@media print {
   .filter-section, .export-buttons {
      display: none;
   }
   
   .reports {
      padding: 0;
      background: white;
   }
}
</style>

<section class="reports">
   
   <div class="page-header">
      <h1>📊 Laporan Keuangan Bulanan</h1>
      <p><?= $month_name ?> <?= $current_year ?> • Periode: <?= date('d/m/Y', strtotime($start)) ?> - <?= date('d/m/Y', strtotime($end)) ?></p>
   </div>

   <!-- Filter -->
   <div class="filter-section">
      <form method="GET" class="filter-form">
         <div class="filter-group">
            <label>Pilih Bulan</label>
            <select name="month">
               <?php for($m = 1; $m <= 12; $m++){ 
                  $m_str = str_pad($m, 2, '0', STR_PAD_LEFT);
               ?>
                  <option value="<?= $m_str ?>" <?= ($current_month == $m_str) ? 'selected' : '' ?>>
                     <?= $month_names[$m_str] ?>
                  </option>
               <?php } ?>
            </select>
         </div>

         <div class="filter-group">
            <label>Pilih Tahun</label>
            <select name="year">
               <?php for($y = date('Y'); $y >= date('Y') - 5; $y--){ ?>
                  <option value="<?= $y ?>" <?= ($current_year == $y) ? 'selected' : '' ?>><?= $y ?></option>
               <?php } ?>
            </select>
         </div>

         <button type="submit" class="btn-filter">Tampilkan Laporan</button>
      </form>
   </div>

   <!-- Stats Cards -->
   <div class="stats-grid">
      <div class="stat-card green">
         <div class="stat-header">
            <div>
               <div class="stat-title">💰 Pendapatan Bersih</div>
               <div class="stat-value">Rp<?= number_format($completed_income, 0, ',', '.') ?></div>
               <div class="stat-subtitle"><?= $completed_count ?> transaksi selesai</div>
            </div>
            <div class="stat-icon">✓</div>
         </div>
      </div>

      <div class="stat-card orange">
         <div class="stat-header">
            <div>
               <div class="stat-title">⏳ Pendapatan Pending</div>
               <div class="stat-value">Rp<?= number_format($pending_income, 0, ',', '.') ?></div>
               <div class="stat-subtitle"><?= $pending_count ?> transaksi menunggu</div>
            </div>
            <div class="stat-icon">⌛</div>
         </div>
      </div>

      <div class="stat-card blue">
         <div class="stat-header">
            <div>
               <div class="stat-title">📈 Rata-rata Transaksi</div>
               <div class="stat-value">Rp<?= number_format($avg_transaction, 0, ',', '.') ?></div>
               <div class="stat-subtitle">Per transaksi selesai</div>
            </div>
            <div class="stat-icon">📊</div>
         </div>
      </div>

      <div class="stat-card red">
         <div class="stat-header">
            <div>
               <div class="stat-title">📅 Rata-rata Harian</div>
               <div class="stat-value">Rp<?= number_format($avg_daily, 0, ',', '.') ?></div>
               <div class="stat-subtitle"><?= count($daily_stats) ?> hari aktif</div>
            </div>
            <div class="stat-icon">📆</div>
         </div>
      </div>

      <div class="stat-card">
         <div class="stat-header">
            <div>
               <div class="stat-title">🛒 Total Transaksi</div>
               <div class="stat-value"><?= count($orders) ?></div>
               <div class="stat-subtitle">Semua status</div>
            </div>
            <div class="stat-icon">📋</div>
         </div>
      </div>

      <div class="stat-card red">
         <div class="stat-header">
            <div>
               <div class="stat-title">❌ Transaksi Dibatalkan</div>
               <div class="stat-value"><?= $cancelled_count ?></div>
               <div class="stat-subtitle">Tidak termasuk pendapatan</div>
            </div>
            <div class="stat-icon">🚫</div>
         </div>
      </div>
   </div>

   <!-- Charts -->
   <div class="charts-grid">
      <div class="chart-card">
         <div class="chart-title">📊 Statistik Harian</div>
         <div class="daily-chart">
            <?php if(count($daily_stats) > 0){ ?>
               <?php foreach($daily_stats as $day => $stats){ ?>
               <div class="daily-item">
                  <div class="daily-date"><?= date('d M Y', strtotime($day)) ?></div>
                  <div class="daily-stats">
                     <span><?= $stats['count'] ?> transaksi</span>
                     <strong>Rp<?= number_format($stats['completed'], 0, ',', '.') ?></strong>
                  </div>
               </div>
               <?php } ?>
            <?php } else { ?>
               <div class="empty-state">
                  <div class="empty-icon">📭</div>
                  <p>Tidak ada data</p>
               </div>
            <?php } ?>
         </div>
      </div>

      <div class="chart-card">
         <div class="chart-title">💳 Metode Pembayaran</div>
         <?php if(count($payment_methods) > 0){ ?>
            <?php foreach($payment_methods as $method => $data){ ?>
            <div class="method-item">
               <div class="method-info">
                  <div class="method-name"><?= htmlspecialchars($method) ?></div>
                  <div class="method-count"><?= $data['count'] ?> transaksi</div>
               </div>
               <div class="method-amount">Rp<?= number_format($data['total'], 0, ',', '.') ?></div>
            </div>
            <?php } ?>
         <?php } else { ?>
            <div class="empty-state">
               <div class="empty-icon">💳</div>
               <p>Tidak ada data</p>
            </div>
         <?php } ?>
      </div>
   </div>

   <!-- Detail Transaksi -->
   <div class="section-header">
      <h2 class="section-title">Detail Semua Transaksi</h2>
      <div class="export-buttons">
         <a href="export_report.php?type=monthly&month=<?= $current_month ?>&year=<?= $current_year ?>" class="btn-export excel">
            📊 Export Excel
         </a>
         <button onclick="window.print()" class="btn-export print">
            🖨️ Print Laporan
         </button>
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
               <th>Total Produk</th>
               <th>Total Harga</th>
               <th>Status</th>
               <th>Aksi</th>
            </tr>
         </thead>
         <tbody>
         <?php if(count($orders) > 0){ ?>
            <?php foreach($orders as $o){ ?>
            <tr>
               <td><strong>#<?= htmlspecialchars($o['id']) ?></strong></td>
               <td><?= date('d/m/Y H:i', strtotime($o['placed_on'])) ?></td>
               <td><?= htmlspecialchars($o['name']) ?></td>
               <td><?= htmlspecialchars($o['email']) ?></td>
               <td><?= htmlspecialchars($o['method']) ?></td>
               <td><?= htmlspecialchars($o['total_products']) ?></td>
               <td><strong>Rp<?= number_format($o['total_price'], 0, ',', '.') ?></strong></td>
               <td>
                  <span class="status-badge status-<?= strtolower($o['payment_status']) ?>">
                     <?= ucfirst(htmlspecialchars($o['payment_status'])) ?>
                  </span>
               </td>
               <td>
                  <a href="order_detail.php?id=<?= $o['id'] ?>" class="btn-detail">Detail</a>
               </td>
            </tr>
            <?php } ?>
         <?php } else { ?>
            <tr>
               <td colspan="9">
                  <div class="empty-state">
                     <div class="empty-icon">📋</div>
                     <p>Tidak ada transaksi pada bulan ini</p>
                  </div>
               </td>
            </tr>
         <?php } ?>
         </tbody>
      </table>
   </div>

</section>

