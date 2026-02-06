<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Ambil parameter tahun dan bulan
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : null;

// Jika ada parameter bulan, tampilkan laporan bulanan
if($month){
   // Laporan Bulanan
   $start_date = "$year-$month-01";
   $end_date = date('Y-m-t', strtotime($start_date));
   
   $select_orders = $conn->prepare("
      SELECT * FROM `orders` 
      WHERE DATE(placed_on) BETWEEN ? AND ?
      ORDER BY placed_on DESC
   ");
   $select_orders->execute([$start_date, $end_date]);
   $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);
   
   $total_income = 0;
   $completed_income = 0;
   $pending_income = 0;
   $completed_count = 0;
   $pending_count = 0;
   $cancelled_count = 0;
   $daily_stats = [];
   $payment_methods = [];
   
   // Ambil jumlah hari dalam bulan
   $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
   
   // Inisialisasi daily_stats untuk semua hari
   for($d = 1; $d <= $days_in_month; $d++){
      $day_key = sprintf("%s-%02d-%02d", $year, $month, $d);
      $daily_stats[$day_key] = [
         'count' => 0, 
         'total' => 0, 
         'completed' => 0,
         'day' => $d
      ];
   }
   
   foreach($orders as $o){
      $total_income += $o['total_price'];
      
      $day = date('Y-m-d', strtotime($o['placed_on']));
      $daily_stats[$day]['count']++;
      $daily_stats[$day]['total'] += $o['total_price'];
      
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
      
      $method = $o['method'];
      if(!isset($payment_methods[$method])){
         $payment_methods[$method] = ['count' => 0, 'total' => 0];
      }
      $payment_methods[$method]['count']++;
      $payment_methods[$method]['total'] += $o['total_price'];
   }
   
   // Hitung growth vs bulan sebelumnya
   $prev_month = date('Y-m', strtotime("$year-$month-01 -1 month"));
   $prev_start = "$prev_month-01";
   $prev_end = date('Y-m-t', strtotime($prev_start));
   
   $prev_orders = $conn->prepare("
      SELECT COUNT(*) as count, SUM(total_price) as total 
      FROM `orders`
      WHERE DATE(placed_on) BETWEEN ? AND ? AND payment_status = 'completed'
   ");
   $prev_orders->execute([$prev_start, $prev_end]);
   $prev_data = $prev_orders->fetch(PDO::FETCH_ASSOC);
   
   $prev_income = $prev_data['total'] ?? 0;
   $prev_count = $prev_data['count'] ?? 0;
   
   $growth_income = $prev_income > 0 ? (($completed_income - $prev_income) / $prev_income) * 100 : 0;
   $growth_count = $prev_count > 0 ? (($completed_count - $prev_count) / $prev_count) * 100 : 0;
   
   $avg_transaction = $completed_count > 0 ? $completed_income / $completed_count : 0;
   $avg_daily = $days_in_month > 0 ? $completed_income / $days_in_month : 0;
   
   $month_names = [
      '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
      '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
      '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
   ];
   
   $current_month_name = $month_names[$month];
   
} else {
   // Laporan Tahunan
   $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE YEAR(placed_on) = ? ORDER BY placed_on DESC");
   $select_orders->execute([$year]);
   $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);
   
   $total_income = 0;
   $completed_income = 0;
   $completed_count = 0;
   $monthly_stats = array_fill(1, 12, ['count' => 0, 'total' => 0, 'completed' => 0]);
   
   foreach($orders as $o){
      $total_income += $o['total_price'];
      $month_num = (int)date('n', strtotime($o['placed_on']));
      $monthly_stats[$month_num]['count']++;
      $monthly_stats[$month_num]['total'] += $o['total_price'];
      
      if($o['payment_status'] == 'completed'){
         $completed_income += $o['total_price'];
         $completed_count++;
         $monthly_stats[$month_num]['completed'] += $o['total_price'];
      }
   }
   
   $month_names = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
      5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
   ];
}
?>

