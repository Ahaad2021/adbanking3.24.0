<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2: */

/**
 * @package Guichet
 */
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/dbProcedures/systeme.php';
require_once 'lib/multilingue/traductions.php';
/**
 * Effectue l'ajustement d'une caisse suite à une erreur d'un guichetier
 * @param int $id_guichet N° du guichet à ajuster
 * @param float $encaisse Nouveau montant de la caisse
 * @param text $num_piece Numéro de la pièce justificative
 * @param text $remarque Commentaire éventuel
 * @param char(3) $devise Devise de la caisse à ajuster
 * @return ErrorObj Objet Erreur
 */
function ajustement_encaisse($id_guichet, $encaisse, $num_piece, $remarque, $devise=NULL, $date_encaisse = NULL) {
  global $dbHandler;
  global $global_multidevise, $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();

  $login = getLoginFromGuichet($id_guichet);
  $logged_logins = logged_logins();

  if ((is_array($logged_logins)) && (in_array($login, $logged_logins))) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GUICHET_OUVERT);
  }

  /* Arrondi du montant opération au guichet */
  $critere = array();
  $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
  $cpte_gui = getComptesComptables($critere);
  $encaisse = arrondiMonnaie( $encaisse, 0, $cpte_gui['devise'] );

  // Passage des écritures comptables
  $mouvements = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();

  // Mise à jour de l'encaisse
  if ($global_multidevise) {
    $solde_encaisse = get_encaisse($id_guichet, $devise);
  } else {
    $solde_encaisse = get_encaisse($id_guichet);
  }

  if ($solde_encaisse > $encaisse) {
    $sens = SENS_DEBIT;
    $sens_inverse = SENS_CREDIT;
    $mnt = $solde_encaisse - $encaisse;
    $type_oper = 260;
    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
    if ($global_multidevise) {
      $OPER = getDetailsOperation($type_oper);
      $PARAM = $OPER->param;
      $num_cpte_cpta = $PARAM['debit']['compte'];

      $temp = array();
      $temp['num_cpte_comptable'] = $num_cpte_cpta;
      $temp['id_ag'] = $global_id_agence;
      $CPTE_CPTA = getComptesComptables($temp);
      $CPTE_CPTA = $CPTE_CPTA[$num_cpte_cpta];
      if ($CPTE_CPTA['devise'] == NULL) {
        $devise_achat = $devise;
      } else {
        $devise_achat = $CPTE_CPTA['devise'];
      }
    } else {
      $devise_achat = $global_monnaie;
    }
    $devise_vente = $devise;
    $debit = false;
  } else {
    $sens = SENS_CREDIT;
    $sens_inverse = SENS_DEBIT;
    $mnt = $encaisse - $solde_encaisse;
    $type_oper = 261;
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
    if ($global_multidevise) {
      $OPER = getDetailsOperation($type_oper);
      $PARAM = $OPER->param;
      $num_cpte_cpta = $PARAM['credit']['compte'];

      $temp = array();
      $temp['num_cpte_comptable'] = $num_cpte_cpta;
      $temp['id_ag'] = $global_id_agence;
      $CPTE_CPTA = getComptesComptables($temp);
      $CPTE_CPTA = $CPTE_CPTA[$num_cpte_cpta];
      if ($CPTE_CPTA['devise'] == NULL) {
        $devise_vente = $devise;
      } else {
        $devise_vente = $CPTE_CPTA['devise'];
      }
    } else {
      $devise_vente = $global_monnaie;
    }
    $devise_achat = $devise;
    $debit = true;
  }

  $myErr = effectueChangePrivate($devise_achat, $devise_vente, $mnt, $type_oper, $cptes_substitue, $mouvements, $debit);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  // Historique
  global $global_nom_login;

  $hisExt = array("type_piece" => 14, "num_piece" => $num_piece, "remarque" => $remarque);

  $myErr= ajout_historique(170,NULL, $id_guichet, $global_nom_login, $date_encaisse, $mouvements, $hisExt);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $db = $dbHandler->closeConnection(true);
  return $myErr;
}

function get_guichet_infos($id_gui) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT *  FROM ad_gui WHERE id_ag=$global_id_agence and id_gui=$id_gui";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour inattendu"
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $db = $dbHandler->closeConnection(true);
  return $row;
}
/***
* Cette fonction permet de recuperer l'id d'un guichet à partir du nom du guichet correspondant
* @author Aminata
* @since 3.0
* @param $a_libel : le libellé du guichet
*
*/
function getIdGuichet($a_libel) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_gui  FROM ad_gui WHERE id_ag = $global_id_agence and libel_gui = '$a_libel'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour inattendu"
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $db = $dbHandler->closeConnection(true);
  return $row;
}


function getLibelGuichet() {
  /*
  Fonction qui extrait les libellés des guichet dans ad_cpt_comptable correspondant aux entrèes dans ad_gui
  IN : Rien
  OUT: Un tableau de type ('libel' => 'string décrivant le(s) libellé(s) des guichet')
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_gui, cpte_cpta_gui,libel_cpte_comptable  ";
  $sql .= "FROM ad_gui, ad_cpt_comptable  ";
  $sql .= "WHERE num_cpte_comptable=cpte_cpta_gui and ad_gui.id_ag=ad_cpt_comptable.id_ag and ad_gui.id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $libels=array();

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $libels["libel"][$row["id_gui"]] = $row["libel_cpte_comptable"];
    $libels["id_compte"][$row["id_gui"]] = $row["cpte_cpta_gui"];
  }

  $dbHandler->closeConnection(true);

  return $libels;
}

function getLoginFromGuichet ($id_gui) {
  // PS qui renvoie le login associé à un guichet donné
  // Renvoie -1 si le guichet n'existe pas
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT login FROM ad_log WHERE guichet=$id_gui";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le guichet $id_gui est présent plusieurs fois dans la DB"
  }
  if ($result->numrows() == 0) {
    $db = $dbHandler->closeConnection(true);
    return -1;
  } else {
    $row = $result->fetchrow();
    $db = $dbHandler->closeConnection(true);
    return $row[0];
  }
}

function getGuichetFromLogin ($login) {
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

function client_actif($num_client) { //Renvoie true si le client est actif
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT etat FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$num_client'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return ($row[0] == 2);
}

function traite_depot($DATA, $id_gui, $login, $data_virement) {
  /* Traite le dépôt :
     - si il y a eu une mise à jour du solde min du compte concerné depuis le dépôt alors on incrémente ce solde min
     - met à jour le solde du compte
     IN : $DATA = array de données concernant chaque dépôt
             "date" => Date du dépôt
             "id_cpte" => ID compte du client (cfr ad_cpt)
             "mnt" => Montant du dépôt
             "num_recu" => Numéro de reçu
             "traite" => flag indiquant s'il faut ou non traiter la ligne
          $id_gui = ID dui guichet sur lequel s'est effectué le dépôt par lot (cfr ad_gui)
          $login = Login de l'utilisateur effectuant le dépôt par lot (cfr ad_log)
     OUT: Objet ErrorObj

  */
  global $dbHandler, $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();


  $id_cpt = $DATA['id_cpte'];
  $montant_remboursement = 0;

  //Va chercher la date de la dernière mise à jour du solde min et le solde sourant
  $sql = "SELECT date_solde_calcul_interets, solde_calcul_interets, solde FROM ad_cpt WHERE id_ag=$global_id_agence AND id_cpte=$id_cpt";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour inattendu"
  }

  $row = $result->fetchrow();
  $date = $row[0];
  $solde_int = $row[1];
  $solde = $row[2];

  //Eventuellement mise à jour solde_interets
  $date = pg2phpDatebis($date);
  $date2 = splitEuropeanDate($DATA['date']);
  $solde_int += $DATA['mnt'];
  // Si la date de dernière mise à jour du solde_min est postérieure à la date du dépôt
  if (gmmktime(0,0,0,$date[0],$date[1],$date[2]) >= gmmktime(0,0,0,$date2[1],$date2[0],$date2[2])) {
    $sql = "UPDATE ad_cpt SET solde_calcul_interets=$solde_int WHERE id_ag=$global_id_agence AND id_cpte=$id_cpt";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

//Nouveau code pour correspondre aux développements des Attentes
  $InfoCpte = getAccountDatas($id_cpt);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  /* Informations supplémentaires: on peut y ajouter toute information disparate */
  $infos_sup = array();
  if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
  	$libel_ope = new Trad();
  	$libel_ope = $DATA['autre_libel_ope'];
  	$libel_ope->save();
    $infos_sup['autre_libel_ope'] = $libel_ope->get_id_str();
  }else{
  	$infos_sup['autre_libel_ope'] = htmlspecialchars($DATA['autre_libel_ope'], ENT_QUOTES, "UTF-8");
  }
  $data=array();
  $data['sens'] = 'in ';
  $data['remarque'] = $data_virement['remarque'];
  $data['communication'] = $data_virement['communication'];
  $type_depot = 158; //Pour dépôt par lôt
  if ($data_virement['source']==2) { // la source provient d'un correspondant
    $InfoTireur = getTireurBenefDatas($data_virement['id_ben']);
    $data['id_ext_benef']           = null;
    $data['id_cpt_ordre']           = null;
    $data['type_piece']             = 11;
    $data['num_piece']              = $data_virement['num_piece']." - "._("n° reçu : ").$DATA['num_recu'];
    $data['date_piece']             = $DATA['date'];
    $data['date']                   = date("d/m/Y");
    $data['montant']                = $DATA['mnt'];
    $data['devise']                 = $InfoCpte['devise'];
    $data['id_correspondant']       = $data_virement['correspondant'];
    $data['id_cpt_benef']           = $DATA['num_client'];
    $data['id_ext_ordre']           = $data_virement['id_ben'];
    $data['id_banque']              = $InfoTireur['id_banque'];

    /* Eventuels frais de virement en cas de dépôt par lot pour les virements de salaires */
    if ($DATA['mnt_com'] > 0)
      $frais_virement = $DATA['mnt_com'];
    else
      $frais_virement = NULL;

    // FIXME ne faut-il pas traiter le multi-devise ici ?
    $myErr = receptionVirement($data, $InfoCpte, $InfoProduit, NULL, $frais_virement,$type_depot, $infos_sup, $montant_remboursement);
  } else if ($data_virement['source']==1) { // la source provient du guichet
    /* Eventuels frais de virement en cas de dépôt par lot pour les virements de salaires */
    if ($DATA['mnt_com'] > 0)
      $frais_virement = $DATA['mnt_com'];
    else
      $frais_virement = NULL;

      $montant_remboursement = $DATA['mnt'];
 	  $myErr = depot_cpte($id_gui,$id_cpt, $DATA['mnt'], $InfoProduit, $InfoCpte, $data, $type_depot, NULL, $frais_virement, $infos_sup);
      $montant_remboursement = $montant_remboursement - $InfoProduit["frais_depot_cpt"];
      $montant_remboursement = $montant_remboursement - $frais_virement;
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $retour = array("id_his" => $myErr->param['id'], "id_client" => $DATA["num_client"], "num_recu" => $DATA["num_recu"],
      "mnt" => $DATA["mnt"],
      "mnt_com" => $DATA["mnt_com"],"cpte_client"=> $DATA["cpte_client"],"devise"=>$DATA["devise"], "montant_remboursement" => $montant_remboursement);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $retour, null, array("comptable" => $myErr->datas["comptable"]));
}

