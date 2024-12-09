<?php
use function \Deployer\{
  download,
  get,
  run,
  runLocally,
  select,
  set,
  task,
  upload
};

task('db:export', function () {
  if (!get('db/export_name')) {
    $now = date('ymdHis');
    set('db/export_name', "{$now}.sql");
  }

  $local_file = "{{db/exports_dir}}/{{db/export_name}}";

  runLocally('mkdir -p {{db/exports_dir}}');
  runLocally("./vendor/bin/wp db export {$local_file} --add-drop-table");
})->once()->desc('Exports the local database');


task('db:import', function () {
  if (!get('db/export_name')) {
    throw new \Error('db/export_name variable required');
  }

  $local_url = \WordUp\Helper::getLocalhost()->get('wp/home');
  $local_file = "{{db/exports_dir}}/{{db/export_name}}";

  runLocally("./vendor/bin/wp db import {$local_file}");
  runLocally("./vendor/bin/wp search-replace {{wp/home}} --all-tables {$local_url}");

  if (!get('db/keep_local_exports')) {
    runLocally("rm {$local_file}");
  }
})->once()->desc('Imports a database export into the local database');


task('db:export:remote', function () {
  if (!get('db/export_name')) {
    $now = date('ymdHis');
    set('db/export_name', "{$now}.sql");
  }

  $local_file = "{{db/exports_dir}}/{{db/export_name}}";
  $remote_file = "{{db/exports_path}}/{{db/export_name}}";

  run('mkdir -p {{db/exports_path}}');
  runLocally('mkdir -p {{db/exports_dir}}');

  runLocally("./vendor/bin/wp db export {$remote_file} --add-drop-table --ssh={{remote_user}}@{{hostname}}:{{current_path}}");
  download($remote_file, $local_file);

  if (!get('db/keep_exports')) {
    run("rm {$remote_file}");
  }
})->once()->desc('Exports and downloads the remote database');


task('db:import:remote', function () {
  if (!get('db/export_name')) {
    throw new \Error('db/export_name variable required');
  }

  $local_url = \WordUp\Helper::getLocalhost()->get('wp/home');
  $local_file = "{{db/exports_dir}}/{{db/export_name}}";
  $remote_file = "{{db/exports_path}}/{{db/export_name}}";

  run('mkdir -p {{db/exports_path}}');
  upload($local_file, $remote_file);

  runLocally("./vendor/bin/wp db import {$remote_file} --ssh={{remote_user}}@{{hostname}}:{{current_path}}");
  runLocally("./vendor/bin/wp search-replace {$local_url} {{wp/home}} --all-tables --ssh={{remote_user}}@{{hostname}}:{{current_path}}");

  if (!get('db/keep_exports')) {
    run("rm {$remote_file}");
  }
})->once()->desc('Uploads a local database export and imports it into the remote database');


task('db:pull', array(
  'db:export:remote',
  'db:import'
))->once()->desc('Pulls remote database to localhost (invokes `db:export:remote` and `db:import`)');


task('db:push', array(
  'db:export',
  'db:import:remote'
))->once()->desc('Pushes local database to remote host (invokes `db:export` and `db:import:remote`)');
?>