<?php
/**
 * Event related functions
 *
 * @package event-functions
*/

/**
* Retrieve list of event matching criteria.
*
* The defaults are as follows:
*
* * 'event_start_before' - default: null
* * 'event_end_before' - default: null
* * 'event_start_after' - default: null
* * 'event_end_after' - This argument, and the above can take a date in 'Y-m-d' format or {@link http://wp-event-organiser.com/documentation/relative-date-formats/ relative dates}. Default: null
* * 'numberposts' - default is - 1 (all events)
* * 'orderby' - default is 'eventstart'
* * 'showpastevents' - default is true (it's recommended to use `event_start_after=today` or `event_end_after=today` instead) 
*
* Other defaults are set by WordPress with {@link http://codex.wordpress.org/Template_Tags/get_posts `get_posts()`}. The function sets the following parameters
*
* * 'post_type' - is set to 'event'
* * 'suppress_filters' - is set to false
*
* ###Example
*
*     $events = eo_get_events(array(
*            'numberposts'=>5,
*            'events_start_after'=>'today',
*            'showpastevents'=>true,//Will be deprecated, but set it to true to play it safe.
*       ));
*
*     <?php if($events):
*        echo '<ul>'; 
*        foreach ($events as $event):
*                //Check if all day, set format accordingly
*                $format = ( eo_is_all_day($event->ID) ? get_option('date_format') ? get_option('date_format').' '.get_option('time_format') );
*                printf('<li><a href="%s"> %s </a> on %s </li>',
*                                   get_permalink($event->ID),
*                                   get_the_title($event->ID),
*                                   eo_get_the_start($format, $event->ID,null,$event->occurrence_id)
*                               );                           
*        endforeach; 
*         echo '</ul>'; 
*     endif; ?>
*
* @since 1.0.0
* @uses get_posts()
* @link https://gist.github.com/4165380 List up-coming events
 *@link https://gist.github.com/4190351 Adds up-coming events in the venue tooltip
 *@link http://wp-event-organiser.com/documentation/relative-date-formats/ Using relative dates in event queries
* @param array $args Event query arguments.
* @return array An array of event (post) objects. Like get_posts. In case of failure it returns null.
*/
function eo_get_events($args=array()){

	//In case an empty string is passed
	if(empty($args))
		$args = array();

	//These are preset to ensure the plugin functions properly
	$required = array('post_type'=> 'event','suppress_filters'=>false);

	//These are the defaults
	$defaults = array(
		'numberposts'=>-1,
		'orderby'=> 'eventstart',
		'order'=> 'ASC',
		'showrepeats'=>1,
		'group_events_by'=>'',
		'showpastevents'=>true); 	
	
	//Construct the query array	
	$query_array = array_merge($defaults,$args,$required);

	if(!empty($query_array['venue'])){	
		$query_array['event-venue'] = $query_array['venue'];
		unset($query_array['venue']);
	}
	
	//Ensure all date queries are yyyy-mm-dd format. Process relative strings ('today','tomorrow','+1 week')
	$dates = array('ondate','event_start_after','event_start_before','event_end_before','event_end_after');
	foreach($dates as $prop):
		if(!empty($query_array[$prop]))
			$query_array[$prop] = eo_format_date($query_array[$prop],'Y-m-d');
	endforeach;
	
	//Make sure 'false' is passed as integer 0
	if(strtolower($query_array['showpastevents'])==='false') $query_array['showpastevents']=0;

	if($query_array){
		$events=get_posts($query_array);
		return $events;
	}

		return null;
}


