<?php

define('OM_THEME_PREFIX', 'om_metro_');
define('OM_THEME_SHORT_PREFIX', 'om_');
define('OM_THEME_NAME', 'metro');
define('TEMPLATE_DIR_URI', get_template_directory_uri());

/*************************************************************************************
 *	Translation Text Domain
 *************************************************************************************/

load_theme_textdomain('om_theme');

/*************************************************************************************
 *	Register WP3.0+ Menu
 *************************************************************************************/
 
if( !function_exists( 'om_register_menu' ) ) {
	function om_register_menu() {
	  register_nav_menu('primary-menu', __('Primary Menu', 'om_theme'));
	  //register_nav_menu('reserved-menu', __('Reserved Menu (can be used in widget)', 'om_theme'));
	}

	add_action('init', 'om_register_menu');
}

/*************************************************************************************
 *	Set Max Content Width
 *************************************************************************************/
 
if ( ! isset( $content_width ) ) $content_width = 940;

/*************************************************************************************
 *	Post Formats
 *************************************************************************************/
 
add_theme_support( 'post-formats', array(
		'audio',
		'gallery', 
		'image', 
		'link', 
		'quote', 
		'video'
)); 
	
/*************************************************************************************
 *	Post Thumbnails
 *************************************************************************************/

if( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 144, 144, true );
	add_image_size( 'thumbnail-post-big', 456, 300, true); // for blogroll
	add_image_size( 'portfolio-thumb', 480, 328, true); // for portfolio
	add_image_size( 'portfolio-q-thumb', 480, 480, true); // for portfolio
	add_image_size( 'page-full', 900, '', false); 
	add_image_size( 'page-full-2', 924, '', false); 
}

/*************************************************************************************
 *	Automatic Feed Links
 *************************************************************************************/

function om_feedburner_hook() {
	echo '<link rel="alternate" type="application/rss+xml" title="'. get_bloginfo( 'name' ) .' RSS Feed" href="'. get_option(OM_THEME_PREFIX.'feedburner') .'" />';
}

if (get_option(OM_THEME_PREFIX.'feedburner')) {
	add_action('wp_head','om_feedburner_hook');
} else {
	add_theme_support( 'automatic-feed-links' );
}

/*************************************************************************************
 *	Excerpt Length
 *************************************************************************************/

if( !function_exists( 'om_excerpt_length' ) ) {
	function om_excerpt_length($length) {
		return 15; 
	}
	add_filter('excerpt_length', 'om_excerpt_length');
}

if( !function_exists( 'om_excerpt_more' ) ) {
	function om_excerpt_more( $more ) {
		global $post;
		return ' <a href="'. get_permalink($post->ID) . '">'.__('Read more', 'om_theme').'</a>';
	}
	add_filter('excerpt_more', 'om_excerpt_more');
}

function om_custom_excerpt_more($excerpt, $return=false) {
	global $post;
	
	$more=' <a href="'. get_permalink($post->ID) . '">'.__('Read more', 'om_theme').'</a>';
	
	if( ($pos=strrpos($excerpt, '</p>')) === false)
		$excerpt = $excerpt.$more;
	else
		$excerpt = substr($excerpt,0,$pos).$more.substr($excerpt,$pos);
	
	if($return)
		return $excerpt;
	else
		echo $excerpt;
}

/*************************************************************************************
 *	Remove Read More Jump
 *************************************************************************************/

function om_remove_more_jump_link($link) {
	$offset = strpos($link, '#more-');
	if ($offset !== false) {
		$end = strpos($link, '"',$offset);
		$link = substr_replace($link, '', $offset, $end-$offset);
	}

	return $link;
}
add_filter('the_content_more_link', 'om_remove_more_jump_link');

/*************************************************************************************
 *	Register Sidebars
 *************************************************************************************/

if( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => __('Main Sidebar','om_theme'),
		'before_widget' => '<div class="block-3 bg-color-sidebar"><div class="block-inner widgets-area">',
		'after_widget' => '</div></div>',
		'before_title' => '<div class="widget-header">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'name' => __('Footer Left Column','om_theme'),
		'id' => 'footer-column-left',
		'before_widget' => '',
		'after_widget' => '<div class="clear"></div>',
		'before_title' => '<div class="widget-header">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'name' => __('Footer Center Column','om_theme'),
		'id' => 'footer-column-center',
		'before_widget' => '',
		'after_widget' => '<div class="clear"></div>',
		'before_title' => '<div class="widget-header">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'name' => __('Footer Right Column','om_theme'),
		'id' => 'footer-column-right',
		'before_widget' => '',
		'after_widget' => '<div class="clear"></div>',
		'before_title' => '<div class="widget-header">',
		'after_title' => '</div>',
	));

	$sidebars_num=intval(get_option(OM_THEME_PREFIX."sidebars_num"));
	for($i=1;$i<=$sidebars_num;$i++)
	{
		register_sidebar(array(
			'name' => __('Main Alternative Sidebar','om_theme').' '.$i,
			'id' => 'alt-sidebar-'.$i,
			'before_widget' => '<div class="block-3 bg-color-sidebar"><div class="block-inner widgets-area">',
			'after_widget' => '</div></div>',
			'before_title' => '<div class="widget-header">',
			'after_title' => '</div>',
		));	
	}
	
}

/*************************************************************************************
 *	Widgets
 *************************************************************************************/

// Latest Tweets
include_once("widgets/tweets/tweets.php");

// Flickr
include_once("widgets/flickr/flickr.php");

// Video
include_once("widgets/video/video.php");

// Recent Posts
include_once("widgets/recent-posts/recent-posts.php");

