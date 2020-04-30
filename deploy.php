<?php
namespace Deployer;

require 'recipe/symfony4.php';
require 'vendor/deployer/recipes/recipe/slack.php';

require_once __DIR__.'/vendor/autoload.php';
(new \Symfony\Component\Dotenv\Dotenv())->load('.env');

// Project name
set('application', 'ual-payments');

// Project repository
set('repository', 'ssh://git@github.com/ualibraries/ual-payments.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Keep last 30 releases
set('keep_releases', 30);

// Default branch to deploy from
set('branch', 'master');

// Shared files/dirs between deploys
set('shared_files', ['.env']);
set('shared_dirs', ['var/log', 'var/sessions', 'backups']);
// Writable dirs by web server
set('writable_dirs', ['var']);

// We're not allowing anonymous stats
set('allow_anonymous_stats', false);

set('slack_webhook', getenv('SLACK_WEBHOOK'));

// Hosts
host('production')
    ->user('deploy')
    ->hostname('pay-prd.library.arizona.edu')
    ->set('deploy_path', '/var/www')
    ->stage('prd');

host('stage')
    ->user('deploy')
    ->hostname('pay-stg.library.arizona.edu')
    ->set('deploy_path', '/var/www')
    ->stage('stg');

// Tasks
task('assets-build', function () {
    run('cd {{release_path}} && composer assets:build');
});

// Backup remote database
task('backup-remote-db', function () {
    cd('{{release_path}}');
    run('source .env && mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > ./backups/$DB_NAME-`date +%s`.sql.gz');
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
    'assets-build',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Slack notifications
before('deploy', 'slack:notify');
after('deploy', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');
