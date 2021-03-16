<?php
/**
 * @package Parametrage
 */
require_once 'lib/dbProcedures/interface.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/agence.php';

/*Procedures stockées pour le paramétrage*/

function get_profil_axs($profil_id) {
  /*
    Renvoie les droits d'accès du profil sous forme d'array
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT fonction FROM adsys_profils_axs WHERE profil=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  $i = 0;
  while ($row = $result->fetchrow()) { //On récupère chaque autorisation
    $retour[$i] = $row[0];
    ++$i;
  }

  $db = $dbHandler->closeConnection(true);
  return $retour;
}

function get_profil_nom($profil_id) {
  /*
     Renvoie le libellé du profil
  */

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT libel FROM adsys_profils WHERE id=$profil_id";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }

  $retour = $result->fetchrow();
  $retour = $retour[0];

  $db = $dbHandler->closeConnection(true);
  return $retour;
}

function get_profil_timeout($profil_id) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT timeout FROM adsys_profils WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }

  $retour = $result->fetchrow();
  $retour = $retour[0];

  $db = $dbHandler->closeConnection(true);
  return $retour;
}


function get_connexion_agence($profil_id) {
  /*
     Renvoie un booléen indiquant si un guichet est associé au profil
  */

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT conn_agc FROM adsys_profils WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }

  $retour = $result->fetchrow();
  $retour = $retour[0];

  $db = $dbHandler->closeConnection(true);
  return ($retour == 't');
}

function get_profil_guichet($profil_id) {
  /*
     Renvoie un booléen indiquant si un guichet est associé au profil
  */

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT guichet FROM adsys_profils WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }

  $retour = $result->fetchrow();
  $retour = $retour[0];

  $db = $dbHandler->closeConnection(true);
  return ($retour == 't');
}

function get_profil_acces_solde($profil_id, $prod_epargne_id = NULL) {
  /*
     Renvoie un booléen indiquant si le profil a accès au solde des comptes clients
  */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Vérifier si l'acces au solde est interdit pour ce produit d'epargne
  $masque_solde_epargne = 'f';
  if($prod_epargne_id != NULL){
		$sql = "SELECT masque_solde_epargne FROM adsys_produit_epargne WHERE id=$prod_epargne_id and id_ag = $global_id_agence";
	  $result = $db->query($sql);
	  if (DB::isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  }
	  if ($result->numrows() != 1) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Produit epargne '$prod_epargne_id' non-trouvé dans la base de données"
	  }
	  $retour = $result->fetchrow();
	  $masque_solde_epargne = $retour[0];
  }

  // Vérifier si l'acces au solde est interdit pour ce profil
  $sql = "SELECT access_solde FROM adsys_profils WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }

  $retour = $result->fetchrow();
  $profil_access = $retour[0];

  $db = $dbHandler->closeConnection(true);
  if($prod_epargne_id != NULL)
  	return (($profil_access == 't') || ($masque_solde_epargne == 'f'));
  else
  	return ($profil_access == 't');
}

function get_profil_acces_solde_vip($profil_id, $id_client = NULL) {
  /*
     Renvoie un booléen indiquant si le profil a accès au solde des comptes clients VIP
  */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Vérifier si l'acces au solde est interdit pour ce client
  $masque_solde_client_vip = 'f';
  if($id_client != NULL) {
          $sql = "SELECT pp_is_vip FROM ad_cli WHERE id_client=$id_client and id_ag = $global_id_agence";
	  $result = $db->query($sql);
	  if (DB::isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  }
	  if ($result->numrows() != 1) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Client '$id_client' non-trouvé dans la base de données"
	  }
	  $retour = $result->fetchrow();
	  $masque_solde_client_vip = $retour[0];
  }

  // Vérifier si l'acces au solde VIP est interdit pour ce profil
  $sql = "SELECT access_solde_vip FROM adsys_profils WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }

  $retour = $result->fetchrow();
  $profil_access_vip = $retour[0];

  $db = $dbHandler->closeConnection(true);
  if($id_client != NULL)
  	return (($profil_access_vip == 't') || ($masque_solde_client_vip == 'f'));
  else
  	return ($profil_access_vip == 't');
}

function manage_display_solde_access($access_solde, $access_solde_vip) {
    $display_solde = false;

    if($access_solde){
        $display_solde = true;

        if(!$access_solde_vip) {
            $display_solde = false;
        }
    }

    return $display_solde;
}

/**
 * Met a jour le libellé et le timeout d'un profil.
 *
 * @param int $profil_id Identifiant du profil
 * @param string $libel Libellé du profil
 * @param int $timeout Timeout du profil (en minutes)
 * @return ErrorObj
 */
function update_profil_libel_timeout($profil_id, $libel, $timeout) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $libel = string_make_pgcompatible($libel);
  if ($timeout == "") $timeout = 0;

  if ($timeout == 0 || ($timeout*60) > get_cfg_var("session.gc_maxlifetime")) {
    $return = new ErrorObj(ERR_TIMEOUT_INVALID, $timeout*60);
  } else {
    $return = new ErrorObj(NO_ERR);
  }

  $sql = "UPDATE adsys_profils SET libel='$libel', timeout=$timeout WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $db = $dbHandler->closeConnection(true);
  return $return;
}

function update_profil_axs($profil_id, $fonctions) {
  /* Met à jour les droits d'accès du profil*/
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $current = get_profil_axs($profil_id); //Récupère les anciennes autorisations d'accès

  //Recherche d'abord les autorisations à ajouter
  if (is_array($fonctions)) { //Si il y a au moins une fonction
    while (list($key, $value) = each($fonctions)) { //Pour chaque nouvelle autorisation
      if ((! is_array($current)) || (! in_array($value, $current))) { //Si l'accès n'est pas encore donné
        $sql = "INSERT INTO adsys_profils_axs(profil, fonction) VALUES($profil_id, $value)";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
      }
    }
  }

  reset($fonctions);

  //Recherche ensuite les autorisations à supprimer
  if (is_array($current)) { //Si il y avait au moins une fonction
    while (list($key, $value) = each($current)) { //Pour chaque ancienne autorisation
      if ((! is_array($fonctions)) || (! in_array($value, $fonctions))) { //Si l'accès n'est plus donné
        $sql = "DELETE FROM adsys_profils_axs WHERE (profil=$profil_id) AND (fonction=$value)";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
      }
    }
  }
  $db = $dbHandler->closeConnection(true);
  return true;
}

/**
 * Met à jour un profil.
 *
 * @param int $profil_id Identifiant du profil
 * @param string $libel Libellé du profil
 * @param int $timeout Timeout du profil (en minutes)
 * @param array $fonctions Tableau des fonctions
 * @return ErrorObj
 */
function update_profil($id_profil, $libel, $timeout, $conn_agc, $masque_solde, $fonctions, $masque_solde_vip) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $return = update_profil_libel_timeout($id_profil, $libel, $timeout);

  $conn_agence = 'f';
  if($conn_agc) {
    $conn_agence = 't';
  }
  if($masque_solde == 't') {
        $access_solde = 'f';
  }
  else {
  	$access_solde = 't';
  }
  if($masque_solde_vip == 't') {
        $access_solde_vip = 'f';
  }
  else {
  	$access_solde_vip = 't';
  }
  $sql = "UPDATE adsys_profils SET conn_agc='$conn_agence', access_solde='$access_solde', access_solde_vip='$access_solde_vip' WHERE id=$id_profil";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  update_profil_axs($id_profil, $fonctions);
  global $global_nom_login;
  ajout_historique(258,NULL, $id_profil, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return $return;
}

function logins_profil($profil_id) {
  /* Renvoie la liste des logins d'un profil dans un array. NULL si aucun. */

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT login FROM ad_log WHERE profil=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $i=0;
  $retour = array();
  while ($row = $result->fetchrow()) {
    $retour[$i] = $row[0];
    ++$i;
  }

  $db = $dbHandler->closeConnection(true);
  return $retour;
}

