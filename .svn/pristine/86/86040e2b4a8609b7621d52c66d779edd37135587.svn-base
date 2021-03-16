<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [158] Dépöt par lot
 * Cette opération comprends les écrans :
 * - Sgu-1 : Type de dépôt
 * - Sgu-2 : Saise des dépôts
 * - Sgu-3 : Demande de confirmation des mouvements
 * - Sgu-4 : Enregistrement des dépôts
 * @package Guichet
 */
require_once 'lib/dbProcedures/guichet.php';

/*{{{ Sgu-1 : Type de dépôt */
if ($global_nom_ecran == "Sgu-1") {
  // tabeau pour la sauvegarde de la saisie
  $SESSION_VARS['saisie_lot'] = array();
  $msg = "";
  $html = new HTML_GEN2(_("Dépôt par lot"));
  $type_depot = array(1 => _("Dépôts clients"), 2 => _("Virement de salaires"));
  $html->addField("type_dep", _("Type de dépôt"), TYPC_LSB);
  $html->setFieldProperties("type_dep", FIELDP_ADD_CHOICES, $type_depot);
  $html->setFieldProperties("type_dep", FIELDP_IS_REQUIRED, true);

  // le libellé de l'écriture
  $choices=array();
  $list_libel = getLEL(); // Récupère de tous les libellés des écritures libres
  $choices[0]=_("Autre libellé");
  foreach ($list_libel as $key => $value)
  	$choices[$value["type_operation"]]=$value["libel_ope"];      
  $html->addField("libel_ope_def",_("Liste libellé opération"), TYPC_LSB);
  $html->setFieldProperties("libel_ope_def", FIELDP_ADD_CHOICES, $choices);
  $html->setFieldProperties("libel_ope_def", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("libel_ope_def", FIELDP_DEFAULT, $SESSION_VARS["type_operation"]);
  $html->setFieldProperties("libel_ope_def", FIELDP_JS_EVENT, array("onChange"=>"changeLibel();"));
  
  $html->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
  $libel_ope = new Trad($SESSION_VARS["libel_ope"]);
  $html->setFieldProperties("libel_ope", FIELDP_DEFAULT, $libel_ope);
  //$html->setFieldProperties("autre_libel_ope", FIELDP_IS_REQUIRED, true);
  $html->setFieldProperties("libel_ope", FIELDP_WIDTH, 40);
		
  $codejs ="\n\nfunction changeLibel() {";
  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==0)\n\t";
	$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value ='';";
	//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =false;";
	$codejs .= "}else{\n";
  foreach($choices as $type_operation=>$value) {
	 	$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==$type_operation)\n\t";
		$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value =\"" . $value . "\";";
		//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =true;";
		$codejs .= "}\n";
  }
  $codejs .= "}}\n";
  $html->addJS(JSP_FORM, "jslibel", $codejs);
  	
  $choices = array(1 => _("Cash"), 2 => _("Correspondant bancaire"));
  $html->addField("source", _("Source des fonds"), TYPC_LSB);
  $html->setFieldProperties("source", FIELDP_ADD_CHOICES, $choices);
  $html->setFieldProperties("source", FIELDP_IS_REQUIRED, true);

  $html->addTable("ad_his_ext", OPER_INCLUDE, array("num_piece","communication","remarque"));
  //Informations tireur (seulement dans le cas du chèque)
  $html->addField("nom_ben", _("Nom du tireur"), TYPC_TXT);
  $html->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
  $html->addHiddenType("id_ben","");

  $html->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
  $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=t', '"._("Recherche")."');return false;"));

  //Correspondant bancaire (seulement ceux en devise de référence)
  $libel_correspondant=getLibelCorrespondant();
  $html->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
  $html->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);


  //ordonner les champs pour l'affichage
  $order = array("type_dep", "libel_ope_def", "libel_ope", "source", "correspondant", "nom_ben", "num_piece", "communication", "remarque");
  $html->setOrder(NULL, $order);

  // transformer les champs en labels non modifiables
  $labels = array_diff($order, array("type_dep", "libel_ope_def", "libel_ope", "source", "communication", "remarque"));
  foreach ($labels as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  // En fonction du type de dépôt activer ou désactiver la source
  $active_source = "
                   function activateSource()
                 {
                   if (document.ADForm.HTML_GEN_LSB_type_dep.value == 0)
                 {
                   document.ADForm.HTML_GEN_LSB_source.value = 0;
                   document.ADForm.HTML_GEN_LSB_source.disabled = true;
                   document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
                   document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
                   document.ADForm.num_piece.disabled = true;
                   document.ADForm.num_piece.value = '';
                 }
                   else if (document.ADForm.HTML_GEN_LSB_type_dep.value == 1)
                 {
                   document.ADForm.HTML_GEN_LSB_source.value = 1;
                   document.ADForm.HTML_GEN_LSB_source.disabled = true;
                   document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
                   document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
                   document.ADForm.num_piece.disabled = true;
                   document.ADForm.num_piece.value = '';
                 }
                   else
                 {
                   document.ADForm.HTML_GEN_LSB_source.value = 0;
                   document.ADForm.HTML_GEN_LSB_source.disabled = false;
                 }

                 }";
  $html->addJS(JSP_FORM, "JS_type_dep", $active_source);
  $html->setFieldProperties("type_dep", FIELDP_JS_EVENT, array("onchange" => "activateSource()"));

  //en fonction du choix du compte, afficher les infos avec le onChange javascript
  $codejs = "
            function activateFields()
          {
            if (document.ADForm.HTML_GEN_LSB_source.value == 0)
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
            document.ADForm.num_piece.disabled = true;
            document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
            document.ADForm.num_piece.value = '';
          }
            else if (document.ADForm.HTML_GEN_LSB_source.value == 1)
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
            document.ADForm.num_piece.disabled = true;
            document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
            document.ADForm.num_piece.value = '';
          }
            else
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = false;
            document.ADForm.num_piece.disabled = false;
          }
          }";

  $html->addJS(JSP_FORM, "JS3", $codejs);
  $html->setFieldProperties("source", FIELDP_JS_EVENT, array("onchange" => "activateFields()"));

  // Checkform
  $jscheck = "if (document.ADForm.HTML_GEN_LSB_source.value == 2)
           {
             if (document.ADForm.HTML_GEN_LSB_correspondant.value == 0)
           {
             msg += '- "._("Le champ correspondant doit etre renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.id_ben.value == '')
           {
             msg += '- "._("Le champ tireur doit etre renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.num_piece.value == '')
           {
             msg += '- "._("Le champ numéro de pièce doit etre renseigné")."\\n';
             ADFormValid = false;
           }
           }";
  $html->addJS(JSP_BEGIN_CHECK, "jscheck", $jscheck);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Sgu-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Sgu-2 : Saisie des dépôts */
else if ($global_nom_ecran == "Sgu-2") {
	
  // Sauvegarde des valeurs provenant du ecran precedent Sgu-3_KG
	if ($global_nom_ecran_prec == "Sgu-3") {// traitement retour d'ecran anticipé_Bug ecran blanche404
		$SESSION_VARS ['saisie_lot'];
		
		$SESSION_VARS["SOURCE"];
		
		/* Récupération du pourcentage et du montant de la commission s'il s'agit de virement de salaires */
		if ($SESSION_VARS ["type_dep"] == 2) {
			$AG = getAgenceDatas ( $global_id_agence );
			$pr_com_vir = $AG ['prc_com_vir'];
			$mnt_com_vir = $AG ['mnt_com_vir'];
		} else {
			$pr_com_vir = 0;
			$mnt_com_vir = 0;
		}
		
		$html = "<h1 align=\"center\">" . _ ( "Saisie par lot (dépôt sur compte )" ) . "</h1><h2 align=\"center\">" . $libel_operation_entet . "</h2><br><br>\n";
		$html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
		$html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
		// Ligne titre: Depot lot
		if ($SESSION_VARS ["type_dep"] == 2)
			$html .= "<TR bgcolor=$colb_tableau><TD><b>" . _ ( "n°" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Date valeur" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° compte" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Montant" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Commission" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Devise" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° reçu" ) . "</b></TD></TR>\n";
		else // virement salaire
			$html .= "<TR bgcolor=$colb_tableau><TD><b>" . _ ( "n°" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Date valeur" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° compte" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Montant" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Devise" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° reçu" ) . "</b></TD></TR>\n";
		
		for($i = 1; $i <= 40; ++ $i) {
			// On alterne la couleur de fond
			if ($i % 2)
				$color = $colb_tableau;
			else
				$color = $colb_tableau_altern;
				
				// Recup valeurs par défaut
			if (isset ( $SESSION_VARS ['saisie_lot'] [$i] ['date'] ))
				$date = $SESSION_VARS ['saisie_lot'] [$i] ['date'];
			else
				$date = date ( "d/m/Y" );
			
			if (isset ( $SESSION_VARS ['saisie_lot'] [$i] ['mnt_com'] ))
				$mnt_com = $SESSION_VARS ['saisie_lot'] [$i] ['mnt_com'];
			else {
				$mnt_com = $mnt_com_vir + ($ {'mnt' . $i} * $pr_com_vir);
				$mnt_com = afficheMontant ( $mnt_com, false );
			}
			
			$cpte_client = $SESSION_VARS ['saisie_lot'] [$i] ['cpte_client'];
			$mnt = afficheMontant ( $SESSION_VARS ['saisie_lot'] [$i] ['mnt'], false );
			$devise_cpte = $SESSION_VARS ['saisie_lot'] [$i] ['devise'];
			$num_recu = $SESSION_VARS ['saisie_lot'] [$i] ['num_recu'];
			
			$html .= "<TR bgcolor=$color>\n";
			
			// n°
			$html .= "<TD><b>$i</b></TD>";
			
			// Date valeur
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"date$i\" size=10 value=" . $SESSION_VARS ['saisie_lot'] [$i] ['date'] . ">\n"; // FIXME OL: NE MARCHERA PAS TEL QUEL EN MULTILINGUE !!!
			$html .= "<FONT size=\"2\"><A href=\"#\" onClick=\"if (! isDate(document.ADForm.date$i.value)) document.ADForm.date$i.value='';open_calendrier(getMonth(document.ADForm.date$i.value), getYear(document.ADForm.date$i.value), $calend_annee_passe, $calend_annee_futur, 'date$i');return false;\">" . _ ( "Calendrier" ) . "</A></FONT></TD>";
			
			// COMPTE Client
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=14 value=" . $SESSION_VARS ['saisie_lot'] [$i] ['cpte_client'] . ">\n";
			$html .= "<FONT size=\"2\"><A href=# onclick=\"open_compte(" . $i . ");return false;\">" . _ ( "Recherche<" ) . "/A></FONT></TD>\n";
			$html .= "<INPUT TYPE=\"hidden\" NAME=\"num_id_cpte" . $i . "\"> </TD> \n";
			
			// Montant
			if ($SESSION_VARS ["type_dep"] == 2) { 
			                                      // $html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);document.ADForm.mnt_com$i.value=$mnt_com_vir+$pr_com_vir*recupMontant(value);\" size=12 value=\"$mnt\"></TD>\n";
				$html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);document.ADForm.mnt_com$i.value=$mnt_com_vir+$pr_com_vir*recupMontant(value);\" size=12 value=".$SESSION_VARS['saisie_lot'][$i]['mnt']."></TD>\n";
				
				// Commission en cas de virement de salaires
				$html .= "<TD><INPUT NAME=\"mnt_com$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);\" size=12 value=" . $SESSION_VARS ['saisie_lot'] [$i] ['mnt_com'] . "></TD>\n";
			} else {	
				$html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);\" size=12 value=" . $SESSION_VARS ['saisie_lot'] [$i] ['mnt'] . "></TD>\n";
			}
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"devise$i\" size=3 disabled=\"true\" value=" . $SESSION_VARS ['saisie_lot'] [$i] ['devise'] . "></TD>\n";
			
			// N° reçu
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"num_recu$i\" size=8 value=" . $SESSION_VARS ['saisie_lot'] [$i] ['num_recu'] . "></TD>\n";
			$html .= "</TR>\n";
		}
		
		$html .= "<TR bgcolor=$colb_tableau><TD colspan=5 align=\"center\">\n";
		
		// Boutons
		$html .= "<TABLE align=\"center\"><TR><TD><INPUT TYPE=\"submit\" VALUE=\"" . _ ( "Valider" ) . "\" onclick=\"ADFormValid = true; checkForm(); assign('Sgu-3');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"" . _ ( "Retour" ) . "\" onclick=\"ADFormValid=true;assign('Sgu-1');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"" . _ ( "Annuler" ) . "\" onclick=\"ADFormValid=true;assign('Gen-6');\"></TD></TR></TABLE>\n";
		$html .= "</TD></TR></TABLE>\n";
		$html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";
		
		// Script checks
		$html .= "<script type=\"text/javascript\">\n";
		$html .= "function checkForm(){";
		$html .= "msg = '';\n";
		
		for($i = 1; $i <= 40; ++ $i) {
			$html .= "  if ((! isDate(document.ADForm.date$i.value)) && (document.ADForm.date$i.value != '')){ ADFormValid = false; msg += '" . sprintf ( _ ( "La date de la ligne n°%s est incorrecte !" ), $i ) . "\\n';}";
		}
		
		$html .= "if (msg != '') alert(msg);";
		$html .= "}";
		
		/* Debut JS : recherche du compte de depôt du client */
		$html .= "";
		$html .= "\nfunction open_compte( index )\n";
		$html .= "{\n";
		$html .= "\nurl = '" . $http_prefix . "/modules/clients/rech_client.php?m_agc=" . $_REQUEST ['m_agc'] . "&choixCompte=1&cpt_dest=cpte_client' +index+ '&id_cpt_dest=num_id_cpte'+index+ '&devise_cpte_dest=devise'+index+'&is_depot=true';\n";
		$html .= "\t\tgarant = OpenBrwXY(url, '" . _ ( "Compte de Dépôt" ) . "', 400, 500);\n";
		$html .= "}\n";
		/* Fin JS : recherche du compte de depôt du client */
		
		$html .= "</script>\n";
		
		echo $html;
	} 	

	// Cette parti traitment initial_Existant_
	else {
	
  if (isset($type_dep)) {
    // Les dépôts de clients se font via la caisse
    if ($type_dep == 1)
      $source = 1;

    $INFOSOURCE = array();
    $INFOSOURCE["source"] = $source;
    if ($source == 2) { // Source = correspondant bancaire
      $INFOSOURCE["correspondant"] = $correspondant;
      $INFOSOURCE["num_piece"] = $num_piece;
      $INFOSOURCE["id_ben"] = $id_ben;
    }
    $INFOSOURCE["communication"] = $communication;
    $INFOSOURCE["remarque"] = $remarque;
    $SESSION_VARS["SOURCE"] = $INFOSOURCE;
    $SESSION_VARS["type_dep"] = $type_dep;
    $SESSION_VARS["libel_ope"] = new Trad();
    $SESSION_VARS["libel_ope"] = serialize($libel_ope);
  }
  


  /* Récupération du pourcentage et du montant de la commission s'il s'agit de virement de salaires */
  if ($SESSION_VARS["type_dep"] == 2) {
    $AG = getAgenceDatas($global_id_agence);
    $pr_com_vir = $AG['prc_com_vir'] ;
    $mnt_com_vir = $AG['mnt_com_vir'];
  } else {
    $pr_com_vir = 0;
    $mnt_com_vir = 0;
  }
  $html = "<h1 align=\"center\">"._("Saisie par lot (dépôt sur compte )")."</h1><h2 align=\"center\">".$libel_ope->traduction()."</h2><br><br>\n";
  $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  //Ligne titre
  if ($SESSION_VARS["type_dep"] == 2)
    $html .="<TR bgcolor=$colb_tableau><TD><b>"._("n°")."</b></TD><TD align=\"center\"><b>"._("Date valeur")."</b></TD><TD align=\"center\"><b>"._("N° compte")."</b></TD><TD align=\"center\"><b>"._("Montant")."</b></TD><TD align=\"center\"><b>"._("Commission")."</b></TD><TD align=\"center\"><b>"._("Devise")."</b></TD><TD align=\"center\"><b>"._("N° reçu")."</b></TD></TR>\n";
  else
    $html .= "<TR bgcolor=$colb_tableau><TD><b>"._("n°")."</b></TD><TD align=\"center\"><b>"._("Date valeur")."</b></TD><TD align=\"center\"><b>"._("N° compte")."</b></TD><TD align=\"center\"><b>"._("Montant")."</b></TD><TD align=\"center\"><b>"._("Devise")."</b></TD><TD align=\"center\"><b>"._("N° reçu")."</b></TD></TR>\n";

  for ($i=1; $i <= 40; ++$i) {
    //On alterne la couleur de fond
    if ($i%2) $color = $colb_tableau;
    else $color = $colb_tableau_altern;

    //Recup valeurs par défaut
    if (isset($SESSION_VARS['saisie_lot'][$i]['date']))
      $date = $SESSION_VARS['saisie_lot'][$i]['date'];
    else
      $date = date("d/m/Y");

    if (isset($SESSION_VARS['saisie_lot'][$i]['mnt_com']))
      $mnt_com = $SESSION_VARS['saisie_lot'][$i]['mnt_com'];
    else {
      $mnt_com = $mnt_com_vir + ($ {'mnt'.$i} * $pr_com_vir);
      $mnt_com = afficheMontant($mnt_com,false);
    }

    $cpte_client = $SESSION_VARS['saisie_lot'][$i]['cpte_client'];
    $mnt = afficheMontant($SESSION_VARS['saisie_lot'][$i]['mnt'],false);
    $devise_cpte = $SESSION_VARS['saisie_lot'][$i]['devise'];
    $num_recu = $SESSION_VARS['saisie_lot'][$i]['num_recu'];


    $html .= "<TR bgcolor=$color>\n";

    //n°
    $html .= "<TD><b>$i</b></TD>";

    //Date valeur
    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"date$i\" size=10 value=\"$date\">\n"; //FIXME OL: NE MARCHERA PAS TEL QUEL EN MULTILINGUE !!!
    $html .= "<FONT size=\"2\"><A href=\"#\" onClick=\"if (! isDate(document.ADForm.date$i.value)) document.ADForm.date$i.value='';open_calendrier(getMonth(document.ADForm.date$i.value), getYear(document.ADForm.date$i.value), $calend_annee_passe, $calend_annee_futur, 'date$i');return false;\">"._("Calendrier")."</A></FONT></TD>";

    //COMPTE Client
    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=14 value=\"$cpte_client\">\n";
    $html .= "<FONT size=\"2\"><A href=# onclick=\"open_compte(".$i.");return false;\">"._("Recherche<")."/A></FONT></TD>\n";
    $html .="<INPUT TYPE=\"hidden\" NAME=\"num_id_cpte".$i."\"> </TD> \n";

    //Montant
    if ($SESSION_VARS["type_dep"] == 2) {
      $html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);document.ADForm.mnt_com$i.value=$mnt_com_vir+$pr_com_vir*recupMontant(value);\" size=12 value=\"$mnt\"></TD>\n";

      // Commission en cas de virement de salaires
      $html .= "<TD><INPUT NAME=\"mnt_com$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);\" size=12 value=\"$mnt_com\"></TD>\n";
    } else {
      $html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);\" size=12 value=\"$mnt\"></TD>\n";
    }

    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"devise$i\" size=3 disabled=\"true\" value=\"$devise_cpte\"></TD>\n";

    //N° reçu
    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"num_recu$i\" size=8 value=\"$num_recu\"></TD>\n";
    $html .= "</TR>\n";
  }

  $html .= "<TR bgcolor=$colb_tableau><TD colspan=5 align=\"center\">\n";

  //Boutons
  $html .= "<TABLE align=\"center\"><TR><TD><INPUT TYPE=\"submit\" VALUE=\""._("Valider")."\" onclick=\"ADFormValid = true; checkForm(); assign('Sgu-3');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour")."\" onclick=\"ADFormValid=true;assign('Sgu-1');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\""._("Annuler")."\" onclick=\"ADFormValid=true;assign('Gen-6');\"></TD></TR></TABLE>\n";
  $html .= "</TD></TR></TABLE>\n";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

  //Script checks
  $html .= "<script type=\"text/javascript\">\n";
  $html .= "function checkForm(){";
  $html .= "msg = '';\n";

  for ($i=1; $i<=40; ++$i) {
    $html .= "  if ((! isDate(document.ADForm.date$i.value)) && (document.ADForm.date$i.value != '')){ ADFormValid = false; msg += '".sprintf(_("La date de la ligne n°%s est incorrecte !"),$i)."\\n';}";
  }

  $html .= "if (msg != '') alert(msg);";
  $html .= "}";

  /* Debut JS : recherche du compte de depôt du client */
  $html .="";
  $html .="\nfunction open_compte( index )\n";
  $html .="{\n";
  $html .="\nurl = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=cpte_client' +index+ '&id_cpt_dest=num_id_cpte'+index+ '&devise_cpte_dest=devise'+index+'&is_depot=true';\n";
  $html .="\t\tgarant = OpenBrwXY(url, '"._("Compte de Dépôt")."', 400, 500);\n";
  $html .="}\n";
   /* Fin JS : recherche du compte de depôt du client */

  $html .= "</script>\n";

  echo $html;
}
}

