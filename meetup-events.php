<?php
/*
 * Plugin Name: Meetup Events
 * Description: Widgets displaying events from Meetup.com
 * Version: 1
 * Author: Jarek Lipski
 * Author URI: http://loomchild.net
 * Plugin URI: http://loomchild.net
 */

class MeetupEvents {
	private $api_url = 'https://api.meetup.com/';

	public function __construct() {
		$options = get_option('vs_meet_options');
		$this->group = $options['meetup_events_group'];

		register_activation_hook( __FILE__, array ($this, 'install' ) );
		add_filter( 'admin_init' , array( $this , 'register_fields' ) );
	}
	
	function register_fields() {
		register_setting( 'general', 'mettup_events_options', array($this, 'validate'));
		add_settings_section('meetup_events', 'Meetup Settings', array($this, 'setting_section_meetup_events'), 'general');
		add_settings_field('meetup_events_group', '<label for="meetup_events_group">Meetup Group:</label>' , array(&$this, 'setting_meetup_events_group') , 'general', 'meetup_events' );
  }

	function setting_section_meetup_events() {
	}

	function setting_meetup_events_group() {
		$options = get_option('meetup_events_options');
		echo "<input id='meetup_events_options' name='meetup_events_options[meetup_events_group]' size='40' type='text' value='{$options['meetup_events_group']}' />";
	}

  public function validate($input) {
    return $input;
	}

	public function get_data(){
    $event_response = wp_remote_get( $this->base_url );
    if( is_wp_error( $event_response )) {
      if ( WP_DEBUG ){
        var_dump($event_response);
      }
      return array();
    }
    $events = json_decode( $event_response['body'] );
		return $events;
  }
}

class VsMeetNextWidget extends WP_Widget {
  function VsMeetNextWidget() {
    parent::WP_Widget(false, $name = 'Meetup Next Event', array('description' => "Display next upcoming event in the group."));	
  }

  function widget($args, $instance) {		
    extract( $args );
    $title = apply_filters('widget_title', $instance['title']);
		$include = $instance['include'];
		$exclude = $instance['exclude'];
		$params = array(
			"detail" => $instance['detail']
		);
    echo $before_widget;
    if ( $title ) echo $before_title . $title . $after_title;
    $vsm = new VsMeetWidget();
    $html = $vsm->get_next_event($params, $include, $exclude);
    echo $html;
    echo $after_widget;
  }

  function update($new_instance, $old_instance) {				
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['include'] = strip_tags($new_instance['include']);
    $instance['exclude'] = strip_tags($new_instance['exclude']);
    $instance['detail'] = $new_instance['detail'];
    
    return $instance;
  }

  function form($instance) {
    if ( $instance ) {
      $title = esc_attr($instance['title']);
      $include = esc_attr($instance['include']);
      $exclude = esc_attr($instance['exclude']);
      $detail = isset($instance['detail']);
    } else {
      $title = '';
      $include = '';
      $exclude = '';
      $detail = false;
    }
    ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>">Title:
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </label></p>
      </label></p>
      <p><label for="<?php echo $this->get_field_id('include'); ?>">Include:
	    <input class="widefat" id="<?php echo $this->get_field_id('include'); ?>" name="<?php echo $this->get_field_name('include'); ?>" type="text" value="<?php echo $include; ?>" />
      </label></p>
      <p><label for="<?php echo $this->get_field_id('exclude'); ?>">Exclude:
	    <input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo $exclude; ?>" />
      </label></p>
      <p><label for="<?php echo $this->get_field_id('detail'); ?>">Show details:
	    <input class="widefat" id="<?php echo $this->get_field_id('detail'); ?>" name="<?php echo $this->get_field_name('detail'); ?>" type="checkbox" value="1" <?php checked($detail); ?> />
      </label></p>
    <?php 
  }
}

class VsMeetListWidget extends WP_Widget {
  function VsMeetListWidget() {
    parent::WP_Widget(false, $name = 'Meetup List Event', array('description' => "Display a list of events."));	
  }

  function widget($args, $instance) {		
    extract( $args );
    $title = apply_filters('widget_title', $instance['title']);
    $limit = intval($instance['limit']); 
    
    echo $before_widget;
    if ( $title ) echo $before_title . $title . $after_title;
  	$meetup_events = new MeetupEvents();
  	$html = $this->get_group_events( $meetup_events, $limit );
   	echo $html;
    echo $after_widget;
  }

	public function get_group_events( $meetup_events, $limit ) {
    $events = $meetup_events->get_data();
    if ( ! $events )
      return;
    
    ob_start();
    get_template_part( 'meetup-list', 'group' );
    $out = ob_get_contents();

    if ( empty( $out ) ) {
      load_template( dirname(__FILE__).'/meetup-list.php', false ); 
      $out = ob_get_contents();
    }
    ob_end_clean();

		return $out;
	}

  function update($new_instance, $old_instance) {				
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['limit'] = intval($new_instance['limit']); 
    return $instance;
  }

  function form($instance) {
    if ( $instance ) {
      $title = esc_attr($instance['title']);
      $limit = intval($instance['limit']); 
    } else {
      $title = '';
      $limit = 5;
    }
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Title:
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('limit'); ?>">Number of events to show:</label>
      <input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit; ?>" size='3' />
	  </p>
    <?php 
  }
}

function meetup_widgets_start() {
	$vsmw = new VsMeetWidget();
} add_action( 'init', 'meetup_widgets_start' );

// Register widgets
add_action('widgets_init', create_function('', 'return register_widget("VsMeetNextWidget");'));
add_action('widgets_init', create_function('', 'return register_widget("VsMeetListWidget");'));
