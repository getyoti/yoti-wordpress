<?php

/**
 * @coversDefaultClass YotiWidget
 *
 * @group yoti
 */
class YotiWidgetTest extends YotiTestBase
{

    /**
     * @covers ::widget
     */
    public function testWidget()
    {
        ob_start();
        the_widget('YotiWidget');

        $html = ob_get_clean();
        $config = $this->getButtonConfigFromMarkup($html);
        $this->assertEquals($config->button->label, 'Use Yoti');
        $this->assertXpath("//div[@class='yoti-connect']/div[@id='{$config->domId}']", $html);
    }

    /**
     * @covers ::widget
     */
    public function testWidgetNotConfigured()
    {
        $config = $this->config;
        unset($config['yoti_pem']);
        update_option(YotiHelper::YOTI_CONFIG_OPTION_NAME, maybe_serialize($config));

        ob_start();
        the_widget('YotiWidget');

        $this->assertXpath(
            '//div[@class="widget yoti_widget"][contains(.,"Yoti not configured.")]',
            ob_get_clean()
        );
    }

    /**
     * @covers ::widget
     */
    public function testWidgetWithCustomScenarioId()
    {
        $expectedTitle = 'Some Custom Title';
        $expectedScenarioId = 'some-scenario-id';

        ob_start();
        the_widget('YotiWidget', [
            'title' => $expectedTitle,
            'yoti_scenario_id' => $expectedScenarioId,
        ]);

        $html = ob_get_clean();
        $config = $this->getButtonConfigFromMarkup($html);

        $this->assertXpath(
            sprintf('//h2[contains(text(),"%s")]', $expectedTitle),
            $html
        );
        $this->assertEquals($config->scenarioId, $expectedScenarioId);

    }

}