/*}}}*/

/*{{{ Sgu-3 : Demande de confirmation des mouvements */
else if ($global_nom_ecran == "Sgu-3") {

  //Récupère les infos
  $total = array();
  $msg = "";
  $nums = array();
  $i = 1;
  while ($ {'cpte_client'.$i} != '') {
  	$count = 0;
    $have_date=false;

    //Recup montant
    if ($ {'mnt'.$i} != "") {
    	++$count;
    	$INFOMVTS[$i]['autre_libel_ope'] = unserialize($SESSION_VARS['libel_ope']);
      $INFOMVTS[$i]['mnt'] = arrondiMonnaie(recupMontant($ {'mnt'.$i}),0,$global_monnaie);
      $SESSION_VARS['saisie_lot'][$i]['mnt'] = arrondiMonnaie(recupMontant($ {'mnt'.$i}),0,$global_monnaie);

    } else {
    	$msg .=sprintf(_("Ligne N°%s: Le montant du dépôt doit-être renseigné "),$i)."<BR>";
    }

    //compte client
    $cpte_client = $ {'cpte_client'.$i};
    $INFOMVTS[$i]['cpte_client'] = $cpte_client;
    $SESSION_VARS['saisie_lot'][$i]['cpte_client'] = $cpte_client;
    ++$count;
    if (! isNumComplet($cpte_client)) {
    	//Vérifie si le compte  client existe
      $msg .=sprintf(_("Ligne N°%s: Le numéro de compte  client N°compte=%s  n'est pas valide !"),$i,$cpte_client)."<br />";

    } else {

    	$id_cpte_client= get_id_compte ($cpte_client);
    	$ACC = getAccountDatas($id_cpte_client);
    	$myErr=CheckDepot($ACC,$INFOMVTS[$i]['mnt']);
      if ($myErr->errCode != NO_ERR)
          $msg .=sprintf(_("Ligne %s:  "),$i). $error[$myErr->errCode].sprintf(_(" N°Compte= %s "),$cpte_client)."<br />";

    	$INFOMVTS[$i]['id_cpte'] = $id_cpte_client;
    	$INFOMVTS[$i]['num_client']=$ACC['id_titulaire'];
    	$INFOMVTS[$i]['devise']=$ACC['devise'];
    	$SESSION_VARS['saisie_lot'][$i]['devise']=$ACC['devise'];
    }
    //Recup date
    $INFOMVTS[$i]['date'] = $ {'date'.$i};
    $SESSION_VARS['saisie_lot'][$i]['date'] = $ {'date'.$i};
    $dummy = splitEuropeanDate($INFOMVTS[$i]['date']);
    if ($INFOMVTS[$i]['date'] != "") {
    	$have_date=true;
      if (gmmktime(0,0,0,$dummy[1],$dummy[0],$dummy[2]) >= gmmktime(0,0,0,date('m'),date('d')+1, date('Y')))
      	$msg .= sprintf(_("Ligne N°%s: La date %s se situe après la date du jour !"),$i,$INFOMVTS[$i]['date'])."<br />";
    }

    //Recup commissison
    if ($ {'mnt_com'.$i} != "") {
    	//++$count; // n'est pas obligatoire
      $INFOMVTS[$i]['mnt_com'] = arrondiMonnaie(recupMontant($ {'mnt_com'.$i}),0,$SESSION_VARS['saisie_lot'][$i]['devise']);
      $SESSION_VARS['saisie_lot'][$i]['mnt_com'] = arrondiMonnaie(recupMontant($ {'mnt_com'.$i}),0,$SESSION_VARS['saisie_lot'][$i]['devise']);;
    } else {
    	$INFOMVTS[$i]['mnt_com'] = 0;
      $SESSION_VARS['saisie_lot'][$i]['mnt_com'] = 0;
    }

    //Recup num_recu
    $INFOMVTS[$i]['num_recu'] = $ {'num_recu'.$i};
    $SESSION_VARS['saisie_lot'][$i]['num_recu'] = $ {'num_recu'.$i};
    if ($INFOMVTS[$i]['num_recu'] != "") {
    	if ((in_array($INFOMVTS[$i]['num_recu'], $nums)) || (exist_num_recu_lot($INFOMVTS[$i]['num_recu'])))  //Vérifie l'unicité du numéro de lot
      	$msg = sprintf(_("Ligne %s: Le numéro de reçu  a déjà été utilisé !"),$i)."<BR>";
      array_push($nums, $INFOMVTS[$i]['num_recu']);
      //++$count;
    }

    //Vérifie que tous les champs soient bien renseignés
    if ((($count != 2) || (! $have_date)) && ($count > 0)) {
    	$msg .= sprintf(_("Ligne %s: Certains champs  ne sont pas renseignés !"),$i)."<br />";
    } else {
    	//calcul somme des depot par rapport à chaque devise
      $total[$INFOMVTS[$i]['devise']] += $INFOMVTS[$i]['mnt'];
    }


    //Doit-on traiter cette entrée ?
    $INFOMVTS[$i]['traite'] = (($count == 2) && ($have_date));
    $i++;
  }

  if ($msg == "" && $i>1) {//Si tout OK
  	$html = new HTML_GEN2(_("Récapitulatif"));

    $table = new HTML_TABLE_table(6, TABLE_STYLE_ALTERN);
    $table->set_property("title",_("Liste des mouvements"));
    $table->add_cell(new TABLE_cell(_("N°")));
    $table->add_cell(new TABLE_cell(_("N° Compte")));
    $table->add_cell(new TABLE_cell(_("Nom Client")));
    $table->add_cell(new TABLE_cell(_("Montant")));
    $table->add_cell(new TABLE_cell(_("Commission")));
    $table->add_cell(new TABLE_cell(_("Devise")));

    while (list($i, $value) = each($INFOMVTS)) {
    	$table->add_cell(new TABLE_cell($i));
      $table->add_cell(new TABLE_cell( $value["cpte_client"]));
      $table->add_cell(new TABLE_cell(getClientName($value["num_client"])));
      $table->add_cell(new TABLE_cell(afficheMontant($value["mnt"], false)));
      $table->add_cell(new TABLE_cell(afficheMontant($value["mnt_com"], false)));
      $table->add_cell(new TABLE_cell($value["devise"]));
      $table->set_cell_property("align","right");
    }
    echo $table->gen_HTML();

    $table2 = new HTML_TABLE_table(2, TABLE_STYLE_CLASSIC);
    $table2->set_property("title",_("Origine des fonds"));
    $table2->set_property("border",$tableau_border);

    if ($SESSION_VARS["SOURCE"]["source"] == 1) {
      $table2->add_cell(new TABLE_cell(_("Source")));
      $table2->add_cell(new TABLE_cell(_("Guichet")));
      $table2->set_row_childs_property("align","left");
    } else {
      $infoTireur=getTireurBenefDatas($SESSION_VARS["SOURCE"]["id_ben"]);
      $libel_correspondant=getLibelCorrespondant($global_monnaie);
      $table2->add_cell(new TABLE_cell(_("Source")));
      $table2->add_cell(new TABLE_cell(_("Correspondant extérieur")));
      $table2->set_row_childs_property("align","left");
      $table2->add_cell(new TABLE_cell(_("Donneur d'ordre")));
      $table2->add_cell(new TABLE_cell($infoTireur["denomination"]));
      $table2->set_row_childs_property("align","left");
      $table2->add_cell(new TABLE_cell(_("Correspondant")));
      $table2->add_cell(new TABLE_cell($libel_correspondant[$SESSION_VARS["SOURCE"]["correspondant"]]));
      $table2->set_row_childs_property("align","left");
      $table2->add_cell(new TABLE_cell(_("Numéro de pièce")));
      $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["num_piece"]));
      $table2->set_row_childs_property("align","left");
      $table2->add_cell(new TABLE_cell(_("Communication")));
      $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["communication"]));
      $table2->set_row_childs_property("align","left");
      $table2->add_cell(new TABLE_cell(_("Remarque")));
      $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["remarque"]));
      $table2->set_row_childs_property("align","left");
    }
    echo $table2->gen_HTML();

    $MyPage = new HTML_message(_("Demande confirmation"));

    $msg_total .=_("La somme totale perçue par devise")."         :<br>";
    foreach ($total as $code=>$montant) {
    	setMonnaieCourante($code);
    	$msg_total .=sprintf(_(" Total  en (%s ) : %s"),$code,"<b>".afficheMontant($montant, true)."</b>").". <BR>";
    }
     $msg_total .="<br> <br>";
    $msg_total .=_("Voulez-vous continuer ?");
    $MyPage->setMessage($msg_total);
