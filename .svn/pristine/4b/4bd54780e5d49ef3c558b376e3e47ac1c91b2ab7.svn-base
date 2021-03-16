<?php
require_once ('lib/dbProcedures/client.php');
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/agence.php');
require_once ('lib/dbProcedures/compte.php');
require_once ('lib/dbProcedures/epargne.php');
require_once ('lib/html/HTML_GEN2.php');
require_once ('lib/html/FILL_HTML_GEN2.php');
require_once 'lib/html/HTML_champs_extras.php';
require_once ('modules/epargne/recu.php');
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/agency_banking.php';
require_once 'lib/misc/excel.php';
require_once 'lib/misc/csv.php';
require_once 'lib/misc/divers.php';
require_once "modules/rapports/csv_epargne.php";
require_once "modules/rapports/xml_epargne.php";
require_once "modules/rapports/xml_agence.php";
require_once 'modules/rapports/xslt.php';
require_once "lib/html/HTML_menu_gen.php";

/**
 * [787] Rapports Agency Banking.
 * Ces fonctions appellent les écrans suivants :
 * - Rab-1 : Sélection du rapport à imprimer
 * - Rab-2 : Personalisation du rapport sur agents
 * - Rcq-4 et Rab-14 : Impression PDF ou export CSV du rapport sur agents
 * - Rcq-20 : Personalisation du rapport Liste des commandes de chéquiers
 * - Rcq-21 et Rcq-22 : Impression PDF ou export CSV du rapport Liste des commandes de chéquiers
 * - Rcq-30 : Personalisation du rapport Liste des chéquiers envoyés à l'impression
 * - Rcq-31 et Rcq-32 : Impression PDF ou export CSV du rapport Liste des chéquiers envoyés à l'impression
 * - Rcq-40 : Personalisation du rapport Liste des chèques/chéquiers mise en opposition
 * - Rcq-41 et Rcq-42 : Impression PDF ou export CSV du rapport Liste des chèques/chéquiers misent en opposition
 *
 * @package Rapports
 **/

