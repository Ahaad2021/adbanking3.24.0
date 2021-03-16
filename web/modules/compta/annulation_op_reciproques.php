<?php

/**
 * Annulations opérations réciproques passées entre le siège et les agences
 *
 * Cette fonction comprend les écrans :
 * - Ano-1: sélection de la période des mouvements à annuler
 * - Ano-2: Affichage de la liste des mouvements à annuler
 * - Ano-3: Marquer les mouvements comme annuler (mettre le flag consolide à true)
 * @package Compta
 */

if ($global_nom_ecran == "Ano-1") { // sélection de la période des mouvements réciproques
  $MyPage = new HTML_GEN2(_("Sélection de la période"));

  // Type de période
  $MyPage->addField("modele", _("Période d'annulation"), TYPC_LSB);
  $choices = array(1 => _("Annulation quotidienne"), 2 => _("Annulation mensuelle"), 3 => _("Annulation personalisée"));
  $MyPage->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);
  $MyPage->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);

  $MyPage->addHTMLExtraCode("espace1", "<br/><br/>");
  $MyPage->addField("date_journaliere", _("Date"), TYPC_DTE);
  $MyPage->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));


  $MyPage->addHTMLExtraCode("espace2", "<br/><br/>");
  $MyPage->addTableRefField("mois", _("Mois"), "adsys_mois");
  $MyPage->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));

  $MyPage->addTableRefField("annee", _("Année"), "adsys_annee");
  $MyPage->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));

  $MyPage->addHTMLExtraCode("espace3", "<br/><br/>");
  $MyPage->addField("date_debut", _("Date début de période"), TYPC_DTE);
  $MyPage->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));

  $MyPage->addField("date_fin", _("Date fin de période"), TYPC_DTE);
  $MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

  // bouton valider
  $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_AXS, 474);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ano-2");
  $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);

  // bouton annuler
  $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // JavaScript
  // Désactivation des éléments
  $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
            document.ADForm.HTML_GEN_LSB_annee.disabled = true;
            document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
            document.ADForm.HTML_GEN_date_date_debut.disabled = true;
            document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
  $MyPage->addJS(JSP_FORM, "JSInit", $JSInit);

  // Code javascript pour activer les champs
  $JS = "\n\tfunction activateFields()
      {
        if (document.ADForm.HTML_GEN_LSB_modele.value == 1)
      {
        document.ADForm.HTML_GEN_date_date_journaliere.disabled = false;
        document.ADForm.HTML_GEN_LSB_mois.disabled = true;
        document.ADForm.HTML_GEN_LSB_annee.disabled = true;
        document.ADForm.HTML_GEN_date_date_debut.disabled = true;
        document.ADForm.HTML_GEN_date_date_fin.disabled = true;
      }
        else if (document.ADForm.HTML_GEN_LSB_modele.value == 2)
      {
        document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
        document.ADForm.HTML_GEN_LSB_mois.disabled = false;
        document.ADForm.HTML_GEN_LSB_annee.disabled = false;
        document.ADForm.HTML_GEN_date_date_debut.disabled = true;
        document.ADForm.HTML_GEN_date_date_fin.disabled = true;
      }
        else if (document.ADForm.HTML_GEN_LSB_modele.value == 3)
      {
        document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
        document.ADForm.HTML_GEN_LSB_mois.disabled = true;
        document.ADForm.HTML_GEN_LSB_annee.disabled = true;
        document.ADForm.HTML_GEN_date_date_debut.disabled = false;
        document.ADForm.HTML_GEN_date_date_fin.disabled = false;
      }
      }";

  $MyPage->addJS(JSP_FORM, "JSActivate", $JS);

  // Code javascript pour la vérification des champs obligatoires
  $JSCheck = "\n\tif (document.ADForm.HTML_GEN_LSB_modele.value == 1)
           {
             if (document.ADForm.HTML_GEN_date_date_journaliere.value == '')
           {
             msg += '- "._("La date de la balance doit être renseignée")."\\n';
             ADFormValid = false;
           }
             if (!isDate(document.ADForm.HTML_GEN_date_date_journaliere.value))
           {
             msg += '- "._("Le format du champs \" Date de la balance\" est incorrect")."\\n';
             ADFormValid = false;
           }
           }
             else if (document.ADForm.HTML_GEN_LSB_modele.value == 2)
           {
             if (document.ADForm.HTML_GEN_LSB_mois.value == '')
           {
             msg += '- "._("Le mois de la balance doit être renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.HTML_GEN_LSB_annee.value == '')
           {
             msg += '- "._("L\\'année de la balance doit être renseignée")."\\n';
             ADFormValid = false;
           }
           }
             else if (document.ADForm.HTML_GEN_LSB_modele.value == 3)
           {
             if (document.ADForm.HTML_GEN_date_date_debut.value == '')
           {
             msg += '- "._("La date de début de période de la balance doit être renseignée")."\\n';
             ADFormValid = false;
           }
             if (!isDate(document.ADForm.HTML_GEN_date_date_debut.value))
           {
             msg += '- "._("Le format du champs \" Date de début\" est incorrect")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.HTML_GEN_date_date_fin.value == '')
           {
             msg += '- "._("La date de fin de période de la balance doit être renseignée")."\\n';
             ADFormValid = false;
           }
             if (!isDate(document.ADForm.HTML_GEN_date_date_fin.value))
           {
             msg += '- "._("Le format du champs \" Date de fin\" est incorrect")."\\n';
             ADFormValid = false;
           }
             if (!isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin.value))
           {
             msg += '- "._("La date de début doit être antérieure à la date de fin")."';
             ADFormValid = false;
           }
           }";

  $MyPage->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);
  $MyPage->setFieldProperties("modele", FIELDP_JS_EVENT, array ("onchange" => "activateFields();"));
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

