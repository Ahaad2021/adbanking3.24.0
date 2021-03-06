<?php

require_once('lib/misc/tableSys.php');

function getCreditAttente($whereCond = null, $order_by = null)
{
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ml_demande_credit";

    if ($whereCond != null) {
        $sql .= " WHERE " . $whereCond;
    }
    if ($order_by != null) {
        $sql .= " order by " . $order_by;
    }
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        $DATAS[$row["id_doss"]] = $row;

    $dbHandler->closeConnection(true);
    return $DATAS;
}

function getDataDemandeMobileLending($where = null){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT m.* from ml_demande_credit m INNER JOIN ad_abonnement b on b.id_client = m.id_client ";

    if ($where != null){
        $sql .= " WHERE ".$where;
    }
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        $DATAS[$row["id_doss"]] = $row;


    $dbHandler->closeConnection(true);
    return $DATAS;
}

function getDataUtilisateurMobileLending($id_uti){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ad_uti WHERE id_utilis = $id_uti";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $row;

}

function getDataCreditMobileLendingEnCours($id_doss){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT d.etat, t.date_ech, t.solde_cap, t.solde_int, t.solde_gar , t.solde_pen FROM ad_dcr d INNER JOIN ad_etr t ON t.id_doss = d.id_doss WHERE d.id_doss = $id_doss AND t.remb = 'f' ORDER BY id_ech ASC limit 1";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        $DATAS[$id_doss] = $row;

    $dbHandler->closeConnection(true);
    return $DATAS;


}

function getDataCreditMobileLendingSolde($id_doss){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT d.etat,t.date_ech, t.mnt_cap, t.mnt_int, t.mnt_gar , t.mnt_reech FROM ad_dcr d INNER JOIN ad_etr t ON t.id_doss = d.id_doss WHERE d.id_doss = $id_doss AND t.remb = 't' order by id_ech DESC limit 1";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        $DATAS[$id_doss] = $row;

    $dbHandler->closeConnection(true);
    return $DATAS;


}


function xmlNouveauClient($DATA,$list_criteres){
    global $adsys, $global_id_agence;
    $document = create_xml_doc("mobile_lending_nouveaux_clients", "rapport_nouveaux_client_mobile_lending.dtd");
    //Element root
    $root = $document->root();
    //En-t??te g??n??raliste
    gen_header($root, 'MLE-NCL');
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $list_criteres);

    $body = $root->new_child("new_client", "");

    foreach($DATA['credit_encours'] as $key => $value) {
        $credit_encours = $body -> new_child("credit_en_cours","");
        $credit_encours->new_child("num_client", $value['no_client']);
        $credit_encours->new_child("nom_client", $value['nom']);
        $credit_encours->new_child("telephone1", $value['telephone']);
        $credit_encours->new_child("dossier", $value['id_doss']);
        $credit_encours->new_child("mnt_credit", afficheMontant($value['mnt_dem']));
        $credit_encours->new_child("duree", $value['duree']);
        $credit_encours->new_child("etat", $value['etat_credit']);
        $credit_encours->new_child("mnt_eche", afficheMontant($value['mnt_eche']));
        $credit_encours->new_child("date_eche", pg2phpDate($value['date_eche']));
    }



    foreach($DATA['credit_solde'] as $key => $value) {
        $credit_solde = $body -> new_child("credit_solde","");
        $credit_solde->new_child("num_client_solde", $value['no_client']);
        $credit_solde->new_child("nom_client_solde", $value['nom']);
        $credit_solde->new_child("telephone_solde", $value['telephone']);
        $credit_solde->new_child("dossier_solde", $value['id_doss']);
        $credit_solde->new_child("mnt_credit_solde", afficheMontant($value['mnt_dem']));
        $credit_solde->new_child("duree_solde", $value['duree']);
        $credit_solde->new_child("etat_solde", $value['etat_credit']);
        $credit_solde->new_child("mnt_eche_solde", afficheMontant($value['mnt_eche']));
        $credit_solde->new_child("date_eche_solde", pg2phpDate($value['date_eche']));
    }

    $xml = $document->dump_mem(true);

    return $xml;
}

function isPremierCreditMobileLending($id_client){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT premier_credit FROM ad_abonnement a INNER JOIN ml_demande_credit m on m.id_client = a.id_client WHERE m.id_client = $id_client AND a.deleted = 'f'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $row;

}

function getDataClientAbonnee($client = NULL) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ml_donnees_client_abonnees";

    if($client != NULL){
        $sql .= " WHERE client = $client";
    }
    $sql .=" ORDER BY client ASC";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //echo "Erreur dans la fonction (getDataClientAbonnee) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getDataClientAbonnee : $sql"));
    }
    if ($result->numrows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $i=1;
    $retour = array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $retour[$i]=$rows;
        $i++;
    }
    $dbHandler->closeConnection(true);
    return $retour;
}