if ($global_nom_ecran == "Rab-1") {
//    // Recherche de tous les rapports à afficher
//    $suffix_exclusion = array('RDA', 'RRA', 'VCC', 'TRA', 'TRS', 'VCH');
//    foreach ($adsys["adsys_rapport"] as $key => $name) {
//        if (substr($key, 0, 3) == 'AGB' && !in_array(substr($key, 4, 6), $suffix_exclusion)) {
//            $rapports[$key] = _($name);
//        }
//    }
//
//    $html = new HTML_GEN2(_("Sélection type rapport Agency Banking"));
//
//    $html->addField("type", _("Type de rapport"), TYPC_LSB);
//    $html->setFieldProperties("type", FIELDP_IS_REQUIRED, true);
//    $html->setFieldProperties("type", FIELDP_ADD_CHOICES, $rapports);
//
//    $html->addFormButton(1, 1, "valider", _("Sélectionner"), TYPB_SUBMIT);
//    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Gen-13");
//    $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
//    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
//    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
//
//
//    // Tableau indiquant le prochain écran en fonction du code rapport
//    $prochEc = array (
//        "RAA" => 2,
//        "RDC" => 5,
//        "RCA" => 7,
//        "BDC" => 8
//    );
//
//    foreach ($prochEc as $code => $ecran)
//        $js .= "if (document.ADForm.HTML_GEN_LSB_type.value == 'AGB-$code')
// 	                 assign('Rab-$ecran');";
//
//    $html->addJS(JSP_BEGIN_CHECK, "js1", $js);
//    $html->show();
    $MyMenu = new HTML_menu_gen(_("Choix rapport"));
    $MyMenu->addItem(_("Rapport sur agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rab-2", 787, "$http_prefix/images/menu_gestion_client.gif");
    $MyMenu->addItem(_("Rapport des commissions"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rab-5", 787, "$http_prefix/images/rapports_compta.gif");
    $MyMenu->addItem(_("Rapport des commissions historisée"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rab-9", 787, "$http_prefix/images/gestion_plan_comptable.gif");
    $MyMenu->addItem(_("Clients créés via agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rab-7", 787, "$http_prefix/images/consultation_client.gif");
    $MyMenu->addItem(_("Brouillard du compte de flotte journalier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rab-8", 787, "$http_prefix/images/menu_compta.gif");
    $MyMenu->addItem(_("Retour Menu Agency Banking"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-16", 0, "$http_prefix/images/back.gif");
    $MyMenu->buildHTML();
    echo $MyMenu->HTMLCode;
}
else if ($global_nom_ecran == "Rab-2"){
    //ecran Personnalisation du rapport: Rapport des agent
    global $global_nom_login, $global_nom_ecran_prec;
    $html = new HTML_GEN2(_("Critères de recherche"));


    //Champs date rapport
    $html->addField("date_rapport", _("Date rapport"), TYPC_DTE);
    $html->setFieldProperties("date_rapport", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("date_rapport", FIELDP_DEFAULT, date("d/m/Y"));


    $array_etat = array(1 => _("Actif"), 2 => _("Inactif"));
    $html->addField("etat_agent", _("Etat agent"), TYPC_LSB);
    $html->setFieldProperties("etat_agent", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("etat_agent", FIELDP_HAS_CHOICE_TOUS, true);
    $html->setFieldProperties("etat_agent", FIELDP_ADD_CHOICES, $array_etat);


    //Boutons
    $html->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rab-4");
    $html->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
    $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rab-14");
    $html->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
    $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rab-14");
    $html->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
else if($global_nom_ecran == "Rab-4" || $global_nom_ecran == "Rab-14"){
    global $global_nom_login,$global_nom_utilisateur,$global_id_utilisateur;

    $critere = array(
        'Date Rapport' => $date_rapport,
        'Etat agent' => (empty($etat_agent))?'Tous':adb_gettext($adsys["adsys_statut_utilisateur"][$etat_agent])
    );
    if (!isProfilAgent($global_nom_login)) {
        $agent = getAgentData($etat_agent, $date_rapport);
    }else{
        $agent = getAgentData($etat_agent, $date_rapport,$global_nom_login);
    }

    //ecran Personnalisation du rapport: Rapport des agent [PDF]
    if($global_nom_ecran == "Rab-4"){
        $xml_agent = xml_rapport_agent($agent, $critere);
        $fichier_pdf = xml_2_xslfo_2_pdf($xml_agent, 'rapport_agent.xslt');
        echo get_show_pdf_html("Gen-16", $fichier_pdf);
    }
    //ecran Personnalisation du rapport: Rapport des agent [XLSX, CSV]
    else  if($global_nom_ecran == "Rab-14"){
        $xml_agent = xml_rapport_agent($agent, $critere);
        $csv_file = xml_2_csv($xml_agent, 'rapport_agent.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if (isset($excel) && $excel == 'Export EXCEL'){
            echo getShowEXCELHTML("Gen-16", $csv_file);
        }
        else{
            echo getShowCSVHTML("Gen-16", $csv_file);
        }
    }
}
else if($global_nom_ecran == "Rab-5"){
    //ecran Personnalisation du rapport: Rapport des commission
    global $global_nom_login, $global_nom_ecran_prec;
    $html = new HTML_GEN2(_("Critères de recherche"));

    $util_agent = isLoginAgent();

    if (!isProfilAgent($global_nom_login)) {
        $html->addField("util", _("Login"), TYPC_LSB);
        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_AUCUN, false);
        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_TOUS, true);
        $html->setFieldProperties("util", FIELDP_ADD_CHOICES, $util_agent);
    }else{
        $html->addHiddenType("util",$util_agent[$global_nom_login]);
    }

    //Champs date début
    $html->addField("date_min", _("Date min"), TYPC_DTE);
    $html->setFieldProperties("date_min", FIELDP_DEFAULT, date("01/01/Y"));

    //Champs date fin
    $html->addField("date_max", _("Date max"), TYPC_DTE);
    $html->setFieldProperties("date_max", FIELDP_DEFAULT, date("d/m/Y"));

    $type_commission = array(1 => 'Retrait', 2 => 'Dépôt', 3 => 'Création client');
    $html->addField("type_commission", _("Type de commission"), TYPC_LSB);
    $html->setFieldProperties("type_commission", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("type_commission", FIELDP_HAS_CHOICE_TOUS, true);
    $html->setFieldProperties("type_commission", FIELDP_ADD_CHOICES, $type_commission);

    $type_commission = array(1 => 'Détaillé', 2 => 'Synthétique');
    $html->addField("type_rapport", _("Type de rapport"), TYPC_LSB);
    $html->setFieldProperties("type_rapport", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("type_rapport", FIELDP_HAS_CHOICE_TOUS, false);
    $html->setFieldProperties("type_rapport", FIELDP_ADD_CHOICES, $type_commission);

    //Boutons
    $html->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rab-15");
    $html->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
    $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rab-25");
    $html->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
    $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rab-25");
    $html->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
else if($global_nom_ecran == "Rab-15" || $global_nom_ecran == "Rab-25"){
    if ($date_min == "") $date_min = NULL;
    if ($date_max == "") $date_max = NULL;

    $critere = array(
        'Login agent' => (empty($util))?'Tous':$util,
        'Date minimum' => (empty($date_min))?' - ':$date_min,
        'Date maximum' => (empty($date_max))?' - ':$date_max,
        'Type commission' => empty($type_commission)?'Tous':(($type_commission == 1)?'Retrait':(($type_commission == 3)?'Création client':'Dépôt')),
        'Type rapport' => ($type_rapport == 1)?'Détaillé':'Synthétique'
    );

    $type_comm = empty($type_commission)?null:(($type_commission == 1)?764:(($type_commission == 3)?762:763));
    $agent_comm = getAgentCommissionTransac($type_comm, $util, $date_min, $date_max);

    //ecran Personnalisation du rapport: Rapport des commission [PDF]
    if($global_nom_ecran == "Rab-15"){
        if($type_rapport == 1){
            $xml_comm_agent = xml_rapport_commission_agent_del($agent_comm, $critere);
            $xslt = 'rapport_commission_agent.xslt';
        }else{
            $xml_comm_agent = xml_rapport_commission_agent_syn($agent_comm, $critere);
            $xslt = 'rapport_commission_agent_syn.xslt';
        }
        $fichier_pdf = xml_2_xslfo_2_pdf($xml_comm_agent, $xslt);
        echo get_show_pdf_html("Gen-16", $fichier_pdf);
    }
    //ecran Personnalisation du rapport: Rapport des commission [XLSX, CSV]
    else  if($global_nom_ecran == "Rab-25"){
        if($type_rapport == 1){
            $xml_comm_agent = xml_rapport_commission_agent_del($agent_comm, $critere);
            $xslt = 'rapport_commission_agent.xslt';
        }else{
            $xml_comm_agent = xml_rapport_commission_agent_syn($agent_comm, $critere);
            $xslt = 'rapport_commission_agent_syn.xslt';
        }
        $csv_file = xml_2_csv($xml_comm_agent, $xslt);
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if (isset($excel) && $excel == 'Export EXCEL'){
            echo getShowEXCELHTML("Gen-16", $csv_file);
        }
        else{
            echo getShowCSVHTML("Gen-16", $csv_file);
        }
    }
}
else if ($global_nom_ecran == "Rab-6"){
    //ecran Personnalisation du rapport: Rapport des agent
    global $global_nom_login, $global_nom_ecran_prec;
    $html = new HTML_GEN2(_("Critères de recherche"));

    $util_agent = isLoginAgent();
    if (!isProfilAgent($global_nom_login)) {
        $html->addField("util", _("Login"), TYPC_LSB);
        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_AUCUN, false);
        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_TOUS, true);
        $html->setFieldProperties("util", FIELDP_ADD_CHOICES, $util_agent);
    }

    //Champs date début
    $html->addField("date_min", _("Date min"), TYPC_DTE);

    //Champs date fin
    $html->addField("date_max", _("Date max"), TYPC_DTE);

    $type_commission = array(1 => 'Retrait', 2 => 'Dépôt');
    $html->addField("type_commission", _("Type de commission"), TYPC_LSB);
    $html->setFieldProperties("type_commission", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("type_commission", FIELDP_HAS_CHOICE_TOUS, true);
    $html->setFieldProperties("type_commission", FIELDP_ADD_CHOICES, $type_commission);

    $type_commission = array(1 => 'Détaillé', 2 => 'Synthétique');
    $html->addField("type_rapport", _("Type de rapport"), TYPC_LSB);
    $html->setFieldProperties("type_rapport", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("type_rapport", FIELDP_HAS_CHOICE_TOUS, false);
    $html->setFieldProperties("type_rapport", FIELDP_ADD_CHOICES, $type_commission);

    //Boutons
    $html->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rab-16");
    $html->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
    $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rab-26");
    $html->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
    $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rab-26");
    $html->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
else if($global_nom_ecran == "Rab-16" || $global_nom_ecran == "Rab-26"){
    if ($date_min == "") $date_min = NULL;
    if ($date_max == "") $date_max = NULL;

    $critere = array(
        'Login agent' => (empty($util))?'Tous':$util,
        'Date minimum' => (empty($date_min))?' - ':$date_min,
        'Date maximum' => (empty($date_max))?' - ':$date_max,
        'Type commission' => empty($type_commission)?'Tous':(($type_commission == 1)?'Retrait':'Dépôt'),
        'Type rapport' => ($type_rapport == 1)?'Détaillé':'Synthétique'
    );

    //ecran Personnalisation du rapport: Rapport des agent [PDF]
    if($global_nom_ecran == "Rab-16"){
//        print_rn('rendering pdf version...');
    }
    //ecran Personnalisation du rapport: Rapport des commission [XLSX, CSV]
    else  if($global_nom_ecran == "Rab-26"){
        if (isset($excel) && $excel == 'Export EXCEL'){
//            print_rn('rendering excel version...');
        }
        else{
//            print_rn('rendering csv version...');
        }
    }
}
else if ($global_nom_ecran == "Rab-7"){
    //ecran Personnalisation du rapport: Rapport des commission
    global $global_nom_login, $global_nom_ecran_prec, $global_adsys;
    $html = new HTML_GEN2(_("Critères de recherche"));

    if (!isProfilAgent($global_nom_login)){
        $util_agent = getAllUtiAgent();
        $html->addField("util", _("Nom Agent"), TYPC_LSB);
        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_AUCUN, false);
        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_TOUS, true);
        $html->setFieldProperties("util", FIELDP_ADD_CHOICES, $util_agent);
    }
//    else{
//        $data_utilisateur = getDataUtilFromLogin();
//        $html->addField("util", _("Login"), TYPC_LSB);
//        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_AUCUN, false);
//        $html->setFieldProperties("util", FIELDP_HAS_CHOICE_TOUS, true);
//        $html->setFieldProperties("util", FIELDP_ADD_CHOICES, $data_utilisateur);
//    }


    //Champs date début
    $html->addField("date_debut", _("Date début"), TYPC_DTE);
    $html->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
    //Champs date Fin
    $html->addField("date_fin", _("Date fin"), TYPC_DTE);
    $html->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

    $html->addTableRefField("stat_juridique", _("Statut juridique"), "adsys_stat_jur");
    $html->setFieldProperties("stat_juridique", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("stat_juridique", FIELDP_HAS_CHOICE_TOUS, true);

    $html->addTableRefField("etat_cli", _("Etat client"), "adsys_etat_client");
    $html->setFieldProperties("etat_cli", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("etat_cli", FIELDP_HAS_CHOICE_TOUS, true);

    //Boutons
    $html->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rab-17");
    $html->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
    $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rab-27");
    $html->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
    $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rab-27");
    $html->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
else if($global_nom_ecran == "Rab-17" || $global_nom_ecran == "Rab-27"){
    global $global_id_utilisateur;
    if ($date_debut == "") $date_debut = NULL;
    if ($date_fin == "") $date_fin = NULL;

    if (!isProfilAgent($global_nom_login)) {
        if (!empty($util)){
            $nom_uti = getDataUtilisateur($util);
        }
    }else{
        $nom_uti = getDataUtilisateur($global_id_utilisateur);
        $util = $global_id_utilisateur;
    }
    $critere = array(
      'Login' => (empty($util))?'Tous':$nom_uti['nom']." ". $nom_uti['prenom'],
      'Date début' => (empty($date_debut))?' - ':$date_debut,
      'Date fin' => (empty($date_fin))?' - ':$date_fin,
      'Statut juridique' => empty($stat_juridique)?'Tous':adb_gettext($adsys["adsys_stat_jur"][$stat_juridique]),
      'Etat client' => empty($etat_cli)?'Tous':adb_gettext($adsys["adsys_etat_client"][$etat_cli])
    );

    $DATAS_CLIENT = getDataCreationClientAgent($date_debut,$date_fin,$util,$stat_juridique,$etat_cli);

    if($global_nom_ecran == "Rab-17"){
        $xml_client = xml_rapport_creation_client_agent($DATAS_CLIENT,$critere);
        $fichier_pdf = xml_2_xslfo_2_pdf($xml_client, 'rapport_creation_client_agent.xslt');
        echo get_show_pdf_html("Gen-16", $fichier_pdf);
    }
    //ecran Personnalisation du rapport: Rapport des agent [XLSX, CSV]
    else  if($global_nom_ecran == "Rab-27"){
        $xml_client = xml_rapport_creation_client_agent($DATAS_CLIENT, $critere);
        $csv_file = xml_2_csv($xml_client, 'rapport_creation_client_agent.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if (isset($excel) && $excel == 'Export EXCEL'){
            echo getShowEXCELHTML("Gen-16", $csv_file);
        }
        else{
            echo getShowCSVHTML("Gen-16", $csv_file);
        }
    }
}

else if ($global_nom_ecran == "Rab-8"){
    global $adsys, $global_nom_login;
    $myForm = new HTML_GEN2(_("Personalisation du rapport"));

    if (!isProfilAgent($global_nom_login)) {
        $guichet = getGuichetAgent();
        $myForm->addField("guichet", _("Guichet"), TYPC_LSB);
        $myForm->setFieldProperties("guichet", FIELDP_HAS_CHOICE_AUCUN, false);
        $myForm->setFieldProperties("guichet", FIELDP_HAS_CHOICE_TOUS, true);
        $myForm->setFieldProperties("guichet", FIELDP_ADD_CHOICES, $guichet);
    }else{
        $guichet = getGuichetAgent($global_nom_login);
        $key = array_keys($guichet);
        $myForm->addHiddenType("guichet",$key[0]);
    }
//    $myForm->addField("guichet", _("Guichet"), TYPC_LSB);
//    $myForm->setFieldProperties("guichet", FIELDP_HAS_CHOICE_AUCUN, false);
//    $myForm->setFieldProperties("guichet", FIELDP_HAS_CHOICE_TOUS, true);
//    $myForm->setFieldProperties("guichet", FIELDP_ADD_CHOICES, $guichet);


    $myForm->addField("date", _("Date du brouillard"), TYPC_DTE);
    $myForm->setFieldProperties("date", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("date", FIELDP_DEFAULT, date("d/m/Y"));
    $myForm->addField("details", _("Afficher le détail des transactions"), TYPC_BOL);
    $myForm->setFieldProperties("details", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("details", FIELDP_DEFAULT, true);
    $myForm->addTable("ad_cpt_comptable", OPER_INCLUDE, array (
      "devise"
    ));
    $myForm->setFieldProperties("devise", FIELDP_LONG_NAME, "Devise");
    $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, false);
    $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_TOUS, true);
    $myForm->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rab-18");
    $myForm->addFormButton(1, 2, "valider_excel", _("Export EXCEL"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider_excel", BUTP_PROCHAIN_ECRAN, "Rab-28");
    $myForm->addFormButton(1, 3, "valider_csv", _("Export CSV"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider_csv", BUTP_PROCHAIN_ECRAN, "Rab-28");
    $myForm->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
}

else if($global_nom_ecran == "Rab-18" || $global_nom_ecran == "Rab-28"){
    global $adsys;
    global $global_id_agence;
    global $dbHandler;
    $db = $dbHandler->openConnection();
    if(isset($guichet)) {
        $sql = "SELECT g.id_gui, g.libel_gui, l.cpte_flotte_agent 
                FROM ad_gui g INNER JOIN ad_log l ON l.guichet = g.id_gui 
                INNER JOIN ad_uti u ON u.id_utilis = l.id_utilisateur WHERE u.is_agent_ag = 't'
                AND g.id_ag = '$global_id_agence' AND g.id_gui= '$guichet'";
//        $sql = "select id_gui, libel_gui from ad_gui where id_ag = '$global_id_agence' and id_gui='$guichet'";
        }
    else
        $sql = "SELECT g.id_gui, g.libel_gui, l.cpte_flotte_agent FROM ad_gui g INNER JOIN ad_log l ON l.guichet = g.id_gui INNER JOIN ad_uti u ON u.id_utilis = l.id_utilisateur WHERE u.is_agent_ag = 't'";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $GUICHET = array ();
    $DATA = array();

    while ($row = $result->fetchrow()) {
        $GUICHET[$row[0]] = $row;
        //$GUICHET[$row[0]]['libel'] = $row[1];
    }
    if ($devise == "0") // Choix [Tous] devise
        $devise = NULL;
    //Parcours des guichet et recupération des infos guichet

    foreach ($GUICHET as $id_gui => $GUI) {
        $DATA_GUI = getBrouillardCaisseAgent($GUI[0], $date, $details, $devise, $export_csv);
        $dataset_key = array_keys($DATA_GUI);
        $DATA[$GUI[0]] = $DATA_GUI;
        $DATA[$GUI[0]][$dataset_key[0]]['cpte_flotte_agent'] = $GUI[2];
    }

    if ($global_nom_ecran == 'Rab-18') {
        //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        $xml = xml_brouillard_caisse_agent($DATA, $date);
        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'brouillard_caisse_agb.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Gen-16", $fichier_pdf);
    } else
        if ($global_nom_ecran == 'Rab-28') {
            //Génération du fichier CSV
            //$DATA = getBrouillardCaisse($guichet, $date, $details, $devise, true);
            $xml = xml_brouillard_caisse_agent($DATA, $date, true);
            $csv_file = xml_2_csv($xml, 'brouillard_caisse_agb.xslt');
            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
            if (isset($valider_excel) && $valider_excel == 'Export EXCEL'){
                echo getShowEXCELHTML("Gen-16", $csv_file);
            }
            else{
                echo getShowCSVHTML("Gen-16", $csv_file);
            }
        }
}
else if($global_nom_ecran == "Rab-9"){
    //ecran Personnalisation du rapport: Rapport des commission
    global $global_nom_login, $global_nom_ecran_prec, $global_adsys;
    $html = new HTML_GEN2(_("Critères de recherche"));


    //Champs date début
    $html->addField("date_debut", _("Date début"), TYPC_DTE);
    $html->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
    //Champs date Fin
    $html->addField("date_fin", _("Date fin"), TYPC_DTE);
    $html->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));


    $type_commission = array(2 => 'Dépôt', 1 => 'Retrait', 3 => 'Création client');
    $html->addField("type_commission", _("Type de commission"), TYPC_LSB);
    $html->setFieldProperties("type_commission", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("type_commission", FIELDP_HAS_CHOICE_TOUS, true);
    $html->setFieldProperties("type_commission", FIELDP_ADD_CHOICES, $type_commission);

    //Boutons
    $html->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rab-19");
    $html->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
    $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rab-29");
    $html->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
    $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rab-29");
    $html->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");
    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
else if($global_nom_ecran == "Rab-19" || $global_nom_ecran == "Rab-29"){
    $comm_version = getCommissionVersion($date_debut, $date_fin, $type_commission);

    $dataset = array();
    foreach($comm_version as $versions){
        $dataset[$versions['type_comm']][strval(pg2phpDate($versions['date_creation']))] = unserialize($versions['version_set']);
    }

    if(empty($type_commission) || ($type_commission == 3)) {
        $comm_client = getCommissionNouveauClient();
        $date_key = strval(pg2phpDate($comm_client['date_creation']));
        $fdate_key = strtotime(str_replace('/', '-', $date_key));
        $fdate_min = strtotime(str_replace('/', '-', $date_debut));
        $fdate_max = strtotime(str_replace('/', '-', $date_fin));

        if(($fdate_key >= $fdate_min) && ($fdate_key <= $fdate_max)) {
            $dataset[3][$date_key][0] = $comm_client;
        }
    }

    $list_critere = array(
        'Date début' => (empty($date_debut))?' - ':$date_debut,
        'Date fin' => (empty($date_fin))?' - ':$date_fin,
        'Type commission' => empty($type_commission)?'Tous':(($type_commission == 1)?'Retrait':(($type_commission == 3)?'Création client':'Dépôt')),
    );

    if ($global_nom_ecran == 'Rab-19') {
        //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        $xml_hist = xml_commission_historic($dataset, $list_critere);
        $fichier_pdf = xml_2_xslfo_2_pdf($xml_hist, 'commission_historique.xslt');
        echo get_show_pdf_html("Gen-16", $fichier_pdf);
    } else {
        if ($global_nom_ecran == 'Rab-29') {
            //Génération du fichier CSV
            //$DATA = getBrouillardCaisse($guichet, $date, $details, $devise, true);
            $xml_hist = xml_commission_historic($dataset, $list_critere);
            $csv_file = xml_2_csv($xml_hist, 'commission_historique.xslt');
            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
            if (isset($excel) && $excel == 'Export EXCEL') {
                echo getShowEXCELHTML("Gen-16", $csv_file);
            } else {
                echo getShowCSVHTML("Gen-16", $csv_file);
            }
        }
    }
}
?>