// Recent Portfolio
include_once("widgets/recent-portfolio/recent-portfolio.php");

// No-margins
include_once("widgets/no-margins/no-margins.php");

// Facebook
include_once("widgets/facebook/facebook.php");

// Testimonials
include_once("widgets/testimonials/testimonials.php");

// Apply Shortcodes for Widgets
add_filter('widget_text', 'do_shortcode');

/*************************************************************************************
 *	Front-end JS/CSS
 *************************************************************************************/
 
if(!function_exists('om_enqueue_scripts')) {
	function om_enqueue_scripts() {

		wp_register_script('isotope', TEMPLATE_DIR_URI.'/js/jquery.isotope.min.js', array('jquery'), false, true);
		wp_register_script('jPlayer', TEMPLATE_DIR_URI.'/js/jquery.jplayer.min.js', array('jquery'), false, true);
		wp_register_script('prettyPhoto', TEMPLATE_DIR_URI.'/js/jquery.prettyPhoto.js', array('jquery'), false, true);
		wp_register_script('omSlider', TEMPLATE_DIR_URI.'/js/jquery.omslider.min.js', array('jquery'), false, true);
		wp_register_script('libraries', TEMPLATE_DIR_URI.'/js/libraries.js', array('jquery'), false, true);
		wp_register_script('validate', TEMPLATE_DIR_URI.'/js/jquery.validate.min.js', array('jquery'), false, true);
		wp_register_script('form', TEMPLATE_DIR_URI.'/js/jquery.form.min.js', array('jquery'), false, true);
		wp_register_script('om_custom', TEMPLATE_DIR_URI.'/js/custom.js', array('jquery','omSlider','libraries'), false, true);

		// Enqueue - No conditions as for use on all pages
		wp_enqueue_script('jquery');
		wp_enqueue_script('jPlayer');
		wp_enqueue_script('prettyPhoto');
		wp_enqueue_script('omSlider');
		wp_enqueue_script('libraries');
		wp_enqueue_script('isotope');
		wp_enqueue_script('validate');
		wp_enqueue_script('form');
		wp_enqueue_script('om_custom');
		
		// styles
		wp_register_style('prettyPhoto', TEMPLATE_DIR_URI.'/css/prettyPhoto.css');
		wp_enqueue_style('prettyPhoto');
	
  }

	add_action('wp_enqueue_scripts', 'om_enqueue_scripts');
}

/*************************************************************************************
 *	More Functions
 *************************************************************************************/

require_once (TEMPLATEPATH . '/functions/misc.php');
require_once (TEMPLATEPATH . '/functions/breadcrumbs.php');
require_once (TEMPLATEPATH . '/functions/common-meta.php');
require_once (TEMPLATEPATH . '/functions/homepage.php');
require_once (TEMPLATEPATH . '/functions/homepage-meta.php');
require_once (TEMPLATEPATH . '/functions/comments.php');
require_once (TEMPLATEPATH . '/functions/page-meta.php');
require_once (TEMPLATEPATH . '/functions/post-meta.php');
require_once (TEMPLATEPATH . '/functions/portfolio.php');
require_once (TEMPLATEPATH . '/functions/portfolio-meta.php');
require_once (TEMPLATEPATH . '/functions/shortcodes.php');
require_once (TEMPLATEPATH . '/functions/testimonials.php');
require_once (TEMPLATEPATH . '/functions/testimonials-meta.php');
require_once (TEMPLATEPATH . '/functions/galleries.php');
require_once (TEMPLATEPATH . '/functions/galleries-meta.php');
require_once (TEMPLATEPATH . '/functions/contact-form.php');
require_once (TEMPLATEPATH . '/functions/facebook-comments.php');

/*************************************************************************************
 *	TinyMCE Shortcodes button
 *************************************************************************************/

require_once (TEMPLATEPATH . '/tinymce/tinymce.php');


/*************************************************************************************
 *	Theme Options
 *************************************************************************************/

require_once (TEMPLATEPATH . '/admin/admin-functions.php');
require_once (TEMPLATEPATH . '/admin/admin-interface.php');
require_once (TEMPLATEPATH . '/functions/theme-options.php');

/*************************************************************************************
 *	Custom Login Logo
 *************************************************************************************/

function om_custom_login_logo() {
	echo '<style type="text/css">h1 a { background-image:url('.TEMPLATE_DIR_URI.'/img/custom-logo-login.png) !important; }</style>';
}
add_action('login_head', 'om_custom_login_logo');

function om_login_headerurl() {
	return home_url();
}
add_filter('login_headerurl', 'om_login_headerurl');

function om_login_headertitle() {
	return get_option('blogname');
}
add_filter('login_headertitle', 'om_login_headertitle');


/*************************************************************************************
 *	Sidebar Sliding
 *************************************************************************************/

function om_sliding_sidebar() {

	echo '<script>jQuery(document).ready(function(){sidebar_slide_init();});</script>';
	
}
 
if(get_option(OM_THEME_PREFIX."sidebar_sliding") == 'true') {
	
	add_action('wp_head', 'om_sliding_sidebar');
	
}

/*************************************************************************************
 *	Hide Homepage slider on mobile view
 *************************************************************************************/

function om_hide_homepage_slider_on_mobile() {

	echo '<style>@media only screen and (max-width: 767px){ .big-slider-wrapper, .big-slider-control {display:none !important} }</style>';
	
}
 
if(get_option(OM_THEME_PREFIX . 'hide_homepage_on_mobile') == 'true') {
	
	add_action('wp_head', 'om_hide_homepage_slider_on_mobile');
	
}