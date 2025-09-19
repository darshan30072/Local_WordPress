<?php
/*
Plugin Name: Custom LMS
Description: A lightweight custom LMS plugin for WordPress.
Version: 1.3
Author: Darshan
*/

if (!defined('ABSPATH')) exit;


// Register Post Types
function clms_register_post_types()
{
    // Course
    register_post_type('course', array(
        'labels' => array(
            'name'               => 'Courses',
            'singular_name'      => 'Course',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Course',
            'edit_item'          => 'Edit Course',
            'new_item'           => 'New Course',
            'view_item'          => 'View Course',
            'search_items'       => 'Search Courses',
            'not_found'          => 'No courses found',
            'not_found_in_trash' => 'No courses found in Trash',
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => 'clms_dashboard',
        'supports' => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'author',
            'comments'
        ),
        'taxonomies' => array('category', 'post_tag')
    ));

    // Chapter
    register_post_type('chapter', array(
        'labels' => array(
            'name'               => 'Chapters',
            'singular_name'      => 'Chapter',
            'add_new_item'       => 'Add New Chapter',
            'edit_item'          => 'Edit Chapter',
            'new_item'           => 'New Chapter',
            'view_item'          => 'View Chapter',
            'search_items'       => 'Search Chapters',
            'not_found'          => 'No Chapters found',
        ),
        'public' => true,
        'has_archive' => false,
        'hierarchical' => true,
        'show_in_menu' => 'clms_dashboard',
        'supports' => array('title', 'editor', 'comments'),
    ));

    // Quiz
    register_post_type('quiz', array(
        'labels' => array(
            'name'               => 'Quizzes',
            'singular_name'      => 'Quiz',
            'add_new_item'       => 'Add New Quiz',
            'edit_item'          => 'Edit Quiz',
            'new_item'           => 'New Quiz',
            'view_item'          => 'View Quiz',
            'search_items'       => 'Search Quizzes',
            'not_found'          => 'No quizzes found',
        ),
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => 'clms_dashboard',
        'supports' => array('title', 'editor'),
    ));

    // Question
    register_post_type('question', array(
        'labels' => array(
            'name'               => 'Questions',
            'singular_name'      => 'Question',
            'add_new_item'       => 'Add New Question',
            'edit_item'          => 'Edit Question',
            'new_item'           => 'New Question',
            'view_item'          => 'View Question',
            'search_items'       => 'Search Questions',
            'not_found'          => 'No questions found',
        ),
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => 'clms_dashboard',
        'supports' => array('title', 'editor'),
        'taxonomies' => array('post_tag')
    ));
}
add_action('init', 'clms_register_post_types');

// Register LMS parent menu
function clms_register_parent_menu()
{
    add_menu_page(
        'LMS',               // Page title
        'LMS',               // Menu title
        'manage_options',    // Capability
        'clms_dashboard',    // Menu slug
        'clms_dashboard_page', // Callback
        'dashicons-welcome-learn-more', // Icon
        3                    // Position
    );
}
add_action('admin_menu', 'clms_register_parent_menu');

// Enable featured images globally
function clms_enable_thumbnails()
{
    add_theme_support('post-thumbnails', ['course', 'chapter', 'quiz', 'question']);
}
add_action('after_setup_theme', 'clms_enable_thumbnails');