function traite_lot ($DATA, $login, $id_gui, $data_virement) {
  //Cette fonction centralise les traitements pour la saisie par lot
  /*
     IN : $DATA = array de données concernant chaque dépôt
             "date" => Date du dépôt
             "id_cpte" => ID compte du client (cfr ad_cpt)
             "mnt" => Montant du dépôt
             "num_recu" => Numéro de reçu
             "traite" => flag indiquant s'il faut ou non traiter la ligne
          $id_gui = ID dui guichet sur lequel s'est effectué le dépôt par lot (cfr ad_gui)
          $login = Login de l'utilisateur effectuant le dépôt par lot (cfr ad_log)
     OUT: Objet ErrorObj avec en paramètre un tableau reprenant toutes les opérations effectuées avec les id_his associés
   */

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $retour = array(); // Le tableau contenant les infos sur les traitements effectués

  for ($i=1; $i <= 40; ++$i)
    if ($DATA[$i]['traite']) {
      $myErr = traite_depot($DATA[$i], $id_gui, $login, $data_virement);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      } else {
//        array_push($retour, $myErr->param);
        $retour["transaction_data"][$myErr->param["id_client"]] = $myErr->param;
        $retour["comptable"][$myErr->param["id_client"]] = $myErr->datas["comptable"];
      }
    }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $retour);
}

function traite_retrait($DATA, $id_gui, $login, $data_virement) {
  /* Traite le retrait :
     - si il y a eu une mise à jour du solde min du compte concerné depuis le dépôt alors on incrémente ce solde min
     - met à jour le solde du compte
     IN : $DATA = array de données concernant chaque dépôt
             "date" => Date du retrait
             "id_cpte" => ID compte du client (cfr ad_cpt)
             "mnt" => Montant du retrait
             "num_recu" => Numéro de reçu
             "traite" => flag indiquant s'il faut ou non traiter la ligne
          $id_gui = ID dui guichet sur lequel s'est effectué le retrait par lot (cfr ad_gui)
          $login = Login de l'utilisateur effectuant le retrait par lot (cfr ad_log)
     OUT: Objet ErrorObj

  */
  global $dbHandler, $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();

  $id_cpt = $DATA['id_cpte'];

  // Nouveau code pour correspondre aux développements des Attentes
  $InfoCpte = getAccountDatas($id_cpt);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  /* Informations supplémentaires: on peut y ajouter toute information disparate */
  $infos_sup = array();
  if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
    $libel_ope = new Trad();
    $libel_ope = $DATA['autre_libel_ope'];
    $libel_ope->save();
    $infos_sup['autre_libel_ope'] = $libel_ope->get_id_str();
  }else{
    $infos_sup['autre_libel_ope'] = htmlspecialchars($DATA['autre_libel_ope'], ENT_QUOTES, "UTF-8");
  }

  $data_chq = array();
  $data_benef = NULL;

  $data_chq['sens'] = 'out';
  $data_chq['remarque'] = $data_virement['remarque'];
  $data_chq['communication'] = $data_virement['communication'];

  $type_retrait = 55; //154; //Pour retrait par lôt

  if ($data_virement['dest_fond']==2) { // la destination provient d'un correspondant
    //$InfoTireur = getTireurBenefDatas($data_virement['id_ben']);
    $data_chq['id_ext_benef']           = $data_virement['id_ben'];
    //$data_chq['id_cpt_ordre']           = $DATA['num_client'];
    $data_chq['type_piece']             = $data_virement['type_piece']; // 11
    $data_chq['num_piece']              = trim($DATA['num_recu']); //$data_virement['num_piece']." - "._("n° reçu : ").$DATA['num_recu'];
    $data_chq['date_piece']             = $data_virement['date_piece'];
    $data_chq['id_correspondant']       = $data_virement['correspondant'];
    // Ajout du destination fond MAE-30
    $data_chq['type_ret'] = $data_virement['type_ret'];
    $data_chq['dest_fond'] = $data_virement['dest_fond'];
    //$data_chq['date']                   = date("d/m/Y");
    //$data_chq['montant']                = recupMontant($DATA['mnt']);
    //$data_chq['devise']                 = $InfoCpte['devise'];
    //$data_chq['id_cpt_benef']           = null;
    //$data_chq['id_ext_ordre']           = null;
    //$data_chq['id_banque']              = $InfoTireur['id_banque'];

    //$MANDATAIRE = getInfosMandat($id_ben);
    $InfoTireur = getTireurBenefDatas($data_virement['id_ben']);
    $data_benef['beneficiaire'] = 't';
    $data_benef['tireur'] = 'f';
    $data_benef['denomination'] = $InfoTireur['denomination'];
    $data_benef['adresse'] = $InfoTireur['adresse'];
    $data_benef['code_postal'] = $InfoTireur['code_postal'];
    $data_benef['ville'] = $InfoTireur['ville'];
    $data_benef['pays'] = $InfoTireur['pays'];
    $data_benef['num_tel'] = $InfoTireur['num_tel'];
    $data_benef['type_piece'] = $InfoTireur['type_piece'];
    $data_benef['num_piece'] = $InfoTireur['num_piece'];
    $data_benef['lieu_delivrance'] = $InfoTireur['lieu_delivrance'];
    foreach ($data_benef as $key => $value) {
      if ($data_benef[$key] == '') unset($data_benef[$key]);
    }

    /* Eventuels frais de virement en cas de dépôt par lot pour les virements de salaires */
    if ($DATA['mnt_com'] > 0) {
      $InfoProduit['frais_transfert'] = recupMontant($DATA['mnt_com']);
    }

    // FIXME ne faut-il pas traiter le multi-devise ici ?
    //$myErr = receptionVirement($data, $InfoCpte, $InfoProduit, NULL, $frais_virement,$type_depot, $infos_sup);
  } else if ($data_virement['dest_fond']==1) { // la source provient du guichet
    /* Eventuels frais de virement en cas de dépôt par lot pour les virements de salaires */
    if ($DATA['mnt_com'] > 0) {
      $InfoProduit['frais_retrait_cpt'] = recupMontant($DATA['mnt_com']);
    }

//    $myErr = depot_cpte($id_gui,$id_cpt, $DATA['mnt'], $InfoProduit, $InfoCpte, $data, $type_depot, NULL, $frais_virement, $infos_sup);
  }

  $myErr = retrait_cpte($id_gui, $id_cpt, $InfoProduit, $InfoCpte, recupMontant($DATA['mnt']), $type_retrait, NULL, $data_chq, NULL, $data_benef);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $retour = array("id_his" => $myErr->param['id'], "id_client" => $DATA["num_client"], "num_recu" => $DATA["num_recu"], "mnt" => $DATA["mnt"], "mnt_com" => $DATA["mnt_com"],"cpte_client"=> $DATA["cpte_client"],"devise"=>$DATA["devise"]);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $retour);
}

function retrait_par_lot ($DATA, $login, $id_gui, $data_virement) {
  //Cette fonction centralise les traitements pour la saisie par lot
  /*
     IN : $DATA = array de données concernant chaque dépôt
             "date" => Date du dépôt
             "id_cpte" => ID compte du client (cfr ad_cpt)
             "mnt" => Montant du dépôt
             "num_recu" => Numéro de reçu
             "traite" => flag indiquant s'il faut ou non traiter la ligne
          $id_gui = ID dui guichet sur lequel s'est effectué le dépôt par lot (cfr ad_gui)
          $login = Login de l'utilisateur effectuant le dépôt par lot (cfr ad_log)
     OUT: Objet ErrorObj avec en paramètre un tableau reprenant toutes les opérations effectuées avec les id_his associés
   */

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $retour = array(); // Le tableau contenant les infos sur les traitements effectués

  for ($i=1; $i <= $data_virement['nb_ope_count']; ++$i)
    if ($DATA[$i]['traite']) {
      $myErr = traite_retrait($DATA[$i], $id_gui, $login, $data_virement);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      } else {
        array_push($retour, $myErr->param);
      }
    }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $retour);
}

