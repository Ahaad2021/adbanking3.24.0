<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Gestion de la table historique, historique comptable et logs systèmes
 * @package Systeme
 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/extraits.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/dbProcedures/message_queue.php';

function ajout_historique($type_fonction, $id_client, $infos, $login, $date, $array_comptable=NULL, $data_ext=NULL, $idhis=NULL, $id_doss = NULL, $source = 2) {
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
  global $dbHandler, $global_id_agence, $debug, $appli;

  $db = $dbHandler->openConnection();
  $id_agence_encours = getNumAgence();

  // S'il y a des données à insérer dans la table historique des transferts avec l'extérieur, on commence par cette insertion.
  if ($data_ext == NULL) {
    $id_his_ext = 'NULL';
  } else {
    $id_his_ext = insertHistoriqueExterieur($data_ext);
    if ($id_his_ext == NULL) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
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
	    signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  }

	  $row = $result->fetchrow();
	  $idhis = $row[0];
	  // On insère dans la table historique
	  $sql = "INSERT INTO ad_his(id_his,id_ag, type_fonction, infos, id_client, login, date, id_his_ext) ";
	  $sql .= "VALUES($idhis,$id_agence_encours, $type_fonction, '$infos', $id_client, '$login', '$date', $id_his_ext)";
	  $result = $db->query($sql);
	  if (DB::isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__);
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
        $info_ecri = $value['info_ecriture'];
        if ($value["type_operation"] == 375 || $value["type_operation"] == 20) {
          $info_ecri = explode('-',$value['info_ecriture']);
          $info_ecri = $info_ecri[0];
        }
        $temp = array("libel" => $value["libel"], "type_operation" => $value["type_operation"], "date_comptable" => $value["date_comptable"], "id_jou" => $value["jou"], "id_exo" => $value["exo"],"info_ecriture"=>$info_ecri);
        $tab_fact[$value['id']] = $temp;
      }

    }
    if (round($equilibre, $global_monnaie_courante_prec) != 0) {
      //Si la somme débit != somme crédit
      $dbHandler->closeConnection(false);
      // FIXME : renvoyer un objet Error à la place du signalErreur
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }
  
  // Garde la liste des comptes comptables qui vont etre impactés par des mouvements
  $liste_comptes_comptable = array();

  $IAR_INFO_temp = array();
  
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
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      // Récupérer le numéro d'ecriture
      $sql = "SELECT max(id_ecriture) from ad_ecriture where id_ag=$global_id_agence ";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      $row = $result->fetchrow();
      $idecri = $row[0];

      // Insertion dans ad_mouvement les mouvements sur les comptes
      foreach ($array_comptable as $key1 => &$value1) { // Pour chaque mouvement
        if ($value1['id'] == $value ) { //mise à jour des soldes comptables

          //REL-80 et REL-84: Gestion montant non arrondie par un flag $isOperationIAR, qui par défaut est false
          //REL-80 et REL-84 $isOperationIAR is set true si c'est operations 374 et (375 et 20 relie)
          //REL-80 fonction setSoldeCpteCli - 4eme parametre. C'est pour les operations 375 et 20 (Remboursement IAR et interet Credit)
          //REL-84 fonction setSoldeComptable - 4eme parametre. C'est pour les operations 20 (Remboursement interet Credit associe a un IAR dans la foulee d'une operation 375)
          /****************************************************************************************/
          $isOperationIAR = false;
          if ($value1['type_operation'] == 374){ //Calcule IAR
            $isOperationIAR = true;
          }
          if ($value1['type_operation'] == 375){ //Reprise IAR
            $isOperationIAR = true;
            if (sizeof($IAR_INFO_temp) == 0){ //first time 375
              $IAR_INFO_temp[0] = $value1['cpte_interne_cli'];
              $IAR_INFO_temp[1] = $value1['info_ecriture'];
            }
            else{
              if ($value1['cpte_interne_cli'] != $IAR_INFO_temp[0] && $value1['info_ecriture'] != $IAR_INFO_temp[1]){
                unset($IAR_INFO_temp);
                $IAR_INFO_temp[0] = $value1['cpte_interne_cli'];
                $IAR_INFO_temp[1] = $value1['info_ecriture'];
              }
            }
          }
          if ($value1['type_operation'] == 20 && sizeof($IAR_INFO_temp) > 0 && ($value1['cpte_interne_cli'] == $IAR_INFO_temp[0] || $value1['info_ecriture'] == $IAR_INFO_temp[1])){ //Remboursement interet Credit associe a un IAR for setSoldeComptable
            $isOperationIAR = true;
            if ($value1['sens'] == 'c' ) {
              unset($IAR_INFO_temp);
            }
          }
          /****************************************************************************************/

          //FIXME : il faut obliger à passer par les sous-comptes (ex : erreur de paramétrage)
          //FIXME : le montant passé doit avoir été correctement récupéré au préalable par un recupMontant approprié
          $MyError = setSoldeComptable($value1['compte'], $value1['sens'], $value1['montant'], $value1["devise"], $isOperationIAR);
          if ($MyError->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $MyError;
          }

          // Mise à jour compte client interne
          if ($value1['cpte_interne_cli'] != '' && $value1['type_operation'] != 270 && $value1['type_operation'] != 170) {
            if ($value1['type_operation'] == 20 && sizeof($IAR_INFO_temp) > 0 && $value1['cpte_interne_cli'] == $IAR_INFO_temp[0] && $value1['info_ecriture'] == $IAR_INFO_temp[1]){ //Remboursement interet Credit associe a un IAR for setSoldeCpteCli
              $isOperationIAR = true;
            }
            $MyError = setSoldeCpteCli($value1['cpte_interne_cli'], $value1['sens'], $value1['montant'], $value1['devise'], $isOperationIAR);
            if ($MyError->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);

              return $MyError;
            }

            $cpte_interne_cli = $value1['cpte_interne_cli'];
          }

          // Recuperer solde pour le message queue
          if (!is_null($value1['cpte_interne_cli'])) {
              $value1['solde_msq'] = getSoldeCpte($value1['cpte_interne_cli']);
          }

          // Fix montant si NULL ou vide
          $ad_mouvement_montant = recupMontant($value1["montant"]);
          if($ad_mouvement_montant==NULL || $ad_mouvement_montant=='') {
              $ad_mouvement_montant = 0;
          }
          else { // #514: arrondir le montant + #356 arrondies
            // #792 on verifie si IAR/IAP est parametré sinon on fait les arrondissement montant pour tous les operations comptables
            $isMouvementIARIAP = is_Mouvement_IAR_IAP($idecri);
            $getCompteIAP = getCompteIAP();
            if ($isMouvementIARIAP == 2 || $isOperationIAR === true){ //IAR
              if(($value1['type_operation'] != 374) && ($value1['type_operation'] != 20) && ($value1['type_operation'] != 375)){
                $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
              }
            }
            else if ($isMouvementIARIAP == 3 || $getCompteIAP != null || $getCompteIAP != ''){ //IAP
              if(($value1['type_operation'] != 40) &&  ($value1['type_operation'] != 62) && ($value1['type_operation'] != 476)){
                $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
              }
              else{ //si au cas ou le montant de l'operation 40 reprise IAP n'est pas arrondie alors on n'arrondie pas ceux pour les operations 62 et 476 - cas gere pour les anciennes ecritures sinon on fait les arrondissements
                $hasDecimal=hasDecimalMntRepriseIAP($idhis);
                if ($hasDecimal === false){
                  $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
                }
              }
            }
            else{ // si $isMouvementIARIAP == 1
              $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']); //par defaut si IAR/IAP n'est pas parametré
            }
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
          $DATA["date_valeur"] = $value1["date_valeur"];
          $DATA["devise"] = $value1["devise"];
          $DATA["consolide"] = $value1["consolide"];
          $DATA["id_ag"] = $global_id_agence;

          $sql = buildInsertQuery("ad_mouvement",$DATA);
          $result = $db->query($sql);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
        }
      }
    }
  }
  	
  // #357 - verification de l'equilibre comptable
  /**
   * @todo : decomment
   */
  /*
  foreach ($liste_comptes_comptable as $compte_comptable) {
    $MyError = verificationEquilibreComptable($compte_comptable, null, $idhis, $db);
  } */
  
  $dbHandler->closeConnection(true);

  //Frais transactionnel SMS sur ecran
  if($appli == 'main' && $array_comptable != NULL){
    $fraisReduced = preleveFraisTransactionnelSMS($array_comptable, $type_fonction, $id_doss, $source);
  }
  //Fin Frais transactionnel SMS

