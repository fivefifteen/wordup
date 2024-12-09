<?php
use function \Deployer\{
  add,
  after,
  get,
  import,
  localhost,
  set,
  task
};

require_once('recipe/common.php');

require_once(__DIR__ . '/deploy/database.php');
require_once(__DIR__ . '/deploy/templates.php');
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
set('db/exports_path', '{{deploy_path}}/{{db/exports_dir}}');
set('db/keep_exports', false);
set('db/keep_local_exports', true);
set('templates/files', array());
set('templates/temp_dir', '.tmp');
set('wp/content_dir', 'wp-content');
set('wp/content_path', '{{release_or_current_path}}/{{wp/content_dir}}');
set('wp/siteurl', '{{wp/home}}');
set('wp/uploads_dir', '{{wp/content_dir}}/uploads');
set('wp/uploads_path', '{{release_or_current_path}}/{{wp/uploads_dir}}');

localhost();

if (!isset($config_file)) $config_file = 'deploy.yml';

if (file_exists($config_file)) {
  import($config_file);
}

$templates_files = get('templates/files');

add('clear_paths', $templates_files);

add('shared_files', array_unique(array_map(function ($template_file, $rendered_file) {
  if (!is_string($rendered_file)) {
    $rendered_file = \WordUp\Helper::getTemplateRenderedName($template_file);
  }

  return $rendered_file;
}, $templates_files, array_keys($templates_files))));
?>