function supprime_profil($profil_id) {
  /*Supprime le profil et ses droits d'accès*/
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM adsys_profils_axs WHERE profil=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $sql = "DELETE FROM adsys_profils WHERE id=$profil_id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  global $global_nom_login;
  ajout_historique(259,NULL, $profil_id, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

function cree_profil($nom_profil, $fonctions, $guichet, $timeout, $masque_solde, $masque_solde_vip) {
  /* Crée le profil et ses droits d'accès*/

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $nom_profil = string_make_pgcompatible($nom_profil);

  $sql = "SELECT nextval('adsys_profils_id_seq')"; //Recherche l'id du profil
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $id = $result->fetchrow();
  $id = $id[0];

  if ($guichet) $b = 't';
  else $b = 'f';
  if ($timeout == "") $timeout = 0;
  if($masque_solde == 't') {
  	$access_solde = 'f';
  } else {
  	$access_solde = 't';
  }
  if($masque_solde_vip == 't') {
  	$access_solde_vip = 'f';
  } else {
  	$access_solde_vip = 't';
  }
  $sql = "INSERT INTO adsys_profils(id, libel, guichet, timeout, access_solde, access_solde_vip) VALUES($id, '$nom_profil', '$b', $timeout, '$access_solde', '$access_solde_vip')"; //Crée le profil
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  update_profil_axs($id, $fonctions);

  global $global_nom_login;
  ajout_historique(256,NULL, $id, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

function check_pass($login, $pwd) {
  /*
    Vérifie si le mote de passe correspond bien au login; renvoie true ou false
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT pwd=md5('$pwd') FROM ad_log WHERE login='$login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();

  $db = $dbHandler->closeConnection(true);
  return ($row[0] == 't');
}

function update_pass($login, $pwd, $other=false) {
  /*
    Modifie le mot de passe
    $other : modifie-t-on le mot de passe de qqun d'autre ?
  */
  global $dbHandler, $global_nom_login;
  $db = $dbHandler->openConnection();
  $date=date("d/m/Y");//date du jour
  $sql = "UPDATE ad_log SET pwd=md5('$pwd'), date_mod_pwd='$date' WHERE login='$login'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if (!$other) ajout_historique(260, NULL, $login, $global_nom_login, date("r"), NULL);
  else ajout_historique(215, NULL, $login, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

function update_other_pass($login, $pwd) {
  update_pass($login, $pwd, true);
}

function get_tablefield_info($table_name, $primary_value) {
  // Renvoi les informations d'une entrée d'une table
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array();
  $sql = "SELECT ident FROM tableliste WHERE nomc='$table_name'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $ident = $row[0];

  $sql = "SELECT nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey,traduit FROM d_tableliste WHERE tablen=$ident";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $i = 1;
  $sql = "";
  $existe_id_ag=0;
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row['ispkey'] == 't') {
      $cle_prim = $row['nchmpc'];
      $retour['pkey'] = $row['nchmpc'];
    } else {
      $sql .= $row['nchmpc'].", ";
      $retour[$row['nchmpc']]['nom_long']	= new Trad($row['nchmpl']);  //Nom table
      $retour[$row['nchmpc']]['requis']	= ($row['isreq'] == 't');
      $retour[$row['nchmpc']]['ref_field']	= $row['ref_field'];
      $retour[$row['nchmpc']]['type']	= $row['type'];
      $retour[$row['nchmpc']]['traduit']	= ($row['traduit'] == 't');

      if ($row['ref_field']) { // Si ref_field on va chercher les valeurs possibles
        $retour[$row['nchmpc']]['choices'] = getReferencedFields($row['ref_field']);
      }
    }
    if ($row['nchmpc']=='id_ag')
      $existe_id_ag=1;
    ++$i;
  }


  $sql = substr($sql, 0, strlen($sql)-2);//Enlève le ", " final
  //Va chercher les valeurs si demandé

  if ($primary_value != NULL) {
    $sql = "SELECT ".$sql." FROM $table_name WHERE $cle_prim='$primary_value'";
    if ($existe_id_ag==1)
      $sql.=" and id_ag=$global_id_agence ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    foreach ($row AS $key => $value)
    if (is_champ_traduit($table_name,$key))
      $retour[$key]['val'] = new Trad($value);
    else
      $retour[$key]['val'] = $value;
  }

  $db = $dbHandler->closeConnection(true);
  return $retour;
}

function update_tablefield($nom_table, $nom_pkey, $val_pkey, $DATA) {
  global $global_id_agence;
  $global_id_agence=getNumAgence();
  // ETAPE 1: Update des champs traduits
  foreach($DATA AS $nom_champ => $valeur)
  if (is_champ_traduit($nom_table,$nom_champ)) {
    $valeur->save();
    unset ($DATA[$nom_champ]);
  };

  if (count($DATA) == 0)
    // Si l'UPDATE concernait uniquement des champs traduits, il n'y a plus rien à faire
    return true;
  $DATA=array_make_pgcompatible($DATA);
  reset($DATA);

  // ETAPE 2: Update des autres champs
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();

  $sql = "UPDATE $nom_table SET ";
  while (list($key, $value) = each($DATA)) {
    $value="$value"; // Pour que le chiffre 0 ne soit pas interprété comme ''
    if (($value != "") && ($value != "NULL"))
      $sql .= $key."='".$value."', ";
    if  ($value == "NULL") $sql .= $key."= NULL, ";
  }

  $sql = substr($sql, 0, strlen($sql)-2);

  $sql .= " WHERE id_ag=$global_id_agence AND $nom_pkey='$val_pkey'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  /* Si produit épargne, vérifier qu'il ne faut pas appliquer le taux, la fréquence et le mode de calcul aux comptes existants */
  if ( $nom_table == "adsys_produit_epargne" and $DATA["modif_cptes_existants"] == 't') {
    $sql = "UPDATE ad_cpt SET tx_interet_cpte=".$DATA["tx_interet"].", freq_calcul_int_cpte=".$DATA["freq_calcul_int"].", mode_calcul_int_cpte=".$DATA["mode_calcul_int"]." WHERE id_ag=$global_id_agence AND id_prod=$val_pkey AND etat_cpte=1";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

   /*Suppression de toute autorisation d'accès à la fonction "Radiation crédit" de la table adsys_profils_axs, quand le passage en perte est automatique*/
  if ( $nom_table == "ad_agc" and $DATA["passage_perte_automatique"] == 't') {
    $sql = "DELETE FROM adsys_profils_axs WHERE fonction=475";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  $db = $dbHandler->closeConnection(true);
  return true;
}

function delete_tablefield($nom_table, $nom_pkey, $val_pkey) {
  global $dbHandler,$global_id_agence ;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM $nom_table WHERE $nom_pkey='$val_pkey' and id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $db = $dbHandler->closeConnection(true);
  return true;
}

function supprime_table($nom_table, $nom_pkey, $val_pkey) {
  //FIXME = Gestion d'erreur base de données
  global $dbHandler;
  $db = $dbHandler->openConnection();

  delete_tablefield($nom_table, $nom_pkey, $val_pkey);

  global $global_nom_login;
  ajout_historique(298, NULL, $nom_table, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

/**
 * Effectue la modification d'une entrée d'une table de paramétrage
 * @author Bernard De Bois
 * @param text $nom_table Nom de la table de paramétrage
 * @param text $nom_pkey Nom du champ clé primaire identifiant l'entrée qui est modifiée (en général 'id')
 * @param int $val_pkey Valeur de $nom_pkey
 * @param Array $DATA Tableau contenant les nouvelles valeurs
 * @return ErrorObj Objet Erreur
 */
function modif_table($nom_table, $nom_pkey, $val_pkey, $DATA) {

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $test_data = completeDatas($nom_table, $nom_pkey, $val_pkey, $DATA);

  $myErr = verif_donnees_table($nom_table, $test_data, "update");
  if ($myErr->errCode!=NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $comptable_his = $myErr->param;

  update_tablefield($nom_table, $nom_pkey, $val_pkey, $DATA);

  global $global_nom_login;

  $myErr = ajout_historique(294, NULL, $nom_table, $global_nom_login, date("r"), $comptable_his);
  if ($myErr->errCode!=NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * renvoie toutes les données d'un record contenant les valeurs non-modifiées et les valeurs modifiées afin de tester la validité des données entre elles
 * @author Bernard De Bois
 * @param $nom_table char : Nom de la table
 * @param $nom_pkey char : Nom de la clé primaire
 * @param $val_pkey char : valeur de la clé primaire dont on fait les modifications.
 * @param $DATA array : tableau contenant les données à modifier (nom des champs / valeurs)
 * @return $final_datas array : retourne un tableau contenant tous les champs correspondant à la clé primaire tels qu'ils devraient être si la modification est autorisée.
 */
function completeDatas($nom_table, $nom_pkey, $val_pkey, $DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM $nom_table WHERE $nom_pkey='$val_pkey' and id_ag=$global_id_agence ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $original_datas = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $final_datas = $original_datas;
  foreach ($DATA as $key=>$value) {
    $final_datas[$key]=$value;
  }
  $db = $dbHandler->closeConnection(true);
  return $final_datas;
}

/**
 * ajout_table Ajoute une entrée à une table de paramétrage dans la BD.
 *
 * @param char $nom_table : nom de la table
 * @param array $DATA : tableau contenant les données de la table
 * @access public
 * @return void
 */
function ajout_table($nom_table, $DATA) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Ticket JIRA MSQ-37
  if(is_array($DATA)){
      if ($DATA['type_opt'] == null && $DATA['libelle'] == 'Ecriture Libre' && $DATA['preleve_frais'] != null && $DATA['date_creation'] != null){
          $DATA['type_opt'] = 0;
      }
  }
  // Ticket JIRA MSQ-37

  // ETAPE 1: Création des traductions
  foreach($DATA AS $nom_champ => $valeur)
  if (is_champ_traduit($nom_table,$nom_champ)) {
    $valeur->save();
    $DATA[$nom_champ] = $valeur->get_id_str();
  };

  // ETAPE 2: Ajout champs proprement dit
  $myErr=verif_donnees_table ($nom_table, $DATA, "insert");
  if ($myErr->errCode!=NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $DATA = array_make_pgcompatible($DATA);

  $champs = "";
  $valeurs = "";

  foreach($DATA AS $key => $value) {
    $champs .= $key.", ";
    if (($value == "") || ($value == "NULL")) $valeurs .= "NULL, ";
    else $valeurs .= "'$value', ";
  }
  $id_agence=getNumAgence();
  $champs = substr($champs, 0, strlen($champs)-2);
  $valeurs = substr($valeurs, 0, strlen($valeurs)-2);
  $sql = "INSERT INTO $nom_table(id_ag,$champs) VALUES($global_id_agence,$valeurs)";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  global $global_nom_login;
  ajout_historique(295, NULL, $nom_table, $global_nom_login, date("r"), NULL);
  $db = $dbHandler->closeConnection(true);
  return $myErr;
}

/**
 * Effectue des contrôles sur les données à insérer ou à modifier dans une table.
 * Peut aussi effectuer certains traitement de preprocessing
 * @author Bernard De Bois / Thomas Fastenakel
 * @param text $nom_table Nom de la table controlée
 * @param Array $data : champs à insérer
 * @param text $type : "insert" ou "update"
 * @return ErrorObj $myErr : message d'erreur
 */
function verif_donnees_table($nom_table, $data, $type) {
  global $global_id_agence;
  switch ($nom_table) {
  case "adsys_correspondant" :
    //on vérifie que les trois comptes comptables du correspondant sont alimentés.
    if ($data['cpte_bqe']==null        || $data['cpte_bqe']=='NULL'        || $data['cpte_bqe']==''
        || $data['cpte_ordre_deb']==null  || $data['cpte_ordre_deb']=='NULL'  || $data['cpte_ordre_deb']==''
        || $data['cpte_ordre_cred']==null || $data['cpte_ordre_cred']=='NULL' || $data['cpte_ordre_cred']=='') {
      return new ErrorObj(ERR_CPTE_ABSENT);
    }
    //on vérifie que les trois comptes choisis ont la même devise et qu'une devise est renseignée.
    $param = array('num_cpte_comptable'=>$data['cpte_bqe']);
    $cpte_bqe = getComptesComptables($param);
    $cpte_bqe = $cpte_bqe[$data['cpte_bqe']];

    $param = array('num_cpte_comptable'=>$data['cpte_ordre_deb']);
    $cpte_ordre_deb = getComptesComptables($param);
    $cpte_ordre_deb = $cpte_ordre_deb[$data['cpte_ordre_deb']];

    $param = array('num_cpte_comptable'=>$data['cpte_ordre_cred']);
    $cpte_ordre_cred = getComptesComptables($param);
    $cpte_ordre_cred = $cpte_ordre_cred[$data['cpte_ordre_cred']];


    if ($cpte_bqe['devise'] != $cpte_ordre_deb['devise'] || $cpte_bqe['devise'] != $cpte_ordre_cred['devise'])
      return new ErrorObj(ERR_DEVISE_CPT_DIFF);
    if ($cpte_bqe['devise']        == null ||
        $cpte_ordre_deb['devise']  == null ||
        $cpte_ordre_cred['devise'] == null)
      return new ErrorObj(ERR_NO_DEVISE);
    //si pas d'erreur, on retourne l'objet NO_ERR
    return new ErrorObj(NO_ERR);
  case "adsys_produit_epargne":
    if ($type == "update") {
      $id_prod = $data["id"];
      $PROD = getProdEpargne($id_prod);
      $cpte_cpta_prod_ep = $PROD["cpte_cpta_prod_ep"];
      $devise = $PROD["devise"];
      if ( ($cpte_cpta_prod_ep != NULL and $cpte_cpta_prod_ep !='') and ($cpte_cpta_prod_ep != $data["cpte_cpta_prod_ep"])) {
        global $dbHandler;
        $db = $dbHandler->openConnection();
        $comptable_his = array();
        // Il faut passer une écriture du motnant total de l'épargne vers le nouveu compte
        // Calcul de la somme des montants de ce produit
        $sql = "SELECT SUM(solde) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod = $id_prod";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $row = $result->fetchrow();
        $solde_total = $row[0];
        if ($solde_total > 0) {
          $cptes_substitue = array();
          $cptes_substitue["cpta"] = array();
          $cptes_substitue["cpta"]["debit"] = $cpte_cpta_prod_ep;
          $cptes_substitue["cpta"]["credit"] = $data["cpte_cpta_prod_ep"];
          $myErr = passageEcrituresComptablesAuto(1003, $solde_total, $comptable_his, $cptes_substitue, $devise);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }

        }
        $dbHandler->closeConnection(true);
      }
      return new ErrorObj(NO_ERR, $comptable_his);
    }
  default :
    return new ErrorObj(NO_ERR);
  }
}

function get_prod_non_financiers() { //Renvoie les id de tous les produits d'épargne non-financiers
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id FROM adsys_produit_epargne WHERE id_ag=$global_id_agence AND service_financier = 'f'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  while ($row = $result->fetchrow()) array_push($retour, $row[0]);

  $db = $dbHandler->closeConnection(true);
  return $retour;
}

function getEpargneNantieProductID($id_agence) { // Renvoie le num de produit définissant les comptes d'épargne nantie
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Récupération du n° de produit d'épargne utilisé par l'agence pour les comptes d'épargne nantie
  $sql = "SELECT id_prod_cpte_epargne_nantie FROM ad_agc WHERE id_ag = $id_agence;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $id_prod = $tmpRow[0];
  $dbHandler->closeConnection(true);
  return $id_prod;
}

function isEncaisseNul($login) {
  // Renvoie true si l'encaisse du guichet associé au login est 0 sinon false
  // Renvoie true aussi si un guichet n'est pas associé au login

  global $dbHandler;
  $db = $dbHandler->openConnection();
  $login=addslashes($login);
  // Récupération du guichet associé au login
  $sql = "SELECT guichet FROM ad_log WHERE login='$login'";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $tmpRow = $result->fetchrow();
  $guichet = $tmpRow[0];

  if ($guichet) { // si un guichet est associé ?
    // Récupération de la liste de toutes les devises
    $temp = get_table_devises();

    // voir si l'encaisse correspondant à chaque devise est différente de 0
    foreach ($temp as $key => $value) {
      $encaisse = get_encaisse($guichet,$key);
      if ($encaisse != 0) {
        $dbHandler->closeConnection(true);
        return false;
      }
    }

    // si pas de devise, vérifier aussi que le solde du compte comptable lié directement au guichet n'est pas null
    $encaisse = get_encaisse($guichet);
    if ($encaisse != 0) {
      $dbHandler->closeConnection(true);
      return false;
    }
  }

  // A ce stade, pas de guichet associé ou bien guichet associé et toutes les encaisses sont null
  $dbHandler->closeConnection(true);
  return true;

}

function getLibelJoursFeries()
// Fonction qui construit des libellés en fonction des jours fériés définis dans ad_fer
// Ces libellés sot du style 'Tous les 25 décembre' ou encore '14 mai 2003'
// IN : Rien
// OUT: Un tableau de type 'libel' => 'string décrivant le(s) jour(s) férié(s)
{
  global $dbHandler;
  $db = $dbHandler->openConnection();

  global $adsys,$global_id_agence;
  $sql = "SELECT * FROM ad_fer where id_ag=$global_id_agence ;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $choix = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $choix[$row['id_fer']] = date_to_texte($row['jour_semaine'],$row["date_jour"],$row["date_mois"],$row["date_annee"]);
    if ($choix[$row['id_fer']] == "")
      $choix[$row['id_fer']] = $row['id_fer'];
  };
  $dbHandler->closeConnection(true);
  return $choix;
}

function getLibelBanques() {
  /*
  Fonction qui extrait les libellés des banques dans ad_cpt_comptable correspondant aux entrèes dans adsys_banques
  IN : Rien
  OUT: Un tableau de type ('libel' => 'string décrivant le(s) libellé(s) des banques, le compte comptable associé et sa devise')
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_banque, cpte_cpta_bqe,libel_cpte_comptable,devise ";
  $sql .= "FROM adsys_banques, ad_cpt_comptable  ";
  $sql .= "WHERE adsys_banques.id_ag=ad_cpt_comptable.id_ag AND adsys_banques.id_ag=$global_id_agence AND num_cpte_comptable=cpte_cpta_bqe";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $libels=array();

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $libels["libel_cpte_comptable"][$row["id_banque"]] = $row["libel_cpte_comptable"];
    $libels["cpte_cpta_bqe"][$row["id_banque"]] = $row["cpte_cpta_bqe"];
    $libels["devise"][$row["id_banque"]] = $row["devise"];
  }

  $dbHandler->closeConnection(true);

  return $libels;
}

/**
 * Renvoie les libellés à afficher pour le choix d'un correspondant bancaire
 * @author Bernard De Bois
 * @param char(3) $devise La devise des correspondants à renvoyer
 * @return Array Tableau contenant le libellé et la clef du correspondant
 */
function getLibelCorrespondant($devise = NULL) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  //$sql = "SELECT a.id, numero_cpte, nom_banque ";
  $sql = "SELECT a.id, nom_banque, c.devise ";
  $sql .= "FROM adsys_correspondant a, adsys_banque b, ad_cpt_comptable c ";
  $sql .= "WHERE a.id_ag=b.id_ag AND b.id_ag=c.id_ag AND a.id_ag=$global_id_agence AND a.id_banque=b.id_banque
          AND a.cpte_bqe = c.num_cpte_comptable";
  if (isset($devise))
    $sql .= " AND c.devise='$devise'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $libels=array();

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $libels[$row['id']]=$row['nom_banque']." - ".$row['cpte_bqe']."(".$row['devise'].")";
  }

  $dbHandler->closeConnection(true);

  return $libels;
}


function getCorrespondantByLibelleBanque($nom, $devise = NULL) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  //$sql = "SELECT a.id, numero_cpte, nom_banque ";
  $sql = "SELECT a.id, a.id_banque, nom_banque, c.devise ";
  $sql .= "FROM adsys_correspondant a, adsys_banque b, ad_cpt_comptable c ";
  $sql .= "WHERE a.id_ag=b.id_ag AND b.id_ag=c.id_ag AND a.id_ag=$global_id_agence AND b.nom_banque = '$nom' AND a.id_banque=b.id_banque
          AND a.cpte_bqe = c.num_cpte_comptable";
  if (isset($devise))
    $sql .= " AND c.devise='$devise'";

  $sql .= " LIMIT 1";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $row = $result->fetchrow();

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return null;
  }

  $dbHandler->closeConnection(true);

  return $row[0];
}

/**
 * Renvoie les libellés à afficher pour le choix d'une banque
 * @author Bernard De Bois
 * @return un tableau contenant le libellé et la clef de la banque
 */
function getLibelBanque() {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_banque, nom_banque ";
  $sql .= "FROM adsys_banque WHERE id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $libels=array();

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $libels[$row['id_banque']]=$row['nom_banque'];
  }

  $dbHandler->closeConnection(true);

  return $libels;
}



/**
 *  modifie les différents billets d'une devise donné
 * @author Mamadou Mbaye
 * @param $valeur tableau comprenant l'ensemble des valeur
 * @param $devise la dvise
 * @return 1
 */
function modifyBillet($valeur,$devise) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  // Suppression des billets existantes
  $sql ="DELETE FROM adsys_types_billets WHERE id_ag=$global_id_agence AND devise ='".$devise."';";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  // insertion des billets
  foreach($valeur as $key => $value) {
    $tmp=array();
    $tmp["valeur"]=$value;
    $tmp["devise"]=$devise;
    $tmp["id_ag"]=$global_id_agence;
    $sql = buildInsertQuery ("adsys_types_billets",$tmp);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }
  }
  $dbHandler->closeConnection(true);
  return 1;
}

/**
 * récupére les différents billets d'une devise donné
 * @author Mamadou Mbaye
 * @param $devise la dvise
 * @return $billet l'ensemble des billet
 */
function recupeBillet($devise) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql ="SELECT  valeur FROM adsys_types_billets WHERE id_ag=$global_id_agence AND devise= '".$devise."' ORDER BY valeur DESC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $billet = array();
  while ($tmprow = $result->fetchrow())
    array_push($billet,$tmprow[0]);
  return $billet;
}

/**
 * récupére les types de biens
 * @author papa
 * @param int $id_bien ou null
 * @return array tableau contenant les types de biens retrouvés
 */
function getTypesBiens($id_bien=NULL) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql ="SELECT  * FROM adsys_types_biens";
  if ($id_bien != NULL)
    $sql .=" WHERE id_ag=$global_id_agence AND id=".$id_bien;
  $sql .=" ORDER BY id DESC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $types_biens = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $types_biens[$row['id']] = $row['libel'];

  $dbHandler->closeConnection(true);
  return $types_biens;
}
/**
 * Liste des pays par agence
 * @return array Liste des pays
 */
function getListePays() {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM adsys_pays WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $PY = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $PY[$row['id_pays']]= $row['libel_pays'];

  return $PY;

}

/**
 * Liste des types bien
 * @return array Liste des types de biens
 */
function getListeTypeBien() {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM adsys_types_biens WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $TB = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $TB[$row['id']]= $row['libel'];

  return $TB;

}
/**
 * Liste des types de billets
 * @return array Liste des types de billet
 */
function getListeTypeBillet() {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT DISTINCT devise FROM ad_cpt_comptable WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $TB = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $TB[$row['devise']]= $row['devise'];



  return $TB;

}
/**
 * Liste des types pièce d'identité
 * @return array Liste des types pièce d'identité
 */
function getListeTypePiece() {

  global $dbHandler,$global_id_agence, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

	$sql .= "SELECT id, traduction(libel, '$global_langue_systeme_dft') as libel FROM adsys_type_piece_identite WHERE id_ag=$global_id_agence ";
  //$sql .= "SELECT * FROM adsys_type_piece_identite WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $TP = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $TP[$row['id']]= $row['libel'];

  return $TP;
}

/**
 * Liste des types pièce comptable
 * @return array Liste des types pièce comptable
 */
function getListeTypePieceComptables() {

  global $dbHandler,$global_id_agence, $global_langue_systeme_dft, $global_langue_utilisateur;
  $db = $dbHandler->openConnection();

	$sql .= "SELECT id, traduction(libel, '$global_langue_utilisateur') as libel FROM adsys_type_piece_payement WHERE id_ag=$global_id_agence order by id ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  $TP = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $TP[$row['id']]= $row['libel'];

  return $TP;
}

/**
 * Liste des taxes
 * @return array Liste des taxes qu'on peut appliquer dans adbanking
 */
function getListeTaxes() {

  global $dbHandler,$global_id_agence, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

	$sql .= "SELECT id, traduction(libel, '$global_langue_systeme_dft') as libel FROM adsys_taxes WHERE id_ag=$global_id_agence order by id ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  $list_tax = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $list_tax[$row['id']]= $row['libel'];

  return $list_tax;
}

/**
 * Liste des secteurs d'activités par agence
 * @return array Liste des secteurs d'activités
 */
function getListeSectActivite() {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM adsys_sect_activite WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $PY = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $PY[$row['id']]= $row['libel'];

  return $PY;

}
/**
 * Liste des langues par agence
 * @return array Liste des banques
 */
function getListeLangue() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM adsys_langue WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $LG = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $LG[$row['id']]= $row['libel'];

  return $LG;

}
/**utilisée par les rapport de la BNR
 * permet de faire le contrôle sur le fichier de paramètrage des rapport de comptabilité
 * @param fichier	$fichier chemin du fichier à charger
 * @param integer	$type_etat type de rapport
 */
