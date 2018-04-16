University of Arizona Libraries - Payments
========================

## Requirements

* 💻
* PHP >= 7.2
* Composer

## Developing locally

Clone the repository using `git clone ssh://git@github.com/ualibraries/ual-payments.git`.

### Environments

### Authentication

* You can add  your public ssh key (typically located at `~/.ssh/id_rsa.pub`) to the Github Repositories Deploy Keys. This will allow you to push, pull, and deploy without needing to provide your password each time. See [Github Documentation](https://developer.github.com/v3/guides/managing-deploy-keys/#deploy-keys) for more information.  

### Rollback

To rollback to the previous release, run `composer deploy:rollback`. See [Deployer documentation](https://deployer.org/docs) for more.

## Issues

We are tracking issues at https://redmine.library.arizona.edu/projects/payments.
