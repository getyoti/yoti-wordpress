<?php

namespace Yoti\WP\Service;

use Yoti\Profile\ActivityDetails;
use Yoti\Profile\UserProfile;
use Yoti\WP\Client\ClientFactoryInterface;
use Yoti\WP\Config;
use Yoti\WP\Message;

/**
 * Class Profile
 */
class Profile
{
    /** Selfie key */
    const SELFIE_FILENAME = 'selfie_filename';

    /**
     * @return array
     */
    public static function profileFields() {
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
     * @var array
     */
    private $config;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @param ClientFactoryInterface $clientFactory
     */
    public function __construct(ClientFactoryInterface $clientFactory)
    {
        $this->clientFactory = $clientFactory;
        $this->config = Config::load();
    }

    /**
     * Login user
     *
     * @param NULL $currentUser
     * @return bool
     */
    public function link($currentUser = NULL)
    {
        if (!$currentUser)
        {
            $currentUser = wp_get_current_user();
        }

        $token = (!empty($_GET['token'])) ? $_GET['token'] : NULL;

        // If no token then ignore
        if (!$token)
        {
            Message::setFlash('Could not get Yoti token.', 'error');

            return FALSE;
        }

        // Init Yoti client and attempt to request user details
        try
        {
            $yotiClient = $this->clientFactory->getClient();
            $activityDetails = $yotiClient->getActivityDetails($token);
            $profile = $activityDetails->getProfile();
        }
        catch (\Exception $e)
        {
            Message::setFlash('Yoti failed to connect to your account.', 'error');

            return FALSE;
        }

        if (!$this->passedAgeVerification($profile)) {
            Message::setFlash('Could not log you in as you haven\'t passed the age verification', 'error');
            return FALSE;
        }

        // Check if Yoti user exists
        $wpYotiUid = $this->getUserIdByYotiId($activityDetails->getRememberMeId());

        // If Yoti user exists in db but isn't an actual account then remove it from yoti table
        if ($wpYotiUid && $currentUser->ID !== $wpYotiUid && !get_user_by('id', $wpYotiUid))
        {
            // remove users account
            $this->deleteYotiUser($wpYotiUid);
        }

        // If user isn't logged in
        if (!$currentUser->ID)
        {
            // Register new user
            if (!$wpYotiUid)
            {
                $errMsg = NULL;
                // Attempt to connect by email
                $wpYotiUid = $this->shouldLoginByEmail($activityDetails, $this->config['yoti_user_email']);

                // If config only existing enabled then check if user exists, if not then redirect
                // to login page
                if (!$wpYotiUid)
                {
                    if (empty($this->config['yoti_only_existing']))
                    {
                        try
                        {
                            $wpYotiUid = $this->createUser($activityDetails);
                        }
                        catch (\Exception $e)
                        {
                            $errMsg = $e->getMessage();
                        }
                    }
                    else
                    {
                        self::storeYotiUser($activityDetails);
                        $redirect = !empty($_GET['redirect']) ? $_GET['redirect'] : home_url();
                        wp_safe_redirect(wp_login_url($redirect));
                        exit;
                    }
                }

                // No user id? no account
                if (!$wpYotiUid)
                {
                    // if couldn't create user then bail
                    Message::setFlash("Could not create user account. $errMsg", 'error');

                    return FALSE;
                }
            }

            // Log user in
            $this->loginUser($wpYotiUid);
        }
        else
        {
            // If current logged in user doesn't match Yoti user registered then bail
            if ($wpYotiUid && $currentUser->ID !== $wpYotiUid)
            {
                Message::setFlash('This Yoti account is already linked to another account.', 'error');
            }
            // If WP user not found in Yoti table then create new Yoti user
            elseif (!$wpYotiUid)
            {
                $this->createYotiUser($currentUser->ID, $activityDetails);
            }
        }

        return TRUE;
    }

    /**
     * Unlink account from currently logged in user
     */
    public function unlink()
    {
        $currentUser = wp_get_current_user();

        // Unlink user account from Yoti
        if (is_user_logged_in())
        {
            $this->deleteYotiUser($currentUser->ID);
            Message::setFlash('Your Yoti profile was successfully unlinked from your account.');

            return TRUE;
        }

        Message::setFlash('Could not unlink from Yoti.');

        return FALSE;
    }

    /**
     * Display user profile image
     *
     * @param $field
     * @param null $userId
     */
    public function binFile($field, $userId = NULL)
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles, TRUE))
        {
            $user = get_user_by('id', $userId);
        }

        if (!$user)
        {
            return;
        }

        $field = ($field === 'selfie') ? self::SELFIE_FILENAME : $field;
        $dbProfile = self::getUserProfile($user->ID);
        if (!$dbProfile || !array_key_exists($field, $dbProfile))
        {
            return;
        }

        $file = Config::uploadDir() . "/{$dbProfile[$field]}";
        if (!file_exists($file))
        {
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
     * @param Profile $profile
     * @return bool
     */
    public function passedAgeVerification(UserProfile $profile)
    {
        return !($this->config['yoti_age_verification'] && !$this->oneAgeIsVerified($profile));
    }

    private function oneAgeIsVerified(UserProfile $profile)
    {
        $ageVerificationsArr = self::processAgeVerifications($profile);
        return empty($ageVerificationsArr) || in_array('Yes', array_values($ageVerificationsArr));
    }

    /**
     * @param Profile $profile
     *
     * @return array
     */
    private function processAgeVerifications(UserProfile $profile)
    {
        $ageVerifications = $profile->getAgeVerifications();
        $ageVerificationsAttr = [];
        foreach($ageVerifications as $attr => $ageVerification) { /** @var AgeVerification $ageVerification*/
            $ageVerificationsAttr[$attr] = $ageVerification->getResult() ? 'Yes' : 'No';
        }
        return $ageVerificationsAttr;
    }

    /**
     * Save Yoti user data in the session.
     *
     * @param ActivityDetails $activityDetails
     */
    public function storeYotiUser(ActivityDetails $activityDetails)
    {
        $_SESSION['yoti-user'] = serialize($activityDetails);
    }

    /**
     * Retrieve Yoti user data from the session.
     *
     * @return ActivityDetails|null
     */
    public function getYotiUserFromStore()
    {
        return $_SESSION && array_key_exists('yoti-user', $_SESSION) ? unserialize($_SESSION['yoti-user']) : NULL;
    }

    /**
     * Remove Yoti user data from the session.
     */
    public function clearYotiUserStore()
    {
        unset($_SESSION['yoti-user']);
    }

    /**
     * Generate Yoti unique username.
     *
     * @param Profile $profile
     * @param string $prefix
     *
     * @return null|string
     */
    private function generateUsername(UserProfile $profile, $prefix = 'yoti.user')
    {
        $givenName = $this->getUserGivenName($profile);
        if ($familyNameAttr = $profile->getFamilyName()) {
            $familyName = $familyNameAttr->getValue();
        }

        // If GivenName and FamilyName are provided use as user nickname/login
        if(NULL !== $givenName && NULL !== $familyName) {
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
                'count_total' => TRUE,
            ]
        );

        // Generate Yoti unique username
        $userCount = (int)$userQuery->get_total();
        $username = $prefix;
        // If we already have a login with this prefix then generate another login
        if ($userCount > 0) {
            do
            {
                $username = $prefix . ++$userCount;
            }
            while (get_user_by('login', $username));
        }

        return $username;
    }

    /**
     * If user has more than one given name return the first one
     *
     * @param Profile $profile
     * @return null|string
     */
    private function getUserGivenName(UserProfile $profile)
    {
        $givenName = NULL;
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
    private function generateEmail($prefix = 'yoti.user', $domain = 'example.com')
    {
        // Get the number of user_email that starts with yotiuser-
        $userQuery = new \WP_User_Query(
            [
                // Search for Yoti users starting with the prefix yotiuser-.
                'search' => $prefix . '*',
                // Search the `user_email` field only.
                'search_columns' => ['user_email'],
                // Return user count
                'count_total' => TRUE,
            ]
        );

        // Generate the default email
        $email = $prefix . "@$domain";

        // Generate Yoti unique user email
        $userCount = (int)$userQuery->get_total();
        if ($userCount > 0)
        {
            do
            {
                $email = $prefix . ++$userCount . "@$domain";
            }
            while (get_user_by('email', $email));
        }

        return $email;
    }

    /**
     * Generate Yoti user password.
     *
     * @param int $length
     * @return mixed
     */
    private function generatePassword($length = 10)
    {
        return wp_generate_password($length);
    }

    /**
     * Create user profile with Yoti data.
     *
     * @param ActivityDetails $activityDetails
     *
     * @return int
     *
     * @throws \Exception
     */
    private function createUser(ActivityDetails $activityDetails)
    {
        $profile = $activityDetails->getProfile();
        $username = $this->generateUsername($profile);
        $password = $this->generatePassword();

        // Check that email is available and valid.
        $userProvidedEmailCanBeUsed = FALSE;
        if ($emailAttr = $profile->getEmailAddress()) {
            $userProvidedEmail = $emailAttr->getValue();
            $userProvidedEmailCanBeUsed = is_email($userProvidedEmail) && !get_user_by('email', $userProvidedEmail);
        }

        // If user has provided an email address and it's not in use then use it,
        // otherwise use Yoti generic email
        $email = $userProvidedEmailCanBeUsed ? $userProvidedEmail : $this->generateEmail();

        $wpUserId = wp_create_user($username, $password, $email);
        // If there has been an error creating the user, stop the process
        if(is_wp_error($wpUserId)) {
            throw new \Exception($wpUserId->get_error_message(), 401);
        }

        $this->createYotiUser($wpUserId, $activityDetails);

        return $wpUserId;
    }

    /**
     * Get Yoti user by ID.
     *
     * @param $yotiId
     *
     * @return int
     */
    private function getUserIdByYotiId($yotiId)
    {
        // Query for users based on the meta data
        $users = (new \WP_User_Query(
            [
                'meta_key' => 'yoti_user.identifier',
                'meta_value' => $yotiId,
            ]
        ))->get_results();
        $user = reset($users);

        return $user ? $user->ID : NULL;
    }

    /**
     * Create Yoti user profile.
     *
     * @param $wpUserId
     * @param ActivityDetails $activityDetails
     */
    public function createYotiUser($wpUserId, ActivityDetails $activityDetails)
    {
        $profile = $activityDetails->getProfile();
        // Create upload dir
        if (!is_dir(Config::uploadDir())) {
            mkdir(Config::uploadDir(), 0777, TRUE);
        }

        $meta = [];
        $attrsArr = array_keys(self::profileFields());

        foreach ($attrsArr as $attrName) {
            if ($attrObj = $profile->getProfileAttribute($attrName)) {
                $value = $attrObj->getValue();
                if (NULL !== $value && $attrName === UserProfile::ATTR_DATE_OF_BIRTH) {
                    $value = $value->format('d-m-Y');
                }
                $meta[$attrName] = $value;
            }
        }

        $selfieFilename = NULL;
        $selfie = $profile->getSelfie();
        if ($selfie) {
            $selfieFilename = self::createUniqueFilename($selfie->getValue()->getBase64Content(), 'png');
            file_put_contents(Config::uploadDir() . '/' . $selfieFilename, $selfie->getValue());
            unset($meta[UserProfile::ATTR_SELFIE]);
            $meta = array_merge(
                [self::SELFIE_FILENAME => $selfieFilename],
                $meta
            );
        }

        // Extract age verification values if the option is set in the Yoti Hub
        // and in the Yoti's config in WP admin
        $ageVerificationsArr = $this->processAgeVerifications($profile);
        foreach($ageVerificationsArr as $ageAttr => $result) {
            $ageAttr = str_replace(':', '_', ucwords($ageAttr, '_'));
            $meta[$ageAttr] = $result;
        }

        update_user_meta($wpUserId, 'yoti_user.profile', $meta);
        update_user_meta($wpUserId, 'yoti_user.identifier', $activityDetails->getRememberMeId());
    }

    /**
     * Get a unique file name.
     *
     * @param string $prefix
     * @param string $extension
     * @return string
     */
    private function createUniqueFilename($prefix, $extension)
    {
        // Get last user meta ID to prevent filename collision.
        global $wpdb;
        $suffix = '0';
        if ($maxId = $wpdb->get_var('SELECT MAX(umeta_id) FROM wp_usermeta')) {
            $suffix = (int) $maxId;
        }

        return md5(uniqid($prefix, TRUE)) . '-' . ((string) $suffix) . '.' . $extension;
    }

    /**
     * Delete Yoti user profile.
     *
     * @param int $userId WP user id
     */
    private function deleteYotiUser($userId)
    {
        // Remove user image.
        $dbProfile = self::getUserProfile($userId);
        $filePath = isset($dbProfile[self::SELFIE_FILENAME]) ? Config::uploadDir() . '/' . $dbProfile[self::SELFIE_FILENAME] : FALSE;
        if ($filePath && is_file($filePath)) {
            if (!unlink($filePath)) {
                self::setFlash('Could not delete user image.', 'error');
            }
        }
        // Remove user metadata.
        delete_user_meta($userId, 'yoti_user.identifier');
        delete_user_meta($userId, 'yoti_user.profile');
    }

    /**
     * Log user by ID.
     *
     * @param $userId
     */
    private function loginUser($userId)
    {
        $user = get_user_by('id', $userId);
        wp_set_current_user($userId, $user->user_login);
        wp_set_auth_cookie($userId);
        do_action('wp_login', $user->user_login, $user);
    }

    /**
     * Get user profile by ID.
     *
     * @param $userId
     *
     * @return mixed
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
     * @param string $userId
     * @return string
     */
    public function selfieUrl($userId)
    {
        $currentUser = wp_get_current_user();
        $isAdmin = in_array('administrator', $currentUser->roles, TRUE);
        $userIdUrlPart = ($isAdmin ? '&user_id=' . esc_html($userId) : '');
        $siteUrl = site_url('wp-login.php') . '?yoti-select=1&action=bin-file&field=selfie' . $userIdUrlPart;
        return wp_nonce_url($siteUrl, 'yoti_verify', 'yoti_verify');
    }

    /**
     * Attempt to connect by email
     *
     * @param Profile $profile
     * @param string $emailConfig
     *
     * @return int|null
     */
    private function shouldLoginByEmail(ActivityDetails $activityDetails, $emailConfig)
    {
        $wpYotiUid = NULL;
        $email = NULL;

        if ($emailAttr = $activityDetails->getProfile()->getEmailAddress()) {
            $email = $emailAttr->getValue();
        }

        if ($email && !empty($emailConfig)) {
            $byMail = get_user_by('email', $email);
            if ($byMail) {
                $wpYotiUid = $byMail->ID;
                $this->createYotiUser($wpYotiUid, $activityDetails);
            }
        }
        return $wpYotiUid;
    }
}