<?php
/**
 * @var bool $is_linked
 * @var string $service_url
 * @var string $qr_url
 * @var string $message
 * @var string $button_text
 * @var array $config
 * @var bool $from_widget
 */
?>
<div class="yoti-connect">
    <?php  if ($message) { ?>
        <div class="<?php esc_attr_e($message['type'] == 'error' ? 'error' : 'message'); ?> notice">
            <p><strong><?php esc_html_e($message['message']); ?></strong></p>
        </div>
    <?php } ?>
    <?php  if (!$is_linked) { ?>
        <span data-yoti-application-id="<?php esc_attr_e($config['yoti_app_id']); ?>"
            data-yoti-scenario-id="<?php esc_attr_e($config['yoti_scenario_id']); ?>"
            data-size="small"
            <?php if($qr_type !== 'connect') { ?>
            data-yoti-type="<?php esc_html_e($qr_type); ?>"
            <?php } ?>
        ><?php esc_html_e($button_text); ?></span>
        <script>
            <?php if (!empty($qr_url)) { ?>
            _ybg.config.qr = <?php wp_json_encode($qr_url); ?>
            <?php } ?>
            <?php if (!empty($service_url)) { ?>
            _ybg.config.service = <?php wp_json_encode($service_url); ?>
            <?php } ?>
            _ybg.init();
        </script>
    <?php } elseif($from_widget) { ?>
        <strong>Yoti</strong> Linked
    <?php } else { ?>
        <?php
        $url = site_url('wp-login.php') . '?yoti-select=1&action=unlink&redirect=' . ($redirect ? '&redirect=' . rawurlencode($redirect) : '');
        $url = wp_nonce_url($url, 'yoti_verify', 'yoti_verify');
        ?>
        <a class="yoti-connect-button"
            href="<?php esc_attr_e($url); ?>"
            onclick="return confirm('This will unlink your account from Yoti.')"
            >Unlink Yoti Account</a>
    <?php } ?>
</div>
