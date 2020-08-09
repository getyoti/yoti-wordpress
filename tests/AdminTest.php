<?php

namespace Yoti\WP\Test;

use Yoti\WP\Admin;

/**
 * @coversDefaultClass Yoti\WP\Admin
 *
 * @group yoti
 */
class AdminTest extends TestBase
{
    /**
     * @covers ::init
     */
    public function testFormAdmin()
    {
        wp_set_current_user($this->adminUser->ID);

        // Set untrimmed POST data to be processed by form submit handler.
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['yoti_verify'] = wp_create_nonce('yoti_verify');
        foreach ($this->config as $key => $value) {
            $_POST[$key] = $value;
            if (is_string($value)) {
                $_POST[$key] .= "\t \n\r\x0B";
            }
        }
        unset($_POST['yoti_pem']);

        ob_start();
        Admin::init();
        $html = ob_get_clean();

        // Check text input fields.
        $text_fields = [
            'yoti_app_id' => 'Yoti App ID',
            'yoti_scenario_id' => 'Yoti Scenario ID',
            'yoti_sdk_id' => 'Yoti Client SDK ID',
            'yoti_company_name' => 'Company Name',
        ];
        foreach ($text_fields as $id => $label) {
            $label_query = sprintf(
                "//label[contains(text(),'%s')][@for='%s']",
                $label,
                $id
            );
            $this->assertXpath($label_query, $html);

            $input_query = sprintf(
                "//input[@name='%s'][@id='%s'][@value='%s'][@type='text']",
                $id,
                $id,
                $this->config[$id]
            );
            $this->assertXpath($input_query, $html);
        }

        // Check checkbox fields.
        $checkbox_fields = [
            'yoti_only_existing' => 'Only allow existing WordPress users to link their Yoti account',
            'yoti_age_verification' => 'Prevent users who have not passed age verification to access your site',
            'yoti_user_email' => 'Prevent users who have not passed age verification to access your site',
        ];
        foreach ($checkbox_fields as $id => $label) {
            $label_query = sprintf("//label[contains(text(),'%s')]", $label);
            $this->assertXpath($label_query, $html);

            $checked = !empty($this->config[$id]) ? "[@checked='checked']" : "[not(@checked)]";
            $input_query = sprintf(
                "//input[@name='%s'][@type='checkbox']%s",
                $id,
                $checked
            );
            $this->assertXpath($input_query, $html);
        }
    }
}
