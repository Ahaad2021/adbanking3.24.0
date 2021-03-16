<?php

/**
 * Description de la classe Credit
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';

class Remboursement extends BaseModel
{

    /** Properties */
    private $_id_dossier;

    public function __construct(&$dbc, $id_agence = NULL)
    {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function rembourseMontantDepot($dbc, $montant_remboursement, $remote_id_agence, $remote_id_client, $id_guichet)
    {
        $client_credit = new Credit($dbc, $remote_id_agence);
        $dossier_ml_demande = $this->getDemandeCredit($remote_id_client, " statut_demande IN (2, 5) ORDER BY id_doss ASC limit 1");

        if ((sizeof($dossier_ml_demande) > 0) && ($montant_remboursement > 0)) {
            $id_doss = $dossier_ml_demande[0]["id_doss"];
            $data_remboursement["infos_doss"][$id_doss] = $client_credit->getDossierCrdtInfo($id_doss);
            $data_remboursement["infos_doss"][$id_doss]["infos_credit"] = $this->get_info_credit($id_doss);
            $data_remboursement["infos_doss"][$id_doss]["DATA_GAR"] = array();
            $data_remboursement["infos_doss"][$id_doss]["gar_num_mob"] = NULL;
            $data_remboursement["infos_doss"][$id_doss]["ech_paye"] = NULL;
            $data_remboursement["infos_doss"][$id_doss]["derniereech"] = NULL;
            $data_remboursement["infos_doss"][$id_doss]["mnt_remb"] = $montant_remboursement;

            $myErr = $this->rembourse_montantInt($data_remboursement["infos_doss"], 2, $id_guichet, NULL, null);
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Renvois des infos sur un dossier de crédit et sa dernière échéance
     * @author Unknow
     * @since 2.1
     * @param int $id_doss L'ID du dossier pour le quel on cherche des infos
     * @return <UL>
     *   <LI> NULL si le dossier n'est pas trouvé </LI>
     *   <LI> Si non un tableau contenant: </LI>
     *   <LI> en première ligne : les infos sur le dossier de crédit et sur la dernière échéance  </LI> </UL>
     *   <LI> les autres lignes contiennent les éventules remboursements de la dernière échéance </LI>
     *   </UL>
     */
    public function get_info_credit($id_doss, $id_ech_remb = NULL)
    {
        /* Récupération des infos sur le dossier de crédit */
        $sql = "SELECT id_doss, terme, cpt_gar_encours, cre_id_cpte, cre_etat FROM ad_dcr WHERE id_ag=:id_agence AND id_doss=:id_dossier";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss);
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
        } else if (count($result) < 1) {
            $this->getDbConn()->rollBack();
//            $dbHandler->closeConnection(true);
            return NULL;
        }

//        $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
        $row = $result;
        $retour['id_doss'] = $id_doss;
        $retour['terme'] = $row["terme"];
        $retour['id_cpt_credit'] = $row["cre_id_cpte"];
        $retour['id_cpt_epargne_nantie'] = $row['cpt_gar_encours']; /* Compte épargne des garanties encours */
        $retour['cre_etat'] = $row["cre_etat"];

        /* Recherche la dernière échéance non remboursée totalement */
        if ($id_ech_remb == NULL) {
            $sql = "SELECT * FROM ad_etr WHERE (id_ag=:id_agence) AND (id_doss=:id_dossier) AND (remb='f') AND (id_ech=(SELECT MIN(id_ech) FROM ad_etr WHERE (id_ag=:id_agence) AND (id_doss=:id_dossier) AND (remb='f')))";
            $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss);
        } else {
            $sql = "SELECT * FROM ad_etr WHERE (id_ag=:id_agence) AND (id_doss=:id_dossier) AND (remb='f') AND (id_ech=:id_echeancier_remb)";
            $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss, ":id_echeancier_remb" => $id_ech_remb);
        }

//        $result=$db->query($sql);
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        } else if (count($result) < 1) {
//            $dbHandler->closeConnection(true);
            $this->getDbConn()->rollBack();
            return $retour;
        }

        $row = $result;
        $retour['id_ech'] = $row['id_ech'];
        $retour['date'] = $row['date_ech'];
        $retour['mnt_cap'] = $row['mnt_cap'];
        $retour['mnt_int'] = $row['mnt_int'];
        $retour['mnt_gar'] = $row['mnt_gar'];

        $retour['solde_cap'] = $row['solde_cap'];
        $retour['solde_int'] = $row['solde_int'];
        $retour['solde_gar'] = $row['solde_gar'];
        $retour['solde_pen'] = $row['solde_pen'];

        /* Récupération d'éventuels remboursements sur la dernier échéance */
        $sql = "SELECT * FROM ad_sre WHERE (id_ag=:id_agence) AND (id_doss=:id_dossier) AND (id_ech=:id_echeancier) AND (num_remb>0) AND annul_remb IS NULL AND id_his IS NULL ORDER BY date_remb";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss, ":id_echeancier" => $retour['id_ech']);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param);

        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $retour['nbre_remb'] = count($result);

        if ($retour['nbre_remb'] > 0) {
            $i = 1;
            foreach ($result as $row) {
                $retour[$i]['date'] = $row['date_remb'];
                $retour[$i]['mnt_remb_cap'] = $row['mnt_remb_cap'];
                $retour[$i]['mnt_remb_int'] = $row['mnt_remb_int'];
                $retour[$i]['mnt_remb_gar'] = $row['mnt_remb_gar'];
                $retour[$i]['mnt_remb_pen'] = $row['mnt_remb_pen'];
                ++$i;
            }
        }

//        $dbHandler->closeConnection(true);
        return $retour;
    }

    public function getDemandeCredit($id_client, $condition = null)
    {
        $sql = "SELECT * FROM ml_demande_credit WHERE id_client = :id_client";
        $param_arr = array(':id_client' => $id_client);

        if (!empty($condition))
            $sql .= " AND " . $condition;

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        $dataset = array();

        if ($results === FALSE || count($results) < 0) {
            return null;
        }

        foreach ($results as $row) {
            $dataset[] = $row;
        }

        return $dataset;
    }


    public function rembourse_montantInt($info_doss, $source, $id_guichet = NULL, $date_remb = NULL, $id_cpte_gar = NULL)
    {
        global $global_nom_login;

        $epargne_obj = new Epargne($this->getDbConn(), $this->getIdAgence());
        $historique_obj = new Historique($this->getDbConn(), $this->getIdAgence());

        foreach ($info_doss as $id_doss => $val_doss) {
            $id_cpte_gar = $val_doss['gar_num_mob'];
            $comptable_his = array();
            if (isset($val_doss["derniereech"]) && $val_doss["derniereech"] != "") {
                if ($date_remb == NULL) {
                    $myErr = $this->rembourse_montant($id_doss, $val_doss['mnt_remb'], $source, $comptable_his, $id_guichet, NULL, NULL, $val_doss["derniereech"], NULL, $id_cpte_gar);
                } else {
                    $myErr = $this->rembourse_montant($id_doss, $val_doss['mnt_remb'], $source, $comptable_his, $id_guichet, NULL, NULL, $val_doss["derniereech"], $date_remb, $id_cpte_gar);
                }
            } else {
                if ($date_remb == NULL) {
                    $myErr = $this->rembourse_montant($id_doss, $val_doss['mnt_remb'], $source, $comptable_his, $id_guichet, NULL, NULL, NULL, NULL, $id_cpte_gar);
                } else {
                    $myErr = $this->rembourse_montant($id_doss, $val_doss['mnt_remb'], $source, $comptable_his, $id_guichet, NULL, NULL, NULL, $date_remb, $id_cpte_gar);
                }
            }
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $myErr;
            }

            $INFOSREMB = $myErr->param; // Récupère les valeurs de retour de rembourseRemote
            $INFOSREMBIAR = $myErr->param['INFOREMBIAR'];

            if ($source == 2) { // Remboursement via le compte lié

                // Perception éventuelle de frais de découvert
                $myErr = $epargne_obj->preleveFraisDecouvert($INFOSREMB["cpt_liaison"], $comptable_his);
                if ($myErr->errCode != NO_ERR) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return $myErr;
                }

            }

            $myErr = $historique_obj->ajoutHistorique(147, $val_doss['id_client'], $id_doss . '|' . $myErr->param['id_ech'] . '|' . $myErr->param['num_remb'], $global_nom_login, date("r"), $comptable_his, NULL);
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $myErr;
            }

            $id_his = $myErr->param;

            if (sizeof($INFOSREMBIAR) > 0) {
                $arrayIdEcriture = array();

                // Recuperation de l'id ecriture de chaque échéance déjà remboursée en ordre du traitement REL-81
                $idDossIAR = $id_doss;
                $sqlGetIdEcritures = "SELECT e.id_ecriture FROM ad_mouvement m, ad_ecriture e WHERE m.id_ecriture = e.id_ecriture AND e.id_his =:id_his AND e.type_operation = 375 AND e.info_ecriture =:id_dossier_IAR AND m.compte IN (SELECT cpte_cpta_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc()) AND m.cpte_interne_cli IS NULL AND m.sens = 'c' ORDER BY e.id_ecriture";
                $param_arr = array(':id_his' => $id_his, "id_dossier_IAR" => $idDossIAR);

                $result_GetIdEcritures = $this->getDbConn()->prepareFetchAll($sqlGetIdEcritures, $param_arr);
                if ($result_GetIdEcritures === false) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }
                $countEch = 0;
                foreach ($result_GetIdEcritures as $row) {
                    $arrayIdEcriture[$INFOSREMBIAR[$countEch]["id_ech"]] = $row['id_ecriture'];
                    $countEch++;
                }

                // Reprise IAR pour chaque echeance déjà remboursée REL-81
                for ($count = 0; $count < sizeof($INFOSREMBIAR); $count++) {

                    if ($INFOSREMBIAR[$count]["int_cal"] > 0) {
                        $interet_calculer = $INFOSREMBIAR[$count]["int_cal"];
                    } else if ($INFOSREMBIAR[$count]["int_cal"] == 0) {
                        $interet_calculer = $INFOSREMBIAR[$count]["int_cal_traite"];
                    }
                    if ($INFOSREMBIAR[$count]['int_cal'] != 0 && $INFOSREMBIAR[$count]['int_cal_traite'] != 0 && $interet_calculer > 0) {

                        $sql_insert_his_repris = "INSERT INTO ad_calc_int_recevoir_his(id_doss, date_traitement, nb_jours, periodicite_jours,id_ech, solde_int_ech, montant, etat_int, solde_cap, cre_etat, devise, id_his_reprise, id_ecriture_reprise, id_ag)
            VALUES ('" . $INFOSREMBIAR[$count]['id_doss'] . "', date(now()), 0,0,'" . $INFOSREMBIAR[$count]['id_ech'] . "',0,$interet_calculer ,2,0,1,'" . $INFOSREMBIAR[$count]['devise'] . "',$id_his," . $arrayIdEcriture[$INFOSREMBIAR[$count]['id_ech']] . ", numagc())";

                        $result_insert_his_repris = $this->getDbConn()->execute($sql_insert_his_repris);
                        if ($result_insert_his_repris === false) {
//                            $dbHandler->closeConnection(false);
                            $this->getDbConn()->rollBack();
                            signalErreur(__FILE__, __LINE__, __FUNCTION__);
                        }
                    }
                }
            }

        }// fin parcours des dossiers

//        $dbHandler->closeConnection(true);
        return $myErr;
    }

    public function rembourse_montant($id_doss, $mnt, $source, &$comptable_his, $id_guichet = NULL, $DATA_REMB = NULL, $ORDRE_REMB = NULL, $dernier_ech = NULL, $date_remb = NULL, $id_cpte_gar = NULL)
    {
        global $global_monnaie_courante_prec;
        $_SESSION['mode'] = 2;
        $_SESSION['int_cal_traite'] = 0;
        $_SESSION['int_cal'] = 0;
        $INFOSREMBAUTO = array(); //REL-81

        $credit_obj = new Credit($this->getDbConn(), $this->getIdAgence());
        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $compta_obj = new Compta($this->getDbConn(), $this->getIdAgence());

        $DCR = $credit_obj->getDossierCrdtInfo($id_doss);
        /* Récupération de toutes les échéances non remboursées ou partiellement remboursées du crédit */
        $mntEch = array();
        $whereCond = "WHERE (remb='f') AND (id_doss='$id_doss')";
        $echeance = $credit_obj->getEcheancier($whereCond);

        //Si on commence aux dernières échéances
        if ($dernier_ech == 1) {
            $j = 0;
            $recup_array = array();
            foreach ($echeance as $cle => $value) {
                $recup_array[$j] = array_pop($echeance);
                $j++;
            }
            $echeance = array();
            $echeance = $recup_array;
        }
        $tab_id_ech = array();
        $indice = 0;
        if (is_array($echeance)) { /* Au moins une échéance est non remboursée */
            /* Construction d'un tableau contenant les montants attendus par échéance */
            while (list($key, $info) = each($echeance)) {
                /* Si aucune précision n'est donnée pour ce qu'il faut payer alors considérer qu'on veut tout rembourser */
                $tab_id_ech[$indice] = $info["id_ech"];
                $indice++;
                if ($DATA_REMB == NULL)
                    array_push($mntEch, round($info["solde_cap"] + $info["solde_int"] + $info["solde_pen"] + $info["solde_gar"], $global_monnaie_courante_prec));
                else {
                    $mnt_attendu = 0;
                    if ($DATA_REMB['cap'] == true) /* il faut rembourser le capital */
                        $mnt_attendu += $info["solde_cap"];

                    if ($DATA_REMB['int'] == true) /* il faut rembourser les intérêts */
                        $mnt_attendu += $info["solde_int"];

                    if ($DATA_REMB['pen'] == true) /* il faut rembourser les pénalités */
                        $mnt_attendu += $info["solde_pen"];

                    if ($DATA_REMB['gar'] == true) /* il faut rembourser les garanties */
                        $mnt_attendu += $info["solde_gar"];

                    array_push($mntEch, round($mnt_attendu, $global_monnaie_courante_prec));
                }
            }
        } else /* Il ne reste auncune échéance à rembourser */
            return new ErrorObj(ERR_CRE_NO_ECH);

        // Vérifier que le montant n'excède pas le total à rembourser
        $totalDu = 0;
        while (list(, $value) = each($mntEch))
            $totalDu += $value;

        if ($DCR["interet_remb_anticipe"] > 0) {
            $totalDu += $DCR["interet_remb_anticipe"];
        }
        if (($source == 1) && ($id_guichet != NULL)) { /* Arrondi du montant au billetage si source = guichet */
            $critere = array();
            $critere['num_cpte_comptable'] = $compte_obj->getCompteCptaGui($id_guichet);
            $cpte_gui = $compta_obj->getComptesComptables($critere);
            $mnt = arrondiMonnaie($mnt, 0, $cpte_gui['devise']);
        } else { /* Arrondi du montant à la précision de la maonnaie si source = compte */
            $mnt = round($mnt, $global_monnaie_courante_prec);
        }

        //  if ($mnt > round($totalDu, $global_monnaie_courante_prec)) {
        //    return new ErrorObj(ERR_CRE_MNT_TROP_ELEVE, sprintf(_("%s est supérieur à %s"),afficheMontant($mnt), afficheMontant($totalDu)));
        //  }
        $mnt = min($mnt, $totalDu);
        reset($mntEch);

        /* Remboursement successifs des échéances selon le montant disponible */
        // Initialisation des compteurs permettant de connaitre les montants remboursés pour chaque poste

        $param = array();
        $param["mnt_remb_pen"] = 0;
        $param["mnt_remb_gar"] = 0;
        $param["mnt_remb_int"] = 0;
        $param["mnt_remb_cap"] = 0;
        $param["int_cal"] = 0;
        $param["int_cal_traite"] = 0;

        $i = 0;

        $_SESSION['int_cal'] = $this->get_calcInt_cpteInt(true, false, $id_doss);
        while (round($mnt, $global_monnaie_courante_prec) > 0) {
            if ($i == 0) {
                if ($DCR["interet_remb_anticipe"] > 0) {
                    if ($mnt >= $mntEch[$i] + $DCR["interet_remb_anticipe"])
                        $mnt_remb = $mntEch[$i] + $DCR["interet_remb_anticipe"];
                    else
                        $mnt_remb = $mnt;
                } else { // MAE-23: si on n'a pas d'interet remboursement anticipé, alors on procede normalement avec le montant
                    if ($mnt >= $mntEch[$i])
                        $mnt_remb = $mntEch[$i];
                    else
                        $mnt_remb = $mnt;
                }
            } else {
                if ($mnt >= $mntEch[$i])
                    $mnt_remb = $mntEch[$i];
                else
                    $mnt_remb = $mnt;
            }

            /* Remboursement tout ou partie d'une échéance du crédit */
            if ($date_remb == NULL) {
                $myErr = $this->rembourseRemote($id_doss, $mnt_remb, $source, $comptable_his, $id_guichet, $DATA_REMB, $ORDRE_REMB, $tab_id_ech[$i], NULL, $id_cpte_gar);
                if ($myErr->errCode == NO_ERR) { //Recuperation Info IAR de chaque echeance remboursée REL-81
                    if ($myErr->param['int_cal'] != 0 && $myErr->param['int_cal_traite'] != 0) {
                        array_push($INFOSREMBAUTO, $myErr->param);
                    }
                }
            } else {
                $myErr = $this->rembourseRemote($id_doss, $mnt_remb, $source, $comptable_his, $id_guichet, $DATA_REMB, $ORDRE_REMB, $tab_id_ech[$i], $date_remb, $id_cpte_gar);
                if ($myErr->errCode == NO_ERR) { //Recuperation Info IAR de chaque echeance remboursée REL-81
                    if ($myErr->param['int_cal'] != 0 && $myErr->param['int_cal_traite'] != 0) {
                        array_push($INFOSREMBAUTO, $myErr->param);
                    }
                }
            }
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $myErr;
            }
            $ret = $myErr->param;

            // MAJ des montants remboursés
            $param["mnt_remb_pen"] += $ret["mnt_remb_pen"];
            $param["mnt_remb_gar"] += $ret["mnt_remb_gar"];
            $param["mnt_remb_int"] += $ret["mnt_remb_int"];
            $param["mnt_remb_cap"] += $ret["mnt_remb_cap"];

            $mnt -= $mnt_remb;
            $i++;
        }
        $param["cpt_liaison"] = $ret["cpt_liaison"];
        $param["cpt_en"] = $ret["cpt_en"];
        $param["RETSOLDECREDIT"] = $ret["RETSOLDECREDIT"];
        $param["devise"] = $ret["devise"];
        $param["int_cal"] += $ret["int_cal"];
        $param["int_cal_traite"] += $ret["int_cal_traite"];
        $param["id_doss"] = $ret["id_doss"];
        $param["id_ech"] = $ret["id_ech"];
        $id_doss = $ret["id_doss"];
        $param["INFOREMBIAR"] = $INFOSREMBAUTO; //Recuperation Info IAR de chaque echeance remboursée REL-81

