<?php 
global $event; 
global $detail;
	
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
$full = ($limit < $attendees);
$spots = $full ? 0 : $limit - $attendees;
?>

<h3 class="event-title"><a href="<?php echo esc_url($event->link); ?>"><?php echo strip_tags($event->name); ?></a></h3>
<?php if ( ! empty( $date ) ): ?>
<p class="event-date"><span class="event-what">Date:</span> <?php echo $date; ?></p>
<p class="event-time"><span class="event-what">Times:</span> <?php echo $timefrom; ?> - <?php echo $timeto; ?></p>
<?php endif; ?>
<p class="event-summary"><?php echo wp_trim_words( strip_tags( $event->description ), 14 ); ?> <a href="<?php echo esc_url($event->link); ?>">read more</a></p>

<p class='event-location'><span class='event-what'>Location:</span> <?php echo $venue; ?></p>

<?php if ( isset ( $detail )) {?>

<?php
if ( isset( $event->venue ) && ( $event->venue->name == 'Life Drawing Montmartre' || $event->venue->name == 'Untitled Factory' ) ) {
	echo "<a href='https://maps.google.com/maps?q=$venue+%28".$event->venue->name."%29&z=17'><img src='/files/2018/08/googlemap.png'></a>";
	if ( ! empty($event->how_to_find_us) ) { ?>
		<p class="event-findus"><?php echo $event->how_to_find_us; ?></p>
<?php 	}
}
?>

<?php } ?>

<p class="event-cost"><span class="event-what">Cost:</span> <?php echo $fee ?></span>
<p class="event-rsvp"><span class="event-what">Book Now:</span> 
<?php if ( $full ): ?>
	<span>Full</span>
<?php else: ?>
	<span class="rsvp-add"><?php echo sprintf(_n('%s spot', '%s spots', $spots), $spots) . ' left' ; ?>&nbsp;&nbsp;<a href="<?php echo $event->link ?>">RSVP via Meetup</a></span><?php endif; ?>
</p>

