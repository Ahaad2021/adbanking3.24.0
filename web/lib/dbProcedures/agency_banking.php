<?php

require_once ('lib/misc/tableSys.php');
require_once "lib/dbProcedures/parametrage.php";
require_once "lib/dbProcedures/agence.php";
require_once "lib/dbProcedures/compte.php";
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Client.php';

function ajout_utilisateur_agent($DATA) {
  /* Paramètre entrant : infos de l'utilisateur & login à créer
     Paramètre sortant : bolléen */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA = array_make_pgcompatible($DATA);
  $DATA["pwd"]=strtolower("pwd");
  //Insertion dans la table des utilisateurs
  $sql = "INSERT INTO ad_uti ";
  $sql .= "(id_utilis, nom, prenom, date_naiss, lieu_naiss, sexe, type_piece_id, num_piece_id, adresse, tel, date_crea, utilis_crea, ";
  $sql .= "date_modif, utilis_modif, id_ag, statut, is_gestionnaire,is_agent_ag) ";
  $sql .= "VALUES('".$DATA['id_utilis']."', '".$DATA['nom']."', '".$DATA['prenom']."', '".$DATA['date_naiss']."', '".$DATA['lieu_naiss']."', '".$DATA['sexe']."', '".$DATA['type_piece_id']."', '".$DATA['num_piece_id']."', '".$DATA['adresse']."', '".$DATA['tel']."', '".$DATA['date_crea']."', '".$DATA['utilis_crea']."', '".$DATA['date_modif']."', '".$DATA['utilis_modif']."', $global_id_agence, '".$DATA['statut']."','".$DATA['is_gestionnaire']."','".$DATA['is_agent_ag']."')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if (isset($DATA["login"])) { // Si on désire associer un login à l'utilisateur
    if ($DATA['guichet']) {//Si présence d'un guichet
      //Récupération du prochain ID de guichet
      $sql = "SELECT nextval('ad_gui_id_gui_seq')";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      } else if ($result->numrows() != 1) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour DB inattendu"
      }
      $row = $result->fetchrow();
      $id_gui = $row[0];
      $id_agence=getNumAgence();
      //Insertion du guichet
      $sql = "INSERT INTO ad_gui ";
      $sql .= "(id_gui,id_ag, libel_gui, date_crea, utilis_crea, encaisse, date_enc, date_modif, utilis_modif, cpte_cpta_gui) ";
      $sql .= "VALUES($id_gui,$global_id_agence,'".$DATA['libelGuichet']."','".$DATA['date_crea']."','".$DATA['utilis_crea']."', 0, '";
      $sql .= $DATA['date_crea']."', '".$DATA['date_modif']."', '".$DATA['utilis_modif']."','".$DATA['cptecpta_gui']."')";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
    }

    //Insertion du login
    $sql = "INSERT INTO ad_log(login, pwd, profil, guichet, id_utilisateur, have_left_frame, id_ag) VALUES('".$DATA['login']."', md5('".$DATA['pwd']."'), '".$DATA['profil']."', '$id_gui', '".$DATA['id_utilis']."', 't', $global_id_agence)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }
  global $global_nom_login;
  ajout_historique(270,NULL, $DATA['id_utilis'], $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

/**
 * Fonction qui renvoie tous les utilisateurs agents
 * @return :array Tableau qui contient la liste de tous les profils
 * Ticket #365
 **/
function getAllUtiAgent() {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $agents=array();
  $sql = " select * from ad_uti where is_agent_ag = 't' order by nom ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $agents[$row['id_utilis']] = $row['nom']." ".$row['prenom'];

  $db = $dbHandler->closeConnection(true);
  return $agents;
}

/**
 * Fonction qui renvoie tous les profils.
 * @return :array Tableau qui contient la liste de tous les profils
 * Ticket AGB
 **/
function getAllProfilAgent() {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $profils=array();
  $sql = " select * from adsys_profils where is_profil_agent ='t' order by libel ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $profils[$row['id']] = $row;

  $db = $dbHandler->closeConnection(true);
  return $profils;
}

/**
 * Fonction qui renvoie tous les utilisateurs relativement a leur profils.
 * @profil :Prends en entré le paramètre id_profil qui par défaut est null
 * @return :array Tableau qui contient la liste de tous les utilisateurs
 **/
function getUtilisateursInfoAgent($profil=null) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $profils=array();
  $utilisateur = array();
  $sql = " select
          profils.libel as profil,
          uti.nom as nom,
          uti.prenom as prenom,
          case when uti.is_gestionnaire = 'true' then 'Oui' else 'Non' end as is_gestionnaire,
          l.login as login,
          uti.date_crea ::date,
          case when uti.statut = 1 then 'Actif'  when uti.statut = 2 then 'Inactif' end as statut
          from ad_uti uti
          inner join ad_log l on uti.id_utilis = l.id_utilisateur and uti.id_ag = l.id_ag
          inner join adsys_profils profils on l.profil = profils.id
          where uti.is_agent_ag = 't' and uti.id_ag = $global_id_agence ";
  if (isset($profil)){
    $sql .= "and profils.id = $profil ";
  }
  $sql .= "order by profil,nom,prenom,login ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $utilisateur[$row['profil']."_".$row['login']] = $row;

  $db = $dbHandler->closeConnection(true);
  return $utilisateur;
}

function get_logins_and_utilisateurs_agent() {
  /*
  Renvoie la liste des logins existants et des utilisateurs associés
  */

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT login, id_utilisateur FROM ad_log g INNER JOIN ad_uti u on u.id_utilis = g.id_utilisateur where u.is_agent_ag = 't'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $i = 0;
  while ($row = $result->fetchrow()) {
    $retour[$i]['login'] = $row[0];
    $retour[$i]['id_utilisateur'] = $row[1];
    ++$i;
  }

  $db = $dbHandler->closeConnection(true);
  return $retour;
}


function ajout_login_agent($DATA) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA = array_make_pgcompatible($DATA);
  $DATA["pwd"]=strtolower($DATA["pwd"]);
  if ($DATA['guichet']) { //Si présence d'un guichet
    // Le compte comptable doit être renseigné
    if ( $DATA['cptecpta_gui']=='' || $DATA['cptecpta_gui']==0) {
      $dbHandler->closeConnection(false);
      return false;
    }

    // Vérifier l'unicité des comptes comptables des guichets
    $sql = "SELECT * FROM ad_gui WHERE id_ag = ".$global_id_agence." AND cpte_cpta_gui ='".$DATA['cptecpta_gui']."'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $nb = $result->numrows();
    if ($nb != 0) {
      $dbHandler->closeConnection(false);
      return false;
    }

    //Récupération du prochain ID de guichet
    $sql = "SELECT nextval('ad_gui_id_gui_seq')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    } else if ($result->numrows() != 1) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour DB inattendu"
    }
    $row = $result->fetchrow();
    $id_gui = $row[0];
    $id_agence=getNumAgence();
    //Insertion du guichet
    $sql = "INSERT INTO ad_gui ";
    $sql .= "(id_gui,id_ag, libel_gui, date_crea, utilis_crea, date_modif, utilis_modif, cpte_cpta_gui) ";
    $sql .= "VALUES($id_gui,$global_id_agence, '".$DATA['libelGuichet']."', '".$DATA['date']."', '".$DATA['utilis']."',";
    $sql .="'". $DATA['date']."', '".$DATA['utilis']."','".$DATA['cptecpta_gui']."')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  if ($id_gui == 0)
    $id_gui = "NULL";

  //Insertion du login
  if ($DATA['have_left_frame']) $val_left = 't';
  else $val_left = 'f';
  if ($DATA['billet_req']) $bil_req = 't';
  else $bil_req = 'f';

  if ($DATA['plafond_retrait'] == null){
    $DATA['plafond_retrait'] = 0;
  }
  if ($DATA['plafond_depot'] == null){
    $DATA['plafond_depot'] = 0;
  }

    $sql = "INSERT INTO ad_log(login, pwd, profil, guichet, id_utilisateur, have_left_frame, billet_req, langue, id_ag,cpte_flotte_agent,cpte_base_agent,cpte_comm_agent,plafond_retrait,plafond_depot,masquer_solde_client) VALUES('" . $DATA['login'] . "', md5('" . $DATA['pwd'] . "')," . $DATA['profil'] . ",$id_gui," . $DATA['id_utilisateur'] . ",'$val_left','$bil_req','" . $DATA['langue'] . "', $global_id_agence,'".$DATA['cpte_flotte_agent']."', '".$DATA['cpte_base_agent']."','".$DATA['cpte_comm_agent']."','".$DATA['plafond_retrait']."', '".$DATA['plafond_depot']."', '".$DATA['masquer_solde_client']."')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  global $global_nom_login;
  ajout_historique(292,NULL, $DATA['login'], $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

/**
 * Fonction qui renvoie tous les profils.
 * @return :array Tableau qui contient la liste de tous les profils
 * Ticket #365
 **/
function getLibelCompta($num_cpt_compta) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $libel=array();
  $sql = " select * from ad_cpt_comptable where num_cpte_comptable = '$num_cpt_compta' order by num_cpte_comptable ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $libel = $row['num_cpte_comptable']." ".$row['libel_cpte_comptable'];

  $db = $dbHandler->closeConnection(true);
  return $libel;
}


function getGuichetAgentFromLogin ($login) {
  // PS qui renvoie le guichet associé à un login donné
  // Renvoie -1 si le guichet n'existe pas
  //         NULL si pas de guchet associé à ce login
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT libel_gui FROM ad_gui, ad_log WHERE ad_gui.id_ag=$global_id_agence AND login='$login' AND guichet = id_gui";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le login $login est présent plusieurs fois dans la DB"
  }
  if ($result->numrows() == 0) {
    $db = $dbHandler->closeConnection(true);
    return -1;
  } else {
    $row = $result->fetchrow();
    $db = $dbHandler->closeConnection(true);
    $id_gui = $row[0];
    if ($id_gui == '')
      return NULL;
    else
      return $id_gui;
  }
}

function getnumcptecompletAgent($id_cpte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT num_complet_cpte from ad_cpt where id_cpte='$id_cpte' and id_ag=$global_id_agence";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  return $row['num_complet_cpte'];
  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
  $cptes_substitue["int"]["credit"] = $id_cpte;


}


function modif_login_agent($id_login, $DATA) {
  //Renvoie 0 si tout OK et -1 si login loggé
  global $dbHandler, $global_id_agence;
  $global_id_agence=getNumAgence();
  $db = $dbHandler->openConnection();
  $DATA = array_make_pgcompatible($DATA);

  $DATA["pwd"]=strtolower($DATA["pwd"]);
  $id_login=addslashes($id_login);
  //Vérifie si la personne n'est pas loggée
  $sql = "DELETE FROM ad_ses WHERE login = '$id_login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  //Met à jour le login
  if ($DATA['billet_req']) $bil_req = 't';
  else $bil_req = 'f';
  if ($DATA['pwd_non_expire']) $pwd_non_expire = 't';
  else $pwd_non_expire = 'f';

  if ($DATA['plafond_retrait'] == null){
    $DATA['plafond_retrait'] = 0;
  }
  if ($DATA['plafond_depot'] == null){
    $DATA['plafond_depot'] = 0;
  }


  $sql = "UPDATE ad_log SET login='".$DATA['login']."', billet_req = '$bil_req', pwd_non_expire  = '$pwd_non_expire', langue='".$DATA['langue']."' ,login_attempt=".$DATA['login_attempt']." ,cpte_flotte_agent ='".$DATA['cpte_flotte_agent']."'";
  if(isset($DATA['cpte_base_agent'])) {
    $sql .= ",cpte_base_agent =" . $DATA['cpte_base_agent'];
  }

  $sql .=",cpte_comm_agent =".$DATA['cpte_comm_agent']." ,plafond_retrait =".$DATA['plafond_retrait']." ,plafond_depot =".$DATA['plafond_depot'];

  if(isset($DATA['masquer_solde_client'])) {
    $sql .= " ,masquer_solde_client ='".$DATA['masquer_solde_client']."'  WHERE login='$id_login'";
  }


  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  //Met à jour le guichet
  if ($DATA['guichet']) {
    // Le compte comptable doit être renseigné
    if ( $DATA['cpte_flotte_agent']=='' || $DATA['cpte_flotte_agent']==="0") { //on force la compraison pour prendre les comptes 0.0, 0.0.1, etc..
      $dbHandler->closeConnection(false);
      return -1;
    }

    // Vérifier l'unicité des comptes comptables des guichets
    $sql = "SELECT * FROM ad_gui WHERE id_ag = ".$global_id_agence." AND cpte_cpta_gui ='".$DATA['cpte_flotte_agent']."' AND id_gui !=".$DATA['guichet'];
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $nb = $result->numrows();
    if ($nb != 0) {
      $dbHandler->closeConnection(false);
      return -1;
    }

    $sql = "UPDATE ad_gui SET libel_gui='".$DATA['libel_gui']."', date_modif='".$DATA['date_modif_gui']."', utilis_modif=".$DATA['utilis_modif_gui'].", cpte_cpta_gui ='".$DATA['cpte_flotte_agent']."' WHERE id_ag = ".$global_id_agence." AND id_gui=".$DATA['guichet'];

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  //Met à jour le mot de passe
  if ($DATA['pwd'] != "") {
    $sql = "UPDATE ad_log SET pwd= md5('".$DATA['pwd']."') WHERE login='".$DATA['login']."'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  global $global_nom_login;
  ajout_historique(290,NULL, $DATA['login'], $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return 0;
}

/**
 * Fonction qui enregistre les commissions
 * @return :array Tableau qui contient la liste de tous les profils
 * Ticket #365
 **/
function ajoutCommission($DATA,$nb_ligne) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

$i = 0;
for ($i = 0; $i <= $nb_ligne; $i++) {
  $DATA[$i]['date_creation'] = date('d-m-Y');
  $DATA[$i]['id_ag'] = $global_id_agence;
  if ($DATA[$i]['comm_tot_prc'] != null || $DATA[$i]['comm_tot_mnt'] != null) {
    $result = executeQuery($db, buildInsertQuery("ag_commission", $DATA[$i]));
  }
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    return $result;
  }
}
  $db = $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function getCommission($type_comm) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ag_commission where type_comm = $type_comm order by id_palier";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $dbHandler->closeConnection(true);

  $DATAS=array();
  $counter = 0;
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $counter++;
    $DATAS[$row["id_palier"]] = $row;
  }
  $DATAS['counter'] = $counter;
  $dbHandler->closeConnection(true);

  return $DATAS;

}
function modif_ligne_commission($id, $DATA, $delete_status, $insert_status)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  if($delete_status) {
      $sql = buildDeleteQuery("ag_commission", array("id" => $id));
      $result = $db->query($sql);
      if (DB:: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
      }
  }

  if($insert_status) {
      $result1 = executeQuery($db, buildInsertQuery("ag_commission", $DATA));
      if (DB::isError($result1)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage());
      }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function getCompteComptableAgency($num_cpte_comptable=null, $classe_compta = null)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence ";

  if ($num_cpte_comptable != null){
    $sql .= " AND num_cpte_comptable = '".$num_cpte_comptable."' ";
  }
  if ($classe_compta != null){
    $sql .= " AND classe_compta = $classe_compta";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $cptes = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $cptes[$row["num_cpte_comptable"]] = $row;
  }
  $dbHandler->closeConnection(true);

  return $cptes;

}

