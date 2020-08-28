<?php

namespace Yoti\WP;

use Yoti\Profile\ActivityDetails;
use Yoti\Profile\Attribute\AgeVerification;
use Yoti\Profile\UserProfile;
use Yoti\WP\Client\ClientFactoryInterface;
use Yoti\WP\Exception\LinkException;
use Yoti\WP\Exception\UnlinkException;
use Yoti\WP\Exception\UserMessageExceptionInterface;

/**
 * Class User
 */
class User
{
    /** Selfie key */
    public const SELFIE_FILENAME = 'selfie_filename';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @param ClientFactoryInterface $clientFactory
     */
    public function __construct(ClientFactoryInterface $clientFactory, Config $config)
    {
        $this->clientFactory = $clientFactory;
        $this->config = $config;
    }

    /**
     * @return array<string,string>
     */
    public static function profileFields(): array
    {
        return [
            UserProfile::ATTR_SELFIE => 'Selfie',
            UserProfile::ATTR_FULL_NAME => 'Full Name',
            UserProfile::ATTR_GIVEN_NAMES => 'Given Names',
            UserProfile::ATTR_FAMILY_NAME => 'Family Name',
            UserProfile::ATTR_PHONE_NUMBER => 'Mobile Number',
            UserProfile::ATTR_EMAIL_ADDRESS => 'Email Address',
            UserProfile::ATTR_DATE_OF_BIRTH => 'Date Of Birth',
            UserProfile::ATTR_POSTAL_ADDRESS => 'Postal Address',
            UserProfile::ATTR_GENDER => 'Gender',
            UserProfile::ATTR_NATIONALITY => 'Nationality',
        ];
    }