/**
* Retrieve a row object from events table of the event by ID of the event
* @access private
* @since 1.0
*
* @param int $post_id Post ID of the event.
* @param int $occurrence The occurrence number. Deprecated use $occurrence_id
* @param int $occurrence_id The occurrence ID
* @return row object of event's row in Events table
*/
function eo_get_by_postid($post_id,$occurrence=0, $occurrence_id=0){
	global $wpdb;

	if( !empty($occurrence_id) ){
		$column = 'event_id';
		$value = $occurrence_id;
	}else{
		//Backwards compatibility!
		$column = 'event_occurrence';
		$value = $occurrence;
	}

	$querystr = $wpdb->prepare("
		SELECT StartDate,EndDate,StartTime,FinishTime FROM  {$wpdb->eo_events} 
		WHERE {$wpdb->eo_events}.post_id=%d
		 AND ({$wpdb->eo_events}.{$column}=%d)
		LIMIT 1",$post_id,$value);

	return $wpdb->get_row($querystr);
}


/**
* Returns the start date of occurrence of event.
* If used inside the loop, with no id no set, returns start date of
* current event occurrence.
* @since 1.0.0
*
* @param string $format String of format as accepted by PHP date
* @param int $post_id Post ID of the event
* @param int $occurrence The occurrence number. Deprecated. Use $occurrence_id
* @param int $occurrence_id  The occurrence ID
* @return string the start date formated to given format, as accepted by PHP date
 */
function eo_get_the_start($format='d-m-Y',$post_id=0,$occurrence=0, $occurrence_id=0){
	global $post;
	$event = $post;

	if( !empty($occurrence) ){
		_deprecated_argument( __FUNCTION__, '1.5.6', 'Third argument is depreciated. Please use a fourth argument - occurrence ID. Available from $post->event_id' );

		//Backwards compatiblity
		if( !empty($post_id) ) $event = eo_get_by_postid($post_id,$occurrence, $occurrence_id);	
	
		if(empty($event)) 
			return false;
	
		$date = trim($event->StartDate).' '.trim($event->StartTime);

		if(empty($date)||$date==" ")
			return false;

		return eo_format_date($date,$format);
	}

	$occurrence_id = (int) ( empty($occurrence_id) && isset($event->occurrence_id)  ? $event->occurrence_id : $occurrence_id);

	$occurrences = eo_get_the_occurrences_of($post_id);

	if( !$occurrences || !isset($occurrences[$occurrence_id]) )
		return false;

	$start = $occurrences[$occurrence_id]['start'];

	return eo_format_datetime($start,$format);
}

/**
* Returns the start date of occurrence of event an event, like eo_get_the_start().
* The difference is that the occurrence ID *must* be supplied (event ID is not). 
* @since 1.6
*
* @param string $format String of format as accepted by PHP date
* @param int $occurrence_id  The occurrence ID
* @return string the start date formated to given format, as accepted by PHP date
 */
function eo_get_the_occurrence_start($format='d-m-Y',$occurrence_id){
	global $wpdb;

	$querystr = $wpdb->prepare("
		SELECT StartDate,StartTime FROM  {$wpdb->eo_events} 
		WHERE {$wpdb->eo_events}.event_id=%d
		LIMIT 1",$occurrence_id);

	$occurrence =  $wpdb->get_row($querystr);

	if( !$occurrence )
		return false;

	$date = trim($occurrence->StartDate).' '.trim($occurrence->StartTime);

	if(empty($date)||$date==" ")
		return false;

	return eo_format_date($date,$format);
}

/**
* Echos the start date of occurence of event
 * @since 1.0.0
 * @uses eo_get_the_start()
 *
* @param string $format String of format as accepted by PHP date
* @param int $post_id Post ID of the event
* @param int $occurrence The occurrence number. Deprecated. Use $occurrence_id instead
* @param int $occurrence_id  The occurrence ID
 */
function eo_the_start($format='d-m-Y',$post_id=0,$occurrence=0,	$occurrence_id=0){
	echo eo_get_the_start($format,$post_id,$occurrence, $occurrence_id);
}


/**
* Returns the end date of occurrence of event. 
* If used inside the loop, with no id no set, returns end date of
* current event occurrence.
 * @since 1.0.0
*
* @param string $format String of format as accepted by PHP date
* @param int $post_id The event (post) ID. Uses current event if empty.
* @param int $occurrence The occurrence number. Deprecated. Use $occurrence_id instead
* @param int $occurrence_id  The occurrence ID
* @return string the end date formated to given format, as accepted by PHP date
 */
function eo_get_the_end($format='d-m-Y',$post_id=0,$occurrence=0, $occurrence_id=0){
	global $post;
	$event = $post;

	if( !empty($occurrence) ){
		_deprecated_argument( __FUNCTION__, '1.5.6', 'Third argument is depreciated. Please use a fourth argument - occurrence ID. Available from $post->event_id' );

		//Backwards compatiblity
		if( !empty($post_id) ) $event = eo_get_by_postid($post_id,$occurrence, $occurrence_id);	
	
		if(empty($event)) 
			return false;
	
		$date = trim($event->EndDate).' '.trim($event->FinishTime);

		if(empty($date)||$date==" ")
			return false;

		return eo_format_date($date,$format);
	}
	$occurrence_id = (int) ( empty($occurrence_id) && isset($event->occurrence_id)  ? $event->occurrence_id : $occurrence_id);

	$occurrences = eo_get_the_occurrences_of($post_id);

	if( !$occurrences || !isset($occurrences[$occurrence_id]) )
		return false;

	$end = $occurrences[$occurrence_id]['end'];

	return eo_format_datetime($end,$format);
}

/**
* Echos the end date of occurence of event
 * @since 1.0.0
 * @uses eo_get_the_end()
 *
* @param string $format String of format as accepted by PHP date
* @param int $post_id Post ID of the event
* @param int $occurrence The occurrence number. Deprecated. Use $occurrence_id instead
* @param int $occurrence_id  The occurrence ID
 */
function eo_the_end($format='d-m-Y',$post_id=0,$occurrence=0, $occurrence_id=0){
	echo eo_get_the_end($format,$post_id,$occurrence, $occurrence_id);
}


/**
* Gets the formated date of next occurrence of an event
* @since 1.0.0
*
* @param string $format The format to use, using PHP Date format
* @param int $post_id The event (post) ID, 
* @return string The formatted date or false if no date exists
 */
function eo_get_next_occurrence($format='d-m-Y',$post_id=0){
	$next_occurrence = eo_get_next_occurrence_of($post_id);

	if( !$next_occurrence )
		return false;

	return eo_format_datetime($next_occurrence['start'],$format);
}

/**
* Returns an array of datetimes (start and end) corresponding to the next occurrence of an event
* eo_get_next_occurrence() on the other hand returns a formated datetime of the start date.
* @since 1.6
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return array Array with keys 'start' and 'end', with corresponding datetime objects
 */
function eo_get_next_occurrence_of($post_id=0){
	global $wpdb;
	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);
	
	//Retrieve the blog's local time and create the date part
	$blog_now = new DateTime(null, eo_get_blog_timezone() );
	$now_date =$blog_now->format('Y-m-d');
	$now_time =$blog_now->format('H:i:s');
	
	$nextoccurrence  = $wpdb->get_row($wpdb->prepare("
		SELECT StartDate, StartTime, EndDate, FinishTime
		FROM  {$wpdb->eo_events}
		WHERE {$wpdb->eo_events}.post_id=%d
		AND ( 
			({$wpdb->eo_events}.StartDate > %s) OR
			({$wpdb->eo_events}.StartDate = %s AND {$wpdb->eo_events}.StartTime >= %s))
		ORDER BY {$wpdb->eo_events}.StartDate ASC
		LIMIT 1",$post_id,$now_date,$now_date,$now_time));

	if( ! $nextoccurrence )
		return false;

	$start = new DateTime($nextoccurrence->StartDate.' '.$nextoccurrence->StartTime);
	$end = new DateTime($nextoccurrence->EndDate.' '.$nextoccurrence->FinishTime);

	return compact('start','end');
}

/**
* Prints the formated date of next occurrence of an event
* @since 1.0.0
* @uses eo_get_next_occurence()
*
* @param string $format The format to use, using PHP Date format
* @param int $post_id The event (post) ID. Uses current event if empty. 
 */
function eo_next_occurence($format='',$post_id=0){
	echo eo_get_next_occurence($format,$post_id);
}


/**
* Return true is the event is an all day event.
 * @since 1.2
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return bool True if event runs all day, or false otherwise
 */
function eo_is_all_day($post_id=0){
	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);

	if( empty($post_id) ) 
		return false;

	$schedule = eo_get_event_schedule($post_id);

	return (bool) $schedule['all_day'];
}

/**
* Returns the formated date of first occurrence of an event
* @since 1.0.0
*
* @param string $format the format to use, using PHP Date format
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return string The formatted date
 */
function eo_get_schedule_start($format='d-m-Y',$post_id=0){
	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);
	$schedule = eo_get_event_schedule($post_id);
	return eo_format_datetime($schedule['schedule_start'],$format);
}

/**
* Prints the formated date of first occurrence of an event
* @since 1.0.0
* @uses eo_get_schedule_start()
*
* @param string $format The format to use, using PHP Date format
* @param int $post_id The event (post) ID. Uses current event if empty.
 */
function eo_schedule_start($format='d-m-Y',$post_id=0){
	echo eo_get_schedule_start($format,$post_id);
}


/**
* Returns the formated date of the last occurrence of an event
 * @since 1.4.0
*
* @param string $format The format to use, using PHP Date format
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return string The formatted date 
 */
function eo_get_schedule_last($format='d-m-Y',$post_id=0){
	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);
	$schedule = eo_get_event_schedule($post_id);
	return eo_format_datetime($schedule['schedule_last'],$format);
}

