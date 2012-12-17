<?php
 /**
 * Register jQuery scripts and CSS files
 * Hooked on to init
 *
 * @since 1.0.0
 * @ignore
 * @access private
 */
function eventorganiser_register_script() {
	global $wp_locale;
	$version = '1.6.1';

	$ext = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) ? '' : '.min';

	/* FullCalendar */
	wp_register_script( 'eo_fullcalendar', EVENT_ORGANISER_URL."js/fullcalendar{$ext}.js",array(
		'jquery',
		'jquery-ui-core',
		'jquery-ui-widget',
		'jquery-ui-button',
	),$version,true);	

	/* Front-end script */
	wp_register_script( 'eo_front', EVENT_ORGANISER_URL."js/frontend{$ext}.js",array(
		'jquery','eo_qtip2',
		'jquery-ui-core',
		'jquery-ui-widget',
		'jquery-ui-button',
		'jquery-ui-datepicker',
		'eo_fullcalendar'	
	),$version,true);
	
	/* Add js variables to frontend script */
	wp_localize_script( 'eo_front', 'EOAjaxFront', array(
			'adminajax'=>admin_url( 'admin-ajax.php'),
			'locale'=>array(
				'monthNames'=>array_values($wp_locale->month),
				'monthAbbrev'=>array_values($wp_locale->month_abbrev),
				'dayNames'=>array_values($wp_locale->weekday),
				'dayAbbrev'=>array_values($wp_locale->weekday_abbrev),
				'today'=>__('today','eventorganiser'),
				'day'=>__('day','eventorganiser'),
				'week'=>__('week','eventorganiser'),
				'month'=>__('month','eventorganiser'),
				'gotodate'=>__('go to date','eventorganiser'),
				'cat'=>__('View all categories','eventorganiser'),
				'venue'=>__('View all venues','eventorganiser'),
				)
			));

	/* Q-Tip */
	wp_register_script( 'eo_qtip2', EVENT_ORGANISER_URL.'js/qtip2.js',array('jquery'),$version,true);

	/* Styles */
	wp_register_style('eo_calendar-style',EVENT_ORGANISER_URL.'css/fullcalendar.css',array(),$version);
	wp_register_style('eo_front',EVENT_ORGANISER_URL.'css/eventorganiser-front-end.css',array('eventorganiser-jquery-ui-style'),$version);
	wp_register_style('eventorganiser-jquery-ui-style',EVENT_ORGANISER_URL.'css/eventorganiser-admin-fresh.css',array(),$version);
}   
add_action('init', 'eventorganiser_register_script');

 /**
 *Register jQuery scripts and CSS files for admin
 *
 * @since 1.0.0
 * @ignore
 * @access private
 */
function eventorganiser_register_scripts(){
	$version = '1.6.1';
	$ext = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) ? '' : '.min';

	/* Google Maps */
	wp_register_script( 'eo_GoogleMap', 'http://maps.googleapis.com/maps/api/js?sensor=true');

	/*  Venue scripts for venue & event edit */
	wp_register_script( 'eo_venue', EVENT_ORGANISER_URL."js/venues{$ext}.js",array(
		'jquery',
		'eo_GoogleMap'
	),$version,true);
	
	/*  Script for event edit page */
	wp_register_script( 'eo_event', EVENT_ORGANISER_URL."js/event{$ext}.js",array(
		'jquery',
		'jquery-ui-datepicker',
		'jquery-ui-autocomplete',
		'jquery-ui-widget',
		'jquery-ui-position'
	),$version,true);	

	/*  Script for admin calendar */
	wp_register_script( 'eo_calendar', EVENT_ORGANISER_URL."js/admin-calendar{$ext}.js",array(
		'eo_fullcalendar',
		'jquery-ui-dialog',
		'jquery-ui-tabs',
		'jquery-ui-position'
	),$version,true);

	/*  Pick and register jQuery UI style */
	$style = ( 'classic' == get_user_option( 'admin_color') ? 'classic' : 'fresh' );
	wp_register_style('eventorganiser-jquery-ui-style',EVENT_ORGANISER_URL."css/eventorganiser-admin-{$style}.css",array(),$version);

	/* Admin styling */
	wp_register_style('eventorganiser-style',EVENT_ORGANISER_URL.'css/eventorganiser-admin-style.css',array('eventorganiser-jquery-ui-style'),$version);
}
add_action('admin_enqueue_scripts', 'eventorganiser_register_scripts',10);


 /**
 * Check the export and event creation (from Calendar view) actions. 
 * These cannot be called later. Most other actions are only called when
 * the appropriate page is loading.
 *
 * @since 1.0.0
 * @ignore
 * @access private
 */
