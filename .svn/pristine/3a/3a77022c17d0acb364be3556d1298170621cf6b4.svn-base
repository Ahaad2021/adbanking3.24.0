<?php
// Visualisation transaction et visualisation toutes transactions
require_once 'lib/dbProcedures/guichet.php';
require_once 'modules/rapports/xml_guichet.php';

////////////////////////  Vgu-1 or Vtg-1 /////////////////////////////////

if (($global_nom_ecran == "Vgu-1") || ($global_nom_ecran == "Vtg-1")) {
  $MyPage = new HTML_GEN2(_("Critères de recherche"));

  //Champs login
  if ($global_nom_ecran != "Vgu-1") {
    $MyPage->addTableRefField("login", _("Login ayant exécuté la fonction"), "ad_log");
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_TOUS, true);
  } 
  
  // Multi agence elements  
  unset($adsys["adsys_fonction_systeme"][92],$adsys["adsys_fonction_systeme"][93], $adsys["adsys_fonction_systeme"][193],$adsys["adsys_fonction_systeme"][194]);

  // Module Specifique : Engrais Chimiques
  unset($adsys["adsys_fonction_systeme"][171],$adsys["adsys_fonction_systeme"][172],$adsys["adsys_fonction_systeme"][173],$adsys["adsys_fonction_systeme"][174],$adsys["adsys_fonction_systeme"][175],$adsys["adsys_fonction_systeme"][176],$adsys["adsys_fonction_systeme"][177],$adsys["adsys_fonction_systeme"][178],$adsys["adsys_fonction_systeme"][179],$adsys["adsys_fonction_systeme"][182],$adsys["adsys_fonction_systeme"][252],$adsys["adsys_fonction_systeme"][253],$adsys["adsys_fonction_systeme"][254]);
  
  // Récupère la liste des fonction dans $adsys
  $liste_fonctions = $adsys["adsys_fonction_systeme"];
    
  asort($liste_fonctions);
  $choiceOrder = array_keys($liste_fonctions);

  //Champs type de fonction, à classer dans l'ordre alphabétique
  $MyPage->addTableRefField("num_fonction", "Fonction", "adsys_fonction_systeme");
  
  $MyPage->setFieldProperties("num_fonction", FIELDP_ORDER_CHOICES, $choiceOrder);
  $MyPage->setFieldProperties("num_fonction", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("num_fonction", FIELDP_HAS_CHOICE_TOUS, true);

  //Uniquement transactions financières?
  $MyPage->addField("trans_fin", _("Uniquement les transactions financières ?"), TYPC_BOL);

  //Champs client
  $MyPage->addField("num_client", _("Numéro client"), TYPC_INT);
  $MyPage->addLink("num_client", "rechercher", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;"));

  //Champs date début
  $MyPage->addField("date_min", _("Date min"), TYPC_DTE);

  //Champs date fin
  $MyPage->addField("date_max", _("Date max"), TYPC_DTE);

  //Champs n° transaction min[B
  $MyPage->addField("trans_min", _("N° transaction min"), TYPC_INT);

  //Champs n° transaction max
  $MyPage->addField("trans_max", _("N° transaction max"), TYPC_INT);

  //Boutons
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  if ($global_nom_ecran == "Vgu-1") $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Vgu-2");
  else if ($global_nom_ecran == "Vtg-1") $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Vtg-2");
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-6");


  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} 

////////////////////////  Vgu-2 or Vtg-2 /////////////////////////////////

else if (($global_nom_ecran == "Vgu-2") || ($global_nom_ecran == "Vtg-2")) {
  if ($global_nom_ecran == "Vgu-2") $login = $global_nom_login;
  else if ($login == '0') $login = NULL;

  if ($num_fonction == 0) $num_fonction = NULL;
  if (($num_client <= 0) || ($num_client == "")) $client = NULL;
  if ($date_min == "") $date_min = NULL;
  if ($date_max == "") $date_max = NULL;
  if ($trans_min == "") $trans_min = NULL;
  if ($trans_max == "") $trans_max = NULL;
  if (isset($trans_fin)) $trans_fin = true;
  else $trans_fin = false;
  $SESSION_VARS['criteres'] = array();
  $SESSION_VARS['criteres']['login'] = $login;
  $SESSION_VARS['criteres']['num_fonction'] = $num_fonction;
  $SESSION_VARS['criteres']['num_client'] = $num_client;
  $SESSION_VARS['criteres']['date_min'] = $date_min;
  $SESSION_VARS['criteres']['date_max'] = $date_max;
  $SESSION_VARS['criteres']['trans_min'] = $trans_min;
  $SESSION_VARS['criteres']['trans_max'] = $trans_max;
  $SESSION_VARS['criteres']['trans_fin'] = $trans_fin;

  $nombre = count_recherche_transactions($login, $num_fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);
  if ($nombre > 300) {

  	$MyPage = new HTML_erreur(_("Trop de correspondances"));
  	$MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche ou imprimer."),$nombre));
    switch ($global_nom_ecran) {
    case 'Vgu-2':
      $nextScreen = "Vgu-1";
      $printScreen = "Vgu-3";
      break;
    case 'Vtg-2':
      $nextScreen = "Vtg-1";
      $printScreen = "Vtg-3";
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran non-reconnu ici"
    }
    $MyPage->addButton(BUTTON_OK, $nextScreen);
    $MyPage->addCustomButton("print", _("Imprimer"), $printScreen, TYPB_SUBMIT);
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;

  } else {
    $resultat = recherche_transactions($login, $num_fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);

    $html = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $html .= "<h1 align=\"center\">"._("Résultat recherche")."</h1><br><br>\n";
    $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //Ligne titre
    $html .= "<TR bgcolor=$colb_tableau>";

    $html .= "<TD><b>n°</b></TD><TD align=\"center\"><b>"._("Date")."</b></TD><TD align=\"center\"><b>"._("Heure")."</b></TD><TD align=\"center\"><b>"._("Fonction")."</b></TD><TD align=\"center\"><b>"._("Opération")."</b></TD><TD align=\"center\"><b>"._("Login")."</b></TD><TD align=\"center\"><b>"._("N° client")."</b></TD><TD align=\"center\"><b>"._("Login initiateur")."</b></TD>".(($num_fonction == 10)?"<TD align=\"center\"><b>"._("Visualiser")."</b></TD>":"")."</TR>\n";

    $SESSION_VARS['id_his'] = array();
    reset($resultat);
    while (list(,$value) = each($resultat)) { //Pour chaque résultat
      //On alterne la couleur de fond
      if ($a) $color = $colb_tableau;
      else $color = $colb_tableau_altern;
      $a = !$a;
      $html .= "<TR bgcolor=$color>\n";

      //n°
      // FIXME/TF Aaaargh quelle horreur !
      if (($value['trans_fin']) || ($adsys["adsys_fonction_systeme"][$value['type_fonction']]==_('Ajustement du solde d\'un compte')) || ($value["id_his_ext"] != ''))
        $html .= "<TD><A href=# onclick=\"OpenBrwXY('$http_prefix/lib/html/detail_transaction.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his']."&operation=".$value['type_operation']."','', 800, 600);\">".$value['id_his']."</A></TD>";
      else $html .= "<TD>".$value['id_his']."</TD>";

      //Date
      $html .= "<TD>".pg2phpDate($value['date'])."</TD>";

      //Heure
      $html .= "<TD>".pg2phpHeure($value['date'])."</TD>";

      //Fonction
      $html .= "<TD>".adb_gettext($adsys["adsys_fonction_systeme"][$value['type_fonction']]);
      $html .= "</TD>\n";

      //Operation
      if ($value['type_operation'] != null){
        $libelOperation= getLibelOperationTransaction($value['type_operation']);
        $html .= "<TD align=\"center\">".$libelOperation."</TD>\n";
      }else{
        $html .= "<TD align=\"center\"></TD>\n";
      }

      
      //Login
      $html .= "<TD>".$value['login']."</TD>\n";

      //N° client
      if($value['type_fonction']==92 || $value['type_fonction']==93)
      {
        if (trim($value['infos'])!='') {
            $html .= "<TD align=\"center\">".trim($value['infos'])."</TD>\n";
        } else {
            $html .= "<TD></TD>\n";
        }
      }
      else
      {
        if ($value['id_client'] > 0) {
            $html .= "<TD align=\"center\">".sprintf("%06d", $value['id_client'])."</TD>\n";
        } else {
            $html .= "<TD></TD>\n";
        }
      }

      if ($value['info_ecriture'] != null){
        $html .= "<TD align=\"center\">".$value['info_ecriture']."</TD>\n";
      }else{
        $html .= "<TD align=\"center\"></TD>\n";
      }

      if($num_fonction == 10){
          $infos = $value["infos"];
          $url = "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;";
          $html .= "<TD align=\"center\"><a href='#' data-type='visualize' data-cli-hist=$infos>Visualiser</a></TD>\n";
      }
      $html .= "</TR>\n";

      array_push($SESSION_VARS['id_his'], $value['id_his']);
    }

    $html .= "<TR bgcolor=$colb_tableau><TD colspan=8 align=\"center\">\n";

    //Boutons
    $html .= "<TABLE align=\"center\"><TR>";

    if ($global_nom_ecran == "Vgu-2") {
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Vgu-1');\"></TD>";
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Vgu-3');\"></TD>";
    } else if ($global_nom_ecran == "Vtg-2") {
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Vtg-1');\"></TD>";
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Vtg-3');\"></TD>";
    }
    $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour menu")."\" onclick=\"ADFormValid=true; assign('Gen-6');\"></TD>";

    $html .= "</TR></TABLE>\n";

    $html .= "</TD></TR></TABLE>\n";
    $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

    $html .= "<script type=\"text/javascript\">
        $('[data-type=visualize]').each(function(index, item){
            if($(item).attr('data-cli-hist') == ''){
                $(this).css('visibility', 'hidden');
            }
        });
        $('[data-type=visualize]').click(function(event){
            var id_cli_hist_val = $(this).attr('data-cli-hist');
            OpenBrwXY('$SERVER_NAME/modules/clients/visualiser_modif_client.php?m_agc=".$_REQUEST['m_agc']."&id_cli_hist=' + id_cli_hist_val, '"._("Recherche")."', 800, 600);return false;
        });
        </script>";
    echo $html;
  }
} 

////////////////////////  Vgu-3 or Vtg-3 /////////////////////////////////

else if (($global_nom_ecran == "Vgu-3") || ($global_nom_ecran == "Vtg-3")) {
	$login = $SESSION_VARS['criteres']['login'];
	$num_fonction = $SESSION_VARS['criteres']['num_fonction'];
	$num_client = $SESSION_VARS['criteres']['num_client'];
	$date_min = $SESSION_VARS['criteres']['date_min'];
	$date_max = $SESSION_VARS['criteres']['date_max'];
	$trans_min = $SESSION_VARS['criteres']['trans_min'];
	$trans_max = $SESSION_VARS['criteres']['trans_max'];
	$trans_fin = $SESSION_VARS['criteres']['trans_fin'];
  	$criteres = array (
  					  _("Login") => $login,
  					  _("Fonction") => $adsys["adsys_fonction_systeme"][$num_fonction],
                      _("Numéro client") => $num_client,
                      _("Date min") => date($date_min),
                      _("Date max") => date($date_max),
                      _("N° transaction min") => $trans_min,
                      _("N° transaction max") => $trans_max
                      ); 
	// Infos sur les transactions
	$DATAS = recherche_transactions_details($login, $num_fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);
	$xml = xml_detail_transactions($DATAS, $criteres); //Génération du code XML
	$fichier_pdf = xml_2_xslfo_2_pdf($xml, 'detail_transactions.xslt'); //Génération du XSL-FO et du PDF
	
	//Message de confirmation + affichage du rapport dans une nouvelle fenêtre
	echo get_show_pdf_html("Gen-6", $fichier_pdf);

	}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>