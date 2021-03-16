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
require_once 'lib/misc/excel.php';
require_once 'lib/misc/csv.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xslt.php';
require_once "lib/html/HTML_menu_gen.php";
require_once ('lib/dbProcedures/carte_atm.php');

if ($global_nom_ecran == "Rat-1"){

    global $global_nom_login, $global_nom_ecran_prec;
    $html = new HTML_GEN2(_("Critères de recherche"));

    $array_rapports = array (
        "carte_a_imprimer" => _("Liste des cartes à imprimer"),
        "carte_a_activer" => _("Liste des cartes à activer"),
        "liste_carte" => _("Liste des cartes"),
        "atm_transaction" => _("Liste des transactions ATM")
    );

    $html->addField("choix_rapport", _("Choix du rapport"), TYPC_LSB);
    $html->setFieldProperties("choix_rapport", FIELDP_HAS_CHOICE_AUCUN, true);
    $html->setFieldProperties("choix_rapport", FIELDP_HAS_CHOICE_TOUS, false);
    $html->setFieldProperties("choix_rapport", FIELDP_ADD_CHOICES, $array_rapports);
    $html->setFieldProperties("choix_rapport", FIELDP_IS_REQUIRED, true);



    //Boutons
    $html->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rat-2");
    $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
    $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();

}

else if ($global_nom_ecran == "Rat-2"){

    if ($choix_rapport == "carte_a_imprimer"){
        $SESSION_VARS['choix_rapport'] = $choix_rapport;

        global $global_nom_login, $global_nom_ecran_prec;
        $html = new HTML_GEN2(_("Critères de recherche"));

        $html->addField("date_deb", _("Date début "), TYPC_DTE);
        $html->setFieldProperties("date_deb", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("date_deb", FIELDP_DEFAULT, '01/01/2020');
        $html->addField("date_fin", _("Date fin "), TYPC_DTE);
        $html->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));


        //Boutons
        $html->addFormButton(1, 1, "excel", _("Export Excel"), TYPB_SUBMIT);
        $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rat-3");
        $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
        $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

        $html->buildHTML();
        echo $html->getHTML();

    }else if ($choix_rapport == "carte_a_activer"){
        $SESSION_VARS['choix_rapport'] = $choix_rapport;

        global $global_nom_login, $global_nom_ecran_prec;
        $html = new HTML_GEN2(_("Critères de recherche"));

        $html->addField("date_deb", _("Date début "), TYPC_DTE);
        $html->setFieldProperties("date_deb", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("date_deb", FIELDP_DEFAULT, '01/01/2020');
        $html->addField("date_fin", _("Date fin "), TYPC_DTE);
        $html->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));


        //Boutons
        $html->addFormButton(1, 1, "excel", _("Export Excel"), TYPB_SUBMIT);
        $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rat-3");
        $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
        $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

        $html->buildHTML();
        echo $html->getHTML();

    }else if ($choix_rapport == "liste_carte"){
        $SESSION_VARS['choix_rapport'] = $choix_rapport;

        global $global_nom_login, $global_nom_ecran_prec;
        $html = new HTML_GEN2(_("Critères de recherche"));

        $html->addField("etat_carte", _("Etat carte"), TYPC_LSB);
        $html->setFieldProperties("etat_carte", FIELDP_HAS_CHOICE_TOUS, true);
        $html->setFieldProperties("etat_carte", FIELDP_ADD_CHOICES, $adsys["etat_carte_atm"]);

        // Recherche client
        $js_chercheClient = "
            OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;
        ";

        $html->addField("num_client", _("N° de client"), TYPC_INT);
        $html->setFieldProperties("num_client", FIELDP_IS_REQUIRED, false);
        $html->setFieldProperties("num_client", FIELDP_IS_LABEL, false);
        $html->addLink("num_client", "rechercher", _("Rechercher"), "#");
        $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $js_chercheClient));

        $html->addField("motif_dem", _("Motif demande"), TYPC_LSB);
        $html->setFieldProperties("motif_dem", FIELDP_HAS_CHOICE_TOUS, true);
        $html->setFieldProperties("motif_dem", FIELDP_ADD_CHOICES, $adsys["motif_demande"]);

        $html->addField("date_exp_min", _("Date expiration minimum "), TYPC_DTE);
        $html->setFieldProperties("date_exp_min", FIELDP_DEFAULT, '01/01/2020');
        $html->setFieldProperties("date_exp_min", FIELDP_IS_REQUIRED, true);
        $html->addField("date_exp_max", _("Date expiration maximum "), TYPC_DTE);
        $html->setFieldProperties("date_exp_max", FIELDP_DEFAULT, date("d/m/Y"));
        $html->setFieldProperties("date_exp_max", FIELDP_IS_REQUIRED, true);


        //Boutons
        $html->addFormButton(1, 1, "excel", _("Export Excel"), TYPB_SUBMIT);
        $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rat-3");
        $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
        $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

        $html->buildHTML();
        echo $html->getHTML();
    }else if ($choix_rapport == "atm_transaction"){
        $SESSION_VARS['choix_rapport'] = $choix_rapport;


        global $global_nom_login, $global_nom_ecran_prec;
        $html = new HTML_GEN2(_("Critères de recherche"));
        $html_jquery = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
        $html->addHTMLExtraCode("jquery_link", $html_jquery);


        $html->addField("num_client", _("N° de client"), TYPC_INT);
        $html->setFieldProperties("num_client", FIELDP_IS_REQUIRED, false);
        $html->setFieldProperties("num_client", FIELDP_IS_LABEL, false);
        $html->addLink("num_client", "rechercher", _("Rechercher"), "#");
        $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $js_chercheClient));

        $html->addField("date_deb", _("Date début "), TYPC_DTE);
        $html->setFieldProperties("date_deb", FIELDP_DEFAULT, '01/01/2020');
        $html->setFieldProperties("date_deb", FIELDP_IS_REQUIRED, true);
        $html->addField("date_fin", _("Date fin "), TYPC_DTE);
        $html->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));
        $html->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);


        $html->addField("num_carte", _("Numéro carte"), TYPC_TXT);


        $js = "  
        $('[name=num_carte]').change(function(){
            if ($(this).val().length > 16 || $(this).val().length < 16){ alert('Votre numéro de carte dépasse le nombre de caractères prevus'); $(this).val('');}
            else{
            var i;
            var num = [];
                for (i =0; i< 4; i++){
                    num[i] = $(this).val().substring(0,4);
                    console.log(num[i]);
                    $(this).val($(this).val().slice(4));console.log($(this).val());
                }
            
            $(this).val(num.join(' '));
            }
        }
        );";


        $html->addJS(JSP_FORM, "control_num_carte", $js);
        //Boutons
        $html->addFormButton(1, 1, "excel", _("Export Excel"), TYPB_SUBMIT);
        $html->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rat-3");
        $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
        $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

        $html->buildHTML();
        echo $html->getHTML();
    }

}

