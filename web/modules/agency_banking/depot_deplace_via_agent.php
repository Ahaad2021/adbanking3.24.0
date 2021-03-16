<?php
    require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
    require_once 'ad_ma/app/models/AgenceRemote.php';
    require_once 'ad_ma/app/models/Agence.php';
    require_once 'ad_ma/app/models/Audit.php';
    require_once 'ad_ma/app/models/Client.php';
    require_once 'ad_ma/app/models/Compta.php';
    require_once 'ad_ma/app/models/Compte.php';
    require_once 'ad_ma/app/models/Credit.php';
    require_once 'ad_ma/app/models/Devise.php';
    require_once 'ad_ma/app/models/Divers.php';
    require_once 'ad_ma/app/models/Epargne.php';
    require_once 'ad_ma/app/models/Guichet.php';
    require_once 'ad_ma/app/models/Historique.php';
    require_once 'ad_ma/app/models/Parametrage.php';
    require_once 'ad_ma/app/models/TireurBenef.php';

    require_once 'ad_ma/app/controllers/epargne/Depot.php';
    require_once 'ad_ma/app/controllers/epargne/Recu.php';

    require_once 'lib/dbProcedures/billetage.php';
    require_once "lib/html/HTML_menu_gen.php";
    require_once 'lib/html/FILL_HTML_GEN2.php';
    require_once 'modules/rapports/xml_devise.php';
    require_once 'modules/rapports/xslt.php';


    /*{{{ Dva-1 : Choix du compte */
    if ($global_nom_ecran == "Dda-1") {
        global $global_remote_id_agence, $global_remote_id_client;

        // Clear data session variables
        unset(
            $SESSION_VARS['NumCpte'],
            $SESSION_VARS['type_depot'],
            $SESSION_VARS['id_pers_ext'],
            $SESSION_VARS['id_mandat'],
            $SESSION_VARS['mandat'],
            $SESSION_VARS['denomination'],
            $SESSION_VARS['mnt'],
            $SESSION_VARS['mnt_cv'],
            $SESSION_VARS['num_chq'],
            $SESSION_VARS['date_chq'],
            $SESSION_VARS['nom_ben'],
            $SESSION_VARS['id_ben'],
            $SESSION_VARS['id_correspondant'],
            $SESSION_VARS['communication'],
            $SESSION_VARS['remarque'],
            $SESSION_VARS["frais_depot_cpt"],
            $SESSION_VARS["banque"],
            $SESSION_VARS["frais_depot_cpt"],
            $SESSION_VARS['envoi'],
            $SESSION_VARS['tib'],
            $SESSION_VARS['type_recherche'],
            $SESSION_VARS['field_name'],
            $SESSION_VARS['field_id'],
            $SESSION_VARS['gpe']
        );

        // Store local monnaie courante
        $global_monnaie_courante_tmp = $global_monnaie_courante;
        $global_monnaie_courante = $global_remote_monnaie_courante;

        $html = new HTML_GEN2(_("Dépôt en déplacé sur un compte : choix du compte"));

        // Begin remote transaction
        $pdo_conn->beginTransaction();

        // Init class
        $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

        //Affichage de tous les comptes du client
        $TempListeComptes = $EpargneObj->getComptesEpargne($global_remote_id_client);

        //Retirer de la liste les comptes à dépôt unique
        $choix = array();
        if (isset($TempListeComptes)) {
            $ListeComptes = $EpargneObj->getComptesDepotPossible($TempListeComptes);

            if (isset($ListeComptes))
                foreach($ListeComptes as $key=>$value)
                    $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];//index par id_cpte pour la listbox
        }

        $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
        //Affichage des valeurs précédemment saisies
        $html->setFieldProperties("NumCpte", FIELDP_DEFAULT, $SESSION_VARS['NumCpte']);
        $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
        $html->setFieldProperties("NumCpte", FIELDP_IS_REQUIRED, true);

        //Ajout des tables
        $html->addTable("ad_cpt", OPER_INCLUDE, array("etat_cpte"));

        // Recupere la liste des devises
        $ListeDevises = Divers::getListDevises($pdo_conn, $global_remote_id_agence);

        $choix_devises = array();
        if (is_array($ListeDevises) && count($ListeDevises) > 0) {
            foreach ($ListeDevises as $key => $value) {
                $choix_devises[$key] = trim($value["code_devise"]);
            }
        }
        $html->addField("devise", _("Devise du compte"), TYPC_LSB);
        $html->setFieldProperties("devise", FIELDP_ADD_CHOICES, $choix_devises);
        $html->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, true);

        // Libelle
        $html->addField("libel", _("Libellé du produit d'épargne"), TYPC_TXT);
        $html->setFieldProperties("libel", FIELDP_IS_REQUIRED, false);


        //Code HTML pour la présentation à l'écran
        $xtra1 = "<b>"._("Choix du compte")."</b>";
        $html->addHTMLExtraCode ("htm1", $xtra1);
        $html->setHTMLExtraCodeProperties ("htm1",HTMP_IN_TABLE, true);
        $xtra2 = "<b>"._("Choix du type de dépôt")."</b>";
        $html->addHTMLExtraCode ("htm2", $xtra2);
        $html->setHTMLExtraCodeProperties ("htm2",HTMP_IN_TABLE, true);

        //Transformer les champs en labels non modifiables
        $fieldslabel = array("etat_cpte", "libel", "devise");

        foreach($fieldslabel as $value) {
            $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
            $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
        }

        $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

        //En fonction du choix du compte, afficher les infos avec le onChange javascript
        $codejs = "\n\nfunction getInfoCompte() {";
        if (isset($ListeComptes)) {
            foreach($ListeComptes as $key=>$value) {
                $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value == $key)\n\t";
                $codejs .= "{\n\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value = " . _($value["etat_cpte"]) . ";";
                $codejs .= "\n\t\tdocument.ADForm.libel.value = \"" . $value["libel"] . "\";";
                $codejs .= "\n\t\tdocument.ADForm.HTML_GEN_LSB_devise.value = '" . $value["devise"] . "';";
                $codejs .= "};\n";
            }
            $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value =='0') {";
            $codejs .= "\n\t\tdocument.ADForm.libel.value='';";
            $codejs .= "\n\t\tdocument.ADForm.HTML_GEN_LSB_devise.value='0';";
            $codejs .= "\n\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value='0';";
            $codejs .= "\n\t}\n";
        }
        $codejs .= "}\ngetInfoCompte();";

        $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte();"));
        $html->addJS(JSP_FORM, "JS3", $codejs);

//        $html->addField("type_depot", _("Type de dépôt"), TYPC_LSB);
//        $choix2 = array();
//        $choix2[1]=_('Dépôt en espèce');
//        $choix2[2]=_('Dépôt par chèque');
//        $choix2[3]=_('Dépôt par ordre de payement');
        //$choix2[5]=_('Dépôt par Travelers Cheque');

