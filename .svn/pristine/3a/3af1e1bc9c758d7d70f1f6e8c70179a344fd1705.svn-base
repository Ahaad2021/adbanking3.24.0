<?php

/**
 * [765] Gestion des annulation retrait et dépôt via agent
 *  *
 * @package Annulation Retrait et Dépôt
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/annulation_retrait_depot.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/agency_banking.php';

require_once "lib/html/HTML_menu_gen.php";

/*{{{ Gdr-1 : Gestion des annulations retrait et dépôt via agent */
if ($global_nom_ecran == "Gdr-1") {

  //global $global_id_client;

  $MyMenu = new HTML_menu_gen("Gestion des annulations retrait et dépôt via agent");

  $MyMenu->addItem(_("Demande d'annulation"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ldr-1", 766, "$http_prefix/images/traitement_chq.gif", "1");

  $MyMenu->addItem(_("Approbation demande d'annulation"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Add-1", 767, "$http_prefix/images/approb_dossier.gif", "2");

  $MyMenu->addItem(_("Effectuer l'annulation"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Eda-1", 768, "$http_prefix/images/annulation.gif", "3");

  $MyMenu->addItem(_("Retour Menu Agent"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-16", 0, "$http_prefix/images/back.gif", "0");

  $MyMenu->buildHTML();

  echo $MyMenu->HTMLCode;

}
else if ($global_nom_ecran == "Ldr-1") {

  global $global_nom_login;

  $myPage = new HTML_GEN2("Liste des opérations retraits / dépôts via agent ".$global_nom_login."");

  $jsBuildBol = "
                    function checkAll(obj, className) {

                        var el = document.getElementsByClassName(className);

                        var i;
                        for (i = 0; i < el.length; i++) {
                            el[i].checked = obj.checked;
                        }

                        return false;
                    }
    ";

  $myPage->addHTMLExtraCode("header_msg","<h3 align=\"center\" style=\"font:12pt arial;\">Veuillez s'il vous plaît choisir au moins une opération à annuler</h3><br/>");

  // Header row
  $myPage->addField("checkall_valid", "<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>N° transaction </span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Opération</span><span style='width: 250px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Type</span><span style='width: 140px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Montant</span><span style='width: 180px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #007777;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;font-weight: bold;'>Commission Agent</span><span style='width: 180px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #007777;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;font-weight: bold;'>Commission Institution</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #007777;font-weight: bold;'>Date</span>", TYPC_BOL);

  $jsBuildBol .= "
                    var objBolEl = document.getElementsByName('HTML_GEN_BOL_checkall_valid')[0];

                    objBolEl.setAttribute(\"class\", \"checkall_valid\");
                    objBolEl.setAttribute(\"alt\", \"Tous Cocher\");
                    objBolEl.setAttribute(\"title\", \"Tous Cocher\");
                    objBolEl.setAttribute(\"id\", \"checkall_valid\");
                    objBolEl.setAttribute(\"name\", \"checkall_valid\");
                    objBolEl.setAttribute(\"onclick\", \"checkAll(this, 'valid')\");

                    var objTrEl = objBolEl.parentNode;

                    objTrEl.innerHTML = '<span style=\"padding-left: 1px;\">&nbsp;</span>' + objTrEl.innerHTML + '<span style=\"padding-left: 1px;\">&nbsp;</span>';
        ";

  // Get liste de retraits et dépôts du jour
  $listeOpeEpg = getListeOperationEpargneViaAgent($global_nom_login);
  $displayHeader = true;
  foreach ($listeOpeEpg as $id => $opeEpg) {
    $devise = $opeEpg["devise"];
    $mnt_agent = getFraisOpeViaAgent(trim($opeEpg["id_his"]),$opeEpg["type_operation"],$opeEpg["cpte_interne_cli"],622);
    $mnt_inst = getFraisOpeViaAgent(trim($opeEpg["id_his"]),$opeEpg["type_operation"],$opeEpg["cpte_interne_cli"],623);
    $comm_agent = afficheMontant($mnt_agent['montant'])." ".$devise;
    $comm_inst = afficheMontant($mnt_inst['montant'])." ".$devise;
    $id_trans = trim($opeEpg["id_his"]);
    $libel_fonc = $adsys["adsys_fonction_systeme"][$opeEpg["type_fonction"]];
    $libel_ope = getLibelOpeAgent($opeEpg["type_operation"]);
    $montant = afficheMontant($opeEpg["montant"])." ".$devise;
    $frais = afficheMontant($frais_ope["montant"])." ".$devise;
    $date_fonc = new DateTime($opeEpg["date"]);

    $libel_fonc = sprintf("<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 250px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 140px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 180px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 180px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;'>%s</span>", $id_trans, $libel_fonc, $libel_ope, $montant,$comm_agent,$comm_inst, $date_fonc->format("d/m/Y H:i"));

    $myPage->addField("check_valid_" . $id_trans, "$libel_fonc", TYPC_BOL);

    $jsBuildBol .= "
                    var objBolEl$id_trans = document.getElementsByName('HTML_GEN_BOL_check_valid_$id_trans')[0];

                    objBolEl$id_trans.setAttribute(\"class\", \"valid\");
                    objBolEl$id_trans.setAttribute(\"alt\", \"Cocher\");
                    objBolEl$id_trans.setAttribute(\"title\", \"Cocher\");
                    objBolEl$id_trans.setAttribute(\"value\", \"$id_trans\");
                    objBolEl$id_trans.setAttribute(\"id\", \"check_valid_$id_trans\");
                    objBolEl$id_trans.setAttribute(\"name\", \"check_valid_$id_trans\");

                    var objTrEl$id_trans = objBolEl$id_trans.parentNode;

                    objTrEl$id_trans.innerHTML = '<span style=\"padding-left: 1px;\">&nbsp;</span>' + objTrEl$id_trans.innerHTML + '<span style=\"padding-left: 1px;\">&nbsp;</span>';
        ";

    if ($displayHeader == true) {
      $jsBuildBol .= "
                    var objBody$id_trans = objTrEl$id_trans.parentNode.parentNode;

                    objBody$id_trans.innerHTML = '<tr bgcolor=\"#FDF2A6\"><td align=\"left\"></td><td align=\"left\"></td></tr>' + objBody$id_trans.innerHTML;
        ";
      $displayHeader = false;
    }
  }

  $jsBuildBol .= "
                    // Default check all Valid
                    //var bolCheckAll = document.getElementsByName('checkall_valid')[0];
                    //bolCheckAll.checked = true;
                    //checkAll(bolCheckAll, 'valid');
        ";

  $myPage->addJS(JSP_FORM, "JS_BUILD_BOL", $jsBuildBol);

  $code_bol_js = "
                      function validateBolFields() {

                        var bol_valid_checked = false;

                        var el_valid = document.getElementsByClassName('valid');

                        var i;
                        for (i = 0; i < el_valid.length; i++) {
                            if (el_valid[i].checked) {
                                bol_valid_checked = true;
                                break;
                            }
                        }

                        if (!bol_valid_checked) {
                            msg += '- Veuillez cocher au moins une case de demande \\n';
                            ADFormValid=false;
                        }
                      }
                      validateBolFields();
        ";

  $myPage->addJS(JSP_BEGIN_CHECK, "JS_VALID_BOL", $code_bol_js);

  $myPage->addHTMLExtraCode("espace","<br/>");

  $myPage->addFormButton(1, 1, "btn_process_demande", _("Valider"), TYPB_SUBMIT);
  $myPage->setFormButtonProperties("btn_process_demande", BUTP_PROCHAIN_ECRAN, 'Ldr-2');
  $myPage->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
  $myPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gdr-1");
  $myPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

  $myPage->show();

}
elseif ($global_nom_ecran == "Ldr-2") {

  global $global_id_client, $global_nom_login;

  $erreur = processOperationEpargneViaAgent($_POST, $global_nom_login);

  if ($erreur->errCode == NO_ERR) {

    // Affichage de la confirmation
    $html_msg = new HTML_message("Confirmation demande annulation");

    if ($erreur->param > 1){
      $demande_msg = "demandes ont été enregistrées";
    } else {
      $demande_msg = "demande a été enregistrée";
    }

    $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $erreur->param, $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html_err = new HTML_erreur("Echec lors de la demande enregistrement d'annulation de retrait / dépôt.");

    $err_msg = $error[$erreur->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Gen-16');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
else if($global_nom_ecran == "Add-1"){
    global $global_id_client;

    $myPage = new HTML_GEN2("Liste des demandes d'annulation via agent");

    $jsBuildBol = "
                    function manageCheckbox(obj, chk_num) {

                        // Uncheck all
                        if (obj.checked) {
                            var valid = document.getElementsByName('check_valid_' + chk_num)[0].checked = false;
                            var rejet = document.getElementsByName('check_rejet_' + chk_num)[0].checked = false;
                        }

                        obj.checked = !obj.checked;

                        return false;
                    }

                    function checkAll(obj) {

                        if (obj.className == 'rejet' && obj.checked) {
                            var el = document.getElementsByClassName('valid');

                            var i;
                            for (i = 0; i < el.length; i++) {
                                el[i].checked = false;
                            }
                        }
                        else if (obj.className == 'valid' && obj.checked) {
                            var el = document.getElementsByClassName('rejet');

                            var i;
                            for (i = 0; i < el.length; i++) {
                                el[i].checked = false;
                            }
                        }

                        var el = document.getElementsByClassName(obj.className);

                        var i;
                        for (i = 0; i < el.length; i++) {
                            el[i].checked = obj.checked;
                        }

                        return false;
                    }
    ";

    $myPage->addHTMLExtraCode("header_msg","<h3 align=\"center\" style=\"font:12pt arial;\">Veuillez s'il vous plaît cocher au moins une case par demande</h3><br/>");

    // Header row
    $myPage->addField("checkall_valid", "<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Login </span><span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Client</span><span style='width: 250px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Type</span><span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Montant</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Comm. agent</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Comm. institution</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #007777;font-weight: bold;'>Date</span>", TYPC_BOL);

    $jsBuildBol .= "
                    var objBolEl = document.getElementsByName('HTML_GEN_BOL_checkall_valid')[0];

                    objBolEl.setAttribute(\"class\", \"valid\");
                    objBolEl.setAttribute(\"alt\", \"Tous Autoriser\");
                    objBolEl.setAttribute(\"title\", \"Tous Autoriser\");
                    objBolEl.setAttribute(\"id\", \"checkall_valid\");
                    objBolEl.setAttribute(\"name\", \"checkall_valid\");
                    objBolEl.setAttribute(\"onclick\", \"checkAll(this)\");

                    var objTrEl = objBolEl.parentNode;

                    var objInputChk = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"checkall_rejet\" name=\"checkall_rejet\" class=\"rejet\" alt=\"Tous Rejeter\" title=\"Tous Rejeter\" onclick=\"checkAll(this)\">';

                    objTrEl.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl.innerHTML + objInputChk;
        ";

    // Get liste des demandes d'annulation
    $listeDemandeAnnulation = getListeDemandeAnnulationViaAgent();

    $displayHeader = true;
    foreach ($listeDemandeAnnulation as $id => $demandeAnnule) {
        $id_demande = trim($demandeAnnule["id"]);
        $login = trim($demandeAnnule["login"]);
        $libel_fonc = getLibelFoncAgent($demandeAnnule["fonc_sys"]);
        $libel_ope = getLibelOpeAgent($demandeAnnule["type_ope"]);
        $devise = $demandeAnnule["devise"];
        $montant = afficheMontant($demandeAnnule["montant"])." ".$devise;
        $date_demande = new DateTime($demandeAnnule["date_crea"]);
        $comm_agent = afficheMontant($demandeAnnule["commission_agent"])." ".$devise;
        $comm_inst = afficheMontant($demandeAnnule["commission_inst"])." ".$devise;
        $client = $demandeAnnule['id_client'];
        
        $libelle_demande = sprintf("<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 250px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;'>%s</span>", $login, $client, $libel_ope, $montant,$comm_agent,$comm_inst, $date_demande->format("d/m/Y H:i"));

        $myPage->addField("check_valid_" . $id_demande, "$libelle_demande", TYPC_BOL);

        $jsBuildBol .= "
                    var objBolEl$id_demande = document.getElementsByName('HTML_GEN_BOL_check_valid_$id_demande')[0];

                    objBolEl$id_demande.setAttribute(\"class\", \"valid\");
                    objBolEl$id_demande.setAttribute(\"alt\", \"Autoriser\");
                    objBolEl$id_demande.setAttribute(\"title\", \"Autoriser\");
                    objBolEl$id_demande.setAttribute(\"value\", \"$id_demande\");
                    objBolEl$id_demande.setAttribute(\"id\", \"check_valid_$id_demande\");
                    objBolEl$id_demande.setAttribute(\"name\", \"check_valid_$id_demande\");
                    objBolEl$id_demande.setAttribute(\"onclick\", \"manageCheckbox(this, $id_demande)\");

                    var objTrEl$id_demande = objBolEl$id_demande.parentNode;

                    var objInputChkRejet$id_demande = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"check_rejet_$id_demande\" name=\"check_rejet_$id_demande\" class=\"rejet\" alt=\"Rejeter\" title=\"Rejeter\" onclick=\"manageCheckbox(this, $id_demande)\" value=\"$id_demande\" value=\"$id_demande\">';

                    objTrEl$id_demande.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl$id_demande.innerHTML + objInputChkRejet$id_demande;
        ";

        if ($displayHeader == true) {
            $jsBuildBol .= "
                    var objBody$id_demande = objTrEl$id_demande.parentNode.parentNode;

                    objBody$id_demande.innerHTML = '<tr bgcolor=\"#FDF2A6\"><td align=\"left\"></td><td align=\"left\"> Autoriser <b>OU</b> Rejeter</td><td align=\"left\"></td></tr>' + objBody$id_demande.innerHTML;
        ";
            $displayHeader = false;
        }
    }

    $jsBuildBol .= "
                    // Default check all Valid
                    var bolCheckAll = document.getElementsByName('checkall_valid')[0];
                    bolCheckAll.checked = true;
                    checkAll(bolCheckAll);
        ";

    $myPage->addJS(JSP_FORM, "JS_BUILD_BOL", $jsBuildBol);

    $code_bol_js = "
                      function validateBolFields() {

                        var bol_valid_rejet_checked = false;

                        var el_valid = document.getElementsByClassName('valid');
                        var el_rejet = document.getElementsByClassName('rejet');

                        var i;
                        for (i = 0; i < el_valid.length; i++) {
                            if (el_valid[i].checked) {
                                bol_valid_rejet_checked = true;
                                break;
                            }
                        }
                        for (i = 0; i < el_rejet.length; i++) {
                            if (el_rejet[i].checked) {
                                bol_valid_rejet_checked = true;
                                break;
                            }
                        }

                        if (!bol_valid_rejet_checked) {
                            msg += '- Veuillez cocher au moins une case de demande \\n';
                            ADFormValid=false;
                        }
                      }
                      validateBolFields();
        ";

    $myPage->addJS(JSP_BEGIN_CHECK, "JS_VALID_BOL", $code_bol_js);

    $myPage->addHTMLExtraCode("espace","<br/>");

    $myPage->addFormButton(1, 1, "btn_process_approbation", _("Valider"), TYPB_SUBMIT);
    $myPage->setFormButtonProperties("btn_process_approbation", BUTP_PROCHAIN_ECRAN, 'Add-2');
    $myPage->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
    $myPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gdr-1");
    $myPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    $myPage->show();
}
else if($global_nom_ecran == "Add-2"){

    global $global_id_client;

    $erreur = processDemandeAnnulationAgent($_POST);

    if ($erreur->errCode == NO_ERR) {

        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation approbation annulation");

        if ($erreur->param > 1){
            $demande_msg = "demandes ont été traitées";
        } else {
            $demande_msg = "demande a été traitée";
        }

        $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $erreur->param, $demande_msg));

        $html_msg->addButton("BUTTON_OK", 'Gen-16');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    } else {
        $html_err = new HTML_erreur("Echec lors de la demande autorisation d'annulation de retrait / dépôt.");

        $err_msg = $error[$erreur->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Gen-16');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }
}
else if($global_nom_ecran == "Eda-1"){
  global $global_id_client;

  // Affichage de la liste des annulations autorisées
  $table = new HTML_TABLE_table(7, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste des demandes d'annulations autorisées");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Opération"));
  $table->add_cell(new TABLE_cell("Type"));
  $table->add_cell(new TABLE_cell("Montant"));
  $table->add_cell(new TABLE_cell("Comm. agent"));
  $table->add_cell(new TABLE_cell("Comm. institution"));
  $table->add_cell(new TABLE_cell(""));

  $dem_autorise = 2;

  // Get liste des demandes d'annulations autorisées
  $listeDemandeAnnulationAutorise = getListeDemandeAnnulationViaAgent(null,$dem_autorise);

  foreach ($listeDemandeAnnulationAutorise as $id => $annulationAutorise) {

    $id_demande = trim($annulationAutorise["id"]);
    $libel_fonc = getLibelFoncAgent($annulationAutorise["fonc_sys"]);
    $libel_ope = getLibelOpeAgent($annulationAutorise["type_ope"]);
    $devise = $annulationAutorise["devise"];
    $montant = afficheMontant($annulationAutorise["montant"])." ".$devise;
    $comm_agent = afficheMontant($annulationAutorise["commission_agent"])." ".$devise;
    $comm_inst = afficheMontant($annulationAutorise["commission_inst"])." ".$devise;

    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($libel_fonc));
    $table->add_cell(new TABLE_cell($libel_ope));
    $table->add_cell(new TABLE_cell($montant));
    $table->add_cell(new TABLE_cell($comm_agent));
    $table->add_cell(new TABLE_cell($comm_inst));
    $table->add_cell(new TABLE_cell("<a href=\"javascript:void(0);\" onclick=\"return submitFormData($id_demande);\">Effectuer l'annulation</a>"));
    $table->set_row_property("height","35px");
  }

  // Génération du tableau des demandes d'annulations autorisées
  echo $table->gen_HTML();

  $myPage = new HTML_GEN2("");

  $myPage->addHiddenType("hdd_id_demande");

  $code_js = "
                  function submitFormData(id) {

                      if (confirm(\"Annuler l'opération ?\")) {
                            document.ADForm.hdd_id_demande.value = id;
                            document.ADForm.prochain_ecran.value = 'Eda-2';
                            if(document.ADForm.m_agc) {
                                document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';
                            }

                            document.ADForm.submit();
                      }

                      return false;
                  }
        ";

  $myPage->addJS(JSP_FORM, "JS_CODE", $code_js);

  $myPage->show();

}
elseif ($global_nom_ecran == "Eda-2") {

  global $global_id_client;

  $erreur = processApprobationAnnulationAgent($_POST['hdd_id_demande'], $global_id_client);

  if ($erreur->errCode == NO_ERR) {

    // Affichage de la confirmation
    $html_msg = new HTML_message("Confirmation annulation");

    $demande_msg = "L'annulation a été effectuée !";

    $html_msg->setMessage($demande_msg);

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html_err = new HTML_erreur("Echec lors de l'annulation de retrait / dépôt.");

    $err_msg = $error[$erreur->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Gen-16');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
else {
  signalErreur(__FILE__, __LINE__, __FUNCTION__);
  // _("L'écran $global_nom_ecran n'existe pas")
}
?>
