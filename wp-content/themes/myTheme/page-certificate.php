<?php
/* Template Name: Certificate */
get_header();

$user_id   = get_current_user_id();
$quiz_id   = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$cert_id   = isset($_GET['cert_id']) ? intval($_GET['cert_id']) : 0;

// Auto-fallback certificate if cert_id not provided
if (!$cert_id) {
    $cert_query = get_posts([
        'post_type'      => 'clms_certificate',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'ASC'
    ]);
    if (!empty($cert_query)) {
        $cert_id = $cert_query[0]->ID;
    }
}

if (!$user_id || !$quiz_id || !$cert_id) {
    echo '<main style="width:80%;margin: auto;">';
    the_content();
    echo '<p style="color:red;width:50%;">Invalid request.</p>';
    echo '</main>';
    get_footer();
    exit;
}

// Get certificate background image
$bg = get_post_meta($cert_id, '_clms_certificate_bg', true);

// Get course name (parent course of quiz)
$course_id   = get_post_meta($quiz_id, '_clms_parent_course', true);
$course_name = $course_id ? get_the_title($course_id) : get_the_title($quiz_id);

// Get user info
$user_info = wp_get_current_user();
$user_name = $user_info->display_name;

// Completion date
$attempts = get_user_meta($user_id, '_clms_quiz_attempts', true);
$completion_date = '';
if (isset($attempts[$quiz_id]) && is_array($attempts[$quiz_id])) {
    $last_attempt   = end($attempts[$quiz_id]);
    $completion_date = $last_attempt['time'];
}
?>
<main>
    <div class="clms-certificate-container" style="max-width:1000px;margin:50px auto;text-align:center;position:relative;">
        <div id="clms-certificate" style="width:100%;position:relative;">
            <?php if ($bg): ?>
                <img src="<?php echo esc_url($bg); ?>" style="width:100%;height:auto;" />
            <?php endif; ?>

            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%, -50%);width:80%;text-align:center;">
                <h2 style="font-size:40px;color:#000;"><?php echo esc_html($user_name); ?></h2>
                <p style="font-size:24px;">has successfully completed</p>
                <h3 style="font-size:30px;font-weight:600;text-decoration-line: underline;"><?php echo esc_html($course_name); ?></h3>
                <p style="font-size:24px;margin-top:20px;">Date: <?php echo esc_html($completion_date); ?></p>
            </div>
        </div>

        <button id="clms-download-pdf" style="margin-top:30px;padding:10px 20px;background:#0073aa;color:#fff;border:none;border-radius:5px;cursor:pointer;">
            📥 Download PDF
        </button>
    </div>
</main>

<!-- jsPDF + html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    document.getElementById("clms-download-pdf").addEventListener("click", function() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF({
            orientation: "landscape",
            unit: "px",
            format: "a4"
        });

        const certificate = document.getElementById("clms-certificate");

        html2canvas(certificate).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const imgProps = doc.getImageProperties(imgData);
            const pdfWidth = doc.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            doc.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
            doc.save("Certificate-<?php echo esc_js($course_name); ?>.pdf");
        });
    });
</script>

<?php get_footer(); ?>