/**
* Prints the formated date of the last occurrence of an event
 * @since 1.4.0
* @uses eo_get_schedule_last()
*
* @param string $format The format to use, using PHP Date format
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return string The formatted date 
 */
function eo_schedule_last($format='d-m-Y',$post_id=0){
	echo eo_get_schedule_last($format,$post_id);
}


/**
* Returns true if event reoccurs or false if it is a one time event.
* @since 1.0.0
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return bool true if event a reoccurring event
*/
function eo_reoccurs($post_id=0){
	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);

	if( empty($post_id) ) 
		return false;

	$schedule = eo_get_event_schedule($post_id);
	
	return ($schedule['schedule'] != 'once');
}


/**
* Returns a summary of the events schedule.
 * @since 1.0.0
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return string A summary of the events schedule.
 */
function eo_get_schedule_summary($post_id=0){
	global $post,$wp_locale;

	$ical2day = array('SU'=>	$wp_locale->weekday[0],'MO'=>$wp_locale->weekday[1],'TU'=>$wp_locale->weekday[2], 'WE'=>$wp_locale->weekday[3], 
						'TH'=>$wp_locale->weekday[4],'FR'=>$wp_locale->weekday[5],'SA'=>$wp_locale->weekday[6]);

	$nth= array(__('last','eventorganiser'),'',__('first','eventorganiser'),__('second','eventorganiser'),__('third','eventorganiser'),__('fourth','eventorganiser'));

	$reoccur = eo_get_event_schedule($post_id);

	if(empty($reoccur))
		return false;

	$return='';

	if($reoccur['schedule']=='once'){
		$return = __('one time only','eventorganiser');

	}elseif($reoccur['schedule']=='custom'){
		$return = __('custom reoccurrence','eventorganiser');

	}else{
		switch($reoccur['schedule']):

			case 'daily':
				if($reoccur['frequency']==1):
					$return .=__('every day','eventorganiser');
				else:
					$return .=sprintf(__('every %d days','eventorganiser'),$reoccur['frequency']);
				endif;
				break;

			case 'weekly':
				if($reoccur['frequency']==1):
					$return .=__('every week on','eventorganiser');
				else:
					$return .=sprintf(__('every %d weeks on','eventorganiser'),$reoccur['frequency']);
				endif;

				foreach( $reoccur['schedule_meta'] as $ical_day){
					$days[] =  $ical2day[$ical_day];
					}
				$return .=' '.implode(', ',$days);
				break;

			case 'monthly':
				if($reoccur['frequency']==1):
					$return .=__('every month on the','eventorganiser');
				else:
					$return .=sprintf(__('every %d months on the','eventorganiser'),$reoccur['frequency']);
				endif;
				$return .= ' ';
				$bymonthday =preg_match('/^BYMONTHDAY=(\d{1,2})/' ,$reoccur['schedule_meta'],$matches);

				if( $bymonthday  ){
					$d = intval($matches[1]);
					$m =intval($reoccur['schedule_start']->format('n'));
					$y =intval($reoccur['schedule_start']->format('Y'));
					$reoccur['start']->setDate($y,$m,$d);
					$return .= $reoccur['schedule_start']->format('jS');

				}elseif($reoccur['schedule_meta']=='date'){
					$return .= $reoccur['schedule_start']->format('jS');

				}else{
					$byday = preg_match('/^BYDAY=(-?\d{1,2})([a-zA-Z]{2})/' ,$reoccur['schedule_meta'],$matches);
					if($byday):
						$n=intval($matches[1])+1;
						$return .=$nth[$n].' '.$ical2day[$matches[2]];
					else:
						var_dump($reoccur['schedule_meta']);
						$bydayOLD = preg_match('/^(-?\d{1,2})([a-zA-Z]{2})/' ,$reoccur['schedule_meta'],$matchesOLD);
						$n=intval($matchesOLD[1])+1;
						$return .=$nth[$n].' '.$ical2day[$matchesOLD[2]];
					endif;
				}
				break;
			case 'yearly':
				if($reoccur['frequency']==1):
					$return .=__('every year','eventorganiser');
				else:
					$return .=sprintf(__('every %d years','eventorganiser'),$reoccur['frequency']);
				endif;
				break;

		endswitch;
		$return .= ' '.__('until','eventorganiser').' '. eo_format_datetime($reoccur['schedule_last'],'M, jS Y');
	}
	
	return $return; 
}

