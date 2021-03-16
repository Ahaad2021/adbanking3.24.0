<?php

/**
 * @package Chèque Interne
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
 * Description de la classe ChequeCertifie
 *
 * @author BD
 */
class ChequeCertifie
{

    /* Table ad_cheque_certifie.etat_cheque */
    const ETAT_CHEQUE_CERTIFIE_TOUS = 0;
    const ETAT_CHEQUE_CERTIFIE_NON_TRAITE = 1;
    const ETAT_CHEQUE_CERTIFIE_TRAITE = 2;
    const ETAT_CHEQUE_CERTIFIE_RESTITUEE = 3;

    /* Table ad_cheques_compensation.etat_cheque */
    const ETAT_CHEQUE_COMPENSATION_TOUS = 0;
    const ETAT_CHEQUE_COMPENSATION_ENREGISTRE = 1;
    const ETAT_CHEQUE_COMPENSATION_VALIDE = 2;
    const ETAT_CHEQUE_COMPENSATION_MIS_EN_ATTENTE = 3;
    const ETAT_CHEQUE_COMPENSATION_REJETE = 4;

    /**
     * Insertion dans la table ad_cheque_certifie
     *
     * @param Integer $num_cheque
     * @param Date $date_cheque
     * @param Double $montant
     * @param Integer $id_benef
     * @param Integer $num_cpte_cli
     * @param Integer $num_cpte_cheque
     * @param Integer $etat_cheque (Tous, non-traité, traité)
     * @param Date $date_traitement
     * @param String $comments
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function insertChequeCertifie($num_cheque, $date_cheque, $montant, $id_benef, $num_cpte_cli, $num_cpte_cheque, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE, $date_traitement = null, $comments = '')
    {

        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $tableFields = array(
            "num_cheque" => $num_cheque,
            "date_cheque" => $date_cheque,
            "date_traitement" => $date_traitement,
            "montant" => recupMontant($montant),
            "id_benef" => trim($id_benef),
            "num_cpte_cli" => $num_cpte_cli,
            "num_cpte_cheque" => $num_cpte_cheque,
            "etat_cheque" => $etat_cheque,
            "comments" => trim($comments),
            "id_ag" => $global_id_agence,
        );

        $sql = buildInsertQuery("ad_cheque_certifie", $tableFields);

        $result = $db->query($sql);

        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Renvoie le num de produit définissant les comptes d'épargne Chèque certifié
     *
     * @param int $id_agence
     *
     * @return int
     */
    public static function getChequeCertifieProductID($id_agence)
    {
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $sql = "SELECT id FROM adsys_produit_epargne WHERE classe_comptable = 8 AND id_ag = $id_agence;";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $tmpRow = $result->fetchrow();
        $id_prod = $tmpRow[0];
        $dbHandler->closeConnection(true);

        return $id_prod;
    }

    /**
     * Vérifié si un chèque est certifié et/ou traité/non-traité
     *
     * @param int $num_cheque
     * @param int $etat_cheque 0:Tous / 1:non-traité / 2:traité
     *
     * @return boolean
     */
    public static function isChequeCertifie($num_cheque, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT num_cheque FROM ad_cheque_certifie WHERE id_ag = $global_id_agence AND num_cheque = $num_cheque";

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $tmpRow = $result->fetchrow();

        $no_cheque = $tmpRow[0];

        $dbHandler->closeConnection(true);

        return ($no_cheque) ? true : false;
    }

    /**
     * Vérifié si un chèque est dans la table ad_cheques_compensation
     *
     * @param int $num_cheque
     * @param int $etat_cheque 0:Tous / 1:enregistré / 2:validé / 3:mis en attente / 4:rejeté)
     *
     * @return boolean
     */
    public static function isChequeCompensation($num_cheque, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT num_cheque FROM ad_cheques_compensation WHERE id_ag = $global_id_agence AND num_cheque = $num_cheque";

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $tmpRow = $result->fetchrow();

        $no_cheque = $tmpRow[0];

        $dbHandler->closeConnection(true);

        return ($no_cheque) ? true : false;
    }

    /**
     * Vérifié si le solde disponible est supérieur ou égal à 40% du montant du chèque
     *
     * @param $num_cpte_cli
     * @param $mnt
     *
     * @return bool
     */
    public static function validateSoldeCheque($num_cpte_cli, $mnt_chq)
    {
        global $dbHandler, $global_id_agence;

        // Get solde disponible
        $solde_cpte_cli = getSoldeDisponible($num_cpte_cli);

        if ($solde_cpte_cli >= recupMontant($mnt_chq)) {
            return true;
        } else {
            return false;
        }

    }
    /**
     * Vérifié si le solde disponible est supérieur ou égal à 40% du montant du chèque
     *
     * @param $num_cpte_cli
     * @param $mnt
     *
     * @return bool
     */
    public static function validateSoldeChequeCompensation($num_cpte_cli, $mnt_chq)
    {
        global $dbHandler, $global_id_agence;

        if ($_SESSION['current_cpte'] != $num_cpte_cli){ //pour le client suivant
            $_SESSION['current_cpte'] = $num_cpte_cli;
            $_SESSION['initial_solde'] = getSoldeDisponible($num_cpte_cli);
            $_SESSION['latest_solde_disponible'] = $_SESSION['initial_solde'];
        }

        if ($_SESSION['initial_solde'] != getSoldeDisponible($num_cpte_cli)){ //pour transaction en parallel de retrait ou depot sur ce compte
            $_SESSION['latest_solde_disponible'] = $_SESSION['latest_solde_disponible'] + (getSoldeDisponible($num_cpte_cli) - $_SESSION['initial_solde']);
        }

        if ($_SESSION['latest_solde_disponible'] >= recupMontant($mnt_chq)) {
            $_SESSION['latest_solde_disponible'] = $_SESSION['latest_solde_disponible'] - recupMontant($mnt_chq);
            return true;
        } else {
            return false;
        }

    }