//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $param);
    }

    public function get_calcInt_cpteInt($montant = false, $compte = false, $id_doss = null, $id_ech = null)
    {
        if ($montant == true && $compte == false) {
            $sql_recup_int_cal = "SELECT ((SELECT sum(montant) FROM ad_calc_int_recevoir_his WHERE id_doss = :id_dossier AND etat_int = 1";
            if ($id_ech != null) {
                $sql_recup_int_cal .= " and id_ech = $id_ech";
            }
            $sql_recup_int_cal .= ") - coalesce((select sum(montant) from ad_calc_int_recevoir_his where id_doss = :id_dossier_ and etat_int = 2";
            if ($id_ech != null) {
                $sql_recup_int_cal .= "and id_ech = :id_echeancier";
                $param = array(":id_dossier" => $id_doss, ":id_dossier_" => $id_doss, ":id_echeancier" => $id_ech);
            }
            $sql_recup_int_cal .= "),0)) as int_calc;";

            $param = array(":id_dossier" => $id_doss, ":id_dossier_" => $id_doss);
            $result_recup_int_cal = $this->getDbConn()->prepareFetchRow($sql_recup_int_cal, $param);

            if ($result_recup_int_cal === false) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Erreur dans la requete SQL")));

            }
            $row_recup_int_cal = $result_recup_int_cal;
            $resultat_int = $row_recup_int_cal['int_calc'];
        }
        if ($compte == true && $montant == false) {
            //recuperation du compte interet couru a recevoir
            $sql_cpte_int_recevoir = "SELECT cpte_cpta_int_recevoir FROM adsys_calc_int_recevoir";
            $result_cpte_int_recevoir = $this->getDbConn()->prepareFetchRow($sql_cpte_int_recevoir);
            if ($result_cpte_int_recevoir === false) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Erreur dans la requete SQL")));
            }
            $row_cpte_int_recevoir = $result_cpte_int_recevoir;
            $resultat_int = $row_cpte_int_recevoir;
        }

//        $dbHandler->closeConnection(true);
        return $resultat_int;
    }


    public function updateInteretAnticipe($id_doss, $Fields)
    {
        /* Met à jour mnt interet anticipe par $id_doss
           Les champs seront remplacés par ceux présents dans $Fields
        */
        $Where["id_doss"] = $id_doss;
        $Where["id_ag"] = $this->getIdAgence();
        $sql = buildUpdateQuery("ad_dcr", $Fields, $Where);
        $result = $this->getDbConn()->execute($sql);
        if ($result ===  false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
        }
//        $dbHandler->closeConnection(true);
        $tab = array();
        $tab[0] = new ErrorObj(NO_ERR);
    }

    public function getCompteCptaDcr($id_doss)
    {
        $sql = "SELECT cpte_cpta_prod_cr_int , cpte_cpta_prod_cr_gar, cpte_cpta_prod_cr_pen, cpte_cpta_prod_cr_frais";
        $sql .= " FROM  adsys_produit_credit b , ad_dcr a  ";
        $sql .= "WHERE b.id_ag = :id_agence AND b.id_ag = a.id_ag AND b.id = a.id_prod and a.id_doss = :id_dossier";
        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_dossier' => $id_doss);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if (count($result) == 0) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
        }

        $cpte_cpta = $result;
//        $dbHandler->closeConnection(true);

        return $cpte_cpta;
    }

    /**
     * Remboursement partiel ou total de la première échéance non remboursée.
     *
     * Fonction appelée par les fonctions rembourse_montantInt et rembourseInt, et aussi par la fonction prelev_auto du batch.
     * @param int $id_doss Identifiant du DCR à rembourser.
     * @param int $mnt Montant à rembourser.
     * @param int $source Source du remboursement : 1 pour guichet, 2 pour compte lié
     * @param array $comptable Tableau contenant les mouvements compatable précédent cette opération de remboursement (on compile).
     * @param int $id_guichet Identifiant du guichet à partir duquel se fait le remboursement.
     * @param array $DATA_REMB Tableau disant ce qu'il faut rembourser : capital, garantie, intérêts ou pénalités.
     * @param array $ORDRE_REMB Tableau donnant l'ordre du remboursement.
     * @param int $ech_paye
     * @return ErrorObj contenant en paramètre le tableau suivant :
     * <ul>
     *   <li>    NO_ERR => Pas d'erreur (
     *      <ul>
     *        <li>  param = array('result' => 1 si crédit non soldé et 2 si crédit soldé</li>
     *        <li>               ('id_ech' => ID de l'échéance remboursée</li>
     *        <li>               ['num_remb'] => Rang du remboursement pour cette échéance)</li>
     *      </ul>
     *   </li>
     *   <li>    ERR_SOLDE_INSUFFISANT => Solde insyffisant pour le remboursement de $mnt</li>
     *   <li>    ERR_CRE_MNT_TROP_ELEVE => Montant trop élevé par rapport à l'échéance</li>
     *   <li>    Tout autre code d'erreur renvoyé par une fonction imbriquée.</li>
     * </ul>
     */
    public function rembourseRemote($id_doss, $mnt, $source, &$comptable, $id_guichet = NULL, $DATA_REMB = NULL, $ORDRE_REMB = NULL, $ech_paye = NULL, $date_remb = NULL, $id_cpte_gar = NULL, $DCR = NULL, $Produitx = NULL, $DEV = NULL, $array_credit = NULL, $cpta_debit = NULL, $cpta_credit_gar = NULL, $CPTS_ETAT = NULL, $id_etat_perte = NULL)
    {
        //  FIXME  IL FAUT REECRIRE CETTE FONCTION DE MANIERE ALLEGEE !
//    global $global_id_agence;
        global $global_nom_login;
//    global $dbHandler;
        global $appli;
        global $global_credit_niveau_retard;
        global $error;
        global $global_monnaie_courante_prec;
        global $global_monnaie;
        global $global_id_client;
        $int_cal = 0;

//    $db = $dbHandler->openConnection();
        $credit_obj = new Credit($this->getDbConn(), $this->getIdAgence());
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());
        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $agence_obj = new Agence($this->getDbConn(), $this->getIdAgence());
        $client_obj = new Client($this->getDbConn(), $this->getIdAgence());

        /* Récupération des infos sur le dossier de crédit */
        if ($DCR == NULL) {
            $DCR = $credit_obj->getDossierCrdtInfo($id_doss);
        }
        $id_client = $DCR["id_client"];

        /* Récupération des infos sur le produit de crédit associé */
        if ($Produitx == NULL) {
            $Produitx = $credit_obj->getProdInfo(" where id =" . $DCR["id_prod"], $id_doss);
        }
        $PROD = $Produitx[0];
        $devise = $PROD["devise"];
        $ORDRE_REMB = $PROD["ordre_remb"];

        /* Récupération des infos sur la devise du produit */
        if ($DEV == NULL) {
            $DEV = $devise_obj->getInfoDevise($devise);
        }

        /* On autorise pas le remboursement d'un crédit en perte */
        if ($DCR["etat"] == 9) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Le dossier est en perte !"));
        }

        /* Recupération des infos sur le crédit : dernière échéance non remboursée ou partiellement et les remboursements */
        if ($ech_paye == NULL)
            $info = $this->get_info_credit($id_doss);
        else
            $info = $this->get_info_credit($id_doss, $ech_paye);

        /* Récupération du compte de liaison */
        $cpt_liaison = $DCR["cpt_liaison"];

        /* Récupération du total attendu pour la dernière échéance non remboursée ou partiellement remboursée */
        $mnt = round($mnt, $global_monnaie_courante_prec);

        $total_credit = round($info['solde_cap'] + $info['solde_int'] + $info['solde_pen'] + $info['solde_gar'], $global_monnaie_courante_prec);

        if ($array_credit == NULL) {
            $array_credit = $this->getCompteCptaDcr($id_doss);
        }

        // MAE-23 : remboursement des interet anticipe
        if ($DCR['interet_remb_anticipe'] > 0) {
            if ($mnt >= $DCR['interet_remb_anticipe']) {
                $mnt = $mnt - $DCR['interet_remb_anticipe'];
                $mnt_int_anti = $DCR['interet_remb_anticipe'];
            } elseif (($mnt > 0) && ($mnt < $DCR['interet_remb_anticipe'])) {
                $mnt_int_anti = $mnt;
                $mnt = 0;
            }

            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            if ($source == 1) { // Source = guichet
                //débit client / crédit garantie
                $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaGui($id_guichet);
            } else if ($source == 2 || $source == 3) { // Source = compte lié  et  // Source = compte de garantie
                if ($cpta_debit != NULL) {
                    $cptes_substitue["cpta"]["debit"] = $cpta_debit;
                } else {
                    $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($cpt_liaison);
                }

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne du compte de liaison"));
                }
                $cptes_substitue["int"]["debit"] = $cpt_liaison;
            }

            // Recherche du type d'opération
            $type_oper_anti = get_credit_type_oper(14);

            if ($array_credit["cpte_cpta_prod_cr_int"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux intérêts"));
            }

            $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_int"];

            //  Passage des écritures comptables
            // débit client / crédit produit
            if ($devise != $global_monnaie) {
                $err = $devise_obj->effectueChangePrivate($devise, $global_monnaie, $mnt_int_anti, $type_oper_anti, $cptes_substitue, $comptable, true, NULL, $id_doss);
            } else {
                // Passage des écritures comptables
                if ($date_remb == NULL) {
                    $err = $compte_obj->passageEcrituresComptablesAuto($type_oper_anti, $mnt_int_anti, $comptable, $cptes_substitue, $devise, NULL, $id_doss);
                } else {
                    $err = $compte_obj->passageEcrituresComptablesAuto($type_oper_anti, $mnt_int_anti, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss);
                }
            }

            if ($err->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $err;
            }
            $mnt_restant_Intanti = $DCR['interet_remb_anticipe'] - $mnt_int_anti;
            $data_remb_int_Anti = array(
                "interet_remb_anticipe" => $mnt_restant_Intanti
            );
            $update_int_anti = $this->updateInteretAnticipe($id_doss, $data_remb_int_Anti);
            $Error = $update_int_anti[0];
            if ($Error->errCode != NO_ERR) {
                //On a un problème, l'état de l'échéancier est non garanti... :(
                $html_err = new HTML_erreur(_("Echec du traitement.") . " ");
                $html_err->setMessage(_("L'opération de repaiement des intérêts a échoué .") . " " . $error[$Error->errCode] . $Error->param);
                $html_err->addButton("BUTTON_OK", 'Gen-11');
                $html_err->buildHTML();
                echo $html_err->HTML_code;
                exit();
            }

        }

