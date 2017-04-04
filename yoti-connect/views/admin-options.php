<?php
/**
 * @var array $data
 * @var string $updateMessage
 * @var array $errors
 */
?>
<div class="wrap">
    <h1>Yoti Connect Settings</h1>
    <p>Please create a Yoti Application on the <a href="<?php echo \Yoti\YotiClient::DASHBOARD_URL; ?>" target="_blank">Yoti Dashboard</a>.</p>
    <p>Note: On the Yoti Dashboard, under the 'Integration' tab, please set the callback URL to: <code><?php echo site_url('wp-login.php?yoti-connect=1&action=link', 'https'); ?></code></p>
    <p>Note: Once you have created your Yoti Application, navigate to the 'Keys' tab to get the IDs and file required below:</p>
    <?php
    if ($updateMessage)
    {
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">' .
            '<p><strong>' . $updateMessage . '</strong></p>' .
            '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' .
            '</div>';
    }

    // if init error
    if ($errors)
    {
        echo '<div id="setting-error-settings_updated" class="error settings-error notice is-dismissible">';
        foreach ($errors as $err)
        {
            echo '<p><strong>' . $err . '</strong></p>';
        }
        echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' .
            '</div>';
    }
    ?>

    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="yoti_app_id">Yoti App ID</label></th>
                <td>
                    <input name="yoti_app_id" type="text" id="yoti_app_id" value="<?php echo htmlspecialchars($data['yoti_app_id']); ?>" class="regular-text code" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="yoti_sdk_id">Yoti SDK ID</label></th>
                <td>
                    <input name="yoti_sdk_id" type="text" id="yoti_sdk_id" value="<?php echo htmlspecialchars($data['yoti_sdk_id']); ?>" class="regular-text code" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="yoti_pem">Yoti PEM File</label></th>
                <td>
                    <?php
                    if (!empty($data['yoti_pem']['name']))
                    {
                        $checked = (!empty($data['yoti_delete_pem']) ? ' checked="checked"' : '');
                        echo '<div class="pem-file">' .
                            '<code><strong>Current file:</strong> ' . htmlspecialchars($data['yoti_pem']['name']) . '</code>' .
                            '<label><input type="checkbox" name="yoti_delete_pem" value="1"' . $checked . ' /> Delete this PEM file</label>' .
                            '</div>';
                    }
                    ?>
                    <input name="yoti_pem" type="file" id="yoti_pem" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="yoti_allow_registration">Allow new users to register via Yoti Connect?</label>
                </th>
                <td>
                    <?php
                        $signupChecked = (!empty($data['yoti_allow_registration']) ? ' checked="checked"' : '');
                        echo '<input type="checkbox" name="yoti_allow_registration" value="1"' . $signupChecked . ' />';
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />
        </p>
    </form>
</div>