<?php

/**
 * @coversDefaultClass YotiHelper
 *
 * @group yoti
 */
class WP_Yoti_HelperTest extends WP_UnitTestCase
{

  /**
   * Test plugin config.
   *
   * @var array
   */
  private $config = [
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
   * Setup tests.
   *
   * @return void
   */
  public function setup()
  {
    parent::setup();
    update_option(YotiHelper::YOTI_CONFIG_OPTION_NAME, maybe_serialize($this->config));
  }

  /**
   * @covers ::getConfig
   */
  public function testGetConfig()
  {
    $this->assertEquals($this->config, YotiHelper::getConfig());
  }
}