//  if ($mnt > $total_credit) {
//    $dbHandler->closeConnection(false);
//    return new ErrorObj(ERR_CRE_MNT_TROP_ELEVE);
//  }

        /* Ordre de remboursement : si aucun ordre n'est spécifié ou $ORDRE_REMB = 1, alors on considère qu'il faut rembourser respectivement :
               - les garanties
               - les pénalités
               - les intérêst
               - le  capital,
               qui est l'ordre par défaut
        */
        if ($ORDRE_REMB == 2)
            $ORDRE_REMB = array("gar", "cap", "int", "pen");
        elseif ($ORDRE_REMB == 3)
            $ORDRE_REMB = array("gar", "int", "cap", "pen");
        elseif ($ORDRE_REMB == 4)
            $ORDRE_REMB = array("gar", "int", "pen", "cap");
        else
            $ORDRE_REMB = array("gar", "pen", "int", "cap");

        /* Si DATA_REMB est null, on considère qu'on veut tout rembourser: les garanties, les pénalités, les intérêts et le capital */
        if ($DATA_REMB == NULL)
            $DATA_REMB = array("gar" => true, "pen" => true, "int" => true, "cap" => true);


        /* amnt est le montant remboursé disponible restant */
        $amnt = min($mnt, $total_credit);

        /* Rembourser selon l'ordre et les remboursement précisés */
        $solde_cap = $solde_int = $solde_gar = $solde_pen = 0;
        $mnt_remb_cap = $mnt_remb_int = $mnt_remb_gar = $mnt_remb_pen = 0;
        foreach ($ORDRE_REMB as $key => $value) {
            if ($DATA_REMB[$value] == true) { /* il faut le rembourser si le montant disponible le permet */
                ${"mnt_remb_" . $value} = min($info["solde_" . $value], $amnt);

                $amnt -= ${"mnt_remb_" . $value};


                ${"solde_" . $value} = $info["solde_" . $value] - ${"mnt_remb_" . $value};

            } else { /* il n'est pas à rembourser */
                ${"mnt_remb_" . $value} = 0;
                ${"solde_" . $value} = $info["solde_" . $value];
            }
        }

        if ($ech_paye != NULL)
            $id_echeance = $ech_paye;
        else
            $id_echeance = $info['id_ech'];
        $num_rembours = $this->getNextNumRemboursement($info['id_doss'], $id_echeance);
        // Insertion du remboursement dans la DB
        if ($date_remb == NULL) {
            $sql = "INSERT INTO ad_sre(id_doss,id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, ";
            $sql .= "mnt_remb_gar) VALUES(" . $info['id_doss'] . ",".$this->getIdAgence()."," . $num_rembours . ",'" . date("d/m/Y") . "'," . $id_echeance . ",$mnt_remb_cap,$mnt_remb_int,$mnt_remb_pen,$mnt_remb_gar)";
        } else {
            $sql = "INSERT INTO ad_sre(id_doss,id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, ";
            $sql .= "mnt_remb_gar) VALUES(" . $info['id_doss'] . ",".$this->getIdAgence()."," . $num_rembours . ",'" . $date_remb . "'," . $id_echeance . ",$mnt_remb_cap,$mnt_remb_int,$mnt_remb_pen,$mnt_remb_gar)";
        }

        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
        }

        /* On considère que le crédit est soldé si les soldes restant dûs du capital,des intérêsts,des penalités et des garanties sont=0 */
        $tmpcap = round($solde_cap, $global_monnaie_courante_prec);
        $tmpint = round($solde_int, $global_monnaie_courante_prec);
        $tmpgar = round($solde_gar, $global_monnaie_courante_prec);
        $tmppen = round($solde_pen, $global_monnaie_courante_prec);

        if ($tmpcap == 0 and $tmpint == 0 and $tmpgar == 0 and $tmppen == 0) {
            $fini = "t";
            $solde_cap = 0;
            $solde_int = 0;
            $solde_gar = 0;
            $solde_pen = 0;
        } else
            $fini = "f";

        //Met à jour le solde restant dû pour l'échéance
        $sql = "UPDATE ad_etr SET remb='$fini', solde_cap=$solde_cap, solde_int=$solde_int, solde_pen=$solde_pen, ";
        $sql .= "solde_gar=$solde_gar WHERE (id_ag=".$this->getIdAgence().") AND (id_doss=" . $info['id_doss'] . ") AND (id_ech=" . $id_echeance . ")";


        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
        }

        //Réalise les débits/crédits
        $id_cpt_credit = $info['id_cpt_credit'];
        $id_cpt_epargne_nantie = $info['id_cpt_epargne_nantie']; /* Compte d'épargne des garanties encours */


        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        if ($source == 1) { // Source = guichet
            //débit client / crédit garantie
            $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaGui($id_guichet);
        } else if ($source == 2 || $source == 3) { // Source = compte lié  et  // Source = compte de garantie
            if ($cpta_debit != NULL) {
                $cptes_substitue["cpta"]["debit"] = $cpta_debit;
            } else {
                $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($cpt_liaison);
            }

            if ($cptes_substitue["cpta"]["debit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne du compte de liaison"));
            }
            $cptes_substitue["int"]["debit"] = $cpt_liaison;
            if ($source == 3) { // Source = compte de garantie
                $gar_num_mob = $id_cpte_gar; //getGarantieNumMob($id_doss);
                if ($gar_num_mob == NULL) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Le compte de garantie numéraire mobilisée non trouvé dans la base de données pour le dossier!") . " : " . $id_doss);
                }
                $id_cpt_gar_mob = $id_cpte_gar;
                //$CPT_GAR = getAccountDatas ($id_cpt_gar_mob);
                //$cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpt_gar_mob);
                //if ($cptes_substitue["cpta"]["debit"] == NULL) {
                //      $dbHandler->closeConnection(false);
                //      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable de garantie associé au produit de crédit :").$Produitx["libel"]);
                //}
                //$cptes_substitue["int"]["debit"] = $id_cpt_gar_mob;
                //mise à jour du montant vente de la garantie
                $solde_gar_num = ($mnt_remb_cap + $mnt_remb_int + $mnt_remb_gar + $mnt_remb_pen);
                $sql = "UPDATE ad_gar SET montant_vente=montant_vente -($solde_gar_num)  WHERE (id_ag=".$this->getIdAgence().") AND gar_num_id_cpte_nantie=" . $id_cpt_gar_mob;
                $result = $this->getDbConn()->execute($sql);
                if ($result === false) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }
                /* Virement du solde du compte de garantie dans le compte de liaison */
                $type_opr_gar = 220;
                $myErr = $this->vireSoldeCloture($id_cpt_gar_mob, $solde_gar_num, 2, $cpt_liaison, $comptable, $type_opr_gar);
                if ($myErr->errCode != NO_ERR) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return $myErr;
                }
            }
        }

        /* S'il y a remboursement de garanties*/
        if ($mnt_remb_gar > 0) {
            // Recherche du type d'opération
            $type_oper = get_credit_type_oper(9, $source);
            // Passage des écritures comptables
            if ($cpta_credit_gar != NULL) {
                $cptes_substitue["cpta"]["credit"] = $cpta_credit_gar;
            } else {
                $cptes_substitue["cpta"]["credit"] = $compte_obj->getCompteCptaProdEp($id_cpt_epargne_nantie);
            }

            if ($cptes_substitue["cpta"]["credit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                //Ici, on renvoie l'erreur pertinente au produit de crédit et non au produit d'épargne
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("Garantie associée au produit de crédit : "));
            }

            $cptes_substitue["int"]["credit"] = $id_cpt_epargne_nantie;

            if ($date_remb == NULL) {
                $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_gar, $comptable, $cptes_substitue, $devise, NULL, $id_doss);
            } else {
                $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_gar, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss);
            }
            if ($err->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $err;
            }
            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);

            // Pour un remboursement par la garantie pas de MAJ dans ce sens
            if ($id_cpte_gar = NULL) {
                /* Mise à jour des garanties en cours dans la table des garanties du dossier */
                if ($DCR['cpt_gar_encours'] != '') {
                    $sql = "UPDATE ad_gar SET montant_vente=montant_vente + $mnt_remb_gar WHERE (id_ag=".$this->getIdAgence().") AND gar_num_id_cpte_nantie=" . $DCR['cpt_gar_encours'];
                    $result = $this->getDbConn()->execute($sql);
                    if ($result === false) {
                        $this->getDbConn()->rollBack();
                        signalErreur(__FILE__, __LINE__, __FUNCTION__);
                    }
                    $infos_gar = $this->getInfosCpteGarEncours($DCR['cpt_gar_encours']);
                    //S'il reste encore des garanties à mobiliser remettre l'état_gar à 1(encours de mobilisation)
                    if ($infos_gar['montant_vente'] > 0) {
                        $sql = "UPDATE ad_gar SET etat_gar = 1 WHERE id_ag=".$this->getIdAgence()." AND gar_num_id_cpte_nantie=" . $DCR['cpt_gar_encours'];
                        $result = $this->getDbConn()->execute($sql);
                        if ($result === false) {
//                            $dbHandler->closeConnection(false);
                            $this->getDbConn()->rollBack();
                            signalErreur(__FILE__, __LINE__, __FUNCTION__);
                        }
                    }
                }
            }
        }

        global $global_monnaie;

        /* S'il y a remboursement de pénalités */
        if ($mnt_remb_pen > 0) {
            // Recherche du type d'opération
            $type_oper = get_credit_type_oper(3, $source);

            if ($array_credit["cpte_cpta_prod_cr_pen"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux pénélités"));
            }

            $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_pen"];
            // Passage des écritures comptables
            // Si la devise du crédit n'est pas la devise de référence, mouvementer la position de change
            if ($devise != $global_monnaie) {
                $err = $devise_obj->effectueChangePrivate($devise, $global_monnaie, $mnt_remb_pen, $type_oper, $cptes_substitue, $comptable, true, NULL, $id_doss);
            } else {
                // Passage des écritures comptables
                if ($date_remb == NULL) {
                    $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_pen, $comptable, $cptes_substitue, $devise, NULL, $id_doss);
                } else {
                    $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_pen, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss);
                }
            }

            if ($err->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $err;
            }
            unset($cptes_substitue["cpta"]["credit"]);
        }

        /* S'il y a remboursement d'intérêts */

        if ($mnt_remb_int > 0) {
            $id_ech_calc = $this->if_exist_id_calc_int_recevoir($id_doss, $ech_paye);
            if ($id_ech_calc == true) {

                /*-------------------------------------------Modification pour calcul int a recevoir--------------------------------------------------------------------------*/

                if ($_SESSION['mode'] == 2) {
                    $int_cal = $_SESSION['int_cal'];
                }
                if ($_SESSION['mode'] != 2) {
                    $int_cal = $this->get_calcInt_cpteInt(true, false, $id_doss);
                }

                if ($int_cal > 0) {

                    $type_oper = get_credit_type_oper(2, 4); //operation Remboursement Interet A Recevoir

                    if ($mnt_remb_int <= $int_cal) {
                        $int_cal = $mnt_remb_int;
                        $_SESSION['int_cal'] -= $int_cal;
                        $_SESSION['int_cal_traite'] = $int_cal;
                        $mnt_remb_int = 0;
                    } else {
                        $mnt_remb_int -= $int_cal;
                        $_SESSION['int_cal'] -= $int_cal;
                        $_SESSION['int_cal_traite'] = $int_cal;
                    }
                    $cpte_int_couru = $this->get_calcInt_cpteInt(false, true, null);
                    $cptes_substitue["cpta"]["credit"] = $cpte_int_couru;
                    if ($date_remb == NULL) {
                        $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $int_cal, $comptable, $cptes_substitue, $devise, NULL, $id_doss . "-" . $ech_paye);
                    } else {
                        $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $int_cal, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss . "-" . $ech_paye);
                    }

                    if ($err->errCode != NO_ERR) {
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        return $err;
                    }

                }
            }

            /*------------------------------------------- Fin Modification pour calcul int a recevoir--------------------------------------------------------------------------*/
            // Recherche du type d'opération
            $type_oper = get_credit_type_oper(2, $source);

            if ($mnt_remb_int > 0) {
                if ($array_credit["cpte_cpta_prod_cr_int"] == NULL) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte du produit de crédit associé aux intérêts"));
                }

                $cptes_substitue["cpta"]["credit"] = $array_credit["cpte_cpta_prod_cr_int"];

                //  Passage des écritures comptables
                // débit client / crédit produit

                if ($devise != $global_monnaie) {
                    $err = $devise_obj->effectueChangePrivate($devise, $global_monnaie, $mnt_remb_int, $type_oper, $cptes_substitue, $comptable, true, NULL, $id_doss);
                } else {
                    // Passage des écritures comptables
                    if ($mnt_remb_int > $int_cal && $id_ech_calc == true) {
                        if ($date_remb == NULL) {
                            $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise, NULL, $id_doss . "-" . $ech_paye);
                        } else {
                            $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss . "-" . $ech_paye);
                        }
                    } else {
                        if ($date_remb == NULL) {
                            $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise, NULL, $id_doss . "-" . $ech_paye);
                        } else {
                            $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_int, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss . "-" . $ech_paye);
                        }
                    }
                }
            }
            if ($err->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $err;
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
            if ($CPTS_ETAT == NULL) {
                $CPTS_ETAT = $this->recup_compte_etat_credit($DCR["id_prod"]);
            }
            // #783 : Solution de recuperer le compte comptable etat de credit actuel du dossier
            $newInfoDoss = $credit_obj->getDossierCrdtInfo($id_doss);
            $creEtatDoss = $DCR["cre_etat"];
            if ($newInfoDoss != null && $DCR["cre_etat"] != $newInfoDoss["cre_etat"]) {
                $creEtatDoss = $newInfoDoss["cre_etat"];
            }
            $cptes_substitue["cpta"]["credit"] = $CPTS_ETAT[$creEtatDoss];
            $cptes_substitue["int"]["credit"] = $id_cpt_credit;

            if ($cptes_substitue["cpta"]["credit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit de crédit"));
            }
            if ($date_remb == NULL) {
                $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_cap, $comptable, $cptes_substitue, $devise, NULL, $id_doss);
            } else {
                $err = $compte_obj->passageEcrituresComptablesAuto($type_oper, $mnt_remb_cap, $comptable, $cptes_substitue, $devise, $date_remb, $id_doss);
            }
            if ($err->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $err;
            }
        }

        // Valeurs qui seront renvoyées à la fonction appelante
        $RET = array();
        $RET['result'] = 1;
        $RET['id_ech'] = $id_echeance;
        $RET['num_remb'] = $info['nbre_remb'] + 1;
        $RET["mnt_remb_pen"] = $mnt_remb_pen;
        $RET["mnt_remb_gar"] = $mnt_remb_gar;
        $RET["mnt_remb_int"] = $mnt_remb_int;
        $RET["mnt_remb_cap"] = $mnt_remb_cap;
        $RET["cpt_liaison"] = $cpt_liaison;
        $RET["cpt_en"] = $id_cpt_epargne_nantie;
        $RET["int_cal_traite"] = $_SESSION['int_cal_traite'];
        $RET["int_cal"] += $int_cal;
        $RET["devise"] = $devise;
        $RET["id_doss"] = $id_doss;
        $RET["id_prod"] = $DCR["id_prod"];


        // S'il y a lieu, reclasser le crédit (passage souffrance -> sain)

        // Recherche de l'ancien état du dossier de crédit
        $oldEtat = $info["cre_etat"];

        // Recherche du nouvel état
        // Pour ce faire, on va calculer le nombre de jours de retard
        $sql = "SELECT date_ech FROM ad_etr WHERE id_ag = :id_agence AND id_doss = :id_dossier AND (remb='f') ORDER BY date_ech";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param);

        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
        }
        $numrows = count($result);
        $newEtat = 0;
        $etat = $oldEtat;

        if ($numrows == 0) { /* Si toutes les échéances sont remboursées */
            // Le crédit passe à l'état soldé
            if ($this->echeancierRembourse($id_doss)) {
                $myErr = $this->soldeCredit($id_doss, $comptable); //Mettre l'état du crédit à soldé
                if ($myErr->errCode != NO_ERR) {
//                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                // mise a jour Ml_demande_credit pour les produits credits de mobile lending accepter
                $data_doss = $credit_obj->getDossierCrdtInfo($id_doss);
                $info_prod = $credit_obj->getProdInfo(" WHERE id = " . $data_doss['id_prod']);
                $flag_mob_lending = $info_prod[0]['is_mobile_lending_credit'];
                $ml_demande_credit = current($this->getDemandeCredit($data_doss['id_client'], " id_doss = $id_doss"));

                if ($flag_mob_lending == 't') {
                    global $dbHandler;
                    $db = $dbHandler->openConnection();
                    $array_update = array("statut_demande" => 3);
                    $array_condi = array("id_client" => $data_doss['id_client'], "id_transaction" => $ml_demande_credit["id_transaction"]);
                    $sql = buildUpdateQuery("ml_demande_credit", $array_update, $array_condi);
                    $result = $this->getDbConn()->execute($sql);
                    if ($result === false) {
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        return new ErrorObj($result->errCode);
                    }
//                    $dbHandler->closeConnection(true);
                }


                $RETSOLDECREDIT = $myErr->param;
                // remise du montant sur la quotite
                $data_agc = $agence_obj->getAgenceDatas($this->getIdAgence());
                if ($data_agc['quotite'] == 't') {
                    $data_cli = $client_obj->getClientDatas($id_client);
                    if ($data_cli['mnt_quotite'] >= 0) {
                        $mnt_prem_ech = $this->getMntTotPremierEch($id_client, $id_doss);

                        $quotite_dispo_apres = $data_cli["mnt_quotite"] + $mnt_prem_ech;
                        $DATA_QUOTITE = array();
                        $DATA_QUOTITE["id_client"] = $id_client;
                        $DATA_QUOTITE["quotite_avant"] = $data_cli["mnt_quotite"];
                        $DATA_QUOTITE["quotite_apres"] = $quotite_dispo_apres;
                        $DATA_QUOTITE["mnt_quotite"] = $quotite_dispo_apres;
                        $DATA_QUOTITE["date_modif"] = date('r');
                        $DATA_QUOTITE["raison_modif"] = 'Remboursement de crédit (solde)';
                        $ajout_quotite = $this->ajouterQuotite($DATA_QUOTITE);


                        $DATA_QUOTITE_UPDATE = array();
                        $DATA_QUOTITE_WHERE = array();
                        $DATA_QUOTITE_UPDATE["mnt_quotite"] = $quotite_dispo_apres;
                        $DATA_QUOTITE_WHERE['id_client'] = $id_client;
                        $update_client = $this->update_quotite_client($DATA_QUOTITE_UPDATE, $DATA_QUOTITE_WHERE);
                    }
                }
            }
        } //end if crédit soldé
        else { //échéances à traiter
            $echeance = $result;
            $date = pg2phpDatebis($echeance["date_ech"]);
            // date premier echeance non rembourser - now
            $nbre_secondes = gmmktime(0, 0, 0, $date[0], $date[1], $date[2]) - gmmktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $etatAvance = $this->calculeEtatPlusAvance($id_doss);

            if ($nbre_secondes >= 0) { // Le crédit est à nouveau sain
                $newEtat = 1;
                if ($date_remb == NULL) {
                    $sql = "UPDATE ad_dcr SET cre_etat = $newEtat,cre_date_etat = '" . date("d/m/Y") . "' WHERE id_ag = ".$this->getIdAgence()." AND id_doss = $id_doss";
                } else {
                    $sql = "UPDATE ad_dcr SET cre_etat = $newEtat,cre_date_etat = '" . $date_remb . "', cre_retard_etat_max = $etatAvance WHERE id_ag=".$this->getIdAgence()." AND id_doss = $id_doss";
                }
                $result = $this->getDbConn()->execute($sql);
                if ($result === false) {
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
                }
            } else {
                $nbre_jours = $nbre_secondes / (3600 * 24);
                $nbre_jours = $nbre_jours * (-1);
                $newEtat = $this->calculeEtatCredit($nbre_jours);

                // Cas particulier où cette fonction a été appelée par le batch
                // lors du passage en perte.
                // Dans ce cas, on reste en souffrance. C'est le batch qui se chargera du passage
                // en perte (via la fonction passagePerte)

                if ($id_etat_perte == NULL) {
                    $id_etat_perte = $this->getIDEtatPerte();
                }

                if ($newEtat == $id_etat_perte)
                    $newEtat -= 1; // FIXME A revoir, il peut y avoir des trous !

                // Mise à jour si nécessaire
                if ($oldEtat != $newEtat) {
                    if ($date_remb == NULL) {
                        $sql = "UPDATE ad_dcr SET cre_etat = $newEtat,cre_date_etat =  '" . date("d/m/Y") . "' WHERE id_ag = ".$this->getIdAgence()." AND id_doss = $id_doss";
                    } else {
                        $sql = "UPDATE ad_dcr SET cre_etat = $newEtat,cre_date_etat =  '" . $date_remb . "', cre_retard_etat_max = $etatAvance WHERE id_ag = ".$this->getIdAgence()." AND id_doss = $id_doss";
                    }
                    $result = $this->getDbConn()->execute($sql);

                    if ($result === false) {
                        $this->getDbConn()->rollBack();
                        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql . "\n");
                    }
                }
            }//end else new état crédit

            // Reclassement du crédit si nécessaire en comptabilité
            $myErr = $this->placeCapitalCredit($id_doss, $oldEtat, $newEtat, $comptable, $devise);
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                signalErreur(__FILE__, __LINE__, __FUNCTION__, $error[$myErr->errCode] . $myErr->param);
                return $myErr;
            }

            // Gestion de l'alerte
            if ($appli != "batch") {
                if (is_array($global_credit_niveau_retard)) {
                    $etat_plus_avance = array_keys($global_credit_niveau_retard);
                    if ($newEtat > $etat_plus_avance[0]) {
                        unset($global_credit_niveau_retard[$etat_plus_avance[0]]);
                        $global_credit_niveau_retard[$newEtat] = array();
                        array_push($global_credit_niveau_retard[$newEtat], $id_doss);
                    } elseif ($newEtat == $etat_plus_avance)
                        array_push($global_credit_niveau_retard[$etat_plus_avance], $id_doss);
                } else {
                    $global_credit_niveau_retard[$newEtat] = array();
                    array_push($global_credit_niveau_retard[$newEtat], $id_doss);
                }
            }
        }//end échéances à traiter

        // Ajoout dans le tableau $RET de $RETSOLDECREDIT si le crédit a été soldé
        if (is_array($RETSOLDECREDIT))
            $RET["RETSOLDECREDIT"] = $RETSOLDECREDIT;

        // #357 - équilibre inventaire - comptabilité
        $cre_id_cpte = $DCR['cre_id_cpte'];

        if ($appli != "batch" && !empty($cre_id_cpte)) {
            $myErr = $this->setNumCpteComptableForCompte($cre_id_cpte, $db);
        }
        // Fin : #357 - équilibre inventaire - comptabilité

    //        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $RET);
    }

   /**
     * Fonction de génération du numéro remboursement prochain
     * @author Saourou MBODJ
     * @param int $id_doss: identifiant du dossier
     * @param int $id_echeance: le numéro de l'échéance
     * @return le numéro de remboursement suivant
     **/

   public function getNextNumRemboursement($id_doss,$id_echeance) {

//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();
        $a_sql=" SELECT count(*) from ad_sre WHERE id_ag=:id_agence AND id_doss=:id_dossier AND id_ech=:id_echeancier";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss, ":id_echeancier" => $id_echeance);
        $result = $this->getDbConn()->prepareFetchRow($a_sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            return $result;
        }

        $my_num_remb=$result;
        $next_num_remb=$my_num_remb["count"]+1;
        return($next_num_remb);
    }

    /**
     * Vire le solde d'un compte une fois celui-ci cloturé
     * Appelé dans le cadre d'une cloture par transfert
     * Peut  amené à liquider un reste en devise de référence si les billets disponibles ne permettent pas une liquidation en cash
     * @param int $id_cpte ID du compte cloturé
     * @param defined (1,2) $dest Destination des fonds (cfr {@link #clotureCompteEpargne}
     * @param int $id_cpte_dest Id du comtep de destinationd es fonds
     * @param array $comptable
     */
     public function vireSoldeCloture($id_cpte, $solde_cloture, $dest, $id_cpte_dest, &$comptable, $type_oper=NULL) {

        global $dbHandler, $global_id_guichet, $global_monnaie;

        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());
        $ACC = $compte_obj->getAccountDatas($id_cpte);
        $classe_comptable = $ACC["classe_comptable"];
        $devise = $ACC["devise"];
        $dev_ref = $global_monnaie;

