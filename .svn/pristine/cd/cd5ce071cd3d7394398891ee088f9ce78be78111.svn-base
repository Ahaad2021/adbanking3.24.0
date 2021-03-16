<?php
/**
 * @package Systeme
 */

require_once('lib/dbProcedures/guichet.php');
require_once('lib/dbProcedures/login_func.php');

function logged_logins() {

  // Fonction qui renvoie tous les logins connectés au système
  // 11/06 - TF - Renvoie également les logins dont la caisse n'a pas été fermée

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Utilisateurs loggés
  $sql = "SELECT login FROM ad_ses";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $retour = array();
  while ($row = $result->fetchrow()) {
    array_push($retour, $row[0]);
  }

  // Utilisateurs déloggés mais dont la caisse n' pas été fermée
  $sql = "SELECT id_gui FROM ad_gui WHERE id_ag = ".$global_id_agence." AND ouvert ='t'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  while ($row = $result->fetchrow()) {
    $login = getLoginFromGuichet($row[0]);
    if (!in_array($login, $retour))
      array_push($retour, $login);
  }

  $db = $dbHandler->closeConnection(true);
  return ($retour);
}

function force_logout($login) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM ad_ses WHERE login='$login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Fermeture du guichet
  $guichet = getIDGuichetFromLogin($login);
  if ($guichet != -1)
    fermetureGuichet($guichet);

  global $global_nom_login;
  ajout_historique(230, NULL, $login, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

function force_all_logout() {
  $logins = logged_logins();
  while (list(,$login) = each($logins))
    force_logout($login);
  return true;
}

function ajustementSoldeCptClient($id_cpte,$id_guichet, $solde, $piece_just) {
  // Fonction d'ajustement du solde d'un compte client
  // Fonction soumise à accès restreint

  global $dbHandler, $global_multidevise, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupère l'ancien solde ainsi que la caisse comptable
  $sql = "SELECT a.solde, a.id_titulaire, a.devise, b.classe_comptable FROM ad_cpt a, adsys_produit_epargne b ";
  $sql .= "WHERE a.id_ag = ".$global_id_agence." AND b.id_ag = ".$global_id_agence." AND a.id_prod = b.id AND a.id_cpte = ".$id_cpte;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result->getMessage()
  }
  $tmprow = $result->fetchrow();
  $ancien_solde = $tmprow[0];
  $id_client = $tmprow[1];
  $devise = $tmprow[2];
  $classe_comptable = $tmprow[3];
  $variation = $solde - $ancien_solde;

  if ($variation > 0)
    $type_oper = 440;
  else
    $type_oper = 442;

  // Passage des écritures comptables
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  // Récupération des du schéma de l'opération
  $result = getDetailsOperation($type_oper);
  $DetailsOperation = $result->param;

  $ACC = getAccountDatas($id_cpte);
  $devise = $ACC['devise'];

  if ($variation > 0) {
    // Récupération du compte au débit de l'opération
    $cptes_substitue["cpta"]["debit"] = $DetailsOperation["debit"]["compte"];
    $compte = $DetailsOperation["debit"]["compte"];

    $CPTE_CPTA = getComptesComptables(array("num_cpte_comptable" => $compte));

    if ($global_multidevise && $CPTE_CPTA[$compte]["devise"] != NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPT_MONODEVISE, _("compte comptable monodevise"));
    }

    //Produit du compte d'épargne associé
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["credit"] = $id_cpte;

  } else {
    // Récupération du compte au crédit de l'opération
    $cptes_substitue["cpta"]["credit"] = $DetailsOperation["credit"]["num_cpte"];
    $compte = $DetailsOperation["credit"]["compte"];

    $CPTE_CPTA = getComptesComptables(array("num_cpte_comptable" => $compte));

    if ($global_multidevise && $CPTE_CPTA[$compte]["devise"] != NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPT_MONODEVISE, _("compte comptable monodevise"));
    }

    //Produit du compte d'épargne associé
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;
  }

  $myErr = passageEcrituresComptablesAuto($type_oper, abs($variation), $comptable, $cptes_substitue, $devise);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  // Ajout dans l'historique
  global $global_nom_login;
  $myErr = ajout_historique(235, $id_client, "$id_cpte|$ancien_solde|$solde", $global_nom_login, date("r"), $comptable, $piece_just);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function getInfoSys($is_actif=null){


  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Utilisateurs loggés
  $sql = "SELECT * FROM adsys_infos_systeme where id_ag = $global_id_agence  ";

  if($is_actif == true)
  {
    $sql .= " and is_active = true ";
  }

  $sql .= " ORDER BY id";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }

  $db = $dbHandler->closeConnection(true);
  return ($retour);
}

?>