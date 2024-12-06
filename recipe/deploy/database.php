<?php
use function \Deployer\{
  download,
  get,
  run,
  runLocally,
  set,
  task,
  upload
};

use function \WordUp\Helper\getLocalhost;

task('db:local:export', function () {
  if (!get('db/export_name')) {
    $now = date('ymdHis');
    set('db/export_name', "{$now}.sql");
  }

  $local_file = "{{db/local_exports_path}}/{{db/export_name}}";

  runLocally('mkdir -p {{db/local_exports_path}}');
  runLocally("./vendor/bin/wp db export {$local_file} --add-drop-table");
})->once()->desc('Exports the local database');


task('db:local:import', function () {
  if (!get('db/export_name')) {
    throw new \Error('db/export_name variable required');
  }

  $local_url = getLocalhost()->get('url');
  $local_file = "{{db/local_exports_path}}/{{db/export_name}}";

  runLocally("./vendor/bin/wp db import {$local_file}");
  runLocally("./vendor/bin/wp search-replace {{wp/home}} --all-tables {$local_url}");

  if (!get('db/keep_local_exports')) {
    run("rm {$local_file}");
  }
})->once()->desc('Imports a database export into the local database');


task('db:remote:export', function () {
  if (!get('db/export_name')) {
    $now = date('ymdHis');
    set('db/export_name', "{$now}.sql");
  }

  $local_file = "{{db/local_exports_path}}/{{db/export_name}}";
  $remote_file = "{{db/exports_path}}/{{db/export_name}}";

  run('mkdir -p {{db/exports_path}}');
  runLocally('mkdir -p {{db/local_exports_path}}');

  runLocally("./vendor/bin/wp db export {$remote_file} --add-drop-table --ssh={{user}}@{{hostname}}:{{release_path}}");
  download($remote_file, $local_file);

  if (!get('db/keep_exports')) {
    run("rm {$remote_file}");
  }
})->once()->desc('Exports and downloads the remote database');


task('db:remote:import', function () {
  if (!get('db/export_name')) {
    throw new \Error('db/export_name variable required');
  }

  $local_url = getLocalhost()->get('url');
  $local_file = "{{db/local_exports_path}}/{{db/export_name}}";
  $remote_file = "{{db/exports_path}}/{{db/export_name}}";

  run('mkdir -p {{db/exports_path}}');
  upload($local_file, $remote_file);

  runLocally("./vendor/bin/wp db import {$remote_file} --ssh={{user}}@{{hostname}}:{{release_path}}");
  runLocally("./vendor/bin/wp search-replace {$local_url} {{wp/home}} --all-tables --ssh={{user}}@{{hostname}}:{{release_path}}");

  if (!get('db/keep_exports')) {
    run("rm {$remote_file}");
  }
})->once()->desc('Uploads a local database export and imports it into the remote database');


task('db:pull', array(
  'db:remote:export',
  'db:local:import'
))->once()->desc('Pulls remote database to localhost (combines `db:remote:export` and `db:local:import`)');


task('db:push', array(
  'db:local:export',
  'db:remote:import'
))->once()->desc('Pushes local database to remote host (combines `db:local:export` and `db:remote:import`)');
?>