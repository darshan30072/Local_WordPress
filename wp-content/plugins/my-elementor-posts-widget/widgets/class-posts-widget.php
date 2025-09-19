<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MEPW_Posts_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mepw_posts';
    }

    public function get_title() {
        return __( 'Posts Widget', 'mepw' );
    }

    public function get_icon() {
        return 'eicon-posts-grid';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'mepw' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Number of posts
        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Number of Posts', 'mepw' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
            ]
        );

        // Category filter dropdown
        $categories = get_categories( [ 'hide_empty' => false ] );
        $options = [ 'all' => __( 'All Categories', 'mepw' ) ];
        foreach ( $categories as $cat ) {
            $options[$cat->slug] = $cat->name;
        }

        $this->add_control(
            'category_filter',
            [
                'label'   => __( 'Category', 'mepw' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => $options,
                'default' => 'all',
            ]
        );

        // Show featured image toggle
        $this->add_control(
            'show_image',
            [
                'label'        => __( 'Show Featured Image', 'mepw' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'mepw' ),
                'label_off'    => __( 'No', 'mepw' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        // Layout type (Grid/List)
        $this->add_control(
            'layout_type',
            [
                'label'   => __( 'Layout', 'mepw' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'grid' => [
                        'title' => __( 'Grid', 'mepw' ),
                        'icon'  => 'eicon-gallery-grid',
                    ],
                    'list' => [
                        'title' => __( 'List', 'mepw' ),
                        'icon'  => 'eicon-menu-bar',
                    ],
                ],
                'default' => 'grid',
                'toggle'  => false,
            ]
        );

        // Pagination type
        $this->add_control(
            'pagination',
            [
                'label'   => __( 'Pagination', 'mepw' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'     => __( 'None', 'mepw' ),
                    'numbers'  => __( 'Numbers', 'mepw' ),
                    'nextprev' => __( 'Next/Prev', 'mepw' ),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Current page number
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

        // Query args
        $args = [
            'post_type'      => 'post',
            'posts_per_page' => $settings['posts_per_page'],
            'paged'          => $paged,
        ];

        if ( $settings['category_filter'] !== 'all' ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $settings['category_filter'],
                ]
            ];
        }

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            // Add wrapper class for layout
            $wrapper_class = $settings['layout_type'] === 'grid' ? 'mepw-posts-grid row' : 'mepw-posts-list';

            echo '<div class="' . esc_attr( $wrapper_class ) . '">';

            while ( $query->have_posts() ) {
                $query->the_post();
                ?>
                <div class="<?php echo $settings['layout_type'] === 'grid' ? 'col-md-4 mb-4' : 'mepw-list-item'; ?>">
                    <div class="mepw-post card h-100">
                        <?php if ( $settings['show_image'] === 'yes' && has_post_thumbnail() ) : ?>
                            <div class="mepw-post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium', [ 'class' => 'img-fluid card-img-top' ] ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h3 class="mepw-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div class="mepw-post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';

            // ✅ Pagination
            if ( $settings['pagination'] !== 'none' ) {
                echo '<div class="mepw-pagination text-center mt-4">';
                if ( $settings['pagination'] === 'numbers' ) {
                    echo paginate_links( [
                        'total'   => $query->max_num_pages,
                        'current' => $paged,
                    ] );
                } elseif ( $settings['pagination'] === 'nextprev' ) {
                    previous_posts_link( __( '← Previous', 'mepw' ), $query->max_num_pages );
                    next_posts_link( __( 'Next →', 'mepw' ), $query->max_num_pages );
                }
                echo '</div>';
            }

            wp_reset_postdata();
        } else {
            echo '<p>' . __( 'No posts found.', 'mepw' ) . '</p>';
        }
    }
}
