<?php
require_once('./vendor/autoload.php');
require_once('recipe/wordup.php');

use function \Deployer\{
  before,
  set,
  task,
  upload
};

set('templates/files', array(
  'robots.txt.staging.mustache',
  'robots.txt.mustache'
));

task('fivefifteen:auth:push', function () {
  upload('auth.json', '{{release_path}}/auth.json');
});

before('deploy:vendors', 'fivefifteen:auth:push');
?>