function getCommissionInstitution()
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ag_param_commission_institution ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getDatasLogin($login=null){
  global $dbHandler, $global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_log ";

  if ($login != null){
    $sql .= " where login= '$login'";
  }
  else {
  $sql .= " where login= '$global_nom_login'";
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getDataCpteEpargne($id_cpte){
  global $dbHandler, $global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_cpt where id_cpte = $id_cpte";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getDataUtilisateur($id_login=null){
  global $dbHandler, $global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_uti where id_utilis = $id_login";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getListeApprovisionnementTransfert($type_appro= null, $etat=null) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ag_approvisionnement_transfert WHERE id_ag = $global_id_agence ";

  if ($type_appro != null) {
    $sql .= " AND type_transaction In (".$type_appro.")" ;
  }
  if ($etat != null){
    $sql .= " AND etat_appro = $etat";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $dbHandler->closeConnection(true);

  $DATAS=array();
  $counter = 0;
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $counter++;
    $DATAS[$row['id']] = $row;
  }
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getApprovisionnementTransfertAgent($id){
  global $dbHandler, $global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ag_approvisionnement_transfert WHERE id_ag = $global_id_agence  AND id = $id";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function CheckCompteApprotrans($InfoCpte, $InfoProduit, $montant) {
  //vérification de l'état du compte : ouvert
  if ($InfoCpte["etat_cpte"] == 3){
    return new ErrorObj(ERR_CPTE_BLOQUE, $InfoCpte["id_cpte"]);
  }
  if ($InfoCpte["etat_cpte"] == 4){
    return new ErrorObj(ERR_CPTE_ATT_FERM, $InfoCpte["id_cpte"]);
  }
  if ($InfoCpte["etat_cpte"] == 7){
    return new ErrorObj(ERR_CPTE_BLOQUE_RETRAIT, $InfoCpte["id_cpte"]);
  }
  //vérifier possibilité retrait
  if ($InfoProduit["retrait_unique"] == 't'){
    return new ErrorObj(ERR_RETRAIT_UNIQUE, $InfoCpte['id_cpte']);
  }


  $solde_disponible = getSoldeDisponible($InfoCpte['id_cpte']);
  if ( $solde_disponible - $montant < 0){
    return  new ErrorObj(ERR_MNT_MIN_DEPASSE, $InfoCpte["id_cpte"]);
  }

  return new ErrorObj(NO_ERR);
}


function approvisionnementAgent($InfoCpte,$id_cpte,$montant_appr,$data_agent,$InfoProduit)
{
  global $dbHandler, $global_id_agence;

  global $global_nom_login, $global_id_agence, $global_monnaie;
  $comptable = array();
  $db = $dbHandler->openConnection();
  $erreur = CheckCompteApprotrans($InfoCpte, $InfoProduit, $montant_appr);
  if ($erreur->errCode != NO_ERR) {
      $erreur = new HTML_erreur(_("Erreur dans l'approvisionnement"));
      $erreur->setMessage(_("Il y a un soucis avec le compte debiteur. Veuillez verifier le compte du client ". $erreur->param." "));
      $erreur->addButton(BUTTON_OK,"Gen-16");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      die();
      $dbHandler->closeConnection(false);
      return $erreur;
  }

  // Passage de l'écriture de retrait
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();
  // Débit du compte client
  $cpta_debit = getCompteCptaProdEp($id_cpte);

  $int_debit = $id_cpte;

// Credit du compte comptable agent
  $cptes_substitue["cpta"]["debit"] = $cpta_debit;
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["debit"] = $int_debit;
  $cptes_substitue["cpta"]["credit"] = $data_agent['cpte_flotte_agent'];

  $operation=618;

  $myErr = passageEcrituresComptablesAuto($operation, $montant_appr, $comptable, $cptes_substitue, $InfoCpte['devise']);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $fonction = 758;
  $myErr = ajout_historique($fonction, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('id'=>$id_his));
}


function checkIfParamApproTransExist(){
  global $dbHandler, $global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) from ag_param_appro_transfert";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}


function get_param_appro_trans(){
  global $dbHandler, $global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $sql = "SELECT autorisation_appro,autorisation_transfert from ag_param_appro_transfert";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function transfertAgent($InfoCpte,$id_cpte,$montant_appr,$data_agent,$InfoProduit)
{
  global $dbHandler, $global_id_agence;

  global $global_nom_login, $global_id_agence, $global_monnaie;
  $comptable = array();
  $db = $dbHandler->openConnection();
//  $erreur = CheckCompteApprotrans($InfoCpte, $InfoProduit, $montant_appr);
//  if ($erreur->errCode != NO_ERR) {
//    if ($erreur->errCode == ERR_MNT_MIN_DEPASSE){
//      $erreur = new HTML_erreur(_("Montant insuffisant"));
//      $erreur->setMessage(_("Le montant disponible sur le compte de l'agent  est insuffisant"));
//      $erreur->addButton(BUTTON_OK,"Gen-16");
//      $erreur->buildHTML();
//      echo $erreur->HTML_code;
//      die();
//      $dbHandler->closeConnection(false);
//      return $erreur;
//    }
//
//  }
  $data_compta  = getDataCpteCompta($data_agent['cpte_flotte_agent']);
  if ($montant_appr > $data_compta['solde']){
    $erreur = new HTML_erreur(_("Montant insuffisant"));
    $erreur->setMessage(_("Le montant demandé pour le transfert est superieur au montant sur le compte de flotte"));
    $erreur->addButton(BUTTON_OK,"Gen-16");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    die();
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Passage de l'écriture de retrait
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();
  // Débit du compte client
  $cpta_credit = getCompteCptaProdEp($id_cpte);

  $int_credit = $id_cpte;

// Credit du compte comptable agent
  $cptes_substitue["cpta"]["debit"] = $data_agent['cpte_flotte_agent'];

  $cptes_substitue["int"]["credit"] = $int_credit;
  $cptes_substitue["cpta"]["credit"] = $cpta_credit;
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $operation=619;

  $myErr = passageEcrituresComptablesAuto($operation, $montant_appr, $comptable, $cptes_substitue, $InfoCpte['devise']);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $fonction = 758;
  $myErr = ajout_historique($fonction, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('id'=>$id_his));
}
function getParamCommissionInsti(){
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ag_param_commission_institution ";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }
    if ($result->numRows() == 0)
    {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $DATAS;
}
function getCommissionDepotRetrait($type_comm = null){
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ag_commission";

    if($type_comm != null) {
        $sql .= " WHERE type_comm = $type_comm";
    }

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }
    if ($result->numRows() == 0)
    {
        $dbHandler->closeConnection(true);
        return NULL;
    }

    $res = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($res, $row);
    $dbHandler->closeConnection(true);
    return $res;
}

function retrait_cpte_agency($id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque=NULL, $CHANGE=NULL,$dataBef=NULL,$isDureeMinEntreRetraits=NULL,$comm_agent = null, $comm_inst = null) {
  global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
  global $dbHandler, $global_multidevise;
  global $global_monnaie;
  $comptable = array();
  $comptable1 = array();
  $is_insert_chq = FALSE;
  $db = $dbHandler->openConnection();
  //vérifier d'abord qu'on peut retirer
  switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
    case 1:
    case 4:
    case 5:
    case 6:
      $retrait_transfert = 0;//il s'agit d'un retrait (il faut prélever des frais de retrait)
      break;
    case 3:
      $retrait_transfert = 1;//il s'agit d'un transfert (il faut prélever des frais de transfert)
      break;
    case 8:
    case 15:
      if ($data_cheque['id_correspondant'] == 0)
        $retrait_transfert = 0; //il s'agit d'un chèque-guichet
      else
        $retrait_transfert = 1;//il s'agit d'un chèque transmis par une banque
      break;
    case 55: // Retrait par lot
      if ($data_cheque['id_correspondant'] == 0)
        $retrait_transfert = 0; //il s'agit d'un chèque-guichet
      else
        $retrait_transfert = 1;//il s'agit d'un chèque transmis par une banque
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $erreur = new ErrorObj(NO_ERR);
  $choix_retrait_comm = getParamCommissionInsti();

  if ($isDureeMinEntreRetraits != null){ // ticket 805 : laisser passer erreur duree min entre 2 retraits
    $erreur = CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat, true);
  }
  else{
    $erreur = CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat);
  }
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }


  // Passage de l'écriture de retrait
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  $cptes_substitue1 = array();
  $cptes_substitue1["cpta"] = array();
  $cptes_substitue1["int"] = array();

  // Débit du compte client
  $cpta_debit = getCompteCptaProdEp($id_cpte);

  $int_debit = $id_cpte;

  // Débit du compte client
  $cptes_substitue["cpta"]["debit"] = $cpta_debit;
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["debit"] = $int_debit;


  $montant = arrondiMonnaiePrecision($montant);
  $data_login = getDatasLogin();
  $cptes_substitue["cpta"]["credit"] = $data_login['cpte_flotte_agent'];
  $operation=624;
  $fonction=764;

  /*if (is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {
    $myErr = change($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"]);
  } else {*/
    $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise']);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  $cpte_ins = $choix_retrait_comm['cpte_compta_comm_retrait'];
  $cpte_agent = ($data_login['cpte_comm_agent'] == '2')?getCompteCptaProdEp($data_login['cpte_base_agent']):$data_login['cpte_flotte_agent'];


  //Prelevement des commissions
  if(strcmp($choix_retrait_comm['choix_retrait_comm'], '1') == 0 ) {
    //transfert compte de commission intermediaire
    $cptes_substitue["int"]["debit"] = NULL;
    $cpte_intermediaire = getCpteCommIntermediaire();
    $critere = array();
    $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
    $cpte_gui = getComptesComptables($critere);
    $montant = arrondiMonnaie($comm_agent, 0, $cpte_gui['devise']);

    //Prelevement du cpte inst vers compte intermediaire
    $cptes_substitue["cpta"]["debit"] = $cpte_ins;
    $cptes_substitue["cpta"]["credit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
    $myErr = passageEcrituresComptablesAuto(628, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    //calcul de l'impots si parametre sur le montant de la commission pour le operation de l'impot
    $info_impot = getInfoImpot();

      if (sizeof($info_impot) > 0 && $info_impot['appl_impot_agent'] == 't') {

        $calculator_prc = $info_impot['prc_import'] / 100;
        $mnt_impot = $montant * $calculator_prc;
        $mnt_impot = arrondiMonnaie($mnt_impot, 0, $cpte_gui['devise']);

        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $info_impot['cpte_impot'];
        $myErr = passageEcrituresComptablesAuto(629, $mnt_impot, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }

        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $cpte_agent;
        $cptes_substitue1["int"]["debit"] = null;
        $cptes_substitue1["int"]["credit"] = ($data_login['cpte_comm_agent'] == '2') ? $data_login['cpte_base_agent'] : null;
        $mnt_comm_restant_agent = $montant - $mnt_impot;
        $mnt_comm_restant_agent = arrondiMonnaie($mnt_comm_restant_agent, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(622, $mnt_comm_restant_agent, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
     else{
      $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $cpte_agent;
        $cptes_substitue1["int"]["debit"] = null;
        $cptes_substitue1["int"]["credit"] = ($data_login['cpte_comm_agent'] == '2')?$data_login['cpte_base_agent']:null;


        $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'],NULL,$id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }

  }
  else{
    //calcul de la somme de la commission totale
    $critere = array();
    $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
    $cpte_gui = getComptesComptables($critere);
    $montant_agent = arrondiMonnaie($comm_agent, 0, $cpte_gui['devise'] );
    $montant_inst = arrondiMonnaie($comm_inst, 0, $cpte_gui['devise'] );
    $mnt_comm_total = $montant_agent + $montant_inst;
    $mnt_comm_total = arrondiMonnaie($mnt_comm_total, 0, $cpte_gui['devise'] );
    $cpte_intermediaire = getCpteCommIntermediaire();

    //transfert commission total sur le cpte intermedaire
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    $cptes_substitue["cpta"]["credit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
    $cptes_substitue["int"]["debit"] = $id_cpte;

    $myErr = passageEcrituresComptablesAuto(628, $mnt_comm_total, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    //calcul de l'impots si parametre sur le montant de la commission pour le operation de l'impot
    $info_impot = getInfoImpot();
    $calculator_prc = $info_impot['prc_import'] / 100;

    if (sizeof($info_impot) > 0 && $info_impot['appl_impot_agent'] == 't') {
      $mnt_impot_agent = $montant_agent * $calculator_prc;
      $mnt_impot_agent = arrondiMonnaie($mnt_impot_agent, 0, $cpte_gui['devise']);


      $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
      $cptes_substitue1["cpta"]["credit"] = $info_impot['cpte_impot'];
      $cptes_substitue1["int"]["debit"] = NULL;

      $myErr = passageEcrituresComptablesAuto(629, $mnt_impot_agent, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

      $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
      $cptes_substitue1["cpta"]["credit"] = $cpte_agent;
      $cptes_substitue1["int"]["credit"] = ($data_login['cpte_comm_agent'] == '2') ? $data_login['cpte_base_agent'] : null;
      $mnt_comm_agent_restant = $montant_agent - $mnt_impot_agent;
      $mnt_comm_agent_restant = arrondiMonnaie($mnt_comm_agent_restant, 0, $cpte_gui['devise']);

      $myErr = passageEcrituresComptablesAuto(622, $mnt_comm_agent_restant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }else{
      $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
      $cptes_substitue1["cpta"]["credit"] = $cpte_agent;
      $cptes_substitue1["int"]["credit"] = ($data_login['cpte_comm_agent'] == '2') ? $data_login['cpte_base_agent'] : null;
      $montant_agent = arrondiMonnaie($montant_agent, 0, $cpte_gui['devise'] );

        $myErr = passageEcrituresComptablesAuto(622, $montant_agent, $comptable1, $cptes_substitue1, $InfoCpte['devise'],NULL,$id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }

      }

      if (sizeof($info_impot) > 0 && $info_impot['appl_impot_institution'] == 't') {
        $mnt_impot_inst = $montant_inst * $calculator_prc;
        $mnt_impot_inst = arrondiMonnaie($mnt_impot_inst, 0, $cpte_gui['devise']);
        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $info_impot['cpte_impot'];
        $cptes_substitue1["int"]["debit"] = NULL;
        $cptes_substitue1["int"]["credit"] = NULL;

        $myErr = passageEcrituresComptablesAuto(629, $mnt_impot_inst, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }

        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $cpte_ins;
        $mnt_comm_inst_restant = $montant_inst - $mnt_impot_inst;
        $mnt_comm_inst_restant = arrondiMonnaie($mnt_comm_inst_restant, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(623, $mnt_comm_inst_restant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
      else {

        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediaire['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $cpte_ins;
        $montant_inst = arrondiMonnaie($montant_inst, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(623, $montant_inst, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
  }


  // Si la duree minimum entre deux retraits n'est pas atteinte, prélever les frais  : Ticket 805
  if ($isDureeMinEntreRetraits != null && $isDureeMinEntreRetraits == 't'){
    if (intval(getNbrJoursEntreDeuxRetrait($operation,$id_cpte)) <= intval($InfoProduit['duree_min_retrait_jour'])){
      $myErr = preleveFraisDureeMinEntre2Retraits($id_cpte, $comptable);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }


  // Si le compte est passé en découvert, prélever les frais de dossier découvert
  $myErr = preleveFraisDecouvert($id_cpte, $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($id_mandat != NULL && $id_mandat != 'CONJ') {
    $MANDAT = getInfosMandat($id_mandat);
    $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
  }
  //
  if ($data_cheque != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_cheque);
  } else {
    $data_his_ext = NULL;
  }
 /* if( $is_insert_chq ) {
    if (is_array($dataBef)){
      $id = insere_tireur_benef($dataBef);
      $data_his_ext['id_tireur_benef']=$id;
    }
    $data_ch['id_cheque']=$data_cheque['num_piece'];
    $data_ch['date_paiement']=$data_cheque['date_piece'];
    if ($type_retrait == 8 or ($type_retrait == 15 and $isChequeCertifie)) {
      $data_ch['etat_cheque']=4; // Certifié
    } else {
      $data_ch['etat_cheque']=1; // Encaissé
    }
    $data_ch['id_benef'] =$data_his_ext['id_tireur_benef'];
    $rep=insertCheque($data_ch, $id_cpte);
    if ($rep->errCode != NO_ERR ) {
      $dbHandler->closeConnection(false);
      return $rep;
    } else {
      if ($type_retrait == 8 or ($type_retrait == 15 and $isChequeCertifie)) {
        // Mettre à jour le statut d'un chèque certifié à Traité
        $erreur = ChequeCertifie::updateChequeCertifieToTraite($num_cheque, $id_cpte, $int_debit, "Retrait chèque interne certifié No. ". $num_cheque);

        if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
        } else {
          // Fermeture du compte de chèque certifié
          $erreur = ChequeCertifie::closeCompteChequeCertifie($int_debit);

          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          }
        }
      }
    }
  }*/
  $myErr = ajout_historique($fonction, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $fonction_agency = 786;
  $myErr1 = ajout_historique($fonction_agency, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable1);
  if ($myErr1->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr1;
  }
  $id_his = $myErr->param;
  $id_his_commission = $myErr1->param;

  $update_id_his = updateIdHisAgency($id_his,$id_his_commission);

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('id'=>$id_his));
}

function depot_cpte_agency($id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA=NULL, $type_depot=NULL, $CHANGE=NULL, $frais_virement=NULL, $infos_sup=NULL) {
    global $dbHandler, $global_id_agence;

    global $global_nom_login, $global_id_agence, $global_monnaie;

    //pour pouvoir commit ou rollback toute la procédure
    $db = $dbHandler->openConnection();

    if ($DATA != NULL) {
        $DATA_HIS_EXT = creationHistoriqueExterieur($DATA);
    } else {
        $DATA_HIS_EXT = NULL;
    }

    // Si le compte était dormant, le faire passer à l'état ouvert
    // FIXME : On devrait pouvoir supprimer ceci
    /* if ($InfoCpte["etat_cpte"] == 4) {
       $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
       $result = $db->query($sql);
       if (DB::isError($result)) {
         $dbHandler->closeConnection(false);
         signalErreur(__FILE__,__LINE__,__FUNCTION__);
       }
     }*/

    //Check que le dépôt est possible sur le compte
    $erreur = CheckDepot($InfoCpte, $montant);
    if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
    }

    // Passage des écritures comptables
    $comptable = array();
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

  $comptable1 = array();
  $cptes_substitue1 = array();
  $cptes_substitue1["cpta"] = array();
  $cptes_substitue1["int"] = array();

    //débit d'un guichet par le crédit d'un client
    $agent_data = getDatasLogin($global_nom_login);
    $cptes_substitue["cpta"]["debit"] = $agent_data['cpte_flotte_agent'];

    /* Arrondi du montant si paiement au guichet*/
    $critere = array();
    $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
    $cpte_gui = getComptesComptables($critere);
    $montant = arrondiMonnaie( $montant, 0, $cpte_gui['devise'] );
    $montant = recupMontant($montant);

    //Produit du compte d'épargne associé
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["credit"] = $id_cpte;
    if (isset($CHANGE)) {
        $myErr = change ($CHANGE['devise'], $InfoCpte['devise'], $CHANGE['cv'], $montant, 621, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'],true,$infos_sup);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

    } else {
        $myErr = passageEcrituresComptablesAuto(621, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
    }

    $choix_depot_comm = getParamCommissionInsti();
    $cpte_ins = $choix_depot_comm['cpte_compta_comm_depot'];
    $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

    $info_impot = getInfoImpot();
    $cpte_intermediare = getCpteCommIntermediaire();
    //choix depot est supporté par l'agent
    if(strcmp($choix_depot_comm['choix_depot_comm'], '1') == 0 ){

      $cptes_substitue["cpta"]["debit"] = $cpte_ins;
        $cptes_substitue["cpta"]["credit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue["int"]["credit"] = null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

        $myErr = passageEcrituresComptablesAuto(628, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
          if(sizeof($info_impot) > 0 && $info_impot['appl_impot_agent'] == 't') {
            $montant_comm_impot = ($info_impot['prc_import'] / 100) * $DATA['commission']['agent'];
            $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
            $cptes_substitue1["cpta"]["credit"] = $info_impot['cpte_impot'];
            $cptes_substitue1["int"]["credit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($montant_comm_impot, 0, $cpte_gui['devise']);

            $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $myErr;
            }


            $montant_comm_agent = arrondiMonnaie($DATA['commission']['agent'] - $montant_comm_impot, 0);
            $cptes_substitue1["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? getCompteCptaProdEp($cpte_agent) : $cpte_agent;
            $cptes_substitue1["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? $cpte_agent : null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($montant_comm_agent, 0, $cpte_gui['devise']);

            $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $myErr;
            }
        }else{
            $cptes_substitue1["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
            $cptes_substitue1["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
            $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

            $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
    }
    else{

        //depot compte intermediare
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
        $cptes_substitue["cpta"]["credit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue["int"]["debit"] = $id_cpte;
        $cptes_substitue["int"]["credit"] = null;
        $mnt_comm_total = $DATA['commission']['agent'] + $DATA['commission']['institution'];

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_total, 0, $cpte_gui['devise'] );

        $myErr = passageEcrituresComptablesAuto(628, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

    if (sizeof($info_impot) > 0) {
      if ($info_impot['appl_impot_agent'] == 't') {
        //commission impot
        $montant_comm_impot = $info_impot['prc_import'] / 100;
        $mnt_comm_agent = arrondiMonnaie($DATA['commission']['agent'] * $montant_comm_impot, 0);

        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $info_impot['cpte_impot'];
        $cptes_substitue1["int"]["credit"] = null;
        $cptes_substitue1["int"]["debit"] = null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_agent, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
        //commission agent
        $mnt_comm_agent = $DATA['commission']['agent'] - $mnt_comm_agent;
        $cptes_substitue1["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? getCompteCptaProdEp($cpte_agent) : $cpte_agent;
        $cptes_substitue1["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? $cpte_agent : null;
        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        //$cptes_substitue["int"]["debit"] = $id_cpte;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_agent, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      } else {
        //commission agent
        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $mnt_comm_agent = $DATA['commission']['agent'];
        $cptes_substitue1["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? getCompteCptaProdEp($cpte_agent) : $cpte_agent;
        $cptes_substitue1["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? $cpte_agent : null;
        //$cptes_substitue["int"]["debit"] = $id_cpte;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_agent, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }

      if ($info_impot['appl_impot_institution'] == 't') {

        $mnt_comm_inst = arrondiMonnaie($DATA['commission']['institution'] * $montant_comm_impot, 0);
        $montant = arrondiMonnaie($mnt_comm_inst, 0, $cpte_gui['devise']);

        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue1["cpta"]["credit"] = $info_impot['cpte_impot'];
        $cptes_substitue1["int"]["credit"] = null;
        $cptes_substitue1["int"]["debit"] = null;

        $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }

        //commission institution
        $mnt_comm_inst = $DATA['commission']['institution'] - $mnt_comm_inst;
        $cptes_substitue1["cpta"]["credit"] = $cpte_ins;
        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue1["int"]["credit"] = null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_inst, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(623, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
      else{
        //commission institution
        $mnt_comm_inst = $DATA['commission']['institution'];
        $cptes_substitue1["cpta"]["credit"] = $cpte_ins;
        $cptes_substitue1["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue1["int"]["credit"] = null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_inst, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(623, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
    }
    else{

          $cptes_substitue1["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
          $cptes_substitue1["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
          $cptes_substitue1["int"]["debit"] = $id_cpte;

          $critere = array();
          $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
          $cpte_gui = getComptesComptables($critere);
          $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

          $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

          if ($myErr->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $myErr;
          }

          $cptes_substitue1["cpta"]["credit"] = $cpte_ins;
          $cptes_substitue1["int"]["credit"] = null;

          $critere = array();
          $critere['num_cpte_comptable'] = $cptes_substitue1["cpta"]["debit"];
          $cpte_gui = getComptesComptables($critere);
          $montant = arrondiMonnaie($DATA['commission']['institution'], 0, $cpte_gui['devise'] );

          $myErr = passageEcrituresComptablesAuto(623, $montant, $comptable1, $cptes_substitue1, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

          if ($myErr->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $myErr;
          }
      }
    }

    if ($type_depot == NULL){//dépôt normal
        $myErr = ajout_historique(763, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);
    }

    if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
    }

  if ($type_depot == NULL){//dépôt normal
    $myErr1 = ajout_historique(786, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable1, $DATA_HIS_EXT);
  }

  if ($myErr1->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr1;
  }

    $id_his = $myErr->param;
    $id_his_commission = $myErr1->param;

    $update_id_his_transaction = updateIdHisAgency($id_his,$id_his_commission);


    $dbHandler->closeConnection(true);

    // le paramètre 'mnt' n'est pour le moment utilisé nulle part
    return new ErrorObj(NO_ERR, array('id'=>$id_his));

}
function constructArray($stack, $filter){
  $keys = array_keys($stack);
  $match = array();

  foreach($keys as $value){
    preg_match('/('.$filter.')/', $value, $matches);
    if(!empty($matches[0])){
        array_push($match, $value);
    }
  }
  return $match;
}
function getNumbers($array){
  $match = array();
  foreach($array as $value) {
      preg_match('/[0-9]+/', $value, $matches);
      if(!empty($matches[0])){
          array_push($match, intval($matches[0]));
      }
  }
  return $match;
}
function getClient($id_cpte_base){
    global $global_id_agence, $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT c.* FROM ad_cli c INNER JOIN ad_cpt t ON t.id_titulaire = c.id_client WHERE c.id_ag = $global_id_agence AND t.id_cpte = $id_cpte_base";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);

    return $DATAS;
}


function print_recu_retrait_agent($id_client, $nom_client, $InfoProduit, $infos, $mnt, $id_his, $code_recu, $id_mandat = NULL,$remarque=NULL,$communication=NULL, $id_pers_ext = NULL, $num_carte_ferlo=NULL, $nom_conj = "", $listTypesBilletArr = array(), $valeurBilletArr = array(),$global_langue_utilisateur, $total_billetArr = array(), $hasBilletage = false,$isBilletageParam,$isDureeMinEntreRetraits=NULL,$comm_agent=0, $comm_inst= 0)
{
  global $global_id_agence, $global_id_profil;
  $format_A5 = false;

  $isAffichageSolde=getParamAffichageSolde();

   $document = create_xml_doc("recu", "recu_depot_retrait_agent.dtd");
  $comm_transaction = $comm_agent +$comm_inst;

  //Element root
  $root = $document->root();
  //$root->set_attribute("type", 8);

  //recuperation des données de l'agence'
  $AG = getAgenceDatas($global_id_agence);
  if($AG['imprimante_matricielle'] == 't'){
    $format_A5 = true;
  }

  //En-tête généraliste
  $ref = gen_header($root, $code_recu);

  setMonnaieCourante($InfoProduit["devise"]);

  //appel a la fonction qui fait la conversion d'un montant  en  montant en lettre
  $mntEnLettre = getMontantEnLettre($mnt,$global_langue_utilisateur ,$InfoProduit["devise"]);

  //Corps
  $body = $root->new_child("body", "");
  if ( $nom_client!= NULL)
    $body->new_child("nom_client", $nom_client);
  if ($id_pers_ext != NULL || $id_mandat != NULL || $nom_conj != NULL) {
    if ($id_mandat != NULL) {
      $MANDAT = getInfosMandat($id_mandat);
      $body->new_child("donneur_ordre", $MANDAT['denomination']);
    } elseif ($id_pers_ext != NULL) {
      $PERS_EXT = getPersonneExt(array("id_pers_ext" => $id_pers_ext));
      $body->new_child("donneur_ordre", $PERS_EXT[0]['denomination']);
    } elseif ($nom_conj) {
      $body->new_child("donneur_ordre",$nom_conj);
    }

  } else {
    //Contôle sur l'affichage des soldes
    $access_solde = get_profil_acces_solde($global_id_profil, $InfoProduit['id']);
    $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $id_client);

    if($isAffichageSolde == 't'){
      if(manage_display_solde_access($access_solde, $access_solde_vip))
        $body->new_child("solde", afficheMontant($infos['solde'], true));
    }
  }
  if ($infos['num_complet_cpte'] != NULL)
    $body->new_child("num_cpte", $infos['num_complet_cpte']);
  if ($num_carte_ferlo != NULL)
    $body->new_child("num_carte_ferlo", $num_carte_ferlo);
  $body->new_child("montant", afficheMontant($mnt, true));
  $body->new_child("num_trans", sprintf("%09d", $id_his));
  if ($comm_transaction != 0) {
    if ($comm_inst != 0){
      $body->new_child("comm_transaction", afficheMontant($comm_transaction, true));
    }
  }


  if ($InfoProduit != NULL){ // ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits
    if ($isDureeMinEntreRetraits != null && $isDureeMinEntreRetraits == 't' && $InfoProduit['frais_duree_min2retrait'] > 0){
      $body->new_child("fraisDureeMin", afficheMontant($InfoProduit['frais_duree_min2retrait'], true));
    }
    else{
      $body->new_child("fraisDureeMin", afficheMontant(0, true));
    }
  }
  if ($remarque != '')
    $body->new_child("remarque", $remarque);
  if ($communication != '')
    $body->new_child("communication", $communication);

  // Billetage
  if($hasBilletage) {
    $body->new_child("hasBilletage", true);

    for ($x=0;$x<count($valeurBilletArr);$x++){
      if ($valeurBilletArr[$x] != 'XXXX') {
        $body->new_child("libel_billet_".$x, afficheMontant($listTypesBilletArr[$x]['libel']));
        $body->new_child("valeur_billet_".$x, $valeurBilletArr[$x]);
        $body->new_child("total_billet_".$x, afficheMontant($total_billetArr[$x]));
      }
    }
  }

  //montant en lettre
  if($mntEnLettre !='')
    $body->new_child("mntEnLettre", $mntEnLettre);

  $xml = $document->dump_mem(true);
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_depot_retrait_agent.xslt');

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  get_show_pdf_html("Gen-3", $fichier_pdf, false);

  $myErr = confirmeGenerationRecu($id_his, $ref);
  if ($myErr->errCode != NO_ERR)
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  return true;
}

function print_recu_depot_agent($id_client, $nom_client, $mnt, $InfoProduit, $infos, $id_his, $id_pers_ext = NULL,$remarq=NULL,$communic=NULL, $mnt_frais_attente = 0, $id_mandat = NULL, $listTypesBilletArr = array(), $valeurBilletArr = array(), $global_langue_rapport, $total_billetArr = array(), $hasBilletage = false, $isBilletageParam,$comm_agent=0, $comm_inst=0) {
    global $global_id_agence, $global_id_profil;
    setMonnaiecourante($InfoProduit["devise"]);
    $comm_transaction = $comm_agent+$comm_inst;

    $isAffichageSolde=getParamAffichageSolde();

    //appel a la fonction qui fait la conversion d'un montant  en  montant en lettre
    $mntEnLettre = getMontantEnLettre($mnt,$global_langue_rapport ,$InfoProduit["devise"]);

    $format_A5 = false;

    $document = create_xml_doc("recu", "recu_depot_retrait_agent.dtd");
    //Element root
    $root = $document->root();
    //$root->set_attribute("type", 6);

    $num= $infos['num_complet_cpte']." ".$infos["libel"];

    //recuperation des données de l'agence'
    $AG = getAgenceDatas($global_id_agence);
    if($AG['imprimante_matricielle'] == 't'){
        $format_A5 = true;
    }
    //En-tête généraliste
    $ref = gen_header($root, 'REC-RDA');

    //Corps
    $body = $root->new_child("body", "");
    $body->new_child("nom_client", $nom_client);
    if ($id_mandat != NULL || $id_pers_ext != NULL)  {
        if ($id_mandat != NULL) {
            $MANDAT = getInfosMandat($id_mandat);
            $body->new_child("donneur_ordre", $MANDAT['denomination']);
        } elseif ($id_pers_ext != NULL) {
            $PERS_EXT = getPersonneExt(array("id_pers_ext" => $id_pers_ext));
            $body->new_child("donneur_ordre", $PERS_EXT[0]['denomination']);
        }
    } elseif($id_pers_ext == NULL ) {
        //Contôle sur l'affichage des soldes
        if ($isAffichageSolde == 't') {
            $access_solde = get_profil_acces_solde($global_id_profil, $InfoProduit['id']);
            $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $id_client);
            if (manage_display_solde_access($access_solde, $access_solde_vip))
                $body->new_child("solde", afficheMontant($infos['solde'], true));
        }
    }
    $body->new_child("num_cpte", $num);
    $body->new_child("montant", afficheMontant($mnt, true));
    $body->new_child("num_trans", sprintf("%09d", $id_his));
    if ($comm_transaction != 0) {
        if($comm_inst != 0) {
            $body->new_child("comm_transaction", afficheMontant($comm_transaction, true));
        }
    }

    if($mnt_frais_attente > 0){
        $body->new_child("frais_attente", afficheMontant($mnt_frais_attente, true));
    }
    if ($remarq != '')
        $body->new_child("remarque", $remarq);
    if ($communic != '')
        $body->new_child("communication", $communic);

    // Billetage
    if($hasBilletage) {
        $body->new_child("hasBilletage", true);

        for ($x = 0; $x < count($valeurBilletArr); $x ++) {
            if ($valeurBilletArr[$x] != 'XXXX') {
                $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
                $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
                $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
            }
        }
    }

    if($mntEnLettre!='')
        $body->new_child("mntEnLettre", $mntEnLettre);

    $xml = $document->dump_mem(true);

    //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)

   /* if($isBilletageParam == 't'){

        if($format_A5){
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu.xslt');
        }
    }
    else{
        if($format_A5){
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5_ancien.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_ancien.xslt');
        }
    }*/
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_depot_retrait_agent.xslt');
    // Affichage du rapport dans une nouvelle fenêtre
    echo get_show_pdf_html(NULL, $fichier_pdf);

    $myErr = confirmeGenerationRecu($id_his, $ref);
    if ($myErr->errCode != NO_ERR)
        signalErreur(__FILE__,__LINE__,__FUNCTION__);

    return true;
}

function getListeOperationEpargneViaAgent($login)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT h.id_his, h.type_fonction, e.type_operation, m.montant, h.date, m.devise, m.cpte_interne_cli FROM ad_his h INNER JOIN ad_ecriture e ON h.id_his=e.id_his INNER JOIN ad_mouvement m ON e.id_ecriture=m.id_ecriture WHERE h.id_ag = $global_id_agence AND h.login = '$login' AND e.type_operation IN (621,624) AND h.id_his NOT IN (SELECT id_his FROM ad_annulation_retrait_depot_agent WHERE id_ag = $global_id_agence AND etat_annul IN (1,2,3,4,5)) AND h.type_fonction IN (763,764) AND to_char(h.date, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') AND m.date_valeur BETWEEN date(now()) - 10 AND date(now()) AND m.cpte_interne_cli is not null ORDER BY h.id_his ASC;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);

    return NULL;
  }

  $tmp_arr = array();

  while ($ListOpe = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

    $tmp_arr[$ListOpe['id_his']] = $ListOpe;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}



function getFraisOpeViaAgent($id_ope,$type_fonction,$id_cpte,$type_ope)
{
        global $global_id_agence,$dbHandler;

//echo $type_operation_func;
        $sql = "SELECT montant
FROM ad_mouvement
WHERE
date_valeur BETWEEN date(now()) - 30 AND date(now()) AND
sens = 'd' AND
id_ecriture =
(SELECT id_ecriture FROM ad_ecriture
WHERE id_ag = $global_id_agence
AND id_his = $id_ope
AND type_operation = $type_ope);";

        $result = executeDirectQuery($sql, FALSE);
        if ($result->errCode != NO_ERR) {
          return $result;
        } else {
          if (empty($result->param)) {
            return NULL;
          } else {
            return $result->param[0];
          }
        }
    }

function getLibelOpeAgent($type_ope)
{
$libel_ope = "";

if ($type_ope > 0) {

  $myErr = getOperations($type_ope);

  if ($myErr->errCode == NO_ERR) {
    $OP = $myErr->param;

    $trad = new Trad($OP['libel']);

    $libel_ope = $trad->traduction();
  }
}

return $libel_ope;
}

function processOperationEpargneViaAgent($data, $login)
{
  global $dbHandler, $global_id_agence, $global_nom_login;

  $demande_count = 0;
  if (isset($data['btn_process_demande'])) {

    // Get liste de retraits et dépôts du jour
    $listeOpeEpg =getListeOperationEpargneViaAgent($login);

    if (is_array($listeOpeEpg) && count($listeOpeEpg) > 0) {

      $db = $dbHandler->openConnection();

      foreach ($listeOpeEpg as $id => $opeEpg) {

        $id_trans = trim($opeEpg["id_his"]);


        if (isset($data['check_valid_' . $id_trans])) {

          $mnt_agent = getFraisOpeViaAgent(trim($opeEpg["id_his"]),$opeEpg["type_operation"],trim($opeEpg["cpte_interne_cli"]),622);
          $mnt_inst = getFraisOpeViaAgent(trim($opeEpg["id_his"]),$opeEpg["type_operation"],trim($opeEpg["cpte_interne_cli"]),623);
          $fonc_sys = trim($opeEpg["type_fonction"]);
          $type_ope = trim($opeEpg["type_operation"]);
          $montant = trim($opeEpg["montant"]);
          $devise = trim($opeEpg["devise"]);
          $comm_agent= trim($mnt_agent["montant"]);
          $comm_inst= trim($mnt_inst["montant"]);
          $data_cli = getClient(trim($opeEpg["cpte_interne_cli"]));
          $id_client = $data_cli['id_client'];

          // Enregistrer une demande d'annulation retrait et dépôt
          $erreur = insertAnnulationRetraitDepotViaAgent($id_trans, $id_client, $fonc_sys, $type_ope, $montant,$comm_agent,$comm_inst ,
$devise);

          if ($erreur->errCode == NO_ERR) {
            $demande_count++;
          } else {
            $dbHandler->closeConnection(false);
            return $erreur;
          }
        }
      }

      if ($demande_count > 0) {

        $type_fonction = 766; // Demande annulation retrait / dépôt agent
        $myErr = ajout_historique($type_fonction, $id_client, "Demande annulation retrait / dépôt", $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }

      // Commit
      $dbHandler->closeConnection(true);
    }
  }

  return new ErrorObj(NO_ERR, $demande_count);
}

function insertAnnulationRetraitDepotViaAgent($id_his, $id_client, $fonc_sys, $type_ope = null, $montant = null, $comm_agent= null, $comm_inst = null, $devise = null, $etat_annul = 1, $comments = 'Demande annulation : Enregistré')
{
  global $dbHandler, $global_id_agence, $global_nom_login;

  $db = $dbHandler->openConnection();

  $tableFields = array(
    "id_his" => $id_his,
    "id_client" => $id_client,
    "fonc_sys" => $fonc_sys,
    "type_ope" => $type_ope,
    "montant" => recupMontant($montant),
    "devise" => $devise,
    "etat_annul" => $etat_annul,
    "comments" => trim($comments),
    "id_ag" => $global_id_agence,
    "login" => $global_nom_login,
    "commission_agent"=>recupMontant($comm_agent),
    "commission_inst"=>recupMontant($comm_inst),
  );
  $sql = buildInsertQuery("ad_annulation_retrait_depot_agent", $tableFields);

  $result = $db->query($sql);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}



function getListeDemandeAnnulationViaAgent($id_client = null, $etat_annul = 1){
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_annulation_retrait_depot_agent WHERE id_ag = $global_id_agence ";

    $sql .= " AND to_char(date_crea, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') ";

    if ($id_client != null) {
        $sql .= " AND id_client = $id_client ";
    }

    if ($etat_annul != 0) {
        $sql .= " AND etat_annul = $etat_annul ";
    }

    $sql .= " ORDER BY id ASC";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);

        return NULL;
    }

    $tmp_arr = array();

    while ($ListDemande = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $tmp_arr[$ListDemande['id']] = $ListDemande;
    }

    $dbHandler->closeConnection(true);

    return $tmp_arr;
}
function getLibelFoncAgent($type_fonc)
{
    global $adsys;

    $libel_fonc = "";

    if ($type_fonc > 0) {

        $libel_fonc = $adsys["adsys_fonction_systeme"][$type_fonc];
    }

    return $libel_fonc;
}
function processDemandeAnnulationAgent($data, $id_client = null)
{
    global $dbHandler, $global_id_agence, $global_nom_login, $adsys;

    $demande_count = 0;

    if (isset($data['btn_process_approbation'])) {

        // Get liste des demandes d'annulation
        $listeDemandeAnnulation = getListeDemandeAnnulationViaAgent();

        if (is_array($listeDemandeAnnulation) && count($listeDemandeAnnulation) > 0) {

            $db = $dbHandler->openConnection();
            $autoriser = 2;
            $reject = 3;

            foreach ($listeDemandeAnnulation as $id => $demandeAnnulation) {

                $isValidationOK = false;
                $isAutorisationOK = false;

                $id_demande = trim($demandeAnnulation["id"]);

                if (isset($data['check_valid_' . $id_demande])) {

                    $isValidationOK = true;
                    $isAutorisationOK = true;

                } elseif (isset($data['check_rejet_' . $id_demande])) {

                    $isValidationOK = true;
                }

                if ($isValidationOK == true) {

                    // Mettre à jour le statut d'une demande d'annulation à Autorisé / Rejeté
                    $erreur = updateEtatAnnulationRetraitDepotViaAgent($id_demande, (($isAutorisationOK) ? $autoriser : $reject), null, null, sprintf("Demande approbation annulation : %s", (($isAutorisationOK) ? "Autorisé" : "Rejeté")));

                    if ($erreur->errCode == NO_ERR) {
                        $demande_count++;
                    } else {
                        $dbHandler->closeConnection(false);
                        return $erreur;
                    }

                    $type_fonction = 767; // Approbation demande annulation retrait / dépôt

                    $myErr = ajout_historique($type_fonction, $demandeAnnulation['id_client'], "Approbation demande annulation retrait / dépôt", $global_nom_login, date("r"));

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }
                }
            }
            // Commit
            $dbHandler->closeConnection(true);
        }
    }

    return new ErrorObj(NO_ERR, $demande_count);
}
function updateEtatAnnulationRetraitDepotViaAgent($id_demande, $etat_annul, $id_his = null, $date_annul = null, $comments = '')
{
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $tableFields = array(
        "etat_annul" => $etat_annul,
        "annul_id_his" => $id_his,
        "date_modif" => date("r"),
        "date_annul" => $date_annul,
        "comments" => trim($comments)
    );

    $sql_update = buildUpdateQuery("ad_annulation_retrait_depot_agent", $tableFields, array('id' => $id_demande, 'id_ag' => $global_id_agence));

    $result = $db->query($sql_update);

    if (DB:: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

function processApprobationAnnulationAgent($id_demande, $id_client = null)
{
  global $dbHandler, $global_id_agence, $global_nom_login, $global_id_guichet;

  $db = $dbHandler->openConnection();

  $infoDemande = getDemandeAnnulationAgent($id_demande);

  $demande_count = 0;

  if (is_array($infoDemande) && count($infoDemande) > 0) {

    $id_his_ope = $infoDemande["id_his"];
    $id_client = $infoDemande["id_client"];
    $login = $infoDemande["login"];
    $fonc_sys_inv = getInverseFoncSysAgent($infoDemande["fonc_sys"]);

    // Vérifié si le login ayant fait l'opération est celui connecté actuellement
    $logged_logins = logged_logins();
    if ($global_nom_login != $login && (is_array($logged_logins)) && (in_array($login, $logged_logins))) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_GUICHET_OUVERT);
    }

    $listeOpeEpgDetail = getListeOpeEpgDetailAgent($id_his_ope);


    if (is_array($listeOpeEpgDetail) && count($listeOpeEpgDetail) > 0) {

      $comptable1 = array();
      $curr_date = date("r");
      $annul_remb_lcr = array();
      $cptes_substitue1 = array();
      $cptes_substitue1["cpta"] = array();
      $cptes_substitue1["int"] = array();

      $comptable = array();
      $curr_date = date("r");
      $annul_remb_lcr = array();
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();

      foreach ($listeOpeEpgDetail as $id_ecriture => $opeEpg) {
        //debut ticket 739 : date detaillée avec précision millisecondes
        $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);

        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        $format = 'Y-m-d H:i:s.u';

        $curr_date_frais_attente = date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp); //date specific pour insertion dans ad_frais_attente
        //fin ticket 739 : date detaillée avec précision millisecondes

        // Ecriture inverse pour les operations 622-623-629
        if ($opeEpg["type_operation"] == 628){
          $listeOpeEpgDetailCommission = getListeOpeEpgDetailAgent($opeEpg["infos"]);
          foreach ($listeOpeEpgDetailCommission as $id_ecriture_comm => $opeEpgComm) {
            if (in_array($opeEpgComm["type_operation"], array(622,623,629))) {


              $cptes_substitue["int"]["credit"] = null;
              $cptes_substitue["int"]["debit"] = null;


              $id_ecriture_ope = $opeEpgComm["id_ecriture"];
              $type_ope = $opeEpgComm["type_operation"];
              $type_ope_inv = getInverseOpeAgent($opeEpgComm["type_operation"]);
              $compte_a_debite = $opeEpgComm["compte_credit"];
              $cpte_interne_cli_a_debite = $opeEpgComm["cpte_interne_cli_credit"];
              $compte_a_credite = $opeEpgComm["compte_debit"];
              $cpte_interne_cli_a_credite = $opeEpgComm["cpte_interne_cli_debit"];
              $montant = recupMontant($opeEpgComm["montant"]);
              $devise = $opeEpgComm["devise"];




              if ($compte_a_debite != null) {
                $cptes_substitue["cpta"]["debit"] = $compte_a_debite;
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                  $dbHandler->closeConnection(false);
                  return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
                }
              }

              if ($cpte_interne_cli_a_debite != null) {
                $cptes_substitue["int"]["debit"] = $cpte_interne_cli_a_debite;
              }

              if ($compte_a_credite != null) {
                $cptes_substitue["cpta"]["credit"] = $compte_a_credite;
                if ($cptes_substitue["cpta"]["credit"] == NULL) {
                  $dbHandler->closeConnection(false);
                  return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
                }
              }

              if ($cpte_interne_cli_a_credite != null) {
                $cptes_substitue["int"]["credit"] = $cpte_interne_cli_a_credite;
              }

              // Passage des écritures comptables
              $myErr = passageEcrituresComptablesAuto($type_ope_inv, $montant, $comptable, $cptes_substitue, $devise, null, $id_ecriture_ope);

              if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
              }
            }
          }

          //retour du compte intermediaire vers le compte client/inst

          $id_ecriture_ope = $opeEpg["id_ecriture"];
          $type_ope = $opeEpg["type_operation"];
          $type_ope_inv = getInverseOpeAgent($opeEpg["type_operation"]);
          $compte_a_debite = $opeEpg["compte_credit"];
          $cpte_interne_cli_a_debite = $opeEpg["cpte_interne_cli_credit"];
          $compte_a_credite = $opeEpg["compte_debit"];
          $cpte_interne_cli_a_credite = $opeEpg["cpte_interne_cli_debit"];
          $montant = recupMontant($opeEpg["montant"]);
          $devise = $opeEpg["devise"];

          if ($compte_a_debite != null) {
            $cptes_substitue["cpta"]["debit"] = $compte_a_debite;
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
              $dbHandler->closeConnection(false);
              return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
            }
          }

          if ($cpte_interne_cli_a_debite != null) {
            $cptes_substitue["int"]["debit"] = $cpte_interne_cli_a_debite;
          }

          if ($compte_a_credite != null) {
            $cptes_substitue["cpta"]["credit"] = $compte_a_credite;
            if ($cptes_substitue["cpta"]["credit"] == NULL) {
              $dbHandler->closeConnection(false);
              return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
            }
          }

          if ($cpte_interne_cli_a_credite != null) {
            $cptes_substitue["int"]["credit"] = $cpte_interne_cli_a_credite;
          }

          // Passage des écritures comptables
          $myErr = passageEcrituresComptablesAuto($type_ope_inv, $montant, $comptable, $cptes_substitue, $devise, null, $id_ecriture_ope);
        }

        // Ecriture inverse pour les operation 621-624-628
        //if (in_array($opeEpg["type_operation"], array(621,624))) {
        if ($opeEpg["type_operation"] == 621 || $opeEpg["type_operation"] == 624 ){
          $cptes_substitue["int"]["credit"] = null;
          $cptes_substitue["int"]["debit"] = null;


          $id_ecriture_ope = $opeEpg["id_ecriture"];
        $type_ope = $opeEpg["type_operation"];
        $type_ope_inv = getInverseOpeAgent($opeEpg["type_operation"]);
        $compte_a_debite = $opeEpg["compte_credit"];
        $cpte_interne_cli_a_debite = $opeEpg["cpte_interne_cli_credit"];
        $compte_a_credite = $opeEpg["compte_debit"];
        $cpte_interne_cli_a_credite = $opeEpg["cpte_interne_cli_debit"];
        $montant = recupMontant($opeEpg["montant"]);
        $devise = $opeEpg["devise"];

          if ($compte_a_debite != null) {
            $cptes_substitue["cpta"]["debit"] = $compte_a_debite;
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
              $dbHandler->closeConnection(false);
              return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
            }
          }

          if ($cpte_interne_cli_a_debite != null) {
            $cptes_substitue["int"]["debit"] = $cpte_interne_cli_a_debite;
          }

          if ($compte_a_credite != null) {
            $cptes_substitue["cpta"]["credit"] = $compte_a_credite;
            if ($cptes_substitue["cpta"]["credit"] == NULL) {
              $dbHandler->closeConnection(false);
              return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
            }
          }

          if ($cpte_interne_cli_a_credite != null) {
            $cptes_substitue["int"]["credit"] = $cpte_interne_cli_a_credite;
          }

          // Passage des écritures comptables
          $myErr = passageEcrituresComptablesAuto($type_ope_inv, $montant, $comptable, $cptes_substitue, $devise, null, $id_ecriture_ope);

          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          } else {
            if ($type_ope == 50 && $type_ope_inv == 51 && $cpte_interne_cli_a_credite != null) {

              // Création dans la table des frais en attente
              $sql_insert = "INSERT INTO ad_frais_attente(id_cpte, date_frais, type_frais, montant, id_ag) VALUES ($cpte_interne_cli_a_credite, '$curr_date_frais_attente', $type_ope, $montant, $global_id_agence);";
              $result_insert = executeDirectQuery($sql_insert);

              if ($result_insert->errCode != NO_ERR){
                $dbHandler->closeConnection(false);
                return new ErrorObj($result_insert->errCode);
              }
            }
          }
        }
      }

      if (is_array($comptable) && count($comptable) > 0) {
//        if (is_array($comptable) && count($comptable) > 0) {
//          $id_his = null;
//
//          $myErr = ajout_historique ($fonc_sys_inv, $id_client, 'Gestion Annulation Retrait et Dépôt', $global_nom_login, $curr_date, $comptable, null, $id_his);
//
//          if ($myErr->errCode != NO_ERR) {
//            $dbHandler->closeConnection(false);
//            return $myErr;
//          }
//        }

        $id_his = null;

        $myErr = ajout_historique ($fonc_sys_inv, $id_client, 'Gestion Annulation Retrait et Dépôt', $global_nom_login, $curr_date, $comptable, null, $id_his);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        } else {

          $id_his = $myErr->param;
          $etat_annul = 4;

          // Mettre à jour le statut d'une demande d'annulation à Effectué
          $erreur = updateEtatAnnulationRetraitDepotViaAgent($id_demande, $etat_annul, $id_his, date("r"), "Demande annulation : Effectué");

          if ($erreur->errCode == NO_ERR) {
            $demande_count++;
          } else {
            $dbHandler->closeConnection(false);
            return $erreur;
          }
        }
      }
    }
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $demande_count);
}

function getDemandeAnnulationAgent($id_demande)
{
  global $global_id_agence;

  $sql = "SELECT * FROM ad_annulation_retrait_depot_agent WHERE id_ag = $global_id_agence AND id = $id_demande";

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

function getInverseFoncSysAgent($fonc_sys)
{
  $fonc_sys_inv = null;

  switch($fonc_sys){

    case 764: // Retrait via agent
      $fonc_sys_inv = 770; // Annulation Retrait via agent
      break;
    case 763: // Dépôt via agent
      $fonc_sys_inv = 769; // Annulation Dépôt via agent
      break;
    default:
      $fonc_sys_inv = null;
  }

  return $fonc_sys_inv;
}

function getListeOpeEpgDetailAgent($id_his)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT z.id_his,z.infos, z.id_ecriture, z.type_operation, z.info_ecriture, MAX (compte_credit) AS compte_credit, MAX(cpte_interne_cli_credit) AS cpte_interne_cli_credit, MAX (compte_debit) AS compte_debit, MAX(cpte_interne_cli_debit) AS cpte_interne_cli_debit, MAX (montant) AS montant, MAX (devise) AS devise FROM (SELECT e.id_his,CASE WHEN e.type_operation = 628 THEN  h.infos ELSE null END AS infos, e.id_ecriture, e.type_operation, e.info_ecriture, CASE WHEN sens = 'c' THEN compte END AS compte_credit, CASE WHEN sens = 'c' THEN cpte_interne_cli END AS cpte_interne_cli_credit, CASE WHEN sens = 'd' THEN compte END AS compte_debit, CASE WHEN sens = 'd' THEN cpte_interne_cli END AS cpte_interne_cli_debit, CASE WHEN sens = 'd' THEN montant END AS montant, CASE WHEN sens = 'd' THEN devise END AS devise FROM ad_ecriture e  INNER JOIN ad_his h ON h.id_his = e.id_his INNER JOIN ad_mouvement M ON M .id_ecriture = e.id_ecriture WHERE e.id_ag = $global_id_agence AND e.id_his = $id_his) z GROUP BY z.id_his,z.infos, z.id_ecriture, z.type_operation, z.info_ecriture ORDER BY z.id_ecriture DESC;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);

    return NULL;
  }

  $tmp_arr = array();

  while ($listHis = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

    $tmp_arr[$listHis['id_ecriture']] = $listHis;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}

function getInverseOpeAgent($type_ope)
{
  $type_ope_inv = null;

  switch($type_ope){

    case 621: // Retrait en espècesv via agent
      $type_ope_inv = 626;
      break;
    case 624: // depot en espece via agent
      $type_ope_inv = 625;
      break;
    case 622: // Comm agent
      $type_ope_inv = 630;
      break;
    case 623: // comm inst
      $type_ope_inv = 631;
      break;
    case 629: // impot sur comm
      $type_ope_inv = 632;
      break;
    case 628: // commission sur transaction
      $type_ope_inv = 633;
      break;
    default:
      $type_ope_inv = $type_ope;
  }

  return $type_ope_inv;
}

function hasOperationEpargneViaAgent($login)
{
  return (count(getListeOperationEpargneViaAgent($login)) > 0 ? true : false);
}

function hasDemandeAnnulationEnregistreViaAgent($login=null)
{
  return hasDemandeAnnulationViaAgent(null, 1);
}
function hasDemandeAnnulationViaAgent($login= null, $etat_annul = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_client FROM ad_annulation_retrait_depot_agent WHERE id_ag = $global_id_agence  ";

  if ($login != null){
    $sql .= " AND login = '$login' ";
  }

  $sql .= " AND to_char(date_crea, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') ";

  if ($etat_annul != 0) {
    $sql .= " AND etat_annul = $etat_annul ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return ($tmpRow[0]) ? true : false;
}
function hasDemandeAnnulationAutoriseViaAgent($login)
{
  return hasDemandeAnnulationViaAgent($login, 2);
}

function get_approvisionnement_agent($login = null,$date_min=null, $date_max = null){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT type_transaction, num_cpte_flotte, etat_appro, login_util, date_creation, login_agent, montant FROM ag_approvisionnement_transfert WHERE id_ag = $global_id_agence  ";

    if(!empty($login)){
      $sql .= " AND login_agent = '".$login."'";
    }

    if (!empty($date_min)){
      $sql .= " AND date_creation >= date('$date_min') ";
    }
    if (!empty($date_max)){
      $sql .= " AND date_creation <= date('$date_max') ";
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $agent = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($agent, $row);

    $db = $dbHandler->closeConnection(true);
    return $agent;
}
function xml_detail_transactions_agent($DATAS, $criteres) {
    global $adsys;
    $document = create_xml_doc("visualisation_transaction_agent", "visualisation_transaction_agent.dtd");

    //Element root
    $root = $document->root();
    //En-tête généraliste
    $ref = gen_header($root, 'AGB-TRA');
    //En-tête contextuel

    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $criteres);

    $transactions = $root->new_child("transactions", "");

    foreach($DATAS as $data){
        $his_data = $transactions->new_child("his_data", "");
        $his_data->new_child("date", pg2phpDate($data['date_creation']));
        $his_data->new_child("fonction", adb_gettext($adsys["type_appro_agent"][$data['type_transaction']]));
        $his_data->new_child("etat", adb_gettext($adsys["etat_appro_trans"][$data['etat_appro']]));
        $his_data->new_child("login", $data['login_util']);
        $his_data->new_child("login_initiateur", $data['login_agent']);
        $his_data->new_child("montant", afficheMontant($data['montant']));
        $his_data->new_child("num_cpte_complet", $data['num_cpte_flotte']);
    }

    return($document->dump_mem(true));
}

function getProfilAgent() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_profils WHERE is_profil_agent = 't'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $dbHandler->closeConnection(true);

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id"]] = $row["libel"];
  }
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function cree_profil_agent($nom_profil, $fonctions, $guichet, $timeout, $masque_solde, $masque_solde_vip) {
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
  $sql = "INSERT INTO adsys_profils(id, libel, guichet, timeout, access_solde, access_solde_vip, is_profil_agent) VALUES($id, '$nom_profil', '$b', $timeout, '$access_solde', '$access_solde_vip','t')"; //Crée le profil
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  update_profil_axs($id, $fonctions);

  global $global_nom_login;
  ajout_historique(777,NULL, $id, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return true;
}

function update_profil_agent($id_profil, $libel, $timeout, $conn_agc, $masque_solde, $fonctions, $masque_solde_vip) {
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
  ajout_historique(778,NULL, $id_profil, $global_nom_login, date("r"), NULL);

  $db = $dbHandler->closeConnection(true);
  return $return;
}

function commissionAgentSurNouveauxClient($data_comm,$id_his_creation=null) {

    global $dbHandler, $global_nom_login;
    global $error;

    $db = $dbHandler->openConnection();

    $info_impot = getInfoImpot();
    $cpte_intermediare = getCpteCommIntermediaire();

    $comptable = array();
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    if($info_impot['appl_impot_agent'] == 't'){
        $cptes_substitue["cpta"]["debit"] = $data_comm['cpte_comm_inst'];
        $cptes_substitue["cpta"]["credit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue["int"]["credit"] = null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($data_comm['montant_comm'], 0, $cpte_gui['devise'] );

        $myErr = passageEcrituresComptablesAuto(628, $montant, $comptable, $cptes_substitue);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        //commission impot
        $montant_comm_impot = ($info_impot['prc_import'] / 100) * $data_comm['montant_comm'];
        $cptes_substitue["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];
        $cptes_substitue["int"]["credit"] = null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($montant_comm_impot, 0, $cpte_gui['devise'] );

        $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable, $cptes_substitue);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        //commission agent
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2') ? $agent_data['cpte_base_agent'] : $agent_data['cpte_flotte_agent'];
        $mnt_comm_agent = $data_comm['montant_comm'] - $montant_comm_impot;

        $cptes_substitue["cpta"]["debit"] = $cpte_intermediare['cpte_comm_intermediaire'];
        $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? getCompteCptaProdEp($cpte_agent) : $cpte_agent;
        $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? $cpte_agent : null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($mnt_comm_agent, 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(627, $montant, $comptable, $cptes_substitue);
      
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
    }else {
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2') ? $agent_data['cpte_base_agent'] : $agent_data['cpte_flotte_agent'];


        $cptes_substitue = array();
        $comptable = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        $cptes_substitue["cpta"]["debit"] = $data_comm['cpte_comm_inst'];
        $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? getCompteCptaProdEp($cpte_agent) : $cpte_agent;
        $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? $cpte_agent : null;

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($data_comm['montant_comm'], 0, $cpte_gui['devise']);

        $myErr = passageEcrituresComptablesAuto(627, $montant, $comptable, $cptes_substitue);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
    }
    // Ajout dans l'historique
    global $global_nom_login, $global_id_client;

    $myErr = ajout_historique(32, $global_id_client, "", $global_nom_login, date("r"), $comptable);

    if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
    }
  $dbHandler->closeConnection(true);

    $id_his = $myErr->param;

    if ($id_his_creation != null){
      $db = $dbHandler->openConnection();
      $array_update=array('infos' => $id_his );
      $array_update_condi = array('id_his' => $id_his_creation);

      $sql = buildUpdateQuery("ad_his", $array_update, $array_update_condi);

      // Mise à jour de la DB
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      $dbHandler->closeConnection(true);
      return new ErrorObj(NO_ERR);

    }


    return new ErrorObj(NO_ERR, 627);
}

function getCommissionNouveauClient() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ag_commission_client";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function isProfilAgent($login) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_log g INNER JOIN adsys_profils p on p.id = g.profil WHERE p.is_profil_agent = 't' AND g.login = '$login' ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return false;
  }

  $dbHandler->closeConnection(true);

  return true;
}

function isLoginAgent() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT login FROM ad_log g INNER JOIN adsys_profils p on p.id = g.profil WHERE p.is_profil_agent = 't' ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["login"]] = $row["login"];
  }
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function count_recherche_transactions_agent($login=null, $date_min, $date_max) {
  // Fonction qui compte le nombre de transactions renvoyées par recherche_transactions
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) FROM ad_his WHERE id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(login='$login') AND  ";
  if ($date_min != NULL) $sql .= "(DATE(date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(date)<=DATE('$date_max')) AND  ";


  //remove multi agence elements
  $sql .= "(type_fonction IN (756,757,758,759)) AND  ";
  $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  '

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}
function creationClientAgent($DATA_CLI, $DATA_CPT, $paye, $ouvre_cpt_base, $versement, $nbre_parts, $nbre_parts_lib, $somme, $id_guichet, $transfert_client, $data_ext, $banque = NULL, $mnt_droits_adhesion = NULL, $nbr_membres_gs = 0, $etat_cli_bloc = null) {
    global $global_id_agence;
    global $global_id_client;
    global $global_etat_client;
    global $global_id_utilisateur;
    global $dbHandler;

    $db = $dbHandler->openConnection();

    $comptable_his = array ();

    $AG_DATA = getAgenceDatas($global_id_agence);
    // Verification de l'existence des membres du groupe solidaire
    if(empty($etat_cli_bloc) && !$etat_cli_bloc) {
        if ($DATA_CLI["statut_juridique"] == 4 && $nbr_membres_gs > 0) {
            $membres_gs = array();
            $nbr_membres_enregistres = 0;
            for ($i = 1; $i <= $nbr_membres_gs; ++$i) {
                // Si nous créons un groupe solidaire, il faut vérifier l'existence des membres (num_client$i)
                $num_client = $DATA_CLI["num_client$i"];
                unset ($DATA_CLI["num_client$i"]);
                if ($num_client != "") {
                    if (!client_exist($num_client)) {
                        $dbHandler->closeConnection(false);
                        return new ErrorObj(ERR_CLIENT_INEXISTANT, sprintf(_("Pour un des membres du groupe solidaire (id_client = %s)."), $num_client));
                    }
                    // et introduire les données nécessaire dans ad_grp_sol
                    $membres_gs["id_grp_sol"] = $DATA_CLI["id_client"];
                    $membres_gs["id_membre"] = $num_client;
                    $membres_gs["id_ag"] = $global_id_agence;
                    $result = executeQuery($db, buildInsertQuery("ad_grp_sol", $membres_gs));
                    if ($result->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $result;
                    } else {
                        $nbr_membres_enregistres++;
                    }
                }
            }
            $DATA_CLI["gi_nbre_membr"] = $nbr_membres_enregistres;
        }
    }
    $DATA_CLI["is_login_agb"] = TRUE;
    // Insertion du client dans la table ad_cli, des relations et création du compte de base
    if(!empty($etat_cli_bloc) && $etat_cli_bloc){
        $myErr = updateCliBloc($DATA_CLI, $DATA_CPT, $ouvre_cpt_base);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
    }else{
        $myErr = insere_client($DATA_CLI, $DATA_CPT, $ouvre_cpt_base);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
    }

    $id_cpte_base = $myErr->param;
    $DATA_CLI["id_cpte_base"] = $id_cpte_base;
    $numcpt = getBaseAccountID($DATA_CLI["id_client"]);
    //FIXME : changer l'appel de f°
    $PROD = getProdEpargne(getBaseProductID($global_id_agence));
    $mnt_min_cpt_base = $PROD['mnt_min'];


    if($mnt_droits_adhesion==NULL){
        $montant_frais_adhesion = getMontantDroitsAdhesion($DATA_CLI["statut_juridique"]);
    }else{
        $montant_frais_adhesion=$mnt_droits_adhesion;
    }

    /**
     * *************************************************
     * EVOLUTION SOUSCRIPTION & LIBERATION
     * Option :Paye frais adhesion et PS
     * Tranche parts sociale TRUE NON utiliser ici
     * *************************************************
     */
    if ($ouvre_cpt_base == 1) {
        if ($paye == 2) {
            if ($AG_DATA ["tranche_part_sociale"] == "t") {
                $mnt_droits_adhesion = $montant_frais_adhesion ;

                $nbre_ps_sous =$nbre_parts ;
                $montant_souscription = $nbre_parts * $AG_DATA["val_nominale_part_sociale"];
                $nbre_ps_lib =$nbre_parts_lib ;
                $montant_liberation_tranche = $somme; //montant par tranche
                $montant_part_soc_restant = $montant_souscription - $montant_liberation_tranche;

                $versement_min= $mnt_min_cpt_base + $montant_frais_adhesion + $montant_liberation_tranche;

            }else{
                $mnt_droits_adhesion = $montant_frais_adhesion ;

                $nbre_ps_sous =$nbre_parts ;
                $montant_souscription = $nbre_parts * $AG_DATA["val_nominale_part_sociale"];
                $nbre_ps_lib =$nbre_parts_lib ;
                $montant_liberation = $somme; //montant complete
                $montant_part_soc_restant = $montant_souscription - $montant_liberation;

                $versement_min= $mnt_min_cpt_base + $montant_frais_adhesion + $montant_liberation;
            }
        }else  if ($paye == 1) {
            $mnt_droits_adhesion = $montant_frais_adhesion ;
            $versement_min = $mnt_min_cpt_base + $montant_frais_adhesion ;
        }

    }//fin ouvre compte de base =1


    if ($paye != 0) { // Il y a quelque chose à payer
        //Versement initial
        if ($versement > 0) {
            $myErr = versementInitial($DATA_CLI["id_client"], $id_guichet, $versement, $comptable_his, $transfert_client, $banque);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        //perception de frais d'adhesion'
        if ($transfert_client == false && $mnt_droits_adhesion > 0) { // frais d'adhésion pour les clients non transférés
            $myErr = perceptionFraisAdhesion($DATA_CLI["id_client"], $comptable_his, $mnt_droits_adhesion);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        } else {
            // Basculer l'état du client à "auxiliaire" sans perception des frais pour le transfert client
            $sql = "UPDATE ad_cli SET etat = 2 WHERE id_ag=$global_id_agence AND id_client = " . $DATA_CLI["id_client"] . ";";
            $result = $db->query($sql);
            if (DB :: isError($result)) {
                $dbHandler->closeConnection(false);
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        }


    } //fin paye 2

    //fin il y a quelque chose à payer
    else {
        // Le client est en cours de validation, il n'a pas le droit d'accéder à son compte de base.
        $num_cpte_base = getBaseAccountID($DATA_CLI['id_client']);
        blocageCompteInconditionnel($num_cpte_base);
    }

    if ($data_ext != NULL) {
        $data_his_ext = creationHistoriqueExterieur($data_ext);
    } else {
        $data_his_ext = NULL;
    }

    // Ajout dans l'historique
    global $global_nom_login;

    $myErr = ajout_historique(762, $DATA_CLI["id_client"], "", $global_nom_login, date("r"), $comptable_his, $data_his_ext);

    if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
    }

    $id_his = $myErr->param;

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR, $id_his);
}


function count_recherche_transactions_depot_retrait_agent($login, $fonction, $num_client, $date_min, $date_max) {
  // Fonction qui compte le nombre de transactions renvoyées par recherche_transactions
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) FROM ad_his WHERE id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(login='$login') AND  ";
  if ($fonction == NULL){
    $sql .= "(type_fonction IN (763,764,762,32)) AND  ";
  } else {
    $sql .= "(type_fonction IN ($fonction)) AND  ";
  }
  if ($num_client != NULL) $sql .= "(id_client=$num_client) AND   ";
  if ($date_min != NULL) $sql .= "(DATE(date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(date)<=DATE('$date_max')) AND  ";


  //remove multi agence elements
  $sql .= "(type_fonction NOT IN (92,93, 193, 194)) AND  ";
  $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  '

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}

function recherche_transactions_agent($login, $fonction, $num_client, $date_min, $date_max) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT h.*, e.type_operation, CASE WHEN h.type_fonction = 470 THEN e.info_ecriture ELSE NULL END as info_ecriture FROM ad_his h LEFT JOIN ad_ecriture e on e.id_his = h.id_his WHERE h.id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(h.login='$login') AND  ";
  if ($fonction == NULL){
    $sql .= "(h.type_fonction IN (763,764, 786 ,762,32)) AND e.type_operation NOT IN (623, 628,629) AND ";
  } else{
    $sql .= "(h.type_fonction IN ($fonction)) AND e.type_operation NOT IN (623, 628,629) AND ";
  }
  if ($num_client != NULL) $sql .= "(h.id_client=$num_client) AND  ";
  if ($date_min != NULL) $sql .= "(DATE(h.date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(h.date)<=DATE('$date_max')) AND  ";

  //remove multi agence elements
  $sql .= "(h.type_fonction NOT IN (623, 92,93, 193, 194)) AND  ";

  $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
  $sql .= "ORDER BY h.id_his DESC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  $i = 0;

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

    //Recup les opérations financières
    $sql = "SELECT count(*) from ad_ecriture WHERE id_ag=$global_id_agence AND id_his=".$row['id_his'];
    $result2 = $db->query($sql);
    if (DB::isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result2->getMessage()
    }
    $row2 = $result2->fetchrow();

    if (($row2[0] > 0)) {
      $retour[$i] = $row;
      $retour[$i]['trans_fin'] = ($row2[0] > 0);
      ++$i;
    }
  }

  $dbHandler->closeConnection(true);

  return $retour;
}

function recherche_transactions_details_trans_agent($login, $fonction, $num_client, $date_min, $date_max) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT m.*, a.*, b.libel_jou,h.* FROM ad_his h, ad_ecriture a, ad_mouvement m, ad_journaux b WHERE h.id_ag=$global_id_agence   ";
  if ($login != NULL) $sql .= " AND (h.login='$login')  ";
  if ($fonction == NULL){
    $sql .= " AND (h.type_fonction IN (763,764, 786 ,32)) AND a.type_operation NOT IN (623, 628,629)  ";
  } else{
    $sql .= " AND (h.type_fonction IN ($fonction)) AND a.type_operation NOT IN (623, 628,629)  ";
  }
  if ($num_client != NULL) $sql .= " AND (h.id_client=$num_client) ";
  if ($date_min != NULL) $sql .= " AND (DATE(h.date)>=DATE('$date_min')) ";
  if ($date_max != NULL) $sql .= " AND (DATE(h.date)<=DATE('$date_max')) ";

  //remove multi agence elements
  $sql .= " AND (type_fonction NOT IN (623, 92,93, 193, 194)) ";

  $sql .= " AND h.id_his = a.id_his AND a.id_ecriture = m.id_ecriture AND a.id_jou = b.id_jou  ";
  $sql .= " AND h.id_ag = a.id_ag AND a.id_ag = m.id_ag AND m.id_ag = b.id_ag  ";
  $sql .= "ORDER BY h.id_his DESC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row['cpte_interne_cli'] != NULL) {
      $InfosCompte = getAccountDatas($row['cpte_interne_cli']);
      $row['cpte_interne_cli'] = $InfosCompte['num_complet_cpte'];
    }
    array_push($retour, $row);
  }
  $dbHandler->closeConnection(true);
  return $retour;
}

function xml_detail_transactions_visualisation_agent($DATAS, $criteres) {
  global $adsys;
  $document = create_xml_doc("detail_transactions", "detail_transactions.dtd");

  //Element root
  $root = $document->root();
  //En-tête généraliste
  $ref = gen_header($root, 'AGB-TRS');
  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  //Corps
  $transactions = $root->new_child("transactions", "");
  $his = array();
  while (list ($cle, $details) = each($DATAS)) {
    if(!isset($his[$details['id_his']])){
      $his[$details['id_his']] = $transactions->new_child("his_data", "");
    }
    $num_trans = $his[$details['id_his']]->new_child("num_trans", $details['id_his']);
    $date = $his[$details['id_his']]->new_child("date", $details['date']);
    $fonction = $his[$details['id_his']]->new_child("fonction", adb_gettext($adsys["adsys_fonction_systeme"][$details['type_fonction']]));
    $login = $his[$details['id_his']]->new_child("login", $details['login']);
    $num_client = $his[$details['id_his']]->new_child("num_client", $details['id_client']);
    if(!isset($ecriture[$details['id_ecriture']])){
      $ecriture[$details['id_ecriture']] = $his[$details['id_his']]->new_child("ligne_ecritures", "");
    }
    $num_ecriture = $ecriture[$details['id_ecriture']]->new_child("num_ecriture", $details['ref_ecriture']);
    $libel_ecriture = new Trad($details['libel_ecriture']);
    $libel_ecriture = $ecriture[$details['id_ecriture']]->new_child("libel_ecriture", $libel_ecriture->traduction());
    $mvmts[$details['id_mouvement']] = $ecriture[$details['id_ecriture']]->new_child("ligne_mouvements", "");
    $mvmts[$details['id_mouvement']]->new_child("compte", $details['compte']);
    $mvmts[$details['id_mouvement']]->new_child("compte_client", $details['cpte_interne_cli']);
    if($details['sens'] == 'd'){
      $mvmts[$details['id_mouvement']]->new_child("montant_debit", afficheMontant($details['montant']));
    }else{
      $mvmts[$details['id_mouvement']]->new_child("montant_credit", afficheMontant($details['montant']));
    }
  }
  return($document->dump_mem(true));

}
function getClientDatasAgent($etat_cli, $id_client = null,$id_uti=null) {
    /* Renvoie un tableau associatif avec toutes les données du client dont l'ID est $id_client
       Valeurs de retour :
       Le tableau si OK
       NULL si le client n'existe pas
       Die si erreur de la base de données
    */
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence AND etat = ".$etat_cli;

    if(!empty($id_client))
        $sql .= ' AND id_client = '.$id_client;

    if(!empty($id_uti))
      $sql .= ' AND utilis_crea = '.$id_uti;
    
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(true);
        //signalErreur(__FILE__, __LINE__, __FUNCTION__);
        echo "Erreur du fonction getClientDatas ! \n";
        exit();
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }

    $temp = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
      array_push($temp, $row);
    $dbHandler->closeConnection(true);
    return $temp;
}

function recherche_creation_client_agent($util, $date_min, $date_max) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cli WHERE is_login_agb ='t' AND id_ag = $global_id_agence";

  if(!empty($util)){
    $sql .= " AND utilis_crea = $util";
  }
  if(!empty($date_min)){
    $sql .=" AND date_creation >= '".$date_min." 00:00:00' ";
  }
  if(!empty($date_max)){
    $sql .= " AND date_creation <= '".$date_max." 23:59:59' ";
  }

  $sql .= "ORDER BY date_creation DESC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $agent = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($agent, $row);

  $db = $dbHandler->closeConnection(true);
  return $agent;
}

function getIdHisCreationClient( $id_client) {
  // Fonction qui compte le nombre de transactions renvoyées par recherche_transactions
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_his FROM ad_his WHERE type_fonction=30 AND id_client = $id_client";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}
function updateEtatCli($id_client, $etat){
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "UPDATE ad_cli SET etat = ".$etat." WHERE id_client = ".$id_client." AND id_ag = ".$global_id_agence;

    $result = $db->query($sql);
    $db = $dbHandler->openConnection();

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $dbHandler->closeConnection(true);
    return true;
}
function updateCliBloc($CLI_DATA, $CPT_DATA, $ouvre_cpt_base) {
    global $db;
    global $dbHandler;
    global $global_id_agence;
    global $global_monnaie;

    $db = $dbHandler->openConnection();

    $CLI_DATA["tmp_already_accessed"] = "t";

    // Retrait des images de $CLI_DATAS
    $IMAGES = array (
        "photo" => $CLI_DATA["photo"],
        "signature" => $CLI_DATA["signature"]
    );
    unset ($CLI_DATA["photo"]);
    unset ($CLI_DATA["signature"]);

    //Récuperer les champs supplémentaire
    if( isset($CLI_DATA["champsExtras"]) ) {
        $ChampsExtras = $CLI_DATA["champsExtras"];
        unset ($CLI_DATA["champsExtras"]);
    }
    $CLI_DATA['id_ag']= $global_id_agence;
    // Construction de la requete INSERT pour les champs texte


    //insertion des champs supplémentaire
    if(is_array($ChampsExtras) and count($ChampsExtras) >0) {
        $myErr = inseresClientChampsExtras($ChampsExtras,$CLI_DATA["id_client"]);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
    }
    // Insertion d'image
    $PATHS = imageLocationClient($CLI_DATA["id_client"]);
    foreach ($IMAGES as $imagename => $imagepath) {
        $source = $IMAGES[$imagename];

        if ($imagename == 'photo')
            $destination = $PATHS["photo_chemin_local"];
        else
            if ($imagename == 'signature')
                $destination = $PATHS["signature_chemin_local"];

        if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
            exec("rm -f ".escapeshellarg($destination));
        else {
            rename($source, $destination);
            chmod($destination, 0777);
        }
    }

    // Insertion de la personne extérieure du client
    $sql = buildInsertQuery('ad_pers_ext', array (
        'id_client' => $CLI_DATA['id_client'],'id_ag'=>$CLI_DATA['id_ag']
    ));

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    // Construction du numéro de compte du client
    $rang = '00';
    $NumCompletCompte = makeNumCpte($CLI_DATA["id_client"], $rang);

    // Rapatriement du n° de produit du compte de base
    $id_prod = getBaseProductID($global_id_agence);
    $PROD = getProdEpargne($id_prod);

    // Construction de ACCOUNT, tableau associatif contenant les données sur le compte de base du client.
    $ACCOUNT = array ();
    $ACCOUNT["id_cpte"] = getNewAccountID();
    $ACCOUNT["id_titulaire"] = $CLI_DATA["id_client"];
    $ACCOUNT["date_ouvert"] = date("d/m/Y");
    $ACCOUNT["utilis_crea"] = $CLI_DATA["utilis_crea"];
    if ($ouvre_cpt_base == 1) {
        $ACCOUNT["etat_cpte"] = 1;
    } else {
        $ACCOUNT["etat_cpte"] = 3; // Le compte est bloqué si on ne veut pas l'utiliser
    }
    $ACCOUNT["solde"] = '0';
    $ACCOUNT["mnt_bloq"] = '0';
    $ACCOUNT["mnt_bloq_cre"] = '0';
    $ACCOUNT["num_cpte"] = '0'; // C'est le premier compte du client
    $ACCOUNT["num_complet_cpte"] = $NumCompletCompte;

    // Get chosen produit epargne
    if(null !== $CPT_DATA["id_prod_epg"] && trim($CPT_DATA["id_prod_epg"]) > 0) {
        $ACCOUNT["id_prod"] = trim($CPT_DATA["id_prod_epg"]);
    } else {
        $ACCOUNT["id_prod"] = $id_prod;
    }

    // Recup les details du produit epargne
    $PROD = getProdEpargne($ACCOUNT["id_prod"]);

    $ACCOUNT["intitule_compte"] = $CPT_DATA["intitule_compte"]; // Intitulé du compte
    $ACCOUNT["devise"] = $global_monnaie;
    $ACCOUNT["type_cpt_vers_int"] = 1; // Les intérets sont versés sur le compte lui-meme
    //  infos héritées du produit
    $ACCOUNT["tx_interet_cpte"] = $PROD["tx_interet"];
    $ACCOUNT["terme_cpte"] = $PROD["terme"];
    $ACCOUNT["mode_calcul_int_cpte"] = $PROD["mode_calcul_int"];
    $ACCOUNT["freq_calcul_int_cpte"] = $PROD["freq_calcul_int"];
    $ACCOUNT["mode_paiement_cpte"] = $PROD["mode_paiement"];
    $ACCOUNT["decouvert_max"] = $PROD["decouvert_max"];
    $ACCOUNT["mnt_min_cpte"] = $PROD["mnt_min"];

    if (!creationCompte($ACCOUNT)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Création du compte de base a échoué"
    }
    $sql = "UPDATE ad_cli SET id_cpte_base = " . $ACCOUNT["id_cpte"] . " WHERE id_ag=$global_id_agence AND id_client = " . $CLI_DATA["id_client"];
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR, $ACCOUNT["id_cpte"]);
}
function getInfoImpot() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ag_param_impot";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getCpteCommIntermediaire() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT cpte_comm_intermediaire FROM ag_param_commission_institution";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function updateIdHisAgency($id_his_transaction,$id_his_commission){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $data = array(
    "infos" => $id_his_commission
  );
  $data_where = array("id_his" => $id_his_transaction);
  $result = executeQuery($db, buildUpdateQuery("ad_his", $data,$data_where));
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }else{
    $connection = true;
  }$dbHandler->closeConnection(true);

}


function getDataCpteCompta($num_cpte_compta){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ad_cpt_comptable WHERE num_cpte_comptable = '$num_cpte_compta'";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}
function xml_rapport_agent($DATA, $list_criteres, $export_csv = false) {
    /*
    fonction qui génère le code XML pour le rapport des DAT arrivant à échéance
    */
    global $global_monnaie;
    global $global_multidevise;
    global $adsys;
    $document = create_xml_doc("rapport_agent", "rapport_agent.dtd");
    //  $global_multidevise=false;
    //définition de la racine
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'AGB-RAA');

    //En-tête contextuel
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $list_criteres);
$counter = 0;
    foreach ($DATA as $value){
      $counter++;
      $account_data = getAccountDatas($value['cpte_base_agent']);
      $agent_node = $root->new_child("agent", "");
      $agent_node->new_child('counter', $counter);
      $agent_node->new_child('cpte_flotte', $value['cpte_flotte_agent']);
      $agent_node->new_child('cpte_base', $account_data['num_complet_cpte']);
      $agent_node->new_child('nom_agent', $value['nom_util']);
      $agent_node->new_child('login_agent', $value['login']);
      $agent_node->new_child('date_creation', pg2phpDate($value['date_crea']));
      $agent_node->new_child('sexe', adb_gettext($adsys["adsys_sexe"][$value['sexe']]));
      $agent_node->new_child('tel', $value['tel']);
      $agent_node->new_child('adresse', $value['adresse']);

    }

    return $document->dump_mem(true);

}
function getAgentData($etat, $date_crea,$login=null){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT login, cpte_flotte_agent, cpte_base_agent, nom||' '||prenom as nom_util, g.date_crea, sexe, tel, adresse
            FROM ad_log l
            INNER JOIN ad_uti u ON l.id_utilisateur = u.id_utilis
            INNER JOIN ad_gui g ON l.guichet = g.id_gui
            WHERE u.is_agent_ag = 't' AND g.date_crea <= date('$date_crea')";

    if(!empty($etat)){
        $sql .= " AND statut = ".$etat."";
    }
    if($login!=null){
      $sql .= " AND login = '$login'";
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $agent = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($agent, $row);

    $db = $dbHandler->closeConnection(true);
    return $agent;
}
function getAgentCommissionTransac($type_fonction, $nom_login, $date_min, $date_max){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT h.id_his, h.type_fonction, h.infos, h.login, m.montant, m.devise, e.type_operation FROM ad_his h
            INNER JOIN ad_ecriture e on e.id_his = h.id_his
            INNER JOIN ad_mouvement m ON m.id_ecriture = e.id_ecriture";

    if(!empty($type_fonction)){
        $sql .= " WHERE type_fonction = ".$type_fonction;
    }else{
        $sql .= " WHERE type_fonction IN(763, 764, 762, 774, 775)";
    }

    if(!empty($nom_login)){
        $sql .= " AND h.login = '$nom_login'";
    }
    $sql .= " AND e.type_operation IN (621,624, 160)
              AND m.sens = 'c'
              AND h.infos <> ''";


    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $transaction = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        if($row['type_fonction'] == 763 || $row['type_fonction'] == 764 || $row['type_fonction'] == 762){
            $id_his = intval($row['infos']);
        }else{
            $id_his = $row['id_his'];
        }

        $transaction[$row['login']][$row['type_operation']][$id_his]['montant'] =$row['montant'];
        $transaction[$row['login']][$row['type_operation']][$id_his]['devise'] =$row['devise'];
        if($row['type_fonction'] == 774 || $row['type_fonction'] == 775){
            $dataset = getCommissionDetailsRemote($id_his, $nom_login, $date_min, $date_max, $row['infos']);
        }else{
            $dataset = getCommissionDetails($id_his, $nom_login, $date_min, $date_max);
        }

        if(!empty($dataset)) {
            $transaction[$row['login']][$row['type_operation']][$id_his]['login'] = $row['login'];
            $transaction[$row['login']][$row['type_operation']][$id_his]['transaction'] = $dataset;
        }else{
            unset($transaction[$row['login']][$row['type_operation']][$id_his]);
            //array_pop($transaction[$row['login']][$row['type_operation']][$id_his]);
        }
    }

//    print_rn($transaction);
    $db = $dbHandler->closeConnection(true);
    return $transaction;
}
function getCommissionDetails($id_his, $nom_login, $date_min, $date_max){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "select a.montant, c.statut_juridique, c.pp_nom, c.pp_prenom, c. pm_raison_sociale, c.gi_nom, d.num_complet_cpte, h.type_fonction, cast(h.date as date), e.type_operation from ad_mouvement a 
            INNER JOIN ad_ecriture e ON a.id_ecriture = e.id_ecriture
            INNER JOIN ad_his h ON e.id_his = h.id_his
            INNER JOIN ad_cli c ON h.id_client = c.id_client
            INNER JOIN ad_cpt d ON c.id_cpte_base = d.id_cpte
            WHERE  e.id_his = $id_his
            AND e.type_operation IN (622, 623, 627)
            AND a.sens = 'c' ";


    if(!empty($nom_login)){
        $sql .= " AND h.login = '".$nom_login."'";
    }
    if(!empty($date_min)){
        $sql .=" AND h.date BETWEEN '".$date_min." 00:00:00' AND '".$date_max." 23:59:59'";
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $agent = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $agent[$row['type_operation']] = $row;
//        array_push($agent, $row);
    }

    $db = $dbHandler->closeConnection(true);
    return $agent;
}
function getCommissionDetailsRemote($id_his, $nom_login, $date_min, $date_max, $remote_meta){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "select a.montant, h.type_fonction, cast(h.date as date), e.type_operation from ad_mouvement a 
            INNER JOIN ad_ecriture e ON a.id_ecriture = e.id_ecriture
            INNER JOIN ad_his h ON e.id_his = h.id_his
            WHERE  e.id_his = $id_his
            AND e.type_operation IN (622, 623, 627)
            AND a.sens = 'c' ";

    if(!empty($nom_login)){
        $sql .= " AND h.login = '".$nom_login."'";
    }
    if(!empty($date_min)){
        $sql .=" AND h.date BETWEEN '".$date_min." 00:00:00' AND '".$date_max." 23:59:59'";
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $exploded_meta = explode(' - ', $remote_meta);
    $agc = explode('=',$exploded_meta[0]);
    $client = explode('=',$exploded_meta[1]);

    $dbc = AgenceRemote::getRemoteAgenceConnection(intval($agc[1]));
    $ClientDataObj = new Client($dbc, intval($agc[1]));
    $client_dataset = $ClientDataObj->getClientAccountData(intval($client[1]));

    $agent = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $agent[$row['type_operation']] = $row;
        $agent[$row['type_operation']]['statut_juridique'] = $client_dataset[0]['statut_juridique'];
        $agent[$row['type_operation']]['pp_nom'] = $client_dataset[0]['pp_nom'];
        $agent[$row['type_operation']]['pp_prenom'] = $client_dataset[0]['pp_prenom'];
        $agent[$row['type_operation']]['pm_raison_sociale'] = $client_dataset[0]['pm_raison_sociale'];
        $agent[$row['type_operation']]['gi_nom'] = $client_dataset[0]['gi_nom'];
        $agent[$row['type_operation']]['num_complet_cpte'] = $client_dataset[0]['num_complet_cpte'];
//        array_push($agent, $row);
    }

    $db = $dbHandler->closeConnection(true);
    return $agent;
}
function xml_rapport_commission_agent_del($DATA, $list_criteres, $export_csv = false){
    global $global_monnaie;
    global $global_multidevise, $global_nom_login;
    $document = create_xml_doc("commission_agent", "rapport_commission_agent.dtd");
    //  $global_multidevise=false;
    //définition de la racine
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'AGB-RDC');

    //En-tête contextuel
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $list_criteres);

    $synthetic_node = $root->new_child('infos_syn', '');
    $synthetic_data = array(
        "total_client" => 0,
        'total_depot' => 0,
        'total_retrait' => 0,
        'comm_agent' => 0,
        'comm_inst' => 0
    );

    $node_ref = array();
    $node_ref['devise_node'] = $synthetic_node->new_child('devise', '');
    $node_ref['depot_node'] = $synthetic_node->new_child('tot_depot', '');
    $node_ref['retrait_node'] = $synthetic_node->new_child('tot_retrait', '');
    $node_ref['comm_agent_node'] = $synthetic_node->new_child('tot_comm_agent', '');

    if(!isProfilAgent($global_nom_login)) {
        $node_ref['comm_inst_node'] = $synthetic_node->new_child('tot_comm_inst', '');
        $node_ref['comm_tot_node'] = $synthetic_node->new_child('tot_commission', '');
    }
    $node_ref['client_node'] = $synthetic_node->new_child('tot_client', '');

    //loop through each agent
    foreach($DATA as $login => $agent_dataset) {
        $agent = $root->new_child("agent", "");
        $agent->new_child("nom_agent", $login);

        //loop through each agent operation (621, 624, 160)
        foreach($agent_dataset as $type_ope => $operation_dataset) {
            $total_comm_agent = 0;
            $total_comm_inst = 0;
            $total_mnt = 0;
            $total_agent_inst_client = 0;
            $mnt_comm_agent = 0;
            $mnt_comm_inst = 0;
            $mnt_comm_client = 0;
            $depot_node = null;
            $retrait_node = null;
            $client_node = null;
            $operation_node = null;
            $row_count = 1;
            $total_comm_client = 0;
            $sub_total = 0;

            if($type_ope == '621'){
                if(empty($depot_node)){
                    $depot_node = $agent->new_child("depot", "");
                    $operation_node = $depot_node;
                }
            }else if($type_ope == '624'){
                if(empty($retrait_node)){
                    $retrait_node = $agent->new_child("retrait", "");
                    $operation_node = $retrait_node;
                }
            }else if($type_ope == '160'){
                if(empty($client_node)){
                    $client_node = $agent->new_child("client", "");
                    $operation_node = $client_node;
                }
            }

            //loop through each operation dataset
            foreach ($operation_dataset as $transaction_key => $dataset) {
                $operation_keys = array_keys($dataset);
                $key = array_keys($dataset['transaction']);
                $synthetic_data['devise'] = $dataset['devise'];

                if (array_key_exists(0, $key)) {
                    if ($key[0] == 622 || $key[0] == 627) {
                        $total_comm_agent += $dataset['transaction'][$key[0]]['montant'];
                        $synthetic_data['comm_agent'] += $dataset['transaction'][$key[0]]['montant'];
                        $mnt_comm_agent = $dataset['transaction'][$key[0]]['montant'];
                    } else if ($key[0] == 623){
                        $total_comm_inst += $dataset['transaction'][$key[1]]['montant'];
                        $synthetic_data['comm_inst'] += $dataset['transaction'][$key[1]]['montant'];
                        $mnt_comm_inst = $dataset['transaction'][$key[1]]['montant'];
                    } else{
                        //reset value if key 622 and 623 does not exist
                        $mnt_comm_agent = 0;
                        $mnt_comm_inst = 0;
                    }
                }

                if (array_key_exists(1, $key)) {
                    if ($key[1] == 623) {
                        $total_comm_inst += $dataset['transaction'][$key[1]]['montant'];
                        $mnt_comm_inst = $dataset['transaction'][$key[1]]['montant'];
                        $synthetic_data['comm_inst'] += $dataset['transaction'][$key[1]]['montant'];
                    }
                }else{
                    //reset value if key 623 does not exist
                    $mnt_comm_inst = 0;
                }

                if (array_key_exists(0, $key)) {
                    if($key[0] == 627){
                        $mnt_comm_client = $dataset['transaction'][$key[0]]['montant'];
                        $total_comm_client += $dataset['transaction'][$key[0]]['montant'];
                        $synthetic_data['total_client']++;
                    }else{
                        $mnt_comm_client = 0;
                    }
                }

                if($type_ope == 621){
                    $synthetic_data['total_depot'] += $dataset['montant'];
                }else if($type_ope == 624){
                    $synthetic_data['total_retrait'] += $dataset['montant'];
                }

                $total_mnt += $dataset['montant'];
                $sub_total = $mnt_comm_agent + $mnt_comm_inst;
                $total_agent_inst_client += $sub_total;

                $comm = $operation_node->new_child("his", "");
                $comm->new_child("num_transac", $row_count);
                $comm->new_child("date_mod", pg2phpDate($dataset['transaction'][$key[0]]['date']));
                $comm->new_child("num_complet_cpte", $dataset['transaction'][$key[0]]['num_complet_cpte']);
                switch ($dataset['transaction'][$key[0]]["statut_juridique"]) {
                    case 1 : //PP
                        $nom_cli = $dataset['transaction'][$key[0]]['pp_prenom'] . " " . $dataset['transaction'][$key[0]]['pp_nom'];
                        break;
                    case 2 : //PM
                        $nom_cli = $dataset['transaction'][$key[0]]['pm_raison_sociale'];
                        break;
                    case 3 : //GI
                        $nom_cli = $dataset['transaction'][$key[0]]['gi_nom'];
                        break;
                    case 4 : //GS
                        $nom_cli = $dataset['transaction'][$key[0]]['gi_nom'];
                        break;
                    default : //Autre
                        $nom_cli = '';
                }
                $comm->new_child("nom_client", $nom_cli);

                $comm->new_child("mnt_transac", afficheMontant($dataset['montant']));
                $comm->new_child("mnt_comm_agent", afficheMontant($mnt_comm_agent));
                if(!isProfilAgent($global_nom_login)) {
                    $comm->new_child("mnt_comm_inst", afficheMontant($mnt_comm_inst));
                    $comm->new_child("mnt_comm_agent_inst", afficheMontant($sub_total));
                }
                $row_count++;
            }
            $comm = $operation_node->new_child("his", "");
            $comm->new_child("num_transac", 'Totaux');
            $comm->new_child("date_mod", '-');
            $comm->new_child("num_complet_cpte", ' - ');
            $comm->new_child("mnt_transac", afficheMontant($total_mnt));
            $comm->new_child("mnt_comm_agent", afficheMontant($total_comm_agent));
            if(!isProfilAgent($global_nom_login)){
                $comm->new_child("mnt_comm_inst", afficheMontant($total_comm_inst));
                $comm->new_child("mnt_comm_agent_inst", $total_agent_inst_client);
            }
        }
    }

    $node_ref['devise_node']->set_content($synthetic_data['devise']);
    $node_ref['depot_node']->set_content(afficheMontant($synthetic_data['total_depot']));
    $node_ref['retrait_node']->set_content(afficheMontant($synthetic_data['total_retrait']));
    $node_ref['comm_agent_node']->set_content(afficheMontant($synthetic_data['comm_agent']));
    if(!isProfilAgent($global_nom_login)) {
        $node_ref['comm_inst_node']->set_content(afficheMontant($synthetic_data['comm_inst']));
        $node_ref['comm_tot_node']->set_content(afficheMontant($synthetic_data['comm_agent'] + $synthetic_data['comm_inst']));
    }
    $node_ref['client_node']->set_content(afficheMontant($synthetic_data['total_client']));

    return $document->dump_mem(true);
}
function xml_creation_cli_agent($DATA, $list_criteres){
    global $global_monnaie;
    global $global_multidevise;
    $document = create_xml_doc("creation_client", "visualisation_creation_client_agent.dtd");
    //  $global_multidevise=false;
    //définition de la racine
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'AGB-VCC');

    //En-tête contextuel
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $list_criteres);

    foreach ($DATA as $id_transaction => $value){
        $client = $root->new_child("client", "");
        $client->new_child("id_trans", $id_transaction);
        $client->new_child("id_client", $value['id_client']);
        $client->new_child("nom_client", $value['nom_client']);
        $client->new_child("statut_juridique", $value['statut_juridique']);
        $client->new_child("etat", $value['etat']);
        $client->new_child("nom_agent", $value['nom_agent']);
        $client->new_child("date_creation", $value['date_creation']);
    }

    return $document->dump_mem(true);
}


function getDataCreationClientAgent($date_deb = null, $date_fin = null, $util=null, $statut_jur = null, $etat_cli = null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ad_cli where is_login_agb = 't'";

  if ($date_deb !=null)
  $sql .= " AND date_crea >= date('$date_deb')";

  if ($date_fin != null)
  $sql .= " AND date_crea <= date('$date_fin')";

  if ($statut_jur != null)
    $sql .= " AND statut_juridique = $statut_jur";

  if ($etat_cli != null)
    $sql .= " AND etat = $etat_cli";

  if ($util != null)
    $sql .= "AND utilis_crea = $util";
  
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $client = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $client[$row['id_client']] = $row;
  }


  $db = $dbHandler->closeConnection(true);
  return $client;

}

function xml_rapport_creation_client_agent($DATA, $list_criteres, $export_csv = false) {
  /*
  fonction qui génère le code XML pour le rapport des DAT arrivant à échéance
  */
  global $global_monnaie;
  global $global_multidevise;
  global $adsys;
  $document = create_xml_doc("rapport_creation_client_agent", "rapport_creation_client_agent.dtd");
  //  $global_multidevise=false;
  //définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'AGB-RCA');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $list_criteres);
  $counter = 0;
  foreach ($DATA as $value){
    $counter++;
    //$account_data = getAccountDatas($value['cpte_base_agent']);
    $client_node = $root->new_child("client", "");
    $client_node->new_child('ordre', $counter);
    $client_node->new_child('num_client', $value['id_client']);
    switch ($value['statut_juridique']) {
      case 1 :
        $nom = $value['pp_nom']." ".$value['pp_prenom'];
        break;
      case 2 :
        $nom = $value['pm_raison_sociale'];
        break;
      case 3 :
        $nom = $value['gi_nom'];
        break;
      case 4 :
        $nom = $value['gi_nom'];
        break;
      default :
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Statut juridique inconnu !"
    }
    $client_node->new_child('nom_client', $nom);
    $nom_uti = getDataUtilisateur($value['utilis_crea']);
    $client_node->new_child('nom_agent', $nom_uti['nom']." ". $nom_uti['prenom']);
    if (!empty($value['login_appr_creation'])) {
      $data_login = getDatasLogin($value['login_appr_creation']);
      $nom_uti_val = getDataUtilisateur($data_login['id_utilisateur']);
      $client_node->new_child('login_validation', $nom_uti_val['nom'] . " " . $nom_uti_val['prenom']);
    }else{
      $client_node->new_child('login_validation', "");
    }
    $client_node->new_child('date_creation', pg2phpDate($value['date_creation']));

  }
  return $document->dump_mem(true);

}
function xml_rapport_commission_agent_syn($DATA, $list_criteres, $export_csv = false){
    global $global_monnaie;
    global $global_multidevise;
    $document = create_xml_doc("commission_agent", "rapport_commission_agent_syn.dtd");
    //  $global_multidevise=false;
    //définition de la racine
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'AGB-RDC');

    //En-tête contextuel
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $list_criteres);

  //loop through each agent
    foreach($DATA as $login => $agent_dataset) {
        $total_comm_agent = 0;
        $total_comm_inst = 0;
        $total_comm_client = 0;
        $total_depot = 0;
        $total_retrait = 0;
        $total_agent_inst = 0;
        $mnt_comm_agent = 0;
        $mnt_comm_inst = 0;
        $mnt_comm_client = 0;
        $client_amount = 0;
        $devise = 0;
        $agent = $root->new_child("agent", "");
        $agent->new_child("nom_agent", $login);

        //loop through each agent operation (621, 624, 160)
        foreach($agent_dataset as $type_ope => $operation_dataset) {
            //loop through each operation dataset
            foreach ($operation_dataset as $transaction_key => $dataset) {
                $operation_keys = array_keys($dataset);
                $key = array_keys($dataset['transaction']);
                $devise = $dataset['devise'];

                if (array_key_exists(0, $key)) {
                    if ($key[0] == 622 || $key[0] == 627) {
                        $total_comm_agent += $dataset['transaction'][$key[0]]['montant'];
                        $mnt_comm_agent = $dataset['transaction'][$key[0]]['montant'];
                    } else if ($key[0] == 623){
                        $total_comm_inst += $dataset['transaction'][$key[1]]['montant'];
                        $mnt_comm_inst = $dataset['transaction'][$key[1]]['montant'];
                    } else{
                        //reset value if key 622 and 623 does not exist
                        $mnt_comm_agent = 0;
                        $mnt_comm_inst = 0;
                    }
                }

                if (array_key_exists(1, $key)) {
                    if ($key[1] == 623) {
                        $total_comm_inst += $dataset['transaction'][$key[1]]['montant'];
                        $mnt_comm_inst = $dataset['transaction'][$key[1]]['montant'];
                    }
                }else{
                    //reset value if key 623 does not exist
                    $mnt_comm_inst = 0;
                }

                if (array_key_exists(0, $key)) {
                    if($key[0] == 627){
                        $mnt_comm_client = $dataset['transaction'][$key[0]]['montant'];
                        $total_comm_client += $dataset['transaction'][$key[0]]['montant'];
                        $client_amount++;
                    }else{
                        $mnt_comm_client = 0;
                    }
                }

                if($type_ope == 621){
                    $total_depot += $dataset['montant'];
                }else if($type_ope == 624){
                    $total_retrait += $dataset['montant'];
                }

                $total_agent_inst += $mnt_comm_agent + $mnt_comm_inst;
            }
        }
        $agent->new_child('nom_agent',  $login);
        $agent->new_child('devise', $devise);
        $agent->new_child('total_mnt_depot', afficheMontant($total_depot));
        $agent->new_child('total_mnt_retrait', afficheMontant($total_retrait));
        $agent->new_child('total_mnt_comm_agent', afficheMontant($total_comm_agent));
        $agent->new_child('total_mnt_comm_inst', afficheMontant($total_comm_inst));
        $agent->new_child('total_mnt_comm_agent_inst', afficheMontant($total_agent_inst));
//        $agent->new_child('total_mnt_comm_client', $total_comm_client);
        $agent->new_child('total_client', afficheMontant($client_amount));
    }
    return $document->dump_mem(true);
}

function getGuichetAgent($login = null) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_gui g INNER JOIN ad_log l ON l.guichet = g.id_gui INNER JOIN ad_uti u ON u.id_utilis = l.id_utilisateur WHERE u.is_agent_ag = 't'";
  if ($login != null){
    $sql .= " AND l.login = '$login'";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $dbHandler->closeConnection(true);

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id_gui"]] = $row['libel_gui'];
  }
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getDataUtilFromLogin(){
  global $dbHandler,$global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_uti u INNER JOIN ad_log l ON l.id_utilisateur = u.id_utilis WHERE l.login = '$global_nom_login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,"DB: ".$result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id_utilis"]] = $row['nom']." ".$row['prenom'];
  }
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function xml_brouillard_caisse_agent($GDATA, $date, $export_csv = false) {
  global $global_multidevise, $global_monnaie, $adsys;
  reset($GDATA);
  $document = create_xml_doc("brouillard_caisse", "brouillard_caisse.dtd");
  $root = $document->root();
  gen_header($root, 'AGB-BDC', " : $date");

  foreach ($GDATA as $guichet => $DATAS) {
    foreach ($DATAS as $devise => $DATA) {
      $brouillard_devise = $root->new_child("brouillard_devise", "");
      $brouillard_devise->set_attribute("devise", $devise);
      $brouillard_devise->set_attribute("guichet", $guichet);
      setMonnaieCourante($devise);
      $infos_globales = $brouillard_devise->new_child("infos_globales", "");
      $infos_globales->new_child("libel_gui", $DATA["cpte_flotte_agent"]);
//      $infos_globales->new_child("libel_gui", $DATA["libel_gui"]);
      $infos_globales->new_child("nom_uti", $DATA["utilisateur"]);
      $infos_globales->new_child("encaisse_deb", afficheMontant($DATA["encaisse_debut"], false, $export_csv));
      //$infos_globales->new_child("encaisse_fin", afficheMontant($DATA["encaisse_fin"], true));
      $infos_globales->new_child("encaisse_fin", afficheMontant($DATA["encaisse_fin"], false, $export_csv));
      // Ajout des infos globales
      $resume_transactions = $infos_globales->new_child("resume_transactions", "");

      while (list ($key, $infos) = each($DATA["global"])) {
        $ligne_resume_transactions = $resume_transactions->new_child("ligne_resume_transactions", "");
        $libel_operation_trad = new Trad($infos["libel_operation"]);
        $libel_operation = $libel_operation_trad->traduction();
        $ligne_resume_transactions->new_child("libel_operation", $libel_operation);
        $ligne_resume_transactions->new_child("nombre", $infos["nombre"]);
        $ligne_resume_transactions->new_child("montant_debit", $infos["montant_debit"]);
        $ligne_resume_transactions->new_child("montant_credit", $infos["montant_credit"]);
        $ligne_resume_transactions->set_attribute("total", "0");
      }
      // Ajout de la ligne pour les totaux
      $infos = $DATA["total"];
      $ligne_resume_transactions = $resume_transactions->new_child("ligne_resume_transactions", "");
      $ligne_resume_transactions->new_child("libel_operation", "TOTAL");
      $ligne_resume_transactions->new_child("nombre", $infos["nombre"]);
      $ligne_resume_transactions->new_child("montant_debit", $infos["montant_debit"]);
      $ligne_resume_transactions->new_child("montant_credit", $infos["montant_credit"]);
      $ligne_resume_transactions->set_attribute("total", "1");
      // Ajout des infos détaillées
      if (is_array($DATA["details"])) {
        $detail = $brouillard_devise->new_child("detail", "");
        while (list ($key, $infos) = each($DATA["details"])) {
          $libel_operation_trad = new Trad($infos["libel_operation"]);
          $libel_operation = $libel_operation_trad->traduction();
          $id_client = makeNumClient($infos["id_client"]);
          $nom_client = mb_substr($infos["client"], 0, 22, "UTF-8");

          // Multi agence fix
          if($libel_operation=="Retrait en déplacé" || $libel_operation=="Dépôt en déplacé")
          {
            $c_id_his = (int)$infos["id_his"];

            $his_data = getHistoriqueDatas(array('id_his' => $c_id_his));

            if(is_array($his_data) && count($his_data)==1)
            {
              $id_client = "";
              $nom_client = trim($his_data[$c_id_his]["infos"]);
            }
            else
            {
              $id_client = "";
              $nom_client = "Client extérieur";
            }
          }

          if(in_array($infos['type_operation'], $adsys["adsys_operation_cheque_infos"]) ){
            $libel_operation = getChequeno($infos["id_his"],$libel_operation,$infos['info_ecriture']);
          }

          $ligne_detail = $detail->new_child("ligne_detail", "");
          $ligne_detail->new_child("num_trans", $infos["id_his"]);
          $ligne_detail->new_child("num_piece", $infos["num_piece"]);
          $ligne_detail->new_child("heure", $infos["heure"]);
          $ligne_detail->new_child("libel_operation", mb_substr($libel_operation, 0, 50, "UTF-8"));
          $ligne_detail->new_child("id_client", $id_client);
          $ligne_detail->new_child("nom_client", $nom_client);
          $ligne_detail->new_child("montant_debit", $infos["montant_debit"]);
          $ligne_detail->new_child("montant_credit", $infos["montant_credit"]);
          $mnt_total = recupMontant($infos["encaisse"]);
          $mnt_total = abs($mnt_total);
          $ligne_detail->new_child("encaisse", afficheMontant($mnt_total));
        }
      }
    }
  }
  return $document->dump_mem(true);
}

function getBrouillardCaisseAgent($guichet, $date, $details, $devise, $export_csv = false) {
  $DATA = array ();
  if (isset ($devise)) {
    $DATA_DEV = getBrouillardCaisseDeviseAgent($guichet, $date, $details, $devise, $export_csv);
    $DATA[$devise] = $DATA_DEV;
  } else {
    $DEVS = get_table_devises();
    foreach ($DEVS as $code_devise => $DEV) {
      $DATA_DEV = getBrouillardCaisseDeviseAgent($guichet, $date, $details, $code_devise, $export_csv);
      $DATA[$code_devise] = $DATA_DEV;
    }
  }
  return $DATA;
}

function getBrouillardCaisseDeviseAgent($guichet, $date, $details, $devise, $export_csv = false)	// Fonction renvoyant toutes les données utilse pour la génération du  rapport de brouillard de caisse
// IN : $guichet = Le numéro du guichet
//      $date = Date du brouillard
//      $details = true  ==> Récupérer le détail des transactions
//                 false ==> Ne pas récupérer le détail des transactions
//      $devise = Devise du brouillard
// OUT: $DATA contient tous les éléments dans deux parties
//        ["global"] pour les infos globales
//        ["details"] pour les infos détaillées si $details = true
{
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  global $adsys;
  $DATA = array ();
  $infos_gui = array ();
  $infos_gui = get_guichet_infos($guichet);
  $DATA['libel_gui'] = $infos_gui["libel_gui"];
  $login = getLoginFromGuichet($guichet);
  $id_uti = get_login_utilisateur($login);
  $DATA['utilisateur'] = get_utilisateur_nom($id_uti);
  if ($global_multidevise)
    $cpte_associe = $infos_gui["cpte_cpta_gui"] . ".$devise";
  else
    $cpte_associe = $infos_gui["cpte_cpta_gui"];
  // Recherche des encaisses
  // Recherche de la date correspondant à 1 jour avant la date $date
  $hier = hier($date);
  setMonnaieCourante($devise);
  $encDeb = abs(calculSolde($cpte_associe, $hier, false));
  $encFin = abs(calculSolde($cpte_associe, $date, false));
  $DATA['encaisse_debut'] = $encDeb;
  $DATA['encaisse_fin'] = $encFin;
  // Recherches de la synthèse des opérations
  $sql = "SELECT distinct m.sens, e.libel_ecriture, count(e.id_his) AS nombre, sum(m.montant) AS montant FROM ad_mouvement m, ad_ecriture e WHERE e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND date(e.date_comptable) = '$date' AND m.id_ecriture = e.id_ecriture AND compte = '$cpte_associe' GROUP BY m.sens, e.libel_ecriture";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $GLOBAL_INFOS = array ();
  // Initialisation des totaux
  $total_nombre = 0;
  $total_debit = 0;
  $total_credit = 0;
  // Pour chaque type d'opération ...
  while ($infos = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $montant = $infos["montant"];
    $total_nombre += $infos["nombre"];
    if ($infos["sens"] == "d")
      $total_debit += $montant;
    else
      $total_credit += $montant;
    $infos["libel_operation"] = $infos["libel_ecriture"];
    $infos["montant"] = afficheMontant($infos["montant"], false, $export_csv);
    if ($infos["sens"] == "d") {
      $infos["montant_debit"] = afficheMontant($montant, false, $export_csv);
    } else {
      $infos["montant_credit"] = afficheMontant($montant, false, $export_csv);
    }
    array_push($GLOBAL_INFOS, $infos);
  }
  $DATA["global"] = $GLOBAL_INFOS;
  // Ajout des totaux dans le tableau
  $total_infos = array (
      "libel_operation" => "TOTAL",
      "nombre" => $total_nombre,
      "montant_debit" => afficheMontant($total_debit,
          true
      ), "montant_credit" => afficheMontant($total_credit, true));
  $DATA["total"] = $total_infos;
  // Récupérations des infos détaillées
  if ($details) {
    $DETAILS = array ();
    $DETAILS1 = array ();
//   $sql = "SELECT h.id_his, he.num_piece, h.id_client, e.libel_ecriture, h.date, m.sens, m.montant FROM ad_his h, ad_his_ext he, ad_ecriture e, ad_mouvement m WHERE h.id_ag = he.id_ag AND he.id_ag = e.id_ag AND e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND m.compte = '$cpte_associe' AND h.id_his=e.id_his AND he.id=h.id_his_ext AND date(e.date_comptable) = '$date' AND m.id_ecriture = e.id_ecriture ORDER BY h.date";
    $sql = "SELECT h.id_his, h.id_his_ext, h.id_client, e.libel_ecriture, h.date, m.sens, m.montant,e.type_operation,e.info_ecriture FROM ad_his h, ad_ecriture e, ad_mouvement m WHERE h.id_ag = e.id_ag AND e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND m.compte = '$cpte_associe' AND h.id_his=e.id_his AND date(e.date_comptable) = '$date' AND m.id_ecriture = e.id_ecriture ORDER BY h.date";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $encCour = $encDeb;
    while ($infos = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if(isset ($infos["id_his_ext"])){
        // Recuperer le numéro de la pièce comptable
        $WHERE['id'] = $infos["id_his_ext"];
        $sql = buildSelectQuery('ad_his_ext', $WHERE);
        $result_his_ext = $db->query($sql);
        if (DB::isError($result_his_ext)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $row = $result_his_ext->fetchrow();
        $infos["num_piece"] = $row[4];
      }
      if (isset ($infos["id_client"]))
        $infos["client"] = getClientName($infos["id_client"]);
      $infos["id_his"] = sprintf("%09d", $infos["id_his"]);
      if ($infos["id_client"] != '')
        $infos["id_client"] = sprintf("%06d", $infos["id_client"]);
      $infos["libel_operation"] = $infos["libel_ecriture"];
      $infos["heure"] = pg2phpTime($infos["date"]);
      $montant = $infos["montant"];
      if ($infos["sens"] == "d") {
        $encCour -= $montant;
        $infos["montant"] = afficheMontant($infos["montant"], false, $export_csv);
        $infos["montant_debit"] = $infos["montant"];
      } else {
        $encCour += $montant;
        $infos["montant"] = afficheMontant($infos["montant"], false, $export_csv);
        $infos["montant_credit"] = $infos["montant"];
      }
      // Encaisse courante
      $infos["encaisse"] = afficheMontant($encCour, false, $export_csv);
      $DETAILS[] = $infos;
    }
    $DATA["details"] = $DETAILS;
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}

function updateOperationCompta($type_ope,$sens,$num_cpte){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ad_cpt_ope_cptes WHERE type_operation = $type_ope AND sens = '$sens'";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0){
    $db1 = $dbHandler->openConnection();
    $array_insert = array('type_operation' => $type_ope, 'num_cpte' => $num_cpte, 'sens' => $sens, 'categorie_cpte' => 0, 'id_ag' => $global_id_agence);
    $result1 = executeQuery($db1, buildInsertQuery("ad_cpt_ope_cptes", $array_insert));
    if (DB::isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
    }
    $dbHandler->closeConnection(true);
  } else{
    $db2 = $dbHandler->openConnection();
    $array_update = array('num_cpte' => $num_cpte, 'categorie_cpte' => 0);
    $array_where = array('type_operation' => $type_ope, 'sens' => $sens);
    $result2 = executeQuery($db2, buildUpdateQuery("ad_cpt_ope_cptes", $array_update, $array_where));
    if (DB::isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result2->getMessage());
    }else{
      $dbHandler->closeConnection(true);
    }
  }
  $dbHandler->closeConnection(true);
  return true;
}
function getCommissionHist($type_comm){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ag_commission_hist WHERE type_comm = ".$type_comm."ORDER BY date_creation ASC";
    $result=$db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }

    $comm_hist = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        array_push($comm_hist, $row);
    }

    $dbHandler->closeConnection(true);
    return $comm_hist;
}

function xml_commission_historic($DATA, $list_criteres){
    global $global_monnaie;
    global $global_multidevise;
    $document = create_xml_doc("commission_hist", "commission_historique.dtd");
    //  $global_multidevise=false;
    //définition de la racine
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'AGB-RCH');

    //En-tête contextuel
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $list_criteres);

    //access each commission type
    foreach($DATA as $type_comm_key => $type_comm_dataset){
        //access each commission by date
        $comm_type = ($type_comm_key == 1)?'retrait':(($type_comm_key == 2)?'depot':'client');
        $comm_node = $root->new_child($comm_type, "");
        foreach($type_comm_dataset as $date_key => $commissions){
            //access each commission historic
            $type_comm = ($type_comm_key == 1)?'retrait':(($type_comm_key == 2)?'Dépôt':'nouveaux client');
            $his = $comm_node->new_child('his', '');
            $his->new_child('date_mod', $date_key);
            $his->new_child('type_comm', $type_comm);
            foreach($commissions as $historic){
                $palier = $his->new_child('palier', '');
                if($type_comm_key != 3) {
                    $palier->new_child('id_palier', $historic['id_palier']);
                    $palier->new_child('mnt_min', afficheMontant($historic['mnt_min']));
                    $palier->new_child('mnt_max', afficheMontant($historic['mnt_max']));
                    $palier->new_child('comm_agent_prc', empty($historic['comm_agent_prc'])?' - ':afficheMontant($historic['comm_agent_prc']));
                    $palier->new_child('comm_agent_mnt', empty($historic['comm_agent_mnt'])?' - ':afficheMontant($historic['comm_agent_mnt']));
                    $palier->new_child('comm_inst_prc', empty($historic['comm_inst_prc'])?' - ':afficheMontant($historic['comm_inst_prc']));
                    $palier->new_child('comm_inst_mnt', empty($historic['comm_inst_mnt'])?' - ':afficheMontant($historic['comm_inst_mnt']));
                    $palier->new_child('comm_tot_prc', afficheMontant($historic['comm_tot_prc']));
                    $palier->new_child('comm_tot_mnt', afficheMontant($historic['comm_tot_mnt']));
                }
                else{
                    $palier->new_child('date_creation', pg2phpDate($historic['date_creation']));
                    $palier->new_child('comm_agent_prc', afficheMontant($historic['montant_comm']));
                }
            }
        }
    }
    return $document->dump_mem(true);
}
function generateCommissionVersion($type_comm, $type_transaction){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $commission = getCommission($type_comm);
    unset($commission['counter']);
    $comm_json = serialize($commission);

    $array_insert = array('date_creation' => date('d-m-Y'), 'version_set' => $comm_json, 'type_comm' => $type_comm, 'type_transaction' => $type_transaction);
    $result = executeQuery($db, buildInsertQuery("ag_comm_hist_resumer", $array_insert));
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }
    $dbHandler->closeConnection(true);

    return true;
}
function getCommissionVersion($date_min, $date_max, $type_comm){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ag_comm_hist_resumer 
            WHERE date_creation BETWEEN '".$date_min." 00:00:00' AND '".$date_max." 23:59:59' ";

    if(!empty($type_comm)){
      $sql .= "AND type_comm = ".$type_comm;
    }

    $sql .= " ORDER BY date_creation ASC";

    $result=$db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $comm_hist = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        array_push($comm_hist, $row);
    }

    $dbHandler->closeConnection(true);
    return $comm_hist;
}
?>


