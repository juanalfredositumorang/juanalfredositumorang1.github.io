<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
}

// tanggal hari ini atau dari input
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$select_orders = $conn->prepare("
   SELECT * FROM `orders`
   WHERE DATE(placed_on) = ?
   ORDER BY placed_on DESC
");
$select_orders->execute([$selected_date]);
$orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

// Inisialisasi variabel
$total_income = 0;
$completed_income = 0;
$pending_income = 0;
$completed_count = 0;
$pending_count = 0;
$cancelled_count = 0;

// Hitung statistik
foreach($orders as $o){
   $total_income += $o['total_price'];
   if($o['payment_status'] == 'completed'){
      $completed_income += $o['total_price'];
      $completed_count++;
   } elseif($o['payment_status'] == 'pending'){
      $pending_income += $o['total_price'];
      $pending_count++;
   } elseif($o['payment_status'] == 'cancelled'){
      $cancelled_count++;
   }
}

// Rata-rata per transaksi
$avg_transaction = $completed_count > 0 ? $completed_income / $completed_count : 0;
?>

<?php include '../components/admin_header.php'; ?>

<style>
.reports {
   padding: 20px;
   max-width: 1400px;
   margin: 0 auto;
}

.title {
   font-size: 28px;
   color: #333;
   margin-bottom: 20px;
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.date-filter {
   display: flex;
   gap: 10px;
   align-items: center;
}

.date-filter input[type="date"] {
   padding: 8px 12px;
   border: 1px solid #ddd;
   border-radius: 5px;
   font-size: 14px;
}

.date-filter button {
   padding: 8px 20px;
   background: #4CAF50;
   color: white;
   border: none;
   border-radius: 5px;
   cursor: pointer;
   font-size: 14px;
}

.date-filter button:hover {
   background: #45a049;
}

.stats-grid {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
   gap: 20px;
   margin-bottom: 30px;
}

.stat-card {
   background: white;
   padding: 20px;
   border-radius: 10px;
   box-shadow: 0 2px 10px rgba(0,0,0,0.1);
   border-left: 4px solid #4CAF50;
}

.stat-card.pending {
   border-left-color: #FF9800;
}

.stat-card.cancelled {
   border-left-color: #f44336;
}

.stat-card.average {
   border-left-color: #2196F3;
}

.stat-label {
   font-size: 14px;
   color: #666;
   margin-bottom: 8px;
}

.stat-value {
   font-size: 28px;
   font-weight: bold;
   color: #333;
}

.stat-value.currency {
   font-size: 24px;
   color: #4CAF50;
}

.summary-section {
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   color: white;
   padding: 30px;
   border-radius: 10px;
   margin-bottom: 30px;
   box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.summary-header {
   font-size: 20px;
   margin-bottom: 20px;
   font-weight: bold;
}

.summary-grid {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
   gap: 20px;
}

.summary-item {
   background: rgba(255,255,255,0.1);
   padding: 15px;
   border-radius: 8px;
}

.summary-item label {
   display: block;
   font-size: 13px;
   opacity: 0.9;
   margin-bottom: 5px;
}

.summary-item .value {
   font-size: 22px;
   font-weight: bold;
}

.table-container {
   background: white;
   border-radius: 10px;
   box-shadow: 0 2px 10px rgba(0,0,0,0.1);
   overflow: hidden;
}

.table-header {
   padding: 20px;
   background: #f8f9fa;
   border-bottom: 2px solid #dee2e6;
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.table-header h3 {
   margin: 0;
   color: #333;
}

.export-btn {
   padding: 8px 20px;
   background: #2196F3;
   color: white;
   border: none;
   border-radius: 5px;
   cursor: pointer;
   font-size: 14px;
}

.export-btn:hover {
   background: #1976D2;
}

.table {
   width: 100%;
   border-collapse: collapse;
}

.table thead {
   background: #f8f9fa;
}

.table th {
   padding: 15px;
   text-align: left;
   font-weight: 600;
   color: #333;
   border-bottom: 2px solid #dee2e6;
}

.table td {
   padding: 12px 15px;
   border-bottom: 1px solid #f1f1f1;
   color: #555;
}

.table tbody tr:hover {
   background: #f8f9fa;
}

.status-completed {
   background: #4CAF50;
   color: white;
   padding: 5px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
}

.status-pending {
   background: #FF9800;
   color: white;
   padding: 5px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
}

.status-cancelled {
   background: #f44336;
   color: white;
   padding: 5px 12px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
}

.empty-state {
   text-align: center;
   padding: 60px 20px;
   color: #999;
}

.empty-state svg {
   width: 80px;
   height: 80px;
   margin-bottom: 20px;
   opacity: 0.3;
}

@media print {
   .date-filter, .export-btn {
      display: none;
   }
   
   .reports {
      padding: 0;
   }
   
   .table-container {
      box-shadow: none;
   }
}

@media (max-width: 768px) {
   .stats-grid {
      grid-template-columns: 1fr;
   }
   
   .table-container {
      overflow-x: auto;
   }
   
   .table {
      min-width: 800px;
   }
}
</style>

<section class="reports">

   <h1 class="title">
      <span>📊 Laporan Keuangan Harian</span>
      <form method="GET" class="date-filter">
         <input type="date" name="date" value="<?= $selected_date ?>" max="<?= date('Y-m-d') ?>">
         <button type="submit">Tampilkan</button>
      </form>
   </h1>

   <div class="summary-section">
      <div class="summary-header">
         Ringkasan Tanggal: <?= date('d F Y', strtotime($selected_date)) ?>
      </div>
      <div class="summary-grid">
         <div class="summary-item">
            <label>Total Transaksi</label>
            <div class="value"><?= count($orders) ?></div>
         </div>
         <div class="summary-item">
            <label>Transaksi Selesai</label>
            <div class="value"><?= $completed_count ?></div>
         </div>
         <div class="summary-item">
            <label>Transaksi Pending</label>
            <div class="value"><?= $pending_count ?></div>
         </div>
         <div class="summary-item">
            <label>Pendapatan Selesai</label>
            <div class="value">Rp<?= number_format($completed_income, 0, ',', '.') ?></div>
         </div>
      </div>
   </div>

   <div class="stats-grid">
      <div class="stat-card">
         <div class="stat-label">💰 Total Pendapatan (Completed)</div>
         <div class="stat-value currency">Rp<?= number_format($completed_income, 0, ',', '.') ?></div>
      </div>
      
      <div class="stat-card pending">
         <div class="stat-label">⏳ Pendapatan Pending</div>
         <div class="stat-value currency">Rp<?= number_format($pending_income, 0, ',', '.') ?></div>
      </div>
      
      <div class="stat-card average">
         <div class="stat-label">📈 Rata-rata per Transaksi</div>
         <div class="stat-value currency">Rp<?= number_format($avg_transaction, 0, ',', '.') ?></div>
      </div>
      
      <div class="stat-card cancelled">
         <div class="stat-label">❌ Transaksi Dibatalkan</div>
         <div class="stat-value"><?= $cancelled_count ?></div>
      </div>
   </div>

   <div class="table-container">
      <div class="table-header">
         <h3>Detail Transaksi</h3>
         <button class="export-btn" onclick="window.print()">🖨️ Cetak Laporan</button>
      </div>
      
      <table class="table">
         <thead>
            <tr>
               <th>ID Order</th>
               <th>Nama Pembeli</th>
               <th>Email</th>
               <th>Metode Bayar</th>
               <th>Total Produk</th>
               <th>Total Harga</th>
               <th>Status</th>
               <th>Waktu</th>
            </tr>
         </thead>
         <tbody>
         <?php if(count($orders) > 0){ ?>
            <?php foreach($orders as $o){ ?>
            <tr>
               <td><strong>#<?= htmlspecialchars($o['id']) ?></strong></td>
               <td><?= htmlspecialchars($o['name']) ?></td>
               <td><?= htmlspecialchars($o['email']) ?></td>
               <td><?= htmlspecialchars($o['method']) ?></td>
               <td><?= htmlspecialchars($o['total_products']) ?></td>
               <td><strong>Rp<?= number_format($o['total_price'], 0, ',', '.') ?></strong></td>
               <td>
                  <span class="status-<?= $o['payment_status'] ?>">
                     <?= ucfirst(htmlspecialchars($o['payment_status'])) ?>
                  </span>
               </td>
               <td><?= date('H:i', strtotime($o['placed_on'])) ?></td>
            </tr>
            <?php } ?>
         <?php } else { ?>
            <tr>
               <td colspan="8">
                  <div class="empty-state">
                     <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                     </svg>
                     <p>Tidak ada transaksi pada tanggal ini</p>
                  </div>
               </td>
            </tr>
         <?php } ?>
         </tbody>
      </table>
   </div>

</section>

