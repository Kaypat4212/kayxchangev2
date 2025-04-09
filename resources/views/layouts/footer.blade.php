<!-- Add this line to your <head> section -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<!-- ======= Footer ======= -->
<style>
  /* Footer Styles */
  .footer {
    background: #f6f9ff;
    padding: 0 0 30px 0;
    font-size: 14px;
    text-decoration: none;
  }

  .footer .footer-newsletter {
    padding: 50px 0;
    background: #00911d;
    border-top: 1px solid #e1ecff;
  }

  .footer .footer-newsletter h4 {
    font-size: 24px;
    margin: 0 0 10px 0;
    padding: 0;
    line-height: 1;
    font-weight: 700;
    color: #012970;
  }

  .footer .footer-newsletter form {
    margin-top: 20px;
    background: #01622d;
    padding: 6px 10px;
    position: relative;
    border-radius: 4px;
    border: 1px solid #00a42e;
  }

  .footer .footer-newsletter form input[type=email], .footer .footer-newsletter form input[type=text] {
    border: 0;
    padding: 8px;
    width: calc(100% - 140px);
  }

  .footer .footer-newsletter form input[type=submit] {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    border: 0;
    background: none;
    font-size: 16px;
    padding: 0 30px;
    margin: 3px;
    background: #d2f2d6;
    color: #000000;
    transition: 0.3s;
    border-radius: 4px;
  }

  .footer .footer-newsletter form input[type=submit]:hover {
    background: #00671d;
    color: white;
  }

  .footer .footer-top {
    background: white url(../img/footer-bg.png) no-repeat right top;
    background-size: contain;
    border-top: 1px solid #e1ecff;
    border-bottom: 1px solid #e1ecff;
    padding: 60px 0 30px 0;
  }

  @media (max-width: 992px) {
    .footer .footer-top {
      background-position: center bottom;
    }
  }

  .footer .footer-top .footer-info {
    margin-bottom: 30px;
  }

  .footer .footer-top .footer-info .logo {
    line-height: 0;
    margin-bottom: 15px;
  }

  .footer .footer-top .footer-info .logo img {
    max-height: 40px;
    margin-right: 6px;
  }

  .footer .footer-top .footer-info .logo span {
    font-size: 30px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #012970;
    font-family: "Nunito", sans-serif;
    margin-top: 3px;
  }

  .footer .footer-top .footer-info p {
    font-size: 14px;
    line-height: 24px;
    margin-bottom: 0;
    font-family: "Nunito", sans-serif;
  }

  .footer .footer-top .social-links a {
    font-size: 20px;
    display: inline-block;
    color: rgba(1, 41, 112, 0.5);
    line-height: 0;
    margin-right: 10px;
    transition: 0.3s;
  }

  .footer .footer-top .social-links a:hover {
    color: #012970;
  }

  .footer .footer-top h4 {
    font-size: 16px;
    font-weight: bold;
    color: #012970;
    text-transform: uppercase;
    position: relative;
    padding-bottom: 12px;
  }

  .footer .footer-top .footer-links {
    margin-bottom: 30px;
  }

  .footer .footer-top .footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .footer .footer-top .footer-links ul i {
    padding-right: 2px;
    color: #d0d4fc;
    font-size: 12px;
    line-height: 0;
    text-decoration: none;
  }

  .footer .footer-top .footer-links ul li {
    padding: 10px 0;
    display: flex;
    align-items: center;
    text-decoration: none;
  }

  .footer .footer-top .footer-links ul li:first-child {
    padding-top: 0;
  }

  .footer .footer-top .footer-links ul a {
    color: #013289;
    transition: 0.3s;
    display: inline-block;
    line-height: 1;
    text-decoration: none;
  }

  .footer .footer-top .footer-links ul a:hover {
    color: #4154f1;
  }

  .footer .footer-top .footer-contact p {
    line-height: 26px;
  }

  .footer .copyright {
    text-align: center;
    padding-top: 30px;
    color: #012970;
  }

  .footer .credits {
    padding-top: 10px;
    text-align: center;
    font-size: 13px;
    color: #012970;
  }

  /* Animation */
  .footer {
    opacity: 0;
    transform: translateY(50px);
    transition: opacity 0.5s, transform 0.5s;
  }

  .footer.aos-animate {
    opacity: 1;
    transform: translateY(0);
  }
</style>

<footer id="footer" class="footer aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">

  <div class="footer-newsletter">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12 text-center">
          <h4 class="text-white">Our Newsletter</h4>
          <p>Subscribe to our newsletter to stay updated with the latest <br> Market news & Updates</p>
        </div>
        <div class="col-lg-6 ">
          <form method="post" action="./subscribe.php">
            <input type="text" placeholder="Your name" name="name"> <br>
            <input class="mt-4" type="email" placeholder="Email address" name="email">
            <input type="submit" value="Subscribe">
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-top">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-5 col-md-12 footer-info">
          <a href="/" class="logo d-flex align-items-center">
            <img src="Assests/favicon.png" alt="">
          </a>
          <h3 style="color: green;">Socials</h3>
          <div class="social-links mt-3">
            <a href="https://www.twitter.com/kay__xchange" class="twitter"><i style="color: green;" class="bi bi-twitter"></i></a>
            <a href="https://api.whatsapp.com/send?phone=+2349016740523&text=Hello%2C%20I%20would%20like%20to%20start%20a%20trade" class="whatsapp"><i style="color: green;" class="bi bi-whatsapp"></i></a>
            <a href="https://www.instagram.com/kay__xchange" class="instagram"><i style="color: green;" class="bi bi-instagram"></i></a>
            <a href="https://t.snapchat.com/nIuJb4u1" class="snapchat"><i style="color: green;" class="bi bi-snapchat"></i></a>
          </div>
        </div>

        <!-- Other Footer Sections (Company, Useful Links, Contact) remain unchanged -->

      </div>
    </div>
  </div>

  <div class="container">
    <div style="color: green;" class="copyright">
      &copy; Copyright <strong><span>Kayxchange</span></strong>. <br> All Rights Reserved 2020-2025
    </div>
  </div>
</footer><!-- End Footer -->

<!-- AOS Library -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js" integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init();
</script>
