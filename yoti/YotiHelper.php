<?php

use Yoti\YotiClient;
use Yoti\ActivityDetails;
use Yoti\Entity\Profile;
use Yoti\Entity\AgeVerification;

require_once __DIR__ . '/sdk/boot.php';

/**
 * Class YotiHelper
 *
 * @author Yoti Ltd <sdksupport@yoti.com>
 */
class YotiHelper
{
    /**
     * Yoti config option name
     */
    const YOTI_CONFIG_OPTION_NAME = 'yoti_config';

    const SELFIE_FILENAME = 'selfie_filename';

    /**
     * Yoti SDK javascript library.
     */
    const YOTI_SDK_JAVASCRIPT_LIBRARY = 'https://sdk.yoti.com/clients/browser.2.1.0.js';

    /**
     * @var array
     */
    public static $profileFields = [
        Profile::ATTR_SELFIE => 'Selfie',
        Profile::ATTR_FULL_NAME => 'Full Name',
        Profile::ATTR_GIVEN_NAMES => 'Given Names',
        Profile::ATTR_FAMILY_NAME => 'Family Name',
        Profile::ATTR_PHONE_NUMBER => 'Mobile Number',
        Profile::ATTR_EMAIL_ADDRESS => 'Email Address',
        Profile::ATTR_DATE_OF_BIRTH => 'Date Of Birth',
        Profile::ATTR_POSTAL_ADDRESS => 'Postal Address',
        Profile::ATTR_GENDER => 'Gender',
        Profile::ATTR_NATIONALITY => 'Nationality',
    ];

    /**
     * Yoti WordPress SDK identifier.
     */
    const SDK_IDENTIFIER = 'WordPress';

    private $config;

    public function __construct()
    {
        $this->config = self::getConfig();
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
            self::setFlash('Could not get Yoti token.', 'error');

            return FALSE;
        }

        // Init Yoti client and attempt to request user details
        try
        {
            $yotiClient = new YotiClient(
                $this->config['yoti_sdk_id'],
                $this->config['yoti_pem']['contents'],
                YotiClient::DEFAULT_CONNECT_API,
                self::SDK_IDENTIFIER
            );
            $activityDetails = $yotiClient->getActivityDetails($token);
            $profile = $activityDetails->getProfile();
        }
        catch (Exception $e)
        {
            self::setFlash('Yoti failed to connect to your account.', 'error');

            return FALSE;
        }

