<?php
defined('ABSPATH') or die();
/**
 * @var array $dbProfile
 * @var bool $displayButton
 * @var int $userId
 */

// Display these fields
use Yoti\Entity\Profile;

$profileFields = YotiHelper::$profileFields;
?>
<h2><?php esc_html_e('Yoti User Profile'); ?></h2>
<table class="form-table">
<?php
foreach ($dbProfile as $attrName => $value)
{
    $label = isset($profileFields[$attrName]) ? $profileFields[$attrName] : $attrName;

    // Display selfie as an image
    if ($attrName === YotiHelper::SELFIE_FILENAME) {
        $selfieUrl = '';
        $label = $profileFields[Profile::ATTR_SELFIE];
        $selfieFileName = $dbProfile[YotiHelper::SELFIE_FILENAME];
        $selfieFullPath = YotiHelper::uploadDir() . '/' . $selfieFileName;
        if (!empty($selfieFileName) && is_file($selfieFullPath)) {
            $selfieUrl = YotiHelper::selfieUrl($userId);
        }
    }
    ?>
    <tr>
        <th><label><?php esc_html_e($label); ?></label></th>
        <td>
            <?php if ($attrName === YotiHelper::SELFIE_FILENAME && !empty($selfieUrl)) { ?>
                <img src="<?php esc_attr_e($selfieUrl); ?>" width="100" />
            <?php } elseif (!empty($value)) { ?>
                <?php esc_html_e($value); ?>
            <?php } else { ?>
                <i>(empty)</i>
            <?php } ?>
        </td>
    </tr>
<?php
}
?>
<?php if ($displayButton) { ?>
    <tr>
        <th></th>
        <td><?php YotiButton::render($_SERVER['REQUEST_URI'], FALSE, TRUE); ?></td>
    </tr>
<?php } ?>
</table>