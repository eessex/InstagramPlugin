<?php
/*
Plugin Name: Eve's Instagram Feed
Plugin URI:    https://github.com/eessex
Version: 0.0.1
Author: Eve Essex
Author URI: http://github.com/eessex
*/

  class wp_my_plugin extends WP_Widget {

    // constructor
    function wp_my_plugin() {
      parent::WP_Widget(false, $name = __("Eve's Instagram Feed Widget", "wp_widget_plugin") );
    }

    // form creation
    function form($instance) {
      // Check values
      if( $instance) {
        $title = esc_attr($instance['title']);
        $text = esc_attr($instance['text']);
        $textarea = esc_textarea($instance['textarea']);
        $select = esc_attr($instance['select']);
      } else {
        $title = '';
        $text = '';
        $textarea = '';
        $select = '';
      } ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Textarea:', 'wp_widget_plugin'); ?></label>
        <textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>"><?php echo $textarea; ?></textarea>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('select'); ?>"><?php _e('Posts to display', 'wp_widget_plugin'); ?></label>
        <select name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>" class="widefat">
        <?php
        $options = array('1', '2', '3', '4', '5', '6');
        foreach ($options as $option) {
        echo '<option value="' . $option . '" id="' . $option . '"', $select == $option ? ' selected="selected"' : '', '>', $option, '</option>';
        }
        ?>
        </select>
      </p>
      <?php
    }
    // update widget
    function update($new_instance, $old_instance) {
      $instance = $old_instance;
      // Fields
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['text'] = strip_tags($new_instance['text']);
      $instance['textarea'] = strip_tags($new_instance['textarea']);
      $instance['select'] = strip_tags($new_instance['select']);
      return $instance;
    }
    // display widget
    function widget($args, $instance) {
       extract( $args );
       // these are the widget options
       $title = apply_filters('widget_title', $instance['title']);
       $text = $instance['text'];
       $textarea = $instance['textarea'];
       echo $before_widget;
       // Display the widget
       echo '<div class="widget-text wp_widget_plugin_box">';
       // Check if title is set
       if ( $title ) {
          echo $before_title . $title . $after_title;
       }
       // Check if text is set
       if( $text ) {
          echo '<p class="wp_widget_plugin_text">'.$text.'</p>';
       }
       // Check if textarea is set
       if( $textarea ) {
         echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>';
       }
       // Get $select value
       	if ( $select == '1' ) {
       		echo '1 post is currently visible';
       	} else {
       		echo '$select posts are currently visible';
       	}

       echo '</div>';
       echo $after_widget;
    }
}
// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_my_plugin");'));

?>
