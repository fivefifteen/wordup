<?php
use function \Deployer\{
  get,
  run,
  runLocally,
  task
};

$dumper = new \Nette\PhpGenerator\Dumper;

task('wp:config:create', function () {
  $db = get('db/credentials');

  $options = '';
  $extra_php = '';
  $secret = null;

  if (isset($db['pass'])) {
    $secret = $db['pass'];
    $db['pass'] = '%secret%';
  }

  $default_options = array(
    'config-file' => "{{release_or_current_path}}/wp-config.php{$options}",
    'force'       => true
  );

  $check_options = array(
    'dbname'    => 'name',
    'dbuser'    => 'user',
    'dbpass'    => 'pass',
    'dbhost'    => 'host',
    'dbprefix'  => 'prefix',
    'dbcharset' => 'charset',
    'dbcollate' => 'collate',
    'locale'    => 'locale'
  );

  $constants = array_merge(array(
    'WP_HOME'         => '{{wp/home}}',
    'WP_SITEURL'      => '{{wp/siteurl}}',
    'WP_CONTENT_URL'  => '{{wp/home}}/{{wp/content_dir}}',
    'WP_CONTENT_DIR'  => '{{wp/uploads_path}}'
  ), get('wp/config/constants') ?: array());

  foreach($default_options as $option_flag => $option_value) {
    $options .= " --{$option_flag}";

    if ($option_value !== true) {
      $options .= "={$option_value}";
    }
  }

  foreach($check_options as $option_flag => $option_key) {
    if (isset($db[$option_key])) {
      $option_value = $db[$option_key];
      $options .= " --{$option_flag}";

      if ($option_value !== true) {
        $options .= "={$option_value}";
      }
    }
  }

  foreach($constants as $key => $value) {
    $value = $dumper->dump($value);
    $extra_php .= "\ndefine('{$key}', {$value});";
  }

  if ($require_paths = get('wp/config/require')) {
    foreach($require_paths as $require_path) {
      $extra_php .= "\n\nrequire_once('{$require_path}');";
    }
  }

  if ($user_defined_extra_php = (array) get('wp/config/extra_php')) {
    $extra_php .= "\n\n" . implode("\n", $user_defined_extra_php);
  }

  if ($extra_php) {
    $options .= " --extra-php <<PHP\n{$extra_php}\nPHP";
  }

  runLocally("./vendor/bin/wp config create --config-file={{release_or_current_path}}/wp-config.php{$options}", array(), null, null, $secret);
});
?>