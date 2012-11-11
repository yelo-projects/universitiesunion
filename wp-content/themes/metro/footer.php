		<?php
			$footer_left = get_option( OM_THEME_PREFIX.'footer_text_left' );
			
			$icons=array('twitter', 'facebook', 'linkedin', 'behance', 'rss', 'blogger', 'deviantart', 'dribble', 'flickr', 'lastfm', 'google', 'myspace', 'pinterest', 'skype', 'vimeo', 'youtube');
			$color=get_option(OM_THEME_PREFIX.'social_icons_color');
			if(!$color)
				$color='light';
			$icons_html='';
			foreach($icons as $v) {
				if( $icon = get_option(OM_THEME_PREFIX.'social_'.$v) )
					$icons_html.= '<a href="'.$icon.'" class="social color-'.$color.' '.$v.'"></a>';
			}

			$is_footer_sidebars = ( is_active_sidebar('footer-column-left') || is_active_sidebar('footer-column-center') || is_active_sidebar('footer-column-right') );
			$is_sub_footer = ($footer_left || $icons_html);
			if($is_footer_sidebars || $is_sub_footer) {
		?>
		
			<!-- Footer -->
			
			<div class="footer block-full bg-color-footer">
				<div class="eat-outer-margins">
					
					<?php if ($is_footer_sidebars) { ?>
						<div class="block-3">
							<div class="block-inner widgets-area">
								<?php get_sidebar('footer-column-left'); ?>
							</div>
						</div>
						
						<div class="block-3">
							<div class="block-inner widgets-area">
								<?php get_sidebar('footer-column-center'); ?>
							</div>
						</div>
						
						<div class="block-3">
							<div class="block-inner widgets-area">
								<?php get_sidebar('footer-column-right'); ?>
							</div>
						</div>
			
						<div class="clear"></div>
					<?php } ?>

					<?php if($is_footer_sidebars && $is_sub_footer) { ?>
						<div class="block-full"><div class="block-inner" style="padding-top:0;padding-bottom:0"><div class="sub-footer-divider"></div></div></div>
					<?php } ?>
					
					<?php if($is_sub_footer) { ?>
						<!-- SubFooter -->
						<div class="block-full sub-footer">
							<div class="block-inner">
								<div class="two-third sub-footer-column-1"><?php echo ($footer_left?$footer_left:'&nbsp;') ?></div>
								<div class="one-third last sub-footer-column-2"><?php echo $icons_html ?></div>
								<div class="clear"></div>
							</div>
						</div>
						
						<!-- /SubFooter -->
					<?php } ?>
		
					
				</div>
			</div>
			
			<!-- /Footer -->

		<?php } ?>
		

	</div>
	
	<?php wp_footer(); ?>
	
	<?php echo get_option( OM_THEME_PREFIX . 'code_before_body' ) ?>
</div>	
</body>
</html>