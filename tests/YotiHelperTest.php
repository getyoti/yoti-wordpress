<?php

/**
 * @coversDefaultClass YotiHelper
 *
 * @group yoti
 */
class YotiHelperTest extends YotiTestBase
{

  /**
   * @covers ::getConfig
   */
  public function testGetConfig()
  {
      $this->assertEquals($this->config, YotiHelper::getConfig());
  }

}
