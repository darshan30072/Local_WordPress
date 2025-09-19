<?php
get_header();

if (have_posts()) :
    while (have_posts()) : the_post(); ?>
        
        <div class="clms-course-single">
            <h1><?php the_title(); ?></h1>
            <div class="clms-course-content">
                <?php the_content(); ?>
            </div>

            <h2>Course Curriculum</h2>
            <div class="clms-course-curriculum">
                <?php
                // Get Chapters
                $chapters = get_posts([
                    'post_type'   => 'chapter',
                    'meta_key'    => '_clms_parent_course',
                    'meta_value'  => get_the_ID(),
                    'orderby'     => 'menu_order',
                    'order'       => 'ASC',
                    'numberposts' => -1
                ]);

                if ($chapters) {
                    echo '<ul class="clms-chapters">';
                    foreach ($chapters as $chapter) {
                        echo '<li>';
                        echo '<h3><a href="' . get_permalink($chapter->ID) . '">' . esc_html($chapter->post_title) . '</a></h3>';
                        echo '<p>' . wp_trim_words($chapter->post_content, 20) . '</p>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No chapters found.</p>';
                }

                // Get Quizzes
                $quizzes = get_posts([
                    'post_type'   => 'quiz',
                    'meta_key'    => '_clms_parent_course',
                    'meta_value'  => get_the_ID(),
                    'numberposts' => -1
                ]);

                if ($quizzes) {
                    echo '<h2>Quizzes</h2><ul class="clms-quizzes">';
                    foreach ($quizzes as $quiz) {
                        echo '<li>';
                        echo '<h3><a href="' . get_permalink($quiz->ID) . '">' . esc_html($quiz->post_title) . '</a></h3>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>

    <?php endwhile;
endif;

get_footer();
