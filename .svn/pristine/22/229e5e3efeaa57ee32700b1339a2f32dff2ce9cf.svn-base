<?php

/**
 * @package Ligne de Credit
 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/extraits.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/VariablesGlobales.php';

/**
 * Insertion dans la table ad_lcr_his
 *
 * @param Integer $id_doss : 
 * @param Date $date_evnt : 
 * @param Integer $type_evnt : 
 * @param Integer $nature_evnt : 
 * @param String $login : 
 * @param Integer $valeur : 
 * @param Integer $id_his : 
 * @param String $comments : 
 * 
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function insertLcrHis($id_doss, $date_evnt, $type_evnt, $nature_evnt, $login, $valeur, $id_his=NULL, $comments='') {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = null;

    $tableFields = array(
        "id_doss"       => $id_doss,
        "date_evnt"     => $date_evnt,
        "type_evnt"     => $type_evnt,
        "nature_evnt"   => $nature_evnt,
        "login"         => trim($login),
        "valeur"        => trim($valeur),
        "id_his"        => $id_his,
        "comments"      => trim($comments),
        "id_ag"         => trim($global_id_agence),
    );
    $sql = buildInsertQuery("ad_lcr_his", $tableFields);

    $result = $db->query($sql);

    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

/**
 * Modification dans la table ad_lcr_his
 */
function updateLcrHis($id_doss, $type_evnt, $nature_evnt, $id_his=NULL, $comments='') {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $Fields = array();
    $Where = array();

    $Fields["id_his"]       = $id_his;
    $Fields["comments"]     = trim($comments);

    $Where["id_doss"]       = $id_doss;
    $Where["type_evnt"]     = $type_evnt;
    $Where["nature_evnt"]   = $nature_evnt;
    $Where["id_ag"]         = $global_id_agence;

    $sql = buildUpdateQuery("ad_lcr_his", $Fields, $Where);


    // Exécution de la requête
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    //$dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

function getPlafondLcr($id_doss) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM get_plafond_lcr($id_doss);";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);
    
    if ($result->numRows() == 0) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
    }

    $tmprow = $result->fetchrow();

    if ($result->numRows() > 1) {
        return 0;
    }

    return $tmprow[0];
}

function getCapitalRestantDuLcr($id_doss, $date_due) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM get_cap_restant_du_lcr($id_doss, '$date_due');";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getMontantRestantADebourserLcr($id_doss, $date_due) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM get_montant_restant_debourser_lcr($id_doss, '$date_due');";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getCalculFraisLcr($id_doss, $date_due, $b_restant=1) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM calcul_frais_lcr($id_doss, '$date_due', $b_restant);";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getCalculInteretsLcr($id_doss, $date_due, $b_restant=1) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM calcul_interets_lcr($id_doss, '$date_due', $b_restant);";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getDernierDateDebLcr($id_doss) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT date_evnt FROM ad_lcr_his WHERE id_doss=$id_doss AND type_evnt=2 ORDER BY date_creation DESC LIMIT 1;";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getDernierDateRembLcr($id_doss) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT date_evnt FROM ad_lcr_his WHERE id_doss=$id_doss AND ((type_evnt = 3 AND nature_evnt = 1) OR (type_evnt = 3 AND nature_evnt = 2) OR type_evnt = 4) ORDER BY date_creation DESC LIMIT 1;";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getDateFinEcheanceLcr($id_doss) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT date_ech FROM ad_etr WHERE id_ag=$global_id_agence AND id_doss=$id_doss ORDER BY id_ech DESC LIMIT 1;";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        $DOSS = getDossierCrdtInfo($id_doss);

        $dateInfos = splitEuropeanDate(pg2phpDate($DOSS['cre_date_approb']));
        $tmp_ech_date = date("Y-m-d", mktime(0,0,0,$dateInfos[1], $dateInfos[0]+$DOSS['duree_mois'], $dateInfos[2]));

        return $tmp_ech_date;
    }

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getSoldePenEcheanceLcr($id_doss) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT solde_pen FROM ad_etr WHERE id_ag=$global_id_agence AND id_doss=$id_doss ORDER BY id_ech DESC LIMIT 1;";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function getRembPenEcheanceLcr($id_doss, $date_remb) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT SUM(mnt_remb_pen) FROM ad_sre WHERE id_ag = $global_id_agence AND annul_remb = null AND id_his = null AND id_doss = $id_doss AND to_char(date_remb, 'YYYY-MM-DD') = '" . php2pg($date_remb) . "';";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

function isEchExistLcr($id_doss) { //Renvoie true si le client existe
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT count(*) FROM ad_etr WHERE id_ag=$global_id_agence AND id_doss = $id_doss";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return ($row[0] > 0);
}

function isRembAvantEchLcr($id_doss) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT remb_auto_lcr FROM ad_dcr WHERE id_ag=$global_id_agence AND id_doss = $id_doss";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return $row[0];
}

/**
 * Cumule montant capital dans la table ad_etr
 */
function updateEchCapLcr($id_doss, $montant, $sens='c') {

    global $global_id_agence;

    if ($sens == 'c') {
        $sign = "+";
    } elseif ($sens == 'd') {
        $sign = "-";
    }

    $sql = sprintf("UPDATE ad_etr SET mnt_cap = mnt_cap %s %d, solde_cap = solde_cap %s %d WHERE id_doss = %d AND id_ech = 1 AND id_ag = %d;", $sign, $montant, $sign, $montant, $id_doss, $global_id_agence);


    return executeDirectQuery($sql);
}

/**
 * Update total montant capital dans la table ad_etr
 */
function updateEchCapTotalLcr($id_doss, $montant) {

    global $global_id_agence;
    
    $sql = sprintf("UPDATE ad_etr SET mnt_cap = %d, solde_cap = %d WHERE id_doss = %d AND id_ech = 1 AND id_ag = %d;", $montant, $montant, $id_doss, $global_id_agence);


    return executeDirectQuery($sql);
}

/**
 * Cumule montant intérêt dans la table ad_etr
 */
function updateEchIntLcr($id_doss, $montant, $sens='c') {

    global $global_id_agence;
    
    if ($sens == 'c') {
        $sign = "+";
    } elseif ($sens == 'd') {
        $sign = "-";
    }
    
    $sql = sprintf("UPDATE ad_etr SET mnt_int = mnt_int %s %d, solde_int = solde_int %s %d WHERE id_doss = %d AND id_ech = 1 AND id_ag = %d;", $sign, $montant, $sign, $montant, $id_doss, $global_id_agence);
    


    return executeDirectQuery($sql);
}

/**
 * Update total montant intérêt dans la table ad_etr
 */
function updateEchIntTotalLcr($id_doss, $montant) {

    global $global_id_agence;
    
    $sql = sprintf("UPDATE ad_etr SET mnt_int = %d, solde_int = %d WHERE id_doss = %d AND id_ech = 1 AND id_ag = %d;", $montant, $montant, $id_doss, $global_id_agence);
    

    return executeDirectQuery($sql);
}

/**
 * Cumule montant frais dans la table ad_dcr
 */
function updateEchFraisLcr($id_doss, $montant, $sens='c') {

    global $global_id_agence;
    
    if ($sens == 'c') {
        $sign = "+";
    } elseif ($sens == 'd') {
        $sign = "-";
    }
    
    $sql = sprintf("UPDATE ad_dcr SET solde_frais_lcr = solde_frais_lcr %s %d WHERE id_doss = %d AND is_ligne_credit = 't' AND id_ag = %d;", $sign, $montant, $id_doss, $global_id_agence);
    


    return executeDirectQuery($sql);
}

/**
 * Update total montant frais dans la table ad_dcr
 */
function updateEchFraisTotalLcr($id_doss, $montant) {

    global $global_id_agence;
    
    $sql = sprintf("UPDATE ad_dcr SET solde_frais_lcr = %d WHERE id_doss = %d AND is_ligne_credit = 't' AND id_ag = %d;", $montant, $id_doss, $global_id_agence);
    


    return executeDirectQuery($sql);
}

