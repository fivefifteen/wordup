<?php
use function \Deployer\{
  get,
  parse,
  runLocally,
  task
};

task('wp:config:create', function () {
  $dumper = new \Nette\PhpGenerator\Dumper;
  $db = get('db/credentials');

  $options = '';
  $extra_php = '';

  $default_options = array(
    'config-file' => "{{release_or_current_path}}/wp-config.php",
    'force'       => true
  );

  if (!\WordUp\Helper::isLocalhost()) {
    $default_options['ssh'] = '{{remote_user}}@{{hostname}}:{{current_path}}';
  }

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
    'WP_CONTENT_DIR'  => '{{wp/content_path}}'
  ), get('wp/config/constants') ?: array());

  $salt_keys = array(
    'AUTH_KEY',
    'SECURE_AUTH_KEY',
    'LOGGED_IN_KEY',
    'NONCE_KEY',
    'AUTH_SALT',
    'SECURE_AUTH_SALT',
    'LOGGED_IN_SALT',
    'NONCE_SALT',
    'WP_CACHE_KEY_SALT'
  );

  if ($salt_values = array_intersect_key($constants, array_flip($salt_keys))) {
    $expected_count = count($salt_keys);
    $actual_count = count($salt_values);

    if ($actual_count !== $expected_count) {
      throw new \Error("If defining your own salts, you should have {$expected_count} total: " . implode(', ', $salt_keys));
    }

    $default_options['skip-salts'] = true;
  }

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

  runLocally("./vendor/bin/wp config create%secret%", array(), null, null, parse($options));
});
?>