<?php

/**
 * [806] Autorisation retrait
 *
 * Cette opération comprends les écrans :
 * - Acc-1 : Liste demande autorisation creation client
 * - Acc-2 : Confirmation autorisation creation client
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';

if ($global_nom_ecran == "Acc-1") {
  global $global_id_client;

  $my_page = new HTML_GEN2("Liste de demandes d'autorisation de création client");

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

  $my_page->addHTMLExtraCode("header_msg","<h3 align=\"center\" style=\"font:12pt arial;\">Veuillez s'il vous plaît cocher au moins une case par demande</h3><br/>");

  // Header row
  $my_page->addField("checkall_valid","<span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>N° Client</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Nom client </span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Statut juridique</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Date demande</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Voir profil</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Utilisateur</span>", TYPC_BOL);


  $jsBuildBol .= "
                    var objBolEl = document.getElementsByName('HTML_GEN_BOL_checkall_valid')[0];

                    objBolEl.setAttribute(\"class\", \"valid\");
                    objBolEl.setAttribute(\"alt\", \"Tous Autoriser\");
                    objBolEl.setAttribute(\"title\", \"Tous Autoriser\");
                    objBolEl.setAttribute(\"id\", \"checkall_valid\");
                    objBolEl.setAttribute(\"name\", \"checkall_valid\");
                    objBolEl.setAttribute(\"onclick\", \"checkAll(this)\");

                    var objTrEl = objBolEl.parentNode;

                    var objInputChk = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"checkall_rejet\" name=\"checkall_rejet\" class=\"rejet\" alt=\"Tous Refuser\" title=\"Tous Refuser\" onclick=\"checkAll(this)\">';

                    objTrEl.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl.innerHTML + objInputChk;
        ";

  // Get liste demande de retrait
  $listeDemandeCreationClient = getListeCreationClient();

  $displayHeader = true;
  foreach ($listeDemandeCreationClient as $id => $demandeCreationClient) {

    $id_demande = trim($demandeCreationClient["id_temp"]);
    switch ($demandeCreationClient['statut_juridique'])
    {
      case 1 :
        $nom_client = $demandeCreationClient['pp_nom'] . " " . $demandeCreationClient['pp_prenom'];
        break;
      case 2 :
        $nom_client = $demandeCreationClient['pm_raison_sociale'];
        break;
      case 3 :
        $nom_client = $demandeCreationClient['gi_nom'];
        break;
      case 4 :
        $nom_client = $demandeCreationClient['gi_nom'];
        break;
      default :
        signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Statut juridique inconnu !"
    }
    $statut_juridique = adb_gettext($adsys['adsys_stat_jur'][$demandeCreationClient["statut_juridique"]]);
    $date_demande = pg2phpDate($demandeCreationClient['date_crea']);
    $profil = "Voir Profil";
    $login = $demandeCreationClient["login_crea"];


    $details = '<a href = "#" onClick ="';
    $details .= "OpenBrw('$SERVER_NAME/modules/clients/consult_client_temp.php?id_client_temp=".$id_demande."');return false;";
    $details .= '" >Voir fiche cliente</a>';

    $libelle_demande = sprintf("<span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span><span style='width: 150px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>", $id_demande,$nom_client, $statut_juridique, $date_demande, $details,$login);

    $my_page->addField("check_valid_" . $id_demande, "$libelle_demande", TYPC_BOL);

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

                    var objInputChkRejet$id_demande = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"check_rejet_$id_demande\" name=\"check_rejet_$id_demande\" class=\"rejet\" alt=\"Refuser\" title=\"Refuser\" onclick=\"manageCheckbox(this, $id_demande)\" value=\"$id_demande\" value=\"$id_demande\">';

                    objTrEl$id_demande.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl$id_demande.innerHTML + objInputChkRejet$id_demande;
        ";

    if ($displayHeader == true) {
      $jsBuildBol .= "
                    var objBody$id_demande = objTrEl$id_demande.parentNode.parentNode;

                    objBody$id_demande.innerHTML = '<tr bgcolor=\"#FDF2A6\"><td align=\"left\"></td><td align=\"left\"> Autoriser <b>OU</b> Refuser</td><td align=\"left\"></td></tr>' + objBody$id_demande.innerHTML;
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

                        var bol_valid_checked = false;

                        var el_valid = document.getElementsByClassName('valid');
                        var el_rejet = document.getElementsByClassName('rejet');

                        var i;
                        for (i = 0; i < el_valid.length; i++) {
                            if (el_valid[i].checked) {
                                bol_valid_checked = true;
                                break;
                            }
                        }
                        for (i = 0; i < el_rejet.length; i++) {
                            if (el_rejet[i].checked) {
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

  $my_page->addJS(JSP_BEGIN_CHECK, "JS_VALID_BOL", $code_bol_js);

  $my_page->addHTMLExtraCode("espace","<br/>");

  $my_page->addFormButton(1, 1, "btn_process_demande", _("Valider"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("btn_process_demande", BUTP_PROCHAIN_ECRAN, 'Acc-2');
  $my_page->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

  $my_page->show();

}
elseif ($global_nom_ecran == "Acc-2") {

  global $global_id_client;

  $erreur = processAutorisationCreationClientAttente($_POST, $global_id_client);

  if ($erreur->errCode == NO_ERR) {

    // Affichage de la confirmation
    $html_msg = new HTML_message("Confirmation autorisation de creation client");

    if ($erreur->param > 1){
      $demande_msg = "demandes de creation client ont été traitées";
    } else {
      $demande_msg = "demande de creation client a été traitée";
    }

    $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $erreur->param, $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Gen-6');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html_err = new HTML_erreur("Echec lors de la demande autorisation de creation client.");

    $err_msg = $error[$erreur->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Gen-6');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

} else {
  signalErreur(__FILE__, __LINE__, __FUNCTION__);
  // _("L'écran $global_nom_ecran n'existe pas")
}