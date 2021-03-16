<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [430] Rapports comptabilité
 * Ces fonctions appellent les écrans suivants :
 * - Tra-1 : Sélection du rapport à imprimer
 * - Tra-2 : BAL - Personnalisation balance comptable
 * - Tra-3 : BAL - Impression balance comptable
 * - Tra-6 : JOU - Personnalisation du journal comptable
 * - Tra-7 : JOU - Impression journal comptable
 * - Tra-8 : EXJ - Personnalisation journal comptable
 * - Tra-9 : EXJ - Exportation journal comptable
 * - Tra-10 : GLI - Personnalisation du Grand Livre Comptable
 * - Tra-11 : GLI - Impression Grand Livre Comptable
 * - Tra-12 : RES - Personnalisation du compte de résultat
 * - Tra-13 : RES - Impression du compte de résultat
 * - Tra-14 : BIL - Personnalisation du bilan
 * - Tra-15 : BIL - Impression du bilan
 * - Tra-16 : SIT - Situation intermédiaire MN/ME
 * - Tra-17 : EXB - Personnalisation exportation bilan
 * - Tra-18 : EXB - Exportation bilan
 * - Tra-19 : EXL - Personnalisation exportation balance comptable
 * - Tra-20 : EXL - Exportation balance comptable
 * - Tra-21 : JAN - Personnalisation journal des annulations
 * - Tra-22 : JAN - Impression journal des annulations
 * @package Rapports
 * @since 04/06/03
 **/

require_once 'modules/compta/xml_compta.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'modules/compta/csv_compta.php';
require_once "modules/rapports/xml_epargne.php";
require_once "modules/rapports/xml_credits.php";
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/csv.php';
require_once 'lib/misc/excel.php';

global $global_multidevise, $global_niveau_max;

/*{{{ Tra-1 : Sélection du rapport à imprimer */
if ($global_nom_ecran == "Tra-1") {
  //Calcul du niveau maximum des comptes comptables
  $list_agence = getAllIdNomAgence();
  if ($global_niveau_max == "" || $global_niveau_max == NULL) {
    $global_niveau_max = getNiveauMaxComptesComptables($list_agence);
  }
  // Recherche de tous les rapports à afficher
  foreach ($adsys["adsys_rapport"] as $key => $name) {
    if (substr($key, 0, 3) == 'CPT'){
    	if(!($key=='CPT-IST' || $key=='CPT-BSH' || $key=='CPT-LIR' || $key=='CPT-GOP') ){
    		$rapports[$key] = _($name);
    	}
    }

  }

  $MyPage = new HTML_GEN2(_("Sélection type rapport comptabilité"));
  $MyPage->addField("type", _("Type de rapport compta"), TYPC_LSB);
  $MyPage->setFieldProperties("type", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("type", FIELDP_ADD_CHOICES, $rapports);

  //Boutons
  $MyPage->addFormButton(1, 1, "valider", _("Sélectionner"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Gen-13");
  $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $prochEc = array (
               "BAL" => 2,
               "JOU" => 6,
               "EXJ" => 8,
               "GLI" => 10,
               "RES" => 12,
               "BIL" => 14,
               "SIT" => 16,
               "EXB" => 17,
               "EXL" => 19,
               "JAN" => 21,
               "IMP" => 32,
               "IAP" => 35,
               "IAR" => 41
             );

  //JS pour bouton
  foreach ($prochEc as $code => $ecran)
  $js .= "if (document.ADForm.HTML_GEN_LSB_type.value == 'CPT-$code')
         assign('Tra-$ecran');";
  $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Tra-2 : BAL - Personnalisation de la balance comptable */
else
  if ($global_nom_ecran == 'Tra-2') {
    global $adsys;

    $myForm = new HTML_GEN2(_("Personnalisation de la balance comptable"));

    if (isSiege()) {
    	//Remettre $global_id_agence à l'identifiant de l'agence courante
	    resetGlobalIdAgence();
	    //Agence- Tri par agence
	    $list_agence = getAllIdNomAgence();
	    $list_agence['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
	    $list_agence['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
	    $list_agence['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");
      unset ($list_agence[$global_id_agence]);
      $myForm->addField("agence", _("Agence"), TYPC_LSB);
      $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $list_agence);
      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);
      $list_niveau = array ();
      for ($i = 2; $i <= $global_niveau_max; $i++) {
        $list_niveau[$i] = $i;
      }
      $myForm->addField("niveau", _("Niveau des comptes comptables"), TYPC_LSB);
      $myForm->setFieldProperties("niveau", FIELDP_ADD_CHOICES, $list_niveau);
      $myForm->setFieldProperties("niveau", FIELDP_HAS_CHOICE_TOUS, true);

    }

    // Modèles de balance
    $myForm->addField("modele", _("Type de balance"), TYPC_LSB);
    $choices = array (
                 1 => _("Balance journalière"),
                 2 => _("Balance mensuelle"),
                 3 => _("Balance personalisée")
               );
    $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

    if ($global_multidevise) {
      $myForm->addTable("ad_cpt_comptable", OPER_INCLUDE, array (
                          "devise"
                        ));
      $myForm->setFieldProperties("devise", FIELDP_LONG_NAME, "Devise");
      $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, false);
      $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_TOUS, true);
    }

    $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

    $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

    $myForm->addHTMLExtraCode("espace2", "<br/><r/>");

    $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
    $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

    $myForm->addHTMLExtraCode("espace3", "<br/><br/>");

    $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
    $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

    $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
    $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
    $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
    $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
    $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-3");

    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    // Doit être fait via JS car si o le fait via HTML_GEN, il va transformer la listbox en textbos
    $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
              document.ADForm.HTML_GEN_LSB_annee.disabled = true;
              document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
              document.ADForm.HTML_GEN_date_date_debut.disabled = true;
              document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
    $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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
    $myForm->addJS(JSP_FORM, "JSActivate", $JS);

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
    //$myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

    $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                  "onchange" => "activateFields();"
                                ));

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
/*}}}*/

/*{{{ Tra-3 : BAL - Impression de la balance */
  else
    if ($global_nom_ecran == 'Tra-3') {
      global $global_id_agence; // agence encours

      // récupération des aegnces à imprimer
      $agence = $_POST['agence']; // agences sélectionées
      $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

      if ($agence == -1) { // on imprime que pour le siège
        $liste_ag[$global_id_agence] = $global_id_agence;
        $agence= $global_id_agence;
        $titre1="( "._("SIEGE")." ) ";//titre pr differencié les rapports
      }
      elseif ($agence > 0){// on imprime pour une agnce
      	$liste_ag[$agence] = $agence;
      }
      else{// on imprime pour toutes les agences et le siège
      	$liste_ag = getAllIdNomAgence(); // récupération des id des agences y compris celui du siège
      	if($agence==-2){
      			$consolide=true;
      			$titre1=" ("._("consolidée").") ";
      	}elseif($agence==-3){
      		unset ($liste_ag[$global_id_agence]);
      		$titre1=" ("._("AGENCES").") ";
      	}
				$agence= $global_id_agence;
       }


      // modèle de balance
      if ($modele == 1) {
        $date_deb = $date_journaliere;
        $date_fin = $date_journaliere;
      } else
        if ($modele == 2) {
          $date_deb = date("d/m/Y", mktime(0, 0, 0, $mois, 1, $annee));
          $date_fin = date("d/m/Y", mktime(0, 0, 0, $mois +1, 0, $annee));
        } else
          if ($modele == 3) {
            $date_deb = $date_debut;
            $date_fin = $date_fin;
          }

      if (($global_multidevise) && ($devise == '0'))
        $devise = NULL; // on imprime pour toute les devises
      if ($niveau == '') {
        $niveau = $niveau_max;
      }
      $DATA = getBalanceComptable($date_deb, $date_fin, $devise, $liste_ag, $niveau,$consolide);

      // Ajout du titre
      if ($modele == 1)
        $titre = $date_journaliere;
      else
        if ($modele == 2)
          $titre = adb_gettext($adsys["adsys_mois"][$mois]) . " " . $annee;
        else
          if ($modele == 3)
            $titre = "Du $date_deb au $date_fin";
      $titre=$titre1.": ".$titre;
      $xml = xml_balance_comptable($DATA, $titre,$agence);

      //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
      $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'balance_comptable.xslt');

      //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
      echo get_show_pdf_html("Tra-1", $fichier_pdf);

    }
/*}}}*/

/*{{{ Tra-6 : JOU - Personnalisation du rapport Journal */
    else
      if (($global_nom_ecran == "Tra-6")) {
        $myForm = new HTML_GEN2(_("Personnalisation du rapport Journal"));
        if (isSiege()) {
          //Agence- Tri par agence
          resetGlobalIdAgence();
          $myForm->addField("agence", _("Agence"), TYPC_LSB);
          $myForm->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
          $liste_agences = getAllIdNomAgence();
          $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
          unset ($liste_agences[$global_id_agence]);
          $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
        }
        // Liste des journaux
        $jnl = getInfosJournal();
        $choices = array ();
        if (isset ($jnl)) {
          foreach ($jnl as $key => $value) {
            $trad_ope = new Trad($value["libel_jou"]);
            $choices[$key] = $trad_ope->traduction();
          }
        }
        // Ajout de l'entrée "Toutes les transactions" pour sortir toutes les écritures de la période sans distinction de journal
        $choices[-1] = _("Toutes les transactions");

        $myForm->addField("journal", _("Journal"), TYPC_LSB);
        $myForm->setFieldProperties("journal", FIELDP_ADD_CHOICES, $choices);
        $myForm->setFieldProperties("journal", FIELDP_HAS_CHOICE_AUCUN, true);
        $myForm->setFieldProperties("journal", FIELDP_IS_REQUIRED, true);

        // Modèles de Journal
        $myForm->addField("modele", _("Type de Journal"), TYPC_LSB);
        $choices = array (
                     1 => _("Journal quotidien"),
                     2 => _("Journal mensuel"),
                     3 => _("Journal personalisé")
                   );
        $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

        $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

        $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

        $myForm->addHTMLExtraCode("espace2", "<br/><br/>");

        $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
        $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

        $myForm->addHTMLExtraCode("espace3", "<br/><br/>");

        $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
        $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

        $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
        $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
        $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
        $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
        $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

        $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-7");

        $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

        // Désactivation des éléments
        $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
                  document.ADForm.HTML_GEN_LSB_annee.disabled = true;
                  document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
                  document.ADForm.HTML_GEN_date_date_debut.disabled = true;
                  document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
        $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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

        $myForm->addJS(JSP_FORM, "JSActivate", $JS);

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

        $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);
        $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                      "onchange" => "activateFields();"
                                    ));
        $myForm->buildHTML();
        echo $myForm->getHTML();

      }
