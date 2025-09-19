<?php
function myfirsttheme_setup() {
    // Enable featured images
    add_theme_support('post-thumbnails');

    // Dynamic title tag
    add_theme_support('title-tag');

    // Register menu
    register_nav_menus([
        'main-menu' => __('Main Menu', 'myfirsttheme')
    ]);
}
add_action('after_setup_theme', 'myfirsttheme_setup');

// Enqueue CSS
function myfirsttheme_enqueue_styles() {
    wp_enqueue_style('myfirsttheme-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'myfirsttheme_enqueue_styles');

// Register sidebar
function myfirsttheme_widgets() {
    register_sidebar([
        'name' => 'Main Sidebar',
        'id' => 'main-sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ]);
}
add_action('widgets_init', 'myfirsttheme_widgets');
?>