<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * @package Systeme
 */

require_once 'lib/misc/divers.php';
require_once 'services/misc_api.php';
include_once 'lib/misc/debug.php';

/**
 * Récupère une liste des tarification
 * 
 * @return array un tableau contenant la clé et le libellé de la tarification
 */
function getListeTarification($all_fields=false, $is_liste=false) {

  global $dbHandler, $global_monnaie;

  $db = $dbHandler->openConnection();
  //$sql = "SELECT * FROM adsys_tarification ORDER BY id_tarification ASC";
  $sql = "SELECT * FROM adsys_tarification ORDER BY code_abonnement DESC";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {

    if($all_fields) {
        if(empty($row['valeur_min'])) $row['valeur_min'] = 0;
        if(empty($row['valeur_max'])) $row['valeur_max'] = 0;
        $DATAS[$row['type_de_frais']] = $row;
    } elseif ($is_liste) {
        $type_frais_arr = array(
                    'SMS_REG' => 'Frais d\'activation du service SMS',
                    'SMS_MTH' => 'Frais forfaitaires mensuels SMS',
                    'SMS_TRC' => 'Frais transfert de compte à compte',
                    'SMS_EWT' => 'Frais transfert vers E-wallet',
                    'SMS_EWT_ADB' => 'Frais transfert E-wallet vers ADBanking',
                    'SMS_FRAIS' => 'Frais forfaitaires transactionnel SMS',
                    'ESTAT_REG' => 'Frais d\'activation du service eStatement',
                    'ESTAT_MTH' => 'Frais forfaitaires mensuels eStatement',
                    'ATM_REG' => 'Frais d\'activation du service ATM',
                    'ATM_MTH' => 'Frais forfaitaires mensuels ATM',
                    'ATM_TSC' => 'Frais transaction ATM',
                    'ATM_USG' => 'Frais à l\'usage du service ATM',
                    'CRED_FRAIS' => 'Frais de dossier de crédit',
                    'CRED_COMMISSION' => 'Perception commissions de déboursement',
                    'CRED_ASSURANCE' => 'Transfert des assurances',
                    'EPG_RET_ESPECES' => 'Frais Retrait en espèces',
                    'EPG_RET_CHEQUE_INTERNE' => 'Frais Retrait cash par chèque interne',
                    'EPG_RET_CHEQUE_TRAVELERS' => 'Frais Retrait travelers cheque',
                    'EPG_RET_CHEQUE_INTERNE_CERTIFIE' => 'Frais Retrait chèque interne certifié'
        );
        $code_abn = explode('_', $row['type_de_frais']);

        if($row['code_abonnement'] != 'credit') {
            $DATAS[$row["id_tarification"]] = sprintf('%s - %s (%s %s)', $code_abn[0], $type_frais_arr[$row['type_de_frais']], $row['valeur'], (($row['mode_frais']==2)?'%':$global_monnaie));
        }
        else { // credit
            $DATAS[$row["id_tarification"]] = sprintf('%s - %s', $code_abn[0], $type_frais_arr[$row['type_de_frais']], $row['valeur']);
        }
    } else {
        $DATAS[$row["id_tarification"]] = $row['type_de_frais'];
    }
  }
  return $DATAS;
}

/**
 * Récupère les infos d'une tarification
 * 
 * @return array un tableau associatif avec les infos sur la tarification
 */
function getTarificationDatas($typeFrais) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM adsys_tarification WHERE id_ag=".$global_id_agence." AND statut='t' AND type_de_frais = '".$typeFrais."' AND ((date(date_debut_validite) IS NULL AND date(date_fin_validite) IS NULL) OR (date(date_debut_validite) <= date(NOW()) AND date(date_fin_validite) IS NULL) OR (date(date_debut_validite) IS NULL AND date(date_fin_validite) >= date(NOW())) OR (date(date_debut_validite) <= date(NOW()) AND date(date_fin_validite) >= date(NOW()))) ORDER BY date_creation DESC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__.' '.$sql);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) {
    return NULL;
  }

  $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return $datas;
}

