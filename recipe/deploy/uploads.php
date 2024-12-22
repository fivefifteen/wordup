<?php
use function \Deployer\{
  download,
  task,
  upload
};

task('uploads:push', function () {
  upload('{{wp/uploads_dir}}/', '{{wp/uploads_path}}');
})->once()->desc('Pushes uploads from local to remote');


task('uploads:pull', function () {
  download('{{wp/uploads_path}}/', '{{wp/uploads_dir}}');
})->once()->desc('Pulls uploads from remote to local');


task('uploads:sync', array(
  'uploads:push',
  'uploads:pull'
))->once()->desc('Syncs uploads between local and remote');
?>