function traite_virement($DATA) {
  /*
  Fonction effectuant le virement d'une somme d'argent sur le compte d'un client
  Le montant d'un virement/chèque peut être réparti sur plusieurs clients
  Le versement est effectué sur le compte de base

  IN : $DATA array ([numero d'ordre) => array (
           date : Date du virement
           num_client : numéro du client bénéficiaire
           mnt : Montant du virement
           traite : booléen : t => A traiter, f => ne pas traiter)

  OUT: Objet ErrorObj
  FIXME : perception des frais en attente à prélever lors du virement
  */

  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();
  $comptable_his = array();

  for ($i=1; $i <= 10; ++$i) {
    if ($DATA[$i]['traite']) {
      //Va chercher l'id du compte de base
      $id_cpte_base = $DATA['id_cpte'];

      //Va chercher la date de la dernière mise à jour du solde min et le solde sourant
      $sql = "SELECT date_solde_calcul_interets, solde_calcul_interets, solde FROM ad_cpt WHERE id_ag=$global_id_agence AND id_cpte=$id_cpte_base";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      } else if ($result->numrows() != 1) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour inattendu"
      }
      $row = $result->fetchrow();
      $date = $row[0];
      $solde_int = $row[1];
      $solde = $row[2];

      //Eventuellement mise à jour solde_interets
      $date = pg2phpDatebis($date);
      $date2 = splitEuropeanDate($DATA['date']);
      $solde_int += $DATA[$i]['mnt'];
      // Si la date de solde_calcul_interets est postérieure à la date du dépôt
      // FIXME - TG : Correct ? Le solde doit-il obligatoirement être mis à jour dans ce cas (solde minimum...)
      if (gmmktime(0,0,0,$date[0],$date[1],$date[2]) >= gmmktime(0,0,0,$date2[1],$date2[0],$date2[2])) {
        $sql = "UPDATE ad_cpt SET solde_calcul_interets=$solde_int WHERE id_ag=$global_id_agence AND id_cpte=$id_cpte_base";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
      }
      // TRansfert client
      $Infobanque =  getInfosBanque($DATA["id_banque"]);
      $id_banque = $DATA["id_banque"];
      //Passages ecritures comptables

      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();

      //unset($cptes_substitue["cpta"]["credit"]);
      //unset($cptes_substitue["int"]["credit"]);
      //unset($cptes_substitue["cpta"]["debit"]);}

      //débit de la banque par le crédit d'un client
      $cptes_substitue["cpta"]["debit"] = $Infobanque[$id_banque]["cpte_cpta_bqe"];

      // Produit du compte d'épargne associé
      $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_base);
      if ($cptes_substitue["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $cptes_substitue["int"]["credit"] = $id_cpte_base;

      $myErr = passageEcrituresComptablesAuto(370, $DATA[$i]['mnt'], $comptable_his, $cptes_substitue);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

      //Perception commission de virement
      $comm = getCommissionVirement($DATA[$i]["mnt"], $global_id_agence);
      if ($comm > 0) {
        unset($cptes_substitue["cpta"]["credit"]);
        unset($cptes_substitue["int"]["credit"]);
        unset($cptes_substitue["cpta"]["debit"]);

        //Produit du compte d'épargne associé
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $id_cpte_base;

        $myErr = passageEcrituresComptablesAuto(151, $comm, $comptable_his, $cptes_substitue);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }

      }

    }
  }
  $justif = $DATA["justif"];

  global $global_nom_login;
  $myErr = ajout_historique(157, NULL, $justif, $global_nom_login, date("r"), $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }


  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function exist_bordereau($num) { //Renvoie true si ce n° de bordereau a déjà été utilisé
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) FROM ad_his WHERE (id_ag=$global_id_agence) AND ((type_fonction=155) OR (type_fonction=156)) AND (infos='$num')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();

  $dbHandler->closeConnection(true);
  return ($row[0] > 0);
}

function exist_num_recu_lot($num) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) FROM ad_his WHERE (id_ag=$global_id_agence) and (type_fonction=158) and (infos = '$num')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();


  $dbHandler->closeConnection(true);
  return ($row[0] > 0);
}

/**
 * Approvisionnement ou délestage d'un guichet par le coffre-fort
 * @author mbaye(modifier)
 * @since 1.0
 * @param int $num_guichet L'ID du guichet concerné
 * @param bool $is_appro Indique de quelle opération il s'agit, appro ou délestage
 * @param float $montant Montant de l'opération
 * @param int $id_agence Le code de l'agence
 * @param array $DATA : array contenant le montant, la devise et le numéro du bordoreau
 * @return ErrorObj
 */
function appro_delest($num_guichet, $is_appro,  $id_agence, $DATA) {

  global $dbHandler;
  global $global_id_agence;
  global $global_nom_login;
  global $global_multidevise;

  $db = $dbHandler->openConnection();

  $infosagence = getAgenceDatas($id_agence);


  //Détermine le sens et le guichet concerné
  if ($is_appro) {
    $num_fct = 155;
    $sens = SENS_DEBIT;
  } else {
    $num_fct = 156;
    $sens = SENS_CREDIT;
  }

  //y a-t-il assez d'argent dans le coffre-fort ?
  $InfosCoffreFort = getCompteCoffreFortInfos($global_id_agence);

  $nums_bordereaux = "";
  foreach ($DATA as $key => $value) {// aproviionnement
    $devise=$DATA[$key]["devise"];
    if ($is_appro) {
      if ( $DATA[$key]["mnt"] > $InfosCoffreFort[$devise]["solde"]) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_DEB_POS, $infosagence["cpte_cpta_coffre"]);
      }
    } else {
      // si delestage conparer avec l'encaisse
      $encaisse=get_encaisse($num_guichet,$devise);
      if ($encaisse < $DATA[$key]["mnt"]) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_DEB_POS, getCompteCptaGui($num_guichet));
      }
    }
    // Mise à jour du string qui va contenir les numéros de bordereau
    if (isset($value["num"])) {
      if ($nums_bordereaux == '')
        $nums_bordereaux = $value["num"];
      else
        $nums_bordereaux .= " / ".$value["num"];
    }
  }

  $mouvements = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cpte_cpta_coffre=$infosagence["cpte_cpta_coffre"];

  if ($sens == SENS_DEBIT) {
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($num_guichet);
    $cptes_substitue["cpta"]["credit"] = $cpte_cpta_coffre;
  } else if ($sens == SENS_CREDIT) {
    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($num_guichet);
    $cptes_substitue["cpta"]["debit"] =$cpte_cpta_coffre;
  }

  $type_oper = ($is_appro? 290 : 300);
  foreach ($DATA as $key => $value) {
    $devise = $DATA[$key]["devise"];
    /* Arrondi du montant opération au guichet*/
    $DATA[$key]["mnt"] = arrondiMonnaie( $DATA[$key]["mnt"], 0, $devise );
    // Passage des écritures comptables - On récupère mouvements pour l'historique
    $myErr = passageEcrituresComptablesAuto($type_oper,$DATA[$key]["mnt"], $mouvements, $cptes_substitue,$devise);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // Ecriture de l'historique
  if ($nums_bordereaux != '')
    $hisExt = array("type_piece" => 13, "num_piece" => $nums_bordereaux);
  $MyError = ajout_historique($num_fct, NULL, $nums_bordereaux, $global_nom_login, date("r"), $mouvements, $hisExt);
  if ($MyError->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyError;
    //FIXME : à partir d'ici, il y avait un conflit dans le merge entre compta et TMB
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $MyError->param);

}

function get_encaisses() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT cpte_cpta_gui,solde ";
  $sql .= "FROM ad_cpt_comptable a, ad_gui b ";
  $sql .= "WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence and a.num_cpte_comptable=b.cpte_cpta_gui ";

  //$sql .= "AND id_gui=$id_guichet";
  //$sql = "SELECT id_gui, solde  FROM ad_gui";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  while ($row = $result->fetchrow()) {
    if ($row[1] == "0")
      $signe = 1;
    else
      $signe = -1;

    $retour[$row[0]] = $row[1]*$signe;
  }

  $dbHandler->closeConnection(true);
  return $retour;
}

function count_recherche_transactions($login, $fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin) {
  // Fonction qui compte le nombre de transactions renvoyées par recherche_transactions
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) FROM ad_his WHERE id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(login='$login') AND  ";
  if ($fonction != NULL) $sql .= "(type_fonction=$fonction) AND  ";
  if ($num_client != NULL) $sql .= "(id_client=$num_client) AND   ";
  if ($date_min != NULL) $sql .= "(DATE(date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(date)<=DATE('$date_max')) AND  ";
  if ($trans_min != NULL) $sql .= "(id_his>=$trans_min) AND  ";
  if ($trans_max != NULL) $sql .= "(id_his<=$trans_max) AND  ";

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

