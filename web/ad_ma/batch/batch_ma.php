<?php

// error_reporting(E_ALL);
// ini_set("display_errors", "on");
 
 
 // Increase allocated memory size
 ini_set("memory_limit", "512M");

/*
  Batch Multi-Agence

  Description :
  Ce module crée 3 écrans :
 * Tnm-1 : Choix d'une agence
 * Tnm-2 : Exécution batch
 * Tnm-3 : Archives & résultats

 */

// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Batch.php';

require_once "lib/html/HTML_menu_gen.php";
require_once "lib/html/HTML_message.php";
require_once "lib/html/HTML_erreur.php";

//-----------------------------------------------------------------
//--------------- Ecran Tnm-1 : Choix d'une agence ---------------
//-----------------------------------------------------------------
if ($global_nom_ecran == "Tnm-1") {
//-------------------------------- ECRAN Tnm-1 : Traitements de nuit Multi-Agence --------------------------------
    // Clear all remote info
    resetVariablesGlobalesRemoteClient();

    // Clear global id agence & multiple batch
    if(isset($_SESSION["gbatch_id_agence"])) {
        unset($_SESSION["gbatch_id_agence"]);
    }

    if(isset($_SESSION["multiple_batch"])) {
        unset($_SESSION["multiple_batch"]);
    }

    if(isset($_SESSION["agc_statut"])) {
        unset($_SESSION["agc_statut"]);
    }

    // Création du formulaire
    $html = new HTML_GEN2();
    $html->setTitle(_("Traitements de nuit Multi-Agence"));

    // Récupère la liste des agences
    $ListeAgences = AgenceRemote::getListAllAgence();

    $choix_agence = array();
    if (is_array($ListeAgences) && count($ListeAgences) > 0) {
        foreach ($ListeAgences as $key => $obj) {
            if (DBC::pingConnection($obj, 1) === TRUE) { // Vérifié si la BDD est active
                $choix_agence[$key] = sprintf("%s (%s)", $obj["app_db_description"], $obj["id_agc"]);
            }
        }
    }
    $html->addField("IdAgence", _("Agence"), TYPC_LSB);
    $html->setFieldProperties("IdAgence", FIELDP_ADD_CHOICES, $choix_agence);
    $html->setFieldProperties("IdAgence", FIELDP_IS_REQUIRED, true);

    // Ajout des champs ornementaux
    $xtra1 = "<b>" . _("Choix d'une agence") . "</b>";
    $html->addHTMLExtraCode("htm1", $xtra1);
    $html->setHTMLExtraCodeProperties("htm1", HTMP_IN_TABLE, true);

    //ordonner les champs
    $html->setOrder(NULL, array("htm1", "IdAgence"));

    //Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Tnm-3');
    $html->addFormButton(1, 2, "archive", _("Archives"), TYPB_SUBMIT);
    $html->setFormButtonProperties("archive", BUTP_PROCHAIN_ECRAN, "Tnm-3");
    $html->setFormButtonProperties("archive", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
//-----------------------------------------------------------------
//--------------- Ecran Tnm-2 : Exécution batch ------------
//-----------------------------------------------------------------
elseif ($global_nom_ecran == "Tnm-2") {
    global $DB_dsn;
//----------------------------- ECRAN Tnm-2 : Traitements de nuit Multi-Agence -----------------------------
    
    // Default value
    $batch_id_agence = 0;

    // Récupère l'ID agence choisie
    if (isset($IdAgence) && $IdAgence > 0) {
        $batch_id_agence = $IdAgence;
    }
    elseif (isset($_SESSION["gbatch_id_agence"]) && $_SESSION["gbatch_id_agence"]>0) {
        $batch_id_agence = trim($_SESSION["gbatch_id_agence"]);
    }
    else {
        $MyPage = new HTML_erreur(_("Ouverture de l'Agence"));
        $MyPage->setMessage(_("L'ID de l'Agence n'a pas été renseigné"));
        $MyPage->addButton(BUTTON_OK, "Tnm-1");
        $MyPage->buildHTML();
        echo $MyPage->HTML_code;
    }

    if($batch_id_agence > 0) {
        // Récupère les infos de l'agence distante choisie
        $batch_agence_obj = AgenceRemote::getRemoteAgenceInfo($batch_id_agence);

        $batch_agence_desc = trim($batch_agence_obj->app_db_description);
        $batch_db_host = trim($batch_agence_obj->app_db_host);
        $batch_db_port = trim($batch_agence_obj->app_db_port);
        $batch_db_name = trim($batch_agence_obj->app_db_name);
        $batch_db_username = trim($batch_agence_obj->app_db_username);
        $batch_db_password = trim($batch_agence_obj->app_db_password);
    }

    require_once 'lib/misc/VariablesGlobales.php';
    require_once 'ad_ma/app/controllers/misc/VariablesGlobales_ma.php';
    require_once 'DB.php';
    require_once 'batch/librairie.php';
    require_once 'ad_ma/batch/divers_ma.php';
    require_once 'lib/html/HTML_message.php';
    require_once 'lib/misc/Erreur.php';

    require_once 'lib/dbProcedures/agence.php';
    require_once 'lib/dbProcedures/extraits.php';

    // Store local agence info
    $global_id_agence_local = $global_id_agence;
    $global_langue_rapport_local = $global_langue_rapport;
    $global_monnaie_local = $global_monnaie;
    $global_monnaie_prec_local = $global_monnaie_prec;
    $global_multidevise_local = $global_multidevise;
    $global_id_exo_local = $global_id_exo;
    $global_monnaie_courante_local = $global_monnaie_courante;
    $global_monnaie_courante_prec_local = $global_monnaie_courante_prec;
    
    if((isset($agc_statut) && $agc_statut==2) || (isset($_SESSION["agc_statut"]) && $_SESSION["agc_statut"]==2))
    {        
        if ((isset($_SESSION["agc_statut"]) && $_SESSION["agc_statut"]==2)) {
            $result = fermeture_agence($batch_id_agence);

            if ($result['result'] < 0 && $result['result']!=-2 && $result['result']!=-1) {
              switch ($result['result']) {
              /*
              case -1 :
                $msg=_("des utilisateurs possédant un guichet sont encore loggés")." (";
                while (list(,$value) = each($result['login'])) {
                  $msg .= "'$value', ";
                }
                $msg = substr($msg,0,strlen($msg)-2);
                $msg .= ") !";
                break;
              case -2 :
                $msg=_("des guichets n'ont pas été correctements fermés")."(";
                while (list(,$value) = each($result['login'])) {
                  $msg .= "'$value', ";
                }
                $msg = substr($msg,0,strlen($msg)-2);
                $msg .= ") !";
                break;
              */
              case -4 :
                $msg = _("l'agence est déjà fermée !");
                break;
              case -5 :
                $msg = _("l'agence est en cours de traitements de nuit !");
                break;
              default :
                $msg = _("erreur inconnue !");
                break;
              }

              $MyPage = new HTML_erreur(_("Fermeture de l'agence : <b>" . $batch_agence_desc . " (" . $batch_id_agence . ")</b>"));
              $MyPage->setMessage(_("Impossible de fermer l'agence")." : ".$msg);
              $MyPage->addButton(BUTTON_OK, "Tnm-1");
              $MyPage->buildHTML();
              echo $MyPage->HTML_code;
            } else {
              
                if($result['result']!=-2 || $result['result']!=-1)
                {
                  if(force_all_logout()) { //on déconnecte tous les guichets d'office
                    fermeture_agence($batch_id_agence);
                  }
                }

                $global_statut_agence = 2;
                $MyPage = new HTML_message(_("Fermeture de l'agence : <b>" . $batch_agence_desc . " (" . $batch_id_agence . ")</b>"));
                $MyPage->setMessage(_("L'agence a été fermée avec succès!"));
                $MyPage->addButton(BUTTON_OK, "Tnm-1");
                $MyPage->buildHTML();
                echo $MyPage->HTML_code;
            }

            // Clear agence status
            if(isset($_SESSION["agc_statut"])) {
                unset($_SESSION["agc_statut"]);
            }
        }
        else {
            $MyPage = new HTML_message(_("Fermeture de l'agence : <b>" . $batch_agence_desc . " (" . $batch_id_agence . ")</b>, demande confirmation"));
            $MyPage->setMessage(_("Etes-vous sûr de vouloir fermer l'agence ?"));
            $MyPage->addButton(BUTTON_OUI, "Tnm-2");
            $MyPage->addButton(BUTTON_NON, "Tnm-1");
            $MyPage->buildHTML();
            echo $MyPage->HTML_code;
            
            $_SESSION["agc_statut"] = $agc_statut;
        }
    }
    else
    {
        // Clear agence status
        if(isset($_SESSION["agc_statut"])) {
            unset($_SESSION["agc_statut"]);
        }

        if(isset($_SESSION["multiple_batch"]) && $_SESSION["multiple_batch"]==1) {

            // Clear multiple batch
            if(isset($_SESSION["multiple_batch"])) {
                unset($_SESSION["multiple_batch"]);
            }

            echo("<h1 align=\"center\">Traitements de nuit Multi-Agence<br /><br /><b>Agence : " . $batch_agence_desc . " (" . $batch_id_agence . ")</b><br /></h1>");

            $verif_result = verif_parametrage();

            if ($verif_result->errCode == NO_ERR) {

                // Init class
                $BatchObj = new Batch();

                // On peut alors exécuter le batch !
                $last_batch = pg2phpDatebis(get_last_batch($batch_id_agence));
                // On ferme la session pour pouvoir utiliser flush() et envoyer les données HTTP en continu.
                // Normalement, il n'y a pas lieu de modifier des variables de session dans le batch, donc on peut fermer la session sans problème.
                session_write_close();

                // On passe au jour suivant
                $tomorrow = mktime(0,0,0,$last_batch[0],$last_batch[1]+1, $last_batch[2]);
                $last_batch[0] = date("m", $tomorrow);
                $last_batch[1] = date("d", $tomorrow);
                $last_batch[2] = date("Y", $tomorrow);

                echo("<br /><br />");

                //exécuter le batch pour toutes les dates où il n'a pas eu lieu
                while (mktime(0,0,0,$last_batch[0],$last_batch[1], $last_batch[2]) < mktime(0,0,0,date("m"), date("d"), date("Y"))) {
                  // Création environnement du batch : cette date est un global pour batch.php
                  $date_jour = sprintf("%02d", $last_batch[1]);
                  $date_mois = sprintf("%02d", $last_batch[0]);
                  $date_annee = $last_batch[2];

                  // Démarrage du batch
                  require('ad_ma/batch/batch_main.php');
                  echo("<br /><br />");

                  // Passage au jour suivant
                  $tomorrow = mktime(0,0,0,$last_batch[0],$last_batch[1]+1, $last_batch[2]);
                  $last_batch[0] = date("m", $tomorrow);
                  $last_batch[1] = date("d", $tomorrow);
                  $last_batch[2] = date("Y", $tomorrow);

                  // On envoie les données HTTP en continu (jour après jour) {@link PHP_MANUAL#flush}
                  flush();
                }

                // Quand tout est fini, message de confirmation
                $html_msg = new HTML_message(_("Exécution du batch"));

                $html_msg->setMessage(_("Fin de l'exécution du batch<br /><br />")._("Voulez vous ouvrir l'agence ?")." (".sprintf(_("La date du jour est %s"),strftime("%A %d %B %Y")).")");
                $html_msg->addButton(BUTTON_OUI, "Tnm-2");
                $html_msg->addButton(BUTTON_NON, "Tnm-1");

                $html_msg->buildHTML();
                echo $html_msg->HTML_code;

                // Destroy object
                unset($BatchObj);
            }
            else{
                debug($verif_result, "retour verif_parametrage");

                $myPage = new HTML_GEN2(_("Echec lors de la vérification du parametrage"));
                $myPage->addHTMLExtraCode("erreur", "<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\">
                                          <tr><td align=\"center\"><font color=\"".$colt_error."\">
                                          "._("Pour éviter d'avoir des erreurs qui empêcheraient l'exécution du batch")."</font></td></tr>
                                          <tr><td align=\"center\"><font color=\"".$colt_error."\">
                                          "._("vous devriez")." ".$error[$verif_result->errCode]."<b>".$verif_result->param."</b></font></td></tr>
                                          <tr><td>&nbsp;</td></tr></table>");
                debug($verif_result->param,"result");
                $myPage->addHiddenType("parametrageOK", true);
                // Le profil a-t-il accès au paramétrage nécessaire ?

                /*
                switch ($verif_result->errCode) {
                case ERR_PARAM_CPT_INT:
                  if (check_access(294)) {
                    $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
                    $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Mta-1');
                    $myPage->addHiddenType("table", "adsys_produit_epargne");
                    $myPage->addHiddenType("HTML_GEN_LSB_contenu", $verif_result->handler);
                    break;
                  }

                case ERR_PARAM_CPT_ASS:
                  if (check_access(294)) {
                          setGlobalIdAgence(getNumAgence());
                          $SESSION_VARS['select_agence']=$global_id_agence;
                    $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
                    $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Cpc-2');
                    $myPage->addHiddenType("HTML_GEN_LSB_libel", $verif_result->handler);
                    break;
                  }

                case ERR_PARAM_OPE:
                  if (check_access(420)) {
                    $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
                    $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Gop-2');
                    $myPage->addHiddenType("id_oper", $verif_result->param);
                    break;
                  }

                case ERR_PARAM_AGC:
                  if (check_access(294)) {
                    $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
                    $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Mta-1');
                    $myPage->addHiddenType("table", "ad_agc");
                    $myPage->addHiddenType("HTML_GEN_LSB_contenu", $verif_result->handler);
                    break;
                  }

                default:
                  $myPage->addFormButton(1, 1, "annuler", _("Annuler"), TYPB_SUBMIT);
                  $myPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
                }
                */

                $myPage->addFormButton(1, 2, "continuer", _("OK"), TYPB_SUBMIT);
                $myPage->setFormButtonProperties("continuer", BUTP_PROCHAIN_ECRAN, 'Tnm-1');

                $myPage->buildHTML();
                echo $myPage->getHTML();
            }

            } else {

            $result = array();
            if($batch_id_agence > 0) {
              $result = ouverture_agence($batch_id_agence);
            }

        $_SESSION["gbatch_id_agence"] = $batch_id_agence;

        if (is_array($result) && $result['result'] < 0 && $result['result']!=-1) { //erreur lors de l'ouverture de l'agence
          switch ($result['result']) {

          /*
          case -1 :
            $msg=_("des logins possédant un guichet sont encore loggés")." (";
            while (list(,$value) = each($result['login'])) {
              $msg .= "'$value', ";
            }
            $msg = substr($msg,0,strlen($msg)-2);
            $msg .= ") !";
            break;
          */
          case -2 :
            $msg = _("aujourd'hui est un jour ferié !");
            break;

          case -3 :
            $msg = _("les traitements de fin de journée n'ont pas encore été exécutés !");
            break;

          case -4 :
            $msg = _("l'agence est déjà ouverte !");
            break;

          case -5 :
            $msg = _("l'agence est en cours de traitements de nuit !");
            break;

          case -6 :
            $msg = _("le batch a déjà été exécuté pour aujourd'hui : impossible de réouvrir l'agence !");
            break;

          case -7 :
            $msg = _("les frais de tenue de compte n'ont pas été prélevés : impossible d'ouvrir l'agence !");
            break;

          default :
            $msg = _("erreur inconnue !");
            break;
          }

          if ($result['result'] == -3) {//message au cas où le batch n'a pas encore été exécuté
            $MyPage = new HTML_erreur(_("Erreur lors de l'exécution des traitements de nuit Multi-Agence<br /><br /><b>Agence : " . $batch_agence_desc . " (" . $batch_id_agence . ")</b>"));

            $AG = getAgenceDatas($batch_id_agence);
            $last_batch = pg2phpDateBis($AG['last_batch']);
            $SESSION_VARS['last_batch'] = $last_batch;
            $last_batch_fr = strftime("%A %d %B %Y", mktime(0,0,0,$last_batch[0], $last_batch[1]+1, $last_batch[2]));

            $MyPage->setMessage("<b>"._("Attention")."</b>, ".sprintf(_("le traitement de nuit n'a <b>pas</b> pu être exécuté pour la date du %s et les dates suivantes éventuelles."),"<b>$last_batch_fr</b>")." <br />"._("Cliquez sur OK pour démarrer les traitements de fin de journée pour ces dates"));
            $MyPage->addButton(BUTTON_OK, "Tnm-2");
            $MyPage->addButton(BUTTON_CANCEL, "Tnm-1");
            $MyPage->buildHTML();

            echo $MyPage->HTML_code;

            $_SESSION["multiple_batch"] = 1;
          }
          else if ($result['result'] == -7) { //frais tenue compte

            echo("<h1 align=\"center\">Traitements de nuit Multi-Agence<br /><br /><b>Agence : " . $batch_agence_desc . " (" . $batch_id_agence . ")</b><br /></h1>");

            // Init class
            $BatchObj = new Batch();

            // On ferme la session pour pouvoir utiliser flush() et envoyer les données HTTP en continu.
            // Normalement, il n'y a pas lieu de modifier des variables de session dans le batch, donc on peut fermer la session sans problème.
            session_write_close();

            // Création environnement du batch : cette date est un global pour batch.php
            $last_batch = pg2phpDatebis(get_last_batch($batch_id_agence));
            $date_jour = $last_batch[1];
            $date_mois = $last_batch[0];
            $date_annee = $last_batch[2];

            // Démarrage du batch pour les frais de tenue de compte
            require('ad_ma/batch/batch_main.php');

            // On envoie les données HTTP en continu (jour après jour) {@link PHP_MANUAL#flush}
            flush();

            echo("<br /><br />");

            $MyPage = new HTML_message();

            $msg = (_("Les frais de tenue de compte ont été prélevés.<br /><br />")._("Voulez vous ouvrir l'agence ?")." (".sprintf(_("La date du jour est %s"),strftime("%A %d %B %Y")).")");
            $MyPage->setMessage($msg);
            $MyPage->addButton(BUTTON_OUI, "Tnm-2");
            $MyPage->addButton(BUTTON_NON, "Tnm-1");
            $MyPage->buildHTML();

            echo $MyPage->HTML_code;

            // Destroy object
            unset($BatchObj);

          } else { //message d'erreur pour tous les autres cas

            $MyPage = new HTML_erreur(_("Ouverture de l'Agence : <b>" . $batch_agence_desc . " (" . $batch_id_agence . ")</b>"));
            $MyPage->setMessage(_("Impossible d'ouvrir l'agence")." : ".$msg);
            $MyPage->addButton(BUTTON_OK, "Tnm-1");
            $MyPage->buildHTML();
            echo $MyPage->HTML_code;
          }

        } //end if erreur lors de l'ouverture de l'agence
        elseif (is_array($result) && ($result['result']==1 || $result['result']==-1)) {

          // Pas d'erreur lors de l'ouverture de l'agence

          // $global_statut_agence = 1;

            if($result['result']==-1)
            {
              if(force_all_logout()) { //on déconnecte tous les guichets d'office
                ouverture_agence($batch_id_agence);
              }
            }

            // Vérification de l'espace disque disponible
            $espace_total = disk_total_space("$lib_path/backup/");
            $espace_libre = disk_free_space("$lib_path/backup/");
            $pourcentage_libre = round($espace_libre / $espace_total * 100, 1);
            if ($pourcentage_libre < 10) {
              $msg = "<span style='color: red;'>".sprintf(_("Attention, le disque du serveur est presque plein (seulement %s%% disponible)."), $pourcentage_libre)."<br />"._("Il faut peut-être déplacer certains backups vers un CD.")."</span><br /><br />";
            } else {
              $msg = "";
            }
            $MyPage = new HTML_message(_("Ouverture de l'Agence : <b>" . $batch_agence_desc . " (" . $batch_id_agence . ")</b>"));
            $MyPage->setMessage($msg._("L'agence a été ouverte avec succès!"));
            $MyPage->addButton(BUTTON_OK, "Tnm-1");
            $MyPage->buildHTML();
            echo $MyPage->HTML_code;

          // Clear global id agence
          if(isset($_SESSION["gbatch_id_agence"])) {
              unset($_SESSION["gbatch_id_agence"]);
          }
        }
      }

    }
    // Restore local agence info
    $global_id_agence = $global_id_agence_local;
    $global_langue_rapport = $global_langue_rapport_local;
    $global_monnaie = $global_monnaie_local;
    $global_monnaie_prec = $global_monnaie_prec_local;
    $global_multidevise = $global_multidevise_local;
    $global_id_exo = $global_id_exo_local;
    $global_monnaie_courante = $global_monnaie_courante_local;
    $global_monnaie_courante_prec = $global_monnaie_courante_prec_local;
}
//-----------------------------------------------------------------
//--------------- Ecran Tnm-3 : Archive du batch ------------
//-----------------------------------------------------------------
elseif ($global_nom_ecran == "Tnm-3") {

    if(isset($display_result)){

        if(isset($DateDeb) && isset($DateFin))
        {
            $batchList = Batch::getListBatchArchive($DateDeb, $DateFin);

            $html = new HTML_GEN2();
            $html->setTitle(_("Archives des Traitements de nuit : Résultats"));

            // Construction du tableau HTML pour l'affichage
            if (isset($batchList) && is_array($batchList) && count($batchList)>0) {
              $date_jour = date("d");
              $date_mois = date("m");
              $date_annee = date("Y");
              $date_ancien = $date_annee."/".$date_mois."/".$date_jour;

              $id_his_ancien = "";
              $ExtraHTML = "<br><TABLE align=\"center\" cellpadding=\"5\" width=\"100%\">";
              $ExtraHTML .= "\n\t<tr align=\"center\" bgcolor=\"$colb_tableau\">";
              $ExtraHTML .= "<td><b>"._("DATE")."</b></td>";
              $ExtraHTML .= "<td><b>"._("AGENCE")."</b></td>";
              $ExtraHTML .= "<td><b>"._("LOGIN")."</b></td>";
              $ExtraHTML .= "<td><b>"._("LIEN BACKUP BDD")."</b></td>";
              $ExtraHTML .= "<td><b>"._("LIEN RAPPORT BATCH")."</b></td>";
              $ExtraHTML .=	"</tr>";

              $color = $colb_tableau;
              foreach ($batchList as $key => $batch) {

                $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);

                // Date
                $tmp_date = pg2phpDatebis($batch["date_crea"]);
                $date_val = $tmp_date[1]."/".$tmp_date[0]."/".$tmp_date[2];

                // Agence
                $agence_val = $batch["app_db_description"]." (".$batch["id_ag"].")";

                // Login
                $login_val = trim($batch["nom_login"]);

                // Lien backup batch
                $db_backup_path_val = trim($batch["db_backup_path"]);

                // Lien backup batch
                $batch_rapport_pdf_path_val = trim($batch["batch_rapport_pdf_path"]);

                $ExtraHTML .= "\n\t<tr align=\"center\" bgcolor=\"$color\">";

                $ExtraHTML .= "<td>$date_val</td>";
                $ExtraHTML .= "<td>$agence_val</td>";
                $ExtraHTML .= "<td>$login_val</td>";
                $ExtraHTML .= "<td>$db_backup_path_val</td>";
                $ExtraHTML .= "<td>$batch_rapport_pdf_path_val</td>";

                $ExtraHTML .= "</tr>";
              }
              $ExtraHTML .= "</TABLE>";

              $html->addHTMLExtraCode("htm1",$ExtraHTML);
            }
        }

        $html->addFormButton(1, 1, "retour", _("Précédent"), TYPB_SUBMIT);
        $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Tnm-3');

        $html->addFormButton(1, 2, "cancel", _("Retour"), TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Tnm-1');
        $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

        $html->buildHTML();
        echo $html->getHTML();
    }
    else{

        if(isset($IdAgence))
        {
            // Default value
            $batch_id_agence = 0;

            // Récupère l'ID agence choisie
            if (isset($IdAgence) && $IdAgence > 0) {
                $batch_id_agence = $IdAgence;
            }
            elseif (isset($_SESSION["gbatch_id_agence"]) && $_SESSION["gbatch_id_agence"]>0) {
                $batch_id_agence = trim($_SESSION["gbatch_id_agence"]);
            }
            else {
                $MyPage = new HTML_erreur(_("Ouverture de l'Agence"));
                $MyPage->setMessage(_("L'ID de l'Agence n'a pas été renseigné"));
                $MyPage->addButton(BUTTON_OK, "Tnm-1");
                $MyPage->buildHTML();
                echo $MyPage->HTML_code;
            }
            
            if($batch_id_agence > 0)
            {
                $_SESSION["gbatch_id_agence"] = $batch_id_agence;

                // Récupère les infos de l'agence distante choisie
                $batch_agence_obj = AgenceRemote::getRemoteAgenceInfo($batch_id_agence);

                $batch_db_driver = "pgsql";
                $batch_agence_desc = trim($batch_agence_obj->app_db_description);
                $batch_db_host = trim($batch_agence_obj->app_db_host);
                $batch_db_port = trim($batch_agence_obj->app_db_port);
                $batch_db_name = trim($batch_agence_obj->app_db_name);
                $batch_db_username = trim($batch_agence_obj->app_db_username);
                $batch_db_password = trim($batch_agence_obj->app_db_password);

                // Initialize database connection
                $pdo_conn_batch = new DBC($batch_db_name, $batch_db_username, $batch_db_password, $batch_db_host, $batch_db_port, $batch_db_driver);

                // Init class
                $AgenceObj = new Agence($pdo_conn_batch);

                $agenceArr = $AgenceObj->getAgenceDatas($batch_id_agence);

                $agence_statut = 0;
                if(is_array($agenceArr) && count($agenceArr)>0) {
                    $agence_statut = $agenceArr['statut'];
                }
                
                switch ($agence_statut)
                {
                    case 1:
                    case 2:
                    case 3:
                        $agence_statut_label = $adsys["adsys_statut_agence"][$agence_statut];
                        break;
                }

                // Afficher les menus
                $MyMenu = new HTML_menu_gen("Traitements de nuit Multi-Agence<br /><br />Agence : <b>" . $batch_agence_desc . " (" . $batch_id_agence . ")</b><br /><br />Statut : <b>".$agence_statut_label."</b>");

                if($agence_statut == 1 || $agence_statut == 3){
                    $MyMenu->addItem(_("Fermeture agence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tnm-2&agc_statut=2", 213, "$http_prefix/images/fermeture_agence.gif","1");
                }  elseif ($agence_statut == 2) {
                    $MyMenu->addItem(_("Ouverture agence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tnm-2&agc_statut=1", 213, "$http_prefix/images/ouverture_agence.gif","1");
                }
                
                $MyMenu->addItem(_("Retour menu principal"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif", "0");

                $MyMenu->buildHTML();
                echo $MyMenu->HTMLCode;
            }
        }
        else
        {
            // Afficher la liste des batch exécutés
            $html = new HTML_GEN2();
            $html->setTitle(_("Archives des Traitements de nuit : Choix d'une periode"));

            $html->addField("DateDeb", _("Date de début :"), TYPC_DTE);
            $html->setFieldProperties("DateDeb",  FIELDP_HAS_CALEND, false);
            $html->addLink("DateDeb", "calendrier1", _("Calendrier"), "#");
            $codejs = "if (! isDate(document.ADForm.HTML_GEN_date_DateDeb.value)) ";
            $codejs .= "document.ADForm.HTML_GEN_date_DateDeb.value='';open_calendrier(getMonth(document.ADForm.HTML_GEN_date_DateDeb.value), getYear(document.ADForm.HTML_GEN_date_DateDeb.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_DateDeb');return false;";
            $html->setLinkProperties("calendrier1", LINKP_JS_EVENT, array("onclick" => $codejs));

            $html->addField("DateFin", _("Date de fin :"), TYPC_DTE);
            $html->setFieldProperties("DateFin",  FIELDP_HAS_CALEND, false);
            $html->addLink("DateFin", "calendrier2", _("Calendrier"), "#");
            $codejs = "if (! isDate(document.ADForm.HTML_GEN_date_DateFin.value)) ";
            $codejs .= "document.ADForm.HTML_GEN_date_DateFin.value='';open_calendrier(getMonth(document.ADForm.HTML_GEN_date_DateFin.value), getYear(document.ADForm.HTML_GEN_date_DateFin.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_DateFin');return false;";
            $html->setLinkProperties("calendrier2", LINKP_JS_EVENT, array("onclick" => $codejs));

            //CheckForm javascript
            $ChkJS = "\n\t\t\tif (document.ADForm.HTML_GEN_date_DateDeb.value == '') {msg += '-"._("Vous devez saisir une valeur pour la date de début")."\\n';ADFormValid=false;};\n";
            $ChkJS .= "\n\t\t\tif (document.ADForm.HTML_GEN_date_DateFin.value == '') {msg += '-"._("Vous devez saisir une valeur pour la date de fin")."\\n';ADFormValid=false;};\n";
            $ChkJS .= "\n\t\t\tif (! isDate(document.ADForm.HTML_GEN_date_DateDeb.value)) {msg += '-"._("Vous devez saisir une date de début")."\\n';ADFormValid=false;};\n";
            $ChkJS .= "\n\t\t\tif ( ! isDate(document.ADForm.HTML_GEN_date_DateFin.value)) {msg += '-"._("Vous devez saisir une date de fin")."\\n';ADFormValid=false;};\n";

            $html->addJS(JSP_BEGIN_CHECK, "JS3",$ChkJS);

            $html->setOrder(NULL,array("DateDeb","DateFin"));

            $html->addHiddenType("display_result", 1);
            $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
            $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
            $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Tnm-3');
            $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Tnm-1');
            $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

            $html->buildHTML();
            echo $html->getHTML();
        }
    }
}

?>