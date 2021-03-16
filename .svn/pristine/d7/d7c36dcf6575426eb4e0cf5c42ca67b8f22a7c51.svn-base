<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/dbProcedures/agence.php';
require_once('lib/misc/cryptage.php');
require_once('lib/misc/password_encrypt_decrypt.php');

/*{{{ Gli-1 : Ecran principal */
if ($global_nom_ecran == "Gli-1") {
  $MyPage = new HTML_GEN2(_("Gestion de la licence"));
  
  unset($SESSION_VARS['licence']);

  $InfosLicence = getCurrentLicenceInfo($global_id_agence);

  $InfosAgence = getAgenceDatas($global_id_agence);

  $MyPage->addField("libel_instit", _("Libellé de l'agence"), TYPC_TXT);
  $MyPage->setFieldProperties("libel_instit", FIELDP_DEFAULT, $InfosAgence['libel_institution']);
  $MyPage->setFieldProperties("libel_instit", FIELDP_IS_LABEL, true);

  $MyPage->addField("date_crea", _("Date création"), TYPC_DTE);
  $MyPage->setFieldProperties("date_crea", FIELDP_DEFAULT, pg2phpDate($InfosLicence['date_creation']));
  $MyPage->setFieldProperties("date_crea", FIELDP_IS_LABEL, true);

  $MyPage->addField("date_exp", _("Date d'expiration"), TYPC_DTE);
  $MyPage->setFieldProperties("date_exp", FIELDP_DEFAULT, pg2phpDate($InfosLicence['date_expiration']));
  $MyPage->setFieldProperties("date_exp", FIELDP_IS_LABEL, true);

  $MyPage->addField("licence_jours_alerte", _("Nombre de jours avant la date d'expiration<br />à partir duquel l'alerte est donnée"), TYPC_INT);
  $MyPage->setFieldProperties("licence_jours_alerte", FIELDP_DEFAULT, $InfosAgence['licence_jours_alerte']);
  $MyPage->setFieldProperties("licence_jours_alerte", FIELDP_IS_LABEL, true);

  $MyPage->addHTMLExtraCode("htm1", "<br />");

  $MyPage->addFormButton(1, 1, "update", _("Mise à jour de la licence"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);

  $MyPage->setFormButtonProperties("update", BUTP_PROCHAIN_ECRAN, 'Gli-2');
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-7');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Gli-2 : Ecran de mise à jour de la licence */
else if ($global_nom_ecran == "Gli-2") {
  $MyPage = new HTML_GEN2(_("Mise à jour de la licence"));

  if (file_exists($licence) && file_exists($date_licence)) {

    // Encryption key
    $crypte_key = "adbankingpublic";

    // Récupéré le chemin physique du fichier
    preg_match("/(.*)\/modules\/systeme\/gestion_licence\.php/",__FILE__,$doc_prefix);
    $doc_prefix = $doc_prefix[1];

    $has_old_licence = false;

    $curr_licence2_path = "$doc_prefix/licence2.txt";

    // Vérification de l'existence du fichier licence2.txt
    if (file_exists($curr_licence2_path)) {
        $curr_crypte_text = file_get_contents($curr_licence2_path);
        $curr_decrypte_arr = unserialize(Decrypte($curr_crypte_text, $crypte_key));

        if (trim($curr_decrypte_arr[3])=='n') {
            $has_old_licence = true;
        }
    }

    // Mise a jour de la table Liste des IMF avec le mot de passe de la licence : AT-31
    if (isMultiAgence()) {
      $db = $dbHandler->openConnection();
      $liste_agences=getListeAgences(true);
      foreach($liste_agences as $key_agence => $value_agence){
        $password = trim($value_agence['app_db_host']).'_'.trim($value_agence['app_db_name']);
        $Fields_multiagence['app_db_password'] = phpseclib_Encrypt($curr_decrypte_arr[9], $password);
        $Where_multiagence["id_mag"] = $value_agence['id_mag'];
        $sql = buildUpdateQuery("adsys_multi_agence", $Fields_multiagence, $Where_multiagence);

        $result = executeDirectQuery($sql);
        if ($result->errCode != NO_ERR){
          $dbHandler->closeConnection(false);
          return new ErrorObj($result->errCode);
        }
      }
      $dbHandler->closeConnection(true);
    }
    $crypte_text = file_get_contents($date_licence);
    $decrypte_arr = unserialize(Decrypte($crypte_text, $crypte_key));

    // Check/set agence restriction
    $acces_allowed = false;
    if(isset($decrypte_arr[3]) && trim($decrypte_arr[3]) != '' && trim($decrypte_arr[3]) != 'n') {

        // Ouvrir une connexion
        $db = $dbHandler->openConnection();

        // Get agence code institution
        $sql_request = "SELECT licence_key, licence_code_identifier, id_ag FROM ad_agc WHERE id_ag=".getNumAgence();

        $result_request = $db->query($sql_request);

        if (!DB::isError($result_request)) {

            $row_request = $result_request->fetchrow(DB_FETCHMODE_ASSOC);

                $licence_key = trim($row_request['licence_key']);
                $db_licence_code_identifier = trim($row_request['licence_code_identifier']);
                $id_ag = trim($row_request['id_ag']);

                $lic_code_ident_arr = explode("-", trim($decrypte_arr[3]));
                $code_banque = trim($lic_code_ident_arr[0]);
                $license_exp_yr = trim($lic_code_ident_arr[1]);

                $str_to_crypt = sprintf("%s-%s", $code_banque, $license_exp_yr);
                $new_licence_code_identifier = crypt($str_to_crypt);

                if ((empty($licence_key) && empty($db_licence_code_identifier) && $has_old_licence) ||
                    (sha1($code_banque) === $licence_key && !$has_old_licence))
                {
                    // Update sql
                    if (empty($licence_key) && $has_old_licence) {
                        $Fields['licence_key'] = sha1($code_banque);
                    }
                    $Fields['licence_code_identifier'] = trim($new_licence_code_identifier);
                    $Where["id_ag"] = $global_id_agence;

                    $sql = buildUpdateQuery("ad_agc", $Fields, $Where);

                $result = executeDirectQuery($sql);
                if ($result->errCode != NO_ERR){
                    $dbHandler->closeConnection(false);
                    return new ErrorObj($result->errCode);
                }

                $acces_allowed = true;
            }

            $dbHandler->closeConnection(true);
        } else {
            $dbHandler->closeConnection(false);
        }
    }
    
    if($acces_allowed) {

        $date_crea = pg2phpDate($decrypte_arr[0]);
        $date_exp = pg2phpDate($decrypte_arr[1]);

        $MyErr = checkLicenceValidity($date_exp);

        if ($MyErr->errCode == NO_ERR) {

            // Move ionCube encoder license on server
            $filename = $licence.".tmp";
            move_uploaded_file($licence, $filename);
            $SESSION_VARS['licence'] = $filename;

            // Move date/mode license on server
            $filename2 = $date_licence.".tmp";
            move_uploaded_file($date_licence, $filename2);
            $SESSION_VARS['licence2'] = $filename2;

          if(file_exists($SESSION_VARS['licence'])) {
            $erreur = setNewLicence($date_crea, $date_exp, $filename, $filename2);
          }
          if ($erreur->errCode == NO_ERR) {

            $InfosAgence = getAgenceDatas($global_id_agence);

            $MyPage->addField("libel_instit", _("Libellé de l'agence"), TYPC_TXT);
            $MyPage->setFieldProperties("libel_instit", FIELDP_DEFAULT, $InfosAgence['libel_institution']);
            $MyPage->setFieldProperties("libel_instit", FIELDP_IS_LABEL, true);

            $MyPage->addField("date_crea_label", _("Date création"), TYPC_DTF);
            $MyPage->setFieldProperties("date_crea_label", FIELDP_DEFAULT, $date_crea);
            $MyPage->setFieldProperties("date_crea_label", FIELDP_IS_LABEL, true);
            $MyPage->addHiddenType("date_crea", $date_crea);

            $MyPage->addField("date_exp_label", _("Date d'expiration"), TYPC_DTF);
            $MyPage->setFieldProperties("date_exp_label", FIELDP_DEFAULT, $date_exp);
            $MyPage->setFieldProperties("date_exp_label", FIELDP_IS_LABEL, true);
            $MyPage->addHiddenType("date_exp", $date_exp);

            $htm2 = "<BR/>";

            $MyPage->addHTMLExtraCode("htm2", $htm2);

            $MyPage->addFormButton(1, 1, "valider", _("Mettre à jour"), TYPB_SUBMIT);
            $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gli-3');
            $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, false);
          }

        } else {
          $html_err = new HTML_erreur(_("Echec de mise à jour de la licence"));
          $html_err->setMessage(_("Erreur")." : "._("La date d'expiration de la nouvelle licence n'est pas valide"));
          $html_err->addButton("BUTTON_OK", 'Gli-2');
          $html_err->buildHTML();
          echo $html_err->HTML_code;
          exit;
        }
    }
    else
    {
        $html_err = new HTML_erreur(_("Echec de mise à jour de la licence"));
        $html_err->setMessage(_("Erreur")." : Votre licence n'est pas valide");
        $html_err->addButton("BUTTON_OK", 'Gli-2');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit;
    }
    
  } else {
      
    $htm1 = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td width=\"300px\">&nbsp;</td><td align=\"right\">"._("Fichier licence")."<font color=\"#FF0000\" face=\"HELVETICA\" size=\"4\"><b>*</b></font> : </td><td><INPUT id=\"licence\" name=\"licence\" type=\"file\" /></td></tr><tr><td width=\"300px\">&nbsp;</td><td align=\"right\">&nbsp;</td><td>&nbsp;</td></tr><tr><td width=\"300px\">&nbsp;</td><td align=\"right\">"._("Fichier licence 2")."<font color=\"#FF0000\" face=\"HELVETICA\" size=\"4\"><b>*</b></font> : </td><td><INPUT id=\"date_licence\" name=\"date_licence\" type=\"file\" /></td></tr><tr><td width=\"300px\">&nbsp;</td><td align=\"right\">&nbsp;</td><td>&nbsp;</td></tr></table>";

    $MyPage->addHTMLExtraCode("htm1", $htm1);
    
    $validateUploadLicence = "var err_msg=''; if (document.ADForm.licence.value.substring(document.ADForm.licence.value.lastIndexOf('.')-7) != 'licence.txt') { err_msg = err_msg + '" . _("- Champ \'Fichier licence\' : Choisissez le fichier \'licence.txt\'") . "'; } if (document.ADForm.date_licence.value.substring(document.ADForm.date_licence.value.lastIndexOf('.')-8) != 'licence2.txt') { err_msg = err_msg + '" . _("\\n- Champ \'Fichier licence 2\' : Choisissez le fichier \'licence2.txt\'") . "'; } if(err_msg != '') { alert(err_msg); return false; }";

    $MyPage->addFormButton(1, 1, "valider", _("Télécharger la nouvelle licence"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gli-2');
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $validateUploadLicence));

    $SESSION_VARS['licence'] = NULL;
  }

  $MyPage->addFormButton(1, 2, "precedent", _("Retour"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

  $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Gli-1');
  $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Gli-3 : Ecran de confirmation de mise à jour de la licence */
else if ($global_nom_ecran == 'Gli-3') {

  //error_reporting(E_ALL);
  //ini_set("display_errors", "on");


  if ($erreur->errCode == NO_ERR) {

    unset($SESSION_VARS['licence']);

    $html_msg = new HTML_message(_("Confirmation de mise à jour de la licence"));
    $html_msg->setMessage(_("La licence a correctement été mise à jour"));
    $html_msg->addButton("BUTTON_OK", 'Gen-7');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Echec de mise à jour de la licence"));
    $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]);
    $html_err->addButton("BUTTON_OK", 'Gli-2');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

/*{{{ Gli-4 : Ecran de création d'un fichier de demande de licence */
else if ($global_nom_ecran == 'Gli-4') {

    /*
    $MyPage = new HTML_GEN2(_("Modification des données de la licence actuelle"));
    
    unset($SESSION_VARS['licence']);

    $InfosAgence = getAgenceDatas($global_id_agence);
    $InfosLicence = getCurrentLicenceInfo($global_id_agence);

    $MyPage->addField("libel_instit", _("Libellé de l'agence"), TYPC_TXT);
    $MyPage->setFieldProperties("libel_instit", FIELDP_DEFAULT, $InfosAgence['libel_institution']);
    $MyPage->setFieldProperties("libel_instit", FIELDP_IS_LABEL, true);

    $MyPage->addField("date_crea", _("Date création"), TYPC_DTE);
    $MyPage->setFieldProperties("date_crea", FIELDP_HAS_CALEND, false);
    $MyPage->setFieldProperties("date_crea", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("date_crea", FIELDP_DEFAULT, pg2phpDate($InfosLicence['date_creation']));
    $MyPage->addLink("date_crea", "calendrier_date_crea", _("Calendrier"), "#");

    $MyPage->addField("date_exp", _("Date d'expiration"), TYPC_DTF);
    $MyPage->setFieldProperties("date_exp", FIELDP_HAS_CALEND, false);
    $MyPage->setFieldProperties("date_exp", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("date_exp", FIELDP_DEFAULT, pg2phpDate($InfosLicence['date_expiration']));
    $MyPage->addLink("date_exp", "calendrier_date_exp", _("Calendrier"), "#");

    $htm2 = "<BR/>";

    $MyPage->addHTMLExtraCode("htm2", $htm2);
  
    $checkDate = "if (document.ADForm.HTML_GEN_date_date_crea.value != '' && !isDate(document.ADForm.HTML_GEN_date_date_crea.value)) { alert('" . _("La Date création n\'est pas une date valide") . "'); return false; } else if (document.ADForm.HTML_GEN_date_date_exp.value != '' && !isDate(document.ADForm.HTML_GEN_date_date_exp.value)) { alert('" . _("La Date d\'expiration n\'est pas une date valide") . "'); return false; }";
    
    $calendrier_date_crea_codejs = "
      if (! isDate(document.ADForm.HTML_GEN_date_date_crea.value)) document.ADForm.HTML_GEN_date_date_crea.value='';
      open_calendrier(getMonth(document.ADForm.HTML_GEN_date_date_crea.value), getYear(document.ADForm.HTML_GEN_date_date_crea.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_date_crea');return false;";
    $calendrier_date_exp_codejs = "
      if (! isDate(document.ADForm.HTML_GEN_date_date_exp.value)) document.ADForm.HTML_GEN_date_date_exp.value='';
      open_calendrier(getMonth(document.ADForm.HTML_GEN_date_date_exp.value), getYear(document.ADForm.HTML_GEN_date_date_exp.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_date_exp');return false;";
    $MyPage->setLinkProperties("calendrier_date_crea", LINKP_JS_EVENT, array("onclick" => $calendrier_date_crea_codejs));
    $MyPage->setLinkProperties("calendrier_date_exp", LINKP_JS_EVENT, array("onclick" => $calendrier_date_exp_codejs));

    $MyPage->addFormButton(1, 1, "valider", _("Mettre à jour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gli-3');
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $checkDate));

    $MyPage->addFormButton(1, 2, "precedent", _("Précédente"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

    $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Gli-1');
    $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
    */
}
/*}}}*/

/*{{{ Gli-5 : Ecran de confirmation de création d'un fichier de demande de licence */
else if ($global_nom_ecran == 'Gli-5') {
  /*
  $DemandeLicence = getDemandeLicence();

  echo "<zcript type=\"text/javascript\">child_window=OpenBrw('$SERVER_NAME/$DemandeLicence', '"._("Licence")."');</script>";
  $html_msg = new HTML_message(_("Confirmation de demande d'une nouvelle licence"));
  $html_msg->setMessage(_("Fichier de demande d'une nouvelle licence correctement créé"));
  $html_msg->addButton("BUTTON_OK", 'Gen-7');
  $html_msg->buildHTML();
  echo $html_msg->HTML_code;
  */

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Nom d'écran inconnu : '$global_nom_ecran'"

?>