function recherche_transactions($login, $fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT h.*, e.type_operation, CASE WHEN h.type_fonction = 470 THEN e.info_ecriture ELSE NULL END as info_ecriture FROM ad_his h LEFT JOIN ad_ecriture e on e.id_his = h.id_his WHERE h.id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(h.login='$login') AND  ";
  if ($fonction != NULL) $sql .= "(h.type_fonction=$fonction) AND  ";
  if ($num_client != NULL) $sql .= "(h.id_client=$num_client) AND  ";
  if ($date_min != NULL) $sql .= "(DATE(h.date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(h.date)<=DATE('$date_max')) AND  ";
  if ($trans_min != NULL) $sql .= "(h.id_his>=$trans_min) AND  ";
  if ($trans_max != NULL) $sql .= "(h.id_his<=$trans_max) AND  ";

  //remove multi agence elements
  $sql .= "(h.type_fonction NOT IN (92,93, 193, 194)) AND  ";

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

    if ((! $trans_fin) || ($row2[0] > 0)) {
      $retour[$i] = $row;
      $retour[$i]['trans_fin'] = ($row2[0] > 0);
      ++$i;
    }
  }

  $dbHandler->closeConnection(true);

  return $retour;
}


/**
 * Renvoie les détails financiers de plusieurs transactions pour le rapport visualisation des transactions
 * @author Ibou
 * @since 3.2.2
 * @param $login, $fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin
 *
 * @return array On renvoie un tableau de la forme array(id_his=>value, type_fonction=>value, id_client=>value, login=>value,
 *               infos=>value, date=>value,
 *               ecritures=><B>array</B>(id_ecriture=><B>array</B>(...détails écriture..., mouvements=><B>array</B>(... détails mouvements...))), ad_his_ext => array(* FROM ad_his_ext)
 */
function recherche_transactions_details($login, $fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT m.*, a.*, b.libel_jou,h.* FROM ad_his h, ad_ecriture a, ad_mouvement m, ad_journaux b WHERE h.id_ag=$global_id_agence   ";
  if ($login != NULL) $sql .= " AND (h.login='$login')  ";
  if ($fonction != NULL) $sql .= " AND (h.type_fonction=$fonction) ";
  if ($num_client != NULL) $sql .= " AND (h.id_client=$num_client) ";
  if ($date_min != NULL) $sql .= " AND (DATE(h.date)>=DATE('$date_min')) ";
  if ($date_max != NULL) $sql .= " AND (DATE(h.date)<=DATE('$date_max')) ";
  if ($trans_min != NULL) $sql .= " AND (h.id_his>=$trans_min) ";
  if ($trans_max != NULL) $sql .= " AND (h.id_his<=$trans_max) ";

  //remove multi agence elements
  $sql .= " AND (type_fonction NOT IN (92,93, 193, 194)) ";

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
/**
 * Renvoie les détails financiers d'une transaction au niveau de la visualisation
 * @author Unknown
 * @since 1.0
 * @param int $id_trans Transaction dans l'historique pour laquelle on veut les détails
 * @return array On renvoie un tableau de la forme array(id_his=>value, type_fonction=>value, id_client=>value, login=>value,
 *               infos=>value, date=>value,
 *               ecritures=><B>array</B>(id_ecriture=><B>array</B>(...détails écriture..., mouvements=><B>array</B>(... détails mouvements...))), ad_his_ext => array(* FROM ad_his_ext)
 */
function get_details_transaction($id_trans,$operation=null) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  // Récupère les infos sur la fonction dans l'historique
  $sql = "SELECT * FROM ad_his WHERE id_ag=$global_id_agence AND id_his=$id_trans";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucune ou plusieurs occurences de la transaction
  }

  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);

  // Récupère l'en-tête de l'écriture dans ad_ecriture
  $sql = "SELECT a.*, b.libel_jou ";
  $sql .= "FROM ad_ecriture a, ad_journaux b ";
  $sql .= "WHERE a.id_ag = b.id_ag AND a.id_ag = $global_id_agence AND a.id_jou = b.id_jou and id_his = $id_trans ";
  if ($operation != NULL){
    $sql .= " AND a.type_operation = $operation ";
  }
  $sql .= "ORDER BY a.id_ecriture;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour['ecritures'] = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour['ecritures'][$row['id_ecriture']] = $row;
  }

  // Récupération du détail des mouvements comptables
  foreach ($retour['ecritures'] as $key => $value) {
    $sql = "SELECT * FROM ad_mouvement WHERE id_ag = $global_id_agence AND id_ecriture = $key ORDER BY sens DESC;";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $retour['ecritures'][$key]['mouvements'] = array();

    $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if ($row['cpte_interne_cli'] != NULL) {
        $InfosCompte = getAccountDatas($row['cpte_interne_cli']);
        $row['num_complet_cpte'] = $InfosCompte['num_complet_cpte'];
      }
      $retour['ecritures'][$key]['mouvements'][$count] = $row;
      $count++;
    }
  }

  // Recherche des infos éventuelles dans ad_his_ext si appliquable
  if ($retour["id_his_ext"] != "") {
    $sql = "SELECT * FROM ad_his_ext WHERE id_ag = $global_id_agence AND id = ".$retour["id_his_ext"];

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $INFOS_EXT = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $retour["infos_ext"] = $INFOS_EXT;
  }

  $dbHandler->closeConnection(true);
  return $retour;
}




function get_info_guichet($guichet) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT encaisse, libel_gui FROM ad_gui where id_ag=$global_id_agence AND id_gui=$guichet";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $info["encaisse"]=$row[0];
  $info["nomguichet"]=$row[1];

  $sql = "SELECT login FROM ad_log WHERE guichet=$guichet";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $login = $row[0];

  $sql = " select nom, prenom from ad_uti, ad_log where ad_uti.id_utilis=ad_log.id_utilisateur and login='$login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $nom = $row[0];
  $prenom = $row[1];


  $info["name"]=$prenom." ".$nom;
  return $info;
}

function getCommissionVirement($montant,$id_ag) {
  /*
  Renvoie le montant de la commission à percevoir ou zéro s'il n'y a rien
  */

  global $dbHandler;
  $db = $dbHandler->openConnection();

  //récuperer dans ad_agc
  $AG = getAgenceDatas($id_ag);

  $mnt = 0;

  $mnt += ($montant * $AG["prc_com_vir"]) + $AG["mnt_com_vir"];

  $dbHandler->closeConnection(true);

  return $mnt;
}

/**
 * Effectue une opération de change Cash ==> Chash
 * Fonction appelée depuis l'interface
 * @param int $id_gui Numéro du guichet
 * @param char(3) $devise_achat Devise achetée
 * @param char(3) $devise_vendue Devise vendue
 * @param float $mnt_deb Montant à changer
 * @param float $mnt_cred Montant à remettre au client
 * @param float $commission Commission nette de change (taxe incluse)
 * @param float $taux Taux utilisé
 * @param defined(1,2,3) $dest_reste Destination du reste de change
 * @param float $reste Reste du change en devise de référence
 * @return ErrorObj Objet Erreur
 * @author Thomas Fastenakel
 */
function changeCash($id_gui, $mnt_deb, $mnt_cred, $devise_achat, $devise_vente, $commissionnette, $taux, $dest_reste) {

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $subst = array();
  $subst["cpta"] = array();

  $cpte_cpta_gui = getCompteCptaGui($id_gui);
  $subst["cpta"]["debit"] = $cpte_cpta_gui;
  $subst["cpta"]["credit"] = $cpte_cpta_gui;

  $comptable = array();

  $myErr = change($devise_achat, $devise_vente, $mnt_deb, $mnt_cred, 460, $subst, $comptable, $dest_reste, $commissionnette, $taux);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  global $global_nom_login;

  $myErr = ajout_historique(186, NULL, NULL, $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return $myErr;
}

/**
 * Fonction qui passe une opération diverse de caisse
 * @author Papa
 * @since 1.0.8
 * @param $type_operation le type de l'opération
 * @param $montant le montant de l'opération
 * @param $devise la devise de l'opération
 * @param $id_cpte l'identificateur du compte (quand utilisé)
 * @param $piece_just la pièce de justification
 * @return Objet Error
 */
function passageODC($type_operation, $montant, $devise, $id_cpte, $piece_just) {
  global $dbHandler, $global_id_guichet, $global_nom_login, $global_id_agence, $global_monnaie;
  $db = $dbHandler->openConnection();

  // Récupération des informations de l'opération
  $temp = array();
  $temp['type_operation'] = $type_operation;
  $operation = getOperations($temp['type_operation']);
  $operation = $operation->param['libel']; // suppression des indices du tableau

  // Récupération de la catégorie et du libelléde l'opération
  $categorie_ope = $operation->param['categorie_ope'];
  $libel_ope = $operation->param['libel'];

  //si le un compte client est renseigné il faut prendre le numéro du client
  if($id_cpte != Null or $id_cpte !='') {
    $ACC = getAccountDatas($id_cpte);
    $id_client = $ACC['id_titulaire'];
  }

  // Vérification de la catégorie de l'opération
  if ($categorie_ope == 3) {
    // Vérification de l'existence du numéro de compte
    if ($id_cpte == '') {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_GENERIQUE);
    }


    // Vérification de la devise du compte
    if ($ACC['devise'] != $devise) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_GENERIQUE);
    }
  }

  // Mouvements et comptes substitués
  $mouvements = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();

  // Détail de l'opération : infos au débit et au crédit
  $myErr = getDetailsOperation($type_operation);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $detail_ope = $myErr->param;

  // traitement au débit de l'opération
  if ($detail_ope['debit']['categorie'] == 0 ) {
    $num = $detail_ope['debit']['compte'];
    $param = array();
    $param["num_cpte_comptable"] = $num;
    $compte = getComptesComptables($param);
    $devise_contrepartie = $compte[$num]['devise'];
    if ($devise_contrepartie == '')
      $devise_contrepartie = $devise;

    $sens = $detail_ope['debit']['sens'];
    $mnt_debit = false;
    $cptes_substitue["cpta"]["debit"] = $detail_ope['debit']['compte'];

  }
  elseif($detail_ope['debit']['categorie'] == 2 ) { // mvt de compte
    // Vérification de l'existence du numéro de compte
    if (getSoldeDisponible($id_cpte) < $montant) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_SOLDE_INSUFFISANT);
    }
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    $cptes_substitue["int"]["debit"] = $id_cpte;

  }
  elseif($detail_ope['debit']['categorie'] == 4) { // mvt de caisse
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);
    /* Arrondi du montant opération au guichet */
    $critere = array();
    $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
    $cpte_gui = getComptesComptables($critere);
    //$montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']); //Ticket 792 : montant doit etre non arrondie pour que les montants à l'ecran sont égal à les montants comptabilité respectivement
  }

  // traitement au crédit
  if ($detail_ope['credit']['categorie'] == 0 ) {
    $num = $detail_ope['credit']['compte'];
    $param = array();
    $param["num_cpte_comptable"] = $num;
    $compte = getComptesComptables($param);
    $devise_contrepartie = $compte[$num]['devise'];
    if ($devise_contrepartie == '') {
      $devise_contrepartie = $devise;
    }
    $sens = $detail_ope['credit']['sens'];
    $mnt_debit = true;
    $cptes_substitue["cpta"]["credit"] = $detail_ope['credit']['compte'];
  }
  elseif($detail_ope['credit']['categorie'] == 2 ) { //mvt de compte
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
    $cptes_substitue["int"]["credit"] = $id_cpte;
  }
  elseif($detail_ope['credit']['categorie'] == 4 ) //mvt de caisse
  $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($global_id_guichet);

  if ($sens == SENS_DEBIT) {
    $devise_debit = $devise_contrepartie;
    $devise_credit = $devise;
  } else {
    $devise_debit = $devise;
    $devise_credit = $devise_contrepartie;
  }

	//perception des éventuelles taxes sur l'opération
	$myErr = reglementTaxe($type_operation, $montant, $sens, $devise, $cptes_substitue, $mouvements);
	if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  // On récupère mouvements pour l'historique
  $myErr = effectueChangePrivate($devise_debit, $devise_credit, $montant, $type_operation, $cptes_substitue, $mouvements, $mnt_debit);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  // Ajout de l'historique
  $myErr = ajout_historique(189, $id_client, NULL, $global_nom_login, date("r"), $mouvements, $piece_just);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $myErr->param);

}