//        $db = $dbHandler->openConnection();

        if ($solde_cloture != 0) {

            if($type_oper == NULL ) {
                switch ($classe_comptable) {
                    case 1:
                    case 2:
                    case 3:
                    case 5:
                    case 6:
                        $type_oper = ($dest == 1? 61 : 62);
                        break;
                    case 4:
                        $type_oper = 81;
                        break;

                    default:
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Classe comptable incorrecte !"
                }
            }

            // Passage écritures comptables
            //débit compte client / crédit compte de base client
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            $cptes_substitue["int"]["debit"] = $id_cpte;

            if ($dest == 1) { // Destination guichet
                $cptes_substitue["cpta"]["credit"] = $compte_obj->getCompteCptaGui($global_id_guichet);

                // Traitement des arrondis
                $mnt_dec = arrondiMonnaie($solde_cloture, -1, $devise);
                if ($solde_cloture != $mnt_dec && $devise != $dev_ref) {
                    $diff = $solde_cloture - $mnt_dec;
                    $diff_dev_ref = $devise_obj->calculeCV($devise, $dev_ref, $diff);
                    if ($diff_dev_ref > 0) {
                        // Passer d'abord une écriture de change pour le reliquiat
                        $myErr = $devise_obj->effectueChangePrivate($devise, $dev_ref, $diff, 455, $cptes_substitue, $comptable);
                        if ($myErr->errCode != NO_ERR) {
//                            $dbHandler->closeConnection(false);
                            $this->getDbConn()->rollBack();
                            return $myErr;
                        }
                        $solde_cloture -= $diff;
                    }
                }

                $erreur = $compte_obj->passageEcrituresComptablesAuto ($type_oper, $solde_cloture, $comptable, $cptes_substitue, $devise);
                if ($erreur->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $erreur;
                }
            } else { // Destination $id_cpte_dest
                $cptes_substitue["cpta"]["credit"] = $compte_obj->getCompteCptaProdEp($id_cpte_dest);
                if ($cptes_substitue["cpta"]["credit"] == NULL) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $cptes_substitue["int"]["credit"] = $id_cpte_dest;

                /* Vérifier que les comptes source et destination ont la même devise */
                $CPT_DEST = $compte_obj->getAccountDatas($id_cpte_dest);
                if ($devise == $CPT_DEST['devise']) {
                    $erreur = $compte_obj->passageEcrituresComptablesAuto ($type_oper, $solde_cloture, $comptable, $cptes_substitue, $devise);
                    if ($erreur->errCode != NO_ERR) {
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        return $erreur;
                    }
                } else { /* les comptes sont de devises différentes */
                    $myErr = $devise_obj->effectueChangePrivate($devise, $CPT_DEST['devise'], $solde_cloture, $type_oper, $cptes_substitue, $comptable);
                    if ($myErr->errCode != NO_ERR) {
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        return $myErr;
                    }
                }
            }
        }


//        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);

    }
    public function getInfosCpteGarEncours($gar_num_id_cpte_nantie){
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $infos_gar = array();
        $sql = " SELECT * FROM ad_gar where gar_num_id_cpte_nantie = :gar_num_id_cpte_nantie ";
        $param = array(":gar_num_id_cpte_nantie" => $gar_num_id_cpte_nantie);
        $result = $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
//            $dbHandler->CloseConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL : ").$sql);
        }
        foreach ($result as $row)
            $infos_gar = $row;

//        $dbHandler->closeConnection(true);
        return $infos_gar;
    }

    public function if_exist_id_calc_int_recevoir($id_doss,$id_ech)
    {
//        global $dbHandler, $global_id_agence;
//
//        $db = $dbHandler->openConnection();

        $sql_id_ech="select id_ech from ad_calc_int_recevoir_his where etat_int = 1 and id_doss = :id_dossier and id_ech = :id_echeancier and id_ag= numagc()";
        $param = array(":id_dossier" => $id_doss, ":id_echeancier" => $id_ech);
        $result_id_ech = $this->getDbConn()->prepareFetchAll($sql_id_ech, $param);;
        if ($result_id_ech === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Erreur dans la requete SQL")));
        }
//        $dbHandler->closeConnection(true);

        if (count($result_id_ech) > 0) {
            return true;
        }
        else{
            return false;
        }

    }

    /**
     * Fonction pour la recupération des tous les comptes et de l'etat lié  à un produit de crédit
     * @param array $id_etat_credit  ID de l'état du crédit
     * @return array $retour contenant le numero de compte et id du produit de crédit
     */
    public function recup_compte_etat_credit($id_produit_credit) {
//        global $dbHandler, $global_id_agence;
//        $db = $dbHandler->openConnection();
        $retour=array();

        $sql ="SELECT *  FROM adsys_etat_credit_cptes WHERE id_ag= :id_agence and id_prod_cre = :id_produit_credit;";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_produit_credit" => $id_produit_credit);
        $result = $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        foreach ($result as $row)
            $retour[$row["id_etat_credit"]]=$row["num_cpte_comptable"];

//        $dbHandler->closeConnection(true);
        return $retour;
    }

    public function echeancierRembourse ($id_doss) {
        // Renvoie true si toutes les échéances sont soldées
        // IN : $id_doss (id du dossier de crédit)
        // OUT: true si soldé false sinon

//        global $dbHandler,$global_id_agence;
//
//        $db = $dbHandler->openConnection();

        $sql = "SELECT remb FROM ad_etr WHERE (id_ag= :id_agence) AND (id_doss= :id_dossier) AND (remb='f')";
        $param =  array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss);
        $result= $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