/*}}}*/

/*{{{ Tra-7 : JOU - Impression du journal comptable */
      else
        if ($global_nom_ecran == 'Tra-7') {
        	if(isSiege()&&$agence==-1){
        		$agence=0;
        	}
          setGlobalIdAgence($agence); //on travaille avec l'agence sélectionnée'
          // Construction de la période selon le modèle choisi
          if ($modele == 1) { // Rappport journal quotidien
            $date_deb = $date_journaliere;
            $date_fin = $date_journaliere;
          } else
            if ($modele == 2) { // Rappport journal mensuel
              $date_deb = "01/$mois/$annee";
              $date_fin = date("d/m/Y", mktime(0,0,0,$mois+1,0,$annee));
            } else
              if ($modele == 3) { // Rappport journal personnalisé
                $date_deb = $date_debut;
                $date_fin = $date_fin;
              }

          // Récupération des infos du journal
          if ($journal > 0) {
            $jnl = getInfosJournal($journal);
            $libel = $jnl[$journal]["libel_jou"];
          } else
            if ($journal == -1)
              $libel = "Toutes les transactions";

          // Sélection des opérations comptables de la période
          $lignesJournal = getLignesJournalCpt($journal, $date_deb, $date_fin);

          if ($lignesJournal != NULL)
            $DATA["lignes_journal"] = $lignesJournal;
          else
            $DATA["lignes_journal"] = NULL;

          $xml = xml_journal_cpt($DATA, array (
                                   _("Journal").": " => $libel,
                                   _("du[[A partir de]]").": " => $date_deb,
                                   _("au[[Jusqu au]]").": " => $date_fin
                                 ));

          $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'journal_cpt.xslt');

          echo get_show_pdf_html("Tra-1", $fichier_pdf);

          ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);

        }
/*}}}*/

/*{{{ Tra-8 : EXJ - Exportation journal comptable */
        else
          if (($global_nom_ecran == "Tra-8")) {
            $myForm = new HTML_GEN2(_("Personnalisation du rapport"));
            //Agence- Tri par agence
            if (isSiege()) {
              resetGlobalIdAgence();
              $myForm->addField("agence", _("Agence"), TYPC_LSB);
              $myForm->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
              $liste_agences = getAllIdNomAgence();
              $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
              unset ($liste_agences[$global_id_agence]);
              $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
            }
            // Ajout d'un champ pour le numéro de compte
            // On pique dans ad_gui, je sais c'est pas très élégant, désolé ...
            $myForm->addTable("ad_gui", OPER_INCLUDE, array ("cpte_cpta_gui"));
            $myForm->setFieldProperties("cpte_cpta_gui", FIELDP_HAS_CHOICE_AUCUN, false);
            $myForm->setFieldProperties("cpte_cpta_gui", FIELDP_HAS_CHOICE_TOUS, true);
            $myForm->setFieldProperties("cpte_cpta_gui", FIELDP_IS_REQUIRED, false);
            $myForm->addHTMLExtraCode("espace1", "<br/>");

            // récupérations de toutes les opérations ADbanking
            $liste_operations = getOperations();
            $liste_operations = $liste_operations->param;;
            $array_op = array ();
            foreach ($liste_operations as $key => $OP){
                $trad_ope = new Trad($OP['libel_ope']);
            	$array_op[$OP["type_operation"]] = $trad_ope->traduction();
            }
            asort($array_op);

            $myForm->addField("type_operation", _("Type d'opération"), TYPC_LSB);
            $myForm->setFieldProperties("type_operation", FIELDP_ADD_CHOICES, $array_op);
            $myForm->setFieldProperties("type_operation", FIELDP_HAS_CHOICE_AUCUN, false);
            $myForm->setFieldProperties("type_operation", FIELDP_HAS_CHOICE_TOUS, true);
            $myForm->addHTMLExtraCode("espace2", "<br/>");

            $myForm->addTable("ad_cpt", OPER_INCLUDE, array ("devise"));
            $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_TOUS, true);
            $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, false);
            $myForm->setFieldProperties("devise", FIELDP_IS_REQUIRED, false);
            $myForm->setFieldProperties("devise", FIELDP_LONG_NAME, "Devise");

            $myForm->addField("date_deb", _("Date de début"), TYPC_DTE);
            $myForm->addField("date_fin", _("Date de fin"), TYPC_DTE);
            $myForm->setFieldProperties("date_deb", FIELDP_DEFAULT, date("01/01/Y"));
            $myForm->setFieldProperties("date_deb", FIELDP_IS_REQUIRED, true);
            $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));
            $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
            $myForm->addHTMLExtraCode("espace3", "<br/>");

            $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
            $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-9");
            $myForm->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
            $myForm->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Tra-9");
            $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
            $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
            $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
            $myForm->buildHTML();
            echo $myForm->getHTML();
          }
/*}}}*/

/*{{{ Tra-9 : EXJ - Génération du CSV du journal comptable */
          else
            if ($global_nom_ecran == 'Tra-9') {
              // TODO il faudrait fusionner Tra-9 avec Tra-7 et utiliser un fichier XSLT pour générer le CSV
              if(isSiege()&&$agence==-1){
	        $agence=0;
              }
              setGlobalIdAgence($agence); //on travaille avec l'agence sélectionnée'

              $CRIT = array ();
              $CRIT["date_deb"] = $date_deb;
              $CRIT["date_fin"] = $date_fin;
              $CRIT["compte"] = $cpte_cpta_gui;
              $CRIT["type_operation"] = $type_operation;
              $CRIT["devise"] = $devise;
              $DATA = getExtraitJournalComptable($CRIT);
              // Création, écriture et envoi du CSV
              $csv = csv_extrait_journal_comptable($CRIT, $DATA);
              $result = doWriteCSV($csv);
              if ($result->errCode == NO_ERR) {
                if (isset($excel) && $excel == 'Export EXCEL'){
                  echo getShowEXCELHTML("Tra-1", $result->param);
                }
                else{
                  echo getShowCSVHTML("Tra-1", $result->param);
                }
                ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);
              } else {
                $my_page = new HTML_erreur(_("Echec lors de l'export du journal comptable"));
                $my_page->setMessage(_("Erreur")." : " . $error[$result->errCode] . "<br />"._("Paramètre")." : " . $result->param);
                $my_page->addButton("BUTTON_OK", 'Gen-13');
                $my_page->show();
              }
            }
/*}}}*/

