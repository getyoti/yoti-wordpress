<?php
/**
 * @var array $data
 * @var string $updateMessage
 * @var array $errors
 */
// Check if linking users by email address is set
$useEmailAddressCheckbox = !empty($data['yoti_user_email']) ? 'checked="checked"' : '';
// Check if linking existing users only is set
$onlyExistingUserCheckbox = !empty($data['yoti_only_existing']) ? 'checked="checked"' : '';
$ageVerificationCheckbox = isset($data['yoti_age_verification']) ? 'checked="checked"' : '';
$dashboardLink = '<a href="' . \Yoti\YotiClient::DASHBOARD_URL . '" target="_blank">Yoti Dashboard</a>';
?>
<div class="wrap">
    <h1>Yoti Settings</h1>
    <p>You need to first create a Yoti App at <?php echo $dashboardLink ?>.</p>
    <p>Note: On the Yoti Dashboard the application domain should be set to <code><?php echo site_url('', 'https'); ?></code>, <br>And the scenario callback URL should be set to: <code><?php echo site_url('wp-login.php?yoti-select=1&action=link', 'https'); ?></code></p>
    <p>Warning: User IDs provided by Yoti are unique to each Yoti Application. Using a different Yoti Application means you will receive a different Yoti User ID for all of your users.</p>
    <?php
    if ($updateMessage) {
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">' .
            '<p><strong>' . $updateMessage . '</strong></p>' .
            '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' .
            '</div>';
    }

    // if init error
    if ($errors) {
        echo '<div id="setting-error-settings_updated" class="error settings-error notice is-dismissible">';
        foreach ($errors as $err) {
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
                  <input name="yoti_app_id" type="text" id="yoti_app_id" value="<?php echo esc_attr($data['yoti_app_id']); ?>" class="regular-text code" />
                <p><code>Yoti App ID</code> is a unique identifier for your specific application.</p>
              </td>
          </tr>
          <tr>
              <th scope="row"><label for="yoti_scenario_id">Yoti Scenario ID</label></th>
              <td>
                  <input name="yoti_scenario_id" type="text" id="yoti_scenario_id" value="<?php echo esc_attr($data['yoti_scenario_id']); ?>" class="regular-text code" />
                <p><code>Yoti Scenario ID</code> is used to render the inline QR code.</p>
              </td>
          </tr>
          <tr>
              <th scope="row"><label for="yoti_sdk_id">Yoti SDK ID</label></th>
              <td>
                  <input name="yoti_sdk_id" type="text" id="yoti_sdk_id" value="<?php echo esc_attr($data['yoti_sdk_id']); ?>" class="regular-text code" />
                  <p><code>Yoti SDK ID</code> is the SDK identifier generated by Yoti Dashboard.</p>
              </td>
          </tr>
          <tr>
              <th scope="row"><label for="yoti_sdk_id">Company Name</label></th>
              <td>
                  <input name="yoti_company_name" type="text" id="yoti_company_name" value="<?php echo esc_attr($data['yoti_company_name']); ?>" class="regular-text code" />
                  <p><code>Company Name</code> to replace WordPress wording in the warning message on the login form.</p>
              </td>
          </tr>
          <tr>
              <th scope="row"><label for="yoti_qr_type">QR Type</label></th>
              <td>
                  <select name="yoti_qr_type" id="yoti_qr_type" value="<?php echo esc_attr($data['yoti_qr_type']); ?>" class="regular-text code">
                  <?php foreach (YotiAdmin::qrTypes() as $key => $value) { ?>
                    <option <?php echo $key == $data['yoti_qr_type'] ? 'selected="selected" ' : '' ?>value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                  <?php } ?>
                  </select>
              </td>
          </tr>
          <tr>
              <th scope="row"><label for="yoti_pem">Yoti PEM File</label></th>
              <td>
                  <?php
                  if (!empty($data['yoti_pem']['name'])) {
                      $checked = (!empty($data['yoti_delete_pem']) ? ' checked="checked"' : '');
                      echo '<div class="pem-file">' .
                          '<code><strong>Current file:</strong> ' . esc_html($data['yoti_pem']['name']) . '</code>' .
                          '<label><input type="checkbox" name="yoti_delete_pem" value="1"' . $checked . ' /> Delete this PEM file</label>' .
                          '</div>';
                  }
                  ?>
                <input name="yoti_pem" type="file" id="yoti_pem" />
                  <p><code>Yoti PEM File</code> is the application pem file. It can be downloaded only once from the Keys tab in your Yoti Dashboard.</p>
              </td>
          </tr>
          <tr>
            <th scope="row"></th>
            <td>
              <label><input type="checkbox" name="yoti_only_existing" value="1"<?php echo $onlyExistingUserCheckbox ?> /> Only allow existing WordPress users to link their Yoti account</label>
            </td>
          </tr>
          <tr>
            <th scope="row"></th>
            <td>
              <label><input type="checkbox" name="yoti_user_email" value="1" <?php echo $useEmailAddressCheckbox ?> /> Attempt to link Yoti email address with WordPress account for first time users</label>
            </td>
          </tr>
          <tr>
              <th scope="row"></th>
              <td>
                  <label><input type="checkbox" name="yoti_age_verification" value="1" <?php echo $ageVerificationCheckbox ?> /> Prevent users who have not passed age verification to access your site</label>
                  <p>(Requires Age verify condition to be set in the <?php echo $dashboardLink ?>)</p>
              </td>
          </tr>
          </tbody>
      </table>
      <p class="submit">
          <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />
      </p>
  </form>
</div>