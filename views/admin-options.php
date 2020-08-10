<?php

use Yoti\WP\Constants;

defined('ABSPATH') or die();

/**
 * @var array $data
 * @var string $updateMessage
 * @var array $errors
 */
?>
<div class="wrap">
    <h1>Yoti Settings</h1>
    <p>
        You need to first create a Yoti App at 
        <a href="<?php esc_attr_e(Constants::YOTI_HUB_URL); ?>" target="_blank">Yoti Hub</a>.
    </p>
    <p>
        Note: On the Yoti Hub the application domain should be set to 
        <code>
            <?php esc_html_e(site_url('', 'https')); ?></code>, <br>And the scenario callback URL should be set 
            to: <code><?php esc_html_e(site_url('wp-login.php?yoti-select=1&action=link', 'https')); ?>
        </code>
    </p>
    <p>
        Warning: User IDs provided by Yoti are unique to each Yoti Application. Using a different Yoti 
        Application means you will receive a different Yoti User ID for all of your users.
    </p>
    <?php if ($updateMessage) { ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <p><strong><?php esc_html_e($updateMessage); ?></strong></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
    <?php } ?>
    <?php if ($errors) { ?>
        <div id="setting-error-settings_updated" class="error settings-error notice is-dismissible">
            <?php foreach ($errors as $err) { ?>
                <p><strong><?php esc_html_e($err); ?></strong></p>
            <?php } ?>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
    <?php } ?>
    <form method="post" enctype="multipart/form-data" action="<?php esc_attr_e($_SERVER['REQUEST_URI']); ?>">
        <?php wp_nonce_field('yoti_verify', 'yoti_verify'); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="yoti_app_id">Yoti App ID</label></th>
                    <td>
                        <input
                            name="yoti_app_id"
                            type="text"
                            id="yoti_app_id"
                            value="<?php esc_attr_e($data['yoti_app_id']); ?>"
                            class="regular-text code"
                        />
                        <p><code>Yoti App ID</code> is a unique identifier for your specific application.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="yoti_scenario_id">Yoti Scenario ID</label></th>
                    <td>
                        <input
                            name="yoti_scenario_id"
                            type="text"
                            id="yoti_scenario_id"
                            value="<?php esc_attr_e($data['yoti_scenario_id']); ?>"
                            class="regular-text code"
                        />
                        <p>
                            <code>Yoti Scenario ID</code> identifies the attributes associated with your 
                            Yoti application. This value can be found on your application page in Yoti Hub.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="yoti_sdk_id">Yoti Client SDK ID</label></th>
                    <td>
                        <input
                            name="yoti_sdk_id"
                            type="text"
                            id="yoti_sdk_id"
                            value="<?php esc_attr_e($data['yoti_sdk_id']); ?>"
                            class="regular-text code"
                        />
                        <p>
                            <code>Yoti Client SDK ID</code> identifies your Yoti Hub application.
                            This value can be found in the Hub, within your application section, 
                            in the keys tab.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="yoti_company_name">Company Name</label></th>
                    <td>
                        <input
                            name="yoti_company_name"
                            type="text"
                            id="yoti_company_name"
                            value="<?php esc_attr_e($data['yoti_company_name']); ?>"
                            class="regular-text code"
                        />
                        <p>
                            <code>Company Name</code> to replace WordPress wording in the 
                            warning message on the login form.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="yoti_pem">Yoti PEM File</label></th>
                    <td>
                        <?php if (!empty($data['yoti_pem']['name'])) { ?>
                            <div class="pem-file">
                                <code>
                                    <strong>Current file:</strong> <?php esc_html_e($data['yoti_pem']['name']); ?>
                                </code>
                                <label>
                                    <input 
                                        type="checkbox"
                                        name="yoti_delete_pem"
                                        value="1"
                                        <?php checked(!empty($data['yoti_delete_pem'])); ?>
                                    /> Delete this PEM file
                                </label>
                            </div>
                        <?php } ?>
                        <input name="yoti_pem" type="file" id="yoti_pem" />
                        <p>
                            <code>Yoti PEM File</code> is the application pem file. 
                            It can be downloaded only once from the Keys tab in your Yoti Hub.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="yoti_only_existing"
                                value="1"
                                <?php checked(!empty($data['yoti_only_existing'])); ?>
                            /> Only allow existing WordPress users to link their Yoti account
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="yoti_user_email"
                                value="1"
                                <?php checked(!empty($data['yoti_user_email'])); ?>
                            /> Attempt to link Yoti email address with WordPress account for first time users
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="yoti_age_verification"
                                value="1"
                                <?php checked(!empty($data['yoti_age_verification'])); ?>
                            /> Prevent users who have not passed age verification to access your site
                        </label>
                        <p>
                            (Requires Age verify condition to be set in 
                            the <a href="<?php esc_attr_e(Constants::YOTI_HUB_URL); ?>" target="_blank">Yoti Hub</a>)
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />
        </p>
    </form>
</div>