//        foreach($choix2 as $key=>$value) {
//            //Type de dépôt autorisé : 1:espèce, 2:chèque, 3:ordre de paiement, 5:Travelers cheque
//            if ($key!=1 and $key!=2 and $key!=3 and $key!=5) {
//                unset($choix2[$key]);
//            }
//        }
//        $html->setFieldProperties("type_depot", FIELDP_ADD_CHOICES, $choix2);
//        $html->setFieldProperties("type_depot", FIELDP_IS_REQUIRED, true);

        //Affichage des valeurs précédemment saisies
//        $html->setFieldProperties("type_depot",FIELDP_DEFAULT,$SESSION_VARS['type_depot']);
        $html->addField("type_depot", _("Type de dépôt"), TYPC_TXT);
        $html->setFieldProperties("type_depot", FIELDP_DEFAULT, 'Dépôt en espèce');
        $html->setFieldProperties("type_depot", FIELDP_IS_LABEL, true);
        $html->addHiddenType("type_depot_id", 1);

        //Ordonner les champs pour l'affichage
        $html->setOrder(NULL, array("htm1","NumCpte","libel", "devise", "etat_cpte", "htm2", "type_depot"));

        $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
        $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dda-2');
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'God-3');

        $html->buildHTML();
        echo $html->getHTML();

        // Commit transaction
        $pdo_conn->commit();

        // Restore local monnaie courante
        $global_monnaie_courante = $global_monnaie_courante_tmp;
    }
    else if ($global_nom_ecran == "Dda-2") {
        global $global_remote_id_agence, $global_remote_id_client, $global_multidevise, $global_id_profil;

        // Store local monnaie courante
        $global_monnaie_courante_tmp = $global_monnaie_courante;
        $global_monnaie_courante = $global_remote_monnaie_courante;
        $SESSION_VARS['type_depot'] = $type_depot_id;
        $ad_log =  getDatasLogin();
        $plafond_depot = empty($ad_log['plafond_depot'])?0:$ad_log['plafond_depot'];
        $choix_depot_comm = getParamCommissionInsti();
        $ag_commission = getCommissionDepotRetrait(2);
        // Begin remote transaction
        $pdo_conn->beginTransaction();

        if (isset($NumCpte)) $SESSION_VARS["NumCpte"] = $NumCpte;
        if (isset($type_depot)) $SESSION_VARS["type_depot"] = $type_depot;
        if (isset($SESSION_VARS['id_pers_ext'])) unset ($SESSION_VARS['id_pers_ext']);

        //Afficher la liste des comptes du client puis le montant à déposer et ne pas oublier les frais d'opérations sur compte éventuels
        $html = new HTML_GEN2(_("Dépôt en déplacé sur un compte"));
        $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
        $html->addHTMLExtraCode("html_js",$html_js);

        // Init class
        $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
        $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);
        $DeviseObj = new Devise($pdo_conn, $global_remote_id_agence);
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);
        $ParametrageObj = new Parametrage($pdo_conn, $global_remote_id_agence);
        $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);

        //Informations compte
        $infoCpte = $CompteObj->getAccountDatas($SESSION_VARS['NumCpte']);

        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $infoCpte["devise"]);

        $MANDATS = $EpargneObj->getListeMandatairesActifs($SESSION_VARS['NumCpte'], true);

        if ($MANDATS != NULL) {
            foreach ($MANDATS as $key => $value) {
                $MANDATS_LSB[$key] = $value['libelle'];
                if ($key == 'CONJ') {
                    $JS_open .= "if (document.ADForm.HTML_GEN_LSB_mandat.value == '$key')
                {
                OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/info_mandat_distant.php?m_agc=".$_REQUEST['m_agc']."&id_cpte=" . $SESSION_VARS['NumCpte'] . "');
                        return false;
            }";
                } else {
                    $JS_open .=
                        "if (document.ADForm.HTML_GEN_LSB_mandat.value == $key)
                {
                OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/info_mandat_distant.php?m_agc=".$_REQUEST['m_agc']."&id_mandat=$key');
                return false;
            }";
                }
            }
        }
        $JS_change =
            "if (document.ADForm.HTML_GEN_LSB_mandat.value != 'EXT')
    {
            document.ADForm.denomination.value = '';
            document.ADForm.id_pers_ext.value = '';
    }";

        $clientName = $ClientObj->getClientName($global_remote_id_client);

        $html->addField("mandat", _("Donneur d'ordre"), TYPC_LSB);
        $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("0" => _("Titulaire (".$clientName.")")));

        if ($MANDATS_LSB != NULL) {
            $MANDATS_LSB = array_flip($MANDATS_LSB); // array(valeur = >cle) au lieu de array(cle => valeur)
            unset($MANDATS_LSB[$clientName]); //on supprime le nom du titulaire dans la liste déroulante
            $MANDATS_LSB = array_flip($MANDATS_LSB); // on remet le array(cle => valeur)
            $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, $MANDATS_LSB);
        }

        $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_AUCUN, false);
        $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_TOUS, false);
        $html->setFieldProperties("mandat", FIELDP_JS_EVENT, array("onchange" => $JS_change));
        $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));
        $html->setFieldProperties("mandat", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);
        $html->addLink("mandat", "afficher", _("Afficher"), "#");
        $html->setLinkProperties("afficher", LINKP_JS_EVENT, array("onclick" => $JS_open));

        $SESSION_VARS['mandat'] = $MANDATS_LSB;

        $JS_rech =
            "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT')
    {       
        OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/gest_pers_ext_distant.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');
        return false;
    }";

        $include = array("denomination");
        $html->addTable("ad_pers_ext", OPER_INCLUDE, $include);
        $html->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("denomination", FIELDP_IS_REQUIRED, false);
        $html->setFieldProperties("denomination", FIELDP_DEFAULT, $SESSION_VARS['denomination']);
        $html->addLink("denomination", "rech_pers_ext", _("Rechercher"), "#");

        $html->setLinkProperties("rech_pers_ext", LINKP_JS_EVENT, array("onclick" => $JS_rech));

        $html->addHiddenType("id_pers_ext", $SESSION_VARS['id_pers_ext']);

        $JS_check =
            "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT' && document.ADForm.id_pers_ext.value == '')
    {
            msg += ' - "._("Vous devez choisir une personne non cliente")."\\n';
                    ADFormValid=false;
    }";

        $html->addJS(JSP_BEGIN_CHECK, "JS1", $JS_check);

        $html->addHTMLExtraCode("mandat_sep", "<br/>");

        $xtra1 = "<b>"._("Compte sélectionné")."</b>";
        $html->addHTMLExtraCode ("htm1", $xtra1);
        $html->setHTMLExtraCodeProperties ("htm1", HTMP_IN_TABLE, true);

        $html->addField("NumCpte", _("Numéro de compte"), TYPC_TXT);
        $html->setFieldProperties("NumCpte", FIELDP_DEFAULT, $infoCpte["num_complet_cpte"]." ".$infoCpte["intitule_compte"]);
        $html->setFieldProperties("NumCpte", FIELDP_IS_LABEL, true);

        $html->addField("libel", _("Libellé du produit d'épargne"), TYPC_TXT);
        $html->setFieldProperties("libel", FIELDP_DEFAULT, $infoCpte["libel"]);
        $html->setFieldProperties("libel", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("libel", FIELDP_IS_REQUIRED, false);

        $access_solde = get_profil_acces_solde($global_id_profil, $infoCpte["id_prod"]);

        if($access_solde) {
            $html->addField("solde", _("Solde"), TYPC_MNT);
            $html->setFieldProperties("solde", FIELDP_DEFAULT, $infoCpte["solde"]);
            $html->setFieldProperties("solde", FIELDP_IS_LABEL, true);
            $html->setFieldProperties("solde", FIELDP_IS_REQUIRED, true);
        }

        $html->addField("mnt_max", _("Montant maximum (0 si aucun)"), TYPC_MNT);
        $html->setFieldProperties("mnt_max", FIELDP_DEFAULT, $infoCpte["mnt_max"]);
        $html->setFieldProperties("mnt_max", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("mnt_max", FIELDP_IS_REQUIRED, false);

        $etat_cpte = $adsys["adsys_etat_cpt_epargne"][$infoCpte["etat_cpte"]];
        $html->addField("etat_cpte", _("Etat du compte"), TYPC_TXT);
        $html->setFieldProperties("etat_cpte", FIELDP_DEFAULT, $etat_cpte);
        $html->setFieldProperties("etat_cpte", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("etat_cpte", FIELDP_IS_REQUIRED, true);

        $xtra2 = "<b>"._("Frais / Montant du dépôt")."</b>";
        $html->addHTMLExtraCode ("htm2", $xtra2);
        $html->setHTMLExtraCodeProperties ("htm2", HTMP_IN_TABLE, true);

        $html->addField("frais_depot_cpt", _("Frais de dépôt"), TYPC_MNT);
        $html->setFieldProperties("frais_depot_cpt", FIELDP_DEFAULT, $infoCpte["frais_depot_cpt"]);
        $html->setFieldProperties("frais_depot_cpt", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("frais_depot_cpt", FIELDP_IS_REQUIRED, false);

        //Champs pour le dépôt au guichet
    $mntjs = "
    var dataset = ".json_encode($ag_commission).";    
    function setCommision(){
        var plafond_depot = parseInt(".$plafond_depot."); 
        var element_val = parseInt($('[name=mnt]').val().replace(/\s/g, ''));
        
        if(dataset == null){
            $('[name=mnt]').val('');
            $('[name=commission_agent]').val(0);
            $('[name=commission_institution]').val(0);
            alert('Les commissions n\'ont pas été paramétré');
        }else if(element_val > plafond_depot){
            alert('Le montant saisi est supérieur au plafond de dépot');
            $('[name=commission_agent]').val(0);
            $('[name=commission_institution]').val(0);
            $('[name=mnt]').val('');
        }else{
            for(var index = 0; index < dataset.length; index++){
                var element_val = parseInt($('[name=mnt]').val().replace(/\s/g, ''));
                var commission_choice = ".$choix_depot_comm["choix_depot_comm"].";
                var calculator_agent = (dataset[index].comm_agent_mnt == null)?((dataset[index].comm_agent_prc/100)*element_val):dataset[index].comm_agent_mnt;
                var calculator_inst = (dataset[index].comm_inst_mnt == null)?((dataset[index].comm_inst_prc/100)*element_val):dataset[index].comm_inst_mnt;
               
                
                if((element_val >= dataset[index].mnt_min) && (element_val <= dataset[index].mnt_max)){
                    if(commission_choice == '2'){
                        $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
                        $('[name=commission_institution]').val(Math.floor(parseInt(calculator_inst)));
                    }else{
                        $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
                        $('[name=commission_institution]').val(0);
                    }
                    break;
                }
                if((dataset.length - 1) == index){
                      alert('Le montant déposé est supérieure au palier maximal');
                      $('[name=mnt]').val('');
                      $('[name=commission_agent]').val('');
                      $('[name=commission_institution]').val('');
//                    if(commission_choice == '2'){
//                        $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
//                        $('[name=commission_institution]').val(Math.floor(parseInt(calculator_inst)));
//                    }else{
//                        $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
//                        $('[name=commission_institution]').val(0);
//                    }
                }
            }
        }
    }
        
        $('[name=mnt]').keyup(function(e){
            if(e.keyCode == 8 || ($('[name=mnt]').val() == '')){
                $('[name=commission_agent]').val('');
                $('[name=commission_institution]').val('');
            }
        });
        
        $('[name=commission_agent]').attr('readonly', true);
        $('[name=commission_institution]').attr('readonly', true);
        
    ";

        $html->addField("mnt",_("Montant déposé"),TYPC_MNT);
        $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS['mnt']);
        $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("mnt", FIELDP_JS_EVENT, array("onChange"=>"setCommision();"));
        $html->addJS(JSP_FORM, "JS8", $mntjs);

            $xtra2 = "<b>" . _("Commission / Montant du dépôt") . "</b>";
            $html->addHTMLExtraCode("htm3", $xtra2);
            $html->setHTMLExtraCodeProperties("htm3", HTMP_IN_TABLE, true);

            $html->addField("commission_agent", _("Commission Agent"), TYPC_MNT);
            $html->setFieldProperties("commission_agent", FIELDP_IS_LABEL, FALSE);

            $html->addField("commission_institution", _("Commission pour l'institution"), TYPC_MNT);
            $html->setFieldProperties("commission_institution", FIELDP_IS_LABEL, FALSE);

        /*
         * @todo : multi-devise
         *
        if($global_multidevise)
        {
            $html->addField("mnt_cv", _("Montant guichet/chèque"), TYPC_DVR);
            $html->setFieldProperties("mnt_cv", FIELDP_IS_REQUIRED, true);
            $html->linkFieldsChange("mnt_cv", "mnt", "achat", 1, true);
            $html->add_js_enable_disable("mnt_cv");

            if (is_array($SESSION_VARS['mnt_cv'])) {
                if ($SESSION_VARS['mnt_cv']['devise'] == $infoCpte['devise']) {
                    $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $SESSION_VARS['mnt']);
                    $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $SESSION_VARS['mnt_cv']['devise']);
                    $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, true);
                } else {
                    $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $SESSION_VARS['mnt_cv']['cv']);
                    $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $SESSION_VARS['mnt_cv']['devise']);
                    $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, false);
                    $html->addJS(JSP_FORM, "js_mnt_cv_taux", "document.ADForm.HTML_GEN_dvr_mnt_cv_taux.value = '".$SESSION_VARS['mnt_cv']['taux']."';\n");
                    $html->addJS(JSP_FORM, "js_mnt_cv_comm_nette", "document.ADForm.HTML_GEN_dvr_mnt_cv_comm_nette.value = '".$SESSION_VARS['mnt_cv']['comm_nette']."';\n");
                    $html->addJS(JSP_FORM, "js_mnt_cv_dest_reste", "document.ADForm.HTML_GEN_dvr_mnt_cv_dest_reste.value = '".$SESSION_VARS['mnt_cv']['dest_reste']."';\n");
                }
            }
        }
        */

        $xtra4 = "<b>"._("Communication / remarque")."</b>";
        $html->addHTMLExtraCode ("htm4", $xtra4);
        $html->setHTMLExtraCodeProperties ("htm4", HTMP_IN_TABLE, true);

        //Communication
        $html->addField("communication", _("Communication"), TYPC_TXT);
        $html->setFieldProperties("communication",FIELDP_DEFAULT,$SESSION_VARS['communication']);

        //Remarque
        $html->addField("remarque", _("Remarque"), TYPC_ARE);
        $html->setFieldProperties("remarque",FIELDP_DEFAULT,$SESSION_VARS['remarque']);

        array_push($ordre, "htm4", "frais_depot_cpt", "communication", "remarque");

        //Ordonner les champs pour l'affichage
        $html->setOrder(NULL, $ordre);

        //Code javascript de vérification au moment de la validation
        $JS_check =
            "if ((recupMontant(document.ADForm.mnt_max.value) > 0) && (recupMontant(document.ADForm.mnt.value) > recupMontant(document.ADForm.mnt_max.value)))
    {
            msg += ' - "._("Le montant est supérieur au montant maximum")."\\n';
                    ADFormValid=false;
}
                    if ((recupMontant(document.ADForm.mnt_max.value) > 0 ) && ((recupMontant(document.ADForm.mnt.value) + ".$infoCpte["solde"].") > recupMontant(document.ADForm.mnt_max.value)))
                    {
                            msg += ' - "._("Le montant à déposer rendra le solde supérieur au montant maximum autorisé")."\\n';
                                    ADFormValid=false;
}";

        $html->addJS(JSP_BEGIN_CHECK, "JS3", $JS_check);
        $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
        $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
        $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
        $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dda-3');
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
        $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Dda-1');

        $html->buildHTML();

        echo $html->getHTML();

        // Commit transaction
        $pdo_conn->commit();

        // Restore local monnaie courante
        $global_monnaie_courante = $global_monnaie_courante_tmp;
    }
    else if($global_nom_ecran == 'Dda-3'){

        //global $global_remote_id_agence, $global_remote_id_client, $global_mouvements;

        global $global_remote_id_agence, $global_id_guichet, $global_monnaie_courante, $global_remote_monnaie_courante;

        // Begin remote transaction
        $pdo_conn->beginTransaction();

        // Store local monnaie courante
        $global_monnaie_courante_tmp = $global_monnaie_courante;
        $global_monnaie_courante = $global_remote_monnaie_courante;

        // Init class
        $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);
        $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
        $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);
        $AgenceObj = new Agence($pdo_conn, $global_remote_id_agence);

        // Begin remote transaction
        //$pdo_conn->beginTransaction();

        // /REM/ $mnt_cv est un Array qui n'est posté qu'en mode multidevise !!
        /*
        if ($global_multidevise) {
            $SESSION_VARS["mnt_cv"] = $mnt_cv;
        } else {
            // Fabrication de l'array $mnt_cv comme si on était en multidevise
            $mnt_cv = array("devise" => $global_monnaie);
            $SESSION_VARS["mnt_cv"] = $mnt_cv;
        }
        */

        $mnt_cv = array("devise" => $global_monnaie);
        $SESSION_VARS["mnt_cv"] = $mnt_cv;

        if (isset($mnt))			    $SESSION_VARS["mnt"]		    = recupMontant($mnt);
        if (isset($frais_depot_cpt))	$SESSION_VARS["frais_depot_cpt"]= recupMontant($frais_depot_cpt);
        if (isset($num_chq))		    $SESSION_VARS["num_chq"]	    = $num_chq;
        if (isset($date_chq))		    $SESSION_VARS["date_chq"]	    = $date_chq;
        if (isset($correspondant))		$SESSION_VARS["id_correspondant"]= $correspondant;
        if (isset($id_ben))		    $SESSION_VARS["id_ben"]		    = $id_ben;
        if (isset($remarque))		    $SESSION_VARS["remarque"]	    = $remarque;
        if (isset($communication))		$SESSION_VARS["communication"]	= $communication;

        if ( isset($SESSION_VARS['id_mandat'])) unset ($SESSION_VARS['id_mandat']);

        if ($mandat == 'EXT') {
            $SESSION_VARS['id_pers_ext'] = $id_pers_ext;
            $SESSION_VARS['denomination'] = $denomination;
        } elseif ($mandat != 0 && $mandat != 'CONJ') {
            $MANDAT = $EpargneObj->getInfosMandat($mandat);
            $clientName = $ClientObj->getClientName($global_remote_id_client);

            if($SESSION_VARS['mandat'][$mandat] == $clientName){
                $SESSION_VARS['id_pers_ext'] = NULL ;
            }else{
                $SESSION_VARS['id_mandat']=$MANDAT['id_mandat'];
                $SESSION_VARS['id_pers_ext'] = $MANDAT['id_pers_ext'];
            }
        } elseif($mandat == ''){
            $SESSION_VARS['id_pers_ext'] = NULL ;
        } elseif ($mandat == 'CONJ') {
            $infos_pers_ext = $EpargneObj->getInfosPersExt($SESSION_VARS['mandat']['CONJ']);
            $SESSION_VARS['id_pers_ext'] = $infos_pers_ext['id_pers_ext'];
        }

        // message d'erreur
        $message_erreur=NULL;

        // Recherche des données des différents opérateurs (banque, tireur, correspondant, ...)
        if (isset($SESSION_VARS['id_correspondant'])) {
            $infosCorrespondant = $CompteObj->getInfosCorrespondant($SESSION_VARS['id_correspondant']);
        }

        if (isset($SESSION_VARS['id_ben']) && $SESSION_VARS['id_ben']!='') {
            $majTireur = $TireurBenefObj->setTireur($SESSION_VARS['id_ben']);
            $infoTireur = $TireurBenefObj->getTireurBenefDatas($SESSION_VARS["id_ben"]);
            $infosbanque = $CompteObj->getInfosBanque($infoTireur['id_banque']);
            $SESSION_VARS["banque"] = $infosbanque['nom_banque'];
        }

        $InfoCpte = $CompteObj->getAccountDatas($SESSION_VARS["NumCpte"]);
        $InfoProduit = $EpargneObj->getProdEpargne($InfoCpte["id_prod"]);

        if (!isset($frais_depot_cpt))
            $SESSION_VARS["frais_depot_cpt"] = $InfoProduit["frais_depot_cpt"];

        // Dans le cas d'un chèque, on vérifie que la devise est identique à celle des comptes du Correspondant et qu'elle est bien paramétrée dans la table correspondant.
        if ($infosCorrespondant['devise']==NULL && $SESSION_VARS['type_depot']==2) {
            $message_erreur=_("Les comptes du correspondant ont des devises différentes.")."<br />";
            $message_erreur.=sprintf(_("Veuillez changer les paramètres de %s avant de continuer"), $infosCorrespondant['nom_banque']."-".$infosCorrespondant['numero_cpte'])."<br /><br />";
        }

        //@todo : multidevise correspondant
        /*
        if ($infosCorrespondant['devise']!=$SESSION_VARS['mnt_cv']['devise'] && $SESSION_VARS['type_depot']==2 && $message_erreur==NULL) {
            $message_erreur.=sprintf(_("La devise du chèque (%s) est différente de la devise des comptes du Correspondant (%s)"),$SESSION_VARS['mnt_cv']['devise'],$infosCorrespondant['devise']);
        }
        */

        // le champ de confirmation du montant dans le formulaire
        $confirmation_amount_field_name = "mnt_reel";

        // Construction du formulaire
        if ($message_erreur != NULL) {
            $html = new HTML_erreur(_("Erreur de paramétrage"));
            $html->setMessage($message_erreur);
            $html->addButton("BUTTON_OK", 'Dcp-21');
            $html->buildHTML();
            echo $html->HTML_code;
        }
        else  // Pas d'erreur
        {
            $html = new HTML_GEN2(_("Confirmation du montant à déposer"));
            /*
             * @todo : multi-devise
             *
            if (($global_multidevise) && ( $InfoCpte["devise"] != $mnt_cv["devise"] )) {	// Dépôt au guichet, avec change.
                $champ_mnt = "mnt_cv";

                $html->addField("mnt",_("Montant déposé sur le compte"),TYPC_MNT);

                $html->addField("mnt_cv",_("Montant déposé au guichet"),TYPC_MNT);
                $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $mnt_cv["cv"]);
                $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $mnt_cv["devise"]);
                $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, true);

                $html->addField($confirmation_amount_field_name,_("Confirmation montant"),TYPC_MNT);
                $html->setFieldProperties($confirmation_amount_field_name, FIELDP_DEVISE, $mnt_cv["devise"]);

                $html->addField("taux",_("Taux"),TYPC_TXT);
                $html->setFieldProperties("taux", FIELDP_DEFAULT, $mnt_cv["taux"]);
                $html->setFieldProperties("taux", FIELDP_IS_LABEL, true);

                $html->addField("un_sur_taux",_("1 / Taux"),TYPC_TXT);
                $html->setFieldProperties("un_sur_taux", FIELDP_DEFAULT, 1/$mnt_cv["taux"]);
                $html->setFieldProperties("un_sur_taux", FIELDP_IS_LABEL, true);

                $html->addField("comm_nette",_("Commission nette"),TYPC_MNT);
                $html->setFieldProperties("comm_nette", FIELDP_DEFAULT, $mnt_cv["comm_nette"]);
                $html->setFieldProperties("comm_nette", FIELDP_IS_LABEL, true);

                if($SESSION_VARS['mnt_cv']['reste'] > 0) {

                    Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $global_monnaie);

                    $html->addField("reste",_("Reste à toucher"),TYPC_MNT);
                    $html->setFieldProperties("reste", FIELDP_DEFAULT, $SESSION_VARS["mnt_cv"]['reste']);
                    $html->setFieldProperties("reste", FIELDP_IS_LABEL, true);

                    if ($SESSION_VARS["mnt_cv"]["dest_reste"] == 1) { // Le reste doit etre remis en cash
                        $html->addField("conf_reste", "Confirmation du reste remis au guichet", TYPC_MNT);
                        $html->setFieldProperties("conf_reste", FIELDP_HAS_BILLET, true);
                    }
                }
                $html->addTableRefField("dest_reste",_("Destination du reste"),"adsys_change_dest_reste");
                $html->setFieldProperties("dest_reste", FIELDP_DEFAULT, $mnt_cv["dest_reste"]);
                $html->setFieldProperties("dest_reste", FIELDP_IS_LABEL, true);
            } else {
                $champ_mnt = "mnt";
                $html->addField("mnt",_("Montant déposé au guichet"),TYPC_MNT);

                // COnfirmation du montant
                $html->addField($confirmation_amount_field_name,_("Confirmation montant"),TYPC_MNT);
                $html->setFieldProperties($confirmation_amount_field_name, FIELDP_DEVISE, $InfoCpte["devise"]);
            }
            */

            $champ_mnt = "mnt";
            $html->addField("mnt",_("Montant déposé au guichet"),TYPC_MNT);

            // COnfirmation du montant
            $html->addField($confirmation_amount_field_name,_("Confirmation montant"),TYPC_MNT);
            $html->setFieldProperties($confirmation_amount_field_name, FIELDP_DEVISE, $InfoCpte["devise"]);


            $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
            $html->setFieldProperties("mnt", FIELDP_DEVISE, $InfoCpte["devise"]);
            $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);

            //$html->setFieldProperties($confirmation_amount_field_name, FIELDP_IS_REQUIRED, true);
            //if ($SESSION_VARS['type_depot']==1) $html->setFieldProperties($confirmation_amount_field_name, FIELDP_HAS_BILLET, true);

            Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);

            $set_monnaie_devise=$InfoCpte['devise'];
