=== Yoti ===

Contributors: Moussa Sidibe, yotiwordpress
Tags: identity, verification, login, form, 2 factor, 2 step authentication, 2FA, access, privacy, authentication, security, sign in, two factor
Requires at least: 3.0.1
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.4.0
License: GNU v3
License URI: https://www.gnu.org/licenses/gpl.txt

Yoti is a digital identity platform that simply allows a user to securely log in to your WordPress site faster, password free!

== Description ==

Yoti is a digital identity app that lets people log in to websites without a password and prove their identity online.
Just click on the login with Yoti button, scan secure QR code with the Yoti app and that’s it.
Get the Yoti plugin and let people log in to your WordPress websites without entering a password. Click here to learn [more.](https://www.yoti.com)

Here is a quick video on how to install the plugin in less than 5 minutes:

https://www.youtube.com/watch?v=kzltLNws1cQ

== Why does your website need Yoti ==

* Stops keyloggers, by not having to type usernames and passwords.
* Stops phishing attacks, safer website access without usernames and passwords.
* Privacy by design. We use advanced hybrid 256-bit encryption to secure your personal information.
* No Passwords. Your users login securely by scanning a QR code.
* KYC checks. Verify the identities of your website users.
* Age verification. Verify the age of users of your website and control access to age-restricted content.

Download the free Yoti app [Android](https://play.google.com/store/apps/details?id=com.yoti.mobile.android.live)
Download the free Yoti app [IOS](https://itunes.apple.com/us/app/yoti/id983980808?ls=1&mt=8)

== Installation ==

= Step1: Installing the plugin =

* From the “Plugins” menu search for “Yoti”,
* click “Install Now” and then “Activate”.
* To store images in a custom directory, edit your `wp-config.php` file to add a new constant called YOTI_UPLOAD_DIR with an absolute path:

  define('YOTI_UPLOAD_DIR', '/path/to/images/');

  By default, images are stored in `WP_CONTENT_DIR . '/uploads/yoti'`

= Step 2: Setting up your Yoti Application =

Visit the Yoti Dashboard [here](https://www.yoti.com/dashboard/login-organisations) to create a new application for your organisation/business.

Specify the basic details of your application such as the name, description and optional logo. These details can be whatever you desire and will not affect the plugin’s functionality.

The main page - Edit the application and set your website URL in the 'Application domain' section, e.g https:yourwebsite.com

The Scenarios tab -  Scenarios are different instances where you request users for information using Yoti. e.g verify your users' age online or in person or quickly sign in users to your website without passwords. This plugin only support single scenario.

* Specify a name for your scenario.
* Specify what information you want to request with this scenario.
* And finally, provide a callback URL so we know where to send your users after they have used Yoti. This URL must be a subdomain of your applications' domain.

The Keys tab – Here is where your keys are generated which will be inputted into the plugin settings. You will need to download your pem file and store it somewhere safe as it will be used as part of the plugin set up.

== Frequently Asked Questions ==

For a more detailed explanation please go to our github [page.](https://github.com/getyoti/yoti-wordpress)

For further support please feel free to email us at: sdksupport@yoti.com

For FAQ please click [here.](https://yoti.zendesk.com/hc/en-us/categories/201129409-Business-FAQs)

== Screenshots ==

1. Settings to set up and configure the plugin
2. Yoti widget added by the plugin
3. Example of logging in with Yoti by scanning the QR code
4. Yoti profile page with all the user attributes

== Changelog ==

Here you can find the changes for each version:

1.4.0

* Allow QR type to be configured
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
