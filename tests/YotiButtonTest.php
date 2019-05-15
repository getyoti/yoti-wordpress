<?php

/**
 * @coversDefaultClass YotiButton
 *
 * @group yoti
 */
class YotiButtonTest extends YotiTestBase
{

    /**
     * Get the button XPath base query.
     *
     * @return string
     */
    private function getButtonXpath() {
        $button_attributes = [
            "[@data-yoti-application-id='app_id']",
            "[@data-yoti-scenario-id='scenario_id']",
            "[@data-size='small']",
            "[@data-yoti-type='inline']",
        ];
        return "//div[@class='yoti-connect']/span" . implode('', $button_attributes);
    }

    /**
     * @covers ::render
     */
    public function testButtonUnlinked()
    {
        wp_set_current_user($this->unlinkedUser->ID);

        ob_start();
        YotiButton::render();

        $this->assertXpath(
            $this->getButtonXpath() . "[contains(text(), 'Link to Yoti')]",
            ob_get_clean()
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonAnonymous()
    {
        wp_set_current_user(0);

        ob_start();
        YotiButton::render();

        $this->assertXpath(
            $this->getButtonXpath() . "[contains(text(), 'Use Yoti')]",
            ob_get_clean()
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonLinked()
    {
        wp_set_current_user($this->linkedUser->ID);

        ob_start();
        YotiButton::render();

        $link_attributes = [
            "[@class='yoti-connect-button']",
            "[contains(@href,'/wp-login.php?yoti-select=1&action=unlink&redirect&yoti_verify=')]",
        ];

        $this->assertXpath(
            '//a' . implode('', $link_attributes) . "[contains(text(), 'Unlink Yoti Account')]",
            ob_get_clean()
        );
    }

}
