<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Gestion du plan comptable
 * @author Thomas Fastenakel
 * @since 26/08/2003
 * @package Compta
 **/

require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/dbProcedures/compta.php';

/*{{{ Ppc-1 : Plan comptable */
if ($global_nom_ecran == 'Ppc-1') {
  global $global_id_agence;
  //Récupération des comptes comptables
  unset($SESSION_VARS["cpte_centralise"]);
  $Classe = getClassesComptables();
  set_time_limit(0);
  $myForm = new HTML_GEN2(_("Gestion du plan comptable"));

  set_time_limit (0);
  //Affichage des classes comptables
  if ($global_multidevise) {
    $myTable =& $myForm->addHTMLTable("plan_comptable", 7, TABLE_STYLE_ALTERN);
  } else {
    $myTable =& $myForm->addHTMLTable("plan_comptable", 6, TABLE_STYLE_ALTERN);
  }
  $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("LIBELLE"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("COMPARTIMENT"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("SENS"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("DEBITEUR"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("CREDITEUR"), 1, 1));
  if ($global_multidevise) {
    $myTable->add_cell(new TABLE_cell(_("DEVISE"), 1, 1));
  }

  $Classe = getClassesComptables();

  while (list(,$cla) = each($Classe)) {
    $myTable->add_cell(new TABLE_cell("<b>".$cla['numero_classe']."</b>", 1, 1));
    $myTable->add_cell(new TABLE_cell("<b>".$cla['libel_classe']."</b>", 1, 1));
    $myTable->add_cell(new TABLE_cell("", 1, 1));
    $myTable->add_cell(new TABLE_cell("", 1, 1));
    $myTable->add_cell(new TABLE_cell("", 1, 1));
    $myTable->add_cell(new TABLE_cell("", 1, 1));
    if ($global_multidevise) {
      $myTable->add_cell(new TABLE_cell("", 1, 1));
    }

    $CC = getComptesComptables();
    while (list(,$compte) = each($CC)) {
      $tampon = $compte['num_cpte_comptable'];

      if ($tampon[0] == $cla['numero_classe']) {
        $myTable->add_cell(new TABLE_cell_link($compte['num_cpte_comptable'],"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ppc-7&num_cpte_comptable=".$compte['num_cpte_comptable']), 1, 1);
        $myTable->add_cell(new TABLE_cell($compte['libel_cpte_comptable'], 1, 1));
        $myTable->add_cell(new TABLE_cell(adb_gettext($adsys['adsys_compartiment_comptable'][$compte['compart_cpte']]), 1, 1));
        $myTable->add_cell(new TABLE_cell(adb_gettext($adsys['adsys_cpte_compta_sens'][$compte['sens_cpte']]), 1, 1));
        if ($compte['devise'] != NULL) {
          setMonnaieCourante($compte["devise"]);
          $solde_debit="";
          $solde_credit="";
          if(($compte['solde']>0) ){
          	$solde_credit=$compte['solde'];
          } elseif( ($compte['solde']<0)  ) {
          	$solde_debit=abs($compte['solde']);
          } elseif( ($compte['solde']==0) && ($compte['sens_cpte']==1 OR $compte['sens_cpte']==3)) {
          	$solde_debit=0;
          } elseif (($compte['solde']==0) && ($compte['sens_cpte']==2) ){
          	$solde_credit=0;
          }
          $myTable->add_cell(new TABLE_cell(afficheMontant($solde_debit, false), 1, 1));
          $myTable->add_cell(new TABLE_cell(afficheMontant($solde_credit, false), 1, 1));

        } else {
          $myTable->add_cell(new TABLE_cell("", 1, 1));
          $myTable->add_cell(new TABLE_cell("", 1, 1));
        }
        if ($global_multidevise) {
          $myTable->add_cell(new TABLE_cell($compte['devise'], 1, 1));
        }
      }
    }
  }

  $myForm->addFormButton(1, 1, "ajout_princ", _("Ajouter un compte principal"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajout_sous", _("Ajouter un sous compte"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "supp_cpt", _("Supprimer un compte"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 4, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajout_princ", BUTP_PROCHAIN_ECRAN, 'Ppc-4');
  $myForm->setFormButtonProperties("ajout_sous", BUTP_PROCHAIN_ECRAN, 'Ppc-2');
  $myForm->setFormButtonProperties("supp_cpt", BUTP_PROCHAIN_ECRAN, 'Ppc-10');
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-14');

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ Ppc-2 : Ajout d'un sous-compte */
else if ($global_nom_ecran == 'Ppc-2') {
  global $global_id_agence;
  // Choix du compte centralisateur
  $SESSION_VARS["cpte_centralise"] = '';
  $SESSION_VARS['infos_sous_cptes'] = array(); // tableau contenant les infos sur les sous-comptes à créer
  $myForm = new HTML_GEN2(_("Choix du compte centralisateur"));

  $cptes = getNumLibelComptables();
  
 // generation de select sans compte is_actif=false i.e compte deja supprimer
  $myForm->addField ( "cpte_centralise", _ ( "Compte centralisateur" ),TYPC_LSB);
  $myForm->setFieldProperties ( "cpte_centralise",FIELDP_IS_REQUIRED,true);
  foreach ($cptes as $key => $value) {
  $myForm->setFieldProperties ( "cpte_centralise",FIELDP_ADD_CHOICES,array($key =>$key ." ". $value['libel_cpte_comptable']));
  }

  $myForm->addFormButton(1, 1, "ajout", _("Ajouter un sous compte"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, 'Ppc-3');
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ppc-1');
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
 
  $myForm->buildHTML();
  echo $myForm->getHTML();

}
/*}}}*/

/*{{{ Ppc-3 : Ajout d'un sous-compte */
else if ($global_nom_ecran == 'Ppc-3') {
  /* Ajout des sous-comptes */
  global $adsys, $global_id_agence;

  $myForm = new HTML_GEN2(_("Ajout d'un sous compte"));//entete

  if ($nbr_sous_cpte == NULL) {
    if ($SESSION_VARS['nbr_sous_cpte'] == NULL) {
      $SESSION_VARS['nbr_sous_cpte'] = 4;
    }
  } else {
    $SESSION_VARS['nbr_sous_cpte'] = $nbr_sous_cpte;
  }

	$myForm->addHiddenType("nbr_sous_cpte", $SESSION_VARS['nbr_sous_cpte']);

  if ($cpte_centralise != NULL) {
    $SESSION_VARS['cpte_centralise'] = $cpte_centralise;
  } else
    $cpte_centralise = $SESSION_VARS["cpte_centralise"];
//ajoute tous les sous comptes lister
  for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
    if ($ {'extension'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['extension'.$i] = $ {'extension'.$i};
    }
    if ($ {'libel_cpte_comptable'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['libel_cpte_comptable'.$i] = $ {'libel_cpte_comptable'.$i};
    }
    if ($ {'compart_cpte'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['compart_cpte'.$i] = $ {'compart_cpte'.$i};
    }
    if ($ {'sens_cpte'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['sens_cpte'.$i] = $ {'sens_cpte'.$i};
    }
    if ($ {'libeldevise'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['libeldevise'.$i] = $ {'libeldevise'.$i};
    }
    if ($ {'numero_compte'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['numero_compte'.$i] = $ {'numero_compte'.$i};
    }
    if ($ {'solde'.$i} != NULL) {
      $SESSION_VARS['infos_sous_cptes']['solde'.$i] = $ {'solde'.$i};
    }
    if ($ {'cpte_provision'.$i} != NULL)  {
    	$SESSION_VARS['infos_sous_cptes']['cpte_provision'.$i] = $ {'cpte_provision'.$i};
    }

  }

  // numéro compte centralisateur
  $param["num_cpte_comptable"] = $cpte_centralise;
  $infos_cpte_centralise = getComptesComptables($param);

  $include = array("num_cpte_comptable","libel_cpte_comptable","compart_cpte","sens_cpte");
  $myForm->addTable("ad_cpt_comptable", OPER_INCLUDE, $include);

  $def = new FILL_HTML_GEN2();
  $def->addFillClause("num", "ad_cpt_comptable");
  $def->addCondition("num", "id_ag", $global_id_agence);
  $def->addCondition("num", "num_cpte_comptable", $cpte_centralise);

  $def->addManyFillFields("num", OPER_INCLUDE, $include);
  $def->fill($myForm);

  $myForm->setFieldProperties("num_cpte_comptable", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("libel_cpte_comptable", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("compart_cpte", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("sens_cpte", FIELDP_IS_LABEL, true);

  // solde compte centralisateur
  if ($infos_cpte_centralise[$cpte_centralise]["compart_cpte"]==3 or $infos_cpte_centralise[$cpte_centralise]["compart_cpte"]==4)
    $SESSION_VARS['solde_reparti'] = calculeSoldeCpteGestion($cpte_centralise);
  else
    $SESSION_VARS['solde_reparti'] = $infos_cpte_centralise[$cpte_centralise]["solde"];

  $myForm->addField("solde",_("Solde compte" ),TYPC_TXT);
  $myForm->setFieldProperties("solde",FIELDP_IS_LABEL ,true);
  $myForm->setFieldProperties("solde",FIELDP_DEFAULT , abs($SESSION_VARS['solde_reparti']));

  // devise du compte centralisateur
  $myForm->addField("devise",_("Devise du compte" ),TYPC_TXT);
  $myForm->setFieldProperties("devise",FIELDP_IS_LABEL ,true);
  $myForm->setFieldProperties("devise",FIELDP_DEFAULT , $infos_cpte_centralise[$cpte_centralise]["devise"]);

  $myForm->addHTMLExtraCode("br1", "<br>");

  // Tableau de saisie des sous-comptes
  $myTable =& $myForm->addHTMLTable("sous_comptes", 9, TABLE_STYLE_ALTERN);

  // En-tête du tableau
  $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Extension"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Numéro"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Libellé"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Sens"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Solde"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Devise"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Compte provision"), 1, 1));

  $different_devise = get_table_devises();


  //recuperation listes des cptes
	$infos_cpte_prov = getComptesComptables();
	//html pr les options des cpte provsion
  foreach($infos_cpte_prov as $key=>$value) {
  	 $cpteprovoptionhtml .= "<option>$key</option>";
  }

  for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
    // Numéro de  la ligne
    $myTable->add_cell(new TABLE_cell($i, 1, 1));

    // Extension du compte
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "extension$i", $SESSION_VARS['infos_sous_cptes']['extension'.$i], "checkAndComput($i)"));

    // Numéro compte comptable
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "num_cpte_comptable$i", $SESSION_VARS['infos_sous_cptes']['numero_compte'.$i], "", "", true));

    // Libellé compte comptable
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "libel_cpte_comptable$i", $SESSION_VARS['infos_sous_cptes']['libel_cpte_comptable'.$i]));

    // Compartiment
    $compart_cpte_lsb = "<select name=\"compart_cpte$i\">";
    $compart_cpte_lsb .= "<option value=\"0\">[Aucun]</option>";
    foreach($adsys["adsys_compartiment_comptable"] as $key=>$value) {
      if ($key == $SESSION_VARS['infos_sous_cptes']['compart_cpte'.$i]) {
        $compart_cpte_lsb .= "<option value=$key selected>$value</option>";
      } else {
        $compart_cpte_lsb .= "<option value=$key>$value</option>";
      }
    }
    $compart_cpte_lsb .= "</select>\n";
    $myTable->add_cell(new TABLE_cell($compart_cpte_lsb, 1, 1));

    // Sens naturel du compte
    $sens_cpte_lsb = "<select name=\"sens_cpte$i\">";
    $sens_cpte_lsb .= "<option value=\"0\">[Aucun]</option>";
    foreach($adsys["adsys_cpte_compta_sens"] as $key=>$value) {
      if ($key == $SESSION_VARS['infos_sous_cptes']['sens_cpte'.$i]) {
        $sens_cpte_lsb .= "<option value=$key selected>$value</option>";
      } else {
        $sens_cpte_lsb .= "<option value=$key>$value</option>";
      }
    }
    $sens_cpte_lsb .= "</select>\n";
    $myTable->add_cell(new TABLE_cell($sens_cpte_lsb, 1, 1));

    // Solde du compte comptable
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "solde$i", $SESSION_VARS['infos_sous_cptes']['solde'.$i], "value = formateMontant(value)"));

    // Devise du compte
    $devisehtml = "<SELECT NAME=\"libeledevise$i\">";
    $devisehtml .= "<option>[Aucun]</option>";
    foreach($different_devise as $key=>$value) {
      if ($key == $SESSION_VARS['infos_sous_cptes']['libeldevise'.$i]) {
        $devisehtml .= "<option selected>$key</option>";
      } else {
        $devisehtml .= "<option>$key</option>";
      }
    }
    $devisehtml .= "</SELECT>\n";
    $myTable->add_cell(new TABLE_cell($devisehtml, 1, 1));

    //compte provision
    $cpteprovhtml = "<SELECT NAME=\"cpte_provision$i\">";
    $cpteprovhtml .= "<option>[Aucun]</option>";
    if ($SESSION_VARS['infos_sous_cptes']['cpte_provision'.$i] != NULL) {
    	$cpteprovhtml .= "<option selected>".$SESSION_VARS['infos_sous_cptes']['cpte_provision'.$i]. "</option>";
    }
    $cpteprovhtml.=$cpteprovoptionhtml;
    $cpteprovhtml .= "</SELECT>\n";
    $myTable->add_cell(new TABLE_cell($cpteprovhtml, 1, 1));

    // Champ caché pour répurer le numéro du compte car il est grisé
    $myForm->addHiddenType("numero_compte$i", $SESSION_VARS['infos_sous_cptes']['numero_compte'.$i]);
  }

  /* Gestion des soldes des sous-comptes */
  $JSform_1 = "";
  for ($i=1; $i <=$SESSION_VARS['nbr_sous_cpte']; $i++) {
    $JSform_1 .="if(document.ADForm.solde.value==0 || document.ADForm.solde.value=='')\n";
    $JSform_1 .="\tdocument.ADForm.solde$i.disabled =true;\n";
    $JSform_1 .="else";
    $JSform_1 .="\tdocument.ADForm.solde$i.disabled =false;\n";
  }

  /* Gestion de la devise des sous-comptes */
  for ($i=1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
    $JSform_1 .="if(document.ADForm.devise.value!=''){\n";
    $JSform_1 .="\tdocument.ADForm.libeledevise$i.disabled =true;\n";
    $JSform_1 .="\tdocument.ADForm.libeledevise$i.value = document.ADForm.devise.value;}\n";
    $JSform_1 .="else";
    $JSform_1 .="\t{document.ADForm.libeledevise$i.disabled =false;\n";
    $JSform_1 .="\tdocument.ADForm.libeledevise$i.value ='[Aucun]';}\n";
  }

  // Javascrgipt
  $JScode_1="";

  // Fonction checkAndComput(num) : crée le numéro du compte à partir du compte centralisateur et l'extension
  $JScode_1 .= "\nfunction checkAndComput(num)\n";
  $JScode_1 .= "{\n";
  for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) { // Cherche la ligne concernée
    $JScode_1 .="if($i==num)\n";
    $JScode_1 .="\tif(document.ADForm.extension$i.value!='')\n{";
    $JScode_1 .="document.ADForm.num_cpte_comptable$i.value = document.ADForm.num_cpte_comptable.value +\".\"+ document.ADForm.extension$i.value ;\n";
    $JScode_1 .= "\t\tdocument.ADForm.numero_compte$i.value =document.ADForm.num_cpte_comptable.value +\".\"+ document.ADForm.extension$i.value ;\n}";
    $JScode_1 .="\telse\n{";
    $JScode_1 .="document.ADForm.num_cpte_comptable$i.value = '';\n";
    $JScode_1 .= "\t\tdocument.ADForm.numero_compte$i.value ='' ;\n}";
  }
  $JScode_1 .= "}\n";
  //Fin de la fonction checkAndComput(num)

  $myForm->addJS(JSP_FORM,"comput",$JScode_1);
  $myForm->addJS(JSP_FORM,"jsform",$JSform_1);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajouter", _("Ajouter une ligne"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ppc-6');
  $myForm->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, 'Ppc-3');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Ppc-1');
  $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick"=>"nbr_sous_cpte.value++;"));

  $myForm->addHTMLExtraCode("html" , $html);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ Ppc-4 : Ajout d'un compte principal */
else if ($global_nom_ecran == 'Ppc-4') {
  global $global_id_agence;
  $myForm = new HTML_GEN2(_("Ajout d'un nouveau compte"));

  $myForm->addTable("ad_cpt_comptable", OPER_EXCLUDE, array("cpte_princ_jou", "cpte_centralise", "etat_cpte", "date_ouvert", "solde"));
  $myForm->addField("extension", _("Extension du compte" ), TYPC_TXT);
  $myForm->setFieldProperties("num_cpte_comptable", FIELDP_IS_LABEL, true);
  $myForm->addHiddenType("numero_compte", $SESSION_VARS['numero_compte']);
  $myForm->setFieldProperties("classe_compta", FIELDP_HAS_CHOICE_AUCUN, false);
  $myForm->setFieldProperties("classe_compta", FIELDP_IS_REQUIRED, false);
  $myForm->setFieldProperties("libel_cpte_comptable", FIELDP_WIDTH, 40);
  $myForm->setOrder(NULL, array("classe_compta", "extension", "num_cpte_comptable", "libel_cpte_comptable", "compart_cpte", "sens_cpte"));

  // Filtre pour les comptes de provision
  $LISTCPTES = getComptesComptables();

  $myForm->setFieldProperties("cpte_provision", FIELDP_INCLUDE_CHOICES, $LISTCPTES);

  $myForm->setFieldProperties("extension", FIELDP_JS_EVENT, array("OnChange"=>"checkAndComput();"));
  $myForm->setFieldProperties("classe_compta", FIELDP_JS_EVENT, array("OnChange"=>"fillFields();"));

  $myForm->setFieldProperties("classe_compta", FIELDP_DEFAULT, $SESSION_VARS['classe_compta']);
  $myForm->setFieldProperties("extension", FIELDP_DEFAULT, $SESSION_VARS['extension']);
  $myForm->setFieldProperties("num_cpte_comptable", FIELDP_DEFAULT, $SESSION_VARS['numero_compte']);
  $myForm->setFieldProperties("libel_cpte_comptable", FIELDP_DEFAULT, $SESSION_VARS['libel_cpte_comptable']);
  $myForm->setFieldProperties("compart_cpte", FIELDP_DEFAULT, $SESSION_VARS['compart_cpte']);
  $myForm->setFieldProperties("sens_cpte", FIELDP_DEFAULT, $SESSION_VARS['sens_cpte']);
  $myForm->setFieldProperties("cpte_provision", FIELDP_DEFAULT, $SESSION_VARS['cpte_provision']);
  $myForm->setFieldProperties("devise", FIELDP_DEFAULT, $SESSION_VARS['devise']);

  $JS .= "\nfunction checkAndComput()\n";
  $JS .= "{\n";
  $JS .= "\tdocument.ADForm.num_cpte_comptable.value = document.ADForm.HTML_GEN_LSB_classe_compta.value +\".\"+ document.ADForm.extension.value;\n";
  $JS .= "\tdocument.ADForm.numero_compte.value = document.ADForm.HTML_GEN_LSB_classe_compta.value +\".\"+ document.ADForm.extension.value;\n";
  $JS .= "}\n";

  $JS .= "\nfunction fillFields()\n";
  $JS .= "{\n";
  $JS .= "\tdocument.ADForm.num_cpte_comptable.value = \"\";\n";
  $JS .= "\tdocument.ADForm.numero_compte.value = \"\";\n";
  $JS .= "\tdocument.ADForm.extension.value = \"\";\n";
  $JS .= "}\n";

  $myForm->addJS(JSP_FORM, "js", $JS);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ppc-5');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Ppc-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ Ppc-5 : Confirmation de l'ajout d'un compte principal */
else if ($global_nom_ecran == 'Ppc-5') {
  // Si la classe compta est null, la remplacer par 0 : HTML_GEN renvoie null pour la valeur 0
  if ($classe_compta == NULL) {
    $classe_compta = 0;
  }

  $SESSION_VARS['classe_compta'] = $classe_compta;
  $SESSION_VARS['extension'] = $extension;
  $SESSION_VARS['numero_compte'] = $numero_compte;
  $SESSION_VARS['libel_cpte_comptable'] = $libel_cpte_comptable;
  $SESSION_VARS['compart_cpte'] = $compart_cpte;
  $SESSION_VARS['sens_cpte'] = $sens_cpte;
  $SESSION_VARS['cpte_provision'] = $cpte_provision;
  $SESSION_VARS['devise'] = $devise;

  $myErr = ajoutCompteComptable($numero_compte, $libel_cpte_comptable, $sens_cpte, $classe_compta, $compart_cpte, $cpte_centralise, $solde, $devise, $cpte_provision);

  if ($myErr->errCode != NO_ERR) {
    if ($myErr->errCode == ERR_CPT_EXIST) {
      $html_err = new HTML_erreur(_("Echec de la création du compte.")." ");
      $html_err->setMessage(sprintf(_("Le compte %s existe déjà"),"<b>".$myErr->param."</b>"));
      $html_err->addButton("BUTTON_OK", 'Ppc-4');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else if ($myErr->errCode == ERR_CPT_RESULTAT_EXIST) {
      $html_err = new HTML_erreur(_("Echec de la création du compte.")." ");
      $html_err->setMessage(sprintf(_("Le compte %s a déjà été déclaré comme compte de résultat"),"<b>".$myErr->param."</b>"));
      $html_err->addButton("BUTTON_OK", 'Ppc-4');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else if ($myErr->errCode == ERR_CPT_ECRITURE_EXIST) {
      $html_err = new HTML_erreur(_("Echec de la création du compte. "));
      $html_err->setMessage(sprintf(_("Il existe des ecritures en attente de valdation sur le compte %s, il faudra les valider pour pouvoir créer un sous compte qui lui est associé"),"<b>".$myErr->param."</b>"));
      $html_err->addButton("BUTTON_OK", 'Ppc-4');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec lors de la création du compte. "));
      $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Ppc-4');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("Le compte a bien été ajouté"));
    $myMsg->addButton(BUTTON_OK, 'Gen-14');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
}
/*}}}*/

/*{{{ Ppc-6 : Confirmation de l'ajout d'un sous-compte */
else if ($global_nom_ecran == 'Ppc-6') { //  Confirmation de l'ajout d'un sous compte
  global $global_id_agence;

  $cpte_centralise = $SESSION_VARS['cpte_centralise'];
  $SESSION_VARS['nbr_sous_cpte'] = $nbr_sous_cpte;
  for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
    $SESSION_VARS['infos_sous_cptes']['extension'.$i] = $ {'extension'.$i};
    $SESSION_VARS['infos_sous_cptes']['libel_cpte_comptable'.$i] = $ {'libel_cpte_comptable'.$i};
    $SESSION_VARS['infos_sous_cptes']['compart_cpte'.$i] = $ {'compart_cpte'.$i};
    $SESSION_VARS['infos_sous_cptes']['sens_cpte'.$i] = $ {'sens_cpte'.$i};
    $SESSION_VARS['infos_sous_cptes']['libeldevise'.$i] = $ {'libeldevise'.$i};
    $SESSION_VARS['infos_sous_cptes']['numero_compte'.$i] = $ {'numero_compte'.$i};
    $SESSION_VARS['infos_sous_cptes']['solde'.$i] = $ {'solde'.$i};
    $SESSION_VARS['infos_sous_cptes']['cpte_provision'.$i] = $ {'cpte_provision'.$i};
  }

  // Récupération des infos venant du compte centralisateur
  $temp = array();
  $temp["num_cpte_comptable"] = $cpte_centralise;
  $infocptecentralise = getComptesComptables($temp);

  // Liste des sous-comptes
  
  $DATA = array();

  // Récupération des infos des lignes de saisie dont le numéro du compte est renseigné
  for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
    if (!empty($ {'numero_compte'.$i})) {
      // Vérifier que le libellé, le compartiment et le sens sont renseignés
      if (empty($ {'libel_cpte_comptable'.$i}) or $ {'compart_cpte'.$i}==0 or $ {'sens_cpte'.$i}==0) {
        $html_err = new HTML_erreur(_("Ajout sous-compte"));
        $html_err->setMessage(_("Echec : Il faut renseigner les libellés, les compartiments et les sens des comptes."));
        $html_err->addButton("BUTTON_OK", 'Ppc-3');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
      }

      // Si le compte centralisateur est de l'actif ou du passif alors le sous-compte doit être du même compartiment
      if ($infocptecentralise[$cpte_centralise]['compart_cpte']==1 or $infocptecentralise[$cpte_centralise]['compart_cpte']==2 ) {
        if ($ {'compart_cpte'.$i} != $infocptecentralise[$cpte_centralise]['compart_cpte']) {
          $html_err = new HTML_erreur(_("Ajout sous-compte"));
          $html_err->setMessage(_("Echec : le compartiment d'un sous-compte d'un compte centralisateur actif ou bien  passif doit être de l'actif ou du passif."));
          $html_err->addButton("BUTTON_OK", 'Ppc-3');
          $html_err->buildHTML();
          echo $html_err->HTML_code;
          exit();
        }
      }
      // Si le compte centralisateur est un produit ou une charge alors les sous-comptes doivent être du même compartiement
      if ($infocptecentralise[$cpte_centralise]['compart_cpte']==3 or $infocptecentralise[$cpte_centralise]['compart_cpte']==4 ) {
        if ($infocptecentralise[$cpte_centralise]['compart_cpte'] != $ {'compart_cpte'.$i}) {
          $html_err = new HTML_erreur(_("Ajout sous-compte"));
          $html_err->setMessage(_("Echec : les sous-comptes doivent être du même compartiment que le compte principal."));
          $html_err->addButton("BUTTON_OK", 'Ppc-3');
          $html_err->buildHTML();
          echo $html_err->HTML_code;
          exit();
        }
      }

      $DATA[$ {'numero_compte'.$i}]["num_cpte_comptable"] = $ {'numero_compte'.$i};
      $DATA[$ {'numero_compte'.$i}]["libel_cpte_comptable"] = $ {'libel_cpte_comptable'.$i};
      $DATA[${'numero_compte'.$i}]["cpte_provision"] = $ {'cpte_provision'.$i};
      //$DATA[${'numero_compte'.$i}]["sens_cpte"] = $infocptecentralise[$cpte_centralise]['sens_cpte'];
      $DATA[$ {'numero_compte'.$i}]["sens_cpte"] = $ {'sens_cpte'.$i};
      $DATA[$ {'numero_compte'.$i}]["classe_compta"] = $infocptecentralise[$cpte_centralise]['classe_compta'];
      //$DATA[${'numero_compte'.$i}]["compart_cpte"] = $infocptecentralise[$cpte_centralise]['compart_cpte'];
      $DATA[$ {'numero_compte'.$i}]["compart_cpte"] = $ {'compart_cpte'.$i};
      $DATA[$ {'numero_compte'.$i}]["cpte_centralise"] = $cpte_centralise;
      $DATA[$ {'numero_compte'.$i}]["solde"] = recupMontant($ {'solde'.$i});

      if ($infocptecentralise[$cpte_centralise]["devise"] == "") {
        if ($ {'libeledevise'.$i} != "[Aucun]") {
          $DATA[$ {'numero_compte'.$i}]["devise"] = $ {'libeledevise'.$i};
        }
      } else {
        $DATA[$ {'numero_compte'.$i}]["devise"] = $infocptecentralise[$cpte_centralise]["devise"];
      }
    }
  }

  // Vérifier qu'il y'a au moins un sous-compte
  if (count($DATA) == 0) {
    $html_err = new HTML_erreur(_("Ajout sous-compte"));
    $html_err->setMessage(_("Echec : aucun sous-compte n'a été saisi."));
    $html_err->addButton("BUTTON_OK", 'Ppc-3');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  // Ajout des sous-comptes dans la base de données
  $myErr = ajoutSousCompteComptable($cpte_centralise, $DATA, $SESSION_VARS['solde_reparti']);

  if ($myErr->errCode != NO_ERR) {
    if ($myErr->errCode == ERR_CPT_EXIST) {
      $html_err = new HTML_erreur(_("Echec de la création de sous-compte. "));
      $html_err->setMessage(sprintf(_("Le compte %s existe déjà"),"<b>".$myErr->param."</b>"));
      $html_err->addButton("BUTTON_OK", 'Ppc-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else  if ($myErr->errCode == ERR_SOLDE_MAL_REPARTI) {
      $html_err = new HTML_erreur(_("Echec de la création du sous-compte."));
      $html_err->setMessage(_("Le solde du compte principal n'est pas bien réparti entre les sous-comptes"));
      $html_err->addButton("BUTTON_OK", 'Ppc-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else if ($myErr->errCode == ERR_CPT_RESULTAT_EXIST) {
      $html_err = new HTML_erreur(_("Echec de la création du compte. "));
      $html_err->setMessage(sprintf(_("Le compte %s a déjà été déclaré comme compte de résultat")),"<b>".$myErr->param."</b>");
      $html_err->addButton("BUTTON_OK", 'Ppc-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else if ($myErr->errCode == ERR_CPT_ECRITURE_EXIST) {
      $html_err = new HTML_erreur(_("Echec de la création du compte.")." ");
      $html_err->setMessage(sprintf(_("IL existe des ecritures en attente de valdation sur le  compte %s, il faudra les valider pour pouvoir créer un sous compte qui lui est associé")),"<b>".$myErr->param."</b>");
      $html_err->addButton("BUTTON_OK", 'Ppc-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec de la création du compte. "));
      $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Ppc-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $message =" "._("Le ou les sous-comptes ont été bien ajoutés.")."<br/> " ;
    $type_oper = getOperationsCompte($cpte_centralise);
    if ( $type_oper != NULL) {
      $message .= "<FONT color = red>".sprintf(_("Veuillez revoir le paramétrage, le compte %s est défini dans les opérations suivantes"),$cpte_centralise)."  :<br/><b>";
      foreach ($type_oper as $key => $value) {
        $message .= $value['type_operation']." ".$value['libel_ope'] ."<br>";
        $message .="<FONT>"  ;
      }
    }
    $myMsg->setMessage($message);
    $myMsg->addButton(BUTTON_OK, 'Ppc-2');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
}
/*}}}*/

/*{{{ Ppc-7 : Modification d'un compte */
else if ($global_nom_ecran == 'Ppc-7') {
  global $global_id_agence;
  $myForm = new HTML_GEN2(_("Modification d'un compte"));

  // Champs à exclure
  $exclude = array("cpte_princ_jou");


  $myForm->addTable("ad_cpt_comptable", OPER_EXCLUDE, $exclude);

  $def = new FILL_HTML_GEN2();
  $def->addFillClause("num", "ad_cpt_comptable");
  $def->addCondition("num", "num_cpte_comptable", $num_cpte_comptable);

  $def->addManyFillFields("num", OPER_EXCLUDE, $exclude);
  $def->fill($myForm);
  
  $myForm->setFieldProperties("num_cpte_comptable", FIELDP_IS_LABEL, true);
  //Donner la possibilité de modifier le sens et compartiment des comptes dont le solde est nul
  $solde = getSoldeCpteComptable($num_cpte_comptable);
  if($solde == 0){
  	$myForm->setFieldProperties("compart_cpte", FIELDP_IS_LABEL, false);
  	$myForm->setFieldProperties("sens_cpte", FIELDP_IS_LABEL, false);
  } else {
  	$myForm->setFieldProperties("compart_cpte", FIELDP_IS_LABEL, true);
  	$myForm->setFieldProperties("sens_cpte", FIELDP_IS_LABEL, true);
  }
  $myForm->setFieldProperties("classe_compta", FIELDP_IS_LABEL, true);
 /* if (getMouvementsComptables(array('compte' => $num_cpte_comptable), 1) != NULL) {
    $myForm->setFieldProperties("sens_cpte", FIELDP_IS_LABEL, true);
  }*/
  $myForm->setFieldProperties("date_ouvert", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("etat_cpte", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("cpte_centralise", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);

  if (!canModifyDevise($num_cpte_comptable)) {
    $myForm->setFieldProperties("devise", FIELDP_IS_LABEL, true);
  }

  $myForm->setOrder(NULL, array("num_cpte_comptable", "libel_cpte_comptable","cpte_centralise", "compart_cpte","classe_compta","sens_cpte", "etat_cpte","date_ouvert","solde","is_hors_bilan"));

  $LISTCPTES = getComptesComptables();

  if (!in_array("cpte_provision", $exclude)) {
    $includeprov = array(); // Recherche de tous les comptes candidats à etre comptes de provision, cçd tous les comptes principaux
    foreach ($LISTCPTES as $num_cpte => $CPT) {
     array_push($includeprov, $num_cpte);
    }
    $myForm->setFieldProperties("cpte_provision", FIELDP_INCLUDE_CHOICES, $includeprov);
  }
  // Modification de la devise
  if  ( !canModifyDevise($num_cpte_comptable) || (canModifyDevise($num_cpte_comptable) && ($LISTCPTES[$num_cpte_comptable]["devise"] != NULL)) ) {
    $prochainEcran = "Ppc-9";
  } else {
    $prochainEcran = "Ppc-8";
  }

  $SESSION_VARS["prochainEcran"] = $prochainEcran;

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "supprimer", _("Supprimer Compte"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN,  $prochainEcran);
  $myForm->setFormButtonProperties("supprimer", BUTP_PROCHAIN_ECRAN, 'Ppc-10');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Ppc-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

  // Enregistrement du numéro de compte
  $SESSION_VARS["num"] = $num_cpte_comptable;
}
/*}}}*/

/*{{{ Ppc-8 : Modification de la devise */
else if ($global_nom_ecran == 'Ppc-8') {
  global $global_id_agence;

  $num_cpte=$SESSION_VARS["num"];
  $field["libel_cpte_comptable"] = $libel_cpte_comptable;
  $field["compart_cpte"] = $compart_cpte;
  $field["cpte_provision"] = $cpte_provision;
  if($HTML_GEN_BOL_is_hors_bilan == true){
        $field["is_hors_bilan"] = 't';
  }else{
       $field["is_hors_bilan"] = 'f';
  }
  if ($sens_cpte != NULL && getMouvementsComptables(array('compte' => $num_cpte), 1) == NULL) {
    $field["sens_cpte"] = $sens_cpte;
  }
  $tmp["num_cpte_comptable"]= $num_cpte;
  $value= getComptesComptables($tmp);

  // Modification de la devise
  if ((canModifyDevise($num_cpte)) && ($value[$num_cpte]["devise"] == NULL)) {
    if ($devise == "0") {
      $message=_("Vous n'avez pas introduit de devise<br> Voulez-vous continuer ?");
    } else {
      $field["devise"] = $devise;
      $message = _("Si vous modifiez la devise vous ne pourrez plus la modifier une seconde fois.")."<br/>
                 ".sprintf(_("Voulez-vous changer la devise en : %s ?"),$devise);
    }
  }

  $SESSION_VARS["field"] = $field;
  $myMsg = new HTML_message(_("Confirmation"));
  $myMsg->setMessage($message);
  $myMsg->addButton("BUTTON_OUI", 'Ppc-9');
  $myMsg->addButton("BUTTON_NON", 'Ppc-1');
  $myMsg->buildHTML();
  echo $myMsg->HTML_code;
}
/*}}}*/

/*{{{ Ppc-9 : Confirmation de la modification d'un compte */
else if ($global_nom_ecran == 'Ppc-9') {
  if ($SESSION_VARS["prochainEcran"] == "Ppc-9") { // On vient de Ppc-7
    $field["libel_cpte_comptable"] = $libel_cpte_comptable;
    $field["cpte_provision"] = $cpte_provision;
    $field["compart_cpte"] = $compart_cpte;
    if($HTML_GEN_BOL_is_hors_bilan == true){
        $field["is_hors_bilan"] = 't';
    }else{
       $field["is_hors_bilan"] = 'f';
    }
    if ($sens_cpte != NULL && getMouvementsComptables(array('compte' => $num_cpte), 1) == NULL) {
      $field["sens_cpte"] = $sens_cpte;
    }
  } else {
    $field = $SESSION_VARS["field"];
  }

  if ($field["cpte_provision"] == 0) { // si aucun compte de provision sélectionné
    $field["cpte_provision"] = NULL;
  }
  debug($field,"champs");
  foreach($field as $key=>$value){
  	if($value != NULL){
  		$fields[$key] = $value;
  	}
  }
  debug($fields,"champs aprés select");
  $myErr = updateCompteComptable($SESSION_VARS["num"],$fields);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec lors de la modification du compte.")." ");
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Ppc-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("Le compte a bien été modifié"));
    $myMsg->addButton(BUTTON_OK, 'Ppc-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
}
/*}}}*/
/*{{{ Ppc-10 : Suppression de compte */
else if ($global_nom_ecran == 'Ppc-10') {
  global $global_id_agence;


  $myForm = new HTML_GEN2(_("Choix du compte à supprimer"));
  $value= getComptesComptables();
  $myForm->addField("num_cpte_comptable",_("Compte à supprimer")." ", TYPC_LSB);
  $myForm->setFieldProperties("num_cpte_comptable", FIELDP_IS_REQUIRED, true);
  foreach ($value as $num_cpte => $CPT) {
      $myForm->setFieldProperties("num_cpte_comptable", FIELDP_ADD_CHOICES, array($num_cpte => $num_cpte." ".$CPT['libel_cpte_comptable']));
  }
  $myForm->addFormButton(1, 1, "supp", _("Supprimer le compte"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("supp", BUTP_PROCHAIN_ECRAN, 'Ppc-11');
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ppc-1');
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

}
/*}}}*/
/*{{{ Ppc-11 :transfert solde du compte  à supprimer*/
else if ($global_nom_ecran == 'Ppc-11') {

  global $adsys, $global_id_agence;

  $myForm = new HTML_GEN2(_("transfert solde du compte  à supprimer"));

  if ($nbr_cpte_transfert == NULL) {
    if ($SESSION_VARS['nbr_cpte_transfert'] == NULL) {
      $SESSION_VARS['nbr_cpte_transfert'] = 4;
    }
  } else {
    $SESSION_VARS['nbr_cpte_transfert'] = $nbr_cpte_transfert;
  }

  $myForm->addHiddenType("nbr_cpte_transfert", $SESSION_VARS['nbr_cpte_transfert']);

  if ($num_cpte_comptable != NULL) {
    $SESSION_VARS['cpte_supprimer'] = $num_cpte_comptable;
  } else
    $num_cpte_comptable = $SESSION_VARS["cpte_supprimer"];


  // numéro compte à supprimer
  $param["num_cpte_comptable"] = $num_cpte_comptable;
  $infos_cpte_supprimer = getComptesComptables($param);

  $include = array("num_cpte_comptable","libel_cpte_comptable","compart_cpte","sens_cpte");
  $myForm->addTable("ad_cpt_comptable", OPER_INCLUDE, $include);

  $def = new FILL_HTML_GEN2();
  $def->addFillClause("num", "ad_cpt_comptable");
  $def->addCondition("num", "id_ag", $global_id_agence);
  $def->addCondition("num", "num_cpte_comptable", $num_cpte_comptable);

  $def->addManyFillFields("num", OPER_INCLUDE, $include);
  $def->fill($myForm);

  $myForm->setFieldProperties("num_cpte_comptable", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("libel_cpte_comptable", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("compart_cpte", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("sens_cpte", FIELDP_IS_LABEL, true);

  // solde compte à supprimer
  if ($infos_cpte_supprimer[$num_cpte_comptable]["compart_cpte"]==3 or $infos_cpte_supprimer[$num_cpte_comptable]["compart_cpte"]==4)
    $SESSION_VARS['solde_transfere'] = calculeSoldeCpteGestion($num_cpte_comptable);
  else
    $SESSION_VARS['solde_transfere'] = $infos_cpte_supprimer[$num_cpte_comptable]["solde"];


  $myForm->addField("solde",_("Solde compte" ),TYPC_TXT);
  $myForm->setFieldProperties("solde",FIELDP_IS_LABEL ,true);
  $myForm->setFieldProperties("solde",FIELDP_DEFAULT ,afficheMontant( abs($SESSION_VARS['solde_transfere'])));

  // devise du compte à supprimer
  $myForm->addField("devise",_("Devise du compte" ),TYPC_TXT);
  $myForm->setFieldProperties("devise",FIELDP_IS_LABEL ,true);
  $myForm->setFieldProperties("devise",FIELDP_DEFAULT , $infos_cpte_supprimer[$num_cpte_comptable]["devise"]);

  $myForm->addHTMLExtraCode("br1", "<br>");

  // Tableau de saisie des comptes à transferer les soldes
  $myTable =& $myForm->addHTMLTable("comptes_transfere", 3, TABLE_STYLE_ALTERN);

  // En-tête du tableau
  $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Comptes comptables"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Solde"), 1, 1));

  //recuperation de tous les comptes comptables
  $tab_comptes=getComptesComptables();
  for ($i = 1; $i <= $SESSION_VARS['nbr_cpte_transfert']; $i++) {
    // Numéro de  la ligne
    $myTable->add_cell(new TABLE_cell($i, 1, 1));

    // comptes comptable
    $num_compte_lsb = "<SELECT NAME=\"num_compte$i\">";
    $num_compte_lsb .= "<option value=\"0\">["._("Aucun")."]</option>";
    foreach($tab_comptes as $key=>$value) {

      $num_compte_lsb .= "<option value=$key>".$key." ".$value['libel_cpte_comptable']."</option>";
    }
    $num_compte_lsb .= "</SELECT>\n";
    $myTable->add_cell(new TABLE_cell($num_compte_lsb, 1, 1));


    // Solde du compte comptable
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "solde$i", $SESSION_VARS['infos_cptes_transfert']['solde'.$i], "value = formateMontant(value)"));
  }

  /* Gestion des soldes des sous-comptes */
  $JSform_1 = "";
  for ($i=1; $i <=$SESSION_VARS['nbr_cpte_transfert']; $i++) {
    $JSform_1 .="if(document.ADForm.solde.value==0 || document.ADForm.solde.value=='')\n";
    $JSform_1 .="\tdocument.ADForm.solde$i.disabled =true;\n";
    $JSform_1 .="else";
    $JSform_1 .="\tdocument.ADForm.solde$i.disabled =false;\n";
  }


  // Javascrgipt
  $JScode_1="";



  $myForm->addJS(JSP_FORM,"comput",$JScode_1);
  $myForm->addJS(JSP_FORM,"jsform",$JSform_1);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajouter", _("Ajouter une ligne"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ppc-12');
  $myForm->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, 'Ppc-11');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Ppc-10');
  $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick"=>"nbr_cpte_transfert.value++;"));

  $myForm->addHTMLExtraCode("html" , $html);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/
/*{{{ Ppc-12 :confirmation de la suppréssion du compte*/
else if ($global_nom_ecran == 'Ppc-12') {

	$is_cpt_supprimer=false;
  unset($SESSION_VARS['infos_cptes_transfert']);
  //recuperation des données
  for ($i = 1; $i <= $SESSION_VARS['nbr_cpte_transfert']; $i++) {
  	if ($ {'num_compte'.$i} != NULL && $ {'solde'.$i}!=NULL) {
  		$SESSION_VARS['infos_cptes_transfert'][$ {'num_compte'.$i}]['num_compte'] = $ {'num_compte'.$i};
      $SESSION_VARS['infos_cptes_transfert'][$ {'num_compte'.$i}]['solde'] = recupMontant($ {'solde'.$i});
  	}
  }

	$nbreClotureparam=getNbreCloturesPeriodiques();
	$nbreCloture=$nbreClotureparam->param[0];

	
	/*
	if (! isCentralisateur ( $SESSION_VARS ["cpte_supprimer"] )) {
		
		// verifier s'il ya pas d'ecriture dans la derniere cloture periodique ou dernier exercice
		
		// !isEcritureDerniereCloturePeriodique($SESSION_VARS["cpte_supprimer"])
		
		if (($nbreCloture > 0 && ! isEcritureDerniereCloturePeriodique ( $SESSION_VARS ["cpte_supprimer"] ))) {
			
			$is_cpt_supprimer = true;
		} else {
			$myMsg = new HTML_erreur ( _ ( "Confirmation" ) );
			$myMsg->setMessage ( sprintf ( _ ( "Le compte %s ne peut pas être supprimé car il y'a des écritures comptables de l'exercice encours déjà passées" ), $SESSION_VARS ["cpte_supprimer"] ) . " " );
			$myMsg->addButton ( BUTTON_OK, 'Ppc-1' );
			$myMsg->buildHTML ();
			echo $myMsg->HTML_code;
			exit ();
		}
		
	} 
	*/
	
	//verifie si solde is null
	if (!isCentralisateur ( $SESSION_VARS ["cpte_supprimer"] )) {
		$soldenullcheck = checksoldeNull( $SESSION_VARS ["cpte_supprimer"] ); //needs to be true for suppression
	
		$Checkoperations = getOperationsCompte($SESSION_VARS ["cpte_supprimer"]);// needs to return null pour suppression
		
        $CheckMovemntEcriture = checkMouvementEcritures ( $SESSION_VARS ["cpte_supprimer"] ); // needs to be false pour suppression 

		if (($soldenullcheck == true && $Checkoperations == NULL && $CheckMovemntEcriture == false)) {
				
			$is_cpt_supprimer = true;// compte peut être supprimé
		} else {
			$myMsg = new HTML_erreur ( _ ( "Confirmation" ) );
			if ($soldenullcheck != true ){
			$myMsg->setMessage ( sprintf ( _ ( "Le compte %s ne peut pas être supprimé car le solde n'est pas égal à zéro." ), $SESSION_VARS ["cpte_supprimer"] ) . " " );
			
			}
			if ($Checkoperations != NULL){
				$myMsg->setMessage ( sprintf ( _ ( "Le compte %s ne peut pas être supprimé car il est lié à des operations." ), $SESSION_VARS ["cpte_supprimer"] ) . " " );
					
			}
			if ($CheckMovemntEcriture != false){
				$myMsg->setMessage ( sprintf ( _ ( "Le compte %s ne peut pas être supprimé car des écritures comptables ont été passé sur ce compte." ), $SESSION_VARS ["cpte_supprimer"] ) . " " );
					
			}
			
			$myMsg->addButton ( BUTTON_OK, 'Ppc-1' );
			$myMsg->buildHTML ();
			echo $myMsg->HTML_code;
			exit ();
		}

	}else { // pour les comptes centralisateur verifier s'il n'a pas de sous compte'
		if (getNbreSousComptesComptables ( $SESSION_VARS ["cpte_supprimer"], true ) > 0) {
			$myMsg = new HTML_erreur ( _ ( "Confirmation" ) );
			$myMsg->setMessage ( sprintf ( _ ( "Le compte %s ne peut pas être supprimé car c'est un compte centralisateur, supprimez d'abord tous ces sous comptes" ), $SESSION_VARS ["cpte_supprimer"] ) . " " );
			$myMsg->addButton ( BUTTON_OK, 'Ppc-10' );
			$myMsg->buildHTML ();
			echo $myMsg->HTML_code;
			exit ();
		} else {
			$is_cpt_supprimer = true; // compte peut être supprimé
		}
	}
	
	if ($is_cpt_supprimer) {
		
		$myErr = supprimerCompte ( $SESSION_VARS ["cpte_supprimer"], $SESSION_VARS ['infos_cptes_transfert'], $SESSION_VARS ['solde_transfere'] );
		if ($myErr->errCode != NO_ERR) {
			$myMsg = new HTML_erreur ( _ ( "Confirmation" ) );
			$myMsg->setMessage ( _ ( "Erreur" ) . " : " . $error [$myErr->errCode] . " " . $myErr->param );
			$myMsg->addButton ( BUTTON_OK, 'Ppc-10' );
			$myMsg->buildHTML ( $myErr->param );
			echo $myMsg->HTML_code;
		} else {
			$myMsg = new HTML_message ( _ ( "Confirmation" ) );
			$myMsg->setMessage ( sprintf ( _ ( "Le compte %s a bien été supprimé" ), $SESSION_VARS ["cpte_supprimer"] ) );
			$myMsg->addButton ( BUTTON_OK, 'Ppc-10' );
			$myMsg->buildHTML ();
			echo $myMsg->HTML_code;
		}
	}


}
else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"

?>