<?php

namespace Yoti\WP;

/**
 * Class Constants
 */
class Constants
{
    /** Yoti SDK javascript library. */
    public const YOTI_SDK_JAVASCRIPT_LIBRARY = 'https://www.yoti.com/share/client/';

    /** Yoti Hub URL. */
    public const YOTI_HUB_URL = 'https://hub.yoti.com';

    /** Yoti WordPress SDK identifier. */
    public const SDK_IDENTIFIER = 'WordPress';

    /** Yoti WordPress SDK version. */
    public const SDK_VERSION = '2.0.0';

    /** Nonce action used to verify requests. */
    public const NONCE_ACTION = 'yoti_verify';
}
