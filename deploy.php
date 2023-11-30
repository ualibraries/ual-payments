<?php
namespace Deployer;

require 'recipe/symfony.php';
require 'contrib/slack.php';
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
(new Dotenv())->loadEnv(__DIR__.'/.env');

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
set('shared_files', ['.env.local']);
set('shared_dirs', ['var/log', 'var/sessions', 'backups']);
// Writable dirs by web server
set('writable_dirs', ['var']);

// We're not allowing anonymous stats
set('allow_anonymous_stats', false);

set('slack_webhook', $_ENV['SLACK_WEBHOOK']);

// Hosts
host('production')
    ->set('remote_user', 'deploy')
    ->set('hostname', 'payments.library.arizona.edu')
    ->set('deploy_path', '/var/www')
    ->set('labels', ['stage' => 'prd']);

host('stage')
    ->set('remote_user', 'deploy')
    ->set('hostname', 'pay-stg.library.arizona.edu')
    ->set('deploy_path', '/var/www')
    ->set('labels', ['stage' => 'stg']);

// Tasks
task('assets-build', function () {
    run('cd {{release_path}} && composer assets:build');
});

// Backup remote database
task('backup-remote-db', function () {
    cd('{{current_path}}');
    run('source .env.local && mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > ./backups/$DB_NAME-`date +%s`.sql.gz');
    // Remove database backup files older than 30 days
    run('find ./backups -name *sql.gz -mtime 30 -type f -delete');
});

desc('Deploy project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:cache:clear',
    'backup-remote-db',
    'database:migrate',
    'assets-build',
    'deploy:publish'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Slack notifications
before('deploy', 'slack:notify');
after('deploy', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');