// Add Course Meta Boxes: Curriculum + Settings
function clms_add_course_meta_boxes()
{
    add_meta_box(
        'clms_course_curriculum',
        'Curriculum',
        'clms_render_curriculum_box',
        'course',
        'normal',
        'high'
    );

    add_meta_box(
        'clms_course_settings',
        'Course Settings',
        'clms_render_settings_box',
        'course',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'clms_add_course_meta_boxes');

// Certificate Meta Box for Courses
function clms_add_course_certificate_meta_box() {
    add_meta_box(
        'clms_course_certificate',
        'Certificate Settings',
        'clms_render_course_certificate_box',
        'course',   // 👉 Add to course, not quiz
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'clms_add_course_certificate_meta_box');

// Render Certificate Meta Box for Course
function clms_render_course_certificate_box($post) {
    $certificates = get_posts([
        'post_type'   => 'clms_certificate',
        'numberposts' => -1
    ]);

    $selected = get_post_meta($post->ID, '_clms_course_certificate', true);

    echo '<label for="clms_course_certificate">Select Certificate:</label>';
    echo '<select name="clms_course_certificate" id="clms_course_certificate" style="width:100%;">';
    echo '<option value="">— None —</option>';
    foreach ($certificates as $cert) {
        echo '<option value="' . esc_attr($cert->ID) . '" ' . selected($selected, $cert->ID, false) . '>';
        echo esc_html($cert->post_title);
        echo '</option>';
    }
    echo '</select>';
}

// Save Course Certificate Meta
function clms_save_course_certificate($post_id) {
    if (isset($_POST['clms_course_certificate'])) {
        update_post_meta($post_id, '_clms_course_certificate', intval($_POST['clms_course_certificate']));
    }
}
add_action('save_post_course', 'clms_save_course_certificate');

// Render Curriculum Meta Box
function clms_render_curriculum_box($post)
{
    // Get saved curriculum order (array of post IDs)
    $curriculum_order = get_post_meta($post->ID, '_clms_curriculum_order', true);
    if (!is_array($curriculum_order)) $curriculum_order = [];

    // Fetch chapters & quizzes for this course
    $chapters = get_posts([
        'post_type'   => ['chapter', 'quiz'],
        'meta_key'    => '_clms_parent_course',
        'meta_value'  => $post->ID,
        'numberposts' => -1
    ]);

    // Reorder chapters according to saved order
    usort($chapters, function ($a, $b) use ($curriculum_order) {
        $pos_a = array_search($a->ID, $curriculum_order);
        $pos_b = array_search($b->ID, $curriculum_order);
        return ($pos_a === false ? PHP_INT_MAX : $pos_a) - ($pos_b === false ? PHP_INT_MAX : $pos_b);
    });

    echo '<ul id="clms-curriculum-list" style="margin:0; padding:0; list-style:none;">';

    if (!empty($chapters)) {
        foreach ($chapters as $chapter) {
            echo '<li class="clms-curriculum-item" data-id="' . esc_attr($chapter->ID) . '" style="padding:8px; margin:4px 0; background:#f9f9f9; border:1px solid #ddd; cursor:move;">';
            echo esc_html($chapter->post_title) . ' (' . esc_html(ucfirst($chapter->post_type)) . ')';
            echo '</li>';
        }
    } else {
        echo '<li>No chapters or quizzes assigned yet.</li>';
    }

    echo '</ul>';
    echo '<input type="hidden" id="clms_curriculum_order" name="clms_curriculum_order" value="' . esc_attr(implode(',', $curriculum_order)) . '">';

    // JS for sortable list
?>
    <script>
        jQuery(document).ready(function($) {
            $('#clms-curriculum-list').sortable({
                update: function(event, ui) {
                    var order = [];
                    $('#clms-curriculum-list .clms-curriculum-item').each(function() {
                        order.push($(this).data('id'));
                    });
                    $('#clms_curriculum_order').val(order.join(','));
                }
            });
        });
    </script>
<?php
}

// Save Curriculum Order
function clms_save_curriculum_order($post_id)
{
    if (isset($_POST['clms_curriculum_order'])) {
        $order = array_filter(array_map('intval', explode(',', $_POST['clms_curriculum_order'])));
        update_post_meta($post_id, '_clms_curriculum_order', $order);
    }
}
add_action('save_post_course', 'clms_save_curriculum_order');

// Render Settings Meta Box
function clms_render_settings_box($post)
{
    $duration = get_post_meta($post->ID, '_clms_duration', true);
    $level = get_post_meta($post->ID, '_clms_level', true);

    echo '<p><label>Course Duration:</label><br>';
    echo '<input type="text" name="clms_duration" value="' . esc_attr($duration) . '" placeholder="e.g. 6 weeks" style="width:100%;"></p>';

    echo '<p><label>Difficulty Level:</label><br>';
    echo '<select name="clms_level" style="width:100%;">';
    $levels = ['Beginner', 'Intermediate', 'Advanced'];
    foreach ($levels as $opt) {
        echo '<option value="' . esc_attr($opt) . '" ' . selected($level, $opt, false) . '>' . esc_html($opt) . '</option>';
    }
    echo '</select></p>';
}

// Save Meta Boxes
function clms_save_course_meta($post_id)
{
    if (isset($_POST['clms_curriculum'])) {
        update_post_meta($post_id, '_clms_curriculum', sanitize_textarea_field($_POST['clms_curriculum']));
    }
    if (isset($_POST['clms_duration'])) {
        update_post_meta($post_id, '_clms_duration', sanitize_text_field($_POST['clms_duration']));
    }
    if (isset($_POST['clms_level'])) {
        update_post_meta($post_id, '_clms_level', sanitize_text_field($_POST['clms_level']));
    }
}
add_action('save_post_course', 'clms_save_course_meta');

// Add meta boxes for Chapters
function clms_add_chapter_meta_boxes()
{
    // Assign to Course 
    add_meta_box(
        'clms_chapter_course',
        'Assign to Course',
        'clms_render_chapter_meta_box',
        ['chapter', 'quiz'],
        'side',
        'default'
    );

    // Chapter Settings
    add_meta_box(
        'clms_chapter_settings',
        'Chapter Settings',
        'clms_render_chapter_settings_box',
        'chapter',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'clms_add_chapter_meta_boxes');

// Render Assign to Course box
function clms_render_chapter_meta_box($post)
{
    $courses = get_posts(['post_type' => 'course', 'numberposts' => -1]);
    $selected = get_post_meta($post->ID, '_clms_parent_course', true);

    echo '<select name="clms_parent_course" style="width:100%;">';
    echo '<option value="">— Select Course —</option>';
    foreach ($courses as $course) {
        echo '<option value="' . esc_attr($course->ID) . '" ' . selected($selected, $course->ID, false) . '>';
        echo esc_html($course->post_title);
        echo '</option>';
    }
    echo '</select>';
}

// Render Chapter Settings box
function clms_render_chapter_settings_box($post)
{
    $duration = get_post_meta($post->ID, '_clms_chapter_duration', true);
    $is_preview = get_post_meta($post->ID, '_clms_chapter_preview', true);

    echo '<p><label>Estimated Duration:</label><br>';
    echo '<input type="text" name="clms_chapter_duration" value="' . esc_attr($duration) . '" placeholder="e.g. 30 mins" style="width:100%;"></p>';

    echo '<p><label><input type="checkbox" name="clms_chapter_preview" value="1" ' . checked($is_preview, '1', false) . '> Allow Free Preview</label></p>';
}

// Save Chapter Meta
function clms_save_chapter_meta($post_id)
{
    // Assign to Course
    if (isset($_POST['clms_parent_course'])) {
        update_post_meta($post_id, '_clms_parent_course', intval($_POST['clms_parent_course']));
    }

    // Settings
    if (isset($_POST['clms_chapter_duration'])) {
        update_post_meta($post_id, '_clms_chapter_duration', sanitize_text_field($_POST['clms_chapter_duration']));
    }
    $is_preview = isset($_POST['clms_chapter_preview']) ? '1' : '';
    update_post_meta($post_id, '_clms_chapter_preview', $is_preview);
}
add_action('save_post_chapter', 'clms_save_chapter_meta');
add_action('save_post_quiz', 'clms_save_chapter_meta');
add_action('save_post_question', 'clms_save_chapter_meta');

// Add Quiz Meta Boxes
function clms_add_quiz_meta_boxes()
{
    // Questions list (sortable)
    add_meta_box(
        'clms_quiz_questions',
        'Quiz Questions',
        'clms_render_quiz_questions_box',
        'quiz',
        'normal',
        'high'
    );

    // Quiz Settings
    add_meta_box(
        'clms_quiz_settings',
        'Quiz Settings',
        'clms_render_quiz_settings_box',
        'quiz',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'clms_add_quiz_meta_boxes');

// Render Quiz Questions Meta Box
function clms_render_quiz_questions_box($post)
{
    // Get saved question order
    $question_order = get_post_meta($post->ID, '_clms_quiz_questions', true);
    if (!is_array($question_order)) $question_order = [];

    // Get all questions linked to this quiz
    $questions = get_posts([
        'post_type'   => 'question',
        'meta_key'    => '_clms_parent_quiz',
        'meta_value'  => $post->ID,
        'numberposts' => -1
    ]);

    // Reorder questions according to saved order
    usort($questions, function ($a, $b) use ($question_order) {
        $pos_a = array_search($a->ID, $question_order);
        $pos_b = array_search($b->ID, $question_order);
        return ($pos_a === false ? PHP_INT_MAX : $pos_a) - ($pos_b === false ? PHP_INT_MAX : $pos_b);
    });

    echo '<ul id="clms-quiz-questions-list" style="margin:0; padding:0; list-style:none;">';
    if (!empty($questions)) {
        foreach ($questions as $q) {
            echo '<li class="clms-quiz-question" data-id="' . esc_attr($q->ID) . '" style="padding:8px; margin:4px 0; background:#f1f1f1; border:1px solid #ddd; cursor:move;">';
            echo esc_html($q->post_title);
            echo '</li>';
        }
    } else {
        echo '<li>No questions assigned yet.</li>';
    }
    echo '</ul>';

    echo '<input type="hidden" id="clms_quiz_questions" name="clms_quiz_questions" value="' . esc_attr(implode(',', $question_order)) . '">';

?>
    <script>
        jQuery(document).ready(function($) {
            $('#clms-quiz-questions-list').sortable({
                update: function() {
                    var order = [];
                    $('#clms-quiz-questions-list .clms-quiz-question').each(function() {
                        order.push($(this).data('id'));
                    });
                    $('#clms_quiz_questions').val(order.join(','));
                }
            });
        });
    </script>
    <?php
}

// Render Quiz Settings Meta Box
function clms_render_quiz_settings_box($post)
{
    $duration = get_post_meta($post->ID, '_clms_quiz_duration', true);
    $passing_grade = get_post_meta($post->ID, '_clms_quiz_passing_grade', true);
    $attempts = get_post_meta($post->ID, '_clms_quiz_attempts', true);

    echo '<p><label>Quiz Duration (minutes):</label><br>';
    echo '<input type="number" name="clms_quiz_duration" value="' . esc_attr($duration) . '" style="width:100%;"></p>';

    echo '<p><label>Passing Grade (%):</label><br>';
    echo '<input type="number" name="clms_quiz_passing_grade" value="' . esc_attr($passing_grade) . '" min="0" max="100" style="width:100%;"></p>';

    echo '<p><label>Allowed Attempts:</label><br>';
    echo '<input type="number" name="clms_quiz_attempts" value="' . esc_attr($attempts) . '" min="1" style="width:100%;"></p>';
}

// Save Quiz Meta
function clms_save_quiz_meta($post_id)
{
    // Save question order
    if (isset($_POST['clms_quiz_questions'])) {
        $order = array_filter(array_map('intval', explode(',', $_POST['clms_quiz_questions'])));
        update_post_meta($post_id, '_clms_quiz_questions', $order);
    }

    // Save settings
    if (isset($_POST['clms_quiz_duration'])) {
        update_post_meta($post_id, '_clms_quiz_duration', intval($_POST['clms_quiz_duration']));
    }
    if (isset($_POST['clms_quiz_passing_grade'])) {
        update_post_meta($post_id, '_clms_quiz_passing_grade', intval($_POST['clms_quiz_passing_grade']));
    }
    if (isset($_POST['clms_quiz_attempts'])) {
        update_post_meta($post_id, '_clms_quiz_attempts', intval($_POST['clms_quiz_attempts']));
    }
}
add_action('save_post_quiz', 'clms_save_quiz_meta');

// Add "Assign to Quiz" box for Questions
function clms_add_question_meta_boxes()
{
    add_meta_box(
        'clms_question_quiz',
        'Assign to Quiz',
        'clms_render_question_meta_box',
        'question',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'clms_add_question_meta_boxes');

function clms_render_question_meta_box($post)
{
    $quizzes = get_posts(['post_type' => 'quiz', 'numberposts' => -1]);
    $selected = get_post_meta($post->ID, '_clms_parent_quiz', true);

    echo '<select name="clms_parent_quiz" style="width:100%;">';
    echo '<option value="">— Select Quiz —</option>';
    foreach ($quizzes as $quiz) {
        echo '<option value="' . esc_attr($quiz->ID) . '" ' . selected($selected, $quiz->ID, false) . '>';
        echo esc_html($quiz->post_title);
        echo '</option>';
    }
    echo '</select>';
}

function clms_save_question_meta($post_id)
{
    if (isset($_POST['clms_parent_quiz'])) {
        update_post_meta($post_id, '_clms_parent_quiz', intval($_POST['clms_parent_quiz']));
    }
}
add_action('save_post_question', 'clms_save_question_meta');

// Add Question Options Meta Box
function clms_add_question_options_meta_box()
{
    add_meta_box(
        'clms_question_options',
        'Question Options',
        'clms_render_question_options_box',
        'question',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'clms_add_question_options_meta_box');

// Render Question Options Box
function clms_render_question_options_box($post)
{
    $type    = get_post_meta($post->ID, '_clms_question_type', true);
    $options = get_post_meta($post->ID, '_clms_question_options', true);
    $answer  = get_post_meta($post->ID, '_clms_question_answer', true);
    $marks   = get_post_meta($post->ID, '_clms_question_marks', true);

    if (!is_array($options)) {
        $options = ['']; // at least one option
    }

    // Question Type Dropdown
    echo '<p><label><strong>Question Type:</strong></label><br>';
    echo '<select name="clms_question_type" style="width:100%">';
    $types = [
        'mcq'       => 'Multiple Choice',
        'truefalse' => 'True / False',
        'fillblank' => 'Fill in the Blank'
    ];
    foreach ($types as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($type, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></p>';

    // MCQ Options UI
    echo '<div id="clms-mcq-options" style="' . ($type === 'mcq' ? '' : 'display:none;') . '">';
    echo '<p><strong>Options (drag to reorder):</strong></p>';
    echo '<ul id="clms-options-wrapper" class="clms-sortable">';

    foreach ($options as $i => $opt) {
    ?>
        <li class="clms-option-item" style="margin-bottom:8px; cursor:move; background:#f9f9f9; padding:6px; border:1px solid #ddd;">
            <span class="dashicons dashicons-move" style="margin-right:6px; vertical-align:middle;"></span>
            <input type="text" name="clms_question_options[]" value="<?php echo esc_attr($opt); ?>" style="width:65%" placeholder="Option <?php echo $i + 1; ?>">
            <label><input type="radio" name="clms_question_answer" value="<?php echo $i; ?>" <?php checked((int)$answer, $i); ?>> Correct</label>
            <button type="button" class="button clms-remove-option">Remove</button>
        </li>
    <?php
    }

    echo '</ul>';
    echo '<button type="button" class="button button-primary" id="clms-add-option">+ Add Option</button>';
    echo '</div>';

    // True/False Options
    echo '<div id="clms-truefalse-options" style="' . ($type === 'truefalse' ? '' : 'display:none;') . '">';
    echo '<p><strong>Select Correct Answer:</strong></p>';
    echo '<label><input type="radio" name="clms_question_answer" value="true" ' . checked($answer, 'true', false) . '> True</label><br>';
    echo '<label><input type="radio" name="clms_question_answer" value="false" ' . checked($answer, 'false', false) . '> False</label>';
    echo '</div>';

    // Fill-in-the-Blank
    echo '<div id="clms-fillblank-options" style="' . ($type === 'fillblank' ? '' : 'display:none;') . '">';
    echo '<p><strong>Correct Answer:</strong></p>';
    echo '<input type="text" name="clms_question_answer" value="' . esc_attr($answer) . '" style="width:100%" placeholder="Type correct answer">';
    echo '</div>';

    // Marks
    echo '<p><label><strong>Marks for this Question:</strong></label><br>';
    echo '<input type="number" name="clms_question_marks" value="' . esc_attr($marks) . '" style="width:80px" min="1" placeholder="e.g. 5"> points</p>';
    ?>

    <script>
        jQuery(document).ready(function($) {
            // Toggle between question types
            $('select[name="clms_question_type"]').on('change', function() {
                var val = $(this).val();
                $('#clms-mcq-options, #clms-truefalse-options, #clms-fillblank-options').hide();
                if (val === 'mcq') $('#clms-mcq-options').show();
                if (val === 'truefalse') $('#clms-truefalse-options').show();
                if (val === 'fillblank') $('#clms-fillblank-options').show();
            });

            // Make options sortable
            $('#clms-options-wrapper').sortable({
                placeholder: "ui-state-highlight",
                stop: function(event, ui) {
                    // reindex values after reorder
                    $('#clms-options-wrapper .clms-option-item').each(function(i) {
                        $(this).find('input[type="text"]').attr('placeholder', 'Option ' + (i + 1));
                        $(this).find('input[type="radio"]').val(i);
                    });
                }
            }).disableSelection();

            // Add new option dynamically
            $('#clms-add-option').on('click', function() {
                var count = $('#clms-options-wrapper .clms-option-item').length;
                var newOption = `
                <li class="clms-option-item" style="margin-bottom:8px; cursor:move; background:#f9f9f9; padding:6px; border:1px solid #ddd;">
                    <span class="dashicons dashicons-move" style="margin-right:6px; vertical-align:middle;"></span>
                    <input type="text" name="clms_question_options[]" style="width:65%" placeholder="Option ${count+1}">
                    <label><input type="radio" name="clms_question_answer" value="${count}"> Correct</label>
                    <button type="button" class="button clms-remove-option">Remove</button>
                </li>`;
                $('#clms-options-wrapper').append(newOption);
            });

            // Remove option
            $(document).on('click', '.clms-remove-option', function() {
                $(this).closest('.clms-option-item').remove();
                // reindex after removal
                $('#clms-options-wrapper .clms-option-item').each(function(i) {
                    $(this).find('input[type="text"]').attr('placeholder', 'Option ' + (i + 1));
                    $(this).find('input[type="radio"]').val(i);
                });
            });
        });
    </script>
    <style>
        #clms-options-wrapper .ui-state-highlight {
            height: 40px;
            border: 2px dashed #0073aa;
            background: #f0f8ff;
        }
    </style>
<?php
}

// Save function (same as before, works with drag/drop + dynamic add/remove)
function clms_save_question_options($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'question') return;

    $type = isset($_POST['clms_question_type']) ? sanitize_text_field($_POST['clms_question_type']) : '';
    update_post_meta($post_id, '_clms_question_type', $type);

    // MCQ
    if ($type === 'mcq' && isset($_POST['clms_question_options'])) {
        $raw_opts = (array)$_POST['clms_question_options'];
        $opts = [];
        foreach ($raw_opts as $opt) {
            $opt = sanitize_text_field($opt);
            if ($opt !== '') $opts[] = $opt;
        }
        update_post_meta($post_id, '_clms_question_options', $opts);

        $ans_index = isset($_POST['clms_question_answer']) ? intval($_POST['clms_question_answer']) : 0;
        if ($ans_index < 0 || $ans_index >= count($opts)) $ans_index = 0;
        update_post_meta($post_id, '_clms_question_answer', $ans_index);
    }

    // True/False
    if ($type === 'truefalse' && isset($_POST['clms_question_answer'])) {
        update_post_meta(
            $post_id,
            '_clms_question_answer',
            $_POST['clms_question_answer'] === 'true' ? 'true' : 'false'
        );
    }

    // Fill in the Blank
    if ($type === 'fillblank' && isset($_POST['clms_question_answer'])) {
        update_post_meta($post_id, '_clms_question_answer', sanitize_text_field($_POST['clms_question_answer']));
    }

    // Marks
    if (isset($_POST['clms_question_marks'])) {
        update_post_meta($post_id, '_clms_question_marks', intval($_POST['clms_question_marks']));
    }
}
add_action('save_post_question', 'clms_save_question_options');

// Register Certificate CPT
function clms_register_certificate_cpt()
{
    $labels = array(
        'name' => 'Certificates',
        'singular_name' => 'Certificate',
        'add_new' => 'Add New Certificate',
        'add_new_item' => 'Add New Certificate',
        'edit_item' => 'Edit Certificate',
        'new_item' => 'New Certificate',
        'view_item' => 'View Certificate',
        'search_items' => 'Search Certificates',
        'not_found' => 'No certificates found',
        'not_found_in_trash' => 'No certificates found in Trash',
        'menu_name' => 'Certificates'
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'clms_dashboard',
        'supports' => array('title', 'thumbnail'),
        'menu_icon' => 'dashicons-awards',
    );

    register_post_type('clms_certificate', $args);
}
add_action('init', 'clms_register_certificate_cpt');

// Add certificate settings
function clms_certificate_metabox()
{
    add_meta_box(
        'clms_certificate_settings',
        'Certificate Settings',
        'clms_certificate_settings_callback',
        'clms_certificate',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'clms_certificate_metabox');

function clms_certificate_settings_callback($post)
{
    $bg = get_post_meta($post->ID, '_clms_certificate_bg', true);
?>
    <p><strong>Certificate Background</strong></p>
    <input type="text" name="clms_certificate_bg" value="<?php echo esc_attr($bg); ?>" style="width:80%;" />
    <button class="button upload-bg">Upload</button>
    <script>
        jQuery(document).ready(function($) {
            $('.upload-bg').click(function(e) {
                e.preventDefault();
                var uploader = wp.media({
                    title: 'Select Certificate Background',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = uploader.state().get('selection').first().toJSON();
                    $('input[name="clms_certificate_bg"]').val(attachment.url);
                }).open();
            });
        });
    </script>
<?php
}

// Save meta
function clms_save_certificate_meta($post_id)
{
    if (isset($_POST['clms_certificate_bg'])) {
        update_post_meta($post_id, '_clms_certificate_bg', sanitize_text_field($_POST['clms_certificate_bg']));
    }
}
add_action('save_post_clms_certificate', 'clms_save_certificate_meta');


// Shortcode: Display Courses with fallback image
function clms_courses_shortcode()
{
    $courses = get_posts(['post_type' => 'course', 'posts_per_page' => -1]);
    ob_start();
    echo '<div class="clms-courses">';
    foreach ($courses as $course) {
        echo '<div class="clms-course">';

        if (has_post_thumbnail($course->ID)) {
            echo get_the_post_thumbnail($course->ID, 'medium');
        } else {
            // Fallback image from plugin assets
            $fallback = plugin_dir_url(__FILE__) . 'assets/fallback-course.jpg';
            echo '<img src="' . esc_url($fallback) . '" alt="Course Image" class="clms-fallback-img" />';
        }

        echo '<h2>' . esc_html($course->post_title) . '</h2>';
        echo '<p>' . wp_trim_words($course->post_content, 20) . '</p>';
        echo '<a class="btn" href="' . get_permalink($course->ID) . '">View Course</a>';
        echo '</div>';
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('lms_courses', 'clms_courses_shortcode');

// Shortcode: Display Quiz with Questions
function clms_quiz_shortcode($atts)
{
    $atts = shortcode_atts([
        'id' => '', // Quiz ID
    ], $atts);

    if (!$atts['id']) return '<p>No quiz selected.</p>';

    $quiz_id = intval($atts['id']);

    $questions = get_posts([
        'post_type'   => 'question',
        'numberposts' => -1,
        'meta_key'    => '_clms_parent_quiz',
        'meta_value'  => $quiz_id,
    ]);

    if (!$questions) return '<p>No questions found for this quiz.</p>';

    ob_start();
    echo '<form class="clms-quiz-form" data-quiz-id="' . esc_attr($quiz_id) . '">';

    foreach ($questions as $q) {
        $type    = get_post_meta($q->ID, '_clms_question_type', true);
        $options = get_post_meta($q->ID, '_clms_question_options', true);
        $marks   = get_post_meta($q->ID, '_clms_question_marks', true);

        echo '<div class="clms-question" style="margin-bottom:20px;">';
        echo '<h4>' . esc_html($q->post_title) . ' (' . intval($marks) . ' pts)</h4>';

        // Render inputs
        if ($type === 'mcq' && is_array($options)) {
            foreach ($options as $opt) {
                echo '<label><input type="radio" name="question_' . esc_attr($q->ID) . '" value="' . esc_attr($opt) . '"> ' . esc_html($opt) . '</label><br>';
            }
        } elseif ($type === 'truefalse') {
            echo '<label><input type="radio" name="question_' . esc_attr($q->ID) . '" value="true"> True</label><br>';
            echo '<label><input type="radio" name="question_' . esc_attr($q->ID) . '" value="false"> False</label>';
        } elseif ($type === 'fillblank') {
            echo '<input type="text" name="question_' . esc_attr($q->ID) . '" placeholder="Your answer">';
        }

        echo '</div>';
    }

    echo '<button type="submit">Submit Quiz</button>';
    echo '</form>';
    echo '<div id="clms-quiz-result"></div>';

?>
    <script>
        jQuery(document).ready(function($) {
            $('.clms-quiz-form').on('submit', function(e) {
                e.preventDefault();

                var quizForm = $(this);
                var quizID = quizForm.data('quiz-id');
                var formData = quizForm.serializeArray();

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'clms_submit_quiz',
                    quiz_id: quizID,
                    answers: formData
                }, function(response) {
                    $('#clms-quiz-result').html(response);
                });
            });
        });
    </script>
<?php

    return ob_get_clean();
}
add_shortcode('lms_quiz', 'clms_quiz_shortcode');

// Handle Quiz Submission
function clms_submit_quiz()
{
    if (!isset($_POST['quiz_id'], $_POST['answers'])) {
        wp_send_json_error('Invalid submission.');
    }

    $quiz_id = intval($_POST['quiz_id']);
    $answers = $_POST['answers'];

    $user_answers = [];
    foreach ($answers as $ans) {
        $user_answers[$ans['name']] = sanitize_text_field($ans['value']);
    }

    // Fetch quiz questions
    $questions = get_posts([
        'post_type'   => 'question',
        'numberposts' => -1,
        'meta_key'    => '_clms_parent_quiz',
        'meta_value'  => $quiz_id,
    ]);

    $total_score = 0;
    $earned_score = 0;

    ob_start();
    echo '<h3>Quiz Results</h3>';
    echo '<ul>';

    foreach ($questions as $q) {
        $correct = get_post_meta($q->ID, '_clms_question_answer', true);
        $marks   = intval(get_post_meta($q->ID, '_clms_question_marks', true));
        $total_score += $marks;

        $field_name = 'question_' . $q->ID;
        $user_answer = isset($user_answers[$field_name]) ? $user_answers[$field_name] : '';

        $is_correct = (strtolower(trim($user_answer)) === strtolower(trim($correct)));

        echo '<li><strong>' . esc_html($q->post_title) . ':</strong> ';
        if ($is_correct) {
            echo '<span style="color:green;">Correct</span> (+' . $marks . ' pts)';
            $earned_score += $marks;
        } else {
            echo '<span style="color:red;">Wrong</span>';
            echo ' (Your Answer: ' . esc_html($user_answer) . ', Correct: ' . esc_html($correct) . ')';
        }
        echo '</li>';
    }

    echo '</ul>';
    echo '<h4>Total Score: ' . $earned_score . ' / ' . $total_score . '</h4>';

    wp_send_json_success(ob_get_clean());
}
add_action('wp_ajax_clms_submit_quiz', 'clms_submit_quiz');
add_action('wp_ajax_nopriv_clms_submit_quiz', 'clms_submit_quiz');

// Enqueue assets
function clms_enqueue_assets()
{
    wp_enqueue_style('clms-style', plugin_dir_url(__FILE__) . 'assets/clms-frontend.css');
    wp_enqueue_script('clms-script', plugin_dir_url(__FILE__) . 'assets/clms-frontend.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'clms_enqueue_assets');

?>