/**
 * Update deboursement_autorisee_lcr dans la table ad_etr
 */
function updateDebAutoriseLcr($id_doss, $status='f') {

    global $global_id_agence;
    global $date_total;

    $date_nettoyage = php2pg($date_total);
    
    $sql = sprintf("UPDATE ad_dcr SET deboursement_autorisee_lcr = '%s', motif_changement_authorisation_lcr = '%s', date_changement_authorisation_lcr = '%s' WHERE id_doss = %d AND is_ligne_credit = 't' AND id_ag = %d;", $status, 'Le dossier ligne de crédit est passé en période de nettoyage le ', $date_nettoyage, $id_doss, $global_id_agence);
    

    return executeDirectQuery($sql);
}

function prelevement_frais_interets_fin_mois_lcr() {
    
    global $dbHandler;
    global $date_total;

    affiche (_("Prélèvements des frais et intérêts ligne de crédit..."));

    $db = $dbHandler->openConnection();
    
    $sql = "SELECT * FROM isFinMois('".demain($date_total)."');"; // demain()

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $row = $result->fetchrow();
    $is_fin_mois = $row[0];
    
    $count_dcr = 0;
    if($is_fin_mois == 't') {
        $myErr = rembourse_frais_int_lcr(demain($date_total)); // demain()
        
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        
        $count_dcr = $myErr->param[0];
    }
    
    $dbHandler->closeConnection(true);
    affiche(sprintf(_("OK. Frais et intérêts ligne de crédit sur %s dossiers"), $count_dcr), true);
    decLevel();
    affiche(_("Prélèvements des frais et intérêts ligne de crédit terminés !"));

    return new ErrorObj(NO_ERR);
}

/**
 * update_interets_lcr Met à jour l'intérêt attendu sur les dossiers de ligne de crédit
 *
 * @access public
 * @return void
 */
function update_interets_lcr() {
    global $dbHandler;
    global $date_total;

    affiche (_("Mise à jour des intérêts ligne de crédit..."));

    $db = $dbHandler->openConnection();
    
    $whereCl = " AND is_ligne_credit='t' AND mode_calc_int=5 AND etat=5";
    
    $dossiers_reels = getDossiersCredits($whereCl);

    $count_dcr = 0;
    if (is_array($dossiers_reels)) {
        foreach($dossiers_reels as $id_doss=>$val_doss) {

            $mnt_int_du = getCalculInteretsLcr($id_doss, php2pg(demain($date_total))); // demain()
            
            if ($mnt_int_du > 0) {
                updateEchIntTotalLcr($id_doss, $mnt_int_du);
                
                $count_dcr++;
            }
        }
    }
    
    $dbHandler->closeConnection(true);

    affiche(sprintf(_("OK (%s dossiers ligne de crédit)"), $count_dcr), true);
    
    decLevel();
    affiche(_("Mise à jour des intérêts ligne de crédit terminés !"));

    return new ErrorObj(NO_ERR);
}

/**
 * Clôture dossier ligne de crédit
 *
 * @param integer $id_doss Identifiant du dossier
 * @param object ErrorObj
 */
function clotureCredit($id_doss) {

    global $dbHandler, $global_monnaie_courante_prec, $global_nom_login, $global_id_agence;

    $db = $dbHandler->openConnection();

    $DCR = getDossierCrdtInfo($id_doss);
    $id_client = $DCR["id_client"];

    $today = date("d/m/Y");
    $isCloture = false;
    $comptable = array();

    /* Recupération des infos sur le crédit : dernière échéance non remboursée ou partiellement et les remboursements */
    $info = get_info_credit($id_doss, 1);

    $info['solde_int'] = getCalculInteretsLcr($id_doss, php2pg($today));
    $info['solde_frais'] = getCalculFraisLcr($id_doss, php2pg($today));

    // Set crédit soldé
    if ($info['solde_cap'] == 0 && $info['solde_int'] == 0 && $info['solde_pen'] == 0 && $info['solde_gar'] == 0 && $info['solde_frais'] == 0) {

        // Insert lcr event
        $date_evnt = $today;
        $type_evnt = 7; // Soldé
        $nature_evnt = NULL;
        $login = $global_nom_login;
        $id_his = NULL;
        $comments = 'Crédit soldé le ' . $date_evnt . ' (Clôture ligne de crédit)';

        $lcrErr2 = insertLcrHis($id_doss, php2pg($date_evnt), $type_evnt, $nature_evnt, $login, 0, $id_his, $comments);

        if ($lcrErr2->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $lcrErr2;
        }
        else {

            // Mise à jour echéancier
            $sql = "UPDATE ad_etr SET remb='t' WHERE id_ag=$global_id_agence AND id_doss=".$id_doss." AND id_ech=1;";
            $result=$db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
                signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
            }
        }
    }

    // Mise à jour du dossier crédit
    $sql = "SELECT date_ech FROM ad_etr WHERE id_ag = $global_id_agence AND id_doss = $id_doss AND (remb='f') ORDER BY date_ech";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    }
    $numrows = $result->numrows();

    if ($numrows == 0) { // Si toutes les échéances sont remboursées
        // Le crédit passe à l'état soldé

        if (echeancierRembourse($id_doss)) {
            $myErr = soldeCredit($id_doss, $comptable); // Mettre l'état du crédit à soldé
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            } else {
                $isCloture = true;

                ajout_historique(610, $id_client, $id_doss, $global_nom_login, date("r"), $comptable);
            }
        }

    } //end if crédit soldé

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR, $isCloture);
}