/**
* Prints a summary of the events schedule.
* @since 1.0.0
* @uses eo_get_schedule_summary()
*
* @param int $post_id The event (post) ID. Uses current event if empty.
 */
function eo_display_reoccurence($post_id=0){
	echo eo_get_schedule_summary($post_id);
}


/** 
* Returns an array of occurrences. Each occurrence is an array with 'start' and 'end' key. 
*  Both of these hold a DateTime object (for the start and end of that occurrence respecitvely).
* @since 1.5
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return array Array of arrays of DateTime objects of the start and end date-times of occurences. False if none exist.
 */
function eo_get_the_occurrences_of($post_id=0){
	global $wpdb;

	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);

	if(empty($post_id)) 
		return false;

	$occurrences = wp_cache_get( 'eventorganiser_occurrences_'.$post_id );
	if( !$occurrences ){

		$results = $wpdb->get_results($wpdb->prepare("
			SELECT event_id, StartDate,StartTime,EndDate,FinishTime FROM {$wpdb->eo_events} 
			WHERE {$wpdb->eo_events}.post_id=%d ORDER BY StartDate ASC",$post_id));
	
		if( !$results )
			return false;

		$occurrences=array();
		foreach($results as $row):
			$occurrences[$row->event_id] = array(
				'start' => new DateTime($row->StartDate.' '.$row->StartTime, eo_get_blog_timezone()),
				'end' => new DateTime($row->EndDate.' '.$row->FinishTime, eo_get_blog_timezone())
			);
		endforeach;
		wp_cache_set( 'eventorganiser_occurrences_'.$post_id, $occurrences );
	}

	return $occurrences;
}

/**
 * Returns the colour of a category
 * @ignore
 * @access private
 */
function eo_get_category_color($term){
	return eo_get_category_meta($term,'color');
}

/**
* Returns the colour of a category associated with the event.
* Applies the 'eventorganiser_event_color' filter.
* @since 1.6
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return string The colour of the category in HEX format
 */
function eo_get_event_color($post_id=0){
	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id);

	if( empty($post_id) )
		return false;

	$color = false;

	$terms = get_the_terms($post_id, 'event-category');
	if( $terms && !is_wp_error($terms) ){
		foreach ($terms as $term):	
			if( ! empty($term->color) ){
				$color_code = ltrim($term->color, '#');
				if ( ctype_xdigit($color_code) && (strlen($color_code) == 6 || strlen($color_code) == 3)){
					$color = '#'.$color_code;
					break;
                       		}
			}
		endforeach;
	}

	return apply_filters('eventorganiser_event_color',$color,$post_id);
}