function getGarantiEnCours($id_client){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "Select sum(montant_vente) as mnt_gar_tot from ad_gar g INNER JOIN ad_dcr d On d.id_doss = g.id_doss WHERE d.id_client = $id_client AND d.etat IN (5,6,7,9,13,14,15) AND g.etat_gar IN (2,3)";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //echo "Erreur dans la fonction (getGarantiEnCours) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getGarantiEnCours: $sql"));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return false;
    }
    $dbHandler->closeConnection(true);
    $tmpRow = $result->fetchrow();
    $mnt = $tmpRow[0];
    return $mnt;

}

function getLocalisationIMF(){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT ml_localisation from ad_agc";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    $dbHandler->closeConnection(true);
    return $DATAS;
}

function getDataClientAbonneeSpecifique($client = NULL) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ml_donnees_client_abonnees_specifique";

    if($client != NULL){
        $sql .= " WHERE client = $client";
    }
    $sql .=" ORDER BY client ASC";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //echo "Erreur dans la fonction (getDataClientAbonnee) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getDataClientAbonnee : $sql"));
    }
    if ($result->numrows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $i=1;
    $retour = array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $retour[$i]=$rows;
        $i++;
    }
    $dbHandler->closeConnection(true);
    return $retour;
}

function getDataDemandeClientEligible($where = null){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT m.id_client, MIN(m.id_transaction) as transaction, m.id_prod, MIN(m.id_doss) FROM ml_demande_credit m ";

    if ($where != null){
        $sql .= " WHERE ".$where;
    }
    $sql .= "GROUP BY m.id_client, m.id_prod";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        $DATAS[$row["id_client"]] = $row;


    $dbHandler->closeConnection(true);
    return $DATAS;
}

function getMobileLendingData($statut_demande, $cre_etat){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM get_rapport_mobile_lending(ARRAY[$statut_demande], ARRAY[$cre_etat]::integer[])";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }

    $DATAS = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        $DATAS[$row["id_doss"]] = $row;


    $dbHandler->closeConnection(true);
    return $DATAS;
}

function xml_mobile_lending($infos_dossiers, $code_rapport) {
    //XML
    $document = create_xml_doc("mobile_lending", "rapport_analytics_mobile_lending.dtd");

    //Element root
    $root = $document->root();

    gen_header($root, $code_rapport);

    //En-t??te contextuel
//    $header_contextuel = $infos_doss->new_child("header_contextuel", "");

    $node_analytics = $root->new_child("analytics", "");

    foreach ($infos_dossiers as $id_client => $data) {
        // Une page d'??ch??ancier
        $infos = $node_analytics->new_child("infos", "");
        $infos->new_child("id_client", $data["id_client"]);
        $infos->new_child("id_doss", $data["id_doss"]);
        $infos->new_child("etat_doss_mob", $data["etat_doss_mob"]);
        $infos->new_child("imf", $data["imf"]);
        $infos->new_child("agence", $data["agence"]);
        $infos->new_child("id_agent", $data["id_agent"]);
        $infos->new_child("localisation", $data["localisation"]);
        $infos->new_child("tranche_localisation", $data["tranche_localisation"]);
        $infos->new_child("sexe", $data["sexe"]);
        $infos->new_child("tranche_sexe", $data["tranche_sexe"]);
        $infos->new_child("sal_moy", $data["sal_moy"]);
        $infos->new_child("tranche_sal_moy", $data["tranche_sal_moy"]);
        $infos->new_child("lg_histo", $data["lg_histo"]);
        $infos->new_child("tranche_lg_histo", $data["tranche_lg_histo"]);
        $infos->new_child("somm_tot_emprunter", $data["somm_tot_emprunter"]);
        $infos->new_child("tranche_somm_tot_emprunter", $data["tranche_somm_tot_emprunter"]);
        $infos->new_child("nbre_credit_carac", $data["nbre_credit_carac"]);
        $infos->new_child("tranche_nbre_credit", $data["tranche_nbre_credit"]);
        $infos->new_child("age", $data["age"]);
        $infos->new_child("tranche_age", $data["tranche_age"]);
        $infos->new_child("tx_irregularite", $data["tx_irregularite"]);
        $infos->new_child("tranche_tx_irregularite", $data["tranche_tx_irregularite"]);
        $infos->new_child("nbre_credit", $data["nbre_credit"]);
        $infos->new_child("mnt_dem", $data["mnt_dem"]);
        $infos->new_child("date_deboursement", $data["date_deboursement"]);
        $infos->new_child("nbre_ech", $data["nbre_ech"]);
        $infos->new_child("retard_ech_1", $data["retard_ech_1"]);
        $infos->new_child("retard_ech_2", $data["retard_ech_2"]);
        $infos->new_child("retard_ech_3", $data["retard_ech_3"]);
        $infos->new_child("mnt_rest_du", $data["mnt_rest_du"]);
        $infos->new_child("penalite", $data["penalite"]);
        $infos->new_child("score_retard_credit", $data["score_retard_credit"]);
        $infos->new_child("score_client", $data["score_client"]);
        $infos->new_child("commentaire", $data["commentaire"]);
    } // fin parcours des dossiers

    return $document->dump_mem(true);
}
?>