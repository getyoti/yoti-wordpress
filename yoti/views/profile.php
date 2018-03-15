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

// Set userId if admin user is viewing his own profile
// and the userId is NULL
if(
    $isAdmin
    && $profileUserId === $currentUser->ID
    && is_null($userId)
) {
    $userId = $profileUserId;
}

if ($dbProfile)
{
    $profileFields = YotiHelper::$profileFields;
    $profileHTML = '<h2>' . __('Yoti User Profile') . '</h2>';
    $profileHTML .= '<table class="form-table">';

    foreach ($profileFields as $param => $label)
    {
        $value = isset($dbProfile[$param]) ? $dbProfile[$param] : '' ;

        if ($param === ActivityDetails::ATTR_SELFIE)
        {
            $value = '';
            $selfieFullPath = YotiHelper::uploadDir() . "/{$dbProfile['selfie_filename']}";
            if ($dbProfile['selfie_filename'] && file_exists($selfieFullPath))
            {
                $selfieUrl = site_url('wp-login.php') . '?yoti-select=1&action=bin-file&field=selfie' . ($isAdmin ? "&user_id=$userId" : '');
                $value = '<img src="' . $selfieUrl . '" width="100" />';
            }
        }

        $profileHTML .= '<tr><th><label>' . esc_html($label) . '</label></th>';
        $profileHTML .= '<td>' . ($value ? $value : '<i>(empty)</i>') . '</td></tr>';
    }

    if (!$userId || $currentUser->ID === $userId || !$isAdmin)
    {
        $profileHTML .= '<tr><th></th>';
        $profileHTML .= '<td>' . YotiButton::render($_SERVER['REQUEST_URI']) . '</td></tr>';
    }

    $profileHTML .= '</table>';

    echo $profileHTML;
}