/**
 * Fonction qui parse et vérifie un fichier de données lors d'un dépôt par lot via fichier
 * @author Antoine
 * @since 2.2.5
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_fichier_lot($fichier_lot, $type_destination) {

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();
  $total=array();
  $total_com=array();

  $total=array();
 	$total_com=array();
  if ($type_destination != 1 && $type_destination != 2) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GENERIQUE);
  }

  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }

  $handle = fopen($fichier_lot, 'r');

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  $count = 0;
  $num_complet_cpte = "";

  while (($data = fgetcsv($handle, 200, ';')) != false) {
    $count++;

    $num = count($data);
    if ($num != 3 && $num !=2) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }

    if ($type_destination == 1) {
      if (isNumComplet($data[0])) {
        $id_cpte = get_id_compte($data[0]);
        if($id_cpte==NULL){
	          $dbHandler->closeConnection(false);
	        return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
        }
        $num_complet_cpte = $data[0];
      } else {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_NUM_COMPLET_CPTE, array("ligne" => $count));
      }
    }
    else {
      $id_client = $data[0];
      preg_match("([0-9]+)", $id_client, $result);
      if (strlen($id_client) != strlen($result[0])) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_ID_CLIENT, array("ligne" => $count));
      } else if (client_exist($id_client)) {
        $id_cpte = getBaseAccountID($id_client);
      } else {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_ID_CLIENT, array("ligne" => $count));
      }
    }

    $ACC = getAccountDatas($id_cpte);
    if ($type_destination == 2 ) {
    	if ($ACC['devise'] != $devise) {
    		$dbHandler->closeConnection(false);
      	return new ErrorObj(ERR_DEVISE_CPTE, array("ligne" => $count));
    	}
    }


    $montant = $data[1];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $montant, $result);
    if (strlen($montant) != strlen($result[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }

    $DATA[$count] = array('id_cpte' => $id_cpte, 'id_client' => $ACC['id_titulaire'], 'num_complet_cpte' => $num_complet_cpte, 'montant' => $montant);
    if (!isset( $total[$ACC['devise']]))
    	 $total[$ACC['devise']]=$montant;
    else
    	$total[$ACC['devise']] += $montant;

    //commission ou frais de virement s'il s'agit virement salaire
    $mnt_commission = $data[2];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $mnt_commission, $result1);
    if (strlen($mnt_commission) != strlen($result1[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }
    if($mnt_commission > 0 ) {
    	$DATA[$count]['mnt_com']=$mnt_commission;
    } else {
    	$DATA[$count]['mnt_com']=$ACC['frais_transfert'];
      //si aucun comission specifier on prend celle du agence : ticket 656
      if ($DATA[$count]['mnt_com']<=0){
        if ($AGC['mnt_com_vir']<=0){
          $DATA[$count]['mnt_com']=$AGC['prc_com_vir']*$data[1];
        }
        else{
          $DATA[$count]['mnt_com']=$AGC['mnt_com_vir'];
        }
      }
    }
    if($DATA[$count]['mnt_com']>0){
    	if (!isset( $total_com[$ACC['devise']]))
    		$total_com[$ACC['devise']]=$DATA[$count]['mnt_com'];
    	else
    		$total_com[$ACC['devise']] += $DATA[$count]['mnt_com'];
    }
  }
  fclose($handle);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('data' => $DATA, 'total' => $total,'total_commission'=>$total_com));
}


/**
 * Fonction qui parse et vérifie un fichier de données lors d'une mise a jour de quotite
 * @author Antoine
 * @since 2.2.5
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_fichier_lot_quotite($fichier_lot)
{

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();
  $total = array();
  $total_com = array();

  $total = array();
  $total_com = array();

  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }

  $handle = fopen($fichier_lot, 'r');

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  $count = 0;
  $num_complet_cpte = "";

  while (($data = fgetcsv($handle, 200, ';')) != false) {
    $count++;

    $num = count($data);
    if ($num != 3 && $num != 2) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }
    $matricule = get_matricule($data[0]);
    if ($matricule == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
    }
    else{
      $num_matricule = trim($data[0]);
    }
      $ACC = getMatriculeDatas($num_matricule);

      $quotite = $data[1];
      preg_match("([0-9]*\.{0,1}[0-9]+)", $quotite, $result);
    if ($data[1] <0){
      $quotite = abs($data[1]);
    }
      if (strlen($quotite) != strlen($result[0])) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
      }
    if ($data[1] <0){
      $quotite = $data[1];
    }

      $DATA[$count] = array('matricule' => $num_matricule, 'id_client' => $ACC, 'montant' => $quotite);
      /*if (!isset($total[$ACC['devise']]))
        $total[$ACC['devise']] = $montant;
      else
        $total[$ACC['devise']] += $montant;*/

      //commission ou frais de virement s'il s'agit virement salaire
      /*$mnt_commission = $data[2];
      preg_match("([0-9]*\.{0,1}[0-9]+)", $mnt_commission, $result1);
      if (strlen($mnt_commission) != strlen($result1[0])) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
      }
      if ($mnt_commission > 0) {
        $DATA[$count]['mnt_com'] = $mnt_commission;
      } else {
        $DATA[$count]['mnt_com'] = $ACC['frais_transfert'];
        //si aucun comission specifier on prend celle du agence : ticket 656
        if ($DATA[$count]['mnt_com'] <= 0) {
          if ($AGC['mnt_com_vir'] <= 0) {
            $DATA[$count]['mnt_com'] = $AGC['prc_com_vir'] * $data[1];
          } else {
            $DATA[$count]['mnt_com'] = $AGC['mnt_com_vir'];
          }
        }
      }
      if ($DATA[$count]['mnt_com'] > 0) {
        if (!isset($total_com[$ACC['devise']]))
          $total_com[$ACC['devise']] = $DATA[$count]['mnt_com'];
        else
          $total_com[$ACC['devise']] += $DATA[$count]['mnt_com'];
      }*/
    }
    fclose($handle);

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR, array('data' => $DATA,));
  }