function eventorganiser_admin_init(){
	global $EO_Errors;
	$EO_Errors = new WP_Error();
}
add_action('admin_init','eventorganiser_admin_init',0);
add_action('admin_init', array('Event_Organiser_Im_Export', 'get_object'));

 /**
 * @since 1.0.0
 * @ignore
 * @access private
 */
function eventorganiser_public_export(){
	if( eventorganiser_get_option('feed') ){
		add_feed('eo-events', array('Event_Organiser_Im_Export','get_object'));
	}
}
add_action('init','eventorganiser_public_export');

/**
 * Queues up the javascript / style scripts for Events custom page type 
 * Hooked onto admin_enqueue_scripts
 *
 * @since 1.0.0
 * @ignore
 * @access private
 */
function eventorganiser_add_admin_scripts( $hook ) {
	global $post,$current_screen,$wp_locale;

	if ( $hook == 'post-new.php' || $hook == 'post.php') {
		if( $post->post_type == 'event' ) {     

			wp_enqueue_script('eo_event');
			wp_localize_script( 'eo_event', 'EO_Ajax_Event', array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'startday'=>intval(get_option('start_of_week')),
					'format'=> eventorganiser_get_option('dateformat').'-yy',
					'location'=>get_option('timezone_string'),
					'locale'=>array(
						'monthNames'=>array_values($wp_locale->month),
						'monthAbbrev'=>array_values($wp_locale->month_abbrev),
						'dayAbbrev'=>array_values($wp_locale->weekday_abbrev),
						'weekDay'=>$wp_locale->weekday,
						'hour'=>__('Hour','eventorganiser'),
						'minute'=>__('Minute','eventorganiser'),
						'day'=>__('day','eventorganiser'),
						'days'=>__('days','eventorganiser'),
						'week'=>__('week','eventorganiser'),
						'weeks'=>__('weeks','eventorganiser'),
						'month'=>__('month','eventorganiser'),
						'months'=>__('months','eventorganiser'),
						'year'=>__('year','eventorganiser'),
						'years'=>__('years','eventorganiser'),
						'daySingle'=>__('every day','eventorganiser'),
						'dayPlural'=>__('every %d days','eventorganiser'),
						'weekSingle'=>__('every week on','eventorganiser'),
						'weekPlural'=>__('every %d weeks on','eventorganiser'),
						'monthSingle'=>__('every month on the','eventorganiser'),
						'monthPlural'=>__('every %d months on the','eventorganiser'),
						'yearSingle'=>__('every year on the','eventorganiser'),
						'yearPlural'=>__('every %d years on the','eventorganiser'),
						'summary'=>__('This event will repeat','eventorganiser'),
						'until'=>__('until','eventorganiser'),
						'occurrence'=>array(__('first','eventorganiser'),__('second','eventorganiser'),__('third','eventorganiser'),__('fourth','eventorganiser'),__('last','eventorganiser'))
					)
					));
			wp_enqueue_script('eo_venue');
			wp_enqueue_style('eventorganiser-style');
		}
	}elseif($current_screen->id=='edit-event'){
			wp_enqueue_style('eventorganiser-style');
	}
}
add_action( 'admin_enqueue_scripts', 'eventorganiser_add_admin_scripts', 998, 1 );


/**
 * Perform database and WP version checks. Display appropriate error messages. 
 * Triggered on update.
 *
 * @since 1.4.0
 * @ignore
 * @access private
 */
function eventorganiser_db_checks(){
	global $wpdb;

	//Check tables exist
	$table_errors = array();
	if($wpdb->get_var("show tables like '$wpdb->eo_events'") != $wpdb->eo_events):
		$table_errors[]= $wpdb->eo_events;
	endif;
	if($wpdb->get_var("show tables like '$wpdb->eo_venuemeta'") != $wpdb->eo_venuemeta):
		$table_errors[]=$wpdb->eo_venuemeta;
	endif;

	if(!empty($table_errors)):?>
		<div class="error"	>
		<p>There has been an error with Event Organiser. One or more tables are missing:</p>
		<ul>
		<?php foreach($table_errors as $table):
			echo "<li>".$table."</li>";
		endforeach; ?>
		</ul>
		<p>Please try re-installing the plugin.</p>
		</div>
	<?php	endif;

	//Check WordPress version
	if(get_bloginfo('version')<'3.3'):?>
		<div class="error"	>
			<p>Event Organiser requires <strong>WordPress 3.3</strong> to function properly. Your version is <?php echo get_bloginfo('version'); ?>. </p>
		</div>
	<?php endif; 
}


