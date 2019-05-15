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
        'yoti_user_email' => 'user@example.com',
        'yoti_age_verification' => 0,
        'yoti_company_name' => 'company_name',
    ];

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

        $linkedUserId = wp_create_user( 'linked_user', 'some_password', 'linked_user@example.com' );
        $this->linkedUser = get_user_by('id', $linkedUserId);
        update_user_meta($this->linkedUser->ID, 'yoti_user.profile', array_map(
            function ($item) {
              return $item . ' value';
            },
            YotiHelper::$profileFields
        ));
        update_user_meta($this->linkedUser->ID, 'yoti_user.identifier', 'some_remember_me_id');

        $unlinkedUserId = wp_create_user( 'unlinked_user', 'some_password', 'unlinked_user@example.com' );
        $this->unlinkedUser = get_user_by('id', $unlinkedUserId);
    }

    /**
     * Teardown tests.
     */
    public function teardown() {
        wp_delete_user($this->linkedUser->ID);
        wp_delete_user($this->unlinkedUser->ID);
        parent::teardown();
    }

    /**
     * Asserts given XPath query returns results for provided HTML.
     *
     * @param string $query
     * @param string $html
     */
    protected function assertXpath($query, $html)
    {
        $dom = new DomDocument();
        $dom->loadHTML('<html><body>' . $html . '</body></html>');
        $xpath = new DOMXPath($dom);
        $result = $xpath->query($query);
        $this->assertTrue($result->length > 0, "{$query} XPath query returned no results");
    }
}
