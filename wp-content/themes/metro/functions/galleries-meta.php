<?php

/*************************************************************************************
 *	Add MetaBox to Galleries edit page
 *************************************************************************************/

$om_galleries_meta_box=array (

	'imageblock' => array (
		'id' => 'om-galleries-meta-box-images',
		'name' =>  __('Gallery Images', 'om_theme'),
		'callback' => 'om_galleries_meta_box_images',
		'fields' => array (
			array ( "name" => '',
					"desc" => __('NOTE: You don\'t need to click "Insert gallery" or anything else after uploading images. Just upload images, close the popup and click "Publish/Update".<br/>To insert the gallery on the page open it for edition and click "Insert Shortcode - Custom Gallery", then choose the gallery to insert.','om_theme'),
					"button_title" => __('View / Upload / Edit / Delete Images','om_theme'),
					"id" => OM_THEME_SHORT_PREFIX."galleries_images",
					"type" => "media_button",
					"std" => ''
			),
		),
	),


);
 
function om_add_galleries_meta_box() {
	global $om_galleries_meta_box;
	
	foreach($om_galleries_meta_box as $metabox) {
		add_meta_box(
			$metabox['id'],
			$metabox['name'],
			$metabox['callback'],
			'galleries',
			'normal',
			'high'
		);
	}
 
}
add_action('add_meta_boxes', 'om_add_galleries_meta_box');

/*************************************************************************************
 *	MetaBox Callbacks Functions
 *************************************************************************************/


function om_galleries_meta_box_images() {
	global $om_galleries_meta_box;

	echo om_generate_meta_box($om_galleries_meta_box['imageblock']['fields']);
}

/*************************************************************************************
 *	Save MetaBox data
 *************************************************************************************/

function om_save_galleries_metabox($post_id) {
	global $om_galleries_meta_box;
 
	om_save_metabox($post_id, $om_galleries_meta_box);

}
add_action('save_post', 'om_save_galleries_metabox');

