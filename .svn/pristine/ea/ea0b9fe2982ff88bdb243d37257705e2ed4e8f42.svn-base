<?php

/*require_once 'functions.php';
require_once 'Erreur.php';
require_once 'divers.php';
//require_once 'VariablesGlobales.php';*/
//require_once 'defection_client_par_lot.php';
//require_once 'lib/misc/VariablesGlobales.php';
//require_once 'Erreurs_defection.php';
//require_once 'DB.php';
require_once '/usr/share/adbanking/web/ad_acu/app/erreur.php';

global $global_id_exo,$global_multidevise,$global_id_agence,$dbHandler;
//$value = getGlobalDatas();
//$global_id_exo = $value['exercice'];
//$global_multidevise = $value['multidevise'];
//$global_id_agence = getNumAgence();


/*--------------------------------passage ecriture comptable auto--------------------------------------*/
function passageEcrituresComptablesAuto($type_oper, $montant, &$comptable_his, $array_cptes=NULL, $devise=NULL, $date_compta=NULL,$info_ecriture=NULL,$infos_sup=NULL) {

  global $dbHandler;
  global $global_id_exo;
  global $global_multidevise;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $mouvements = array();
  //verifier s'il faut substituer des comptes
  if (isset($array_cptes)) {
    //FIXME : verifier que le vecteur a au plus 2 lignes (1 debit et 1 credit)
    //lire chaque element du vecteur

    foreach ($array_cptes as $key=>$value) {
      //prendre les comptes a substituer
      if ($key == "cpta") { //il existe des comptes comptables a substituer
        foreach ($value as $key2=>$value2)
          if ($key2 == "debit")
            $cpte_debit_sub = $value2;
          elseif ($key2 == "credit")
            $cpte_credit_sub = $value2;
      }

      if ($key == "int") { //il existe des comptes internes a renseigner
        foreach ($value as $key2=>$value2){
          if ($key2 == "debit"){
            $cpte_int_debit = $value2;
          }
          elseif ($key2 == "credit"){
            $cpte_int_credit = $value2;
          }
        }
      }
    }
  }

  //FIXME : gérer les frais en attente


  //Recuperer les infos sur l'operation

  $InfosOperation = array();
  $MyError = getOperations($type_oper);
  if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
    $dbHandler->closeConnection(false);
    return $MyError;
  } else {
    $InfosOperation = $MyError->param;
  }

  // comptes au débit et crédit
  $DetailsOperation = array();

  $MyError = getDetailsOperation($type_oper);
  if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
    $dbHandler->closeConnection(false);
    return $MyError;
  } else {
    $DetailsOperation = $MyError->param;
  }

  // Recherche du dernier élément du tableau

  end ($comptable_his);
  $tmparr = current($comptable_his);
  $last_libel_oper = $tmparr["libel"];

  if ($last_libel_oper == $type_oper)
    $newID = getLastIdOperation($comptable_his);
  else
    $newID = getLastIdOperation($comptable_his)+1;

  //Changer le libellé de l'opération, si autre libellé
  if ($infos_sup["autre_libel_ope"] != NULL)
    $InfosOperation["libel"] = $infos_sup["autre_libel_ope"];

  //FIXME : ici ça marche parce qu'on a 1 débit et 1 crédit
  $comptable = array();

  // Choix du journal ,cela va dependre des comptes au débit et au crédit

  //Compte comptable à debiter

  if (isset($cpte_debit_sub))
    $cpte_debit = $cpte_debit_sub;
  else
    $cpte_debit = $DetailsOperation["debit"]["compte"];

  // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
  if (!isset($cpte_debit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au débit de l'opération %s"), $type_oper));
  }

  //Compte comptable à crediter
  if (isset($cpte_credit_sub))
    $cpte_credit = $cpte_credit_sub;
  else
    $cpte_credit = $DetailsOperation["credit"]["compte"];

  // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
  if (!isset($cpte_credit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au crédit de l'opération %s"), $type_oper));
  }

  // Si multidevise, vérifie que l'écriture peut avoir lieu
  if ($global_multidevise) {
    if ($devise == NULL) { // Par defaut la devise de reference est utilisee
      global $global_monnaie;
      $devise = $global_monnaie;
    }
    $cpte_debit_dev = checkCptDeviseOK($cpte_debit, $devise);
    if ($cpte_debit_dev == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DEVISE_CPT, _("Devise")." : $devise, "._("compte debit")." : $cpte_debit");
    }
    $cpte_credit_dev = checkCptDeviseOK($cpte_credit, $devise);
    if ($cpte_credit_dev == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DEVISE_CPT, _("Devise")." : $devise, "._("compte")." : $cpte_credit");
    }

    $cpte_debit = $cpte_debit_dev;
    $cpte_credit = $cpte_credit_dev;

    // Vérifie également que les comptes internes associés s'ils existent sont dans la bonne devise
    if (isset($cpte_int_debit)) {
      $ACC = getAccountDatas($cpte_int_debit);
      if ($ACC["devise"] != $devise) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise")." : $devise, "._("opération")." : $type_oper");
      }
    }
    if (isset($cpte_int_credit)) {
      $ACC = getAccountDatas($cpte_int_credit);
      if ($ACC["devise"] != $devise) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise")." : $devise, "._("opération")." : $type_oper");
      }
    }
  } else { // En mode unidevise, la devise est toujours la devise de référence
    global $global_monnaie;
    $devise = $global_monnaie;
  }

  // On ne mouvemente pas un compte centralisateur
  if (isCentralisateur($cpte_debit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPT_CENTRALISE, _("compte")." : $cpte_debit");
  }

  if (isCentralisateur($cpte_credit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPT_CENTRALISE, _("compte")." : $cpte_credit");
  }
  $jou_cpte_debit = getJournalCpte($cpte_debit);
  $jou_cpte_credit = getJournalCpte($cpte_credit);

  $id_exo = "";

  if(is_array($infos_sup) && count($infos_sup) > 0 && array_key_exists('id_exo', $infos_sup)){
    $id_exo = $infos_sup['id_exo'];
  }

  $exo_encours = "";
  $date_fin = "";
  $date_debut = "";

  // la date comptable doit être dans la période de l'exercice en cours à cours
  if(empty($id_exo)) {
    $exo_encours = getExercicesComptables($global_id_exo);
    $date_debut = pg2phpDate($exo_encours[0]["date_deb_exo"]); // date debut exo ou max date prov
    $date_fin = pg2phpDate($exo_encours[0]["date_fin_exo"]);   // date hier
  }
  else { // ou dans les bornes fournis en parametres
    $exo_encours = $id_exo;
    $date_debut = $infos_sup['date_debut'];
    $date_fin = $infos_sup['date_fin'];
  }

  // date comptable
  if ($date_compta == NULL) {
    $date_comptable = date("d/m/Y"); // date du jour
    $agence = 'numagc()';
    $data_agc =getDataAgence($agence);
    $id_ag = $data_agc['id_ag'];
    if (isAfter($date_comptable, $date_fin))
      $date_comptable = pg2phpDate(get_last_batch($id_ag));
  } else
    $date_comptable = $date_compta;

  if ( (isAfter($date_debut, $date_comptable)) or (isAfter($date_comptable, $date_fin))) {
    $dbHandler->closeConnection(false);
    $msg = ". La date n'est pas dans la période de l'exercice.";
    if(!empty($id_exo)) {
      $msg = ". La date n'est pas valide.";
    }
    return new ErrorObj(ERR_DATE_NON_VALIDE, $msg);
  }
  //echo "Journal au débit : $jou_cpte_debit et journal au crédit : $jou_cpte_credit<BR>";
  if (($jou_cpte_debit["id_jou"] != $jou_cpte_credit["id_jou"]) &&  ($jou_cpte_debit["id_jou"] > 1) && ($jou_cpte_credit["id_jou"] > 1) ) {
    //Utilisation d'un compte de liaison
    $InfosOperation["jou_debit"] = $jou_cpte_debit ["id_jou"];
    $InfosOperation["jou_credit"] = $jou_cpte_credit ["id_jou"];
    $temp1 = $jou_cpte_debit["id_jou"];
    $temp2 = $jou_cpte_credit["id_jou"];

    //Recuperation du compte de liaison

    $temp["id_jou1"] = $temp1;
    $temp["id_jou2"] = $temp2;

    $temp_liaison = getJournauxLiaison($temp);

    if (count($temp_liaison ) != 1 ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_PAS_CPTE_LIAISON);
    }
    $cpte_liaison = $temp_liaison[0]["num_cpte_comptable"];

    // Passages écritures du compte debit au compte de liaison
    $comptable[0]["id"] = $newID;
    $comptable[0]["compte"] = $cpte_debit;
    if (isset($cpte_int_debit))
      $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
    else
      $comptable[0]["cpte_interne_cli"] = NULL;

    $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[0]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
    $comptable[0]["sens"] = SENS_DEBIT;
    $comptable[0]["montant"] = $montant;
    $comptable[0]["date_comptable"] = $date_comptable;
    $comptable[0]["libel"] = $InfosOperation["libel"];
    $comptable[0]["jou"] = $InfosOperation["jou_debit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[0]["validation"] = 't';
    $comptable[0]["devise"] = $devise;
    $comptable[0]["info_ecriture"] = $info_ecriture;

    $comptable[1]["id"] = $newID;
    $comptable[1]["compte"] = $cpte_liaison;
    $comptable[1]["cpte_interne_cli"] = NULL;
    $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[1]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
    $comptable[1]["sens"] = SENS_CREDIT;
    $comptable[1]["montant"] = $montant;
    $comptable[1]["date_comptable"] = $date_comptable;
    $comptable[1]["libel"] = $InfosOperation["libel"];
    $comptable[1]["jou"] = $InfosOperation["jou_debit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[1]["validation"] = 't';
    $comptable[1]["devise"] = $devise;
    $comptable[1]["info_ecriture"] = $info_ecriture;


    // Passages ecritures du compte credit au compte de liaison

    $newID++;
    $comptable[2]["id"] = $newID;
    $comptable[2]["compte"] = $cpte_liaison;
    $comptable[2]["cpte_interne_cli"] = NULL;
    $comptable[2]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[2]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
    $comptable[2]["sens"] = SENS_DEBIT;
    $comptable[2]["montant"] = $montant;
    $comptable[2]["date_comptable"] = $date_comptable;
    $comptable[2]["libel"] = $InfosOperation["libel"];
    $comptable[2]["jou"] = $InfosOperation["jou_credit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[2]["validation"] = 't';
    $comptable[2]["devise"] = $devise;
    $comptable[2]["info_ecriture"] = $info_ecriture;

    $comptable[3]["id"] = $newID;
    $comptable[3]["compte"] = $cpte_credit;
    if (isset($cpte_int_credit))
      $comptable[3]["cpte_interne_cli"] = $cpte_int_credit;
    else
      $comptable[3]["cpte_interne_cli"] = NULL;
    $comptable[3]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[3]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
    $comptable[3]["sens"] = SENS_CREDIT;
    $comptable[3]["montant"] = $montant;
    $comptable[3]["date_comptable"] = $date_comptable;
    $comptable[3]["libel"] = $InfosOperation["libel"];
    $comptable[3]["jou"] = $InfosOperation["jou_credit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[3]["validation"] = 't';
    $comptable[3]["devise"] = $devise;
    $comptable[3]["info_ecriture"] = $info_ecriture;
  }

  else {//Ici, on choisit le journal dont l'id > journal principal si un des comptes est associé à ce journal
    $InfosOperation["jou"] = max($jou_cpte_debit ["id_jou"],$jou_cpte_credit ["id_jou"]);

    $comptable[0]["id"] = $newID;
    $comptable[0]["compte"] = $cpte_debit;
    if (isset($cpte_int_debit))
      $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
    else
      $comptable[0]["cpte_interne_cli"] = NULL;
    $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
    //$comptable[0]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
    $comptable[0]["sens"] = SENS_DEBIT;
    $comptable[0]["montant"] = $montant;
    $comptable[0]["date_comptable"] = $date_comptable;
    $comptable[0]["libel"] = $InfosOperation["libel"];
    $comptable[0]["jou"] = $InfosOperation["jou"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[0]["validation"] = 't';
    $comptable[0]["devise"] = $devise;
    $comptable[0]["info_ecriture"] = $info_ecriture;

    $comptable[1]["id"] = $newID;
    $comptable[1]["compte"] = $cpte_credit;
    if (isset($cpte_int_credit))
      $comptable[1]["cpte_interne_cli"] = $cpte_int_credit;
    else
      $comptable[1]["cpte_interne_cli"] = NULL;
    $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
    //$comptable[1]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
    $comptable[1]["sens"] = SENS_CREDIT;
    $comptable[1]["montant"] = $montant;
    $comptable[1]["date_comptable"] = $date_comptable;
    $comptable[1]["libel"] = $InfosOperation["libel"];
    $comptable[1]["jou"] = $InfosOperation["jou"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[1]["validation"] = 't';
    $comptable[1]["devise"] = $devise;
    $comptable[1]["info_ecriture"] = $info_ecriture;
  }

  $comptable_his = array_merge($comptable_his, $comptable);

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}

/*-------------------------------------Fin passage ecriture comptable auto---------------------------------------------*/
/*-------------------------------------Functions associer au passage ecriture comptable auto---------------------------*/

function isCentralisateur($num_cpte) {
  global $global_id_agence;
  $sql = "SELECT COUNT(*) FROM ad_cpt_comptable where cpte_centralise ='$num_cpte'  ";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] > 0);
  }
}
/**
 * Renvoie un tableau associatif avec toutes les données du compte
 *
 * Les données retournées sont une synthèse cumulative de celles du produit et celles du compte lui-même,
 * en donnant la priorité aux données venant du produit.
 *
 * @param int $id_cpte L'identifiant du compte.
 * @return array NULL si le compte n'existe pas, le tableau des données sinon.
 */
function getAccountDatas($id_cpte) {
  global $global_id_agence, $erreur;

  if(($id_cpte == null) or ($id_cpte == '')){
    //signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le numéro du compte n'est pas renseigné")));
    echo "\n Fonction getAccountDatas : Le numéro du compte n'est pas renseigné ! \n";
    exit();
  }else {
    // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
    $sql = "SELECT * FROM adsys_produit_epargne p, ad_cpt c WHERE c.id_ag = $global_id_agence AND c.id_ag = p.id_ag AND c.id_prod = p.id AND c.id_cpte = '$id_cpte'";
  }
  $result = executeDirectQuery($sql);
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


/**
 * Transforme une date venant de Postgres vers le format de PHP
/**
 * Transforme une date venant de Postgres vers le format de PHP
 * @param str $a_date Date au format aaaa-mm-jj
 * @return str Date au format jj/mm/aaaa
 */
function pg2phpDate($a_date) {
  if ($a_date == "") return "";
  // Ex : 2002-02-05
  $a_date = substr($a_date,0,10);
  $M = substr($a_date,5,2);
  $J = substr($a_date,8,2);
  $A = substr($a_date,0,4);
  return "$J/$M/$A";
}

function get_last_batch($id_agence) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT last_batch FROM ad_agc WHERE (id_ag=$id_agence)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction get_last_batch() \n";
    exit();
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);

  return $row[0];
}

