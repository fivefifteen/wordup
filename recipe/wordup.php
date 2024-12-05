<?php
use function \Deployer\{
  add,
  import,
  set
};

require_once('recipe/common.php');

add('recipes', array('wordup'));

set('deploy_path', '/var/www/public_html');
set('public_path', '/var/www/public');
set('db_backups_path', 'db_backups');
set('wp_content_dir', 'wp-content');
set('wp_uploads_dir', '{{wp_content_dir}}/uploads');
set('shared_files', array('wp-config.php'));
set('shared_dirs', array('{{wp_uploads_dir}}'));
set('writable_dirs', array('{{wp_uploads_dir}}'));

if (file_exists('deploy.yml')) {
  import('deploy.yml');
}
?>