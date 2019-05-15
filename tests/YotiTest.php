<?php

/**
 * @coversDefaultClass Yoti
 *
 * @group yoti
 */
class YotiTest extends YotiTestBase
{

    /**
     * Get unlink base XPath query.
     *
     * @return string
     */
    private function getLinkXpath() {
        $link_attributes = [
            "[@class='yoti-connect-button']",
            "[contains(@href,'/wp-login.php?yoti-select=1&action=unlink&redirect&yoti_verify=')]",
        ];
        return '//table//td/div/a' . implode('', $link_attributes);
    }

    /**
     * @covers ::show_user_profile
     */
    public function testUserProfileUnlinked()
    {
        wp_set_current_user($this->unlinkedUser->ID);

        ob_start();
        Yoti::show_user_profile($this->unlinkedUser);

        $this->assertEmpty(ob_get_clean());
    }

    /**
     * @covers ::show_user_profile
     */
    public function testUserProfileLinkedNoAttributes()
    {
        wp_set_current_user($this->linkedUser->ID);
        update_user_meta($this->linkedUser->ID, 'yoti_user.profile', []);

        ob_start();
        Yoti::show_user_profile($this->linkedUser);

        // Check unlink anchor is added.
        $this->assertXpath(
            $this->getLinkXpath() . "[contains(text(), 'Unlink Yoti Account')]",
            ob_get_clean()
        );
    }

    /**
     * @covers ::show_user_profile
     */
    public function testUserProfileLinkedWithAttributes()
    {
        wp_set_current_user($this->linkedUser->ID);

        ob_start();
        Yoti::show_user_profile($this->linkedUser);

        $html = ob_get_clean();

        // Check unlink anchor is added.
        $this->assertXpath(
            $this->getLinkXpath() . "[contains(text(), 'Unlink Yoti Account')]",
            $html
        );

        // Check table of attributes.
        foreach (YotiHelper::$profileFields as $attrLabel) {
            $this->assertXpath(
                "//tr/th/label[contains(text(),'{$attrLabel}')]/parent::th/parent::tr/td[contains(text(),'{$attrLabel} value')]",
                $html
            );
        }
    }

}
