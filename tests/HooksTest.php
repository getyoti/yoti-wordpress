<?php

namespace Yoti\WP\Test;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Yoti\ActivityDetails;
use Yoti\Entity\Profile;
use Yoti\WP\Helper;
use Yoti\WP\Hooks;

/**
 * @coversDefaultClass Yoti\WP\Hooks
 *
 * @group yoti
 */
class HooksTest extends TestBase
{
    use ExpectDeprecationTrait;

    /**
     * Get unlink base XPath query.
     *
     * @return string
     */
    private function getUnlinkXpath()
    {
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
    private function assertProfileAttributes($html)
    {
        foreach (Helper::$profileFields as $attrLabel) {
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
        Hooks::show_user_profile($this->unlinkedUser);

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
        Hooks::show_user_profile($this->linkedUser);

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
        Hooks::show_user_profile($this->linkedUser);

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
        Hooks::show_user_profile($this->linkedUser);

        $html = ob_get_clean();

        $this->assertXpath($this->getUnlinkXpath(), $html);
        $this->assertProfileAttributes($html);
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderSessionData()
    {
        $_REQUEST['REQUEST_METHOD'] = 'GET';
        $_REQUEST['redirect_to'] = home_url();
        $_SESSION['yoti-user'] = serialize($this->createMockActivityDetails());

        ob_start();
        Hooks::yoti_login_header();
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
     * @runInSeparateProcess
     *
     * @covers ::yoti_login
     */
    public function testLoginNotVerified()
    {
        wp_create_nonce('yoti_verify');
        $_POST['yoti_nolink'] = '0';
        $_POST['yoti_verify'] = 'invalid-verification';
        Helper::storeYotiUser($this->createMockActivityDetails());

        Hooks::yoti_login('unlinked_user', $this->unlinkedUser);

        $flash = Helper::getFlash();
        $this->assertEquals(
            'Yoti profile could not be linked, please try again.',
            $flash['message']
        );
        $this->assertEquals('message', $flash['type']);
        $this->assertEmpty(Helper::getYotiUserFromStore());
        $this->assertFalse(Helper::getUserProfile($this->unlinkedUser->ID));
    }

    /**
     * @covers ::yoti_login
     */
    public function testLoginNoVerification()
    {
        Hooks::yoti_login('unlinked_user', $this->unlinkedUser);
        $this->assertEmpty(Helper::getFlash());
    }

    /**
     * @covers ::yoti_login
     */
    public function testLoginVerified()
    {
        $_POST['yoti_nolink'] = '0';
        $_POST['yoti_verify'] = wp_create_nonce('yoti_verify');
        Helper::storeYotiUser($this->createMockActivityDetails());

        Hooks::yoti_login('unlinked_user', $this->unlinkedUser);

        $this->assertEmpty(Helper::getFlash());
        $this->assertEmpty(Helper::getYotiUserFromStore());
        $this->assertTrue(is_array(Helper::getUserProfile($this->unlinkedUser->ID)));
    }

    /**
     * @covers ::yoti_login
     */
    public function testLoginVerifiedNoLink()
    {
        $_POST['yoti_nolink'] = '1';
        $_POST['yoti_verify'] = wp_create_nonce('yoti_verify');
        Helper::storeYotiUser($this->createMockActivityDetails());

        Hooks::yoti_login('unlinked_user', $this->unlinkedUser);

        $this->assertEmpty(Helper::getFlash());
        $this->assertEmpty(Helper::getYotiUserFromStore());
        $this->assertFalse(Helper::getUserProfile($this->unlinkedUser->ID));
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderNoSessionData()
    {
        ob_start();
        Hooks::yoti_login_header();
        $this->assertEmpty(ob_get_clean());
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderClearSessionDataOnReload()
    {
        $_REQUEST['REQUEST_METHOD'] = 'GET';
        $_SESSION['yoti-user'] = serialize($this->createMockActivityDetails());

        ob_start();
        Hooks::yoti_login_header();

        // Header should not be added to login.
        $this->assertEmpty(ob_get_clean());

        // Assert session data is cleared.
        $this->assertTrue(empty($_SESSION['yoti-user']));
    }

    /**
     * @covers ::yoti_login_header
     */
    public function testLoginHeaderNoLinkChecked()
    {
        $_REQUEST['REQUEST_METHOD'] = 'POST';
        $_POST['yoti_nolink'] = '1';
        $_POST['yoti_verify'] = wp_create_nonce('yoti_verify');
        $_SESSION['yoti-user'] = serialize($this->createMockActivityDetails());

        ob_start();
        Hooks::yoti_login_header();
        $html = ob_get_clean();

        // Check the checkbox is checked.
        $this->assertXpath('//input[@name="yoti_nolink"][@checked="checked"]', $html);
    }

    /**
     * @covers ::yoti_plugin_activate_notice
     */
    public function testActivateNotice()
    {
        global $pagenow;
        $pagenow = "plugins.php";

        ob_start();
        Hooks::yoti_plugin_activate_notice();
        $html = ob_get_clean();

        $base_query = '//div[@class="notice notice-success is-dismissible"]';

        // Check the text is correct.
        $this->assertXpath(
            $base_query . '/p[contains(.,"Almost done - Complete Yoti")]',
            $html
        );

        // Check the link is correct.
        $this->assertXpath(
            $base_query . '/p/a[contains(@href,"/wp-admin/options-general.php?page=yoti")][contains(.,"settings here")]',
            $html
        );
    }

    /**
     * @covers ::yoti_enqueue_scripts
     */
    public function testInitScript()
    {
        wp_enqueue_scripts();
        $scripts = wp_scripts();

        ob_start();
        $scripts->do_footer_items();
        $html = ob_get_clean();

        $this->assertXpath("//script[@src='https://www.yoti.com/share/client/']", $html);
        $this->assertXpath("//script[contains(text(), 'window.Yoti.Share.init(yotiConfig);')]", $html);
    }

    /**
     * @group legacy
     */
    public function testClassAlias()
    {
        $this->expectDeprecation(sprintf('%s is deprecated, use %s instead', \Yoti::class, Hooks::class));
        $this->assertInstanceOf(Hooks::class, new \Yoti());
    }

    /**
     * Create mock activity details.
     *
     * @return \Yoti\ActivityDetails
     */
    private function createMockActivityDetails()
    {
        $activityDetails = $this->createMock(ActivityDetails::class);

        $profile = $this->createMock(Profile::class);
        $profile
            ->method('getAgeVerifications')
            ->willReturn([]);

        $activityDetails
            ->method('getProfile')
            ->willReturn($profile);

        return $activityDetails;
    }
}
