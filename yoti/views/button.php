<?php
/**
 * @var bool $is_linked
 * @var string $message
 * @var string $button_text
 * @var array $config
 * @var bool $from_widget
 * @var string $unlink_url
 * @var string $button_id
 */
?>
<div class="yoti-connect">
    <?php  if ($message) { ?>
        <div class="<?php esc_attr_e($message['type'] == 'error' ? 'error' : 'message'); ?> notice">
            <p><strong><?php esc_html_e($message['message']); ?></strong></p>
        </div>
    <?php } ?>
    <?php  if (!$is_linked) { ?>
        <div id="<?php esc_attr_e($button_id); ?>" class="yoti-button"></div>
        <script>
            var yotiConfig = yotiConfig || { elements: [] };
            yotiConfig.elements.push({
                "domId": "<?php esc_attr_e($button_id); ?>",
                "clientSdkId": "<?php esc_attr_e($config['yoti_sdk_id']); ?>",
                "scenarioId": "<?php esc_attr_e($config['yoti_scenario_id']); ?>",
                "button": {
                    "label": "<?php esc_attr_e($button_text); ?>"
                }
            });
        </script>
    <?php } elseif($from_widget) { ?>
        <strong>Yoti</strong> Linked
    <?php } else { ?>
        <a class="yoti-connect-button"
            href="<?php esc_attr_e($unlink_url); ?>"
            onclick="return confirm('This will unlink your account from Yoti.')"
            >Unlink Yoti Account</a>
    <?php } ?>
</div>
