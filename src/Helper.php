<?php
namespace WordUp;

use function \Deployer\{
  currentHost,
  fetch,
  get,
  set
};

class Helper {
  static $salt_keys = array(
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

  static function generatePassword($length = 12, $special_chars = true, $extra_special_chars = false) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    if ($special_chars) {
      $chars .= is_string($special_chars) ? $special_chars : '!@#$%^&*()';
    }

    if ($extra_special_chars) {
      $chars .= is_string($extra_special_chars) ? $extra_special_chars : '-_ []{}<>~`+=,.;:/?|';
    }

    $factory = new \RandomLib\Factory;
    $generator = $factory->getMediumStrengthGenerator();

    return $generator->generateString($length, $chars);
  }

  static function generateSalts(array $keys = array(), string|bool $extra_special_chars = false) {
    $salt_keys = $keys ? $keys : self::$salt_keys;
    $salts = array();

    foreach($salt_keys as $salt_key) {
      $salts[$salt_key] = self::generatePassword(64, true, $extra_special_chars);
    }

    return $salts;
  }

  static function getHostsList() {
    return array_keys(\Deployer\Deployer::get()->hosts->all());
  }

  static function getLocalhost() {
    return \Deployer\Deployer::get()->hosts->get('localhost');
  }

  static function getTemplateExtExp() {
    $hosts = self::getHostsList();
    return '/(?:\.(' . implode('|', $hosts) . '))?\.mustache$/';
  }

  static function getTemplateRenderedName($file_name) {
    return preg_replace(self::getTemplateExtExp(), '', $file_name);
  }

  static function getTemplateStage($file_name) {
    preg_match(self::getTemplateExtExp(), $file_name, $matches);

    if ($matches) {
      if (count($matches) > 1) {
        return $matches[1];
      }

      return true;
    }

    return false;
  }

  static function isLocalhost() {
    return currentHost()->getAlias() === 'localhost';
  }
}
?>