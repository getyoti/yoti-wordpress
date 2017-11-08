<?php
/**
 * @var ActivityDetails $profile
 * @var array $dbProfile
 */

// Display these fields
use Yoti\ActivityDetails;

$currentUser = wp_get_current_user();
$isAdmin = in_array('administrator', $currentUser->roles, TRUE);
$userId = (!empty($_GET['user_id'])) ? $_GET['user_id'] : NULL;

if ($profile)
{
    echo '<h2>' . __('Yoti User Profile') . '</h2>';
    echo '<table class="form-table">';

    foreach (YotiHelper::$profileFields as $param => $label)
    {
        $value = $profile->getProfileAttribute($param);
        if ($param === ActivityDetails::ATTR_SELFIE)
        {
            $selfieFullPath = YotiHelper::uploadDir() . "/{$dbProfile['selfie_filename']}";
            if ($dbProfile['selfie_filename'] && file_exists($selfieFullPath))
            {
                $selfieUrl = site_url('wp-login.php') . '?yoti-select=1&action=bin-file&field=selfie' . ($isAdmin ? "&user_id=$userId" : '');
                $value = '<img src="' . $selfieUrl . '" width="100" />';
            }
            else
            {
                $value = '';
            }
        }
        echo '<tr><th><label>' . esc_html($label) . '</label></th>';
        echo '<td>' . ($value ? $value : '<i>(empty)</i>') . '</td></tr>';
    }

    if (!$userId || $currentUser->ID === $userId || !$isAdmin)
    {
        echo '<tr><th></th>';
        echo '<td>' . YotiButton::render($_SERVER['REQUEST_URI']) . '</td></tr>';
    }
    echo '</table>';
}
