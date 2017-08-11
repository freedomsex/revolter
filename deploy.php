<?php

namespace Deployer;

require 'recipe/common.php';
require 'recipe/symfony3.php';

set('git_cache', false);

// Configuration

set('repository', 'https://github.com/freedomsex/revolter.git');
// -- Общие папки
//set('shared_dirs', array_merge([''], get('shared_dirs')));
//set('shared_files', []);
//set('writable_dirs', []);

////
// Servers 
//   Внимание, не настроен stage по умолчанию (default_stage)
////
server('prod', 'domain.com')
    ->user('username')
    ->identityFile()
    ->set('deploy_path', '/var/www/revolter')
    ->stage('prod');

// Отдельный файл натроек для dev сервера
if(file_exists('dep_conf.php')) {
	set('default_stage', 'dev');
	set('keep_releases', 5);
	include('dep_conf.php');
}

// Ссылка на шаблон base.html.twig 
// Закомментируйте, если изменяли его
task('base-html-link', function() { 
    cd("{{release_path}}/app/Resources/views");
    run("ln -sf {{release_path}}/vendor/revolter/idealist-bundle/Resources/views/base.html.twig");
});
after('deploy:vendors', 'base-html-link');


set('composer_command', '/bin/composer.phar');
set('copy_dirs', ['vendor']);
set('writable_use_sudo', true);
set('writable_mode', 'chown');

// Tasks

// Перезапустить PHP после успешного деплоя
task('reload:php7', function() {
    run('service php7.0-fpm restart');
});
// После деплоя перезапустим php
after('deploy:symlink', 'reload:php7');
after('rollback', 'reload:php7');
// После отката на прошлый релиз - тоже перезапустим его


after('deploy', 'success');