//        $dbHandler->closeConnection(true);
        if (count($result) == 0) return 1;
        else return 0;
    }

    public function soldeCredit ($id_doss, &$comptable_his) {
//        global $dbHandler,$global_id_agence;
//
//        $db = $dbHandler->openConnection();

        $credit_obj = new Credit($this->getDbConn(), $this->getIdAgence());
        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        // Vérifie que le crédit est bien soldé.
        if ($this->echeancierRembourse($id_doss) != 1){
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Tentative de solder un crédit alors qu'il reste des échéances non-remboursées: $id_doss")); // "Appel à soldeCredit alors qu'il reste des échéances non-remboursées"
        }
        $DOSS = $credit_obj->getDossierCrdtInfo($id_doss);

        /* Fermeture des comptes d'épargne nanties numéraires du dossier */
        $liste_gar = $this->getListeGaranties($id_doss);
        $INFOSCLOTGAR = array(); // Contient ls retours de la fonction clotureCompteEpargne
        foreach($liste_gar as $key=>$val ) {
            /* Restitution dans le compte de prélèvement ou compte de liaison du crédit */
            $cpt_rest = $DOSS['cpt_liaison'];
            /*Garantie doit être numéraire, non restituée et non réalisée */
            if ($val['type_gar'] == 1 and $val['etat_gar'] != 4  ) {
                $nantie = $val['gar_num_id_cpte_nantie'];  // compte de garantie
                $CPT_GAR = $compte_obj->getAccountDatas($nantie);

                /* Si le compte de prélevement n'est pas fermé, y verser la garantie */
                if ($val['gar_num_id_cpte_prelev'] != '') {
                    $CPT_PRELEV = $compte_obj->getAccountDatas($val['gar_num_id_cpte_prelev']); // compte de prélèvement
                    if ($CPT_PRELEV['etat_cpte'] != 2)
                        $cpt_rest = $val['gar_num_id_cpte_prelev'];
                }

                if ($CPT_GAR['etat_cpte'] != 2) {
                    if($CPT_GAR['solde'] < 0){// contrôle sur le solde négatif
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Tentative de restituer une garantie dont le solde est négatif, crédit n°: $id_doss"));
                    }
                    // FIXME Pourquoi ne pas passer NULL ou même ommettre l'argument à la place de ce '$dummy' ?
                    $dummy = array();
                    $myErr = $this->clotureCompteEpargne($CPT_GAR["id_cpte"],5, 2, $cpt_rest, $comptable_his, $dummy);
                    if ($myErr->errCode != NO_ERR) {
//                        $dbHandler->closeConnection(false);
                        $this->getDbConn()->rollBack();
                        return $myErr;
                    }
                    $INFOSCLOTGAR[$CPT_GAR["id_cpte"]] = $myErr->param;
                }
            }
        }

        // Fermeture du compte de crédit
        $sql = "SELECT cre_id_cpte FROM ad_dcr WHERE id_doss = :id_dossier and id_ag= :id_agence ";
        $param = array(":id_dossier" => $id_doss, ":id_agence" => $this->getIdAgence());
        $result= $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $tmprow = $result;
        $idCptCre = $tmprow["cre_id_cpte"];

        $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag= ".$this->getIdAgence()." AND id_cpte = $idCptCre;";
        $result= $this->getDbConn()->execute($sql);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        // Passage du dossier de crédit à l'état "soldé"
        $Fields=array ("etat" => 6,"date_etat" => date("d/m/Y"));
        $this->updateCredit ($id_doss, $Fields); //Mettre l'état du dossier de crédit à soldé

        /* Passage des états des garanties mobilisées ou en cours de mobilisation à l'état 'Restitué' */
        $sql = "UPDATE ad_gar SET etat_gar=4 WHERE id_ag=".$this->getIdAgence()." AND id_doss = $id_doss AND (etat_gar = 1 OR etat_gar = 3)";
        $result= $this->getDbConn()->execute($sql);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        // Passage de l'écritures de régul dans le cas d'un rééchelonnement
        $mnt_reech = $this->getMontantReechelonne($id_doss);
        if ($mnt_reech > 0) {     // Ce crédit a fait l'objet d'au moins 1 rééch/mor
            // Passage de l'écriture comptable de régularisation
            $myErr = $compte_obj->passageEcrituresComptablesAuto(400, $mnt_reech, $comptable_his);
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $myErr;
            }
        }

        // Retour à l'appelant des données pertinentes à cette fonction
        if (is_array($INFOSCLOTGAR))
            $RET = array("GAR" => $INFOSCLOTGAR);

//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $RET);
    }

    public function getListeGaranties($id_doss) {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $liste_gar = array();

        $sql = "SELECT * FROM ad_gar WHERE id_doss = :id_doss";
        $sql.=" and id_ag= :id_agence ";
        $sql.=" ORDER BY id_gar";
        $param = array(":id_doss" => $id_doss, ":id_agence" => $this->getIdAgence());
        $result = $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        foreach ($result as $row)
            $liste_gar[$row['id_gar']] = $row;

//        $dbHandler->closeConnection(true);

        return $liste_gar;

    }

    public function clotureCompteEpargne($id_cpte, $raison_cloture, $dest, $id_cpte_dest, &$comptable,$frais=array()) {

        global $dbHandler, $global_id_client, $global_id_agence, $global_nom_login, $global_monnaie;

        $credit_obj = new Credit($this->getDbConn(), $this->getIdAgence());
        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $epargne_obj = new Epargne($this->getDbConn(), $this->getIdAgence());

        $InfoCpte = $compte_obj->getAccountDatas($id_cpte);
        $InfoProduit = $epargne_obj->getProdEpargne($InfoCpte["id_prod"]);

        // Bloquer d'abord le compte pour qu'il n'y ait pas d'opérations financières dessus
        $this->blocageCompteInconditionnel($id_cpte);

        // A partir de ce moment, nous sommes à l'intérieur d'une transaction, les autres utilisateurs voient le compte comme bloqué
//        $db = $dbHandler->openConnection();

        $this->deblocageCompteInconditionnel($id_cpte);

        $erreur = $this->checkCloture($id_cpte);

        if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
        }

        if (isset($frais["fermeture"]) && check_access(299))
            $frais_fermeture_modif = $frais["fermeture"];

        if (isset($frais["tenue"]) && check_access(299))
            $frais_tenue_modif = $frais["tenue"];

        if (isset($frais["penalites"]) && check_access(299))
            $penalites_modif = $frais["penalites"];

        $id_cpte_base =  $compte_obj->getBaseAccountID ($InfoCpte["id_titulaire"]);

        $devise = $InfoCpte["devise"];
        $dev_ref = $global_monnaie;

        $RET = array(); // Tableau qui sera renvoyé à l'appelant

        // Si le compte était en attente de fermeture, on procède directement au virement du solde
        if ($InfoCpte["etat_cpte"] == 5) {
            $solde_cloture = $InfoCpte["solde"];
        } else {
            // calcul des intérêts en fonction du paramétrage du produit
            // si 30j ou Fin de mois, et en cas de rupture anticipée : aucun, prorata, tout

            // Dans le cadre d'une cloture, les intérets sont toujours versés sur le compte lui-meme
            $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];

            $erreur = $this->arreteCompteEpargne($InfoCpte, $InfoProduit, $comptable);

            if ($erreur->errCode != NO_ERR) {
                $this->getDbConn()->rollBack();
                return $erreur;
            }

            $solde_cloture = $erreur->param["solde_cloture"];
            $RET["mnt_int"] = $erreur->param["int"];

            // Prélèvement des pénalités
            if ($InfoProduit["terme"] > 0) {
                $erreur = $this->prelevePenalitesEpargne($id_cpte, $comptable, $penalites_modif);
                if ($erreur->errCode != NO_ERR) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return $erreur;
                }
                $solde_cloture -= $erreur->param;
                $RET["mnt_pen"] = $erreur->param;
            }

            // Frais de tenue de compte
            $erreur = $this->preleveFraisDeTenue($id_cpte, $comptable, $frais_tenue_modif);
            if ($erreur->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $erreur;
            }
            $solde_cloture -= $erreur->param;
            $RET["mnt_frais_tenue"] = $erreur->param;

            //frais de fermeture
            $erreur = $this->preleveFraisFermeture($id_cpte, $comptable, $frais_fermeture_modif);
            if ($erreur->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $erreur;
            }
            $solde_cloture -= $erreur->param;
            $RET["mnt_frais_fermeture"] = $erreur->param;
        }

        // Cas spécifique de la cloture par le batch : dans ce cas $dest = 2 mais aucun compte n'a été spécifié

        if ($dest == 2 && $id_cpte_dest == NULL) {
            global $global_cpt_base_ouvert;
            if ($devise == $dev_ref) { // On peut transférer sur le compte de base
                if ($global_cpt_base_ouvert) {
                    $id_cpte_dest = $compte_obj->getBaseAccountID($InfoCpte["id_titulaire"]);
                    $attente_cloture = false;
                } else {
                    $attente_cloture = true;
                }
            } else {
                $attente_cloture = true;
            }
        } else {
            $attente_cloture = false;
        }

        if ($attente_cloture == true) {
            // Il faut mettre le compte dans un état intermédiaire. On ne veut pas forcer la conversion
            $updateFields = array("etat_cpte" => 5);
            $where = array("id_cpte" => $id_cpte,'id_ag'=>$this->getIdAgence());
            $sql = buildUpdateQuery("ad_cpt", $updateFields, $where);
            $result = $this->getDbConn()->execute($sql);
            if ($result === false) {
                $this->getDbConn()->rollBack();
                signalErreur(__FILE__,__LINE__,__FUNCTION__); //
            }
            $RET['attente'] = true;
        }

        else {

            // Virement du solde du compte à clôturer
            $erreur = $this->vireSoldeCloture ($id_cpte, $solde_cloture, $dest, $id_cpte_dest, $comptable);
            if ($erreur->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $erreur;
            }

            if(($raison_cloture != 2 ) || ($InfoProduit["classe_comptable"] != 6)){// on ne ferme pas le compte pour les épargnes à la source si c'est une demande du client
                //fermeture du compte, raison clôture "Sur demande du client"
                $erreur = $this->fermeCompte($id_cpte, $raison_cloture, $solde_cloture);
                if ($erreur->errCode != NO_ERR) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return $erreur;
                }
            }else{// on intialise certains champs (solde de calcul des intérêts,...) pour les épargnes à la source en cas de demande du client
                $updateFields = array("solde_calcul_interets" => 0, "date_calcul_interets"=>date("d/m/Y"), "date_solde_calcul_interets"=>date("d/m/Y"), "interet_a_capitaliser"=>0, "interet_annuel"=>0);
                $where = array("id_cpte" => $id_cpte,'id_ag'=> $this->getIdAgence());
                $sql = buildUpdateQuery("ad_cpt", $updateFields, $where);
                $result = $this->getDbConn()->execute($sql);
                if ($result === false) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__,__LINE__,__FUNCTION__); //
                }
            }
            $RET['attente'] = false;
        }

        $RET["solde_cloture"] = $solde_cloture;

        // Invalidation des mandats liés au compte
        $MANDATS = $epargne_obj->getMandats($id_cpte);
        if (is_array($MANDATS))
            foreach ($MANDATS as $key=>$value) {
                $this->invaliderMandat($key);
            }

//        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $RET);
    }

   public function updateCredit($id_doss, $Fields)
    {
        /*
         * Met à jour le dossier de crédit référencé par $id_doss Les champs seront remplacés par ceux présents dans $Fields
         */
//        global $dbHandler, $global_id_agence;
//        $db = $dbHandler->openConnection ();
        $Where ["id_doss"] = $id_doss;
        $Where ["id_ag"] = $this->getIdAgence();
        $sql = buildUpdateQuery ( "ad_dcr", $Fields, $Where );
        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
//            $dbHandler->closeConnection ( false );
            $this->getDbConn()->rollBack();
            signalErreur( __FILE__, __LINE__, __FUNCTION__, _ ( "Erreur dans la requête SQL" ) . " : " . $sql );
        }

        // #357 : équilibre inventaire - comptabilité
        // Update le num_cpt comptable pour le compte interne associe au produit de credit
        $sql = "SELECT cre_id_cpte FROM ad_dcr WHERE id_doss = :id_dossier";
        $param = array(":id_dossier" => $id_doss);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
       if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur ( __FILE__, __LINE__, __FUNCTION__);
        }
        $row = $result;

        $cre_id_cpte = $row['cre_id_cpte'];
        $myErr = $this->setNumCpteComptableForCompte ($cre_id_cpte, $db);

        // Update le num_cpt comptable pour le compte interne associe au garantie
//        $sql = "SELECT gar_num_id_cpte_nantie FROM ad_gar WHERE id_doss = :id_dossier";
//        $param = array(":id_dossier" => $id_doss);
//        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
//
//        print_rn($param);
//        if ($result === false) {
//            $this->getDbConn()->rollBack();
//            signalErreur ( __FILE__, __LINE__, __FUNCTION__);
//        }
//        $row = $result;
//
//        $gar_num_id_cpte_nantie = $row['gar_num_id_cpte_nantie'];
//        $myErr = $this->setNumCpteComptableForCompte ($gar_num_id_cpte_nantie, $db);
        // #357 fin : équilibre inventaire - comptabilité

//        $dbHandler->closeConnection ( true );
        return true;
    }

    public function getMontantReechelonne($id_doss) {
        // Fonction qui renvoie le montant rééchelonné poru le dossier $id_doss
        // Le montant rééchelonné correspond à la somme des différents montants rééchelonnés dans le cas de rééchelooements/moratoires multiples
        // Le montant est 0 si aucun rééch/mor n'a eu lieu pour ce crédit
        // IN : $id_doss : Le numéro de dossier de crédit
        // OUT: $montant_reech = Le montant rééchelonné (stocké dans ad_etr)

//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $sql = "SELECT sum(mnt_reech) FROM ad_etr WHERE id_ag= :id_agence AND id_doss = :id_dossier;";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_dossier" => $id_doss);
        $result= $this->getDbConn()->prepareFetchRow($sql, $param);
        if (DB::isError($result)) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n");
        }

        $retour = $result;

        $mnt_reech = $retour["sum"];
//        $dbHandler->closeConnection(true);
        return $mnt_reech;
    }

    public function blocageCompteInconditionnel ($id_cpte) {
        /*
         Cette PS bloque le compte $id_cpte
         */

//        global $dbHandler,$global_id_agence;
//
//        $db = $dbHandler->openConnection();

        $sql = "SELECT etat_cpte FROM ad_cpt WHERE id_ag = :id_agence AND id_cpte = :id_cpte;";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte" => $id_cpte);
        $result= $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false){
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $tmprow = $result;
        $etat = $tmprow["etat_cpte"];

        if ($etat == 2){  // Le compte est fermé
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de bloquer le compte $id_cpte qui est fermé"
        }


        //on change l'état du compte à  bloqué
        $sql = "UPDATE ad_cpt SET etat_cpte = 3 WHERE id_ag=".$this->getIdAgence()." AND id_cpte = $id_cpte;";
        $result=$this->getDbConn()->execute($sql);
        if ($result === false){
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

//        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    public function deblocageCompteInconditionnel ($id_cpte) {
        /*
         Cette PS débloque le compte $id_cpte
        */

//        global $dbHandler,$global_id_agence;
//
//        $db = $dbHandler->openConnection();

        $sql = "SELECT etat_cpte FROM ad_cpt WHERE id_ag = :id_agence AND id_cpte = :id_cpte;";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte" => $id_cpte);
        $result= $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false)
            signalErreur(__FILE__,__LINE__,__FUNCTION__);

        $tmprow = $result;
        $etat = $tmprow["etat_cpte"];

        if ($etat == 2)  // Le compte est fermé
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de débloquer le compte $id_cpte qui est fermé"

        //changer le compte à  ouvert
        $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=".$this->getIdAgence()." AND id_cpte = $id_cpte;";
        $result=$this->getDbConn()->execute($sql);
        if ($result === false)
            signalErreur(__FILE__,__LINE__,__FUNCTION__);

//        $dbHandler->closeConnection(true);

        //quel intérêt ?
        return new ErrorObj(NO_ERR);
    }

    public function checkCloture($id_cpte) {

//        global $dbHandler,$global_id_agence;

        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $InfoCpte = $compte_obj->getAccountDatas($id_cpte);

        //vérifier qu'il ne s'agit pas du compte de base
        $id_cpte_base =  $compte_obj->getBaseAccountID ($InfoCpte["id_titulaire"]);

        if ($id_cpte == $id_cpte_base) {
            return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);
        }

        if ($InfoCpte["etat_cpte"] == 3) {
            return new ErrorObj(ERR_CPTE_BLOQUE);
        }

        if ($InfoCpte["etat_cpte"] == 4)
            return new ErrorObj(ERR_CPTE_DORMANT);

