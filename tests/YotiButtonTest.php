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

        $this->assertXpath(
            $this->getButtonXpath() . "[contains(text(), 'Link to Yoti')]",
            YotiButton::render()
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonAnonymous()
    {
        $this->assertXpath(
            $this->getButtonXpath() . "[contains(text(), 'Use Yoti')]",
            YotiButton::render()
        );
    }

    /**
     * @covers ::render
     */
    public function testButtonScript()
    {
        $html = YotiButton::render();

        $this->assertXpath("//script[contains(.,'_ybg.init();')]", $html);
        $this->assertXpath("//script[not(contains(.,'_ybg.config.qr'))]", $html);
        $this->assertXpath("//script[not(contains(.,'_ybg.config.service'))]", $html);
    }

    /**
     * @covers ::render
     */
    public function testButtonScriptStaging()
    {
        putenv('YOTI_CONNECT_BASE_URL=https://www.example.com/connect');

        $html = YotiButton::render();

        $this->assertXpath('//script[contains(.,"_ybg.init();")]', $html);
        $this->assertXpath("//script[contains(.,'_ybg.config.qr = \"https:\/\/www.example.com\/qr\/\";')]", $html);
        $this->assertXpath("//script[contains(.,'_ybg.config.service = \"https:\/\/www.example.com\/connect\/\";')]", $html);
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

}
