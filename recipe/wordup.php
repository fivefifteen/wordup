<?php
use function \Deployer\{
  add,
  after,
  import,
  localhost,
  set,
  task
};

require_once('recipe/common.php');

require_once(__DIR__ . '/deploy/database.php');
require_once(__DIR__ . '/deploy/uploads.php');
require_once(__DIR__ . '/deploy/wordpress.php');

task('deploy', array(
  'deploy:info',
  'deploy:setup',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  'deploy:env',
  'deploy:shared',
  'deploy:writable',
  'deploy:vendors',
  'deploy:clear_paths',
  'deploy:symlink',
  'deploy:unlock',
  'deploy:cleanup',
  'deploy:success'
))->desc('Deploys your WordPress project');

// Built-in Deployer options
add('clear_paths', array(
  '.git',
  '.gitignore',
  '.lando.yml',
  'auth.json',
  'composer.json',
  'composer.lock',
  'deploy.yml',
  'deploy.php',
  'readme.md'
));
add('recipes', array('wordup'));
add('shared_dirs', array('{{wp/uploads_dir}}'));
add('shared_files', array('wp-config.php'));
add('writable_dirs', array('{{wp/uploads_dir}}'));

// Custom WordUp Options
set('db/exports_dir', 'db_exports');
set('db/exports_path', '{{release_or_current_path}}/{{db/exports_dir}}');
set('db/keep_exports', false);
set('db/keep_local_exports', true);
set('wp/content_dir', 'wp-content');
set('wp/siteurl', '{{wp/home}}');
set('wp/uploads_dir', '{{wp/content_dir}}/uploads');
set('wp/uploads_path', '{{release_or_current_path}}/{{wp/uploads_dir}}');

localhost();

if (!isset($config_file)) $config_file = 'deploy.yml';

if (file_exists($config_file)) {
  import($config_file);
}
?>