<?php
// Register Menus
function mytheme_register_menus()
{
    register_nav_menus(
        array(
            'primary-menu' => __('Primary Menu', 'My Theme'), // for header
            'footer-menu'  => __('Footer Menu', 'My Theme'),  // for footer
        )
    );
}
add_action('init', 'mytheme_register_menus');

// Customizer for footer contact & socials
if (! function_exists('mytheme_customize_register')) {
    function mytheme_customize_register($wp_customize)
    {
        // Contact section
        $wp_customize->add_section('company_info', array(
            'title'    => __('Company Info', 'mytheme'),
            'priority' => 30,
        ));

        $wp_customize->add_setting('company_address', array('default' => '123 Consectetur at ligula 10660'));
        $wp_customize->add_setting('company_phone', array('default' => '010-020-0340'));
        $wp_customize->add_setting('company_email', array('default' => 'info@company.com'));

        $wp_customize->add_control('company_address', array(
            'label'   => __('Address', 'mytheme'),
            'section' => 'company_info'
        ));
        $wp_customize->add_control('company_phone', array(
            'label'   => __('Phone', 'mytheme'),
            'section' => 'company_info'
        ));
        $wp_customize->add_control('company_email', array(
            'label'   => __('Email', 'mytheme'),
            'section' => 'company_info'
        ));

        // Socials
        $wp_customize->add_section('social_links', array(
            'title'    => __('Social Links', 'mytheme'),
            'priority' => 31,
        ));

        foreach (['facebook', 'instagram', 'twitter', 'linkedin'] as $social) {
            $wp_customize->add_setting("{$social}_url");
            $wp_customize->add_control("{$social}_url", array(
                'label'   => ucfirst($social) . ' URL',
                'section' => 'social_links',
                'type'    => 'url',
            ));
        }
    }
}
add_action('customize_register', 'mytheme_customize_register');

// Enable custom header
add_theme_support('custom-header');
// Enable Featured Images across all post types
add_theme_support('post-thumbnails');

// Enable Elementor editor for the Posts Page
function allow_elementor_on_blog_page($can_edit, $post_id)
{
    // Get the page set as Posts Page in Reading settings
    
    $posts_page_id = get_option('page_for_posts');

    if ($post_id == $posts_page_id) {
        return true; // Allow Elementor
    }

    return $can_edit;
}
add_filter('elementor/utils/is_post_type_support', 'allow_elementor_on_blog_page', 10, 2);

// Registration Form Shortcode
function custom_registration_form()
{
    ob_start(); ?>
    <form method="post" action="">
        <?php wp_nonce_field('custom_register_action', 'custom_register_nonce'); ?>
        <p>
            <label for="reg_username">Username</label><br>
            <input type="text" name="reg_username" required>
        </p>
        <p>
            <label for="reg_email">Email</label><br>
            <input type="email" name="reg_email" required>
        </p>
        <p>
            <label for="reg_password">Password</label><br>
            <input type="password" name="reg_password" required>
        </p>
        <p>
            <input type="submit" name="custom_register" value="Register">
        </p>
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('custom_register', 'custom_registration_form');

// Handle Registration
function handle_custom_registration()
{
    if (isset($_POST['custom_register'])) {
        // Verify nonce
        if (!isset($_POST['custom_register_nonce']) || !wp_verify_nonce($_POST['custom_register_nonce'], 'custom_register_action')) {
            echo "<p style='color:red;'>Security check failed. Please try again.</p>";
            return;
        }

        $username = sanitize_user($_POST['reg_username']);
        $email = sanitize_email($_POST['reg_email']);
        $password = $_POST['reg_password'];

        $error = new WP_Error();

        if (username_exists($username)) {
            $error->add('username_exists', 'Username already exists.');
        }
        if (email_exists($email)) {
            $error->add('email_exists', 'Email already exists.');
        }
        if (empty($username) || empty($email) || empty($password)) {
            $error->add('empty_fields', 'All fields are required.');
        }

        if (empty($error->errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (!is_wp_error($user_id)) {
                // Auto-login after registration
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_redirect(home_url());
                exit;
            } else {
                echo "<p style='color:red;'>Something went wrong. Please try again.</p>";
            }
        } else {
            foreach ($error->get_error_messages() as $msg) {
                echo "<p style='color:red;'>$msg</p>";
            }
        }
    }
}
add_action('init', 'handle_custom_registration');

// Login Form Shortcode
function custom_login_form()
{
    ob_start(); ?>
    <form method="post" action="">
        <?php wp_nonce_field('custom_login_action', 'custom_login_nonce'); ?>
        <p>
            <label for="login_username">Username or Email</label><br>
            <input type="text" name="login_username" required>
        </p>
        <p>
            <label for="login_password">Password</label><br>
            <input type="password" name="login_password" required>
        </p>
        <p>
            <input type="submit" name="custom_login" value="Login">
        </p>
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('custom_login', 'custom_login_form');

// Handle Login
function handle_custom_login()
{
    if (isset($_POST['custom_login'])) {
        // Verify nonce
        if (!isset($_POST['custom_login_nonce']) || !wp_verify_nonce($_POST['custom_login_nonce'], 'custom_login_action')) {
            echo "<p style='color:red;'>Security check failed. Please try again.</p>";
            return;
        }

        $login_input = sanitize_text_field($_POST['login_username']);
        $password    = $_POST['login_password'];

        // Check if user exists (by email or username)
        $user = get_user_by('login', $login_input); // check username
        if (!$user) {
            $user = get_user_by('email', $login_input); // check email
        }

        if (!$user) {
            echo "<p style='color:red;'>User not registered. Please sign up first.</p>";
            return;
        }

        // If user exists → proceed with login
        $creds = array(
            'user_login'    => $user->user_login,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            echo "<p style='color:red;'>Invalid password. Please try again.</p>";
        } else {
            wp_redirect(home_url()); // redirect to homepage
            exit;
        }
    }
}

add_action('init', 'handle_custom_login');

// Logout Shortcode
function custom_logout_link()
{
    if (is_user_logged_in()) {
        return '<a href="' . wp_logout_url(home_url()) . '">Logout</a>';
    }
}
add_shortcode('custom_logout', 'custom_logout_link');

// Custom GET API for Products
add_action('rest_api_init', function () {
    register_rest_route('mytheme/v1', '/products', array(
        'methods'  => 'GET',
        'callback' => 'get_products_api',
        'permission_callback' => '__return_true', // Public access
    ));
});

function get_products_api(WP_REST_Request $request)
{
    $paged = $request->get_param('page') ? intval($request->get_param('page')) : 1;
    $per_page = $request->get_param('limit') ? intval($request->get_param('limit')) : 6;
    $category = sanitize_text_field($request->get_param('category'));

    $tax_query = [];
    if (!empty($category)) {
        $tax_query[] = array(
            'taxonomy' => 'products_category',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }

    $args = array(
        'post_type'      => 'prod',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => $tax_query,
    );

    $query = new WP_Query($args);
    $products = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $id = get_the_ID();
            $products[] = array(
                'id'          => $id,
                'title'       => get_the_title(),
                'description' => get_field('products_description', $id),
                'price'       => get_field('products_price', $id),
                'image'       => get_field('products_image', $id),
                'permalink'   => get_permalink($id),
                'categories'  => wp_get_post_terms($id, 'products_category', array('fields' => 'names')),
                'date'        => get_the_date(),
            );
        }
        wp_reset_postdata();
    }

    return array(
        'products' => $products,
        'total'    => $query->found_posts,
        'pages'    => $query->max_num_pages,
        'current'  => $paged,
    );
}

?>