/*{{{ Tra-10 : GLI - Personnalisation du Grand Livre Comptable */
            else
              if ($global_nom_ecran == 'Tra-10') {
                $myForm = new HTML_GEN2(_("Personnalisation du Grand Livre Comptable"));
                //Agence- Tri par agence
                if (isSiege()) {
                  resetGlobalIdAgence();
                  $myForm->addField("agence", _("Agence"), TYPC_LSB);
                  $myForm->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
                  $liste_agences = getAllIdNomAgence();
                  $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
                  unset ($liste_agences[$global_id_agence]);
                  $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
                }
                // Recupere le numero des comptes comptables à afficher dans la liste box
                $cpte_comptable = getNomsComptesComptables(NULL);

                $myForm->addField("cpte_min", _("Du Compte"), TYPC_LSB);
                $myForm->setFieldProperties("cpte_min", FIELDP_ADD_CHOICES, $cpte_comptable);

                $myForm->addField("cpte_max", _("Au Compte"), TYPC_LSB);
                $myForm->setFieldProperties("cpte_max", FIELDP_ADD_CHOICES, $cpte_comptable);

                //Par période
                $myForm->addField("modele", _("Période"), TYPC_LSB);
                $choices = array (
                             1 => _("Grand livre journalier")."
                                   ", 2 => _("Grand livre mensuel"), 3 => _("Grand livre personalisé"));
                $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

                //Par période
                $myForm->addField("type_affichage", _("Type affichage"), TYPC_LSB);
                $choice_aff = array (
                             1 => _("Détaillé"
                                   ), 2 => _("Condensé"));
                $myForm->setFieldProperties("type_affichage", FIELDP_ADD_CHOICES, $choice_aff);
                
                //Filtre par journal
                // Construction de la liste des journaux existant
                $myForm->addTableRefField ( "journal", _ ( "Journal" ), "ad_journaux" );
                $myForm->setFieldProperties ( "journal", FIELDP_HAS_CHOICE_AUCUN, true );

                $myForm->addHTMLExtraCode("espace1", "<br/><br/>");
                

                $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

                $myForm->addHTMLExtraCode("espace2", "<br /><br />");

                $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
                $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

                $myForm->addHTMLExtraCode("espace3", "<br/><br/>");

                $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
                $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

                $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
                $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
                $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
                $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
                $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
                $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
                $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
                $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
                $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
                $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
                $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

                $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-11");

                $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
                $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                // Doit être fait via JS car si o le fait via HTML_GEN, il va transformer la listbox en textbos
                $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
                          document.ADForm.HTML_GEN_LSB_annee.disabled = true;
                          document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
                          document.ADForm.HTML_GEN_date_date_debut.disabled = true;
                          document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
                $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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
                $myForm->addJS(JSP_FORM, "JSActivate", $JS);

                // Code javascript pour la vérification des champs obligatoires
                $JSCheck = "\n\tif (document.ADForm.HTML_GEN_LSB_modele.value == 1)
                         {
                           if (document.ADForm.HTML_GEN_date_date_journaliere.value == '')
                         {
                           msg += '- "._("La date doit être renseignée")."\\n';
                           ADFormValid = false;
                         }
                           if (!isDate(document.ADForm.HTML_GEN_date_date_journaliere.value))
                         {
                           msg += '- "._("Le format du champs \" Date \" est incorrect")."\\n';
                           ADFormValid = false;
                         }
                         }
                           else if (document.ADForm.HTML_GEN_LSB_modele.value == 2)
                         {
                           if (document.ADForm.HTML_GEN_LSB_mois.value == '')
                         {
                           msg += '- "._("Le mois  doit être renseigné")."\\n';
                           ADFormValid = false;
                         }
                           if (document.ADForm.HTML_GEN_LSB_annee.value == '')
                         {
                           msg += '- "._("L\\'année  doit être renseignée")."\\n';
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
                           msg += '- "._("La date de fin de période  doit être renseignée")."\\n';
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
                $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                              "onchange" => "activateFields();"
                                            ));

                $myForm->buildHTML();
                echo $myForm->getHTML();
              }
/*}}}*/

/*{{{ Tra-11 : GLI - Impression du Grand Livre Comptable */
              else
                if ($global_nom_ecran == 'Tra-11') {
                 //on travaille avec l'agence sélectionnée'
 	               if(isSiege()){
 	                   if( $agence == -1 ) {
 	                      $agence = 0;
 	                   }
 	                   setGlobalIdAgence($agence);
                  }

                  if ($modele == 1) {
                    $date_deb = $date_journaliere;
                    $date_fin = $date_journaliere;
                  } else
                    if ($modele == 2) {
                      $date_deb = date("d/m/Y", mktime(0, 0, 0, $mois, 1, $annee));
                      $date_fin = date("d/m/Y", mktime(0, 0, 0, $mois +1, 0, $annee));
                    } else
                      if ($modele == 3) {
                        $date_deb = $date_debut;
                        $date_fin = $date_fin;
                      }

                  if ($cpte_min == 0)
                    $cpte_min = NULL;

                  if ($cpte_max == 0)
                    $cpte_max = NULL;
                  
                  //verification si un journal a été choisi
                  if (isset ($journal)){
                   //get info sur les journaux
                  $info_journal = getInfosjournal ( $journal );
                  $SESSION_VARS ['info'] =  $info_journal;        
                  }
                   
                  //to pass id_jou in  getGrandLivre ***/***********************************
                  $DATA = getGrandLivre($date_deb, $date_fin, $cpte_min, $cpte_max, $type_affichage, $journal );
                  if (!empty ($DATA)) {
                    // Ajout du titre
                    if ($modele == 1)
                      $titre = $date_journaliere;
                    else
                      if ($modele == 2)
                        $titre = adb_gettext($adsys["adsys_mois"][$mois]) . " " . $annee;
                      else
                        if ($modele == 3)
                          $titre = "Du $date_deb au $date_fin";

                    if ((isset ($cpte_min)) and (isset ($cpte_max)))
                      $titre .= "  ".sprintf(_("Du compte  %s au compte %s"),$cpte_min,$cpte_max)." ";
                    else
                      if (isset ($cpte_min))
                        $titre .= "  ".sprintf(_("des comptes supérieurs ou égaux à %s"),$cpte_min)."  ";
                      else
                        if (isset ($cpte_max))
                          $titre .= "  ".sprintf(_("des comptes inférieurs ou égaux à %s"),$cpte_max)."  ";
										if($type_affichage == 1)
											$condense = false;
											else
											$condense = true;
                   	 $xml = xml_grandlivre($DATA, $titre, $condense);

                    //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                     $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'grandlivre.xslt');

                    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                    echo get_show_pdf_html("Tra-1", $fichier_pdf);
                  } else {
                    $html_msg = new HTML_message(_("Impression grand livre"));
                    $html_msg->setMessage(_("Aucune donnée renvoyée"));
                    $html_msg->addButton("BUTTON_OK", "Tra-10");
                    $html_msg->buildHTML();
                    echo $html_msg->HTML_code;
                  }
                }
/*}}}*/

/*{{{ Tra-12 : RES - Personnalisation du compte de résultat */
                else
                  if ($global_nom_ecran == 'Tra-12') {
                    $myForm = new HTML_GEN2(_("Personnalisation du compte de résultat"));

                    if (isSiege()) {
                    	//Remettre $global_id_agence à l'identifiant de l'agence courante
	                    resetGlobalIdAgence();
	                    //Agence- Tri par agence
	                    $list_agence = getAllIdNomAgence();
	                    $list_agence['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
	                    $list_agence['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
	                    $list_agence['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");
	                    $list_niveau = array ();
	                    for ($i = 2; $i <= $global_niveau_max; $i++) {
	                      $list_niveau[$i] = $i;
	                    }
                      unset ($list_agence[$global_id_agence]);
                      $myForm->addField("agence", _("Agence"), TYPC_LSB);
                      $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $list_agence);
                      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
                      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);
                      $myForm->addField("niveau", _("Niveau des comptes comptables"), TYPC_LSB);
                      $myForm->setFieldProperties("niveau", FIELDP_ADD_CHOICES, $list_niveau);
                      $myForm->setFieldProperties("niveau", FIELDP_HAS_CHOICE_TOUS, true);
                    }

                    // Modèles de balance
                    $myForm->addField("modele", _("Type compte de résultat"), TYPC_LSB);
                    $choices = array (
                                 1 => _("Résultat journalier"
                                       ), 2 => _("Résultat mensuel"), 3 => _("Résultat personalisé"));
                    $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

                    $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

                    $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

                    $myForm->addHTMLExtraCode("espace2", "<br/><br/>");

                    $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
                    $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

                    $myForm->addHTMLExtraCode("espace3", "<br/><br/>");

                    $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
                    $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

                    $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
                    $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
                    $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
                    $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
                    $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

                    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-13");

                    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
                    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                    // Doit être fait via JS car si o le fait via HTML_GEN, il va transformer la listbox en textbos
                    $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
                              document.ADForm.HTML_GEN_LSB_annee.disabled = true;
                              document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
                              document.ADForm.HTML_GEN_date_date_debut.disabled = true;
                              document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
                    $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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
                    $myForm->addJS(JSP_FORM, "JSActivate", $JS);

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
                    $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                    $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                                  "onchange" => "activateFields();"
                                                ));

                    $myForm->buildHTML();
                    echo $myForm->getHTML();
                  }
/*}}}*/

/*{{{ Tra-13 : RES - Impresion du compte de résultat */
                  else
                    if ($global_nom_ecran == 'Tra-13') {

                      global $global_id_agence; // agence encours
                      global $global_monnaie;

                      // récupération des aegnces à imprimer
                      $agence = $_POST['agence']; // agences sélectionées
                      $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

                      if ($agence == -1) { // on imprime que pour le siège
                        $liste_ag[$global_id_agence] = $global_id_agence;
                        $agence=$global_id_agence;
                        $titre1=" "._("siège")." ";
                      }
                      elseif ($agence > 0) // on imprime pour une agnce
                      $liste_ag[$agence] = $agence;
                      else { // on imprime pour toutes les agences et le siège
                      	$liste_ag=getAllIdNomAgence();

                      	if($agence==-2){
                      		$consolide=true;
                      		$titre1=" "._("consolidé")." ";
                      	}elseif($agence==-3){
                      		unset ($liste_ag[$global_id_agence]);
                      		$titre1=" "._("des agences")." ";
                      	}
                      	$agence=$global_id_agence;
                      }
                      // modèle de balance
                      if ($modele == 1) {
                        $date_deb = $date_journaliere;
                        $date_fin = $date_journaliere;
                      } else
                        if ($modele == 2) {
                          $date_deb = date("d/m/Y", mktime(0, 0, 0, $mois, 1, $annee));
                          $date_fin = date("d/m/Y", mktime(0, 0, 0, $mois +1, 0, $annee));
                        } else
                          if ($modele == 3) {
                            /* Le résultat est lié à un exercice. Donc les dates de début et de fin doivent être sur un même exercie */
                            $date_deb = $date_debut;
                            $date_fin = $date_fin;
                          }
                      if ($niveau == '') {
                        $niveau = $niveau_max;
                      }
                      $erreur = getCompteDeResultat($date_deb, $date_fin, $liste_ag, $niveau,$consolide);

                      if ($erreur->errCode != NO_ERR) {
                        $html_err = new HTML_erreur(_("Compte de résultat"));
                        $html_err->setMessage(_("Echec : " . $erreur->param));
                        $html_err->addButton(BUTTON_OK, 'Tra-12');
                        $html_err->buildHTML();
                        echo $html_err->HTML_code;
                        exit ();
                      }

                      $DATA = $erreur->param;

                      // Ajout du titre
                      if ($modele == 1)
                        $titre = $date_journaliere;
                      else
                        if ($modele == 2)
                          $titre = adb_gettext($adsys["adsys_mois"][$mois]) . " " . $annee;
                        else
                          if ($modele == 3)
                            $titre = _("Du[[A partir de]]")." $date_deb "._("au[[Jusqu au]]")." $date_fin";
                      $titre= $titre1." ($global_monnaie): ".$titre;
                      $xml = xml_compte_de_resultat($DATA, $titre,$agence);

                      //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                      $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'compte_de_resultat.xslt');

                      //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                      echo get_show_pdf_html("Tra-1", $fichier_pdf);

                    }
