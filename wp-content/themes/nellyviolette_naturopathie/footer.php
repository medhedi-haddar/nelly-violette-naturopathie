<footer>
        <div class="container">
            <div class="row">
            <div class="col-md-2">
                <ul class="footer-menu">
                    <li>
                        <a  href="#">NELLY </a>
                    </li>
                    <li >
                        <a  href="#">LA NATUROPATHE</a>
                    </li>
                    <li >
                        <a  href="#">TARIFS</a>
                    </li>
                    <li >
                        <a  href="#">CONTACT</a>
                    </li>
                    <li >
                        <a  href="#">BLOG</a>
                    </li>
                </ul>
                <ul class="reseaux-social">
                    <li><i class="fa fa-facebook"></i><a href=""> facebook</a></li>
                    <li><i class="fa fa-twitter"></i><a href=""> twitter</a></li>
                    <li><i class="fa fa-youtube"></i><a href=""> youtube</a></li>
                </ul>
            </div>
            <div class="col-md-3 ml-md-auto" id="newsletter">
                <h3>Newsletter</h3>
                <p>recevez le conseil de nelly chaque Vendredi</p>
                <form action="">
                    <input type="email" name="" id="">
                    <button type="submit">Ok</button>
                </form>
            </div>
            <div class="col-md-5 ml-md-auto" id="certifie">
                <h3>certifié</h3>
                <p>Quo pervenire ante certam diem non licebit solitudines suorum</p>
                <div class="row justify-content-around">
                    <object type="image/svg+xml" data="<?php echo esc_url(get_template_directory_uri()).'/assets/img/logo-La-fena-blanc.svg';?>" width="20%" height="auto"></object>
                    <object type="image/svg+xml" data="<?php echo esc_url(get_template_directory_uri()).'/assets/img/logo-OMNES-blanc.svg';?>" width="20%" height="auto"></object>
                    <object type="image/svg+xml" data="<?php echo esc_url(get_template_directory_uri()).'/assets/img/logo-isupnat-blanc.svg';?>" width="20%" height="auto"></object>
                </div>
                <p>La naturopathie ne se substitue jamais à la médecine conventionnelle allopathique. Le praticien naturopathe ne remplace en
                    aucun cas votre médecin et ne pose aucun diagnostic.
                </p>
            </div>
        </div>
        <div class="row text-center justify-content-around" id="end">
            <p>Plan du site, Mentions légales… SIRET ?… Droits photos…Malivolus aperta cum provincias saepeque ab metuens desineret in coalitos
            desineret coalitos cum graviter se desineret tandem coalitos illas pericula</p>
            <p id="copyright">Tous droits réservés © 2018 - Nelly Violette - Practicien Naturopathe à Paris</p>
        </div>
        </div>
    </footer>

<script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
    <script src="<?php echo esc_url(get_template_directory_uri()).'/assets/js/bootstrap.min.js';?>"></script>
 
    <script src="<?php echo esc_url(get_template_directory_uri()).'/assets/slick/slick.js';?>"></script>
    <script>
     $(document).on('ready', function () {
            $(".aside-slider").slick({
             dots: true,
             infinite: true,
             slidesToShow: 1,
             slidesToScroll: 1,
             arrows: false,
             autoplay:true
         });
     });

     $(function () {
            $('.comment, .pour-quoi').click(function () {
                $(this).toggleClass('active');
            });
        });
    </script>
    </body>
</html>