/**
 * Displays any errors or notices in the global $EO_Errors
 *
 * @since 1.4.0
 * @ignore
 * @access private
 */
function eventorganiser_admin_notices(){
	global $EO_Errors;
	$errors=array();
	$notices=array();
	if(isset($EO_Errors)):
		$errors = $EO_Errors->get_error_messages('eo_error');
		$notices= $EO_Errors->get_error_messages('eo_notice');
		if(!empty($errors)):?>
			<div class="error"	>
			<?php foreach ($errors as $error):?>
				<p><?php echo $error;?></p>
			<?php endforeach;?>
			</div>
		<?php endif;?>
		<?php if(!empty($notices)):?>
			<div class="updated">
			<?php foreach ($notices as $notice):?>
				<p><?php echo $notice;?></p>
			<?php endforeach;?>
			</div>
		<?php	endif;
	endif;
}
add_action('admin_notices','eventorganiser_admin_notices');


 /**
 * Adds link to the plug-in settings on the settings page
 *
 * @since 1.5
 * @ignore
 * @access private
 */
function eventorganiser_plugin_settings_link($links, $file) {
    
	if( $file == 'event-organiser/event-organiser.php' ) {
		/* Insert the link at the end*/
		$links['settings'] = sprintf('<a href="%s"> %s </a>',
			admin_url('options-general.php?page=event-settings'),
			__('Settings','eventorganiser')
                );
        }
	return $links;
}
add_filter('plugin_action_links', 'eventorganiser_plugin_settings_link', 10, 2);    

/**
 * Schedules cron job for automatically deleting expired events
 *
 * @since 1.4.0
 * @ignore
 * @access private
 */
function eventorganiser_cron_jobs(){
	wp_schedule_event(time()+60, 'daily', 'eventorganiser_delete_expired');
}


/**
 * Clears the 'delete expired events' cron job.
 *
 * @since 1.4.0
 * @ignore
 * @access private
 */
function eventorganiser_clear_cron_jobs(){
	wp_clear_scheduled_hook('eventorganiser_delete_expired');
}


/**
 * Callback for the delete expired events cron job. Deletes events that finished at least 24 hours ago.
 * For recurring events it is only deleted once the last occurrence has expired.
 *
 * @since 1.4.0
 * @ignore
 * @access private
 */
function eventorganiser_delete_expired_events(){
	//Get expired events
	$events = eo_get_events(array('showrepeats'=>0,'showpastevents'=>1,'eo_interval'=>'expired'));
	if($events):
		foreach($events as $event):
			$now = new DateTime('now', eo_get_blog_timezone());
			$start = new DateTime($event->StartDate.' '.$event->StartTime, eo_get_blog_timezone());
			$end = new DateTime($event->EndDate.' '.$event->FinishTime, eo_get_blog_timezone());
			$expired = round(abs($end->format('U')-$start->format('U'))) + 24*60*60; //Duration + 24 hours
			$finished =  new DateTime($event->reoccurrence_end.' '.$event->StartTime, eo_get_blog_timezone());
			$finished->modify("+$expired seconds");

			//Delete if 24 hours has passed
			if($finished <= $now):
				wp_trash_post((int) $event->ID);
			endif;
		endforeach;
	endif;
}
add_action('eventorganiser_delete_expired', 'eventorganiser_delete_expired_events');



/**
 *  Adds retina support for the screen icon
 * Thanks to numeeja (http://cubecolour.co.uk/)
 *
 * @since 1.5.0
 * @ignore
 * @access private
 */
function eventorganiser_screen_retina_icon(){

	$screen_id = get_current_screen()->id;

	if( !in_array($screen_id, array('event','edit-event','edit-event-tag','edit-event-category','event_page_venues','event_page_calendar')) )
		return;

	$icon_url = EVENT_ORGANISER_URL.'css/images/eoicon-64.png'
	?>
	<style>
	@media screen and (-webkit-min-device-pixel-ratio: 2) {
		#icon-edit.icon32 {
			background: url(<?php echo $icon_url;?>) no-repeat;
			background-size: 32px 32px;
			height: 32px;
			width: 32px;
		}
	}
	</style>
	<?php
}
add_action('admin_print_styles','eventorganiser_screen_retina_icon');


