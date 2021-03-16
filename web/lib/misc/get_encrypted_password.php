<?php
require_once 'password_encrypt_decrypt.php';
require_once 'cryptage.php';
/*
 *
 */
global $database_pass;
//$database_pass = $argv[1];

$licence2_path = "/usr/share/adbanking/web/licence2.txt";

// Vérification de l'existence du fichier licence2.txt
if (file_exists($licence2_path)) {

  $crypte_key = "adbankingpublic";

  $crypte_text = file_get_contents($licence2_path);
  $decrypte_arr = unserialize(Decrypte($crypte_text, $crypte_key));

  // check password
  if(null !== $decrypte_arr[9]) {
    $database_pass = $decrypte_arr[9];
  }
}

function get_encrypted_password(){
  global $database_pass;

  $password_converter = new Encryption;
  $encoded_password = $password_converter->encode($database_pass);

  //echo $encoded_password;

  return $encoded_password;
}

echo get_encrypted_password();
?>