/**
 * Fonction qui parse et vérifie un fichier de données lors d'un dépôt par lot via fichier
 * @author Antoine
 * @since 2.2.5
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_fichier_lot_client_par_matricule($fichier_lot) {

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();
  $total=array();
  $total_com=array();

  $total=array();
  $total_com=array();
  /*if ($type_destination != 1 && $type_destination != 2) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GENERIQUE);
  }*/

  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }
  $handle = fopen($fichier_lot, 'r');

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  $count = 0;
  $num_complet_cpte = "";

  while (($data = fgetcsv($handle, 200, ';')) != false) {
    $count++;

    $num = count($data);
    if ($num != 3 && $num !=2) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }

    $matricule = get_matricule($data[0]);
    if ($matricule == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
    }
    else{
      $num_matricule = trim($data[0]);
    }
    //Recupere id_client
    $num_cli = getMatriculeDatas($num_matricule);

    //recupere id_cpte_base du client
    $id_cpte = getBaseAccountID($num_cli);


    $ACC = getAccountDatas($id_cpte);

    $montant = $data[1];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $montant, $result);
    if (strlen($montant) != strlen($result[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }

    $DATA[$count] = array('id_cpte' => $id_cpte, 'id_client' => $num_cli, 'num_complet_cpte' => $num_complet_cpte, 'montant' => $montant);
    if (!isset( $total[$ACC['devise']]))
      $total[$ACC['devise']]=$montant;
    else
      $total[$ACC['devise']] += $montant;

    //commission ou frais de virement s'il s'agit virement salaire
    $mnt_commission = $data[2];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $mnt_commission, $result1);
    if (strlen($mnt_commission) != strlen($result1[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }
    if($mnt_commission > 0 ) {
      $DATA[$count]['mnt_com']=$mnt_commission;
    } else {
      $DATA[$count]['mnt_com']=$ACC['frais_transfert'];
      //si aucun comission specifier on prend celle du agence : ticket 656
      if ($DATA[$count]['mnt_com']<=0){
        if ($AGC['mnt_com_vir']<=0){
          $DATA[$count]['mnt_com']=$AGC['prc_com_vir']*$data[1];
        }
        else{
          $DATA[$count]['mnt_com']=$AGC['mnt_com_vir'];
        }
      }
    }
    if($DATA[$count]['mnt_com']>0){
      if (!isset( $total_com[$ACC['devise']]))
        $total_com[$ACC['devise']]=$DATA[$count]['mnt_com'];
      else
        $total_com[$ACC['devise']] += $DATA[$count]['mnt_com'];
    }
  }
  fclose($handle);
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('data' => $DATA, 'total' => $total,'total_commission'=>$total_com));
}






/**
 * Fonction qui parse et vérifie un fichier de données lors des retrait pour la carte UBA ( MAE-30)
 * @author Antoine
 * @since 2.2.5
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_fichier_lot_carte_uba($fichier_lot)
{

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();
  $total = array();
  $total_com = array();

  $total = array();
  $total_com = array();

  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }

  $handle = fopen($fichier_lot, 'r');

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  $count = 0;
  $num_complet_cpte = "";

  while (($data = fgetcsv($handle, 200, ';')) != false) {
    $count++;

    $num = count($data);
    if ($num != 3 && $num != 2) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }
    $exist_num_carte = get_carte_uba($data[0]);
    if ($exist_num_carte == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
    }
    else{
      $num_carte = $data[0];
    }
    $ACC = getCarteUBADatas($num_carte);
    //$num_cpte = getBaseAccountID($ACC);
    //recuperation par defaut du compte pour la MA2E
    $num_cpte = getCompteData($ACC,7);
    if (sizeof($num_cpte) == null){
      return new ErrorObj(ERR_CPTE_INEXISTANT, array("ligne" => $count));
    }

    $mnt_retrait = $data[1];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $mnt_retrait, $result);
    if (strlen($mnt_retrait) != strlen($result[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }

    // verify si le solde du client est suffisant pour le retrait


    $DATA[$count] = array('numero_carte' => $num_carte, 'id_client' => $ACC, 'numero_cpte' => $num_cpte['id_cpte'], 'montant' => $mnt_retrait);
    /*if (!isset($total[$ACC['devise']]))
      $total[$ACC['devise']] = $montant;
    else
      $total[$ACC['devise']] += $montant;*/

    //commission ou frais de virement s'il s'agit virement salaire
    /*$mnt_commission = $data[2];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $mnt_commission, $result1);
    if (strlen($mnt_commission) != strlen($result1[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }
    if ($mnt_commission > 0) {
      $DATA[$count]['mnt_com'] = $mnt_commission;
    } else {
      $DATA[$count]['mnt_com'] = $ACC['frais_transfert'];
      //si aucun comission specifier on prend celle du agence : ticket 656
      if ($DATA[$count]['mnt_com'] <= 0) {
        if ($AGC['mnt_com_vir'] <= 0) {
          $DATA[$count]['mnt_com'] = $AGC['prc_com_vir'] * $data[1];
        } else {
          $DATA[$count]['mnt_com'] = $AGC['mnt_com_vir'];
        }
      }
    }
    if ($DATA[$count]['mnt_com'] > 0) {
      if (!isset($total_com[$ACC['devise']]))
        $total_com[$ACC['devise']] = $DATA[$count]['mnt_com'];
      else
        $total_com[$ACC['devise']] += $DATA[$count]['mnt_com'];
    }*/
  }
  fclose($handle);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('data' => $DATA,));
}





/**
 * Fonction qui parse et vérifie un fichier de données lors de la souscription de part social par lot via fichier
 * @author ares
 * @since 2.8.9
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_ps_fichier_lot($fichier_lot) {

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();


  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }

  $handle = fopen($fichier_lot, 'r');

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  $count = 0;
  $num_complet_cpte = "";

  while (($data = fgetcsv($handle, 200, ';')) != false) {
    $count++;

    $num = count($data);
    if ($num != 3) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }

     $id_client = $data[0];
      preg_match("([0-9]+)", $id_client, $result);

      if (strlen($id_client) != strlen($result[0])) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_ID_CLIENT, array("ligne" => $count));
      }elseif (!client_exist($id_client)){
      	$dbHandler->closeConnection(false);
        return new ErrorObj(ERR_ID_CLIENT_NON_EXIST, array("ligne" => $count));
      }
      $nbre_ps = $data[1];
      preg_match("([0-9]+)", $nbre_ps, $result);

      if (strlen($nbre_ps) != strlen($result[0])) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_ID_CLIENT, array("ligne" => $count));
      }

    $montant = $data[2];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $montant, $result);
    if (strlen($montant) != strlen($result[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }
    $montant=recupMontant($montant);
    // le montant des part sociale doit etre multiple de la val nominale de ps
    if($montant!=($AGC['val_nominale_part_sociale']*$nbre_ps) ){
    	$dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MNT_PS, array("valeur nominale part sociale"=>$AGC['val_nominale_part_sociale'],"ligne" => $count));
    }
    // verifier le nbre de part sociale max souscripte autorisé pour un client
    $nbre_part_sous_param=getNbrePartSoc($id_client);
    $nbre_part_sous=$nbre_part_sous_param->param[0]['nbre_parts']+$nbre_ps;
    $nbre_part_max=$AGC['nbre_part_social_max_cli'];
    if(($nbre_part_max>0 ) && ($nbre_part_sous > $nbre_part_max) ) {
     $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBRE_MAX_PS, array("Nbre de parts sociales max"=>$nbre_part_max,"ligne" => $count));
    }

    $DATA[$count] = array( 'id_client' => $id_client, 'nbre_ps' => $nbre_ps, 'montant' => $montant);
    $total += $montant;
  }
  fclose($handle);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('data' => $DATA, 'total' => $total));
}

/**
 * Fonction qui effectue les versements lors d'un dépôt par lot via fichier
 * @author Antoine Guyette
 * @since 2.2.5
 * @param $fichier_lot emplacement du fichier de données
 * @param $id_gui identifiant du guichet
 * @param $data_virement données supplémentaires pour un virement et la source des fonds
 * @return ErrorObj
 */
