<?php

/**
 * Logout d'un utilisateur
 * Cette opération comprends les écrans :
 * - Out-1 : Vérification encaisse si le login a un guichet associé
 * - Out-2 : Demande de confirmation du logout
 * - Out-3 : Confirmation de la déconnexion
 * @since 6/12/2001
 * @package Systeme
 **/

require_once 'lib/misc/VariablesSession.php';
require_once 'lib/dbProcedures/login_func.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agency_banking.php';

global $global_nom_login;
if (($global_id_guichet == 0  && $global_nom_ecran == "Out-1") || (isProfilAgent($global_nom_login) && $global_nom_ecran == "Out-1" )) {
   //On passe direct à l'écran 2
    $global_nom_ecran = "Out-2";
}

if ($global_nom_ecran == "Out-1") {
  if ($global_id_guichet > 0) { //Si il existe un guichet associé au login on demande l'encaisse
    $libel_devise=array();
    $temp=get_table_devises();
    $MyPage = new HTML_GEN2(_("Quitter"));
    $js = "var bon=0; var message=''; ";
    foreach ($temp as $key => $value) {
      $nom_court = "encaisse".$key;
      $nom_long =_("Encaisse").$key;
      array_push($libel_devise,$key);
      setMonnaieCourante($key);
      $MyPage->addField($nom_court,$nom_long,TYPC_MNT);
      if ($global_billet_req) {
        $MyPage->setFieldProperties($nom_court, FIELDP_HAS_BILLET, true);
        $MyPage->setFieldProperties($nom_court, FIELDP_SENS_BIL, SENS_BIL_CAISSE_SEULE);
        $MyPage->setFieldProperties($nom_court, FIELDP_DEFAULT, 0);
      }
      $MyPage->setFieldProperties($nom_court, FIELDP_IS_REQUIRED, true);

      $encaisse = get_encaisse($global_id_guichet,$key);

      $js.= " \nif((recupMontant(document.ADForm.".$nom_court.".value) != $encaisse) && (document.ADForm.".$nom_court.".value != ''))";
      $js .= " { bon=1; message+='".sprintf(_("-Le montant de l\\'encaisse pour la devise \"%s\" ne correspond pas !"),$key)."\\n';}";
    }
    $js .= " if( bon== 1){ ";
    $js .= "  ++nbre_tentative;";
    $js .= " if (nbre_tentative < $nbre_max_tentative_encaisse)";
    $js .= " { alert(message);ADFormValid = false; } else{";
    $js .= "alert('".sprintf(_("Ceci est votre '%s'ème tentative : veuillez vous adresser auprès d\\'un administrateur !"),"+nbre_tentative+")."');";
    $js .= "    ADFormValid = true; assign('Out-2');}";
    $js .= "  }";

    $MyPage->addFormButton(1,1,"butok",_("Valider"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Out-2");

    $MyPage->addFormButton(2,1,"butanul",_("Annuler déconnexion"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butanul", BUTP_PROCHAIN_ECRAN, "Gen-3");
    $MyPage->setFormButtonProperties("butanul", BUTP_CHECK_FORM, false);


    $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);
    $MyPage->addJS(JSP_FORM, "js2", "nbre_tentative=0;assign('Gen-3');ADFormValid = false;");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
}

elseif ($global_nom_ecran == "Out-2") {
      $MyPage = new HTML_message(_("Quitter"));
      $MyPage->setMessage(sprintf(_("Etes-vous sûr de vouloir quitter %s ?"), $ProjectName));
      $MyPage->addButton(BUTTON_OUI, "Out-3");
      $MyPage->addButton(BUTTON_NON, "Gen-3");

// Indique à Out-3 qu'il n'y a pas eu de timeout
      $SESSION_VARS['timeout'] = false;
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
}

elseif ($prochain_ecran == "Out-3") {
  //On inscrit le log système
  ajout_log_systeme(date("H:i:s d F Y"), _('Déconnexion confirmée'), $global_nom_login, $REMOTE_ADDR);

  if ($global_id_guichet > 0 && $SESSION_VARS['timeout'] == false)  // S'il y a un guichet et pas de timeout
    fermetureGuichet($global_id_guichet);   // On ferme le guichet

  // On supprime la session courante
  delete_session($global_nom_login, false);

  echo "<script type=\"text/javascript\">\n";
  echo "window.parent.location = \"$SERVER_NAME/login/login.php\";\n";
  echo "</script>\n";
}

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>