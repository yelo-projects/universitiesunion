<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php
    if (!defined('WPSEO_VERSION')) {
        wp_title('|', true, 'right');
        bloginfo('name');
    }
    else {
        //IF WordPress SEO by Yoast is activated
        wp_title('');
    }?></title>


	<!-- Pingbacks -->
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/custom.css.php" type="text/css" />
	<?php if(get_option(OM_THEME_PREFIX . 'responsive') == 'true') : ?>
		<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/responsive.css" type="text/css" />
	<?php endif; ?>
	<!--[if IE 8]>
		<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/ie8.css" type="text/css" />
	<![endif]-->
	<!--[if lt IE 8]>
		<style>body{background:#fff;font:18px/24px Arial} .bg-overlay{display:none} .chromeframe {margin:40px;text-align:center} .chromeframe a{color:#0c5800;text-decoration:underline}</style>
	<![endif]-->

	<?php
		$custom_css=get_option(OM_THEME_PREFIX . 'code_custom_css');
		if($custom_css)
			echo '<style>'.$custom_css.'</style>';
	?>
	
	<?php echo get_option( OM_THEME_PREFIX . 'code_before_head' ) ?>
	
	<?php wp_head(); ?>
</head>
<?php
	$body_class='';
	if(get_option(OM_THEME_PREFIX.'sidebar_position')=='left')
		$body_class='flip-sidebar';
	if(@$post) {
		$sidebar_post=get_post_meta($post->ID, OM_THEME_SHORT_PREFIX.'sidebar_custom_pos', true);
		if($sidebar_post == 'left')
			$body_class='flip-sidebar';
		elseif($sidebar_post == 'right')
			$body_class='';
	}
?>
<body <?php body_class( $body_class ) ?>>
<!--[if lt IE 8]><p class="chromeframe"><?php _e('Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.','om_theme'); ?></p><![endif]-->
<div class="bg-overlay">

	<div class="container">
		
		<!-- Headline -->
		<div class="headline block-full">
			<div class="headline-text">
				<?php echo get_option(OM_THEME_PREFIX . 'intro_text') ?>
			</div>
		</div>
		<!-- /Headline -->
	
		<!-- Logo & Menu -->
		
		<div class="logo-pane block-3 block-h-1 bg-color-menu">
			<div class="logo-pane-inner">

				<?php
				if(get_option(OM_THEME_PREFIX . 'site_logo_type') == 'text') {
					echo '<div class="logo-text"><a href="' . home_url() .'">'. get_option(OM_THEME_PREFIX . 'site_logo_text') .'</a></div>';
				} else {
					if( $tmp=get_option(OM_THEME_PREFIX . 'site_logo_image') )
						echo '<div class="logo-image"><a href="' . home_url() .'"><img src="'.$tmp.'" alt="'.htmlspecialchars( get_bloginfo( 'name' ) ).'" /></a></div>';
				}
				?>
			</div>
		</div>
		
		<?php
			if ( has_nav_menu( 'primary-menu' ) ) {

				function om_nav_menu_classes ($items) {

					function hasSub ($menu_item_id, &$items) {
		        foreach ($items as $item) {
	            if ($item->menu_item_parent && $item->menu_item_parent==$menu_item_id) {
	              return true;
	            }
		        }
		        return false;
					};					
					
					$menu_root_num=0;
					foreach($items as $item) {
						if(!$item->menu_item_parent)
							$menu_root_num++;
							
						if (hasSub($item->ID, $items)) {
							$item->classes[] = 'menu-parent-item';
						}
					}
					if($menu_root_num < 7)
						$size_class='block-h-1';
					else
						$size_class='block-h-half';
					foreach ($items as &$item) {
						if($item->menu_item_parent)
							continue;
						$item->classes[] = 'block-1';
						$item->classes[] = $size_class;
					}
					return $items;    
				}
				add_filter('wp_nav_menu_objects', 'om_nav_menu_classes');	

				$menu = wp_nav_menu( array(
					'theme_location' => 'primary-menu',
					'container' => false,
					'echo' => false,
					'link_before'=>'<span>',
					'link_after'=>'</span>',
					'items_wrap' => '%3$s'
				) );
				
				remove_filter('wp_nav_menu_objects', 'om_nav_menu_classes');	
				
				$root_num=preg_match_all('/class="[^"]*block-1[^"]*"/', $menu, $m);
				echo '<ul class="primary-menu block-6 no-mar">'.$menu;
				$blank_num=0;
				$blank_str='';
				if($root_num < 7) {
					$blank_num=6-$root_num;
					$blank_str='<li class="block-1 block-h-1 blank">&nbsp;</li>';
				} elseif($root_num < 13) {
					$blank_num=12-$root_num;
					$blank_str='<li class="block-1 block-h-half blank">&nbsp;</li>';
				}
				echo str_repeat($blank_str,$blank_num);
				echo '</ul>';

		
				echo '<div class="primary-menu-select bg-color-menu">';
				om_select_menu( 'primary-menu' );
				echo '</div>';
			}
		?>
		<div class="clear"></div>
		
		<!-- /Logo & Menu -->

		<?php if( is_front_page() ) { get_template_part('includes/homepage-slider'); } ?>