<!--<?php

      include 'components/connect.php';

      session_start();

      if (isset($_SESSION['user_id'])) {
         $user_id = $_SESSION['user_id'];
      } else {
         $user_id = '';
      };

      include 'components/add_cart.php';

      ?> -->

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <!-- showcase area -->

   <section class="hero">

      <div class="swiper hero-slider">

         <div class="swiper-wrapper">

            <div class="swiper-slide slide">
               <div class="content">
                  <span>order online</span>
                  <h3>Coffe Shop</h3>
                  <a href="menu.php" class="btn">see menus</a>
               </div>
               <div class="image">
                  <img src="images/coffe.jpg" alt="">
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="content">
                  <span>order online</span>
                  <h3>pizza</h3>
                  <a href="menu.php" class="btn">lihat menu </a>
               </div>
               <div class="image">
                  <img src="images/home-img-1.png" alt="">
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="content">
                  <span>order online</span>
                  <h3>chezzy hamburger</h3>
                  <a href="menu.php" class="btn">see menus</a>
               </div>
               <div class="image">
                  <img src="images/home-img-2.png" alt="">
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="content">
                  <span>order online</span>
                  <h3>rosted chicken</h3>
                  <a href="menu.php" class="btn">see menus</a>
               </div>
               <div class="image">
                  <img src="images/home-img-3.png" alt="">
               </div>
            </div>

         </div>

         <div class="swiper-pagination"></div>

      </div>

   </section>

   <!-- booking Area -->

   <section class="booking">
      <h1 class="title"> Kopi membangun dasar energimu setiap hari.</h1>


      <div class="bookinput">
         <input type="text" value=" 1 Pesan ">
         <label for="for">
            <h3>FOR</h3>
         </label>
         <input type="date">
         <label for="at">
            <h3 class="p-2">AT</h3>
         </label>
         <input type="time" value="7.00pm">
         <input type="submit" class="btn" value="BOOK A TABLE">
      </div>
   </section>

   <!-- category Area -->

   <section class="category">

      <h1 class="title">food category</h1>

      <div class="box-container">

         <a href="category.php?category=Coffee" class="box">
            <img src="images/cat-1.png" alt="">
            <h3>Coffee</h3>
         </a>

         <a href="category.php?category=main dish" class="box">
            <img src="images/cat-2.png" alt="">
            <h3>Special dishes</h3>
         </a>

         <a href="category.php?category=drinks" class="box">
            <img src="images/cat-3.png" alt="">
            <h3>drinks</h3>
         </a>

         <a href="category.php?category=desserts" class="box">
            <img src="images/cat-4.png" alt="">
            <h3>desserts</h3>
         </a>

      </div>

   </section>
   <!-- give way -->

   <!-- products -->

   <section class="products">

      <h1 class="title">latest dishes</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 8");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
                  <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                  <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="flex">
                     <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  </div>
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">no products added yet!</p>';
         }
         ?>

      </div>

      <div class="more-btn">
         <a href="menu.php" class="btn">veiw all</a>
      </div>

   </section>


   <section class="products">

      <h1 class="title">Most Customer Choise May You Like</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY popularity DESC LIMIT 4");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
                  <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                  <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="flex">
                     <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  </div>
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">no products added yet!</p>';
         }
         ?>

      </div>

      <div class="more-btn">
         <a href="product.php" class="btn">veiw all</a>
      </div>

   </section>

   <section class="products">

      <h1 class="title">New Arrive</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY popularity DESC LIMIT 4");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
                  <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                  <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="flex">
                     <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  </div>
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">no products added yet!</p>';
         }
         ?>

      </div>

      <div class="more-btn">
         <a href="product.php" class="btn">veiw all</a>
      </div>

   </section>

   <section class="products">

      <h1 class="title">Explore our shop</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 12");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
                  <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                  <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="flex">
                     <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  </div>
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">no products added yet!</p>';
         }
         ?>

      </div>

      <div class="more-btn">
         <a href="product.php" class="btn">veiw all</a>
      </div>

   </section>

   <!-- about section starts  -->

   <section class="about">

      <div class="row">

         <div class="image">
            <img src="images/witers.jpg" alt="">
         </div>

         <div class="content">
            <h3>kenapa milih kami </h3>
            <p>Nikmati kemudahan berbelanja hanya dengan sekali klik! Tanpa perlu keluar rumah, semua kebutuhanmu bisa langsung dipesan secara online dengan aman dan cepat. Produk berkualitas, harga bersahabat, dan layanan yang ramah siap memanjakanmu. Yuk, buktikan sendiri betapa mudah dan praktisnya belanja di sini!</p>
            <a href="menu.php" class="btn">our menu</a>
         </div>

      </div>

   </section>

   <!-- about section ends -->

   <!-- steps section starts  -->

   <section class="steps">

      <h1 class="title">simple steps</h1>

      <div class="box-container">

         <div class="box">
            <img src="images/step-1.png" alt="">
            <h3>silahkan order</h3>
            <p>Setiap cangkir kami diseduh dari biji kopi pilihan dan disajikan dengan sepenuh hati.Pilih rasa yang kamu suka — dari yang pahit menenangkan hingga manis yang menyapa hangat.Cukup satu klik untuk memesan, dan biarkan aroma kopi kami menghidupkan kenangan di setiap tegukan.</p>
         </div>

         <div class="box">
            <img src="images/step-2.png" alt="">
            <h3>fast delivery</h3>
            <p>Pesan sekarang dan nikmati kopi hangatmu dalam hitungan menit.Kami antar cepat, agar rasa dan aroma terbaik tetap sampai di tanganmu.🚀 Karena kenangan tak perlu menunggu lama.</p>
         </div>

         <div class="box">
            <img src="images/step-3.png" alt="">
            <h3>enjoy food</h3>
            <p>selamat menikmati makanan dan minuman</p>
         </div>

      </div>

   </section>
   <!-- For News Letter -->
   <section class="subscribe">
      <h1 class="title">For News Letter</h1>
      <p>Gabung Bersama Pecinta Kopi! Dapatkan info promo, menu terbaru, dan kisah seru seputar dunia kopi langsung di inbox kamu.💌 Berlangganan sekarang dan nikmati kenikmatan setiap seduhan tanpa ketinggalan!</p>
      <input type="email" placeholder="Enter Your Email" class="email">
      <input type="submit" value="subscribe" class="btn">
   </section>
   <!-- Map  -->



   <section class="contact">

      <div class="row">

         <div class="map">
         <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3769.166902166161!2d106.68893947475169!3d-6.3460956936437185!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69e5a6e26dc3cd%3A0xccd6344b8021119d!2sPamulang%20University%20Campus%202%20(UNPAM%20Viktor)!5e1!3m2!1sen!2sid!4v1762754713948!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            

         <form action="" method="post">
            <h3>Contact Us !</h3>
            <input type="text" name="name" maxlength="50" class="box" placeholder="Enter Your Name" required>
            <input type="email" name="email" maxlength="50" class="box" placeholder="Enter Your Email" required>
            <textarea name="msg" class="box" required placeholder="Enter Your Message" maxlength="500" cols="30" rows="10"></textarea>
            <input type="submit" value="send" name="send" class="btn">
         </form>

      </div>

   </section>


   <!-- steps section ends -->


   <?php include 'components/footer.php'; ?>


   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
   <!-- custom js file link  -->
   <script src="js/script.js"></script>

   <script>
      var swiper = new Swiper(".hero-slider", {
         loop: true,
         grabCursor: true,
         effect: "flip",
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
      });
   </script>

</body>
</html>