<?php
defined('ABSPATH') or die();
/**
 * @var string $companyName
 * @var bool $noLink
 */
?>
<div style="margin: 0 0 25px 0" class="message">
    <div style="font-weight: bold; margin-bottom: 5px;">
    Warning: You are about to link your <?php esc_html_e($companyName); ?> account to your Yoti account.
    If you don't want this to happen, tick the checkbox below.
    </div>
    <input type="checkbox" id="edit-yoti-link" name="yoti_nolink" value="1" class="form-checkbox" <?php checked($noLink); ?>>
    <label class="option" for="edit-yoti-link">Don't link my Yoti account</label>
</div>
<?php wp_nonce_field('yoti_verify', 'yoti_verify'); ?>