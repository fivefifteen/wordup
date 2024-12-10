<?php
require_once('./vendor/autoload.php');
require_once('recipe/wordup.php');

// Everything below this line isn't needed and are
// just examples of some of the things you could do
use function \Deployer\{
  before,
  set,
  task,
  upload
};

// Have two different robots.txt files, one for staging
// and one for every other host
set('templates/files', array(
  'robots.txt.staging.mustache',
  'robots.txt.mustache'
));

// Upload a decrypted auth.json file so that composer
// can access private packages
task('auth:push', function () {
  upload('auth.json', '{{release_path}}/auth.json');
});
before('deploy:vendors', 'auth:push');
?>