<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */


/**
 * Permet de générer une clé de cryptage
 */
function GenerationCle($Texte,$CleDEncryptage) {
  $CleDEncryptage = md5($CleDEncryptage);
  $Compteur=0;
  $VariableTemp = "";
  for ($Ctr=0;$Ctr<strlen($Texte);$Ctr++) {
    if ($Compteur==strlen($CleDEncryptage))
      $Compteur=0;
    $VariableTemp.= substr($Texte,$Ctr,1) ^ substr($CleDEncryptage,$Compteur,1);
    $Compteur++;
  }
  return $VariableTemp;
}

/**
 * Permet de crypter un texte
 */
function Crypte($Texte,$Cle) {
  srand((double)microtime()*1000000);
  $CleDEncryptage = md5(rand(0,32000) );
  $Compteur=0;
  $VariableTemp = "";
  for ($Ctr=0;$Ctr<strlen($Texte);$Ctr++) {
    if ($Compteur==strlen($CleDEncryptage))
      $Compteur=0;
    $VariableTemp.= substr($CleDEncryptage,$Compteur,1).(substr($Texte,$Ctr,1) ^ substr($CleDEncryptage,$Compteur,1) );
    $Compteur++;
  }
  return base64_encode(GenerationCle($VariableTemp,$Cle) );
}

/**
 * Permet de decrypter un texte
 */
function Decrypte($Texte,$Cle) {
  $Texte = GenerationCle(base64_decode($Texte),$Cle);
  $VariableTemp = "";
  for ($Ctr=0;$Ctr<strlen($Texte);$Ctr++) {
    $md5 = substr($Texte,$Ctr,1);
    $Ctr++;
    $VariableTemp.= (substr($Texte,$Ctr,1) ^ $md5);
  }
  return $VariableTemp;
}

/**
 * Permet de crypter un fichier
 */
function CrypteFichier($FichierDecrypte, $FichierCrypte, $Cle) {
  $FileHandler = fopen($FichierDecrypte, 'r');
  $TexteDecrypte = fread($FileHandler, filesize ($FichierDecrypte));
  fclose($FileHandler);

  $TexteCrypte = Crypte($TexteDecrypte, $Cle);

  $FileHandler = fopen($FichierCrypte, 'w');
  fwrite($FileHandler, $TexteCrypte);
  fclose($FileHandler);

  return TRUE;
}

/**
 * Permet de decrypter un fichier
 */
function DecrypteFichier($FichierCrypte, $FichierDecrypte, $Cle) {
  $FileHandler = fopen($FichierCrypte, 'r');
  $TexteCrypte = fread($FileHandler, filesize ($FichierCrypte));
  fclose($FileHandler);

  $TexteDecrypte = Decrypte($TexteCrypte, $Cle);

  $FileHandler = fopen($FichierDecrypte, 'w');
  fwrite($FileHandler, $TexteDecrypte);
  fclose($FileHandler);

  return TRUE;
}

?>