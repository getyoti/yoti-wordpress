# Yoti WordPress Plugin

Password free logins for your WordPress account


## Installation (For Mac & Linux users)

1) Download and unzip this repository, or, clone this repository
2) Run `./pack-plugin.sh`. This will Download the Yoti PHP SDK, and place it within the plugin directory.
3) On completion of step 2, you will have a file called `yoti-connect-(version)-edge.zip`.
4) Upload this file on WordPress at `Plugins > Add New`, then click `Upload Plugin`.
5) Once installed, click `Activate Plugin`.


## Setting up the plugin

To set things up, navigate on WordPress to `Settings > Yoti Connect`.

Here you will be asked to add the following information:

* Yoti App ID
* Yoti SDK ID
* Yoti PEM File

To get these, you must navigate to the [Yoti Dashboard](https://www.yoti.com/dashboard) and create a Yoti **Application** (Not page!)

### Setting up your Yoti Application

* Specify the basic details of your application such as the name, description and optional logo. These details can be whatever you like and will not affect the plugins functionality.
* The `Data` tab - Specify any attributes you like, at this time, you must choose at least one. It is recommend you choose `Given Name(s)`, `Family Name` and `Email Address` at a minimum, if you plan to allow new user registrations.
* The `Integration` tab - Here is where you specify the callback URL. This is found on your WordPress settings page. __NOTE__: If you get redirected to your WordPress frontpage instead of the Admin area, simply add `/wp-admin` to the URL.
* The `Keys` tab - Here is where you will find the App ID, the SDK ID and the link to download your PEM file for the WordPress plugin.


## Using the Plugin

### Allowing new registrations

By default, this is not enabled for security. Ticking the box and saving your changes allows a new user to Register and Log in by using thier Yoti. 

A new user who registeres this way will be set to the `Subscriber` role in WordPress.

If left disabled, if a new user tries to scan the Yoti QR code, they will be redirected back to the login page with an error message displayed.

### Linking existing accounts to use Yoti authentication

To allow your existing users to log in using Yoti instead of entering thier username/password combination, they must:

1) Log in as normal into WordPress
2) Navigate to `Users > Your Profile`
3) Scroll down to `Yoti Profile`
4) Click on the `Link account to Yoti` button
5) Scan the QR code and allow the share request

The User is now linked to Yoti authentication, at the log in screen they can click `Log in with Yoti`, scan the QR code, and they will be logged in.

## Help

If you require any assisatance, please navigate to the [official Yoti Contact](https://www.yoti.com/contact/) page, and will out the `I have a question about my Yoti` form, or check out our FAQs that can be found on that page.
