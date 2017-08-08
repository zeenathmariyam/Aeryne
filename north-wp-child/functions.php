<?php
define('THEME_TEXT', 'north-child');
/*-----------------------------------------------------------------------------------

	Here we have all the custom functions for the theme
	Please be extremely cautious editing this file.
	You have been warned!

-------------------------------------------------------------------------------------*/
add_action( 'after_setup_theme', function () {
    load_theme_textdomain(THEME_TEXT, get_template_directory() . '/languages/');
});


add_action('wp_enqueue_scripts', function () {
    if (!is_admin()) {
        wp_deregister_script('thb-app');
        wp_register_script('thb-app', get_stylesheet_directory_uri() . '/assets/js/plugins/app2.js', array('jquery', 'thb-vendor'), null, TRUE);
    }
});

require get_stylesheet_directory() .'/inc/woocommerce.php'; //Denna rad MÅSTE kommenteras bort i huvudtemats-function.php

// Define Theme Name for localization

//global $sitepress;
//var_dump($sitepress);


// this is used for taxing:
add_filter('woocommerce_countries_base_country', 'set_base_to_usercountry', 1, 1);
// and this is used for shipping:
add_filter('woocommerce_customer_default_location', 'set_base_to_usercountry', 1, 1);
function set_base_to_usercountry($country) {

    switch (ICL_LANGUAGE_CODE)
    {
        case 'sv':
            return 'se';
            break;
        case 'fr':
            return 'fr';
            break;
        default:
            return 'gb';
    }
    
    //$country = 'se';//ICL_LANGUAGE_CODE; //USERCOUNTRY; // comes from a geoIP lookup in my case.
    return $country;
}
/*
// and this is also needed not to have trouble with the "modded_tax".
// (which looks like rounding issues, but is a tax conversion issue.)
//add_filter('woocommerce_customer_taxable_address', 'alter_taxable_address', 1, 1);
function alter_taxable_address($address) {
    // $address comes as an array with 4 elements. 
    // first element keeps the 2-digit country code.
    $address[0] = ICL_LANGUAGE_CODE; 
    return $address;
}*/



add_action( 'wp_enqueue_styles', function () {
    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/child-style.css',
        array( $parent_style ),
        '1.0'
    );
});

/*add_action('wp_print_scripts', function () {
    wp_dequeue_script('wc-cart-fragments');
    return true;
}, 999);*/


require_once 'inc/WCo_Locale.php';
require_once 'inc/Aeryne_Academy.php';

add_filter('woocommerce_get_availability_text', function($availability, $product) {
    if ($product->get_total_stock() == 0)
        $availability = __("Oh no, out of stock! Don't hesitate to contact customer service for information on when the product is back on track!", THEME_TEXT);

    return $availability;
}, 10, 2);

add_filter( 'woocommerce_adjust_non_base_location_prices', '__return_false' );

/*if (is_user_logged_in() && get_current_user_id() == 139) {
    add_filter('woocommerce_order_needs_payment', '__return_false');
    add_filter('woocommerce_cart_needs_payment', '__return_false');
}*7

/***wishlist counter -zee ***/

if( defined( 'YITH_WCWL' ) && ! function_exists( 'yith_wcwl_ajax_update_count' ) ){
function yith_wcwl_ajax_update_count(){
wp_send_json( array(
'count' => yith_wcwl_count_all_products()
) );
}
add_action( 'wp_ajax_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );

function enqueue_custom_wishlist_js(){
	wp_enqueue_script( 'yith-wcwl-custom-js', get_stylesheet_directory_uri() . '/assets/js/plugins/yith-wcwl-custom.js', array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_wishlist_js' );
}


/*
if( defined( 'YITH_WCWL' ) && ! function_exists( 'yith_wcwl_ajax_update_count_product' ) ){
function yith_wcwl_ajax_update_count_product($product_id = false ){
wp_send_json( array(
'count' => yith_wcwl_count_add_to_wishlist($product_id)
) );
}
add_action( 'wp_ajax_yith_wcwl_update_wishlist_count_product', 'yith_wcwl_ajax_update_count_product' );
add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count_product', 'yith_wcwl_ajax_update_count_product' );
//add_action( 'wp_enqueue_scripts', 'enqueue_custom_wishlist_js' );

}
*/
/***wishlist counter -zee ***/
/***product display -zee ***/
//require_once 'inc/product-display.php';
/*
function product_display_columns(){

return "inside the function";

}
add_filter( 'show_columns', 'product_display_columns');

function column_script(){
	wp_enqueue_script( 'column-js', get_stylesheet_directory_uri() . '/assets/js/plugins/column.js', array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'column_script' );

///**********************/

if ( ! defined( 'WPINC' ) ) { die; }


/* 1. REGISTER SHORTCODE
------------------------------------------ */

/* Init Hook */
add_action( 'init', 'my_wp_ajax_noob_plugin_init', 10 );

/**
 * Init Hook to Register Shortcode.
 * @since 1.0.0
 */
function my_wp_ajax_noob_plugin_init(){

	/* Register Shortcode */
	add_shortcode( 'john-cena', 'my_wp_ajax_noob_john_cena_shortcode_callback' );

}

/**
 * Shortcode Callback
 * Just display empty div. The content will be added via AJAX.
 */
function my_wp_ajax_noob_john_cena_shortcode_callback(){

	/* Enqueue JS only if this shortcode loaded. */
	//wp_enqueue_script( 'my-wp-ajax-noob-john-cena-script' );get_template_directory_uri() get_stylesheet_directory_uri()
	wp_enqueue_script( 'my-wp-ajax-noob-john-cena-script', get_stylesheet_directory_uri() . "/assets/script.js", array( 'jquery' ), '1.0.0', true );

	/* Output empty div. */
	return '<div id="john-cena"></div>';
}


/* 2. REGISTER SCRIPT
------------------------------------------ */

/* Enqueue Script */
add_action( 'wp_enqueue_scripts', 'my_wp_ajax_noob_scripts' );

/**
 * Scripts
 */
function my_wp_ajax_noob_scripts(){



	/* JS + Localize */
//	wp_register_script( 'my-wp-ajax-noob-john-cena-script', get_stylesheet_directory_uri() . "assets/script.js", array( 'jquery' ), '1.0.0', true );
	wp_localize_script( 'my-wp-ajax-noob-john-cena-script', 'john_cena_ajax_url', site_url().'/wp-admin/admin-ajax.php' );
}


/* 3. AJAX CALLBACK
------------------------------------------ */

/* AJAX action callback */
add_action( 'wp_ajax_john_cena', 'my_wp_ajax_noob_john_cena_ajax_callback' );
add_action( 'wp_ajax_nopriv_john_cena', 'my_wp_ajax_noob_john_cena_ajax_callback' );


/**
 * Ajax Callback
 */
function my_wp_ajax_noob_john_cena_ajax_callback(){
	$first_name = isset( $_POST['first_name'] ) ? $_POST['first_name'] : 'N/A';
	$last_name = isset( $_POST['last_name'] ) ? $_POST['last_name'] : 'N/A';
	?>
	<p>Hello. Your first name is <?php echo strip_tags( $first_name ); ?>.</p>
	<p>And your last name is <?php echo strip_tags( $last_name ); ?>.</p>
	<?php
	wp_die(); // required. to end AJAX request.
}





/***product display -zee ***/