/*}}}*/

/*{{{ Tra-14 : BIL - Personnalisation du Bilan */
                    else
                      if ($global_nom_ecran == 'Tra-14') {
                        $myForm = new HTML_GEN2(_("Saisie de la date"));
                        //Remettre $global_id_agence à l'identifiant de l'agence courante
                        resetGlobalIdAgence();
                        //Agence- Tri par agence
                        $list_agence = getAllIdNomAgence();



                        if (isSiege()) {
                        	$list_agence['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
                        	$list_agence['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
    						$list_agence['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");
                          $list_niveau = array ();
                          for ($i = 2; $i <= $global_niveau_max; $i++) {
                            $list_niveau[$i] = $i;
                          }
                          unset ($list_agence[$global_id_agence]);
                          $myForm->addField("agence", _("Agence"), TYPC_LSB);
                          $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $list_agence);
                          $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
                          $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);
                          $myForm->addField("niveau", _("Niveau des comptes comptables"), TYPC_LSB);
                          $myForm->setFieldProperties("niveau", FIELDP_ADD_CHOICES, $list_niveau);
                          $myForm->setFieldProperties("niveau", FIELDP_HAS_CHOICE_TOUS, true);

                        }

                        //  $myForm->addHTMLExtraCode("espace1", "<br /><br />");

                        $myForm->addField("date_bilan", _("Date"), TYPC_DTE);

                        $myForm->setFieldProperties("date_bilan", FIELDP_IS_REQUIRED, true);
                        $myForm->setFieldProperties("date_bilan", FIELDP_DEFAULT, date("d/m/Y"));
                        $myForm->addField("solde_non_null", _("Ne pas afficher les comptes de solde nul ?"), TYPC_BOL);
    										$myForm->setFieldProperties("solde_non_null", FIELDP_DEFAULT, FALSE);

                        $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-15");
                        $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);

                        $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                        $myForm->buildHTML();
                        echo $myForm->getHTML();
                      }
/*}}}*/

/*{{{ Tra-15 : BIL - Impression du bilan */
                      else
                        if ($global_nom_ecran == 'Tra-15') {
                          set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time

                          global $global_id_agence;
                          // récupération des agences à imprimer

                          $agence = $_POST['agence']; // agences sélectionées
                          $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

                          if ($agence == -1) { // on imprime que pour le siège
                            $liste_ag[$global_id_agence] = $global_id_agence;
                            $agence=$global_id_agence;
                            $titre1="("._("SIEGE").")";
                          }
                          elseif ($agence > 0){ // on imprime pour une agnce
                          $liste_ag[$agence] = $agence;
                          }
                          else { // on imprime pour toutes les agences et le siège
                      			$liste_ag=getAllIdNomAgence();

                      			if($agence==-2){
                      				$consolide='true';
                      				$titre1="("._("consolidé").")";
                      			}elseif($agence==-3){
                      				unset ($liste_ag[$global_id_agence]);
                      				 $titre1="("._("AGENCES").")";
                      			}
                      			$agence=$global_id_agence;
                          }

                          if ($niveau == '') {
                            $niveau = $niveau_max;
                          }

                          $DATA = getBilan($date_bilan, $liste_ag, $niveau,$consolide,$solde_non_null);

                          $titre = $date_bilan;
                          $titre=$titre1.": ".$titre;
                          $xml = xml_bilan($DATA, $titre,$agence);

                          //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                          $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'bilan.xslt');

                          //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                          echo get_show_pdf_html("Tra-1", $fichier_pdf);

                        }
/*}}}*/

/*{{{ Tra-16 : SIT - Situation intermédiaire MN/ME */
                        else
                          if ($global_nom_ecran == 'Tra-16') {
                            setGlobalIdAgence($agence); //on travaille avec l'agence sélectionnée'
                            $DATA = getSituationMNME();

                            $xml = xml_situation_intermediaire($DATA);

                            //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'simulation_intermediaire.xslt');

                            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                            echo get_show_pdf_html("Tra-1", $fichier_pdf);
                          }
/*}}}*/

/*{{{ Tra-17 : EXB - Personnalisation exportation Bilan */
                          else
                            if ($global_nom_ecran == 'Tra-17') {
                              $myForm = new HTML_GEN2(_("Saisie de la date"));
                              //Agence- Tri par agence
                              if (isSiege()) {
                              //  resetGlobalIdAgence();
                                $myForm->addField("agence", _("Agence"), TYPC_LSB);
                                $myForm->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
                                $liste_agences = getAllIdNomAgence();
                                $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
                                $liste_agences['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
    														$liste_agences['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");
                                unset ($liste_agences[$global_id_agence]);
                                $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
                              }
                              $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

                              $myForm->addField("date_bilan", _("Date"), TYPC_DTE);

                              $myForm->setFieldProperties("date_bilan", FIELDP_IS_REQUIRED, true);
                              $myForm->setFieldProperties("date_bilan", FIELDP_DEFAULT, date("d/m/Y"));

                              $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                              $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-18");
                              $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);

                              $myForm->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
                              $myForm->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Tra-18");
                              $myForm->setFormButtonProperties("excel", BUTP_CHECK_FORM, true);

                              $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
                              $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                              $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                              $myForm->buildHTML();
                              echo $myForm->getHTML();
                            }
/*}}}*/

/*{{{ Tra-18 : EXB - Exportation Bilan */
                            else
                              if ($global_nom_ecran == 'Tra-18') {
                              	$agence = $_POST['agence']; // agences sélectionées
                          			$liste_ag = array (); // préparation de la liste des agences à passer à la fonction

                          			if ($agence == -1) { // on imprime que pour le siège
                            			$liste_ag[$global_id_agence] = $global_id_agence;
                            			$agence= $global_id_agence;
                          			}
                          			elseif ($agence > 0) // on imprime pour une agnce
                          				$liste_ag[$agence] = $agence;
                          			else { // on imprime pour toutes les agences et le siège
                      						$liste_ag=getAllIdNomAgence();
                      						$agence= $global_id_agence;
                      						if($agence==-2){
                      							$consolide=true;
                      						}elseif($agence==-3){
                      							unset ($liste_ag[$global_id_agence]);
                      						}
                          			}
                                setGlobalIdAgence($agence); //on travaille avec l'agence sélectionnée'
                                $DATA = getBilan($date_bilan, $liste_ag, $niveau,$consolide);
                                $csv = csv_bilan($DATA, $date_bilan);
                                $result = doWriteCSV($csv);
                                if ($result->errCode == NO_ERR) {
                                  // Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                                  if (isset($excel) && $excel == 'Export EXCEL'){
                                    echo getShowEXCELHTML("Gen-14", $result->param);
                                  }
                                  else{
                                    echo getShowCSVHTML("Gen-14", $result->param);
                                  }
                                  ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);
                                }
                              }
/*}}}*/

/*{{{ Tra-19 : EXL - Personnalisation exportation balance comptable */
                              else
                                if ($global_nom_ecran == 'Tra-19') {
                                  $myForm = new HTML_GEN2(_("Personnalisation de la balance comptable"));
                                  //Agence- Tri par agence
                                  if (isSiege()) {

                                    resetGlobalIdAgence();
                                    $liste_agences = getAllIdNomAgence();
                                    $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
                                    $liste_agences['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
    								$liste_agences['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");
                                    unset ($liste_agences[$global_id_agence]);

															      $myForm->addField("agence", _("Agence"), TYPC_LSB);
															      $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES,$liste_agences);
															      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
															      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);

															      $list_niveau = array ();
															      for ($i = 2; $i <= $global_niveau_max; $i++) {
															        $list_niveau[$i] = $i;
															      }
															      $myForm->addField("niveau", _("Niveau des comptes comptables"), TYPC_LSB);
															      $myForm->setFieldProperties("niveau", FIELDP_ADD_CHOICES, $list_niveau);
															      $myForm->setFieldProperties("niveau", FIELDP_HAS_CHOICE_TOUS, true);
                                  }
                                  // Modèles de balance
                                  $myForm->addField("modele", _("Type de balance"), TYPC_LSB);
                                  $choices = array (
                                               1 => _("Balance journalière"),
                                               2 => _("Balance mensuelle"),
                                               3 => _("Balance personalisée")
                                             );
                                  $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

                                  if ($global_multidevise) {
                                    $myForm->addTable("ad_cpt_comptable", OPER_INCLUDE, array (
                                                        "devise"
                                                      ));
                                    $myForm->setFieldProperties("devise", FIELDP_LONG_NAME, "Devise");
                                    $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, false);
                                    $myForm->setFieldProperties("devise", FIELDP_HAS_CHOICE_TOUS, true);
                                  }

                                  $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

                                  $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

                                  $myForm->addHTMLExtraCode("espace2", "<br /><br />");

                                  $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
                                  $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

                                  $myForm->addHTMLExtraCode("espace3", "<br/><br/>");

                                  $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
                                  $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

                                  $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
                                  $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
                                  $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
                                  $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
                                  $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
                                  $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
                                  $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
                                  $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
                                  $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
                                  $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
                                  $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

                                  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                                  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-20");
                                  $myForm->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
                                  $myForm->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Tra-20");

                                  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
                                  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                                  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                                  // Doit être fait via JS car si o le fait via HTML_GEN, il va transformer la listbox en textbos
                                  $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
                                            document.ADForm.HTML_GEN_LSB_annee.disabled = true;
                                            document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
                                            document.ADForm.HTML_GEN_date_date_debut.disabled = true;
                                            document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
                                  $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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
                                  $myForm->addJS(JSP_FORM, "JSActivate", $JS);

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
                                  $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                                  $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                                                "onchange" => "activateFields();"
                                                              ));

                                  $myForm->buildHTML();
                                  echo $myForm->getHTML();
                                }
