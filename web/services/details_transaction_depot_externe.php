<?php

$return_data = array(
    'success' => false,
    'datas' => array(),
);

$id_agence_source = trim($_REQUEST['id_ag']);
$type_depot = trim($_REQUEST['type_depot']);
$id_transaction_adbanking = trim($_REQUEST['id_transaction_adbanking']);
$id_transaction_source = trim($_REQUEST['id_transaction_source']);

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
//require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/dbProcedures/parametrage.php';
//require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';

$global_nom_login = 'api';

function isEmpty($value)
{
    return $value == null || $value == '';
}

if(isEmpty($id_agence_source) || isEmpty($type_depot) || (isEmpty($id_transaction_adbanking) && isEmpty($id_transaction_source)))
{
    $MyErr = new ErrorObj("ERR_PARAMETRE_MANQUANT: [$id_agence_source - $type_depot - $id_transaction_adbanking - $id_transaction_source]");
}
else
{
    $MyErr = new ErrorObj(NO_ERR);
}

if ($MyErr->errCode === NO_ERR) {
    $filtres = array(
        'type_depot' => $type_depot,
        'id_transaction_adbanking' => $id_transaction_adbanking,
        'id_transaction_source' => $id_transaction_source,
    );

    $datas = getDetailsTransaction($filtres);

    //print_r($datas);

    $dbHandler->closeConnection(true);

    $return_data = array(
        'success' => true,
        'datas' => $datas,
    );
}
else
{
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
