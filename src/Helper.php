<?php
namespace WordUp;

class Helper {
  static function getLocalhost() {
    return \Deployer\Deployer::get()->hosts->get('localhost');
  }
}
?>