<?php

namespace Yoti\WP\Test;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Yoti\WP\Button;

/**
 * @coversDefaultClass Yoti\WP\Button
 *
 * @group yoti
 */
class ButtonTest extends TestBase
{
    use ExpectDeprecationTrait;

    /**
     * @covers ::render
     */
    public function testButtonUnlinked()
    {
        wp_set_current_user($this->unlinkedUser->ID);

        $html = Button::render();

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
        $html = Button::render();

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
            Button::render()
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonWithCustomScenarioId()
    {
        $expectedScenarioId = 'some-custom-id';

        $html = Button::render(NULL, FALSE, FALSE, [
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

        $html = Button::render(NULL, FALSE, FALSE, [
            'yoti_button_text' => $expectedText,
        ]);

        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, $expectedText);
        $this->assertEquals($config->clientSdkId, $this->config['yoti_sdk_id']);
        $this->assertEquals($config->scenarioId, $this->config['yoti_scenario_id']);
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

    /**
     * @group legacy
     */
    public function testClassAlias()
    {
        $this->expectDeprecation(sprintf('%s is deprecated, use %s instead', \YotiButton::class, Button::class));
        $this->assertInstanceOf(Button::class, new \YotiButton());
    }
}