    /**
     * Retourne les caractéristiques d'un produit d'épargne ChèqueCertifié
     *
     * @return array Un tableau associatif avec les caractéristiques du produit, NULL si pas de produit trouvé.
     */
    public static function getProdEpargneChequeCertifie()
    {
        global $global_id_agence;

        $sql = "SELECT * FROM adsys_produit_epargne WHERE classe_comptable = 8 AND id_ag = $global_id_agence";
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
     * Création du compte lié au chèque certifié
     *
     * @param $num_cpte_cli
     * @param $id_prod
     * @param $devise
     * @param $num_cheque
     *
     * @return int
     */
    public static function createCompteChequeCertifie($num_cpte_cli, $id_prod, $devise, $num_cheque)
    {
        global $dbHandler, $global_id_agence, $global_id_utilisateur;
        global $db;

        // Ouverture de transaction
        $db = $dbHandler->openConnection();

        // Infos du compte de prélèvement
        $compte_prelev = getAccountDatas($num_cpte_cli);

        // Préparation des données à passer à creationCompte()
        $DATA_CPT_CHQ = array();
        $DATA_CPT_CHQ['devise'] = $devise;
        $DATA_CPT_CHQ['utilis_crea'] = $global_id_utilisateur;
        $DATA_CPT_CHQ['etat_cpte'] = 3; // Etat du compte de chèque certifié (Bloqué)
        $DATA_CPT_CHQ['id_titulaire'] = $compte_prelev['id_titulaire'];
        $DATA_CPT_CHQ['date_ouvert'] = date("d/m/Y"); // Date d'ouverture du cpte de chèque certifié
        $DATA_CPT_CHQ['mnt_bloq'] = 0;
        $DATA_CPT_CHQ['id_prod'] = $id_prod;
        $DATA_CPT_CHQ['type_cpt_vers_int'] = $num_cpte_cli;
        $DATA_CPT_CHQ['intitule_compte'] = "Compte Chèque certifié No. " . $num_cheque;
        $DATA_CPT_CHQ['id_titulaire'] = $compte_prelev['id_titulaire'];

        $rang = getRangDisponible($compte_prelev['id_titulaire']);
        $DATA_CPT_CHQ['num_cpte'] = $rang;
        $DATA_CPT_CHQ['num_complet_cpte'] = makeNumCpte($compte_prelev['id_titulaire'], $rang);

        // Création du compte d'épargne chèque certifié
        $id_cpte = creationCompte($DATA_CPT_CHQ);

        $dbHandler->closeConnection(true);

        return $id_cpte;
    }

    /**
     * Fermeture du compte compte lié au chèque certifié
     *
     * @param int $id_cpte
     *
     * @return ErrorObj
     */
    public static function closeCompteChequeCertifie($id_cpte)
    {
        global $dbHandler, $global_id_agence;

        // Ouverture de transaction
        $db = $dbHandler->openConnection();

        $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte;";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        }

        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Création nouveau chèque certifié
     *
     * @param $num_cheque
     * @param $montant_cheque
     * @param $id_benef
     * @param $num_cpte_cli
     * @param $date_cheque
     * @param $date_traitement
     * @param $etat_cheque
     *
     * @return ErrorObj
     */
    public static function processChequeCertifie($num_cheque, $montant_cheque, $id_benef = null, $num_cpte_cli = null, $date_cheque, $date_traitement = null, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE)
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $db = $dbHandler->openConnection();

        // Vérifié si le chèque n’est pas mis en opposition
        $num_cpte_cli = (trim($num_cpte_cli)!='')?$num_cpte_cli:null;

        $result = valideCheque($num_cheque, $num_cpte_cli);

        if ($result->errCode == NO_ERR) {

            // Recup le numéro cpte client associé au chèque
            if ($num_cpte_cli == null) {

                $a_id_chequier = $result->param;

                $resultChq = getChequiers($a_id_chequier);

                if ($resultChq->errCode == NO_ERR) {
                    $num_cpte_cli = $resultChq->param[0]['id_cpte'];
                } else {
                    return new ErrorObj($resultChq->errCode);
                }
            }

            // Vérifié l'existence du numéro du chèque
            if (!ChequeCertifie::isChequeCertifie($num_cheque, ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS)) {

                // Vérifié le solde disponible
                if (ChequeCertifie::validateSoldeCheque($num_cpte_cli, $montant_cheque)) {

                    // Recup du produit épargne chèque certifié
                    $EPG_CHQ_CERTIF = ChequeCertifie::getProdEpargneChequeCertifie();

                    $id_prod = $EPG_CHQ_CERTIF['id'];
                    $devise_chq = $EPG_CHQ_CERTIF['devise'];

                    // Créer le compte chèque certifié
                    $num_cpte_cheque = ChequeCertifie::createCompteChequeCertifie($num_cpte_cli, $id_prod, $devise_chq, $num_cheque);

                    if ($num_cpte_cheque > 0) {
                        // Passage de l'écriture de chèque certifié
                        $comptable = array(); // Mouvements comptable
                        $cptes_substitue = array();
                        $cptes_substitue["cpta"] = array();
                        $cptes_substitue["int"] = array();

                        // Débit compte de prélèvement / Crédit compte client
                        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($num_cpte_cli);
                        if ($cptes_substitue["cpta"]["debit"] == NULL) {
                            $dbHandler->closeConnection(false);
                            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
                        }
                        $cptes_substitue["int"]["debit"] = $num_cpte_cli;

                        $cptes_substitue["cpta"]["credit"] = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_int"];
                        $cptes_substitue["int"]["credit"] = $num_cpte_cheque;

                        // Certification chèque
                        $myErr = passageEcrituresComptablesAuto(530, recupMontant($montant_cheque), $comptable, $cptes_substitue, $devise_chq, null, $num_cheque);
                        if ($myErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $myErr;
                        } else {

                            // Prélever une commission ?
                            if ($EPG_CHQ_CERTIF["cpte_cpta_prod_ep_commission"] != null && $EPG_CHQ_CERTIF["mnt_commission"] > 0) {
                                $cptes_substitue = array();
                                $cptes_substitue["cpta"] = array();
                                $cptes_substitue["int"] = array();

                                // Débit compte de prélèvement / Crédit compte comptable des commissions
                                $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($num_cpte_cli);
                                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                                    $dbHandler->closeConnection(false);
                                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
                                }
                                $cptes_substitue["int"]["debit"] = $num_cpte_cli;

                                $cptes_substitue["cpta"]["credit"] = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_commission"];

                                // Commissions sur certification chèque
                                $myErr = passageEcrituresComptablesAuto(531, $EPG_CHQ_CERTIF["mnt_commission"], $comptable, $cptes_substitue, $devise_chq, null, $num_cheque);
                            }

                            $err = ChequeCertifie::insertChequeCertifie($num_cheque, $date_cheque, $montant_cheque, $id_benef, $num_cpte_cli, $num_cpte_cheque, $etat_cheque, $date_traitement);

                            if ($err->errCode != NO_ERR) {
                                $dbHandler->closeConnection(false);
                                return $err;
                            } else {

                                // Log numéro chèque certifié dans la table ad_his_ext
                                $data_cheque = array();
                                $data_cheque["num_piece"] = $num_cheque;
                                $data_cheque["date_piece"] = php2pg($date_cheque);
                                $data_cheque["remarque"] = "Certification chèque interne No : " . $num_cheque;
                                $data_cheque["sens"] = "---";

                                $data_his_ext = creationHistoriqueExterieur($data_cheque);

                                $ACC = getAccountDatas($num_cpte_cli);

                                // Gestion des chèques certifiés
                                $myErr = ajout_historique(162, $ACC["id_titulaire"], "Numéro chèque : " . $num_cheque, $global_nom_login, date("r"), $comptable, $data_his_ext);
                                if ($myErr->errCode != NO_ERR) {
                                    $dbHandler->closeConnection(false);
                                    return $myErr;
                                } else {

                                    return new ErrorObj(NO_ERR, $myErr->param);
                                }
                            }
                        }

                    } else {

                        return new ErrorObj(ERR_CPTE_INEXISTANT);
                    }

                } else {

                    return new ErrorObj(ERR_SOLDE_INSUFFISANT);
                }

            } else {

                return new ErrorObj(ERR_DUPLICATE_CHEQUE);
            }

        } else {

            return new ErrorObj($result->errCode);
        }
    }

