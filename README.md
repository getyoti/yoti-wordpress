**This plugin is no longer maintained. The PHP SDK, which this plugin makes calls through, is still available at <https://github.com/getyoti/yoti-php-sdk>**

# Yoti WordPress Plugin

[![Build Status](https://travis-ci.com/getyoti/yoti-wordpress.svg?branch=master)](https://travis-ci.com/getyoti/yoti-wordpress)

This repository contains the tools you need to quickly integrate your WordPress backend with Yoti so that your users can share their identity details with your application in a secure and trusted way. The plugin uses the Yoti PHP SDK. If you're interested in finding out more about the SDK, click [here](https://github.com/getyoti/yoti-php-sdk).

## Installing the plugin

You can install the Yoti WordPress plugin in two ways:

### By importing the plugin into your project

1. Download the Yoti plugin from [wordpress.org](https://wordpress.org/plugins/yoti/)
2. Log on to your Wordpress Admin Dashboard e.g. `https://www.wordpressurl.org.uk/wp-admin`
3. Navigate to `Plugins > Add New`
4. Search for Yoti, install and activate the plugin

### By using this repository (For MacOS and Linux users)

1. Clone this repository
2. Run `./bin/pack-plugin.sh`. This will download the Yoti PHP SDK and place it in the plugin directory
3. On completion of step 2, you will have a file called `yoti-wordpress-edge.zip`.
4. Upload this file to your Wordpress Admin Dashboard at `Plugins > Add New`, then click `Upload Plugin`.
5. Once installed, click on `Activate Plugin`.

## Setting up your Yoti Application

Visit the Yoti Hub [here](https://hub.yoti.com) to create a new application for your organisation/business.

Specify the basic details of your application such as the name, description and optional logo. These details can be whatever you desire and will not affect the plugin’s functionality.

The main page - Edit the application and set your website URL in the 'Application domain' section, e.g https:yourwebsite.com

The Scenarios tab -  Scenarios are different instances where you request users for information using Yoti. e.g verify your users' age online or in person or quickly sign in users to your website without passwords. This plugin only support single scenario.

* Specify a name for your scenario.
* Specify what information you want to request with this scenario.
* And finally, provide a callback URL so we know where to send your users after they have used Yoti. This URL must be a subdomain of your applications' domain.

The Keys tab – Here is where your keys are generated which will be inputted into the plugin settings. You will need to download your pem file and store it somewhere safe as it will be used as part of the plugin set up.


## Plugin Setup

To set things up, navigate on WordPress to `Settings > Yoti`.
You will be asked to add the following information:

* `Yoti App ID` is the unique identifier of your specific application.
* `Yoti Scenario ID` identifies the attributes associated with your Yoti application. This value can be found on your application page in Yoti Hub.
* `Yoti Client SDK ID` identifies your Yoti Hub application. This value can be found in the Hub, within your application section, in the keys tab.
* `Company Name` will replace WordPress wording in the warning message displayed on the custom login form.
* `Yoti PEM File` is the application pem file. It can be downloaded only once from the Keys tab in your Yoti Hub.

Please do not open the .pem file as this might corrupt the key and you will need to create a new application.

## Settings for new registrations

`Only allow existing WordPress users to link their Yoti account` - This setting allows a new user to Register and Log in by using their Yoti. A new user who registeres this way will be set to the `Subscriber` role in WordPress. If enabled, when a new user tries to scan the Yoti QR code, they will be redirected back to the login page with an error message displayed.

`Attempt to link Yoti email address with WordPress account for first time users` - This setting enables linking a Yoti account to a WordPress user if the email from both platforms is identical.

## How to retrieve user data provided by Yoti
Upon registration using Yoti, user data will be stored as serialized data into `wp_usermeta` table: the `meta_value` field corresponding to the `meta_key` value `yoti_user.profile` and WordPress `user_id`.

You can write a query to retrieve all data stored in `wp_usermeta.meta_value` where `wp_usermeta.meta_key` value is `yoti_user.profile`, which will return a list of serialized data.

## Docker

We provide a WordPress [Docker](https://docs.docker.com/) container that includes the Yoti plugin.

### Setup

Clone this repository and go into the folder `yoti-wordpress`:

```shell
$ git clone https://github.com/getyoti/yoti-wordpress.git
$ cd yoti-wordpress/docker
```

Rebuild the images if you have modified the `docker-compose.yml` file:

```shell
$ docker-compose build --no-cache
```

Build the containers:

```shell
$ docker-compose up -d
```

After the command has finished running, go to [https://localhost:7001](https://localhost:7001) and follow the instructions.

The Yoti plugin will be installed alongside WordPress. Activate it and follow our [plugin setup process](#plugin-setup).

### Local Development

#### Fetching the SDK

To fetch the latest SDK and place in `./yoti/sdk` directory:

```shell
$ ./bin/checkout-sdk.sh
```

#### Running the local working plugin

To run the local working copy of the plugin:

```shell
$ cd ./docker
$ docker-compose up wordpress-dev
```

After the command has finished running, go to <https://localhost:7002>

To use Xdebug in an IDE, map the `/var/www/html/wp-content/plugins/yoti` volume to the module directory on the host machine.

#### Running Tests

To run the tests and check coding standards:

```shell
$ cd ./docker
$ ./run-tests.sh
```

### Removing the Docker containers

Run the following commands to remove docker containers:

```shell
$ docker-compose stop
$ docker-compose rm
```

## Support

For any questions or support please email [sdksupport@yoti.com](mailto:sdksupport@yoti.com).
Please provide the following to get you up and working as quickly as possible:

* Computer type
* OS version
* Version of WordPress being used
* Screenshot

Once we have answered your question we may contact you again to discuss Yoti products and services. If you’d prefer us not to do this, please let us know when you e-mail.
