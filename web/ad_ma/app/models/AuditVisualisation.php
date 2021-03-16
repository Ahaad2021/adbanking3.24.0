<?php

require_once 'ad_ma/app/controllers/misc/class.db.oo.php';
require_once 'Agence.php';
require_once 'AgenceRemote.php';
require_once 'Compta.php';
require_once 'Compte.php';
require_once 'Divers.php';
require_once 'lib/misc/divers.php';

/**
 * Description de la classe Audit
 *
 * @author rajeev
 *
 */
class AuditVisualisation
{
    const LOGIN_DISTANT = 'distant';
    const TRANSAC_DEPOT = 'depot';
    const TRANSAC_RETRAIT = 'retrait';
        
    private $listOperations = array(
            160,    // Depot especes
            503,    // Depot cheque
            508,    // Depot virement
            140,    // Retrait cash
            512     // Retrait cheque et retrait par authorisation
    );

    public function __construct() {

    }

    public function __destruct() {

    }

    /**
     * Récupère la liste des logins disponible en local
     *
     * @return array listeLogins
     */
    public function getListeLoginsForAudit()
    {
        global $dbHandler;

        $loginDistant = self::LOGIN_DISTANT;
         
        //pour pouvoir commit ou rollback toute la procédure
        $db = $dbHandler->openConnection();

        // Requete pour recuperer les logins
        $sql = "SELECT DISTINCT login FROM ad_log ORDER BY login;";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $libelleTous = _("[Tous locaux]");
        $separator = '-';
        $libelleDistant = _("[Distant]");
        $logins = array(0 => $libelleTous, $loginDistant => $libelleDistant);

        while ($row = $result->fetchrow()) {
            $logins[$row[0]] = $row[0];
        }

        $dbHandler->closeConnection(true);
        return $logins;
    }

    /**
     * Récupère la liste des fonctions pour l'audit multi-agences
     *
     * @return array $fonctions
     */
    public function getListeFonctionsForAudit()
    {
        $libelleDepot = _("Dépôt en déplacé");
        $libelleRetrait = _("Retrait en déplacé");
        $libelleTous = _("[Tous]");
        $fonctions = array(0 => $libelleTous, 'depot' => $libelleDepot, 'retrait' => $libelleRetrait);
        return $fonctions;
    }


    /**
     * Récupère la liste des agences distants
     *
     * @return array agencesDistants
     */
    public static function getListeAgencesDistantsForAudit()
    {
        $libelleTous = _("[Tous]");
        $temp_array = array();
        $temp_array[1] = $libelleTous;
        $listeAgences = AgenceRemote::getListRemoteAgence(true);
        $finalListeAgences = array_merge($temp_array, $listeAgences);
        return $finalListeAgences;
    }


    /**************************************** COMPTAGE DES RESULTATS **************************************/
     
    /**
     * Récupère le nombre des transactions en deplacé pour les resultats de recherche
     * @param array $criteres
     * @return $count
     */
    public function countTransactions($criteres)
    {
        $count = '';
        $login = $criteres['login'];
        $loginDistant = self::LOGIN_DISTANT;

        if($login == $loginDistant) {
            $count = $this->getCountTransactionsRemote($criteres);
        }
        else {
            $count = $this->getCountTransactionsLocal($criteres);
        }
        return $count;
    }


    /**
     *
     * Return the count of transactions that were executed with the current database as remote (login = distant)
     *
     * @param array $criteres
     * @return $count:
     */
    public function getCountTransactionsRemote($criteres)
    {
        global $global_id_agence;

        $fonction = $criteres['num_fonction'];
        $IdAgence = $criteres['IdAgence'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];

        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        $remote_conn = AgenceRemote::getRemoteAgenceConnection($IdAgence);

        $count = 0;

        $sql = "SELECT count(*) FROM adsys_audit_multi_agence WHERE id_ag_local=:id_ag_local AND  ";
        $sql .= "(id_ag_distant =:id_ag_distant) AND  ";
        $sql .= "(success_flag =:success_flag) AND  ";

        if ($fonction != NULL) $sql .= "(type_transaction=:fonction) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=:num_client) AND  ";
        if ($date_min != NULL) $sql .= "(date_maj)>=DATE(:date_min) AND  ";
        if ($date_max != NULL) $sql .= "(date_maj)<=DATE(:date_max) AND  ";        
        if ($trans_local != NULL) $sql .= "(id_his_distant=:trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_local=:trans_distant) AND  ";       

        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '

        $params = array(':id_ag_local' => $IdAgence,
                ':id_ag_distant' => $global_id_agence,
                ':success_flag' => $success_flag );

        if ($fonction != NULL) $params[':fonction'] = $fonction;
        if ($num_client != NULL) $params[':num_client'] = $num_client;
        if ($date_min != NULL) $params[':date_min'] = $date_min;
        if ($date_max != NULL) $params[':date_max'] = $date_max;
        if ($trans_local != NULL) $params[':trans_local'] = $trans_local;
        if ($trans_distant != NULL) $params[':trans_distant'] = $trans_distant;
         
        $count = $remote_conn->prepareFetchColumn($sql, $params);
        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        return $count;
    }

    /**
     *
     * Return the count of transactions that were executed with the current database as Local (login != distant)
     *
     * @param array $criteres
     * @return multitype:
     */
    public function getCountTransactionsLocal($criteres)
    {
        global $dbHandler,$global_id_agence;
        $db = $dbHandler->openConnection();

        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $IdAgence = $criteres['IdAgence'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];

        if($login == 0 ) $login = NULL;

        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        $sql = "SELECT count(*) FROM adsys_audit_multi_agence WHERE id_ag_local=$global_id_agence AND  ";
        $sql .= "(success_flag = $success_flag) AND  ";
        if ($login != NULL) $sql .= "(nom_login=$login) AND  ";
        if ($fonction != NULL) $sql .= "(type_transaction='$fonction') AND  ";
        if ($IdAgence != NULL) $sql .= "(id_ag_distant=$IdAgence) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=$num_client) AND  ";
        if ($date_min != NULL) $sql .= "(DATE(date_maj)>=DATE('$date_min')) AND  ";
        if ($date_max != NULL) $sql .= "(DATE(date_maj)<=DATE('$date_max')) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_local=$trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_distant=$trans_distant) AND  ";
        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
         
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $dbHandler->closeConnection(true);
        $row = $result->fetchrow();
        return $row[0];
    }

    /**************************************** RECHERCHE  **************************************/

    /**
     * Récupère la liste des transactions en deplacé pour les resultats de recherche
     *
     * @return array detailsTransactions
     */
    public function rechercheTransactions($criteres)
    {
        $detailsTransactions = array();
        $login = $criteres['login'];
        $loginDistant = self::LOGIN_DISTANT;

        if($login == $loginDistant) {
            $detailsTransactions = $this->rechercheTransactionsRemote($criteres);
        }
        else {
            $detailsTransactions =$this->rechercheTransactionsLocal($criteres);
        }

        return $detailsTransactions;
    }


