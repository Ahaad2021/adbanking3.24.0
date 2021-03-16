<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [154] Retrait par lot
 * Cette opération comprends les écrans :
 * - Rgu-1 : Type de retrait
 * - Rgu-2 : Saisie des retraits
 * - Rgu-3 : Demande de confirmation des mouvements
 * - Rgu-4 : Enregistrement des retraits
 * @package Guichet
 */
require_once 'lib/dbProcedures/guichet.php';

/*{{{ Rgu-1 : Type de retrait */
if ($global_nom_ecran == "Rgu-1") {

  $global_nom_ecran_prec == "";
  unset($INFOSOURCE);
  unset($SESSION_VARS);

  // tabeau pour la sauvegarde de la saisie
  $SESSION_VARS['retrait_par_lot'] = array();
  $msg = "";
  $html = new HTML_GEN2(_("Retrait par lot"));
  $type_retrait = array(1 => _("Retrait client"), 2 => _("Paiement de crédit"), 3 => _("Approvisionnement carte"));
  $html->addField("type_ret", _("Type de retrait"), TYPC_LSB);
  $html->setFieldProperties("type_ret", FIELDP_ADD_CHOICES, $type_retrait);
  $html->setFieldProperties("type_ret", FIELDP_IS_REQUIRED, true);

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
  $html->addField("dest_fond", _("La destination des fonds"), TYPC_LSB);
  $html->setFieldProperties("dest_fond", FIELDP_ADD_CHOICES, $choices);
  $html->setFieldProperties("dest_fond", FIELDP_IS_REQUIRED, true);

  $html->addTable("ad_his_ext", OPER_INCLUDE, array("communication","remarque"));
  //Informations tireur (seulement dans le cas du chèque)
  $html->addField("nom_ben", _("Nom du tireur"), TYPC_TXT);
  $html->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
  $html->addHiddenType("id_ben","");

  $html->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
  $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=b', '"._("Recherche")."');return false;"));

  //Correspondant bancaire (seulement ceux en devise de référence)
  $libel_correspondant=getLibelCorrespondant();
  $html->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
  $html->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);

  $type_piece_choices = array(3 => $adsys["adsys_type_piece_payement"][3], 13 => $adsys["adsys_type_piece_payement"][13], 15 => $adsys["adsys_type_piece_payement"][15]);
  $html->addField("type_piece", "La pièce justificative", TYPC_LSB);
  $html->setFieldProperties("type_piece", FIELDP_ADD_CHOICES, $type_piece_choices);
  $html->setFieldProperties("type_piece", FIELDP_IS_REQUIRED, true);

  $html->addField("date_piece", "Date pièce <span id='ValiddateChq' style='display:none'><font color='#FF0000' face='HELVETICA' size='4'><b>*</b></font></span>", TYPC_DTE);

  $html->addField("num_piece", "Référence pièce (Numéro)", TYPC_TXT);

  $html->addField("nb_ope", "Nombre d’opérations", TYPC_TXT);
  $html->setFieldProperties("nb_ope", FIELDP_DEFAULT, 40);

  //ordonner les champs pour l'affichage
  $order = array("type_ret", "libel_ope_def", "libel_ope", "dest_fond", "correspondant", "nom_ben", "type_piece", "date_piece", "num_piece", "nb_ope", "communication", "remarque");
  $html->setOrder(NULL, $order);

  // transformer les champs en labels non modifiables
  $labels = array_diff($order, array("type_ret", "libel_ope_def", "libel_ope", "dest_fond", "date_piece", "nb_ope", "communication", "remarque"));
  foreach ($labels as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  // En fonction du type de retrait activer ou désactiver la destination des fonds
  $active_dest_fond = "
                   function activateDestFond()
                 {
                   if (document.ADForm.HTML_GEN_LSB_type_ret.value == 0)
                 {
                   document.ADForm.HTML_GEN_LSB_dest_fond.disabled = true;
                   document.ADForm.HTML_GEN_LSB_dest_fond.value = 0;
                   document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
                   document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
                   document.ADForm.HTML_GEN_LSB_type_piece.disabled = true;
                   document.ADForm.HTML_GEN_LSB_type_piece.value = 0;
                   document.ADForm.HTML_GEN_date_date_piece.disabled = true;
                   document.ADForm.HTML_GEN_date_date_piece.value = '';
                   document.ADForm.num_piece.disabled = true;
                   document.ADForm.num_piece.value = '';
                 }
                 else if (document.ADForm.HTML_GEN_LSB_type_ret.value == 1)
                 {
                   document.ADForm.HTML_GEN_LSB_dest_fond.disabled = true;
                   document.ADForm.HTML_GEN_LSB_dest_fond.value = 1;
                   document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
                   document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
                   document.ADForm.HTML_GEN_LSB_type_piece.disabled = true;
                   document.ADForm.HTML_GEN_LSB_type_piece.value = 0;
                   document.ADForm.HTML_GEN_date_date_piece.disabled = true;
                   document.ADForm.HTML_GEN_date_date_piece.value = '';
                   document.ADForm.num_piece.disabled = true;
                   document.ADForm.num_piece.value = '';
                 }
                 else if (document.ADForm.HTML_GEN_LSB_type_ret.value == 3){
                   document.ADForm.HTML_GEN_LSB_dest_fond.disabled = true;
                   document.ADForm.HTML_GEN_LSB_dest_fond.value = 2;
                   document.ADForm.HTML_GEN_LSB_correspondant.disabled = false;
                   document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
                   document.ADForm.HTML_GEN_LSB_type_piece.disabled = false;
                   document.ADForm.HTML_GEN_LSB_type_piece.value = 0;
                   document.ADForm.HTML_GEN_date_date_piece.disabled = false;
                   document.ADForm.HTML_GEN_date_date_piece.value = '';
                   document.ADForm.num_piece.disabled = false;
                   document.ADForm.num_piece.value = '';
                   document.ADForm.nb_ope.disabled = true;
                   document.ADForm.nb_ope.value = '';

                 }
                   else
                 {
                   document.ADForm.HTML_GEN_LSB_dest_fond.value = 0;
                   document.ADForm.HTML_GEN_LSB_dest_fond.disabled = false;
                 }

                 }";
  $html->addJS(JSP_FORM, "JS_type_ret", $active_dest_fond);
  $html->setFieldProperties("type_ret", FIELDP_JS_EVENT, array("onchange" => "activateDestFond()"));

  //en fonction du choix du compte, afficher les infos avec le onChange javascript
  $codejs = "
            function activateFields()
          {
            if (document.ADForm.HTML_GEN_LSB_dest_fond.value == 0)
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
            document.ADForm.HTML_GEN_LSB_type_piece.disabled = true;
            document.ADForm.HTML_GEN_date_date_piece.disabled = true;
            document.ADForm.num_piece.disabled = true;
            document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
            document.ADForm.HTML_GEN_LSB_type_piece.value = 0;
            document.ADForm.HTML_GEN_date_date_piece.value = '';
            document.ADForm.num_piece.value = '';
          }
            else if (document.ADForm.HTML_GEN_LSB_dest_fond.value == 1)
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
            document.ADForm.HTML_GEN_LSB_type_piece.disabled = true;
            document.ADForm.HTML_GEN_date_date_piece.disabled = true;
            document.ADForm.num_piece.disabled = true;
            document.ADForm.HTML_GEN_LSB_correspondant.value = 0;
            document.ADForm.HTML_GEN_LSB_type_piece.value = 0;
            document.ADForm.HTML_GEN_date_date_piece.value = '';
            document.ADForm.num_piece.value = '';
          }
          else if (document.ADForm.HTML_GEN_LSB_dest_fond.value == 2)
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = false;
            document.ADForm.HTML_GEN_LSB_type_piece.disabled = false;
            document.ADForm.HTML_GEN_date_date_piece.disabled = false;
            document.ADForm.num_piece.disabled = false;
          }
            else
          {
            document.ADForm.HTML_GEN_LSB_correspondant.disabled = false;
            document.ADForm.HTML_GEN_LSB_type_piece.disabled = false;
            document.ADForm.HTML_GEN_date_date_piece.disabled = false;
            document.ADForm.num_piece.disabled = false;
          }
          }activateFields();";

  $html->addJS(JSP_FORM, "JS3", $codejs);
  $html->setFieldProperties("dest_fond", FIELDP_JS_EVENT, array("onchange" => "activateFields()"));

  // Checkform
  $jscheck = "if (document.ADForm.HTML_GEN_LSB_dest_fond.value == 2)
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
             if (document.ADForm.HTML_GEN_LSB_type_piece.value == 0)
           {
             msg += '- "._("La pièce justificative doit etre renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.HTML_GEN_date_date_piece.value == '')
           {
             msg += '- "._("La date pièce doit etre renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.num_piece.value == '')
           {
             msg += '- "._("Le champ numéro de pièce doit etre renseigné")."\\n';
             ADFormValid = false;
           }
           }
           if (document.ADForm.HTML_GEN_LSB_type_ret. value == 2){
            if (document.ADForm.HTML_GEN_LSB_dest_fond.value == 3){
             msg += '- "._("Cette destination ne peut etre utiliser pour ce type de retrait")."\\n';
             ADFormValid = false;
            }
           }

           ";
  $html->addJS(JSP_BEGIN_CHECK, "jscheck", $jscheck);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rgu-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Rgu-2 : Saisie des retraits */
else if ($global_nom_ecran == "Rgu-2") {

    //require_once ('lib/misc/debug.php');
    //print_rn($_POST);

    if (isset($nb_ope) && ($nb_ope >=40 || $nb_ope <= 500)) {
      $nb_ope_count = $nb_ope;
    }
    elseif (isset($SESSION_VARS["SOURCE"]["nb_ope_count"])) {
      $nb_ope_count = trim($SESSION_VARS["SOURCE"]["nb_ope_count"]);
    }
    else {
      $nb_ope_count = 40;
    }

    //$nb_ope_count = ((isset($nb_ope) && ($nb_ope >=40 || $nb_ope <= 500)) ? $nb_ope : ((isset($SESSION_VARS["SOURCE"]["nb_ope_count"])) ? trim($SESSION_VARS["SOURCE"]["nb_ope_count"]) : 40));

    //echo 'nb_ope_count = '.$nb_ope_count.'<br/>';

  // Sauvegarde des valeurs provenant du ecran precedent Rgu-3_KG
	if ($global_nom_ecran_prec == "Rgu-3") {// traitement retour d'ecran anticipé_Bug ecran blanche404
		//$SESSION_VARS ['retrait_par_lot'];

		//$SESSION_VARS["SOURCE"];
		
		/* Récupération du pourcentage et du montant de la commission s'il s'agit de virement de salaires */
		if ($SESSION_VARS ["type_ret"] == 2) {
			$AG = getAgenceDatas ( $global_id_agence );
			$pr_com_vir = $AG ['prc_com_vir'];
			$mnt_com_vir = $AG ['mnt_com_vir'];
		} else {
			$pr_com_vir = 0;
			$mnt_com_vir = 0;
		}
		
		$html = "<h1 align=\"center\">" . _ ( "Saisie par lot (retrait sur compte)" ) . "</h1><h2 align=\"center\">" . $libel_operation_entet . "</h2><br><br>\n";
		$html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
		$html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
		// Ligne titre: Depot lot
		if ($SESSION_VARS ["type_ret"] == 2)
			$html .= "<TR bgcolor=$colb_tableau><TD><b>" . _ ( "n°" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Date valeur" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° compte" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Montant" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Commission" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Devise" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° reçu" ) . "</b></TD></TR>\n";
		else // virement salaire
			$html .= "<TR bgcolor=$colb_tableau><TD><b>" . _ ( "n°" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Date valeur" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° compte" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Montant" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "Devise" ) . "</b></TD><TD align=\"center\"><b>" . _ ( "N° reçu" ) . "</b></TD></TR>\n";

		for($i = 1; $i <= $nb_ope_count; ++ $i) {
			// On alterne la couleur de fond
			if ($i % 2)
				$color = $colb_tableau;
			else
				$color = $colb_tableau_altern;
				
				// Recup valeurs par défaut
			if (isset ( $SESSION_VARS ['retrait_par_lot'] [$i] ['date'] ))
				$date = $SESSION_VARS ['retrait_par_lot'] [$i] ['date'];
			else
				$date = date ( "d/m/Y" );
			
			if (isset ( $SESSION_VARS ['retrait_par_lot'] [$i] ['mnt_com'] ))
				$mnt_com = $SESSION_VARS ['retrait_par_lot'] [$i] ['mnt_com'];
			else {
				$mnt_com = $mnt_com_vir + ($ {'mnt' . $i} * $pr_com_vir);
				$mnt_com = afficheMontant ( $mnt_com, false );
			}
			
			$cpte_client = $SESSION_VARS ['retrait_par_lot'] [$i] ['cpte_client'];
			$mnt = afficheMontant ( $SESSION_VARS ['retrait_par_lot'] [$i] ['mnt'], false );
			$devise_cpte = $SESSION_VARS ['retrait_par_lot'] [$i] ['devise'];
			$num_recu = $SESSION_VARS ['retrait_par_lot'] [$i] ['num_recu'];
			
			$html .= "<TR bgcolor=$color>\n";
			
			// n°
			$html .= "<TD><b>$i</b></TD>";
			
			// Date valeur
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"date$i\" size=10 value=" . $SESSION_VARS ['retrait_par_lot'] [$i] ['date'] . ">\n"; // FIXME OL: NE MARCHERA PAS TEL QUEL EN MULTILINGUE !!!
			$html .= "<FONT size=\"2\"><A href=\"#\" onClick=\"if (! isDate(document.ADForm.date$i.value)) document.ADForm.date$i.value='';open_calendrier(getMonth(document.ADForm.date$i.value), getYear(document.ADForm.date$i.value), $calend_annee_passe, $calend_annee_futur, 'date$i');return false;\">" . _ ( "Calendrier" ) . "</A></FONT></TD>";
			
			// COMPTE Client
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=14 value=" . $SESSION_VARS ['retrait_par_lot'] [$i] ['cpte_client'] . ">\n";
			$html .= "<FONT size=\"2\"><A href=# onclick=\"open_compte(" . $i . ");return false;\">" . _ ( "Recherche<" ) . "/A></FONT></TD>\n";
			$html .= "<INPUT TYPE=\"hidden\" NAME=\"num_id_cpte" . $i . "\"> </TD> \n";
			
			// Montant
			if ($SESSION_VARS ["type_ret"] == 2) {
			                                      // $html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);document.ADForm.mnt_com$i.value=$mnt_com_vir+$pr_com_vir*recupMontant(value);\" size=12 value=\"$mnt\"></TD>\n";
				$html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);document.ADForm.mnt_com$i.value=$mnt_com_vir+$pr_com_vir*recupMontant(value);\" size=12 value=".$SESSION_VARS['retrait_par_lot'][$i]['mnt']."></TD>\n";
				
				// Commission en cas de virement de salaires
				$html .= "<TD><INPUT NAME=\"mnt_com$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);\" size=12 value=" . $SESSION_VARS ['retrait_par_lot'] [$i] ['mnt_com'] . "></TD>\n";
			} else {	
				$html .= "<TD><INPUT NAME=\"mnt$i\" TYPE=\"text\" onchange=\"value = formateMontant(value);\" size=12 value=" . $SESSION_VARS ['retrait_par_lot'] [$i] ['mnt'] . "></TD>\n";
			}
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"devise$i\" size=3 disabled=\"true\" value=" . $SESSION_VARS ['retrait_par_lot'] [$i] ['devise'] . "></TD>\n";
			
			// N° reçu
			$html .= "<TD><INPUT TYPE=\"text\" NAME=\"num_recu$i\" size=8 value=" . $SESSION_VARS ['retrait_par_lot'] [$i] ['num_recu'] . "></TD>\n";
			$html .= "</TR>\n";
		}
		
		$html .= "<TR bgcolor=$colb_tableau><TD colspan=5 align=\"center\">\n";
		
		// Boutons
		$html .= "<TABLE align=\"center\"><TR><TD><INPUT TYPE=\"submit\" VALUE=\"" . _ ( "Valider" ) . "\" onclick=\"ADFormValid = true; checkForm(); assign('Rgu-3');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"" . _ ( "Retour" ) . "\" onclick=\"ADFormValid=true;assign('Rgu-1');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"" . _ ( "Annuler" ) . "\" onclick=\"ADFormValid=true;assign('Gen-6');\"></TD></TR></TABLE>\n";
		$html .= "</TD></TR></TABLE>\n";
		$html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";
		
		// Script checks
		$html .= "<script type=\"text/javascript\">\n";
		$html .= "function checkForm(){";
		$html .= "msg = '';\n";
		
		for($i = 1; $i <= $nb_ope_count; ++ $i) {
			$html .= "  if ((! isDate(document.ADForm.date$i.value)) && (document.ADForm.date$i.value != '')){ ADFormValid = false; msg += '" . sprintf ( _ ( "La date de la ligne n°%s est incorrecte !" ), $i ) . "\\n';}";
		}
		
		$html .= "if (msg != '') alert(msg);";
		$html .= "}";
		
		/* Debut JS : recherche du compte de depôt du client */
		$html .= "";
		$html .= "\nfunction open_compte( index )\n";
		$html .= "{\n";
		$html .= "\nurl = '" . $http_prefix . "/modules/clients/rech_client.php?m_agc=" . $_REQUEST ['m_agc'] . "&choixCompte=1&cpt_dest=cpte_client' +index+ '&id_cpt_dest=num_id_cpte'+index+ '&devise_cpte_dest=devise'+index+'&is_depot=true';\n";
		$html .= "\t\tgarant = OpenBrwXY(url, '" . _ ( "Compte de Retrait" ) . "', 400, 500);\n";
		$html .= "}\n";
		/* Fin JS : recherche du compte de depôt du client */
		
		$html .= "</script>\n";
		
		echo $html;
	} 	

	// Cette parti traitment initial_Existant_
	else {

    // MAE- 30: Upload excel pour la carte UBA
    if ( $type_ret == 3 || $SESSION_VARS["type_ret"] == 3) {
      if ($type_ret == 3 ) {
        $INFOSOURCE = array();
        $INFOSOURCE["type_ret"] = 3;
        $INFOSOURCE["dest_fond"] = 2;
        $INFOSOURCE["correspondant"] = $correspondant;
        $INFOSOURCE["type_piece"] = $type_piece;
        $INFOSOURCE["date_piece"] = $date_piece;
        $INFOSOURCE["num_piece"] = $num_piece;
        $INFOSOURCE["id_ben"] = $id_ben;
        $INFOSOURCE["communication"] = $communication;
        $INFOSOURCE["remarque"] = $remarque;
        $SESSION_VARS["SOURCE_UBA"] = $INFOSOURCE;
      }
      if (!isset($SESSION_VARS["type_ret"])) {
        $SESSION_VARS["type_ret"] = $type_ret;
      }
      if ($global_nom_ecran_prec == "Rgu-1") {
        $SESSION_VARS["libel_ope"] = new Trad();
        $SESSION_VARS["libel_ope"] = serialize($libel_ope);
      }





      if (file_exists($fichier_lot)) {
        $filename = $fichier_lot.".tmp";
        move_uploaded_file($fichier_lot, $filename);
        exec("chmod a+r ".escapeshellarg($filename));
        $SESSION_VARS['fichier_lot'] = $filename;
      } else {
        $SESSION_VARS['fichier_lot'] = NULL;
      }
      $libel_ope = unserialize($SESSION_VARS["libel_ope"]);
      $MyPage = new HTML_GEN2(_("Récupération du fichier de données"));
      $htm1 = "<h2 align=\"center\">".$libel_ope->traduction()."</h2><br>\n";
      $htm1 .= "<P align=\"center\">"._("Fichier de données").": <INPUT name=\"fichier_lot\" type=\"file\" /></P>";
      $htm1 .= "<P align=\"center\"> <INPUT type=\"submit\" value=\"Envoyer\" onclick=\"document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Rgu-2';\"/> </P>";
      $htm1 .= "<br />";

      $MyPage->addHTMLExtraCode("htm1", $htm1);

      $MyPage->AddField("statut", _("Statut"), TYPC_TXT);
      $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

      if ($SESSION_VARS['fichier_lot'] == NULL) {
        $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier non reçu"));
      } else {
        $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier reçu"));
      }

      $MyPage->addHTMLExtraCode("htm2", "<br />");

      $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
      $MyPage->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
      $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

      $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Rgu-3');
      $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Rgu-1');
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');

      $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

      $MyPage->buildHTML();
      echo $MyPage->getHTML();

    }
    else {
      if (isset($type_ret)) {
        // Les retraits de clients se font via la caisse
        if ($type_ret == 1)
          $dest_fond = 1;

        $INFOSOURCE = array();
        $INFOSOURCE["dest_fond"] = $dest_fond;
        $INFOSOURCE["nb_ope_count"] = $nb_ope_count;
        if ($dest_fond == 2) { // Source = correspondant bancaire
          $INFOSOURCE["correspondant"] = $correspondant;
          $INFOSOURCE["type_piece"] = $type_piece;
          $INFOSOURCE["date_piece"] = $date_piece;
          $INFOSOURCE["num_piece"] = $num_piece;
          $INFOSOURCE["id_ben"] = $id_ben;
        }
        $INFOSOURCE["communication"] = $communication;
        $INFOSOURCE["remarque"] = $remarque;
        $SESSION_VARS["SOURCE"] = $INFOSOURCE;
        $SESSION_VARS["type_ret"] = $type_ret;
        $SESSION_VARS["libel_ope"] = new Trad();
        $SESSION_VARS["libel_ope"] = serialize($libel_ope);
      }


      /* Récupération du pourcentage et du montant de la commission s'il s'agit de virement de salaires */
      if ($SESSION_VARS["type_ret"] == 2) {
        $AG = getAgenceDatas($global_id_agence);
        $pr_com_vir = $AG['prc_com_vir'];
        $mnt_com_vir = $AG['mnt_com_vir'];
      } else {
        $pr_com_vir = 0;
        $mnt_com_vir = 0;
      }
      $html = "<h1 align=\"center\">" . _("Saisie par lot (retrait sur compte )") . "</h1><h2 align=\"center\">" . $libel_ope->traduction() . "</h2><br><br>\n";
      $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
      $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
      //Ligne titre
      if ($SESSION_VARS["type_ret"] == 2)
        $html .= "<TR bgcolor=$colb_tableau><TD><b>" . _("n°") . "</b></TD><TD align=\"center\"><b>" . _("Date valeur") . "</b></TD><TD align=\"center\"><b>" . _("N° compte") . "</b></TD><TD align=\"center\"><b>" . _("Montant") . "</b></TD><TD align=\"center\"><b>" . _("Commission") . "</b></TD><TD align=\"center\"><b>" . _("Devise") . "</b></TD><TD align=\"center\"><b>" . _("N° reçu") . "</b></TD></TR>\n";
      else
        $html .= "<TR bgcolor=$colb_tableau><TD><b>" . _("n°") . "</b></TD><TD align=\"center\"><b>" . _("Date valeur") . "</b></TD><TD align=\"center\"><b>" . _("N° compte") . "</b></TD><TD align=\"center\"><b>" . _("Montant") . "</b></TD><TD align=\"center\"><b>" . _("Devise") . "</b></TD><TD align=\"center\"><b>" . _("N° reçu") . "</b></TD></TR>\n";

      for ($i = 1; $i <= $nb_ope_count; ++$i) {
        //On alterne la couleur de fond
        if ($i % 2) $color = $colb_tableau;
        else $color = $colb_tableau_altern;

        //Recup valeurs par défaut
        if (isset($SESSION_VARS['retrait_par_lot'][$i]['date']))
          $date = $SESSION_VARS['retrait_par_lot'][$i]['date'];
        else
          $date = date("d/m/Y");

        if (isset($SESSION_VARS['retrait_par_lot'][$i]['mnt_com']))
          $mnt_com = $SESSION_VARS['retrait_par_lot'][$i]['mnt_com'];
        else {
          $mnt_com = $mnt_com_vir + (${'mnt' . $i} * $pr_com_vir);
          $mnt_com = afficheMontant($mnt_com, false);
        }

        $cpte_client = $SESSION_VARS['retrait_par_lot'][$i]['cpte_client'];
        $mnt = afficheMontant($SESSION_VARS['retrait_par_lot'][$i]['mnt'], false);
        $devise_cpte = $SESSION_VARS['retrait_par_lot'][$i]['devise'];
        $num_recu = $SESSION_VARS['retrait_par_lot'][$i]['num_recu'];


        $html .= "<TR bgcolor=$color>\n";

        //n°
        $html .= "<TD><b>$i</b></TD>";

        //Date valeur
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"date$i\" size=10 value=\"$date\">\n"; //FIXME OL: NE MARCHERA PAS TEL QUEL EN MULTILINGUE !!!
        $html .= "<FONT size=\"2\"><A href=\"#\" onClick=\"if (! isDate(document.ADForm.date$i.value)) document.ADForm.date$i.value='';open_calendrier(getMonth(document.ADForm.date$i.value), getYear(document.ADForm.date$i.value), $calend_annee_passe, $calend_annee_futur, 'date$i');return false;\">" . _("Calendrier") . "</A></FONT></TD>";

        //COMPTE Client
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=14 value=\"$cpte_client\">\n";
        $html .= "<FONT size=\"2\"><A href=# onclick=\"open_compte(" . $i . ");return false;\">" . _("Recherche<") . "/A></FONT></TD>\n";
        $html .= "<INPUT TYPE=\"hidden\" NAME=\"num_id_cpte" . $i . "\"> </TD> \n";

        //Montant
        if ($SESSION_VARS["type_ret"] == 2) {
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

      $html .= "<TR bgcolor=$colb_tableau><TD colspan=6 align=\"center\">\n";

      //Boutons
      $html .= "<TABLE align=\"center\"><TR><TD><INPUT TYPE=\"submit\" VALUE=\"" . _("Valider") . "\" onclick=\"ADFormValid = true; checkForm(); assign('Rgu-3');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"" . _("Retour") . "\" onclick=\"ADFormValid=true;assign('Rgu-1');\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"" . _("Annuler") . "\" onclick=\"ADFormValid=true;assign('Gen-6');\"></TD></TR></TABLE>\n";
      $html .= "</TD></TR></TABLE>\n";
      $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

      //Script checks
      $html .= "<script type=\"text/javascript\">\n";
      $html .= "function checkForm(){";
      $html .= "msg = '';\n";

      for ($i = 1; $i <= $nb_ope_count; ++$i) {
        $html .= "  if ((! isDate(document.ADForm.date$i.value)) && (document.ADForm.date$i.value != '')){ ADFormValid = false; msg += '" . sprintf(_("La date de la ligne n°%s est incorrecte !"), $i) . "\\n';}";
      }

      $html .= "if (msg != '') alert(msg);";
      $html .= "}";

      /* Debut JS : recherche du compte de depôt du client */
      $html .= "";
      $html .= "\nfunction open_compte( index )\n";
      $html .= "{\n";
      $html .= "\nurl = '" . $http_prefix . "/modules/clients/rech_client.php?m_agc=" . $_REQUEST['m_agc'] . "&choixCompte=1&cpt_dest=cpte_client' +index+ '&id_cpt_dest=num_id_cpte'+index+ '&devise_cpte_dest=devise'+index+'&is_depot=true';\n";
      $html .= "\t\tgarant = OpenBrwXY(url, '" . _("Compte de Retrait") . "', 400, 500);\n";
      $html .= "}\n";
      /* Fin JS : recherche du compte de depôt du client */

      $html .= "</script>\n";

      echo $html;
    }
  }
}

/*}}}*/

/*{{{ Rgu-3 : Demande de confirmation des mouvements */
else if ($global_nom_ecran == "Rgu-3") {

  // MAE-30 : recuperation du fichier de donnees
  if ($SESSION_VARS["type_ret"] == 3) {
    $MyErr = parse_fichier_lot_carte_uba($SESSION_VARS['fichier_lot']);
    if ($MyErr->errCode == NO_ERR) {
      $param = $MyErr->param;
      $DATA_REJECT = array();
      $DATA_ACCEPTED = array();
      $total_mnt_accept = 0;
      $total_mnt_reject = 0;
      $count_acc = 0;
      foreach ($param['data'] as $key => $value){
        $InfoCpte = getAccountDatas($value['numero_cpte']);
        $InfoProduit = getProdEpargne(7);
        $checkCompte = CheckRetrait($InfoCpte, $InfoProduit, $value['montant'], null, null, false);
        if ($checkCompte->errCode == NO_ERR) {
          $dbHandler->closeConnection(false);
          $solde_dispo = getSoldeDisponible($value['numero_cpte']);
          $cpte_client = getAccountDatas($value['numero_cpte']);
          if ($solde_dispo < $value['montant']){
            $DATA_REJECT[$key]['numero_carte'] = $value['numero_carte'];
            $DATA_REJECT[$key]['id_cpte'] = $value['numero_cpte'];
            $DATA_REJECT[$key]['mnt'] = $value['montant'];
            $DATA_REJECT[$key]['num_client'] = $value['id_client'];
            $DATA_REJECT[$key]['cpte_client'] = $cpte_client['num_complet_cpte'];
            $DATA_REJECT[$key]['devise'] = $global_monnaie;
            $DATA_REJECT[$key]['date'] =date("d/m/Y");
            $DATA_REJECT[$key]['traite'] = 't';
            $total_mnt_reject += $value['montant'];
          }
          else{
            $count_acc ++;
            $DATA_ACCEPTED[$key]['autre_libel_ope'] = unserialize($SESSION_VARS['libel_ope']);
            $DATA_ACCEPTED[$key]['numero_carte'] = $value['numero_carte'];
            $DATA_ACCEPTED[$key]['id_cpte'] = $value['numero_cpte'];
            $DATA_ACCEPTED[$key]['mnt'] = $value['montant'];
            $DATA_ACCEPTED[$key]['num_client'] = $value['id_client'];
            $DATA_ACCEPTED[$key]['cpte_client'] = $cpte_client['num_complet_cpte'];
            $DATA_ACCEPTED[$key]['devise'] = $global_monnaie;
            $DATA_ACCEPTED[$key]['date'] =date("d/m/Y");
            $DATA_ACCEPTED[$key]['traite'] = 't';
            $total_mnt_accept += $value['montant'];
          }
        }
      }

    }$SESSION_VARS['data_rejected'] = $DATA_REJECT;
    $SESSION_VARS['nb_ope_count'] =$count_acc;

    $MyPage = new HTML_message(_("Demande confirmation"));

    $msg_total .= _("La somme totale perçue") . "         :<br>";
    $msg_total .= sprintf(_(" Total : %s"), "<b>" . afficheMontant($total_mnt_accept, true) . "</b>") . ". <BR>";
    $msg_total .= "<br> <br>";
    $msg_total .= _("Voulez-vous continuer ?");
    $MyPage->setMessage($msg_total);
//   	$MyPage->setMessage(sprintf(_("La somme totale perçue pour ces %s opérations est de %s en (%s )"),$SESSION_VARS['nbre_ope'],"<b>".afficheMontant($montant, true)."</b>",$code).". "._("Voulez-vous continuer ?"));

    $MyPage->addButton(BUTTON_OUI, "Rgu-4");
    $MyPage->addButton(BUTTON_NON, "Rgu-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
    $SESSION_VARS["MVTS"] = serialize($DATA_ACCEPTED);
  }
  else {
    //Récupère les infos
    $total = array();
    $msg = "";
    $nums = array();
    $i = 1;
    while (${'cpte_client' . $i} != '') {
      $count = 0;
      $have_date = false;

      //Recup montant
      if (${'mnt' . $i} != "") {
        ++$count;
        $INFOMVTS[$i]['autre_libel_ope'] = unserialize($SESSION_VARS['libel_ope']);
        $INFOMVTS[$i]['mnt'] = arrondiMonnaie(recupMontant(${'mnt' . $i}), 0, $global_monnaie);
        $SESSION_VARS['retrait_par_lot'][$i]['mnt'] = arrondiMonnaie(recupMontant(${'mnt' . $i}), 0, $global_monnaie);

      } else {
        $msg .= sprintf(_("Ligne N°%s: Le montant du retrait doit-être renseigné "), $i) . "<BR>";
      }

      //compte client
      $cpte_client = ${'cpte_client' . $i};
      $INFOMVTS[$i]['cpte_client'] = $cpte_client;
      $SESSION_VARS['retrait_par_lot'][$i]['cpte_client'] = $cpte_client;
      ++$count;
      if (!isNumComplet($cpte_client)) {
        //Vérifie si le compte  client existe
        $msg .= sprintf(_("Ligne N°%s: Le numéro de compte  client N°compte=%s  n'est pas valide !"), $i, $cpte_client) . "<br />";

      } else {

        $id_cpte_client = get_id_compte($cpte_client);
        $ACC = getAccountDatas($id_cpte_client);
        $myErr = CheckDepot($ACC, $INFOMVTS[$i]['mnt']);
        if ($myErr->errCode != NO_ERR)
          $msg .= sprintf(_("Ligne %s:  "), $i) . $error[$myErr->errCode] . sprintf(_(" N°Compte= %s "), $cpte_client) . "<br />";

        $INFOMVTS[$i]['id_cpte'] = $id_cpte_client;
        $INFOMVTS[$i]['num_client'] = $ACC['id_titulaire'];
        $INFOMVTS[$i]['devise'] = $ACC['devise'];
        $SESSION_VARS['retrait_par_lot'][$i]['devise'] = $ACC['devise'];
      }
      //Recup date
      $INFOMVTS[$i]['date'] = ${'date' . $i};
      $SESSION_VARS['retrait_par_lot'][$i]['date'] = ${'date' . $i};
      $dummy = splitEuropeanDate($INFOMVTS[$i]['date']);
      if ($INFOMVTS[$i]['date'] != "") {
        $have_date = true;
        if (gmmktime(0, 0, 0, $dummy[1], $dummy[0], $dummy[2]) >= gmmktime(0, 0, 0, date('m'), date('d') + 1, date('Y')))
          $msg .= sprintf(_("Ligne N°%s: La date %s se situe après la date du jour !"), $i, $INFOMVTS[$i]['date']) . "<br />";
      }

      //Recup commissison
      if (${'mnt_com' . $i} != "") {
        //++$count; // n'est pas obligatoire
        $INFOMVTS[$i]['mnt_com'] = arrondiMonnaie(recupMontant(${'mnt_com' . $i}), 0, $SESSION_VARS['retrait_par_lot'][$i]['devise']);
        $SESSION_VARS['retrait_par_lot'][$i]['mnt_com'] = arrondiMonnaie(recupMontant(${'mnt_com' . $i}), 0, $SESSION_VARS['retrait_par_lot'][$i]['devise']);;
      } else {
        $INFOMVTS[$i]['mnt_com'] = 0;
        $SESSION_VARS['retrait_par_lot'][$i]['mnt_com'] = 0;
      }

      //Recup num_recu
      $INFOMVTS[$i]['num_recu'] = ${'num_recu' . $i};
      $SESSION_VARS['retrait_par_lot'][$i]['num_recu'] = ${'num_recu' . $i};
      if ($INFOMVTS[$i]['num_recu'] != "") {
        if ((in_array($INFOMVTS[$i]['num_recu'], $nums)) || (exist_num_recu_lot($INFOMVTS[$i]['num_recu'])))  //Vérifie l'unicité du numéro de lot
          $msg = sprintf(_("Ligne %s: Le numéro de reçu  a déjà été utilisé !"), $i) . "<BR>";
        array_push($nums, $INFOMVTS[$i]['num_recu']);
        //++$count;
      }

      //Vérifie que tous les champs soient bien renseignés
      if ((($count != 2) || (!$have_date)) && ($count > 0)) {
        $msg .= sprintf(_("Ligne %s: Certains champs  ne sont pas renseignés !"), $i) . "<br />";
      } else {
        //calcul somme des depot par rapport à chaque devise
        $total[$INFOMVTS[$i]['devise']] += $INFOMVTS[$i]['mnt'];
      }

      //Doit-on traiter cette entrée ?
      $INFOMVTS[$i]['traite'] = (($count == 2) && ($have_date));
      $i++;
    }

    if ($msg == "" && $i > 1) {//Si tout OK
      $html = new HTML_GEN2(_("Récapitulatif"));

      $table = new HTML_TABLE_table(7, TABLE_STYLE_ALTERN);
      $table->set_property("title", _("Liste des mouvements"));
      $table->add_cell(new TABLE_cell(_("N°")));
      $table->add_cell(new TABLE_cell(_("N° Compte")));
      $table->add_cell(new TABLE_cell(_("Nom Client")));
      $table->add_cell(new TABLE_cell(_("Montant")));
      $table->add_cell(new TABLE_cell(_("Commission")));
      $table->add_cell(new TABLE_cell(_("Devise")));
      $table->add_cell(new TABLE_cell(_("N° reçu")));

      while (list($i, $value) = each($INFOMVTS)) {
        $table->add_cell(new TABLE_cell($i));
        $table->add_cell(new TABLE_cell($value["cpte_client"]));
        $table->add_cell(new TABLE_cell(getClientName($value["num_client"])));
        $table->add_cell(new TABLE_cell(afficheMontant($value["mnt"], false)));
        $table->add_cell(new TABLE_cell(afficheMontant($value["mnt_com"], false)));
        $table->add_cell(new TABLE_cell($value["devise"]));
        $table->add_cell(new TABLE_cell($value["num_recu"]));
        //$table->set_cell_property("align","right");
      }
      echo $table->gen_HTML();

      $table2 = new HTML_TABLE_table(2, TABLE_STYLE_CLASSIC);
      $table2->set_property("title", _("Destination des fonds"));
      $table2->set_property("border", $tableau_border);

      if ($SESSION_VARS["SOURCE"]["dest_fond"] == 1) {
        $table2->add_cell(new TABLE_cell(_("Source")));
        $table2->add_cell(new TABLE_cell(_("Guichet")));
        $table2->set_row_childs_property("align", "left");
      } else {
        $infoTireur = getTireurBenefDatas($SESSION_VARS["SOURCE"]["id_ben"]);
        $libel_correspondant = getLibelCorrespondant($global_monnaie);
        $type_piece_choices = array(3 => $adsys["adsys_type_piece_payement"][3], 13 => $adsys["adsys_type_piece_payement"][13], 15 => $adsys["adsys_type_piece_payement"][15]);
        $table2->add_cell(new TABLE_cell(_("Source")));
        $table2->add_cell(new TABLE_cell(_("Correspondant extérieur")));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Donneur d'ordre")));
        $table2->add_cell(new TABLE_cell($infoTireur["denomination"]));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Correspondant")));
        $table2->add_cell(new TABLE_cell($libel_correspondant[$SESSION_VARS["SOURCE"]["correspondant"]]));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Pièce justificative")));
        $table2->add_cell(new TABLE_cell($type_piece_choices[$SESSION_VARS["SOURCE"]["type_piece"]]));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Date pièce")));
        $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["date_piece"]));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Référence pièce (Numéro)")));
        $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["num_piece"]));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Communication")));
        $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["communication"]));
        $table2->set_row_childs_property("align", "left");
        $table2->add_cell(new TABLE_cell(_("Remarque")));
        $table2->add_cell(new TABLE_cell($SESSION_VARS["SOURCE"]["remarque"]));
        $table2->set_row_childs_property("align", "left");
      }
      echo $table2->gen_HTML();


      $MyPage = new HTML_message(_("Demande confirmation"));

      $msg_total .= _("La somme totale perçue par devise") . "         :<br>";
      foreach ($total as $code => $montant) {
        setMonnaieCourante($code);
        $msg_total .= sprintf(_(" Total  en (%s ) : %s"), $code, "<b>" . afficheMontant($montant, true) . "</b>") . ". <BR>";
      }
      $msg_total .= "<br> <br>";
      $msg_total .= _("Voulez-vous continuer ?");
      $MyPage->setMessage($msg_total);
//   	$MyPage->setMessage(sprintf(_("La somme totale perçue pour ces %s opérations est de %s en (%s )"),$SESSION_VARS['nbre_ope'],"<b>".afficheMontant($montant, true)."</b>",$code).". "._("Voulez-vous continuer ?"));

      $MyPage->addButton(BUTTON_OUI, "Rgu-4");
      $MyPage->addButton(BUTTON_NON, "Rgu-2");

      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      $SESSION_VARS["MVTS"] = serialize($INFOMVTS);

    } else { //Si erreur
      if ($i == 1) {
        $msg .= _("Aucune Données trouvées ");
        unset($SESSION_VARS['retrait_par_lot']);
      }
      $MyPage = new HTML_erreur(_("Erreur saisie par lot"));
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "Rgu-2");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }
  }

}
/*}}}*/