    /**
     * Récupère une liste de chèques certifiés
     *
     * @param null $id_cpte_client
     * @param int $etat_cheque
     *
     * @return array|null
     */
    public static function getChequeCertifieClient($id_cpte_client = null, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();
        $sql = "SELECT * FROM ad_cheque_certifie WHERE id_ag = $global_id_agence ";

        if ($id_cpte_client != null) {
            $sql .= " AND num_cpte_cli = $id_cpte_client ";
        }

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $sql .= " ORDER BY id ASC";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($result->numRows() == 0) {
            return NULL;
        }

        $tmp_arr = array();

        while ($ListCheques = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

            $tmp_arr[$ListCheques['num_cheque']] = $ListCheques;
        }

        return $tmp_arr;
    }

    /**
     * Retourne le nombre de chèques certifiés
     *
     * @param int $etat_cheque
     *
     * @return null|int
     */
    public static function getNbChequeCertifie($etat_cheque = ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT COUNT(1) FROM ad_cheque_certifie WHERE id_ag = $global_id_agence";

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmpRow = $result->fetchrow();

        $nb_chq = $tmpRow[0];

        $dbHandler->closeConnection(true);

        return $nb_chq;
    }

    /**
     * Récupère les infos d'un chèque certifié
     *
     * @param int $num_cheque
     * @param null|int $id_cpte_client
     * @param int $etat_cheque
     *
     * @return mixed
     */
    public static function getChequeCertifie($num_cheque, $id_cpte_client = null, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS)
    {
        global $global_id_agence;

        $sql = "SELECT * FROM ad_cheque_certifie WHERE id_ag = $global_id_agence AND num_cheque = $num_cheque";

        if ($id_cpte_client != null) {
            $sql .= " AND num_cpte_cli = $id_cpte_client ";
        }

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $sql .= " ORDER BY id ASC LIMIT 1";

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
     * Mettre à jour le statut d'un chèque certifié
     *
     * @param int $etat_cheque
     * @param int $num_cheque
     * @param int $num_cpte_cli
     * @param int $num_cpte_cheque
     * @param string $comments
     *
     * @return ErrorObj
     */
    public static function updateChequeCertifie($etat_cheque, $num_cheque, $num_cpte_cli, $num_cpte_cheque, $comments = '')
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $tableFields = array(
            "etat_cheque" => $etat_cheque,
            "comments" => trim($comments),
            "date_traitement" => date("r")
        );

        $sql_update = buildUpdateQuery("ad_cheque_certifie", $tableFields, array('num_cheque' => $num_cheque, 'id_ag' => $global_id_agence, 'num_cpte_cli' => $num_cpte_cli, 'num_cpte_cheque' => $num_cpte_cheque));

        $result = $db->query($sql_update);

        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Mettre à jour le statut d'un chèque certifié à Traité
     *
     * @param int $num_cheque
     * @param int $num_cpte_cli
     * @param int $num_cpte_cheque
     * @param string $comments
     *
     * @return ErrorObj
     */
    public static function updateChequeCertifieToTraite($num_cheque, $num_cpte_cli, $num_cpte_cheque, $comments = '')
    {
        return ChequeCertifie::updateChequeCertifie(ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE, $num_cheque, $num_cpte_cli, $num_cpte_cheque, $comments);
    }

    /**
     * Fonction qui parse et vérifie un fichier de données lors de l'ajout des chèques remis à la compensation.
     *
     * @param $fichier_excel
     *
     * @return ErrorObj
     */
    public static function parseFileChequeCompensation($fichier_excel)
    {
        global $dbHandler, $global_id_agence, $global_nom_login, $error;

        $db = $dbHandler->openConnection();

        if (!file_exists($fichier_excel)) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_FICHIER_DONNEES);
        }