function getJournauxLiaison($fields_values=NULL) {
  /**
   *Fonction renvoyant l'ensemble des comptes de liaison et leurs journaux associés
   * @author Papa NDIAYE
   * @since 1.0.8
   * @param array $fields_values, on construit la clause WHERE ainsi : ... WHERE field = value ...
   * @return array ( index => infos)
   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values))){
    echo "Mauvais argument dans l'appel de la fonction getJournauxLiaison ! \n";
    exit();
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
  }

  // construction de la requête
  $sql = "SELECT * FROM ad_journaux_liaison where id_ag=$global_id_agence";
  if (isset($fields_values)) {
    $sql .= " AND ";
    foreach ($fields_values as $key => $value)
      if ( $key == 'id_jou1' || $key == 'id_jou2')
        $sql .= "(id_jou1=$value OR id_jou2=$value ) AND "; // Soit il est à la première position soit il est la 2ème
      else
        $sql .= "$key = '$value' AND ";
    $sql = substr($sql, 0, -4);
  }
  $sql .= " ORDER BY id_jou1 ASC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getJournauxLiaison ! \n";
    exit();
  }

  // Liste des comptes de liaison
  $info = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($info,$row);

  $dbHandler->closeConnection(true);
  return $info;

}

function getDateValeur($a_compte, $a_sens, $a_date_compta) {
  global $global_id_agence;
  if (!isset($a_compte))
    return $a_date_compta;
  $info_compte = getAccountDatas($a_compte);
  $info_produit = getProdEpargne($info_compte["id_prod"]);

  $decalage_debit = $info_produit["nbre_jours_report_debit"];
  $decalage_credit = $info_produit["nbre_jours_report_credit"];

  $nombre_jours = 0;
  if ($a_sens=='c') $nombre_jours = $decalage_credit;
  if ($a_sens=='d') $nombre_jours = $decalage_debit * (-1);

  $annee = substr($a_date_compta,6,4);
  $mois = substr($a_date_compta,3,2);
  $jour = substr($a_date_compta,0,2);

  $date_valeur = jour_ouvrable($jour, $mois, $annee, $nombre_jours);
  return $date_valeur;

}

function getProdEpargne($a_id_produit) {
  global $global_id_agence;
  $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND id = '$a_id_produit'";
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


function jour_ouvrable($date_jour, $date_mois, $date_annee, $nbre_jour) {

// Cette fonction renvoie la date du n ème jour ouvrable suivant la date $date_jour/$date_mois/$date_annee
// Si $nbre_jour est négatif, on remonte dans le temps
// IN  : $date_jour, $date_mois, $date_annee : La date de départ
//       $nbre_jours : Le nombre de jours à avancer / reculer
// OUT : La date demandée au format jj/mm/aaaa

  if ($nbre_jour > 0) $sens = 1;
  else $sens = -1;

  $dj = $date_jour;
  $dm = $date_mois;
  $da = $date_annee;
  for ($i = 0; $i < $nbre_jour*$sens; ) {
    $timestamp = mktime(0,0,0,$dm,$dj+$sens,$da); //Incrémente ou décrémente d'un jour
    $dj = date("d", $timestamp);
    $dm = date("m", $timestamp);
    $da = date("Y", $timestamp);
    if (! is_ferie($dj, $dm, $da)) ++$i;
  }
  $timestamp = gmmktime(0,0,0,$dm,$dj,$da);
  $dj = date("d", $timestamp);
  $dm = date("m", $timestamp);
  $da = date("Y", $timestamp);

  return $dj."/".$dm."/".$da;
}

function isAfter($date1, $date2, $equal = false) {
  // Fonction qui renvoie true si $date1 est postérieure à $date2
  // false si $date1 est antérieure ou égale à $date2
  // IN : $date1 au format jj/mm/aaaa
  //      $date2 au format jj/mm/aaaa
  // OUT: true ou false

  $j1 = substr($date1,0,2);
  $m1 = substr($date1,3,2);
  $a1 = substr($date1,6,4);

  $j2 = substr($date2,0,2);
  $m2 = substr($date2,3,2);
  $a2 = substr($date2,6,4);

  $time1 = mktime(0,0,0,$m1, $j1, $a1);
  $time2 = mktime(0,0,0,$m2, $j2, $a2);

  if($equal) {
    return ($time1 >= $time2);
  }
  else {
    return ($time1 > $time2);
  }
}

function getJournalCpte($num_cpte) {
  //renvoie les informations sur le Journal associé au compte comptable

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];
  $infos = array();

  // Regarder si ce compte a un journal associé
  $sql="SELECT *  FROM ad_cpt_comptable where id_ag=$id_ag and num_cpte_comptable = '$num_cpte' and cpte_princ_jou = 't'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction getJournalCpte() \n";
    exit();
  }

  if ($result->numrows()==0) { // Si pas de journal associé. Rem :pourquoi ne pas faire appel à getComptesComptes et vérifier que c'un compte principal
    //$dbHandler->closeConnection(true);
    $non_jou = true;
    //return NULL;
  }

  $sql="SELECT *  FROM ad_journaux  where id_ag=$id_ag and num_cpte_princ = '$num_cpte' ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    // $dbHandler->closeConnection(true);
    $non_jou = true; // $non_jou nous indique que c'est le journal 1 qui sera utilisé par défaut
    // return NULL;
  }

  if ($non_jou == false) { // Sinon pas la peine, on sait déjà qu'il n'y a pas de journal associé
    // Si on a de la chance, ce compte est directement associé à un journal
    $sql="SELECT *  FROM ad_journaux  where id_ag=$id_ag and num_cpte_princ = '$num_cpte' ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      echo "Erreur dans la fonction getJournalCpte()  = >  cpte associe journal \n";
      exit();
    }

    if ($result->numrows()==1) {
      $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
      $infos = $row;
      //$dbHandler->closeConnection(true);
      //return($row);
    } else {
      // On regarde si le compte centralisateur n'est pas compte principal d'un journal
      // FIXME : INUTILE : On peut déjà faire l'appel récursif !
      /*
      $sql="SELECT *  FROM ad_journaux  where num_cpte_princ = (SELECT cpte_centralise  FROM ad_cpt_comptable where num_cpte_comptable = '$num_cpte') ";
      $result = $db->query($sql);
      if (DB::isError($result))
        {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

      if($result->numrows()==1)
        {
          $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
          $infos = $row;
          //  $dbHandler->closeConnection(true);
          // return($row);
        }
      else
      {*/
      $sql ="SELECT cpte_centralise  FROM ad_cpt_comptable where id_ag=$id_ag and num_cpte_comptable = '$num_cpte'";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__);
        echo "Erreur dans la fonction getJournalCpte() => recuperation cpte_centralise \n";
        exit();
      }

      if ($result->numrows()==1) {
        $row = $result->fetchrow();
        $info_jou = getJournalCpte($row[0]); // Appel récursif avec le compte centralisateur
        $dbHandler->closeConnection(true);
        return $info_jou;
      } else {
        // On est arrivés à la racine du plan comptable, il y a donc une inconsistance dans la base de données
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Inconsistance dans la DB : le compte $num_cpte est censé tre compte principal et pourant ..."
        echo "Erreur dans la fonction getJournalCpte() => verification si nb row = 1 \n";
        exit();
      }

    }
  }
  $dbHandler->closeConnection(true);
  if ($non_jou == true) {
    $jou_princ = getInfosJournal(1);
    $infos = $jou_princ[1];
    return($infos);
  } else
    return($infos);

}

function getInfosJournal($id_jou = NULL) {
  /*
    Renvoie les infos des journaux
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];

  $sql="SELECT *  FROM ad_journaux where id_ag=$id_ag ";

  if ($id_jou != NULL)
    $sql .= "AND id_jou=$id_jou";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction getInfosJournal() => info Journaux \n";
    exit();
  }

  $jnl=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $jnl[$row["id_jou"]]=$row;
  }

  $dbHandler->closeConnection(true);

  return $jnl;
}
/*Roshan Funtions*/
function getOperations($id_oper=0) {
  // Fonction renvoyant toutes les associations définies selon les opérations ou les informations concernant une opération particulière
  // IN : $id_oper = 0 ==> Renvoie toutes les opérations
  //               > 0 ==> Renvoie l'opération id_oper
  // OUT: Objet ErrorObj avec en param :
  //      Si $id_oper = 0 : array($key => array("type_operation", "libel", "cptes" = array ("sens" = array("categorie, "compte")))
  //                  > 0 : array("libel") = libellé de l'opération

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];

  $sql = "SELECT * FROM ad_cpt_ope ";
  if ($id_oper == 0)
    $sql .= "WHERE id_ag = $id_ag ORDER BY type_operation";
  else
    $sql .= "WHERE type_operation = $id_oper and id_ag = $id_ag ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getOperations ! \n";
    exit();
  }

  if ($id_oper > 0) {
    if ($result->numRows() == 0) {
      // Il n'y a pas d'association pour cette opération
      $dbHandler->closeConnection(false);
      return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $id_oper n'existe pas");
    } else {
      $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
      $dbHandler->closeConnection(true);
      return new ErrorObj(NO_ERR, array("libel" => $row["libel_ope"], "type_operation" => $row["type_operation"], "categorie_ope" => $row["categorie_ope"]));
    }
  } else {
    $OP= array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag = $id_ag and type_operation = ".$rows["type_operation"];
      $result2 = $db->query($sql);
      if (DB::isError($result2)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__);
        echo "Erreur du fonction getOperations ! \n";
        exit();
      }
      while ($row_cptes = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
        $rows["cptes"][$row_cptes["sens"]] = $row_cptes;
      }

      array_push($OP,$rows);
    }
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR, $OP);
  }
}


function getDetailsOperation($type_oper) {
  global $dbHandler, $global_id_agence, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];

  // récupération du détail de l'opération
  $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag=$id_ag and type_operation = $type_oper ORDER BY sens DESC;";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);

  if (DB::isError($result)) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getDetailsOperation ! \n";
    exit();
  }

  if ($result->numRows() == 0) // Il n'y a pas d'association pour cette opération
    return new ErrorOBj(ERR_NO_ASSOCIATION);


  //Définition des constantes
  define("SENS_CREDIT", "c");
  define("SENS_DEBIT", "d");

  $OP = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row["sens"] == SENS_DEBIT) // informations au débit de l'opération
      $OP["debit"] = array("compte"=>$row["num_cpte"], "sens"=>$row["sens"], "categorie"=>$row["categorie_cpte"]);
    elseif($row["sens"] == SENS_CREDIT)  // informations au crédit de l'opération
      $OP["credit"] = array("compte"=>$row["num_cpte"],"sens"=>$row["sens"],"categorie"=>$row["categorie_cpte"]);
  }

  return new ErrorObj(NO_ERR, $OP);
}

function getLastIdOperation($array_comptable) {
  /*
    PRECONDITION :
    Prend en argument un tableau d'écritures ocmptables pour l'historique et renvoie le dernier n° d'opération (débit/crédit)

  */

  if (!is_array($array_comptable))
    return 1;

  reset($array_comptable);
  $id_max = 0;
  while (list(,$tmp) = each($array_comptable)) {
    if ($id_max < $tmp["id"])
      $id_max = $tmp['id'];
  }
  return $id_max;
}

function checkCptDeviseOK($num_cpte, $devise) {
  global $global_multidevise, $error;
  global $global_id_agence;
  if ($global_multidevise) {
    // Recherche des infos sur le compte
    $ACC = getComptesComptables(array("num_cpte_comptable" => $num_cpte));
    //debug($ACC,"acc");
    if (sizeof($ACC) != 1) {
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      echo "Erreur du fonction checkCptDeviseOK ! \n";
      exit();
    }
    $ACC = $ACC[$num_cpte];

    // Si le compte a une devise associée, alors vérifier que c'est la même que celle de l'opération
    if (isset($ACC["devise"])) {
      if ($ACC["devise"] == $devise)
        return $num_cpte;
      else {
        return NULL;
      }
    } else {
      // Chercher si le compte possède un sous-compte dans la devise renseignée
      $ACC2 = getComptesComptables(array("cpte_centralise" => $num_cpte, "devise" => $devise));
      if (count($ACC2) == 1) {
        $ACC  = array_pop($ACC2);
        return $ACC["num_cpte_comptable"];
      } else if (count($ACC2) == 0) {
        // Création du sous-compte dans la devise de l'écriture
        $sscomptes = array();
        $sscompte = array();
        $sscompte["num_cpte_comptable"] = $num_cpte.".$devise";
        $sscompte["libel_cpte_comptable"] = $ACC["libel_cpte_comptable"]."-$devise";
        $sscompte["solde"] = 0;
        $sscompte["devise"] = $devise;
        $sscomptes[$num_cpte.".$devise"] = $sscompte;

        $myErr = ajoutSousCompteComptable($num_cpte, $sscomptes);
        if ($myErr->errCode != NO_ERR) {
          //debug(sprintf(_("Problème lors de la création du sous-compte %s"),$num_cpte.$devise)." : ".$error[$myErr->errCode]);
          echo "Problème lors de la création du sous-compte ".$num_cpte.$devise." : ".$error[$myErr->errCode]."\n";
          //signalErreur(__FILE__,__LINE__,__FUNCTION__);
          echo "Erreur du fonction checkCptDeviseOK ! \n";
          exit();
        } else
          return $num_cpte.".".$devise;
      } else{
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Au moins deux sous-comptes du compte %s existent dans la devise %s"),$num_cpte,$devise));
        echo "Au moins deux sous-comptes du compte ".$num_cpte." existent dans la devise ".$devise."\n";
      }
      return $num_cpte;
    }
  } else
    return $num_cpte;
}

function getComptesComptables($fields_values=NULL, $niveau=NULL,$date_modif=NULL) {
  global $dbHandler,$global_id_agence;

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values))){
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
    echo "Mauvais argument dans l'appel de la fonction getComptesComptables ! \n";
    exit();
  }
  $db = $dbHandler->openConnection();
  if($date_modif == NULL){
    $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND is_actif = 't' AND ";
  }else{
    $date_mod= php2pg($date_modif);
    $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_mod')) AND ";
  }
  if (isset($fields_values)) {

    foreach ($fields_values as $key => $value)
      if (($value == '') or ($value == NULL))
        $sql .= "$key IS NULL AND ";
      else
        $sql .= "$key = '$value' AND ";

  }
  $sql = substr($sql, 0, -4);
  $sql .= "ORDER BY id_ag, num_cpte_comptable ASC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getComptesComptables ! \n";
    exit();
  }

  $cptes = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    if (getNiveauCompte($row["num_cpte_comptable"]) <= $niveau && $niveau != NULL) {
      $cptes[$row["num_cpte_comptable"]] = $row;
    }
    elseif($niveau == NULL) {
      $cptes[$row["num_cpte_comptable"]] = $row;
    }


  $dbHandler->closeConnection(true);

  return $cptes;
}


/**
 * @description: Calcul le niveau d'un compte
 * @param text Numéro d'un Compte comptables
 * @return int le niveau du compte comptable
 */
function getNiveauCompte($compte) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  //On commence par récupérer le numéro de lot
  $sql = "SELECT getNiveau('$compte',$global_id_agence) ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    echo "Erreur du fonction getNiveauCompte | ".$result->getMessage()."\n";
    exit();
  }
  $row = $result->fetchrow();
  $niveau = $row[0];
  $dbHandler->closeConnection(true);
  return $niveau;

}

function ajoutSousCompteComptable($compte_centralisateur,$liste_sous_comptes, $solde_reparti=NULL) {
  /*
     Fonction qui ajoute des sous-comptes à un compte comptable

     IN: - $compte_centralisateur = le numéro du compte auquel on veut ajouter des sous-comptes
         - $liste_sous_comptes = tableau contenant la liste des sous-comptes au format
           array (n° cpte => array(n° cpte, libel, solde de départ, devise))

     OUT : Objet ErrorObj
  */
  global $dbHandler, $global_nom_login,$global_id_agence;

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  //Recupèration des infos du compte centralisateur
  $param["num_cpte_comptable"]=$compte_centralisateur;
  $infocptecentralise = getComptesComptables($param);

  // Verifier s'il n y a pas, pour le compte centralisateur, des ecritures en attente dans le brouillard
  $ecriture_attente = isEcritureAttente($compte_centralisateur);
  if ($ecriture_attente == true) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPT_ECRITURE_EXIST, $compte_centralisateur);
  }

  // Récupère le nombre de sous-comptes du compte centralisateur
  $nbre_souscompte = getNbreSousComptesComptables($compte_centralisateur) ;

  // Vérifie si c'est la première création de sous-comptes pour le compte centralisateur
  if ($nbre_souscompte == 0 ) {
    // première création, Vérifier alors que solde du compte centralisateur est complétement réparti entre les sous-comptes

    $soldesc=0; // la somme des soldes des sous-comptes
    if (isset($liste_sous_comptes))
      foreach($liste_sous_comptes as $key=>$value)
        $soldesc = $soldesc + abs($value["solde"]);
    if ($solde_reparti == NULL) {
      if ($infocptecentralise[$compte_centralisateur]['compart_cpte'] == 3 OR $infocptecentralise[$compte_centralisateur]['compart_cpte'] == 4) {
        $solde_reparti = calculeSoldeCpteGestion($compte_centralisateur);
      } else {
        $solde_reparti = $infocptecentralise[$compte_centralisateur]['solde'];
      }
    }

    //comparaison entre la sommme des soldes et le solde du compte centralisateur
    if ( abs($solde_reparti) != $soldesc) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_SOLDE_MAL_REPARTI, $compte_centralisateur);
    }
  }
  // Ajout des sous comptes
  if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
    foreach($liste_sous_comptes as $key=>$value)
      if ($key!='') {
        // Vérifier que le sous-compte n'existe pas dans la DB
        $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag=$global_id_agence and num_cpte_comptable='$key';";
        // FIXME : Utiliser getComptesComptables ?
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          //signalErreur(__FILE__,__LINE__,__FUNCTION__);
          echo "Erreur du fonction ajoutSousCompteComptable ! \n";
          exit();
        }

        //if compte exist deja on modifier le procedure
        /*
          if ($result->numRows() > 0) {
            $dbHandler->closeConnection(false);
             return new ErrorObj(ERR_CPT_EXIST, $key);
         }*/


        // Héritage automatique de la devise du compte centralisateur
        if (!isset($value["devise"]) && isset($infocptecentralise[$compte_centralisateur]["devise"]))
          $value["devise"] = $infocptecentralise[$compte_centralisateur]["devise"];

        // Vérfieir si la devise du sous-compte n'est pas différente de la devise du compte centralisateur
        if ($infocptecentralise[$compte_centralisateur]["devise"] != NULL && $infocptecentralise[$compte_centralisateur]["devise"] != $value["devise"]) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_DEV_DIFF_CPT_CENTR, $value["devise"]);
        }
        // Construction de la requête d'insertion de sous-compte
        $DATA = array();

        // Vérifier si la devise du sous-compte n'est pas différente de la devise du compte de provision
        if ( $value['cpte_provision'] != "[Aucun]" &&  $value["cpte_provision"] != NULL) {
          $infoscpteprov=getComptesComptables(array("num_cpte_comptable"=>$value["cpte_provision"]));
          if($infoscpteprov[$value["cpte_provision"] ]["devise"] != $value["devise"] ) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_DEV_DIFF_CPT_PROV, $value["devise"]);
          }
          $DATA["cpte_provision"] =$value["cpte_provision"];
        } else {
          $DATA["cpte_provision"] =NULL;
        }

        $DATA["num_cpte_comptable"] = $value["num_cpte_comptable"];
        $DATA["libel_cpte_comptable"] = $value["libel_cpte_comptable"];
        if ($value["compart_cpte"]!='') // si le compartiment n'edst pas renseigné alors il l'hérite du compte père
          $DATA["compart_cpte"] = $value["compart_cpte"];
        else
          $DATA["compart_cpte"] = $infocptecentralise[$compte_centralisateur]["compart_cpte"];

        if ($value["sens_cpte"]!='') // si le sens n'est pas renseigné alors il l'hérite du compte père
          $DATA["sens_cpte"] = $value["sens_cpte"];
        else
          $DATA["sens_cpte"] = $infocptecentralise[$compte_centralisateur]["sens_cpte"];

        $DATA["classe_compta"] = $infocptecentralise[$compte_centralisateur]["classe_compta"];
        //$DATA["cpte_centralise"] = $compte_centralisateur;

        if ($infocptecentralise[$compte_centralisateur]['cpte_princ_jou']=='t')
          $DATA["cpte_princ_jou"] = 't';
        else
          $DATA["cpte_princ_jou"] = 'f';

        $DATA["solde"] = 0;

        $now = date("Y-m-d");
        $DATA["date_ouvert"] = $now;
        $DATA["etat_cpte"] = 1;
        $DATA["id_ag"] = $global_id_agence;
        $DATA["devise"] = $value["devise"];



        // pour cas ou  le sous compte exist deja  on va faire un update
        $DATA["is_actif"] = TRUE;
        $Where = array("num_cpte_comptable" => $key,'id_ag'=> $global_id_agence,'is_actif'=>'FALSE');

        if ($result->numRows() > 0){
          $sql = buildUpdateQuery("ad_cpt_comptable", $DATA, $Where);

        }
        // else insert normal
        else{

          $sql = buildInsertQuery("ad_cpt_comptable",$DATA);

        }
        // Insertion dans la DB
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          //signalErreur(__FILE__,__LINE__,__FUNCTION__);
          echo "Erreur du fonction ajoutSousCompteComptable ! \n";
          exit();
        }

        //Recherche des contrepartie pour le compte centralisateur
        $cpt_cptie=getInfosJournalCptie(NULL,$compte_centralisateur);
        if(is_array($cpt_cptie)){
          foreach($cpt_cptie as $key1=>$DATA){
            foreach($liste_sous_comptes as $key2=>$value2){
              //ajout dans le journal des contreparties
              // ajoutjournalCptie verifie si il y a une entre dans la table ad_cpt_comptable avec le nuvo num_cpte_comptable
              $myErr=ajoutJournalCptie($DATA["id_jou"], $value2["num_cpte_comptable"]);
              if ($myErr->errCode != NO_ERR) {
                echo "fonction ajoutSousCompteComptable : Echec création journal. -> ".$myErr->param."\n";
                exit();
                /*$html_err = new HTML_erreur(_("Echec création journal. "));
                $html_err->setMessage(_("Erreur")." : ".$myErr->param);
                $html_err->addButton("BUTTON_OK", 'Jou-6');
                $html_err->buildHTML();
                echo $html_err->HTML_code;*/
              }
            }
          }
        }
        // Insertion dans la DB
        /* $result = $db->query($sql);
        /* if (DB::isError($result)) {
           $dbHandler->closeConnection(false);
           signalErreur(__FILE__,__LINE__,__FUNCTION__);
         }*/

        if ( abs($solde_reparti) != 0 && ($value['solde'] != 0)) {
          // Passage des écritures comptables
          $comptable = array();
          $cptes_substitue = array();
          $cptes_substitue["cpta"] = array();
          if ($solde_reparti < 0 ) {
            //crédit du compte centralisateur par le débit d'un sous-compte
            $cptes_substitue["cpta"]["debit"] = $key;
            $cptes_substitue["cpta"]["credit"] = $compte_centralisateur;
          } else {
            //débit d'un sous compte par le credit du compte centralisateur
            $cptes_substitue["cpta"]["debit"] = $compte_centralisateur;
            $cptes_substitue["cpta"]["credit"] = $key;
          }
          $myErr = passageEcrituresComptablesAuto(1003, abs($value["solde"]), $comptable, $cptes_substitue, $value["devise"]);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
          $erreur=ajout_historique(410, NULL, _("Virement solde compte principal"), $global_nom_login, date("r"), $comptable);
          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          }
        }
      }

  // Mise à jour du champs compte centralisateur des sous-compte
  if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
    foreach($liste_sous_comptes as $key=>$value)
      if ($key!='') {
        $niveau = getNiveauCompte($compte_centralisateur) + 1;
        $sql = "UPDATE ad_cpt_comptable set cpte_centralise='$compte_centralisateur', niveau = $niveau WHERE id_ag=$global_id_agence AND num_cpte_comptable = '$key'";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          //signalErreur(__FILE__,__LINE__,__FUNCTION__);
          echo "Erreur du fonction ajoutSousCompteComptable ! \n";
          exit();
        }
      }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}
