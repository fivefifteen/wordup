<?php
use function \Deployer\{
  download,
  task,
  upload
};

task('uploads:push', function () {
  upload('{{wp_uploads_dir}}', '{{wp_uploads_path}}');
})->once()->desc('Pushes uploads from local to remote');


task('uploads:pull', function () {
  download('{{wp_uploads_path}}', '{{wp_uploads_dir}}');
})->once()->desc('Pulls uploads from remote to local');


task('uploads:sync', array(
  'uploads:push',
  'uploads:pull'
))->once()->desc('Syncs uploads between local and remote');
?>