<?php
namespace Deployer;
require 'recipe/npm.php';

require 'recipe/laravel.php';

// Project name
set('application', 'templatron');

set('keep_releases', 3);

// Project repository
set('repository', 'git@github.com:UMN-LATIS/templatron.git');
// set('writable_use_sudo', true);
// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Hosts
host('dev')
    ->hostname("cla-templatron-dev.oit.umn.edu")
    ->user('swadm')
    ->stage('development')
    // ->identityFile()
    ->set('bin/php', '/opt/rh/rh-php73/root/usr/bin/php')
	->set('deploy_path', '/swadm/var/www/html/');

host('stage')
    ->hostname("cla-templatron-tst.oit.umn.edu")
    ->user('swadm')
    ->stage('stage')
    // ->identityFile()
    ->set('bin/php', '/opt/rh/rh-php73/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('prod')
    ->hostname("cla-templatron-prd.oit.umn.edu")
    ->user('swadm')
    ->stage('production')
    // ->identityFile()
    ->set('bin/php', '/opt/rh/rh-php73/root/usr/bin/php')
	->set('deploy_path', '/swadm/var/www/html/');

task('assets:generate', function() {
  cd('{{release_path}}');
  run('npm run production');
})->desc('Assets generation');
// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
after('deploy:update_code', 'npm:install');
after('npm:install', 'assets:generate');
before('deploy:symlink', 'artisan:migrate');

