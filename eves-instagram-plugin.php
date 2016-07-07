<?php
/*
Plugin Name: Eve's Instagram Feed
Description: Widget to show image feeds by user plus some configuration options.
Plugin URI:    https://github.com/eessex/InstagramPlugin
Version: 0.0.1
Author: Eve Essex
Author URI: http://github.com/eessex
*/

// fix SSL request error
add_action( 'http_request_args', 'no_ssl_http_request_args', 10, 2 );
function no_ssl_http_request_args( $args, $url ) {
    $args['sslverify'] = false;
    return $args;
}


  class wp_my_plugin extends WP_Widget {

    // constructor
    function wp_my_plugin() {
      parent::WP_Widget(false, $name = __("Instagram Feed", "wp_widget_plugin") );
    }

    // form creation
    function form($instance) {
      // Check values
      if( $instance) {
        $title = esc_attr($instance['title']);
        $username = esc_attr($instance['username']);
        $follow_text = esc_attr( $instance['follow_text'] );
        $textarea = esc_textarea($instance['textarea']);
        $access_key = esc_attr($instance['access_key']);
        $select = esc_attr($instance['select']);
      } else {
        $title = '';
        $username = '';
        $follow_text = '';
        $textarea = '';
        $access_key = '';
        $select = '';
      } ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('access_key'); ?>"><?php _e('Instagram API Access Key:', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('access_key'); ?>" name="<?php echo $this->get_field_name('access_key'); ?>" type="access_key" value="<?php echo $access_key; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Instagram Username:', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="username" value="<?php echo $username; ?>" />
      </p>
      <p>
        <input id="<?php echo esc_attr( $this->get_field_id( 'follow_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'follow_text' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $follow_text ); ?> />
        <label for="<?php echo esc_attr( $this->get_field_id( 'follow_text' ) ); ?>"><?php _e( 'Display follow text & link to your account.', 'wp_widget_plugin' ); ?></label>
      </p>

      <p>
        <label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Text area:', 'wp_widget_plugin'); ?></label>
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
      $instance['access_key'] = strip_tags($new_instance['access_key']);
      $instance['username'] = strip_tags($new_instance['username']);
      $instance['follow_text'] = strip_tags($new_instance['follow_text']);
      $instance['textarea'] = strip_tags($new_instance['textarea']);
      $instance['select'] = strip_tags($new_instance['select']);
      return $instance;
    }
    // Display widget
    function widget($args, $instance) {
      // Define main output
      $str    = '';
      // Get remote data
       $request_url = 'https://api.instagram.com/v1/users/199972609/media/recent?count=6&access_token=' . $instance['access_key'];
       $result = wp_remote_get( $request_url );

      extract( $args );
      // Widget options
      $title = apply_filters('widget_title', $instance['title']);
      $access_key = $instance['access_key'];
      $username = $instance['username'];
      $follow_text = $instance['follow_text'];
      $textarea = $instance['textarea'];
      $select = $instance['select'];
      echo $before_widget;

      // Widget display
      echo '<div class="widget-text wp_widget_plugin_box">';
       // Check if title is set
       if ( $title ) {
          echo $before_title . $title . $after_title;
       }
       // Check if username is set
       if( $username && $follow_text) {
          echo '<h3 class="wp_widget_plugin_username"><a href="http://instagram.com/'.$username.'">Follow @'.$username.' on Instagram</a></h3>';
       }
       // Check if textarea is set
       if( $textarea ) {
         echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>';
       }
       // Get number of posts to display
       	if ( $select == '1' ) {
       		echo '1 post is currently visible';
       	} else {
       		echo $select . ' posts are currently visible';
       	}

      // Parse instagram API
      if ( is_wp_error( $result ) ) {
           // Error handling
           $error_message = $result->get_error_message();
           $str           = "Something went wrong: $error_message";
       } else {
           // Get the data
           $result    = json_decode( $result['body'] );
           $main_data = array();
           $n         = 0;

           // Get username and actual thumbnail
           foreach ( $result->data as $d ) {
             if ($n <= ($select -1)) {
               $main_data[ $n ]['user']      = $d->user->username;
               $main_data[ $n ]['low_resolution'] = $d->images->low_resolution->url;
               $main_data[ $n ]['comments']      = $d->comments->count;
               $main_data[ $n ]['likes']      = $d->likes->count ;
               $n++;
             }
           }

           // Create main string, pictures embedded in links
           foreach ( $main_data as $data ) {
              if ($data['likes'] > 0 ) {
               $likes = '<span class="likes">' . $data['likes'] . ' likes</span>';
              }
              if ($data['comments'] > 0 ) {
               $comments = '<span class="comments">' . $data['comments'] . ' comments</span>';
              }
               $str .= '<a target="_blank" href="http://instagram.com/'.$data['user'].'"><div class="item"><img src="'.$data['low_resolution'].'" alt="'.$data['user'].' pictures"><div class="overlay">'.$likes.$comments.'</div></div></a> ';
           }
       }

       echo $str;


      echo '</div>';
      echo $after_widget;
    }
}
// Register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_my_plugin");'));

?>
