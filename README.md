# Yoti Wordpress SDK #

Welcome to the Yoti Wordpress SDK. This repo contains the tools you need to quickly integrate your Wordpress back-end with Yoti, so that your users can share their identity details with your application in a secure and trusted way.    

## Table of Contents

1) [An Architectural view](#an-architectural-view) -
High level overview of integration

2) [References](#references)-
Guides before you start

3) [Requirements](#requirements)-
Everything you need to get started

4) [Installing the SDK](#installing-the-sdk)-
How to install our SDK

5) [Plugin Setup](#plugin-setup)-
How to set up the plugin in Wordpress

6) [Setting up your Yoti Application](#setting-up-your-yoti-application)-
Setting up your Yoti Application in Wordpress

7) [Allowing new registrations](#allowing-new-registrations)- 
Extra features in WordPress

8) [Linking existing accounts to use Yoti authentication](#linking-existing-accounts-to-use-yoti-authentication)

9) [API Coverage](#api-coverage)-
Attributes defined

10) [Yoti Docker](#yoti-docker)
How to set up Yoti Docker module

11) [Support](#support)-
Please feel free to reach out

## An Architectural view

Before you start your integration, here is a bit of background on how the integration works. To integrate your application with Yoti, your back-end must expose a GET endpoint that Yoti will use to forward tokens.
The endpoint can be configured in the Yoti Dashboard when you create/update your application. For more information on how to create an application please check our [developer page](https://www.yoti.com/developers/documentation/#login-button-setup).

The image below shows how your application back-end and Yoti integrate into the context of a Login flow.
Yoti SDK carries out for you steps 6, 7 and the profile decryption in step 8.

![alt text](/login_flow.png "Login flow")


Yoti also allows you to enable user details verification from your mobile app by means of the Android (TBA) and iOS (TBA) SDKs. In that scenario, your Yoti-enabled mobile app is playing both the role of the browser and the Yoti app. Your back-end doesn't need to handle these cases in a significantly different way. You might just decide to handle the `User-Agent` header in order to provide different responses for desktop and mobile clients.

## References

* [AES-256 symmetric encryption][]
* [RSA pkcs asymmetric encryption][]
* [Protocol buffers][]
* [Base64 data][]

[AES-256 symmetric encryption]:   https://en.wikipedia.org/wiki/Advanced_Encryption_Standard
[RSA pkcs asymmetric encryption]: https://en.wikipedia.org/wiki/RSA_(cryptosystem)
[Protocol buffers]:               https://en.wikipedia.org/wiki/Protocol_Buffers
[Base64 data]:                    https://en.wikipedia.org/wiki/Base64

## Requirements

This SDK works with the WordPress business plan package.

## Installing the SDK
You can install the Yoti SDK in two ways:

### By importing the Yoti SDK inside your project:

1) Log on to the admin console of your Wordpress website. e.g. Https://www.wordpressurl.org.uk/wp-admin
2) Navigate to at `Plugins > Add New`.
3) Search for Yoti and install and activate the plug in.

### By using this repos (For Mac & Linux users)

1) Download and unzip this repository, or, clone this repository
2) Run `./pack-plugin.sh`. This will download the Yoti PHP SDK, and place it within the plugin directory.
3) On completion of step 2, you will have a file called `yoti-wordpress-(version)-edge.zip`.
4) Upload this file on WordPress at `Plugins > Add New`, then click `Upload Plugin`.
5) Once installed, click `Activate Plugin`.

## Plugin Setup

To set things up, navigate on WordPress to `Settings > Yoti`.
 
 Here you will be asked to add the following information:
 
Yoti App ID
Yoti Scenario ID

Yoti SDK ID

Company Name

Yoti PEM File

Where:

- `Yoti App ID` is unique identifier for your specific application.

- `Yoti Scenario ID` is used to render the inline QR code.

- `Yoti SDK ID` is the SDK identifier generated by Yoti Dashboard in the Key tab when you create your app. Note this is not your Application Identifier which is needed by your client-side code.

- `Company Name` this will replace WordPress wording in the warning message which is displayed on the custom login form.

- `Yoti PEM File` is the application pem file. It can be downloaded only once from the Keys tab in your Yoti Dashboard.

Please do not open the pem file as this might corrupt the key and you will need to create a new application.

## Setting up your Yoti Application

Specify the basic details of your application such as the name, description and optional logo. These details can be whatever you like and will not affect the plugins functionality.

The `Data` tab - Specify any attributes you like, at this time, you must choose at least one. It is recommend you choose `Given Name(s)`, `Family Name` and `Email Address` at a minimum, if you plan to allow new user registrations.

The `Integration` tab - Here is where you specify the callback URL. This is found on your WordPress settings page. __NOTE__: If you get redirected to your WordPress frontpage instead of the Admin area, simply add `/wp-admin` to the URL.

## Allowing new registrations
 
By default, this is not enabled for security. Ticking the box and saving your changes allows a new user to Register and Log in by using thier Yoti. 
 
A new user who registeres this way will be set to the `Subscriber` role in WordPress.
 
If left disabled, if a new user tries to scan the Yoti QR code, they will be redirected back to the login page with an error message displayed.

## API Coverage

* Activity Details
    * [X] User ID `user_id`
    * [X] Profile
        * [X] Photo `selfie`
        * [X] Given Names `given_names`
        * [X] Family Name `family_name`
        * [X] Mobile Number `phone_number`
        * [X] Email address `email_address`
        * [X] Date of Birth `date_of_birth`
        * [X] Address `postal_address`
        * [X] Gender `gender`
        * [X] Nationality `nationality`
        
## Yoti Docker
This is a Docker module for WordPress including Yoti plugin.

### Setup
To try out our Docker module, clone this repos and run the following commands:

`cd yoti-wordpress` if this is the directory where you cloned the repos.

`docker-compose build` to rebuild the images if you have modified `docker-compose.yml` file.

`docker-compose up -d` to build the containers.        

After the command has finished running, browse the link below and follow the instructions.

`http://localhost:7000`

Yoti plugin will be installed along side WordPress, activate it and follow our plugin setup in [section 5](#yoti-plugin) to set it up.

### Removing docker containers
Run the following commands to remove docker containers:

`docker-compose stop` and 

`docker-compose rm`

## Support

For any questions or support please email [sdksupport@yoti.com](mailto:sdksupport@yoti.com).
Please provide the following the get you up and working as quick as possible:

- Computer Type
- OS Version
- Screenshot