function parse_format_etat_compta($fichier,$type_etat) {

	global $global_id_agence;



	if (!file_exists($fichier)) {
		return new ErrorObj(ERR_FICHIER_DONNEES);
	}

	$handle = fopen($fichier, 'r');

	$count = 0;
	$num_complet_cpte = "";
	$tab_compte=getComptesComptables();
	$DATA=array();
	while (($data = fgetcsv($handle, 200, ';')) != false) {

		if($count!=0) {
			// verifiaction nombre de colonne
			$num = count($data);
			if ( ($num != 8 && $type_etat==1) || ($num != 7 && $type_etat!=1) ){
				return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count+1,"Nbre col"=>$num));
				fclose($handle);
			}
			//verifier si les numero d'ordre son des nombres'
			$num_ord = $data[0];
			preg_match("([0-9]+)", $num_ord, $result);

			if (strlen($num_ord) != strlen($result[0])) {
				return new ErrorObj(ERR_NBR, array("ligne" => $count+1));
				fclose($handle);
			}
			//verifier si le numero du compartiment est valide
			$num_compart=$data[3];
			if ( ( ($type_etat==1 || $type_etat==5) && ($num_compart!=1 ) ) && ( ($type_etat==1 || $type_etat==5) && ($num_compart!=2 ) )  && ( ($type_etat==1 ) && ($num_compart!=0 ) )){
				return new ErrorObj(ERR_NUM_COMPART_NON_VALIDE, array("ligne" => $count+1,"valeur"=>$num_compart,"valeur possible"=>_("1 ou 2")));
				fclose($handle);
			}elseif( ( ($type_etat==2) && ($num_compart!=3 ) ) && ( ($type_etat==2) && ($num_compart!=4 ) ) ){
				return new ErrorObj(ERR_NUM_COMPART_NON_VALIDE, array("ligne" => $count+1,"valeur"=>$num_compart,"valeur possible"=>_("3 ou 4")));
				fclose($handle);
			}


			//verifier si les comptes comptables associés existe
			if($data[6]!='') {
				$comptes=split('[/,;]', $data[6]);
			}else {
				$comptes=NULL;
			}
			foreach ($comptes as $key=>$valeur) {
				if(!isset($tab_compte[$valeur])) {
					fclose($handle);
					return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count+1,"compte"=>$valeur));
				}
			}
			//verifier si les comptes comptables associés existe
			if($data[7]!='') {
				$comptes_prov=split('[/,;]', $data[7]);
			}else {
				$comptes_prov=NULL;
			}
			foreach ($comptes_prov as $key=>$valeur) {
				if(!isset($tab_compte[$valeur])) {
					fclose($handle);
					return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count+1,"compte"=>$valeur));
				}
			}


			//verifier si le numero d'ordre du  père existe
			$num_ord_pere=$data[4];
			if(!isset($DATA[$num_ord_pere]) && $DATA[$num_ord_pere]['donnee']["id_poste_centralise"]!=''  ) {

				return new ErrorObj(ERR_NON_ID_PERE, array("ligne " => $count+1));
			}
			//calcule du niveau du poste par rapport au père
			if(isset($DATA[$num_ord_pere])){
				$niveau=(1)*$DATA[$num_ord_pere]['donnee']['niveau'] + 1;
			}else {
				$niveau=$data[5];
			}
			$DATA[$count]['donnee']=array("id_poste"=>$data[0],"code"=>$data[1],"libel"=>$data[2],"compartiment"=>$data[3],"id_poste_centralise"=>$data[4],"niveau"=>$niveau,"type_etat"=>$type_etat);
			$DATA[$count]['compte']=$comptes;
			$DATA[$count]['compte_prov']=$comptes_prov;



		}$count++;
	}

	fclose($handle);


	return new ErrorObj(NO_ERR, array('data' => $DATA));
}
/**
 *insertion des postes ds ad_poste
 *@param array $DATA données à inserer
 *@return ErrorObj
 *
  */
