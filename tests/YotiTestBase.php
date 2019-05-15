<?php

/**
 * Base Class for Yoti Tests.
 */
class YotiTestBase extends WP_UnitTestCase
{
    /**
     * Test plugin config.
     *
     * @var array
     */
    protected $config = [
        'yoti_app_id' => 'app_id',
        'yoti_scenario_id' => 'scenario_id',
        'yoti_sdk_id' => 'sdk_id',
        'yoti_only_existing' => 1,
        'yoti_success_url' => '/user',
        'yoti_fail_url' => '/',
        'yoti_user_email' => 1,
        'yoti_age_verification' => 0,
        'yoti_company_name' => 'company_name',
        'yoti_qr_type' => 'inline',
        'yoti_pem' => [
            'contents' => 'some-pem-contents',
        ],
    ];

    /**
     * @var WP_User
     */
    protected $adminUser;

    /**
     * @var WP_User
     */
    protected $linkedUser;

    /**
     * @var WP_User
     */
    protected $unlinkedUser;

    /**
     * Setup tests.
     *
     * @return void
     */
    public function setup()
    {
        parent::setup();

        update_option(YotiHelper::YOTI_CONFIG_OPTION_NAME, maybe_serialize($this->config));

        // Create Linked User.
        $linkedUserId = wp_create_user('linked_user', 'some_password', 'linked_user@example.com');
        $this->linkedUser = get_user_by('id', $linkedUserId);
        update_user_meta($this->linkedUser->ID, 'yoti_user.profile', array_map(
            function ($item) {
              return $item . ' value';
            },
            YotiHelper::$profileFields
        ));
        update_user_meta($this->linkedUser->ID, 'yoti_user.identifier', 'some_remember_me_id');

        // Create Unlinked User.
        $unlinkedUserId = wp_create_user('unlinked_user', 'some_password', 'unlinked_user@example.com');
        $this->unlinkedUser = get_user_by('id', $unlinkedUserId);

        // Create Admin User.
        $adminUserId = wp_create_user('admin_user', 'some_password', 'admin_user@example.com');
        $this->adminUser = get_user_by('id', $adminUserId);
        $this->adminUser->set_role('administrator');

        // Run as anonymous user by default.
        wp_set_current_user(0);
    }

    /**
     * Teardown tests.
     */
    public function teardown()
    {
        wp_delete_user($this->linkedUser->ID);
        wp_delete_user($this->unlinkedUser->ID);

        // Reset request.
        $_POST = $_GET = $_REQUEST = [];

        // Reset session.
        $_SESSION = [];

        // Reset pagenow.
        global $pagenow;
        $pagenow = "index.php";

        // Clear environment variables.
        putenv('YOTI_CONNECT_BASE_URL');

        parent::teardown();
    }

    /**
     * Get XPath query result for provided HTML.
     *
     * @param string $query
     * @param string $html
     */
    protected function getXpathResult($query, $html)
    {
        $dom = new DomDocument();
        $dom->loadHTML('<html><body>' . $html . '</body></html>');
        $xpath = new DOMXPath($dom);
        return $xpath->query($query);
    }

    /**
     * Asserts given XPath query returns results for provided HTML.
     *
     * @param string $query
     * @param string $html
     */
    protected function assertXpath($query, $html)
    {
        $result = $this->getXpathResult($query, $html);
        $this->assertTrue($result->length > 0, "{$query} XPath query returned no results");
    }

    /**
     * Asserts given XPath query returns no results for provided HTML.
     *
     * @param string $query
     * @param string $html
     */
    protected function assertNotXpath($query, $html)
    {
        $result = $this->getXpathResult($query, $html);
        $this->assertTrue($result->length == 0, "{$query} XPath query returned results");
    }

    /**
     * Get the button XPath base query.
     *
     * @return string
     */
    protected function getButtonXpath()
    {
        $button_attributes = [
            sprintf("[@data-yoti-application-id='%s']", $this->config['yoti_app_id']),
            sprintf("[@data-yoti-scenario-id='%s']", $this->config['yoti_scenario_id']),
            sprintf("[@data-yoti-type='%s']", $this->config['yoti_qr_type']),
            "[@data-size='small']",
        ];
        return "//div[@class='yoti-connect']/span" . implode('', $button_attributes);
    }
}
