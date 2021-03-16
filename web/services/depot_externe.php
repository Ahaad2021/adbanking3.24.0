<?php

$return_data = array(
    'success' => false,
    'datas' => array(),
);

$libelleAgence = trim($_REQUEST['libelle_institution_source']);
$codeAgence = trim($_REQUEST['code_institution_source']);
$numeroSource = trim($_REQUEST['numero_source']);

$num_compte_cible = trim($_REQUEST['num_compte_cible']);
$id_agence_source = trim($_REQUEST['id_ag']);
$id_pays_tireur_benef = trim($_REQUEST['id_pays']); //RWANDA => 1
$montant = trim($_REQUEST['montant']);
$id_transaction_rswitch = trim($_REQUEST['id_transaction_rswitch']);

// Load ini data for agence
$_REQUEST['m_agc'] = $id_agence_source;



// On charge les variables globales
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/misc/access.php';
require_once 'services/misc_api.php';

$global_id_agence = $id_agence_source;

require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';

$global_nom_login = 'api';

$global_id_guichet = 1; // To create


$valeurs = getCustomLoginInfo();

$global_monnaie = $valeurs['monnaie'];
$global_monnaie_prec = $valeurs['monnaie_prec'];
$global_monnaie_courante_prec = $valeurs['monnaie_prec'];
$global_id_exo = $valeurs['exercice'];

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/compte.php';
include_once 'lib/misc/debug.php';

$appli = "main"; // On est dans l'application (et pas dans le batch)

$MyErr = null;

function isEmpty($value)
{
  return $value == null || $value == '';
}

if(isEmpty($libelleAgence) || isEmpty($codeAgence) || isEmpty($numeroSource) ||
   isEmpty($num_compte_cible) || isEmpty($id_agence_source) ||
   isEmpty($id_pays_tireur_benef) || isEmpty($montant) || isEmpty($id_transaction_rswitch))
{
    $MyErr = new ErrorObj("ERR_PARAMETRE_MANQUANT: [$libelleAgence - $codeAgence - $numeroSource - $num_compte_cible - $id_agence_source - $id_pays_tireur_benef - $montant - $id_transaction_rswitch]");
}
else if (!is_numeric($montant) || $montant <= 0) {
    $MyErr = new ErrorObj('ERR_MONTANT');
}
else
{
    $id_cpte_cible = get_id_compte($num_compte_cible);
    if ($id_cpte_cible == NULL) {
        $MyErr = new ErrorObj('ERR_NUM_COMPLET_CPTE_DEST_NOT_EXIST');
    } else {
        $InfoCpte = getAccountDatas($id_cpte_cible);
        $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

        if ($InfoCpte != NULL) {
            $MyErr = new ErrorObj(NO_ERR);
        } else {
            $MyErr = new ErrorObj('ERR_CPTE_DEST_INEXISTANT');
        }
    }

    $correspondant = getCorrespondantByLibelleBanque($libelleAgence);
    if ($correspondant == NULL) {
        $MyErr = new ErrorObj('CORRESPONDANT_NOT_EXIST');
    }
}


// Output error
if ($MyErr->errCode === NO_ERR) {
    //Recuperation/Creation personne externe
    $personneExterne = getPersonneExt(array('denomination'=> $numeroSource));

    $idPersExt = null;
    if(empty($personneExterne)) {
        $erreurPersExt = ajouterPersonneExt(array('denomination'=> $numeroSource));
        if ($erreurPersExt->errCode === NO_ERR) {
            $idPersExt = $erreurPersExt->param['id_pers_ext'];
        } else {
            //Erreur lors de la creation d'une personne externe
            return array(
                'success' => false,
                'datas' => array(
                    'msg' => $erreurPersExt->errCode,
                ),
            );
        }
    } else {
        $idPersExt = $personneExterne[0]['id_pers_ext'];
    }

    $InfoTireur=getMatchedTireurBenef(array('denomination'=> $libelleAgence), 't');

    $idTireur = null;
    if($InfoTireur==null) {
        $idTireur=insere_tireur_benef(array('denomination'=> $libelleAgence, 'id_banque'=> 1, 'pays' => $id_pays_tireur_benef, 'id_ag'=>$id_agence_source, 'tireur'=>'t'));
    } else {
        $idTireur = $InfoTireur[0]['id'];
    }

    $data['id_pers_ext']      = $idPersExt;
    $data['id_correspondant'] = $correspondant['id'];
    $data['id_ext_benef']	  = null;
    $data['id_cpt_benef']     = $num_compte_cible;
    $data['id_ext_ordre']	  = $idTireur;
    $data['id_cpt_ordre']	  = null;
    $data['sens']		      = 'in ';
    $data['type_piece']		  = 3;
//    $data['num_piece']		= $SESSION_VARS['num_chq'];
//    $data['date_piece']		= $SESSION_VARS['date_chq'];
    $data['date']		      = date("d/m/Y");
    $data['montant']		  = recupMontant($montant);
    $data['devise']		      = $InfoCpte['devise'];
    $data['id_banque']		= $correspondant['id_banque'];
    $data['communication']    = $id_transaction_rswitch;
    $data['remarque']         = 'TRANSFERT DEPUIS RSWITCH : ' . $libelleAgence . ' - ' . $codeAgence;

    $CHANGE = NULL;
    $erreur = receptionVirement($data, $InfoCpte, $InfoProduit, $CHANGE);

    if ($erreur->errCode === NO_ERR) {
        $message = "Transaction id : ".sprintf("%09d", $erreur->param['id']);

        $dbHandler->closeConnection(true);
        $return_data = array(
            'success' => true,
            'datas' => array(
                'msg' => $message,
            ),
        );
    } else {

        $dbHandler->closeConnection(false);

        $return_data = array(
            'success' => false,
            'datas' => array(
                'msg' => array_key_exists($erreur->errCode, $error)  ? $error[$erreur->errCode] : $erreur->errCode,
            ),
        );
    }

 // Fin Transfert sur deux différents comptes clients dans deux différentes agences
} else {

    $dbHandler->closeConnection(false);

    $return_data = array(
        'success' => false,
        'datas' => array(
            'msg' => array_key_exists($MyErr->errCode, $error)  ? $error[$MyErr->errCode] : $MyErr->errCode,
        ),
    );
}

echo json_encode($return_data, 1 | 4 | 2 | 8);
exit;
