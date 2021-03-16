<?php
/*
 *
 */
global $current_version,$param_version,$operator;
$current_version = $argv[1];
$param_version = $argv[2];
$operator = $argv[3];

function compare_version(){
  global $current_version,$param_version,$operator;

  if (version_compare($current_version,$param_version,$operator)){
    echo 'true';
    return true;
  }
  else{
    echo 'false';
    return false;
  }
}

compare_version();
?>