function insertionPostes($DATA) {
	global $dbHandler;

	$db = $dbHandler->openConnection();
	//recuperez le dernier numero de la table ad_poste
	$sql = "SELECT MAX(id_poste)   FROM ad_poste  ";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$row = $result->fetchrow();
	$id_poste_debut=$row[0];

	foreach ($DATA as $cle=>$data) {
		//insertion dans la table ad_poste
		$tab_donnee=$data['donnee'];
		$tab_compte=$data['compte'];
		$tab_compte_prov=$data['compte_prov'];
		//clé du psote
		$tab_donnee['id_poste']+=$id_poste_debut;
		//cle du pere
		if($tab_donnee['id_poste_centralise']=="" || $tab_donnee['id_poste_centralise']==NULL) {
			$tab_donnee['id_poste_centralise']=NULL;
		}else {
			$tab_donnee['id_poste_centralise']+=$id_poste_debut;
		}

		$MyErr=insertionPoste($tab_donnee);
		if ($MyErr->errCode != NO_ERR) {
			$dbHandler->closeConnection(false);
			return $MyErr;
		}
		// insertion ds ad_poste_comptes
		if($tab_compte != NULL && is_array($tab_compte)){
			//on récupère le id du poste qu'on vient d'insérer
			$sql = "select max(id_poste) from 	ad_poste ";
			$result = $db->query($sql);
			if (DB::isError($result)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__,__LINE__,__FUNCTION__);
			}
			$tmprow = $result->fetchrow();
			$id_poste = $tmprow[0];
			foreach ($tab_compte as $compte){
				$tab_compte_poste['id_poste']=$id_poste;
				$tab_compte_poste['num_cpte_comptable']=$compte;
				//$tab_compte_poste['is_cpte_provision']=false;
				$MyErr=insertionPosteCompte($tab_compte_poste);
				if ($MyErr->errCode != NO_ERR) {
					$dbHandler->closeConnection(false);
					return $MyErr;
				}
			}
			//insertion des comptes des provisions
			foreach ($tab_compte_prov as $compte){
				$tab_compte_poste_prov['id_poste']=$id_poste;
				$tab_compte_poste_prov['num_cpte_comptable']=$compte;
				$tab_compte_poste_prov['is_cpte_provision']=true;
				$MyErr=insertionPosteCompte($tab_compte_poste_prov);
				if ($MyErr->errCode != NO_ERR) {
					$dbHandler->closeConnection(false);
					return $MyErr;
				}
			}
		}

	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
	/**
 *insertion des postes ds ad_poste
 *@param array $DATA données à inserer
 *@return ErrorObj
 *
 */
function insertionPostesAndComptes($DATA) {
	global $dbHandler;

	$db = $dbHandler->openConnection();
	//recuperez le dernier numero de la table ad_poste
	$sql = "SELECT MAX(id_poste)   FROM ad_poste  ";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$row = $result->fetchrow();
	$id_poste_debut=$row[0];

	foreach ($DATA as $cle=>$data) {
		//insertion dans la table ad_poste
		$tab_donnee=$data['donnee'];
		$tab_compte=$data['compte'];
		$tab_compte_prov=$data['compte_prov'];
		$MyErr=insertionPoste($tab_donnee);
		if ($MyErr->errCode != NO_ERR) {
			$dbHandler->closeConnection(false);
			return $MyErr;
		}
		// insertion ds ad_poste_comptes
		if($tab_compte != NULL && is_array($tab_compte)){
			//on récupère le id du poste qu'on vient d'insérer
			$sql = "select max(id_poste) from 	ad_poste ";
			$result = $db->query($sql);
			if (DB::isError($result)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__,__LINE__,__FUNCTION__);
			}
			$tmprow = $result->fetchrow();
			$id_poste = $tmprow[0];
			foreach ($tab_compte as $compte){
				$tab_compte_poste['id_poste']=$id_poste;
				$tab_compte_poste['num_cpte_comptable']=$compte[0];
				$tab_compte_poste['code']= $tab_donnee['code'];
				$tab_compte_poste['signe']=$compte[1];
				if(isset($compte[2])) {
					$tab_compte_poste['operation']=$compte[2];
				}
				//$tab_compte_poste['is_cpte_provision']=false;
				$MyErr=insertionPosteCompte($tab_compte_poste);
				if ($MyErr->errCode != NO_ERR) {
					$dbHandler->closeConnection(false);
					return $MyErr;
				}
			}
			//insertion des comptes des provisions
			foreach ($tab_compte_prov as $compte){
				$tab_compte_poste_prov['id_poste']=$id_poste;
				$tab_compte_poste_prov['code']= $tab_donnee['code'];
				$tab_compte_poste_prov['num_cpte_comptable']=$compte[0];
				$tab_compte_poste_prov['signe']=$compte[1];
				if(isset($compte[2])) {
					$tab_compte_poste_prov['operation']=$compte[2];
				}
				$tab_compte_poste_prov['is_cpte_provision']=true;
				$MyErr=insertionPosteCompte($tab_compte_poste_prov);
				if ($MyErr->errCode != NO_ERR) {
					$dbHandler->closeConnection(false);
					return $MyErr;
				}
			}
		}

	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
/**
 * Fonction : insertion d'un Poste
 * param $DATA array tableau des données à inserer
 * return errObj object
 */
 function insertionPoste($DATA){
 	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = buildInsertQuery ("ad_poste",  $DATA);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }
/**
 * Fonction : insertion des comptes de Poste
 * param $DATA array tableau des données à inserer
 * return errObj object
 */
function insertionPosteCompte($DATA){
	global $dbHandler;

	$db = $dbHandler->openConnection();
	$sql = buildInsertQuery ("ad_poste_compte",  $DATA);
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
 /**
 * Fonction: teste l'existance d'un type de rapport
 *
 */
function existetypeRapport($type_rapport){
	global $dbHandler;

	$db = $dbHandler->openConnection();
	$sql = "SELECT MAX(*) FROM ad_poste a,ad_poste_compte WHERE a.code = b.code AND type_etat=".$type_rapport;
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$row= $result->fetchrow();

	$dbHandler->closeConnection(true);
	if($row[0]>0){
		return true;
	}else {
		return false;
	}

}

  
  
  /**
 * Fonction: teste l'existance des poste pour un  rapport
 *
 */
function existePosteRapport($code_rapport){
	global $dbHandler;

	$db = $dbHandler->openConnection();
	$sql = "SELECT MAX(*) FROM ad_poste a WHERE code_rapport='".$code_rapport."'";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$row= $result->fetchrow();

	$dbHandler->closeConnection(true);
	if($row[0]>0){
		return true;
	}else {
		return false;
	}

}
/**
 * Fonction: teste l'existance des poste pour un  rapport
 *
 */
function existePosteCompteRapport($code_rapport){
	global $dbHandler;

	$db = $dbHandler->openConnection();
	$sql = "SELECT MAX(*) FROM  ad_poste_compte  WHERE code in (SELECT code FROM ad_poste where  code_rapport='".$code_rapport."')";
	
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$row= $result->fetchrow();

	$dbHandler->closeConnection(true);
	if($row[0]>0){
		return true;
	}else {
		return false;
	}

}
  /**
   * Fonction: supprime un rapport
   *
   *
   */
 function deleteRapport($type_rapport){
 	global $dbHandler;
 	$db = $dbHandler->openConnection();
  $sql = "DELETE FROM ad_poste WHERE type_etat=".$type_rapport;

  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }
 
 /**
 * Fonction: supprime les comptes d'un rapport
 *
 *
 */
function deleteCompteRapport($type_rapport){
	global $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "DELETE FROM ad_poste_compte  WHERE code in ( select code From ad_poste where code_rapport='".$type_rapport."');";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
 /**
 * Fonction: supprime les postes d'un rapport
 *
 *
 */
function deletePostesRapport($code_rapport){
	global $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "DELETE FROM ad_poste  WHERE code_rapport ='".$code_rapport."';";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
/**
 * Fonction: supprime les postes d'un rapport
 *
 *
 */
function deletePostesCompteRapport($code_rapport){
	global $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "DELETE FROM  ad_poste_compte   WHERE code  in ( select code FROM ad_poste  WHERE code_rapport ='".$code_rapport."');";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__));
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
 
 /**
 * Fonction : insertion de rapport jasper
 * param $DATA array tableau des données à inserer et les chemins des fichiers
 * return errObj object
 */
 function insertionJasperRapport($DATA){
	global $dbHandler;

	$rep=isJasperCodeRapport($DATA["code_rapport"]);
	if($rep) return  new ErrorObj(ERR_DB_SQL,array(sprintf(_("Le code du rapport  %s existe dèjà"),$DATA["code_rapport"])));

	$dataReportsFiles = $DATA['FILES'];
	unset($DATA['FILES']);
	$rep = saveReportFiles($DATA["code_rapport"], $dataReportsFiles, true);
	if($rep['error']!="") {
		return new ErrorObj(ERR_GENERIQUE,array(sprintf(_("%s"),$rep['error'])));
	}
	$DATA['nom_fichier'] = $rep['filereportmaster'];

	$db = $dbHandler->openConnection();
	$sql = buildInsertQuery ("ad_jasper_rapport",  $DATA);
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
/**
 * Sauvegarde les fichiers des rapports
 * @param	dataReportsFiles array  tableau contenant les fichiers du rapports
 * @param $code_rapport  String
 * @param isCreateDir boolean
 */
function saveReportFiles($code_rapport, $DataReportsFiles,$isCreateDir) {
	$error = "";
	$fileReportMaster = null;
	$arrayResponse =array();
	$mainPathsReport = getPathJasperRapports();
	$pathsReport = $mainPathsReport.DIRECTORY_SEPARATOR.$code_rapport;
	if($isCreateDir) {
		mkdir($pathsReport,intval('0777',8));
		chmod($pathsReport,intval('0777',8));
	}
	
	if(!is_dir($pathsReport)) {
		$error .=sprintf(_("Le dossier %s n'existe pas"),$pathsReport); 
	} 
	if(is_writeable($pathsReport)) {
		$error .=sprintf(_("Impossible d'écrire dans le dossier %s ",$pathsReport));
	}
	foreach ($DataReportsFiles as  $dataFiles) {
		$pathfilename =$pathsReport.DIRECTORY_SEPARATOR.$dataFiles['NAME'];
		if (file_exists($pathfilename) ) {
			unlink($pathfilename);
		}
		if($dataFiles['TYPE'] == 'MASTER') {
			$fileReportMaster = $pathfilename;
		}
		$rep = move_uploaded_file($dataFiles['FILE'], $pathfilename);
		chmod($pathfilename, intval('0755',8));
		if(!$rep) {
			$msg=_("erreur impossible de deplacer fichier ".$value['tmp_name']."uploadé .");
			$error .= $msg."<br>";
		}
	}
	$arrayResponse['filereportmaster']=$fileReportMaster;
	$arrayResponse['error'] = $error;
	return $arrayResponse;
}
function clearDir($dossier) {
	$ouverture=@opendir($dossier);
	if (!$ouverture) return;
	while($fichier=readdir($ouverture)) {
		if ($fichier == '.' || $fichier == '..') continue;
			if (is_dir($dossier."/".$fichier)) {
				$r=clearDir($dossier."/".$fichier);
				if (!$r) return false;
			}
			else {
				$r=@unlink($dossier."/".$fichier);
				if (!$r) return false;
			}
	}
closedir($ouverture);
$r=@rmdir($dossier);
if (!$r) return false;
	return true;
}
/**
 * Renvoie le dossier de sauvergarde de rapports jasper
 */
function getPathJasperRapports() {
	global $lib_path;
	return $lib_path."/rapports/jasper";
}

 /**
 * Liste des rapports jasper
 * @return array Liste des rapports jaspers array (code_rapport=>libel)
 *
 */
function getJasperRapportsCodeByLibel($code_rapport =NULL) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  if (!is_null($code_rapport)) $condition = "AND code_rapport='$code_rapport' ";
  $sql .= "SELECT code_rapport,libel  FROM ad_jasper_rapport WHERE id_ag=$global_id_agence ";
  $sql .=$condition;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $rapports = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
     $rapports[$row['code_rapport']]= $row['libel'];

  return  $rapports;

}

/**
 * Liste des rapports jasper
 * @param array $fields_array tableau contenant les nom des colonnes à selectionner
 * @return array Liste des rapports jaspers
 *
 */
function getJasperRapports($fields_array=NULL,$where ) {
	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = buildSelectQuery ("ad_jasper_rapport",$where,  $fields_array);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);

  $rapports = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
     $rapports[$row['code_rapport']]= $row;

  return new ErrorObj(NO_ERR,$rapports);
}

 /**
 * Fonction : insertion de parametre des rapports jaspers
 * param $DATA array tableau des données à inserer
 * return errObj object
 */
 function insertionJasperParam($DATA){
 	global $dbHandler;
  $rep=isJasperCodeParam($DATA["code_param"]);
  if($rep) return  new ErrorObj(ERR_DB_SQL,array(sprintf(_("Le code paramètre  %s existe dèjà"),$DATA["code_param"])));

  $db = $dbHandler->openConnection();
  $sql = buildInsertQuery ("ad_jasper_param",  $DATA);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }


 /**
 * Fonction : verifie si le code du rapport existe deja
 * param $code_rapport integer code du rapport à verifier
 * return errObj object
 */
 function isJasperCodeRapport($code_rapport){
 	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql ="select  count(code_rapport) FROM ad_jasper_rapport WHERE code_rapport='$code_rapport'";
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	$dbHandler->closeConnection(true);
  $row = $result->fetchrow();
  $count = $row[0];
  if ($count > 0 ) return  true;
  else return false;
 }
 /*
 * Fonction : verifie si le code du paramètre existe
 * param $code_param integer code du rapport à verifier
 * return errObj object
 */
 function isJasperCodeParam($code_param){
 	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql ="select  count(code_param) FROM ad_jasper_param WHERE code_param='$code_param'";
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	$dbHandler->closeConnection(true);
  $row = $result->fetchrow();
  $count = $row[0];
  if ($count > 0 ) return  true;
  else return false;
 }

 /*
 * Fonction : verifie si le code du paramètre  est associé au rapport
 * param $code_rapport integer code du rapport à verifier
 * return errObj object
 */
 function isJasperRapportCodeParam($code_rapport ,$code_param){
 	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql ="select  count(code_param) FROM ad_jasper_rapport_param WHERE code_rapport='$code_rapport' AND code_param='$code_param'";
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	$dbHandler->closeConnection(true);
  $row = $result->fetchrow();
  $count = $row[0];
  if ($count > 0 ) return  true;
  else return false;
 }
 /**
 * Fonction : maj du rapport jasper
 * param $DATA array tableau des données à mettre à jour
 * return errObj object
 */
 function updateJasperRapport($DATA,$where){
	global $dbHandler;   
	$dataReportsFiles = $DATA['FILES'];
	unset($DATA['FILES']);
	$rep = saveReportFiles($DATA["code_rapport"], $dataReportsFiles,FALSE);
	if($rep['error']!="") {
		return new ErrorObj(ERR_GENERIQUE,array(sprintf(_("Rapport %s"),$rep['error'])));
	}

    if(isset($rep['filereportmaster']) && $rep['filereportmaster'] !== NULL) {
      $DATA['nom_fichier'] = $rep['filereportmaster'];
    }

	$db = $dbHandler->openConnection();
	$sql = buildUpdateQuery ("ad_jasper_rapport",  $DATA,$where);
	$result = $db->query($sql);
	if (DB::isError($result)) {
	$dbHandler->closeConnection(false);
	return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}
  /**
 * Fonction : suppression du rapport jasper
 * param $where array tableau des la condition du rapport à supprimer
 * return errObj object
 */
 function deleteJasperRapport($code_rapport){
 	global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();

  //verifier s'iln 'ya pas des paramètre qui sont liés à ce rapport
  //condition de suppression
  $where=array();
	$where["code_rapport"]=$code_rapport;
	$where["id_ag"]=$global_id_agence;

  //verifier s'iln 'ya pas des paramètre qui sont liés à ce rapport
  $param=getJasperParamsRapports($code_rapport );
  if ($param->errCode !=NO_ERR ) {
  	return $param;
  }
  $param=$param->param;
  if( !empty($param) ) {
   	$rep=deleteJasperParamRapport($where);
  }
 	$sql = buildDeleteQuery ("ad_jasper_rapport",$where);
  $result = $db->query($sql);
	if (DB::isError($result)) {
	  $dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array(_("Fichier")=>__FILE__,_("ligne")=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }


 /**
 * Liste des parammetres jasper
 * @return array Liste des rapports jaspers array (code_param=>libel)
 *
 */
function getJasperParamCodeByLibel() {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT code_param,libel  FROM ad_jasper_param WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $params = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
     $params[$row['code_param']]= $row['libel'];

  return  $params;

}

/**
 * Liste des paramètres jasper
 * @param array $fields_array tableau contenant les nom des colonnes à selectionner
 * @return array Liste des rapports jaspers
 *
 */
function getJasperparams($fields_array=NULL,$where ) {
	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = buildSelectQuery ("ad_jasper_param",$where,  $fields_array);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);

  $rapports = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
     $rapports[$row['code_param']]= $row;

  return new ErrorObj(NO_ERR,$rapports);
}


/**
 * Fonction : maj du paramètre jasper
 * param $DATA array tableau des données à mettre à jour
 * return errObj object
 */
 function updateJasperParam($DATA,$where){
 	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = buildUpdateQuery ("ad_jasper_param",  $DATA,$where);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	    return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }

 /**
 * Fonction : suppression du paramètre jasper
 * param $where array tableau des la condition du paramètre à supprimer
 * return errObj object
 */
 function deleteJasperParam($where){
 	global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = buildDeleteQuery ("ad_jasper_param",$where);

  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }

 /**
 * Fonction : associer un  parametre à un rapport jaspers
 * param $DATA array tableau des données à inserer
 * return errObj object
 */
 function insertionJasperParamRapport($DATA){
 	global $dbHandler;

  $rep=isJasperRapportCodeParam($DATA["code_rapport"],$DATA["code_param"]);
  if($rep) return  new ErrorObj(ERR_DB_SQL,array(sprintf(_("Le code paramètre  %s est dèjà associé au rapport %s"),$DATA["code_param"],$DATA["code_rapport"])));

  $db = $dbHandler->openConnection();
  $sql = buildInsertQuery ("ad_jasper_rapport_param",  $DATA);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }



 /**
 * Fonction : dissocier un  parametre à un rapport jaspers
 * @param $where array tableau contenant les conditions des données à supprimer.
 * return errObj object
 */
 function deleteJasperParamRapport($where){
 	global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = buildDeleteQuery  ("ad_jasper_rapport_param",  $where);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
 }

 /**
 * Fonction : liste des parametre et des rapports
 * @param $where array tableau contenant les conditions des données à selectionner.
 * return errObj object
 */
 function getJasperParamRapport($where){
 	global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = buildSelectQuery   ("ad_jasper_rapport_param",  $where);
  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}

  $dbHandler->closeConnection(true);

  $rapports = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
     $rapports[$row['code_rapport']]= $row;

  return new ErrorObj(NO_ERR,$rapports);
 }
 /**
 * Liste des paramètre d'unrapport jasper
 * @param text $code_rapport code du rapport
 * @return array Liste des rapports jaspers et leurs paramètres
 *
 */
function getJasperParamsRapports($code_rapport ) {
	global $dbHandler;

  $db = $dbHandler->openConnection();
  $where["a.code_param"]="b.code_param";
  $where["code_rapport"]=$code_rapport;
  $fields_array=array("b.*");

  $sql = "SELECT b.* FROM ad_jasper_rapport_param a,ad_jasper_param b WHERE  a.code_param = b.code_param AND code_rapport = '$code_rapport';";

  $result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
	  return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
	}
  $dbHandler->closeConnection(true);

  $rapports = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
     $rapports[$row['code_param']]= $row;

  return new ErrorObj(NO_ERR,$rapports);
}
/**utilisée par les rapport de la BNR
 * permet de faire le contrôle sur le fichier de paramètrage des rapport de comptabilité
 * @param fichier	$fichier chemin du fichier à charger
 * @param integer	$type_etat type de rapport
 */
function parse_format_bnr($fichier,$type_etat) {

	global $global_id_agence;



	if (!file_exists($fichier)) {
		return new ErrorObj(ERR_FICHIER_DONNEES);
	}

	$handle = fopen($fichier, 'r');

	$count = 0;
	$num_complet_cpte = "";
	$tab_compte=getComptesComptables();
	$class_compta = getClassesComptables();
	$poste = getListePoste($type_etat);
	$poste_compte =array();
	$poste_compte_prov =  array();
	$DATA=array();
	while (($data = fgetcsv($handle, 200, ';')) != false) {

		if($count!=0) {

			if (isset($poste[$data[0]] )) {

				//verifier si les comptes comptables associés existe
				if($data[2]!='') {
					$comptes=split('[/,;]', $data[2]);
				}else {
					$comptes=NULL;
				}
				foreach ($comptes as $key=>$valeur) {
					if(trim($valeur)!='' && $valeur!=null ) {
						$chaine=$valeur;
						$pos=strpos($valeur,'+');
						$signe="+";
						if ($pos !== false ) {
							$chaine=substr($valeur,$pos+1);
							$signe="+";
						} else {
							$pos=strpos($valeur,'-');

							if ($pos !== false ) {
								$chaine=substr($valeur,$pos+1);
								$signe='-';
							}
						}
						$valeur=trim($chaine);
						if( !( isset($tab_compte[$valeur]) || isset($class_compta[$valeur -1]) ) ) {
							fclose($handle);
							return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count+1,"compte"=>$valeur));
						} else {
							if($data[4]!='' && $data[4]!= null ) {
								$poste_compte[$data[0]][]=array($valeur,$signe,$data[4]);
							} else {
								$poste_compte[$data[0]][]=array($valeur,$signe);
							}
						}
					}
				}
				//verifier si les comptes comptables associés existe
				if($data[3]!='') {
					$comptes=split('[/,;]', $data[3]);
				}else {
					$comptes=NULL;
				}
				foreach ($comptes as $key=>$valeur) {
				if(trim($valeur)!='' && $valeur!=null ) {
					$chaine=$valeur;
					$pos=strpos($valeur,'+');
					$signe="+";
					if ($pos !== false ) {
						$chaine=substr($valeur,$pos+1);
						$signe="+";
					} else {
						$pos=strpos($valeur,'-');

						if ($pos !== false ) {
							$chaine=substr($valeur,$pos+1);
							$signe='-';
						}
					}
					$valeur=trim($chaine);
					if(!isset($tab_compte[$valeur])) {
						fclose($handle);
						return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count+1,"compte"=>$valeur));
					} else {
						if($data[4]!='' && $data[4]!= null ) {
						$poste_compte_prov[$data[0]][]=array($valeur,$signe,$data[4]);
						} else {
							$poste_compte_prov[$data[0]][]=array($valeur,$signe);
						}
					}
				}
				}
			}
		}$count++;
	}
	$DATA['compte']=$poste_compte;
	$DATA['compte_prov']=$poste_compte_prov;
	fclose($handle);


	return new ErrorObj(NO_ERR, array('data' => $DATA));
}

