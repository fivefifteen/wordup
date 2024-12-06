<?php
namespace WordUp;

class Helper {
  static function localhost() {
    return \Deployer\Deployer::get()->hosts->get('localhost');
  }
}
?>