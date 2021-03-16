<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [205] Ouverture de l'agence
 * Cette fonction appelle les écrans suivants :
 * - Oag-1 : Demande de confirmation d'ouverture
 * - Oag-2 : Ouverture de l'agence
 * - Oag-3 : Batch interactif
 *
 * @package Systeme
 **/

require_once 'lib/html/HTML_message.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'batch/divers.php';


/*{{{ Oag-1 : Demande de confirmation d'ouverture */
if ($global_nom_ecran == "Oag-1") {

  $MyPage = new HTML_message(_("Ouverture agence, demande confirmation"));
  $MyPage->setMessage(_("Etes-vous sûr de vouloir ouvrir l'agence ?")." (".sprintf(_("La date du jour est %s"),strftime("%A %d %B %Y")).")");
  $MyPage->addButton(BUTTON_OUI, "Oag-2");
  $MyPage->addButton(BUTTON_NON, "Gen-7");
  $MyPage->buildHTML();

  echo $MyPage->HTML_code;

}
/*}}}*/

/*{{{ Oag-2 : Ouverture de l'agence */
else if ($global_nom_ecran == "Oag-2") {

  setGlobalIdAgence(getNumAgence()); //Initialisation de $global_id_agence
  $result = ouverture_agence($global_id_agence);

  if ($result['result'] < 0) {//erreur lors de l'ouverture de l'agence
    switch ($result['result']) {

    case -1 :
      $msg=_("des logins possédant un guichet sont encore loggés")." (";
      while (list(,$value) = each($result['login'])) {
        $msg .= "'$value', ";
      }
      $msg = substr($msg,0,strlen($msg)-2);
      $msg .= ") !";
      break;

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
      $MyPage = new HTML_erreur(_("Erreur lors de l'exécution des traitements de nuit"));

      $AG = getAgenceDatas($global_id_agence);
      $last_batch = pg2phpDateBis($AG['last_batch']);
      $SESSION_VARS['last_batch'] = $last_batch;
      $last_batch_fr = strftime("%A %d %B %Y", mktime(0,0,0,$last_batch[0], $last_batch[1]+1, $last_batch[2]));

      $MyPage->setMessage("<b>"._("Attention")."</b>, ".sprintf(_("le traitement de nuit n'a <b>pas</b> pu être exécuté pour la date du %s et les dates suivantes éventuelles."),"<b>$last_batch_fr</b>")." <br />"._("Cliquez sur OK pour démarrer les traitements de fin de journée pour ces dates"));
      $MyPage->addButton(BUTTON_OK, "Oag-3");
      $MyPage->addButton(BUTTON_CANCEL, "Gen-7");
      $MyPage->buildHTML();

      echo $MyPage->HTML_code;
    }

    else if ($result['result'] == -7) { //frais tenue compte

      // Création environnement du batch : cette date est un global pour batch.php
      $last_batch = pg2phpDatebis(get_last_batch($global_id_agence));
      $date_jour = $last_batch[1];
      $date_mois = $last_batch[0];
      $date_annee = $last_batch[2];

      // Démarrage du batch pour les frais de tenue de compte
      require('batch/batch.php');

      $MyPage = new HTML_message(_("Traitement de nuit"));

      $msg = _("Les frais de tenue de compte ont été prélevés.");
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "Oag-2");
      $MyPage->buildHTML();

      echo $MyPage->HTML_code;
    } else { //message d'erreur pour tous les autres cas

      $MyPage = new HTML_erreur(_("Ouverture de l'agence"));
      $MyPage->setMessage(_("Impossible d'ouvrir l'agence")." : ".$msg);
      $MyPage->addButton(BUTTON_OK, "Gen-7");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }

  } //end if erreur lors de l'ouverture de l'agence

  else

  {
    // Pas d'erreur lors de l'ouverture de l'agence

    $global_statut_agence = 1;

    // Vérification de l'espace disque disponible
    $espace_total = disk_total_space("$lib_path/backup/");
    $espace_libre = disk_free_space("$lib_path/backup/");
    $pourcentage_libre = round($espace_libre / $espace_total * 100, 1);
    if ($pourcentage_libre < 10) {
      $msg = "<span style='color: red;'>".sprintf(_("Attention, le disque du serveur est presque plein (seulement %s%% disponible)."), $pourcentage_libre)."<br />"._("Il faut peut-être déplacer certains backups vers un CD.")."</span><br /><br />";
    } else {
      $msg = "";
    }
    $MyPage = new HTML_message(_("Ouverture de l'agence"));
    $MyPage->setMessage($msg._("L'agence a été ouverte avec succès!"));
    $MyPage->addButton(BUTTON_OK, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }

}
/*}}}*/

