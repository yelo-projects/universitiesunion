<?php

/*************************************************************************************
 *	MetaBox Generator
 *************************************************************************************/

function om_generate_meta_box($fields) {
	global $post;

	$output='';

	$output.= '<input type="hidden" name="om_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';

	$output.= '<table class="form-table">';
 
	foreach ($fields as $field) {
		$meta = get_post_meta($post->ID, $field['id'], true);
		switch ($field['type']) {
	
			case 'textarea':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'">
								<strong>'.$field['name'].'</strong>
								<div class="howto">'. $field['desc'].'</div>
							</label>
						</th>
						<td>
							<textarea name="'.$field['id'].'" id="'.$field['id'].'" rows="8" style="width:100%;">'.($meta ? $meta : stripslashes(htmlspecialchars($field['std']))).'</textarea>
						</td>
					</tr>
				';
			break;
			
			case 'text':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'"><strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
						</th>
						<td>
							<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.($meta ? $meta : stripslashes(htmlspecialchars($field['std']))). '" style="width:75%;" />
						</td>
					</tr>
				';
			break;
			
			case 'text_browse':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'"><strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
						</th>
						<td>
							<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.($meta ? $meta : stripslashes(htmlspecialchars($field['std']))). '" style="width:75%;" />
							<input type="button" class="button input-browse-button" rel="'.$field['id'].'" value="'.__('Browse','om_theme').'" title="'.__('Click &laquo;Insert Into Post&raquo; to choose the file','om_theme').'" />
						</td>
					</tr>
				';
			break;

			case 'select':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'"><strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
						</th>
						<td>
							<select id="' . $field['id'] . '" name="'.$field['id'].'">
				';
				foreach ($field['options'] as $k=>$option) {
					$output.= '<option'.($meta == $k ? ' selected="selected"':'').' value="'. $k .'">'. $option .'</option>';
				} 
				$output.='
							</select>
						</td>
					</tr>
				';
			break;

			case 'media_button':
				$button_title=__('Upload Images', 'om_theme');
				if($field['button_title'])
					$button_title=$field['button_title'];
				$output.= '
					<tr>
						<td colspan="2">
							<strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
							<br/><input type="button" class="button" value="'.$button_title.'" onclick="tb_show(\'\', \'media-upload.php?post_id='.($post->ID).'&amp;type=image&amp;TB_iframe=true\');">
						</td>
					</tr>
				';
			break;
			
			case 'sidebar':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'"><strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
						</th>
						<td>
							<select name="'.$field['id'].'" id="'.$field['id'].'"/><option value="">'.__('Main Sidebar','om_theme').'</option>
				';
				$sidebars_num=intval(get_option(OM_THEME_PREFIX."sidebars_num"));
				for($i=1;$i<=$sidebars_num;$i++)
				{
					$output.='<option value="'.$i.'" '.($meta==$i?' selected="selected"':'').'>'.__('Main Alternative Sidebar','om_theme').' '.$i.'</option>';
				}
				$output .='			
							</select>
						</td>
					</tr>
				';
			break;
			
			case 'portfolio_root_cats':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'"><strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
						</th>
						<td>
				';

					$args = array(
						'show_option_all'    => __('All Categories', 'om_theme'),
						'show_option_none'   => '',
						'hide_empty'         => 0, 
						'echo'               => 0,
						'selected'           => $meta,
						'hierarchical'       => 1, 
						'name'               => $field['id'],
						'id'         		     => $field['id'],
						'class'              => '',
						'depth'              => 1,
						'tab_index'          => 0,
						'taxonomy'           => 'portfolio-type',
						'hide_if_empty'      => false 	
					);
			
					$output .= wp_dropdown_categories( $args );

				$output .='			
						</td>
					</tr>
				';
			break;
			
			case 'homepage_root_pages':
				$output.= '
					<tr>
						<th style="width:25%">
							<label for="'.$field['id'].'"><strong>'.$field['name'].'</strong>
							<div class="howto">'. $field['desc'].'</div>
						</th>
						<td>
							<select name="'.$field['id'].'" id="'.$field['id'].'"/><option value="0">'.__('All Blocks','om_theme').'</option>
				';
				
					$pages=get_posts( array(
						'numberposts' => -1,
						'post_parent' => 0,
						'post_type' => 'homepage',
					) );
					
					foreach($pages as $v) {
						$output.='<option value="'.$v->ID.'" '.($meta==$v->ID?' selected="selected"':'').'>'.__('Child blocks of:','om_theme').' '.$v->post_title.'</option>';
					}

				$output .='		
							</select>	
						</td>
					</tr>
				';
			break;
			
		}

	}
	$output.= '</table>';
	
	return $output;
}

/*************************************************************************************
 *	Save MetaBox data
 *************************************************************************************/

function om_save_metabox($post_id, $om_meta_box) {

 	if (!isset($_POST['om_meta_box_nonce']) || !wp_verify_nonce($_POST['om_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}
		
	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}
 
	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
 
 	foreach ($om_meta_box as $metabox_key=>$metabox)
 	{
		foreach ($metabox['fields'] as $field) {
			$old = get_post_meta($post_id, $field['id'], true);
			$new = @$_POST[$field['id']];
			if (($new || $new=='0') && $new != $old) {
				update_post_meta($post_id, $field['id'], $new);
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
			}
		}
	}
}