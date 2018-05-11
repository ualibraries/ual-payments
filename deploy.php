<?php
namespace Deployer;

require 'recipe/symfony4.php';
require 'vendor/deployer/recipes/recipe/slack.php';

// Project name
set('application', 'ual-payments');

// Project repository
set('repository', 'ssh://git@github.com/ualibraries/ual-payments.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Keep all releases
set('keep_releases', -1);

// Default branch to deploy from
set('branch', 'master');

// Shared files/dirs between deploys
set('shared_files', ['.env']);
set('shared_dirs', ['var/log', 'var/sessions']);
// Writable dirs by web server
set('writable_dirs', ['var']);

// We're not allowing anonymous stats
set('allow_anonymous_stats', false);

set('slack_webhook', 'https://hooks.slack.com/services/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');

// Hosts
host('production')
    ->user('deploy')
    ->hostname('pay-prd.library.arizona.edu')
    ->set('deploy_path', '/var/www')
    ->stage('prd'); 
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// Backup remote database
task('backup-remote-db', function () {
    cd('{{release_path}}');
    run('source .env && mysqldump -u $DB_USER -p$DB_PASS $DB_DATABASE | gzip > ./backups/$DB_DATABASE-`date +%s`.sql.gz');
    // Remove database backup files older than 30 days
    run('find ./backups -name *sql.gz -mtime 30 -type f -delete');
});

desc('Deploy project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'backup-remote-db',
    'database:migrate',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->onStage(['prd']);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Slack notifications
before('deploy', 'slack:notify');
after('deploy', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');