function isPeriodeNettoyageLcr($id_doss, $duree_nettoyage_lcr=0) {
    
    // TO IMPLEMENT
    $date_due = php2pg(date("d/m/Y"));
    
    if ($date_due >= php2pg(calculDateDureeMois(pg2phpDate(getDateFinEcheanceLcr($id_doss)), -$duree_nettoyage_lcr))) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function blocage_periode_de_nettoyage_lcr() {
    global $dbHandler, $global_nom_login;
    global $date_total;

    affiche (_("Passage en période de nettoyage - ligne de crédit..."));

    $db = $dbHandler->openConnection();
    
    $whereCl = " AND is_ligne_credit='t' AND deboursement_autorisee_lcr='t' AND mode_calc_int=5 AND etat=5";    
    $dossiers_reels = getDossiersCredits($whereCl);

    $count_dcr = 0;
    if (is_array($dossiers_reels)) {
        foreach($dossiers_reels as $id_doss=>$val_doss) {

            if (php2pg(($date_total)) >= php2pg(calculDateDureeMois(pg2phpDate(getDateFinEcheanceLcr($id_doss)), -$val_doss["duree_nettoyage_lcr"]))) { // demain()

                $lcrErr = updateDebAutoriseLcr($id_doss, 'f');

                if ($lcrErr->errCode == NO_ERR) {

                    // Insert lcr event
                    $date_evnt = $date_total;
                    $type_evnt = 5; // Suspension
                    $nature_evnt = NULL;
                    $login = $global_nom_login;
                    $id_his = NULL;
                    $comments = 'Crédit suspendu le ' . $date_evnt . ' (Période de nettoyage)';

                    $lcrErr2 = insertLcrHis($id_doss, php2pg($date_evnt), $type_evnt, $nature_evnt, $login, 0, $id_his, $comments);

                    if ($lcrErr2->errCode != NO_ERR) {
                      $dbHandler->closeConnection(false);
                      return $lcrErr2;
                    }

                    $count_dcr++;
                }
            }
        }
    }
    
    $dbHandler->closeConnection(true);

    affiche(sprintf(_("OK (%s dossiers passés en période de nettoyage)"), $count_dcr), true);
    
    decLevel();
    affiche(_("Passage en période de nettoyage - ligne de crédit - terminés !"));

    return new ErrorObj(NO_ERR);    
}

function rembourse_cap_lcr($date_remb, $num_compte, $montant_depo = null, $id_his = NULL) {

    global $dbHandler;
    global $global_id_agence;
    global $global_nom_login;
    global $global_monnaie_courante_prec;
    global $appli;
    global $error;

    $db = $dbHandler->openConnection();

    $whereCl = " AND is_ligne_credit='t' AND remb_auto_lcr='t' AND mode_calc_int=5 AND etat=5";
    $whereCl .= " AND cpt_liaison = '$num_compte'";

    $dossiers_reels = getDossiersCredits($whereCl);

    //$date_remb = $date_exec;
    $count_dcr = 0;
    $total_mnt = 0;
    if (is_array($dossiers_reels)) {
        foreach($dossiers_reels as $id_doss=>$val_doss) {
            $comptable = array();

            if ($val_doss['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe

                ///// START LOOP /////
                /* Récupération des infos sur le dossier de crédit */
                $DCR = getDossierCrdtInfo($id_doss);
                $id_client = $DCR["id_client"];

                /* Récupération des infos sur le produit de crédit associé */
                $Produit = getProdInfo(" where id =".$DCR["id_prod"], $id_doss);
                $PROD = $Produit[0];
                $devise = $PROD["devise"];

                /* Récupération des infos sur la devise du produit */
                $DEV = getInfoDevise($devise);

                /* Recupération des infos sur le crédit : première échéance non remboursée */
                $info = get_info_credit($id_doss, 1);

                /* Récupération du compte de liaison */
                $cpt_liaison = $DCR["cpt_liaison"];
                
                $soldeDispo = getSoldeDisponible($cpt_liaison);

                //Kheshan ticket pp178p1-bon montant de depo utiliser
                /* Récupération du total attendu pour la première échéance non remboursée */
                if (is_null($montant_depo) ){
                    $mnt = round($soldeDispo, $global_monnaie_courante_prec);
                }else{
                    $mnt = $montant_depo;
                }
                //$mnt = round($soldeDispo, $global_monnaie_courante_prec);


                if ($info['solde_cap'] > 0 && $mnt > 0) {

                    $total_credit = round($info['solde_cap'], $global_monnaie_courante_prec);
                    /* Ordre de remboursement :
                           - le capital
                           qui est l'ordre par défaut
                    */
                    $ORDRE_REMB = array("cap");

                    /* Si DATA_REMB est : le capital */
                    $DATA_REMB = array("cap"=>true);

                    /* amnt est le montant remboursé disponible restant */
                       $amnt = min($mnt, $total_credit); //takes whichever is lovvest

                    /* Rembourser selon l'odre et les remboursement précisés */
                    $solde_cap = 0;
                    $mnt_remb_cap = 0;
                    foreach($ORDRE_REMB as $key=>$value) {
                      if ($DATA_REMB[$value] == true) { /* il faut le rembourser si le montant disponible le permet */
                        $ {"mnt_remb_".$value} = min($info["solde_".$value], $amnt);
                        $amnt -= $ {"mnt_remb_".$value};
                        $ {"solde_".$value} = $info["solde_".$value] - $ {"mnt_remb_".$value};
                      }
                    }
                    $id_echeance=$info['id_ech'];

                    $num_rembours = getNextNumRemboursement($info['id_doss'],$id_echeance);
                    
                    if ($mnt_remb_cap > 0) {

                        // Insertion du remboursement dans la DB
                        $sql = "INSERT INTO ad_sre(id_doss,id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, mnt_remb_gar) VALUES(".$info['id_doss'].",$global_id_agence,".$num_rembours.",'".$date_remb."',".$id_echeance.",$mnt_remb_cap,0,0,0)";

                        $result = $db->query($sql);
                        if (DB::isError($result)) {
                          $dbHandler->closeConnection(false);
                          signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                        }

                        //Met à jour le solde restant dû pour l'échéance
                        updateEchCapTotalLcr($info['id_doss'], $solde_cap);
                    }
                    
                    // Réalise les débits/crédits
                    $id_cpt_credit = $info['id_cpt_credit'];

                    $cptes_substitue = array();
                    $cptes_substitue["cpta"] = array();
                    $cptes_substitue["int"] = array();

                    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($cpt_liaison);
                    if ($cptes_substitue["cpta"]["debit"] == NULL) {
                      $dbHandler->closeConnection(false);
                      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne du compte de liaison"));
                    }
                    $cptes_substitue["int"]["debit"] = $cpt_liaison;

                    global $global_monnaie;

                    /* S'il y a remboursemnt de capital */
                    if ($mnt_remb_cap > 0) {
                      // Recherche du type d'opération
                      $type_oper = get_credit_type_oper(1, 2);

                      // Passage des écritures comptables
                      // Débit client / crédit compte de crédit
                      // Recherche du compte comptable associé au crédit en fonction de son état
                      $CPTS_ETAT = recup_compte_etat_credit($DCR["id_prod"]);
                      $cptes_substitue["cpta"]["credit"] = $CPTS_ETAT[$DCR["cre_etat"]];
                      $cptes_substitue["int"]["credit"] = $id_cpt_credit;

                      if ($cptes_substitue["cpta"]["credit"] == NULL) {
                        $dbHandler->closeConnection(false);
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit de crédit"));
                      }
                      if ($date_remb == NULL) {
                        $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_cap, $comptable,$cptes_substitue, $devise,NULL,$id_doss);
                      } else {
                          $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_cap, $comptable,$cptes_substitue, $devise, $date_remb,$id_doss);
                      }
                      if ($err->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $err;
                      } else {
                          // Insert lcr event
                          $date_evnt = (($date_remb == NULL)?date("d/m/Y"):$date_remb);
                          $type_evnt = 3; // Remboursement
                          $nature_evnt = 1; // Capital
                          $login = $global_nom_login;
                          //$id_his = NULL;
                          $comments = 'Remboursement capital de '.afficheMontant($mnt_remb_cap).' '.$devise;

                          $lcrErr = insertLcrHis($id_doss, php2pg($date_evnt), $type_evnt, $nature_evnt, $login, $mnt_remb_cap, $id_his, $comments);

                          if ($lcrErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $lcrErr;
                          } else {
                                $info['solde_frais'] = getCalculFraisLcr($id_doss, php2pg(($date_evnt)));
                                // Set crédit soldé
                                if ($solde_cap == 0 && $info['solde_int'] == 0 && $info['solde_pen'] == 0 && $info['solde_gar'] == 0 && $info['solde_frais'] == 0) {
                                      // Insert lcr event
                                      $date_evnt = (($date_remb == NULL)?date("d/m/Y"):$date_remb);
                                      $type_evnt = 7; // Soldé
                                      $nature_evnt = NULL;
                                      $login = $global_nom_login;
                                      //$id_his = NULL;
                                      $comments = 'Crédit soldé le ' . $date_evnt;

                                    //Evolution pp161 :kheshan
                                    $date_ech = php2pg(pg2phpDate(getDateFinEcheanceLcr($id_doss)));
                                    //si on est dans le periode de nettoyage
                                    If (isPeriodeNettoyageLcr($id_doss, $DCR["duree_nettoyage_lcr"]) AND (php2pg($date_evnt) < $date_ech) AND $DCR["deboursement_autorisee_lcr"] == 'f'){ //in period de nettoyage
                                        //echo 'En periode nettoyage';
                                        $comments .= ' (En période nettoyage)';
                                    }
                                    elseif((php2pg($date_evnt) >= $date_ech)){//a la fin de lécheance
                                        //echo 'event > echeance';
                                        $comments .= ' (Fin échéance)';
                                    }


                                    $lcrErr2 = insertLcrHis($id_doss, php2pg($date_evnt), $type_evnt, $nature_evnt, $login, 0, $id_his, $comments);

                                      if ($lcrErr2->errCode != NO_ERR) {
                                        $dbHandler->closeConnection(false);
                                        return $lcrErr2;
                                      }
                                      else {
                                          $date_ech = php2pg(pg2phpDate(getDateFinEcheanceLcr($id_doss)));
                                          //Evolution pp161 :kheshan
                                          $date_debut_nettoyage = calculDateDureeMois($date_ech, -$DCR["duree_nettoyage_lcr"]);
                                          //Si credit soldé durant periode de nettoyage
                                          if((isPeriodeNettoyageLcr($id_doss, $DCR["duree_nettoyage_lcr"]) AND (php2pg($date_evnt) < $date_ech) AND $DCR["deboursement_autorisee_lcr"] == 'f')){// en periode nettoyage
                                              // mise à jour echeancier theorique
                                              $sql = "UPDATE ad_etr SET remb='t' WHERE id_ag=$global_id_agence AND id_doss=".$id_doss." AND id_ech=1;";
                                              $result=$db->query($sql);
                                              if (DB::isError($result)) {
                                                  $dbHandler->closeConnection(false);
                                                  signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                                              }
                                          }
                                          elseif((php2pg($date_evnt) >= $date_ech)){//credit solder  a la fin echeancier
                                              // mise à jour echeancier theorique
                                              $sql = "UPDATE ad_etr SET remb='t' WHERE id_ag=$global_id_agence AND id_doss=".$id_doss." AND id_ech=1;";
                                              $result=$db->query($sql);
                                              if (DB::isError($result)) {
                                                  $dbHandler->closeConnection(false);
                                                  signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                                              }


                                          }
                                      }
                                }
                              //the rest
                          }
                      }

                      $total_mnt += $mnt_remb_cap;

                      unset($cptes_substitue["cpta"]["credit"]);
                    }

                    // S'il y a lieu, reclasser le crédit (passage souffrance -> sain)

                    // Recherche de l'ancien état du dossier de crédit
                    $oldEtat = $info["cre_etat"];

                    // Recherche du nouvel état
                    // Pour ce faire, on va calculer le nombre de jours de retard
                    $sql = "SELECT date_ech FROM ad_etr WHERE id_ag = $global_id_agence AND id_doss = $id_doss AND (remb='f') ORDER BY date_ech";
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                      $dbHandler->closeConnection(false);
                      signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                    }
                    $numrows=$result->numrows();
                    $newEtat = 0;
                    $etat=$oldEtat;

                    if ($numrows == 0) { // Si toutes les échéances sont remboursées
                      // Le crédit passe à l'état soldé
                        
                      if (echeancierRembourse($id_doss)) {
                        $myErr = soldeCredit($id_doss, $comptable); //Mettre l'état du crédit à soldé
                        if ($myErr->errCode != NO_ERR) {
                          $dbHandler->closeConnection(false);
                          return $myErr;
                        }
                        $RETSOLDECREDIT = $myErr->param;
                      }

                    } //end if crédit soldé
                    else
                    { //échéances à traiter
                      $echeance = $result->fetchrow(DB_FETCHMODE_ASSOC);
                      $date = pg2phpDatebis($echeance["date_ech"]);
                      $nbre_secondes = gmmktime(0,0,0,$date[0], $date[1],$date[2]) - gmmktime(0,0,0,date("m"), date("d"), date("Y"));
                      $etatAvance = calculeEtatPlusAvance($id_doss);

                      if ($nbre_secondes>=0) { // Le crédit est à nouveau sain
                          $newEtat=1;
                          if ($date_remb == NULL) {
                            $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat = '".date("d/m/Y")."' WHERE id_ag = $global_id_agence AND id_doss = $id_doss";
                          } else {
                             $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat = '".$date_remb."', cre_retard_etat_max = $etatAvance WHERE id_ag=$global_id_agence AND id_doss = $id_doss";
                          }
                          $result = $db->query($sql);
                          if (DB::isError($result)) {
                            $dbHandler->closeConnection(false);
                            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                          }
                      } else {
                        $nbre_jours = $nbre_secondes/(3600*24);
                        $nbre_jours = $nbre_jours * (-1);
                        $newEtat = calculeEtatCredit($nbre_jours);

                        // Cas particulier où cette fonction a été appelée par le batch
                        // lors du passage en perte.
                        // Dans ce cas, on reste en souffrance. C'est le batch qui se chargera du passage
                        // en perte (via la fonction passagePerte)

                        $id_etat_perte = getIDEtatPerte();

                        if ($newEtat == $id_etat_perte) {
                          $newEtat -= 1; // FIXME A revoir, il peut y avoir des trous !
                        }

                        // Mise à jour si nécessaire
                        if ($oldEtat != $newEtat) {
                          if ($date_remb == NULL) {
                            $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat =  '".date("d/m/Y")."' WHERE id_ag = $global_id_agence AND id_doss = $id_doss";
                          } else {
                              $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat =  '".$date_remb."', cre_retard_etat_max = $etatAvance WHERE id_ag = $global_id_agence AND id_doss = $id_doss";
                          }
                          $result = $db->query($sql);
                          if (DB::isError($result)) {
                            $dbHandler->closeConnection(false);
                            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
                          }
                        }
                      }//end else new état crédit

                      // Reclassement du crédit si nécessaire en comptabilité
                      $myErr = placeCapitalCredit($id_doss,$oldEtat,$newEtat, $comptable, $devise);
                      if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        signalErreur(__FILE__,__LINE__,__FUNCTION__,$error[$myErr->errCode].$myErr->param);
                        return $myErr;
                      }

                    // Gestion de l'alerte
                    if ($appli!="batch") {
                      if (is_array($global_credit_niveau_retard)) {
                        $etat_plus_avance = array_keys($global_credit_niveau_retard);
                        if ($newEtat > $etat_plus_avance[0] ) {
                          unset($global_credit_niveau_retard[$etat_plus_avance[0]]);
                          $global_credit_niveau_retard[$newEtat] = array();
                          array_push($global_credit_niveau_retard[$newEtat], $id_doss);
                        }
                        elseif($newEtat == $etat_plus_avance ) {
                          array_push($global_credit_niveau_retard[$etat_plus_avance], $id_doss);
                        }
                      } else {
                        $global_credit_niveau_retard[$newEtat] = array();
                        array_push($global_credit_niveau_retard[$newEtat],$id_doss);
                      }
                    }

                  }//end échéances à traiter

                    $myErr = ajout_historique (607, $val_doss['id_client'], 'Remboursement capital crédit', $global_nom_login, date("r"), $comptable, NULL, $id_his);
                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }

                    $id_his = $myErr->param;
                    
                    $count_dcr++;
                }
            }
            //reduire le montant de depo  apres le remboursement
            $montant_depo = $montant_depo - $mnt_remb_cap;
        }
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR, array($count_dcr, $total_mnt));    
}

