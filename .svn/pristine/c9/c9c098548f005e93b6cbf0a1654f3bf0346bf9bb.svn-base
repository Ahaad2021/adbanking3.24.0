<?php
// Ticket JIRA MB-306
// Give the payment or withdraw script time to complete
sleep(10);

// Get posted data
$identifiant_client = trim($_REQUEST['identifiant_client']);
$id_agence_source = trim($_REQUEST['id_agence']);
$id_compte_source = trim($_REQUEST['id_compte']);
$montant = trim($_REQUEST['montant']);
$libelle = trim($_REQUEST['libelle']);
$id_transaction_mtn = trim($_REQUEST['id_transaction_mtn']);
$code_prestataire = trim($_REQUEST['code_prestataire']);
$type_operation = trim($_REQUEST['type_operation']);
$sens = trim($_REQUEST['sens']);

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

// Get id_client from identifiant_client
$client_info_abn = getOneOrNullClientAbonnementInfo($identifiant_client);
$id_client = $client_info_abn['id_client'];

// Load ini data for agence
$_REQUEST['m_agc'] = $id_agence_source;

$params = "identifiant_client: $identifiant_client, id_client : $id_client, id_agence_source: $id_agence_source, id_compte_source: $id_compte_source, montant: $montant, libelle: $libelle";
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

    $MyErr = $erreur = null;
    $bloqMontant = false;
    $deBloqMontant = false;
    $doTransfert = false;
    $out = 0;

// Check if clients from same agence
if ($id_agence_source == null || $id_agence_source == '') {
    $MyErr = new ErrorObj('ERR_CPTE_AUTRE_AGC');
    $out = 1;
} elseif ($montant <= 0) {
    $MyErr = new ErrorObj('ERR_MONTANT');
    $out = 1;
} else {
        $MyErr = new ErrorObj(NO_ERR);
}

// Handle verification
if ($MyErr->errCode === NO_ERR) {
    if ($type_action == 1){

        $erreur = checkBloqMontantCpte($id_compte_source, $montant, $id_agence_source);

        if ($erreur->errCode === NO_ERR) {
            $cpteSrc = getAccountDatas($id_compte_source);

            $return_data = array(
                'success' => true,
                'datas' => array(
                    'msg' => sprintf("Le montant %s %s a été bloqué sur le compte : %s ", $montant, $cpteSrc['devise'], $cpteSrc['num_complet_cpte']),
                    'id_his' => sprintf("%09d", $erreur->param),
                ),
            );
        } else {
            $return_data = array(
                'success' => false,
                'datas' => array(
                    'msg' => "Aucun blocage",
                ),
            );
        }

    } elseif ($type_action == 2){
        // Récupération du montant réel
        $mnt_reel = recupMontant($montant);

        // Verifier si mouvement a eu lieu
        $log = checkTransactionEwallet($type_operation, $id_transaction_mtn, $sens, $mnt_reel, $id_compte_source, $id_agence_source);

        if (is_null($log)){

            // Verifier si prestataire a un compte comptable associe
            $prestataire_info = getPrestataireInfo($code_prestataire);
            if (isset($prestataire_info['compte_comptable']) && !empty($prestataire_info['compte_comptable']) ) {
                $return_data = array(
                    'success' => false,
                    'datas' => array(
                        'msg' => 'Transaction non effectuer',
                    ),
                );
            } else {
                $return_data = array(
                    'success' => false,
                    'datas' => array(
                        'msg' => 'ERR_CPTE_NON_PARAM : aucun compte comptable associe au prestataire eWallet',
                    ),
                );
            }
        } else {
            //recuperation solde compte
            $accountDatas = getAccountDatas($id_compte);
            $solde = $accountDatas['solde'];
            $solde = arrondiMonnaiePrecision($solde);

            $return_data = array(
                'success' => true,
                'datas' => array(
                    'msg' => "N° de transaction : " . sprintf("%09d", $log['id_his']),
                    'id_his' => sprintf("%09d", $log['id_his']),
                    'solde' => $solde,
                ),
            );
        }
    }


} else {
    // Some kind of validation errors, send error response
    $return_data = array(
        'success' => false,
        'datas' => array(
            'msg' => "errorCode = " .$MyErr->errCode. ", OUT = " .$out,
        ),
    );
}

echo json_encode($return_data, 1 | 4 | 2 | 8);
exit;