/*{{{ Oag-3 : Batch interactif */
else if ($global_nom_ecran == 'Oag-3') {
  if (!isset($parametrageOK)) {
    // Il faut vérifier le paramétrage des données importantes
    echo "<div class='batch'>\n";
    $result = verif_parametrage();
    echo "</div>";

    if ($result->errCode != NO_ERR) {
      // Houston we got a problem, where are we going now?
      debug($result, "retour verif_parametrage");
      $parametrageOK = false;
      $myPage = new HTML_GEN2(_("Echec lors de la vérification du parametrage"));
      $myPage->addHTMLExtraCode("erreur", "<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\">
                                <tr><td align=\"center\"><font color=\"".$colt_error."\">
                                "._("Pour éviter d'avoir des erreurs qui empêcheraient l'exécution du batch")."</font></td></tr>
                                <tr><td align=\"center\"><font color=\"".$colt_error."\">
                                "._("vous devriez")." ".$error[$result->errCode]."<b>".$result->param."</b></font></td></tr>
                                <tr><td>&nbsp;</td></tr></table>");
      debug($result->param,"result");
      $myPage->addHiddenType("parametrageOK", true);
      // Le profil a-t-il accès au paramétrage nécessaire ?
      switch ($result->errCode) {
      case ERR_PARAM_CPT_INT:
        if (check_access(294)) {
          $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
          $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Mta-1');
          $myPage->addHiddenType("table", "adsys_produit_epargne");
          $myPage->addHiddenType("HTML_GEN_LSB_contenu", $result->handler);
          break;
        }

      case ERR_PARAM_CPT_ASS:
        if (check_access(294)) {
        	setGlobalIdAgence(getNumAgence());
        	$SESSION_VARS['select_agence']=$global_id_agence;
          $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
          $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Cpc-2');
          $myPage->addHiddenType("HTML_GEN_LSB_libel", $result->handler);
          break;
        }

      case ERR_PARAM_OPE:
        if (check_access(420)) {
          $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
          $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Gop-2');
          $myPage->addHiddenType("id_oper", $result->param);
          break;
        }

      case ERR_PARAM_AGC:
        if (check_access(294)) {
          $myPage->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
          $myPage->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Mta-1');
          $myPage->addHiddenType("table", "ad_agc");
          $myPage->addHiddenType("HTML_GEN_LSB_contenu", $result->handler);
          break;
        }

      default:
        $myPage->addFormButton(1, 1, "annuler", _("Annuler"), TYPB_SUBMIT);
        $myPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
      }
      $myPage->addFormButton(1, 2, "continuer", _("Continuer"), TYPB_SUBMIT);
      $myPage->setFormButtonProperties("continuer", BUTP_PROCHAIN_ECRAN, 'Oag-3');
      $myPage->buildHTML();
      echo $myPage->getHTML();
    } else {
      $parametrageOK = true;
    }
  }

  if ($parametrageOK) {
    // On peut alors exécuter le batch !
    $last_batch = $SESSION_VARS["last_batch"];
    // On ferme la session pour pouvoir utiliser flush() et envoyer les données HTTP en continu.
    // Normalement, il n'y a pas lieu de modifier des variables de session dans le batch, donc on peut fermer la session sans problème.
    session_write_close();

    // On passe au jour suivant
    $tomorrow = mktime(0,0,0,$last_batch[0],$last_batch[1]+1, $last_batch[2]);
    $last_batch[0] = date("m", $tomorrow);
    $last_batch[1] = date("d", $tomorrow);
    $last_batch[2] = date("Y", $tomorrow);

    //exécuter le batch pour toutes les dates où il n'a pas eu lieu
    while (mktime(0,0,0,$last_batch[0],$last_batch[1], $last_batch[2]) < mktime(0,0,0,date("m"), date("d"), date("Y"))) {
      // Création environnement du batch : cette date est un global pour batch.php
      $date_jour = sprintf("%02d", $last_batch[1]);
      $date_mois = sprintf("%02d", $last_batch[0]);
      $date_annee = $last_batch[2];

      // Démarrage du batch
      require('batch/batch.php');

      // Passage au jour suivant
      $tomorrow = mktime(0,0,0,$last_batch[0],$last_batch[1]+1, $last_batch[2]);
      $last_batch[0] = date("m", $tomorrow);
      $last_batch[1] = date("d", $tomorrow);
      $last_batch[2] = date("Y", $tomorrow);

      // On envoie les données HTTP en continu (jour après jour) {@link PHP_MANUAL#flush}
      flush();
    }
    // Quand tout est fini, message de confirmation
    $MyPage = new HTML_message(_("Exécution du batch"));
    $MyPage->setMessage(_("Fin de l'exécution du batch"));
    $MyPage->addButton(BUTTON_OK, "Oag-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>