function isEcritureAttente($num_cpte) {
  //Verifie s'il y des ecritures en attente sur le compte

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT count(compte) FROM ad_brouillard where id_ag=$global_id_agence and compte ='$num_cpte' ";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction isEcritureAttente! \n";
    exit();
  }

  $row = $result->fetchrow();

  if ($row[0] > 0)
    return true;
  else
    return false;
}

function getNbreSousComptesComptables($num_cpte,$a_isActif=NULL) {
  /*

   Fonction renvoyant le nombre de sous comptes d'un compte principal définis dans le plan comptable
   IN : numero du compte

   OUT: nombre de sous compte   */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT count(num_cpte_comptable) FROM ad_cpt_comptable where id_ag=$global_id_agence and  num_cpte_comptable like '$num_cpte.%' ";
  if($a_isActif != NULL){
    $sql .=" AND is_actif='".$a_isActif."' ";

  }

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getNbreSousComptesComptables ! \n";
    exit();
  }

  $row = $result->fetchrow();

  return $row[0];
}

/**********
 * Fonction qui calcule pour un compte le solde des mouvements de l'exerciece en cours
 * utile pour la répartition des soldes des comptes de gestions centralisateurs lors de la création de sous-comptes
 * @author Papa
 * @since 2.2
 * @param txt $compte Le numéro du compte comptable
 * @return int Le solde des mouvements du compte dans l'exercice en cours
 */
function calculeSoldeCpteGestion($compte) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $solde=0;

  /* Exercice en cours */
  $AG = getAgenceDatas($global_id_agence );
  $id_exo_encours = $AG["exercice"];

  $infos_exo_encours = getExercicesComptables($id_exo_encours);

  /* Mouvements au débit dans l'exercie en cours */
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE b.id_ag=$global_id_agence and a.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('".$infos_exo_encours[0]['date_deb_exo']."') AND date('".$infos_exo_encours[0]['date_fin_exo']."') AND sens = 'd' ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction calculeSoldeCpteGestion ! \n";
    exit();
  }
  $row = $result->fetchrow();
  $total_debit = $row[0];

  /* Mouvements au crédit dans l'exercie en cours */
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('".$infos_exo_encours[0]['date_deb_exo']."') AND date('".$infos_exo_encours[0]['date_fin_exo']."') AND sens = 'c'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction calculeSoldeCpteGestion ! \n";
    exit();
  }
  $row = $result->fetchrow();
  $total_credit = $row[0];

  $solde = $total_credit - $total_debit;

  $dbHandler->closeConnection(true);
  return $solde;
}

function getAgenceDatas($id_ag) {
  /* Cette fonction renvoie toutes les informations relatives à l'agence dont lID est $id_agence
   IN : l'ID de l'agence
   OUT: un tableau associatif avec les infos sur l'agence si tout va bien
        NULL si l'agence n'existe pas
        Die si erreur de la DB
  */
  global $dbHandler, $global_id_agence;

  if ($id_ag == NULL)
    $id_ag = $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_agc";
  if ($id_ag != NULL)
    $sql .= " WHERE id_ag = $id_ag";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getAgenceDatas ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  return $DATAS;
}


function getExercicesComptables($id_exo=NULL) {
  /*

   Fonction renvoyant l'ensemble des exercices comptables
   IN : <néant>
   OUT: array ( index => array(infos exercice))

  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];

  $sql = "SELECT * FROM ad_exercices_compta where id_ag=$id_ag ";
  if ($id_exo)
    $sql .= " AND id_exo_compta=$id_exo ";
  $sql .= "ORDER BY id_exo_compta";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getExercicesComptables ! \n";
    exit();
  }

  $exos = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    array_push($exos, $row);
  }

  $dbHandler->closeConnection(true);
  return $exos;
}

function getInfosJournalCptie($id_jou=NULL,$num_cpte=NULL) {
  // renvoie les donnes de la table ad_journaux_cptie
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql="SELECT *  FROM ad_journaux_cptie where id_ag=$global_id_agence ";
  if ($id_jou != NULL) {
    $sql .= "AND id_jou=$id_jou";
    if ($num_cpte != NULL)
      $sql .= " and (num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%')";
  } else
    if ($num_cpte != NULL)
      $sql .= "AND num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur fonction getInfosJournalCptie ! \n";
    exit();
  }

  $cptie = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($cptie,$row);

  $dbHandler->closeConnection(true);
  return $cptie;
}

function supJournalCptie($id_jou,$id_compte) {
  // supprime des comptes de contrepartie d'un journal

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // le compte et ses sous-comptes qui sont de la contrepartie
  $cptie=getInfosJournalCptie($id_jou,$id_compte);
  if (isset($cptie))
    foreach($cptie as $row) {
      $id=$row["id_jou"];
      $num=$row["num_cpte_comptable"];

      $sql="delete from ad_journaux_cptie where id_ag=$global_id_agence and id_jou=$id and num_cpte_comptable='$num'";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__);
        echo "Erreur du fonction supJournalCptie ! \n";
        exit();
      }
    }

  $dbHandler->closeConnection(true);
  return true;
}

function getSousComptes($compte, $recursif=true,$condSousComptes,$whereSousCpte) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $liste_sous_comptes=array();

  $sql ="SELECT * FROM ad_cpt_comptable WHERE cpte_centralise ='".$compte."' AND id_ag = ".$global_id_agence;
  $sql.=$whereSousCpte;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getSousComptes ! \n";
    exit();
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    // ajoute le compte dans la liste
    $liste_sous_comptes[$row['num_cpte_comptable']] = $row;

    // ajouter les sous-comptes du sous-compte par récursivité si récursif
    if ($recursif)
      $liste_sous_comptes = array_merge($liste_sous_comptes,getSousComptes($row['num_cpte_comptable'], true,$whereSousCpte));
  }

  $dbHandler->closeConnection(true);
  return $liste_sous_comptes;
}


function ajoutJournalCptie($id_jou,$compte) {
  // Ajout le compte $compte et ses sous-comptes dans la contrepartie du journal dont l'id est donné

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // si le compte ou les sous-comptes sont de la contrepartie, les supprimer d'abord
  $sup=supJournalCptie($id_jou,$compte);

  // Récupération de tous les comptes dérivés de ce compte
  $sous_comptes=getSousComptes($compte, true);

  // Ajout du compte dans la contrepartie du journal
  $sql="INSERT INTO ad_journaux_cptie Values($id_jou,'$compte',$global_id_agence)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction ajoutJournalCptie ! \n";
    exit();
  }

  // Ajout des sous-comptes dans la contrepartie du journal
  if (isset($sous_comptes))
    foreach($sous_comptes as $key=>$value) {
      // récupère informations du sous-compte
      $param["num_cpte_comptable"]=$key;
      $cpte=getComptesComptables($param);

      // vérifie si le sous-compte n'est pas compte principal d'un journal
      if ($cpte[$key]["cpte_princ_jou"]=='t') {
        $dbHandler->closeConnection(false);
        return new ErrorOBj(ERR_DEJA_PRINC_JOURNAL,$key);
      }

      // ajout du sous-compte dans la contrepartie
      $sql="INSERT INTO ad_journaux_cptie Values($id_jou,'$key',$global_id_agence)";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__);
        echo "Erreur du fonction ajoutJournalCptie ! \n";
        exit();
      }
    }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/* End Roshan Functions*/


/*------------------------------------- FIN Functions associer au passage ecriture comptable auto---------------------------*/
/*-------------------------------------Functions Main---------------------------*/
function getNumAgence() {
  // Fonction qui renvoie le numéro de l'agence, en fait le id_ag de la première entrée de l table ad_agc

  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT NumAgc()";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getNumAgence ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0){
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
    echo "fonction getNumAgence : Il n'y a pas d'entrée dans la table agence ! \n";
    exit();
  }
  $tmprow = $result->fetchrow();
  if ($result->numRows() > 1) return 0;
  return $tmprow[0];

}

function deleteFraisAttente($id_cpte){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "DELETE FROM ad_frais_attente WHERE id_cpte='$id_cpte' and id_ag=numagc() ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    echo "fonction deleteFraisAttente : Erreur dans la requête SQL | ID Cpte : ".$id_cpte."! \n";
    exit();
  }
  $dbHandler->closeConnection(true);
  return 1;
}

/**
 * Renvoie le compte comptable associé à un compte client donné.
 *
 * Dans le cas d'un compte d'épargne nantie, on remonte jusqu'au produit de crédit.
 * @param int $id_cpte_cli Id du compte client associé
 * @return text Numéro du compte comptable associé
 */
function getCompteCptaProdEp($id_cpte_cli) {
  global $dbHandler, $global_id_agence, $erreur;

  $db = $dbHandler->openConnection();

  if(($id_cpte_cli == null) or ($id_cpte_cli == '')){
    $dbHandler->closeConnection(false);
    echo "Le compte interne du client n'est pas renseigné.";
  } else {
    $sql = "SELECT b.id, b.cpte_cpta_prod_ep ";
    $sql .= "FROM ad_cpt a, adsys_produit_epargne b  ";
    $sql .= "WHERE b.id_ag = numagc() AND b.id_ag = a.id_ag AND a.id_prod = b.id AND a.id_cpte='$id_cpte_cli'";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("DB").": ".$result->getMessage());
    echo "Erreur de la requete getCompteCptaProdEp | Error Trace -> ".$result->getMessage();
    exit();
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Aucun compte associé. Veuillez revoir le paramétrage"));
    echo "Aucun compte associé -> ".$id_cpte_cli.". Veuillez revoir le paramétrage \n";
    exit();
  }

  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  if ($row['id'] == 4) { // Cas particulier du compte d'épargne nantie
    $sql = "SELECT cpte_cpta_prod_cr_gar from adsys_produit_credit a, ad_dcr b where b.id_ag = numagc() AND b.id_ag = a.id_ag AND a.id = b.id_prod AND ";
    $sql .= "b.id_doss = (SELECT distinct(id_doss) FROM ad_gar WHERE id_ag = numagc() AND gar_num_id_cpte_nantie = $id_cpte_cli)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__, "DB: ".$result->getMessage());
      echo "Erreur de la requete getCompteCptaProdEp | Error Trace -> ".$result->getMessage();
      exit();
    }

    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);
    return $row[0];
  } else {
    $dbHandler->closeConnection(true);
    return $row["cpte_cpta_prod_ep"];
  }
}

function GetCompteCpteOperation($id_ope){
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT num_cpte from ad_cpt_ope_cptes where type_operation = $id_ope and sens = 'c'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("DB").": ".$result->getMessage());
    echo "Erreur de la requete GetCompteCpteOperation | Error Trace -> ".$result->getMessage();
    exit();
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Aucun compte associé. Veuillez revoir le paramétrage"));
    echo "Aucun compte associé | ID Op : ".$id_ope.". Veuillez revoir le paramétrage \n";
    exit();
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $row["num_cpte"];

}

function getGlobalDatas(){
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT libel_ag, statut, libel_institution, type_structure, exercice, langue_systeme_dft, code_devise_reference ";
  $sql .= "FROM ad_agc WHERE id_ag=numagc()"; // $global_id_agence
  $result = $db->query($sql); //Cherche ds table des agences
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur de la focntion getGlobalDatas ! \n";
    exit();
  } else if ($result->numrows() <> 1) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucune ou plusieurs occurences de la même agence!"
    echo "focntion getGlobalDatas : Aucune ou plusieurs occurences de la même agence ! \n";
    exit();
  }
  $row = $result->fetchrow();
  $retour['exercice'] = $row[4];
  $retour['code_devise_reference'] = $row[6];

  // Sommes-nous en mode unidevise ou multidevise
  $sql = "SELECT count(*) FROM devise WHERE id_ag =numagc()";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur de la fonction getGlobalDatas ! \n";
    exit();
  }
  $row = $result->fetchrow();
  if ($row[0] > 1) {
    $retour['multidevise'] = 1;
  }
  else {
    $retour['multidevise'] = 0;
  }

  $dbHandler->closeConnection(true);

  return $retour;
}

