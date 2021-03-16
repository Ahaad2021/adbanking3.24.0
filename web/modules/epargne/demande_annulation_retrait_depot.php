<?php

/**
 * [61] Demande annulation retrait et dépôt
 *
 * Cette opération comprends les écrans :
 * - Dae-1 : Liste des opérations retraits / dépôts du jour
 * - Dae-2 : Confirmation demande retrait / dépôt
 *
 * @package Annulation Retrait et Dépôt
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/annulation_retrait_depot.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';


if ($global_nom_ecran == "Dae-1") {

    global $global_id_client;

    $myPage = new HTML_GEN2("Liste des opérations retraits / dépôts");

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
    $myPage->addField("checkall_valid", "<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>N° transaction </span><span style='width: 100px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Opération</span><span style='width: 250px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Type</span><span style='width: 140px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Montant</span><span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #007777;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;font-weight: bold;'>Frais</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #007777;font-weight: bold;'>Date</span>", TYPC_BOL);

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
    $listeOpeEpg = AnnulationRetraitDepot::getListeOperationEpargne($global_id_client);
    $displayHeader = true;
    foreach ($listeOpeEpg as $id => $opeEpg) {
        $frais_ope = AnnulationRetraitDepot::getFraisOpe(trim($opeEpg["id_his"]),$opeEpg["type_operation"],$opeEpg["cpte_interne_cli"]);
        $id_trans = trim($opeEpg["id_his"]);
        $libel_fonc = AnnulationRetraitDepot::getLibelFonc($opeEpg["type_fonction"]);
        $libel_ope = AnnulationRetraitDepot::getLibelOpe($opeEpg["type_operation"]);
        $devise = $opeEpg["devise"];
        $montant = afficheMontant($opeEpg["montant"])." ".$devise;
        $frais = afficheMontant($frais_ope["montant"])." ".$devise;
        $date_fonc = new DateTime($opeEpg["date"]);

        $libel_fonc = sprintf("<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 100px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 250px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 140px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;'>%s</span>", $id_trans, $libel_fonc, $libel_ope, $montant,$frais, $date_fonc->format("d/m/Y H:i"));

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
    $myPage->setFormButtonProperties("btn_process_demande", BUTP_PROCHAIN_ECRAN, 'Dae-2');
    $myPage->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
    $myPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gae-1");
    $myPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    $myPage->show();

} elseif ($global_nom_ecran == "Dae-2") {

    global $global_id_client;

    $erreur = AnnulationRetraitDepot::processOperationEpargne($_POST, $global_id_client);

    if ($erreur->errCode == NO_ERR) {

        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation demande annulation");

        if ($erreur->param > 1){
            $demande_msg = "demandes ont été enregistrées";
        } else {
            $demande_msg = "demande a été enregistrée";
        }

        $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $erreur->param, $demande_msg));

        $html_msg->addButton("BUTTON_OK", 'Gae-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    } else {
        $html_err = new HTML_erreur("Echec lors de la demande enregistrement d'annulation de retrait / dépôt.");

        $err_msg = $error[$erreur->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Gae-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }

} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}