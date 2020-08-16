<?php
defined('ABSPATH') or die();
/**
 * @var bool $is_linked
 * @var string $message
 * @var string $button_text
 * @var string $sdkId
 * @var string $scenarioId
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
            yotiConfig.elements.push(<?php echo wp_json_encode([
                'domId' => esc_attr($button_id),
                'clientSdkId' => esc_attr($sdkId),
                'scenarioId' => esc_attr($scenarioId),
                'button' => [
                    'label' => esc_attr($button_text),
                ],
            ]); ?>);
        </script>
    <?php } elseif ($from_widget) { ?>
        <strong>Yoti</strong> Linked
    <?php } else { ?>
        <a class="yoti-connect-button"
            href="<?php esc_attr_e($unlink_url); ?>"
            onclick="return confirm('This will unlink your account from Yoti.')"
            >Unlink Yoti Account</a>
    <?php } ?>
</div>