function preleveFraisAbonnement($type_frais, $id_client, $type_oper = 180, $montant_transaction = 0, $type_fonction = null, $id_doss = null, $source = 2) {
    global $dbHandler, $global_nom_login, $global_id_client, $global_id_agence, $global_monnaie;

    $comptable = array();
    if(is_null($type_fonction)) $type_fonction = 12;

    $type_frais_arr = array('SMS_REG' => 'Frais d\'activation du service', 'SMS_MTH' => 'Frais forfaitaires mensuels', 'SMS_TRC' => 'Frais transfert de compte à compte', 'SMS_EWT' => 'Frais transfert vers E-wallet', 'ESTAT_REG' => 'Frais d\'activation du service eStatement', 'ESTAT_MTH' => 'Frais forfaitaires mensuels eStatement');

    $myErr = new ErrorObj(NO_ERR);

    $tarif = getTarificationDatas($type_frais);

    // Prélèvement des frais d'abonnement
    if (is_array($tarif) && count($tarif) > 0) {

        //$compteCptaProdFrais = $tarif['compte_comptable'];
        $mode_frais = $tarif['mode_frais'];

        if ($mode_frais == 2 && $montant_transaction > 0) {
            $percentage = $tarif['valeur'];

            if ($percentage > 100) {
                $percentage = 100;
            } elseif ($percentage <= 0) {
                $percentage = 1;
            }

            $montant = ($montant_transaction * ($percentage / 100));
        } else {
            $montant = $tarif['valeur'];
        }
        
        if ($montant > 0) {

            // Get client compte de base
            $id_cpte_source = ($source == 3)?getRembAccountID($id_client, $id_doss):getBaseAccountID($id_client);

            // Aucun compte de base n'est associé à ce client
            if ($id_cpte_source == NULL) {
                //signalErreur(__FILE__, __LINE__, __FUNCTION__);        
                $myErr = new ErrorObj(ERR_CPTE_INEXISTANT);
            }

            // Get compte comptable info
            //$InfoCpteCompta = getComptesComptables(array("num_cpte_comptable" => $compteCptaProdFrais));

            // Le compte comptable n'existe pas
            /*
            if (!is_array($InfoCpteCompta[$compteCptaProdFrais]) || (is_array($InfoCpteCompta[$compteCptaProdFrais]) && count($InfoCpteCompta[$compteCptaProdFrais])==0)) {
                //signalErreur(__FILE__, __LINE__, __FUNCTION__);
                $myErr = new ErrorObj(ERR_CPTE_NON_PARAM, $type_frais_arr[$tarif['type_de_frais']]);
            }
            */

            // Infos compte source
            $InfoCpteSource = getAccountDatas($id_cpte_source);

            // Le compte source n'existe pas
            if (!is_array($InfoCpteSource) || (is_array($InfoCpteSource) && count($InfoCpteSource)==0)) {            
                $myErr = new ErrorObj(ERR_CPTE_CLI_NEG);
            }

            $soldeDispo = getSoldeDisponible($id_cpte_source);

            if ($soldeDispo < $montant) {
                if ($type_frais == 'SMS_MTH' || $type_frais == 'ESTAT_MTH' || $type_frais == 'SMS_FRAIS') {
                    // Mise en attente : Prélèvement des frais d'abonnement
                    $date = DateTime::createFromFormat('U.u', microtime(true));
                    $sql = "INSERT INTO ad_frais_attente (id_cpte,id_ag, date_frais, type_frais, montant) VALUES (".$id_cpte_source.", ".$global_id_agence.", '".$date->format('Y-m-d H:i:s.u')."', ".$type_oper.", ".$montant.")";;
                    $result = executeDirectQuery($sql);
                    
                    if ($result->errCode != NO_ERR) {
                        $myErr = new ErrorObj($result->errCode);
                    }
                } else {
                    $myErr = new ErrorObj(ERR_CPTE_CLI_NEG);
                }
            } else {
                // Passage des écritures comptables : débit client / crédit client
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();

                // Débit d'un compte client
                $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                  $dbHandler->closeConnection(false);
                  $myErr = new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $cptes_substitue["int"]["debit"] = $id_cpte_source;

                // Crédit d'un compte produit
                /*
                $cptes_substitue["cpta"]["credit"] = $compteCptaProdFrais;
                if ($cptes_substitue["cpta"]["credit"] == NULL) {
                  $dbHandler->closeConnection(false);
                  $myErr = new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à l'abonnement"));
                }

                if ($InfoCpteSource['devise'] != $InfoCpteCompta[$compteCptaProdFrais]['devise']) {                
                    $myErr = new ErrorObj(ERR_DEVISE_CPT_DIFF);
                } else {
                    $myErr = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise'], NULL, $id_cpte_source);
                }
                */

                $myErr = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise'], NULL, $id_cpte_source);
            }

            if ($myErr->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $myErr;
            } else {
                $myErr = ajout_historique($type_fonction, $InfoCpteSource["id_titulaire"], '', $global_nom_login, date("r"), $comptable);
                if ($myErr->errCode != NO_ERR) {
                  $dbHandler->closeConnection(false);
                  return $myErr;
                }
            }
        }
    }
    /*else {
        if(function_exists('print_rn')) {
            print_rn($type_frais);echo $id_client;die;
        } else {
            var_dump($type_frais);echo $id_client;die;
        }
        
        //echo json_encode($type_frais, 1 | 4 | 2 | 8);
        //exit;
        //signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }*/    
    
//    $dbHandler->closeConnection(true);
    return $myErr;
}

