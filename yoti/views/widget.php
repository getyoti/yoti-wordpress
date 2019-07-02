<?php
/**
 * @var array $args
 * @var string $title
 * @var array $config
 */
?>
<?php echo $args['before_widget'] ?: ''; ?>
<?php
// Apply widget title html
if(!empty($title)){
    echo $args['before_title'] ?: '';
    esc_html_e($title);
    echo $args['after_title'] ?: '';
}
?>
<ul><li>
    <?php if (!empty($config['yoti_sdk_id']) && !empty($config['yoti_pem']['contents'])) { ?>
        <?php YotiButton::render(NULL, TRUE, TRUE); ?>
    <?php } else { ?>
        <strong>Yoti not configured.</strong>
    <?php } ?>
</li></ul>
<?php echo $args['after_widget'] ?: ''; ?>