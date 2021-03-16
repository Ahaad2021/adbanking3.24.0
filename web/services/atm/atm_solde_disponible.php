<?php

$return_data = array(
    'success' => false,
    'datas' => array(),
);

// //Get posted data
$identifiant_client = trim($_REQUEST['identifiant_client']);
$id_agence = trim($_REQUEST['id_agence_source']);
$id_carte = trim($_REQUEST['id_carte']);
$id_compte_source = trim($_REQUEST['id_compte_source']);
$libelle = trim($_REQUEST['libelle']);
$id_transaction_ext = trim($_REQUEST['id_transaction_ext']);

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

// verifier si abonnement et carte atm sont actifs
$client_info_atm = getOneOrNullClientATMInfo($identifiant_client, $id_carte, $id_compte_source);
$id_client = $client_info_atm['id_client'];

// Load ini data for agence
$_REQUEST['m_agc'] = $id_agence;

$params = "identifiant_client: $identifiant_client, id_agence: $id_agence, id_carte: $id_carte, id_compte: $id_compte, id_transaction_ext: $id_transaction_ext, code_prestataire: $code_prestataire, libelle: $libelle";
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

// fonctions systèmes ~ Solde disponible
$type_oper_frais = 190;
$fonc_solde_dispo_api = 921; // 
$mnt_fictif = 100;

// Strip client id from identifiant and set global_id_client
$global_id_client = $id_client;

// if client is found
if (!is_null($client_info_atm)) {
    // Prélève frais solde disponible
    $err = preleveFraisAbonnement('ATM_TSC', $global_id_client, $type_oper_frais, $mnt_fictif, $fonc_solde_dispo_api);

    if ($err->errCode !== NO_ERR) {

        $dbHandler->closeConnection(false);

        $return_data = array(
          'success' => false,
          'datas' => array(
            'msg' => 'ERROR in preleveFraisAbonnement:' . $err->param,
          ),
        );
    } else {
        $erreur = ajout_historique($fonc_solde_dispo_api, $id_client, 'Solde disponible', $global_nom_login, date("r"));
        if ($erreur->errCode !== NO_ERR) {
          $error_msg = "ERREUR dans ajout_historique: " . $params . ", type_fonction: $type_fonction, id_client: " . $id_client . ", global_nom_login: $global_nom_login ";
        }

        //recuperation solde compte
        $accountDatas = getAccountDatas($id_compte_source);
        $solde = $accountDatas['solde'];
        $solde = arrondiMonnaiePrecision($solde);

        $solde_dispo = getSoldeDisponible($id_compte_source);

        $return_data = array(
            'success' => true,
            'datas' => array(
              'solde' => $solde,  
              'solde_disponible' => $solde_dispo,
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