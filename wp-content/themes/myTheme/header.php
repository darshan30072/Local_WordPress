<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php wp_title('|', true, 'right');
            bloginfo('name'); ?></title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/icon.png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/templatemo.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/custom.css">

    <!-- Google Fonts & FontAwesome -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/fontawesome.min.css">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light py-2">
        <div class="container d-flex justify-content-between align-items-center p-0">

            <!-- Site Title / Logo -->
            <a class="navbar-brand text-success logo h1 align-self-center" href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#templatemo_main_nav" aria-controls="templatemo_main_nav" aria-expanded="false"
                aria-label="<?php esc_attr_e('Toggle navigation', 'MyTheme'); ?>">
                <span class="navbar-toggler-icon"></span>
            </button> 

            <div class="align-self-center collapse navbar-collapse d-lg-flex justify-content-lg-between"
                id="templatemo_main_nav">

                <!-- WordPress Menu -->
                <?php
                wp_nav_menu(
                    array(
                        "theme_location"  => "primary-menu",
                        "container_class" => "menu-header-container flex-fill",
                        "menu_class"      => "nav navbar-nav d-flex justify-content-evenly ms-lg-auto",
                        "fallback_cb"     => false,
                    )
                );
                ?>
            </div>
        </div>
    </nav>
    <!-- Close Header -->