// MSQ mouvement
	if(isMSQEnabled() && $appli == 'main' && $array_comptable != NULL){
		envoi_sms_mouvement($array_comptable);
	}
// Fin MSQ mouvement

  return new ErrorObj(NO_ERR, $idhis, null, $array_comptable);
}

function ajout_log_systeme($date, $description, $login, $adr_reseau) {

  global $log_path;

  // Ajout dans le fichier log
  $fich = fopen("$log_path/access.log", 'a');
  $text = "[$date] $login@$adr_reseau : $description\n";
  fwrite($fich, $text);
  fclose($fich);

  return true;
}

function insertHistoriqueExterieur($data_ext) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  //On commence par récupérer le numéro de lot
  $sql = "SELECT nextval('ad_his_ext_seq')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }
  $row = $result->fetchrow();
  $id_his_ext = $row[0];

  $data_ext["id"] = $id_his_ext;
  $data_ext["id_ag"] = $global_id_agence;

  $sql = buildInsertQuery("ad_his_ext", $data_ext);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return $id_his_ext;
}

/**
 * Fonction qui ajoute une entrée dans l'historique
 * @author Papa
 * @since 2.7
 * @param array $DATA : tableau contenant les infos de l'historique
 * @return renvoie le id de l'entrée si pas erreur si non le code de l'erreur rencontrée
 */
