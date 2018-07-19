---
layout: default
title: README
---
University of Arizona Libraries - Payments
========================

[![CircleCI](https://circleci.com/gh/ualibraries/ual-payments.svg?style=svg)](https://circleci.com/gh/ualibraries/ual-payments)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/ualibraries/ual-payments.svg?style=flat)](https://scrutinizer-ci.com/g/ualibraries/ual-payments/)
![license](https://img.shields.io/github/license/mashape/apistatus.svg)

## Requirements

* 💻
* PHP >= 7.2
* Composer
* NodeJs

## Getting started

* Clone the repository using `git clone ssh://git@github.com/ualibraries/ual-payments.git`.
* Run `composer install`
* Create a database and enter the connection string in `.env`.
* Run `bin/console doctrine:migrations:migrate` to get the database structure in place.
* Run `npm install`
* Run `npm run build`

## Deploying

This project uses [Deployer](https://deployer.org/) for its deployments. Deployment commands are scripted in `composer.json`. To deploy, use the following commands:

* **Production:**  
`composer deploy:prd`
* **Staging:**  
`composer deploy:stg`

## Environments

* **Production** - pay-prd
* **Staging** - pay-stg

## Authentication
* If you don't already have an SSH key on your development machine, [generate one and add it to the ssh-agent](https://help.github.com/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent/#generating-a-new-ssh-key).
* Add your SSH key to the server environment for the `deploy` user.  You can do this using `ssh-copy-id`:
```
$ ssh-copy-id deploy@pay-prd
```
If you don't have the password for the deploy account, you can ask someone who already has server access to add your key.  Send them your public key (e.g. `~/.ssh/id_rsa.pub`) and have them append it to the `authorized_keys` file for the `deploy` user:

```
deploy@pay-prd:~$ cat your_id_rsa.pub >> /home/deploy/.ssh/authorized_keys
```

## Rollback

To rollback to the previous release, run `composer deploy:rollback`. See [Deployer documentation](https://deployer.org/docs) for more.

## Documentation

Additional documentation can be found in the `docs/source` directory.

Preview the documentation by running `composer docs:preview`. Then go to http://localhost:8000.

Build the documentation by running `composer docs:build`.

Deploy the documentation by running `composer docs:deploy`. You may need to specify your AWS profile: `composer docs:deploy -- --profile=ual`.

View the documentation at [UAL Payments Documentation](http://ualibr-payments-documentation.s3-website-us-west-2.amazonaws.com) (`http://ualibr-payments-documentation.s3-website-us-west-2.amazonaws.com`).

## License
Licensed under the [MIT license](https://github.com/ualibraries/ual-payments/blob/master/LICENSE).