/**
 * permet de faire le contrôle sur le fichier de paramètrage des rapport de comptabilité
 * @param fichier	$fichier chemin du fichier à charger
 * @param integer	$type_etat type de rapport
 */
function parse_format_poste($fichier,$code_rapport) {

	global $global_id_agence;



	if (!file_exists($fichier)) {
		return new ErrorObj(ERR_FICHIER_DONNEES);
	}

	$handle = fopen($fichier, 'r');

	$count = 0;
	$DATA=array();
	while (($data = fgetcsv($handle, 200, ';')) != false) {

		if($count!=0) {
			if(trim($data[2]) == '') $data[2] = NULL;
			if(trim($data[3]) == '') $data[3] = NULL;
			if(trim($data[4]) == '') $data[4] = NULL;
			$DATA['poste'][$count]=array("code"=>$data[0],"libel"=>$data[1],"compartiment"=>$data[2],
							"id_poste_centralise"=>$data[3],"niveau"=>$data[4],'code_rapport'=>$code_rapport);
				
		}$count++;
	}

	fclose($handle);

	return new ErrorObj(NO_ERR, array('data' => $DATA));
}

/**
 * Liste des postes
 * @return array Liste des postes
 */
function getListePoste($code_rapport) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();

	$sql = "SELECT * FROM ad_poste WHERE  code_rapport = '$code_rapport' order by code";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}


	$dbHandler->closeConnection(true);

	$poste = array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
	$poste[$row['code']]= $row;

	return $poste;

}
function insertionsPostes($DATA) {
	global $dbHandler;
	$tab_poste=$DATA['poste'];
	foreach ($tab_poste as $code => $poste){
		$MyErr=insertionPoste($poste);
		if ($MyErr->errCode != NO_ERR) {
			$dbHandler->closeConnection(false);
			return $MyErr;
				
		}
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}

/**
 *insertion des postes ds ad_poste
 *@param array $DATA données à inserer
 *@return ErrorObj
 *
 */
function insertionComptesPosteBNR($DATA) {
	global $dbHandler;
	$tab_compte=$DATA['compte'];
	$tab_compte_prov=$DATA['compte_prov'];
	// insertion ds ad_poste_comptes
	

	foreach ($tab_compte as $code => $comptes){
		$tab_compte_poste['code']=$code;
		foreach ($comptes as $compte){
			$tab_compte_poste['num_cpte_comptable']=$compte[0];
			$tab_compte_poste['signe']=$compte[1];
			if(isset($compte[2])) {
				$tab_compte_poste['operation']=$compte[2];
			}
			//$tab_compte_poste['is_cpte_provision']=false;
			$MyErr=insertionPosteCompte($tab_compte_poste);
			if ($MyErr->errCode != NO_ERR) {
				$dbHandler->closeConnection(false);
				return $MyErr;
			}
		}
	}
	//insertion des comptes des provisions
	foreach ($tab_compte_prov as  $code => $comptes){
		$tab_compte_poste_prov['code']=$code;
		foreach ($comptes as $compte){
			$tab_compte_poste_prov['num_cpte_comptable']=$compte[0];
			$tab_compte_poste_prov['signe']=$compte[1];
			$tab_compte_poste_prov['is_cpte_provision']=true;
			$MyErr=insertionPosteCompte($tab_compte_poste_prov);
			if ($MyErr->errCode != NO_ERR) {
				$dbHandler->closeConnection(false);
				return $MyErr;
			}
		}
	}




	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}


/**
 *insertion des postes ds ad_poste
 *@param array $DATA données à inserer
 *@return ErrorObj
 *
 */
function insertionComptesPosteBNRCompteREsulat($DATA) {
	global $dbHandler;
	$tab_compte=$DATA['compte'];
	$tab_compte_prov=$DATA['compte_prov'];
	$tab_compte_parent = array();
	$tab_poste = array();
	$db = $dbHandler->openConnection();
	foreach ( $DATA as $poste_comptes ) {
		$tab_compte[$poste_comptes['donnee']['code']]= $poste_comptes['compte'];
		$poste_comptes['donnee']['type_etat'] = 2;
		$tab_poste[$poste_comptes['donnee']['code']]= $poste_comptes['donnee'];
	}
	// insertion ds ad_poste_comptes
	//	if($code == 'M.IS.NFIBP') {
	unset($tab_compte['M.IS.NFIBP']);
	foreach ($tab_compte['M.IS.FR'] as $compte){
		$tab_compte_parent['M.IS.NFIBP'][] = $compte;
	}
	foreach ($tab_compte['M.IS.FE'] as $compte){
		if($compte[1] == '+') {$compte[1] = '-';}
		elseif($compte[1] == '-')  $compte[1] = '+';
		$tab_compte_parent['M.IS.NFIBP'][] = array($compte[0],$compte[1]);
	}

	unset($tab_compte['M.IS.NPE']);
	foreach ($tab_compte['M.IS.LL'] as $compte){
		$tab_compte_parent['M.IS.NPE'][] = $compte;
	}
	foreach ($tab_compte['M.IS.RL'] as $compte){
		if($compte[1] == '+') {$compte[1] = '-';}
		elseif($compte[1] == '-')  $compte[1] = '+';
		$tab_compte_parent['M.IS.NPE'][] = array($compte[0],$compte[1]);
	}

	unset($tab_compte['M.IS.FRAP']);
	foreach ($tab_compte_parent['M.IS.NFIBP'] as $compte){

		$tab_compte_parent['M.IS.FRAP'][] = array($compte[0],$compte[1]);
	}
	foreach ($tab_compte_parent['M.IS.NPE'] as $compte){
		if($compte[1] == '+') {$compte[1] = '-';}
		elseif($compte[1] == '-')  $compte[1] = '+';
		$tab_compte_parent['M.IS.FRAP'][] = array($compte[0],$compte[1]);
	}


	unset($tab_compte['M.IS.NOI']);

	foreach ($tab_compte_parent['M.IS.FRAP'] as $compte){

		$tab_compte_parent['M.IS.NOI'][] = array($compte[0],$compte[1]);
	}
	foreach ($tab_compte['M.IS.OE'] as $compte){
		if($compte[1] == '+') {$compte[1] = '-';}
		elseif($compte[1] == '-')  $compte[1] = '+';
		$tab_compte_parent['M.IS.NOI'][] = array($compte[0],$compte[1]);
	}

	unset($tab_compte['M.IS.NNI']);
	foreach ($tab_compte['M.IS.NNOR.1'] as $compte){
		$tab_compte_parent['M.IS.NNI'][] = $compte;
	}
	foreach ($tab_compte['M.IS.NNOE.1'] as $compte){
		if($compte[1] == '+') {$compte[1] = '-';}
		elseif($compte[1] == '-')  $compte[1] = '+';
		$tab_compte_parent['M.IS.NNI'][] = array($compte[0],$compte[1]);
	}
	unset($tab_compte['M.IS.NIBTD']);
	foreach ($tab_compte_parent['M.IS.NNI'] as $compte){
		$tab_compte_parent['M.IS.NIBTD'][] = array($compte[0],$compte[1]);
	}
	foreach ($tab_compte_parent['M.IS.NOI'] as $compte){
		$tab_compte_parent['M.IS.NIBTD'][] = array($compte[0],$compte[1]);
	}
	
	unset($tab_compte['M.IS.NIATBD']);
	foreach ($tab_compte_parent['M.IS.NIBTD'] as $compte){
		$tab_compte_parent['M.IS.NIATBD'][] = array($compte[0],$compte[1]);
	}
	foreach ($tab_compte['M.IS.IT'] as $compte){
		if($compte[1] == '+') {$compte[1] = '-';}
		elseif($compte[1] == '-')  $compte[1] = '+';
		$tab_compte_parent['M.IS.NIATBD'][] =array($compte[0],$compte[1]);
	}

	unset($tab_compte['M.IS.NIATD']);
	foreach ($tab_compte_parent['M.IS.NIATBD'] as $compte){
		$tab_compte_parent['M.IS.NIATD'][] = array($compte[0],$compte[1]);
	}
	foreach ($tab_compte['M.IS.D'] as $compte){
		$tab_compte_parent['M.IS.NIATD'][] = $compte;
	}

	
	foreach ($tab_poste as $code => $poste){
		//$tab_compte_poste['is_cpte_provision']=false;
		$MyErr=insertionPoste($poste);
		if ($MyErr->errCode != NO_ERR) {
			$dbHandler->closeConnection(false);
			return $MyErr;
		}
		$sql = "select max(id_poste) from 	ad_poste ";
			$result = $db->query($sql);
			if (DB::isError($result)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__,__LINE__,__FUNCTION__);
			}
			$tmprow = $result->fetchrow();
			$id_poste = $tmprow[0];
			$tab_poste[$code]['id_poste'] = $id_poste;
	}
	reset($tab_poste);
	foreach ($tab_compte as $code => $comptes){
		foreach ($comptes as $compte){
			$tab_compte_poste_parent = Array();
			$tab_compte_poste_parent['id_poste'] = $tab_poste[$code]['id_poste'];
			$tab_compte_poste_parent['code']=$code;
			$tab_compte_poste_parent['num_cpte_comptable']=$compte[0];
			$tab_compte_poste_parent['signe']=$compte[1];
			$MyErr=insertionPosteCompte($tab_compte_poste_parent);
			if ($MyErr->errCode != NO_ERR) {
				$dbHandler->closeConnection(false);
				return $MyErr;
			}
		}
	}
	//insertion des comptes des parent
	foreach ($tab_compte_parent as  $code => $comptes){

		foreach ($comptes as $compte){
			$tab_compte_poste_parent = Array();
			$tab_compte_poste_parent['id_poste'] = $tab_poste[$code]['id_poste'];
			$tab_compte_poste_parent['code']=$code;
			$tab_compte_poste_parent['num_cpte_comptable']=$compte[0];
			$tab_compte_poste_parent['signe']=$compte[1];
			$MyErr=insertionPosteCompte($tab_compte_poste_parent);
			if ($MyErr->errCode != NO_ERR) {
				$dbHandler->closeConnection(false);
				return $MyErr;
			}
		}
	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}

function parse_format_etat_compta_poste($fichier,$type_etat) {
	global $global_id_agence;
	if (!file_exists($fichier)) {
		return new ErrorObj(ERR_FICHIER_DONNEES);
	}
	$handle = fopen($fichier, 'r');
	$count = 0;
	$num_complet_cpte = "";
	$tab_compte=getComptesComptables();
	//$class_compta = getClassesComptables();
	//$poste = getListePoste($type_etat);
	$poste_compte =array();
	$poste_compte_prov =  array();

	$DATA=array();
	while (($data = fgetcsv($handle, 200, ';')) != false) {

		if($count!=0) {
			//verifier si les comptes comptables associés existe
			if($data[2]!='') {
				$comptes=split('[,]', $data[2]);
			}else {
				$comptes=NULL;
			}
			foreach ($comptes as $key=>$valeur) {
				if(trim($valeur)!='' && $valeur!=null ) {
					$chaine=$valeur;
					$pos=strpos($valeur,'+');
					$signe="+";
					if ($pos !== false ) {
						$chaine=substr($valeur,$pos+1);
						$signe="+";
					} else {
						$pos=strpos($valeur,'-');

						if ($pos !== false ) {
							$chaine=substr($valeur,$pos+1);
							$signe='-';
						}
					}
					$valeur=trim($chaine);
					if( !isset($tab_compte[$valeur]) ) {
						fclose($handle);
						return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count+1,"compte"=>$valeur));
					} else {
						if($data[5]!='' && $data[5]!= null ) {
							$poste_compte[]=array($valeur,$signe,$data[5]);
						} else {
							$poste_compte[]=array($valeur,$signe);
						}
					}
				}
			}
			//verifier si les comptes comptables associés existe
			if($data[3]!='') {
				$comptes=split('[,]', $data[3]);
			}else {
				$comptes=NULL;
			}
			foreach ($comptes as $key=>$valeur) {
				if(trim($valeur)!='' && $valeur!=null ) {
					$chaine=$valeur;
					$pos=strpos($valeur,'+');
					$signe="+";
					if ($pos !== false ) {
						$chaine=substr($valeur,$pos+1);
						$signe="+";
					} else {
						$pos=strpos($valeur,'-');

						if ($pos !== false ) {
							$chaine=substr($valeur,$pos+1);
							$signe='-';
						}
					}
					$valeur=trim($chaine);
					if(!isset($tab_compte[$valeur])) {
						fclose($handle);
						return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count+1,"compte"=>$valeur));
					} else {
						if($data[5]!='' && $data[5]!= null ) {
							$poste_compte_prov[]=array($valeur,$signe,$data[5]);
						} else {
							$poste_compte_prov[]=array($valeur,$signe);
						}
					}
				}
			}
			$DATA[$count]['donnee']=array("code"=>$data[0],"libel"=>$data[1],"compartiment"=>$data[4],"code_rapport"=>$type_etat);
			$DATA[$count]['compte']=$poste_compte;
			$DATA[$count]['compte_prov']=$poste_compte_prov;
			$poste_compte = NULL;
			$poste_compte_prov = null;
		}$count++;
	}
	fclose($handle);
	return new ErrorObj(NO_ERR, array('data' => $DATA));
}
/**
 * Fonction qui renvoie les champs extras des tables
 * @param text $table_name
 * @return array Tableau des champs
 */
