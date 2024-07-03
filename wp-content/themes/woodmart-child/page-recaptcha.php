<?php
/* Template Name: reCAPTCHA Verification */

get_header();
?>

<div id="recaptcha-container" class="recaptcha-container">
    <h2>Please verify you are a human</h2>
    <div class="g-recaptcha" data-sitekey="6Ld__PYpAAAAADrwQ6y5jlTH5HX06KTe5hI6uHOf" data-callback="recaptchaCallback"></div>
</div>
<script>
function recaptchaCallback() {
    var response = grecaptcha.getResponse();
    if (response.length !== 0) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo esc_url(admin_url('admin-post.php')); ?>", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var result = JSON.parse(xhr.responseText);
                if (result.success) {
                    window.location.href = result.redirect_url;
                } else {
                    alert("Verification failed. Please try again.");
                }
            }
        };
        xhr.send("action=verify_recaptcha&g-recaptcha-response=" + response);
    } else {
        alert('reCAPTCHA validation failed. Please try again.');
    }
}
</script>

<?php
get_footer();
?>
