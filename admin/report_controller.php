<?php
include '../components/connect.php';

// === PENDAPATAN HARIAN ===
$daily = $conn->query("
    SELECT SUM(total_price) AS total 
    FROM orders 
    WHERE payment_status='completed' 
    AND DATE(placed_on) = CURDATE()
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// === PENDAPATAN MINGGUAN ===
$weekly = $conn->query("
    SELECT SUM(total_price) AS total 
    FROM orders 
    WHERE payment_status='completed' 
    AND YEARWEEK(placed_on, 1) = YEARWEEK(CURDATE(), 1)
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// === PENDAPATAN BULANAN ===
$monthly = $conn->query("
    SELECT SUM(total_price) AS total 
    FROM orders 
    WHERE payment_status='completed' 
    AND YEAR(placed_on) = YEAR(CURDATE())
    AND MONTH(placed_on) = MONTH(CURDATE())
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// === PENDAPATAN TAHUNAN ===
$yearly = $conn->query("
    SELECT SUM(total_price) AS total 
    FROM orders 
    WHERE payment_status='completed' 
    AND YEAR(placed_on) = YEAR(CURDATE())
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

?>