<?php
get_header(); ?>

		<div class="block-6 no-mar content-with-sidebar">
			<div class="block-6 bg-color-main">
				<div class="block-inner">
					<?php
	          if ( current_user_can( 'edit_post', $post->ID ) )
	      	    edit_post_link( __('edit', 'om_theme'), '<div class="edit-post-link">[', ']</div>' );
	    		?>
					<div class="tbl-bottom">
						<div class="tbl-td">
							<h1 class="page-h1"><?php the_title(); ?></h1>
						</div>
						<?php if(get_option(OM_THEME_PREFIX . 'show_breadcrumbs') == 'true') { ?>
							<div class="tbl-td">
								<?php om_breadcrumbs(get_option(OM_THEME_PREFIX . 'breadcrumbs_caption')) ?>
							</div>
						<?php } ?>
					</div>
					<div class="clear page-h1-divider"></div>
	      		
	          <?php echo get_option(OM_THEME_PREFIX . 'code_after_page_h1'); ?>
	
						<?php while (have_posts()) : the_post(); ?>
			
							<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
								<?php the_content(); ?>
							</div>

<?php
global $post;
$cats=array();
foreach(get_the_category() as $category) {
    $cats[]=$category->cat_ID;
}
$showposts = -1; // -1 shows all posts
$do_not_show_stickies = 1; // 0 to show stickies
$args=array(
   'category__in' => $cats,
   'showposts' => $showposts,
   'caller_get_posts' => $do_not_show_stickies
   );
$my_query = new WP_Query($args);
 
?>
 
    <?php if( $my_query->have_posts() ) : ?>
 
        <?php while ($my_query->have_posts()) : $my_query->the_post(); ?>
            <?php
            //necessary to show the tags
            global $wp_query;
            $wp_query->in_the_loop = true;
            ?>
            <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                <small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>
 
                <div class="entry">
                    <?php the_content('Read the rest of this entry »'); ?>
                </div>
 
                <p class="postmetadata"><?php the_tags('Tags: ', ', ', '
'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments »', '1 Comment »', '% Comments »'); ?></p>
            </div>
 
        <?php endwhile; ?>
 
    <?php else : ?>
 
        <h2 class="center">Not Found</h2>
        <p class="center">Sorry, but you are looking for something that isn't here.</p>
 
    <?php endif; ?>

						<?php endwhile; ?>
						
						<?php echo get_option(OM_THEME_PREFIX . 'code_after_page_content'); ?>
						
						<?php wp_link_pages(array('before' => '<div class="navigation-pages"><span class="title">'.__('Pages:', 'om_theme').'</span>', 'after' => '</div>', 'pagelink' => '<span class="item">%</span>', 'next_or_number' => 'number')); ?>
								
				</div>
			</div>

			<?php
				$fb_comments=false;
				if(function_exists('om_facebook_comments') && get_option(OM_THEME_PREFIX . 'fb_comments_pages') == 'true') {
						if(get_option(OM_THEME_PREFIX . 'fb_comments_position') == 'after')
							$fb_comments='after';
						else
							$fb_comments='before';
				}
			?>
			
			<?php if($fb_comments == 'before') { om_facebook_comments();	} ?>
			
			<?php if(get_option(OM_THEME_PREFIX . 'hide_comments_pages') != 'true') : ?>
				<?php comments_template('',true); ?>
			<?php endif; ?>
			
			<?php if($fb_comments == 'after') { om_facebook_comments();	} ?>

		</div>


		<div class="block-3 no-mar sidebar">
			<?php
				// alternative sidebar
				$alt_sidebar=intval(get_post_meta($post->ID, OM_THEME_SHORT_PREFIX.'sidebar', true));
				if($alt_sidebar && $alt_sidebar <= intval(get_option(OM_THEME_PREFIX."sidebars_num")) ) {
					if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'alt-sidebar-'.$alt_sidebar ) ) ;
				} else {
					get_sidebar();
				}
				?>
		</div>
		
		<!-- /Content -->
		
		<div class="clear anti-mar">&nbsp;</div>

<?php get_footer(); ?>
