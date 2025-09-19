<?php
/**
 * Plugin Name: My Elementor Posts Widget
 * Description: Custom Elementor widget to display WordPress posts.
 * Version: 1.0
 * Author: Darshan 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load widget class
function mepw_register_posts_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/class-posts-widget.php' );
    $widgets_manager->register( new \MEPW_Posts_Widget() );
}
add_action( 'elementor/widgets/register', 'mepw_register_posts_widget' );