function getClientDatas($id_client) {
  /* Renvoie un tableau associatif avec toutes les données du client dont l'ID est $id_client
     Valeurs de retour :
     Le tableau si OK
     NULL si le client n'existe pas
     Die si erreur de la base de données
  */
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$id_client' ";
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
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  // FIXME ** TF - 27/09/2002 -- Ce champs temporaire ne doit pas être visible par les modules
  unset ($DATAS["tmp_already_accessed"]);
  return $DATAS;
}
function getBaseAccountID ($id_client) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_cpte_base FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    echo "Erreur du fonction getBaseAccountID ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;
  $tmpRow = $result->fetchrow();
  return $tmpRow[0];
}
function deblocageCompteInconditionnel ($id_cpte) {
  /*
   Cette PS débloque le compte $id_cpte
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT etat_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result)){
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction deblocageCompteInconditionnel ! \n";
    exit();
  }

  $tmprow = $result->fetchrow();
  $etat = $tmprow[0];

  if ($etat == 2){  // Le compte est fermé
    echo "Erreur du fonction deblocageCompteInconditionnel : ! Impossible de débloquer le compte $id_cpte qui est fermé ! \n";
    exit();
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de débloquer le compte $id_cpte qui est fermé"
  }

  //changer le compte à  ouvert
  $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result)){
    echo "Erreur du fonction deblocageCompteInconditionnel ! \n";
    exit();
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  //quel intérêt ?
  return new ErrorObj(NO_ERR);
}

function buildUpdateQuery ($TableName, $Fields, $Where="") {

  if (sizeof($Fields) == 0){
    echo "Aucun champ à mettre à jour fonction buildUpdateQuery ! \n";
    exit();
    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à mettre à jour"));
  }
  $Fields = array_make_pgcompatible($Fields);
  reset($Fields);
  $sql = "UPDATE $TableName SET ";
  while (list($key, $value) = each($Fields)) {
    if ("$value" == '')
      $sql .= $key." = NULL, ";
    else
      $sql .= $key." = '".$value."', ";
  }
  $sql = substr($sql, 0, strlen($sql) - 2);
  if (is_array($Where)) {
    $sql .= " WHERE  ";
    while (list($key, $value) = each($Where))
      $sql .= " $key = '$value' AND";
    $sql = substr($sql, 0, strlen($sql) - 3);
  }
  $sql .=";";
  return $sql;
}
function array_make_pgcompatible($ary) {
  if (! is_array($ary)){
    echo "Fonction array_make_pgcompatible : L'argument attendu est un array \n";
    exit();
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'argument attendu est un array"
  }
  foreach ($ary AS $key => $value)
    $ary[$key] = string_make_pgcompatible($ary[$key]);
  return $ary;
}
function string_make_pgcompatible($str) {
  return addslashes($str);
}

function buildInsertQuery ($TableName, $Fields) {

  if (count($Fields) == 0){
    echo "Fonction buildInsertQuery : Aucun champ à ajouter! \n";
    exit();
    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à ajouter"));
  }
  // On rend le tableau PG Compilant
  $Fields = array_make_pgcompatible($Fields);
  $sql = "INSERT INTO $TableName (";
  reset($Fields);
  while (list($key, $value) = each($Fields))
    $sql .= "$key, ";
  $sql = substr($sql, 0, strlen($sql) - 2);
  $sql .=") VALUES (";
  reset($Fields);
  while (list($key, $value) = each($Fields)) {
    if ($value == "")
      $sql .= "NULL, ";
    else
      $sql .="'$value', ";
  };
  $sql = substr($sql, 0, strlen($sql) - 2);
  $sql .=");";

  return $sql;
}

function buildSelectQuery ($table, $WHERE = NULL) {
  $sql = "SELECT * FROM $table";
  if (is_array($WHERE)) {
    $sql .= " WHERE ";
    while (list($key, $value) = each($WHERE)) {
      $sql .= " $key = '$value' AND";
    }
    $sql = substr($sql, 0, strlen($sql) - 4);
  }
  $sql .= ";";
  return $sql;
}


function executeDirectQuery($a_sql, $a_flat = FALSE) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $result = executeQuery($db, $a_sql, $a_flat);
  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }
  $dbHandler->closeConnection(true);
  return($result);
}

function executeQuery(&$db, $a_sql, $a_flat = FALSE) {
  global $dbHandler;

  $result = $db->query($a_sql);
  if (DB::isError($result)) {
    // S'il y a une erreur, on retourne ERR_DB_SQL avec le code de la requête ayant posé problème
    return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
  } else if (DB::isManip($a_sql)) {
    // Si c'est un UPDATE, INSERT ou DELETE, on retourne alors le nombre de lignes affectées
    return new ErrorObj(NO_ERR, $db->affectedRows());
  } else {
    // On suppose que la requête était un SELECT, on retourne alors les lignes trouvées
    $rows = array();
    if ($a_flat) {
      // On concatène les lignes (et les colonnes) retournées
      while ($row = $result->fetchrow()) {
        foreach ($row as $col => $content)
          array_push($rows, $content);
      }
    } else {
      // On retourne un tableau ($row) par ligne de résultats
      while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($rows, $row);
    }
    $result->free();
    return new ErrorObj(NO_ERR, $rows);
  }
}


/*-------------------------------------Fin Functions Main---------------------------*/
/*---------------------------Main Function LanceDefection-----------------------*/
function lanceDefectionClient($id_client, $etat, $raison_defection = 'N/A', $id_guichet) {
  global $dbHandler;
  global $global_id_agence, $global_monnaie;

  $db = $dbHandler->openConnection();
  // Si le compte de base du client était bloqué, on débloque celui-ci inconditionnellement
  $cptBase = getBaseAccountID($id_client);
  $cptPs = getPSAccountID($id_client);
  deblocageCompteInconditionnel($cptBase);
  if ($cptPs != null || $cptPs != ''){
    deblocageCompteInconditionnel($cptPs);
  }


  $myErr = testDefection($id_client);
  if ($myErr->errCode == ERR_DEF_SLD_NON_NUL) {
    $balance = $myErr->param;
    if ($balance < 0) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Incohérence dans l'algo!! LA balance est négative"
      echo "Erreur fonction lanceDefectionClient : Incohérence dans l'algo!! LA balance est négative ! \n";
      exit();
    }
  } else
    if ($myErr->errCode == NO_ERR)
      $balance = 0;
    else {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Incohérence dans l'algo!! Erreur :".$myErr->errCode
      echo "Erreur fonction lanceDefectionClient : Incohérence dans l'algo!! Erreur :".$myErr->errCode." ! \n";
      exit();
    }

  // Le cas d'un client EAV est un peu particulier, il n'y a aucun opération financière à effectuer
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1) {
    // Défection client EAV
    // Rien à faire à ce niveau
  } else {
    // Mise à jour du solde_clot du compte de base
    $ACC = getAccountDatas($cptBase);
    $soldeCpt = $ACC["solde"];
    $sql = "UPDATE ad_cpt SET date_clot = '" . date("d/m/Y") . "', solde_clot = $soldeCpt WHERE id_ag=$global_id_agence AND id_cpte = $cptBase;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "Erreur du fonction lanceDefectionClient ! \n";
      exit();
    }

    $comptable_his = array ();
    $myErr = soldeTousComptes($id_client, $comptable_his);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* Si la balance est > 0, retrait du solde du compte de base arrondi à l'unité monétaire la plus petite */
    /*if ($balance > 0) {
      global $error;
      $baseAccountID = getBaseAccountID($id_client);
      $CPT = getAccountDatas($baseAccountID);

      $balanceArrondie = arrondiMonnaie($balance, -1);
      if ($balanceArrondie > 0) {
        // Débit du compte de base, crédit du compte guichet, c'est le même cptes_substitue pour l'arrondie
        $cptes_substitue = array ();
        $cptes_substitue["cpta"] = array ();
        $cptes_substitue["int"] = array ();

        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($baseAccountID);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $baseAccountID;
        $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);

        $myErr = passageEcrituresComptablesAuto(140, $balanceArrondie, $comptable_his, $cptes_substitue);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(true);
          return $myErr;
        }
      }

      // Transfert du reste de l'arrondi vers le compte agence
      $reste = $balance - $balanceArrondie;
      if ($reste > 0) {
        // Débit du compte de base, crédit du compte paramétré au crédit de l'opération 321
        $cptes_substitue = array ();
        $cptes_substitue["cpta"] = array ();
        $cptes_substitue["int"] = array ();

        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($baseAccountID);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        $cptes_substitue["int"]["debit"] = $baseAccountID;

        $myErr = passageEcrituresComptablesAuto(321, $reste, $comptable_his, $cptes_substitue);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(true);
          return $myErr;
        }
      }

    }*/ /* Fin if (balance > 0) */
  } /* Fin else  non client EAV */

  // Défection proprement dite
  $myErr = defectionClient($id_client, $etat, $raison_defection, $comptable_his, $cptPs);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  global $global_nom_login;
  $infos = "Defection client par lots";
  $myErr = ajout_historique(15, $id_client, $infos, $global_nom_login, date("r"), $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;
  //Solder le compte de base du client à la fin de la défection
  $sql = "UPDATE ad_cpt SET solde = 0 WHERE id_ag = $global_id_agence AND id_cpte = $cptBase;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
    echo "Erreur du fonction lanceDefectionClient ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $id_his);
}
/*----------------------------------------------------------------------------*/
/*---------------------SoldeTousComptes-----------------------------------------*/
function soldeTousComptes($id_client, &$comptable_his) {
  global $global_id_agence, $global_monnaie;
  global $dbHandler;
  $dummy=array(); // Array vide nécessaire à  la fonction clotureCompteEpargne.

  $db = $dbHandler->openConnection();

  $CPTS = get_comptes_epargne($id_client); // les comptes epargne qui sont services financiers

  // On vérifie si un des comptes est bloqué
  reset($CPTS);
  while (list($key, $cpt) = each($CPTS))
    if ($cpt['etat_cpte'] == 3) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Il reste des comptes bloqués."));
      echo "Erreur dans la fonction soldeTousComptes() => il reste des comptes Bloques \n";
      exit();
    }

  $balance = getBalance ($id_client);

  // Vérifie que seule une balance dans la devise de référence est présente
  if (sizeof($balance) > 1) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Il y a une balance non nulle dans une devise étrangère"));
    echo "Erreur dans la fonctions soldeTousComptes() => Il y a une balance non nulle dans une devise étrangère(balance > 1) \n";
    exit();
  }
  if (!isset($balance["$global_monnaie"])) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Il y a une balance non nulle dans une devise étrangère"));
    echo "Erreur dans la fonctions soldeTousComptes() => Il y a une balance non nulle dans une devise étrangère \n";
    exit();
  }

  // Ici, on sait qu'on a suffisemment d'argent.
  $baseAccountID = getBaseAccountID($id_client);
  reset($CPTS);

  // Supprimer le compte de base de la lsite des comptes à clôturer
  unset($CPTS[$baseAccountID]);
  // Réaliser les clôtures pour de bon
  reset($CPTS);

  // clôture des comptes d'épargne de service financier sauf le compte de base
  while (list($key, $cpt) = each($CPTS)) {
    $myErr = clotureCompteEpargne($key, 1, 2, $baseAccountID, $comptable_his, $dummy);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }
  // Cloture du compte de parts sociales
  $idCptPS = getPSAccountID($id_client);
  if ($idCptPS != NULL) { // Dans le cas contraire, le client est EAV au auxiliaire et ne possède pas de compte PS
    $myErr = clotureCompteEpargne($idCptPS, 1, 2, $baseAccountID, $comptable_his, $dummy);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // Traitements liés aux crédits
  /* Le client a-t-il des crédits en cours ? */

  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value) {
    $solde_credit = 0;
    $DOSS = getDossierCrdtInfo($id_doss);
    $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }


    /* - Annulation garantie à constituer restant due.Elle n'est comptabilisée ni dans les avoirs ni dans les dettes du client
         - Compte de liaison étant fermé (si c'est pas le compte de base), prendre alors compte de base comme compte de liaison .
    */
    $myErr = supprimeReferenceCredit($id_client,$id_doss);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* Récupération de l'épargne nantie numéraire du dossier */
    /* Le compte de garantie n'étant pas un service financier, il ne fait pas partie des comptes fermés ci-dessus */
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$val ) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        $CPT_GAR = getAccountDatas($nantie);
        if ($CPT_GAR['id_titulaire'] == $id_client) {
          /* A ce niveau le compte de liaison est peut être fermé. Effectuer donc le virement dans le compte de base */
          $myErr = clotureCompteEpargne($nantie, 1, 2, $baseAccountID, $comptable_his, $dummy);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
        } else { /* le compte de garantie appartient à un autre client, il faut restituer la garantie */
          /* id du compte de base du client garant */
          $id_cpt_base_gar = getBaseAccountID($CPT_GAR['id_titulaire']);

          /* Virement de la garantie dans le compte de base du cleint garant */
          $myErr = clotureCompteEpargne($nantie, 1, 2, $id_cpt_base_gar, $comptable_his,NULL);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
        }
      }
    } /* Fin foreach($liste_gar as $key=>$val ) */
  }
  foreach($dossiers as $id_doss=>$value) {
    // Apurement des crédits : on sait qu'on peut l'apurer
    $myErr = apurementCredit($id_doss,$comptable_his);

    if ($myErr->errCode != NO_ERR) {
      global $error;
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function apurementCredit($id_doss,&$comptable_his)
// PS qui effectue l'apurement des crédits en se servant à partir du compte lié
// IN : L'ID du client
// OUT: Objet erreur dont les codes sont
//   - NO_ERR : Tout est OK
//   - ERR_SOLDE_INSUFFISANT ; Solde insuffisant sur le compte de base pour effectuer l'apurement
{
  global $global_id_agence;
  global $error;
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $solde_credit = 0;
  $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $myErr = rembourse_montant($id_doss, $solde_credit, 2, $comptable_his);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function supprimeReferenceCredit($id_client,$id_doss,$annul_gar=true, $change_liaison=true)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  /* Récupération du compte de base */
  $baseAccountID = getBaseAccountID($id_client);

  /* Annulation de la garantie restant due */
  if ($annul_gar == true) {
    $sql = "UPDATE ad_etr SET solde_gar = 0 where id_ag=$global_id_agence AND id_doss = $id_doss;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql . "\n" . $result->getMessage());
      echo "Erreur dans la fonction supprimeReferenceCredit() => Erreur dans la requête SQL \n";
      exit();
    }
  }

  /* Modification du compte de liaison */
  if ($change_liaison == true) {
    $sql = "UPDATE ad_dcr SET cpt_liaison=$baseAccountID WHERE id_ag=$global_id_agence AND id_doss= $id_doss;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql . "\n" . $result->getMessage());
      echo "Erreur dans la fonction supprimeReferenceCredit() => Erreur dans la requête SQL \n";
      exit();
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}
/*-------------------------------------------------------------*/
/*-----------------------clotureCompteEpargne & sous functions-----------------------------------------*/
function clotureCompteEpargne($id_cpte, $raison_cloture, $dest, $id_cpte_dest, &$comptable,$frais=array()) {

  global $dbHandler, $global_id_client, $global_id_agence, $global_nom_login, $global_monnaie;

  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  // Bloquer d'abord le compte pour qu'il n'y ait pas d'opérations financières dessus
  blocageCompteInconditionnel($id_cpte);

  // A partir de ce moment, nous sommes à l'intérieur d'une transaction, les autres utilisateurs voient le compte comme bloqué
  $db = $dbHandler->openConnection();

  deblocageCompteInconditionnel($id_cpte);

  $erreur = checkCloture($id_cpte);

  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  if (isset($frais["fermeture"]) && check_access(299))
    $frais_fermeture_modif = $frais["fermeture"];

  if (isset($frais["tenue"]) && check_access(299))
    $frais_tenue_modif = $frais["tenue"];

  if (isset($frais["penalites"]) && check_access(299))
    $penalites_modif = $frais["penalites"];

  $id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);

  $devise = $InfoCpte["devise"];
  $dev_ref = $global_monnaie;

  $RET = array(); // Tableau qui sera renvoyé à l'appelant

  // Si le compte était en attente de fermeture, on procède directement au virement du solde
  if ($InfoCpte["etat_cpte"] == 5) {
    $solde_cloture = $InfoCpte["solde"];
  } else {
    // calcul des intérêts en fonction du paramétrage du produit
    // si 30j ou Fin de mois, et en cas de rupture anticipée : aucun, prorata, tout

    // Dans le cadre d'une cloture, les intérets sont toujours versés sur le compte lui-meme
    $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];

    $erreur = arreteCompteEpargne($InfoCpte, $InfoProduit, $comptable);

    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    $solde_cloture = $erreur->param["solde_cloture"];
    $RET["mnt_int"] = $erreur->param["int"];

    // Prélèvement des pénalités
    if ($InfoProduit["terme"] > 0) {
      $erreur = prelevePenalitesEpargne($id_cpte, $comptable, $penalites_modif);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
      $solde_cloture -= $erreur->param;
      $RET["mnt_pen"] = $erreur->param;
    }

    // Frais de tenue de compte
    /*$erreur = preleveFraisDeTenue($id_cpte, $comptable, $frais_tenue_modif);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
    $solde_cloture -= $erreur->param;
    $RET["mnt_frais_tenue"] = $erreur->param;*/

    //frais de fermeture
    /*$erreur = preleveFraisFermeture($id_cpte, $comptable, $frais_fermeture_modif);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
    $solde_cloture -= $erreur->param;
    $RET["mnt_frais_fermeture"] = $erreur->param;*/
  }

  // Cas spécifique de la cloture par le batch : dans ce cas $dest = 2 mais aucun compte n'a été spécifié

  if ($dest == 2 && $id_cpte_dest == NULL) {
    global $global_cpt_base_ouvert;
    if ($devise == $dev_ref) { // On peut transférer sur le compte de base
      if ($global_cpt_base_ouvert) {
        $id_cpte_dest = getBaseAccountID($InfoCpte["id_titulaire"]);
        $attente_cloture = false;
      } else {
        $attente_cloture = true;
      }
    } else {
      $attente_cloture = true;
    }
  } else {
    $attente_cloture = false;
  }

  if ($attente_cloture == true) {
    // Il faut mettre le compte dans un état intermédiaire. On ne veut pas forcer la conversion
    $updateFields = array("etat_cpte" => 5);
    $where = array("id_cpte" => $id_cpte,'id_ag'=>$global_id_agence);
    $sql = buildUpdateQuery("ad_cpt", $updateFields, $where);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
      echo "Erreur dans la fonction clotureCompteEpargne() => Passage a un etat intermediaire \n";
      exit();
    }
    $RET['attente'] = true;
  }

  else {

    // Virement du solde du compte à clôturer
    $erreur = vireSoldeCloture ($id_cpte, $solde_cloture, $dest, $id_cpte_dest, $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    if(($raison_cloture != 2 ) || ($InfoProduit["classe_comptable"] != 6)){// on ne ferme pas le compte pour les épargnes à la source si c'est une demande du client
      //fermeture du compte, raison clôture "Sur demande du client"
      $erreur = fermeCompte($id_cpte, $raison_cloture, $solde_cloture);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    }else{// on intialise certains champs (solde de calcul des intérêts,...) pour les épargnes à la source en cas de demande du client
      $updateFields = array("solde_calcul_interets" => 0, "date_calcul_interets"=>date("d/m/Y"), "date_solde_calcul_interets"=>date("d/m/Y"), "interet_a_capitaliser"=>0, "interet_annuel"=>0);
      $where = array("id_cpte" => $id_cpte,'id_ag'=>$global_id_agence);
      $sql = buildUpdateQuery("ad_cpt", $updateFields, $where);
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
        echo "Erreur dans la fonction clotureCompteEpargne() => intialisation certains champs \n";
        exit();
      }
    }
    $RET['attente'] = false;
  }

  $RET["solde_cloture"] = $solde_cloture;

  // Invalidation des mandats liés au compte
  $MANDATS = getMandats($id_cpte);
  if (is_array($MANDATS))
    foreach ($MANDATS as $key=>$value) {
      invaliderMandat($key);
    }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $RET);
}

function fermeCompte ($id_cpte, $raison_cloture, $solde_cloture, $date_cloture=NULL) {
  /*  $ACC = getAccountDatas($id_cpte);
  if ($ACC["solde"] != $solde_cloture)
    return new ErrorObj(ERR_CPTE_SOLDE_NON_NUL, ($ACC["solde"] - $solde_cloture));
  */

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $fields_array = array();
  $fields_array["etat_cpte"] = 2; // Compte fermé
  $fields_array["raison_clot"] = $raison_cloture;
  if ($date_cloture == NULL)
    $fields_array["date_clot"] = date("d/m/Y");
  else
    $fields_array["date_clot"] = $date_cloture;

  $fields_array["solde_clot"] = $solde_cloture;

  $sql = buildUpdateQuery ("ad_cpt", $fields_array, array("id_cpte"=>$id_cpte,'id_ag'=>$global_id_agence));

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
    echo "Erreur de la fonction fermeCompte->".$result->getMessage()."\n";exit();
  };

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}


