<?php
namespace WordUp;

use function \Deployer\{
  currentHost
};

class Helper {
  static function getLocalhost() {
    return \Deployer\Deployer::get()->hosts->get('localhost');
  }

  static function isLocalhost() {
    return currentHost()->getAlias() === 'localhost';
  }
}
?>