function insertHistorique($DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération du numéro de l'historique
  $sql = "SELECT nextval('ad_his_id_his_seq')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $id_his = $row[0];

  $DATA['id_his'] = $id_his;
  $DATA['id_ag'] = $global_id_agence;
  $sql = buildInsertQuery("ad_his", $DATA);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return $id_his;
}


/**
 * Renseigne le numéro du reçu généré par ADbanking dans la table ad_his_ext
 * @author Thomas Fastenakel
 * @param int $id_his ID de la transaction (dans ad_his)
 * @param text $ref_recu Numéro de reçu généré
 * @return ErrorObj Objet Erreur
 */
function confirmeGenerationRecu($id_his, $ref_recu) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Recherche s'il existe déjà uen entrée correspondante à id_his dans ad_his_ext
  $sql = "SELECT * FROM ad_his a, ad_his_ext b WHERE a.id_ag=b.id_ag AND a.id_ag=$global_id_agence AND a.id_his = $id_his AND a.id_his_ext = b.id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() == 0) { // Il faut créer une nouvelle entrée
    //On commence par récupérer le numéro de lot
    $sql = "SELECT nextval('ad_his_ext_seq')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }
    $row = $result->fetchrow();
    $id_his_ext = $row[0];

    $DATA = array();
    $DATA["id"] = $id_his_ext;
    $DATA["type_piece"] = 8; // Reçu ADbanking
    $DATA["num_piece"] = $ref_recu;
    $DATA["id_ag"] = $global_id_agence;

    $sql = buildInsertQuery("ad_his_ext", $DATA);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

    // Mettre à jour le lien dans ad_his
    $UPDATE = array("id_his_ext" => $id_his_ext);
    $sql = buildUpdateQuery("ad_his", $UPDATE, array("id_his" => $id_his,'id_ag'=>$global_id_agence));
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

  } else if ($result->numrows() == 1) { // Une entrée existe déjà
    $INFOSEXT = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $UPDATE = array();
    $UPDATE["type_piece"] = 8;
    if ($INFOSEXT["num_piece"] != '')
      $UPDATE["num_piece"] = $INFOSEXT["num_piece"]."/".$ref_recu;
    else
      $UPDATE["num_piece"] = $ref_recu;
    $sql = buildUpdateQuery("ad_his_ext", $UPDATE, array("id" => $INFOSEXT["id"],'id_ag'=>$global_id_agence));
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }
  } else { // Impossible
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Incohérence dans ad_his
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function recherche_historique($DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA = array_make_pgcompatible($DATA);

  $sql = "SELECT * FROM ad_his WHERE id_ag=$global_id_agence AND ";
  if (isset($DATA['date_min'])) $sql .= "(DATE(date) >= DATE('".$DATA['date_min']."')) AND ";
  if (isset($DATA['date_max'])) $sql .= "(DATE(date) <= DATE('".$DATA['date_max']."')) AND ";
  if (isset($DATA['login'])) $sql .= "(login='".$DATA['login']."') AND ";
  if (isset($DATA['type_ope'])) $sql .= "(type_fonction='".$DATA['type_ope']."') AND ";
  $sql = substr($sql, 0, strlen($sql)-5);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) array_push($retour, $row);

  $dbHandler->closeConnection(true);
  return $retour;
}