function rembourse_frais_int_lcr($date_remb, $num_compte = null) {

    global $dbHandler;
    global $global_id_agence;
    global $global_nom_login;
    global $global_monnaie_courante_prec;
    global $appli;
    global $error;

    $db = $dbHandler->openConnection();

    $whereCl = " AND is_ligne_credit='t' AND mode_calc_int=5 AND etat=5";
    
    if ($num_compte != null) {
        $whereCl .= " AND cpt_liaison = '$num_compte'";
    }
    
    $dossiers_reels = getDossiersCredits($whereCl);

    //require_once ('lib/misc/debug.php');

    $id_his = NULL;
    //$date_remb = $date_exec;
    $count_dcr = 0;
    $total_mnt = 0;
    if (is_array($dossiers_reels)) {
        foreach($dossiers_reels as $id_doss=>$val_doss) {
            $comptable = array();

            if ($val_doss['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe

                ///// START LOOP /////
                /* Récupération des infos sur le dossier de crédit */
                $DCR = getDossierCrdtInfo($id_doss);
                $id_client = $DCR["id_client"];

                /* Récupération des infos sur le produit de crédit associé */
                $Produit = getProdInfo(" where id =".$DCR["id_prod"], $id_doss);
                $PROD = $Produit[0];
                $devise = $PROD["devise"];

                /* Récupération des infos sur la devise du produit */
                $DEV = getInfoDevise($devise);

                /* Recupération des infos sur le crédit : première échéance non remboursée */
                $info = get_info_credit($id_doss, 1);

                /* Récupération du compte de liaison */
                $cpt_liaison = $DCR["cpt_liaison"];

                $soldeDispo = getSoldeDisponible($cpt_liaison);

                /* Récupération du total attendu pour la première échéance non remboursée */
                $mnt = round($soldeDispo, $global_monnaie_courante_prec);

                $info['solde_int'] = getCalculInteretsLcr($id_doss, php2pg($date_remb));
                $info['solde_frais'] = getCalculFraisLcr($id_doss, php2pg($date_remb));

                if (($info['solde_frais'] > 0 && $mnt > 0) || ($info['solde_int'] > 0 && $mnt > 0)) {

                    $total_credit = round($info['solde_frais'] + $info['solde_int'], $global_monnaie_courante_prec);

                    /* Ordre de remboursement :
                           - les frais
                           - les intérêst
                           qui est l'ordre par défaut
                    */
                    $ORDRE_REMB = array("frais", "int");

                    /* Si DATA_REMB est : les frais et les intérêts */
                    $DATA_REMB = array("frais"=>true, "int"=>true);

                    /* amnt est le montant remboursé disponible restant */
                    $amnt = min($mnt, $total_credit);

                    /* Rembourser selon l'odre et les remboursement précisés */
                    $solde_frais = $solde_int = 0;
                    $mnt_remb_frais = $mnt_remb_int = 0;
                    foreach($ORDRE_REMB as $key=>$value) {
                      if ($DATA_REMB[$value] == true) { /* il faut le rembourser si le montant disponible le permet */
                        $ {"mnt_remb_".$value} = min($info["solde_".$value], $amnt);
                        $amnt -= $ {"mnt_remb_".$value};
                        $ {"solde_".$value} = $info["solde_".$value] - $ {"mnt_remb_".$value};
                      }
                    }
                    $id_echeance=$info['id_ech'];

                    $num_rembours = getNextNumRemboursement($info['id_doss'],$id_echeance);
                    
                    if ($mnt_remb_int > 0) {

                        // Insertion du remboursement dans la DB
                        $sql = "INSERT INTO ad_sre(id_doss,id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, mnt_remb_gar) VALUES(".$info['id_doss'].",$global_id_agence,".$num_rembours.",'".$date_remb."',".$id_echeance.",0,$mnt_remb_int,0,0)";

                        $result = $db->query($sql);
                        if (DB::isError($result)) {
                          $dbHandler->closeConnection(false);
                          signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                        }

                        //Met à jour le solde restant dû pour l'échéance
                        updateEchIntTotalLcr($info['id_doss'], $solde_int);
                    }

                    $array_credit = getCompteCptaDcr($id_doss);

                    $cptes_substitue = array();
                    $cptes_substitue["cpta"] = array();
                    $cptes_substitue["int"] = array();

                    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($cpt_liaison);
                    if ($cptes_substitue["cpta"]["debit"] == NULL) {
                      $dbHandler->closeConnection(false);
                      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne du compte de liaison"));
                    }
                    $cptes_substitue["int"]["debit"] = $cpt_liaison;

                    global $global_monnaie;

                    // Get date ech
                    $date_ech = php2pg(pg2phpDate(getDateFinEcheanceLcr($id_doss)));

                    $date_evnt = php2pg($date_remb);
                    if ($date_evnt > $date_ech) {
                        $date_evnt = $date_ech;
                    }

                    /* S'il y a remboursement des frais */
                    if ($mnt_remb_frais > 0) {
                      // Recherche du type d'opération
                      $type_oper = 25;

                      if ($array_credit["cpte_cpta_prod_cr_frais"] == NULL) {
                        $dbHandler->closeConnection(false);
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux frais"));
                      }

                      $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_frais"];

                      //  Passage des écritures comptables
                      // débit client / crédit produit
                      if ($devise != $global_monnaie) {
                              $err = effectueChangePrivate($devise, $global_monnaie, $mnt_remb_frais, $type_oper, $cptes_substitue, $comptable,true,NULL,$id_doss);
                      } else {
                        // Passage des écritures comptables
                        $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_frais, $comptable, $cptes_substitue, $devise, $date_remb,$id_doss);
                      }
                      if ($err->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $err;
                      } else {
                          // Insert lcr event
                          //$date_evnt = php2pg($date_remb);
                          $type_evnt = 4; // Prélèvement frais
                          $nature_evnt = NULL;
                          $login = $global_nom_login;
                          $comments = 'Prélèvement frais de '.afficheMontant($mnt_remb_frais).' '.$devise;

                          $lcrErr = insertLcrHis($id_doss, $date_evnt, $type_evnt, $nature_evnt, $login, $mnt_remb_frais, $id_his, $comments);

                          if ($lcrErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $lcrErr;
                          }
                      }

                      $total_mnt += $mnt_remb_frais;

                      unset($cptes_substitue["cpta"]["credit"]);
                    }

                    /* S'il y a remboursement d'intérêts */
                    if ($mnt_remb_int > 0) {
                      // Recherche du type d'opération
                      $type_oper = get_credit_type_oper(2, 2);

                      if ($array_credit["cpte_cpta_prod_cr_int"] == NULL) {
                        $dbHandler->closeConnection(false);
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux intérêts"));
                      }

                      $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_int"];

                      //  Passage des écritures comptables
                      // débit client / crédit produit
                      if ($devise != $global_monnaie) {
                              $err = effectueChangePrivate($devise, $global_monnaie, $mnt_remb_int, $type_oper, $cptes_substitue, $comptable,true,NULL,$id_doss);
                      } else {
                        // Passage des écritures comptables
                        $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise, $date_remb,$id_doss);
                      }
                      if ($err->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $err;
                      } else {
                          // Insert lcr event
                          //$date_evnt = php2pg($date_remb);
                          $type_evnt = 3; // Remboursement
                          $nature_evnt = 2; // Intérêts
                          $login = $global_nom_login;
                          $comments = 'Remboursement intérêts de '.afficheMontant($mnt_remb_int).' '.$devise;

                          $lcrErr = insertLcrHis($id_doss, $date_evnt, $type_evnt, $nature_evnt, $login, $mnt_remb_int, $id_his, $comments);

                          if ($lcrErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $lcrErr;
                          }
                      }

                      $total_mnt += $mnt_remb_int;

                      unset($cptes_substitue["cpta"]["credit"]);
                    }

                    $myErr = ajout_historique (607, $val_doss['id_client'], 'Prélèvement frais et remboursement intérêts', $global_nom_login, date("r"), $comptable, NULL, $id_his);
                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }

                    $id_his = $myErr->param;
                    
                    $count_dcr++;
                }
            }
        }
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR, array($count_dcr, $total_mnt));
}

