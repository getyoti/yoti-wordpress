<?php

use Yoti\WP\Button;
use Yoti\WP\Config;

defined('ABSPATH') or die();
/**
 * @var array $args
 * @var string $title
 * @var Config $config
 * @var array $instance
 */
?>
<?php echo $args['before_widget'] ?: ''; ?>
<?php
// Apply widget title html
if (!empty($title)) {
    echo $args['before_title'] ?: '';
    esc_html_e($title);
    echo $args['after_title'] ?: '';
}
?>

<?php if ($config->getClientSdkId() !== null && $config->getPemContent() !== null) { ?>
    <?php Button::render(null, true, $instance); ?>
<?php } else { ?>
    <strong>Yoti not configured.</strong>
<?php } ?>

<?php echo $args['after_widget'] ?: ''; ?>