<?php include '../components/admin_header.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan <?= $month ? $current_month_name : '' ?> <?= $year ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            min-width: 150px;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-filter {
            padding: 10px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            height: 44px;
            transition: transform 0.2s;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
        }
        
        .btn-back {
            padding: 8px 20px;
            background: #718096;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .stat-card.success::before {
            background: linear-gradient(90deg, #48bb78, #38a169);
        }
        
        .stat-card.primary::before {
            background: linear-gradient(90deg, #4299e1, #3182ce);
        }
        
        .stat-card.warning::before {
            background: linear-gradient(90deg, #ed8936, #dd6b20);
        }
        
        .stat-label {
            color: #718096;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            color: #1a202c;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-subtext {
            color: #a0aec0;
            font-size: 13px;
        }
        
        .stat-growth {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .stat-growth.positive {
            color: #48bb78;
        }
        
        .stat-growth.negative {
            color: #f56565;
        }
        
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .chart-card h3 {
            color: #1a202c;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .table-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .table-card h3 {
            color: #1a202c;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f7fafc;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px;
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tbody tr:hover {
            background: #f7fafc;
        }
        
        .month-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .month-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-completed {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-pending {
            background: #feebc8;
            color: #7c2d12;
        }
        
        .status-cancelled {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .method-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .method-item strong {
            color: #1a202c;
        }
        
        .method-item small {
            color: #718096;
            font-size: 12px;
        }
        
        .method-total {
            color: #667eea;
            font-weight: 700;
            font-size: 16px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .stat-value {
                font-size: 24px;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group select,
            .filter-group input,
            .btn-filter {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Tahun</label>
                    <select name="year">
                        <?php for($y = date('Y'); $y >= 2020; $y--){ ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Bulan (Opsional)</label>
                    <select name="month">
                        <option value="">-- Laporan Tahunan --</option>
                        <?php 
                        $month_names_select = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        foreach($month_names_select as $m => $name){ ?>
                            <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= $name ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn-filter">Tampilkan</button>
                <?php if($month){ ?>
                    <a href="?year=<?= $year ?>" class="btn-back">← Kembali ke Tahunan</a>
                <?php } ?>
            </form>
        </div>

        <?php if($month){ ?>
            <!-- LAPORAN BULANAN -->
            <div class="header">
                <h1>📊 Laporan Keuangan Bulanan</h1>
                <p><?= $current_month_name ?> <?= $year ?></p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Transaksi</div>
                    <div class="stat-value"><?= count($orders) ?></div>
                    <div class="stat-growth <?= $growth_count >= 0 ? 'positive' : 'negative' ?>">
                        <?= $growth_count >= 0 ? '↗' : '↘' ?> <?= abs(number_format($growth_count, 1)) ?>% vs bulan lalu
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-label">Transaksi Selesai</div>
                    <div class="stat-value"><?= $completed_count ?></div>
                    <div class="stat-subtext"><?= count($orders) > 0 ? round(($completed_count/count($orders))*100, 1) : 0 ?>% dari total</div>
                </div>
                
                <div class="stat-card primary">
                    <div class="stat-label">Pendapatan Completed</div>
                    <div class="stat-value">Rp <?= number_format($completed_income, 0, ',', '.') ?></div>
                    <div class="stat-growth <?= $growth_income >= 0 ? 'positive' : 'negative' ?>">
                        <?= $growth_income >= 0 ? '↗' : '↘' ?> <?= abs(number_format($growth_income, 1)) ?>% vs bulan lalu
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-label">Pendapatan Pending</div>
                    <div class="stat-value">Rp <?= number_format($pending_income, 0, ',', '.') ?></div>
                    <div class="stat-subtext"><?= $pending_count ?> transaksi menunggu</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Rata-rata Transaksi</div>
                    <div class="stat-value">Rp <?= number_format($avg_transaction, 0, ',', '.') ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Rata-rata Harian</div>
                    <div class="stat-value">Rp <?= number_format($avg_daily, 0, ',', '.') ?></div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card">
                    <h3>Pendapatan Harian</h3>
                    <canvas id="dailyChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h3>Metode Pembayaran</h3>
                    <?php if(count($payment_methods) > 0){ 
                        foreach($payment_methods as $method => $data){ ?>
                        <div class="method-item">
                            <div>
                                <strong><?= htmlspecialchars($method) ?></strong><br>
                                <small><?= $data['count'] ?> transaksi</small>
                            </div>
                            <div class="method-total">Rp <?= number_format($data['total'], 0, ',', '.') ?></div>
                        </div>
                    <?php } 
                    } else { ?>
                        <p style="text-align:center;color:#718096;padding:40px;">Tidak ada data metode pembayaran</p>
                    <?php } ?>
                </div>
            </div>

            <!-- All Transactions Table -->
            <div class="table-card">
                <h3>Semua Transaksi</h3>
                <?php if(count($orders) > 0){ ?>
                <div class="table-wrapper">
                    <table>
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
                            <?php foreach($orders as $o){ ?>
                            <tr>
                                <td><strong>#<?= $o['id'] ?></strong></td>
                                <td><?= date('d M Y H:i', strtotime($o['placed_on'])) ?></td>
                                <td><?= htmlspecialchars($o['name']) ?></td>
                                <td><?= htmlspecialchars($o['email']) ?></td>
                                <td><?= htmlspecialchars($o['method']) ?></td>
                                <td><strong>Rp <?= number_format($o['total_price'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?= $o['payment_status'] ?>">
                                        <?= ucfirst($o['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h4>Tidak ada transaksi</h4>
                    <p>Belum ada transaksi pada bulan ini</p>
                </div>
                <?php } ?>
            </div>

            <script>
                // Data untuk grafik harian
                const dailyLabels = <?= json_encode(array_column($daily_stats, 'day')) ?>;
                const dailyRevenue = <?= json_encode(array_column($daily_stats, 'completed')) ?>;
                
                const dailyCtx = document.getElementById('dailyChart').getContext('2d');
                new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: dailyLabels,
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: dailyRevenue,
                            borderColor: 'rgb(102, 126, 234)',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            </script>

        <?php } else { ?>
            <!-- LAPORAN TAHUNAN -->
            <div class="header">
                <h1>📅 Laporan Keuangan Tahunan</h1>
                <p>Ringkasan lengkap transaksi dan pendapatan tahun <?= $year ?></p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Transaksi</div>
                    <div class="stat-value"><?= count($orders) ?></div>
                    <div class="stat-subtext">Semua status pembayaran</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-label">Transaksi Selesai</div>
                    <div class="stat-value"><?= $completed_count ?></div>
                    <div class="stat-subtext"><?= count($orders) > 0 ? round(($completed_count/count($orders))*100, 1) : 0 ?>% dari total transaksi</div>
                </div>
                
                <div class="stat-card primary">
                    <div class="stat-label">Pendapatan Selesai</div>
                    <div class="stat-value">Rp <?= number_format($completed_income, 0, ',', '.') ?></div>
                    <div class="stat-subtext">Pembayaran completed</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-label">Total Semua Transaksi</div>
                    <div class="stat-value">Rp <?= number_format($total_income, 0, ',', '.') ?></div>
                    <div class="stat-subtext">Semua status pembayaran</div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card">
                    <h3>Tren Pendapatan Bulanan</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h3>Jumlah Transaksi per Bulan</h3>
                    <canvas id="transactionChart"></canvas>
                </div>
            </div>
            
            <!-- Monthly Statistics Table -->
            <div class="table-card">
                <h3>Statistik Per Bulan (Klik untuk detail)</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Jumlah Transaksi</th>
                                <th>Total Pendapatan</th>
                                <th>Rata-rata per Transaksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($monthly_stats as $m => $stats){ ?>
                                <?php if($stats['count'] > 0){ ?>
                                <tr>
                                    <td>
                                        <a href="?year=<?= $year ?>&month=<?= sprintf('%02d', $m) ?>" class="month-link">
                                            <strong><?= $month_names[$m] ?> →</strong>
                                        </a>
                                    </td>
                                    <td><?= $stats['count'] ?></td>
                                    <td>Rp <?= number_format($stats['completed'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($stats['count'] > 0 ? $stats['completed']/$stats['count'] : 0, 0, ',', '.') ?></td>
                                </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- All Transactions Table -->
            <div class="table-card">
                <h3>Semua Transaksi</h3>
                <?php if(count($orders) > 0){ ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Order</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Metode</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o){ ?>
                            <tr>
                                <td><strong>#<?= $o['id'] ?></strong></td>
                                <td><?= htmlspecialchars($o['name']) ?></td>
                                <td><?= htmlspecialchars($o['email']) ?></td>
                                <td><?= htmlspecialchars($o['method']) ?></td>
                                <td><strong>Rp <?= number_format($o['total_price'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?= $o['payment_status'] ?>">
                                        <?= ucfirst($o['payment_status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($o['placed_on'])) ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h4>Tidak ada transaksi</h4>
                    <p>Belum ada transaksi yang tercatat pada tahun <?= $year ?></p>
                </div>
                <?php } ?>
            </div>
            
            <script>
                // Data untuk grafik
                const monthLabels = <?= json_encode(array_values($month_names)) ?>;
                const monthlyRevenue = <?= json_encode(array_values(array_column($monthly_stats, 'completed'))) ?>;
                const monthlyCount = <?= json_encode(array_values(array_column($monthly_stats, 'count'))) ?>;
                
                // Grafik Pendapatan
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: monthlyRevenue,
                            borderColor: 'rgb(102, 126, 234)',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Grafik Transaksi
                const transactionCtx = document.getElementById('transactionChart').getContext('2d');
                new Chart(transactionCtx, {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: 'Jumlah Transaksi',
                            data: monthlyCount,
                            backgroundColor: 'rgba(72, 187, 120, 0.8)',
                            borderColor: 'rgb(72, 187, 120)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            </script>
        <?php } ?>
    </div>
</body>
</html>