function getHistoriqueDatas($fields_values=NULL) {
  // PS qui renvoie un array avec toutes les infos concernant l'id_his passé en paramètre
  // IN : $fields_values array contenant les conditions de l'historique
  // OUT: NULL si pas d'id_his correspondant
  //      array() sinon
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
//  $sql = "SELECT * FROM ad_his WHERE id_ag=$global_id_agence AND id_his = $id_his";
//  $result = $db->query($sql);
//  if (DB::isError($result)) {
//    $dbHandler->closeConnection(false);
//    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage(), false
//  }
//  if ($result->numrows() == 1) {
//    $tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC);
//    return $tmprow;
//  } else
//    return NULL;

    //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_his WHERE id_ag=$global_id_agence and ";

  if (isset($fields_values)) {
    foreach ($fields_values as $key => $value)
    $sql .= "$key = '$value' AND ";

  }
  $sql = substr($sql, 0, -4);
  $sql .= "ORDER BY id_his ASC";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $hist = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $hist[$row["id_his"]]=$row;
  return $hist;
}

function calculeSoldeTousComptesComptables($date) {
  // Cete fonction qui renvoie le solde de tous les comptes de type comptable pour une date donne
  // Fonction créée à des fins d'optimisation pour qu'il n'y ait qu'un
  // requête SQL à exécuter lorsqu'on désire connaître les soldes de
  // tous les comptes comptables (extends: pour la balance comptable)
  // IN : Date du jour pù on dsire connaître le solde (solde fin de journée)
  // OUT: array(numéro de compte => Solde)

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  global $global_id_agence;

  // Récupère le solde courant
  $sql = "SELECT num_cpte_comptable, libel_cpte_comptable, solde FROM ad_cpt_comptable WHERE id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage(), false
  }
  $solde = array();
  while ($row = $result->fetchrow()) {
    $solde[$row[0]] = $row[2];
  }

  // Il faut maintenant remonter jusqu'à la date demandée
  $current = date("d/m/Y");

  // Recherche du total des débits pour la journée $curret
  $sql = "SELECT compte, sum(montant) FROM ad_ecriture,ad_mouvement WHERE ad_ecriture.id_ag=ad_mouvement.id_ag AND ad_ecriture.id_ag=$global_id_agence AND ad_ecriture.id_ecriture=ad_mouvement.id_ecriture AND date(date_comptable) >= date('$date')+interval '1 day' AND date(date_comptable) <= '$current' AND sens = 'd' GROUP BY compte";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage(), false
  }
  while ($row = $result->fetchrow()) {
    $solde[$row[0]] += $row[1];
  }

  // Recherche du total des débits pour la journée $curret
  $sql = "SELECT compte, sum(montant) FROM ad_ecriture,ad_mouvement WHERE ad_ecriture.id_ag=ad_mouvement.id_ag AND ad_ecriture.id_ag=$global_id_agence AND ad_ecriture.id_ecriture=ad_mouvement.id_ecriture AND date(date_comptable) >= date('$date')+interval '1 day' AND date(date_comptable) <= '$current' AND sens = 'c' GROUP BY compte";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage(), false
  }
  while ($row = $result->fetchrow()) {
    $solde[$row[0]] -= $row[1];
  }

  return $solde;

}

