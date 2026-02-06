<?php
if (isset($message)) {
   foreach ($message as $message) {
      echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
<style>
   /* Message Notifications */
.message {
   position: fixed;
   top: 20px;
   right: 20px;
   background: #fff;
   padding: 15px 20px;
   border-radius: 8px;
   box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
   display: flex;
   align-items: center;
   gap: 15px;
   z-index: 10000;
   animation: slideIn 0.3s ease;
   border-left: 4px solid #5f3afc;
}

.message span {
   color: #333;
   font-size: 16px;
}

.message i {
   cursor: pointer;
   color: #999;
   font-size: 18px;
   transition: color 0.3s;
}

.message i:hover {
   color: #e74c3c;
}

@keyframes slideIn {
   from {
      transform: translateX(100%);
      opacity: 0;
   }
   to {
      transform: translateX(0);
      opacity: 1;
   }
}

/* Header */
.header {
   background: #fff;
   box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
   position: sticky;
   top: 0;
   left: 0;
   right: 0;
   z-index: 1000;
}

.header .flex {
   display: flex;
   align-items: center;
   justify-content: space-between;
   padding: 1.5rem 5%;
   max-width: 1400px;
   margin: 0 auto;
}

/* Logo */
.header .logo {
   font-size: 24px;
   font-weight: 700;
   color: #2c3e50;
   text-decoration: none;
   transition: color 0.3s;
}

.header .logo span {
   color: #5f3afc;
}

.header .logo:hover {
   color: #5f3afc;
}

/* Navbar */
.navbar {
   display: flex;
   align-items: center;
   gap: 25px;
}

.navbar a {
   color: #6c757d;
   text-decoration: none;
   font-size: 16px;
   font-weight: 500;
   padding: 8px 15px;
   border-radius: 6px;
   transition: all 0.3s;
   position: relative;
}

.navbar a:hover {
   color: #5f3afc;
   background: #f8f9fa;
}

.navbar a::after {
   content: '';
   position: absolute;
   bottom: 0;
   left: 50%;
   transform: translateX(-50%);
   width: 0;
   height: 2px;
   background: #5f3afc;
   transition: width 0.3s;
}

.navbar a:hover::after {
   width: 70%;
}

/* Dropdown Menu */
.dropdown {
   position: relative;
   display: inline-block;
}

.dropbtn {
   background: transparent;
   color: #6c757d;
   font-size: 16px;
   font-weight: 500;
   border: none;
   cursor: pointer;
   padding: 8px 15px;
   border-radius: 6px;
   transition: all 0.3s;
   display: flex;
   align-items: center;
   gap: 8px;
}

.dropbtn:hover {
   color: #5f3afc;
   background: #f8f9fa;
}

.dropbtn i {
   font-size: 12px;
   transition: transform 0.3s;
}

.dropdown:hover .dropbtn i {
   transform: rotate(180deg);
}

.dropdown-content {
   display: none;
   position: absolute;
   background: #fff;
   min-width: 200px;
   box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
   border-radius: 8px;
   z-index: 1;
   top: 100%;
   margin-top: 5px;
   overflow: hidden;
}

.dropdown-content a {
   color: #333;
   padding: 12px 20px;
   text-decoration: none;
   display: block;
   font-size: 15px;
   transition: all 0.3s;
}

.dropdown-content a:hover {
   background: #f8f9fa;
   color: #5f3afc;
   padding-left: 25px;
}

.dropdown:hover .dropdown-content {
   display: block;
   animation: dropdownFade 0.3s ease;
}

@keyframes dropdownFade {
   from {
      opacity: 0;
      transform: translateY(-10px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}

/* Icons */
.icons {
   display: flex;
   align-items: center;
   gap: 15px;
}

.icons div {
   width: 45px;
   height: 45px;
   background: #f8f9fa;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 18px;
   color: #6c757d;
   cursor: pointer;
   transition: all 0.3s;
}

.icons div:hover {
   background: #5f3afc;
   color: #fff;
   transform: scale(1.1);
}

#menu-btn {
   display: none;
}

/* Profile Dropdown */
.profile {
   position: absolute;
   top: 120%;
   right: 5%;
   background: #fff;
   border-radius: 12px;
   box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
   padding: 20px;
   width: 250px;
   display: none;
   animation: profileFade 0.3s ease;
}

.profile.active {
   display: block;
}

@keyframes profileFade {
   from {
      opacity: 0;
      transform: translateY(-10px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}

.profile p {
   font-size: 18px;
   font-weight: 600;
   color: #2c3e50;
   margin-bottom: 15px;
   text-align: center;
   padding-bottom: 15px;
   border-bottom: 1px solid #e9ecef;
}

.profile .btn {
   display: block;
   width: 100%;
   padding: 12px;
   background: #5f3afc;
   color: #fff;
   text-align: center;
   text-decoration: none;
   border-radius: 8px;
   margin-bottom: 10px;
   font-weight: 500;
   transition: all 0.3s;
}

.profile .btn:hover {
   background: #4a2fd1;
   transform: translateY(-2px);
   box-shadow: 0 5px 15px rgba(95, 58, 252, 0.3);
}

.profile .delete-btn {
   display: block;
   width: 100%;
   padding: 12px;
   background: #fff;
   color: #e74c3c;
   text-align: center;
   text-decoration: none;
   border-radius: 8px;
   border: 2px solid #e74c3c;
   font-weight: 500;
   transition: all 0.3s;
}

.profile .delete-btn:hover {
   background: #e74c3c;
   color: #fff;
   transform: translateY(-2px);
   box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

/* Responsive */
@media (max-width: 991px) {
   .header .flex {
      padding: 1.5rem 3%;
   }

   #menu-btn {
      display: flex;
   }

   .navbar {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: #fff;
      flex-direction: column;
      align-items: flex-start;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
      transition: clip-path 0.3s ease;
   }

   .navbar.active {
      clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
   }

   .navbar a {
      width: 100%;
      padding: 12px 20px;
      margin: 5px 0;
   }

   .dropdown {
      width: 100%;
   }

   .dropbtn {
      width: 100%;
      justify-content: space-between;
   }

   .dropdown-content {
      position: static;
      box-shadow: none;
      margin-top: 0;
      margin-left: 20px;
   }
}

@media (max-width: 450px) {
   .header .logo {
      font-size: 20px;
   }

   .profile {
      right: 3%;
      width: 220px;
   }
}
</style>

<header class="header">

   <section class="flex">

      <a href="dashboard.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="dashboard.php">home</a>
         <a href="products.php">products</a>
         <a href="placed_orders.php">orders</a>

         <!-- Dropdown Menu -->
         <div class="dropdown">
            <button class="dropbtn">
               reports <i class="fas fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
               <a href="report_daily.php">Daily Report</a>
               <a href="report_weekly.php">Weekly Report</a>
               <a href="report_monthly.php">Monthly Report</a>
               <a href="report_yearly.php">Yearly Report</a>
            </div>
         </div>
         <!-- End Dropdown -->

         <a href="admin_accounts.php">admins</a>
         <a href="users_accounts.php">users</a>
         <a href="employee_accounts.php">employees</a>
         <a href="messages.php">messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
         $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
         $select_profile->execute([$admin_id]);
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="update_profile.php" class="btn">update profile</a>
         <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">logout</a>
      </div>

   </section>

</header>