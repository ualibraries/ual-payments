<?php
namespace Deployer;

require 'recipe/symfony.php';
require 'vendor/deployer/recipes/recipe/slack.php';

// Project name
set('application', 'ual-payments');

// Project repository
set('repository', 'https://github.com/ualibraries/ual-payments.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Keep all releases
set('keep_releases', -1);

// Default branch to deploy from
set('branch', 'master');

set('bin_dir', 'bin');
set('var_dir', 'var');

// Shared files/dirs between deploys 
add('shared_files', ['app/config/parameters.yml']);
add('shared_dirs', ['var/logs']);

// Writable dirs by web server 
add('writable_dirs', ['var/cache', 'var/logs', 'var/sessions']);
set('allow_anonymous_stats', false);

set('slack_webhook', 'https://hooks.slack.com/services/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');

// Hosts
host('production')
    ->hostname('pay-prd.library.arizona.edu')
    ->set('deploy_path', '/var/www')
    ->stage('prd'); 
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Slack notifications
before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');

// Migrate database before symlink new release.
/**
 * @todo uncomment after database is created
 */   
//after('deploy:vendors', 'database:migrate');