function invaliderMandat($id_mandat) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA['valide'] = 'f';
  $WHERE['id_mandat'] = $id_mandat;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildUpdateQuery('ad_mandat', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction invaliderMandat ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function vireSoldeCloture($id_cpte, $solde_cloture, $dest, $id_cpte_dest, &$comptable, $type_oper=NULL) {

  global $dbHandler, $global_id_guichet, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $classe_comptable = $ACC["classe_comptable"];
  $devise = $ACC["devise"];
  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($solde_cloture != 0) {

    if($type_oper == NULL ) {
      switch ($classe_comptable) {
        case 1:
        case 2:
        case 3:
        case 5:
        case 6:
          $type_oper = ($dest == 1? 61 : 62);
          break;
        case 4:
          $type_oper = 81;
          break;

        default:
          $dbHandler->closeConnection(false);
          //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Classe comptable incorrecte !"
          echo "Erreur du fonction vireSoldeCloture ! Classe comptable incorrecte \n";
          exit();
      }
    }

    // Passage écritures comptables
    //débit compte client / crédit compte de base client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $id_cpte;

    if ($dest == 1) { // Destination guichet
      $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($global_id_guichet); echo "Ligne 2465 : TESTING!! ID Gui -> ".$global_id_guichet."\n";

      // Traitement des arrondis
      $mnt_dec = arrondiMonnaie($solde_cloture, -1, $devise);
      if ($solde_cloture != $mnt_dec && $devise != $dev_ref) {
        $diff = $solde_cloture - $mnt_dec;
        $diff_dev_ref = calculeCV($devise, $dev_ref, $diff);
        if ($diff_dev_ref > 0) {
          // Passer d'abord une écriture de change pour le reliquiat
          $myErr = effectueChangePrivate($devise, $dev_ref, $diff, 455, $cptes_substitue, $comptable);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
          $solde_cloture -= $diff;
        }
      }

      $erreur = passageEcrituresComptablesAuto ($type_oper, $solde_cloture, $comptable, $cptes_substitue, $devise);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    } else { // Destination $id_cpte_dest
      $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_dest);
      if ($cptes_substitue["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }
      $cptes_substitue["int"]["credit"] = $id_cpte_dest;

      /* Vérifier que les comptes source et destination ont la même devise */
      $CPT_DEST = getAccountDatas($id_cpte_dest);
      if ($devise == $CPT_DEST['devise']) {
        $erreur = passageEcrituresComptablesAuto ($type_oper, $solde_cloture, $comptable, $cptes_substitue, $devise);
        if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
        }
      } else { /* les comptes sont de devises différentes */
        $myErr = effectueChangePrivate($devise, $CPT_DEST['devise'], $solde_cloture, $type_oper, $cptes_substitue, $comptable);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
    }
  }


  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}

function getCompteCptaGui($id_gui) {
  /*
    Renvoie le compte comptable associé à un guichet
  */

  global $dbHandler, $global_id_client,$global_id_agence, $erreur;
  $db = $dbHandler->openConnection();

  if(($id_gui == null) or ($id_gui == '')){
    //erreur("getCompteCptaGui", sprintf(_("Le numéro du guichet n'est pas renseigné.")));
    echo "Erreur du fonction getCompteCptaGui ! Le numéro du guichet n'est pas renseigné. \n";
    exit();
  }else {
    $sql = "SELECT cpte_cpta_gui ";
    $sql .= "FROM ad_gui  ";
    $sql .= "WHERE id_ag = $global_id_agence AND id_gui = '$id_gui'";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    echo "Erreur du fonction getCompteCptaGui ! \n";
    exit();
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
    echo "Erreur du fonction getCompteCptaGui ! Aucun compte associé. Veuillez revoir le paramétrage \n";
    exit();
  }
  $row = $result->fetchrow();
  $cpte_cpta = $row[0];

  $dbHandler->closeConnection(true);
  return $cpte_cpta;

}

function preleveFraisFermeture($id_cpte, &$comptable, $frais_fermeture = NULL, $id_cpte_ps) {
  /*
    Lors de la fermeture d'un compte d'épargne, on prend les frais de fermeture s'il y en a
  */

  global $dbHandler, $global_client_debiteur, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $ACC_PS_ID = $id_cpte_ps;
  $ACC_PS["solde"]=0; //par defaut zero : les clients qui n'ont pas des Part Sociales
  if ($ACC_PS_ID != "" || $ACC_PS_ID != null){ //les clients qui ont des Part Sociales
    $ACC_PS = getAccountDatas($ACC_PS_ID);
  }
  if (!isset($frais_fermeture)){
    $frais_fermeture = $ACC["solde"]+$ACC_PS["solde"];
  }

  $devise = $ACC["devise"];
  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($frais_fermeture > 0) {
    //débit compte de base / crédit compte de produit
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;

    $erreur = effectueChangePrivate($devise, $dev_ref, $frais_fermeture, 60, $cptes_substitue, $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

  }//if frais fermeture > 0

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $frais_fermeture);

}

function preleveFraisDeTenue($id_cpte, &$comptable, $frais_tenue= NULL) {
  global $dbHandler, $global_client_debiteur, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $PROD = getProdEpargne($ACC["id_prod"]);

  if (!isset($frais_tenue))
    $frais_tenue = $PROD["frais_tenue_cpt"];

  $devise = $ACC["devise"];
  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($frais_tenue > 0) {
    //ne pas mvter les cptes si le montant est nul

    //débit compte de base / crédit compte de produit
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;

    $erreur = effectueChangePrivate($devise, $dev_ref, $frais_tenue, 50, $cptes_substitue, $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $frais_tenue);

}

function blocageCompteInconditionnel ($id_cpte) {
  /*
   Cette PS bloque le compte $id_cpte
   */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT etat_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result)){
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction blocageCompteInconditionnel() => Selection etat Cpte \n";
    exit();
  }

  $tmprow = $result->fetchrow();
  $etat = $tmprow[0];

  if ($etat == 2){  // Le compte est fermé
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de bloquer le compte $id_cpte qui est fermé"
    echo "Erreur dans la fonction blocageCompteInconditionnel() =>  si etat =2 \n";
    exit();
  }


  //on change l'état du compte à  bloqué
  $sql = "UPDATE ad_cpt SET etat_cpte = 3 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result)){
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction blocageCompteInconditionnel() => Mise a jour etat_cpte \n";
    exit();
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}


function arreteCompteEpargne($InfoCpte=array(), $InfoProduit=array(), &$comptable) {
  /*
    Calcul les intérêts pour les comptes rémunérés DAV, DAT, Autres dépôts, Capital social arrivés à échéance
    en principe appelée pour une rupture anticipée
    OUT  objet Error contenant en paramètre le solde de cloture
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  /* Récupération du taux de base de l'épargne de l'agence */
  $AG = getAgenceDatas($global_id_agence);
  if ($AG["base_taux_epargne"] == 1)
    $base_taux = 360;
  elseif($AG["base_taux_epargne"] == 2)
    $base_taux = 365;

  /* Initialisation des intérêts à la rupture */
  $interets = 0;

  /* Si c'est un compte à terme ( DAT ou CAT ) qui n'est pas en attente */
  if ($InfoCpte["terme_cpte"] > 0 and $InfoCpte["etat_cpte"] != 5) {
    $today = date("d/m/Y");
    $temp_today = explode("/", $today);
    $temp_today = mktime(0,0,0,$temp_today[1],$temp_today[0],$temp_today[2]);

    $date_fin = pg2phpDate($InfoCpte["dat_date_fin"]);
    $temp_date_fin = explode("/", $date_fin);
    $temp_date_fin = mktime(0,0,0,$temp_date_fin[1],$temp_date_fin[0],$temp_date_fin[2]);

    /* s'agit-il d'une rupture anticipée ? */
    if ($temp_today <= $temp_date_fin) {
      if ($InfoProduit["mode_calcul_int_rupt"]== 1)  /* Sans intérêts à la rupture */
        $interets = 0 ;
      elseif ($InfoProduit["mode_calcul_int_rupt"] == 2 or $InfoProduit["mode_calcul_int_rupt"] == 3) {
        /* Intérêts au prorata ou Intérêts sur le reste du terme */
        $date_ouv = $InfoCpte["date_ouvert"];
        $date_las_cap = $InfoCpte["date_calcul_interets"];
        $mode_paie = $InfoCpte["mode_paiement_cpte"];
        $freq_cap = $InfoCpte["freq_calcul_int_cpte"];
        $terme = $InfoCpte["terme_cpte"];

        /* Intérêts au prorata, récupérer nombre de mois entre date du jour et dernière capitalisation (ou date ouverture)  */
        if ($InfoProduit["mode_calcul_int_rupt"] == 2)
          $date_cap = date("d/m/Y");

        /* Intérêts pour le reste du terme, récupérer nombre de mois entre date fin du compte  et dernière capitalisation  */
        if ($InfoProduit["mode_calcul_int_rupt"] == 3)
          if (isset($InfoCpte["dat_date_fin"]))
            $date_cap = $InfoCpte["dat_date_fin"];
          else
            $date_cap = date("d/m/Y");

        $nb_jours = getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap);

        /* Calcul des intérêts à payer à la rupture */
        $interets = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours)/ $base_taux;
        if($InfoCpte['mode_calcul_int_cpte'] == 12){// Si mode épargne à la source, intérêts prend la valeur du champs interet_a_capitaliser qui cumule les intérêts entre deux dates de capitalisation
          $interets = $InfoCpte['interet_a_capitaliser'];
        }
        $interets = arrondiMonnaie($interets, 0, $InfoCpte['devise']);
      }else $interets = 0;

    } /* Fin si date du jour < date de fin  */

    /* Si le compte de versement des intérêts n'est pas renseigné, prendre le compte lui-même */
    if (!isset($InfoCpte["cpt_vers_int"]) or $InfoCpte["cpt_vers_int"] == NULL)
      $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];

    if ($InfoCpte["cpt_vers_int"] == $InfoCpte["id_cpte"])
      $solde_cloture = $InfoCpte["solde"] + $interets;
    else
      $solde_cloture = $InfoCpte["solde"];

    /* Versement des intérêts */
    if ($interets > 0) {
      $erreur = payeInteret($InfoCpte["id_cpte"], $InfoCpte["cpt_vers_int"], $interets, $comptable);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    }
  } else { /* C'est pas un compte à terme */
    /* Pas de calcul d'intérêts ni de pénalités */
    $solde_cloture = $InfoCpte["solde"]; /* solde courant du compte */

    /* Cas des comptes de garantie.On considère que le crédit est soldé et on qu'on veut clôturer le compte de garantie */
    /* On considère que solde clôture = solde courant + les garanties incluses dans les derniers remboursements non encore commités */

    if ($InfoCpte["id_prod"]== 4) { /* Si c'est un compte de garantie */
      /* Parcours des écritures comptables en attente */
      if (is_array($comptable))
        foreach($comptable as $key=>$value) {
          /* S'il y a des mouvements en attente pour le compte de garantie */
          if ($value["cpte_interne_cli"] == $InfoCpte["id_cpte"]) {
            if ($value["sens"] == SENS_CREDIT and $value["montant"] > 0)
              $solde_cloture += $value["montant"];
            elseif($value["sens"] == SENS_DEBIT and $value["montant"] > 0)
              $solde_cloture -= $value["montant"];
          }
        }
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('solde_cloture' => $solde_cloture, 'int' => $interets));

}

function payeInteret($id_cpte, $id_cpte_dest, $interets, &$comptable)
{
  // FIXME : que fait-on avec les comptes bloqués ?

  global $global_id_agence, $dbHandler, $global_monnaie;

  $db = $dbHandler->openConnection();

  $ACC = getAccountDatas($id_cpte);
  $devise = $ACC["devise"];

  //versement de l'intérêt : débit compte charge / crédit compte client
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEpInt($id_cpte);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable des intérêts associé au produit d'épargne"));
  }

  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_dest);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte_dest;

  // Les intérts sont comptabilisés au débit en devise de référence, il faut donc appeler effectueChangePrivate en mettant la varialbe mnt_debit ) false car c'est le montant au crédit qui est fourni

  $erreur = effectueChangePrivate($global_monnaie, $devise, $interets, 40, $cptes_substitue, $comptable, false);

  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  //FIXME : Doit-on mettre à jour solde calcul intérêts à solde ?

  // màj champ interets-annuels du compte
  // FIXME : il faut réinitialiser ce champ en fin d'exo
  $sql = "UPDATE ad_cpt ";
  $sql .= "SET interet_annuel = interet_annuel + $interets ";
  $sql .= "WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction payeInteret ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}


function checkCloture($id_cpte) {

  global $dbHandler,$global_id_agence;

  $InfoCpte = getAccountDatas($id_cpte);

  //vérifier qu'il ne s'agit pas du compte de base
  $id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);

  if ($id_cpte == $id_cpte_base) {
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);
  }

  if ($InfoCpte["etat_cpte"] == 3) {
    return new ErrorObj(ERR_CPTE_BLOQUE);
  }

  if ($InfoCpte["etat_cpte"] == 4)
    return new ErrorObj(ERR_CPTE_DORMANT);

  $db = $dbHandler->openConnection();

  // Vérifier si ce compte n'est pas un compte de prélèvement d'une garantie bloquée
  $sql = "SELECT count(*) FROM ad_gar WHERE id_ag=$global_id_agence AND gar_num_id_cpte_prelev = $id_cpte AND etat_gar IN (1,2)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction checkCloture() => Verification si pas un cpte garantie \n";
    exit();
  }
  $tmprow = $result->fetchrow();

  if ($tmprow[0] > 0) { // Il y a au moins un crédit ou compte de garanties lié
    $dbHandler->closeConnection(true);
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);
  }

  /* Vérifié si le compte n'est pas un compte de liaison d'un crédit en cours */
  $sql = "SELECT count(*) FROM ad_dcr WHERE id_ag=$global_id_agence AND etat IN (1,2,5,7,14,15) AND cpt_liaison = $id_cpte";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction checkCloture() => Verifie si compte de liaison d'un crédit en cours \n";
    exit();
  }
  $tmprow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  if ($tmprow[0] > 0) // Il y a au moins un crédit ou compte de garanties lié
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);

  return new ErrorObj(NO_ERR);
}

function prelevePenalitesEpargne($id_cpte, &$comptable, $penalites=NULL) {
  global $dbHandler, $global_monnaie_prec, $global_client_debiteur, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $PROD = getProdEpargne($ACC["id_prod"]);

  // On vérifie d'abord qu'il s'agit bien d'un compte à terme
  if ($PROD["terme"] > 0) {

    $db = $dbHandler->openConnection();

    $cpte_date_fin = $ACC["dat_date_fin"];
    $solde = $ACC["solde"];
    if (isset($penalites)) {
      $penalites_const = $penalites;
      $penalites_prop = 0;
    } else {
      $penalites_const = $PROD["penalite_const"];
      $penalites_prop = $PROD["penalite_prop"];
    }
    $devise = $ACC["devise"];
    $dev_ref = $global_monnaie;
    $DEV = getInfoDevise($devise);

    $today = date("d/m/Y");
    $today = explode("/", $today);
    $today = mktime(0,0,0,$today[1],$today[0],$today[2]);

    $date_fin = pg2phpDate($cpte_date_fin);
    $date_fin = explode("/", $date_fin);
    $date_fin = mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]);

    //si rupture anticipée
    if ( $date_fin > $today ) {

      if (($penalites_const > 0) || ($penalites_prop > 0)) {
        //FIXME : on prend quel solde pour calculer les pénalités ?
        $penalites = ($penalites_const + ($solde * $penalites_prop));
        $penalites = round($penalites, $DEV["precision"]);

        if ($penalites > 0) {
          //FIXME : est-ce que c'est la bonne manière de faire ?
          //Si le client est débiteur , on ne pourra pas prendre les penalites  sur le compte de base
          /* OBSOLETE
          if ($global_client_debiteur)
            {
              $dbHandler->closeConnection(false);
              return new ErrorObj(ERR_CLIENT_DEBITEUR);
              } */

          //débit compte à cloturer / crédit compte de produit
          $cptes_substitue = array();
          $cptes_substitue["cpta"] = array();
          $cptes_substitue["int"] = array();

          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
          if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
          }

          $cptes_substitue["int"]["debit"] = $id_cpte;

          $erreur = effectueChangePrivate($devise, $dev_ref, $penalites, 110, $cptes_substitue, $comptable);
          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          }

        }//if pénalités > 0

      }//if pénalités

    } //if date > today
    $dbHandler->closeConnection(true);
  } else {
    return new ErrorObj(ERR_NON_CAT);
  }


  return new ErrorObj(NO_ERR, $penalites);
}
/*----------------------------------------------------------------*/
/*-----------------EffectueChangePrivate---------------------------------------------*/
function effectueChangePrivate ($devise_achat, $devise_vente, $montant, $type_oper, $subst, &$comptable, $mnt_debit=true, $cv=NULL, $info_ecriture=NULL, $infos_sup=NULL, $date_comptable = NULL) {


  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Vérifie que les devises sont renseignées
  if ($devise_achat == '' || $devise_vente == '') {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Devises non renseignées"));
    echo "Erreur du fonction effectueChangePrivate ! \n";
    exit();
  }
  if ($devise_achat == $devise_vente) {
    // Pas d'opération de change à réaliser
    $result = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $subst, $devise_achat, $date_comptable, $info_ecriture, $infos_sup);
    $montant_debit = $montant;
    $montant_credit = $montant;
  } else {
    if ($mnt_debit == true) {
      // $montant représente un montant à débiter en $devise_achat
      $montant_debit = $montant;
      if ($cv == NULL)
        $montant_credit = calculeCV($devise_achat, $devise_vente, $montant);
      else
        $montant_credit = $cv;
    } else {
      // $montant représente un montant à créditer en $devise_vente
      $montant_credit = $montant;
      if ($cv == NULL)
        $montant_debit = calculeCV($devise_vente, $devise_achat, $montant);
      else
        $montant_debit = $cv;
    }

    // On récupère la devise de référence
    global $global_monnaie;
    $dev_ref = $global_monnaie;

    // Passage des écritures relatives à la devise d'achat
    $cptes = $subst;
    if ($devise_achat != $dev_ref) {
      $cpt_devise=getCptesLies($devise_achat);
      $cptes["cpta"]["credit"] = $cpt_devise['position'];
    } else {
      $cpt_devise=getCptesLies($devise_vente);
      $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
    }

    $cptes["int"]["credit"] = NULL;
    $result = passageEcrituresComptablesAuto($type_oper, $montant_debit, $comptable, $cptes, $devise_achat,$date_comptable,$info_ecriture,$infos_sup);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }

    // Passage des écritures relatives à la devise de vente
    $cptes = $subst;
    if ($devise_vente != $dev_ref) {
      $cpt_devise=getCptesLies($devise_vente);
      $cptes["cpta"]["debit"] = $cpt_devise['position'];
    } else {
      $cpt_devise=getCptesLies($devise_achat);
      $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
    }
    $cptes["int"]["debit"] = NULL;
    $result = passageEcrituresComptablesAuto($type_oper, $montant_credit, $comptable, $cptes, $devise_vente,$date_comptable,$info_ecriture,$infos_sup);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }

    // Passage des écritures relatives à la devise de référence (intermédiaire)
    if (($devise_achat != $dev_ref) && ($devise_vente != $dev_ref)) {
      // Recherche de la CV en devise de référence
      $cv_montant_dev_ref = calculeCV($devise_achat, $dev_ref, $montant_debit);
      $cptes = $subst;
      $cpt_devise=getCptesLies($devise_achat);
      $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
      $cptes["int"]["debit"] = NULL;
      $cpt_devise=getCptesLies($devise_vente);
      $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
      $cptes["int"]["credit"] = NULL;
      $result = passageEcrituresComptablesAuto($type_oper, $cv_montant_dev_ref, $comptable, $cptes, $dev_ref,$date_comptable,$info_ecriture,$infos_sup);
    }
  }

  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }

  // Préparation des valeurs de retour
  $param_result = array("montant_debit" => $montant_debit, "montant_credit" => $montant_credit);
  $result = new ErrorObj(NO_ERR, $param_result);
  $dbHandler->closeConnection(true);
  return $result;
}

