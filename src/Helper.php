<?php
namespace WordUp;

use function \Deployer\{
  currentHost
};

class Helper {
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
    preg_match(self::getTemplateExtExp(), $file_name, $matches, PREG_SET_ORDER, 0);

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