//            $html->addField("frais_depot", _("Frais de dépot"), TYPC_MNT);
//            $html->setFieldProperties("frais_depot", FIELDP_DEFAULT, $SESSION_VARS["frais_depot_cpt"]);
//            $html->setFieldProperties("frais_depot", FIELDP_IS_LABEL, true);
            $html->addField("confirm_commission_agent", _("Commission pour agent"), TYPC_MNT);
            $html->setFieldProperties("confirm_commission_agent", FIELDP_DEFAULT, $_POST['commission_agent']);
            $html->setFieldProperties("confirm_commission_agent", FIELDP_IS_READONLY,TRUE);

            $html->addField("confirm_commission_institution", _("Commission pour l'institution"), TYPC_MNT);
            $html->setFieldProperties("confirm_commission_institution", FIELDP_DEFAULT, $_POST['commission_institution']);
            $html->setFieldProperties("confirm_commission_institution", FIELDP_IS_READONLY,TRUE);

            if($InfoCpte['appl_comm_deplace'] == 't') {
                // Ajout Filed sur Od commission en deplace depot
                $html->addField("od_comm_depot", _("Commission en déplacé"), TYPC_MNT);
                $html->setFieldProperties("od_comm_depot", FIELDP_DEFAULT, 0);
                $html->setFieldProperties("od_comm_depot", FIELDP_IS_LABEL, true);
                $html->setFieldProperties("od_comm_depot", FIELDP_JS_EVENT, array("onload" => $CheckOnLoad));

                    $CheckOnLoad = "
                mnt_depot = recupMontant(document.ADForm.mnt.value);
               document.ADForm.od_comm_depot.value = Math.ceil(mnt_depot * ".$InfoCpte['comm_depot_od'].");
    
                if( (recupMontant(document.ADForm.od_comm_depot.value) < ".$InfoCpte['comm_depot_od_mnt_min'].") || (recupMontant(document.ADForm.od_comm_depot.value) > ".$InfoCpte['comm_depot_od_mnt_max'].") ){
                    if (recupMontant(document.ADForm.od_comm_depot.value) < ".$InfoCpte['comm_depot_od_mnt_min']."){
                        document.ADForm.od_comm_depot.value = ".$InfoCpte['comm_depot_od_mnt_min'].";
                    }
                    else {
                         document.ADForm.od_comm_depot.value = ".$InfoCpte['comm_depot_od_mnt_max'].";
                    }
                }
                ";
                    $html->addJS(JSP_FORM, "JS0", $CheckOnLoad);
            }

            $html->addField("frais_depot", _("Frais de dépot"), TYPC_MNT);
            $html->setFieldProperties("frais_depot", FIELDP_DEFAULT, $SESSION_VARS["frais_depot_cpt"]);
            $html->setFieldProperties("frais_depot", FIELDP_IS_LABEL, true);

            // Saisir le billetage
            global $global_billet_req;

            // Billetage requis uniquement pour depot en especes
            if ($global_billet_req && $SESSION_VARS["type_depot"] == 1) {

                $html->setFieldProperties($confirmation_amount_field_name, FIELDP_IS_READONLY, true);

                $JS_billetage =
                    "OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/billetage_distant.php?m_agc=".$_REQUEST['m_agc']."&shortName=$confirmation_amount_field_name&direction=in&devise=$global_remote_monnaie_courante');";
                $html->addLink($confirmation_amount_field_name, "set_billetage", _("Billetage"), "#");
                $html->setLinkProperties("set_billetage", LINKP_JS_EVENT, array("onclick" => $JS_billetage));

                // Init class
                $ParametrageObj = new Parametrage($pdo_conn, $global_remote_id_agence);

                $result_billetage = $ParametrageObj->recupeBillet($global_remote_monnaie_courante);

                // Destroy object
                unset($ParametrageObj);

                for ($x = 0; $x < count($result_billetage); $x++) {
                    $html->addHiddenType($confirmation_amount_field_name . "_billet_" . $x, 0);
                    $html->addHiddenType($confirmation_amount_field_name . "_billet_rendu_" . $x, 0);
                }
            }

            //Contôler si le montant à retirer ne dépasse pas le montant plafond de retrait autorisé s'il y a lieu
            global $global_nom_login, $colb_tableau; // $global_id_agence
            $info_login = get_login_full_info($global_nom_login);

            $info_agence = $AgenceObj->getAgenceDatas($global_remote_id_agence);

            $msg = "";

            if ($info_agence['plafond_depot_guichet'] == 't'){
                if($info_login['depasse_plafond_depot'] == 'f' && $SESSION_VARS["mnt"] > $info_agence['montant_plafond_depot']){
                    $msg = "<center>"._("Le montant dépasse le montant plafond de dépôt autorisé. Ce login n'est pas habilité à le faire.");
                    $msg .= " "._("Veuillez contacter votre administrateur")."</center>";
                }
            }
            if ($msg != "") {
                $html = new HTML_erreur(_("Dépôt impossible")." ");
                $html->setMessage($msg);
                $html->addButton(BUTTON_OK, "God-3");
                $html->buildHTML();
                echo $html->HTML_code;
                exit();
            }

