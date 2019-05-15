<?php

/**
 * @coversDefaultClass YotiAdmin
 *
 * @group yoti
 */
class YotiAdminTest extends YotiTestBase
{

    /**
     * @covers ::init
     */
    public function testFormAdmin()
    {
        wp_set_current_user($this->adminUser->ID);

        ob_start();
        YotiAdmin::init();
        $html = ob_get_clean();

        // Check text input fields.
        $text_fields = [
            'yoti_app_id' => 'Yoti App ID',
            'yoti_scenario_id' => 'Yoti Scenario ID',
            'yoti_sdk_id' => 'Yoti SDK ID',
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

        // Check QR type field.
        $this->assertXpath("//label[contains(text(),'QR Type')]", $html);
        foreach (YotiAdmin::qrTypes() as $value => $label) {
            $selected = $value == $this->config['yoti_qr_type'] ? "[@selected='selected']" : "[not(@selected)]";
            $this->assertXpath(
                sprintf(
                    "//select[@name='yoti_qr_type']/option[@value='%s'][contains(text(),'%s')]%s",
                    $value,
                    $label,
                    $selected
                ),
                $html
            );
        }

    }

}
