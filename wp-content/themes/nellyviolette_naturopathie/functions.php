<?php 
/*  menu init */
require_once('bs4navwalker.php');
function register_my_menu() {
    register_nav_menus(array(

    
                'header-menu'=>__( 'Header Menu' ),
                'footer-menu'=>__( 'Footer Menu' )
    )
    );
}
add_action( 'init', 'register_my_menu' );

?>