function traite_fichier_lot ($fichier_lot, $type_destination, $id_gui, $data_virement, $autre_libel_ope = NULL) {

  global $dbHandler;

  $db = $dbHandler->openConnection();

  if ($type_destination == 3){
    $MyErr = parse_fichier_lot_client_par_matricule($fichier_lot);
  }else {
    $MyErr = parse_fichier_lot($fichier_lot, $type_destination);
  }

  if ($MyErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyErr;
  }

  $param = $MyErr->param;

  $DATA = $param['data'];
  $i=1;
   if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
  	$libel_ope_trad = new Trad();
  	$libel_ope_trad = $autre_libel_ope;
  	$libel_ope_trad->save();
    $libel_ope = $libel_ope_trad->get_id_str();
  }else{
  	$libel_ope = htmlspecialchars($autre_libel_ope, ENT_QUOTES, "UTF-8");
  }

  foreach ($DATA as $OPERATION) {
      $InfoCpte = getAccountDatas($OPERATION['id_cpte']);
      $erreur = CheckDepot($InfoCpte, $OPERATION['montant']);
      if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          $index=_("ligne");
          if (! is_array($erreur->param)) {
              $erreur->param =array("-"=>$erreur->param,$index => $i);
          } else {
              $erreur->param[$index]=$i;
          }
          return $erreur;
      }
  }

  foreach ($DATA as $OPERATION) {
    $OPERATION["date"] = date("d/m/Y");

      $MyErr = traite_fichier_depot($OPERATION, $id_gui, $data_virement, $libel_ope);

    if ($MyErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      $index=_("ligne");
      if (! is_array($MyErr->param)) {
      	$MyErr->param =array("-"=>$MyErr->param,$index => $i);
      } else {
      	$MyErr->param[$index]=$i;
      }
      return $MyErr;
    }
    $i++;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Fonction qui effectue les mises a jour  des quotite via fichier
 * @author
 * @since 2.2.5
 * @return ErrorObj
 */
function traite_fichier_matricule ($fichier_lot, $id_gui, $autre_libel_ope = NULL)
{

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $MyErr = parse_fichier_lot_quotite($fichier_lot);

  if ($MyErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyErr;
  }

  $param = $MyErr->param;

  $DATA = $param['data'];
  foreach ($DATA as $key => $value) {

    $data_matricule=array();
    $data_matricule['mnt_quotite']=$value["montant"];

    $where_update_quotite["id_client"] = $value['id_client'];
    $where_update_quotite["matricule"] = $value["matricule"];
    $update_quotite = buildUpdateQuery('ad_cli',$data_matricule,$where_update_quotite);
    $result_quotite = $db->query($update_quotite);
    if (DB::isError($result_quotite)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  // ajout historique
  global $global_nom_login;

  $myErr = ajout_historique(159, $id_gui, "Mise à jour quotité (par lot)", $global_nom_login, date("r"));

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }


  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}



/**
 * Fonction qui effectue un dépôt ou un virement lors d'un dépôt par lot via fichier
 * @author Antoine Guyette
 * @since 2.2.4
 * @param $DATA données pour le dépôt ou le virement
 * @param $id_gui identifiant du guichet
 * @param $data_virement données supplémentaires pour un virement et la source des fonds
 * @return ErrorObj
 */
function traite_fichier_depot ($DATA, $id_gui, $data_virement, $autre_libel_ope=NULL) {

  global $dbHandler,$global_id_agence, $error;

  $db = $dbHandler->openConnection();


  $id_cpte = $DATA['id_cpte'];
  $id_client = $DATA['id_client'];
  // Informations supplémentaires: on peut y ajouter toute information disparate 
  $infos_sup = array();
  $infos_sup["autre_libel_ope"] = $autre_libel_ope;

  // Va chercher la date de la dernière mise à jour du solde min et le solde sourant
  $sql = "SELECT date_solde_calcul_interets, solde_calcul_interets, solde FROM ad_cpt WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result->fetchrow();
  $date = $row[0];
  $solde_int = $row[1];
  $solde = $row[2];

  // Eventuellement mise à jour solde_interets
  $date = pg2phpDatebis($date);
  $date2 = splitEuropeanDate($DATA['date']);
  $solde_int += $DATA['montant'];
  // Si la date de dernière mise à jour du solde_min est postérieure à la date du dépôt
  if (gmmktime(0,0,0,$date[0],$date[1],$date[2]) >= gmmktime(0,0,0,$date2[1],$date2[0],$date2[2])) {
    $sql = "UPDATE ad_cpt SET solde_calcul_interets = $solde_int WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  // Récupération des informations du compte et du produit d'épargne
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  /* Eventuels frais de virement en cas de dépôt par lot via fichier pour les virements de salaires */
  if ($DATA['mnt_com'] > 0)
      $frais_virement = $DATA['mnt_com'];
   else
      $frais_virement = NULL;
   $type_depot = 159;//Dépôt par lôt via fichier
  // Source : correspondant banquaire
  if ($data_virement['source'] == 2) {
    $InfoTireur = getTireurBenefDatas($data_virement['id_ben']);
    $data['id_ext_benef']           = null;
    $data['id_cpt_ordre']           = null;
    $data['sens']                   = 'in ';
    $data['type_piece']             = 11;
    $data['num_piece']              = $data_virement['num_piece'];
    $data['date_piece']             = $DATA['date'];
    $data['date']                   = date("d/m/Y");
    $data['montant']                = $DATA['montant'];
    $data['devise']                 = $InfoCpte['devise'];
    $data['remarque']               = $data_virement['remarque'];
    $data['communication']          = $data_virement['communication'];
    $data['id_correspondant']       = $data_virement['correspondant'];
    $data['id_cpt_benef']           = $DATA['id_client'];
    $data['id_ext_ordre']           = $data_virement['id_ben'];
    $data['id_banque']              = $InfoTireur['id_banque'];


    // FIXME ne faut-il pas traiter le multi-devise ici ?
    $montant_remboursement = $DATA['montant'];
    $MyErr = receptionVirement($data, $InfoCpte, $InfoProduit, NULL , $frais_virement , $type_depot, $infos_sup, $montant_remboursement);
    $montant_remboursement -= getPreleveFraisSMS($MyErr->datas["comptable"]);
    if ($MyErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $MyErr;
    }else{
        if(isMobileLending()) {
            //EVOL -- MOBILE LENDING
            $data_demande_mb = getDemandeCredit($id_client, " statut_demande IN (2, 5) ORDER BY id_doss ASC limit 1");
            if (sizeof($data_demande_mb) > 0) {
                $prod_info_cdt = getProdInfoByID($data_demande_mb[0]['id_prod']);
                if ($prod_info_cdt[$data_demande_mb[0]['id_prod']]['crdt_salairie'] == 't') {
                    $rembourse_erreur = rembourseMontantDepot($DATA['id_client'], $montant_remboursement);

                    if ($rembourse_erreur->errCode != NO_ERR) {
                        $html_err = new HTML_erreur(_("Echec du remboursement."));
                        $html_err->setMessage(_("Erreur") . " : " . $error[$rembourse_erreur->errCode] . "<br/>" . _("Paramètre") . " : " . $rembourse_erreur->param);
                        $html_err->addButton("BUTTON_OK", 'Dcp-1');
                        $html_err->buildHTML();
                        echo $html_err->HTML_code;
                        exit();
                    }
                }
            }
        }
    }
  }

  // Source : Guichet
  else if ($data_virement['source'] == 1) {
    //     unset($data_virement['source']);
    $data_virement['sens'] = 'in ';

    $MyErr = depot_cpte($id_gui, $id_cpte, $DATA['montant'], $InfoProduit, $InfoCpte, $data_virement, $type_depot,NULL,$frais_virement, $infos_sup);
    if ($MyErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $MyErr;
    }else{
        $montant_remboursement = $DATA['montant'];
        if(isMobileLending()) {
            //EVOL -- MOBILE LENDING ML-76
            $montant_remboursement -= $frais_virement;
            $montant_remboursement -= getPreleveFraisSMS($MyErr->datas["comptable"]);
            $data_demande_mb = getDemandeCredit($id_client, " statut_demande IN (2, 5) ORDER BY id_doss ASC limit 1");
            if (sizeof($data_demande_mb) > 0) {
                $prod_info_cdt = getProdInfoByID($data_demande_mb[0]['id_prod']);
                if($prod_info_cdt[$data_demande_mb[0]['id_prod']]['crdt_salairie'] == 't') {
                    $rembourse_erreur = rembourseMontantDepot($id_client, $montant_remboursement);

                    if ($rembourse_erreur->errCode != NO_ERR) {
                        $html_err = new HTML_erreur(_("Echec du remboursement."));
                        $html_err->setMessage(_("Erreur") . " : " . $error[$rembourse_erreur->errCode] . "<br/>" . _("Paramètre") . " : " . $rembourse_erreur->param);
                        $html_err->addButton("BUTTON_OK", 'Dcp-1');
                        $html_err->buildHTML();
                        echo $html_err->HTML_code;
                        exit();
                    }
                }
            }
        }
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Fonction qui effectue les souscription des part sociales par lot via fichier
 * @author ares
 * @since 2.8.9
 * @param $fichier_lot emplacement du fichier de données
 * @param $id_gui identifiant du guichet
 * @param $dataSourceFonds données de la source des fonds
 * @return ErrorObj
 */
function traite_ps_fichier_lot ($fichier_lot, $dataSourceFonds) {

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $MyErr =parse_ps_fichier_lot($fichier_lot);


  if ($MyErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyErr;
  }

  $param = $MyErr->param;

  $DATA = $param['data'];
  foreach ($DATA as $OPERATION) {
    $OPERATION["date"] = date("d/m/Y");
    $MyErr = traite_ps($OPERATION, $dataSourceFonds);
    if ($MyErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $MyErr;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Fonction qui effectue une souscription de part sociale lors d'une souscription des part sociales par lot via fichier
 * @author ares
 * @since 2.8.9
 * @param $DATA données pour la souscription de part social
 * @param $dataSourceFonds données de la source des fonds
 * @return ErrorObj
 */
function traite_ps ($DATA, $dataSourceFonds) {

  global $global_id_agence,$dbHandler,$global_id_utilisateur,$global_nom_login;

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  // Source : correspondant banquaire
  if ($dataSourceFonds['source'] == 2) {
    $InfoTireur = getTireurBenefDatas($dataSourceFonds['id_ben']);
    $data['id_ext_benef']           = null;
    $data['id_cpt_ordre']           = null;
    $data['sens']                   = 'in ';
    $data['type_piece']             = 11;
    $data['num_piece']              = $dataSourceFonds['num_piece'];
    $data['date_piece']             = $DATA['date'];
    $data['date']                   = date("d/m/Y");
    $data['montant']                = $DATA['montant'];
    $data['devise']                 = $devise ;
    $data['remarque']               = $dataSourceFonds['remarque'];
    $data['communication']          = $dataSourceFonds['communication'];
    $data['id_correspondant']       = $dataSourceFonds['id_source'];
    $data['id_cpt_benef']           = $DATA['id_client'];
    $data['id_ext_ordre']           = $dataSourceFonds['id_ben'];
    $data['id_banque']              = $InfoTireur['id_banque'];


  } // Source : compte de base
  elseif($dataSourceFonds['source'] == 3){

  	$id_cpt = getBaseAccountID($DATA['id_client']);
  	$soldeB=getSoldeDisponible($id_cpt);
  	$montant=recupMontant($DATA['montant']);

  	if($montant>$soldeB){
  		$dbHandler->closeConnection(false);
  		return new ErrorObj(ERR_CPTE_CLI_NEG,array('ID client'=>$DATA['id_client']));
  	}

  }
$compta=array();
if ($data!=NULL) $data_his_ext = creationHistoriqueExterieur($data);
  else $data_his_ext = NULL;


$MyError = souscriptionPartSocialeSource($DATA['id_client'],$DATA['nbre_ps'],$global_id_utilisateur,$compta,$DATA['montant'], $dataSourceFonds['source'],$dataSourceFonds['id_source']);
if ($MyError->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyError;
  }

$MyError = ajout_historique(190, $DATA['id_client'], NULL, $global_nom_login, date("r"), $compta, $data_his_ext, NULL);
  if ($MyError->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyError;
  }


  return new ErrorObj(NO_ERR);
}
/**
 * Fonction qui parse et vérifie un fichier de données lors de l'ajout des chéquiers imprimés.
 * @author ares
 * @since 3.4
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_ajout_chequier_imprimer_fichier($fichier_lot) {

  global $global_id_agence, $dbHandler;
  $db = $dbHandler->openConnection();

  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }
  $handle = fopen($fichier_lot, 'r');
  //$AGC = getAgenceDatas($global_id_agence);
  //$devise = $AGC['code_devise_reference'];
  $count = 0;
  $num_complet_cpte = "";
  $tabErreur =array();

  while (($data = fgetcsv($handle, 200, ';')) != false) {
  	$count++;
    $num = count($data);
    if (isFusion()){
        if ($num != 5) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $num));
        }
    }else{
        if ($num != 3) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $num));
        }
    }
    /*if ($num != 3) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $num));
    }
      if ($num != 5) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $num));
      }*/
    if (isNumComplet($data[0])) {
    	$id_cpte = get_id_compte($data[0]);
        if($id_cpte==NULL){
	          $dbHandler->closeConnection(false);
	        return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
        }
        $num_complet_cpte = $data[0];
      } else {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_NUM_COMPLET_CPTE, array("ligne" => $count));
      }
      if (isFusion()){
//          $DATA[$id_cpte][$count] = array( 'id_cpte' => $id_cpte, 'num_first_cheque' => $data[1], 'num_last_cheque' => $data[2], 'n_agence' => $data[3], 'n_guichet' => $data[4]);
          $DATA[$id_cpte][$count] = array( 'id_cpte' => $id_cpte, 'num_first_cheque' => $data[1], 'num_last_cheque' => $data[2]);
      }else{
          $DATA[$id_cpte][$count] = array( 'id_cpte' => $id_cpte, 'num_first_cheque' => $data[1], 'num_last_cheque' => $data[2]);
      }
      //$DATA[$id_cpte][$count] = array( 'id_cpte' => $id_cpte, 'num_first_cheque' => $data[1], 'num_last_cheque' => $data[2]);
      //$DATA[$id_cpte][$count] = array( 'id_cpte' => $id_cpte, 'num_first_cheque' => $data[1], 'num_last_cheque' => $data[2], 'n_agence' => $data[3], 'n_guichet' => $data[4]);
      //$total += $montant;
  }
  fclose($handle);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('data' => $DATA));
}

function traite_ajout_chequier_imprimer($data)
{
    global $global_id_agence, $dbHandler;
    $db = $dbHandler->openConnection();

    $temp = array();

    foreach ($data as $id_cpte => $chequiers)
    {

        $count_carnets = 0;

        foreach ($chequiers as $cheques) {
          $temp[$id_cpte]['num_carnets_cumul']++; // = $count_carnets++;

        }

        $cmdeParam = getAttenteImpressionChequier($id_cpte);

        if ($cmdeParam->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $cmdeParam;
        }

        if (is_array($cmdeParam->param) && count($cmdeParam->param) > 0) {
            $cmde = $cmdeParam->param[0];

            // la commande de chequier courant :
            $cmde = $cmdeParam->param[0];
            $nbre_carnets = $cmde['nbre_carnets'];

            // control sur le nombre de carnets:
            if ($temp[$id_cpte]['num_carnets_cumul'] != $nbre_carnets) {
                $dbHandler->closeConnection(false);
                $num_cpte_complet = getLibelCompte(2, $id_cpte);
                $msg = " : $num_cpte_complet";
                return new ErrorObj(ERR_CMD_CHEQUIER_MAX_CARNETS, $msg);
            }

            $err1 = setImpressionChequier($cmde['id']);

            if ($err1->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $err1;
            }
        } else {
            $dbHandler->closeConnection(false);
            $msg = "";
            foreach ($chequiers as $ligne => $cheque) {
                $msg .= sprintf(_(' ligne  %s ,'), $ligne);

            }
            return new ErrorObj(ERR_NO_CMD_CHEQUIER, $msg);
        }

        foreach ($chequiers as $chequier) {
            $err = insertChequier($chequier);
            if ($err->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $err;
            }
        }
    }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

/**
 * Fonction qui parse et vérifie un fichier de données lors de la perception frais d'adhesion par lot via fichier
 * @author Roshan
 * @since 3.20
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_fa_fichier_lot($fichier_lot) {

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();


  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }

  $handle = fopen($fichier_lot, 'r');

  $AGC = getAgenceDatas($global_id_agence);
  $devise = $AGC['code_devise_reference'];

  $count = 0;
  $total = 0;
  $num_complet_cpte = "";

  while (($data = fgetcsv($handle, 200, ';')) != false) {
    $count++;

    $num = count($data);
    if ($num != 3) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }

    $id_client = $data[0];
    preg_match("([0-9]+)", $id_client, $result);

    if (strlen($id_client) != strlen($result[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_ID_CLIENT, array("ligne" => $count));
    }elseif (!client_exist($id_client)){
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_ID_CLIENT_NON_EXIST, array("ligne" => $count));
    }

    if (isNumComplet($data[1])) {
      $id_cpte = get_id_compte($data[1]);
      if($id_cpte==NULL){
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
      }
      $num_complet_cpte = $data[1];
    } else {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NUM_COMPLET_CPTE, array("ligne" => $count));
    }

    $montant = $data[2];
    preg_match("([0-9]*\.{0,1}[0-9]+)", $montant, $result);
    if (strlen($montant) != strlen($result[0])) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_MONTANT, array("ligne" => $count));
    }
    $montant=recupMontant($montant);

    $DATA[$count] = array( 'id_client' => $id_client, 'num_complet_cpte' => $num_complet_cpte, 'montant' => $montant);
    $total += $montant;
  }
  fclose($handle);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('data' => $DATA, 'total' => $total));
}

/**
 * Fonction qui effectue les perception frais d'adhesion par lot via fichier
 * @author Roshan
 * @since 3.20
 * @param $fichier_lot emplacement du fichier de données
 * @param $id_gui identifiant du guichet
 * @param $dataSourceFonds données de la source des fonds
 * @return ErrorObj
 */
function traite_fa_fichier_lot ($fichier_lot, $dataSourceFonds) {

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $MyErr =parse_fa_fichier_lot($fichier_lot);


  if ($MyErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyErr;
  }

  $param = $MyErr->param;

  $DATA = $param['data'];
  foreach ($DATA as $OPERATION) {
    $OPERATION["date"] = date("d/m/Y");
    $MyErr = traite_fa($OPERATION, $dataSourceFonds);
    if ($MyErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $MyErr;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Fonction qui effectue une perception frais d'adhesion lors d'une perception frais d'adhesion par lot via fichier
 * @author Roshan
 * @since 3.20
 * @param $DATA données pour la perception frais d'adhesion
 * @param $dataSourceFonds données de la source des fonds
 * @return ErrorObj
 */
function traite_fa ($DATA, $dataSourceFonds) {

  global $global_id_agence,$dbHandler,$global_id_utilisateur,$global_nom_login,$global_id_guichet;

  $CLI = array();
  $CLI = getClientDatas($DATA['id_client']);
  $mnt_droits_adhesion = getMontantDroitsAdhesion($CLI['statut_juridique']);
  debug($mnt_droits_adhesion);

  $myErr = perceptionFraisAdhesionInt($DATA['id_client'],$global_id_guichet,$DATA['montant'],1,$mnt_droits_adhesion);

  if ($myErr->errCode != NO_ERR){
    return $myErr;
  }

  return new ErrorObj(NO_ERR);
}

function insertApproDelestageAttente($id_guichet,$montant,$devise,$etat_appro_delestage,$type_action,$billetage)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $date_crea = date('r');
  $tableFields = array(
    "id_ag"=>$global_id_agence,
    "id_guichet"=>$id_guichet,
    "montant"=>recupMontant($montant),
    "devise"=>$devise,
    "etat_appro_delestage"=>$etat_appro_delestage,
    "type_action"=>$type_action,
    "date_creation"=>date('r'),
    "billetage"=>$billetage

  );

  $sql = buildInsertQuery("ad_approvisionnement_delestage_attente", $tableFields);

  $result = $db->query($sql);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }


  $sql_recup_num_transaction = "select max(id) from ad_approvisionnement_delestage_attente;";

  $result_recup_num_transaction=$db->query($sql_recup_num_transaction);
  if (DB::isError($result_recup_num_transaction)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result_recup_num_transaction->numRows() == 0)
    return NULL;

  $num_transaction = $result_recup_num_transaction->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR,$num_transaction);

}

/*
 * get traduction/libel operation diverses from ad traduction
 * usage : pour le rapports des operations diverses
 * Ahaad
 */
function getLibelOperationTransaction($type_op) { //Renvoie le libellé dun operation
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql="SELECT traduction from ad_cpt_ope a, ad_traductions b where a.libel_ope = b.id_str and  type_operation = $type_op  ;";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $retour = $result->fetchrow();
  return $retour[0];
}

?>