/**
 * @param $typeFrais (ex. CRED_FRAIS)
 * @param $pourcentage
 * @param $base_calcul
 * @return mixed
 */
function getfraisTarification($typeFrais, $pourcentage, $base_calcul, $mnt_fixe = 0)
{
    $listeFrais = getTarificationDatas($typeFrais);

    if(is_null($pourcentage)) $pourcentage = 0; // init
    if(is_null($base_calcul)) $base_calcul = 0; // init
    $frais_calcule = $pourcentage * $base_calcul;

    if(!is_null($listeFrais)) {
        if($frais_calcule <  $listeFrais['valeur_min']) $frais_calcule =  $listeFrais['valeur_min'];
        if($frais_calcule >  $listeFrais['valeur_max']) $frais_calcule =  $listeFrais['valeur_max'];
    }

    return ($frais_calcule + $mnt_fixe);
}

/**
 * Check if array_comptable contains transaction which concerns a client which has subscribed to Mobile Banking
 * Deduct frais sms transactionnel from base account
 * @param $array_comptable
 */
function preleveFraisTransactionnelSMS(&$array_comptable, $type_function, $id_doss = NULL, $source = 2)
{
  global $adsys;
  global $global_langue_rapport;

  $type_frais = 'SMS_FRAIS';
  $type_operation = 188;
  $montant_transaction = 0;
  $arr_type_operation = array();

  $type_opt = getListeTypeOptPourPreleveFraisSMS(true);
  foreach ($type_opt as $key => $value) {
      foreach ($value as $opt => $valeur) {
          $arr_type_operation[] = $valeur;
      }
  }

  $getFrais = getTarificationDatas($type_frais);
  if(empty($getFrais)){
    return true;
  }

  if(!empty($array_comptable)){
    foreach ($array_comptable as $k => $val){
      if (isset($val['cpte_interne_cli'])){
        if (in_array($val["type_operation"], $arr_type_operation) && is_array($client = checkClientAbonnement($val['cpte_interne_cli'])) && $cpt_epargne = get_comptes_epargne($client["id_client"]) ){
          foreach ($cpt_epargne as $id_cpte => $values){
            if ($val['cpte_interne_cli'] == $id_cpte) {

              $myErr = checkTypeOperationFraisSMSsensCredit($type_operation);
              if($myErr->errCode != NO_ERR){
                return false;
              }

              preleveFraisAbonnement($type_frais, $client["id_client"], $type_operation, $montant_transaction,  $type_function, $id_doss, $source);

              //No recu will be generated as discussed with Olivier.
              /*$InfoCpte = getAccountDatas  ($val["cpte_interne_cli"]);//id_cpte as parameter
              $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
              $infos = get_compte_epargne_info($val["cpte_interne_cli"]);//id_cpte as parameter

              print_recu_frais_transactionnel_SMS($client["id_client"], ++$id_his, $mnt, $global_langue_rapport, $InfoProduit, $infos);*/
            }
          }
        }
      }
    }
  }


  return true;
}