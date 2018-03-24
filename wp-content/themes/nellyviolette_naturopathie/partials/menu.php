<header>
    <div class="container">
        <div class="row flex-row justify-content-between" id="header">    
            <div class="logo-nelly">
                <a href="">
                    <img src="<?php echo esc_url(get_template_directory_uri()).'/assets/images/logo.png';?>" alt="">
                </a>
            </div>
            <div class="menu-bar row flex-column justify-content-between ">
                <div class="menu-rapide">
                    <ul class="nav ml-auto justify-content-end pr-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" id="recherche-menu">Recherche sur le site</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="question-menu">Poser une question</a>
                        </li>
                    </ul>
                    
                </div>
                <nav class="navbar navbar-expand-lg navbar-light">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <?php
                        $args =array( 
                                'theme_location'    => 'header-menu' ,
                                'container'         => false,
                                'container_id'    => 'bs4navbar',
                                'container_class' => 'collapse navbar-collapse',
                                'menu_id'         => false,
                                'menu_class'      => 'navbar-nav ml-auto',
                                'depth'           => 2,
                                'fallback_cb'     => 'bs4navwalker::fallback',
                                'walker'          => new bs4navwalker()
                                ) ;
                        wp_nav_menu( $args);
                    ?>
                </nav>
            </div>
        </div>
    </div>
</header>