/*}}}*/

/*{{{ Tra-20 : EXL - Exportation balance comptable */
                                else
                                  if ($global_nom_ecran == 'Tra-20') {
                                     global $global_id_agence; // agence encours

																      // récupération des aegnces à imprimer
																      $agence = $_POST['agence']; // agences sélectionées
																      $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

																      if ($agence == -1) { // on imprime que pour le siège
																        $liste_ag[$global_id_agence] = $global_id_agence;
																      }
																      elseif ($agence > 0){// on imprime pour une agnce
																      	$liste_ag[$agence] = $agence;
																      }
																      else{// on imprime pour toutes les agences et le siège
																      	$liste_ag = getAllIdNomAgence(); // récupération des id des agences y compris celui du siège
																      }


	                                    if ($modele == 1) {
	                                      $date_deb = $date_journaliere;
	                                      $date_fin = $date_journaliere;
	                                    } else
	                                      if ($modele == 2) {
	                                        $date_deb = date("d/m/Y", mktime(0, 0, 0, $mois, 1, $annee));
	                                        $date_fin = date("d/m/Y", mktime(0, 0, 0, $mois +1, 0, $annee));
	                                      } else
	                                        if ($modele == 3) {
	                                          $date_deb = $date_debut;
	                                          $date_fin = $date_fin;
	                                        }

	                                    if (($global_multidevise) && ($devise != '0'))
	                                      $DATA = getBalanceComptable($date_deb, $date_fin, $devise,$liste_ag,$niveau,$consolide);
	                                    else
	                                      $DATA = getBalanceComptable($date_deb, $date_fin,null,$liste_ag,$niveau,$consolide);

	                                    // Ajout du titre
	                                    if ($modele == 1)
	                                      $titre = $date_journaliere;
	                                    else
	                                      if ($modele == 2)
	                                        $titre = adb_gettext($adsys["adsys_mois"][$mois]) . " " . $annee;
	                                      else
	                                        if ($modele == 3)
	                                          $titre = "Du $date_deb au $date_fin";

	                                    $csv = csv_balance($DATA, $titre);
	                                    $result = doWriteCSV($csv);
	                                    if ($result->errCode == NO_ERR) {
	                                      // Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                                        if (isset($excel) && $excel == 'Export EXCEL'){
                                          echo getShowEXCELHTML("Tra-1", $result->param);
                                        }
                                        else{
	                                        echo getShowCSVHTML("Tra-1", $result->param);
                                        }
	                                      ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);
	                                    }
                                  }
/*}}}*/

/*{{{ Tra-21 : JAN - Personnalisation du rapport Journal des annulations */
                                  else
                                    if (($global_nom_ecran == "Tra-21")) {
                                      $myForm = new HTML_GEN2(_("Personnalisation du journal des annulations"));
                                      //Agence- Tri par agence
                                      if (isSiege()) {
                                        resetGlobalIdAgence();
                                        $myForm->addField("agence", _("De l'agence"), TYPC_LSB);
                                        $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
      																	$myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);
                                        $liste_agences = getAllIdNomAgence();
                                        $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
                                        unset ($liste_agences[$global_id_agence]);
                                        $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
                                        unset ($liste_agences[-1]);
                                        $myForm->addField("agence1", _("Et l'agence"), TYPC_LSB);
                                        $myForm->setFieldProperties("agence1", FIELDP_ADD_CHOICES, $liste_agences);
                                        $myForm->setFieldProperties("agence1", FIELDP_IS_LABEL,true);
                                        /* JS : active ou désactive des champs selon le type de garantie */
																				  $JS_active = "";
																				  $JS_active .="\nfunction check_agence()";
																				  $JS_active .="\n{";
																				  $JS_active .="\tif(document.ADForm.HTML_GEN_LSB_agence.value == -1)";
																				  $JS_active .="\n\t{";
																				  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_agence1.disabled = false; ";
																				  $JS_active .="\n\t}";
																				  $JS_active .="\n\telse ";
																				  $JS_active .="\n\t{";
																				  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_agence1.disabled = true;";
																				  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_agence1.value=0;";
																				  $JS_active .="\n\t}";
																				  $JS_active .="\n\t}";//fin de la fonction
																				  $myForm->setFieldProperties("agence", FIELDP_JS_EVENT, array("OnChange"=>"check_agence()"));
																				  $myForm->addJS(JSP_FORM,"actve", $JS_active);
																				  $liste_op_siege_agc=$adsys["adsys_op_siege_agence"];

                                      }else{
                                      	 // on est ds une agence
                                      	 // si on est dans une agence, éliminer les opérations qui ont lieu au siège
																			   foreach($adsys["adsys_op_siege_agence"] as $num_op=>$libel){
																			    	if($num_op % 2 ==0){//num opération paire à l'agence
																			    		$liste_op_siege_agc[$num_op]=$libel;
																			    	}
																			    }
                                      }
                                      //type operation
                                      $myForm->addField("typeOperation", _("Opération"), TYPC_LSB);
                                      $myForm->setFieldProperties("typeOperation", FIELDP_ADD_CHOICES, $liste_op_siege_agc);
                                      // Période du Journal
                                      $myForm->addField("modele", _("Type de Journal"), TYPC_LSB);
                                      $choices = array (
                                                   1 => _("Journal quotidien"),
                                                   2 => _("Journal mensuel"),
                                                   3 => _("Journal personalisé")
                                                 );
                                      $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

                                      $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

                                      $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

                                      $myForm->addHTMLExtraCode("espace2", "<br/><br/>");

                                      $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
                                      $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

                                      $myForm->addHTMLExtraCode("espace3", "<br /><br />");

                                      $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
                                      $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

                                      $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
                                      $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
                                      $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
                                      $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
                                      $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
                                      $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
                                      $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
                                      $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
                                      $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
                                      $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
                                      $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

                                      $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                                      $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-22");

                                      $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
                                      $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                                      $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                                      // Désactivation des éléments
                                      $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
                                                document.ADForm.HTML_GEN_LSB_annee.disabled = true;
                                                document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
                                                document.ADForm.HTML_GEN_date_date_debut.disabled = true;
                                                document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
                                      $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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

                                      $myForm->addJS(JSP_FORM, "JSActivate", $JS);

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

                                      $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);
                                      $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                                                    "onchange" => "activateFields();"
                                                                  ));
                                      $myForm->buildHTML();
                                      echo $myForm->getHTML();

                                    }
/*}}}*/

/*{{{ Tra-22 : JOU - Impression du journal des annulations */
                                    else
                                      if ($global_nom_ecran == 'Tra-22') { debug($agence,"agence");
                                      	if (isSiege()) {

																		        if ($agence == -1) {//siege
																		        	$liste_ag[$global_id_agence] = $global_id_agence;
																		        	$agence= $global_id_agence;
																		        	$titre=" "._("du SIEGE");
																		        	if($agence1!=NULL){
																		        		$tmp_list_age=getAllIdNomAgence();
																		        		$titre.=" "._("et de l'agence")." ".$tmp_list_age[$agence1];
																		        	}
																		        }elseif($agence>0){//agence au siege
																		        	$liste_ag[$agence] = $agence;

																		        }elseif($agence==0) {//agences et siege
																		          $liste_ag = getAllIdNomAgence();
																		          $titre=" "._("du SIEGE et AGENCES");
																		        }
																		      } else
																		        $liste_ag[$global_id_agence] = $global_id_agence; //Dans une agence

                                        setGlobalIdAgence($agence); //on travaille avec l'agence sélectionnée'
                                        // Construction de la période selon le modèle choisi
                                        if ($modele == 1) { // Rappport journal quotidien
                                          $date_deb = $date_journaliere;
                                          $date_fin = $date_journaliere;
                                        } else
                                          if ($modele == 2) { // Rappport journal mensuel
                                            $date_deb = date("d/m/Y", mktime(0, 0, 0, $mois, 1, $annee));
                                            $date_fin = date("d/m/Y", mktime(0, 0, 0, $mois +1, 0, $annee));
                                          } else
                                            if ($modele == 3) { // Rappport journal personnalisé
                                              $date_deb = $date_debut;
                                              $date_fin = $date_fin;
                                            }

                                        // Sélection des mouvements de la période passés sur les comptes reflets
                                        $consolide = NULL; // tous les mouvements (consolidés ou pas)

                                        $lignesJournal = getMouvementsReciproques($date_deb, $date_fin, $consolide,$liste_ag,$agence1,$typeOperation);

                                        if ($lignesJournal != NULL)
                                          $DATA["lignes_journal"] = $lignesJournal;
                                        else
                                          $DATA["lignes_journal"] = NULL;

                                        $liste_criteres = array (
                                                            _("Journal")." " => _("Les mouvements sur les comptes reflets")." ".$titre."
                                                                            ", _("du[[A partir de]]")." " => $date_deb, _("au[[Jusqu au]]")." " => $date_fin);
                                         if($typeOperation>0){
                                        	$liste_criteres[_("Opération")]=adb_gettext($adsys["adsys_op_siege_agence"][$typeOperation]);
                                        }
                                        $xml = xml_mouvements_reciproques($DATA, $liste_criteres,$agence);

                                        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'journal_annulations.xslt');

                                        echo get_show_pdf_html("Tra-1", $fichier_pdf);

                                        ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);

                                      }