//        $db = $dbHandler->openConnection();

        // Vérifier si ce compte n'est pas un compte de prélèvement d'une garantie bloquée
        $sql = "SELECT count(*) FROM ad_gar WHERE id_ag= :id_agence AND gar_num_id_cpte_prelev = :id_cpte AND etat_gar IN (1,2)";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte" => $id_cpte);
        $result=$this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $tmprow = $result;

        if ($tmprow["count"] > 0) { // Il y a au moins un crédit ou compte de garanties lié
//            $dbHandler->closeConnection(true);
            $this->getDbConn()->rollBack();
            return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);
        }

        /* Vérifié si le compte n'est pas un compte de liaison d'un crédit en cours */
        $sql = "SELECT count(*) FROM ad_dcr WHERE id_ag= :id_agence AND etat IN (1,2,5,7,14,15) AND cpt_liaison = :id_cpte";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte" => $id_cpte);
        $result=$this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $tmprow = $result;

//        $dbHandler->closeConnection(true);

        if ($tmprow["count"] > 0) // Il y a au moins un crédit ou compte de garanties lié
            return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);

        return new ErrorObj(NO_ERR);
    }

    public function arreteCompteEpargne($InfoCpte=array(), $InfoProduit=array(), &$comptable) {
        /*
          Calcul les intérêts pour les comptes rémunérés DAV, DAT, Autres dépôts, Capital social arrivés à échéance
          en principe appelée pour une rupture anticipée
          OUT  objet Error contenant en paramètre le solde de cloture
        */

//        global $dbHandler, $global_id_agence;
//        $db = $dbHandler->openConnection();
        $agence = new Agence($this->getDbConn(), $this->getIdAgence());

        /* Récupération du taux de base de l'épargne de l'agence */
        $AG = $agence->getAgenceDatas($this->getIdAgence());

        if ($AG["base_taux_epargne"] == 1)
            $base_taux = 360;
        elseif($AG["base_taux_epargne"] == 2)
            $base_taux = 365;

        /* Initialisation des intérêts à la rupture */
        $interets = 0;

        /* Si c'est un compte à terme ( DAT ou CAT ) qui n'est pas en attente */
        if ($InfoCpte["terme_cpte"] > 0 and $InfoCpte["etat_cpte"] != 5) {
            $today = date("d/m/Y");
            $temp_today = explode("/", $today);
            $temp_today = mktime(0,0,0,$temp_today[1],$temp_today[0],$temp_today[2]);

            $date_fin = pg2phpDate($InfoCpte["dat_date_fin"]);
            $temp_date_fin = explode("/", $date_fin);
            $temp_date_fin = mktime(0,0,0,$temp_date_fin[1],$temp_date_fin[0],$temp_date_fin[2]);

            /* s'agit-il d'une rupture anticipée ? */
            if ($temp_today <= $temp_date_fin) {
                if ($InfoProduit["mode_calcul_int_rupt"]== 1)  /* Sans intérêts à la rupture */
                    $interets = 0 ;
                elseif ($InfoProduit["mode_calcul_int_rupt"] == 2 or $InfoProduit["mode_calcul_int_rupt"] == 3) {
                    /* Intérêts au prorata ou Intérêts sur le reste du terme */
                    $date_ouv = $InfoCpte["date_ouvert"];
                    $date_las_cap = $InfoCpte["date_calcul_interets"];
                    $mode_paie = $InfoCpte["mode_paiement_cpte"];
                    $freq_cap = $InfoCpte["freq_calcul_int_cpte"];
                    $terme = $InfoCpte["terme_cpte"];

                    /* Intérêts au prorata, récupérer nombre de mois entre date du jour et dernière capitalisation (ou date ouverture)  */
                    if ($InfoProduit["mode_calcul_int_rupt"] == 2)
                        $date_cap = date("d/m/Y");

                    /* Intérêts pour le reste du terme, récupérer nombre de mois entre date fin du compte  et dernière capitalisation  */
                    if ($InfoProduit["mode_calcul_int_rupt"] == 3)
                        if (isset($InfoCpte["dat_date_fin"]))
                            $date_cap = $InfoCpte["dat_date_fin"];
                        else
                            $date_cap = date("d/m/Y");

                    $nb_jours = $this->getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap, pg2phpDate($InfoCpte["dat_date_fin"]));

                    //Jira MAE-22/27 formule calcul interet
                    if ($AG['appl_date_val_classique'] == 't'){
                        $interets = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours)/ $base_taux;
                    }
                    else{
                        /* Calcul des intérêts à payer à la rupture */
                        $interets = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours)/ $base_taux;
                        if($InfoCpte['mode_calcul_int_cpte'] == 12){// Si mode épargne à la source, intérêts prend la valeur du champs interet_a_capitaliser qui cumule les intérêts entre deux dates de capitalisation
                            $interets = $InfoCpte['interet_a_capitaliser'];
                        }
                    }
                    // #356 : ne pas arrondir
                    // REL-101 : arrondir les interets
                    $interets = arrondiMonnaie($interets, 0, $InfoCpte['devise']);
                }else $interets = 0;

            } /* Fin si date du jour < date de fin  */

            /* Si le compte de versement des intérêts n'est pas renseigné, prendre le compte lui-même */
            if (!isset($InfoCpte["cpt_vers_int"]) or $InfoCpte["cpt_vers_int"] == NULL)
                $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];

            if ($InfoCpte["cpt_vers_int"] == $InfoCpte["id_cpte"])
                $solde_cloture = $InfoCpte["solde"] + $interets;
            else
                $solde_cloture = $InfoCpte["solde"];

            /* Versement des intérêts */
            if ($interets > 0) {
                $erreur = $this->payeInteret($InfoCpte["id_cpte"], $InfoCpte["cpt_vers_int"], $interets, $comptable);
                if ($erreur->errCode != NO_ERR) {
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return $erreur;
                }
            }
        } else { /* C'est pas un compte à terme */
            /* Pas de calcul d'intérêts ni de pénalités */
            $solde_cloture = $InfoCpte["solde"]; /* solde courant du compte */

            /* Cas des comptes de garantie.On considère que le crédit est soldé et on qu'on veut clôturer le compte de garantie */
            /* On considère que solde clôture = solde courant + les garanties incluses dans les derniers remboursements non encore commités */

            if ($InfoCpte["id_prod"]== 4) { /* Si c'est un compte de garantie */
                /* Parcours des écritures comptables en attente */
                if (is_array($comptable))
                    foreach($comptable as $key=>$value) {
                        /* S'il y a des mouvements en attente pour le compte de garantie */
                        if ($value["cpte_interne_cli"] == $InfoCpte["id_cpte"]) {
                            if ($value["sens"] == SENS_CREDIT and $value["montant"] > 0)
                                $solde_cloture += $value["montant"];
                            elseif($value["sens"] == SENS_DEBIT and $value["montant"] > 0)
                                $solde_cloture -= $value["montant"];
                        }
                    }
            }
        }

//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, array('solde_cloture' => $solde_cloture, 'int' => $interets));

    }

    public function getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap, $dat_date_fin) {
//        global $dbHandler;
//        $db = $dbHandler->openConnection();

        $sql = "SELECT getPeriodeCapitalisation(date('$date_cap'), date('$date_ouv'), ";
        if ($date_las_cap == NULL) { /* Si c'est la première rémunération */
            $sql .= "NULL,date('$dat_date_fin'));";
            $param = array(":date_cap" => $date_cap, ":date_ouv" =>$date_ouv, ":date_fin" => $dat_date_fin);
        }
        else {
            $sql .= "date('$date_las_cap'),date('$dat_date_fin'));";
            $param = array(":date_cap" => $date_cap, ":date_ouv" =>$date_ouv, ":date_las_cap" => $date_las_cap, ":date_fin" => $dat_date_fin);
        }
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $row = $result;
        $nb_jours = $row["nb_jours"];

//        $dbHandler->closeConnection(true);
        return $nb_jours;
    }

    public function payeInteret($id_cpte, $id_cpte_dest, $interets, &$comptable)
    {
        // FIXME : que fait-on avec les comptes bloqués ?

        global $global_id_agence, $dbHandler, $global_monnaie;

//        $db = $dbHandler->openConnection();
        $compte = new Compte($this->getDbConn(), $this->getIdAgence());
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());

        $ACC = $compte->getAccountDatas($id_cpte);
        $devise = $ACC["devise"];
        $interets_calcules = 0;
        $interets_diff = 0;
        $cpte_cpta_int_paye = '';

        //calcul des interets a payer : #356
        $erreur = $this->getIntCptEpargneCalculInfos($id_cpte);

        if ($erreur->errCode != NO_ERR) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            return $erreur;
        }

        if(!is_null($erreur->param)) {
            $infosCalc = $erreur->param;
            $interets_calcules = $infosCalc['interets_calcules'];
            $cpte_cpta_int_paye = $infosCalc['cpte_cpta_int_paye'];
        }

        //versement de l'intérêt : débit compte charge / crédit compte client
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        $cpte_compta_int_prod = $this->getCompteCptaProdEpInt($id_cpte);
        if ($cpte_compta_int_prod == NULL) {
            $this->getDbConn()->rollBack();
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable des intérêts associé au produit d'épargne"));
        }

        $cpte_compta_assoc_prod = $compte->getCompteCptaProdEp($id_cpte_dest);
        if ($cpte_compta_assoc_prod == NULL) {
            $this->getDbConn()->rollBack();
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        // Si on des interets calculee pour le compte, a comptabiliser
        if($interets_calcules > 0  && !is_null($cpte_cpta_int_paye))
        {
            $interets_diff = $interets - $interets_calcules;

            $cptes_substitue["cpta"]["debit"] = $cpte_cpta_int_paye;
            $cptes_substitue["cpta"]["credit"] = $cpte_compta_assoc_prod;
            $cptes_substitue["int"]["credit"] = $id_cpte_dest;

            // Comptabiliser les interets a calculer
            // Les intérts sont comptabilisés au débit en devise de référence, il faut donc appeler effectueChangePrivate en mettant la varialbe mnt_debit ) false car c'est le montant au crédit qui est fourni
            $erreur = $devise_obj->effectueChangePrivate($global_monnaie, $devise, $interets_calcules, 40, $cptes_substitue, $comptable, false);

            if ($erreur->errCode != NO_ERR) {
//                $this->getDbConn()->rollBack();
                $this->getDbConn()->rollBack();
                return $erreur;
            }

            // Comptabiliser la difference
            if ($interets_diff > 0)
            {
                $cptes_substitue["cpta"]["debit"] = $cpte_compta_int_prod;
                $cptes_substitue["cpta"]["credit"] = $cpte_compta_assoc_prod;
                $cptes_substitue["int"]["credit"] = $id_cpte_dest;

                $erreur = $devise_obj->effectueChangePrivate($global_monnaie, $devise, $interets_diff, 40, $cptes_substitue, $comptable, false);

                if ($erreur->errCode != NO_ERR) {
                    $this->getDbConn()->rollBack();
                    return $erreur;
                }
            }
        }
        else  // Aucun interet calculee
        {
            $cptes_substitue["cpta"]["debit"] = $cpte_compta_int_prod;
            $cptes_substitue["cpta"]["credit"] = $cpte_compta_assoc_prod;
            $cptes_substitue["int"]["credit"] = $id_cpte_dest;

            // Les intérts sont comptabilisés au débit en devise de référence, il faut donc appeler effectueChangePrivate en mettant la varialbe mnt_debit ) false car c'est le montant au crédit qui est fourni
            $erreur = $devise_obj->effectueChangePrivate($global_monnaie, $devise, $interets, 40, $cptes_substitue, $comptable, false);

            if ($erreur->errCode != NO_ERR) {
                $this->getDbConn()->rollBack();
                return $erreur;
            }
        }

        //FIXME : Doit-on mettre à jour solde calcul intérêts à solde ?

        // màj champ interets-annuels du compte
        // FIXME : il faut réinitialiser ce champ en fin d'exo
        $sql = "UPDATE ad_cpt SET interet_annuel = interet_annuel + $interets WHERE id_ag=".$this->getIdAgence()." AND id_cpte = $id_cpte;";
        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

//        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);

    }

    public function getIntCptEpargneCalculInfos($id_cpte)
    {
//        global $dbHandler, $global_id_agence;
//        $db = $dbHandler->openConnection();

        $date_calcul = date("d/m/Y"); // date du jour
        $sql = "SELECT SUM(montant_int) FROM ad_calc_int_paye_his
          WHERE id_cpte = $id_cpte AND date_calc <= date(':date_calcul') AND etat_calc_int = 1 AND id_ag = :id_agence;";
        $param = array(":date_calcul" => $date_calcul, ":id_agence" => $this->getIdAgence());

        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if(count($result) == 0) {
            $this->getDbConn()->rollBack();//$dbHandler->closeConnection(false);
            return new ErrorObj(NO_ERR, NULL);
        }

        $row = $result;
        $interets_calcules =$row["sum"];

        if(is_null($interets_calcules)) {
            $this->getDbConn()->rollBack();//$dbHandler->closeConnection(false);
            return new ErrorObj(NO_ERR, NULL);
        }

        $sql = "SELECT cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = :id_agence LIMIT 1;";
        $param = array(":id_agence" => $this->getIdAgence());
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $row = $result->fetchrow();
        $cpte_cpta_int_paye =$row["cpte_cpta_int_paye"];

        if($interets_calcules > 0 && is_null($cpte_cpta_int_paye)) {
            $this->getDbConn()->rollBack();
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable des calculs d'intérêts sur les comptes d'épargnes"));
        }

        $infos = array('interets_calcules' => $interets_calcules, 'cpte_cpta_int_paye' => $cpte_cpta_int_paye);

//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $infos);
    }

    public function getCompteCptaProdEpInt($id_cpte_cli) {
//        global $dbHandler, $global_id_agence;
//
//        $db = $dbHandler->openConnection();

        $sql = "SELECT cpte_cpta_prod_ep_int ";
        $sql .= "FROM ad_cpt a, adsys_produit_epargne b  ";
        $sql .= "WHERE a.id_ag = :id_agence AND a.id_ag = b.id_ag AND a.id_prod = b. id AND a.id_cpte=':id_cpte_cli'";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte_cli" => $id_cpte_cli);
        $result = $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        if (count($result) == 0) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
        }
        $cpte_cpta = $result;