function getChampsExtras($table_name) {
  global $dbHandler,$global_id_agence,$global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id, traduction(libel, '$global_langue_systeme_dft') as libel, id_ag, table_name, type,isreq FROM champs_extras_table where table_name = '$table_name' AND id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $champsExtras = array ();
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC))
    array_push($champsExtras, $tmprow);
  $dbHandler->closeConnection(true);
  return $champsExtras;
}

/**
 * Fonction qui renvoie les le nombre de caractères pour la validation de la piece d'identité
 * @return array Tableau des piece d'identité et leur nombre de caractères
 */

function getListPieceIdentLength() {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT id, char_length FROM adsys_type_piece_identite WHERE id_ag=$global_id_agence ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }


    $dbHandler->closeConnection(true);

    $TP = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        $TP[$row['id']]= $row['char_length'];

    return $TP;
}

function getParamAbonnement($cle) {
  global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = "SELECT cle, valeur, lib_texte1 FROM adsys_param_abonnement WHERE cle = '$cle'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $ligne = $result->fetchrow();

  $dbHandler->closeConnection(true);
  return $ligne;
}

/**
 * Fonction qui renvoie les types operations comptables Mouvement SMS
 * @param Prelev_frais -> boolean / $is_deleted -> boolean
 * @return array Tableau des champs
 * Ticket MB-153
 */
