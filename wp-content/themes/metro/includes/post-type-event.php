<?php 
if ( is_singular() ) { // single post mode
	?>
	<div <?php post_class('post-full'); ?>" id="post-<?php the_ID(); ?>">
		<div class="post-meta">
			<div class="event-date eventBlock">
				<?php if(eo_is_all_day()): ?>
					<?php $date_format = 'j F Y'; ?>
				<?php else: ?>
					<?php $date_format = 'j F Y g:ia'; ?>
				<?php endif; ?>

				<?php printf(__('This event is on %s','eventorganiser'), '<div>'.eo_get_the_start('j F Y').'</div>' );?>
				<div class="nextEvent">
					<?php if(eo_is_all_day()): ?><?php print eo_get_the_start('g:ia'); ?><?php else: ?>all day<?php endif; ?>
				</div>
				<?php if(eo_reoccurs()):?>
					<div class="eventReoccurs">
					<?php $next =   eo_get_next_occurrence();?>
					<?php if($next): ?>
						<?php printf(__('This event is running from %1$s until %2$s','eventorganiser'), eo_get_schedule_start('d F Y'), eo_get_schedule_last('d F Y'));?>

					<?php else: ?>
						<?php printf(__('This event finished on %s','eventorganiser'), '<div>'.eo_get_schedule_last('d F Y','').'</div>');?>
					<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if(get_option(OM_THEME_PREFIX . 'post_hide_categories') != 'true' && $categories = get_the_term_list( get_the_ID(), 'event-category', '', ', ','')) { ?>
				<div class="post-categories">
					<?php echo '<span class="label">'.__('Categories:','om_theme').' </span>'.$categories ?>
				</div>
			<?php } ?>
			<?php the_tags('<div class="post-tags"><span class="label">'.__('Tags:','om_theme').' </span>', ', ', '</div>' ) ?>
			<?php if(get_option(OM_THEME_PREFIX.'post_show_author') == 'true') { ?>
				<div class="post-author"><?php _e('by','om_theme'); ?> <?php the_author(); ?></div>
			<?php } ?>
		</div>
		<?php if(eo_get_venue_name()): ?>
			<div id="#Event-Venue" class="toggler eventBlock">
				<div class="toggleTrigger eventVenue">
					at <span><?php echo eo_get_venue_name(); ?></span>
				</div>
				<div class="venue-archive-meta">
					<?php if(!empty($venue_description)){?>
							<?php echo $venue_description; ?>
					<?php } ?>
					<?php echo do_shortcode('[eo_venue_map width="100%" height="300px"]'); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="post-text">
			<?php if(has_post_thumbnail() && get_option(OM_THEME_PREFIX.'post_single_show_thumb') == 'true') { ?>
				<div class="post-thumb single-pic">
					<?php the_post_thumbnail('page-full'); ?>
				</div>
			<?php } ?>
			<?php the_content(); ?>
		</div>
		<div class="clear"></div>
	</div>

	<?php
	
} else { // posts list mode

	?>
	<div <?php post_class('post-big'); ?> id="post-<?php the_ID(); ?>">
		<div class="eat-left">
			<div class="post-head">
				<div class="post-date block-1 zero-mar" title="<?php the_time( get_option('date_format') ); ?>">
					<div class="block-inner">
						<div class="post-date-inner">
							<?php if(eo_is_all_day()):?>
								<div><?php eo_the_start('d'); ?></div>
								<?php eo_the_start('M'); ?>
								<div>All Day</div>
							<?php else: ?>
								<div><?php eo_the_start('d'); ?></div>
								<?php eo_the_start('M'); ?> 	
								<div class="eventTime"><?php eo_the_start('g:ia'); ?></div>
							<?php endif; ?>

							<?php if(eo_get_venue_name()): ?>
								<?php _e('at','eventorganiser');?> <?php eo_venue_name();?>
							<?php endif; ?>
						</div>
						</div>
				</div>
				<div class="post-title">
					<div class="post-title-inner">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

						<?php if(get_option(OM_THEME_PREFIX . 'post_hide_categories') != 'true' && $categories = get_the_category_list(', ')) { ?>
							<div class="post-categories">
								<?php echo '<span class="label">'.__('Categories:','om_theme').' </span>'.$categories ?>
							</div>
						<?php } ?>
						<?php the_tags('<div class="post-tags"><span class="label">'.__('Tags:','om_theme').' </span>', ', ', '</div>' ) ?>
						<div class="post-comments">
							<?php comments_popup_link( '<span class="label">'.__('Comments:','om_theme').' </span>'.__('No','om_theme'), '<span class="label">'.__('Comments:','om_theme').' </span> 1', '<span class="label">'.__('Comments:','om_theme').' </span> %'); ?>
						</div>

					</div>
				</div>
			</div>
		</div>
		<div class="post-tbl">
			<?php if(has_post_thumbnail()) { ?>
				<div class="post-pic block-3 zero-mar">
					<div class="block-3 zero-mar">
						<div class="block-inner move-left">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail-post-big'); ?></a>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="post-data">
				<div class="post-text">
					<?php (has_excerpt() ? om_custom_excerpt_more(get_the_excerpt()) : the_content(__('Read more', 'om_theme')) );	?>
				</div>
				
			</div>
		</div>
	</div>
	
	<?php
	
}
