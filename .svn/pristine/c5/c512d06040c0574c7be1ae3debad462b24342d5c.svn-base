<?php

/**
 * [167] Traitement des chèques ordinaires mis en attente
 *
 * Cette opération comprends les écrans :
 * - Pom-1 : Traiter les chèques ordinaires mis en attente
 * - Pom-2 : Confirmation traitement des chèques ordinaires mis en attente
 * - Pom-3 : Traitement individuel des chèques ordinaires
 *
 * @package Chèques certifiés
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/cheque_interne.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';

if ($global_nom_ecran == "Pom-1") {

    // Affichage de la confirmation
    $html_msg = new HTML_message("Traitement des chèques ordinaires mis en attente");

    $html_msg->setMessage("<br />Êtes-vous sûr de vouloir traiter des chèques ordinaires mis en attente ?<br />");

    $html_msg->addCustomButton("btn_process_validate", _("Valider"), 'Pom-2');
    $html_msg->addCustomButton("btn_process_reject", _("Rejeter"), 'Pom-2');
    $html_msg->addCustomButton("btn_process_other", _("Valider et/ou Rejeter"), 'Pom-3');
    $html_msg->addCustomButton("btn_annuler", _("Annuler"), 'Tcc-1');

    $html_msg->buildHTML();

    echo $html_msg->HTML_code;

} elseif ($global_nom_ecran == "Pom-2") {

    /*require_once ('lib/misc/debug.php');
    print_rn($_POST);die;*/

    $erreur = ChequeCertifie::processChequeCompensationOrdinaireMiseEnAttente($_POST);

    if ($erreur->errCode == NO_ERR) {

        $dbHandler->closeConnection(true);

        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation traitement des chèques ordinaires mis en attente");

        if ($erreur->param > 1) {
            $chq_msg = "chèque(s) ordinaire(s) a/ont été traité(s)";
        } elseif ($erreur->param == 1) {
            $chq_msg = "chèque ordinaire a été traité";
        } else {
            $erreur->param = "";
            $chq_msg = "Aucun chèque ordinaire traité";
        }

        $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $erreur->param, $chq_msg));

        $html_msg->addButton("BUTTON_OK", 'Tcc-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    } else {
        $html_err = new HTML_erreur("Echec lors du traitement des chèques ordinaires mis en attente.");

        $err_msg = $error[$erreur->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Tcc-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }

} elseif ($global_nom_ecran == "Pom-3") {

    // Nous avons des chèquiers à imprimer
    $my_page = new HTML_GEN2("Liste de chèques ordinaires mis en attente");

    // Get liste chèques ordinaires mis en attente
    $listeChequeOrdinaire = ChequeCertifie::getListeChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_MIS_EN_ATTENTE);

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

    $my_page->addHTMLExtraCode("header_msg","<h3 align=\"center\" style=\"font:12pt arial;\">Veuillez s'il vous plaît cocher au moins une case par chèque : </h3><br/>");

    // Check all field
    $my_page->addField("checkall_valid", "", TYPC_BOL);

    $jsBuildBol .= "
                    var objBolEl = document.getElementsByName('HTML_GEN_BOL_checkall_valid')[0];

                    objBolEl.setAttribute(\"class\", \"valid\");
                    objBolEl.setAttribute(\"alt\", \"Tous Valider\");
                    objBolEl.setAttribute(\"title\", \"Tous Valider\");
                    objBolEl.setAttribute(\"id\", \"checkall_valid\");
                    objBolEl.setAttribute(\"name\", \"checkall_valid\");
                    objBolEl.setAttribute(\"onclick\", \"checkAll(this)\");

                    var objTrEl = objBolEl.parentNode;

                    var objInputChk = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"checkall_rejet\" name=\"checkall_rejet\" class=\"rejet\" alt=\"Tous Rejeter\" title=\"Tous Rejeter\" onclick=\"checkAll(this)\">';

                    objTrEl.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl.innerHTML + objInputChk;
        ";

    $displayHeader = true;
    foreach ($listeChequeOrdinaire as $id => $chqOrdinaire) {

        $id_chq_compensation = trim($chqOrdinaire["id"]);
        $num_cheque = trim($chqOrdinaire["num_cheque"]);

        $libelle = sprintf("No. chèque : %s ", $num_cheque);

        $my_page->addField("check_valid_" . $num_cheque, _("$libelle"), TYPC_BOL);

        $jsBuildBol .= "
                    var objBolEl$num_cheque = document.getElementsByName('HTML_GEN_BOL_check_valid_$num_cheque')[0];

                    objBolEl$num_cheque.setAttribute(\"class\", \"valid\");
                    objBolEl$num_cheque.setAttribute(\"alt\", \"Valider\");
                    objBolEl$num_cheque.setAttribute(\"title\", \"Valider\");
                    objBolEl$num_cheque.setAttribute(\"value\", \"$num_cheque\");
                    objBolEl$num_cheque.setAttribute(\"id\", \"check_valid_$num_cheque\");
                    objBolEl$num_cheque.setAttribute(\"name\", \"check_valid_$num_cheque\");
                    objBolEl$num_cheque.setAttribute(\"onclick\", \"manageCheckbox(this, $num_cheque)\");

                    var objTrEl$num_cheque = objBolEl$num_cheque.parentNode;

                    var objInputChkRejet$num_cheque = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"check_rejet_$num_cheque\" name=\"check_rejet_$num_cheque\" class=\"rejet\" alt=\"Rejeter\" title=\"Rejeter\" onclick=\"manageCheckbox(this, $num_cheque)\" value=\"$num_cheque\" value=\"$num_cheque\">';

                    objTrEl$num_cheque.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl$num_cheque.innerHTML + objInputChkRejet$num_cheque;
        ";

        if ($displayHeader == true) {
            $jsBuildBol .= "
                    var objBody$num_cheque = objTrEl$num_cheque.parentNode.parentNode;

                    objBody$num_cheque.innerHTML = '<tr bgcolor=\"#FDF2A6\"><td align=\"left\"></td><td align=\"left\"> Valider OU Rejeter</td><td align=\"left\"></td></tr>' + objBody$num_cheque.innerHTML;
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

    $my_page->addJS(JSP_FORM, "JS_BUILD_BOL", $jsBuildBol);

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
                            msg += '- Veuillez cocher au moins une case \\n';
                            ADFormValid=false;
                        }
                      }
                      validateBolFields();
        ";

    $my_page->addJS(JSP_BEGIN_CHECK, "JS_VALID_BOL", $code_bol_js);

    $my_page->addHTMLExtraCode("espace","<br/>");

    $my_page->addFormButton(1, 1, "btn_process_other_validate", _("Traiter"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("btn_process_other_validate", BUTP_PROCHAIN_ECRAN, 'Pom-2');
    $my_page->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Tcc-1");
    $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    $my_page->show();

} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}