/*}}}*/

/*{{{ Tra-23 : IST - Personnalisation du compte de résultat  de la BNR*/
                else
                  if ($global_nom_ecran == 'Tra-23') {
                    $myForm = new HTML_GEN2(_("Personnalisation du compte de résultat BNR")." ");
                    $SESSION_VARS['type_etat']=$type_rapport;
                    if (isSiege()) {
                    	//Remettre $global_id_agence à l'identifiant de l'agence courante
	                    resetGlobalIdAgence();
	                    //Agence- Tri par agence
	                    $list_agence = getAllIdNomAgence();
	                    $list_agence['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
	                    $list_agence['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
	                    $list_agence['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");

                      unset ($list_agence[$global_id_agence]);
                      $myForm->addField("agence", _("Agence"), TYPC_LSB);
                      $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $list_agence);
                      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
                      $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);

                    }

                    // Modèles de balance
                    $myForm->addField("modele", _("Type compte de résultat"), TYPC_LSB);
                    $choices = array (
                                 1 => _("Résultat journalier")."
                                       ", 2 => _("Résultat mensuel"), 3 => _("Résultat personalisé"));
                    $myForm->setFieldProperties("modele", FIELDP_ADD_CHOICES, $choices);

                    $myForm->addHTMLExtraCode("espace1", "<br /><br />");

                    $myForm->addField("date_journaliere", _("Date"), TYPC_DTE);

                    $myForm->addHTMLExtraCode("espace2", "<br /><br />");

                    $myForm->addTableRefField("mois", _("Mois"), "adsys_mois", "sortNumeric");
                    $myForm->addTableRefField("annee", _("Année"), "adsys_annee");

                    $myForm->addHTMLExtraCode("espace3", "<br /><br />");

                    $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
                    $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

                    $myForm->setFieldProperties("modele", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_journaliere", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_journaliere", FIELDP_DEFAULT, date("d/m/Y"));
                    $myForm->setFieldProperties("mois", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("mois", FIELDP_DEFAULT, date("m"));
                    $myForm->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("annee", FIELDP_DEFAULT, date("Y"));
                    $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
                    $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
                    $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

                    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-24");

                    $myForm->addFormButton(1, 2, "valider_csv", _("Export CSV"), TYPB_SUBMIT);
                    $myForm->setFormButtonProperties("valider_csv", BUTP_PROCHAIN_ECRAN, "Tra-25");

                    $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
                    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ara-57");
                    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                    // Doit être fait via JS car si o le fait via HTML_GEN, il va transformer la listbox en textbos
                    $JSInit = "document.ADForm.HTML_GEN_LSB_mois.disabled = true;
                              document.ADForm.HTML_GEN_LSB_annee.disabled = true;
                              document.ADForm.HTML_GEN_date_date_journaliere.disabled = true;
                              document.ADForm.HTML_GEN_date_date_debut.disabled = true;
                              document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
                    $myForm->addJS(JSP_FORM, "JSInit", $JSInit);

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
                    $myForm->addJS(JSP_FORM, "JSActivate", $JS);

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
                    $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                    $myForm->setFieldProperties("modele", FIELDP_JS_EVENT, array (
                                                  "onchange" => "activateFields();"
                                                ));

                    $myForm->buildHTML();
                    echo $myForm->getHTML();
                  }
/*}}}*/

/*{{{ Tra-24 Tra-25: IST - Impresion du compte de résultat BNR */
                  else
                    if ($global_nom_ecran == 'Tra-24' || $global_nom_ecran == 'Tra-25') {

                      global $global_id_agence; // agence encours
                      global $global_monnaie;

                      // récupération des aegnces à imprimer
                      $agence = $_POST['agence']; // agences sélectionées
                      $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

                      if ($agence == -1) { // on imprime que pour le siège
                        $liste_ag[$global_id_agence] = $global_id_agence;
                        $agence=$global_id_agence;
                        $titre1=" "._("siège")." ";
                      }
                      elseif ($agence > 0) // on imprime pour une agnce
                      $liste_ag[$agence] = $agence;
                      else { // on imprime pour toutes les agences et le siège
                      	$liste_ag=getAllIdNomAgence();

                      	if($agence==-2){
                      		$consolide=true;
                      		$titre1=" "._("consolidé")." ";
                      	}elseif($agence==-3){
                      		unset ($liste_ag[$global_id_agence]);
                      		$titre1=" "._("des agences")." ";
                      	}
                      	$agence=$global_id_agence;
                      }
                      // modèle de balance
                      if ($modele == 1) {
                        $date_deb = $date_journaliere;
                        $date_fin = $date_journaliere;
                      } else
                        if ($modele == 2) {
                          $date_deb = date("d/m/Y", mktime(0, 0, 0, $mois, 1, $annee));
                          $date_fin = date("d/m/Y", mktime(0, 0, 0, $mois +1, 0, $annee));
                        } else
                          if ($modele == 3) {
                            /* Le résultat est lié à un exercice. Donc les dates de début et de fin doivent être sur un même exercie */
                            $date_deb = $date_debut;
                            $date_fin = $date_fin;
                          }

                      $DATA = getPoste_solde($date_deb, $date_fin,$SESSION_VARS['type_etat'], $liste_ag);
 				              if (empty($DATA) ) {

                        $html_err = new HTML_erreur(_("Rapport Compte de résultat"));
                        $msg=_("Aucun format du rapport compte de résultat n'a été paramètré");
                        $html_err->setMessage(_("Echec")." : ".$msg);
                        $html_err->addButton(BUTTON_OK, "Ara-57");
                        $html_err->buildHTML();
                        echo $html_err->HTML_code;
                      }
                      // Ajout du titre
                      if ($modele == 1)
                        $titre = $date_journaliere;
                      else
                        if ($modele == 2)
                          $titre = sprintf(_("du[[à partire du]] %s au[[jusqu au]] %s"),$date_deb,$date_fin); //"From $date_deb To $date_fin"
                        else
                          if ($modele == 3)
                            $titre = sprintf(_("du[[à partire du]] %s au[[jusqu au]] %s"),$date_deb,$date_fin); //"From $date_deb To $date_fin"
                      $titre= $titre1." ($global_monnaie): ".$titre;

                      if( $global_nom_ecran == 'Tra-24' ){
                      	//génération du xml
                      	 $xml = xml_compte_resultat_BNR($DATA,$SESSION_VARS['type_etat'], $titre,$agence);
                      	//Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'compte_de_resultat_bnr.xslt');
                        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                        echo get_show_pdf_html("Ara-57", $fichier_pdf);

                      } elseif( $global_nom_ecran == 'Tra-25') {
                      	//génération du xml
                      	 $xml = xml_compte_resultat_BNR($DATA,$SESSION_VARS['type_etat'], $titre,$agence,true);
                      	//Génération  du fichier CSV
			                  $csv_file = xml_2_csv($xml, 'compte_de_resultat_bnr.xslt');
			                  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
			                  echo getShowCSVHTML("Ara-57", $csv_file);
                      }
                    }
/*}}}*/

/*{{{ Tra-26 : BSH - Personnalisation du Bilan BNR */
                    else
                      if ($global_nom_ecran == 'Tra-26') {
                        $myForm = new HTML_GEN2(_("Saisie de la date"));
                        //Remettre $global_id_agence à l'identifiant de l'agence courante
                        resetGlobalIdAgence();
                        //Agence- Tri par agence
                        $list_agence = getAllIdNomAgence();
                        $SESSION_VARS['type_etat']=$type_rapport;


                        if (isSiege()) {
                        	$list_agence['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
                        	$list_agence['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
    						$list_agence['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");

                          unset ($list_agence[$global_id_agence]);
                          $myForm->addField("agence", _("Agence"), TYPC_LSB);
                          $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $list_agence);
                          $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
                          $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);


                        }

                        //  $myForm->addHTMLExtraCode("espace1", "<br /><br />");

                        $myForm->addField("date_bilan", _("Date"), TYPC_DTE);

                        $myForm->setFieldProperties("date_bilan", FIELDP_IS_REQUIRED, true);
                        $myForm->setFieldProperties("date_bilan", FIELDP_DEFAULT, date("d/m/Y"));

                        $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-27");
                        $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);

                        $myForm->addFormButton(1, 2, "valider_csv", _("Export CSV"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("valider_csv", BUTP_PROCHAIN_ECRAN, "Tra-28");

                        $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ara-57");
                        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                        $myForm->buildHTML();
                        echo $myForm->getHTML();
                      }
/*}}}*/

/*{{{ Tra-27 ou Tra-28 : BSH - Impression du bilan BNR */
                      else
                        if ($global_nom_ecran == 'Tra-27' || $global_nom_ecran == 'Tra-28') {
                          set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time

                          global $global_id_agence;
                          // récupération des agences à imprimer

                          $agence = $_POST['agence']; // agences sélectionées
                          $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

                          if ($agence == -1) { // on imprime que pour le siège
                            $liste_ag[$global_id_agence] = $global_id_agence;
                            $agence=$global_id_agence;
                            $titre1="("._("SIEGE").")";
                          }
                          elseif ($agence > 0){ // on imprime pour une agnce
                          $liste_ag[$agence] = $agence;
                          }
                          else { // on imprime pour toutes les agences et le siège
                      			$liste_ag=getAllIdNomAgence();

                      			if($agence==-2){
                      				$consolide=true;
                      				$titre1="("._("consolidé").")";
                      			}elseif($agence==-3){
                      				unset ($liste_ag[$global_id_agence]);
                      				 $titre1="("._("AGENCES").")";
                      			}
                      			$agence=$global_id_agence;
                          }

                          if ($niveau == '') {
                            $niveau = $niveau_max;
                          }
                          $DATA =  getPoste_solde($date_bilan, $date_bilan,$SESSION_VARS['type_etat'], $liste_ag,$consolide);

                          $titre = $date_bilan;
                          $titre=$titre1.": ".$titre;

                          if( $global_nom_ecran == 'Tra-27' ) {
                          	//Génération du xml
                          	$xml = xml_bilan_BNR($DATA,$SESSION_VARS['type_etat'], $titre,$agence);
                          	//Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'bilan_bnr.xslt');
                            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
                            echo get_show_pdf_html("Ara-57", $fichier_pdf);

                          } elseif( $global_nom_ecran == 'Tra-28' ) {
                          	//Génération du xml
                          	$xml = xml_bilan_BNR($DATA,$SESSION_VARS['type_etat'], $titre,$agence,true);
                          	//Génération  du fichier CSV
					                  $csv_file = xml_2_csv($xml, 'bilan_bnr.xslt');
					                  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
					                  echo getShowCSVHTML("Ara-57", $csv_file);
                          }
                        }
/*}}}*/
/*{{{ Tra-29  : LIR - Personnalisation du Ratio de liquidité BNR */
                    else
                      if ($global_nom_ecran == 'Tra-29' ) {
                        $myForm = new HTML_GEN2(_("Saisie de la date"));
                        //Remettre $global_id_agence à l'identifiant de l'agence courante
                        resetGlobalIdAgence();
                        //Agence- Tri par agence
                        $list_agence = getAllIdNomAgence();
                        //id du rapport
                        $SESSION_VARS['type_etat']=$type_rapport;

                        if (isSiege()) {
                        	$list_agence['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");

                          unset ($list_agence[$global_id_agence]);
                          $myForm->addField("agence", _("Agence"), TYPC_LSB);
                          $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $list_agence);
                          $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_AUCUN, false);
                          $myForm->setFieldProperties("agence", FIELDP_HAS_CHOICE_TOUS, true);


                        }

                        //  $myForm->addHTMLExtraCode("espace1", "<br /><br />");

                        $myForm->addField("date_ratio", _("Date"), TYPC_DTE);

                        $myForm->setFieldProperties("date_ratio", FIELDP_IS_REQUIRED, true);
                        $myForm->setFieldProperties("date_ratio", FIELDP_DEFAULT, date("d/m/Y"));

                        $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-30");
                        $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);

                        $myForm->addFormButton(1, 2, "valider_csv", _("Export CSV"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("valider_csv", BUTP_PROCHAIN_ECRAN, "Tra-31");

                        $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
                        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ara-57");
                        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

                        $myForm->buildHTML();
                        echo $myForm->getHTML();
                      }
/*}}}*/

/*{{{ Tra-30 Tra-31 : RLI - Impression du bilan BNR */
                      else
                        if ($global_nom_ecran == 'Tra-30' || $global_nom_ecran == 'Tra-31') {
                          set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time

                          global $global_id_agence;
                          // récupération des agences à imprimer

                          $agence = $_POST['agence']; // agences sélectionées
                          $liste_ag = array (); // préparation de la liste des agences à passer à la fonction

                          if ($agence == -1) { // on imprime que pour le siège
		                        $liste_ag[$global_id_agence] = $global_id_agence;
		                        $agence=$global_id_agence;
		                        $titre1=" ".("siège")." ";
		                      }
		                      elseif ($agence > 0) // on imprime pour une agnce
		                      $liste_ag[$agence] = $agence;
		                      else { // on imprime pour toutes les agences et le siège
		                      	$liste_ag=getAllIdNomAgence();

		                      	if($agence==-2){
		                      		$consolide=true;
		                      		$titre1=" "._("consolidé")." ";
		                      	}elseif($agence==-3){
		                      		unset ($liste_ag[$global_id_agence]);
		                      		$titre1=" "._("des agences")." ";
		                      	}
		                      	$agence=$global_id_agence;
		                      }

                          $DATA =  getPoste_solde_ratio_liquidite($date_ratio,$SESSION_VARS['type_etat'], $liste_ag);
                          if (empty($DATA) ) {

		                        $html_err = new HTML_erreur(_("Rapport ratio de liquidité")." ");
		                        $msg=_("Aucun format du rapport ratio de liquidité n'a été paramètré");
		                        $html_err->setMessage(_("Echec : ".$msg ));
		                        $html_err->addButton(BUTTON_OK, "Ara-57");
		                        $html_err->buildHTML();
		                        echo $html_err->HTML_code;
			                      }else {
				                      	$titre=$titre1;
			                          $list_criteres = array ("Date of edition" => $date_ratio);

			                          if( $global_nom_ecran == 'Tra-30') {
			                          	//Génération du xml
			                          	 $xml = xml_ratio_liquidite_BNR($DATA,$titre,$list_criteres);
			                          	//Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
			                            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'ratio_liquidite_bnr.xslt');
			                            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
			                            echo get_show_pdf_html("Ara-57", $fichier_pdf);

			                          } elseif ($global_nom_ecran == 'Tra-31') {
			                          	//Génération du xml
			                          	 $xml = xml_ratio_liquidite_BNR($DATA,$titre,$list_criteres,true);
			                          	//Génération  du fichier CSV
								                  $csv_file = xml_2_csv($xml, 'ratio_liquidite_bnr.xslt');
								                  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
								                  echo getShowCSVHTML("Ara-57", $csv_file);
			                          }

			                      }

                        }

                        else
                          if ($global_nom_ecran == 'Tra-32') {
                            $myForm = new HTML_GEN2(_("Impôt mobilier collecté"));
                            //Agence- Tri par agence
//                            if (isSiege()) {
//                              //  resetGlobalIdAgence();
//                              $myForm->addField("agence", _("Agence"), TYPC_LSB);
//                              $myForm->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
//                              $liste_agences = getAllIdNomAgence();
//                              $liste_agences['-1'] = _("SIEGE[[considérer les données du siège uniquement]]");
//                              $liste_agences['-2'] = _("[TOUS]CONSOLIDE[[considérer les données des agences de manière consolidée]]");
//                              $liste_agences['-3'] = _("[TOUS]AGENCES[[considérer les données de toutes les agences de manière individuelle]]");
//                              unset ($liste_agences[$global_id_agence]);
//                              $myForm->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
//                            }
                            $myForm->addHTMLExtraCode("espace1", "<br/><br/>");


                            $prod_epargne = getListProdEpargneDAT();

                            $choix_epargne = array();
                            if (isset($prod_epargne)) {
                              foreach ($prod_epargne as $key => $value) {
                                $choix_epargne[$value["id"]] = trim($value["libel"]);
                              }
                            }

                            $myForm->addField("id_prod_epg", _("Produit épargne"), TYPC_LSB);
                            $myForm->setFieldProperties("id_prod_epg", FIELDP_HAS_CHOICE_AUCUN, false);
                            $myForm->setFieldProperties("id_prod_epg", FIELDP_HAS_CHOICE_TOUS, true);
                            $myForm->setFieldProperties("id_prod_epg", FIELDP_ADD_CHOICES, $choix_epargne);

                            $myForm->addField("date_debut", _("Date début de période"), TYPC_DTE);
                            $myForm->addField("date_fin", _("Date fin de période"), TYPC_DTE);

                            $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
                            $myForm->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
                            $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
                            $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

                            $myForm->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
                            $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-33");
                            $myForm->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
                            $myForm->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Tra-34");
                            $myForm->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
                            $myForm->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Tra-34");
                            $myForm->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
                            $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                            $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);


                            // Code javascript pour la vérification des champs obligatoires
                            $JSCheck = "\n\tif (document.ADForm.HTML_GEN_date_date_debut.value == '')
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
                                 }";

                            $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                            $myForm->buildHTML();
                            echo $myForm->getHTML();
                          }

                          /*}}}*/

                          /*{{{ Tra-33 : JOU - Impression du journal comptable */
                          else
                            if ($global_nom_ecran == 'Tra-33' || $global_nom_ecran == 'Tra-34') {
//                              if(isSiege()&&$agence==-1){
//                                $agence=0;
//                              }
//                              setGlobalIdAgence($agence); //on travaille avec l'agence sélectionnée'

                              $tranche_data = get_data_impot_mobilier_collecte($id_prod_epg, $date_debut, $date_fin, null, null);
                              //On recupère le nombre de lignes générées par la requête pour la condition d'arret
                              $xml = xml_liste_impot_mobilier_collecte($tranche_data, $date_debut, $date_fin);

                              if ($global_nom_ecran == 'Tra-33') {
                                //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
                                $fichier_pdf = xml_2_xslfo_2_pdf($xml, "liste_impot_mobilier_collecte.xslt");
                                echo get_show_pdf_html("Tra-32", $fichier_pdf);
                              } else if ($global_nom_ecran == 'Tra-34') {
                                $csv_file = xml_2_csv($xml, "liste_impot_mobilier_collecte.xslt");
                                if (isset($excel) && $excel == 'Export EXCEL') {
                                  echo getShowEXCELHTML("Tra-32", $csv_file);
                                }
                                else{
                                  echo getShowCSVHTML("Tra-32", $csv_file);
                                }
                              }

                              ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);

                            }
                            /*}}}*/

                            /*{{{ Tra-35 : IAP - Personnalisation du rapport intérêts à payer */
                            else
                                if ($global_nom_ecran == 'Tra-35') {
                                    $myForm = new HTML_GEN2(_("Intérêts à payer sur les compte d’épargne"));

                                    $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

                                    $prod_epargne = getListProdEpargneDAT();

                                    $choix_epargne_DAT = array();
                                    if (isset($prod_epargne)) {
                                        foreach ($prod_epargne as $key => $value) {
                                            $choix_epargne_DAT[$value["id"]] = trim($value["libel"]);
                                        }
                                    }

                                    $myForm->addField("id_prod_epg", _("Produit d'épargne"), TYPC_LSB);
                                    $myForm->setFieldProperties("id_prod_epg", FIELDP_HAS_CHOICE_AUCUN, false);
                                    $myForm->setFieldProperties("id_prod_epg", FIELDP_HAS_CHOICE_TOUS, true);
                                    $myForm->setFieldProperties("id_prod_epg", FIELDP_ADD_CHOICES, $choix_epargne_DAT);

                                    $myForm->addField("date_rapport", _("Date"), TYPC_DTE);
                                    $myForm->setFieldProperties("date_rapport", FIELDP_IS_REQUIRED, true);
                                    $myForm->setFieldProperties("date_rapport", FIELDP_DEFAULT, date("d/m/Y"));

                                    $myForm->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
                                    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-36");
                                    $myForm->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
                                    $myForm->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Tra-37");
                                    $myForm->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
                                    $myForm->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Tra-37");
                                    $myForm->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
                                    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                                    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);


                                    // Code javascript pour la vérification des champs obligatoires
                                    $JSCheck = "\n\tif (document.ADForm.HTML_GEN_date_date_rapport.value == '')
                                 {
                                   msg += '- "._("La date rapport doit être renseignée")."\\n';
                                   ADFormValid = false;
                                 }
                                   if (!isDate(document.ADForm.HTML_GEN_date_date_rapport.value))
                                 {
                                   msg += '- "._("Le format du champs \" Date \" est incorrect")."\\n';
                                   ADFormValid = false;
                                 }";

                                    $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                                    $myForm->buildHTML();
                                    echo $myForm->getHTML();
                                }

                                /*}}}*/

                                /*{{{ Tra-36 : IAP - Impression du rapport intérêts à payer */
                                else
                                    if ($global_nom_ecran == 'Tra-36' || $global_nom_ecran == 'Tra-37') {

                                        global $adsys;

                                        $id_prod_epg = $date_rapport = NULL;

                                        if(!is_null($_POST['id_prod_epg']))
                                            $id_prod_epg = $_POST['id_prod_epg'];

                                        if(!is_null($_POST['date_rapport']))
                                            $date_rapport = $_POST['date_rapport'];

                                        $criteres_recherche['id_prod_epg'] = $id_prod_epg;
                                        $criteres_recherche['date_rapport'] = $date_rapport;

                                        $id_prod_epg_libel = _('Tous');
                                        if($id_prod_epg != NULL){
                                            $id_prod_epg_libel = getLibelProdEp($id_prod_epg);
                                        }

                                        $criteres = array (
                                            _("Produit d'épargne") => $id_prod_epg_libel,
                                            _("Date") => date($date_rapport),
                                        );

                                        $DATAS = getRapportCalcIntPayeData($criteres_recherche);
                                        $xml = xml_calc_int_paye($DATAS, $criteres);

                                        if ($global_nom_ecran == 'Tra-36') {
                                            $fichier_pdf = xml_2_xslfo_2_pdf($xml, "calc_int_paye.xslt"); //Génération du XSL-FO et du PDF
                                            echo get_show_pdf_html("Tra-35", $fichier_pdf);
                                        } else if ($global_nom_ecran == 'Tra-37') {
                                            $csv_file = xml_2_csv($xml, "calc_int_paye.xslt");
                                            if (isset($excel) && $excel == 'Export EXCEL') {
                                              echo getShowEXCELHTML("Tra-35", $csv_file);
                                            }
                                            else{
                                              echo getShowCSVHTML("Tra-35", $csv_file);
                                            }
                                        }

                                        ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);

                                    }
                                    /*}}}*/