    /**
     * @return ActivityDetails
     */
    private function getActivityDetails(): ActivityDetails
    {
        // phpcs:disable
        $token = !empty($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : null;
        // phpcs:enable

        if ($token === null) {
            throw LinkException::noToken();
        }

        try {
            $client = $this->clientFactory->getClient();
            return $client->getActivityDetails($token);
        } catch (\Exception $e) {
            throw LinkException::couldNotConnect();
        }
    }

    /**
     * Login user
     *
     * @throws UserMessageExceptionInterface
     */
    public function link(): void
    {
        $activityDetails = $this->getActivityDetails();
        $profile = $activityDetails->getProfile();

        $this->ensurePassedAgeVerification($profile);

        $rememberMeId = $activityDetails->getRememberMeId();
        if ($rememberMeId === null) {
            throw LinkException::missingRememberMeId();
        }
        $linkedUserId = $this->getUserIdByYotiId($rememberMeId);

        if ($linkedUserId && !$this->userExists($linkedUserId)) {
            $this->deleteYotiUser($linkedUserId);
        }

        if (is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            if ($linkedUserId && $linkedUserId !== $currentUser->ID) {
                throw LinkException::alreadyLinked();
            }
            if (!$linkedUserId) {
                $this->createYotiUser($currentUser->ID, $activityDetails);
            }
        } else {
            if ($linkedUserId) {
                $this->loginUser($linkedUserId);
            } else {
                $emailLinkedUserId = $this->linkByEmail($activityDetails);
                if ($emailLinkedUserId !== null) {
                    $this->loginUser($emailLinkedUserId);
                } else {
                    if ($this->config->onlyLinkExistingUsers()) {
                        $this->storeYotiUser($activityDetails);

                        // phpcs:disable
                        $redirect = !empty($_GET['redirect'])
                            ? sanitize_text_field(wp_unslash($_GET['redirect']))
                            : home_url();
                        // phpcs:enable

                        wp_safe_redirect(wp_login_url($redirect));
                        exit;
                    }
                    $newUserId = $this->createUser($activityDetails);
                    $this->loginUser($newUserId);
                }
            }
        }
    }

    /**
     * Unlink account from currently logged in user
     *
     * @return void
     *
     * @throws UserMessageExceptionInterface
     */
    public function unlink(): void
    {
        if (!is_user_logged_in()) {
            throw UnlinkException::couldNotUnlink();
        }

        $this->deleteYotiUser(wp_get_current_user()->ID);
    }

    /**
     * Display user profile image
     *
     * @param string $field
     * @param int|null $userId
     */
    public function binFile($field, $userId = null): void
    {
        $user = wp_get_current_user();
        if (
            in_array('administrator', $user->roles, true) &&
            $userId !== null
        ) {
            $user = get_user_by('id', $userId);
        }

        if (!$user) {
            return;
        }

        $field = ($field === 'selfie') ? self::SELFIE_FILENAME : $field;
        $dbProfile = $this->getUserProfile($user->ID);
        if (!$dbProfile || !array_key_exists($field, $dbProfile)) {
            return;
        }

        $file = $this->config->uploadDir() . "/{$dbProfile[$field]}";
        if (!file_exists($file)) {
            return;
        }

        $type = 'image/png';
        header('Content-Type:' . $type);
        header('Content-Length: ' . filesize($file));
        readfile($file);
    }

    /**
     * Check if age verification applies and is valid.
     *
     * @param UserProfile $profile
     */
    private function ensurePassedAgeVerification(UserProfile $profile): void
    {
        if (!$this->config->requireAgeVerification()) {
            return;
        }

        if ($this->oneAgeIsVerified($profile)) {
            return;
        }

        throw LinkException::failedAgeVerification();
    }

    /**
     * @param UserProfile $profile
     *
     * @return bool
     */
    private function oneAgeIsVerified(UserProfile $profile): bool
    {
        $ageVerificationsArr = $this->processAgeVerifications($profile);
        return empty($ageVerificationsArr) || in_array('Yes', array_values($ageVerificationsArr));
    }

    /**
     * @param UserProfile $profile
     *
     * @return array<string,string>
     */
    private function processAgeVerifications(UserProfile $profile): array
    {
        $ageVerifications = $profile->getAgeVerifications();
        $ageVerificationsAttr = [];
        foreach ($ageVerifications as $attr => $ageVerification) { /** @var AgeVerification $ageVerification*/
            $ageVerificationsAttr[$attr] = $ageVerification->getResult() ? 'Yes' : 'No';
        }
        return $ageVerificationsAttr;
    }

    /**
     * Save Yoti user data in the session.
     *
     * @param ActivityDetails $activityDetails
     */
    public function storeYotiUser(ActivityDetails $activityDetails): void
    {
        $_SESSION['yoti-user'] = serialize($activityDetails);
    }

    /**
     * Retrieve Yoti user data from the session.
     *
     * @return ActivityDetails|null
     */
    public function getYotiUserFromStore(): ?ActivityDetails
    {
        return $_SESSION && array_key_exists('yoti-user', $_SESSION) ? unserialize($_SESSION['yoti-user']) : null;
    }

    /**
     * Remove Yoti user data from the session.
     */
    public function clearYotiUserStore(): void
    {
        unset($_SESSION['yoti-user']);
    }

    /**
     * Generate Yoti unique username.
     *
     * @param UserProfile $profile
     * @param string $prefix
     *
     * @return string
     */
    private function generateUsername(UserProfile $profile, $prefix = 'yoti.user'): string
    {
        $givenName = $this->getUserGivenName($profile);
        if ($familyNameAttr = $profile->getFamilyName()) {
            $familyName = $familyNameAttr->getValue();
        }

        // If GivenName and FamilyName are provided use as user nickname/login
        if (isset($givenName) && isset($familyName)) {
            $userFullName = $givenName . ' ' . $familyName;
            $userProvidedPrefix = strtolower(str_replace(' ', '.', $userFullName));
            $prefix = validate_username($userProvidedPrefix) ? $userProvidedPrefix : $prefix;
        }

        // Get the number of user_login that starts with prefix
        $userQuery = new \WP_User_Query(
            [
                'search' => $prefix . '*',
                // Search the `user_login` field only.
                'search_columns' => ['user_login'],
                // Return user count
                'count_total' => true,
            ]
        );

        // Generate Yoti unique username
        $userCount = (int)$userQuery->get_total();
        $username = $prefix;
        // If we already have a login with this prefix then generate another login
        if ($userCount > 0) {
            do {
                $username = $prefix . ++$userCount;
            } while (get_user_by('login', $username));
        }

        return $username;
    }

    /**
     * If user has more than one given name return the first one
     *
     * @param UserProfile $profile
     *
     * @return null|string
     */
    private function getUserGivenName(UserProfile $profile): ?string
    {
        $givenName = null;
        if ($givenNamesAttr = $profile->getGivenNames()) {
            $givenName = explode(' ', $givenNamesAttr->getValue())[0];
        }
        return $givenName;
    }

    /**
     * Generate Yoti unique user email.
     *
     * @param string $prefix
     * @param string $domain
     *
     * @return string
     */
    private function generateEmail($prefix = 'yoti.user', $domain = 'example.com'): string
    {
        // Get the number of user_email that starts with yotiuser-
        $userQuery = new \WP_User_Query(
            [
                // Search for Yoti users starting with the prefix yotiuser-.
                'search' => $prefix . '*',
                // Search the `user_email` field only.
                'search_columns' => ['user_email'],
                // Return user count
                'count_total' => true,
            ]
        );

        // Generate the default email
        $email = $prefix . "@$domain";

        // Generate Yoti unique user email
        $userCount = (int)$userQuery->get_total();
        if ($userCount > 0) {
            do {
                $email = $prefix . ++$userCount . "@$domain";
            } while (get_user_by('email', $email));
        }

        return $email;
    }

    /**
     * Create user profile with Yoti data.
     *
     * @param ActivityDetails $activityDetails
     *
     * @return int
     *
     * @throws UserMessageExceptionInterface
     */
    private function createUser(ActivityDetails $activityDetails): int
    {
        $profile = $activityDetails->getProfile();
        $username = $this->generateUsername($profile);
        $password = wp_generate_password(10);

        // Check that email is available and valid.
        $userProvidedEmailCanBeUsed = false;
        if ($emailAttr = $profile->getEmailAddress()) {
            $userProvidedEmail = $emailAttr->getValue();
            $userProvidedEmailCanBeUsed = is_email($userProvidedEmail) && !get_user_by('email', $userProvidedEmail);
        }

        // If user has provided an email address and it's not in use then use it,
        // otherwise use Yoti generic email
        $email = isset($userProvidedEmail) && $userProvidedEmailCanBeUsed ? $userProvidedEmail : $this->generateEmail();

        $userId = wp_create_user($username, $password, $email);
        if ($userId instanceof \WP_Error) {
            throw LinkException::couldNotCreateAccount();
        }

        $this->createYotiUser($userId, $activityDetails);

        return $userId;
    }

    /**
     * Get Yoti user by ID.
     *
     * @param string $yotiId
     *
     * @return int|null
     */
    private function getUserIdByYotiId($yotiId): ?int
    {
        // Query for users based on the meta data
        $users = (new \WP_User_Query(
            [
                'meta_key' => 'yoti_user.identifier',
                'meta_value' => $yotiId,
            ]
        ))->get_results();
        $user = reset($users);

        return $user ? $user->ID : null;
    }

    /**
     * Create Yoti user profile.
     *
     * @param int $userId
     * @param ActivityDetails $activityDetails
     */
    public function createYotiUser($userId, ActivityDetails $activityDetails): void
    {
        $profile = $activityDetails->getProfile();
        // Create upload dir
        if (!is_dir($this->config->uploadDir())) {
            mkdir($this->config->uploadDir(), 0777, true);
        }

        $meta = [];
        $attrsArr = array_keys(self::profileFields());

        foreach ($attrsArr as $attrName) {
            if ($attrObj = $profile->getProfileAttribute($attrName)) {
                $value = $attrObj->getValue();
                if (null !== $value && $attrName === UserProfile::ATTR_DATE_OF_BIRTH) {
                    $value = $value->format('d-m-Y');
                }
                $meta[$attrName] = $value;
            }
        }

        $selfieFilename = null;
        $selfie = $profile->getSelfie();
        if ($selfie) {
            $selfieFilename = $this->createUniqueFilename($selfie->getValue()->getBase64Content(), 'png');
            file_put_contents($this->config->uploadDir() . '/' . $selfieFilename, $selfie->getValue());
            unset($meta[UserProfile::ATTR_SELFIE]);
            $meta = array_merge(
                [self::SELFIE_FILENAME => $selfieFilename],
                $meta
            );
        }

        // Extract age verification values if the option is set in the Yoti Hub
        // and in the Yoti's config in WP admin
        $ageVerificationsArr = $this->processAgeVerifications($profile);
        foreach ($ageVerificationsArr as $ageAttr => $result) {
            $ageAttr = str_replace(':', '_', ucwords($ageAttr, '_'));
            $meta[$ageAttr] = $result;
        }

        update_user_meta($userId, 'yoti_user.profile', $meta);
        update_user_meta($userId, 'yoti_user.identifier', $activityDetails->getRememberMeId());
    }

    /**
     * Get a unique file name.
     *
     * @param string $prefix
     * @param string $extension
     *
     * @return string
     */
    private function createUniqueFilename($prefix, $extension): string
    {
        // Get last user meta ID to prevent filename collision.
        global $wpdb;
        $suffix = '0';
        if ($maxId = $wpdb->get_var('SELECT MAX(umeta_id) FROM wp_usermeta')) {
            $suffix = (int) $maxId;
        }

        return md5(uniqid($prefix, true)) . '-' . ((string) $suffix) . '.' . $extension;
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    private function userExists(int $userId): bool
    {
        return $userId === wp_get_current_user()->ID || get_user_by('id', $userId);
    }

    /**
     * Delete Yoti user profile.
     *
     * @param int $userId WP user id
     */
    private function deleteYotiUser($userId): void
    {
        $dbProfile = $this->getUserProfile($userId);
        if ($dbProfile === false) {
            return;
        }

        // Remove user image.
        $filePath = isset($dbProfile[self::SELFIE_FILENAME])
            ? $this->config->uploadDir() . '/' . $dbProfile[self::SELFIE_FILENAME]
            : false;

        if ($filePath && is_file($filePath)) {
            if (!unlink($filePath)) {
                throw UnlinkException::couldNotDeleteImage();
            }
        }

        // Remove user metadata.
        delete_user_meta($userId, 'yoti_user.identifier');
        delete_user_meta($userId, 'yoti_user.profile');
    }

    /**
     * Log user by ID.
     *
     * @param int $userId
     */
    private function loginUser($userId): void
    {
        $user = get_user_by('id', $userId);
        if ($user === false) {
            return;
        }

        wp_set_current_user($userId, $user->user_login);
        wp_set_auth_cookie($userId);
        do_action('wp_login', $user->user_login, $user);
    }

    /**
     * Get user profile by ID.
     *
     * @param int $userId
     *
     * @return array<string,mixed>|false
     */
    public function getUserProfile($userId)
    {
        $dbProfile = get_user_meta($userId, 'yoti_user.profile');
        $dbProfile = reset($dbProfile);

        return $dbProfile;
    }

    /**
     * Get Selfie URL.
     *
     * @param int $userId
     * @param array<string,mixed>|null $dbProfile
     *
     * @return string|null
     */
    public function selfieUrl($userId, $dbProfile = null): ?string
    {
        $dbProfile = $dbProfile ?? $this->getUserProfile($userId);
        if ($dbProfile === false) {
            return null;
        }

        if (!isset($dbProfile[self::SELFIE_FILENAME])) {
            return null;
        }

        $selfieFullPath = $this->config->uploadDir() . '/' . $dbProfile[self::SELFIE_FILENAME];
        if (!is_file($selfieFullPath)) {
            return null;
        }

        $currentUser = wp_get_current_user();
        $isAdmin = in_array('administrator', $currentUser->roles, true);

        $queryData = [
            'yoti-select' => '1',
            'action' => 'bin-file',
            'field' => 'selfie',
        ];

        if ($isAdmin) {
            $queryData['user_id'] = (string) $userId;
        }

        $siteUrl = site_url('wp-login.php') . '?' . http_build_query($queryData);

        return wp_nonce_url($siteUrl, Constants::NONCE_ACTION, Constants::NONCE_ACTION);
    }

    /**
     * Attempt to connect by email
     *
     * @param ActivityDetails $activityDetails
     *
     * @return int|null
     */
    private function linkByEmail(ActivityDetails $activityDetails): ?int
    {
        $userId = null;
        $email = null;

        if ($emailAttr = $activityDetails->getProfile()->getEmailAddress()) {
            $email = $emailAttr->getValue();
        }

        if ($email && $this->config->linkNewUsersByEmail()) {
            $user = get_user_by('email', $email);
            if ($user) {
                $userId = $user->ID;
                $this->createYotiUser($userId, $activityDetails);
            }
        }
        return $userId;
    }
}