/*{{{ Rgu-4 : Enregistrement des retraits */
else if ($global_nom_ecran == "Rgu-4") {
  //appel DB

  if ($SESSION_VARS['type_ret'] == 3) {

    //$SESSION_VARS['SOURCE']['dest_fond'] = 3;
    $SESSION_VARS['SOURCE_UBA']['nb_ope_count'] = $SESSION_VARS['nb_ope_count'];
    /*$SESSION_VARS['SOURCE']['correspondant'] = $SESSION_VARS['nb_ope_count'];
    $SESSION_VARS['SOURCE']['type_piece'] = $SESSION_VARS['nb_ope_count'];
    $SESSION_VARS['SOURCE']['date_piece'] = $SESSION_VARS['nb_ope_count'];
    $SESSION_VARS['SOURCE']['num_piece'] = $SESSION_VARS['nb_ope_count'];
    $SESSION_VARS['SOURCE']['id_ben'] = $SESSION_VARS['nb_ope_count'];*/
    $myErr = retrait_par_lot(unserialize($SESSION_VARS["MVTS"]), $global_nom_login, $global_id_guichet, $SESSION_VARS['SOURCE_UBA']);
  }
  else {
    $myErr = retrait_par_lot(unserialize($SESSION_VARS["MVTS"]), $global_nom_login, $global_id_guichet, $SESSION_VARS['SOURCE']);
  }

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
    $table->add_cell(new TABLE_cell(_("N°Compte")));
    $table->add_cell(new TABLE_cell(_("Nom Client")));
    $table->add_cell(new TABLE_cell(_("Montant")));
    $table->add_cell(new TABLE_cell(_("Commission")));
     $table->add_cell(new TABLE_cell(_("Devise")));

    while (list($key, $value) = each($myErr->param)) {

      $table->add_cell(new TABLE_cell(sprintf("%09d", $value["id_his"])));
      $table->add_cell(new TABLE_cell($value["num_recu"]));
      $table->add_cell(new TABLE_cell( $value["cpte_client"]));
      $table->add_cell(new TABLE_cell(getClientName($value["id_client"])));
      $table->add_cell(new TABLE_cell(afficheMontant($value["mnt"], false)));
      $table->add_cell(new TABLE_cell(afficheMontant($value["mnt_com"], false)));
      $table->add_cell(new TABLE_cell( $value["devise"]));
      $table->set_cell_property("align","right");
    }
    //liberez le tableau
    unset($SESSION_VARS['retrait_par_lot']);
    echo $table->gen_HTML();


    if (sizeof($SESSION_VARS['data_rejected']) > 0){
      $html = new HTML_GEN2(_("Tableau des transactions incorrectes"));

      $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
      $table->set_property("title", _("Transactions rejetés"));
      $table->add_cell(new TABLE_cell(_("N° carte")));
      $table->add_cell(new TABLE_cell(_("N° Client")));
      $table->add_cell(new TABLE_cell(_("Numero compte")));
      $table->add_cell(new TABLE_cell(_("Nom client")));
      $table->add_cell(new TABLE_cell(_("Montant")));

      while (list($i, $value) = each($SESSION_VARS['data_rejected'])) {
        $table->add_cell(new TABLE_cell($value['numero_carte']));
        $table->add_cell(new TABLE_cell($value["num_client"]));
        $table->add_cell(new TABLE_cell($value["cpte_client"]));
        $table->add_cell(new TABLE_cell(getClientName($value["num_client"])));
        $table->add_cell(new TABLE_cell(afficheMontant($value["mnt"], false)));
        //$table->set_cell_property("align","right");
      }
      echo $table->gen_HTML();
    }
  } else {
    $html_err = new HTML_erreur(_("Echec de la saisie par lot.")." ");

    $msg = '';
    $param = $myErr->param;
    if ($param != NULL) {
      if(is_array($param)) {
        foreach($param as $key => $val) {
          $msg .= "<br /> ".$key." : ".$param["$key"]."";
        }
      }else {
        $msg .=  $param;
      }
    }

    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$msg);
    $html_err->addButton("BUTTON_OK", 'Gen-6');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>