        $handle = fopen($fichier_excel, 'r');

        $count = 0;
        $chq_count = 0;
        $cheque_err = array();

        $chqValidite = getValidityChequeDate();

        $chqOrdVal = $chqValidite['validite_chq_ord'];
        $chqCertVal = $chqValidite['validite_chq_cert'];

        while (($data = fgetcsv($handle, 200, ';')) != false) {

            $error_code = ERR_GENERIQUE;

            $count++;

            if ($count == 1) {
                continue;
            }

            $num_col = count($data);

            if ($num_col == 6) {

                $num_cpte_cli = trim($data[0]);
                $num_cheque = trim($data[1]);
                $date_cheque = trim($data[2]);
                $montant = recupMontant(trim($data[3]));
                $etab_benef = trim($data[4]);
                //$is_certifie = (int)trim($data[5]);
                $is_certifie = ChequeCertifie::isChequeCertifie($num_cheque, ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS);
                $nom_benef = trim($data[5]);

                if (!ChequeCertifie::isChequeCompensation($num_cheque)) {

                    // Vérifie la validité du numéro complet de compte
                    if (isNumComplet($num_cpte_cli)) {

                        // Renvoie l'id du compte en fonction de son numéro de compte complet
                        $id_cpte_cli = get_id_compte($num_cpte_cli);

                        if ($id_cpte_cli != NULL) {
                            // Vérifié si le chèque n’est pas mis en opposition
                            $result = valideCheque($num_cheque, $id_cpte_cli);

                            if ($result->errCode == NO_ERR) {

                                // Si chèque certifié
                                if ($is_certifie == true) {

                                    // Si chèque certifié non-traité
                                    if (ChequeCertifie::isChequeCertifie($num_cheque, ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE)) {

                                        $INFO_CHQ = ChequeCertifie::getChequeCertifie($num_cheque, $id_cpte_cli);

                                        // Si la date et montant correspondent de la table ad_cheque_certifie
                                        if (nbreDiffJours(date("d/m/Y"), $date_cheque) < $chqCertVal && $date_cheque == pg2phpDate($INFO_CHQ['date_cheque']) && $montant == recupMontant($INFO_CHQ['montant'])) {

                                            $erreur = ChequeCertifie::insertChequeCompensation($id_cpte_cli, $num_cheque, $date_cheque, $montant, $etab_benef, true, $nom_benef);

                                            if ($erreur->errCode == NO_ERR) {
                                                $chq_count++;
                                                $error_code = $erreur->errCode;
                                            }
                                        } else {
                                            $cheque_err[$num_cheque] = "La Date / Montant ne correspond pas";
                                        }
                                    } else {
                                        $cheque_err[$num_cheque] = "Le chèque certifié n ést pas à non-traité";
                                    }
                                } else {

                                    // Si la date ne dépasse pas la limite
                                    if (nbreDiffJours(date("d/m/Y"), $date_cheque) < $chqOrdVal) {

                                        $erreur = ChequeCertifie::insertChequeCompensation($id_cpte_cli, $num_cheque, $date_cheque, $montant, $etab_benef, false, $nom_benef);

                                        if ($erreur->errCode == NO_ERR) {
                                            $chq_count++;
                                            $error_code = $erreur->errCode;
                                        }
                                    } else {
                                        $cheque_err[$num_cheque] = "La Date ne correspond pas";
                                    }
                                }
                            } else {
                                $cheque_err[$num_cheque] = $error[$result->errCode];
                            }

                        } else {
                            $cheque_err[$num_cheque] = "Id compte client non trouvé";
                        }
                    } else {
                        $cheque_err[$num_cheque] = "Numéro complet de compte non valide";
                    }
                } else {
                    $cheque_err[$num_cheque] = "Le numéro de chèque existe déjà !";
                }
            }
        }
        fclose($handle);