elseif ($global_nom_ecran == "Ano-2") { // affichage de la liste des mouvements à annuler
  $SESSION_VARS['total_devise'] = array(); // totaux par devise

  // récupération de la saisie de l'écran précédent
  if (isset($modele)) { // si on vient de Ano-1
    // Construction de la période selon le modèle choisi
    if ($modele == 1) { // Rappport journal quotidien
      $SESSION_VARS['date_debut'] = $date_journaliere;
      $SESSION_VARS['date_fin'] = $date_journaliere;
    } else if ($modele == 2) { // Rappport journal mensuel
      $SESSION_VARS['date_debut'] = "01/$mois/$annee";
      $SESSION_VARS['date_fin'] = date("d/m/Y", mktime(0,0,0,$mois+1,0,$annee));
    } else if ($modele == 3) { // Rappport journal personnalisé
      $SESSION_VARS['date_debut'] = $date_debut;
      $SESSION_VARS['date_fin'] = $date_fin;
    }

    // récupération des mouvements réciproques de la période pouvant être annulés: qui ne sont pas encore consolidés
    $consolide = 'f';
    $liste_ag = getAllIdNomAgence();
    $SESSION_VARS["liste_op"] = getMouvementsReciproques($SESSION_VARS['date_debut'], $SESSION_VARS['date_fin'], $consolide, $liste_ag); debug($SESSION_VARS["liste_op"],"list op");
  }

  $MyPage = new HTML_GEN2(_("Mouvements réciproques à contrepasser"));

  // date de début période
  $MyPage->addField("date_debut", _("Date début de période"), TYPC_DTE);
  $MyPage->setFieldProperties("date_debut", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, $SESSION_VARS['date_debut']);

  // date de fin de période
  $MyPage->addField("date_fin", _("Date fin de période"), TYPC_DTE);
  $MyPage->setFieldProperties("date_fin", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, $SESSION_VARS['date_fin']);


  // affichage de la liste des mouvements
  $table = new HTML_TABLE_table(8, TABLE_STYLE_ALTERN);
  $table->set_property("title","");
  $table->add_cell(new TABLE_cell(_("N°")));
  $table->add_cell(new TABLE_cell(_("Date")));
  $table->add_cell(new TABLE_cell(_("Opération")));
  $table->add_cell(new TABLE_cell(_("Compte")));
  $table->add_cell(new TABLE_cell(_("Intitulé compte")));
  //pour afficher l'id_ag du mouvement
  $table->add_cell(new TABLE_cell(_("Numéro agence")));
  $table->add_cell(new TABLE_cell(_("Débit")));
  $table->add_cell(new TABLE_cell(_("Crédit")));

  $i = 1;
  if (is_array($SESSION_VARS["liste_op"]))
    foreach($SESSION_VARS["liste_op"] as $key=>$value) {
    $table->add_cell(new TABLE_cell($i));
    $table->add_cell(new TABLE_cell(pg2phpDate($value['date_comptable'])));
    $table->add_cell(new TABLE_cell($value['libel_ecriture']));
    $table->add_cell(new TABLE_cell($value['compte']));
    $table->add_cell(new TABLE_cell($value['libel_cpte_comptable']));
    //pour afficher l'id_ag du mouvement
    $table->add_cell(new TABLE_cell($value['id_ag']));
    if ($value['sens'] == 'd') {
      $table->add_cell(new TABLE_cell(afficheMontant($value['montant'])));
      $table->add_cell(new TABLE_cell(''));
      //$SESSION_VARS['total_debit'] += $value['montant'];
      $SESSION_VARS['total_devise'][$value['devise']]['debit'] += $value['montant'];
    }
    elseif($value['sens'] == 'c') {
      $table->add_cell(new TABLE_cell(''));
      $table->add_cell(new TABLE_cell(afficheMontant($value['montant'])));
      //$SESSION_VARS['total_credit'] += $value['montant'];
      $SESSION_VARS['total_devise'][$value['devise']]['credit'] += $value['montant'];
    }
    $table->set_row_childs_property("align","left");
    $i++;
  }

  // affichage totaux par devise
  foreach($SESSION_VARS['total_devise'] as $devise=>$value) {
    $table->add_cell(new TABLE_cell(''));
    $table->add_cell(new TABLE_cell(''));
    $table->add_cell(new TABLE_cell(''));
    $table->add_cell(new TABLE_cell(''));
    $table->add_cell(new TABLE_cell(''));
    $table->add_cell(new TABLE_cell(sprintf(_('Total en %s'),$devise)));
    if ($value['debit'] != NULL)
      $table->add_cell(new TABLE_cell(afficheMontant($value['debit'])));
    else
      $table->add_cell(new TABLE_cell(afficheMontant(0)));

    if ($value['credit'] != NULL)
      $table->add_cell(new TABLE_cell(afficheMontant($value['credit'])));
    else
      $table->add_cell(new TABLE_cell(afficheMontant(0)));
    $table->set_row_childs_property("bold");
  }

  $table->set_property("align", "center");
  $table->set_property("border", $tableau_border);
  $table->set_property("bgcolor", $colb_tableau);

  // génération du tableau des mouvements
  $html = $table->gen_HTML();

  // bouton valider
  $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_AXS, 474);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ano-3");
  $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);

  // retour menu précédent
  $MyPage->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Ano-1");
  $MyPage->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  // retour menu comptable
  $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->addHTMLExtraCode("html",$html);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}


