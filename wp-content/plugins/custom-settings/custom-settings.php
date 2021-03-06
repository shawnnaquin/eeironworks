<?php
/*
Plugin Name: Custom Settings
Plugin URI: wordpress codex
Description: Register Side Bars
Author: wordpress codex
Version: 1
Author URI: https://wordpress.com
*/

// $new_general_setting = new new_general_setting();

// class new_general_setting {
//     function new_general_setting( ) {
//         add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
//     }
//     function register_fields() {
//         register_setting( 'general', 'bout_location', 'esc_attr' );
//         add_settings_field('bou_location', '<label for="bout_location">'.__('Bout Location?' , 'bout_location' ).'</label>' , array(&$this, 'fields_html') , 'general' );
//         register_setting( 'general', 'background_image', 'esc_attr' );
//         add_settings_field('bg_image', '<label for="background_image">'.__('Background Image?' , 'background_image' ).'</label>' , array(&$this, 'fields_html') , 'general' );

//     }
//     function fields_html() {
//         $value = get_option( 'bout_location', '' );
//         echo '<input type="text" id="bout_location" name="bout_location" value="' . $value . '" />';
//         $value1 = get_option( 'background_image', '' );
//         echo '<input type="text" id="background_image" name="background_image" value="' . $value1 . '" />';
//     }
// }

remove_action( 'admin_init', 'A2A_SHARE_SAVE_add_meta_box' );
remove_action( 'save_post', 'A2A_SHARE_SAVE_meta_box_save' );
remove_action( 'edit_attachment', 'A2A_SHARE_SAVE_meta_box_save' );

// wp_enqueue_script( 'vimeo', $src, $deps, $ver, $in_footer );
wp_enqueue_script( 'script', 'https://player.vimeo.com/api/player.js', true);

// Async load
function async_scripts($url) {
    if ( strpos( $url, '#asyncload') === false )
        return $url;
    else if ( is_admin() )
        return str_replace( '#asyncload', '', $url );
    else
        return str_replace( '#asyncload', '', $url )."' async='async' defer='defer";
}


add_filter( 'clean_url', 'async_scripts', 10,1 );

function my_custom_admin_styles() {
  echo '<style>
    input.long-text {
        width:45rem;
    }
  </style>';
}

add_action('admin_head', 'my_custom_admin_styles');