/**
* Returns an array of classes associated with an event. 
* Adds eo-event-venue-[venue slug] for the event's venue.
* Adds eo-event-cat-[category slug] for each event category it bleongs to. 
* Adds eo-event-[future|past|running].
* Applies filter eventorganiser_event_classes
* @since 1.6
*
* @param int $post_id The event (post) ID. Uses current event if empty.
* @param int $occurrence_id The occurrence ID. Uses current event if empty.
* @return array Array of classes
 */
function eo_get_event_classes($post_id=0, $occurrence_id=0){
	global $post;

	$post_id = (int) ( empty($post_id) ? get_the_ID() : $post_id );
	$occurrence_id = (int) ( empty($occurrence_id) && isset($post->occurrence_id)  ? $post->occurrence_id : $occurrence_id );

	$event_classes = array();
			
	//Add venue class
	if( eo_get_venue_slug() )
		$event_classes[] = 'eo-event-venue-'.eo_get_venue_slug();

	//Add category classes
	$cats= get_the_terms(get_the_ID(), 'event-category');
	if( $cats && !is_wp_error($cats) ){	
		foreach ($cats as $cat)
			$event_classes[] = 'eo-event-cat-'.$cat->slug;
	}

	//Add 'time' class
	$start = eo_get_the_start(DATETIMEOBJ, $post_id, null, $occurrence_id);
	$end= eo_get_the_end(DATETIMEOBJ, $post_id, null, $occurrence_id);
	$now = new DateTime('now',eo_get_blog_timezone());
	if( $start > $now ){
		$event_classes[] = 'eo-event-future';
	}elseif( $end < $now ){
		$event_classes[] = 'eo-event-past';
	}else{
		$event_classes[] = 'eo-event-running';
	}

	return  apply_filters('eventorganiser_event_classes', array_unique($event_classes), $post_id, $occurrence_id);
}