//        $dbHandler->closeConnection(true);

        return $cpte_cpta["cpte_cpta_prod_ep_int"];

    }


    public function prelevePenalitesEpargne($id_cpte, &$comptable, $penalites=NULL) {
        global $dbHandler, $global_monnaie_prec, $global_client_debiteur, $global_monnaie;

        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $epargne_obj = new Epargne($this->getDbConn(), $this->getIdAgence());
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());

        $ACC = $compte_obj->getAccountDatas($id_cpte);
        $PROD = $epargne_obj->getProdEpargne($ACC["id_prod"]);

        // On vérifie d'abord qu'il s'agit bien d'un compte à terme
        if ($PROD["terme"] > 0) {

//            $db = $dbHandler->openConnection();

            $cpte_date_fin = $ACC["dat_date_fin"];
            $solde = $ACC["solde"];
            if (isset($penalites)) {
                $penalites_const = $penalites;
                $penalites_prop = 0;
            } else {
                $penalites_const = $PROD["penalite_const"];
                $penalites_prop = $PROD["penalite_prop"];
            }
            $devise = $ACC["devise"];
            $dev_ref = $global_monnaie;
            $DEV = $devise_obj->getInfoDevise($devise);

            $today = date("d/m/Y");
            $today = explode("/", $today);
            $today = mktime(0,0,0,$today[1],$today[0],$today[2]);

            $date_fin = pg2phpDate($cpte_date_fin);
            $date_fin = explode("/", $date_fin);
            $date_fin = mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]);

            //si rupture anticipée
            if ( $date_fin > $today ) {

                if (($penalites_const > 0) || ($penalites_prop > 0)) {
                    //FIXME : on prend quel solde pour calculer les pénalités ?
                    $penalites = ($penalites_const + ($solde * $penalites_prop));
                    $penalites = round($penalites, $DEV["precision"]);

                    if ($penalites > 0) {
                        //FIXME : est-ce que c'est la bonne manière de faire ?
                        //Si le client est débiteur , on ne pourra pas prendre les penalites  sur le compte de base
                        /* OBSOLETE
                        if ($global_client_debiteur)
                          {
                            $dbHandler->closeConnection(false);
                            return new ErrorObj(ERR_CLIENT_DEBITEUR);
                            } */

                        //débit compte à cloturer / crédit compte de produit
                        $cptes_substitue = array();
                        $cptes_substitue["cpta"] = array();
                        $cptes_substitue["int"] = array();

                        $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($id_cpte);
                        if ($cptes_substitue["cpta"]["debit"] == NULL) {
                            $this->getDbConn()->rollBack();
                            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                        }

                        $cptes_substitue["int"]["debit"] = $id_cpte;

                        $erreur = $devise_obj->effectueChangePrivate($devise, $dev_ref, $penalites, 110, $cptes_substitue, $comptable);
                        if ($erreur->errCode != NO_ERR) {
//                            $dbHandler->closeConnection(false);
                            $this->getDbConn()->rollBack();
                            return $erreur;
                        }

                    }//if pénalités > 0

                }//if pénalités

            } //if date > today
//            $dbHandler->closeConnection(true);
        } else {
            return new ErrorObj(ERR_NON_CAT);
        }


        return new ErrorObj(NO_ERR, $penalites);
    }

    public function preleveFraisDeTenue($id_cpte, &$comptable, $frais_tenue= NULL) {
        global $dbHandler, $global_client_debiteur, $global_monnaie;

        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $epargne_obj = new Epargne($this->getDbConn(), $this->getIdAgence());
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());

        $ACC = $compte_obj->getAccountDatas($id_cpte);
        $PROD = $epargne_obj->getProdEpargne($ACC["id_prod"]);

        if (!isset($frais_tenue))
            $frais_tenue = $PROD["frais_tenue_cpt"];

        $devise = $ACC["devise"];
        $dev_ref = $global_monnaie;

//        $db = $dbHandler->openConnection();

        if ($frais_tenue > 0) {
            //ne pas mvter les cptes si le montant est nul
            $type_ope = 50;
            $subst = array();
            $subst["cpta"] = array();
            $subst["int"] = array();
            $subst["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($id_cpte);
            if ($subst["cpta"]["debit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
            }
            $subst["int"]["debit"] = $id_cpte;

            $myErr = $this->reglementTaxe($type_ope, $frais_tenue, SENS_CREDIT, $devise, $subst, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
            $montant_tva = $myErr->param['montant_credit'];

            //débit compte de base / crédit compte de produit
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $erreur = $devise_obj->effectueChangePrivate($devise, $dev_ref, $frais_tenue, 50, $cptes_substitue, $comptable);
            if ($erreur->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $erreur;
            }

        }
        if ($montant_tva >0){
            $frais_tenue += $montant_tva;
        }
//        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $frais_tenue);

    }

    public function reglementTaxe($type_operation, $montant, $sens, $devise, $cptes_substitue, &$comptable){
        global $dbHandler, $global_id_agence, $global_monnaie, $global_id_exo, $global_nom_login;

//        $db = $dbHandler->openConnection();
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());
        $taxesOperation = $this->getTaxesOperation($type_operation);
        $details_taxesOperation = $taxesOperation->param;
        if (sizeof($details_taxesOperation) > 0){
            $subst_tva = array();
            $subst_tva["cpta"] = array();
            $subst_tva["int"] = array();
            if ($sens == SENS_DEBIT) {
                $devise_debit_tax = $global_monnaie;
                $devise_credit_tax = $devise;
                $mnt_debit = false;
                $type_oper_tax = 473;//paiement tva récupérable
                $subst_tva["cpta"]["debit"] = $details_taxesOperation[1]["cpte_tax_ded"];
                if ($subst_tva["cpta"]["debit"] == NULL){
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé à la taxe récupérable: ").$details_taxesOperation[1]["libel_taxe"]);
                }
                $subst_tva["cpta"]["credit"] = $cptes_substitue["cpta"]["credit"];
                $subst_tva["int"]["credit"] = $cptes_substitue["int"]["credit"];

            } else {
                $devise_debit_tax = $devise;
                $devise_credit_tax = $global_monnaie;
                $mnt_debit = true;
                $type_oper_tax = 474;//perception tva collectée
                $subst_tva["cpta"]["debit"] = $cptes_substitue["cpta"]["debit"];
                $subst_tva["int"]["debit"] = $cptes_substitue["int"]["debit"];
                $subst_tva["cpta"]["credit"] = $details_taxesOperation[1]["cpte_tax_col"];
                if ($subst_tva["cpta"]["credit"] == NULL){
//                    $dbHandler->closeConnection(false);
                    $this->getDbConn()->rollBack();
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé à la taxe collectée: ").$details_taxesOperation[1]["libel_taxe"]);
                }
            }

            $mnt_tax = $montant * $details_taxesOperation[1]["taux"];
            $myErr = $devise_obj->effectueChangePrivate($devise_debit_tax, $devise_credit_tax, $mnt_tax, $type_oper_tax, $subst_tva, $comptable, $mnt_debit);
            if ($myErr->errCode != NO_ERR) {
                $this->getDbConn()->rollBack();
                return $myErr;
            }

        }
//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $myErr->param);
    }

    public function getTaxesOperation($type_oper=NULL) {
        global $dbHandler, $global_id_agence, $global_langue_systeme_dft;
//        $db = $dbHandler->openConnection();

        // récupération des taxes appliquées à l'opération
        $sql = "SELECT a.type_taxe, a.id_taxe, a.type_oper, traduction(b.libel,  ':global_langue_systeme_dft') as libel_taxe, b.taux, b.cpte_tax_col, b.cpte_tax_ded from ad_oper_taxe a , adsys_taxes b where a.id_ag = b.id_ag and b.id_ag = :id_agence and a.id_taxe = b.id ";
        $param = array(":global_langue_systeme_dft" => $global_langue_systeme_dft, ":id_agence" => $this->getIdAgence());
        if ($type_oper != NULL)
            $sql .= "and a.type_oper = $type_oper ";
        $result = $this->getDbConn()->prepareFetchAll($sql, $param);
        $dbHandler->closeConnection(true);

        if ($result === false) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $taxes = array();
        if (count($result) == 0) {
            return new ErrorObj(NO_ERR, $taxes);
        }
        else{
            foreach ($result as $row) {
                $taxes[$row["type_taxe"]] = array("type_taxe"=>$row["type_taxe"], "id_taxe"=>$row["id_taxe"], "libel_taxe"=>$row["libel_taxe"],
                    "taux"=>$row["taux"], "cpte_tax_col"=>$row["cpte_tax_col"], "cpte_tax_ded"=>$row["cpte_tax_ded"]);
            }
        }
        return new ErrorObj(NO_ERR, $taxes);
    }

    public function preleveFraisFermeture($id_cpte, &$comptable, $frais_fermeture = NULL) {
        /*
          Lors de la fermeture d'un compte d'épargne, on prend les frais de fermeture s'il y en a
        */

        global $dbHandler, $global_client_debiteur, $global_monnaie;

        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());
        $epargne_obj = new Epargne($this->getDbConn(), $this->getIdAgence());
        $devise_obj = new Devise($this->getDbConn(), $this->getIdAgence());

        $ACC = $compte_obj->getAccountDatas($id_cpte);
        if (!isset($frais_fermeture))
            $frais_fermeture = $ACC["frais_fermeture_cpt"];

        $devise = $ACC["devise"];
        $dev_ref = $global_monnaie;

//        $db = $dbHandler->openConnection();

        if ($frais_fermeture > 0) {
            // Calcul de la TVA sur frais
            /*$taxesOperation = getTaxesOperation($type_operation);
            $details_taxesOperation = $taxesOperation->param;
            if(sizeof($details_taxesOperation)>0){
              $mnt_TVA = $frais_fermeture * $details_taxesOperation[1]['taux'];
            }
            */
            //débit compte de base / crédit compte de produit
            $type_ope = 60;
            $subst = array();
            $subst["cpta"] = array();
            $subst["int"] = array();
            $subst["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($id_cpte);
            if ($subst["cpta"]["debit"] == NULL) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
            }
            $subst["int"]["debit"] = $id_cpte;

            $myErr = $this->reglementTaxe($type_ope, $frais_fermeture, SENS_CREDIT, $devise, $subst, $comptable);
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $myErr;
            }
            $montant_tva = $myErr->param['montant_credit'];


            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $compte_obj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $erreur = $devise_obj->effectueChangePrivate($devise, $dev_ref, $frais_fermeture, 60, $cptes_substitue, $comptable);
            if ($erreur->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return $erreur;
            }

        }//if frais fermeture > 0

        if ($montant_tva >0){
            $frais_fermeture += $montant_tva;
        }
//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $frais_fermeture);

    }

    public function fermeCompte ($id_cpte, $raison_cloture, $solde_cloture, $date_cloture=NULL) {
        /*  $ACC = getAccountDatas($id_cpte);
        if ($ACC["solde"] != $solde_cloture)
          return new ErrorObj(ERR_CPTE_SOLDE_NON_NUL, ($ACC["solde"] - $solde_cloture));
        */

//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $fields_array = array();
        $fields_array["etat_cpte"] = 2; // Compte fermé
        $fields_array["raison_clot"] = $raison_cloture;
        if ($date_cloture == NULL)
            $fields_array["date_clot"] = date("d/m/Y");
        else
            $fields_array["date_clot"] = $date_cloture;

        $fields_array["solde_clot"] = $solde_cloture;

        $sql = buildUpdateQuery ("ad_cpt", $fields_array, array("id_cpte"=>$id_cpte,'id_ag'=>$this->getIdAgence()));

        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        };

//        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    public function invaliderMandat($id_mandat) {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $DATA['valide'] = 'f';
        $WHERE['id_mandat'] = $id_mandat;
        $WHERE['id_ag'] = $this->getIdAgence();

        $sql = buildUpdateQuery('ad_mandat', $DATA, $WHERE);

        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR);
    }

    public function getMntTotPremierEch($id_client, $id_doss) {
//        global $dbHandler,$global_id_agence;
//
//        $db = $dbHandler->openConnection();
        $sql = "select (etr.mnt_cap + etr.mnt_int) as tot_ech from ad_cli cli inner join ad_dcr dcr on cli.id_client = dcr.id_client inner join ad_etr etr on dcr.id_doss = etr.id_doss
where etr.id_ech = 1 and cli.id_client = :id_client and dcr.id_doss = :id_doss";
        $param = array(":id_client" => $id_client, ":id_doss" => $id_doss);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $retour = $result;
//        $dbHandler->closeConnection(true);
        return $retour['tot_ech'];
    }

    public function ajouterQuotite($DATA) {
        global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
//        $db = $dbHandler->openConnection();
        $DATA['id_ag']= $this->getIdAgence();

        $sql = buildInsertQuery('ad_quotite',$DATA);
        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        // Enregistrement - Ajout d'un ordre permanent
        //ajout_historique(56, $global_id_client, 'Ajout d\'un ordre permanent', $global_nom_login, date("r"), NULL);

//        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR);
    }

    public function update_quotite_client($DATA_update,$DATA_where) {

//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $update_quotite_cli = buildUpdateQuery('ad_cli',$DATA_update,$DATA_where);
        $result_quotite_cli = $this->getDbConn()->execute($update_quotite_cli);
        if ($result_quotite_cli === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        else {
            return new ErrorObj(NO_ERR);
        }
    }

    public function calculeEtatPlusAvance($id_doss) {

//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $date = array();
        $sql1 = "SELECT a.date_ech, b.date_remb from ad_etr a, ad_sre b WHERE a.id_doss = b.id_doss and b.id_doss = :id_dossier and a.id_ech = b.id_ech";
        $param = array(":id_dossier" => $id_doss);
        $result1 = $this->getDbConn()->prepareFetchAll($sql1, $param);
        if ($result1 === false) {
//            $dbHandler->CloseConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql1);
        }

        $sql2 = "SELECT date_ech from ad_etr WHERE id_doss = :id_dossier AND remb = 'f'";
        $param = array(":id_dossier" => $id_doss);
        $result2 = $this->getDbConn()->prepareFetchRow($sql2, $param);
        if ($result2 === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql2);
        }
        $row2 = $result2;

        foreach ($result1 as $row1) {
            $date['date_remb'] = pg2phpDatebis($row1['date_remb']);
            $date['date_ech'] = pg2phpDatebis($row1['date_ech']);

            if ($row2["date_ech"] < date("d/m/Y")){
                // on calcule le nombre de jours entre la date du remboursement et la date de l'echeance suivante
                $nbre_secondes2 = gmmktime(0,0,0,date('m'), date('d'), date('y')) - gmmktime(0,0,0,$date['date_remb'][0], $date['date_remb'][1], $date['date_remb'][2]);
                $nbre_jours2 = $nbre_secondes2/(3600*24);
                $etat = $this->calculeEtatCredit($nbre_jours2);
            } else {
                // on calcule le nombre de jours entre la date de l'échéance et la date du remboursement
                $nbre_secondes1 = gmmktime(0,0,0,$date['date_remb'][0], $date['date_remb'][1], $date['date_remb'][2])-gmmktime(0,0,0,$date['date_ech'][0], $date['date_ech'][1], $date['date_ech'][2]);
                $nbre_jours1 = $nbre_secondes1/(3600*24);
                $etat = $this->calculeEtatCredit($nbre_jours1);
            }
        }

//        $dbHandler->closeConnection(true);
        return $etat;
    }

    public function calculeEtatCredit($nbre_jours_retard) {
        global $error,$global_id_agence, $dbHandler;

//        $db = $dbHandler->openConnection();

        $agence_obj = new Agence($this->getDbConn(), $this->getIdAgence());
        $infos_ag = $agence_obj->getAgenceDatas($this->getIdAgence());

        // $nbre_max_jours la somme des nombres de jours de retard des états de crédits, excepté les crédits en perte et à radier
        // si $nbre_jours_retard depasse $nbre_max_jours, le credit doit être à l'état en perte ou à l'état "à radier"
        $sql = "SELECT sum(nbre_jours) from adsys_etat_credits";
        $sql.=" where nbre_jours > 0 and id_ag=:id_agence ";
        $param = array(":id_agence" => $this->getIdAgence());
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n");
        }
        $row = $result;