    /**
     *
     * Return the transactions that were executed with the current database as Local (login != distant)
     *
     * @param array $criteres
     * @return multitype:
     */
    public function rechercheTransactionsLocal($criteres)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $IdAgence = $criteres['IdAgence'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];

        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        $champs = "date_maj,
                id_ag_local,
                id_ag_distant,
                nom_login,
                id_client_distant,
                id_compte_distant,
                type_transaction,
                type_choix_libel,
                montant,
                id_his_local,
                id_ecriture_local,
                id_his_distant,
                id_ecriture_distant,
                error_message,
                sql_log,
                success_flag ";

        $sql = "SELECT $champs FROM adsys_audit_multi_agence WHERE id_ag_local=$global_id_agence AND  ";
        $sql .= "(success_flag = $success_flag) AND  ";
        if ($login != NULL) $sql .= "(nom_login='$login') AND  ";
        if ($fonction != NULL) $sql .= "(type_transaction='$fonction') AND  ";
        if ($IdAgence != NULL) $sql .= "(id_ag_distant=$IdAgence) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=$num_client) AND  ";
        if ($date_min != NULL) $sql .= "(DATE(date_maj)>=DATE('$date_min')) AND  ";
        if ($date_max != NULL) $sql .= "(DATE(date_maj)<=DATE('$date_max')) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_local=$trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_distant=$trans_distant) AND  ";

        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
        $sql .= " ORDER BY id_his_local DESC ;";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $retour = array();
        $i = 0;

        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        {
            $idAgence = $row['id_ag_distant'];
            $nom_agence = AgenceRemote::getRemoteAgenceName($idAgence);
            $row['nom_agence'] = $nom_agence;

            if(!empty($row['id_his_local']))
            {
                //Recup les opérations financières
                $sql = "SELECT count(*) from ad_ecriture WHERE id_ag=$global_id_agence AND id_his=".$row['id_his_local'];
                $result2 = $db->query($sql);

                if (DB::isError($result2)) {
                    $dbHandler->closeConnection(false);
                    $result2->getMessage();
                    signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result2->getMessage()
                }
                $row2 = $result2->fetchrow();

                if ((! $trans_fin) || ($row2[0] > 0))
                {
                    $retour[$i] = $row;
                    $retour[$i]['trans_fin'] = ($row2[0] > 0);
                    ++$i;
                }
            }
            else {
                array_push($retour, $row);
            }
        }
        $dbHandler->closeConnection(true);
        return $retour;
    }
     

    /**
     *
     * Return the transactions that were executed with the current database as Remote (login = distant)
     *
     * @param array $criteres
     * @return array $retour
     */
    public function rechercheTransactionsRemote($criteres)
    {
        $idAgence = $criteres['IdAgence'];

        if(empty($idAgence)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        $remote_conn = AgenceRemote::getRemoteAgenceConnection($idAgence);

        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];

        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        $champs = " date_maj,
                id_ag_local,
                id_ag_distant,
                nom_login,
                id_client_distant,
                id_compte_distant,
                type_transaction,
                type_choix_libel,
                montant,
                id_his_local,
                id_ecriture_local,
                id_his_distant,
                id_ecriture_distant,
                error_message,
                sql_log,
                success_flag ";

        $sql = "SELECT $champs FROM adsys_audit_multi_agence WHERE id_ag_local=:id_ag_local AND  ";
        $sql .= "(id_ag_distant=:id_ag_distant) AND  ";
        $sql .= "(success_flag=:success_flag) AND  ";
         
        if ($fonction != NULL) $sql .= "(type_transaction=:fonction) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=:num_client) AND  ";
        if ($date_min != NULL) $sql .= "(date_maj)>=DATE(:date_min) AND  ";
        if ($date_max != NULL) $sql .= "(date_maj)<=DATE(:date_max) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_distant=:trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_local=:trans_distant) AND  ";
        
        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
        $sql .= " ORDER BY id_his_local DESC ;";

        $params = array(
                ':id_ag_local' => $idAgence,
                ':id_ag_distant' => $global_id_agence,
                ':success_flag' => $success_flag
        );

        if ($fonction != NULL) $params[':fonction'] = $fonction;
        if ($num_client != NULL) $params[':num_client'] = $num_client;
        if ($date_min != NULL) $params[':date_min'] = $date_min;
        if ($date_max != NULL) $params[':date_max'] = $date_max;
        if ($trans_local != NULL) $params[':trans_local'] = $trans_local;
        if ($trans_distant != NULL) $params[':trans_distant'] = $trans_distant;
        
        $results = $remote_conn->prepareFetchAll($sql, $params);
         
        $retour = array();
        $i = 0;

        foreach ($results as $row){
            $nom_agence = AgenceRemote::getRemoteAgenceName($idAgence);
            $row['nom_agence'] = $nom_agence;
            $row['nom_login'] = '['.$login.']' .' - '. $row['nom_login'];
            // Flag pour transactions local (si les comptes de liason sont affectés, il faut afficher les infos)
            if(!empty($row['id_ecriture_local'])) $row['trans_fin'] = true;
            else $row['trans_fin'] = false;
            array_push($retour, $row);
        }
         
        $dbHandler->closeConnection(true);
        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        return $retour;
    }

    /**
     * Renvoie les détails financiers d'une seule transaction sur un serveur distant
     *
     * @param int $id_trans_local id transaction dans l'historique de l'agence distante pour laquelle on veut les détails
     * @param int $id_agence id de l'agence distante dans adsys_multi_agence pour laquelle on veut les détails
     *
     */
    public function getOneRemoteTransactionDetails($id_trans_distant, $id_agence)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agence);

        // Récupère les infos sur la fonction dans l'historique
        $sql = "SELECT * FROM ad_his WHERE id_ag=:id_agence AND id_his=:id_trans_distant";
        $param_arr = array(':id_agence' => $id_agence, 'id_trans_distant' => $id_trans_distant);
        $result = $remote_conn->prepareFetchAll($sql, $param_arr);

        if (count($result) != 1) {
            AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucune ou plusieurs occurences de la transaction
        }

        //assign to return array
        $retour = $result[0];

        // infos ecritures local (ici la base local pour le transaction en deplace est une base distante du serveur courant)
        $retour['ecritures_local'] = array();

        // Récupère l'en-tête de l'écriture dans ad_ecriture
        $sql = "SELECT a.*, b.libel_jou ";
        $sql .= "FROM ad_ecriture a, ad_journaux b ";
        $sql .= "WHERE a.id_ag = b.id_ag AND a.id_ag=:id_agence AND a.id_jou = b.id_jou and id_his=:id_trans_distant ";
        $sql .= "ORDER BY a.id_ecriture;";
        $param_arr = array(':id_agence' => $id_agence, 'id_trans_distant' => $id_trans_distant);

        $results = $remote_conn->prepareFetchAll($sql, $param_arr);
         
        foreach($results as $result) {
            // traduction:
            $result['libel_ecriture'] = Divers::getRemoteTradFromId($remote_conn, $result['libel_ecriture']);
            $retour['ecritures_local'][$result['id_ecriture']] = $result;
        }

        // Récupération du détail des mouvements comptables
        foreach ($retour['ecritures_local'] as $key => $value)
        {
            $sql = "SELECT * FROM ad_mouvement WHERE id_ag=:id_agence AND id_ecriture=:id_ecriture ORDER BY sens DESC;";
            $param_arr = array(':id_agence' => $id_agence, 'id_ecriture' => $key);

            $results = $remote_conn->prepareFetchAll($sql, $param_arr);
             
            $retour['ecritures_local'][$key]['mouvements'] = array();

            $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

            foreach ($results as $result) {
                if ($result['cpte_interne_cli'] != NULL) {
                    $CompteObj = new Compte($remote_conn, $id_agence);
                    $InfosCompte = $CompteObj->getAccountDatas($result['cpte_interne_cli']);
                    $result['num_complet_cpte'] = $InfosCompte['num_complet_cpte'];
                }
                $retour['ecritures_local'][$key]['mouvements'][$count] = $result;
                $count++;
            }
        }

        // Recherche des infos éventuelles dans ad_his_ext si appliquable
        if ($retour["id_his_ext"] != "") {

            $sql = "SELECT * FROM ad_his_ext WHERE id_ag =:id_agence AND id=:id_his_ext";
            $param_arr = array(':id_agence' => $id_agence, 'id_his_ext' => $retour["id_his_ext"]);
            $result = $remote_conn->prepareFetchAll($sql, $param_arr);
            $retour["infos_ext"] = $result[0];
        }

        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        return $retour;
    }


    /**************************************** GENERATION RAPPORTS  **************************************/


    /**
     * Renvoie les détails financiers des transactions en deplacé pour le rapport visualisation des transactions
     * en deplace
     * @param array criteres recuperee de la page criteres de recherche
     * @return array
     *
     */
    public function getMultiAgenceAuditData($criteres)
    {
        $detailsTransactions = array();
        $login = $criteres['login'];
        $loginDistant = self::LOGIN_DISTANT;
        
        // login distant, les clients sont de l'agence locale, servi par des agences externes
        if($login == $loginDistant) {
            $detailsTransactions = $this->getAuditDataForLocalClients($criteres); 
        }
        else { // ici les clients externes a l'agence sont servis par l'agence
            $detailsTransactions = $this->getAuditDataForExternalClients($criteres);
        }

        return $detailsTransactions;
    }

    ///////////////////////////// CLIENTS EXTERNES /////////////////////////////////////////////
    
    
    /**
     *
     * Renvoie les détails financiers des transactions pour les clients externes qui sont servis par l'agence, 
     * le login choisi = tous ou un login autre que 'distant' dans l'ecran "criteres de recherche".
     *
     * @param array criteres
     * @return array
     */
    public function getAuditDataForExternalClients($criteres)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $IdAgence = $criteres['IdAgence'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];

        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        $champs = "date_maj,
                id_ag_local,
                id_ag_distant,
                nom_login,
                id_client_distant,
                id_compte_distant,
                type_transaction,
                type_choix_libel,
                id_his_local,
                id_his_distant ";

        $sql = "SELECT $champs FROM adsys_audit_multi_agence WHERE id_ag_local=$global_id_agence AND  ";
        $sql .= "(success_flag = $success_flag) AND  ";
        if ($login != NULL) $sql .= "(nom_login='$login') AND  ";
        if ($fonction != NULL) $sql .= "(type_transaction='$fonction') AND  ";
        if ($IdAgence != NULL) $sql .= "(id_ag_distant=$IdAgence) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=$num_client) AND  ";
        if ($date_min != NULL) $sql .= "(DATE(date_maj)>=DATE('$date_min')) AND  ";
        if ($date_max != NULL) $sql .= "(DATE(date_maj)<=DATE('$date_max')) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_local=$trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_distant=$trans_distant) AND  ";

        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
        $sql .= " ORDER BY id_his_local DESC ;";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $retour = array();
        $i = 0;

        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        {          
            $row['type_transaction'] = trim($row['type_transaction']);
             
            if(!empty($row['id_his_distant']) && !empty($row['id_ag_distant'])) {
                //recupere les infos sur la base distante
                $details_transaction = $this->getRemoteEcrituresForExternalClients($row['id_his_local'], $row['id_his_distant'], $row['id_ag_distant']);
                $row = array_merge($row, $details_transaction);
            }
            array_push($retour, $row);
        }
        $dbHandler->closeConnection(true);

        return $retour;
    }


    /**
     * Renvoie les détails financiers d'une transaction pour un client externe
     * On doit se connecter sur la base distante pour recuperer les donnees dans le log adsys_audit_multi_agence
     *
     * @param int $id_trans_local id_his_local dans adsys_audit_multi_agence
     * @param int $id_trans_distant id_his_distant dans adsys_audit_multi_agence
     * @param int $id_agence id_ag_distant dans adsys_audit_multi_agence
     */
    public function getRemoteEcrituresForExternalClients($id_trans_local = null, $id_trans_distant, $id_agence)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        // Connect sur la base distante
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agence);

        $sql = "SELECT id_his, id_his_ext FROM ad_his WHERE id_ag=:id_agence AND id_his=:id_trans_distant";
        $param_arr = array(':id_agence' => $id_agence, ':id_trans_distant' => $id_trans_distant);
        $result = $remote_conn->prepareFetchAll($sql, $param_arr);

        if (count($result) != 1) {
            AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucune ou plusieurs occurences de la transaction
        }

        //assign to return array
        $retour = $result[0];

        // infos ecritures local (ici la base local pour le transaction en deplace est une base distante du serveur courant)
        $retour['ecritures_local'] = array();

        // Récupère l'en-tête de l'écriture dans ad_ecriture
        $sql = "SELECT a.id_ecriture, a.type_operation, a.libel_ecriture ";
        $sql .= "FROM ad_ecriture a, ad_journaux b ";
        $sql .= "WHERE a.id_ag = b.id_ag AND a.id_jou = b.id_jou AND a.id_ag=:id_agence  AND a.id_his=:id_trans_distant ";
        $sql .= "ORDER BY a.id_ecriture;";

        $param_arr = array(':id_agence' => $id_agence, 'id_trans_distant' => $id_trans_distant);

        $results = $remote_conn->prepareFetchAll($sql, $param_arr);
         
        foreach($results as $result) {
            // traduction:
            $result['libel_ecriture'] = Divers::getRemoteTradFromId($remote_conn, $result['libel_ecriture']);
            $retour['ecritures_local'][$result['id_ecriture']] = $result;
        }

        // Récupération du détail des mouvements comptables
        foreach ($retour['ecritures_local'] as $key => $value)
        {
            // L'operation pour affichage dans le rapport
            if(! in_array($value['type_operation'], $this->listOperations)) {
                $retour['ecritures_local'][$key]['libel'] = $value['libel_ecriture'];
            }

            $champs = " id_mouvement,
                    compte,
                    cpte_interne_cli,
                    sens,
                    montant,
                    devise ";

            $sql = "SELECT $champs FROM ad_mouvement WHERE id_ag=:id_agence AND id_ecriture=:id_ecriture ORDER BY sens DESC;  ";
           
            $param_arr = array(':id_agence' => $id_agence, 'id_ecriture' => $key);
            $results = $remote_conn->prepareFetchAll($sql, $param_arr);
                         
            $retour['ecritures_local'][$key]['mouvements'] = array();

            $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

            foreach ($results as $result) {
                if ($result['cpte_interne_cli'] != NULL) {
                    $CompteObj = new Compte($remote_conn, $id_agence);
                    $numeroCpte = $CompteObj->getClientAccount($result['cpte_interne_cli']);
                    $result['num_complet_cpte'] = $numeroCpte;
                }
                $retour['ecritures_local'][$key]['mouvements'][$count] = $result;
                $count++;
            }
        }

        // infos ecritures distant (ici la base distante est le serveur local)
        if(!empty($id_trans_local)) {
            $retour['ecritures_distant'] = $this->getLocalEcrituresForExternalClients($id_trans_local);
        }
        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        return $retour;
    }


    /**
     * Renvoie les détails financiers locals pour les clients externe a l'agence
     *
     * @param int $id_trans Transaction
     */
    public function getLocalEcrituresForExternalClients($id_trans)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        if(empty($id_trans)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        // Récupère les infos sur la fonction dans l'historique
        $sql = "SELECT * FROM ad_his WHERE id_ag=$global_id_agence AND id_his=$id_trans";
        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        if (count($result) != 1) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucune ou plusieurs occurences de la transaction
        }
         
        $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);

        $retour['ecritures'] = array();

        // Récupère l'en-tête de l'écriture dans ad_ecriture
        $sql = "SELECT a.id_ecriture, a.type_operation, a.libel_ecriture ";
        $sql .= "FROM ad_ecriture a, ad_journaux b ";
        $sql .= "WHERE a.id_ag = b.id_ag AND a.id_ag=$global_id_agence AND a.id_jou = b.id_jou and id_his=$id_trans ";
        $sql .= "ORDER BY a.id_ecriture;";

        $result = $db->query($sql);

        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))  {
            // traduction:
            $row['libel_ecriture'] = Divers::getLocalTradFromId($row['libel_ecriture']);
            $retour['ecritures'][$row['id_ecriture']] = $row;
        }

        // Récupération du détail des mouvements comptables
        foreach ($retour['ecritures'] as $key => $value)
        {
            $sql = "SELECT * FROM ad_mouvement WHERE id_ag=$global_id_agence AND id_ecriture=$key ORDER BY sens DESC;";

            $retour['ecritures'][$key]['mouvements'] = array();

            $result2 = $db->query($sql);

            $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

            while ($row2 = $result2->fetchrow(DB_FETCHMODE_ASSOC))  {
                if ($row2['cpte_interne_cli'] != NULL) {
                    $InfosCompte = getAccountDatas($row2['cpte_interne_cli']);
                    $row2['num_complet_cpte'] = $InfosCompte['num_complet_cpte'];
                }
                $retour['ecritures'][$key]['mouvements'][$count] = $row2;
                $count++;
            }
        }

        // Recherche des infos éventuelles dans ad_his_ext si appliquable
        if ($retour["id_his_ext"] != "") {
            $id_his_ext = $retour["id_his_ext"];
            $sql = "SELECT * FROM ad_his_ext WHERE id_ag=$global_id_agence AND id=$id_his_ext";
            $result = $db->query($sql);

            $retour["infos_ext"] = $result->fetchrow(DB_FETCHMODE_ASSOC);
        }

        $dbHandler->closeConnection(true);

        return $retour;
    }
    
    /*
     * Recupere le total de depot en deplace et de retrait en deplace d'une agence
     * par rapport aux agences externes, trier par nom agence
     *
     * @return array $summary
     */
    /*
    public static function getSummaryForVisualisationClientsExterne($criteres)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();
        
        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];
        $id_agc_ext = $criteres['id_agence_ext'];
        
        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';
                
        $champs = "id_ag_distant, type_transaction, montant ";
        $sql = "SELECT $champs FROM adsys_audit_multi_agence WHERE id_ag_local=$global_id_agence AND  ";
        $sql .= "(id_his_local > 0) AND  ";
        $sql .= "(id_his_distant > 0) ";
        $sql .= " ORDER BY id_ag_distant ASC ;";
        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $somme_depot = $somme_retrait = 0;
        $summary = array();

        // recupere les montants par type de transaction
        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        {
            $type_transaction = trim($row['type_transaction']);
             
            if($type_transaction == self::TRANSAC_DEPOT) {
                $somme_depot += $row['montant'];
                $id_agence = trim($row['id_ag_distant']);
                $summary[$id_agence]['total_depot'] = $somme_depot;
            }
            elseif($type_transaction == self::TRANSAC_RETRAIT) {
                $somme_retrait += $row['montant'];
                $id_agence = trim($row['id_ag_distant']);
                $summary[$id_agence]['total_retrait'] = $somme_retrait;
            }
        }

        // calcule les valeurs nette
        foreach ($summary as $key => &$value) {
            // ajout le nom de l'agence
            $nom_agence = AgenceRemote::getRemoteAgenceName($key);
            $value['id_agence'] = $key;
            $value['nom_agence'] = $nom_agence;
            $value['net'] = $value['total_depot'] - $value['total_retrait'];
        }

        $dbHandler->closeConnection(true);
        return $summary;
    }
    */

    /**
     * Recupere le total de depot en deplace et de retrait en deplace d'une agence
     * par rapport aux agences externes, trier par nom agence
     * 
     * @param array $criteres
     * @return array $summary
     */
    public static function getSummaryForVisualisationClientsExterne($criteres)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();        
    
        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $id_agc_ext = $criteres['IdAgence'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];
    
        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';            
         
        $champs = "type_transaction, type_choix_libel, montant, id_ag_distant ";
        
        $sql = "SELECT $champs FROM adsys_audit_multi_agence WHERE id_ag_local=$global_id_agence AND  ";
        $sql .= "(success_flag = $success_flag) AND  ";
        
        if ($login != NULL) $sql .= "(nom_login='$login') AND  ";
        if ($fonction != NULL) $sql .= "(type_transaction='$fonction') AND  ";
        if ($id_agc_ext != NULL) $sql .= "(id_ag_distant=$id_agc_ext) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=$num_client) AND  ";
        if ($date_min != NULL) $sql .= "(DATE(date_maj)>=DATE('$date_min')) AND  ";
        if ($date_max != NULL) $sql .= "(DATE(date_maj)<=DATE('$date_max')) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_local=$trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_distant=$trans_distant) AND  ";
        
        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
        $sql .= " ;";
              
        $result = $db->query($sql);
        
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
                
        $agence_local = AgenceRemote::getRemoteAgenceName($global_id_agence);
        $agence_local = trim($agence_local);
        
        $somme_depot = $somme_retrait = $grand_total_depot = $grand_total_retrait =0;
        
        $summary = array();
        $summary['rows_summary'] = array();
        $summary['grand_summary'] = array();

        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        {
            // Infos agence :
            $id_ag = trim($row['id_ag_distant']);
            $agence_remote = AgenceRemote::getRemoteAgenceName($id_ag);

            if(empty($summary['rows_summary'][$id_ag]['agence_locale'])) {
                $summary['rows_summary'][$id_ag]['agence_locale'] = $agence_local;
            }
            if(empty($summary['rows_summary'][$id_ag]['agence_externe'])) {
                $summary['rows_summary'][$id_ag]['agence_externe'] = $agence_remote;
            }
            
            // Calcule total
            $type_transaction = trim($row['type_transaction']);           
            $montant = trim($row['montant']);          

            if($type_transaction == self::TRANSAC_DEPOT) {
                //$somme_depot += $montant;
                $summary['rows_summary'][$id_ag]['total_depot'] += $montant;
            }
            elseif($type_transaction == self::TRANSAC_RETRAIT) {
                //$somme_retrait += $montant;
                $summary['rows_summary'][$id_ag]['total_retrait'] += $montant;
            }
        }     
        
        // calcule grand total
        foreach($summary['rows_summary'] as $row) {           
            $grand_total_depot += $row['total_depot'];
            $grand_total_retrait += $row['total_retrait'];
        }
        
        $summary['grand_summary']['agence'] = $agence_local;
        $summary['grand_summary']['grand_total_depot'] = $grand_total_depot;
        $summary['grand_summary']['grand_total_retrait'] = $grand_total_retrait;
                
        $dbHandler->closeConnection(true);
        
        return $summary;    
    }
        
    
    ///////////////////////////// CLIENTS INTERNES /////////////////////////////////////////////
    
    /**
     * Renvoie les détails financiers des transactions pour les clients locaux servi par des agences externes, 
     * le login choisi = distant dans l'ecran "criteres de recherche"
     * 
     * @param array criteres
     * @return array
     */
    public function getAuditDataForLocalClients($criteres)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        $login = $criteres['login'];
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $IdAgence = $criteres['IdAgence'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];

        if(empty($login) || empty($IdAgence)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        //get remote connection
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($IdAgence);

        $champs = "date_maj,
                id_ag_local,
                id_ag_distant,
                nom_login,
                id_client_distant,
                id_compte_distant,
                type_transaction,
                type_choix_libel,
                id_his_local,
                id_his_distant ";

        $sql = "SELECT $champs FROM adsys_audit_multi_agence WHERE id_ag_local=:id_ag_local AND  ";
        $sql .= "(id_ag_distant=:id_ag_distant) AND  ";
        $sql .= "(success_flag=:success_flag) AND  ";
         
        if ($fonction != NULL) $sql .= "(type_transaction=:fonction) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=:num_client) AND  ";
        if ($date_min != NULL) $sql .= "(date_maj)>=DATE(:date_min) AND  ";
        if ($date_max != NULL) $sql .= "(date_maj)<=DATE(:date_max) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_local=:trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_distant=:trans_distant) AND  ";

        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
        $sql .= " ORDER BY id_his_local DESC ;";

        $params = array(':id_ag_local' => $IdAgence,
                ':id_ag_distant' => $global_id_agence,
                ':success_flag' => $success_flag);

        if ($fonction != NULL) $params[':fonction'] = $fonction;
        if ($num_client != NULL) $params[':num_client'] = $num_client;
        if ($date_min != NULL) $params[':date_min'] = $date_min;
        if ($date_max != NULL) $params[':date_max'] = $date_max;
        if ($trans_local != NULL) $params[':trans_local'] = $trans_local;
        if ($trans_distant != NULL) $params[':trans_distant'] = $trans_distant;


        $results = $remote_conn->prepareFetchAll($sql, $params);

        $retour = array();
        $i = 0;

        foreach ($results as $row)
        {           
            $row['type_transaction'] = trim($row['type_transaction']);
            $row['type_choix_libel'] = trim($row['type_choix_libel']);           
            $row['nom_login'] = trim($row['nom_login']);
            
            if(!empty($row['id_his_distant']) && !empty($row['id_his_local'])) {
                $details_transaction = $this->getLocalEcrituresForLocalClients($row['id_his_distant'], $row['id_his_local'], $row['id_ag_local']);                
            }            
           
            if(is_array($details_transaction)) {
                $row = array_merge($row, $details_transaction);
            }
             
            array_push($retour, $row);
        }

        return $retour;
    }

    
    /**
     * Recupere les ecritures qui sont faits sur l'agence pour les clients locals 
     * @param int $id_trans
     * @param int $id_trans_distant
     * @param unknown $id_agence_distant
     * @return array
     */
    public function getLocalEcrituresForLocalClients($id_trans, $id_trans_distant, $id_agence_distant)
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        if(empty($id_trans)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        // Récupère les infos sur la fonction dans l'historique
        $sql = "SELECT id_his, id_his_ext FROM ad_his WHERE id_ag=$global_id_agence AND id_his=$id_trans";

        $result = $db->query($sql);

        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        if (count($result) != 1) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucune ou plusieurs occurences de la transaction
        }
         
        $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);

        $retour['ecritures_local'] = array();

        // Récupère l'en-tête de l'écriture dans ad_ecriture     
        $sql = "SELECT a.id_ecriture, a.type_operation, a.libel_ecriture ";
        $sql .= "FROM ad_ecriture a, ad_journaux b ";
        $sql .= "WHERE a.id_ag = b.id_ag AND a.id_ag=$global_id_agence AND a.id_jou = b.id_jou and id_his=$id_trans ";
        $sql .= "ORDER BY a.id_ecriture;";

        $result = $db->query($sql);

        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))  {
            // traduction:
            $row['libel_ecriture'] = Divers::getLocalTradFromId($row['libel_ecriture']);
            $retour['ecritures_local'][$row['id_ecriture']] = $row;
        }

        // Récupération du détail des mouvements comptables
        foreach ($retour['ecritures_local'] as $key => $value)
        {
            // L'operation pour affichage dans le rapport
           
            if(! in_array($value['type_operation'], $this->listOperations)) {
                $retour['ecritures_local'][$key]['libel'] = $value['libel_ecriture'];
            } 

            $champs = " id_mouvement,
                        compte,
                        cpte_interne_cli,
                        sens,
                        montant,
                        devise ";         

            $sql = "SELECT $champs FROM ad_mouvement WHERE id_ag=$global_id_agence AND id_ecriture=$key ORDER BY sens DESC;";

            $retour['ecritures_local'][$key]['mouvements'] = array();

            $result2 = $db->query($sql);

            $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

            while ($row2 = $result2->fetchrow(DB_FETCHMODE_ASSOC))  {
                if ($row2['cpte_interne_cli'] != NULL) {
                    $InfosCompte = getAccountDatas($row2['cpte_interne_cli']);
                    $row2['num_complet_cpte'] = $InfosCompte['num_complet_cpte'];
                }
                $retour['ecritures_local'][$key]['mouvements'][$count] = $row2;
                $count++;
            }
        }
         
        // infos distant
        if(!empty($id_agence_distant)) {
            $ecritures =  $this->getRemoteEcrituresForLocalClients($id_trans_distant, $id_agence_distant);
            $retour['ecritures_distant'] = $ecritures['ecritures_distant'];
        }
        $dbHandler->closeConnection(true);
        return $retour;
    }


    /**
     * Renvoie les ecritures distants sur les base externes pour les clients locaux a l'agence qui
     * sont servis par les agences externes
     *
     * @param int $id_trans 
     * @param int $id_agence
     * 
     */
    public function getRemoteEcrituresForLocalClients($id_trans, $id_agence)
    {
        if(empty($id_trans)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        if(empty($id_agence)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agence);

        // Récupère l'en-tête de l'écriture dans ad_ecriture
        $sql = "SELECT a.id_ecriture, a.type_operation, a.libel_ecriture ";
        $sql .= "FROM ad_ecriture a, ad_journaux b ";
        $sql .= "WHERE a.id_ag = b.id_ag AND a.id_ag=:id_agence AND a.id_jou = b.id_jou and id_his=:id_trans ";
        $sql .= "ORDER BY a.id_ecriture;";

        $params = array(':id_agence' => $id_agence, ':id_trans' => $id_trans);

        $results = $remote_conn->prepareFetchAll($sql, $params);

        foreach($results as $result)
        {
            $result['libel_ecriture'] = Divers::getRemoteTradFromId($remote_conn, $result['libel_ecriture']);
            $retour['ecritures_distant'][$result['id_ecriture']] = $result;
        }

        // Récupération du détail des mouvements comptables
        foreach ($retour['ecritures_distant'] as $key => $value)
        {
            // L'operation pour affichage dans le rapport
            if(! in_array($value['type_operation'], $this->listOperations)) {
                $retour['ecritures_distant'][$key]['libel'] = $value['libel_ecriture'];
            }           

            $champs = " id_mouvement,
                        id_ecriture,
                        compte,
                        cpte_interne_cli,
                        sens,
                        montant,
                        devise ";

            $sql = "SELECT $champs FROM ad_mouvement WHERE id_ag=:id_agence AND id_ecriture=:id_ecriture ORDER BY sens DESC;";
            $params = array(':id_agence' => $id_agence, ':id_ecriture' => $key);

            $results = $remote_conn->prepareFetchAll($sql, $params);
             
            $retour['ecritures_distant'][$key]['mouvements'] = array();

            $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

            foreach ($results as $result) {
                if ($result['cpte_interne_cli'] != NULL) {
                    $CompteObj = new Compte($remote_conn, $id_agence);
                    $result['num_complet_cpte'] = $CompteObj->getClientAccount($id_compte);
                }
                $retour['ecritures_distant'][$key]['mouvements'][$count] = $result;
                $count++;
            }
        }

        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        return $retour;
    }
    
    /**  
     * Recupere le de total depot en deplace et de total retrait en deplace d'une agence
     * par rapport aux operations de ses clients dans d'autres agences
     * 
     * @param int $id_agc_ext
     * @return array $summary
     */  
    public static function getSummaryForVisualisationClientsInterne($criteres)
    {        
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();  

        $id_agc_ext = $criteres['IdAgence'];
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agc_ext);
               
        $fonction = $criteres['num_fonction'];
        $trans_reussi = $criteres['trans_reussi'];
        $num_client = $criteres['num_client'];
        $date_min = $criteres['date_min'];
        $date_max = $criteres['date_max'];
        $trans_local = $criteres['trans_local'];
        $trans_distant = $criteres['trans_distant'];   
        
        if(!empty($criteres['trans_reussi'])) {
            $success_flag = 'TRUE';
        }
        else $success_flag = 'FALSE';

        // START : RECUPERE INFOS SUR BASE EXTERNE
        
        $sql = "SELECT type_transaction, type_choix_libel, montant 
                FROM adsys_audit_multi_agence 
                WHERE id_ag_local=:id_ag_local 
                AND id_ag_distant=:id_ag_distant 
                AND success_flag=:success_flag AND  ";              
        
        if ($fonction != NULL) $sql .= "(type_transaction=:fonction) AND  ";
        if ($num_client != NULL) $sql .= "(id_client_distant=:num_client) AND  ";
        if ($date_min != NULL) $sql .= "(date_maj)>=DATE(:date_min) AND  ";
        if ($date_max != NULL) $sql .= "(date_maj)<=DATE(:date_max) AND  ";
        if ($trans_local != NULL) $sql .= "(id_his_local=:trans_local) AND  ";
        if ($trans_distant != NULL) $sql .= "(id_his_distant=:trans_distant) AND  ";
        
        $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
        $sql .= " ;";
        
        $params = array(':id_ag_local' => $id_agc_ext,
                        ':id_ag_distant' => $global_id_agence,
                        ':success_flag' => 'TRUE');  

        if ($fonction != NULL) $params[':fonction'] = $fonction;
        if ($num_client != NULL) $params[':num_client'] = $num_client;
        if ($date_min != NULL) $params[':date_min'] = $date_min;
        if ($date_max != NULL) $params[':date_max'] = $date_max;
        if ($trans_local != NULL) $params[':trans_local'] = $trans_local;
        if ($trans_distant != NULL) $params[':trans_distant'] = $trans_distant;
       
        $results = $remote_conn->prepareFetchAll($sql, $params);    
        
        $somme_depot = $somme_retrait = 0;
        $summary = array();
    
        foreach ($results as $row)
        {
            $type_transaction = trim($row['type_transaction']);
            $montant = trim($row['montant']);
             
            if($type_transaction == self::TRANSAC_DEPOT) {               
                $somme_depot += $montant;                
                $summary['total_depot'] = $somme_depot;
            }
            elseif($type_transaction == self::TRANSAC_RETRAIT) {
                $somme_retrait += $montant;               
                $summary['total_retrait'] = $somme_retrait;
            }
        }     

        // END : RECUPERE INFOS SUR BASE EXTERNE       
        
        // INFOS AGENCE :
        $agence_local = AgenceRemote::getRemoteAgenceName($global_id_agence);
        $agence_local = trim($agence_local);
        $summary['agence_locale'] = $agence_local;
        
        $agence_remote = AgenceRemote::getRemoteAgenceName($id_agc_ext);
        $agence_remote = trim($agence_remote);
        $summary['agence_externe'] = $agence_remote;
                   
        $dbHandler->closeConnection(true);
        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        return $summary;
    }


    /**
     * Recupere la situation de compensation de l'agence local vis-a-vis d'un agence distant
     * @param $filtres
     * @return array
     */
    private function getSituationLocalVsDistant($filtres)
    {
        global $dbHandler, $global_id_agence, $global_monnaie_courante;
        $db = $dbHandler->openConnection();

        $id_agc_ext = $filtres['IdAgence'];
        $date_debut = $filtres['date_debut'];
        $date_fin = $filtres['date_fin'];
        $cpte_liaison = $filtres['cpte_liaison_distant'];
        $code_devise = $filtres['code_devise'];

        $situation_local = array();
        $situation_local['total_depot'] = 0;
        $situation_local['total_retrait'] = 0;
        $situation_local['mvmts_deb'] = 0;
        $situation_local['mvmts_cred'] = 0;
        $situation_local['cpte_liaison'] = $cpte_liaison;

        //solde compte liaison :
        //get 'connection' for agence local
        $local_conn = AgenceRemote::getRemoteAgenceConnection($global_id_agence);
        $comptaObj = new Compta($local_conn, $global_id_agence);
        $soldes = $comptaObj->getSoldeCompteLiaisonForCompensation($cpte_liaison, $global_id_agence, $date_debut, $date_fin, $code_devise);

        // Nom agence local
        $agcLocalObj = new Agence($local_conn, $global_id_agence);
        $agcLocalName = $agcLocalObj->getAgenceName($global_id_agence);
        $situation_local['agence_local'] = $agcLocalName;

        // Nom agence distant
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agc_ext);
        $agcRemoteObj = new Agence($remote_conn, $id_agc_ext);
        $agcRemoteName = $agcRemoteObj->getAgenceName($id_agc_ext);
        $situation_local['agence_distant'] = $agcRemoteName;

        // Autres infos
        $situation_local['solde_deb'] = $soldes['solde_deb'];
        $situation_local['solde_fin'] = $soldes['solde_fin'];
        $situation_local['mvmts_deb'] = $soldes['mvmts_deb'];
        $situation_local['mvmts_cred'] = $soldes['mvmts_cred'];
        $situation_local['total_depot'] = $soldes['total_depot'];
        $situation_local['total_retrait'] = $soldes['total_retrait'];
        $situation_local['solde_comm_od_depot'] = $soldes['solde_comm_od_depot'];
        $situation_local['solde_comm_od_retrait'] = $soldes['solde_comm_od_retrait'];
        $situation_local['solde_comm_od'] = $soldes['solde_comm_od'];
        $situation_local['devise'] = $soldes['devise'];
        AgenceRemote::unsetRemoteAgenceConnection($local_conn);
        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        $dbHandler->closeConnection(true);
        return $situation_local ;
    }


    /**
     * Recupere la situation de compensation d'un agence distant vis-a-vis de agence local
     * @param $filtres
     * @return array
     */
    private function getSituationRemoteVsLocal($filtres)
    {
        global $global_id_agence, $global_monnaie_courante;

        $id_agc_ext = $filtres['IdAgence'];
        $date_debut = $filtres['date_debut'];
        $date_fin = $filtres['date_fin'];
        $cpte_liaison = $filtres['cpte_liaison_local'];
        $code_devise = $filtres['code_devise'];

        if(empty($id_agc_ext)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        // Recup le nom de l'agence local
        $agence_local = AgenceRemote::getRemoteAgenceName($global_id_agence);
        $agence_local = trim($agence_local);
        // Recup le nom de l'agence distant
        $agence_distant = AgenceRemote::getRemoteAgenceName($id_agc_ext);
        $agence_distant = trim($agence_distant);

        //get remote connection
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agc_ext);

        $situation_distant = array();
        $situation_distant['total_depot'] = 0;
        $situation_distant['total_retrait'] = 0;
        $situation_distant['mvmts_deb'] = 0;
        $situation_distant['mvmts_cred'] = 0;
        $situation_distant['cpte_liaison'] = $cpte_liaison;

        //solde compte liaison :
        $comptaObj = new Compta($remote_conn, $id_agc_ext);
        $soldes = $comptaObj->getSoldeCompteLiaisonForCompensation($cpte_liaison, $id_agc_ext, $date_debut, $date_fin, $code_devise);

        // Nom agence distant
        $agcRemoteObj = new Agence($remote_conn, $id_agc_ext);
        $agcRemoteName = $agcRemoteObj->getAgenceName($id_agc_ext);
        $situation_distant['agence_distant'] = $agcRemoteName;

        // Nom agence local
        $local_conn = AgenceRemote::getRemoteAgenceConnection($global_id_agence);
        $agcLocalObj = new Agence($local_conn, $global_id_agence);
        $agcLocalName = $agcLocalObj->getAgenceName($global_id_agence);
        $situation_distant['agence_local'] = $agcLocalName;

        $situation_distant['solde_deb'] = $soldes['solde_deb'];
        $situation_distant['solde_fin'] = $soldes['solde_fin'];
        $situation_distant['mvmts_deb'] = $soldes['mvmts_deb'];
        $situation_distant['mvmts_cred'] = $soldes['mvmts_cred'];
        $situation_distant['total_depot'] = $soldes['total_depot'];
        $situation_distant['total_retrait'] = $soldes['total_retrait'];
        $situation_distant['solde_comm_od_depot'] = $soldes['solde_comm_od_depot'];
        $situation_distant['solde_comm_od_retrait'] = $soldes['solde_comm_od_retrait'];
        $situation_distant['solde_comm_od'] = $soldes['solde_comm_od'];
        $situation_distant['devise'] = $soldes['devise'];

        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        AgenceRemote::unsetRemoteAgenceConnection($local_conn);
        return $situation_distant;
    }

    /**
     * Recupere les donnees pour les compensation siege lors des operations en deplacés
     * Fonction a mettre a jour lorsque le module de compensation sera developpé (quand ?)
     * @param $criteres
     * @return array
     */
    public function getSituationSiegeLocalVsDIstant($filtres){
      global $dbHandler, $global_id_agence, $global_monnaie_courante;
      $db = $dbHandler->openConnection();

      $id_agc_ext = $filtres['IdAgence'];
      $date_debut = $filtres['date_debut'];
      $date_fin = $filtres['date_fin'];
      $cpte_liaison = $filtres['cpte_liaison_local'];
      $code_devise = $filtres['code_devise'];

      $situation_local = array();
      $situation_local['total_depot'] = 0;
      $situation_local['total_retrait'] = 0;
      $situation_local['mvmts_deb'] = 0;
      $situation_local['mvmts_cred'] = 0;
      $situation_local['cpte_liaison'] = $cpte_liaison;

      //solde compte liaison :
      //get 'connection' for agence local
      $local_conn = AgenceRemote::getRemoteAgenceConnection($global_id_agence);
      $comptaObj = new Compta($local_conn, $global_id_agence);
      $soldes = $comptaObj->getSoldeCompteLiaisonForCompensationSiege($cpte_liaison, $id_agc_ext, $date_debut, $date_fin, $code_devise);

      // Nom agence local
      $agcLocalObj = new Agence($local_conn, $global_id_agence);
      $agcLocalName = $agcLocalObj->getAgenceName($global_id_agence);
      $situation_local['agence_local'] = $agcLocalName;

      // Nom agence distant
      $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agc_ext);
      $agcRemoteObj = new Agence($remote_conn, $id_agc_ext);
      $agcRemoteName = $agcRemoteObj->getAgenceName($id_agc_ext);
      $situation_local['agence_distant'] = $agcRemoteName;

      // Autres infos
      $situation_local['solde_deb'] = $soldes['solde_deb'];
      $situation_local['solde_fin'] = $soldes['solde_fin'];
      $situation_local['mvmts_deb'] = $soldes['mvmts_deb'];
      $situation_local['mvmts_cred'] = $soldes['mvmts_cred'];
      $situation_local['total_depot'] = $soldes['total_depot'];
      $situation_local['total_retrait'] = $soldes['total_retrait'];
      $situation_local['solde_comm_od_depot'] = $soldes['solde_comm_od_depot'];
      $situation_local['solde_comm_od_retrait'] = $soldes['solde_comm_od_retrait'];
      $situation_local['devise'] = $soldes['devise'];

      AgenceRemote::unsetRemoteAgenceConnection($local_conn);
      AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
      $dbHandler->closeConnection(true);
      return $situation_local ;
    }

    /**
     * Recupere les donnees pour les compensation siege lors des operations en deplacés
     * Fonction a mettre a jour lorsque le module de compensation sera developpé (quand ?)
     * @param $criteres
     * @return array
     */
    public function getSituationSiegeRemoteVsLocal($filtres){
        global $dbHandler, $global_id_agence, $global_monnaie_courante;
        $db = $dbHandler->openConnection();

        $id_agc_ext = $filtres['IdAgence'];
        $date_debut = $filtres['date_debut'];
        $date_fin = $filtres['date_fin'];
        $cpte_liaison = $filtres['cpte_liaison_distant'];
        $code_devise = $filtres['code_devise'];

        //get remote connection
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agc_ext);

        $situation_distant = array();
        $situation_distant['total_depot'] = 0;
        $situation_distant['total_retrait'] = 0;
        $situation_distant['mvmts_deb'] = 0;
        $situation_distant['mvmts_cred'] = 0;
        $situation_distant['cpte_liaison'] = $cpte_liaison;

        //solde compte liaison :
        //get 'connection' for agence local
        $comptaObj = new Compta($remote_conn, $id_agc_ext);
        $soldes = $comptaObj->getSoldeCompteLiaisonForCompensationSiege($cpte_liaison, $global_id_agence, $date_debut, $date_fin, $code_devise);

        // Nom agence local
        $local_conn = AgenceRemote::getRemoteAgenceConnection($global_id_agence);
        $agcLocalObj = new Agence($local_conn, $global_id_agence);
        $agcLocalName = $agcLocalObj->getAgenceName($global_id_agence);
        $situation_distant['agence_local'] = $agcLocalName;

        // Nom agence distant
        //$remote_conn = AgenceRemote::getRemoteAgenceConnection($id_agc_ext);
        $agcRemoteObj = new Agence($remote_conn, $id_agc_ext);
        $agcRemoteName = $agcRemoteObj->getAgenceName($id_agc_ext);
        $situation_distant['agence_distant'] = $agcRemoteName;

        // Autres infos
        $situation_distant['solde_deb'] = $soldes['solde_deb'];
        $situation_distant['solde_fin'] = $soldes['solde_fin'];
        $situation_distant['mvmts_deb'] = $soldes['mvmts_deb'];
        $situation_distant['mvmts_cred'] = $soldes['mvmts_cred'];
        $situation_distant['total_depot'] = $soldes['total_depot'];
        $situation_distant['total_retrait'] = $soldes['total_retrait'];
        $situation_distant['total_retrait'] = $soldes['total_retrait'];
        $situation_distant['solde_comm_od_depot'] = $soldes['solde_comm_od_depot'];
        $situation_distant['solde_comm_od_retrait'] = $soldes['solde_comm_od_retrait'];
        $situation_distant['devise'] = $soldes['devise'];

        AgenceRemote::unsetRemoteAgenceConnection($local_conn);
        AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
        $dbHandler->closeConnection(true);
        return $situation_distant ;
    }

    /**
     * Recupere les donnees pour les compensation inter-agences lors des operations en deplacés
     * Fonction a mettre a jour lorsque le module de compensation sera developpé (quand ?)
     * @param $criteres
     * @return array
     */
    public function getMultiAgencesCompensationData($criteres)
    {
        global $dbHandler, $global_multidevise, $global_id_agence;

        $db = $dbHandler->openConnection();

        $compensation_data = array();

        $criteres_recherche = $criteres['criteres_recherche'];
        $IdAgence = $criteres_recherche['IdAgence'];

        // Compte liaison de l'agence local:
        $cpte_liaison_ag_local = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);

        $listAgences = AgenceRemote::getListRemoteAgence(true); // get list of online agencies

        // Loop through devises
        $sql = "SELECT code_devise FROM devise WHERE id_ag=".$global_id_agence." ORDER BY code_devise ASC;";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        while ($row = $result->fetchrow()) {
            $code_devise = $row[0];

            $str_code_devise = '';
            if ($global_multidevise) {
                $str_code_devise = '.'.$code_devise;
            }

            // An id_agence externe was supplied
            if(!is_null($IdAgence))
            {
                // the supplied agence is online
                if(array_key_exists($IdAgence, $listAgences))
                {
                    $filtres = array();
                    $filtres['IdAgence'] = $IdAgence;
                    $filtres['date_debut'] = $criteres_recherche['date_debut'];
                    $filtres['date_fin'] = $criteres_recherche['date_fin'];
                    $filtres['cpte_liaison_distant'] = $listAgences[$IdAgence]['compte_liaison'].$str_code_devise;
                    $filtres['cpte_liaison_local'] = $cpte_liaison_ag_local.$str_code_devise;
                    $filtres['code_devise'] = $code_devise;

                    $situation_local_data = $this->getSituationLocalVsDistant($filtres);
                    $situation_distant_data = $this->getSituationRemoteVsLocal($filtres);

                    if (isCompensationSiege() && isMultiAgenceSiege()){
                      $situation_local_data = $this->getSituationSiegeLocalVsDistant($filtres);
                      $situation_distant_data = $this->getSituationSiegeRemoteVsLocal($filtres);
                    }

                    $compensation_data[$code_devise][$IdAgence]['situation_local_data'] = $situation_local_data;
                    $compensation_data[$code_devise][$IdAgence]['situation_distant_data'] = $situation_distant_data;
                }
                else { // throw an exception because the supplied agency isnt online
                    signalErreur(__FILE__,__LINE__,__FUNCTION__);
                }
            }
            else // no id_agence was supplied, therefore we take all
            {
                foreach($listAgences as $id_agence_remote=>$agence_remote_data)
                {
                    $filtres = array();
                    $filtres['IdAgence'] = $id_agence_remote;
                    $filtres['date_debut'] = $criteres_recherche['date_debut'];
                    $filtres['date_fin'] = $criteres_recherche['date_fin'];
                    $filtres['cpte_liaison_distant'] = $agence_remote_data['compte_liaison'].$str_code_devise;
                    $filtres['cpte_liaison_local'] = $cpte_liaison_ag_local.$str_code_devise;
                    $filtres['code_devise'] = $code_devise;

                    $situation_local_data = $this->getSituationLocalVsDistant($filtres);
                    $situation_distant_data = $this->getSituationRemoteVsLocal($filtres);

                    if (isCompensationSiege() && isMultiAgenceSiege()){
                      $situation_local_data = $this->getSituationSiegeLocalVsDistant($filtres);
                      $situation_distant_data = $this->getSituationSiegeRemoteVsLocal($filtres);
                    }

                    $compensation_data[$code_devise][$id_agence_remote]['situation_local_data'] = $situation_local_data;
                    $compensation_data[$code_devise][$id_agence_remote]['situation_distant_data'] = $situation_distant_data;
                }
            }
        }

        $dbHandler->closeConnection(true);
        return $compensation_data;
    }

}

?>