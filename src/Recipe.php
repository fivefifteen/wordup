<?php
namespace WordUp;

class Recipe {
  function __construct($config_file = null) {
    global $config_file;
    require_once(__DIR__ . '/../recipe/wordup.php');
    return $this;
  }
}
?>