/** 
 * Purge the occurrences cache
 * Hooked onto eventorganiser_save_event and eventorganiser_delete_event
 *
 *@access private
 * @ignore
 *@since 1.6
 */
function _eventorganiser_delete_occurrences_cache($post_id=0){
	wp_cache_delete( 'eventorganiser_occurrences_'.$post_id );
}
//The following need to trigger the cache clear clearly need to trigger a cache clear
$hooks = array('eventorganiser_save_event', 'eventorganiser_delete_event');
foreach( $hooks as $hook ){
	add_action($hook, '_eventorganiser_delete_occurrences_cache');
}


/**
 * Purge the cached results of get_calendar.
 * Hooked onto eventorganiser_save_event, eventorganiser_delete_event, wp_trash_post,
 * update_option_gmt_offset,update_option_start_of_week,update_option_rewrite_rules
 * and edited_event-category.
 *
 *@access private
 * @ignore
 *@since 1.5
 */
function _eventorganiser_delete_calendar_cache() {
	delete_transient( 'eo_widget_calendar' );
	delete_transient('eo_full_calendar_public');
	delete_transient('eo_full_calendar_admin');
	delete_transient('eo_widget_agenda');
}

//The following need to trigger the cache
$hooks = array(
	'eventorganiser_save_event', 'eventorganiser_delete_event', 'wp_trash_post','update_option_gmt_offset', /* obvious */
	'update_option_start_of_week', /* Start of week is used for calendars */
	'update_option_rewrite_rules', /* If permalinks updated - links on fullcalendar might now be invalid */ 
	'edited_event-category', /* Colours of events may change */
);
foreach( $hooks as $hook ){
	add_action($hook, '_eventorganiser_delete_calendar_cache');
}


/**
 * Handles admin pointers
 *
 * Kick starts the enquing. Rename this to something unique (i.e. include your plugin/theme name).
 *
 *@access private
 *@ignore
 *@since 1.5
 */
function eventorganiser_pointer_load( $hook_suffix ) {

		$screen_id = get_current_screen()->id;

		//Get pointers for this screen
		$pointers = apply_filters('eventorganiser_admin_pointers-'.$screen_id, array());

		if( !$pointers || !is_array($pointers) )
			return;

		// Get dismissed pointers
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$valid_pointers =array();

		//Check pointers and remove dismissed ones.
		foreach ($pointers as $pointer_id => $pointer ){

			//Sanity check
			if ( in_array( $pointer_id, $dismissed ) || empty($pointer)  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
				continue;

			 $pointer['pointer_id'] = $pointer_id;

			//Add the pointer to $valid_pointers array
			$valid_pointers['pointers'][] =  $pointer;
		}

		//No valid pointers? Stop here.
		if( empty($valid_pointers) )	
			return;

		// Add pointers style to queue. 
		wp_enqueue_style( 'wp-pointer' );
		
		// Add pointers script to queue. Add custom script.
		wp_enqueue_script('eventorganiser-pointer',EVENT_ORGANISER_URL.'js/eventorganiser-pointer.js',array('wp-pointer','eo_event'));

		// Add pointer options to script. 
		wp_localize_script('eventorganiser-pointer', 'eventorganiserPointer', $valid_pointers);
}
add_action( 'admin_enqueue_scripts', 'eventorganiser_pointer_load',99999);


/**
 * Adds pointer for 1.5
 *
 *@access private
 *@ignore
 *@since 1.5
 */
function eventorganiser_occurrencepicker_pointer( $p ){
	$p['occpicker150'] =array(	
		'target' =>'.eo_occurrence_toogle',
		'options'=>array(
					'content'  => sprintf('<h3> %s </h3> <p> %s </p>',
							__( 'New Feature: Add / Remove Dates' ,'eventorganiser'),
							 __( 'This link reveals a datepicker which highlights the dates on which the event occurs. Click a date to add or remove it from the event\'s schedule.','eventorganiser')
							),
					'position' => array('edge' => 'left', 'align' => 'middle'),
					)
	); 
	return $p;
}
add_filter('eventorganiser_admin_pointers-event','eventorganiser_occurrencepicker_pointer');
 ?>