//        $dbHandler->closeConnection(true);
        $nbre_max_jours = $row["sum"];

        if ($nbre_jours_retard >= $nbre_max_jours){// passer en perte si passage automatique, passer à l'état "à radier" sinon
            if($infos_ag['passage_perte_automatique'] == "t"){
                $id = (int) $this->getIDEtatPerte();
                return $id;
            }
            else {
                $myErr = $this->getIDEtatARadier();
                if ($myErr->errCode != NO_ERR) {
                    signalErreur(__FILE__,__LINE__,__FUNCTION__, $error[$myErr->errCode].$myErr->param);
                    return NULL;
                }
                $id = $myErr->param;
                return $id;
            }
        }
        else{
            $ETATS = $this->getTousEtatCredit();
            $interv_max = -1;
            $trouve = false;
            $id = null;
            foreach ($ETATS as $id => $ETAT) {

                $interv_min = $interv_max+1;

//		// Pas de limite, si etat=perte
//		if ($ETAT["nbre_jours"] == -1)
//      $interv_max = RETARD_INFINI;
//    else
                $interv_max = $interv_min + $ETAT["nbre_jours"] - 1;
                if ($nbre_jours_retard >= $interv_min && $nbre_jours_retard <= $interv_max) {
                    $trouve = true;
                    break;
                }
            }
            if ($trouve) {
                return $id;
            }
            else { // Quelque chose a cloché
                return 0;
            }
        }
    }

    public function getIDEtatPerte() {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        // Recherche de l'état correspondant à en perte (c'est le dernier état)
        $sql = "SELECT id FROM adsys_etat_credits where nbre_jours = -1 and id_ag = :id_agence ";
        $param = array(":id_agence" => $this->getIdAgence());
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n");
        }
        $row = $result;
//        $dbHandler->closeConnection(true);
        return $row["id"];
    }

    public function getIDEtatARadier() {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        // Recherche de l'état correspondant à en perte (c'est le dernier état)
        $sql = "SELECT id FROM adsys_etat_credits";
        $sql.=" where nbre_jours = -2 and id_ag=:id_agence ";
        $param = array(":id_agence" => $this->getIdAgence());

        $result = $this->getDbConn()->prepareFetchRow($sql, $param);;
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n");
        }
        $row = $result;
        if(count($result) == 0){
//            $dbHandler->closeConnection(true);
            $this->getDbConn()->rollBack();
            return new ErrorObj (ERR_CPTE_ETAT_CRE_NON_PARAMETRE, _("Etat à radier"));
        } else{// si l'état à radier n'est pas paramétré, renvoie l'état en perte
//            $dbHandler->closeConnection(true);
            return new ErrorObj(NO_ERR, $row["id"]);
        }

    }

    public function getTousEtatCredit($en_retard = false) {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $retour = array();
        $sql = "SELECT * FROM adsys_etat_credits ";
        $sql.=" where id_ag = :id_agence ";
        if($en_retard){
            $sql.=" AND nbre_jours != 1 AND nbre_jours != -1 ";
        }
        $sql.=" ORDER BY id ";
        $param = array(":id_agence" => $this->getIdAgence());

        $result= $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
//        $dbHandler->closeConnection(true);
        if (count($result) == 0) return NULL;
        foreach ($result as $rows)
            $retour[$rows["id"]]=$rows;
        return $retour;
    }

    public function placeCapitalCredit($id_dossier,$ancien_etat,$nouv_etat, &$comptable, $devise) {
        global $dbHandler;
        global $appli, $date_total;
        global $mouvement_declassement;
        global $appli;

        $credit_obj = new Credit($this->getDbConn(), $this->getIdAgence());
        $compte_obj = new Compte($this->getDbConn(), $this->getIdAgence());

        // Récupére l'id du produit de crédit lié au dossier de crédit
        $dossier = $credit_obj->getDossierCrdtInfo($id_dossier);
        if (!is_array($dossier)) {
            return new ErrorObj (ERR_DOSSIER_NOT_EXIST, $id_dossier);
        }

        $id_produit_credit=$dossier["id_prod"];

        // AT-68 : Check si la date est une date de fin d'annees
        if ($appli == 'batch') {
            $finAnnee = $this->checkIfIsFinAnnee($date_total);
        }

        // récupére les comptes liées aux états de cérdit
        $cpt_etat= $this->recup_compte_etat_credit($id_produit_credit);
        $cpt_ancien_etat=$cpt_etat[$ancien_etat];
        $cpt_nouv_etat=$cpt_etat[$nouv_etat];

        if ($cpt_nouv_etat == NULL) {
            $produit = $this->getProdInfoByID();
            return new ErrorObj (ERR_CPTE_ETAT_CRE_NON_PARAMETRE, $produit[$id_produit_credit]["libel"]);
        }

        if ($cpt_ancien_etat != $cpt_nouv_etat) {
            // récupére le montant du capital restant du
//            $db = $dbHandler->openConnection();

            // Recherche du capital restant dû
            $solde = $this->getSoldeCapital($id_dossier);

            //déplacer le capital restant dû de l'ancien vers le nouveau compte
            $cptes_substitue["int"]["debit"] = $dossier['cre_id_cpte'];
            $cptes_substitue["int"]["credit"] = $dossier['cre_id_cpte'];
            $cptes_substitue["cpta"]["debit"] =$cpt_nouv_etat;
            $cptes_substitue["cpta"]["credit"] = $cpt_ancien_etat;

            //Test verification set date par rapport au reclassement/declassement
            if ($appli == 'batch'){
                if ($ancien_etat > $nouv_etat){ //Reclassement
                    $date_reclassement = $date_total;
                    $myErr = $compte_obj->passageEcrituresComptablesAuto(270, $solde, $comptable, $cptes_substitue, $devise,$date_reclassement,$id_dossier);
                }
                else{ //Declassement
                    $date_declassement = demain($date_total);
                    //creation de cette array pour recuperer les dossier qui vont declasser dans l'exercice suivante. Ticket AT-68
                    if (isset($finAnnee) && $finAnnee == 't'){
                        $mouvement_declassement[$id_dossier] = $id_dossier;
                    }
                    $myErr = $compte_obj->passageEcrituresComptablesAuto(270, $solde, $comptable, $cptes_substitue, $devise,$date_declassement,$id_dossier);
                }
            }
            else{
                $myErr = $compte_obj->passageEcrituresComptablesAuto(270, $solde, $comptable, $cptes_substitue, $devise,NULL,$id_dossier);
            }
            if ($myErr->errCode != NO_ERR) {
//                $dbHandler->closeConnection(false);
                $this->getDbConn()->rollBack();
                return new ErrorObj (ERR_DOSSIER_NOT_EXIST, $id_dossier);
            }

//            $dbHandler->closeConnection(true);
        }

        return new ErrorObj(NO_ERR);
    }

    public function checkIfIsFinAnnee($date_jour) {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        $sql = "select isfinannee(date(':date_jour'))";
        $param = array(":date_jour" => $date_jour);
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
        }
        $row = $result;
        $is_fin_annee = $row["fin_ann"];
//        $dbHandler->closeConnection(true);
        return $is_fin_annee;
    }

    public function getProdInfoByID($id=null) {
        global $dbHandler,$global_id_agence;
        $Produit = array();
//        $db = $dbHandler->openConnection();

        $sql = "SELECT * FROM adsys_produit_credit";
        $sql.=" where id_ag=:id_agence ";
        $param = array(":id_agence" => $this->getIdAgence());
        if($id != null) {
            $sql.=" and id = $id ";
        }
        $result= $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($result === false) {
//            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
        }
        foreach ($result as $rows) {
            $Produit[$rows['id']] = $rows;
        }
//        $dbHandler->closeConnection(true);
        return $Produit;
    }

    public function getSoldeCapital ($id_dossier, $date=NULL) {
//        global $dbHandler,$global_id_agence;
//        $db = $dbHandler->openConnection();

        if($date == NULL)
            $date=date("Y")."-".date("m")."-".date("d");
        $sql = "SELECT sum(mnt_cap) from ad_etr where id_doss=:id_dossier ";
        $sql.=" and id_ag=:id_agence ";
        $param = array(":id_dossier" => $id_dossier, ":id_agence" => $this->getIdAgence());
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            Signalerreur(__FILE__,__LINE__,__FUNCTION__, "DB: ");
        }
        $principal = $result;
        $sql = "SELECT sum(mnt_remb_cap) from ad_sre where id_doss= :id_dossier and date_remb <=:date";
        $sql.=" and id_ag= :id_agence ";
        $param = array(":id_dossier" => $id_dossier, ":date" => $date, ":id_agence" => $this->getIdAgence());
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($result === false) {
            $this->getDbConn()->rollBack();
            Signalerreur(__FILE__,__LINE__,__FUNCTION__, "DB: ");
        }
//        $dbHandler->closeConnection(true);
        $principalrepayed = $result;
        $soldeCapital = ($principal["sum"]-$principalrepayed["sum"]) > 0 ? $principal["sum"]-$principalrepayed["sum"]:0;
        return $soldeCapital;
    }

    public function setNumCpteComptableForCompte($id_cpte, &$db)
    {
        global $error, $global_id_agence, $dbHandler;

        $num_cpte_comptable = NULL;

        // validation
        if (empty($id_cpte)) {
            return false; // le compte interne n'est pas defini, on ne fait rien
        }

        //verification de l'existance du compte interne:
        $sql = "SELECT count(*) FROM ad_cpt WHERE id_ag = :id_agence AND id_cpte = :id_cpte";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte" => $id_cpte);
        $result = $this->getDbConn()->prepareFetchAll($sql, $param);

        if ($result === false) {
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $count = $result["count"];

        if ($count == 0) {
            return false; // le compte interne n'est pas defini en base, on ne fait rien
        }

        // recupere le id_prod l'etat compte et la devise du compte
        $sql = "SELECT id_prod, etat_cpte, devise FROM ad_cpt c WHERE c.id_ag = :id_agence AND c.id_cpte = ':id_cpte'";
        $param = array(":id_agence" => $this->getIdAgence(), ":id_cpte" => $id_cpte);
        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === false) {
//            $dbHandler->closeConnection (false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        $row = $result;
        $id_prod = $row['id_prod'];
        $etat_cpte = $row['etat_cpte'];
        $devise = $row['devise'];

        // If id_prod defined
        if(!empty($id_prod))
        {
            if($etat_cpte == 2) { // compte fermée, set num_cpte_comptable a NULL
                $num_cpte_comptable = NULL;
            }
            else if($id_prod == 1 || $id_prod > 5) // Depot à vue ou dépot / compte à terme :
            {
                // compte à l'état dormant (etat_cpte=4) qui sont déclassés sur le compte comptable de l'operation 170
                if($etat_cpte == 4) {
                    $sql = "SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 170 AND sens = 'c' AND id_ag = :id_agence;";
                    $param = array(":id_agence" => $this->getIdAgence());
                    $result = $this->getDbConn()->prepareFetchRow($sql, $param);
                    if ($result === false) {
                        $this->getDbConn()->rollBack();
                        signalErreur(__FILE__,__LINE__,__FUNCTION__);
                    }
                    $row = $result;
                    $num_cpte_comptable = $row['num_cpte'];
                }
                else { // Depot à vue ou dépot / compte à terme OUVERTS
                    $sql = "SELECT cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id = :id_produit AND id_ag = :id_agence AND devise = ':devise';";
                    $param = array(":id_produit" => $id_prod, ":id_agence" => $this->getIdAgence(), ":devise" => $devise);
                    $result = $this->getDbConn()->prepareFetchRow($sql, $param);
                    if ($result === false) {
                        $this->getDbConn()->rollBack();
                        signalErreur(__FILE__,__LINE__,__FUNCTION__);
                    }
                    $row = $result;
                    $num_cpte_comptable = $row['cpte_cpta_prod_ep'];
                }
            }
            elseif($id_prod == 3) // comptes de crédit
            {
                $sql = "SELECT etat_cpte.num_cpte_comptable
					FROM adsys_etat_credit_cptes etat_cpte, ad_dcr doss 
					WHERE doss.cre_id_cpte = :id_cpte
					AND etat_cpte.id_prod_cre = doss.id_prod 
					AND etat_cpte.id_etat_credit = doss.cre_etat
					AND etat_cpte.id_ag = :id_agence
					AND doss.id_ag = :id_agence_
		    		AND doss.cre_etat IS NOT NULL;"; // Les fonds sont deboursés

                $param = array(":id_cpte" => $id_cpte, ":id_agence" => $this->getIdAgence(), ":id_agence_" => $this->getIdAgence());
                $result = $this->getDbConn()->prepareFetchRow($sql, $param);
                if ($result) {
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__,__LINE__,__FUNCTION__);
                }
                $row = $result;

                if (empty($row)) {
                    return false;
                }

                $num_cpte_comptable = $row['num_cpte_comptable'];
            }
            elseif($id_prod == 4) // comptes de garantie
            {
                $sql = "SELECT prod.cpte_cpta_prod_cr_gar 
					FROM adsys_produit_credit prod, ad_dcr doss, ad_gar gar
					WHERE gar.gar_num_id_cpte_nantie = :id_cpte
					AND gar.type_gar = 1
					AND gar.id_doss = doss.id_doss
					AND doss.id_prod = prod.id
					AND prod.id_ag = :id_agence
					AND doss.id_ag = :id_agence_
					AND gar.id_ag = :id_agence__
					AND prod.cpte_cpta_prod_cr_gar IS NOT NULL 
					AND doss.cre_etat IS NOT NULL;"; // Les fonds sont deboursés

                $param = array(":id_cpte" => $id_cpte, ":id_agence" => $this->getIdAgence(), ":id_agence_" => $this->getIdAgence(), ":id_agence__" => $this->getIdAgence());
                $result = $this->getDbConn()->prepareRow($sql, $param);
                if ($result === false) {
                    $this->getDbConn()->rollBack();
                    signalErreur(__FILE__,__LINE__,__FUNCTION__);
                }
                $row = $result;

                if (empty($row)) {
                    return false; //le compte comptable n'est pas defini, on ne met pas a jour num_cpte_comptable
                }
                $num_cpte_comptable = $row['cpte_cpta_prod_cr_gar'];
            }
        }

        // Update the num_cpte_comptable column in ad_cpt

        if(empty($num_cpte_comptable)) {
            $sql = "UPDATE ad_cpt SET num_cpte_comptable = NULL WHERE id_cpte = $id_cpte;";
        }
        else {
            $sql = "UPDATE ad_cpt SET num_cpte_comptable = '$num_cpte_comptable' WHERE id_cpte = $id_cpte;";
        }

        $result = $this->getDbConn()->execute($sql);
        if ($result === false) {
//            $dbHandler->closeConnection (false);
            $this->getDbConn()->rollBack();
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        return new ErrorObj(NO_ERR, $num_cpte_comptable);
    }
}

?>