/**
 * Renvoie true si le client est débiteur càd s'il possède un découvert sur au moins un de ses comptes
 * @param int $id_client ID du client
 * @return bool
 * @author Thomas Fastenakel
 */
function isClientDebiteur($id_client) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  // On prend tous les comtpes à soldes négatifs sauf les comptes de crédit
  $sql .= "SELECT * FROM ad_cpt WHERE id_ag=$global_id_agence AND id_titulaire = $id_client AND solde < 0 AND id_prod != 3";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() > 0)
    return true;
  else
    return false;
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

/**
 * Renvoie true s'il existe au moins un mouvement sur le compte $num_cpte
 * Entre les date $date_deb et $date_fin
 * @author Thomas Fastenakel
 * @param char $num_cpte Numéro du compte
 * @param date $date_deb Date de début de la période
 * @param date $date_fin Date de fin de la période
 * @return bool
 */
function existeMouvement($num_cpte, $date_deb, $date_fin,$consolide=NULL) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  // On prend tous les comptes à soldes négatifs sauf les comptes de crédit
  $sql .= "SELECT count(*) FROM ad_ecriture a, ad_mouvement b WHERE a.id_ag=b.id_ag AND a.id_ag=$global_id_agence AND date_comptable BETWEEN '$date_deb' AND '$date_fin' AND compte = '$num_cpte' AND a.id_ecriture = b.id_ecriture";
  if($consolide){ // si état consolidé
  	$sql .=" AND b.consolide!='t'";
    }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  $row = $result->fetchrow();
  if ($row[0] > 0)
    return true;
  else
    return false;
}

/**
 * Renvoie true s'il existe au moins un mouvement sur le compte $num_cpte ou un des ses sous-compte
 * Entre les date $date_deb et $date_fin
 * @author Ares
 * @param char $num_cpte Numéro du compte
 * @param date $date_deb Date de début de la période
 * @param date $date_fin Date de fin de la période
 * @param text $condSousComptes condition de selection des sous comptes
 * @param bool $consolide si on veux avoir les états consolidés
 * @return bool
 */
function existeMouvementRecursif($num_cpte, $date_deb, $date_fin,$consolide,$condSousComptes) {
  global $dbHandler,$global_id_agence;


  $b_reponse=false;
  $b_reponse =existeMouvement($num_cpte, $date_deb, $date_fin,$consolide);
  if($b_reponse)	return $b_reponse;

  /* Si c'est un compte centralisateur */
  if (isCentralisateur($num_cpte)) {
  	$sous_comptes = array();
    $sous_comptes = getSousComptes($num_cpte,false,$condSousComptes);

    /* Ajouter dans son solde les soldes de ses sous-comptes directs */
    while (list($key,$value)=each($sous_comptes)) {
    	$b_reponse =existeMouvementRecursif($key, $date_deb, $date_fin,$consolide,$condSousComptes);
    	if($b_reponse) return $b_reponse;
    }

  }
  return $b_reponse;
}


/**
 * Fabrique un numéro d'écriture comptable
 * Bloque la lign,e concernée de ad_journal pour éviter des conditions de course
 * @author Thomas Fastenakel
 * @param int $id_jou ID du journal
 * @param int $id_exo ID de l'exercice dans lequel l'écriture est passée
 * @return text Numéro d'écriture
 */
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
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return $ref_ecriture;
}
?>