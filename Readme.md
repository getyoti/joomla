**This extension is no longer maintained. The PHP SDK, which this extension makes calls through, is still available at <https://github.com/getyoti/yoti-php-sdk>**

# Yoti Joomla Extension

[![Build Status](https://travis-ci.com/getyoti/yoti-joomla.svg?branch=master)](https://travis-ci.com/getyoti/yoti-joomla)

This repository contains the tools you need to quickly integrate your Joomla back-end with Yoti so that your users can share their identity details with your application in a secure and trusted way. The extension uses the Yoti PHP SDK. If you're interested in finding out more about the SDK, click [here](https://github.com/getyoti/yoti-php-sdk).

## Installing the extension

To import the Yoti Joomla extension inside your project:

1. Log on to the admin console of your Joomla website. e.g. `https://www.joomladev.com/administrator`
2. Navigate at `Extensions-> Manage -> Install` and do one of the following:

    * click the `Install from Web` link and search for `Yoti`
    * click `Upload Package File` and upload the downloaded zip file from [our extension page](https://extensions.joomla.org/extensions/extension/access-a-security/yoti/).
    * alternatively, you can use the `Install from URL` or `Install from folder` options

3. Install and enable `Yoti login` module and `Yoti - User profile` plugin.

## Extension Setup

To set Yoti up follow the instruction below:

1. Navigate on Joomla to `Components-> Yoti` (details in the [Yoti Components settings](#yoti-components-settings) section below)
2. Navigate to `Extensions -> Modules`, search for the `Yoti Login` module and publish it.
3. Navigate to `Extensions -> Plugins`, search for the `Yoti - User Profile` plugin and enable it.

## Setting up your Yoti Application

After you have registered your [Yoti](https://www.yoti.com/), access the [Yoti Hub](https://hub.yoti.com) to create a new application.

Specify the basic details of your application such as the name, description and optional logo. These details can be whatever you like and will not affect the extensions' functionality.

The `Data` tab - Specify any attributes you'd like users to share. You must select at least one. If you plan to allow new user registrations, we recommended choosing `Given Name(s)`, `Family Name` and `Email Address` at a minimum.

The `Integration` tab - Here is where you specify the callback URL. This can be found on your Yoti settings page in your Joomla admin panel.

### Yoti Components Settings

To set things up, navigate on Joomla to the Yoti extension.
You will be asked to add the following information:

* `Yoti App ID` is the unique identifier of your specific application.
* `Yoti Scenario ID` identifies the attributes associated with your Yoti application. This value can be found on your application page in Yoti Hub.
* `Yoti Client SDK ID` identifies your Yoti Hub application. This value can be found in the Hub, within your application section, in the keys tab.
* `Company Name` will replace Joomla wording in the warning message displayed on the custom login form.
* `Yoti PEM File` is the application pem file. It can be downloaded only once from the Keys tab in your Yoti Hub.

Please do not open the .pem file as this might corrupt the key and you will need to create a new application.

## Settings for new registrations

`Only allow existing Joomla users to link their Yoti account` - This setting allows a new user to register and log in by using their Yoti. If enabled, when a new user tries to scan the Yoti QR code, they will be redirected back to the login page with an error message displayed.

`Attempt to link Yoti email address with Joomla account for first time users` - This setting enables linking a Yoti account to a Joomla user if the email from both platforms is identical.

## How to retrieve user data provided by Yoti
Upon registration using Yoti, user data will be stored as serialized data into `{DB_prefix}_yoti_users` table in the `data` field.

You can write a query to retrieve all data stored in `{DB_prefix}_yoti_users.data`, which will return a list of serialized data.

## Docker

We provide a [Docker](https://docs.docker.com/) container that includes the Yoti extension.

### Setup

Clone this repository and go into the folder:

```shell
$ cd yoti-joomla
```

Copy `.env.example` to `.env` and set environment variables.

Rebuild the images if you have modified the `docker-compose.yml` file:

```shell
$ cd docker
$ docker-compose build --no-cache
```

Installing Joomla:

```shell
$ cd docker
$ ./install-joomla.sh
```

After the command has finished running, go to [https://localhost:6001](https://localhost:6001) 
and follow our [extension setup process](#extension-setup).

### Local Development

#### Fetching the SDK

To fetch the latest SDK and place in ./yoti/site/sdk directory:

```shell
$ ./checkout-sdk.sh
```

#### Running the local working extension

To run the local working copy of the extension:

```shell
$ cd docker
$ ./install-joomla.sh joomla-dev
```

After the command has finished running, go to <https://localhost:6002>

#### Manual Installation

Run Joomla without the plugin:

```shell
$ cd docker
$ ./install-joomla.sh joomla-base
```

After the command has finished running, go to <https://localhost:6003> and install the plugin
through the administrator UI.

#### Running tests

```shell
$ cd docker
$ ./run-tests.sh
```

### Removing the Docker containers

Run the following commands to remove docker containers:

```shell
$ cd docker
$ docker-compose down
```

## Support

For any questions or support please email [sdksupport@yoti.com](mailto:sdksupport@yoti.com).

Please provide the following to get you up and working as quickly as possible:

- Computer Type
- OS Version
- Version of Joomla being used
- Screenshot

Once we have answered your question we may contact you again to discuss Yoti products and services. If you’d prefer us not to do this, please let us know when you e-mail.
