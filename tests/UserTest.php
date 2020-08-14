<?php

namespace Yoti\WP\Test;

use Yoti\Profile\ActivityDetails;
use Yoti\Profile\Attribute;
use Yoti\Profile\UserProfile;
use Yoti\WP\Client\ClientFactoryInterface;
use Yoti\WP\Config;
use Yoti\WP\Service;
use Yoti\WP\User;
use Yoti\YotiClient;

/**
 * @group yoti
 */
class UserTest extends TestBase
{
    private const SOME_REMEMBER_ME_ID = 'some-remember-me-id';

    public function testLink()
    {
        wp_set_current_user($this->unlinkedUser->ID);
        $_GET['token'] = 'some-token';

        $givenNamesMock = $this->createMock(Attribute::class);
        $givenNamesMock->method('getValue')->willReturn('some given name');

        $profileMock = $this->createMock(UserProfile::class);
        $profileMock
            ->method('getProfileAttribute')
            ->will($this->returnValueMap([
                [ UserProfile::ATTR_GIVEN_NAMES, $givenNamesMock ],
            ]));

        $user = new User(
            $this->createMockClientFactory($profileMock),
            Service::config()
        );

        $user->link();

        $dbProfile = $user->getUserProfile($this->unlinkedUser->ID);

        $this->assertCount(1, $dbProfile);
        $this->assertEquals('some given name', $dbProfile[UserProfile::ATTR_GIVEN_NAMES]);
    }

    public function testGetUserProfile()
    {
        wp_set_current_user($this->linkedUser->ID);

        $user = Service::user();
        $dbProfile = $user->getUserProfile($this->linkedUser->ID);

        $this->assertCount(10, $dbProfile);
    }

    public function testUnlink()
    {
        wp_set_current_user($this->linkedUser->ID);

        $user = Service::user();
        $user->unlink();

        $this->assertFalse($user->getUserProfile($this->linkedUser->ID));
    }

    /**
     * @param UserProfile $userProfile
     *
     * @return ClientFactoryInterface
     */
    private function createMockClientFactory(UserProfile $userProfile)
    {
        $activityDetailsMock = $this->createMock(ActivityDetails::class);
        $activityDetailsMock->method('getProfile')->willReturn($userProfile);
        $activityDetailsMock->method('getRememberMeID')->willReturn(self::SOME_REMEMBER_ME_ID);

        $clientMock = $this->createMock(YotiClient::class);
        $clientMock->method('getActivityDetails')->willReturn($activityDetailsMock);

        $clientFactoryMock = $this->createMock(ClientFactoryInterface::class);
        $clientFactoryMock->method('getClient')->willReturn($clientMock);

        return $clientFactoryMock;
    }
}