function calculeCV($devise1, $devise2, $montant) {

  if ($devise1 == $devise2)
    return $montant;

  $taux = getTauxChange($devise1, $devise2, false);
  $cv_montant = $montant * $taux;
  $DEV = getInfoDevise($devise2);
  $cv_montant = round($cv_montant, $DEV["precision"]);
  return $cv_montant;
}
function getTauxChange($devise1, $devise2, $commercial, $type=NULL) {
  // Recherche infos devise 1
  $DEV1 = getInfoDevise($devise1);
  if (!is_array($DEV1)) { // La devise 1 n'existe pas
    return NULL;
  }

  // Recherche infos devise 2
  $DEV2 = getInfoDevise($devise2);
  if (!is_array($DEV2)) { // La devise 2 n'existe pas
    return NULL;
  }

  if (!$commercial) { // C'est le taux indicatif qui dpoit etre utilisé
    $field_taux1 = "taux_indicatif";
    $field_taux2 = "taux_indicatif";
  } else { // On prend le taux achat de $devise1 et le taux vente de $devise2 pour maximiser le bénéfice
    if ($type == 1) { // CASH
      $field_taux1 = "taux_achat_cash";
      $field_taux2 = "taux_vente_cash";
    } else if ($type == 2) { // TRANSFERT
      $field_taux1 = "taux_achat_trf";
      $field_taux2 = "taux_vente_trf";
    }
  }

  // Calcul du taux réel
  $taux_change = round($DEV2[$field_taux2] / $DEV1[$field_taux1], 12);
  return $taux_change;
}


function getCptesLies($devise) {
  global $global_id_agence;
  $comptes=array();
  $AG = getAgenceDatas($global_id_agence);
  $cpt_pos_ch = $AG["cpte_position_change"];
  $cpt_cv_pos_ch = $AG["cpte_contreval_position_change"];
  $cpt_credit = $AG["cpte_variation_taux_cred"];
  $cpt_debit = $AG["cpte_variation_taux_deb"];
  $comptes['position']=$cpt_pos_ch.".".$devise;
  $comptes['cvPosition']=$cpt_cv_pos_ch.".".$devise;
  $comptes['debit']=$cpt_debit.".".$devise;
  $comptes['credit']=$cpt_credit.".".$devise;
  return $comptes;
}

/*----------------------------------------------------------------*/

/*-------------------------Function du testDefection()--------------------------------------------------*/

function testDefection($id_client) {
// Cette fonction vérifie
// - que le client est EAV ou que dans le cas contraire
//    - qu'aucun compte n'est bloqué
//    - que la balance est bien nulle pour la devise de référence. // Contradictoire avec la phrase suivante
// Renvoie un objet Erreur avec éventuellement en paramètre la balance dans le devise de référence si celle-ci n'est pas nulle

  global $global_id_agence, $global_monnaie;
  global $dbHandler;

  $db = $dbHandler->openConnection();

  // Si le client est EAV la défection est autorisée
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1) {
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
  }

  $CPTS = get_comptes_epargne($id_client);

  // On vérifie si un des comptes est bloqué
  reset($CPTS);
  while (list($key, $cpt) = each($CPTS))
    if ($cpt['etat_cpte'] == 3) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPT_BLOQUE, $cpt['id_cpte']);
    }

  $balance = getBalance($id_client);

  // Vérifie qu'on a suffisemment d'argent pour effectuer la clô´ture
  if (!isArrayNull($balance)) {
    $dbHandler->closeConnection(true);
    return new ErrorObj(ERR_DEF_SLD_NON_NUL, $balance[$global_monnaie]);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}
//Fonction qui determine si un client est radié
function is_client_radie(){
  global $global_etat_client;
  if(in_array($global_etat_client, array(3,5,6))) {
    return true;
  }
  else {
    return false;
  }
}


function get_comptes_epargne($id_client, $devise=NULL) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id AND ";
  $sql .= "a.id_titulaire = '$id_client' and b.service_financier = true and b.classe_comptable <> 8";
  // On ne prend pas les comptes bloqués
  if (!is_client_radie()){
    $sql .= " AND (a.etat_cpte <> 2)";
  }
  if ($devise != NULL)
    $sql .= " AND a.devise = '$devise'";

  $sql .= " ORDER BY a.num_complet_cpte";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction get_comptes_epargne | Id Client :".$id_client."\n";
    exit();
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;

  $TMPARRAY = array();
  while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$prod["id_cpte"]] = $prod;
    $TMPARRAY[$prod["id_cpte"]]["soldeDispo"] = getSoldeDisponible($prod["id_cpte"]);
  }

  return $TMPARRAY;
}

function getSoldeDisponible($id_cpte) {
  // Remplir 2 tableaux avec toutes les infos sur le compte et le produit associé
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  if ($InfoProduit["retrait_unique"] == 't' || $InfoCpte["etat_cpte"] == 3)
    $solde_dispo = 0;
  else
    $solde_dispo = $InfoCpte["solde"] - $InfoCpte["mnt_bloq"] - $InfoCpte["mnt_min_cpte"] + $InfoCpte["decouvert_max"];

  if ($solde_dispo < 0)
    $solde_dispo = 0;

  return $solde_dispo;
}

function getBalance($id_client) {
  global $error;
  global $dbHandler, $global_id_agence;
  global $global_monnaie_prec;
  global $global_monnaie;

  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();
  $balance = array();
  $balance[$dev_ref] = 0;

  // Si le client est EAV, la balance est de toute manière à  0
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1)
    return $balance;

  /* Récupération des soldes positifs des comptes services financiers */
  $CPTS = get_comptes_epargne($id_client);
  while (list($key, $cpt) = each($CPTS)) {
    $infos_simul = simulationArrete ($cpt['id_cpte']);
    $balance[$cpt["devise"]] += $infos_simul["solde_cloture"];
  }

  /* Récupération du compte de parts sociales comme il ne fait pas partie des comptes ci-dessus */
  $idCptPS = getPSAccountID($id_client);
  if ($idCptPS != NULL) {
    $infos_simul = simulationArrete ($idCptPS);
    $balance[$dev_ref] += $infos_simul["solde_cloture"];
  }

  /* Enlever le solde du crédit dans les avoirs du client */
  /* Le client a-t-il des crédits en cours ? */
  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value) {

    $solde_credit = 0;
    $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);
    if ($myErr->errCode != NO_ERR) {
      echo "Erreur du fonction getBalance : simulationArreteCPteCredit ! \n";
      exit();
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $balance[$myErr->param] -= $solde_credit;

    /* Récupération de l'épargne nantie numéraire du dossier appartenant au client */
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$val ) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        if ($nantie != NULL) { /* S'il y a un compte d'épargne nantie associé au dossier de crédit */
          $CPT_GAR = getAccountDatas($nantie);
          if ($CPT_GAR['id_titulaire'] == $id_client) {
            $infos_simul = simulationArrete($nantie);
            $balance[$CPT_GAR["devise"]] += $infos_simul["solde_cloture"];
          }
        }
      }
    }
  }

  $dbHandler->closeConnection(true);
  return $balance;
}


function getPSAccountID($id_client) {
  global $global_id_agence;
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $idProdPS = getPSProductID($global_id_agence);
  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client AND id_prod = $idProdPS AND etat_cpte = 1;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction getPSAccountID() \n";
    exit();
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  } else if ($result->numRows() >1) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Zéro ou plusieurs comptes de parts sociales pour ce client"
    echo "Erreur dans la fonction getPSAccountID() \n";
    exit();
  }
  $tmprow = $result->fetchrow();
  $idCptPS = $tmprow[0];
  $dbHandler->closeConnection(true);
  return $idCptPS;
}
function getPSProductID ($id_agence)
// Renvoie le num de produit referençant les comptes de parts sociales
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Recuperation du n° de produit d'épargne utilisé par l'agence pour les comptes de parts sociales
  $sql = "SELECT id_prod_cpte_parts_sociales FROM ad_agc WHERE id_ag = $id_agence;"; // Recherche l'état du client
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // impossible de trouver le n° de produit", $result->getMessage()
    echo "Erreur dans la fonction getPSProductID() \n";
    exit();
  }
  $tmpRow = $result->fetchrow();
  $id_prod = $tmpRow[0];
  $dbHandler->closeConnection(true);
  return $id_prod;
}

/*
 * SimulationArrete
 */
function simulationArrete($id_cpte, $frais_fermeture=NULL, $penalites=NULL, $frais_tenue=NULL) {
  global $dbHandler, $global_id_agence;

  /* Initialisation des données */
  $infos_simulation = array();
  $infos_simulation["interets"] = 0 ;

  if ($frais_fermeture == NULL)
    $infos_simulation["frais_fermeture"] = 0;
  else
    $infos_simulation["frais_fermeture"] = $frais_fermeture;

  if ($penalites == NULL)
    $infos_simulation["penalites"] = 0;
  else
    $infos_simulation["penalites"] = $penalites;

  if ($frais_tenue == NULL)
    $infos_simulation["frais_tenue"] = 0;
  else
    $infos_simulation["frais_tenue"] = $frais_tenue;

  /* Récupération des infos sur le compte */
  $InfoCpte = getAccountDatas($id_cpte);

  /* Récupération des infos sur le produit associé au compte */
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  /* Récupération du taux de base de l'épargne de l'agence */
  $AG = getAgenceDatas($global_id_agence);
  if ($AG["base_taux_epargne"] == 1)
    $base_taux = 360;
  elseif($AG["base_taux_epargne"] == 2)
    $base_taux = 365;

  /* Initialisation du solde de clôture */
  $infos_simulation["solde_cloture"] = $InfoCpte["solde"];

  // Si le compte est en attente de fermeture, son solde après arrêté est = à son solde courant
  if ($InfoCpte["etat_cpte"] == 5)
    return $infos_simulation;

  /* Si compte à terme (DAT ou CAT), à ce stade on est sûr que c'est une rupture anticipée. Calculer donc les intérêts à la rupture */
  if ($InfoCpte["terme_cpte"] > 0) {
    if ($InfoProduit["mode_calcul_int_rupt"] == 1)  /* 'Aucun intérêt à la rupture' */
      $infos_simulation["interets"] = 0;
    elseif ($InfoProduit["mode_calcul_int_rupt"] == 2 or $InfoProduit["mode_calcul_int_rupt"] == 3) {
      /* 'Intérêts au prorata' ou 'Intérêts pour le reste du terme' */
      $date_ouv = $InfoCpte["date_ouvert"];
      $date_las_cap = $InfoCpte["date_calcul_interets"];
      $mode_paie = $InfoCpte["mode_paiement_cpte"];
      $freq_cap = $InfoCpte["freq_calcul_int_cpte"];
      $terme = $InfoCpte["terme_cpte"];

      /* au prorata, récupérer nombre de mois entre date du jour et dernière capitalisation (ou date ouverture si jamais de capita)*/
      if ($InfoProduit["mode_calcul_int_rupt"] == 2)
        $date_cap = date("d/m/Y");

      /* Intérêts pour le reste du terme, récupérer nombre de mois entre date fin du compte  et dernière capitalisation  */
      if ($InfoProduit["mode_calcul_int_rupt"] == 3)
        if (isset($InfoCpte["dat_date_fin"]))
          $date_cap = $InfoCpte["dat_date_fin"];
        else
          $date_cap = date("d/m/Y");

      $nb_jours = getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap);

      /* Calcul des intérêts à payer à la rupture */
      $infos_simulation["interets"] = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours )/ $base_taux;
      if($InfoCpte['mode_calcul_int_cpte'] == 12){// Si mode épargne à la source, intérêts prend la valeur du champs interet_a_capitaliser qui cumule les intérêts entre deux dates de capitalisation
        $infos_simulation["interets"] = $InfoCpte['interet_a_capitaliser'];
      }
    } /* Fin intérêts au prorata ou pour le reste du terme */

    /* Pour une cloture,les intérêts sont versés dans le compte lui-mêmede */
    $infos_simulation["solde_cloture"] += $infos_simulation["interets"];


    if ($InfoProduit["calcul_pen_int"] == 2) {
      $calc_pen_int = true;
    }

    $int=$infos_simulation["interets"];

    /* Calcul des pénalités de rupture anticipée */
    if (!isset($penalites)) {
      if (($InfoProduit["penalite_const"] > 0) || ($InfoProduit["penalite_prop"] > 0))
        $infos_simulation["penalites"] = calculPenalites($id_cpte, $infos_simulation["solde_cloture"],($calc_pen_int==true?$int:null));
    }

  } /* Fin si compte à terme */
  else
    $infos_simulation["penalites"] = 0; /* Pas de pénalités de rupture pour les autres type de compte */

  if ($infos_simulation["penalites"] > 0)
    $infos_simulation["solde_cloture"] -= $infos_simulation["penalites"];

  /* Prélèvement des frais de fermeture */
  if (!isset($frais_fermeture)){ /* Si les frais ne sont pas renseignés, prendre par défaut les frais paramétrés dans le produit */
    $infos_simulation["frais_fermeture"] = $infos_simulation["frais_fermeture"];
    if ($InfoProduit["id"] == 1){ // || $InfoProduit["id"] == 2
      $infos_simulation["frais_fermeture"] = $InfoCpte["solde"];
    }
    //$infos_simulation["frais_fermeture"] = $InfoCpte["solde"];//$InfoProduit["frais_fermeture_cpt"];
  }

  if ($infos_simulation["frais_fermeture"] > 0) {
    $infos_simulation["solde_cloture"] -= $infos_simulation["frais_fermeture"];
  }

  /* Si les frais de tenue ne sont pas renseignés, prendre par défaut les frais paramétrés dans le produit */
  /*if (!isset($frais_tenue))
    $infos_simulation["frais_tenue"] = $InfoProduit["frais_tenue_cpt"];

  if ($infos_simulation["frais_tenue"] > 0)
    $infos_simulation["solde_cloture"] -= $infos_simulation["frais_tenue"];echo "frais tenue ".$infos_simulation["frais_tenue"]."\n";*/

  /* Arrondi du solde de clôture */
  $DEV = getInfoDevise($InfoCpte['devise']);
  $infos_simulation["solde_cloture"] = round($infos_simulation["solde_cloture"], $DEV["precision"]);

  return $infos_simulation;
}

function getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT getPeriodeCapitalisation(date('$date_cap'), date('$date_ouv'), ";
  if ($date_las_cap == NULL) /* Si c'est la première rémunération */
    $sql .="NULL);";
  else
    $sql .= "date('$date_las_cap'));";

  $result = $db->query($sql);
  if (DB::isError($result)){
    echo "Erreur getJoursCapitalisation() ".$result->getMessage()."\n";
    exit();
    //erreur("getJoursCapitalisation()", $result->getMessage());
  }

  $row = $result->fetchrow();
  $nb_jours = $row[0];

  $dbHandler->closeConnection(true);
  return $nb_jours;
}
function calculPenalites($id_cpte, $solde,$int=NULL) {
  /*
  Calcul des pénalités sur un compte à terme non échu
  */
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  $devise = $InfoCpte["devise"];
  $DEV = getInfoDevise($devise);
  if($InfoProduit["mode_calcul_penal_rupt"] == 1) {
    //si le param $int est renseigné, le calcule se fait à partir des intérets générés au lieu du capital.
    $penalites = round(($InfoProduit["penalite_const"] + ((isset($int)?$int:$solde) * $InfoProduit["penalite_prop"])),$DEV["precision"]);
  }
  elseif($InfoProduit["mode_calcul_penal_rupt"] == 2) {
    $cpte_date_fin = $InfoCpte["dat_date_fin"];
    $cpte_date_ouvert = $InfoCpte["date_ouvert"];

    $today = date("d/m/Y");
    $today = explode("/", $today);
    $today = mktime(0,0,0,$today[1],$today[0],$today[2]);

    $date_fin = pg2phpDate($cpte_date_fin);
    $date_fin = explode("/", $date_fin);
    $date_fin = mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]);

    $date_ouvert = pg2phpDate($cpte_date_ouvert);
    $date_ouvert = explode("/",$date_ouvert);
    $date_ouvert = mktime(0,0,0,$date_ouvert[1],$date_ouvert[0],$date_ouvert[2]);

    if ($date_fin > $today) {
      $jours_restant = ($date_fin - $today)/(60*60*24);
      $duree_totale_epargne = ($date_fin - $date_ouvert)/(60*60*24);
    }
    //si le param $int est renseigné, le calcule se fait à partir des intérets générés au lieu du capital.
    $penalites = round(($InfoProduit["penalite_const"] + ((isset($int)?$int:$solde)  * $InfoProduit["penalite_prop"] * $jours_restant)/($duree_totale_epargne)),$DEV["precision"]);
  }
  else $penalites = 0;
  return $penalites;
}
function getInfoDevise($dev) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $retour = NULL;

  $sql = "SELECT * FROM devise WHERE code_devise = '$dev' and id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getInfoDevise ! \n";
    exit();
  }
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $retour;
}

function getIdDossier($id_client, $whereCl) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT ad_dcr.*, adsys_produit_credit.libel as libelle, periodicite FROM ad_dcr,adsys_produit_credit ";
  $sql .="WHERE ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_dcr.id_ag=$global_id_agence AND id_client = '$id_client' AND id_prod=id $whereCl ORDER BY id_doss;";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    echo "Erreur du fonction getIdDossier ! \n";
    exit();
  }

  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $retour[$row["id_doss"]] = $row;

  $dbHandler->closeConnection(true);
  return $retour;
}

