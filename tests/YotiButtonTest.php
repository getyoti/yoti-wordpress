<?php

/**
 * @coversDefaultClass YotiButton
 *
 * @group yoti
 */
class YotiButtonTest extends YotiTestBase
{

    /**
     * @covers ::render
     */
    public function testButtonUnlinked()
    {
        wp_set_current_user($this->unlinkedUser->ID);

        $html = YotiButton::render();

        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, 'Link to Yoti');
        $this->assertEquals($config->clientSdkId, $this->config['yoti_sdk_id']);
        $this->assertEquals($config->scenarioId, $this->config['yoti_scenario_id']);
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

    /**
     * @covers ::render
     */
    public function testButtonAnonymous()
    {
        $html = YotiButton::render();

        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, 'Use Yoti');
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

    /**
     * @covers ::render
     */
    public function testButtonLinked()
    {
        wp_set_current_user($this->linkedUser->ID);

        $link_attributes = [
            "[@class='yoti-connect-button']",
            "[contains(@href,'/wp-login.php?yoti-select=1&action=unlink&redirect&yoti_verify=')]",
        ];

        $this->assertXpath(
            '//a' . implode('', $link_attributes) . "[contains(text(), 'Unlink Yoti Account')]",
            YotiButton::render()
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonWithCustomScenarioId()
    {
        $expectedScenarioId = 'some-custom-id';

        $html = YotiButton::render(NULL, FALSE, FALSE, [
            'yoti_scenario_id' => $expectedScenarioId,
        ]);

        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, 'Use Yoti');
        $this->assertEquals($config->clientSdkId, $this->config['yoti_sdk_id']);
        $this->assertEquals($config->scenarioId, $expectedScenarioId);
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

    /**
     * @covers ::render
     */
    public function testButtonWithCustomText()
    {
        $expectedText = 'some custom text';

        $html = YotiButton::render(NULL, FALSE, FALSE, [
            'yoti_button_text' => $expectedText,
        ]);

        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, $expectedText);
        $this->assertEquals($config->clientSdkId, $this->config['yoti_sdk_id']);
        $this->assertEquals($config->scenarioId, $this->config['yoti_scenario_id']);
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

}
