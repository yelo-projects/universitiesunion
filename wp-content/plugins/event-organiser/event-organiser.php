<?php
/*
Plugin Name: Event Organiser
Plugin URI: http://www.wp-event-organiser.com
Version: 1.6.1
Description: Creates a custom post type 'events' with features such as reoccurring events, venues, Google Maps, calendar views and events and venue pages
Author: Stephen Harris
Author URI: http://www.stephenharris.info
*/
/*  Copyright 2011 Stephen Harris (contact@stephenharris.info)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/**
 * The main plug-in loader
 */

/**
 * Set the plug-in database version
 * @global string $eventorganiser_db_version
 * @name $eventorganiser_db_version
 */ 
global $eventorganiser_db_version;
$eventorganiser_db_version = "1.6.1";


global $wpdb, $eventorganiser_events_table;
$eventorganiser_events_table = $wpdb->prefix."eo_events";

/**
 * Defines the plug-in directory url
 * <code>url:http://mysite.com/wp-content/plugins/event-organiser</code>
 */
define('EVENT_ORGANISER_URL',plugin_dir_url(__FILE__ ));

/**
 * For use in datetime formats. To return a datetime object rather than formatted string
 */
define( 'DATETIMEOBJ', 'DATETIMEOBJ', true );

/**
 * Defines the plug-in directory path
 * <code>/home/mysite/public_html/wp-content/plugins/event-organiser</code>
 */
define('EVENT_ORGANISER_DIR',plugin_dir_path(__FILE__ ));

/**
 * Defines the plug-in language directory relative to plugins
 * <code> event-organiser/languages </code> 
 */
define('EVENT_ORGANISER_I18N',basename(dirname(__FILE__)).'/languages');

/**
 * Loads translations
 */
function eventorganiser_i18n() {
	load_plugin_textdomain( 'eventorganiser', false, EVENT_ORGANISER_I18N);
}
add_action('init', 'eventorganiser_i18n');

global $eventorganiser_roles;
$eventorganiser_roles = array(
		 'edit_events' =>__('Edit Events','eventorganiser'),
		 'publish_events' =>__('Publish Events','eventorganiser'),
		 'delete_events' => __('Delete Events','eventorganiser'),
		'edit_others_events' =>__('Edit Others\' Events','eventorganiser'),
		 'delete_others_events' => __('Delete Other\'s Events','eventorganiser'),
		'read_private_events' =>__('Read Private Events','eventorganiser'),
		 'manage_venues' => __('Manage Venues','eventorganiser'),
		 'manage_event_categories' => __('Manage Event Categories & Tags','eventorganiser'),
);
			
/****** Install, activation & deactivation******/
require_once(EVENT_ORGANISER_DIR.'includes/event-organiser-install.php');

register_activation_hook(__FILE__,'eventorganiser_install'); 
register_deactivation_hook( __FILE__, 'eventorganiser_deactivate' );
register_uninstall_hook( __FILE__,'eventorganiser_uninstall');


function eventorganiser_get_option($option,$default=false){

      $defaults = array(
		'url_event'=> 'events/event',
		'url_events'=> 'events/event',
		'url_venue'=> 'events/venue',
		'url_cat' => 'events/category',
		'url_tag' => 'events/tag',
		'navtitle' =>  __('Events','eventorganiser'),
		'group_events'=>'',
		'feed' => 1,
		'eventtag' => 1,
		'deleteexpired' => 0,
		'supports' => array('title','editor','author','thumbnail','excerpt','custom-fields','comments'),
		'event_redirect' => 'events',
		'dateformat'=>'dd-mm',
		'prettyurl'=> 1,
		'templates'=> 1,
		'addtomenu'=> 0,
		'menu_item_db_id'=>0,
		'excludefromsearch'=>0,
		'showpast'=> 0,
		'eventtag' => 1,
		'runningisnotpast' => 0,
      );
      $options = get_option('eventorganiser_options',$defaults);
      $options = wp_parse_args( $options, $defaults );

      if( !isset($options[$option]) )
           return $default;

      return $options[$option];
}


/****** Register event post type and event taxonomy******/
require_once(EVENT_ORGANISER_DIR.'includes/event-organiser-cpt.php');

/****** Register scripts, styles and actions******/
require_once(EVENT_ORGANISER_DIR.'includes/event-organiser-register.php');

/****** Deals with the queries******/
require_once(EVENT_ORGANISER_DIR.'includes/event-organiser-archives.php');

/****** Deals with importing/exporting & subscriptions******/
require_once(EVENT_ORGANISER_DIR."includes/class-event-organiser-im-export.php");

if(is_admin()):
	require_once(EVENT_ORGANISER_DIR.'classes/class-eventorganiser-admin-page.php');

	/****** event editing pages******/
	require_once(EVENT_ORGANISER_DIR.'event-organiser-edit.php');
	require_once(EVENT_ORGANISER_DIR.'event-organiser-manage.php');
        	
	/****** settings, venue and calendar pages******/
	require_once(EVENT_ORGANISER_DIR.'event-organiser-settings.php');
	require_once(EVENT_ORGANISER_DIR.'event-organiser-venues.php');
	require_once(EVENT_ORGANISER_DIR.'event-organiser-calendar.php');
	
else:
    /****** Templates ******/
    require_once('includes/event-organiser-templates.php');    
endif;

if ( defined('DOING_AJAX') && DOING_AJAX ) {
    /****** Ajax actions ******/
    require_once(EVENT_ORGANISER_DIR.'includes/event-organiser-ajax.php');
}

/****** Functions ******/
require_once(EVENT_ORGANISER_DIR."includes/event-organiser-event-functions.php");
require_once(EVENT_ORGANISER_DIR."includes/event-organiser-venue-functions.php");
require_once(EVENT_ORGANISER_DIR."includes/event-organiser-utility-functions.php");
require_once(EVENT_ORGANISER_DIR."includes/deprecated.php");
require_once(EVENT_ORGANISER_DIR."includes/event.php");

/****** Widgets and Shortcodes ******/
require_once(EVENT_ORGANISER_DIR.'classes/class-eo-agenda-widget.php');
require_once(EVENT_ORGANISER_DIR.'classes/class-eo-event-list-widget.php');
require_once(EVENT_ORGANISER_DIR.'classes/class-eo-calendar-widget.php');
require_once(EVENT_ORGANISER_DIR.'classes/class-eventorganiser-shortcodes.php');

add_action( 'widgets_init', 'eventorganiser_widgets_init');
function eventorganiser_widgets_init(){
	load_plugin_textdomain( 'eventorganiser', false, EVENT_ORGANISER_I18N);
	register_widget('EO_Event_List_Widget');
	register_widget('EO_Events_Agenda_Widget');
	register_widget('EO_Calendar_Widget');
}
?>
