=== Yoti ===

Contributors: yotiwordpress
Tags: identity, verification, login, form, 2 factor, 2 step authentication, 2FA, access, privacy, authentication, security, sign in, two factor
Requires at least: 3.0.1
Tested up to: 5.5
Requires PHP: 7.2
Stable tag: 2.0.0
License: GNU v3
License URI: https://www.gnu.org/licenses/gpl.txt

Yoti is a digital identity platform that simply allows a user to securely log in to your WordPress site faster, password free!

== Description ==

Yoti is your digital identity. A global identity platform and free consumer app that puts your ID on your phone. It’s the simplest, safest, fastest way to prove your identity online and in person.

Founded in 2014, Yoti began a mission to become the world’s trusted identity platform. We’re committed to doing things differently to other tech companies – like promising to never mine or sell your data; Yoti is designed so that we couldn’t even if we wanted to.

== What Yoti can offer your site ==

* KYC checks by verifying the identities of your website users
* Age verification. Verify the age of users on your website and control access to age-restricted content.
* Prevents keyloggers, by not having to type usernames and passwords.Your users login securely by scanning a QR code.
* Privacy by design. We use advanced hybrid 256-bit encryption to secure your personal information.

*Note: The wordpress plugin is limited to basic functionality. Please use [PHP SDK](https://github.com/getyoti/yoti-php-sdk) for added features. For more information on our services and our products please visit our site: <https://www.yoti.com/>.*

Download the free Yoti app [Android](https://play.google.com/store/apps/details?id=com.yoti.mobile.android.live)
Download the free Yoti app [IOS](https://itunes.apple.com/us/app/yoti/id983980808?ls=1&mt=8)

== What is the journey for Wordpress users ==
1. Add a Yoti button on to your site.
2. Users will click the yoti button and a QR code will appear, the user will scan the QR code.
3. Redirect the users to your callback URL.

For examples on how to improve your customer experience please go here: <https://developers.yoti.com/yoti/scenario-examples>

Here is a quick video showing the flow:

https://youtu.be/nPjMA9Z-nks

= Contact us =
If you have any other questions please do not hesitate to contact <mailto:sdksupport@yoti.com>.

Once we have answered your question, we may contact you again to discuss Yoti products and services. If you’d prefer us not to do this, please let us know when you e-mail.

== Installation ==

= Step 1: Installing the plugin =

* From the *Plugins* menu search for *Yoti*, Click *Install Now* and then *Activate*.
* To store images in a custom directory, edit your `wp-config.php` file to add a new constant called *YOTI_UPLOAD_DIR* with an absolute path:

  define('YOTI_UPLOAD_DIR', '/path/to/images/');

  By default, images are stored in `WP_CONTENT_DIR . '/uploads/yoti'`

Note: you can also import out plugin, please see our [github pages](https://github.com/getyoti/yoti-wordpress) for more information

= Step 2: Setting up your Yoti Application =

Onboard your organisation with Yoti by visiting the Yoti Hub [here](https://hub.yoti.com/) and create a Yoti application to retrieve your
API keys.

Instructions on onboarding with Yoti are [here](https://developers.yoti.com/yoti/getting-started-hub).

Generating API keys instructions are [here](https://developers.yoti.com/yoti/generate-api-keys).

= STEP 3: Enable Widget =

Go back to your wordpress Yoti plugin settings and add in the API keys retrieved from your Yoti Hub account. Once complete, place your
widget on your desired page.

= Contact us =
If you have any other questions please do not hesitate to contact <mailto:sdksupport@yoti.com>.

Once we have answered your question, we may contact you again to discuss Yoti products and services. If you’d prefer us not to do this, please let us know when you e-mail.

== Changelog ==

Here you can find the changes for each version:

1.5.0

Release Date - 22 January 2020

* Scenario ID and button text can now be configured per widget

1.4.2

Release Date - 10 September 2019

* Default Curl request handler now verifies host and peer certificates. Only the unencrypted
  parts of the receipt were at risk, as user profiles are always encrypted.

1.4.1

Release Date - 21 August 2019

* Check that button has been configured before initialising
* Text fields are now trimmed to remove any leading/trailing whitespace

1.4.0

Release Date - 6 August 2019

* Widget now displays QR code in modal

1.3.2

Release Date - 3 July 2019

* Display unlink button on profile when only Remember Me ID is shared

1.3.1

Release Date - 27 March 2019

* Passing user object to wp_login hook

1.3.0

Release Date - 11 March 2019

* Allowing image directory to be configured
* Updated browser.js to 2.3.0

1.2.2

Release Date - 11 February 2019

* Fixed login by email when email is not available

1.2.1

Release Date - 8 February 2019

* Fixed username generation when given names or family name are unavailable

1.2.0

Release Date - 23 January 2019

* Update the Yoti PHP SDK to version 2

1.1.9

Release Date - 1 August 2018

* Update the Details and Installation section on the plugin page to reflect the changes from Yoti dashboard
* Update the banner on the plugin page

1.1.8

Release Date - 4 April 2018

* Update documentation for Yoti's plugin page on WordPress
* Update screenshots for Yoti's plugin page on WordPress

1.1.7

Release Date - 16 March 2018

* Integrate age verification functionality
* Display Full Name attribute

1.1.6

Release Date - 4 January 2018

* Integrate the new inline QR code version 2.0.1.
* Refactor Yoti button widget to follow WordPress widget standard.
* Show Yoti settings link on the plugins page after activation.
* Add admin notice display after Yoti plugin activation.

1.1.5

Release Date - 14 December 2017

* Integrate the new inline QR style for Yoti button.
* Apply WordPress widget style to Yoti button widget.

1.1.4

Release Date - 11 November 2017

* Integrate SDK identifier to track plugin usage.
* Apply Yoti style to the unlink button.
* Add Company Name to Yoti settings.

1.1.3

Release Date - 14 August2017

* Change Yoti generic user ID to use the combination of user given names and family name.
* Change Yoti generic email to use user email address if provided.

1.1.2

Release Date - 3 August 2017

* Remove Yoti plugin config data when the plugin is uninstalled and removed from Wordpress.
* Rename Yoti plugin from `Yoti Connect` to Yoti.
* Change Yoti generic user ID from yoti-connect-x to yoti.userx, e.g yoti.user1.

1.1.1

Release Date - 20 July 2017

* Fix a bug that was occurring when a user decides not to link their account to Yoti during the login process.

1.1.0

Release Date - 20 July 2017

* Remove PHP module mcrypt dependency from WordPress plugin.

1.0.9

Release Date - 19 May 2017

* Add plugin documentation.

1.0.0

Release Date – 12 March 2017

* First release.

== Upgrade Notice ==

This is N/A for now - but our plugin will get better and better so we will let you know!
