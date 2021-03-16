<?php
    // On charge les variables globales
    require_once '/usr/share/adbanking/web/services/misc_api.php';
    require_once '/usr/share/adbanking/web/services/mobile_lending/function_algo.php';
    require_once '/usr/share/adbanking/web/lib/misc/password_encrypt_decrypt.php';
    require_once '/usr/share/adbanking/web/lib/php-amqplib/MouvementMSQPublisher.php';
    require_once '/usr/share/adbanking/web/ad_acu/app/erreur.php';
    require_once '/usr/share/adbanking/web/lib/misc/password_encrypt_decrypt.php';

    /***********************************************************************************************************/
    global $DB_host, $DB_name, $DB_user, $DB_cluster,$DB_dsn,$dbHandler, $adsys,
           $global_id_agence, $global_id_client, $global_id_exo, $global_monnaie, $appli, $global_nom_login;
    $dbHandler = new handleDB();
    $ini_array = array();
    $DB_host = "localhost";
    $DB_user = "adbanking";
    $adbanking_configuration = parse_ini_file("/usr/share/adbanking/web/jasper_config/adbanking".$_REQUEST["id_agence"].".ini");
    $DB_name = $adbanking_configuration["DB_name"];
    $DB_pass_conf = $adbanking_configuration["DB_pass"];
    $password_converter = new Encryption;
    $decoded_password = $password_converter->decode($DB_pass_conf);
    $DB_pass = $decoded_password;
    $appli = 'main';

    $identifiant_client = $_REQUEST['identifiant_client'];
    $id_client = intval(substr($identifiant_client, -8));
    $montant = $_REQUEST['montant'];
    $devise = $_REQUEST['devise'];
    $duree = $_REQUEST['duree'];
    $id_transaction = $_REQUEST['id_transaction_mobile'];
    $code_agent = $_REQUEST['code_agent'];
    $code_imf = $_REQUEST['code_imf'];
    $id_agence = $_REQUEST['id_agence'];
    $signature_contrat = 't';
    $telephone = $_REQUEST['num_sms'];
    $statut_demande = $_REQUEST['statut_demande'];
    $err_msg = "";
    $global_id_agence = $id_agence;
    $global_id_client = $id_client;
    $status_msg = 7;
    //AT-31 : securisé le mot de passe
    //$password_converter = new Encryption;
    //$decoded_password = $password_converter->decode($argv[2]);
    //$DB_pass = $decoded_password;


    // Connexion par socket UNIX
    $DB_dsn = sprintf("pgsql://%s:%s@/%s", $DB_user, $DB_pass, $DB_name);
    // FIXME le DSN "unix()" n'est actuellement pas correctement reconnu par PEAR:DB, il faut donc utiliser la syntaxe ci-avant pour se connecter par le socket.
    // voir http://pear.php.net/bugs/bug.php?id=339&edit=1
    //$DB_dsn = sprintf("pgsql://%s:%s@unix(%s:%s)/%s", $ini_array["DB_user"], $ini_array["DB_pass"], $ini_array["DB_socket"], $ini_array["DB_port"], $DB_name);


    class handleDB {

        /**
         * Nombre de connexions à la base de données
         * @var int
         */
        var $count;

        /**
         * Handler de connexion à la DB
         * @var handler
         */
        var $handle;

        /**
         * Indique si un ROLLBACK a été demandé par une fonction
         * @var bool
         */
        var $cancel;

        /**
         * Constructor
         * @return object
         */
        function handleDB() {
            $this->count = 0;
            $this->handle = NULL;
            $this->cancel = false;
            return $this;
        }

        /**
         * Renvoie un handler de connexion à la DB
         * Méthode invoquée par toute fonction qui désire effectuer des opérations sur la DB.
         * Si aucune connexion n'avait été précédemment effectuée, ouvre une nouvelle connexion.
         * @return handler Un handler de connexion
         */
        function openConnection() {
            global $DB_dsn, $DEBUG;

            if ($this->count == 0) {
                require_once 'DB.php';
                $db = DB::connect($DB_dsn, false);
                if (DB::isError($db)) {
                    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible d'établir la connexion avec la BD")." : ".$db->getmessage());
                    echo "Impossible d'établir la connexion avec la BD =>".$db->getMessage();
                    exit();
                }
                /*if ($DEBUG) {
                  $sqlLogActivate = array("SET log_statement = 'all';", "SET log_min_error_statement = 'WARNING';");
                  foreach ($sqlLogActivate as $sql) {
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Problème à l'activation de la trace PSQL")." : ".$result->getMessage());
                    }
                  }
                } else {
                  $sqlLogActivate = array("SET log_statement = 'none';", "SET log_min_error_statement = 'PANIC';");
                  foreach ($sqlLogActivate as $sql) {
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Problème à la désactivation de la trace PSQL")." : ".$result->getMessage());
                    }
                  }
                }*/
                $result = $db->query("BEGIN");
                if (DB::isError($result)) {
                    //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible de démarrer la transaction")." : ".$result->getMessage());
                    echo "Impossible d'établir la connexion avec la BD =>".$result->getMessage();
                    exit();
                }
                $this->handle = $db;
            }
            ++$this->count;
            return $this->handle;
        }

        /**
         * Fonction privée effectuant le COMMIT ou le ROLLBACK lorsque le compteur d'accès à la DB passe à 0
         * @access private
         */
        function closeConnectionPrivate() {
            if ($this->cancel == true) $result = $this->handle->query("ROLLBACK");
            else $result = $this->handle->query("COMMIT");

            if (DB::isError($result)) echo "Error closeConnectionPrivate found! \n";
            $this->handle->disconnect();
        }

        /**
         * Méthode invoquée lorsqu'une fonction a terminé ses traitements sur la DB
         * Si le compteur de connexion passe à 0, invoquer {@link #closeConnectionPrivate}
         * @param bool $commit indique si un COMMIT doit être effectué (1) ou un ROLLBACK(0)
         * @return bool true si la fermeture de connexion a pu avoir lieu
         */
        function closeConnection($commit) {
            if ($this->count > 0) { //S'il reste des connexions ouvertes
                --$this->count;
                if (($commit == true) && ($this->cancel == true)) {
                    echo "<br /><font color=\"red\">"."Le commit ne peut avoir lieu car un ROLLBACK a déjà été demandé"."</font><br />";
                }
                if ((($this->count == 0) || ($commit == false)) && ($this->cancel == false)) {
                    /*Si (on vient de fermer la dernière des  connexions ou qu'il s'agit d'un ROLLBACK  mais qu'il n'y a pas encore eu de ROLLBACK auparavant*/
                    if ($commit == false)
                        $this->cancel = true;
                    $this->closeConnectionPrivate(); //Ferme la connexion (COMMIT ou ROLLBACK)
                    $this->handle = NULL;
                }
                if ($this->count == 0) {
                    $this->cancel = false;
                }
                return true;
            } else return false;
        }

        function getOpenConnection(){
            return  $this->count;
        }
    }

    //Verification sui la demande st dupliquer
    $where_condi_exist = " id_transaction = '".$id_transaction."' ";
    $data_ml_demande_exist = getDemandeCredit($id_client, $where_condi_exist);
    if (sizeof($data_ml_demande_exist) > 0){
        $return_data = array(
            'success' => false,
            'datas' => array(
                'duplicate' => true,
                'code_imf' => $code_imf,
                'telephone' => $telephone,
                'id_trans_ext' => $id_transaction
            ),
        );
        echo json_encode($return_data, 1 | 4 | 2 | 8);
        exit;
    }


    $data_entry_ussd = array(
        'id_client' => $id_client,
        'id_doss' => -1,
        'mnt_dem' => $montant,
        'devise' => $devise,
        'duree' => $duree,
        'id_transaction' => $id_transaction,
        'code_agent' => intval($code_agent),
        'code_imf' => $code_imf,
        'signature_contrat' => $signature_contrat,
        'telephone' => $telephone,
        'statut_demande' => 1,
        'date_creation' => date('r')
    );

    $data_entry_dcr = array();
    $client_dataset = getClientDatas($data_entry_ussd['id_client']);
    $donnees_client_abonnees = current(getDataClientAbonnee($data_entry_ussd['id_client']));
    $client_abonnement = getClientAbonnement(" WHERE id_client = ".$data_entry_ussd['id_client']." AND deleted = 'f' AND id_service = 1");

    $AGC = getAgenceDatas($global_id_agence);
    $valeurs = getCustomLoginInfo();
    $global_id_exo = $AGC["exercice"];
    $global_monnaie = $valeurs["monnaie"];
    $global_nom_login = $valeurs["login"];
    $utilisateur = getAgentMobileLending();
    $client_demande = getDemandeCredit($data_entry_ussd['id_client']);
    $object_credit = array_keys(getObjetsCredit('M.ES.O'));
    $detail_demande = array_keys(getDetailsObjCredit('M.ES.O.1'));
    if ($client_abonnement['salarie'] == 't'){
        $product_dataset = getProdInfo(" where is_mobile_lending_credit = 't' AND crdt_salairie = 't' ");
    }else{
        $product_dataset = getProdInfo(" where is_mobile_lending_credit = 't' AND crdt_salairie = 'f' ");
    }

    if(intval($data_entry_ussd["code_agent"]) >= 1){
        $agent_code = current(getUtilisateurs($data_entry_ussd["code_agent"]));
    }else{
        $agent_code = null;
    }

    //Verification si le client a deja un credit en cours et attente
    $condition_encours = " statut_demande IN (2,5)";
    $dcr_encours = getDemandeCredit($data_entry_ussd['id_client'],$condition_encours);

    $condition_en_attente = " statut_demande IN (4)";
    $dcr_attente = getDemandeCredit($data_entry_ussd['id_client'],$condition_en_attente);
    if (sizeof($dcr_encours) > 0){
        $msg_err = new ErrorObj (ERR_GENERIQUE, _("Votre demande a été rejeté car vous avez un autre dossier en cours"));
        $err_msg = $error[$msg_err->errCode]." ".$msg_err->param;
        $status_msg = 8;
    }
    else if (sizeof($dcr_attente) > 0){
        $msg_err = new ErrorObj (ERR_GENERIQUE, _("Votre demande a été rejetée car vous avez un autre dossier en attente"));
        $err_msg = $error[$msg_err->errCode]." ".$msg_err->param;
        $status_msg = 9;
    }
    else if(($product_dataset->errCode == NO_ERR) && ($err_msg == "")) {
        $product_dataset = current($product_dataset);
        $data_entry_ussd['id_prod'] = $product_dataset['id'];

        $db = $dbHandler->openConnection();
        $sql_query = buildInsertQuery("ml_demande_credit", $data_entry_ussd);


        $result = $db->query($sql_query);
        if (DB:: isError($result)) {
            $dbHandler->closeConnection(false);
            $err_obj = new ErrorObj(ERR_DB_SQL, _("ligne: " . __LINE__ . " $sql_query"));
            $err_msg = $error[$err_obj->errCode] . " " . $err_obj->param;
        }
        $dbHandler->closeConnection(true);

        $DATA['signature_contrat'] = 't';
        $DATA['id_client'] = $data_entry_ussd['id_client'];
        $err = updateAbonnement($DATA);
        if ($err->errCode != NO_ERR) {
            $err_msg = $error[$err->errCode] . " " . $err->param;
        }

        // recuperation des données de IMF
        $data_imf = getDataMlIMF();

        if ($donnees_client_abonnees['score_final'] >= 0 && $donnees_client_abonnees['score_final'] < $data_imf['seuil_score_deb']) {
            $status_dossier = 'rejet';
        } else if ($donnees_client_abonnees['score_final'] >= $data_imf['seuil_score_deb'] && $donnees_client_abonnees['score_final'] < $data_imf['seuil_score_fin']) {
            $status_dossier = 'attente';
        } else if ($donnees_client_abonnees['score_final'] >= $data_imf['seuil_score_fin']) {
            $status_dossier = 'deboursement';
        } else {
            $status_dossier = null;
        }


        //Termes
        $adsys["adsys_termes_credit"][1]['libel'] = _("Court terme");
        $adsys["adsys_termes_credit"][1]['mois_min'] = 0;
        $adsys["adsys_termes_credit"][1]['mois_max'] = 12;
        $adsys["adsys_termes_credit"][2]['libel'] = _("Moyen terme");
        $adsys["adsys_termes_credit"][2]['mois_min'] = 13;
        $adsys["adsys_termes_credit"][2]['mois_max'] = 36;
        $adsys["adsys_termes_credit"][3]['libel'] = _("Long terme");
        $adsys["adsys_termes_credit"][3]['mois_min'] = 37;
        $adsys["adsys_termes_credit"][3]['mois_max'] = 0;


        if (($data_entry_ussd["duree"] >= $adsys["adsys_termes_credit"][1]['mois_min'])
            && ($data_entry_ussd["duree"] <= $adsys["adsys_termes_credit"][1]['mois_max']))
            $data_entry_dcr[$id_client]["terme"] = 1; // Court terme

        if (($data_entry_ussd["duree"] >= $adsys["adsys_termes_credit"][2]['mois_min'])
            && ($data_entry_ussd["duree"] <= $adsys["adsys_termes_credit"][2]['mois_max']))
            $data_entry_dcr[$id_client]["terme"] = 2; // Moyen terme

        if ($data_entry_ussd["duree"] >= $adsys["adsys_termes_credit"][3]['mois_min'])
            $data_entry_dcr[$id_client]["terme"] = 3; // Long terme


        $data_entry_dcr [$id_client]["id_dcr_grp_sol"] = NULL;
        $data_entry_dcr [$id_client]["id_prod"] = $data_entry_ussd['id_prod'];
        $data_entry_dcr [$id_client]["is_extended"] = 'f';
        $data_entry_dcr [$id_client]["mnt_dem"] = $data_entry_ussd['mnt_dem'];
        $data_entry_dcr [$id_client]["differe_jours"] = NULL;
        $data_entry_dcr [$id_client]["differe_ech"] = NULL;
        $data_entry_dcr [$id_client]["date_dem"] = $data_entry_ussd['date_creation'];
        $data_entry_dcr [$id_client]["cre_date_debloc"] = NULL; //Date de deboursement
        $data_entry_dcr [$id_client]["etat"] = 1;  //Etat --> En attente de decision
        $data_entry_dcr [$id_client]["date_etat"] = date("d/m/Y");  //Etat --> Date changement du etat dossier
        $data_entry_dcr [$id_client]["id_agent_gest"] = $agent_code["id_utilis"];
        $data_entry_dcr [$id_client]["duree_mois"] = $data_entry_ussd['duree'];
        $data_entry_dcr [$id_client]["delai_grac"] = NULL;
        $data_entry_dcr [$id_client]["nb_jr_bloq_cre_avant_ech"] = NULL;
        $data_entry_dcr [$id_client]["prelev_commission"] = 'f';
        $data_entry_dcr [$id_client]["assurances_cre"] = 'f';
        $data_entry_dcr [$id_client]["gar_num"] = 0;
        $data_entry_dcr [$id_client]["gar_num_encours"] = 0;
        $data_entry_dcr [$id_client]["gar_mat"] = 0;
        $data_entry_dcr [$id_client]["gar_tot"] = 0;
        $data_entry_dcr [$id_client]["id_client"] = $data_entry_ussd['id_client'];
        $data_entry_dcr [$id_client]["cpt_liaison"] = $client_dataset['id_cpte_base'];
        $data_entry_dcr [$id_client]["id_bailleur"] = NULL;
        $data_entry_dcr [$id_client]["obj_dem"] = $object_credit[0];
        $data_entry_dcr [$id_client]["detail_obj_dem_bis"] = $detail_demande[0];
        $data_entry_dcr [$id_client]["detail_obj_dem_2"] = 0;
        $data_entry_dcr [$id_client]["prelev_auto"] = 't';
        $data_entry_dcr [$id_client]["mnt_frais_doss"] = 0;
        $data_entry_dcr [$id_client]["mnt_commission"] = 0;
        $data_entry_dcr [$id_client]["cre_nbre_reech"] = 0;
        $data_entry_dcr [$id_client]["num_cre"] = getNumCredit($data_entry_ussd['id_client']) + 1;
        $data_entry_dcr [$id_client]["suspension_pen"] = 'f';
        $data_entry_dcr [$id_client]["gs_cat"] = 0;
        $data_entry_dcr [$id_client]["cpt_prelev_frais"] = $client_dataset['id_cpte_base'];   //Default to cpt_liason

        switch ($status_dossier) {
            case 'rejet':
                $ad_dcr_insertion = insereDossierUSSD($data_entry_dcr, 105);
                if ($ad_dcr_insertion->errCode == NO_ERR) {
                    $id_doss = $ad_dcr_insertion->param[$id_client];
                    $data_entry_dcr [$id_doss] = $data_entry_dcr[$id_client];
                    $data_modif_dcr [$id_doss]["etat"] = 3;
                    $data_modif_dcr [$id_doss]["date_etat"] = $data_entry_dcr [$id_client]["date_etat"];
                    $data_modif_dcr [$id_doss]["motif"] = NULL;
                    $data_modif_dcr [$id_doss]["details_motif"] = NULL;
                    $data_modif_dcr [$id_doss]["id_client"] = $id_client;
                    $data_modif_dcr [$id_doss]["last_etat"] = $data_entry_dcr [$id_client]["etat"];
                    $err = rejetDossierUSSD($data_modif_dcr);
                    if ($err->errCode == NO_ERR) {
                        $err = updateDemandeCredit($id_doss, $id_client, 6, $data_entry_ussd['id_transaction']);
                        if ($err->errCode == NO_ERR) {
                            $return_data = array(
                                'success' => true,
                                'datas' => array(
                                    'code_imf' => $data_entry_ussd['code_imf'],
                                    'montant' => $data_entry_dcr[$id_doss]['mnt_dem'],
                                    'statut' => 6,
                                    'telephone' => $data_entry_ussd['telephone'],
                                    'taux_interet' => $product_dataset['tx_interet'],
                                    'date_ech' => null,
                                    'id_doss' => $id_doss,
                                    'id_trans_ext' => $data_entry_ussd['id_transaction'],
                                    'langue_id' => $client_abonnement['langue'],
                                    'prenom' => $client_dataset['pp_prenom'],
                                    'num_imf' => $AGC['tel']
                                ),
                            );
                        } else {
                            $err_msg = $error[$err->errCode] . " " . $err->param;
                        }
                    } else {
                        $err_msg = $error[$err->errCode] . " " . $err->param;
                    }
                } else {
                    $err_msg = $error[$ad_dcr_insertion->errCode] . " " . $ad_dcr_insertion->param;
                }
                break;
            case 'attente':
                $ad_dcr_insertion = insereDossierUSSD($data_entry_dcr, 105);
                if ($ad_dcr_insertion->errCode == NO_ERR) {
                    $id_doss = $ad_dcr_insertion->param[$id_client];
                    $err = updateDemandeCredit($id_doss, $id_client, 4, $data_entry_ussd['id_transaction']);
                    $data_entry_dcr[$id_doss] = $data_entry_dcr[$id_client];
                    if ($err->errCode == NO_ERR) {
                        $return_data = array(
                            'success' => true,
                            'datas' => array(
                                'code_imf' => $data_entry_ussd['code_imf'],
                                'montant' => $data_entry_dcr[$id_doss]['mnt_dem'],
                                'statut' => 4,
                                'telephone' => $data_entry_ussd['telephone'],
                                'taux_interet' => $product_dataset['tx_interet'],
                                'date_ech' => null,
                                'id_doss' => $id_doss,
                                'id_trans_ext' => $data_entry_ussd['id_transaction'],
                                'langue_id' => $client_abonnement['langue'],
                                'prenom' => $client_dataset['pp_prenom'],
                                'num_imf' => $AGC['tel']
                            ),
                        );
                    } else {
                        $err_msg = $error[$err->errCode] . " " . $err->param;
                    }
                } else {
                    $err_msg = $error[$ad_dcr_insertion->errCode] . " " . $ad_dcr_insertion->param;
                }
                break;
            case 'deboursement':
                $ad_dcr_insertion = insereDossierUSSD($data_entry_dcr, 105);
                if ($ad_dcr_insertion->errCode == NO_ERR) {
                    $id_doss = $ad_dcr_insertion->param[$id_client];
                    $data_update_dcr[$id_doss] = $data_entry_dcr[$id_client];   //Change key
                    $premier_credit = isPremierCreditMobileLending($data_update_dcr [$id_doss]["id_client"]);
                    $premier_credit['premier_credit'] == 't'?($product_dataset['tx_interet'] = 0):$product_dataset['tx_interet'];
                    $data_update_dcr[$id_doss]["etat"] = 2;  //Etat: Accepté
                    $data_update_dcr[$id_doss]["id_ag"] = $global_id_agence;
                    $data_update_dcr[$id_doss]["id_doss"] = $id_doss;
                    $data_update_dcr[$id_doss]["detail_obj_dem"] = NULL;
                    $data_update_dcr[$id_doss]["motif"] = NULL;
                    $data_update_dcr[$id_doss]["nouv_duree_mois"] = NULL;
                    $data_update_dcr[$id_doss]["cpt_gar_encours"] = NULL;
                    $data_update_dcr[$id_doss]["cre_id_cpte"] = NULL;
                    $data_update_dcr[$id_doss]["cre_etat"] = NULL;
                    $data_update_dcr[$id_doss]["cre_date_debloc"] = date("d/m/Y");
                    $data_update_dcr[$id_doss]["cre_date_etat"] = date("d/m/Y");
                    $data_update_dcr[$id_doss]["cre_date_approb"] = date("d/m/Y");
                    $data_update_dcr[$id_doss]["cre_mnt_octr"] = $data_update_dcr[$id_doss]["mnt_dem"];
                    $data_update_dcr[$id_doss]["perte_capital"] = 0;
                    $data_update_dcr[$id_doss]["cre_retard_etat_max"] = NULL;
                    $data_update_dcr[$id_doss]["cre_retard_etat_max_jour"] = NULL;
                    $data_update_dcr[$id_doss]["cre_prelev_frais_doss"] = 'f';
                    $data_update_dcr[$id_doss]["prov_mnt"] = 0;
                    $data_update_dcr[$id_doss]["prov_date"] = NULL;
                    $data_update_dcr[$id_doss]["prov_is_calcul"] = 't';
                    $data_update_dcr[$id_doss]["doss_repris"] = 'f';
                    $data_update_dcr[$id_doss]["cre_cpt_att_deb"] = NULL;
                    $data_update_dcr[$id_doss]["date_creation"] = date("d/m/Y");
                    $data_update_dcr[$id_doss]["date_modif"] = date("d/m/Y");
                    $data_update_dcr[$id_doss]["is_ligne_credit"] = 'f';
                    $data_update_dcr[$id_doss]["deboursement_autorisee_lcr"] = 't';
                    $data_update_dcr[$id_doss]["motif_changement_authorisation_lcr"] = NULL;
                    $data_update_dcr[$id_doss]["date_changement_authorisation_lcr"] = NULL;
                    $data_update_dcr[$id_doss]["duree_nettoyage_lcr"] = 0;
                    $data_update_dcr[$id_doss]["remb_auto_lcr"] = 'f';
                    $data_update_dcr[$id_doss]["tx_interet_lcr"] = 0;
                    $data_update_dcr[$id_doss]["taux_frais_lcr"] = 0;
                    $data_update_dcr[$id_doss]["taux_min_frais_lcr"] = 0;
                    $data_update_dcr[$id_doss]["taux_max_frais_lcr"] = 0;
                    $data_update_dcr[$id_doss]["ordre_remb_lcr"] = 1;
                    $data_update_dcr[$id_doss]["mnt_assurance"] = 0;
                    $data_update_dcr[$id_doss]["cre_mnt_bloq"] = 0;
                    $data_update_dcr[$id_doss]["interet_remb_anticipe"] = NULL;
                    $data_update_dcr[$id_doss]["diff_ech_apres_deb"] = NULL;
                    $data_update_dcr[$id_doss]["devise"] = "RWF";
                    $data_update_dcr[$id_doss]["max_jours_compt_penalite"] = 0;
                    $data_update_dcr[$id_doss]["last_etat"] = 2;
                    $data_update_dcr[$id_doss]["gar_num_mob"] = 0;
                    $data_update_dcr[$id_doss]["gar_mat_mob"] = 0;
                    $data_update_dcr[$id_doss]["cre_mnt_a_deb"] = $data_update_dcr[$id_doss]["mnt_dem"];
                    $data_update_dcr[$id_doss]["mnt_tax_commission"] = 0;
                    $data_update_dcr[$id_doss]["mnt_frais"] = 0;
                    $data_update_dcr[$id_doss]["cre_mnt_deb"] = 0;
                    $data_update_dcr[$id_doss]["details_motif"] = NULL;
                    $data_update_dcr[$id_doss]["DATA_GAR"] = array();
                    $data_update_dcr[$id_doss]["transfert_ass"] = NULL;
                    $data_update_dcr[$id_doss]["transfert_com"] = NULL;
                    $data_update_dcr[$id_doss]["transfert_frais"] = NULL;

                    $ref = $data_update_dcr [$id_doss];
                    $ech_param["lib_date"] = _("Date de déboursement");
                    $ech_param["index"] = 0;
                    $ech_param["titre"] = _("Echéancier réel de remboursement");
                    $ech_param["nbre_jour_mois"] = 30;
                    $ech_param["montant"] = $data_update_dcr[$id_doss]["cre_mnt_octr"];
                    $ech_param["mnt_reech"] = '0';
                    $ech_param["mnt_octr"] = $data_update_dcr[$id_doss]["cre_mnt_octr"];
                    $ech_param["prelev_commission"] = $data_update_dcr [$id_doss]["prelev_commission"];
                    $ech_param["mnt_assurance"] = $data_update_dcr[$id_doss]["mnt_assurance"];
                    $ech_param["mnt_commission"] = $data_update_dcr[$id_doss]["mnt_commission"];
                    $ech_param["mnt_tax_commission"] = $data_update_dcr[$id_doss]["mnt_tax_commission"];
                    $ech_param["mnt_des_frais"] = 0;
                    $ech_param["debours"] = "true";
                    $ech_param["prelev_frais_doss"] = $product_dataset ["prelev_frais_doss"];
                    $ech_param["garantie"] = 0;
                    $ech_param["duree"] = $data_update_dcr [$id_doss]["duree_mois"];
                    $ech_param["date"] = $data_update_dcr [$id_doss]["cre_date_debloc"];
                    $ech_param["id_prod"] = $data_update_dcr [$id_doss]["id_prod"];
                    $ech_param["id_doss"] = $data_update_dcr [$id_doss]["id_doss"];
                    $ech_param["differe_jours"] = 0;
                    $ech_param["differe_ech"] = 0;
                    $ech_param["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
                    $ech_param["id_client"] = $data_update_dcr [$id_doss]["id_client"];

                    $echeancier = calcul_echeancier_theorique($ref["id_prod"], $ref["cre_mnt_octr"], $ref["duree_mois"], 0, 0, NULL, 1, $ref["id_doss"], null, $premier_credit['premier_credit']);
                    if ($echeancier->errCode != NO_ERR) {
                        $err_msg = $error[$echeancier->errCode] . " " . $echeancier->param;
                    }
                    $echeancier_full_dataset = completeEcheancier($echeancier, $ech_param);
                    if ($echeancier_full_dataset->errCode != NO_ERR) {
                        $err_msg = $error[$echeancier_full_dataset->errCode] . " " . $echeancier_full_dataset->param;
                    }

                    $data_update_dcr[$id_doss]["etr"] = $echeancier_full_dataset;
                    $data_update_dcr[$id_doss]["data_cpt_cre"] = array();
                    $data_update_dcr[$id_doss]["transfert_com"] = NULL;
                    $data_update_dcr[$id_doss]["transfert_fond"] = array(
                        "id_cpte_cli" => $data_update_dcr[$id_doss]["cpt_liaison"],
                        "montant" => $data_update_dcr[$id_doss]["mnt_dem"]
                    );
                    $data_update_dcr[$id_doss]["data_cpt_cre"] = array(
                        "utilis_crea" => intval($utilisateur),
                        "id_titulaire" => $data_update_dcr [$id_doss]["id_client"],
                        "etat_cpte" => 1,
                        "date_ouvert" => date("d/m/Y"),
                        "id_prod" => $AGC["id_prod_cpte_credit"],
                        "devise" => 'RWF'
                    );

                    $total_cap = 0;
                    $total_int = 0;
                    $total_gar = 0;
                    while (list(, $ech) = each($data_update_dcr[$id_doss]["etr"])) {
                        $total_cap += $ech["mnt_cap"];
                        $total_int += $ech["mnt_int"];
                        $total_gar += $ech["mnt_gar"];
                    }

                    $data_update_dcr[$id_doss]["total_cap"] = $total_cap;
                    $data_update_dcr[$id_doss]["total_int"] = $total_int;
                    $data_update_dcr[$id_doss]["total_gar"] = $total_gar;

                    $err = deboursementCreditUSSD($data_update_dcr, 1, 2, null);

                    if ($err->errCode == NO_ERR) {
                        $err = updateDemandeCredit($id_doss, $id_client, 2, $data_entry_ussd['id_transaction']);
                        $DATA['premier_credit'] = 'f';
                        $DATA['signature_contrat'] = 't';
                        $DATA['id_client'] = $data_entry_ussd['id_client'];
                        $err = updateAbonnement($DATA);
                        if ($err->errCode != NO_ERR) {
                            $err_msg = $error[$err->errCode] . " " . $err->param;
                        }
                        if ($err->errCode == NO_ERR) {
                            if ($client_abonnement['salarie'] == 't'){
                                $return_data = array(
                                    'success' => true,
                                    'datas' => array(
                                        'code_imf' => $data_entry_ussd['code_imf'],
                                        'montant' => $data_update_dcr[$id_doss]['mnt_dem'],
                                        'statut' => 10,
                                        'telephone' => $data_entry_ussd['telephone'],
                                        'taux_interet' => $product_dataset['tx_interet'],
                                        'date_ech' => $echeancier_full_dataset[1]["date_ech"],
                                        'id_doss' => $data_update_dcr[$id_doss]["id_doss"],
                                        'id_trans_ext' => $data_entry_ussd['id_transaction'],
                                        'langue_id' => $client_abonnement['langue'],
                                        'prenom' => $client_dataset['pp_prenom'],
                                        'num_imf' => $AGC['tel']
                                    ),
                                );
                            }else{
                                $return_data = array(
                                    'success' => true,
                                    'datas' => array(
                                        'code_imf' => $data_entry_ussd['code_imf'],
                                        'montant' => $data_update_dcr[$id_doss]['mnt_dem'],
                                        'statut' => 2,
                                        'telephone' => $data_entry_ussd['telephone'],
                                        'taux_interet' => $product_dataset['tx_interet'],
                                        'date_ech' => $echeancier_full_dataset[1]["date_ech"],
                                        'id_doss' => $data_update_dcr[$id_doss]["id_doss"],
                                        'id_trans_ext' => $data_entry_ussd['id_transaction'],
                                        'langue_id' => $client_abonnement['langue'],
                                        'prenom' => $client_dataset['pp_prenom'],
                                        'num_imf' => $AGC['tel']
                                    ),
                                );
                            }

                        } else {
                            $err_msg = $error[$err->errCode] . " " . $err->param;
                        }
                    } else {
                        $err_msg = $error[$err->errCode] . " " . $err->param;
                    }
                }

                break;
            default:
                $err_msg = "Status du dossier non trouvé dans la base\n";
        }
    }else{
        $err_msg = $error[$product_dataset->errCode]." ".$product_dataset->param;
    }
    if($err_msg != ""){
        $return_data = array(
            'success' => false,
            'datas' => array(
                'msg' => $err_msg,
                'code_imf' => $data_entry_ussd['code_imf'],
                'statut' => $status_msg,
                'telephone' => $data_entry_ussd['telephone'],
                'id_trans_ext' => $data_entry_ussd['id_transaction'],
                'langue_id' => $client_abonnement['langue'],
                'prenom' => $client_dataset['pp_prenom'],
                'num_imf' => $AGC['tel']
            ),
        );
    }

    echo json_encode($return_data, 1 | 4 | 2 | 8);
    exit;
?>
