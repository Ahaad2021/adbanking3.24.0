<?php
/**
 * @package Ifutilisateur
 */
require_once 'lib/dbProcedures/handleDB.php';

function get_screen($nom_ecran) { //Recherche le fichier qui contient l'écran et la fonction à laquelle il est associé
  global $dbHandler;

  $db = $dbHandler->openConnection();

  $sql = "SELECT fichier, fonction FROM ecrans WHERE nom_ecran='$nom_ecran'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Ecran '%s' non trouvé dans la base de données!"), $nom_ecran));
  }

  $row = $result->fetchrow();
  $fresult['fichier'] = $row[0];
  $fresult['fonction'] = $row[1];
  $dbHandler->closeConnection(true);

  return $fresult;
}

function update_last_axs($time, $login) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $login=addslashes($login);
  $sql = "UPDATE ad_ses SET last_access='$time' WHERE login='$login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB:".$result->getMessage()
  }
  $dbHandler->closeConnection(true);
  return true;
}

?>