<?php
/*
Plugin Name: Eve's Instagram Feed
Description: Widget to show image feeds by user plus some configuration options.
Plugin URI:    https://github.com/eessex/InstagramPlugin
Version: 0.0.1
Author: Eve Essex
Author URI: http://github.com/eessex
*/

// Add styles
add_action( 'wp_enqueue_scripts', 'add_stylesheet' );
function add_stylesheet() {
    wp_register_style( 'instagram-feed-style', plugins_url('css/style.css', __FILE__) );
    wp_enqueue_style( 'instagram-feed-style' );
}

// Request with SSL optional
add_action( 'http_request_args', 'no_ssl_http_request_args', 10, 2 );
function no_ssl_http_request_args( $args, $url ) {
    $args['sslverify'] = false;
    return $args;
}

  class wp_my_plugin extends WP_Widget {
    // constructor
    function wp_my_plugin() {
      parent::WP_Widget(false, $name = __("Instagram Feed", "instagram-feed") );
    }

    // Create form
    function form($instance) {
      // Check values
      if( $instance) {
        $title = esc_attr($instance['title']);
        $user_id = esc_attr($instance['user_id']);
        $follow_text = esc_attr( $instance['follow_text'] );
        $textarea = esc_textarea($instance['textarea']);
        $access_key = esc_attr($instance['access_key']);
        $select = esc_attr($instance['select']);
      } else {
        $title = '';
        $user_id = '';
        $follow_text = '';
        $textarea = '';
        $access_key = '';
        $select = '';
      } ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'instagram-feed'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('access_key'); ?>"><?php _e('Instagram API Access Key:', 'instagram-feed'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('access_key'); ?>" name="<?php echo $this->get_field_name('access_key'); ?>" type="access_key" value="<?php echo $access_key; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('user_id'); ?>"><?php _e('User ID:', 'instagram-feed'); ?> </label>
        <input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>" type="user_id" value="<?php echo $user_id; ?>" />
      </p>
      <p>
        <input id="<?php echo esc_attr( $this->get_field_id( 'follow_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'follow_text' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $follow_text ); ?> />
        <label for="<?php echo esc_attr( $this->get_field_id( 'follow_text' ) ); ?>"><?php _e( 'Display follow text & link to this account.', 'instagram-feed' ); ?></label>
      </p>

      <p>
        <label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Text area:', 'instagram-feed'); ?></label>
        <textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>"><?php echo $textarea; ?></textarea>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('select'); ?>"><?php _e('Posts to display', 'instagram-feed'); ?></label>
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
      $instance['user_id'] = strip_tags($new_instance['user_id']);
      $instance['follow_text'] = strip_tags($new_instance['follow_text']);
      $instance['textarea'] = strip_tags($new_instance['textarea']);
      $instance['select'] = strip_tags($new_instance['select']);
      return $instance;
    }

    // Display widget
    function widget($args, $instance) {

      // Fetch widget params
      $title = apply_filters('widget_title', $instance['title']);
      $access_key = $instance['access_key'];
      $user_id = $instance['user_id'];
      $follow_text = $instance['follow_text'];
      $textarea = $instance['textarea'];
      $select = $instance['select'];
      echo $before_widget;

      // Get remote data
// $request_url = 'https://www.instagram.com/newmusicusa/media/'
      $request_url = 'https://api.instagram.com/v1/users/'.$user_id.'/media/recent?count=6&access_token=' . $instance['access_key'];
      $result = wp_remote_get( $request_url );
      extract( $args );

      // Widget display
      echo '<div class="instagram-feed">';

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
               $str    = '';
               $main_data[ $n ]['user']      = $d->user->username;
               $main_data[ $n ]['low_resolution'] = $d->images->low_resolution->url;
               $main_data[ $n ]['comments']      = $d->comments->count;
               $main_data[ $n ]['likes']      = $d->likes->count ;

               if ($n == 0) {
                 echo '<div class="header">';
                 // Check if title is set
                 if ( $title ) {
                    echo $before_title . $title . $after_title;
                 }
                 // Check if textarea is set
                 if( $textarea ) {
                   echo '<p class="textarea">'.$textarea.'</p>';
                 }
                 // Add optional follow text
                 if ($follow_text == '1') {
                   echo '<div class="follow-text"><a href="http://instagram.com/'.$user_id.'">@'. $main_data[ $n ]['user'] .' on Instagram</a></div>';
                 }
                 echo '</div>';
               }
               $n++;
             }
           }

           // Create main string, pictures embedded in links
           echo '<div class="images">';
           foreach ( $main_data as $data ) {
               $str = '<div class="item"><a target="_blank" href="http://instagram.com/'.$data['user'].'"><img src="'.$data['low_resolution'].'" alt="'.$data['user'].' pictures"><div class="overlay"><ul><li class="likes">' . $data['likes'] . ' <i class="fa fa-heart" aria-hidden="true"></i></li><li class="comments">' . $data['comments'] . ' <i class="fa fa-comment" aria-hidden="true"></i></li></ul></div></a></div> ';
               echo $str;
           }
           echo '</div>';
        }
      echo '</div>';
    }
  }
  // Register widget
  add_action('widgets_init', create_function('', 'return register_widget("wp_my_plugin");'));

?>
