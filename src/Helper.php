<?php
namespace WordUp\Helper;

function getLocalhost() {
  return \Deployer\Deployer::get()->hosts->get('localhost');
}
?>