<!-- <?php

      include 'components/connect.php';

      session_start();

      if (isset($_SESSION['user_id'])) {
         $user_id = $_SESSION['user_id'];
      } else {
         $user_id = '';
      };

      ?> -->

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <!-- header section starts  -->
   <?php include 'components/user_header.php'; ?>
   <!-- header section ends -->

   <div class="heading">
      <h3>about us</h3>
      <p><a href="home.php">Home</a> <span> / About</span></p>
   </div>

   <!-- Our Ower -->
   <section class="card">

      <img src="images/juan.jpg" class="card-img" alt="...">

      <div class="doc">
         <h3 class="title">Pemilik CAFE </h3>
         <br>
         <p> <b>JUAN ALFREDO FERRYERA SITUMORANG </b></p>
         <p> TEKNIK INFORMATIKA <br> Department TEKNIK INFORMATIKA
            <br>
            <b>Office: </b> R.313<br> <b> Email: </b> 12222222@gmail.com
         </p>
         <br>
         <br>
         <a href="https://www.linkedin.com/public-profile/settings/?trk=d_flagship3_profile_self_view_public_profile&lipi=urn%3Ali%3Apage%3Ad_flagship3_profile_view_base%3BZRVG59wyR7WErviuvkRUIw%3D%3D" target="_blank" class="btn">MASUK</a>
      </div>

   </section>

   <!-- Our Team -->


   <section class="team">

      <h1 class="title">TEAM SAYA </h1>

      <div class="swiper-wrapper">
         <div class="box">
            <img src="images/fazri.jpg" alt="">

            <h2>Fajri Muhammad / wakil onwer </h2>
            <h3>ID: TAC-WOLF-N7-032</h3>
         </div>

         <div class="box">
            <img src="images/farrel.jpg" alt="">

            <h2>Farrell / bendahara </h2>
            <h3>ID: OP-BlackRevenant-33</h3>
         </div>

         <div class="box">
            <img src="images/riski.jpg" alt="">
            <h2>RISKI / seketaris</h2>
            <h3>ID: Shadow-Delta-09</h3>
         </div>

         <div class="box">
            <img src="images/robi.jpg" alt="">
            <h2>Robi / pemasaran</h2>
            <h3>ID: TAC-VIPER-S2-901</h3>
         </div>
      </div>

   </section>

   <!-- about section starts  -->

   <section class="about">

      <div class="row">

         <div class="image">
            <img src="images/barista.jpeg" alt="">
         </div>

         <div class="content">
            <h3>Our Mission</h3>
            <p>Misi kami adalah menginspirasi inovasi, menumbuhkan kreativitas, dan memberdayakan individu untuk membentuk dunia yang lebih cerdas dan terhubung melalui teknologi.</p>
            <br>
            <br>
            <p>To inspire innovation, nurture creativity, and empower individuals to shape a smarter and more connected world through technology.</p>
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
            <h3>choose order</h3>
            <p>Setiap cangkir kami diseduh dari biji kopi pilihan dan disajikan dengan sepenuh hati.Pilih rasa yang kamu suka — dari yang pahit menenangkan hingga manis yang menyapa hangat.Cukup satu klik untuk memesan, dan biarkan aroma kopi kami menghidupkan kenangan di setiap tegukan..</p>
         </div>

         <div class="box">
            <img src="images/step-2.png" alt="">
            <h3>fast delivery</h3>
            <p>Pesan sekarang dan nikmati kopi hangatmu dalam hitungan menit.Kami antar cepat, agar rasa dan aroma terbaik tetap sampai di tanganmu.🚀 Karena kenangan tak perlu menunggu lama.</p>
         </div>

         <div class="box">
            <img src="images/step-3.png" alt="">
            <h3>enjoy food</h3>
            <p>Selamat menimakti</p>
         </div>

      </div>

   </section>

   <!-- steps section ends -->

   <!-- reviews section starts  -->

   <section class="reviews">

      <h1 class="title">customer's reivews</h1>

      <div class="swiper reviews-slider">

         <div class="swiper-wrapper">

            <div class="swiper-slide slide">
               <img src="images/davina.jpg" alt="">
               <p>Cafe Zxuan punya suasana yang cozy dan estetik banget. Kopinya enak, aromanya strong tapi rasanya tetap smooth. Makanannya juga fresh dan porsinya pas. Staff-nya ramah, cepat, dan sangat membantu. Tempat duduk nyaman dan banyak colokan, cocok buat kerja, belajar, atau nongkrong. Musiknya juga santai dan nggak terlalu berisik. Overall, Cafe Zxuan wajib dikunjungi, terutama buat pecinta kopi dan tempat nongki yang tenang.</p>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
               <h3>DAVINA</h3>
            </div>

            <div class="swiper-slide slide">
               <img src="images/Vonzy Felicia.jpg" alt="">
               <p>Cafe Zxuan menawarkan pengalaman bersantai yang nyaman dengan kualitas kopi dan hidangan terbaik. Pelayanan cepat, ramah, dan profesional. Tempatnya bersih, tenang, serta sangat cocok untuk pertemuan, belajar, maupun bekerja. Sangat direkomendasikan bagi siapa saja yang mencari suasana cafe yang elegan dan menyenangkan.</p>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
               <h3>Vonzy Felicia</h3>
            </div>

            <div class="swiper-slide slide">
               <img src="images/Kim Soo hyunjpg.jpg" alt="">
               <p>Cafe Zxuan menawarkan pengalaman bersantai yang nyaman dengan kualitas kopi dan hidangan terbaik. Pelayanan cepat, ramah, dan profesional. Tempatnya bersih, tenang, serta sangat cocok untuk pertemuan, belajar, maupun bekerja. Sangat direkomendasikan bagi siapa saja yang mencari suasana cafe yang elegan dan menyenangkan.</p>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
               <h3>Kim Soo hyun</h3>
            </div>

            <div class="swiper-slide slide">
               <img src="images/ferryera.jpg" alt="">
               <p>Pertama kali datang ke Cafe Zxuan langsung jatuh cinta dengan suasananya. Kopinya luar biasa enak, makanan rasanya konsisten, dan pelayanannya benar-benar bikin nyaman. Tempatnya cozy dan bikin betah berlama-lama. Cafe Zxuan adalah salah satu cafe terbaik yang pernah saya kunjungi!</p>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
               <h3>ferryra</h3>
            </div>

            <div class="swiper-slide slide">
               <img src="images/about-card.jpg" alt="">
               <p>Temukan kenikmatan kopi terbaik di Café Zxuan. Dengan suasana hangat dan pelayanan ramah, kami siap menemani waktu santai, kerja, dan momen bersama orang tersayang. Ayo kunjungi Café Zxuan hari ini</p>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
               <h3>amalia</h3>
            </div>

            <div class="swiper-slide slide">
               <img src="images/haya.jpg" alt="">
               <p>Kami percaya, setiap kopi punya cerita. Terima kasih sudah menjadi bagian dari cerita kami.</p>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
               <h3>haya</h3>
            </div>

         </div>

         <div class="swiper-pagination"></div>

      </div>

   </section>

   <!-- reviews section ends -->




   <!-- footer section starts  -->
   <?php include 'components/footer.php'; ?>
   <!-- footer section ends -->=






   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

   <script>
      var swiper = new Swiper(".reviews-slider", {
         loop: true,
         grabCursor: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         breakpoints: {
            0: {
               slidesPerView: 1,
            },
            700: {
               slidesPerView: 2,
            },
            1024: {
               slidesPerView: 3,
            },
         },
      });
   </script>

</body>

</html>