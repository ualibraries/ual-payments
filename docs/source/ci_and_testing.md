---
layout: default
title: CI & Testing 
---
University of Arizona Libraries - Payments - CI &amp; Testing
========================

This project uses [Behat](https://github.com/Behat/Behat) for Behavior Driven Development (BDD) and [CircleCI](https://circleci.com/) for Continuous Integration (CI).

## Testing Locally

* Copy `behat.local.yml.dist` to `behat.local.yml`
* Change the `base_url` parameter to the webroot of your local build
* To use BrowserStack for your local tests, log in to your BrowserStack owner account and retrieve your access key under Account > Settings.
* Update behat.local.yml to include your username and access_key:
```
default:
  extensions:
    Behat\MinkExtension:
      base_url: 'http://localhost/public/'
      sessions:
        javascript:
          browser_stack:
            username: <Your Username>
            access_key: <Your Access Key> 
```
* Download appropriate [BrowserStack Local Binary](https://www.browserstack.com/local-testing) and execute it using the provided documentation on the download page.
* Run `vendor/bin/behat`
* Your tests should now execute using BrowserStack.  A video of your test run should be available in the "Automate" section of your BrowserStack dashboard.

**IMPORTANT**

Using BrowserStack in this manner will use your plan's "Automate" minutes, so be careful.


## Using CircleCI 

The configuration settings for CircleCI are stored in the `.circleci` directory.  Right now, there are two files:

* config.yml - The main CircleCI configuration file that specifies how the build and test the project
* circleci.conf- The Apache configuration file for the main CircleCI container of the build.

Our CircleCI environment is testing against PHP 7.2, Apache 2, and MySQL 5.7.  A build will be triggered each time we push to `master` or `develop` to ensure that
we don't use up too many BrowserStack minutes.  Additionally, the following environment variables have been configured in the admin Web interface for our project for CircleCI:

* BROWSERSTACK_ACCESS_KEY - The access key for our BrowserStack account
* BROWSERSTACK_USERNAME  - The username for our BrowserStack account
* SHIB_TEST_UAID - A UAID to be used in environments where Shibboleth is not available.  This value is set to `TEST_UAID`.
* TEST_DATABASE_URL - A formatted MySQL URL that uses the default username and password for the [CircleCI MySQL Docker Container](https://hub.docker.com/r/circleci/mysql/).