function simulationArreteCpteCredit (&$solde_credit, $id_doss) {
  global $global_id_agence;
  global $dbHandler;

  $db = $dbHandler->openConnection();
  $DOSS = getDossierCrdtInfo($id_doss);
  $PRODS = getProdInfo(" where id =".$DOSS["id_prod"]);
  $PROD = $PRODS[0];
  $devise_cre = $PROD["devise"];

  $solde = 0;
  $sql = "SELECT sum(solde_cap) + sum(solde_int) + sum(solde_pen) from ad_etr where id_ag=$global_id_agence AND id_doss = $id_doss;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
    echo "Erreur du fonction simulationArreteCpteCredit : ".$sql."\n".$result->getMessage()."\n";
    exit();
  }

  $row = $result->fetchRow();

  $dbHandler->closeConnection(true);

  $solde_credit = $row[0];

  return new ErrorObj(NO_ERR, $devise_cre);

}


function getDossierCrdtInfo($id_dossier) {
  global $dbHandler,$global_id_agence;
  global $global_multidevise,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT ad_dcr.* ,adsys_produit_credit.devise, adsys_produit_credit.max_jours_compt_penalite";
  $sql .= " FROM ad_dcr,adsys_produit_credit WHERE id_doss ='$id_dossier' AND id_prod = id ";
  $sql.=" and ad_dcr.id_ag=adsys_produit_credit.id_ag and ad_dcr.id_ag = $global_id_agence ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    echo "Erreur du fonction getDossierCrdtInfo : Erreur dans la requête SQL ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);

  if ($result->numrows() != 1)
    return NULL;

  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return $row;
}


function getListeGaranties($id_doss) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $liste_gar = array();

  $sql = "SELECT * FROM ad_gar WHERE id_doss = $id_doss";
  $sql.=" and id_ag= $global_id_agence ";
  $sql.=" ORDER BY id_gar";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getListeGaranties ! \n";
    exit();
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $liste_gar[$row['id_gar']] = $row;

  $dbHandler->closeConnection(true);

  return $liste_gar;

}

/*-------------------------------------------------------------------------*/

/*-----------------------------File Divers------------------------------------------*/
function isArrayNull($arr) {
  foreach ($arr as $key => $value) {
    if ($value != 0)
      return false;
  }
  return true;
}

function php2pg($a_date)
{
  if ($a_date == "") return "";
  $J = substr($a_date,0,2);
  $M = substr($a_date,3,2);
  $A = substr($a_date,6,4);
  return "$A-$M-$J";

}

function arrondiMonnaie($mnt, $sens, $devise=NULL) {

  global $global_billets;
  global $dbHandler;
  global $global_monnaie,$global_id_agence;

  if ($devise == NULL)
    $devise = $global_monnaie;

  $db = $dbHandler->openConnection();

  $sql = "SELECT MIN(valeur) FROM adsys_types_billets WHERE id_ag=$global_id_agence and devise = '$devise'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction arrondiMonnaie \n";
    exit();
  }

  $tmprow = $result->fetchrow();
  $dbHandler->closeConnection(true);

  $min = $tmprow[0];

  if ($min == 0) {
    echo "<BR><B><FONT COLOR=red> *** ".sprintf(_("Le billetage pour la devise %s n'a pas été renseigné"), $devise)."<BR> *** "._("On suppose la plus petite unité monétaire à 1")."</FONT></B><BR>";
    $min = 1;
  }
  $DEV = getInfoDevise($devise);// recuperation d'info sur la devise'
  $precision_devise=pow(10,$DEV["precision"]);
  $reste = fmod($mnt*$precision_devise, $min*$precision_devise)/$precision_devise;
  if ($reste == 0)
    return $mnt;

  if ($sens == 0)
    $sens = ((2*$reste > $min)? 1 : -1);

  if ($sens < 0)
    $arrondi = $mnt - ($reste);
  else if ($sens > 0)
    $arrondi = $mnt + $min - ($reste);

  return $arrondi;
}
/*-------------------------------------------------------------------------*/


/*-----------------------lanceDefection-------------------------------------------*/
function defectionClient($id_client, $etat, $raison_defection = "N/A", & $comptable_his, $id_cpte_ps)
// PS qui réalise la défection d'un client
// Précondition :
//   Le client ne possède que son compte de base, et celui-ci doit avoir été soldé au préalable
//   Il ne reste dessus que la somme nécessaire à la défection
// IN : L'ID du client dont on veut effectuer la défection
//      $etat = Etat du client après la défection (décédé, raidé etc. )
//      $raison_defection = String entré par l'utilisateur comme raison de la défection
// IN-OUT $comptable_his : Historique des précédents mouvements
// OUT: Objet ErrorObj
{
  global $global_id_agence;
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Vérifications
  $idCpteBase = getBaseAccountID($id_client);

  $ACC = getAccounts($id_client);
  //$ACC = get_comptes_epargne($id_client);

  if (sizeof($ACC) != 1) { // Il reste encore des comptes
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DEF_CPT_EXIST);
  }
  if (!isset ($ACC[$idCpteBase])) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DEF_CPT_EXIST);
  }

  $id_cpte = $idCpteBase; // Changement de nom pcq j'ai la flemme de le modifier dans tout le code
  $ACC_BASE = getAccountDatas($id_cpte);
  $PROD = getProdEpargne($ACC_BASE["id_prod"]);

  // Si le client est EAV, la balance est de toute manière à 0
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1) {
    // La clôture du compte de base ne doit pas se faire
  } else {
    // Fermeture du compte de base
    // ************ Copie de la fonction clotureCompteEpargne avec quelques changements ***********
    global $dbHandler, $global_id_client, $global_nom_login;

    $InfoCpte = getAccountDatas($id_cpte);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

    $infos_simul = simulationArrete($id_cpte);
    $solde_cloture = $infos_simul["solde_cloture"];

    /* FIXME/ATTENTION : Le système actuel de passage des écritures en fin de transaction ne nous permùet pas de faire les vérifications quant au solde du compte.
       Rétablir ce check dès que ce système aura été mis à jour
       Le danger actuel étant qu'onne peut plus tre certain que le compte cloturé aura bien un solde nul
    if ($solde_cloture != 0)
    {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La fermeture du compte de base ne laisse pas le solde à 0, il reste $solde_cloture"
    } */

    blocageCompteInconditionnel($id_cpte);
    $dbHandler->closeConnection(true);
    // A partir de ce moment, nous sommes à l'intérieur d'une transaction, les autres utilisateurs voient le compte comme bloqué
    $db = $dbHandler->openConnection();

    deblocageCompteInconditionnel($id_cpte);
    //deblocageCompteInconditionnel($id_cpte_ps);

    //arrêté du compte
    $erreur = arreteCompteEpargne($ACC_BASE, $PROD, $comptable_his); //solde initial + intérêts
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    // Prélèvement des frais de tenue de compte
    /*$erreur = preleveFraisDeTenue($id_cpte, $comptable_his);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }*/

    $ACC = getAccountDatas($id_cpte);

    /* Prélèvement des frais de fermeture */
    $erreur = preleveFraisFermeture($id_cpte, $comptable_his, NULL, $id_cpte_ps);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
  }

  // On vérifie que le solde est bien nul

  /* FIXME/ATTENTION : Le système actuel de passage des écritures en fin de transaction ne nous permùet pas de faire les vérifications quant au solde du compte.
     Rétablir ce check dès que ce système aura été mis à jour
     Le danger actuel étant qu'on ne peut plus etre certain que le compte cloturé aura bien un solde nul

  $ACC = getAccountDatas($id_cpte);
  if ($ACC['solde'] != 0)
  {
  $dbHandler->closeConnection(false);
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Incohérence dans algo de fermeturen solde du compte à fermer n'a pas été annulé, il reste ".$ACC["solde"]
  } */

  // Fermeture du compte proprement dite
  $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
    echo "Erreur du fonction defectionClient - Fermeture du compte proprement dite! \n";
    exit();
  };

  // Invalidation des mandats liés au compte de base
  $MANDATS = getMandats($id_cpte);
  if ($MANDATS != NULL) {
    foreach ($MANDATS as $key => $value) {
      invaliderMandat($key);
    }
  }

  // mise à jour raison clôture
  $sql = "UPDATE ad_cpt SET raison_clot = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
    echo "Erreur du fonction defectionClient ! mise à jour raison clôture \n";
    exit();
  }

  // ********* Fin de la copie ***************
  // Défection du client
  $sql = "UPDATE ad_cli SET etat = $etat, nbre_parts = 0, raison_defection = '$raison_defection', date_defection = '" . date("d/m/Y") . "' WHERE id_ag=$global_id_agence AND id_client = $id_client;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__);
    echo "Erreur du fonction defectionClient ! Défection du client \n";
    exit();
  }

  // Suppression des relations du client
  $DATA['valide'] = 'f';
  $WHERE['id_client'] = $id_client;
  $WHERE['id_ag'] = $global_id_agence;
  $sql = buildUpdateQuery('ad_rel', $DATA, $WHERE);
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__, __LINE__, __FUNCTION__);
    echo "Erreur du fonction defectionClient ! Suppression des relations du client \n";
    exit();
  }

  $dbHandler->closeConnection(true);
  return new errorObj(NO_ERR);
}

/*-------------------------------------------------------------------*/
/*-------------------Functions associer a lanceDefection----------------------------------------*/
function getAccounts ($id_client) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT b.*, a.* FROM adsys_produit_epargne b, ad_cpt a WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_prod = b.id AND ";
  $sql .= "a.id_titulaire = '$id_client'";
  $sql .= " AND NOT (a.etat_cpte = 2) ORDER BY a.num_complet_cpte";  //il se peut qu'on veuille avoir les comptes bloqués
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getAccounts ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) return NULL;
  $TMPARRAY = array();
  while ($cpt = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$cpt["id_cpte"]] = $cpt;
  }
  return $TMPARRAY;
}

function getMandats($id_cpte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $TMPARRAY = array();

  if ($id_cpte == NULL) {
    return NULL;
  }

  $WHERE['id_cpte'] = $id_cpte;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildSelectQuery('ad_mandat', $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur du fonction getMandats ! \n";
    exit();
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $PERS_EXT = getPersonneExt(array('id_pers_ext' => $row['id_pers_ext']));
    $ACC = getAccountDatas($id_cpte);
    $row['denomination'] = $PERS_EXT[0]['denomination'];
    $row['devise'] = $ACC['devise'];
    $id_mandat = $row['id_mandat'];
    unset($row['id_mandat']);
    $TMPARRAY[$id_mandat] = $row;
  }

  $dbHandler->closeConnection(true);
  return $TMPARRAY;
}


function getPersonneExt($a_where)
{
  global $global_id_agence;

  // construction de la chaine de la requete pr cherche le personne ext ds la table ad_pers_ext
  $sql_pe ="SELECT id_pers_ext, id_client , ";
  $sql_pe .=" denomination, ";
  $sql_pe .="adresse, " ;
  $sql_pe .="code_postal, ville, pays, ";
  $sql_pe .="num_tel, ";
  $sql_pe .=" date_naiss, ";
  $sql_pe .="lieu_naiss, ";
  $sql_pe .="type_piece_id, " ;
  $sql_pe .=" num_piece_id, ";
  $sql_pe .=" lieu_piece_id, " ;
  $sql_pe .=" date_piece_id, " ;
  $sql_pe .="date_exp_piece_id ," ;
  $sql_pe .="id_ag ";
  $sql_pe .=" FROM ad_pers_ext WHERE id_ag = $global_id_agence AND id_client is null ";

  // contruction de la chaine de la req pr recherche  client pers_ext ds ad_cli
  $sql_cli ="SELECT pe.id_pers_ext, cli.id_client , ";
  $sql_cli .="pp_nom || ' ' || pp_prenom as denomination, ";
  $sql_cli .="cli.adresse, " ;
  $sql_cli .="cli.code_postal, cli.ville, cli.pays, ";
  $sql_cli .="cli.num_tel, ";
  $sql_cli .="cli.pp_date_naissance as date_naiss, ";
  $sql_cli .="cli.pp_lieu_naissance as lieu_naiss, ";
  $sql_cli .="cli.pp_type_piece_id as type_piece_id, " ;
  $sql_cli .="cli.pp_nm_piece_id as num_piece_id, ";
  $sql_cli .="cli.pp_lieu_delivrance_id as lieu_piece_id, " ;
  $sql_cli .="cli.pp_date_piece_id  as date_piece_id, " ;
  $sql_cli .="cli.pp_date_exp_id as date_exp_piece_id ," ;
  $sql_cli .="cli.id_ag ";
  $sql_cli .="FROM ad_cli cli, ad_pers_ext pe WHERE cli.id_ag = $global_id_agence AND cli.id_client = pe.id_client AND statut_juridique = 1";

  // contruction du critere de selection des client et non client pers_ext

  if (is_array($a_where)) {
    $a_where = array_make_pgcompatible($a_where);
    $sql_pe  .= " AND ";
    $sql_cli .= " AND ";
    foreach ($a_where as $champ => $valeur) {
      if ($champ == "denomination") {
        $sql_cli .= "pp_nom || ' ' || pp_prenom LIKE '$valeur%' AND";
        $sql_pe .= " $champ LIKE '$valeur%' AND";

      } elseif ($champ=="id_client")  {
        $sql_cli .= " cli.$champ = '$valeur' AND"; //prefixé le champ par l'alias cli
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="lieu_naiss") {
        $sql_cli .= " cli.pp_lieu_naissance = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="date_naiss") {
        $sql_cli .= " cli.pp_date_naissance = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="id_pers_ext") {
        $sql_pe .= " $champ = '$valeur' AND";
        $sql_cli .= " $champ = '$valeur' AND";
      }
      else {
        $sql_cli .= " cli.$champ = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
    }
    // On retire le dernier 'AND'
    $sql_pe = substr($sql_pe, 0, strlen($sql_pe) - 4);
    $sql_cli = substr($sql_cli, 0, strlen($sql_cli) - 4);
  }

  // concaténation des req pr unir le resultat
  // attention: le nbre de champ ds la requete sql_pe doit correspondre au de champ ds la requete sql_cli
  $sql = $sql_pe." UNION ".$sql_cli." ;";
  $result = executeDirectQuery($sql, false);
  if ($result->errCode != NO_ERR) {
    return array();
  }
  return $result->param;
}
/*--------------------------------------------------------------*/

/*-------------------Ajout Historique---------------------------------------*/

function ajout_historique($type_fonction, $id_client, $infos, $login, $date, $array_comptable=NULL, $data_ext=NULL, $idhis=NULL) {
  /*
    Cette f° se charge d'enregister dans l'historique aussi bien la f° (ad_his) que les opérations comptables associées (ad_ecriture qui contient les informations sur les ecritures et ad_mouvement qui donne les mouvement sur les comptes
  et ad_cpt_comptable)

  Paramètres entrants :
    - type d'opération (cf. n° table système)
    - infos supplémentaires (cfr documentation/historique.txt)
    - date
    - login de l'utilisateur

    - SI c'est une opération comptable, un tableau a 9 colonnes :
      - 'id' : identifie l'écriture dans un lot d'écriture
      - 'compte' : compte à mouvementer
      - 'cpte_interne_cli' : si le mouvement concerne un compte client, on passe l'id (cf ad_cpt). Ce champ peut être NULL
      - 'sens' : sens du mouvement ('c' ou 'd')
      - 'montant' : montant du mouvement
      - 'date_comptable' : date de valeur  du mouvement
      - 'libel' : libellé de l'écriture
      - 'jou' : identifiant du journal associé à l'opération. Ce champ peut être NULL
      - 'exo' : identifiant de l'exercice comptable associé à la date de valeur
      - 'devise' : Code de la devise du mouvement

  FIXME
     Cette procédure vérifie si la somme des montants débités est équivalante à la somme des montants crédités après quoi elle renseigne les tables concernées.

  OUT
    objet Error
    Si OK, Renvoie l'id dans la table historique comme paramètre
  */

  global $global_monnaie_courante_prec;
  global $dbHandler, $global_id_agence, $debug;

  $db = $dbHandler->openConnection();
  $id_agence_encours = getNumAgence();

  // S'il y a des données à insérer dans la table historique des transferts avec l'extérieur, on commence par cette insertion.
  if ($data_ext == NULL) {
    $id_his_ext = 'NULL';
  } else {
    $id_his_ext = insertHistoriqueExterieur($data_ext);
    if ($id_his_ext == NULL) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      echo "Erreur dans la fonction insertHistoriqueExterieur \n";
      exit();
    }
  }

  $infos = string_make_pgcompatible($infos);
  // Pour ne pas avoir une erreur de PSQL si pas de client associé.
  if ($id_client == '' || $id_client == NULL) {
    $id_client = 'NULL';
  }
  if ($idhis == NULL ) {
    // On commence par récupérer le numéro de lot
    $sql = "SELECT nextval('ad_his_id_his_seq')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      echo "Erreur dans la fonction ajout_historique (recuperation numero lot Id_his) \n";
      exit();
    }

    $row = $result->fetchrow();
    $idhis = $row[0];
    // On insère dans la table historique
    $sql = "INSERT INTO ad_his(id_his,id_ag, type_fonction, infos, id_client, login, date, id_his_ext) ";
    $sql .= "VALUES($idhis,$id_agence_encours, $type_fonction, '$infos', $id_client, '$login', '$date', $id_his_ext)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      echo "Erreur lors de l'insertion dans la table historique \n";
      exit();
    }
  }

  // Si c'est une opération comptable
  if ($array_comptable != NULL) {
    // On vérifie si somme débit == somme crédit et on inscrit dans la base de données
    $equilibre = 0;

    reset($array_comptable);

    // Pour factoriser les lignes par id dans array_comptable pour faire un entête/détail (ad_ecriture/ad_mouvement)
    $tab_id = array();
    $tab_fact = array();
    foreach ($array_comptable as $key => $value) {
      // Verifier que l'operation a bien un libellé
      if (! isset($value['libel'])) {
        echo "<p><font color=\"red\">".sprintf(_("Erreur : l'écriture n'a pas de libellé pour la transaction %s, compte %s !"),$idhis,$value['compte'])."</font></p>";
        return;
      }

      // Pour chaque débit crédit
      if ($value['sens'] == SENS_CREDIT) {
        $equilibre += $value['montant'];
      } elseif ($value['sens'] == SENS_DEBIT) {
        $equilibre -= $value['montant'];
      }

      // Recherche de tous les id différents
      if (in_array($value['id'],$tab_id) == false) {
        $temp = array();
        array_push($tab_id,$value['id']);
        $temp = array("libel" => $value["libel"], "type_operation" => $value["type_operation"], "date_comptable" => $value["date_comptable"], "id_jou" => $value["jou"], "id_exo" => $value["exo"],"info_ecriture"=>$value['info_ecriture']);
        $tab_fact[$value['id']] = $temp;
      }

    }
    if (round($equilibre, $global_monnaie_courante_prec) != 0) {
      //Si la somme débit != somme crédit
      $dbHandler->closeConnection(false);
      // FIXME : renvoyer un objet Error à la place du signalErreur
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      echo "Erreur dans la fonction ajout_historique \n";
      exit();
    }
  }

  // Garde la liste des comptes comptables qui vont etre impactés par des mouvements
  $liste_comptes_comptable = array();

  if ($tab_id != NULL) {
    foreach ($tab_id as $key => $value) { // Pour chaque écriture
      // Insertion dans ad_ecriture les infos factorisées
      // Construction de la requête d'insertion
      $DATA = array();
      $DATA["id_his"] = $idhis;
      $DATA["date_comptable"] = $tab_fact[$value]["date_comptable"];
      $DATA["libel_ecriture"] = $tab_fact[$value]["libel"];
      $DATA["type_operation"] = $tab_fact[$value]["type_operation"];
      $DATA["id_jou"] = $tab_fact[$value]["id_jou"];
      $DATA["id_ag"] = $global_id_agence;
      $DATA["id_exo"] = $tab_fact[$value]["id_exo"];
      $DATA["ref_ecriture"] = makeNumEcriture($DATA["id_jou"], $DATA["id_exo"]);
      $DATA["info_ecriture"] = $tab_fact[$value]["info_ecriture"];

      $sql = buildInsertQuery("ad_ecriture",$DATA);

      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__);
        echo "Erreur dans insertion de ad_ecriture \n";
        exit();
      }

      // Récupérer le numéro d'ecriture
      $sql = "SELECT max(id_ecriture) from ad_ecriture where id_ag=$global_id_agence ";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__);
        echo "Erreur dans la selection du max(id_ecriture) \n";
        exit();
      }

      $row = $result->fetchrow();
      $idecri = $row[0];

      // Insertion dans ad_mouvement les mouvements sur les comptes
      foreach ($array_comptable as $key1 => $value1) { // Pour chaque mouvement
        if ($value1['id'] == $value ) { //mise à jour des soldes comptables
          //FIXME : il faut obliger à passer par les sous-comptes (ex : erreur de paramétrage)
          //FIXME : le montant passé doit avoir été correctement récupéré au préalable par un recupMontant approprié
          $MyError = setSoldeComptable($value1['compte'], $value1['sens'], $value1['montant'], $value1["devise"]);
          if ($MyError->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $MyError;
          }

          // Mise à jour compte client interne
          if ($value1['cpte_interne_cli'] != '') {

            $MyError = setSoldeCpteCli($value1['cpte_interne_cli'], $value1['sens'], $value1['montant'], $value1['devise']);
            if ($MyError->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);

              return $MyError;
            }

            $cpte_interne_cli = $value1['cpte_interne_cli'];
          }

          // Fix montant si NULL ou vide
          $ad_mouvement_montant = recupMontant($value1["montant"]);
          if($ad_mouvement_montant==NULL || $ad_mouvement_montant=='') {
            $ad_mouvement_montant = 0;
          }
          else { // #514: arrondir le montant
            $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
          }

          // Alimenter la liste des comptes comptables qui sont impactés par des mouvements
          if(!(in_array($value1["compte"], $liste_comptes_comptable, TRUE))) {
            $liste_comptes_comptable[] = $value1["compte"];
          }

          // Insertion dans ad_mouvements
          $DATA = array();
          $DATA["id_ecriture"] = $idecri;
          $DATA["compte"] = $value1["compte"];
          $DATA["cpte_interne_cli"] = $value1["cpte_interne_cli"];
          $DATA["sens"] = $value1["sens"];
          $DATA["montant"] = $ad_mouvement_montant;
          $DATA["date_valeur"] = date('r');//$value1["date_valeur"];
          $DATA["devise"] = $value1["devise"];
          $DATA["consolide"] = null; //$value1["consolide"];
          $DATA["id_ag"] = $global_id_agence;

          $sql = buildInsertQuery("ad_mouvement",$DATA);
          $result = $db->query($sql);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            //signalErreur(__FILE__,__LINE__,__FUNCTION__);
            echo "Erreur dans l'insertion ad_mouvement \n";
            exit();
          }
        }
      }
    }
  }

  // #357 - verification de l'equilibre comptable
  foreach ($liste_comptes_comptable as $compte_comptable) {
    $MyError = verificationEquilibreComptable($compte_comptable, null, $idhis, $db);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $idhis);
}

