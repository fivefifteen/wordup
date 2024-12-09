<?php
namespace WordUp;

class Helper {
  static function getLocalhost() {
    return \Deployer\Deployer::get()->hosts->get('localhost');
  }

  static function isLocalhost() {
    return \Deployer\Deployer::currentHost()->getAlias() === 'localhost';
  }
}
?>