function getListesTypeOperationSMS($id = null,$prelev_frais = null, $is_deleted =null) {
  global $dbHandler,$global_id_agence,$global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

  $sql = "select * from adsys_param_mouvement  where id_ag = numagc() ";
  if ($id != null){
    $sql .= " and id = $id";
  }

  if ($prelev_frais != null){
    $sql .= " and preleve_frais = '$prelev_frais'";
  }
  if ($is_deleted != null){
    $sql .= " and deleted = '$is_deleted'";
  }
  $sql .= "order by type_opt";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $listeOpt = array ();
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC))
    array_push($listeOpt, $tmprow);
  $dbHandler->closeConnection(true);
  return $listeOpt;
}


/**
 * Fonction qui renvoie les opérations comptables dont il faut prélever le frais forfaitaire transactionnel SMS
 * @param null $prelev_frais
 * @return array
 */
function getListeTypeOptPourPreleveFraisSMS($prelev_frais = null) {
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "select type_opt from adsys_param_mouvement  where id_ag = numagc() and deleted = 'f' ";

    if ($prelev_frais != null){
        $sql .= " and preleve_frais = '$prelev_frais'";
    }


    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE_, _LINE_, _FUNCTION__);
    }
    $listeOpt = array ();
    while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC))
        array_push($listeOpt, $tmprow);
    $dbHandler->closeConnection(true);
    return $listeOpt;

}