elseif ($global_nom_ecran == "Ano-3") { // Marquer les mouvements comme annuler (mettre le flag consolide à true)
                                       //FIXME: le champ consolide de ad_mouvement devrais etre mis a false
  global $global_id_agence;

// vérification  des totaux par devise
  foreach($SESSION_VARS['total_devise'] as $devise=>$value) {
    // total débit doit être = total crédit pour chaque devise
    if ($value['debit'] != $value['credit']) {
      $html_err = new HTML_erreur(_("Echec passage écriture d'annulation"));
      $html_err->setMessage(sprintf(_("Le total du débit n'est pas égal au total du crédit pour la devise %s"), $devise));
      $html_err->addButton("BUTTON_OK", 'Ano-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }
   }
  // recuperation mouvements qui doivent être marquer consolidé(annulé lors de l'edition des rapports consolidé')
  $num_op = 1;
  if (is_array($SESSION_VARS["liste_op"]))
    foreach($SESSION_VARS["liste_op"] as $key=>$value) {
    $DATA[$num_op]['mouvement_consolide'] = $value['id_mouvement']; // mouvement consolidé (annulé)
    $DATA[$num_op]['id_ag'] = $value['id_ag'];
    $num_op++;
   }

  $fonction = 474; // passage annulation mouvements en mettant le flag consolide(table ad_mouvement) a true
  $erreur = annulationEcrituresComptables($DATA);
  if ($erreur->errCode == NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation annulation mouvements"));
    $MyPage->setMessage(_("Les mouvements ont été annulés par contrepassation avec succès !")."<br/><br/>"._("Numéro de transaction")." :<b><code> ".sprintf("%09d", $erreur->param)."</code></b>");
    $MyPage->addButton(BUTTON_OK, "Gen-14");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Annulation mouvements réciproques"));
    $html_err->setMessage(_("Echec")." : ".$error[$erreur->errCode].$erreur->param);
    $html_err->addButton("BUTTON_OK", 'Ano-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
?>