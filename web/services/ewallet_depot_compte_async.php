<?php

// Ticket JIRA MB-224 a été intégré sur ce fichier

$return_data = array(
    'success' => false,
    'datas' => array(),
);

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

// Permet d'afficher les dates/heures en langue française
//setlocale(LC_ALL, "fr_BE");

// Get posted data
/*
$identifiant_client = trim($_REQUEST['identifiant_client']);
$id_agence = trim($_REQUEST['id_agence']);
$id_compte = trim($_REQUEST['id_compte']);
$montant = trim($_REQUEST['montant']);
$libelle = trim($_REQUEST['libelle']);
$id_transaction_mtn = trim($_REQUEST['id_transaction_mtn']);
$code_prestataire = trim($_REQUEST['code_prestataire']);
$statut_demande = trim($_REQUEST['statut_demande']);*/
$identifiant_client = 2266;
$id_agence = 1;
$id_compte = 5102;
$montant = 175;
$libelle = "depot ";
$id_transaction_mtn = 12345;
$code_prestataire = 'MTN_RW';
$statut_demande = 3;

// Strip client id from identifiant and set global_id_client
$id_client = intval(substr($identifiant_client, -8));
//$id_agence = strstr($identifiant_client, substr($identifiant_client, -8), true);

// Load ini data for agence
$_REQUEST['m_agc'] = $id_agence;

$params = "identifiant_client: $identifiant_client, id_agence: $id_agence, id_compte: $id_compte, type_action: $type_action, montant: $montant, libelle: $libelle";
$error_msg = "";
// On charge les variables globales
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/abonnement.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/dbProcedures/transfert.php';
require_once 'lib/misc/access.php';
require_once 'services/misc_api.php';

require_once 'lib/dbProcedures/main_func.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/misc/divers.php';
include_once 'lib/misc/debug.php';

$valeurs = getCustomLoginInfo();

$global_agence = $valeurs['libel_ag'];
$global_id_agence = $valeurs['id_ag'];
$global_nom_login = $valeurs['login'];
$global_monnaie = $valeurs['monnaie'];
$global_monnaie_prec = $valeurs['monnaie_prec'];
$global_monnaie_courante_prec = $valeurs['monnaie_prec'];
$global_monnaie_courante = $valeurs['monnaie'];
$global_remote_monnaie = $valeurs['monnaie'];
$global_remote_monnaie_courante = $valeurs['monnaie'];
$global_multidevise = $valeurs['multidevise'];
$global_last_axs = time();
$global_institution = $valeurs['institution'];
$global_type_structure = $valeurs['type_structure'];
$global_id_exo = $valeurs['exercice'];
$global_langue_systeme_dft = $valeurs['langue_systeme_dft'];
$global_langue_utilisateur = 'fr_BE'; //$valeurs['langue'];
$global_id_guichet = 1; // To create

require_once 'lib/dbProcedures/main_func.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/misc/divers.php';
include_once 'lib/misc/debug.php';

$appli = "main"; // On est dans l'application (et pas dans le batch)

// Strip client id from identifiant and set global_id_client
$global_id_client = $id_client;

// Fonctions systèmes ~ Depot eWallet
$fonc_transfert_api = 98;
$type_oper = 117; // Dépôt eWallet
$type_oper_frais = 184;

$MyErr = $erreur = null;
$bloqMontant = false;
$deBloqMontant = false;
$doTransfert = false;
$out = 0;

/************************ 1. Do some checks *********************************************/
// Check if clients from same agence
if ($id_agence == null || $id_agence == '') {
    $MyErr = new ErrorObj('ERR_CPTE_AUTRE_AGC');
    $out = 1;
}
elseif ($montant <= 0) {
    $MyErr = new ErrorObj('ERR_MONTANT');
    $out = 1;
}
else
{
    // Check cpte source if exist for client source
    $cpte_src_arr = get_comptes_epargne($global_id_client);

    // accounts were found
    if (is_array($cpte_src_arr) && count($cpte_src_arr) > 0){
        if ($cpte_src_arr[$id_compte]) { // the client account was returned
            if ($cpte_src_arr[$id_compte]['id_titulaire'] != $global_id_client) {
                $MyErr = new ErrorObj('ERR_CPTE_SRC_INEXISTANT');
                $out = 4;
            }
        } else {
            $MyErr = new ErrorObj(NO_ERR);
        }
    } else {
        $MyErr = new ErrorObj('ERR_CPTE_SRC_INEXISTANT');
        $out = 2;
    }
}
/************************ 2. Verify check results and process *********************************************/

