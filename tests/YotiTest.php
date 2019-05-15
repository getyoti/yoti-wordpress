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
    private function getUnlinkXpath() {
        $link_attributes = [
            "[@class='yoti-connect-button']",
            "[contains(@href,'/wp-login.php?yoti-select=1&action=unlink&redirect&yoti_verify=')]",
        ];
        return '//table//td/div/a' . implode('', $link_attributes) . "[contains(text(), 'Unlink Yoti Account')]";
    }

    /**
     * Assert that attributes are present on profile.
     *
     * @param string $html
     */
    private function assertProfileAttributes($html) {
        foreach (YotiHelper::$profileFields as $attrLabel) {
            $this->assertXpath(
                "//tr/th/label[contains(text(),'{$attrLabel}')]/parent::th/parent::tr/td[contains(text(),'{$attrLabel} value')]",
                $html
            );
        }
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

        $this->assertXpath($this->getUnlinkXpath(), ob_get_clean());
    }

    /**
     * @covers ::show_user_profile
     */
    public function testUserProfileLinkedWithAttributesAsAdmin()
    {
        wp_set_current_user($this->adminUser->ID);
        $_GET['user_id'] = $this->linkedUser->ID;

        ob_start();
        Yoti::show_user_profile($this->linkedUser);

        $html = ob_get_clean();

        $this->assertNotXpath($this->getUnlinkXpath(), $html);
        $this->assertProfileAttributes($html);
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

        $this->assertXpath($this->getUnlinkXpath(), $html);
        $this->assertProfileAttributes($html);
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderSessionData() {
        $_REQUEST['REQUEST_METHOD'] = 'GET';
        $_REQUEST['redirect_to'] = home_url();
        $_SESSION['yoti-user'] = serialize($this->createMock(Yoti\ActivityDetails::class));

        ob_start();
        Yoti::yoti_login_header();
        $html = ob_get_clean();

        // Check message.
        $lines = [
            "Warning: You are about to link your company_name account to your Yoti account.",
            "If you don't want this to happen, tick the checkbox below.",
        ];
        foreach ($lines as $line) {
            $this->assertXpath(sprintf('//div[@class="message"]/div[contains(text(),"%s")]', $line), $html);
        }

        // Check the checkbox is present.
        $this->assertXpath('//input[@type="checkbox"][@name="yoti_nolink"][@id="edit-yoti-link"][@value="1"]', $html);
        $this->assertXpath('//label[@for="edit-yoti-link"][contains(text(),"Don\'t link my Yoti account")]', $html);

        // Check hidden verfification input.
        $this->assertXpath('//input[@type="hidden"][@name="yoti_verify"][@value]', $html);

        // Check session data is still present.
        $this->assertNotEmpty($_SESSION['yoti-user']);
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderNoSessionData() {
        ob_start();
        Yoti::yoti_login_header();
        $this->assertEmpty(ob_get_clean());
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderClearSessionDataOnReload() {
        $_REQUEST['REQUEST_METHOD'] = 'GET';
        $_SESSION['yoti-user'] = serialize($this->createMock(Yoti\ActivityDetails::class));

        ob_start();
        Yoti::yoti_login_header();

        // Header should not be added to login.
        $this->assertEmpty(ob_get_clean());

        // Assert session data is cleared.
        $this->assertTrue(empty($_SESSION['yoti-user']));
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderNoLinkChecked() {
        $_REQUEST['REQUEST_METHOD'] = 'POST';
        $_POST['yoti_nolink'] = '1';
        $_POST['yoti_verify'] = wp_create_nonce('yoti_verify');
        $_SESSION['yoti-user'] = serialize($this->createMock(Yoti\ActivityDetails::class));

        ob_start();
        Yoti::yoti_login_header();
        $html = ob_get_clean();

        // Check the checkbox is checked.
        $this->assertXpath('//input[@name="yoti_nolink"][@checked="checked"]', $html);
    }
}
