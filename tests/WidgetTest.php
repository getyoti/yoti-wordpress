<?php

namespace Yoti\WP\Test;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Yoti\WP\Config;
use Yoti\WP\Widget;

/**
 * @coversDefaultClass Yoti\WP\Widget
 *
 * @group yoti
 */
class WidgetTest extends TestBase
{
    use ExpectDeprecationTrait;

    /**
     * @covers ::widget
     */
    public function testWidget()
    {
        ob_start();
        the_widget(Widget::class);

        $html = ob_get_clean();
        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, 'Use Yoti');
        $this->assertEquals($config->scenarioId, $this->config['yoti_scenario_id']);
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

    /**
     * @covers ::widget
     */
    public function testWidgetNotConfigured()
    {
        $config = $this->config;
        unset($config['yoti_pem']);
        update_option(Config::YOTI_CONFIG_OPTION_NAME, maybe_serialize($config));

        ob_start();
        the_widget(Widget::class);

        $this->assertXpath(
            '//div[@class="widget yoti_widget"][contains(.,"Yoti not configured.")]',
            ob_get_clean()
        );
    }

    /**
     * @covers ::widget
     */
    public function testWidgetWithCustomSettings()
    {
        $expectedTitle = 'Some Custom Title';
        $expectedScenarioId = 'some-scenario-id';
        $expectedButtonText = 'Some Custom Button Text';

        ob_start();
        the_widget(Widget::class, [
            'title' => $expectedTitle,
            'yoti_scenario_id' => $expectedScenarioId,
            'yoti_button_text' => $expectedButtonText,
        ]);

        $html = ob_get_clean();
        $config = $this->getButtonConfigFromMarkup($html);

        $this->assertXpath(
            sprintf('//h2[contains(text(),"%s")]', $expectedTitle),
            $html
        );
        $this->assertEquals($config->scenarioId, $expectedScenarioId);
        $this->assertEquals($config->button->label, $expectedButtonText);
    }

    /**
     * @group legacy
     */
    public function testClassAlias()
    {
        $this->expectDeprecation(sprintf('%s is deprecated, use %s instead', \YotiWidget::class, Widget::class));
        $this->assertInstanceOf(Widget::class, new \YotiWidget());
    }
}
