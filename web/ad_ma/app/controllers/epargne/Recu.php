<?php

/**
 * Description de la classe Recu
 *
 * @author danilo
 */
class Recu {

    /** Properties */
    private $_db_conn;
    private $_id_agence;

    public function __construct(&$dbc, $id_agence) {
        $this->setDbConn($dbc);
        $this->setIdAgence($id_agence);
    }

    public function __destruct() {
        
    }

    /**
     * Methods
     */
    public static function printRecuRetrait(&$dbc, $id_agence, $id_client, $nom_client, $InfoProduit, $infos, $mnt, $id_his, $code_recu = 'REC-REE', $id_mandat = NULL, $remarque = NULL, $communication = NULL, $id_pers_ext = NULL, $num_carte_ferlo = NULL, $nom_conj = "", $listTypesBilletArr = array(), $valeurBilletArr = array(), $global_langue_rapport, $total_billetArr = array(), $hasBilletage = false,$commission_od_deplace_retrait = 0)
    {
        global $global_id_profil;

        $isAffichageSolde=getParamAffichageSolde();
        
        $format_A5 = false;
        $document = create_xml_doc("recu", "recu_remote.dtd");
        
        // Element root
        $root = $document->root();
        $root->set_attribute("type", 8);
        
        // Init class
        $AgenceObj = new Agence($dbc);
        
        // recuperation des données de l'agence'
        $AG = $AgenceObj->getAgenceDatas($id_agence);
        
        if ($AG['imprimante_matricielle'] == 't') {
            $format_A5 = true;
        }
        
        // Destroy object
        unset($AgenceObj);
        
        // En-tête généraliste
        $ref = gen_header($root, $code_recu);
        
        Divers::setMonnaieCourante($dbc, $id_agence, $InfoProduit["devise"]);
        setMonnaieCourante($InfoProduit["devise"]);
        
        // appel a la fonction qui fait la conversion d'un montant en montant en lettre
        $mntEnLettre = getMontantEnLettre($mnt, $global_langue_rapport, $InfoProduit["devise"]);
        
        // Corps
        $body = $root->new_child("body", "");
        if ($nom_client != NULL)
            $body->new_child("nom_client", $nom_client);
        
        if (trim($AG['libel_ag']) != "")
            $body->new_child("nom_agence_remote", sprintf("%s (%s)", $AG['libel_ag'], $AG['id_ag']));
        
        if ($id_pers_ext != NULL || $id_mandat != NULL || $nom_conj != NULL) {
            if ($id_mandat != NULL) {
                // Init class
                $EpargneObj = new Epargne($dbc, $id_agence);
                
                $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
                
                // Destroy object
                unset($EpargneObj);
                
                $body->new_child("donneur_ordre", $MANDAT['denomination']);
            } elseif ($id_pers_ext != NULL) {
                
                // Init class
                $ClientObj = new Client($dbc, $id_agence);
                
                $PERS_EXT = $ClientObj->getPersonneExt(array(
                    "id_pers_ext" => $id_pers_ext
                ));
                $body->new_child("donneur_ordre", $PERS_EXT[0]['denomination']);
                
                // Destroy object
                unset($ClientObj);
            } elseif ($nom_conj) {
                $body->new_child("donneur_ordre", $nom_conj);
            }
        } else {
            $access_solde = get_profil_acces_solde($global_id_profil, $InfoProduit['id']);

            if ($isAffichageSolde == 't') {
                if ($access_solde) {
                    $body->new_child("solde", Divers::afficheMontant($infos['solde'], true));
                }
            }
        }
        if ($infos['num_complet_cpte'] != NULL)
            $body->new_child("num_cpte", $infos['num_complet_cpte']);
        if ($num_carte_ferlo != NULL)
            $body->new_child("num_carte_ferlo", $num_carte_ferlo);
        $body->new_child("montant", Divers::afficheMontant($mnt, true));
        $body->new_child("num_trans", sprintf("%09d", $id_his));
        if ($InfoProduit != NULL)
            $body->new_child("frais", Divers::afficheMontant($InfoProduit['frais_retrait_cpt'], true));
        if ($commission_od_deplace_retrait != NULL)
            $body->new_child("commission_od_deplace", Divers::afficheMontant($commission_od_deplace_retrait, true));
        if ($remarque != '')
            $body->new_child("remarque", $remarque);
        if ($communication != '')
            $body->new_child("communication", $communication);            

        // Billetage
        if ($hasBilletage) {
            $body->new_child("hasBilletage", true);
            
            for ($x = 0; $x < count($valeurBilletArr); $x ++) {
                if ($valeurBilletArr[$x] != 'XXXX') {
                    $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
                    $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
                    $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
                }
            }
        }        
        
        // montant en lettre
        if (! empty($mntEnLettre))
            $body->new_child("mntEnLettre", $mntEnLettre);
        
        $xml = $document->dump_mem(true);
        
        // Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        if ($format_A5) {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5_remote.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_remote.xslt');
        }
        
        // Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        get_show_pdf_html("Rcp-41", $fichier_pdf, false);
        
        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        
        $myErr = $HistoriqueObj->confirmeGenerationRecu($id_his, $ref);
        
        // Destroy object
        unset($HistoriqueObj);
        
        if ($myErr->errCode != NO_ERR) {
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        return true;
    }   

    
    public static function printRecuRetraitCheque(&$dbc, $id_agence, $id_client, $nom_client, $mnt, $InfoProduit, $infos, $id_his, $num_cheque, $date_cheque, $id_mandat = NULL, $beneficiaire = NULL,$commission_od_deplace_cheq_retrait = 0) {
        global $global_id_profil;
        $format_A5 = false;

        $document = create_xml_doc("recu", "recu_remote.dtd");

        // Init class
        $AgenceObj = new Agence($dbc);

        //recuperation des données de l'agence'
        $AG = $AgenceObj->getAgenceDatas($id_agence);
        if ($AG['imprimante_matricielle'] == 't') {
            $format_A5 = true;
        }

        // Destroy object
        unset($AgenceObj);

        $num = $infos['num_complet_cpte'] . " " . $infos["libel"];
        //Element root
        $root = $document->root();
        $root->set_attribute("type", 40);

        //En-tête généraliste
        gen_header($root, 'REC-REC');

        //Corps
        $body = $root->new_child("body", "");
        $body->new_child("nom_client", $nom_client);
        if (trim($AG['libel_ag']) != "")
            $body->new_child("nom_agence_remote", sprintf("%s (%s)", $AG['libel_ag'], $AG['id_ag']));

        $body->new_child("num_cpte", $num);
        $body->new_child("montant", Divers::afficheMontant($mnt, true));
        $body->new_child("num_trans", sprintf("%09d", $id_his));
        $body->new_child("frais", Divers::afficheMontant($InfoProduit['frais_retrait_cpt'], true));
        $body->new_child("commission_od_deplace", Divers::afficheMontant($commission_od_deplace_cheq_retrait, true));

        //Contôle sur l'affichage des soldes
        if ($id_mandat != NULL) {
            // Init class
            $EpargneObj = new Epargne($dbc, $id_agence);

            $MANDAT = $EpargneObj->getInfosMandat($id_mandat);

            // Destroy object
            unset($EpargneObj);

            if ($MANDAT['denomination'] != $nom_client) {
                $body->new_child("donneur_ordre", $MANDAT['denomination']);
            }
        }

        $info_cheque = $body->new_child("info_cheque", "");
        $info_cheque->new_child("num_cheque", $num_cheque);
// $info_cheque->new_child("banque_cheque", getLibel("adsys_banques", $id_bqe));
        $info_cheque->new_child("date_cheque", $date_cheque);
        if ($beneficiaire != NULL) {
            $info_cheque->new_child("beneficiaire", $beneficiaire);
        }

        $xml = $document->dump_mem(true);

        //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        if ($format_A5) {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5_remote.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_remote.xslt');
        }

        // Affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html(NULL, $fichier_pdf);
    }

    /**
     * Fonction imprimant le recçu aprés une opération de change
     * 
     * @param  int $id_his     Numéro de transaction
     * @param  $mnt_achat      Le montant de la devise achetée
     * @param  $devise_achat   La devise achetée
     * @param  $source_achat   Le compte où le montant en devise achetée sera prélevé
     * @param  $mnt_vente      Le montant de la devise vendue
     * @param  $devise_vente   La devise vendue
     * @param  $dest_vente    Le compte où le montant en devise vendue sera déposé
     * @param  $comm_nette     Le montant de la commission nette
     * @param  $taux           Le taux de change utilisé
     * @param  $reste          Le montant du reste
     * @param  $dest_reste     Le compte de destination du reste du change
     * @param  $affiche_reste  Champ indiquant si le reste doit etre affiché ou non
     */
    public static function printRecuChange(&$dbc, $id_agence, $id_his, $mnt_achat, $devise_achat, $source_achat, $mnt_vente, $devise_vente, $comm_nette, $taux, $reste, $dest_vente, $dest_reste = NULL, $affiche_reste = NULL, $listTypesBilletArr = array(), $valeurBilletArr = array(), $global_langue_rapport, $total_billetArr = array(), $hasBilletageChange = false) 
    {
        $DATA_RECU["achat"]["mnt"] = $mnt_achat;
        $DATA_RECU["achat"]["devise"] = $devise_achat;
        $DATA_RECU["achat"]["source_dest"] = $source_achat;
        $DATA_RECU["vente"]["mnt"] = $mnt_vente;
        $DATA_RECU["vente"]["devise"] = $devise_vente;
        $DATA_RECU["vente"]["source_dest"] = $dest_vente;
        $DATA_RECU["reste"] = $reste;  
        
        //Billetage:
        if($hasBilletageChange) {
            $DATA_RECU["listTypesBilletArr"]=$listTypesBilletArr;
            $DATA_RECU["valeurBilletArr"]=$valeurBilletArr;
            $DATA_RECU["total_billetArr"]=$total_billetArr;
            $DATA_RECU["global_langue_rapport"]=$global_langue_rapport;
            $DATA_RECU["hasBilletageChange"]=$hasBilletageChange;
        }
        
        if ($dest_reste == "1")
            $nom_dest_reste = _("Au guichet");
        else if ($dest_reste == "2")
            $nom_dest_reste = _("Sur le compte de base");
        else if ($dest_reste == "3")
            $nom_dest_reste = _("VIDE");

        $DATA_RECU["dest_reste"] = $nom_dest_reste;
        $DATA_RECU["taux"] = $taux;
        $DATA_RECU["commission"] = $comm_nette;
        $DATA_RECU["affiche_reste"] = $affiche_reste;

        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);

        //debug($DATA_RECU,"DATA");
        $RET = $DeviseObj->xmlChangeTaux($id_his, $DATA_RECU);
        $xml = $RET["xml"];
        $ref = $RET["ref"];

        // Destroy object
        unset($DeviseObj);

        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'change_taux.xslt', true);

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        get_show_pdf_html("Gen-6", $fichier_pdf);

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);

        $myErr = $HistoriqueObj->confirmeGenerationRecu($id_his, $ref);

        // Destroy object
        unset($HistoriqueObj);

        if ($myErr->errCode != NO_ERR) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
    }

    /**
     * 
     * @param unknown $id_client
     * @param unknown $nom_client
     * @param unknown $mnt Le montant du depot
     * @param unknown $InfoProduit
     * @param unknown $infos
     * @param int $id_his    Numéro de transaction
     * @param string $id_pers_ext
     * @param string $remarq
     * @param string $communic
     * @param number $mnt_frais_attente
     * @param string $id_mandat
     *
     * @return boolean
     */
    public static function printRecuDepot(&$dbc, $id_agence, $id_client, $nom_client, $mnt, $InfoProduit, $infos, $id_his, $id_pers_ext = NULL, $remarq = NULL, $communic = NULL, $mnt_frais_attente = 0, $id_mandat = NULL, $listTypesBilletArr = array(), $valeurBilletArr = array(), $global_langue_rapport, $total_billetArr = array(), $hasBilletage = false,$commission_od_depot=0)
    {
        global $global_id_profil;

        $isAffichageSolde=getParamAffichageSolde();

        Divers::setMonnaieCourante($dbc, $id_agence, $InfoProduit["devise"]);      

        //appel a la fonction qui fait la conversion d'un montant  en  montant en lettre
        $mntEnLettre = getMontantEnLettre($mnt,$global_langue_rapport ,$InfoProduit["devise"]);
                
        $format_A5 = false;
        $document = create_xml_doc("recu", "recu_remote.dtd");

        //Element root
        $root = $document->root();
        $root->set_attribute("type", 6);

        $num = $infos['num_complet_cpte'] . " " . $infos["libel"];

        // Init class
        $AgenceObj = new Agence($dbc);

        //recuperation des données de l'agence'
        $AG = $AgenceObj->getAgenceDatas($id_agence);

        if ($AG['imprimante_matricielle'] == 't') {
            $format_A5 = true;
        }
        //En-tête généraliste
        $ref = gen_header($root, 'REC-DEE');

        //Corps
        $body = $root->new_child("body", "");
        $body->new_child("nom_client", $nom_client);
        
        if (trim($AG['libel_ag']) != "")
            $body->new_child("nom_agence_remote", sprintf("%s (%s)", $AG['libel_ag'], $AG['id_ag']));
        
        if ($id_mandat != NULL || $id_pers_ext != NULL) {
            if ($id_mandat != NULL) {
                $EpargneObj = new Epargne($dbc, $id_agence);
                $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
                $body->new_child("donneur_ordre", $MANDAT['denomination']);
            } elseif ($id_pers_ext != NULL) {
                // Init class
                $ClientObj = new Client($dbc, $id_agence);
                $PERS_EXT = $ClientObj->getPersonneExt(array("id_pers_ext" => $id_pers_ext));
                $body->new_child("donneur_ordre", $PERS_EXT[0]['denomination']);
            }
        } elseif ($id_pers_ext == NULL) {
            $access_solde = get_profil_acces_solde($global_id_profil, $InfoProduit['id']);
            if ($isAffichageSolde == 't') {
            if($access_solde) {
                $montantAfficher = Divers::afficheMontant($infos['solde'], true);
                $body->new_child("solde", $montantAfficher);
            }
            }
        }
        $body->new_child("num_cpte", $num);

        $montantAfficher = Divers::afficheMontant($mnt, true);
        $body->new_child("montant", $montantAfficher);
        $body->new_child("num_trans", sprintf("%09d", $id_his));

        $montantAfficher = Divers::afficheMontant($InfoProduit['frais_depot_cpt'], true);
        $body->new_child("frais", $montantAfficher);
        if ($mnt_frais_attente > 0) {
            $montantAfficher = Divers::afficheMontant($mnt_frais_attente, true);
            $body->new_child("frais_attente", $montantAfficher);
        }
        if ($commission_od_depot > 0){
            $montantAfficher = Divers::afficheMontant($commission_od_depot, true);
            $body->new_child("commission_od_deplace", $montantAfficher);
        }
        if ($remarq != '')
            $body->new_child("remarque", $remarq);
        if ($communic != '')
            $body->new_child("communication", $communic);     
        
        // Billetage
        if ($hasBilletage) {
            $body->new_child("hasBilletage", true);
        
            for ($x = 0; $x < count($valeurBilletArr); $x ++) {
                if ($valeurBilletArr[$x] != 'XXXX') {
                    $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
                    $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
                    $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
                }
            }
        }
        
        // montant en lettre
        if (! empty($mntEnLettre))
            $body->new_child("mntEnLettre", $mntEnLettre);        
        
        $xml = $document->dump_mem(true);

        //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        if ($format_A5) {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5_remote.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_remote.xslt');
        }
        // Affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html(NULL, $fichier_pdf);

        $HistoriqueObj = new Historique($dbc, $id_agence);
        $myErr = $HistoriqueObj->confirmeGenerationRecu($id_his, $ref);

        if ($myErr->errCode != NO_ERR)
            signalErreur(__FILE__, __LINE__, __FUNCTION__);

        return true;
    }

    public static function printRecuDepotAgent(&$dbc, $id_agence, $id_client, $nom_client, $mnt, $InfoProduit, $infos, $id_his, $id_pers_ext = NULL, $remarq = NULL, $communic = NULL, $mnt_frais_attente = 0, $id_mandat = NULL, $listTypesBilletArr = array(), $valeurBilletArr = array(), $global_langue_rapport, $total_billetArr = array(), $hasBilletage = false,$commission_od_depot=0, $commission_agent = 0, $commission_institution = 0)
    {
        global $global_id_profil;

        $commission_transaction = $commission_agent + $commission_institution;

        $isAffichageSolde=getParamAffichageSolde();

        Divers::setMonnaieCourante($dbc, $id_agence, $InfoProduit["devise"]);

        //appel a la fonction qui fait la conversion d'un montant  en  montant en lettre
        $mntEnLettre = getMontantEnLettre($mnt,$global_langue_rapport ,$InfoProduit["devise"]);

        $format_A5 = false;
        $document = create_xml_doc("recu_agent", "recu_remote_agent.dtd");

        //Element root
        $root = $document->root();
        $root->set_attribute("type", 6);

        $num = $infos['num_complet_cpte'] . " " . $infos["libel"];

        // Init class
        $AgenceObj = new Agence($dbc);

        //recuperation des données de l'agence'
        $AG = $AgenceObj->getAgenceDatas($id_agence);

        if ($AG['imprimante_matricielle'] == 't') {
            $format_A5 = true;
        }
        //En-tête généraliste
        $ref = gen_header($root, 'REC-DEE');

        //Corps
        $body = $root->new_child("body", "");
        $body->new_child("nom_client", $nom_client);

        if (trim($AG['libel_ag']) != "")
            $body->new_child("nom_agence_remote", sprintf("%s (%s)", $AG['libel_ag'], $AG['id_ag']));

        if ($id_mandat != NULL || $id_pers_ext != NULL) {
            if ($id_mandat != NULL) {
                $EpargneObj = new Epargne($dbc, $id_agence);
                $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
                $body->new_child("donneur_ordre", $MANDAT['denomination']);
            } elseif ($id_pers_ext != NULL) {
                // Init class
                $ClientObj = new Client($dbc, $id_agence);
                $PERS_EXT = $ClientObj->getPersonneExt(array("id_pers_ext" => $id_pers_ext));
                $body->new_child("donneur_ordre", $PERS_EXT[0]['denomination']);
            }
        } elseif ($id_pers_ext == NULL) {
            $access_solde = get_profil_acces_solde($global_id_profil, $InfoProduit['id']);
            if ($isAffichageSolde == 't') {
                if($access_solde) {
                    $montantAfficher = Divers::afficheMontant($infos['solde'], true);
                    $body->new_child("solde", $montantAfficher);
                }
            }
        }
        $body->new_child("num_cpte", $num);

        $montantAfficher = Divers::afficheMontant($mnt, true);
        $body->new_child("montant", $montantAfficher);
        $body->new_child("num_trans", sprintf("%09d", $id_his));

        $montantAfficher = Divers::afficheMontant($InfoProduit['frais_depot_cpt'], true);
        $body->new_child("frais", $montantAfficher);
        if ($mnt_frais_attente > 0) {
            $montantAfficher = Divers::afficheMontant($mnt_frais_attente, true);
            $body->new_child("frais_attente", $montantAfficher);
        }
        if ($commission_od_depot > 0){
            $montantAfficher = Divers::afficheMontant($commission_od_depot, true);
            $body->new_child("commission_od_deplace", $montantAfficher);
        }
        if ($commission_transaction > 0){
            $montantAfficher = Divers::afficheMontant($commission_transaction, true);
            if($commission_institution != 0){
                $body->new_child("commission_transaction", $montantAfficher);
            }
        }
        if ($remarq != '')
            $body->new_child("remarque", $remarq);
        if ($communic != '')
            $body->new_child("communication", $communic);

        // Billetage
        if ($hasBilletage) {
            $body->new_child("hasBilletage", true);

            for ($x = 0; $x < count($valeurBilletArr); $x ++) {
                if ($valeurBilletArr[$x] != 'XXXX') {
                    $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
                    $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
                    $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
                }
            }
        }

        // montant en lettre
        if (! empty($mntEnLettre))
            $body->new_child("mntEnLettre", $mntEnLettre);

        $xml = $document->dump_mem(true);
        //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        if ($format_A5) {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5_remote_agent.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_remote_agent.xslt');
        }
        // Affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html(NULL, $fichier_pdf);

        $HistoriqueObj = new Historique($dbc, $id_agence);
        $myErr = $HistoriqueObj->confirmeGenerationRecu($id_his, $ref);

        if ($myErr->errCode != NO_ERR)
            signalErreur(__FILE__, __LINE__, __FUNCTION__);

        return true;
    }
      public static function printRecuRetraitAgent(&$dbc, $id_agence, $id_client, $nom_client, $InfoProduit, $infos, $mnt, $id_his, $code_recu = 'REC-RRA', $id_mandat = NULL, $remarque = NULL, $communication = NULL, $id_pers_ext = NULL, $num_carte_ferlo = NULL, $nom_conj = "", $listTypesBilletArr = array(), $valeurBilletArr = array(), $global_langue_rapport, $total_billetArr = array(), $hasBilletage = false,$commission_od_deplace_retrait = 0,$commission_agent=0, $commission_institution=0)
    {
        global $global_id_profil;

        $commission_transaction = $commission_agent + $commission_institution;

        $isAffichageSolde=getParamAffichageSolde();

        $format_A5 = false;
        $document = create_xml_doc("recu_agent", "recu_remote_agent.dtd");

        // Element root
        $root = $document->root();
        $root->set_attribute("type", 8);

        // Init class
        $AgenceObj = new Agence($dbc);

        // recuperation des données de l'agence'
        $AG = $AgenceObj->getAgenceDatas($id_agence);

        if ($AG['imprimante_matricielle'] == 't') {
            $format_A5 = true;
        }

        // Destroy object
        unset($AgenceObj);

        // En-tête généraliste
        $ref = gen_header($root, $code_recu);

        Divers::setMonnaieCourante($dbc, $id_agence, $InfoProduit["devise"]);
        setMonnaieCourante($InfoProduit["devise"]);

        // appel a la fonction qui fait la conversion d'un montant en montant en lettre
        $mntEnLettre = getMontantEnLettre($mnt, $global_langue_rapport, $InfoProduit["devise"]);

        // Corps
        $body = $root->new_child("body", "");
        if ($nom_client != NULL)
            $body->new_child("nom_client", $nom_client);

        if (trim($AG['libel_ag']) != "")
            $body->new_child("nom_agence_remote", sprintf("%s (%s)", $AG['libel_ag'], $AG['id_ag']));

        if ($id_pers_ext != NULL || $id_mandat != NULL || $nom_conj != NULL) {
            if ($id_mandat != NULL) {
                // Init class
                $EpargneObj = new Epargne($dbc, $id_agence);

                $MANDAT = $EpargneObj->getInfosMandat($id_mandat);

                // Destroy object
                unset($EpargneObj);

                $body->new_child("donneur_ordre", $MANDAT['denomination']);
            } elseif ($id_pers_ext != NULL) {

                // Init class
                $ClientObj = new Client($dbc, $id_agence);

                $PERS_EXT = $ClientObj->getPersonneExt(array(
                  "id_pers_ext" => $id_pers_ext
                ));
                $body->new_child("donneur_ordre", $PERS_EXT[0]['denomination']);

                // Destroy object
                unset($ClientObj);
            } elseif ($nom_conj) {
                $body->new_child("donneur_ordre", $nom_conj);
            }
        } else {
            $access_solde = get_profil_acces_solde($global_id_profil, $InfoProduit['id']);

            if ($isAffichageSolde == 't') {
                if ($access_solde) {
                    $body->new_child("solde", Divers::afficheMontant($infos['solde'], true));
                }
            }
        }
        if ($infos['num_complet_cpte'] != NULL)
            $body->new_child("num_cpte", $infos['num_complet_cpte']);
        if ($num_carte_ferlo != NULL)
            $body->new_child("num_carte_ferlo", $num_carte_ferlo);
        $body->new_child("montant", Divers::afficheMontant($mnt, true));
        $body->new_child("num_trans", sprintf("%09d", $id_his));
        if ($InfoProduit != NULL)
            $body->new_child("frais", Divers::afficheMontant($InfoProduit['frais_retrait_cpt'], true));
        if ($commission_od_deplace_retrait != NULL)
            $body->new_child("commission_od_deplace", Divers::afficheMontant($commission_od_deplace_retrait, true));
        if ($commission_transaction != 0) {
            if($commission_institution !=0) {
                $body->new_child("commission_transaction", Divers::afficheMontant($commission_transaction, true));
            }
        }
        if ($remarque != '')
            $body->new_child("remarque", $remarque);
        if ($communication != '')
            $body->new_child("communication", $communication);

        // Billetage
        if ($hasBilletage) {
            $body->new_child("hasBilletage", true);

            for ($x = 0; $x < count($valeurBilletArr); $x ++) {
                if ($valeurBilletArr[$x] != 'XXXX') {
                    $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
                    $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
                    $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
                }
            }
        }

        // montant en lettre
        if (! empty($mntEnLettre))
            $body->new_child("mntEnLettre", $mntEnLettre);

        $xml = $document->dump_mem(true);

        // Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        if ($format_A5) {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recuA5_remote_agent.xslt');
        } else {
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_remote_agent.xslt');
        }

        // Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        get_show_pdf_html("Rcp-41", $fichier_pdf, false);

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);

        $myErr = $HistoriqueObj->confirmeGenerationRecu($id_his, $ref);

        // Destroy object
        unset($HistoriqueObj);

        if ($myErr->errCode != NO_ERR) {
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return true;
    }

    /** Getters & Setters */
    public function getDbConn() {
        return $this->_db_conn;
    }

    public function setDbConn(&$value) {
        $this->_db_conn = $value;
    }

    public function getIdAgence() {
        return $this->_id_agence;
    }

    public function setIdAgence($value) {
        $this->_id_agence = $value;
    }

}

?>