        if (!$this->passedAgeVerification($profile)) {
            self::setFlash('Could not log you in as you haven\'t passed the age verification', 'error');
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
                        catch (Exception $e)
                        {
                            $errMsg = $e->getMessage();
                        }
                    }
                    else
                    {
                        self::storeYotiUser($activityDetails);
                        wp_redirect(wp_login_url(!empty($_GET['redirect']) ? $_GET['redirect'] : home_url()));
                        exit;
                    }
                }

                // No user id? no account
                if (!$wpYotiUid)
                {
                    // if couldn't create user then bail
                    $this->setFlash("Could not create user account. $errMsg", 'error');

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
                self::setFlash('This Yoti account is already linked to another account.', 'error');
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
            self::setFlash('Your Yoti profile was successfully unlinked from your account.');

            return TRUE;
        }

        self::setFlash('Could not unlink from Yoti.');

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

        $file = YotiHelper::uploadDir() . "/{$dbProfile[$field]}";
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
    public function passedAgeVerification(Profile $profile)
    {
        return !($this->config['yoti_age_verification'] && !$this->oneAgeIsVerified($profile));
    }

    private function oneAgeIsVerified(Profile $profile)
    {
        $ageVerificationsArr = self::processAgeVerifications($profile);
        return empty($ageVerificationsArr) || in_array('Yes', array_values($ageVerificationsArr));
    }

    /**
     * @param Profile $profile
     *
     * @return array
     */
    private function processAgeVerifications(Profile $profile)
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
     * @param \Yoti\ActivityDetails $activityDetails
     */
    public static function storeYotiUser(ActivityDetails $activityDetails)
    {
        $_SESSION['yoti-user'] = serialize($activityDetails);
    }

    /**
     * Retrieve Yoti user data from the session.
     *
     * @return ActivityDetails|null
     */
    public static function getYotiUserFromStore()
    {
        return $_SESSION && array_key_exists('yoti-user', $_SESSION) ? unserialize($_SESSION['yoti-user']) : NULL;
    }

    /**
     * Remove Yoti user data from the session.
     */
    public static function clearYotiUserStore()
    {
        unset($_SESSION['yoti-user']);
    }

    /**
     * Set user notification message.
     *
     * @param $message
     * @param string $type
     */
    public static function setFlash($message, $type = 'message')
    {
        $_SESSION['yoti-connect-flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get user notification message.
     *
     * @return mixed
     */
    public static function getFlash()
    {
        $message = NULL;
        if (!empty($_SESSION['yoti-connect-flash']))
        {
            $message = $_SESSION['yoti-connect-flash'];
            self::clearFlash();
        }
        return $message;
    }

    /**
     * Clear Yoti flash message.
     */
    public static function clearFlash()
    {
        unset($_SESSION['yoti-connect-flash']);
    }

    /**
     * Generate Yoti unique username.
     *
     * @param Profile $profile
     * @param string $prefix
     *
     * @return null|string
     */
    private function generateUsername(Profile $profile, $prefix = 'yoti.user')
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
        $userQuery = new WP_User_Query(
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
    private function getUserGivenName(Profile $profile)
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
        $userQuery = new WP_User_Query(
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
     * @throws Exception
     */
    private function createUser(ActivityDetails $activityDetails)
    {
        $profile = $activityDetails->getProfile();
        $username = $this->generateUsername($profile);
        $password = $this->generatePassword();
        $userProvidedEmail = $profile->getEmailAddress()->getValue();
        // If user has provided an email address and it's not in use then use it,
        // otherwise use Yoti generic email
        $userProvidedEmailCanBeUsed = is_email($userProvidedEmail) && !get_user_by('email', $userProvidedEmail);
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
        $users = (new WP_User_Query(
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
        if (!is_dir(self::uploadDir())) {
            mkdir(self::uploadDir(), 0777, TRUE);
        }

        $meta = [];
        $attrsArr = array_keys(self::$profileFields);

        foreach ($attrsArr as $attrName) {
            if ($attrObj = $profile->getProfileAttribute($attrName)) {
                $value = $attrObj->getValue();
                if (NULL !== $value && $attrName === Profile::ATTR_DATE_OF_BIRTH) {
                    $value = $value->format('d-m-Y');
                }
                $meta[$attrName] = $value;
            }
        }

        $selfieFilename = NULL;
        $selfie = $profile->getSelfie();
        if ($selfie) {
            $selfieFilename = md5("selfie_$wpUserId") . '.png';
            file_put_contents(self::uploadDir() . "/$selfieFilename", $selfie->getValue());
            unset($meta[Profile::ATTR_SELFIE]);
            $meta = array_merge(
                [self::SELFIE_FILENAME => $selfieFilename],
                $meta
            );
        }

        // Extract age verification values if the option is set in the dashboard
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
     * Delete Yoti user profile.
     *
     * @param int $userId WP user id
     */
    private function deleteYotiUser($userId)
    {
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
        do_action('wp_login', $user->user_login);
    }

    /**
     * Get user profile by ID.
     *
     * @param $userId
     *
     * @return mixed
     */
    public static function getUserProfile($userId)
    {
        $dbProfile = get_user_meta($userId, 'yoti_user.profile');
        $dbProfile = reset($dbProfile);

        return $dbProfile;
    }

    /**
     * Get Yoti upload dir.
     *
     * @return string
     */
    public static function uploadDir()
    {
        return WP_CONTENT_DIR . '/uploads/yoti';
    }

    /**
     * Get Yoti upload dir URL.
     *
     * @return string
     */
    public static function uploadUrl()
    {
        return content_url('/uploads/yoti');
    }

    /**
     * Get Yoti Config.
     *
     * @return array
     */
    public static function getConfig()
    {
        return maybe_unserialize(get_option(YotiHelper::YOTI_CONFIG_OPTION_NAME));
    }

    /**
     * Remove Yoti config option data from WordPress option table.
     */
    public static function deleteYotiConfigData()
    {
        delete_option(YotiHelper::YOTI_CONFIG_OPTION_NAME);
    }

    /**
     * Get Yoti app login URL.
     *
     * @return null|string
     */
    public static function getLoginUrl()
    {
        $config = self::getConfig();
        if (empty($config['yoti_app_id'])) {
            return NULL;
        }

        return YotiClient::getLoginUrl($config['yoti_app_id']);
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
        $email = $activityDetails->getProfile()->getEmailAddress()->getValue();

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