If ($statut_demande == 1){

    if ($MyErr->errCode == NO_ERR) // No errors, proceed with transfer
    {
        $cpte_client = getCompteCptaProdEp($id_compte);
        $exist_brouillard = isEcritureAttenteMTN($cpte_client,$id_compte,$id_transaction_mtn);
        if ($exist_brouillard == false) {

            $idecr = 1;
            $AG = getAgenceDatas($global_id_agence);
            $info_debit = "Debit compte prestataire";
            $info_credit = "Credit compte du client";
            $prestataire_info = getPrestataireInfo($code_prestataire);
            if (!isset($prestataire_info) && trim($prestataire_info['compte_comptable']) == NULL) {
                $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au prestataire eWallet"));
            }


            // Credit d'un compte client
            $cpte_client = getCompteCptaProdEp($id_compte);
            if ($cpte_client == NULL) {
                $dbHandler->closeConnection(false);
                $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au produit d'épargne"));
            }

            // Array pour le compte Debiteur
            $DATA[1]['id'] = $idecr;
            $DATA[1]['compte'] = trim($prestataire_info['compte_comptable']);
            $DATA[1]['cpte_interne_cli'] = null;
            $DATA[1]['devise'] = $global_monnaie;

            $DATA[1]['sens'] = 'd';
            $mnt_debit = arrondiMonnaiePrecision($montant, $global_monnaie);
            $DATA[1]['montant'] = $mnt_debit;


            $DATA[1]['date_comptable'] = date('yy-m-d');
            $DATA[1]['libel_ecriture'] = $info_debit;
            $DATA[1]['type_operation'] = $type_oper;
            $DATA[1]['id_jou'] = 1;
            $DATA[1]['id_exo'] = $AG["exercice"];
            $DATA[1]['id_taxe'] = null;

            // Array pour le compte crediteur : le client
            $DATA[2]['id'] = $idecr;
            $DATA[2]['compte'] = trim($cpte_client);
            $DATA[2]['cpte_interne_cli'] = $id_compte;
            $DATA[2]['devise'] = $global_monnaie;

            $DATA[2]['sens'] = 'c';
            $mnt_credit = arrondiMonnaiePrecision($montant, $global_monnaie);
            $DATA[2]['montant'] = $mnt_credit;


            $DATA[2]['date_comptable'] = date('yy-m-d');
            $DATA[2]['libel_ecriture'] = $info_credit;
            $DATA[2]['type_operation'] = $type_oper;
            $DATA[2]['id_jou'] = 1;
            $DATA[2]['id_exo'] = $AG["exercice"];
            $DATA[2]['id_taxe'] = null;
            $DATA[2]['infos_brouillard'] = $id_transaction_mtn;

            $myErr = passageEcrituresBrouillardEwallet($DATA);
            if ($myErr->errCode === NO_ERR){
                $return_data = array(
                    'success' => true,
                    'datas' => array(
                        'msg' => "N° de transaction : " . sprintf("%09d", $id_transaction_mtn),
                        'montant' => $mnt_credit,
                        'id_his' => $myErr->param,
                    ),
                );
            }
        }
        else{
            $dbHandler->closeConnection(false);
            $out = 3;
            $error_brouillard_exist = new ErrorObj(ERR_ENTRY_EXIST_AD_BROUILLARD,'La transaction existe deja!');
            $return_data = array(
                'success' => false,
                'datas' => array(
                    'msg' => "errorCode = " .$error_brouillard_exist->errCode. ", errorMsg = ".$error_brouillard_exist->param."  OUT = " .$out,
                ),
            );
        }


    }
    echo json_encode($return_data, 1 | 4 | 2 | 8);

}
else if($statut_demande == 2){
    //Verifier si la demande est presente dans la table ad_brouillard
    $cpte_client = getCompteCptaProdEp($id_compte);
    $date_now = date('yy-m-d');
    $exist_brouillard = isEcritureAttenteMTN($cpte_client,$id_compte,$id_transaction_mtn);
    $id_his_brouillard = getDataBrouillard($cpte_client,$type_oper,$id_transaction_mtn);

    if ($MyErr->errCode == NO_ERR) // No errors, proceed with transfer
    {
        if ($exist_brouillard == true) {

            // Récupération du montant réel
            $mnt_reel = recupMontant($montant);
            // Infos compte destination
            $InfoCpte = getAccountDatas($id_compte);
            // Ensuite vérifier qu'on peut déposer sur le compte destination
            $erreur = CheckDepotEwallet($InfoCpte, $mnt_reel);

            if ($erreur->errCode != NO_ERR) { // Erreur check depot
                $error_msg = "ERREUR dans CheckDepotEwallet: " . $params . ", mnt_reel: $mnt_reel";

                $dbHandler->closeConnection(false);
                $return_data = array(
                    'success' => false,
                    'datas' => array(
                        'msg' => "errCode:" . $erreur->errCode . ", " . $error_msg,
                    ),
                );
            } else { // OK
                // Passage des écritures comptables : débit compte prestataire / crédit client
                $comptable = array();
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();// Do mouvement
                $type_fonction = $fonc_transfert_api; // Transfert eWallet

                // Get prestatiare eWallet
                $prestataire_info = getPrestataireInfo($code_prestataire);
                $cpteSrc = getAccountDatas($id_compte);

                if (isset($prestataire_info) && trim($prestataire_info['compte_comptable']) != NULL) {
                    // debit d'un compte ewallet
                    $cptes_substitue["cpta"]["debit"] = trim($prestataire_info['compte_comptable']);
                    if ($cptes_substitue["cpta"]["debit"] == NULL) {
                        $dbHandler->closeConnection(false);
                        $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au prestataire eWallet"));
                    }

                    // Credit d'un compte client
                    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_compte);
                    if ($cptes_substitue["cpta"]["credit"] == NULL) {
                        $dbHandler->closeConnection(false);
                        $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au produit d'épargne"));
                    }
                    $cptes_substitue["int"]["credit"] = $id_compte; // compte intermediaire

                    // Le mouvement / realisation transfert
                    $erreur = passageEcrituresComptablesAuto($type_oper, $mnt_reel, $comptable, $cptes_substitue, $cpteSrc['devise'], NULL, $id_transaction_mtn);
                    if ($erreur->errCode !== NO_ERR) {
                        $error_msg = "ERREUR dans passageEcrituresComptablesAuto: " . $params . ", mnt_reel: $mnt_reel, type_oper: $type_oper";
                    }

                    if ($erreur->errCode === NO_ERR) {
                        $erreur = ajout_historique($type_fonction, $cpteSrc["id_titulaire"], 'Dépôt eWallet', $global_nom_login, date("r"), $comptable);
                        if ($erreur->errCode !== NO_ERR) {
                            $error_msg = "ERREUR dans ajout_historique: " . $params . ", type_fonction: $type_fonction, cpteSrc: " . $cpteSrc["id_titulaire"] . ", global_nom_login: $global_nom_login, comptable: " . serialize($comptable);
                        }
                    }
                } else {
                    $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au prestataire eWallet"));
                }

                /********************************************************* Sortie : post traitement transfer ************************/

                if ($erreur->errCode === NO_ERR) {
                    // Prélève frais transfert E-wallet
                    $err = preleveFraisAbonnement('SMS_EWT', $global_id_client, $type_oper_frais, $mnt_reel, $type_fonction);

                    if ($err->errCode !== NO_ERR) {
                        $dbHandler->closeConnection(false);
                        $return_data = array(
                            'success' => false,
                            'datas' => array(
                                'msg' => 'ERROR in preleveFraisAbonnement:' . $err->param,
                            ),
                        );
                    } else {
                        //recuperation solde compte
                        $accountDatas = getAccountDatas($id_compte);
                        $solde = $accountDatas['solde'];
                        $solde = arrondiMonnaiePrecision($solde);

                        $return_data = array(
                            'success' => true,
                            'datas' => array(
                                'msg' => "N° de transaction : " . sprintf("%09d", $erreur->param),
                                'id_his' => sprintf("%09d", $erreur->param),
                                'solde' => $solde,
                            ),
                        );
                        supEcritureBrouillard($id_his_brouillard);
                    }

                } else {
                    $dbHandler->closeConnection(false);
                    $return_data = array(
                        'success' => false,
                        'datas' => array(
                            'msg' => $error_msg . ", errCode:" . $erreur->errCode,
                            'param' => $erreur->param,
                        ),
                    );
                }
            }
        }
        else{ //return that no entry in ad_brouillard
            $dbHandler->closeConnection(false);
            $out = 5;
            $error_brouillard = new ErrorObj(ERR_NO_ENTRY_AD_BROUILLARD,'Pas de demande présent dans le brouillard');
            $return_data = array(
                'success' => false,
                'datas' => array(
                'msg' => "errorCode = " .$error_brouillard->errCode. ", errorMsg = ".$error_brouillard->param."  OUT = " .$out,
                ),
            );

        }
    }
    else { // Some kind of validation errors, send error response
        $dbHandler->closeConnection(false);
        $return_data = array(
            'success' => false,
            'datas' => array(
                'msg' => "errorCode = " .$MyErr->errCode. ", OUT = " .$out,
            ),
        );
    }

    echo json_encode($return_data, 1 | 4 | 2 | 8);
}
else if ($statut_demande == 3){
    $cpte_client = getCompteCptaProdEp($id_compte);
    $id_his_brouillard = getDataBrouillard($cpte_client,$type_oper,$id_transaction_mtn);

    if (sizeof($id_his_brouillard) > 0){
        supEcritureBrouillard($id_his_brouillard);
        //trace dans ad_his
        $myerror = ajout_historique (52, $id_client, 'Annulation ecriture Brouillard ', $global_nom_login, date("r"), NULL);
        $return_data = array(
            'success' => true,
            'datas' => array(
                'id_his' => sprintf("%09d", $myerror->param),
                'msg' => "Mouvement en attente supprimé avec succès",
            ),
        );

    }
    else {
        $out = 5;
        $error_brouillard = new ErrorObj(ERR_NO_ENTRY_AD_BROUILLARD,'Pas de demande présent dans le brouillard');
        $return_data = array(
            'success' => false,
            'datas' => array(
                'msg' => "errorCode = " .$error_brouillard->errCode. ", errorMsg = ".$error_brouillard->param."  OUT = " .$out,
            ),
        );
    }
    echo json_encode($return_data, 1 | 4 | 2 | 8);
}

?>