/**************************************************************************************************************************************************************/
                                    /*{{{ Tra-41 : IAP - Personnalisation du rapport intérêts à recevoir */
                                    else
                                      if ($global_nom_ecran == 'Tra-41') {
                                        $myForm = new HTML_GEN2(_("Intérêts à recevoir sur les compte de credits"));

                                        $myForm->addHTMLExtraCode("espace1", "<br/><br/>");

                                        $prod_credit = getListProdCredits();

                                        $choix_credit = array();
                                        if (isset($prod_credit)) {
                                          foreach ($prod_credit as $key => $value) {
                                            $choix_credit[$value["id"]] = trim($value["libel"]);
                                          }
                                        }

                                        $myForm->addField("id_prod_crdt", _("Produit de credits"), TYPC_LSB);
                                        $myForm->setFieldProperties("id_prod_crdt", FIELDP_HAS_CHOICE_AUCUN, false);
                                        $myForm->setFieldProperties("id_prod_crdt", FIELDP_HAS_CHOICE_TOUS, true);
                                        $myForm->setFieldProperties("id_prod_crdt", FIELDP_ADD_CHOICES, $choix_credit);

                                        $myForm->addField("date_rapport", _("Date"), TYPC_DTE);
                                        $myForm->setFieldProperties("date_rapport", FIELDP_IS_REQUIRED, true);
                                        $myForm->setFieldProperties("date_rapport", FIELDP_DEFAULT, date("d/m/Y"));

                                        $myForm->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
                                        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Tra-42");
                                        $myForm->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
                                        $myForm->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Tra-43");
                                        $myForm->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
                                        $myForm->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Tra-43");
                                        $myForm->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
                                        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tra-1");
                                        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);


                                        // Code javascript pour la vérification des champs obligatoires
                                        $JSCheck = "\n\tif (document.ADForm.HTML_GEN_date_date_rapport.value == '')
                                 {
                                   msg += '- "._("La date rapport doit être renseignée")."\\n';
                                   ADFormValid = false;
                                 }
                                   if (!isDate(document.ADForm.HTML_GEN_date_date_rapport.value))
                                 {
                                   msg += '- "._("Le format du champs \" Date \" est incorrect")."\\n';
                                   ADFormValid = false;
                                 }";

                                        $myForm->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

                                        $myForm->buildHTML();
                                        echo $myForm->getHTML();
                                      }

                                      /*}}}*/
                                      /*{{{ Tra-42-43 : IAP - Impression du rapport intérêts à recevoir */
                                      else
                                        if ($global_nom_ecran == 'Tra-42' || $global_nom_ecran == 'Tra-43') {

                                          global $adsys;

                                          $id_prod_crdt = $date_rapport = NULL;

                                          if(!is_null($_POST['id_prod_crdt']))
                                            $id_prod_crdt = $_POST['id_prod_crdt'];

                                          if(!is_null($_POST['date_rapport']))
                                            $date_rapport = $_POST['date_rapport'];

                                          $criteres_recherche['id_prod_crdt'] = $id_prod_crdt;
                                          $criteres_recherche['date_rapport'] = $date_rapport;

                                          $id_prod_crdt_libel = _('Tous');
                                          if($id_prod_crdt != NULL){
                                            $id_prod_crdt_libel = getLibelProdcrdt($id_prod_crdt);
                                          }

                                          $criteres = array (
                                              _("Produit de credits") => $id_prod_crdt_libel,
                                              _("Date") => date($date_rapport),
                                          );

                                          $DATAS = getRapportCalcIntRecevoirData($criteres_recherche);
                                          //print_rn($DATAS);
                                          //exit();
                                          $xml = xml_calc_int_recevoir($DATAS, $criteres);

                                          if ($global_nom_ecran == 'Tra-42') {
                                            //TODO changer les fichier xslt pour pdf et excel
                                            $fichier_pdf = xml_2_xslfo_2_pdf($xml, "calc_int_recevoir.xslt"); //Génération du XSL-FO et du PDF
                                            echo get_show_pdf_html("Tra-41", $fichier_pdf);
                                          } else if ($global_nom_ecran == 'Tra-43') {
                                            $csv_file = xml_2_csv($xml, "calc_int_recevoir.xslt");
                                            if (isset($excel) && $excel == 'Export EXCEL'){
                                              echo getShowEXCELHTML("Tra-41", $csv_file);
                                            }
                                            else{
                                              echo getShowCSVHTML("Tra-41", $csv_file);
                                            }
                                          }

                                          ajout_historique(370, NULL, NULL, $global_nom_login, date("r"), NULL);

                                        }
                                        /*}}}*/

/*}}}*/
                                      else
                                        signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>