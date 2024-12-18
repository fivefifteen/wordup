<?php
use function \Deployer\{
  fetch,
  get,
  info,
  parse,
  runLocally,
  set,
  task
};

task('wp:config:create', function () {
  $dumper = new \Nette\PhpGenerator\Dumper;
  $salt_keys = \WordUp\Helper::$salt_keys;
  $db = get('db/credentials');
  $user_defined_constants = get('wp/config/constants', array());

  $options = '';
  $extra_php = '';

  $default_options = array(
    'config-file' => "{{release_or_current_path}}/wp-config.php",
    'force'       => true,
    'skip-check'  => true,
    'skip-salts'  => true
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

  $constants = array_merge(
    \WordUp\Helper::generateSalts(
      array_diff(\WordUp\Helper::$salt_keys, array_keys($user_defined_constants)),
      '-_ []{}~+=,.;:/?|'
    ),
    array(
      'WP_HOME'         => '{{wp/home}}',
      'WP_SITEURL'      => '{{wp/siteurl}}',
      'WP_CONTENT_URL'  => '{{wp/home}}/{{wp/content_dir}}',
      'WP_CONTENT_DIR'  => '{{wp/content_path}}'
    ),
    $user_defined_constants
  );

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
})->desc('Generates a wp-config.php file');


task('wp:salts:php', function () {
  $salts = \WordUp\Helper::generateSalts();
  $dumper = new \Nette\PhpGenerator\Dumper;
  $results = array();

  foreach($salts as $key => $value) {
    $value = $dumper->dump($value);
    $results[] = "define('{$key}', {$value});";
  }

  $contents = "<?php\n" . implode("\n", $results) . "\n?>";

  runLocally('mkdir -p {{wp/salts/temp_dir}}');

  if (!get('wp/salts/php/file_name')) {
    $now = date('ymdHis');
    set('wp/salts/php/file_name', "salts-{$now}.php");
  }

  $file_path = parse('{{wp/salts/temp_dir}}/{{wp/salts/php/file_name}}');

  file_put_contents($file_path, $contents);

  info("Salts saved to {$file_path}");
})->once()->desc('Generates salts in PHP format and saves them to a file');


task('wp:salts:json', function () {
  $salts = \WordUp\Helper::generateSalts(array(), '-_ []{}<>~`+=,.;:?|');
  $contents = json_encode($salts, JSON_PRETTY_PRINT);

  runLocally('mkdir -p {{wp/salts/temp_dir}}');

  if (!get('wp/salts/json/file_name')) {
    $now = date('ymdHis');
    set('wp/salts/json/file_name', "salts-{$now}.json");
  }

  $file_path = parse('{{wp/salts/temp_dir}}/{{wp/salts/json/file_name}}');

  file_put_contents($file_path, $contents);

  info("Salts saved to {$file_path}");
})->once()->desc('Generates salts in JSON format and saves them to a file');


task('wp:salts:yml', function () {
  $salts = \WordUp\Helper::generateSalts(array(), '-_ []{}<>~+=,.;:/?|');
  $contents = Symfony\Component\Yaml\Yaml::dump($salts);

  runLocally('mkdir -p {{wp/salts/temp_dir}}');

  if (!get('wp/salts/yml/file_name')) {
    $now = date('ymdHis');
    set('wp/salts/yml/file_name', "salts-{$now}.yml");
  }

  $file_path = parse('{{wp/salts/temp_dir}}/{{wp/salts/yml/file_name}}');

  file_put_contents($file_path, $contents);

  info("Salts saved to {$file_path}");
})->once()->desc('Generates salts in YML format and saves them to a file');
?>