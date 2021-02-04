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
* Install Zombie.js globally with NPM: `$ sudo npm install -g zombie`.  Unfortunately due to a quirk with the ZombieDriver, Zombie.js must be installed
globally, it won't work if you install it locally for the project.
* Update behat.local.yml to include a path to your global `node_modules` directory:

```
default:
  extensions:
    Behat\MinkExtension:
      base_url: 'http://localhost/public/'
      sessions:
        javascript:
          zombie:
            node_modules_path: /usr/lib/node_modules/
```

* Run `composer test`.  This will execute both the Behat and PHPUnit tests for the project.

**Important**

The Behat tests will fail if Symfony's debug toolbar is enabled due to a conflict between the toolbar's JavaScript and Zombie.js.  To disable it, edit
`config/packages/<environment>/web_profiler.yaml` and set the `toolbar` key to `false`:

```
web_profiler:
    toolbar: false
```

## Using CircleCI

The configuration settings for CircleCI are stored in the `.circleci` directory.  Right now, there are two files:

* config.yml - The main CircleCI configuration file that specifies how the build and test the project
* circleci.conf- The Apache configuration file for the main CircleCI container of the build.

Our CircleCI environment is testing against PHP 7.4, Apache 2, and MySQL 5.7.  A build will be triggered each time we push to `master` or `develop` to ensure that
we don't use up too many BrowserStack minutes.  Additionally, the following environment variables have been configured in the admin Web interface for our project for CircleCI:

* SHIB_TEST_UAID - A test alma user id to be used in environments where Shibboleth is not available.  This value is set to `TEST_ID`.
* ALMA_CIRCLECI_TEST_USER_PASSWORD - The password for the test alma user
* ALMA_API_URL - The url for the Alma Api. This value is set to `API_URL`.
* ALMA_API_KEY - The Api key for the Alma Api. This value is set to `API_KEY`.

You can run a build manually (for any branch) by going to the settings for the project and going to `TestCommands` linked on the left. At the bottom of the page there is a option that says `Test settings on...` and from there you can select any branch that has been pushed to github. Once the branch is selected press `Save & Go!`.
