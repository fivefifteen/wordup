<?php
use function \Deployer\{
  add,
  after,
  import,
  set,
  task
};

require_once('recipe/common.php');

require_once(__DIR__ . '/deploy/database.php');
require_once(__DIR__ . '/deploy/uploads.php');

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

add('recipes', array('wordup'));

// Built-in Deployer options
set('clear_paths', array(
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
set('current_path', '/var/www/public');
set('deploy_path', '/var/www/public_html');
set('shared_dirs', array('{{wp_uploads_dir}}'));
set('shared_files', array('wp-config.php'));
set('use_atomic_symlink', false);
set('writable_dirs', array('{{wp_uploads_dir}}'));

// Custom WordUp Options
set('db_exports_path', '{{deploy_path}}/db_exports');
set('db_local_exports_path', 'db_exports');
set('keep_db_exports', false);
set('keep_local_db_exports', true);
set('wp_content_dir', 'wp-content');
set('wp_uploads_dir', '{{wp_content_dir}}/uploads');
set('wp_uploads_path', '{{release_path}}/{{wp_uploads_dir}}');

if (!isset($config_file)) $config_file = 'deploy.yml';

if (file_exists($config_file)) {
  import($config_file);
}
?>