//            if (!(($SESSION_VARS['type_depot'] >= 1) && ($SESSION_VARS['type_depot'] <= 5)))
//                signalErreur(__FILE__,__LINE__,__FUNCTION__); // _("Type dépôt non renseigné")


            $ChkJS = "
        if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.$champ_mnt.value))
        {
        msg += '-"._("Le montant saisi ne correspond pas au montant à déposer")."\\n';
                ADFormValid=false;
    };";

            $js_enable_fields = " if(ADFormValid == true) {\n\t\t document.ADForm.od_comm_depot.removeAttribute('disabled'); }\n";
            $html->addJS(JSP_END_CHECK,"js_enable_fields",$js_enable_fields);

            $html->addJS(JSP_BEGIN_CHECK, "JS3",$ChkJS);
            $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
            $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
            $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
            $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
            $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

            $SESSION_VARS['envoi'] = 0;
            $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dda-4');
            $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Dda-2');
            $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'God-3');

            $html->buildHTML();
            echo $html->getHTML();

            $SESSION_VARS["set_monnaie_courante"]=$InfoCpte['devise'];
        }

        // Commit transaction
        $pdo_conn->commit();

        // Restore local monnaie courante
        $global_monnaie_courante = $global_monnaie_courante_tmp;
    }
    else if($global_nom_ecran == 'Dda-4'){
        global $global_monnaie_courante, $global_remote_monnaie_courante, $global_id_guichet, $global_id_agence, $global_remote_id_agence, $global_remote_monnaie, $global_nom_login;
        global $global_remote_id_client, $global_remote_client;
        global $dbHandler;

        // Store local monnaie courante
        $global_monnaie_courante_tmp = $global_monnaie_courante;
        $global_monnaie_courante = $global_remote_monnaie_courante;

        if(!empty($SESSION_VARS['mnt_cv']['cv'])){
            $dev = $SESSION_VARS['mnt_cv']['devise'];
        }
        else {
            $dev = $SESSION_VARS["set_monnaie_courante"];
        }

        // capturer des types de billets de la bd et nombre de billets saisie par l'utilisateur
        $valeurBilletArr = array();
        $listTypesBilletArr = buildBilletsVect($dev);
        $total_billetArr = array();

        // insert nombre billet into array
        for ($x = 0; $x < 20; $x ++) {
            if (isset($_POST['mnt_reel_billet_' . $x]) && trim($_POST['mnt_reel_billet_' . $x]) != '') {
                $valeurBilletArr[] = trim($_POST['mnt_reel_billet_' . $x]);
            } else {
                if (isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel']) != '') {
                    $valeurBilletArr[] = 'XXXX';
                }
            }
        }

        // calcul total pour chaque billets
        for ($x = 0; $x < 20; $x ++) {
            if ($valeurBilletArr[$x] == 'XXXX') {
                $total_billetArr[] = 'XXXX';
            } else {
                if (isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel']) != '' && isset($valeurBilletArr[$x]['libel']) && trim($valeurBilletArr[$x]['libel']) != '') {
                    $total_billetArr[] = (int) ($valeurBilletArr[$x]) * (int) ($listTypesBilletArr[$x]['libel']);
                }
            }
        }

        //controle d'envoie du formulaire
        $SESSION_VARS['envoi']++;
        if( $SESSION_VARS['envoi'] != 1 ) {
            $html_err = new HTML_erreur(_("Confirmation"));
            $html_err->setMessage(_("Donnée dèjà envoyée"));
            $html_err->addButton("BUTTON_OK", 'Gen-6');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
            exit();
        }
        //fin contrôle

        // Init class
        $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);
        $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
        $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);
        $AgenceObj = new Agence($pdo_conn, $global_remote_id_agence);
        $HistoriqueObj = new Historique($pdo_conn, $global_remote_id_agence);
        $AuditObj = new Audit();

        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $SESSION_VARS["set_monnaie_courante"]);

        //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
        //$NumCpte et $mnt ont été postés de l'écran précédent; $mnt est le montant net à verser non compris les frais d'opération
        //Vérification si le client n'est pas "débiteur"
        // recupére les information sur le compte

        $InfoCpte = $CompteObj->getAccountDatas($SESSION_VARS["NumCpte"]);
        $InfoProduit = $EpargneObj->getProdEpargne($InfoCpte["id_prod"]);

        if ($SESSION_VARS['mnt_cv']['cv'] == '')
            $SESSION_VARS["mnt"] = recupMontant($mnt_reel);

        // remplacer les frais de dépot par la valeur saisie s'il y'a possibilité de modification de frais
        if (isset($SESSION_VARS['frais_depot_cpt']))
            $InfoProduit["frais_depot_cpt"] = $SESSION_VARS["frais_depot_cpt"];

        if(isset($_POST['od_comm_depot'])) {
            $commission_ope_deplace = recupMontant($_POST['od_comm_depot']);
        }

        if ($SESSION_VARS['mnt_cv']['cv']!='')
            $CHANGE = $SESSION_VARS['mnt_cv'];
        else
            $CHANGE = NULL;


        $data['id_pers_ext'] = $SESSION_VARS['id_pers_ext'];
        $data['commission_op_deplace']= $commission_ope_deplace;


        ///////////////// CAS : Dépôt au guichet //////////////////////

        if ($SESSION_VARS["type_depot"] == 1)
        {
            $data['sens'] = 'in ';
            $data['communication'] = $SESSION_VARS['communication'];
            $data['remarque'] = $SESSION_VARS['remarque'];

            $type_depot = $SESSION_VARS["type_depot"];
            $erreur_remote = NULL;
            $erreur_local = NULL;

            $pdo_conn->beginTransaction();

            $rollBackRemote = true;

            try
            {
                $data["commission"]["agent"] = $confirm_commission_agent;
                $data["commission"]["institution"] = $confirm_commission_institution;
                // Sauvegarder la transaction en cours
                $AuditObj->insertTransacData($global_nom_login, $global_id_agence, $global_remote_id_agence, $global_remote_id_client, $SESSION_VARS["NumCpte"], 'depot', $SESSION_VARS['type_depot'], Divers::getTypeTransactionChoixLibel('depot', $SESSION_VARS['type_depot']), $SESSION_VARS["mnt"], serialize($SESSION_VARS), $SESSION_VARS["devise"],0,null,$commission_ope_deplace, $data["commission"]["agent"], $data["commission"]["institution"]);

                //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
                $erreur_remote = Depot::depotCpteRemoteAgent($pdo_conn, $global_remote_id_agence, $global_id_guichet, $SESSION_VARS["NumCpte"], $SESSION_VARS["mnt"], $InfoProduit, $InfoCpte, $data, $type_depot, $CHANGE);

                if ($erreur_remote->errCode == NO_ERR) {
                    // essaie transaction local
                    $erreur_local = Depot::depoCpteLocalAgent($global_remote_id_agence, $global_id_guichet, null, $SESSION_VARS["mnt"], $InfoProduit, $InfoCpte, $data, $type_depot, $CHANGE);

                    if ($erreur_local->errCode == NO_ERR)
                    {
                        // Commit remote transaction
                        if ($pdo_conn->commit())
                        {
                            $rollBackRemote = false;

                            if(isset($erreur_remote->param['id_his']) && $erreur_remote->param['id_his']>0)
                            {
                                // Sauvegarder l'ID historique en déplacé
                                $AuditObj->updateRemoteHisId($erreur_remote->param['id_his']);
                            }

                            if(isset($erreur_remote->param['id_ecriture']) && $erreur_remote->param['id_ecriture']>0)
                            {
                                // Sauvegarder l'ID ecriture en déplacé
                                $AuditObj->updateRemoteEcritureId($erreur_remote->param['id_ecriture']);
                            }

                            if ($dbHandler->closeConnection(true)) { // Commit local transaction

                                if(isset($erreur_local->param['id_his']) && $erreur_local->param['id_his']>0) {
                                    // Sauvegarder l'ID historique en local
                                    $AuditObj->updateLocalHisId($erreur_local->param['id_his']);
                                }

                                if(isset($erreur_local->param['id_ecriture']) && $erreur_local->param['id_ecriture']>0) {
                                    // Sauvegarder l'ID ecriture en local
                                    $AuditObj->updateLocalEcritureId($erreur_local->param['id_ecriture']);
                                }

                                // Valider la transaction en cours
                                $AuditObj->updateTransacFlag('t');
                            }
                            else
                            {
                                // Local transaction failed, roll back local:
                                $dbHandler->closeConnection(false);

                                // Begin remote transaction
                                $pdo_conn->beginTransaction();

                                // Revert remote transaction because remote transaction was already committed
                                $errMsg = "Echec id_his=".$erreur_remote->param['id_his'];
                                $erreur_remote_revert = Depot::depotCpteRemoteRevert($pdo_conn, $global_remote_id_agence, $global_id_guichet, $SESSION_VARS["NumCpte"], $SESSION_VARS["mnt"], $InfoProduit, $InfoCpte, $data, $type_depot, $CHANGE, NULL, NULL, $errMsg);

                                // If revert wasnt successful
                                if ($erreur_remote_revert->errCode != NO_ERR) {
                                    return $erreur_remote_revert;
                                }

                                // Commit remote transaction
                                $pdo_conn->commit();

                                throw new PDOException('Il y a eu un problème sur le serveur local !');
                            }
                        }
                        else { // remote transacation collapsed
                            // Save remote data in temp tables
                            throw new PDOException('Il y a eu un problème sur le serveur distant !');
                        }
                    }
                }
            }
            catch (PDOException $e) {
                // Sauvegarder le message d'erreur
                $AuditObj->saveErrorMessage($e->getMessage());

                // Sauvegarder le log SQL
                $AuditObj->saveSQLLog($pdo_conn->getError());

                if ($rollBackRemote) {
                    $pdo_conn->rollBack(); // Roll back remote transaction
                }

                signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());
            }

            if ($erreur_remote->errCode == NO_ERR && $erreur_local->errCode == NO_ERR) // no errors
            {
                // Start transaction
                $pdo_conn->beginTransaction();

                //prélèvement des frais en attente si solde_disponible > montant_frais
                $prelevement_frais = false;
                $num_compte = $SESSION_VARS["NumCpte"]; debug($num_compte,"num cpte");
                $mnt_frais_attente = 0;

                try
                {
                    //Y a t-il des frais en attente sur le compte ?
                    $hasFrais = $EpargneObj->hasFraisAttenteCompte($num_compte);

                    // Prelevement frais en attente
                    if($hasFrais)
                    {
                        $result = $EpargneObj->getFraisAttenteCompte($num_compte);
                        $liste_frais_attente = $result;

                        //Pour chaque frais en attente
                        foreach($liste_frais_attente as $key=>$frais_attente)
                        {
                            //Recupération du solde disponible sur le compte
                            $solde_disponible = $EpargneObj->getSoldeDisponible($num_compte);

                            $montant_frais = $frais_attente['montant'];
                            $type_frais = $frais_attente['type_frais'];
                            $date_frais = $frais_attente['date_frais'];

                            $comptable = array();//pour passage ecritures

                            //vois si le solde disponible est suffisant pour prélever les frais
                            if($solde_disponible >= $montant_frais)
                            {
                                $erreurs = $EpargneObj->paieFraisAttente($num_compte, $type_frais, $montant_frais, $comptable);

                                if ($erreurs->errCode != NO_ERR){
                                    return $erreurs;
                                }
                                //Suppression dans la table des frais en attente
                                $result = $EpargneObj->supprimeFraisAttente($num_compte, $date_frais, $type_frais);

                                if ($result->errCode != NO_ERR){
                                    return new ErrorObj($result->errCode);
                                    //return new ErrorObj(1000); // erreur  generique
                                }

                                $prelevement_frais = true;

                                //memoriser montant des frais prélevés
                                $mnt_frais_attente += $montant_frais;

                                //Historiser le prelevement
                                $fonction = 93; // 75 - Depot ou 93 - Depot en deplace  ???

                                $infos_his = 'agc='.$global_id_agence . ' - login=' . $global_nom_login;
                                $login = $login_remote;

                                $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable);
                                //$myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable);

                                if ($myErr->errCode != NO_ERR) {
                                    return $myErr;
                                }
                            }
                        }
                    }

                    $id_his = $erreur_remote->param['id_his'];

                    $infos = $EpargneObj->getCompteEpargneInfo($SESSION_VARS['NumCpte']);

                    Recu::printRecuDepotAgent($pdo_conn, $global_remote_id_agence, $global_remote_id_client, $global_remote_client, $SESSION_VARS['mnt'], $InfoProduit, $infos, $id_his, $data['id_pers_ext'], $SESSION_VARS["remarque"], $SESSION_VARS["communication"], $mnt_frais_attente, $SESSION_VARS['id_mandat'], $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, true,$commission_ope_deplace, $data['commission']['agent'],$data['commission']['institution']);

                    $html_msg = new HTML_message(_("Confirmation de dépôt sur un compte"));

                    Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
                    setMonnaieCourante($InfoProduit['devise']);

                    $montantAfficher = Divers::afficheMontant($SESSION_VARS['mnt'], true);
                    $message =_("Montant déposé sur le compte : ").$montantAfficher;

                    // @todo: Pas de fonctionnalite de change pour le mmt
                    /* if (isset($CHANGE)) {
                    // Impression du bordereau de change
                    $cpteSource = $CompteObj->getAccountDatas($SESSION_VARS['NumCpte']);

                    $cpteGuichet = getCompteCptaGui($global_id_guichet);
                    $cpteDevise = $cpteGuichet.".".$SESSION_VARS['mnt_cv']['devise'];

                    $SESSION_VARS["mnt_cv"]["source_achat"]= $cpteSource["num_complet_cpte"];//." ".$cpteSource["intitule_compte"];
                    $SESSION_VARS["mnt_cv"]["dest_vente"]= $global_guichet;
                    printRecuChange($id_his, $SESSION_VARS["mnt_cv"]["cv"],$SESSION_VARS["mnt_cv"]["devise"],$SESSION_VARS["mnt_cv"]["source_achat"],$SESSION_VARS["mnt"],$global_monnaie_courante,$SESSION_VARS["mnt_cv"]["comm_nette"],$SESSION_VARS["mnt_cv"]["taux"],$SESSION_VARS["mnt_cv"]["reste"],$SESSION_VARS["mnt_cv"]["dest_vente"]);

                    setMonnaieCourante($CHANGE['devise']);
                    $message .="<br>"._("Montant déposé au guichet : ").afficheMontant($CHANGE['cv'], true);
                    } */

                    if ($SESSION_VARS['frais_depot_cpt']>0) {
                        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
                        setMonnaieCourante($InfoCpte['devise']);
                        $montantAfficher = Divers::afficheMontant($SESSION_VARS['frais_depot_cpt'], true);
                        $message .="<br>"._("Frais de dépôt : ").$montantAfficher;
                    }

                    if($InfoCpte['appl_comm_deplace'] == 't') {
                        if ($commission_ope_deplace > 0) {
                            Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
                            setMonnaieCourante($InfoCpte['devise']);
                            $montantAfficher = Divers::afficheMontant($commission_ope_deplace, true);
                            $message .= "<br>" . _("Commission sur opération en deplacé : ") . $montantAfficher;
                        }
                    }

                    if ($data['commission']['agent']>0) {
                        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
                        setMonnaieCourante($InfoCpte['devise']);
                        $montantAfficher = Divers::afficheMontant($data['commission']['agent'], true);
                        $message .="<br>"._("Commission pour agent : ").$montantAfficher;
                    }

                    if ($data['commission']['institution']>0) {
                        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
                        setMonnaieCourante($InfoCpte['devise']);
                        $montantAfficher = Divers::afficheMontant($data['commission']['institution'], true);
                        $message .="<br>"._("Commission pour institution : ").$montantAfficher;
                    }


                    if ($erreur_remote->param["mnt"] > 0) {
                        $message .= "<br>"._("Des frais impayés ont été débités de votre compte de base pour un montant de")." :<br>";
                        $montantAfficher = Divers::afficheMontant($erreur->param["mnt"], true);
                        $message .= $montantAfficher;
                    }
                    if ($prelevement_frais) {
                        $message .= "<br>"._("Des frais en attente ont été débités de votre compte de base pour un montant de")." :<br>";
                        $montantAfficher = Divers::afficheMontant($mnt_frais_attente, true);
                        $message .= $montantAfficher;
                    }

                    $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $id_his)."</code></B>";
                    $html_msg->setMessage($message);
                    $html_msg->addButton("BUTTON_OK", 'Gen-16');
                    $html_msg->buildHTML();

                    echo $html_msg->HTML_code;

                    // Commit transaction
                    $pdo_conn->commit();

                } catch (Exception $e)
                {
                    // rollback transaction
                    $pdo_conn->rollBack();
                }

            } else {

                if ($erreur_remote->errCode != NO_ERR) {
                    $erreur = $erreur_remote;
                } elseif ($erreur_local->errCode != NO_ERR) {
                    $erreur = $erreur_local;
                }

                debug($erreur->param);

                $html_err = new HTML_erreur(_("Echec du dépôt en déplacé sur un compte. "));
                $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
                $html_err->addButton("BUTTON_OK", 'Gen-16');
                $html_err->buildHTML();
                echo $html_err->HTML_code;
            };
        }

        // @todo: NOT REQUIRED ?
        // On vérifie si le client n'est plus débiteur
        /*
         if (!Historique::isClientDebiteur($pdo_conn, $global_remote_id_agence, $global_id_client))

            $global_client_debiteur = false;
        */


        // Clear data session variables
        unset(
            $SESSION_VARS['NumCpte'],
            $SESSION_VARS['type_depot'],
            $SESSION_VARS['id_pers_ext'],
            $SESSION_VARS['id_mandat'],
            $SESSION_VARS['mandat'],
            $SESSION_VARS['denomination'],
            $SESSION_VARS['mnt'],
            $SESSION_VARS['mnt_cv'],
            $SESSION_VARS['num_chq'],
            $SESSION_VARS['date_chq'],
            $SESSION_VARS['nom_ben'],
            $SESSION_VARS['id_ben'],
            $SESSION_VARS['id_correspondant'],
            $SESSION_VARS['communication'],
            $SESSION_VARS['remarque'],
            $SESSION_VARS["frais_depot_cpt"],
            $SESSION_VARS["banque"],
            $SESSION_VARS["frais_depot_cpt"],
            $SESSION_VARS['envoi'],
            $SESSION_VARS['tib'],
            $SESSION_VARS['type_recherche'],
            $SESSION_VARS['field_name'],
            $SESSION_VARS['field_id'],
            $SESSION_VARS['gpe']
        );

        // Restore local monnaie courante
        $global_monnaie_courante = $global_monnaie_courante_tmp;
    }
?>
