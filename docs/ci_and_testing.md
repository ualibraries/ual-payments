---
layout: default
title: CI & Testing
---
University of Arizona Libraries - Payments - CI &amp; Testing
========================

This project uses [Behat](https://github.com/Behat/Behat) for Behavior Driven Development (BDD) and [CircleCI](https://circleci.com/) for Continuous Integration (CI).

## Testing Locally

* Copy `.env` to `.env.test.local`
* Copy `behat.local.yml.dist` to `behat.local.yml`
* Change the `base_url` parameter to the webroot of your local build
* **JavaScript scenarios** use [mink/webdriver-classic-driver](https://github.com/minkphp/webdriver-classic-driver) (W3C WebDriver) with Chrome against **Selenium 4**. Run [Selenium Standalone Chrome](https://github.com/SeleniumHQ/docker-selenium), or use [Lando](https://lando.dev/) (see `.lando.yml`). The WebDriver URL is supplied with **`BEHAT_PARAMS`** (Lando: `http://selenium:4444`; CircleCI: `http://127.0.0.1:4444`). **Do not set `webdriver_classic.wd_host` in `behat.yml`**, or it overrides `BEHAT_PARAMS`. For a one-off local setup, you can set `wd_host` only in `behat.local.yml`.
* Run `composer test`.  This will execute both the Behat and PHPUnit tests for the project.

**Important**

The Behat tests will fail if Symfony's debug toolbar is enabled due to a conflict between the toolbar's JavaScript and the browser driver.  To disable it, edit
`config/packages/<environment>/web_profiler.yaml` and set the `toolbar` key to `false`:

```
web_profiler:
    toolbar: false
```

## Using CircleCI

The configuration settings for CircleCI are stored in the `.circleci` directory.  Right now, there are two files:

* config.yml - The main CircleCI configuration file that specifies how to build and test the project
* circleci.conf- The Apache configuration file for the main CircleCI container of the build.

Our CircleCI environment is testing against PHP 7.4, Apache 2, and MySQL 5.7.  A build will be triggered each time we push to `master` or `develop` to ensure that
we don't use up too many BrowserStack minutes.  Additionally, the following environment variables have been configured in the admin Web interface for our project for CircleCI:

* SHIB_TEST_UAID - A test alma user id to be used in environments where Shibboleth is not available.  This value is set to `TEST_ID`.
* ALMA_CIRCLECI_TEST_USER_PASSWORD - The password for the test alma user
* ALMA_API_URL - The url for the Alma Api. This value is set to `API_URL`.
* ALMA_API_KEY - The Api key for the Alma Api.

You can run a build manually (for any branch) by going to the settings for the project and going to `TestCommands` linked on the left.  At the bottom of the page there is a option that says `Test settings on...` and from there you can select any branch that has been pushed to github.  Once the branch is selected press `Save & Go!`.
