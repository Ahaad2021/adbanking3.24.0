<?php

require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/agence.php');
require_once ('lib/dbProcedures/client.php');
require_once ('lib/dbProcedures/traitements_compensation.php');
require_once ('lib/dbProcedures/epargne.php');
require_once ('lib/html/HTML_GEN2.php');
require_once ('lib/html/FILL_HTML_GEN2.php');
require_once 'lib/html/HTML_champs_extras.php';

require_once 'ad_ma/app/models/AgenceRemote.php';
require_once ('modules/rapports/xml_compensation_siege_auto.php');
require_once ('lib/misc/csv.php');
require_once ('modules/rapports/xslt.php');

/*
error_reporting(E_ALL);
ini_set("display_errors", "on");
*/

/* Tcs-1 : Initialisation compensation */
if ($global_nom_ecran == "Tcs-1") {

    global $dbHandler, $doc_prefix, $global_nom_login, $global_monnaie;

    // Affichage message
    $html_msg = new HTML_message("Initialisation des compensation");
    
    $statut_job = getStatutJobExterne();
    
    if ($statut_job == 'ENCOURS') {
        $html_msg->setMessage("<br /><span style=\"color:#FF0000;\">Un traitement de compensation est actuellement en cours !<br/><br/>Veuillez patienter quelques minutes puis revenir sur cette ecran.</span><br />");
    } else {

        // Javascript
        $js_valid  = "function processTraitement(){\n";
        $js_valid .= "document.ADForm.onsubmit = function(){ if (document.ADForm.prochain_ecran.value == 'Tcs-2') { document.ADForm.img_loading.src='$http_prefix/images/loading.gif'; document.ADForm.BOUI.disabled = true; } }";
        $js_valid .= "\n}\nprocessTraitement();";
        $html_msg->setMessage("<br />Lancer le Traitement de compensation au siège ?<br /><img name=\"img_loading\"/><script>".$js_valid."</script>");

        $html_msg->addButton("BUTTON_OUI", 'Tcs-2');
        $html_msg->addButton("BUTTON_NON", 'Gen-7');
    }

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
}
/* Tcs-2 : Traitement des écritures de compensation */
elseif ($global_nom_ecran == "Tcs-2") {
    global $dbHandler, $doc_prefix, $global_nom_login, $global_monnaie, $global_monnaie_courante;

    $statut_job = getStatutJobExterne();

    if ($statut_job != 'ENCOURS') {
        // Read table adsys_multi_agence
        $ListeAgences = AgenceRemote::getListRemoteAgence(true);

        $file_path = "$doc_prefix/multiagence/properties/multiagence.csv";

        // Delete file
        if (file_exists($file_path)) {
            @unlink($file_path);
        }

        // Create file
        @touch($file_path);

        chmod($file_path, 0777);

        // Add header to file
        file_put_contents($file_path, "app_db_host;app_db_name;id_agc;app_db_username;app_db_password\r\n", FILE_APPEND | LOCK_EX);

        $choix_agence = array();
        if (is_array($ListeAgences) && count($ListeAgences) > 0) {
            foreach ($ListeAgences as $key => $obj) {
                $line = $obj["app_db_host"]."||@||".$obj["app_db_name"]."||@||".$obj["id_agc"]."||@||".$obj["app_db_username"]."||@||".$obj["app_db_password"]."\r\n";
                // Append content to file
                file_put_contents($file_path, $line, FILE_APPEND | LOCK_EX);
            }
        }
        //Vidage partielle des tables logs
        $total_vidage = truncatelogmultiagence();

        // Début exécution du job
        updateStatutJobExterne('ENCOURS');

        $cmd_job = "sh /usr/share/adbanking/web/multiagence/batchs/ALIM_SIEGE.sh 2>&1"; // >> /usr/share/adbanking/web/multiagence/logs/job_log.log 2>&1


        $result_job = exec($cmd_job, $output_job, $return_job);

        // Fin exécution du job
        updateStatutJobExterne('TERMINE');

        // Delete file
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }
    
    // Affichage de la confirmation
    $html_msg = new HTML_message("Traitement compensation au siège...");

    //if ($return_job > 0) {
        $message = "";
        $error_arr = -1;
        $error_arr = @getEtlLogError();
        if($error_arr < 0) {
            //$html_msg->setMessage($output_job[0]."<br /> ". $output_job[1]);
            $message .= "Erreur avec le Job Talend : ".$output_job[0]."<br /> ". $output_job[1];
        }
        else{
            if ($error_arr > 0){
                //$html_msg->setMessage(sprintf("<br />Erreur accès serveur !<br /><br />IP adresse : %s<br />Base de donnée : %s<br />", $error_arr[0], $error_arr[1]));
                $message .= sprintf("<br />Attention pas accès serveur pour certain agence(s)!<br />Veuillez vérifier le rapport log multiagence pour plus de détailles!<br />");
            }
        }

    //} else {

        $fonction = 214;
        $operation = 614;
        $id_his = NULL;
        
        // Traiter les compensations
        $ListeCompensations = getListeCompensations();

        if (is_array($ListeCompensations) && count($ListeCompensations) > 0) {

            for ($x = 0; $x < count($ListeCompensations); $x++) {

                // Build écritures
                // Passage de l'écriture de retrait
                $comptable = array();
                $ajout_historique = 'f';
                $msg_erreur = NULL;
                
                // Retrait / Depot
                $montant = $ListeCompensations[$x]['montant'];
                $devise = $ListeCompensations[$x]['code_devise_montant'];

                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();

                $cptes_substitue["cpta"]["debit"] = $ListeCompensations[$x]['compte_debit_siege'];
                $cptes_substitue["cpta"]["credit"] = $ListeCompensations[$x]['compte_credit_siege'];

                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $devise, NULL, NULL, NULL);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                // Commission
                $commission = $ListeCompensations[$x]['commission'];
                $code_devise_commission = $global_monnaie_courante; //$ListeCompensations[$x]['code_devise_commission'];
                
                if ($commission > 0) {
                    $cptes_substitue = array();
                    $cptes_substitue["cpta"] = array();

                    $cptes_substitue["cpta"]["debit"] = $ListeCompensations[$x]['compte_debit_siege'];
                    $cptes_substitue["cpta"]["credit"] = $ListeCompensations[$x]['compte_credit_siege'];

                    $myErr = passageEcrituresComptablesAuto($operation, $commission, $comptable, $cptes_substitue, $code_devise_commission, NULL, NULL, NULL);
                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }
                }

                $myErr = ajout_historique($fonction, NULL, 'Traitement compensation au siège', $global_nom_login, date("r"), $comptable, NULL, $id_his);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    $msg_erreur = serialize($myErr);
                } else {
                    $ajout_historique = 't';
                }

                $id_his = $myErr->param;
                $id_ecriture = getIDEcritureByIDHis($id_his);

                // Update current ad_multi_agence_compensation
                updateCompensation ($ListeCompensations[$x]['id'], $ListeCompensations[$x]['id_audit_agc'], $ListeCompensations[$x]['id_ag_local'], $ListeCompensations[$x]['id_ag_distant'], $id_his, $id_ecriture, $ajout_historique, $msg_erreur);

                $dbHandler->closeConnection(true); // TO UNCOMMENT - commit transaction
            }

            //$html_msg->setMessage("<br />Les écritures ont été effectuées avec succès.<br />");
            $message .= "<br />Les écritures ont été effectuées avec succès.<br />Traitement Terminé!!";
        }
        else {
            //$html_msg->setMessage("<br />Aucune écritures effectuées.<br />");
            $message .= "<br />Il y aucune écritures effectuées.<br />Traitement Terminé!!";
        }
    //}
    $html_msg->setMessage($message);
    $html_msg->addButton("BUTTON_OK", 'Gen-7');
    $html_msg->buildHTML();

    echo $html_msg->HTML_code;
}
/* Tcs-3 : Confirmation Traitement des compensations */
elseif ($global_nom_ecran == "Tcs-3") {
    
}
/* Rec-1 : Personnalisation du rapport etat de la compensation des operations en deplace */
elseif ($global_nom_ecran == "Rec-1") {
  global $adsys;

  $MyPage = new HTML_GEN2(_("Personnalisation du rapport état de la compensation des opérations en déplacé"));

  $MyPage->addField("agence", _("Agence"), TYPC_LSB);
  //$MyPage->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);
  $MyPage->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
  $ListMultiAgence = array();
  $ListMultiAgence = getListMultiAgences();
  $MyPage->setFieldProperties("agence", FIELDP_ADD_CHOICES, $ListMultiAgence);
  $MyPage->addField("date_rapport", _("Date du Rapport"), TYPC_DTG);
  $MyPage->setFieldProperties("date_rapport", FIELDP_DEFAULT, date("d/m/Y"));
  $MyPage->setFieldProperties("date_rapport", FIELDP_IS_REQUIRED, true);
  $MyPage->addField("etat_compensation", _("Etat Compensation"), TYPC_LSB);
  //$MyPage->setFieldProperties("etat_compensation", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("etat_compensation", FIELDP_HAS_CHOICE_TOUS, true);
  $MyPage->setFieldProperties("etat_compensation", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("etat_compensation", FIELDP_ADD_CHOICES, $adsys["adsys_etat_compensation_siege_auto"]);

  //Boutons
  $MyPage->addFormButton(1,1,"pdf", _("Rapport PDF"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("pdf", BUTP_PROCHAIN_ECRAN, "Rec-2");
  $MyPage->addFormButton(1,2,"csv", _("Export CSV"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rec-2");
  $MyPage->addFormButton(1,3,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-7");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/* Rec-2 : Confirmation sortie du rapport */
elseif ($global_nom_ecran == "Rec-2") {
  global $global_monnaie;

  $date_rapport = php2pg($date_rapport);
  $getDataLog = array();
  $getDataLog = getDataLogEtatCompensationSiege($date_rapport, $agence, $etat_compensation);

  if ($getDataLog == null){
    $html_err = new HTML_erreur();
    $err_msg = "Aucune donnée correspond au criteres de recherche!!";
    $html_err->setMessage(sprintf("Attention : %s ", $err_msg));
    $html_err->addButton("BUTTON_OK", 'Rec-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  if ($agence == null) {//&& ($agence == '' || $agence == null)){
    $agence = _("Tous");
  }
  $etat_compensation = adb_gettext($adsys["adsys_etat_compensation_siege_auto"]["$etat_compensation"]);
  if ($etat_compensation == null){
    $etat_compensation = _("Tous");
  }

  $criteres = array(
    _("Agence") => $agence,
    _("Date du Rapport") => date('d/m/Y',strtotime("$date_rapport")),
    _("Etat Compensation") => $etat_compensation
  );

  if (isset($pdf) && $pdf == 'Rapport PDF'){ //Generation PDF
    $XML_DATA_LOG = xml_compensation_siege_auto_log($criteres,$getDataLog,$global_monnaie);

    $fichier = xml_2_xslfo_2_pdf($XML_DATA_LOG, 'compensation_siege_log.xslt');
    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo get_show_pdf_html("Gen-7", $fichier);

  }
  elseif ((isset($csv) && $csv == 'Export CSV')){ //Generation CSV/EXCEL ||(isset($excel_bud_budget) && $excel_bud_budget == 'Export EXCEL')
    $XML_DATA_LOG = xml_compensation_siege_auto_log($criteres,$getDataLog,$global_monnaie,true);

    $fichier = xml_2_csv($XML_DATA_LOG, 'compensation_siege_log.xslt');
    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo getShowCSVHTML("Gen-7", $fichier);
  }

}
