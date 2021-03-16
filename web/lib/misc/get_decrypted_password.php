<?php
require_once 'password_encrypt_decrypt.php';
/*
 *
 */
global $database_pass;
$database_pass = $argv[1];

function get_decrypted_password(){
  global $database_pass;

  $password_converter = new Encryption;
  $decoded_password = $password_converter->decode($database_pass);

  //echo $decoded_password;

  return $decoded_password;
}

echo get_decrypted_password();
?>