add_filter('admin_init', 'my_general_settings_register_fields');

    function my_general_settings_register_fields()
    {
        // https://player.vimeo.com/video/218861893

        register_setting('general', 'main_video_link', 'esc_attr');
        add_settings_field('main_video_link', '<label for="main_video_link">'.__('Main Video Link' , 'main_video_link' ).'</label>' , 'main_video_link_html', 'general');

        register_setting('general', 'intro_copy', 'esc_attr');
        add_settings_field('intro_copy', '<label for="intro_copy">'.__('Intro Copy' , 'intro_copy' ).'</label>' , 'intro_copy_html', 'general');

        register_setting('general', 'site_street', 'esc_attr');
        add_settings_field('site_street', '<label for="site_street">'.__('Site Street' , 'site_street' ).'</label>' , 'site_street_html', 'general');

        register_setting('general', 'site_address', 'esc_attr');
        add_settings_field('site_address', '<label for="site_address">'.__('Site Address' , 'site_address' ).'</label>' , 'site_address_html', 'general');

        register_setting('general', 'site_address_link', 'esc_attr');
        add_settings_field('site_address_link', '<label for="site_address_link">'.__('Site Address Link' , 'site_address_link' ).'</label>' , 'site_address_link_html', 'general');

        register_setting('general', 'site_phone', 'esc_attr');
        add_settings_field('site_phone', '<label for="site_phone">'.__('Site Phone' , 'site_phone' ).'</label>' , 'site_phone_html', 'general');

        register_setting('general', 'blog_header', 'esc_attr');
        add_settings_field('blog_header', '<label for="blog_header">'.__('Blog Header' , 'blog_header' ).'</label>' , 'blog_header_html', 'general');

        register_setting('general', 'blog_subheader', 'esc_attr');
        add_settings_field('blog_subheader', '<label for="blog_subheader">'.__('Blog Subheader' , 'blog_subheader' ).'</label>' , 'blog_subheader_html', 'general');

        register_setting('general', 'video_header', 'esc_attr');
        add_settings_field('video_header', '<label for="video_header">'.__('Video Header' , 'video_header' ).'</label>' , 'video_header_html', 'general');

        register_setting('general', 'video_subheader', 'esc_attr');
        add_settings_field('video_subheader', '<label for="video_subheader">'.__('Video Subheader' , 'video_subheader' ).'</label>' , 'video_subheader_html', 'general');

        register_setting('general', 'video_title', 'esc_attr');
        add_settings_field('video_title', '<label for="video_title">'.__('Show Video Titles?' , 'video_title' ).'</label>' , 'video_title_html', 'general');

        register_setting('general', 'site_logo', 'esc_attr');
        add_settings_field('site_logo', '<label for="site_logo">'.__('Site Logo Image <br/><small>(*.png, *.gif)<br/><span style="color:red">(required)</span></small>' , 'site_logo' ).'</label>' , 'site_logo_html', 'general');

        register_setting('general', 'site_logo_vector', 'esc_attr');
        add_settings_field('site_logo_vector', '<label for="site_logo_vector">'.__('Site Logo Vector<br/><small>(*.svg)<br/>(optional)</small>' , 'site_logo_vector' ).'</label>' , 'site_logo_vector', 'general');

        register_setting('general', 'site_logo_vector', 'esc_attr');
        add_settings_field('site_logo_vector', '<label for="site_logo_vector">'.__('Site Logo Vector<br/><small>(*.svg)<br/>(optional)</small>' , 'site_logo_vector' ).'</label>' , 'site_logo_vector', 'general');

        register_setting('general', 'facebook', 'esc_attr');
        add_settings_field('facebook', '<label for=facebook">'.__('Facebook Link<br/>' , 'facebook' ).'</label>' , 'facebook', 'general');

        register_setting('general', 'twitter', 'esc_attr');
        add_settings_field('twitter', '<label for=twitter">'.__('Twitter Link<br/>' , 'twitter' ).'</label>' , 'twitter', 'general');

        register_setting('general', 'instagram', 'esc_attr');
        add_settings_field('instagram', '<label for=instagram">'.__('Instagram Link<br/>' , 'instagram' ).'</label>' , 'instagram', 'general');

    }

    function main_video_link_html()
    {
        $main_video_link = get_option( 'main_video_link', '' );
        echo '<input type="text" class="long-text" id="main_video_link" name="main_video_link" value="' . $main_video_link . '" />';
    }

    function intro_copy_html()
    {
        $intro_copy = get_option( 'intro_copy', '' );
        echo '<input type="text" class="long-text" id="intro_copy" name="intro_copy" value="' . $intro_copy . '" />';
    }

    function blog_header_html()
    {
        $blog_header = get_option( 'blog_header', '' );
        echo '<input type="text" class="long-text" id="blog_header" name="blog_header" value="' . $blog_header . '" />';
    }

    function site_street_html()
    {
        $site_street = get_option( 'site_street', '' );
        echo '<input type="text" class="long-text" id="site_street" name="site_street" value="' . $site_street . '" />';
    }

    function site_address_html()
    {
        $site_address = get_option( 'site_address', '' );
        echo '<input type="text" class="long-text" id="site_address" name="site_address" value="' . $site_address . '" />';
    }

    function site_address_link_html()
    {
        $site_address_link = get_option( 'site_address_link', '' );
        echo '<input type="text" class="long-text" id="site_address_link" name="site_address_link" value="' . $site_address_link . '" />';
    }

    function site_phone_html()
    {
        $site_phone = get_option( 'site_phone', '' );
        echo '<input type="text" class="long-text" id="site_phone" name="site_phone" value="' . $site_phone . '" />';
    }

    function blog_subheader_html()
    {
        $blog_subheader = get_option( 'blog_subheader', '' );
        echo '<input type="text" class="long-text" id="blog_subheader" name="blog_subheader" value="' . $blog_subheader . '" />';
    }

    function video_header_html()
    {
        $video_header = get_option( 'video_header', '' );
        echo '<input type="text" class="long-text" id="video_header" name="video_header" value="' . $video_header . '" />';
    }

    function video_subheader_html()
    {
        $video_subheader = get_option( 'video_subheader', '' );
        echo '<input type="text" class="long-text" id="video_subheader" name="video_subheader" value="' . $video_subheader . '" />';
    }

    function video_title_html()
    {
        $video_title = get_option( 'video_title', '' );
        echo '<input type="checkbox" id="video_title" name="video_title" value="' . $video_title . '" />';
    }

    function map_html()
    {
        $map = get_option( 'map', '' );
        echo '<input type="text" class="long-text" id="map" name="map" value="' . $map . '" />';
    }

    function site_logo_html()
    {
        $site_logo = get_option( 'site_logo', '' );
        echo '<input type="text" class="long-text" id="site_logo" name="site_logo" value="' . $site_logo . '" />';
    }

    function site_logo_vector()
    {
        $site_logo_vector = get_option( 'site_logo_vector', '' );
        echo '<input type="text" class="long-text" id="site_logo_vector" name="site_logo_vector" value="' . $site_logo_vector . '" />';
    }

    function instagram()
    {
        $instagram = get_option( 'instagram', '' );
        echo '<input type="text" class="long-text" id="instagram" name="instagram" value="' . $instagram . '" />';
    }
    function twitter()
    {
        $twitter = get_option( 'twitter', '' );
        echo '<input type="text" class="long-text" id="twitter" name="twitter" value="' . $twitter . '" />';
    }
    function facebook()
    {
        $facebook = get_option( 'facebook', '' );
        echo '<input type="text" class="long-text" id="facebook" name="facebook" value="' . $facebook . '" />';
    }

    add_action('admin_head', 'custom_styles');

    function custom_styles() {
      echo '<style>
        .cpac-column-value-image, .cpac-column-value-image > img {
            width:50px!important;
            height:50px!important;
        }
        .widefat td {
            vertical-align:middle;
        }
      </style>';
    }

?>