/*
 * PS qui effectue le remboursement d'un montant donné pour un crédit donné.
 * Cette fonction doit être appelée depuis l'interface
 * Elle appelle rembourse_montant et s'occupe de l'ajout historique
 */
function rembourse_montantInt_lcr($info_doss, $source, $id_guichet = NULL, $date_remb = NULL)
{
  global $dbHandler;
  global $appli;
  global $global_credit_niveau_retard;
  global $global_nom_login;

  $db = $dbHandler->openConnection();

  foreach($info_doss as $id_doss=>$val_doss) {
    $comptable_his = array();

    if ($date_remb == NULL) {
        $myErr = rembourse_lcr($id_doss, $val_doss['mnt_remb'], $source, $comptable_his, $id_guichet);
    } else {
        $myErr = rembourse_lcr($id_doss, $val_doss['mnt_remb'], $source, $comptable_his, $id_guichet, $date_remb);
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    $INFOSREMB = $myErr->param; // Récupère les valeurs de retour de rembourse

    if ($source == 2) { // Remboursement via le compte lié
      // Perception éventuelle de frais de découvert
      $myErr2 = preleveFraisDecouvert($INFOSREMB["cpt_liaison"], $comptable_his);
      if ($myErr2->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr2;
      }
    }

    $myErr = ajout_historique (607, $val_doss['id_client'], $id_doss.'|'.$myErr->param['id_ech'].'|'.$myErr->param['num_remb'], $global_nom_login, date("r"), $comptable_his, NULL);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    $id_his = $myErr->param;

  }// fin parcours des dossiers

  $dbHandler->closeConnection(true);
  return $myErr;
}

function rembourse_lcr ($id_doss, $mnt, $source, &$comptable, $id_guichet = NULL, $date_remb = NULL) {

  global $global_id_agence;
  global $global_nom_login;
  global $dbHandler;
  global $appli;
  global $global_credit_niveau_retard;
  global $error;
  global $global_monnaie_courante_prec;

  $db = $dbHandler->openConnection();

  /* Récupération des infos sur le dossier de crédit */
  $DCR = getDossierCrdtInfo($id_doss);
  $id_client = $DCR["id_client"];

  /* Récupération des infos sur le produit de crédit associé */
  $Produit = getProdInfo(" where id =".$DCR["id_prod"], $id_doss);
  $PROD = $Produit[0];
  $devise = $PROD["devise"];
  $ORDRE_REMB = $DCR["ordre_remb_lcr"];

  /* Récupération des infos sur la devise du produit */
  $DEV = getInfoDevise($devise);

  /* On autorise pas le remboursement d'un crédit en perte */
  if ($DCR["etat"] == 9) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Le dossier est en perte !"));
  }

  /* Recupération des infos sur le crédit : dernière échéance non remboursée ou partiellement et les remboursements */
  $info = get_info_credit($id_doss, 1);

  /* Récupération du compte de liaison */
  $cpt_liaison = $DCR["cpt_liaison"];

  /* Récupération du total attendu pour la dernière échéance non remboursée ou partiellement remboursée */
  $mnt = round($mnt, $global_monnaie_courante_prec);

  if ($date_remb != NULL) {
      $today = $date_remb;
  } else {
      $today = (date("d/m/Y"));
  }

  $info['solde_int'] = getCalculInteretsLcr($id_doss, php2pg($today));
  $info['solde_frais'] = getCalculFraisLcr($id_doss, php2pg($today));

  $total_credit = round($info['solde_frais'] + $info['solde_cap'] + $info['solde_int'] + $info['solde_pen'] + $info['solde_gar'], $global_monnaie_courante_prec);
//  if ($mnt > $total_credit) {
//    $dbHandler->closeConnection(false);
//    return new ErrorObj(ERR_CRE_MNT_TROP_ELEVE);
//  }

  /* Ordre de remboursement : si aucun ordre n'est spécifié ou $ORDRE_REMB = 1, alors on considère qu'il faut rembourser respectivement :
         - les frais
         - les garanties
         - les pénalités
         - les intérêst
         - le  capital,
         qui est l'ordre par défaut
  */
  if ($ORDRE_REMB == 2) {
        // Frais -> Garantie -> capital -> intérêt -> pénalité
  	$ORDRE_REMB = array("frais", "gar", "cap", "int", "pen");
  } elseif($ORDRE_REMB == 3) {
        // Frais -> Garantie -> intérêt -> capital -> pénalité
  	$ORDRE_REMB = array("frais", "gar", "int", "cap", "pen");
  } elseif($ORDRE_REMB == 4) {
        // Frais -> Garantie -> intérêt -> pénalité -> capital
  	$ORDRE_REMB = array("frais", "gar", "int", "pen", "cap");
  } elseif($ORDRE_REMB == 5) {
        // Frais -> Intérêt -> pénalité -> capital -> garantie
  	$ORDRE_REMB = array("frais", "int", "pen", "cap", "gar");
  } elseif($ORDRE_REMB == 6) {
        // Frais -> Intérêt -> capital -> pénalité -> garantie
  	$ORDRE_REMB = array("frais", "int", "cap", "pen", "gar");
  } elseif($ORDRE_REMB == 7) {
        // Frais -> Pénalité -> intérêt -> capital -> garantie
  	$ORDRE_REMB = array("frais", "pen", "int", "cap", "gar");
  } elseif($ORDRE_REMB == 8) {
        // Frais -> Capital -> intérêt -> pénalité -> garantie
  	$ORDRE_REMB = array("frais", "cap", "int", "pen", "gar");
  } else {
        // Frais -> Garantie -> pénalité -> intérêt -> capital
        $ORDRE_REMB = array("frais", "gar", "pen", "int", "cap");
  }

  /* Si DATA_REMB est null, on considère qu'on veut tout rembourser: les frais, les garanties, les pénalités, les intérêts et le capital */
  $DATA_REMB = array("frais"=>true, "gar"=>true, "pen"=>true, "int"=>true, "cap"=>true);

  /* amnt est le montant remboursé disponible restant */
  $amnt = min($mnt, $total_credit);

  /* Rembourser selon l'odre et les remboursement précisés */
  $solde_frais = $solde_cap = $solde_int = $solde_gar = $solde_pen = 0;
  $mnt_remb_frais = $mnt_remb_cap = $mnt_remb_int = $mnt_remb_gar = $mnt_remb_pen = 0;
  foreach($ORDRE_REMB as $key=>$value) {
    if ($DATA_REMB[$value] == true) { /* il faut le rembourser si le montant disponible le permet */
      $ {"mnt_remb_".$value} = min($info["solde_".$value], $amnt);
      $amnt -= $ {"mnt_remb_".$value};
      $ {"solde_".$value} = $info["solde_".$value] - $ {"mnt_remb_".$value};
    }
  }
  $id_echeance=$info['id_ech'];

  $num_rembours = getNextNumRemboursement($info['id_doss'],$id_echeance);
  // Insertion du remboursement dans la DB
  
  if ($mnt_remb_cap > 0 || $mnt_remb_int > 0 || $mnt_remb_pen > 0 || $mnt_remb_gar > 0) {

    if ($date_remb == NULL) {
      $sql = "INSERT INTO ad_sre(id_doss,id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, ";
      $sql .= "mnt_remb_gar) VALUES(".$info['id_doss'].",$global_id_agence,".$num_rembours.",'".date("d/m/Y")."',".$id_echeance.",$mnt_remb_cap,$mnt_remb_int,$mnt_remb_pen,$mnt_remb_gar)";
    } else {
      $sql = "INSERT INTO ad_sre(id_doss,id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, ";
      $sql .= "mnt_remb_gar) VALUES(".$info['id_doss'].",$global_id_agence,".$num_rembours.",'".$date_remb."',".$id_echeance.",$mnt_remb_cap,$mnt_remb_int,$mnt_remb_pen,$mnt_remb_gar)";
    }
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    }

    //Met à jour le solde restant dû pour l'échéance
    $sql = "UPDATE ad_etr SET mnt_cap=$solde_cap, mnt_int=$solde_int, solde_cap=$solde_cap, solde_int=$solde_int, solde_pen=$solde_pen, solde_gar=$solde_gar WHERE (id_ag=$global_id_agence) AND (id_doss=".$info['id_doss'].") AND (id_ech=". $id_echeance.")";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
    }
  }

  //Réalise les débits/crédits
  $id_cpt_credit = $info['id_cpt_credit'];
  $id_cpt_epargne_nantie = $info['id_cpt_epargne_nantie']; /* Compte d'épargne des garanties encours */

  $array_credit = getCompteCptaDcr($id_doss);

  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  if ($source == 2) { // Source = compte lié
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($cpt_liaison);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne du compte de liaison"));
    }
    $cptes_substitue["int"]["debit"] = $cpt_liaison;
  }

  /* S'il y a remboursement de garanties*/
  if ($mnt_remb_gar > 0) {
    // Recherche du type d'opération
    $type_oper = get_credit_type_oper(9, $source);
    // Passage des écritures comptables
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpt_epargne_nantie);

    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      //Ici, on renvoie l'erreur pertinente au produit de crédit et non au produit d'épargne
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("Garantie associée au produit de crédit : "));
    }

    $cptes_substitue["int"]["credit"] = $id_cpt_epargne_nantie;

    if ($date_remb == NULL) {
      $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_gar, $comptable, $cptes_substitue, $devise,NULL,$id_doss);
    } else {
    	$err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_gar, $comptable, $cptes_substitue, $devise, $date_remb,$id_doss);
    }
    if ($err->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $err;
    }
    unset($cptes_substitue["cpta"]["credit"]);
    unset($cptes_substitue["int"]["credit"]);

    // Pour un remboursement par la garantie pas de MAJ dans ce sens
    if($id_cpte_gar = NULL){
    	/* Mise à jour des garanties en cours dans la table des garanties du dossier */
	    if ($DCR['cpt_gar_encours'] != '') {
	      $sql = "UPDATE ad_gar SET montant_vente=montant_vente + $mnt_remb_gar WHERE (id_ag=$global_id_agence) AND gar_num_id_cpte_nantie=".$DCR['cpt_gar_encours'];
	      $result = $db->query($sql);
	      if (DB::isError($result)) {
	        $dbHandler->closeConnection(false);
	        signalErreur(__FILE__,__LINE__,__FUNCTION__);
	      }
	      $infos_gar = getInfosCpteGarEncours($DCR['cpt_gar_encours']);
	      //S'il reste encore des garanties à mobiliser remettre l'état_gar à 1(encours de mobilisation)
	      if($infos_gar['montant_vente'] > 0){
	      	$sql = "UPDATE ad_gar SET etat_gar = 1 WHERE (id_ag=$global_id_agence) AND gar_num_id_cpte_nantie=".$DCR['cpt_gar_encours'];
		      $result = $db->query($sql);
		      if (DB::isError($result)) {
		        $dbHandler->closeConnection(false);
		        signalErreur(__FILE__,__LINE__,__FUNCTION__);
		      }
	      }
	    }
    }
  }

  global $global_monnaie;
  
  // Get date ech
  $date_ech = php2pg(pg2phpDate(getDateFinEcheanceLcr($id_doss)));

  /* S'il y a remboursement des frais */
  if ($mnt_remb_frais > 0) {
    // Recherche du type d'opération
    $type_oper = 25;

    if ($array_credit["cpte_cpta_prod_cr_frais"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux frais"));
    }

    $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_frais"];

    //  Passage des écritures comptables
    // débit client / crédit produit
    if ($devise != $global_monnaie) {
            $err = effectueChangePrivate($devise, $global_monnaie, $mnt_remb_frais, $type_oper, $cptes_substitue, $comptable,true,NULL,$id_doss);
    } else {
      // Passage des écritures comptables
      if ($date_remb == NULL) {
        $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_frais, $comptable, $cptes_substitue, $devise,NULL,$id_doss);
      } else {
      	  $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_frais, $comptable, $cptes_substitue, $devise, $date_remb,$id_doss);
      }
    }
    if ($err->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $err;
    } else {
        // Insert lcr event
        $date_evnt = php2pg(($date_remb == NULL)?(date("d/m/Y")):$date_remb);
        if ($date_evnt > $date_ech) {
            $date_evnt = $date_ech;
        }
        $type_evnt = 4; // Prélèvement frais
        $nature_evnt = NULL;
        $login = $global_nom_login;
        $id_his = NULL;
        $comments = 'Prélèvement frais de '.afficheMontant($mnt_remb_frais).' '.$devise;

        $lcrErr = insertLcrHis($id_doss, $date_evnt, $type_evnt, $nature_evnt, $login, $mnt_remb_frais, $id_his, $comments);

        if ($lcrErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $lcrErr;
        }
    }

    unset($cptes_substitue["cpta"]["credit"]);
  }

  /* S'il y a remboursement de pénalités */
  if ($mnt_remb_pen > 0) {
    // Recherche du type d'opération
    $type_oper = get_credit_type_oper(3, $source);

    if ($array_credit["cpte_cpta_prod_cr_pen"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux pénélités"));
    }

    $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_pen"];
    // Passage des écritures comptables
    // Si la devise du crédit n'est pas la devise de référence, mouvementer la position de change
    if ($devise != $global_monnaie) {
      $err = effectueChangePrivate($devise, $global_monnaie, $mnt_remb_pen, $type_oper, $cptes_substitue, $comptable,true,NULL,$id_doss);
    } else {
      // Passage des écritures comptables
      if ($date_remb == NULL) {
        $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_pen, $comptable, $cptes_substitue, $devise,NULL,$id_doss);
      } else {
      	  $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_pen, $comptable, $cptes_substitue, $devise, $date_remb,$id_doss);
      }
    }

    if ($err->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $err;
    }
    unset($cptes_substitue["cpta"]["credit"]);
  }

  /* S'il y a remboursement d'intérêts */
  if ($mnt_remb_int > 0) {
    // Recherche du type d'opération
    $type_oper = get_credit_type_oper(2, $source);

    if ($array_credit["cpte_cpta_prod_cr_int"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux intérêts"));
    }

    $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_int"];

    //  Passage des écritures comptables
    // débit client / crédit produit
    if ($devise != $global_monnaie) {
            $err = effectueChangePrivate($devise, $global_monnaie, $mnt_remb_int, $type_oper, $cptes_substitue, $comptable,true,NULL,$id_doss);
    } else {
      // Passage des écritures comptables
      if ($date_remb == NULL) {
        $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise,NULL,$id_doss);
      } else {
      	  $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise, $date_remb,$id_doss);
      }
    }
    if ($err->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $err;
    } else {
        // Insert lcr event
        $date_evnt = php2pg(($date_remb == NULL)?(date("d/m/Y")):$date_remb);
        if ($date_evnt > $date_ech) {
            $date_evnt = $date_ech;
        }
        $type_evnt = 3; // Remboursement
        $nature_evnt = 2; // Intérêts
        $login = $global_nom_login;
        $id_his = NULL;
        $comments = 'Remboursement intérêts de '.afficheMontant($mnt_remb_int).' '.$devise;

        $lcrErr = insertLcrHis($id_doss, $date_evnt, $type_evnt, $nature_evnt, $login, $mnt_remb_int, $id_his, $comments);

        if ($lcrErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $lcrErr;
        }
    }

    unset($cptes_substitue["cpta"]["credit"]);
  }

  /* S'il y a remboursemnt de capital */
  if ($mnt_remb_cap > 0) {
    // Recherche du type d'opération
    $type_oper = get_credit_type_oper(1, $source);

    // Passage des écritures comptables
    // Débit client / crédit compte de crédit
    // Recherche du compte comptable associé au crédit en fonction de son état
    $CPTS_ETAT = recup_compte_etat_credit($DCR["id_prod"]);
    $cptes_substitue["cpta"]["credit"] = $CPTS_ETAT[$DCR["cre_etat"]];
    $cptes_substitue["int"]["credit"] = $id_cpt_credit;

    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit de crédit"));
    }
    if ($date_remb == NULL) {
      $err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_cap, $comptable,$cptes_substitue, $devise,NULL,$id_doss);
    } else {
    	$err = passageEcrituresComptablesAuto($type_oper, $mnt_remb_cap, $comptable,$cptes_substitue, $devise, $date_remb,$id_doss);
    }
    if ($err->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $err;
    } else {
        // Insert lcr event
        $date_evnt = php2pg(($date_remb == NULL)?(date("d/m/Y")):$date_remb);
        $type_evnt = 3; // Remboursement
        $nature_evnt = 1; // Capital
        $login = $global_nom_login;
        $id_his = NULL;
        $comments = 'Remboursement capital de '.afficheMontant($mnt_remb_cap).' '.$devise;

        $lcrErr = insertLcrHis($id_doss, $date_evnt, $type_evnt, $nature_evnt, $login, $mnt_remb_cap, $id_his, $comments);

        if ($lcrErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $lcrErr;
        }
    }
  }

  // Set crédit soldé
  if ($solde_cap == 0 && $solde_int == 0 && $solde_pen == 0 && $solde_gar == 0 && $solde_frais == 0) {
        // Insert lcr event
        $date_evnt = date('d/m/Y');
        $type_evnt = 7; // Soldé
        $nature_evnt = NULL;
        $login = $global_nom_login;
        $id_his = NULL;
        $comments = 'Crédit soldé le ' . $date_evnt;
      //Evolution pp161 :kheshan
      $date_ech = php2pg(pg2phpDate(getDateFinEcheanceLcr($id_doss)));
      //si on est dans le periode de nettoyage

     /* var_dump(php2pg($date_evnt));
      var_dump(isPeriodeNettoyageLcr($id_doss, $DCR["duree_nettoyage_lcr"]));
      var_dump($DCR["deboursement_autorisee_lcr"]);
      var_dump($date_ech);*/

      If (isPeriodeNettoyageLcr($id_doss, $DCR["duree_nettoyage_lcr"]) AND (php2pg($date_evnt) < $date_ech) AND $DCR["deboursement_autorisee_lcr"] == 'f'){ //in period de nettoyage
          //echo 'En periode nettoyage';
          $comments .= ' (En période nettoyage)';
          }
      elseif((php2pg($date_evnt) >= $date_ech)){//a la fin de lécheance
          //echo 'event > echeance';
          $comments .= ' (Fin échéance)';
      }

        $lcrErr = insertLcrHis($id_doss, php2pg($date_evnt), $type_evnt, $nature_evnt, $login, 0, $id_his, $comments);

        if ($lcrErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $lcrErr;
        } else {
            $date_ech = php2pg(pg2phpDate(getDateFinEcheanceLcr($id_doss)));
            //Evolution pp161 :kheshan
            $date_debut_nettoyage = calculDateDureeMois($date_ech, -$DCR["duree_nettoyage_lcr"]);
            //Si credit soldé durant periode de nettoyage
            if((isPeriodeNettoyageLcr($id_doss, $DCR["duree_nettoyage_lcr"]) AND (php2pg($date_evnt) < $date_ech) AND $DCR["deboursement_autorisee_lcr"] == 'f')){// en periode nettoyage
                // mise à jour echeancier theorique
                $sql = "UPDATE ad_etr SET remb='t' WHERE id_ag=$global_id_agence AND id_doss=".$id_doss." AND id_ech=1;";
                $result=$db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                }
            }
            elseif((php2pg($date_evnt) >= $date_ech)){//credit solder  a la fin echeancier
                // mise à jour echeancier theorique
                $sql = "UPDATE ad_etr SET remb='t' WHERE id_ag=$global_id_agence AND id_doss=".$id_doss." AND id_ech=1;";
                $result=$db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
                }


            }
        }
  }

  // Valeurs qui seront renvoyées à la fonction appelante
  $RET = array();
  $RET['result'] = 1;
  $RET['id_ech'] =  $id_echeance;
  $RET['num_remb'] = $info['nbre_remb']+1;
  $RET["mnt_remb_pen"] = $mnt_remb_pen;
  $RET["mnt_remb_gar"] = $mnt_remb_gar;
  $RET["mnt_remb_int"] = $mnt_remb_int;
  $RET["mnt_remb_cap"] = $mnt_remb_cap;
  $RET["cpt_liaison"] = $cpt_liaison;
  $RET["cpt_en"] = $id_cpt_epargne_nantie;


  // S'il y a lieu, reclasser le crédit (passage souffrance -> sain)

  // Recherche de l'ancien état du dossier de crédit
  $oldEtat = $info["cre_etat"];

  // Recherche du nouvel état
  // Pour ce faire, on va calculer le nombre de jours de retard
  $sql = "SELECT date_ech FROM ad_etr WHERE id_ag = $global_id_agence AND id_doss = $id_doss AND (remb='f') ORDER BY date_ech";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
  }
  $numrows=$result->numrows();
  $newEtat = 0;
  $etat=$oldEtat;

  if ($numrows == 0) { // Si toutes les échéances sont remboursées
    // Le crédit passe à l'état soldé
    if (echeancierRembourse($id_doss)) {
      $myErr = soldeCredit($id_doss, $comptable); //Mettre l'état du crédit à soldé
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
      $RETSOLDECREDIT = $myErr->param;
    }
    
  } //end if crédit soldé
  else
  { //échéances à traiter
    $echeance = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $date = pg2phpDatebis($echeance["date_ech"]);
    $nbre_secondes = gmmktime(0,0,0,$date[0], $date[1],$date[2]) - gmmktime(0,0,0,date("m"), date("d"), date("Y"));
    $etatAvance = calculeEtatPlusAvance($id_doss);

    if ($nbre_secondes>=0) { // Le crédit est à nouveau sain
        $newEtat=1;
        if ($date_remb == NULL) {
          $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat = '".date("d/m/Y")."' WHERE id_ag = $global_id_agence AND id_doss = $id_doss";
        } else {
           $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat = '".$date_remb."', cre_retard_etat_max = $etatAvance WHERE id_ag=$global_id_agence AND id_doss = $id_doss";
        }
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
        }
    } else {
      $nbre_jours = $nbre_secondes/(3600*24);
      $nbre_jours = $nbre_jours * (-1);
      $newEtat = calculeEtatCredit($nbre_jours);

      // Cas particulier où cette fonction a été appelée par le batch
      // lors du passage en perte.
      // Dans ce cas, on reste en souffrance. C'est le batch qui se chargera du passage
      // en perte (via la fonction passagePerte)

      $id_etat_perte = getIDEtatPerte();

      if ($newEtat == $id_etat_perte) {
        $newEtat -= 1; // FIXME A revoir, il peut y avoir des trous !
      }

      // Mise à jour si nécessaire
      if ($oldEtat != $newEtat) {
        if ($date_remb == NULL) {
          $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat =  '".date("d/m/Y")."' WHERE id_ag = $global_id_agence AND id_doss = $id_doss";
        } else {
            $sql = "UPDATE ad_dcr SET cre_mnt_deb = $solde_cap, cre_etat = $newEtat,cre_date_etat =  '".$date_remb."', cre_retard_etat_max = $etatAvance WHERE id_ag = $global_id_agence AND id_doss = $id_doss";
        }
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
        }
      }
    }//end else new état crédit

    // Reclassement du crédit si nécessaire en comptabilité
    $myErr = placeCapitalCredit($id_doss,$oldEtat,$newEtat, $comptable, $devise);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$error[$myErr->errCode].$myErr->param);
      return $myErr;
    }

    // Gestion de l'alerte
    if ($appli!="batch") {
      if (is_array($global_credit_niveau_retard)) {
        $etat_plus_avance = array_keys($global_credit_niveau_retard);
        if ($newEtat > $etat_plus_avance[0] ) {
          unset($global_credit_niveau_retard[$etat_plus_avance[0]]);
          $global_credit_niveau_retard[$newEtat] = array();
          array_push($global_credit_niveau_retard[$newEtat], $id_doss);
        }
        elseif($newEtat == $etat_plus_avance ) {
          array_push($global_credit_niveau_retard[$etat_plus_avance], $id_doss);
        }
      } else {
        $global_credit_niveau_retard[$newEtat] = array();
        array_push($global_credit_niveau_retard[$newEtat],$id_doss);
      }
    }

  }//end échéances à traiter

  // Ajout dans le tableau $RET de $RETSOLDECREDIT si le crédit a été soldé
  if (is_array($RETSOLDECREDIT)) {
    $RET["RETSOLDECREDIT"] = $RETSOLDECREDIT;
  }

  // #357 - équilibre inventaire - comptabilité
  $cre_id_cpte = $DCR['cre_id_cpte'];
    
  if($appli!="batch" && !empty($cre_id_cpte)) {
  	$myErr = setNumCpteComptableForCompte($cre_id_cpte, $db);
  }
  // Fin : #357 - équilibre inventaire - comptabilité
  
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $RET);
}