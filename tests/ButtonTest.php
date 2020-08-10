<?php

namespace Yoti\WP\Test\Button;

use Yoti\WP\Button;
use Yoti\WP\Test\TestBase;

/**
 * @coversDefaultClass Yoti\WP\Button
 *
 * @group yoti
 */
class ButtonTest extends TestBase
{
    /**
     * @covers ::render
     */
    public function testButtonUnlinked()
    {
        wp_set_current_user($this->unlinkedUser->ID);

        ob_start();
        Button::render();
        $html = ob_get_clean();

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
        ob_start();
        Button::render();
        $html = ob_get_clean();

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

        ob_start();
        Button::render();
        $html = ob_get_clean();

        $this->assertXpath(
            '//a' . implode('', $link_attributes) . "[contains(text(), 'Unlink Yoti Account')]",
            $html
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonWithCustomScenarioId()
    {
        $expectedScenarioId = 'some-custom-id';

        ob_start();
        Button::render(NULL, FALSE, [
            'yoti_scenario_id' => $expectedScenarioId,
        ]);
        $html = ob_get_clean();

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

        ob_start();
        Button::render(NULL, FALSE, [
            'yoti_button_text' => $expectedText,
        ]);
        $html = ob_get_clean();

        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, $expectedText);
        $this->assertEquals($config->clientSdkId, $this->config['yoti_sdk_id']);
        $this->assertEquals($config->scenarioId, $this->config['yoti_scenario_id']);
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }
}
