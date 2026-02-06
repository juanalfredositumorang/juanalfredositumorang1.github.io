<?php

@include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

// ===============================
//     QUERY LAPORAN PENDAPATAN
// ===============================

// Harian
$today = date('Y-m-d');
$daily = $conn->prepare("SELECT SUM(total_price) AS total FROM orders WHERE placed_on = ?");
$daily->execute([$today]);
$daily_income = $daily->fetchColumn() ?? 0;

// Mingguan (7 hari terakhir)
$weekly = $conn->prepare("SELECT SUM(total_price) AS total FROM orders WHERE placed_on >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$weekly->execute();
$weekly_income = $weekly->fetchColumn() ?? 0;

// Bulanan
$month = date('m');
$year = date('Y');
$monthly = $conn->prepare("SELECT SUM(total_price) AS total FROM orders WHERE MONTH(placed_on)=? AND YEAR(placed_on)=?");
$monthly->execute([$month,$year]);
$monthly_income = $monthly->fetchColumn() ?? 0;

// Tahunan
$yearly = $conn->prepare("SELECT SUM(total_price) AS total FROM orders WHERE YEAR(placed_on)=?");
$yearly->execute([$year]);
$yearly_income = $yearly->fetchColumn() ?? 0;

?>

<!-- HTML START -->
<?php include 'admin_header.php'; ?>

<section class="dashboard">

   <h1 class="heading">Laporan Pendapatan</h1>

   <div class="box-container">

      <div class="box">
         <h3>Rp<?= number_format($daily_income, 0, ',', '.') ?></h3>
         <p>Pendapatan Harian</p>
      </div>

      <div class="box">
         <h3>Rp<?= number_format($weekly_income, 0, ',', '.') ?></h3>
         <p>Pendapatan Mingguan</p>
      </div>

      <div class="box">
         <h3>Rp<?= number_format($monthly_income, 0, ',', '.') ?></h3>
         <p>Pendapatan Bulanan</p>
      </div>

      <div class="box">
         <h3>Rp<?= number_format($yearly_income, 0, ',', '.') ?></h3>
         <p>Pendapatan Tahunan</p>
      </div>

   </div>

   <h2 class="heading" style="margin-top:20px;">Semua Transaksi</h2>

   <div class="box-container">
      <table border="1" cellpadding="10" cellspacing="0" style="width:100%; background:#fff; color:#000;">
         <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Total Harga</th>
            <th>Tanggal</th>
         </tr>

         <?php
            $orders = $conn->prepare("SELECT * FROM orders ORDER BY placed_on DESC");
            $orders->execute();
            if($orders->rowCount() > 0){
               while($row = $orders->fetch(PDO::FETCH_ASSOC)){
         ?>

         <tr>
             <td><?= $row['id']; ?></td>
             <td><?= $row['name']; ?></td>
             <td>Rp<?= number_format($row['total_price'], 0, ',', '.') ?></td>
             <td><?= $row['placed_on']; ?></td>
         </tr>

         <?php } } else { ?>

         <tr>
            <td colspan="4" style="text-align:center;">Tidak ada data transaksi.</td>
         </tr>

         <?php } ?>
      </table>
   </div>

</section>

<style>
.box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit, minmax(250px,1fr));
   gap:15px;
}
.box{
   background:#333;
   padding:20px;
   text-align:center;
   border-radius:10px;
}
.box h3{
   color:#fff;
   font-size:26px;
}
.box p{
   color:#ccc;
   margin-top:5px;
}
</style>
