<?php
    /* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

    /**
     * Dépôt sur un compte d'épargne
     * @author Hassan Diallo
     * @author Olivier Luickx
     * @since 04/02/2002
     * @package Epargne
     **/

    require_once 'lib/dbProcedures/epargne.php';
    require_once 'lib/dbProcedures/credit_lcr.php';
    require_once 'modules/epargne/recu.php';
    require_once 'lib/dbProcedures/parametrage.php';
    require_once 'lib/dbProcedures/tireur_benef.php';
    require_once 'lib/misc/divers.php';
    require_once 'lib/html/FILL_HTML_GEN2.php';
    require_once 'lib/misc/VariablesSession.php';
    require_once 'modules/rapports/xml_devise.php';
    require_once 'lib/dbProcedures/billetage.php';
    require_once 'lib/dbProcedures/agence.php';
    require_once 'lib/dbProcedures/agency_banking.php';
    require_once "lib/html/HTML_menu_gen.php";


if ($global_nom_ecran == "Vis-1") {
    $MyMenu = new HTML_menu_gen(_("Visualisations"));
    if (isProfilAgent($global_nom_login) == TRUE) {
        $MyMenu->addItem(_("Visualisation des demandes d’approvisionnement/transfert pour agent"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Vad-1", 771, "$http_prefix/images/visualisation_transactions.gif");
    }
    else{
        $MyMenu->addItem(_("Visualisation des demandes d’approvisionnement/transfert des agents"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Vad-3", 771, "$http_prefix/images/visualisation_transactions.gif");
    }
    $MyMenu->addItem(_("Visualisation des transactions"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Vta-1", 781, "$http_prefix/images/visualisation_transactions.gif");
    if (isProfilAgent($global_nom_login) == TRUE) {
        $MyMenu->addItem(_("Visualisation des clients créés via agents"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Cva-2", 784, "$http_prefix/images/visualisation_transactions.gif");
    }
    else{
        $MyMenu->addItem(_("Visualisation de tous les clients créés via agent"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Cva-1", 784, "$http_prefix/images/visualisation_transactions.gif");
    }
    $MyMenu->addItem(_("Retour Menu Principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-16", 0, "$http_prefix/images/back.gif");
    $MyMenu->buildHTML();
    echo $MyMenu->HTMLCode;
}
 else if ($global_nom_ecran == "Vad-1") {
        global $global_nom_login;

        if ($date_min == "") $date_min = NULL;
        if ($date_max == "") $date_max = NULL;
        $SESSION_VARS['criteres'] = array();
        if (isProfilAgent($global_nom_login)){
              $login_agent = $global_nom_login;
            $SESSION_VARS['login']= $login_agent;
        }else {
            if (isset($login)) {
                $login_agent = $login;
                $SESSION_VARS['login']= $login_agent;
            } else if (empty($login)) {
                $login_agent = null;
                $SESSION_VARS['login'] = $login_agent;
            }
        }

        $SESSION_VARS['criteres']['login'] = (empty($login_agent)?'Tous':$login_agent);
        $SESSION_VARS['criteres']['date_min'] = $date_min;
        $SESSION_VARS['criteres']['date_max'] = $date_max;

        $nombre = count_recherche_transactions_agent($login_agent, $date_min, $date_max);
        $agent_dataset = get_approvisionnement_agent($login_agent,$date_min,$date_max);

        if ($nombre > 100) {
            $MyPage = new HTML_erreur(_("Trop de correspondances"));
            $MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche ou imprimer."),$nombre));
            $nextScreen = "Gen-16";
            $printScreen = "Vad-2";
//            $SESSION_VARS['login'] = $login_agent;
//            $SESSION_VARS['date_min'] = $date_min;
//            $SESSION_VARS['date_max'] = $date_max;
            $MyPage->addButton(BUTTON_OK, $nextScreen);
            $MyPage->addCustomButton("print", _("Imprimer"), $printScreen, TYPB_SUBMIT);
            $MyPage->buildHTML();
            echo $MyPage->HTML_code;
        } else {
//            $resultat = recherche_transactions($global_nom_login, $num_fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);

            $html = "<h1 align=\"center\">"._("Résultat recherche")."</h1><br><br>\n";
            $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
            $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

            //Ligne titre
            $html .= "<TR bgcolor=$colb_tableau>";

            $html .= "<TD align=\"center\"><b>"._("Date")."</b></TD><TD align=\"center\"><b>"._("Fonction")."</b></TD><TD align=\"center\"><b>"._("Etat")."</b></TD><TD align=\"center\"><b>"._("Login approbateur")."</b></TD><TD align=\"center\"><b>"._("Login initiateur")."</b></TD><TD align=\"center\"><b>"._("Montant")."</b></TD><TD align=\"center\"><b>"._("Compte de flotte")."</b></TD></TR>\n";

            while (list(,$value) = each($agent_dataset)) { //Pour chaque résultat
                //On alterne la couleur de fond
                if ($a) $color = $colb_tableau;
                else $color = $colb_tableau_altern;
                $a = !$a;
                $html .= "<TR bgcolor=$color>\n";

                //Date
                $html .= "<TD>".pg2phpDate($value['date_creation'])."</TD>";

                //Fonction
                $html .= "<TD>".adb_gettext($adsys["type_appro_agent"][$value['type_transaction']]);
                $html .= "</TD>\n";

                //etat
                $html .= "<TD>".adb_gettext($adsys["etat_appro_trans"][$value['etat_appro']]);
                $html .= "</TD>\n";

                //Login
                $html .= "<TD>".$value['login_util']."</TD>\n";

                //Login initiateur
                $html .= "<TD>".$value['login_agent']."</TD>\n";

                //Montant
                $html .= "<TD>".afficheMontant($value['montant'])."</TD>\n";

                //numero compte de flotte
                $html .= "<TD>".$value['num_cpte_flotte']."</TD>\n";
                $html .= "</TR>\n";
            }

            $html .= "<TR bgcolor=$colb_tableau><TD colspan=7 align=\"center\">\n";

            //Boutons
            $html .= "<TABLE align=\"center\"><TR>";

            $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Vad-2');\"></TD>";
            $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour menu")."\" onclick=\"ADFormValid=true; assign('Gen-16');\"></TD>";

            $html .= "</TR></TABLE>\n";

            $html .= "</TD></TR></TABLE>\n";
            $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

            echo $html;
        }
    }

    else if($global_nom_ecran == "Vad-2"){
        global $global_nom_login;


        $criteres = array(
          _("Agent") => $SESSION_VARS['criteres']['login'],
          _("Date min") => $SESSION_VARS['criteres']['date_min'],
          _("Date max") => $SESSION_VARS['criteres']['date_max']
        );

        $DATAS = get_approvisionnement_agent($SESSION_VARS['login'],$SESSION_VARS['criteres']['date_min'],$SESSION_VARS['criteres']['date_max']);
        $xml = xml_detail_transactions_agent($DATAS, $criteres);
        $rapport = xml_2_xslfo_2_pdf($xml, 'visualisation_transaction_agent.xslt');
        echo get_show_pdf_html('Gen-16', $rapport);
    }

    else if($global_nom_ecran == "Vad-3"){
        $MyPage = new HTML_GEN2(_("Critères de recherche"));

        $login_agent = isLoginAgent();
        $MyPage->addField("login", _("Login ayant exécuté la fonction"), TYPC_LSB);
        $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_AUCUN, false);
        $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_TOUS, true);
        $MyPage->setFieldProperties("login", FIELDP_ADD_CHOICES, $login_agent);

        //Champs date début
        $MyPage->addField("date_min", _("Date min"), TYPC_DTE);
        $MyPage->setFieldProperties("date_min", FIELDP_DEFAULT, date("01/01/Y"));

        //Champs date fin
        $MyPage->addField("date_max", _("Date max"), TYPC_DTE);
        $MyPage->setFieldProperties("date_max", FIELDP_DEFAULT, date("d/m/Y"));

        //Boutons
        $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
        $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
        $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Vad-1");
        $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");


        $MyPage->buildHTML();
        echo $MyPage->getHTML();
    }
?>