else if ($global_nom_ecran == "Rat-3"){

    if ($SESSION_VARS['choix_rapport'] == "carte_a_imprimer"){
        $criteres = array(
            _("Date debut") => $date_deb,
            _("Date fin") =>$date_fin
        );

        $date_deb = php2pg($date_deb);
        $date_fin = php2pg($date_fin);
        $xml_imprimer = xml_atm_carte_a_imprimer($criteres,$date_deb, $date_fin,true);

        $fichier = xml_2_csv($xml_imprimer, 'atm_carte_a_imprimer.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Rat-1", $fichier);
        unset ($SESSION_VARS['choix_rapport']);

    }
    else if ($SESSION_VARS['choix_rapport'] == "carte_a_activer"){
        $criteres = array(
            _("Date debut") => $date_deb,
            _("Date fin") =>$date_fin
        );

        $date_deb = php2pg($date_deb);
        $date_fin = php2pg($date_fin);
        $xml_imprimer = xml_atm_carte_a_activer($criteres,$date_deb, $date_fin,true);

        $fichier = xml_2_csv($xml_imprimer, 'atm_carte_a_activer.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Rat-1", $fichier);
        unset ($SESSION_VARS['choix_rapport']);

    }
    else if ($SESSION_VARS['choix_rapport'] == "liste_carte"){
        if ($etat_carte == null){
            $etat_carte_libel = "Tous";
        }
        if ($motif_dem == null){
            $motif_dem_libel = "Tous";
        }
        if ($num_client == null){
            $num_client_libel = "Tous";
        }

        $criteres = array(
            _("Etat carte") => $etat_carte_libel,
            _("No client") =>$num_client_libel,
            _("Motif demande") => $motif_dem_libel,
            _("Date expiration minimum") => $date_exp_min,
            _("Date expiration maximum") =>$date_exp_max
        );

        $date_exp_min = php2pg($date_exp_min);
        $date_exp_max = php2pg($date_exp_max);


        $xml_liste= xml_atm_liste_carte($criteres,$etat_carte,$num_client,$motif_dem,$date_exp_min, $date_exp_max,true);
        $fichier = xml_2_csv($xml_liste, 'atm_liste_carte.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Rat-1", $fichier);
        unset ($SESSION_VARS['choix_rapport']);
    }
    else if ($SESSION_VARS['choix_rapport'] == "atm_transaction"){

        $criteres = array(
            _("No client") => $num_client,
            _("Date début") => $date_deb,
            _("Date fin") =>$date_fin,
            _("Numéro carte") => $num_carte
        );

        if ($num_carte != null){
            $num_carte = str_replace(' ','',$num_carte);
        }

        $date_deb = php2pg($date_deb);
        $date_fin = php2pg($date_fin);
        $xml_transaction= xml_atm_transaction($criteres,$num_carte,$num_client,$date_deb, $date_fin,true);
        $fichier = xml_2_csv($xml_transaction, 'atm_transaction.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Rat-1", $fichier);
        unset ($SESSION_VARS['choix_rapport']);
    }
}

?>