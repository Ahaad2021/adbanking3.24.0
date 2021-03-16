<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */


/**
 * Fichier de gestion des menus principaux
 *
 * Les écrans suivants sont définis :
 * - Cex-1 : Ecran Sélection de la table
 * - Cex-2 : Ecran Liste des champs extras
 * - Cex-3 : Ecran Ajout d'un champs dans une table
 * - Cex-4 : Ecran Confirmation ajout d'un champs
 * - Cex-5 : Ecran Modification d'un champs dans une table
 * - Cex-6 : Ecran confirmation Modification d'un champs
 * - Cex-7 : Ecran Confirmation suppression
 * Gestion des champs extras pour certaines tables
 * @package Parametrage
 */

require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_message.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/HTML_erreur.php';
$liste_agences = getAllIdNomAgence();
/* Les tables à paramétrer */
$tables = array("ad_cli" => _("Clients"),
                "ad_dcr" => _("Dossiers de crédit"));


asort($tables);
$SESSION_VARS['tables'] = $tables;
/*{{{ Cex-1 : Sélection de la table */
if ($global_nom_ecran == "Cex-1") {
	  unset($SESSION_VARS["select_agence"]);
  resetGlobalIdAgence();
  $MyPage = new HTML_GEN2(_("Gestion des champs extras"));
  //Liste des agence
  if (isSiege()) { //Si on est au siège
    $MyPage->addField("list_agence", "Liste des agences", TYPC_LSB);
    $MyPage->setFieldProperties("list_agence", FIELDP_ADD_CHOICES, $liste_agences);
    $MyPage->setFieldProperties("list_agence", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("list_agence", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("list_agence", FIELDP_DEFAULT,getNumAgence());
  }

  $MyPage->addField("table", _("Table de paramétrage"), TYPC_LSB);
   $MyPage->setFieldProperties("table", FIELDP_ADD_CHOICES, $tables);
  $MyPage->setFieldProperties("table", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("table", FIELDP_HAS_CHOICE_AUCUN, true);

  $MyPage->addButton("table", "param", _("Paramétrer"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("param", BUTP_PROCHAIN_ECRAN, "Cex-2");

  //Bouton formulaire
  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gen-12");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
  
}
/*}}}*/
/*{{{ Cex-2 : Liste des champs extras */
else if ($global_nom_ecran == 'Cex-2') {
  //Récupèration de l'id de l'agence sélectionnée
  if ($_POST['list_agence']=="" && $SESSION_VARS['select_agence']=="")
    $SESSION_VARS['select_agence']=$global_id_agence;
  elseif($SESSION_VARS['select_agence']=="")
  $SESSION_VARS['select_agence']=$_POST['list_agence'];
  setGlobalIdAgence($SESSION_VARS['select_agence']);
  if (isset($table)) $SESSION_VARS['table'] = $table;
  
  $title = _("Liste des champs extras de la table")." '".$SESSION_VARS['tables'][$SESSION_VARS['table']]."'";
  $myForm = new HTML_GEN2($title);
  $champsExtras = getChampsExtras($SESSION_VARS['table']);
  $xtHTML = "<TABLE align=\"center\" border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>";
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("Libellé champs")."</b></td><td>&nbsp;</td><td>&nbsp;</td></tr>";
  while (list(, $champs) = each($champsExtras)) {
      if ($SESSION_VARS['select_agence']==getNumAgence())
        $xtHTML .= "<tr bgcolor=\"$colb_tableau\"><td> <a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cex-5&id=".$champs['id']."\">".$champs['libel']."</a></td><td><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cex-5&id=".$champs['id']."\">"._("Mod")."</a></td><td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cex-5&id=".$champs['id']."\" onclick=\"return confirm('"._("Etes-vous sur de vouloir supprimer le champs")." \\'".$champs['libel']."\\' ?');\">"._("Sup")."</A></td></tr>";
      else
        $xtHTML .= "<tr bgcolor=\"$colb_tableau\"><td> <a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cex-5&id=".$champs['id']."\">".$champs['libel']."</a></td><td>Mod</td><td>Sup</td></tr>";
  }
  $xtHTML .= "</TABLE>";
  $myForm->addHTMLExtraCode("tableau", $xtHTML);
  $myForm->addHTMLExtraCode("br", "<BR><BR>");
  if ($SESSION_VARS['select_agence']==getNumAgence()) {
    $myForm->addFormButton(1,1,"new", _("Ajouter"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("new", BUTP_PROCHAIN_ECRAN, "Cex-3");
  }
  $myForm->addFormButton(1,2, "retour", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cex-1");
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/
/*{{{ Cex-3 : Ajout d'un champs dans une table */
else if ($global_nom_ecran == "Cex-3") {
  global $global_id_agence;

  //Ajout
  $MyPage = new HTML_GEN2(_("Ajout d'un champs extras dans")." ''".$SESSION_VARS['tables'][$SESSION_VARS['table']]."'");

  //Nom table
  $MyPage->addField("ntable", _("Table"), TYPC_TXT);
  $MyPage->setFieldProperties("ntable", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("ntable", FIELDP_DEFAULT, $SESSION_VARS['tables'][$SESSION_VARS['table']]);

  $ary_exclude = array();
  $ary_exclude["table_name"] = $SESSION_VARS['table'];
  $SESSION_VARS['ary_exclude'] = $ary_exclude;
  // Récupération des infos sur l'entrée de la table
  //$info = get_tablefield_info($SESSION_VARS['table'], NULL);
  $info = get_tablefield_info("champs_extras_table", NULL);
  $SESSION_VARS['info'] = $info;
  while (list($key, $value) = each($info)) { //Pour chaque champs de la table
      if (($key != "pkey") && //On n'insère pas les clés primaires
        (! array_key_exists($key, $ary_exclude))) { //On n'insère pas certains champs en fonction du contexte
      if (! $value['ref_field']) { //Si champs ordinaire
        $type = $value['type'];
        if ($value['traduit'])
          $type = TYPC_TTR;
        $fill = 0;
        if ((substr($type, 0, 2) == "in") && ($type != "int")) { //Si int avec fill zero
          $fill = substr($type, 2, 1);
          $type = "int";
        }
        $MyPage->addField($key, $value['nom_long'], $type);
        if ($fill != 0) $MyPage->setFieldProperties($key, FIELDP_FILL_ZERO, $fill);
      } else { //Si champs qui référence
        $MyPage->addField($key, $value['nom_long'], TYPC_LSB);
        $MyPage->setFieldProperties($key, FIELDP_ADD_CHOICES, $value['choices']);
      }
      $MyPage->setFieldProperties($key, FIELDP_IS_REQUIRED, $value['requis']);
    }
  }
   //Bouton
  $MyPage->addFormButton(1,1,"butval", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Cex-4");
  $MyPage->addFormButton(1,2,"butret", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Cex-2");
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/
/*{{{ Cex-4 : Confirmation ajout d'un champs dans une table */
else if ($global_nom_ecran == "Cex-4") {
  global $global_id_agence;

  //Ajout
  $MyPage = new HTML_GEN2(_("confirmation d'un champs extras dans")." ''".$SESSION_VARS['tables'][$SESSION_VARS['table']]."'");
  
  $ary_exclude = $SESSION_VARS['ary_exclude'];
  while (list($key, $value) = each($SESSION_VARS['info'])) {
    if (($key != "pkey") && (! array_key_exists($key, $ary_exclude))) { //On n'insére pas certains champs en fonction du contexte
      if ($value['type'] == TYPC_MNT) $DATA[$key] = recupMontant($ {$key});
      else if ($value['type'] == TYPC_BOL) {
        if (isset($ {$key}))
          $DATA[$key] = "t";
        else $DATA[$key] = "f";
      } else if ($value['type'] == TYPC_PRC)
        $DATA[$key] = "".(($ {$key}) / 100)."";
      //else if (($value['type'] == TYPC_TXT) && (${$key} == "0") && ($value['ref_field'] == 1400)) // il faut accepter les valeurs 0
      //$DATA[$key] = "NULL";//FIXME:je sais,ce n'est vraiment pas propre.Probléme d'intégrité référentielle sur les comptes comptables
      else if (($value['type'] == TYPC_TXT) && ($value['ref_field'] == 1400)) {
        // On considère que la valeur 0 pour les list box est le choix [Aucun]
        if ($ {"HTML_GEN_LSB_".$key}=="0")
          $DATA[$key] = "NULL";
        else
          $DATA[$key]= $ {"HTML_GEN_LSB_".$key
                         };

      } else $DATA[$key] = $ {
                               $key
                             };


      if ((($value['type'] == TYPC_MNT) || ($value['type'] == TYPC_INT) || ($value['type'] == TYPC_PRC)) && ($ {$key} == NULL || $ {$key} == "")) {
        $DATA[$key] = '0'; //NULL correspond à la valeur zéro pour les chiffres.  Ah bon ?  Ca limite l'usage des valeurs par défaut de PSQL... dommage. :(
      }
      
    }elseif(array_key_exists($key, $ary_exclude)){
    	$DATA[$key] = $ary_exclude[$key];
    }
  }
  //appel DB
  $myErr=ajout_table("champs_extras_table", $DATA);

  //HTML
  if ($myErr->errCode==NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation ajout"));
    $message = sprintf(_("Le champs a été ajoutée avec succès"),$SESSION_VARS['tables'][$SESSION_VARS['table']]);
    $MyPage->setMessage($message);
 		$MyPage->addButton(BUTTON_OK, "Cex-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $MyPage = new HTML_erreur(_("Echec de l'insertion"));
    $MyPage->setMessage($error[$myErr->errCode]);
    $MyPage->addButton(BUTTON_OK, "Cex-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/
/*{{{ Cex-5 : Modification d'un champs*/
else if ($global_nom_ecran == "Cex-5") {
 
  global $global_id_agence;

  //Ajout
  $MyPage = new HTML_GEN2(_("Modification d'un champs extras")." ''".$SESSION_VARS['tables'][$SESSION_VARS['table']]."'");

  //Nom table
  $MyPage->addField("ntable", _("Table"), TYPC_TXT);
  $MyPage->setFieldProperties("ntable", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("ntable", FIELDP_DEFAULT, $SESSION_VARS['tables'][$SESSION_VARS['table']]);

  $ary_exclude = array();
  $ary_exclude["table_name"] = $SESSION_VARS['table'];
  $SESSION_VARS['ary_exclude'] = $ary_exclude;
  $disabled_field = array();
  $disabled_field["type"] = 'type';
  $SESSION_VARS['disabled_field'] = $disabled_field;
  // Récupération des infos sur l'entrée de la table
  $SESSION_VARS['table_row_id'] = $id;
  $info = get_tablefield_info("champs_extras_table", $SESSION_VARS['table_row_id']);
  $SESSION_VARS['info'] = $info;
  while (list($key, $value) = each($info)) { //Pour chaque champs de la table
      if (($key != "pkey")&& //On n'insère pas les clés primaires
        (! array_key_exists($key, $ary_exclude))) { //On n'insère pas certains champs en fonction du contexte { //On n'insère pas certains champs en fonction du contexte
      if (! $value['ref_field']) { //Si champs ordinaire
        $type = $value['type'];
        if ($value['traduit'])
          $type = TYPC_TTR;
        $fill = 0;
        if ((substr($type, 0, 2) == "in") && ($type != "int")) { //Si int avec fill zero
          $fill = substr($type, 2, 1);
          $type = "int";
        }
        $MyPage->addField($key, $value['nom_long'], $type);
        if ($fill != 0) $MyPage->setFieldProperties($key, FIELDP_FILL_ZERO, $fill);
      } else { //Si champs qui référence
        $MyPage->addField($key, $value['nom_long'], TYPC_LSB);
        $MyPage->setFieldProperties($key, FIELDP_ADD_CHOICES, $value['choices']);
      }
      if ($type == TYPC_BOL) $value['val'] = ($value['val'] == 't');
      $MyPage->setFieldProperties($key, FIELDP_IS_REQUIRED,$value['requis'] );
      $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $value['val']);
    }
//    if(array_key_exists($key, $disabled_field))
//    	$MyPage->setFieldProperties($key, FIELDP_IS_LABEL, true);
  }
   //Bouton
  $MyPage->addFormButton(1,1,"butval", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Cex-6");
  $MyPage->addFormButton(1,2,"butret", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Cex-2");
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/
/*{{{ Cex-6 : Confirmation modification d'un champs*/
else if ($global_nom_ecran == "Cex-6") {
 
  global $global_id_agence;

  //Ajout
  $MyPage = new HTML_GEN2(_("Confiration modification d'un champs extras")." ''".$SESSION_VARS['tables'][$SESSION_VARS['table']]."'");

  $ary_exclude = $SESSION_VARS['ary_exclude'];
  $disabled_field = $SESSION_VARS['disabled_field'];
  // Parcours des infos sur l'entrée de la table
     while (list($key, $value) = each($SESSION_VARS['info'])) {
    if (($key != "pkey") && (! in_array($key, $ary_exclude))) { //On n'insére pas les clés primaires

      //On n'insére pas certains champs en fonction du contexte
      if ((($value['type'] == TYPC_MNT) || ($value['type'] == TYPC_INT) || ($value['type'] == TYPC_PRC))
          && ($ {$key} == NULL)) {
        $ {$key} = "0"; //NULL correspond à la valeur zéro pour les chiffres
      }

      //FIXME : je sais, ce n'est vraiment pas propre...
      //if (($value['type'] == TYPC_TXT) && (${$key} == 0) && ($value['ref_field'] == 1400))
      // ${$key} = "NULL";

      if (($value['type'] == TYPC_TXT) && ($value['ref_field'] == 1400)) {
        // On consodère que la valeur 0 pour les list box est le choix [Aucun]
        if ($ {"HTML_GEN_LSB_".$key}=="0")
          $ {$key} = "NULL";
        else
          $DATA[$key]= $ {"HTML_GEN_LSB_".$key
                         };
      }

      if ($value['type'] == TYPC_MNT) $DATA[$key] = recupMontant($ {$key});
      else if ($value['type'] == TYPC_BOL) {

        if (isset($ {$key})) $DATA[$key] = "t";
        else $DATA[$key] = "f";

      } else if ($value['type'] == TYPC_PRC) $DATA[$key] = "".(($ {$key}) / 100)."";
      else $DATA[$key] = $ {
                             $key
                           };
    }elseif(array_key_exists($key, $ary_exclude)){
    	$DATA[$key] = $ary_exclude[$key];
    }
  }
  //Mise à jour de la table : appel dbProcedure
  $myErr =  modif_table("champs_extras_table", $SESSION_VARS['info']['pkey'], $SESSION_VARS['table_row_id'], $DATA);

  //HTML
  if ($myErr->errCode==NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation modification"));
    $MyPage->setMessage(sprintf(_("L'entrée de la table '%s' a été modifiée avec succès !"),$SESSION_VARS['table_nom_long']));
    $MyPage->addButton(BUTTON_OK, "Cex-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $MyPage = new HTML_erreur(_("Echec de la modification"));
    $MyPage->setMessage($error[$myErr->errCode]);
    $MyPage->addButton(BUTTON_OK, "Cex-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>