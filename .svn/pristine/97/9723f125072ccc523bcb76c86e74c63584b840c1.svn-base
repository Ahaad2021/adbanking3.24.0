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

// // Get posted data
$identifiant_client = trim($_REQUEST['identifiant_client']);
$id_agence = trim($_REQUEST['id_agence_source']);
$id_carte = trim($_REQUEST['id_carte']);
$id_compte = trim($_REQUEST['id_compte_source']);
$montant = trim($_REQUEST['montant']);
$libelle = trim($_REQUEST['libelle']);
$id_transaction_mtn = trim($_REQUEST['id_transaction_ext']);

// On charge les variables globales
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/abonnement.php';
require_once 'lib/dbProcedures/carte_atm.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/dbProcedures/transfert.php';
require_once 'lib/misc/access.php';
require_once 'services/misc_api.php';

// Get id_client from identifiant_client
$client_info_atm = getOneOrNullClientATMInfo($identifiant_client, $id_carte, $id_compte);
$id_client = $client_info_atm['id_client'];

// Load ini data for agence
$_REQUEST['m_agc'] = $id_agence;

$params = "identifiant_client: $identifiant_client, id_agence: $id_agence, id_carte: $id_carte, id_compte: $id_compte, id_transaction_ext: $id_transaction_ext, montant: $montant, libelle: $libelle";
$error_msg = "";

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
$fonc_revirement_atm_api = 924;
$type_oper = 192; // Revirement ATM
$type_oper_frais = 190; // Frais revirement ATM // Je ne pense pas qu'il faut appliquer un frais ici

$MyErr = $erreur = null;
$MyErr = new ErrorObj(NO_ERR);
$bloqMontant = false;
$deBloqMontant = false;
$doTransfert = false;
$out = 0;

// if client is found
if (!is_null($client_info_atm)) {
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
    // Handle transfert
    if ($MyErr->errCode === NO_ERR) // No errors, proceed with transfer
    {
        // Récupération du montant réel
        $mnt_reel = recupMontant($montant);
        // Infos compte destination
        $InfoCpte = getAccountDatas($id_compte);
        // Ensuite vérifier qu'on peut déposer sur le compte destination
        $erreur = CheckDepotATM($InfoCpte, $mnt_reel);

        if ($erreur->errCode != NO_ERR)  { // Erreur check depot
            $error_msg = "ERREUR dans CheckDepotATM: " . $params . ", mnt_reel: $mnt_reel";

            $dbHandler->closeConnection(false);
            $return_data = array(
                'success' => false,
                'datas' => array(
                    'msg' => "errCode:" .$erreur->errCode.", ". $error_msg,
                ),
            );
        }
        else { // OK
            // Passage des écritures comptables : débit compte prestataire / crédit client
            $comptable = array();
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();// Do mouvement
            $type_fonction = $fonc_revirement_atm_api; // Revirement ATM

            $cpteSrc = getAccountDatas($id_compte);

            if (isset($client_info_atm) && trim($client_info_atm['compte_comptable']) != NULL) {
                // debit d'un compte ewallet
                $cptes_substitue["cpta"]["debit"] = trim($client_info_atm['compte_comptable']);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au prestataire ATM"));
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
                if($erreur->errCode !== NO_ERR) {
                    $error_msg = "ERREUR dans passageEcrituresComptablesAuto: " . $params . ", mnt_reel: $mnt_reel, type_oper: $type_oper";
                }

                if ($erreur->errCode === NO_ERR) {
                    $erreur = ajout_historique($type_fonction, $cpteSrc["id_titulaire"], 'Revirement ATM', $global_nom_login, date("r"), $comptable);
                    if($erreur->errCode !== NO_ERR) {
                        $error_msg = "ERREUR dans ajout_historique: " . $params . ", type_fonction: $type_fonction, cpteSrc: " . $cpteSrc["id_titulaire"] . ", global_nom_login: $global_nom_login, comptable: " . serialize($comptable);
                    }
                }
            }
            else {
                $erreur = new ErrorObj('ERR_CPTE_NON_PARAM', _("compte comptable associé au prestataire ATM"));
            }

            /********************************************************* Sortie : post traitement transfer ************************/

            if ($erreur->errCode === NO_ERR) {
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
            }
            else {
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
    else { // Some kind of validation errors, send error response
        $dbHandler->closeConnection(false);
        $return_data = array(
            'success' => false,
            'datas' => array(
                'msg' => "errorCode = " .$MyErr->errCode. ", OUT = " .$out,
            ),
        );
    }
} else {
    $return_data = array(
        'success' => false,
        'datas' => array(
          'msg' => 'Active abonnement ATM not found',
        ),
      );
}

echo json_encode($return_data, 1 | 4 | 2 | 8);
exit;