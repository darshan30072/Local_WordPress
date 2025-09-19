<?php
get_header();

if (have_posts()) :
    while (have_posts()) : the_post(); ?>

        <div class="clms-quiz-single">
            <h1><?php the_title(); ?></h1>
            <div class="clms-quiz-content">
                <?php the_content(); ?>
            </div>

            <h2>Quiz Questions</h2>

            <?php
            // Get Questions linked to this Quiz
            $questions = get_posts([
                'post_type'   => 'question',
                'meta_key'    => '_clms_parent_quiz',
                'meta_value'  => get_the_ID(),
                'numberposts' => -1
            ]);

            if ($questions) {
                // Quiz settings
                $allowed_attempts = intval(get_post_meta(get_the_ID(), '_clms_quiz_attempts', true));
                $passing_grade    = intval(get_post_meta(get_the_ID(), '_clms_quiz_passing_grade', true));
                if (!$passing_grade) $passing_grade = 50;

                $user_id = get_current_user_id();
                $attempts_data = ($user_id) ? get_user_meta($user_id, '_clms_quiz_attempts', true) : [];
                if (!is_array($attempts_data)) $attempts_data = [];
                $user_attempts = isset($attempts_data[get_the_ID()]) ? count($attempts_data[get_the_ID()]) : 0;

                // Save attempt function
                function clms_save_quiz_attempt($user_id, $quiz_id, $score, $total_score, $percentage) {
                    $attempts = get_user_meta($user_id, '_clms_quiz_attempts', true);
                    if (!is_array($attempts)) $attempts = [];

                    if (!isset($attempts[$quiz_id])) $attempts[$quiz_id] = [];

                    $attempts[$quiz_id][] = [
                        'score'      => $score,
                        'total'      => $total_score,
                        'percentage' => $percentage,
                        'time'       => current_time('mysql'),
                    ];

                    update_user_meta($user_id, '_clms_quiz_attempts', $attempts);
                    return $attempts; // return updated for immediate use
                }

                // Enforce attempt limits
                if ($allowed_attempts && $user_attempts >= $allowed_attempts) {
                    echo '<p style="color:red;font-weight:bold;">❌ You have reached the maximum number of attempts for this quiz.</p>';
                } else {
                    $show_form = true;

                    // Check if form submitted
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $total_score = 0;
                        $user_score  = 0;

                        echo '<div class="clms-results" id="clms-results">';
                        foreach ($questions as $index => $question) {
                            $type     = get_post_meta($question->ID, '_clms_question_type', true);
                            $options  = get_post_meta($question->ID, '_clms_question_options', true);
                            $marks    = intval(get_post_meta($question->ID, '_clms_question_marks', true));
                            $answer   = get_post_meta($question->ID, '_clms_question_answer', true);
                            $user_ans = isset($_POST['q'.$question->ID]) ? sanitize_text_field($_POST['q'.$question->ID]) : '';

                            $total_score += $marks;

                            echo '<div class="clms-question">';
                            echo '<p><strong>Q'.($index+1).':</strong> '.esc_html($question->post_title).'</p>';

                            $is_correct = false;
                            $correct_display = '';

                            if ($type === 'mcq' && is_array($options)) {
                                $correct_index   = intval($answer);
                                $correct_answer  = $options[$correct_index] ?? '';
                                $correct_display = $correct_answer;
                                if ($user_ans !== '' && $user_ans === $correct_answer) $is_correct = true;
                            } elseif ($type === 'truefalse') {
                                $correct_display = ucfirst($answer);
                                if ($user_ans === $answer) $is_correct = true;
                            } elseif ($type === 'fillblank') {
                                $correct_display = $answer;
                                if (strtolower(trim($user_ans)) === strtolower(trim($answer))) $is_correct = true;
                            }

                            if ($is_correct) {
                                echo '<p style="color:green;">✔ Correct ('.$marks.' points)</p>';
                                $user_score += $marks;
                            } else {
                                echo '<p style="color:red;">✘ Wrong. Correct Answer: <strong>'.esc_html($correct_display).'</strong></p>';
                            }
                            echo '</div>';
                        }

                        // Calculate percentage
                        $percentage = ($total_score > 0) ? round(($user_score / $total_score) * 100) : 0;

                        echo '<h3>Your Score: '.$user_score.' / '.$total_score.' ('.$percentage.'%)</h3>';

                        if ($percentage >= $passing_grade) {
                            echo '<p style="color:green;font-weight:bold;">🎉 Congratulations! You passed.</p>';
                            $show_form = false;

                            // Show certificate button
                            $certificate_url = site_url('/certificate?quiz_id=' . get_the_ID());
                            echo '<a href="' . esc_url($certificate_url) . '" style="display:inline-block;margin-top:10px;padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:4px;">🎓 Generate Certificate</a>';
                        } else {
                            echo '<p style="color:red;font-weight:bold;">❌ You did not pass.</p>';
                            echo '<button id="clms-retake-btn" style="margin-top:10px;padding:8px 16px;">🔄 Retake Quiz</button>';
                        }

                        echo '</div>';

                        // Save attempt if user logged in
                        if ($user_id) {
                            $attempts_data = clms_save_quiz_attempt($user_id, get_the_ID(), $user_score, $total_score, $percentage);
                        }
                    }

                    // Always output form (visible or hidden)
                    echo '<form class="clms-quiz-form" id="clms-quiz-form" method="post" '.(($show_form && $_SERVER['REQUEST_METHOD'] !== 'POST') ? '' : 'style="display:none;"').'>';
                    foreach ($questions as $index => $question) {
                        $type    = get_post_meta($question->ID, '_clms_question_type', true);
                        $options = get_post_meta($question->ID, '_clms_question_options', true);
                        $marks   = intval(get_post_meta($question->ID, '_clms_question_marks', true));

                        echo '<div class="clms-question" style="margin-bottom:20px;">';
                        echo '<p><strong>Q'.($index+1).':</strong> '.esc_html($question->post_title);
                        if ($marks) echo ' <span style="color:#777;">('.$marks.' points)</span>';
                        echo '</p>';

                        if ($type === 'mcq' && is_array($options)) {
                            foreach ($options as $i => $opt) {
                                echo '<label><input type="radio" name="q'.$question->ID.'" value="'.esc_attr($opt).'"> '.esc_html($opt).'</label><br>';
                            }
                        } elseif ($type === 'truefalse') {
                            echo '<label><input type="radio" name="q'.$question->ID.'" value="true"> True</label><br>';
                            echo '<label><input type="radio" name="q'.$question->ID.'" value="false"> False</label>';
                        } elseif ($type === 'fillblank') {
                            echo '<input type="text" name="q'.$question->ID.'" placeholder="Your answer">';
                        } else {
                            echo '<p>No answer type configured.</p>';
                        }
                        echo '</div>';
                    }
                    echo '<button type="submit">Submit Quiz</button>';
                    echo '</form>';
                }

                // Show past attempts
                if ($user_id && isset($attempts_data[get_the_ID()])) {
                    echo '<h3>Your Previous Attempts:</h3><ul>';
                    foreach ($attempts_data[get_the_ID()] as $i => $attempt) {
                        echo '<li>Attempt '.($i+1).': '.$attempt['score'].'/'.$attempt['total'].' ('.$attempt['percentage'].'%) on '.$attempt['time'].'</li>';
                    }
                    echo '</ul>';
                }
            } else {
                echo '<p>No questions added yet.</p>';
            }
            ?>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const retakeBtn = document.getElementById("clms-retake-btn");
                if (retakeBtn) {
                    retakeBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        const form = document.getElementById("clms-quiz-form");
                        if (form) {
                            form.reset();
                            form.style.display = "block";
                            form.scrollIntoView({ behavior: "smooth", block: "start" });
                        }
                        const results = document.getElementById("clms-results");
                        if (results) results.style.display = "none";
                        this.style.display = "none";
                    });
                }
            });
        </script>

<?php endwhile;
endif;

get_footer();