/**
 * Liste des classes socio-economiques par agence
 * @return array Liste des pays
 */
function getListeClasseSocioEconomique() {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM adsys_classe_socio_economique_rwanda WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $PY = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $PY[$row['id']]= $row['classe'];

  return $PY;

}
/**
 * Liste des educations
 * @param int $id_ag identifiant de l'agence
 * @return array tableau contenant la liste des educations
 */
function getListeEducation($whereCond) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_education_rwanda where id_ag=$global_id_agence ";
  if (($whereCond == null) || ($whereCond == "")) {
    $sql .=	" ";
  } else {
    $sql .=	" AND $whereCond ";
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    ignalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id"]] = $row['code_education'];

  return $DATAS;
}
//Minimal  version of the function 'preleveFraisTransactionnelSMS'
//Retreiving the total amount perceived as 'SMS_FRAIS'
function getPreleveFraisSMS(&$array_comptable){
    $sms_list_operation = getListeTypeOptPourPreleveFraisSMS(true);
    $type_opt_list = array();
    $montant_sms = 0;
    $tarif = getTarificationDatas('SMS_FRAIS');

    foreach($sms_list_operation as $type_opt)
        $type_opt_list[] = $type_opt["type_opt"];

    if(is_array($tarif) && count($tarif) > 0) {
        foreach ($array_comptable as $index => $value) {
            if (!empty($value['cpte_interne_cli']) && in_array($value['type_operation'], $type_opt_list)) {
                $client = checkClientAbonnement($value['cpte_interne_cli']);

                if (!empty($client)) {
                    $cpt_epargne = get_comptes_epargne($client["id_client"]);

                    foreach ($cpt_epargne as $id_cpte => $dataset) {
                        if ($value['cpte_interne_cli'] == $id_cpte) {
                           $montant_sms += $tarif['valeur'];
                        }
                    }
                }
            }
        }
    }
    return $montant_sms;
}
?>