function verificationEquilibreComptable($compte_comptable = NULL, $id_cpte = NULL, $id_his = NULL, &$db)
{
  global $dbHandler, $error;
  global $global_id_agence, $global_nom_login;

  // Le compte interne est renseigné, il faut recuperer le compte comptable a partir du champ num_cpte_comptable
  if(!empty($id_cpte)) {
    $sql = "SELECT num_cpte_comptable FROM ad_cpt WHERE id_cpte = $id_cpte;";
    $result = $db->query ($sql);
    if (DB::isError ($result)) {
      $dbHandler->closeConnection (false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
      echo "Erreur dans la fonction verificationEquilibreComptable() => Erreur Select Query \n";
      exit();
    }
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $compte_comptable = $row['num_cpte_comptable'];
  }

  // Verification si on a bien ce compte comptable renseigné pour ce compte interne
  $count_compte = 0;

  if(!empty($compte_comptable)) {
    $sql = "SELECT COUNT(*) FROM ad_cpt WHERE num_cpte_comptable = '$compte_comptable' and etat_cpte != 4;";
    $result = $db->query ($sql);
    if (DB::isError ($result)) {
      $dbHandler->closeConnection (false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
      echo "Erreur dans la fonction verificationEquilibreComptable() => Erreur Select Query Cpte Compta empty \n";
      exit();
    }
    $row = $result->fetchrow();
    $count_compte = $row[0];
  }

  // Ne rien faire si le compte n'est pas renseigne dans num_cpte_comptable dans ad_cpt
  if(empty($compte_comptable) || $count_compte < 1) {
    //return 0; // ?
  }
  else
  {
    if(empty($id_cpte)) $id_cpte = 'NULL';
    if(empty($id_his)) $id_his = 'NULL';
    $sql = "SELECT verification_equilibre_comptable($id_cpte, '$compte_comptable', '$global_nom_login', $id_his, $global_id_agence);";
    $result = $db->query ($sql);
    if (DB::isError ($result)) {
      $dbHandler->closeConnection (false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
      echo "Erreur dans la fonction verificationEquilibreComptable() => Erreur Select verification_equilibre_comptable()\n";
      exit();
    }
    $row = $result->fetchrow();
    $hasEcart = $row[0];

    return new ErrorObj(NO_ERR, $hasEcart);
  }
}


function setSoldeCpteCli($id_cpte, $sens, $montant, $devise) {
  /*

    Fonction qui met à jour le solde d'un compte client dans ad_cpt suite à une opération financière.
    Il faut vérifier que le solde ne peut pas être négatif sauf pour un compte dont le id produit est celui du type de compte de
    crédit.
    Important : on ne vérifie pes les soldes mini, c'est à l'appelant de le faire

     IN : $id_cpte = identifiant dans ad_cpt
          $sens = SENS_DEBIT => le compte interne est débité (signe de l'opération est -)
                  SENS_CREDIT => le compte interne est crédité (signe de l'opération est +)
          $montant = Montant du transfert sur le compte interne

     OUT : Objet Erreur

    */

  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $id_prod_credit = getCreditProductID ($global_id_agence);

  $sql = "SELECT solde, id_prod, devise ,mnt_bloq,mnt_min_cpte,decouvert_max ";
  $sql .= "FROM ad_cpt ";
  $sql .= "WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte ";
  $sql .= "FOR UPDATE OF ad_cpt;";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction setSoldeCpteCli() => Select Error \n ";
    exit();
  }
  //FIXME : vérifier si on a trouvé quelque chose

  $row = $result->fetchrow();

  $solde = $row[0];

  $ProdCpte = $row[1];
  $devise_cpte = $row[2];

  // #514+PP165 : Arrondir le montant a passer :
  $montant = arrondiMonnaiePrecision($montant, $devise);

  if ($sens == SENS_DEBIT)
    $solde = $solde - $montant;
  elseif ($sens == SENS_CREDIT)
    $solde = $solde + $montant;

  //$solde = round($solde, EPSILON_PRECISION);

  //verifier de quel type de compte client il s'agit : compte d'epargne ou de credit
  if ($ProdCpte ==  $id_prod_credit) {
    //Si compte de crédit, le solde doit être débiteur et ne peut devenir positif
    if ($solde > 0) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_DEB_POS, _("compte client")." $id_cpte");
    }
  } else {
    $mnt_bloq = $row[3];
    $mnt_min_cpte = $row[4];
    $decouvert_max = $row[5];
    $solde1 = $solde + $decouvert_max;
    if ($solde1 < 0) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_CRED_NEG, _("compte client")." $id_cpte");
    }
  }

  // Vérification sur la devise
  if ($devise_cpte != $devise) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Tentative de mouvementer le compte client $id_cpte dans la devise $devise"
    echo "Erreur dans la fonction setSoldeCpteCli()\n";
    exit();
  }

  //mettre à  jour le solde
  $sql = "UPDATE ad_cpt SET solde = $solde WHERE id_ag=$global_id_agence AND id_cpte=$id_cpte;";
  //echo "<br>sql est $sql <br>";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction setSoldeCpteCli() => Update Error \n ";
    exit();
  }


  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function getCreditProductID ($id_agence)
// Renvoie le num de produit référençant les comptes de crédit
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Récupération du n° de produit d'épargne utilisé par l'agence pour les comptes de crédit
  $sql = "SELECT id_prod_cpte_credit FROM ad_agc WHERE id_ag = $id_agence;"; // Recherche l'état du client
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    echo "Erreur de la fonction getCreditProductID-> la requete sql! \n";exit();
  }
  $tmpRow = $result->fetchrow();
  $id_prod = $tmpRow[0];
  $dbHandler->closeConnection(true);
  return $id_prod;
}


function setSoldeComptable($cpte, $sens, $montant, $devise) {
  /*
  Fonction qui met à jour le solde d'un compte de comptabilité.
  On devrait décider qu'on ne peut pas mouvementer un compte collectif mais plutôt un sous-ompte. Mais, pour le moment,
  on suppose que chaque compte a un solde indépendant et pour obtenir le solde d'un compte collectif, il faudra faire la somme  des sous-comptes.

  Au niveau de la DB, on devrait faire des CHECK CONSTRAINTS pour s'assurer en fonction du sens du compte que :
  - Un compte créditeur ne peut devenir négatif
  - Un compte débiteur ne peut devenir positif
  Il faudrait trouver un moyen de récupérer l'erreur interne générée par un trigger

  Pour l'instant, on passe par PHP pour implémenter les contraintes d'intégrité

  * cpte est le n° compte comptable
  * sens est SENS_DEBIT (signe -) ou SENS_CREDIT (signe +)
  * montant à mouvementer sur le compte c'est en valeur absolue
  * devise = Devise du mouvement


  FIXME : tester si le compte qu'on veut mettre à jour n'est pas fermé, bloqué, etc

  */

//  echo "On met à jour le compte comptable $cpte";

  global $dbHandler,$global_id_agence, $error;

  $db = $dbHandler->openConnection();

  //Quel est le solde du compte
  $sql = "SELECT solde, sens_cpte, cpte_centralise, devise ";
  $sql .= "FROM ad_cpt_comptable ";
  $sql .= "WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte' ";
  $sql .= "FOR UPDATE OF ad_cpt_comptable;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Le compte comptable lié n existe pas")); // "Compte inconnu"
    echo "Erreur de la fonction setSoldeComptable -> Le compte comptable lie n'existe pas !";exit();
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $solde = $row["solde"];
  $sens_cpte = $row["sens_cpte"];
  $cpte_centralise = $row["cpte_centralise"];
  $devise_cpte = $row["devise"];

  // #514 : Arrondir le montant a passer :
  $montant = arrondiMonnaiePrecision($montant, $devise);

  //vérifier si le nouveau solde est conforme au sens du compte
  if ($sens == SENS_DEBIT) {
    $solde = $solde - $montant;
  } else if ($sens == SENS_CREDIT) {
    $solde = $solde + $montant;
  }

  if ($sens_cpte == 1) {

    //cas des comptes naturellement débiteurs : le solde ne peut pas devenir positif
    if ($solde > 0) {
      $dbHandler->closeConnection(false);
      return new  ErrorObj(ERR_CPTE_DEB_POS, $cpte); // "Compte $cpte debiteur va devenir positif !"
    }
  } else if ($sens_cpte == 2) {
    //cas des comptes naturellement créditeurs : le solde ne peut pas devenir négatif
    if ($solde < 0) {
      $dbHandler->closeConnection(false);
      return new  ErrorObj(ERR_CPTE_CRED_NEG, $cpte); // "Le compte $cpte crediteur va devenir negatif !"
    }
  }

  /* On ne mouvemente pas un compte centralisateur
  if (isCentralisateur($cpte))
    {
      $dbHandler->closeConnection(false);
      return new  ErrorObj(ERR_CPT_CENTRALISE, "Compte $cpte"); // "Tentative de mouvementer le compte centralisateur $cpte"
    }
  */

  // Vérifie que le mouvement a bien lieu dans la meme devise
  if ($devise_cpte != $devise) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Tentative de mouvementer le compte dans une autre devise"));
    echo "Erreur de la fonction setSoldeComptable-> Tentative de mouvementer le compte dans une devise ! \n";exit();
  }

  //mettre a jour solde courant et solde centralise
  $sql = "UPDATE ad_cpt_comptable ";
  $sql .= "SET solde = $solde ";
  $sql .= "WHERE id_ag=$global_id_agence AND num_cpte_comptable = '$cpte';";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}


function makeNumEcriture($id_jou, $id_exo) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  // On prend tous les comptes à soldes négatifs sauf les comptes de crédit
  $sql = "SELECT last_ref_ecriture FROM ad_journaux WHERE id_ag=$global_id_agence AND id_jou = $id_jou FOR UPDATE";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $num_ecr = $row[0];
  $num_ecr++;

  $JOU = getInfosJournal($id_jou);
  $code_jou = $JOU[$id_jou]["code_jou"];

  $ref_ecriture = $code_jou."-".sprintf("%08d", $num_ecr)."-".sprintf("%02d", $id_exo);

  $sql = "UPDATE ad_journaux SET last_ref_ecriture = $num_ecr WHERE id_ag=$global_id_agence AND id_jou = $id_jou";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur de la focntion makeNumEcriture! \n"; exit();
  }

  $dbHandler->closeConnection(true);

  return $ref_ecriture;
}


function insertHistoriqueExterieur($data_ext) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  //On commence par récupérer le numéro de lot
  $sql = "SELECT nextval('ad_his_ext_seq')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    echo "Erreur dans la fonction insertHistoriqueExterieur() => SQL erreur \n";
    exit();
  }
  $row = $result->fetchrow();
  $id_his_ext = $row[0];

  $data_ext["id"] = $id_his_ext;
  $data_ext["id_ag"] = $global_id_agence;

  $sql = buildInsertQuery("ad_his_ext", $data_ext);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "Erreur dans la fonction insertHistoriqueExterieur() => SQL erreur Insert \n";
    exit();
  }

  $dbHandler->closeConnection(true);

  return $id_his_ext;
}


function recupMontant($montant) {
  global $mnt_sep_mil;
  global $mnt_sep_dec;

  if ($montant == "")
    return NULL;

  // Il faut transformer les blancs insécables en blancs simples, pour retrouver les bon séparateurs.
  // C'est donc ici le premier " " qui est un blanc insécable !
  $montant = mb_ereg_replace(" ", " ", $montant);
  $montant = str_replace($mnt_sep_mil, "", $montant);
  $montant = str_replace($mnt_sep_dec, ".", $montant);
  return doubleval($montant);
}

function arrondiMonnaiePrecision($mnt, $devise = NULL)
{
  global $global_monnaie,$global_id_agence;

  if (empty($devise)) {
    $devise = $global_monnaie;
  }

  $precisionDevise = getPrecisionDevise($devise);
  $mnt = round($mnt, $precisionDevise);

  return $mnt;
}

function getPrecisionDevise($devise)
{
  global $error;

  if(!empty ($devise)) {
    $infos_devise = getInfoDevise($devise);
    return $infos_devise['precision'];
  }
  else {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("La devise n'est pas renseigné"));
    echo "Erreur dans la fonction getPrecisionDevise() => La devise n'est pas renseigné \n";
    exit();
  }
}


/**
 * Fonction utilisée pour décrypter un text avec le procédé de cryptage Blowfish
 * @author B&D
 * @since 1.0
 */
function phpseclib_Decrypt_ACU($ciphertext, $password="") {
  // Include SSH library*/
  /*
  require_once('ad_ma/batch/phpseclib0.3.5/Crypt/RC4.php');

  $cipher = new Crypt_RC4();

  $cipher->setPassword($password);

  return $cipher->decrypt(utf8_decode($ciphertext));
  */

  //require_once('lib/misc/cryptage.php');

  return Decrypte_ACU($ciphertext, $password);
}

/**
 * Permet de decrypter un texte
 */
function Decrypte_ACU($Texte,$Cle) {
  $Texte = GenerationCle_ACU(base64_decode($Texte),$Cle);
  $VariableTemp = "";
  for ($Ctr=0;$Ctr<strlen($Texte);$Ctr++) {
    $md5 = substr($Texte,$Ctr,1);
    $Ctr++;
    $VariableTemp.= (substr($Texte,$Ctr,1) ^ $md5);
  }
  return $VariableTemp;
}

/**
 * Permet de générer une clé de cryptage
 */
function GenerationCle_ACU($Texte,$CleDEncryptage) {
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
/*-------------------------------------------------------*/

function truncateTable($table_name){
  global $dbHandler,$global_monnaie,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "TRUNCATE TABLE $table_name ";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else{
    $dbHandler->closeConnection(true);

    return true;
  }
}


function checkPositionSaison($annee,$saison){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "select id_saison from ec_saison_culturale where id_annee = $annee order by id_saison";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  $count = 0;

  $dbHandler->closeConnection(true);

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $count++;
    if ($saison == $row['id_saison']){
      return $count;
    }
  }
}

function getAnneeAgricoleFromSaison($id_saison =null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_saison_culturale WHERE id_ag = $global_id_agence ";

  if ($id_saison != null){
    $sql .= " AND id_saison = ".$id_saison;
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
?>