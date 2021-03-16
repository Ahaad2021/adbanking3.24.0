<?php

/**
 * @package Annulation retrait et dépôt
 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/VariablesGlobales.php';

/**
 * Description de la classe AnnulationRetraitDepot
 *
 * @author BD
 */
class AnnulationRetraitDepot
{
    // Les opérations financières
    const OPE_RETRAIT = 70;
    const OPE_DEPOT = 75;
    const OPE_RETRAIT_EXPRESS = 85;
    const OPE_DEPOT_EXPRESS = 86;

    // Les états d'annulations (ad_annulation_retrait_depot.etat_annul)
    const ETAT_ANNUL_TOUS = 0;
    const ETAT_ANNUL_ENREGISTRE = 1;
    const ETAT_ANNUL_AUTORISE = 2;
    const ETAT_ANNUL_REJETE = 3;
    const ETAT_ANNUL_EFFECTUE = 4;
    const ETAT_ANNUL_SUPPRIME = 5;

    /**
     * Enregistrer une demande d'annulation retrait et dépôt
     *
     * @param Integer $id_his
     * @param Integer $id_client
     * @param Integer $fonc_sys
     * @param Integer $type_ope
     * @param Double $montant
     * @param String $devise
     * @param Integer $etat_annul
     * @param String $comments
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function insertAnnulationRetraitDepot($id_his, $id_client, $fonc_sys, $type_ope = null, $montant = null, $frais= null, $devise = null, $etat_annul = AnnulationRetraitDepot::ETAT_ANNUL_ENREGISTRE, $comments = 'Demande annulation : Enregistré')
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $db = $dbHandler->openConnection();

        $tableFields = array(
            "id_his" => $id_his,
            "id_client" => $id_client,
            "fonc_sys" => $fonc_sys,
            "type_ope" => $type_ope,
            "montant" => recupMontant($montant),
            "devise" => $devise,
            "etat_annul" => $etat_annul,
            "comments" => trim($comments),
            "id_ag" => $global_id_agence,
            "login" => $global_nom_login,
            "frais"=>recupMontant($frais),
        );
        $sql = buildInsertQuery("ad_annulation_retrait_depot", $tableFields);

        $result = $db->query($sql);

        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Récupère une liste de retraits et dépôts du jour
     *
     * @param int $id_client
     *
     * @return array|null
     */
    public static function getListeOperationEpargne($id_client)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT h.id_his, h.type_fonction, e.type_operation, m.montant, h.date, m.devise, m.cpte_interne_cli FROM ad_his h INNER JOIN ad_ecriture e ON h.id_his=e.id_his INNER JOIN ad_mouvement m ON e.id_ecriture=m.id_ecriture WHERE h.id_ag = $global_id_agence AND h.id_client = $id_client AND e.type_operation IN (140,160,500,503,508,511,512,532) AND h.id_his NOT IN (SELECT id_his FROM ad_annulation_retrait_depot WHERE id_ag = $global_id_agence AND id_client = $id_client AND etat_annul IN (1,2,3,4,5)) AND h.type_fonction IN (70,75,85,86) AND to_char(h.date, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') AND m.date_valeur BETWEEN date(now()) - 10 AND date(now()) AND m.cpte_interne_cli is not null ORDER BY h.id_his ASC;";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($result->numRows() == 0) {
            $dbHandler->closeConnection(true);

            return NULL;
        }

        $tmp_arr = array();

        while ($ListOpe = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

            $tmp_arr[$ListOpe['id_his']] = $ListOpe;
        }

        $dbHandler->closeConnection(true);

