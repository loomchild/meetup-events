<?php 
global $event; 
global $options; 
global $params; 
	
$date = date( 'F d, Y', intval( $event->time/1000 + $event->utc_offset/1000 ) );
$timefrom = date( 'g:i a', intval( $event->time/1000 + $event->utc_offset/1000 ) );
$timeto = date( 'g:i a', intval( $event->time/1000 + $event->duration/1000 + $event->utc_offset/1000 ) );

if ( isset ( $event->fee ) ) {
	$fee = $event->fee->amount . '€';
} else {
	$fee = 'free';
}

$venue = $event->venue->name.', '.$event->venue->address_1 . ', ' . $event->venue->city;

$attendees = absint( $event->yes_rsvp_count );
$limit = absint( $event->rsvp_limit );
$full = ($attendees == $limit);
$spots = $limit - $attendees;
?>

<h3 class="event-title"><a href="<?php echo esc_url($event->event_url); ?>"><?php echo strip_tags($event->name); ?></a></h3>
<?php if ( ! empty( $date ) ): ?>
<p class="event-date"><span class="event-what">Date:</span> <?php echo $date; ?></p>
<p class="event-time"><span class="event-what">Times:</span> <?php echo $timefrom; ?> - <?php echo $timeto; ?></p>
<?php endif; ?>
<p class="event-summary"><?php echo wp_trim_words( strip_tags( $event->description ), 14 ); ?> <a href="<?php echo esc_url($event->event_url); ?>">read more</a></p>

<p class='event-location'><span class='event-what'>Location:</span> <?php echo $venue; ?></p>

<?php if ( isset ( $params['detail'] )) {?>

<?php
if ( isset( $event->venue ) ) {
	echo "<a href='http://maps.google.com/maps?q=$venue+%28".$event->venue->name."%29&z=17'><img src='http://maps.googleapis.com/maps/api/staticmap?center=$venue&zoom=17&scale=false&size=300x300&maptype=roadmap&format=png&visual_refresh=true&markers=size:large%7Ccolor:0xff0000%7C$venue'></a>";
	if ( ! empty($event->how_to_find_us) ) { ?>
<p class="event-findus"><?php echo $event->how_to_find_us; ?></p>
<?php 	}
} else {
	$venue = apply_filters( 'vsm_no_location_text', "Location: TBA" );
	if ( ! empty( $venue ) ){
		echo "<p class='event-location'>$venue</p>";
	}
}?>

<?php } ?>

<p class="event-cost"><span class="event-what">Cost:</span> <?php echo $fee ?></span>
<p class="event-rsvp"><span class="event-what">Book Now:</span> 
<?php if ( $full ): ?>
	<span>Full</span>
<?php else: ?>
	<span class="rsvp-add"><?php echo sprintf(_n('%s spot', '%s spots', $spots), $spots) . ' left' ; ?>&nbsp;&nbsp;<a href="'.$event->event_url.'">RSVP via Meetup</a> or <a href="mailto:contact@lifedrawingmontmartre.com?subject=RSVP">Email Us</a></span><?php endif; ?>
</p>