/**
* Checks if an event taxonomy archive page is being displayed. A simple wrapper for is_tax().
* @since 1.6
*
* @return bool True if an event category, tag or venue archive page is being displayed. False otherwise.
 */
function eo_is_event_taxonomy(){
	return (is_tax(array('event-category','event-tag','event-venue')));
}

/**
* Retrieves the permalink for the ICAL event feed. A simple wrapper for get_feed_link().
*
* Retrieve the permalink for the events feed. The returned link is the url with which visitors can subscribe 
* to your events. Visiting the url directly will prompt a download an ICAL file of your events. The events feed 
* includes only **public** events (draft, private and trashed events are not included).
*
* @since 1.6
*
* @return string The link to the ICAL event feed..
 */
function eo_get_events_feed(){
	return get_feed_link('eo-events');
}


/**
 * Returns a the url which adds a particular occurrence of an event to
 * a google calendar. Must be used inside the loop
 *
 *Returns an url which adds a particular occurrence of an event to a Google calendar. This function can only be used inside the loop. 
 * An entire series cannot be added to a Google calendar - however users can subscribe to your events. Please note that, unlike 
 * subscribing to events, changes made to an event will not be reflected on an event added to the Google calendar.
 *
 * @since 1.2.0
 *
 * @return string Url which adds event to a google calendar
 */
function eo_get_the_GoogleLink(){
	global $post;
	setup_postdata($post);

	if(empty($post)|| get_post_type($post )!='event'){ 
		wp_reset_postdata();
		return false;
	}

	$start = eo_get_the_start(DATETIMEOBJ); 
	$end = eo_get_the_start(DATETIMEOBJ); 

	if(eo_is_all_day()):
		$end->modify('+1 second');
		$format = 'Ymd';
	else:		
		$format = 'Ymd\THis\Z';
		$start->setTimezone( new DateTimeZone('UTC') );
		$end->setTimezone( new DateTimeZone('UTC') );
	endif;

	$excerpt = apply_filters('the_excerpt_rss', get_the_excerpt());

	$url = add_query_arg(array(
		'text'=>get_the_title(), 
		'dates'=>$start->format($format).'/'.$end->format($format),
		'trp'=>false,
		'details'=> esc_html($excerpt),
		'sprop'=>get_bloginfo('name')
	),'http://www.google.com/calendar/event?action=TEMPLATE');

	$venue_id = eo_get_venue();
	if($venue_id):
		$venue =eo_get_venue_name($venue_id).", ".implode(', ',eo_get_venue_address($venue_id));
		$url = add_query_arg('location',$venue, $url);
	endif;

	wp_reset_postdata();
	return $url;
}



function eo_has_event_started($id='',$occurrence=0){
	$tz = eo_get_blog_timezone();
	$start = new DateTime(eo_get_the_start('d-m-Y H:i',$id,$occurrence), $tz);
	$now = new DateTime('now', $tz);

	return ($start <= $now );
}

function eo_has_event_finished($id='',$occurrence=0){
	$tz = eo_get_blog_timezone();
	$end = new DateTime(eo_get_the_end('d-m-Y H:i',$id,$occurrence), $tz);
	$now = new DateTime('now', $tz);

	return ($end <= $now );
}

function eo_event_category_dropdown( $args = '' ) {
	$defaults = array(
		'show_option_all' => '', 
		'echo' => 1,
		'selected' => 0, 
		'name' => 'event-category', 
		'id' => '',
		'class' => 'postform event-organiser event-category-dropdown event-dropdown', 
		'tab_index' => 0, 
	);

	$defaults['selected'] =  (is_tax('event-category') ? get_query_var('event-category') : 0);
	$r = wp_parse_args( $args, $defaults );
	$r['taxonomy']='event-category';
	extract( $r );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";

	$categories = get_terms($taxonomy, $r ); 
	$name = esc_attr( $name );
	$class = esc_attr( $class );
	$id = $id ? esc_attr( $id ) : $name;

	$output = "<select style='width:150px' name='$name' id='$id' class='$class' $tab_index_attribute>\n";
	
	if ( $show_option_all ) {
		$output .= '<option '.selected($selected,0,false).' value="0">'.$show_option_all.'</option>';
	}

	if ( ! empty( $categories ) ) {
		foreach ($categories as $term):
			$output .= '<option value="'.$term->slug.'"'.selected($selected,$term->slug,false).'>'.$term->name.'</option>';
		endforeach; 
	}
	$output .= "</select>\n";

	if ( $echo )
		echo $output;

	return $output;
}
?>