        return $tmp_arr;
    }

    /**
     * Vérifié si une liste de retraits ou dépôts existe pour ce client
     *
     * @param int $id_client
     *
     * @return boolean
     */
    public static function hasOperationEpargne($id_client)
    {
        return (count(AnnulationRetraitDepot::getListeOperationEpargne($id_client)) > 0 ? true : false);
    }

    /**
     * Récupère une liste de demandes d'annulation
     *
     * @param null|int $id_client
     * @param int $etat_annul 0:Tous / 1:enregistré / 2:autorisé / 3:rejeté / 4:effectué / 5:supprimé
     *
     * @return array|null
     */
    public static function getListeDemandeAnnulation($id_client = null, $etat_annul = 1)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT * FROM ad_annulation_retrait_depot WHERE id_ag = $global_id_agence ";

        $sql .= " AND to_char(date_crea, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') ";

        if ($id_client != null) {
            $sql .= " AND id_client = $id_client ";
        }

        if ($etat_annul != 0) {
            $sql .= " AND etat_annul = $etat_annul ";
        }

        $sql .= " ORDER BY id ASC";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($result->numRows() == 0) {
            $dbHandler->closeConnection(true);

            return NULL;
        }

        $tmp_arr = array();

        while ($ListDemande = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
            $tmp_arr[$ListDemande['id']] = $ListDemande;
        }

        $dbHandler->closeConnection(true);

        return $tmp_arr;
    }

    /**
     * Vérifié si il y a une demande d'annulation
     *
     * @param int $id_client
     * @param int $etat_annul 0:Tous / 1:enregistré / 2:autorisé / 3:rejeté / 4:effectué / 5:supprimé
     *
     * @return boolean
     */
    public static function hasDemandeAnnulation($id_client, $etat_annul = 1)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT id_client FROM ad_annulation_retrait_depot WHERE id_ag = $global_id_agence AND id_client = $id_client ";

        $sql .= " AND to_char(date_crea, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') ";

        if ($etat_annul != 0) {
            $sql .= " AND etat_annul = $etat_annul ";
        }

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $tmpRow = $result->fetchrow();

        $dbHandler->closeConnection(true);

        return ($tmpRow[0]) ? true : false;
    }

    /**
     * Vérifié si une demande d'annulation existe pour ce client
     *
     * @param int $id_client
     *
     * @return boolean
     */
    public static function hasDemandeAnnulationEnregistre($id_client)
    {
        return AnnulationRetraitDepot::hasDemandeAnnulation($id_client, 1);
    }

    /**
     * Vérifié si une autorisation d'annulation existe pour ce client
     *
     * @param int $id_client
     *
     * @return boolean
     */
    public static function hasDemandeAnnulationAutorise($id_client)
    {
        return AnnulationRetraitDepot::hasDemandeAnnulation($id_client, 2);
    }

    /**
     * Mettre à jour le statut d'une demande annulation de retrait et dépôt
     *
     * @param int $id_demande
     * @param int $etat_annul
     * @param string $comments
     * @param null|int $id_his
     *
     * @return ErrorObj
     */
    public static function updateEtatAnnulationRetraitDepot($id_demande, $etat_annul, $id_his = null, $date_annul = null, $comments = '')
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $tableFields = array(
            "etat_annul" => $etat_annul,
            "annul_id_his" => $id_his,
            "date_modif" => date("r"),
            "date_annul" => $date_annul,
            "comments" => trim($comments)
        );

        $sql_update = buildUpdateQuery("ad_annulation_retrait_depot", $tableFields, array('id' => $id_demande, 'id_ag' => $global_id_agence));

        $result = $db->query($sql_update);

        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Traitement des opérations de retrait et dépôt
     *
     * @param $_POST $data
     * @param null|int $id_client
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function processOperationEpargne($data, $id_client = null)
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $demande_count = 0;
        if (isset($data['btn_process_demande'])) {

            // Get liste de retraits et dépôts du jour
            $listeOpeEpg = AnnulationRetraitDepot::getListeOperationEpargne($id_client);

            if (is_array($listeOpeEpg) && count($listeOpeEpg) > 0) {

                $db = $dbHandler->openConnection();

                foreach ($listeOpeEpg as $id => $opeEpg) {

                    $id_trans = trim($opeEpg["id_his"]);


                    if (isset($data['check_valid_' . $id_trans])) {

                        $frais = AnnulationRetraitDepot::getFraisOpe(trim($opeEpg["id_his"]),$opeEpg["type_operation"],trim($opeEpg["cpte_interne_cli"]));
                        $fonc_sys = trim($opeEpg["type_fonction"]);
                        $type_ope = trim($opeEpg["type_operation"]);
                        $montant = trim($opeEpg["montant"]);
                        $devise = trim($opeEpg["devise"]);
                        $frais_retrait_depot= trim($frais["montant"]);

                        // Enregistrer une demande d'annulation retrait et dépôt
                        $erreur = AnnulationRetraitDepot::insertAnnulationRetraitDepot($id_trans, $id_client, $fonc_sys, $type_ope, $montant,$frais_retrait_depot, $devise);

                        if ($erreur->errCode == NO_ERR) {
                            $demande_count++;
                        } else {
                            $dbHandler->closeConnection(false);
                            return $erreur;
                        }
                    }
                }

                if ($demande_count > 0) {

                    $type_fonction = 61; // Demande annulation retrait / dépôt
                    $myErr = ajout_historique($type_fonction, $id_client, "Demande annulation retrait / dépôt", $global_nom_login, date("r"));

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }
                }

                // Commit
                $dbHandler->closeConnection(true);
            }
        }

        return new ErrorObj(NO_ERR, $demande_count);
    }

    /**
     * Traitement des demandes annulation retrait et dépôt
     *
     * @param $_POST $data
     * @param null|int $id_client
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function processDemandeAnnulation($data, $id_client = null)
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $demande_count = 0;

        if (isset($data['btn_process_approbation'])) {

            // Get liste des demandes d'annulation
            $listeDemandeAnnulation = AnnulationRetraitDepot::getListeDemandeAnnulation($id_client);

            if (is_array($listeDemandeAnnulation) && count($listeDemandeAnnulation) > 0) {

                $db = $dbHandler->openConnection();

                foreach ($listeDemandeAnnulation as $id => $demandeAnnulation) {

                    $isValidationOK = false;
                    $isAutorisationOK = false;

                    $id_demande = trim($demandeAnnulation["id"]);

                    if (isset($data['check_valid_' . $id_demande])) {

                        $isValidationOK = true;
                        $isAutorisationOK = true;

                    } elseif (isset($data['check_rejet_' . $id_demande])) {

                        $isValidationOK = true;
                    }

                    if ($isValidationOK == true) {

                        // Mettre à jour le statut d'une demande d'annulation à Autorisé / Rejeté
                        $erreur = AnnulationRetraitDepot::updateEtatAnnulationRetraitDepot($id_demande, (($isAutorisationOK) ? AnnulationRetraitDepot::ETAT_ANNUL_AUTORISE : AnnulationRetraitDepot::ETAT_ANNUL_REJETE), null, null, sprintf("Demande approbation annulation : %s", (($isAutorisationOK) ? "Autorisé" : "Rejeté")));

                        if ($erreur->errCode == NO_ERR) {
                            $demande_count++;
                        } else {
                            $dbHandler->closeConnection(false);
                            return $erreur;
                        }
                    }
                }

                if ($demande_count > 0) {

                    $type_fonction = 62; // Approbation demande annulation retrait / dépôt

                    $myErr = ajout_historique($type_fonction, $id_client, "Approbation demande annulation retrait / dépôt", $global_nom_login, date("r"));

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }
                }

                // Commit
                $dbHandler->closeConnection(true);
            }
        }

        return new ErrorObj(NO_ERR, $demande_count);
    }

    /**
     * Récupère les mouvements d'une historisation à inverser
     *
     * @param int $id_his
     *
     * @return array|null
     */
    public static function getListeOpeEpgDetail($id_his)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT z.id_his, z.id_ecriture, z.type_operation, z.info_ecriture, MAX (compte_credit) AS compte_credit, MAX(cpte_interne_cli_credit) AS cpte_interne_cli_credit, MAX (compte_debit) AS compte_debit, MAX(cpte_interne_cli_debit) AS cpte_interne_cli_debit, MAX (montant) AS montant, MAX (devise) AS devise FROM (SELECT e.id_his, e.id_ecriture, e.type_operation, e.info_ecriture, CASE WHEN sens = 'c' THEN compte END AS compte_credit, CASE WHEN sens = 'c' THEN cpte_interne_cli END AS cpte_interne_cli_credit, CASE WHEN sens = 'd' THEN compte END AS compte_debit, CASE WHEN sens = 'd' THEN cpte_interne_cli END AS cpte_interne_cli_debit, CASE WHEN sens = 'd' THEN montant END AS montant, CASE WHEN sens = 'd' THEN devise END AS devise FROM ad_ecriture e INNER JOIN ad_mouvement M ON M .id_ecriture = e.id_ecriture WHERE e.id_ag = $global_id_agence AND e.id_his = $id_his) z GROUP BY z.id_his, z.id_ecriture, z.type_operation, z.info_ecriture ORDER BY z.id_ecriture DESC;";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($result->numRows() == 0) {
            $dbHandler->closeConnection(true);

            return NULL;
        }

        $tmp_arr = array();

        while ($listHis = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

            $tmp_arr[$listHis['id_ecriture']] = $listHis;
        }

        $dbHandler->closeConnection(true);

        return $tmp_arr;
    }

    /**
     * Traitement des demandes approbation annulation retrait et dépôt
     *
     * @param int $id_demande
     * @param null|int $id_client
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function processApprobationAnnulation($id_demande, $id_client = null)
    {
        global $dbHandler, $global_id_agence, $global_nom_login, $global_id_guichet;

        $db = $dbHandler->openConnection();

        $infoDemande = AnnulationRetraitDepot::getDemandeAnnulation($id_demande, $id_client);

        $demande_count = 0;

        if (is_array($infoDemande) && count($infoDemande) > 0) {

            $id_his_ope = $infoDemande["id_his"];
            $id_client = $infoDemande["id_client"];
            $login = $infoDemande["login"];
            $fonc_sys_inv = AnnulationRetraitDepot::getInverseFoncSys($infoDemande["fonc_sys"]);

            // Vérifié si le login ayant fait l'opération est celui connecté actuellement
            $logged_logins = logged_logins();
            if ($global_nom_login != $login && (is_array($logged_logins)) && (in_array($login, $logged_logins))) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_GUICHET_OUVERT);
            }

            $listeOpeEpgDetail = AnnulationRetraitDepot::getListeOpeEpgDetail($id_his_ope);

            if (is_array($listeOpeEpgDetail) && count($listeOpeEpgDetail) > 0) {

                $comptable = array();
                $curr_date = date("r");
                $annul_remb_lcr = array();

                foreach ($listeOpeEpgDetail as $id_ecriture => $opeEpg) {
                    //debut ticket 739 : date detaillée avec précision millisecondes
                    $utimestamp = microtime(true);

                    $timestamp = floor($utimestamp);

                    $milliseconds = round(($utimestamp - $timestamp) * 1000000);

                    $format = 'Y-m-d H:i:s.u';

                    $curr_date_frais_attente = date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp); //date specific pour insertion dans ad_frais_attente
                    //fin ticket 739 : date detaillée avec précision millisecondes

                    $id_ecriture_ope = $opeEpg["id_ecriture"];
                    $type_ope = $opeEpg["type_operation"];
                    $type_ope_inv = AnnulationRetraitDepot::getInverseOpe($opeEpg["type_operation"]);
                    $compte_a_debite = $opeEpg["compte_credit"];
                    $cpte_interne_cli_a_debite = $opeEpg["cpte_interne_cli_credit"];
                    $compte_a_credite = $opeEpg["compte_debit"];
                    $cpte_interne_cli_a_credite = $opeEpg["cpte_interne_cli_debit"];
                    $montant = recupMontant($opeEpg["montant"]);
                    $devise = $opeEpg["devise"];

                    // Annulation Remboursement capital sur crédits
                    if($type_ope == 10 && $type_ope_inv == 11) {

                        $annul_remb_lcr[] = array(
                            'id_doss' => (int)$opeEpg["info_ecriture"],
                            'id_ech' => 1,
                            'montant' => $montant,
                        );
                    } elseif (in_array($type_ope, array(10, 50, 140, 512, 532, 511, 130, 131, 152, 160, 500, 503, 508, 510, 150, 151, 450, 473, 474))) {
                        $cptes_substitue = array();
                        $cptes_substitue["cpta"] = array();
                        $cptes_substitue["int"] = array();

                        if ($compte_a_debite != null) {
                            $cptes_substitue["cpta"]["debit"] = $compte_a_debite;
                            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                                $dbHandler->closeConnection(false);
                                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
                            }
                        }

                        if ($cpte_interne_cli_a_debite != null) {
                            $cptes_substitue["int"]["debit"] = $cpte_interne_cli_a_debite;
                        }

                        if ($infoDemande["type_ope"]==532 && $type_ope==532 && $cpte_interne_cli_a_credite != null) {

                                    $cpte_interne_cli_a_credite = AnnulationRetraitDepot::getCompteChequeCertifie($cpte_interne_cli_a_credite,$montant);

                                    $compte_a_credite = getCompteCptaProdEp($cpte_interne_cli_a_credite);

                        }

                        if ($compte_a_credite != null) {
                            $cptes_substitue["cpta"]["credit"] = $compte_a_credite;
                            if ($cptes_substitue["cpta"]["credit"] == NULL) {
                                $dbHandler->closeConnection(false);
                                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé"));
                            }
                        }

                        if ($cpte_interne_cli_a_credite != null) {
                            $cptes_substitue["int"]["credit"] = $cpte_interne_cli_a_credite;
                        }

                        // Passage des écritures comptables
                        $myErr = passageEcrituresComptablesAuto($type_ope_inv, $montant, $comptable, $cptes_substitue, $devise, null, $id_ecriture_ope);

                        if ($myErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $myErr;
                        } else {
                            if ($type_ope == 50 && $type_ope_inv == 51 && $cpte_interne_cli_a_credite != null) {

                                // Création dans la table des frais en attente
                                $sql_insert = "INSERT INTO ad_frais_attente(id_cpte, date_frais, type_frais, montant, id_ag) VALUES ($cpte_interne_cli_a_credite, '$curr_date_frais_attente', $type_ope, $montant, $global_id_agence);";
                                $result_insert = executeDirectQuery($sql_insert);

                                if ($result_insert->errCode != NO_ERR){
                                    $dbHandler->closeConnection(false);
                                    return new ErrorObj($result_insert->errCode);
                                }
                            }
                        }
                    }
                }

                if (is_array($comptable) && count($comptable) > 0) {

                    $id_his = null;

                    // Annulation Remboursement capital sur crédits
                    if (is_array($annul_remb_lcr) && count($annul_remb_lcr) > 0) {

                        foreach($annul_remb_lcr as $doss) {

                            $id_doss = $doss["id_doss"];
                            $id_ech = $doss["id_ech"];
                            $montant = recupMontant($doss["montant"]);

                            $whereCond = " WHERE id_doss = $id_doss AND annul_remb IS NULL AND id_his IS NULL AND id_ech = $id_ech AND to_char(date_remb, 'YYYY-MM-DD') = to_char(now(), 'YYYY-MM-DD') AND mnt_remb_cap = '$montant' AND mnt_remb_int = 0 AND mnt_remb_gar = 0 AND mnt_remb_pen = 0 ";
                            $valRemb = getRemboursement($whereCond);

                            $num_remb = $valRemb[0]['num_remb'];

                            $DATA_REMB[$id_doss][$id_ech][$num_remb] = $valRemb[0];

                            // Annulation remboursement capital sur crédits
                            $myErr = annuleRemb(2, $global_id_guichet, $DATA_REMB, $fonc_sys_inv, $id_his, 'Gestion Annulation Retrait et Dépôt');

                            if ($myErr->errCode != NO_ERR) {
                                $dbHandler->closeConnection(false);
                                return $myErr;
                            } else {
                                $id_his = $myErr->param;
                            }
                        }
                    }

                    $myErr = ajout_historique ($fonc_sys_inv, $id_client, 'Gestion Annulation Retrait et Dépôt', $global_nom_login, $curr_date, $comptable, null, $id_his);

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    } else {

                        $id_his = $myErr->param;

                        // Mettre à jour le statut d'une demande d'annulation à Effectué
                        $erreur = AnnulationRetraitDepot::updateEtatAnnulationRetraitDepot($id_demande, AnnulationRetraitDepot::ETAT_ANNUL_EFFECTUE, $id_his, date("r"), "Demande annulation : Effectué");

                        if ($erreur->errCode == NO_ERR) {
                            $demande_count++;
                        } else {
                            $dbHandler->closeConnection(false);
                            return $erreur;
                        }

                        if ($infoDemande["type_ope"] == 512 || $infoDemande["type_ope"] == 532) {


                            $cheque_erreur = AnnulationRetraitDepot::AnnulationCheque($infoDemande["id_his"], $infoDemande["id_client"], $montant, $infoDemande["type_ope"]);

                            if ($cheque_erreur->errCode != NO_ERR) {
                                $dbHandler->closeConnection(false);
                                return $cheque_erreur;
                            }
                        }

                    }
                }
            }
        }

        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $demande_count);
    }

    /**
     * Ecraser les infos d'un cheque dans ad_cheque apres l'annulation
     *
     * @param int $id_his
     *
     * @return mixed
     */

    public static function AnnulationCheque($id_his, $id_client, $montant, $id_oper)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql_get_cheque_id_benef = "select he.num_piece as cheque, he.type_piece as type_piece from ad_his h, ad_his_ext he
			  where h.id_his_ext = he.id and h.id_his = $id_his and h.id_ag = $global_id_agence;";

        $get_cheque_id_benef = $db->query($sql_get_cheque_id_benef);

        if (DB:: isError($get_cheque_id_benef)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $get_chq_piece = $get_cheque_id_benef->fetchrow(DB_FETCHMODE_ASSOC);

        $sql_get_cpte = "";

        if ($id_oper == 512 || $id_oper == 532) {
            $sql_get_cpte = "SELECT chq.id_cpte as compte from ad_chequier chq inner join ad_cpt c on chq.id_cpte = c.id_cpte
            WHERE chq.id_ag = $global_id_agence AND (chq.num_first_cheque <= " . $get_chq_piece['cheque'] . " AND chq.num_last_cheque >= " . $get_chq_piece['cheque'] . ") AND c.id_titulaire = $id_client";
        }

        $get_cpte = $db->query($sql_get_cpte);

        if (DB:: isError($get_cpte)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $get_compte = $get_cpte->fetchrow(DB_FETCHMODE_ASSOC);

        //if not = 4
        if ($get_chq_piece['piece']!=4) {

            $validCheque = valideCheque($get_chq_piece['cheque'], $get_compte['compte']);


            if ($validCheque->errCode == ERR_CHEQUE_USE) {

                $sql_delete_cheque = "delete from ad_cheque where id_cheque=" . $get_chq_piece['cheque'];
                $delete_result = $db->query($sql_delete_cheque);

                if (DB:: isError($delete_result)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }

                if ($id_oper == 532) {
                    $cheque_exist = AnnulationRetraitDepot::validChequeCertifie($get_chq_piece['cheque'], $montant, $get_compte['compte']);

                    if ($cheque_exist->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $cheque_exist;
                    }

                    $sql_modif_cheque_certifie = "update ad_cheque_certifie set etat_cheque = " . ChequeCertifie::ETAT_CHEQUE_CERTIFIE_RESTITUEE . ", comments = 'Annulation Retrait chèque interne certifié No. " . $get_chq_piece['cheque'] . "' where id_ag = $global_id_agence and num_cheque = " . $get_chq_piece['cheque'] . " and montant = $montant and etat_cheque = " . ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE;

                    $modif_cheque_certifie = $db->query($sql_modif_cheque_certifie);

                    if (DB:: isError($modif_cheque_certifie)) {
                        $dbHandler->closeConnection(false);
                        signalErreur(__FILE__, __LINE__, __FUNCTION__);
                    }

                }

                $dbHandler->closeConnection(true);

            } else {

                $dbHandler->closeConnection(true);

                return new ErrorObj($validCheque->errCode);
            }
        }//end if not = 4

        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Valider si le cheque certifie exist dans ad_cheque_certifie dependant l'etat cheque traite
     *
     * @param int $numCheque
     * @param int $montant
     * @param int $num_cpte
     * @return mixed
     */
    public static function validChequeCertifie($numCheque, $montant, $num_cpte)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql_exist_cheque = "select count(*) as num_row from ad_cheque_certifie where id_ag = $global_id_agence and num_cheque = $numCheque
        and montant = $montant and num_cpte_cli = $num_cpte and etat_cheque = " . ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE;

        $exist_cheque = $db->query($sql_exist_cheque);

        if (DB:: isError($exist_cheque)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $cheque = $exist_cheque->fetchrow(DB_FETCHMODE_ASSOC);

        if ($cheque['num_row'] < 0) {
            return new ErrorObj(ERR_NO_CHEQUE);
        }

        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }
    /**
     * Récupère compte client d'un cheque certifie dependant compte temporaire
     *
     * @param int $num_cpte
     * @param int $montant
     *
     * @return string::compte client
     */
    public static function getCompteChequeCertifie($num_cpte,$montant){
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql_get_cpte_temporaire = "select num_cpte_cli from ad_cheque_certifie where num_cpte_cheque = $num_cpte
        and id_ag = $global_id_agence and montant = $montant and etat_cheque = " . ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE;

        $get_cpte_temporaire = $db->query($sql_get_cpte_temporaire);

        if (DB:: isError($get_cpte_temporaire)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $compte = $get_cpte_temporaire->fetchrow(DB_FETCHMODE_ASSOC);

        $dbHandler->closeConnection(true);

        return $compte['num_cpte_cli'];
    }

    /**
     * Récupère les infos d'une demande annulation
     *
     * @param int $id_demande
     * @param null|int $id_client
     *
     * @return mixed
     */
    public static function getDemandeAnnulation($id_demande, $id_client = null)
    {
        global $global_id_agence;

        $sql = "SELECT * FROM ad_annulation_retrait_depot WHERE id_ag = $global_id_agence AND id = $id_demande";

        if ($id_client != null) {
            $sql .= " AND id_client = $id_client ";
        }

        $result = executeDirectQuery($sql, FALSE);
        if ($result->errCode != NO_ERR) {
            return $result;
        } else {
            if (empty($result->param)) {
                return NULL;
            } else {
                return $result->param[0];
            }
        }
    }

    /**
     * Récupère la fonction système inverse
     *
     * @param int $fonc_sys
     *
     * @return string
     */
    public static function getInverseFoncSys($fonc_sys)
    {
        $fonc_sys_inv = null;

        switch($fonc_sys){

            case 70: // Retrait
            case 85: // Retrait express
                $fonc_sys_inv = 65; // Annulation Retrait
                break;
            case 75: // Dépôt
            case 86: // Dépôt express
                $fonc_sys_inv = 66; // Annulation Dépôt
                break;
            default:
                $fonc_sys_inv = null;
        }

        return $fonc_sys_inv;
    }

    /**
     * Récupère l'opération financière inverse
     *
     * @param int $type_ope
     *
     * @return string
     */
    public static function getInverseOpe($type_ope)
    {
        $type_ope_inv = null;

        switch($type_ope){

            case 140: // Retrait en espèces
                $type_ope_inv = 144;
                break;
            case 512: // Retrait cash par chèque interne
                $type_ope_inv = 542;
                break;
            case 532: // Retrait cash par chèque certifié
                $type_ope_inv = 547;
                break;
            case 511: // Retrait travelers cheque
                $type_ope_inv = 543;
                break;
            case 131: // Perception des frais de retrait
                $type_ope_inv = 132;
                break;
            case 130: // Perception des frais de retrait(retrait especes)
                $type_ope_inv = 133;
                break;
            case 134: // Perception des frais de retrait(retrait cash par cheque interne)
                $type_ope_inv = 135;
                break;
            case 136: // Perception des frais de retrait(retrait travelers cheque)
                $type_ope_inv = 137;
                break;
            case 138: // Perception des frais de retrait ( retrait cheque interne certifie)
                $type_ope_inv = 139;
                break;
            case 152: // Frais de transfert
                $type_ope_inv = 153;
                break;
            case 160: // Dépôt espèces
                $type_ope_inv = 161;
                break;
            case 500: // Mise en attente chèque
                $type_ope_inv = 546;
                break;
            case 503: // Réception chèque externe
                $type_ope_inv = 544;
                break;
            case 508: // Virement national
                $type_ope_inv = 509;
                break;
            case 510: // Perception frais de crédit direct sauf bonne fin
                $type_ope_inv = 545;
                break;
            case 150: // Perception frais de dépôt
                $type_ope_inv = 155;
                break;
            case 151: // Frais de virement
                $type_ope_inv = 154;
                break;
            case 50: // Retrait des frais de tenue de compte
                $type_ope_inv = 51;
                break;
            case 10: // Remboursement capital sur crédits
                $type_ope_inv = 11;
                break;
            case 473: // Paiement TVA deductible
                $type_ope_inv = 477;
                break;
            case 474: // Perception TVA collectée
                $type_ope_inv = 478;
                break;
            default:
                $type_ope_inv = $type_ope;
        }

        return $type_ope_inv;
    }

    /**
     * Récupère le libellé fonction système
     *
     * @param int $type_fonc
     *
     * @return string
     */
    public static function getLibelFonc($type_fonc)
    {
        global $adsys;

        $libel_fonc = "";

        if ($type_fonc > 0) {

            $libel_fonc = $adsys["adsys_fonction_systeme"][$type_fonc];
        }

        return $libel_fonc;
    }

    /**
     * Récupère le libellé d'une opération financière
     *
     * @param int $type_ope
     *
     * @return string
     */
    public static function getLibelOpe($type_ope)
    {
        $libel_ope = "";

        if ($type_ope > 0) {

            $myErr = getOperations($type_ope);

            if ($myErr->errCode == NO_ERR) {
                $OP = $myErr->param;

                $trad = new Trad($OP['libel']);

                $libel_ope = $trad->traduction();
            }
        }

        return $libel_ope;
    }


    public static function getFraisOpe($id_ope,$type_fonction,$id_cpte)
    {
        global $global_id_agence,$dbHandler;

        $db = $dbHandler->openConnection();

        $type_operation_func = null;

        $sql_frais_bool="select p.frais_retrait_spec as frais_bool from adsys_produit_epargne p, ad_cpt c where p.id=c.id_prod and c.id_cpte = $id_cpte";

        $result_frais_bool=$db->query($sql_frais_bool);
        if (DB::isError($result_frais_bool)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $frais_bool = $result_frais_bool->fetchRow(DB_FETCHMODE_ASSOC);

        if ($frais_bool['frais_bool'] == 't' && in_array($type_fonction,array("140","512","511","532"))){
            switch($type_fonction){

                case 140: // Retrait en espèces
                    $type_operation_func = 130;
                    break;
                case 512: // Retrait cash par chèque interne
                    //$type_operation_func = 131;
                    $type_operation_func = 134;
                    break;
                case 511: // Retrait travelers cheque
                    $type_operation_func = 136;
                    break;
                case 532: // retrait cheque interne certifié
                    //$type_operation_func = 131;
                    $type_operation_func = 138;
                    break;
            }
            $dbHandler->closeConnection(true);

        }
        else{

            $type_operation_func = 131;
            $dbHandler->closeConnection(true);
        }

//echo $type_operation_func;
        $sql = "SELECT montant
FROM ad_mouvement
WHERE
date_valeur BETWEEN date(now()) - 30 AND date(now()) AND
sens = 'd' AND
id_ecriture =
(SELECT id_ecriture FROM ad_ecriture
WHERE id_ag = $global_id_agence
AND id_his = $id_ope
AND type_operation = $type_operation_func LIMIT 1);";

        $result = executeDirectQuery($sql, FALSE);
        if ($result->errCode != NO_ERR) {
            return $result;
        } else {
            if (empty($result->param)) {
                return NULL;
            } else {
                return $result->param[0];
            }
        }
    }
}