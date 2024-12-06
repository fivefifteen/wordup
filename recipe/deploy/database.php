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

use function \WordUp\Helper\localhost;

task('db:local:export', function () {
  if (!($export_name = get('db_export_name'))) {
    $now = date('ymdHis');
    $db_export_name = "{$now}.sql";
    set('db_export_name', $db_export_name);
  }

  $local_file = "{{db_local_exports_path}}/{$db_export_name}";

  runLocally('mkdir -p {{db_local_exports_path}}');
  runLocally("./vendor/bin/wp db export {$local_file} --add-drop-table");
})->once()->desc('Exports the local database');


task('db:local:import', function () {
  $db_export_name = get('db_export_name');

  if (!$db_export_name) {
    throw new \Error('db_export_name variable required');
  }

  $localhost = localhost();
  $local_url = $localhost->get('url');
  $local_file = "{{db_local_exports_path}}/{$db_export_name}";

  runLocally("./vendor/bin/wp db import {$local_file}");
  runLocally("./vendor/bin/wp search-replace {{url}} --all-tables {$local_url}");

  if (!get('keep_local_db_exports')) {
    run("rm {$local_file}");
  }
})->once()->desc('Imports a database export into the local database');


task('db:remote:export', function () {
  if (!($export_name = get('db_export_name'))) {
    $now = date('ymdHis');
    $db_export_name = "{$now}.sql";
    set('db_export_name', $db_export_name);
  }

  $local_file = "{{db_local_exports_path}}/{$db_export_name}";
  $remote_file = "{{db_exports_path}}/{$db_export_name}";

  run('mkdir -p {{db_exports_path}}');
  runLocally('mkdir -p {{db_local_exports_path}}');

  runLocally("./vendor/bin/wp db export {$remote_file} --add-drop-table --ssh={{user}}@{{hostname}}:{{release_path}}");
  download($remote_file, $local_file);

  if (!get('keep_db_exports')) {
    run("rm {$remote_file}");
  }
})->once()->desc('Exports and downloads the remote database');


task('db:remote:import', function () {
  $db_export_name = get('db_export_name');

  if (!$db_export_name) {
    throw new \Error('db_export_name variable required');
  }

  $localhost = localhost();
  $local_url = $localhost->get('url');
  $local_file = "{{db_local_exports_path}}/{$db_export_name}";
  $remote_file = "{{db_exports_path}}/{$db_export_name}";

  run('mkdir -p {{db_exports_path}}');
  upload($local_file, $remote_file);

  runLocally("./vendor/bin/wp db import {$remote_file} --ssh={{user}}@{{hostname}}:{{release_path}}");
  runLocally("./vendor/bin/wp search-replace {$local_url} {{url}} --all-tables --ssh={{user}}@{{hostname}}:{{release_path}}");

  if (!get('keep_db_exports')) {
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