        // Enregistrement des chèques reçus en compensation
        $myErr = ajout_historique(164, null, "Enregistrement des chèques", $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        } else {

            $dbHandler->closeConnection(true);

            if (count($cheque_err) > 0) {
                $error_code = ERR_GENERIQUE;
            }

            return new ErrorObj($error_code, array('cheque_err' => $cheque_err, 'chq_count' => $chq_count));
        }
    }

    /**
     * Insertion dans la table ad_cheques_compensation
     *
     * @param Integer $num_cpte_cli
     * @param Integer $num_cheque
     * @param Date $date_cheque
     * @param Double $montant
     * @param String $etab_benef
     * @param Boolean $is_certifie
     * @param Integer $etat_cheque (enregistré, validé, mis en attente, rejeté)
     * @param String $nom_benef
     * @param String $comments
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function insertChequeCompensation($num_cpte_cli, $num_cheque, $date_cheque, $montant, $etab_benef, $is_certifie, $nom_benef = '', $etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_ENREGISTRE, $comments = '')
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $tableFields = array(
            "num_cpte_cli" => trim($num_cpte_cli),
            "num_cheque" => trim($num_cheque),
            "date_cheque" => $date_cheque,
            "montant" => recupMontant($montant),
            "etab_benef" => trim($etab_benef),
            "is_certifie" => (($is_certifie) ? 't' : 'f'),
            "etat_cheque" => $etat_cheque,
            "date_etat" => date('r'),
            "nom_benef" => trim($nom_benef),
            "comments" => trim($comments),
            "id_ag" => $global_id_agence,
        );

        $sql = buildInsertQuery("ad_cheques_compensation", $tableFields);

        $result = $db->query($sql);

        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Récupère une liste de chèques compensation
     *
     * @param null|boolean $is_certifie
     * @param int $etat_cheque
     * @param null|int $id_cpte_client
     *
     * @return array|null
     */
    public static function getListeChequeCompensation($is_certifie = null, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS, $id_cpte_client = null)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();
        $sql = "SELECT * FROM ad_cheques_compensation WHERE id_ag = $global_id_agence ";

        if ($is_certifie != null) {
            $sql .= " AND is_certifie = '$is_certifie' ";
        }

        if ($id_cpte_client != null) {
            $sql .= " AND num_cpte_cli = $id_cpte_client ";
        }

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $sql .= " ORDER BY num_cpte_cli,num_cheque ASC"; //num_cheque
        

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($result->numRows() == 0) {
            return NULL;
        }

        $tmp_arr = array();

        while ($ListCheques = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

            $tmp_arr[$ListCheques['num_cheque']] = $ListCheques;
        }

        return $tmp_arr;
    }

    /**
     * Récupère une liste de chèques (certifiés) compensation
     *
     * @param int $etat_cheque
     * @param null|int $id_cpte_client
     *
     * @return array|null
     */
    public static function getListeChequeCompensationCertifie($etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS, $id_cpte_client = null)
    {
        return ChequeCertifie::getListeChequeCompensation("t", $etat_cheque, $id_cpte_client);
    }

    /**
     * Récupère une liste de chèques (non certifiés) compensation
     *
     * @param int $etat_cheque
     * @param null|int $id_cpte_client
     *
     * @return array|null
     */
    public static function getListeChequeCompensationOrdinaire($etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS, $id_cpte_client = null)
    {
        return ChequeCertifie::getListeChequeCompensation("f", $etat_cheque, $id_cpte_client);
    }

    /**
     * Retourne le nombre de chèques compensation
     *
     * @param null|boolean $is_certifie
     * @param int $etat_cheque
     *
     * @return null|int
     */
    public static function getNbChequeCompensation($is_certifie = null, $etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT COUNT(1) FROM ad_cheques_compensation WHERE id_ag = $global_id_agence";

        if ($is_certifie != null) {
            $sql .= " AND is_certifie = '$is_certifie' ";
        }

        if ($etat_cheque != ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS) {
            $sql .= " AND etat_cheque = $etat_cheque ";
        }

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmpRow = $result->fetchrow();

        $nb_chq = $tmpRow[0];

        $dbHandler->closeConnection(true);

        return $nb_chq;
    }

    /**
     * Retourne le nombre de chèques (certifiés) compensation
     *
     * @param int $etat_cheque
     *
     * @return array|null
     */
    public static function getNbChequeCompensationCertifie($etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS)
    {
        return ChequeCertifie::getNbChequeCompensation("t", $etat_cheque);
    }

    /**
     * Retourne le nombre de chèques (non certifiés) compensation
     *
     * @param int $etat_cheque
     *
     * @return array|null
     */
    public static function getNbChequeCompensationOrdinaire($etat_cheque = ChequeCertifie::ETAT_CHEQUE_COMPENSATION_TOUS)
    {
        return ChequeCertifie::getNbChequeCompensation("f", $etat_cheque);
    }

    /**
     * Traitement des chèques certifiés
     *
     * @return ErrorObj
     */
    public static function processChequeCompensationCertifie()
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $db = $dbHandler->openConnection();

        $comptable = array();

        $fonction = 165; // Traitement des chèques certifiés
        $operation = 535; // Compensation chèque certifié

        // Get liste chèques certifiés
        $listeChequeCertifie = ChequeCertifie::getListeChequeCompensationCertifie(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_ENREGISTRE);

        // Recup du produit épargne chèque certifié
        $EPG_CHQ_CERTIF = ChequeCertifie::getProdEpargneChequeCertifie();

        // Débit du compte client lié au chèque certifié
        $id_prod = $EPG_CHQ_CERTIF['id'];
        $cpta_debit = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_int"];
        $devise_chq = $EPG_CHQ_CERTIF['devise'];

        $chq_count = 0;

        foreach ($listeChequeCertifie as $id => $chqCertifie) {

            // Passage de l'écriture de retrait
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $id_chq_compensation = trim($chqCertifie["id"]);
            $num_cheque = trim($chqCertifie["num_cheque"]);
            $date_cheque = pg2phpDate($chqCertifie["date_cheque"]);
            $id_cpte_cli = trim($chqCertifie["num_cpte_cli"]);           
            $montant = recupMontant($chqCertifie["montant"]);

            // Vérifié l'existence du numéro du chèque
            if (ChequeCertifie::isChequeCertifie($num_cheque, ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE)) {

                $INFO_CHQ = ChequeCertifie::getChequeCertifie($num_cheque, $id_cpte_cli);

                $int_debit = $INFO_CHQ['num_cpte_cheque'];

                // Débit du compte comptable associé aux chèques certifiés
                $cptes_substitue["cpta"]["debit"] = $cpta_debit;
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $int_debit;
              
                // Save numéro chèque in ad_ecriture.info_ecriture
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $devise_chq, null, $num_cheque);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                } else {
                    $data_ch = array();

                    $data_ch['id_cheque'] = $num_cheque;
                    $data_ch['date_paiement'] = $date_cheque;
                    $data_ch['etat_cheque'] = 4; // Certifié

                    // Inséré le chèque dans la table ad_cheque
                    $rep = insertCheque($data_ch, $id_cpte_cli);

                    if ($rep->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $rep;
                    } else {

                        // Mettre à jour le statut d'un chèque certifié à Traité
                        $erreur = ChequeCertifie::updateChequeCertifieToTraite($num_cheque, $id_cpte_cli, $int_debit, "Compensation chèque interne certifié No. " . $num_cheque);

                        if ($erreur->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $erreur;
                        } else {
                            // Fermeture du compte de chèque certifié
                            $erreur = ChequeCertifie::closeCompteChequeCertifie($int_debit);

                            if ($erreur->errCode != NO_ERR) {
                                $dbHandler->closeConnection(false);
                                return $erreur;
                            } else {
                                // Mettre à jour le statut d'un chèque compensation à Validé
                                $erreur = ChequeCertifie::updateEtatChequeCompensation($id_chq_compensation, $num_cheque, $id_cpte_cli, ChequeCertifie::ETAT_CHEQUE_COMPENSATION_VALIDE, "Compensation chèque interne certifié No. " . $num_cheque);

                                if ($erreur->errCode != NO_ERR) {
                                    $dbHandler->closeConnection(false);
                                    return $erreur;
                                } else {
                                    $chq_count++;
                                }
                            }
                        }
                    }
                }
            }
        }

        $myErr = ajout_historique($fonction, null, "Traitement des chèques certifiés", $global_nom_login, date("r"), $comptable);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        } else {

            // Commit
            $dbHandler->closeConnection(true);

            return new ErrorObj(NO_ERR, $chq_count);
        }
    }

    /**
     * Traitement des chèques ordinaires (non certifiés)
     *
     * @return ErrorObj
     */
    public static function processChequeCompensationOrdinaire()
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $_SESSION['current_cpte'] = null;
        $_SESSION['initial_solde'] = 0;
        $_SESSION['latest_solde_disponible'] = 0;

        $db = $dbHandler->openConnection();

        $comptable = array();

        $fonction = 166; // Traitement des chèques ordinaires (non certifiés)

        // Get liste chèques ordinaires (non certifiés)
        $listeChequeOrdinaire = ChequeCertifie::getListeChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_ENREGISTRE);

        $chq_count = 0;

        foreach ($listeChequeOrdinaire as $id => $chqOrdinaire) {

            // Passage de l'écriture de retrait
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $isEcritureOK = false;
            $isValidationOK = false;

            $id_chq_compensation = trim($chqOrdinaire["id"]);
            $num_cheque = trim($chqOrdinaire["num_cheque"]);
            $date_cheque = pg2phpDate($chqOrdinaire["date_cheque"]);
            $id_cpte_cli = trim($chqOrdinaire["num_cpte_cli"]);
            $montant = recupMontant($chqOrdinaire["montant"]);

            if ($chq_count == 0){ //pour le premier client
                if ($_SESSION['current_cpte'] == null){
                    $_SESSION['current_cpte'] = $id_cpte_cli;
                    $_SESSION['initial_solde'] = getSoldeDisponible($id_cpte_cli);
                    $_SESSION['latest_solde_disponible'] = $_SESSION['initial_solde'];
                }
            }

            // Vérifié le solde disponible
            if (ChequeCertifie::validateSoldeChequeCompensation($id_cpte_cli, $montant)) {

                $operation = 536; // Validation compensation chèque ordinaire

                $isValidationOK = true;

                // Débit compte de prélèvement / Crédit compte client
                $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_cli);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
                }
                $cptes_substitue["int"]["debit"] = $id_cpte_cli;
               
                // Save numéro chèque in ad_ecriture.info_ecriture
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, null, null, $num_cheque);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                } else {
                    $isEcritureOK = true;
                }

            } else {

                $operation = 537; // Mise en attente compensation chèque ordinaire
               
                // Save numéro chèque in ad_ecriture.info_ecriture
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, null, null, $num_cheque);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                } else {
                    $isEcritureOK = true;
                }
            }

            if ($isEcritureOK == true) {

                if ($isValidationOK == true) {
                    $data_ch = array();

                    $data_ch['id_cheque'] = $num_cheque;
                    $data_ch['date_paiement'] = $date_cheque;
                    $data_ch['etat_cheque'] = 4; // Certifié

                    // Inséré le chèque dans la table ad_cheque
                    $rep = insertCheque($data_ch, $id_cpte_cli);

                    if ($rep->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $rep;
                    }
                }

                // Mettre à jour le statut d'un chèque compensation à Validé / Mis en attente
                $erreur = ChequeCertifie::updateEtatChequeCompensation($id_chq_compensation, $num_cheque, $id_cpte_cli, (($isValidationOK) ? ChequeCertifie::ETAT_CHEQUE_COMPENSATION_VALIDE : ChequeCertifie::ETAT_CHEQUE_COMPENSATION_MIS_EN_ATTENTE), "Compensation chèque ordinaire No. " . $num_cheque);

                if ($erreur->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $erreur;
                } else {
                    $chq_count++;
                }
            }
        }

        $myErr = ajout_historique($fonction, null, "Traitement des chèques ordinaires (non certifiés)", $global_nom_login, date("r"), $comptable);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        } else {

            // Commit
            $dbHandler->closeConnection(true);

            return new ErrorObj(NO_ERR, $chq_count);
        }
    }

    /**
     * Traitement des chèques ordinaires mis en attente
     *
     * @param Array $data
     *
     * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
     */
    public static function processChequeCompensationOrdinaireMiseEnAttente($data = null)
    {
        global $dbHandler, $global_id_agence, $global_nom_login;

        $_SESSION['current_cpte'] = null;
        $_SESSION['initial_solde'] = 0;
        $_SESSION['latest_solde_disponible'] = 0;

        $db = $dbHandler->openConnection();

        $comptable = array();

        $fonction = 167; // Traitement des chèques ordinaires mis en attente

        // Get liste chèques ordinaires mis en attente
        $listeChequeOrdinaire = ChequeCertifie::getListeChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_MIS_EN_ATTENTE);

        $chq_count = 0;

        foreach ($listeChequeOrdinaire as $id => $chqOrdinaire) {

            // Passage de l'écriture de retrait
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $isEcritureOK = false;
            $isValidationOK = false;
            $isRejectOK = false;

            $id_chq_compensation = trim($chqOrdinaire["id"]);
            $num_cheque = trim($chqOrdinaire["num_cheque"]);
            $date_cheque = pg2phpDate($chqOrdinaire["date_cheque"]);
            $id_cpte_cli = trim($chqOrdinaire["num_cpte_cli"]);
            $montant = recupMontant($chqOrdinaire["montant"]);

            if ($chq_count == 0){
                if ($_SESSION['current_cpte'] == null){
                    $_SESSION['current_cpte'] = $id_cpte_cli;
                    $_SESSION['initial_solde'] = getSoldeDisponible($id_cpte_cli);
                    $_SESSION['latest_solde_disponible'] = $_SESSION['initial_solde'];
                }
            }

            if (isset($data['btn_process_other_validate'])) {

                if (isset($data['check_valid_' . $num_cheque])) {

                    // Validation et vérification du solde disponible
                    if (ChequeCertifie::validateSoldeChequeCompensation($id_cpte_cli, $montant)) {

                        // Acceptation chèque mis en attente
                        $operation = 539;

                        $isValidationOK = true;

                        // Débit compte de prélèvement / Crédit compte client
                        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_cli);
                        if ($cptes_substitue["cpta"]["debit"] == NULL) {
                            $dbHandler->closeConnection(false);
                            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
                        }
                        $cptes_substitue["int"]["debit"] = $id_cpte_cli;
                       
                        // Save numéro chèque in ad_ecriture.info_ecriture
                        $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, null, null, $num_cheque);

                        if ($myErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $myErr;
                        } else {
                            $isEcritureOK = true;
                        }

                    }

                } elseif (isset($data['check_rejet_' . $num_cheque])) {

                    $isRejectOK = true;

                    // Rejet chèque mis en attente
                    $operation = 538;

                    // Save numéro chèque in ad_ecriture.info_ecriture
                    $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, null, null, $num_cheque);

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    } else {
                        $isEcritureOK = true;
                    }
                }
            } else {

                // Vérifié le solde disponible
                if (ChequeCertifie::validateSoldeChequeCompensation($id_cpte_cli, $montant)) {

                    // Acceptation chèque mis en attente
                    $operation = 539;

                    $isValidationOK = true;

                    // Débit compte de prélèvement / Crédit compte client
                    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_cli);
                    if ($cptes_substitue["cpta"]["debit"] == NULL) {
                        $dbHandler->closeConnection(false);
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
                    }
                    $cptes_substitue["int"]["debit"] = $id_cpte_cli;
                   
                    // Save numéro chèque in ad_ecriture.info_ecriture
                    $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, null, null, $num_cheque);

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    } else {
                        $isEcritureOK = true;
                    }

                } else {

                    if (isset($data['btn_process_reject'])) {

                        $isRejectOK = true;

                        // Rejet chèque mis en attente
                        $operation = 538;

                        // Save numéro chèque in ad_ecriture.info_ecriture
                        $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, null, null, $num_cheque);

                        if ($myErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $myErr;
                        } else {
                            $isEcritureOK = true;
                        }
                    }
                }
            }

            if ($isEcritureOK == true) {

                if ($isValidationOK == true) {
                    $data_ch = array();

                    $data_ch['id_cheque'] = $num_cheque;
                    $data_ch['date_paiement'] = $date_cheque;
                    $data_ch['etat_cheque'] = 4; // Certifié

                    // Inséré le chèque dans la table ad_cheque
                    $rep = insertCheque($data_ch, $id_cpte_cli);

                    if ($rep->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $rep;
                    }
                }

                // Mettre à jour le statut d'un chèque compensation à Validé / Mis en attente / Rejeté
                $erreur = ChequeCertifie::updateEtatChequeCompensation($id_chq_compensation, $num_cheque, $id_cpte_cli, (($isValidationOK) ? ChequeCertifie::ETAT_CHEQUE_COMPENSATION_VALIDE : ChequeCertifie::ETAT_CHEQUE_COMPENSATION_REJETE), "Compensation chèque ordinaire No. " . $num_cheque);

                if ($erreur->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $erreur;
                } else {
                    $chq_count++;
                }
            }
        }

        $myErr = ajout_historique($fonction, null, "Traitement des chèques ordinaires (non certifiés)", $global_nom_login, date("r"), $comptable);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        } else {

            // Commit
            $dbHandler->closeConnection(true);

            return new ErrorObj(NO_ERR, $chq_count);
        }
    }

    /**
     * Mettre à jour le statut d'un chèque compensation
     *
     * @param int $id_chq_compensation
     * @param int $num_cheque
     * @param int $num_cpte_cli
     * @param int $etat_cheque
     * @param string $comments
     *
     * @return ErrorObj
     */
    public static function updateEtatChequeCompensation($id_chq_compensation, $num_cheque, $num_cpte_cli, $etat_cheque, $comments
    = '')
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $tableFields = array(
            "etat_cheque" => $etat_cheque,
            "date_etat" => date("r"),
            "comments" => trim($comments)
        );

        $sql_update = buildUpdateQuery("ad_cheques_compensation", $tableFields, array('id' => $id_chq_compensation, 'num_cheque' => $num_cheque, 'num_cpte_cli' => $num_cpte_cli, 'id_ag' => $global_id_agence));

        $result = $db->query($sql_update);

        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Mise en opposition de chèque certifié
     *
     * @param int $num_cheque
     * @param int $num_cpte_client
     * @param array $comptable
     *
     * @return ErrorObj
     */

    public static function oppositionChequeCertifie($num_cheque, $num_cpte_client, &$comptable)
    {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $EPG_CHQ_CERTIF = ChequeCertifie::getProdEpargneChequeCertifie();
        $INFO_CHQ = ChequeCertifie::getChequeCertifie($num_cheque, $num_cpte_client);

        $num_cpte_cheque = $INFO_CHQ['num_cpte_cheque'];
        $montant_cheque = $INFO_CHQ['montant'];

        // Passage de l'écriture de chèque certifié
        //$comptable = array(); // Mouvements comptable
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        // Débit compte de prélèvement / Crédit compte client
        $cptes_substitue["cpta"]["debit"] = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_int"];

        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
        }

        $cptes_substitue["int"]["debit"] = $num_cpte_cheque;

        $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($num_cpte_client);
        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
        }

        $cptes_substitue["int"]["credit"] = $num_cpte_client;

        // Certification chèque
        $myErr = passageEcrituresComptablesAuto(541, recupMontant($montant_cheque), $comptable, $cptes_substitue, null, null, $num_cheque);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        } else {
            $erreur = ChequeCertifie::updateChequeCertifie(ChequeCertifie::ETAT_CHEQUE_CERTIFIE_RESTITUEE, $num_cheque, $num_cpte_client, $num_cpte_cheque, "Mise en opposition du chèque certifié No. " . $num_cheque);

            if ($erreur->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $erreur;
            }

            $err = ChequeCertifie::closeCompteChequeCertifie($num_cpte_cheque);

            if ($err->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $err;
            }

            $dbHandler->closeConnection(true);

            return new ErrorObj(NO_ERR);
        }

    }
}