//   	$MyPage->setMessage(sprintf(_("La somme totale perçue pour ces %s opérations est de %s en (%s )"),$SESSION_VARS['nbre_ope'],"<b>".afficheMontant($montant, true)."</b>",$code).". "._("Voulez-vous continuer ?"));

    $MyPage->addButton(BUTTON_OUI, "Sgu-4");
    $MyPage->addButton(BUTTON_NON, "Sgu-2");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
    
    $SESSION_VARS["MVTS"] = serialize($INFOMVTS);

  } else { //Si erreur
    if ($i==1 ) {
    	$msg .=_("Aucune Données trouvées ");
    	unset($SESSION_VARS['saisie_lot']);
    }
    $MyPage = new HTML_erreur(_("Erreur saisie par lot"));
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Sgu-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }

}
/*}}}*/

/*{{{ Sgu-4 : Enregistrement des dépôts */
else if ($global_nom_ecran == "Sgu-4") {
  //appel DB
  $myErr = traite_lot(unserialize($SESSION_VARS["MVTS"]), $global_nom_login, $global_id_guichet,$SESSION_VARS['SOURCE']);

  if ($myErr->errCode == NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation saisie par lot"));
    $MyPage->setMessage(_("L'enregistrement du lot a été réalisé avec succès !"));
    $MyPage->addButton(BUTTON_OK, "Gen-6");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;

    $table = new HTML_TABLE_table(7, TABLE_STYLE_ALTERN);
    $table->set_property("title",_("Liste numéros de transaction"));
    $table->add_cell(new TABLE_cell(_("N° transaction")));
    $table->add_cell(new TABLE_cell(_("N° reçu")));
    $table->add_cell(new TABLE_cell(_("N° N°Compte")));
    $table->add_cell(new TABLE_cell(_("Nom Client")));
    $table->add_cell(new TABLE_cell(_("Montant")));
    $table->add_cell(new TABLE_cell(_("Commission")));
     $table->add_cell(new TABLE_cell(_("Devise")));

    while (list($id_client, $value) = each($myErr->param["transaction_data"])) {

        $table->add_cell(new TABLE_cell(sprintf("%09d", $value["id_his"])));
        $table->add_cell(new TABLE_cell($value["num_recu"]));
        $table->add_cell(new TABLE_cell( $value["cpte_client"]));
        $table->add_cell(new TABLE_cell(getClientName($value["id_client"])));
        $table->add_cell(new TABLE_cell(afficheMontant($value["mnt"], false)));
        $table->add_cell(new TABLE_cell(afficheMontant($value["mnt_com"], false)));
        $table->add_cell(new TABLE_cell( $value["devise"]));
        $table->set_cell_property("align","right");

        if(isMobileLending()) {

            $value["montant_remboursement"] -= getPreleveFraisSMS($myErr->param["comptable"][$id_client]);
            $data_demande_mb = getDemandeCredit($id_client, " statut_demande IN (2, 5) ORDER BY id_doss ASC limit 1");
            if (sizeof($data_demande_mb) > 0) {
                $prod_info_cdt = getProdInfoByID($data_demande_mb[0]['id_prod']);
                if($prod_info_cdt[$data_demande_mb[0]['id_prod']]['crdt_salairie'] == 't') {
                    $rembourse_erreur = rembourseMontantDepot($id_client, $value["montant_remboursement"]);

                    if ($rembourse_erreur->errCode != NO_ERR) {
                        $html_err = new HTML_erreur(_("Echec du remboursement."));
                        $html_err->setMessage(_("Erreur") . " : " . $error[$rembourse_erreur->errCode] . "<br/>" . _("Paramètre") . " : " . $rembourse_erreur->param);
                        $html_err->addButton("BUTTON_OK", 'Sgu-1');
                        $html_err->buildHTML();
                        echo $html_err->HTML_code;
                        exit();
                    }
                }
            }
        }
    }
    //liberez le tableau
    unset($SESSION_VARS['saisie_lot']);
    echo $table->gen_HTML();
  } else {
    $html_err = new HTML_erreur(_("Echec de la saisie par lot.")." ");
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-6');
    $html_err->buildHTML();
    echo $html_err->HTML_code;

  }

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>