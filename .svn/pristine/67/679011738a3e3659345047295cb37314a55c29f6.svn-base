<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [129] Correction des dossier de crédit
 * Cette opération comprends les écrans :
 * - Cdd-1 : Sélection du type de correction
 * - Cdd-2 : sélection d'un dossier de crédit
 * - Cdd-3 : Affichage des informations dépendant du type de correction
 * - Cdd-4 : Affichage des remboursements effectués sur le crédit
 * - Cdd-5 : Affichage des remboursements à annuler
 * - Cdd-6 : Confirmation annulation des remboursements
 * - Cdd-7 : Affichage du dossier à supprimer
 * - Cdd-8 : confirmation suppression dossier de crédit
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';

global $global_id_client;

/*{{{ Cdd-1 : Sélection de l'opération de correction d'un dossier de crédit */
if ($global_nom_ecran == "Cdd-1") {
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);


  // Création du formulaire
  $Myform = new HTML_GEN2(_("Sélection du type de correction"));
  $Myform->addField("type_cor",_("Type de correction"), TYPC_LSB);
  $choices = array(1=> _("Suppression d'un remboursement"),2=> _("Suppression du dossier de crédit"));
  $Myform->setFieldProperties("type_cor",FIELDP_ADD_CHOICES,$choices);
  $Myform->setFieldProperties("type_cor", FIELDP_IS_REQUIRED, true);

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Cdd-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Cdd-2 : Sélection d'un dossier de crédit */
else if ($global_nom_ecran == "Cdd-2") {

	// Si l'on vient de l'ecran Cdd-1, recuperer les valeurs de session
	if (strstr($global_nom_ecran_prec,"Cdd-1")) {
	$SESSION_VARS["type_cor"] = $type_cor;
	}

  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  $dossiers = array(); // Tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Listbox des dossiers à afficher
  $i = 1; // Clé de la liste box

	// En fonction du choix du numéro de dossier (id_doss=id_client) , afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

    if ($SESSION_VARS["type_cor"] == 1){
        $whereCl = " AND (etat = 5 or etat = 7 or etat = 8 or etat = 9 or etat = 13 or etat = 14 or etat = 15) ";
    }
    else if ($SESSION_VARS["type_cor"] == 2){
        $whereCl = " AND (etat = 10 or etat = 5)";
    }
    $whereCl .= " AND is_ligne_credit='f' AND mode_calc_int!=5 ";

  $dossiers_reels = getIdDossier($global_id_client, $whereCl);
  if (is_array($dossiers_reels)) {
    foreach($dossiers_reels as $id_doss=>$value) {
      if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe
        $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
        $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
        $dossiers[$i] = $value;

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
        $codejs .= "}\n";
        $i++;
     }
    }
   }

	// SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
    // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2

    $dossiers_fictifs = getCreditFictif(" WHERE id_membre=$global_id_client and gs_cat=2");

    // Récupération des dossiers des membres
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    // Ajout dans la liste box, pour chaque dossier fictif du GS, les dossiers réels des membres du GS
    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val) {
        // Récupération des dossiers individuels réels (dans ad_dcr) suivant l'opération de correction
        if ($SESSION_VARS["type_cor"] == 1){
          if (($val['is_ligne_credit'] != 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 5 or $val['etat'] == 6 or $val['etat'] == 7 or $val['etat'] == 8 or $val['etat'] == 9)) {
            $date_dem = $date = pg2phpDate($val['date_dem']);
            $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
          }
        }
        else if ($SESSION_VARS["type_cor"] == 2){
            if (($val['is_ligne_credit'] != 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 10 or $val['etat'] == 5)) {
            $date_dem = $date = pg2phpDate($val['date_dem']);
            $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
          }
        }
      }

      if ($infos != '') { // Si au moins on 1 dossier
        $infos .= "du $date_dem";
        $liste[$i] = $infos;
        $dossiers[$i] = $value; // on garde les infos du dossier fictif

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $val["libelle"] . "\";";
        $codejs .= "}\n";
        $i++;
      }
    }
  }

  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
  $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
  $codejs .= "\n\t}\n";
  $codejs .= "}\ngetInfoDossier();";

	$SESSION_VARS['dossiers'] = $dossiers;

  // Création du formulaire
  if (sizeof($SESSION_VARS['dossiers']) > 0){
  $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
  $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
  $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);
//  if ( $SESSION_VARS["type_cor"] == 1) { // Correction d'un remboursement
	$Myform->addField("source",_("Destination"), TYPC_LSB);

  if (profil_has_guichet($global_id_profil)) {
     $source = array(1 => "Guichet", 2 => "Compte lié");
  	} else {
		 $source = array(2 => "Compte lié");
		}
	$Myform->setFieldProperties("source", FIELDP_ADD_CHOICES, $source);
  $Myform->setFieldProperties("source", FIELDP_IS_REQUIRED, true);



  $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);

  $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
  $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

  $Myform->setFieldProperties("id_doss",FIELDP_ADD_CHOICES,$liste);
  $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange"=>"getInfoDossier();"));
  $Myform->addJS(JSP_FORM, "JS3", $codejs);

  // Javascript : vérifie qu'un dossier est sélectionné
  $JS_1 = "";
  $JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné.")."\\n';ADFormValid=false;}\n";
  $Myform->addJS(JSP_BEGIN_CHECK,"testdos",$JS_1);

  // Ordre d'affichage des champs
  $order = array("id_doss","id_prod");

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Cdd-1");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Cdd-3");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
  }
  else{
  		$Myform = new HTML_erreur(_("Selection de dossier. "));
      $Myform->setMessage(sprintf(_("Il n y a pas de dossiers de crédits à corriger")));
      $Myform->addButton("BUTTON_OK", 'Cdd-1');
      $Myform->buildHTML();
      echo $Myform->getHTML();
      die();
  }


}
/*}}}*/

/*{{{ Cdd-3 : informations sur le dossier de credit */
else if ($global_nom_ecran == "Cdd-3") {

	if (!isset($SESSION_VARS["id_doss"])) {
    $SESSION_VARS["id_doss"] = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
  }
  if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
    /* Récupéartion des infos du dossier de crédit */
    $whereCl_dossier=" AND id_doss=".$SESSION_VARS["id_doss"];
  } else {
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);
    foreach($dossiers_membre as $id_doss=>$val_doss) {
      $SESSION_VARS["id_doss"] = $val_doss['id_doss'];
      $whereCl_dossier =" AND id_doss =".$SESSION_VARS["id_doss"];
    }
  }

	    // Si on vient de Cdd-2, on récupère les infos de la BD
    if (strstr($global_nom_ecran_prec,"Cdd-2")) {
    	unset($SESSION_VARS['infos_doss']);
    	$SESSION_VARS['source'] = $source;
      // Récupération des dossiers à corriger
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
        $SESSION_VARS['gs_cat'] = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'];
        // Les informations sur le dossier
        $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
        $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];
        $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
        $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
        $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
        $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);

        // Infos dossiers fictifs dans le cas de GS avec dossier unique
        if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
          $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
          $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);
        }
      }
      elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
        $SESSION_VARS['gs_cat'] = 2;
        foreach($dossiers_membre as $id_doss=>$val_doss) {
          // infos dossier fictif
          $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];  // id du dossier fictif (dossier du groupe)
          $whereCond = " WHERE id = $id_doss_fic";
          $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

          // dossiers réels des membre du GS
          $dossiers_membre = getDossiersMultiplesGS($global_id_client);
          foreach($dossiers_membre as $id_doss=>$val) {
            if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) {
              $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
              $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
              $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
              $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);
              $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
            }
          }
        }
      }
    }

  if ( $SESSION_VARS["type_cor"] == 1) { // Correction d'un remboursement

    $MyPage = new HTML_GEN2(_("Correction des dossiers de crédit"));
		// permet de savoir s'il y a un remboursement pour afficher la page, sinon un message d'infos(aucun remboursement...) est affiché
		$has_remb = false;
		$affich_remb = false; //s'il y a un remboursement, on affiche le tableau des échéances
    //Echéancier théorique
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
      $mnt_remb = 0;
      //$id_ech = $val_dos['infos_credit']['id_ech'];
       //verifier si le dossier de crédit a été repris
		 	// recuperation de la date de la reprise
		 	$date_reprise_parm=getDateCreditRepris($id_doss);
		 	$date_reprise=$date_reprise_parm->param[0];

		 	if( !is_null($date_reprise)) {
		 		$whereCond="WHERE (id_doss='".$id_doss."') AND  date_remb > date('$date_reprise') AND annul_remb is null AND id_his is null ";
		 	} else {
		 		$whereCond="WHERE (id_doss='".$id_doss."') AND annul_remb is null AND id_his is null";
		 	}

      $nom_cli = getClientName($val_doss['id_client']);

			//$whereCond="WHERE (id_doss='".$id_doss."')";
      $echeance =getRemboursement($whereCond);
			$SESSION_VARS['infos_doss'][$id_doss]["echeance"]= $echeance;
			//Tableau des echéances remboursés et partiellement remboursés
      $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
      $retour .= "<TD colspan=7 align=\"left\"><b>"._("Echéances du dossier de crédit")."</b></TD>\n";
      $retour .= "</TR>\n";
      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
      $retour .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Date")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Capital attendu")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Intérêts attendus")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Garantie attendue")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Total attendu")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Corriger remboursements")."</TD>\n";
      $retour .= "</TR>\n";
      foreach($echeance as $key => $value) {
      //$REMB = getRemboursement("WHERE id_doss = ".$id_doss." AND id_ech = ".$value["id_ech"]);
	      //if (sizeof($REMB) > 0){
	      $has_remb = true;
	      $affich_remb = true;
	      // Affichage
	      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
	      $retour .= "<TD align=\"center\"><a href=\"javascript:open_remb(".$id_doss.",".$value['id_ech'].")\">".$value['id_ech']."</a></TD>\n";
	      $retour .= "<TD align=\"left\">".pg2phpDate($value['date_remb'])."</TD>\n";
	      $retour .= "<TD align=\"right\">".afficheMontant ($value['mnt_remb_cap'],true)."</TD>\n";
	      $retour .= "<TD align=\"right\">".afficheMontant ($value['mnt_remb_int'],true)."</TD>\n";
	      $retour .= "<TD align=\"right\">".afficheMontant ($value['mnt_remb_gar'],true)."</TD>\n";
	      $retour .= "<TD align=\"right\">".afficheMontant ($value['mnt_remb_cap'] + $value['mnt_remb_int'] + $value['mnt_remb_gar'],true)."</TD>\n";
	      $retour .= "<TD align=\"center\"><INPUT type=\"checkbox\" name=\"echeance".$id_doss."_".$value['id_ech']."\" $checked \"></INPUT></TD>";
	      $retour .= "</TR>\n";
	     // }
      }
      $retour .= "</TABLE>\n";

      // s'il y a remboursement, on on construit le tableau pour les échéances
      if ($affich_remb){
       $MyPage->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Correction dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
       $MyPage->addHTMLExtraCode("ech".$id_doss,$retour);
       $MyPage->setHTMLExtraCodeProperties("ech".$id_doss, HTMP_IN_TABLE, true);
       $affich_remb = false;
      }


      // Calcul et affichage du montant à rembourser par membre

      if ($val_doss['gs_cat'] == 1) {
        $IdDossierReel = $id_doss;
        $WhereCF=" where id_dcr_grp_sol=$IdDossierReel ";
        $ListDossierFictif=getCreditFictif($WhereCF);
        $montant_ech_fic=array();
        $champHidden="<input type=\"hidden\" name=\"nb_mem\" value=\"".sizeof($ListDossierFictif)."\">";
        $MyPage->addHTMLExtraCode("champ_hidden_nb_mem".$id_doss,$champHidden);
        $champHiddenRecupMntRem="<input type=\"hidden\" name=\"mnt_rem\" value=\"".$mnt_remb."\">";
        $MyPage->addHTMLExtraCode("champ_hidden_mnt_rem".$id_doss, $champHiddenRecupMntRem);
        foreach($ListDossierFictif as $cle=>$valeur ) {
          $idClient= $valeur["id_membre"];
          $nomClient=getClientName($idClient);
          $echeancier_fictif=calcul_echeancier_theorique($val_doss['id_prod'],recupMontant($valeur["mnt_dem"]),$val_doss["duree_mois"],$val_doss["differe_jours"],$val_doss["differe_ech"], NULL, 1, $id_doss);
          $montant_tot_ech_fic=recupMontant($echeancier_fictif[$val_doss['infos_credit']['id_ech']]["mnt_cap"])+recupMontant($echeancier_fictif[$val_doss['infos_credit']['id_ech']]['mnt_int']);
          $montant_ech_fic[$idClient][0]=$nomClient;
          $montant_ech_fic[$idClient][1]=$montant_tot_ech_fic;
        }

        //Affichage des remboursements par membre
        $tableauCreditFic="<br /><table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Détails du remboursement par membre")."</b></td></tr></table>\n";
        $MyPage->addHTMLExtraCode("tab_cre_fic".$id_doss,$tableauCreditFic);

        $indice=0;
        $champHidden_recup="";
        foreach($montant_ech_fic as $cle=>$valeur ) {
          $champHidden_recup .="<input type=\"hidden\" name=\"hid_cre_fic_".$indice."\" value=\"".$valeur[1]."\">";
          $MyPage->addField("membre".$indice, _("Membre"), TYPC_TXT);
          $MyPage->setFieldProperties("membre".$indice, FIELDP_IS_LABEL, true);
          $MyPage->setFieldProperties("membre".$indice, FIELDP_DEFAULT,$id_cli." ".$valeur[0]);

          $MyPage->addField("cre_fic_".$indice, _("Montant"), TYPC_TXT);
          $MyPage->setFieldProperties("cre_fic_".$indice, FIELDP_IS_LABEL, true);
          $MyPage->setFieldProperties("cre_fic_".$indice, FIELDP_DEFAULT,afficheMontant($valeur[1],false));
          $MyPage->addHTMLExtraCode("epace".$indice,"<br />");

          $indice++;
        }
        $MyPage->addHTMLExtraCode("champ_hidden_mnt_rem_hidden".$id_doss,$champHidden_recup);
        //Fin affichage des dossier remboursements par membre
      }
      //Fin montant à rembourser par membre
    }
    //S'il n'ya pas de remboursement pour ce dossier de crédit, on
		if(!$has_remb){
			$Myform = new HTML_erreur(_("Suppression remboursement d'un dossier de crédit. "));
      $Myform->setMessage(sprintf(_("Le dossier sélectionné n'a pas de remboursements, impossible de continuer l'opération")));
      $Myform->addButton("BUTTON_OK", 'Cdd-2');
      $Myform->buildHTML();
      echo $Myform->getHTML();
      die();
		}
		 // Permet d'ouvrir la page de détail du remboursement
  	$JScode="";
  	$JScode .="\nfunction open_remb(id_doss,id_ech)";
  	$JScode .="\t{\n";
  	$JScode .="\t	// Construction de l'URL : de type ./lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_ech=id\n";
  	$JScode .="\t\turl = '../lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_doss='+id_doss+'&id_ech='+id_ech;\n";
  	$JScode .="\t\tRembWindow = window.open(url, '"._("Produit sélectionné")."', 'alwaysRaised=1,dependent=1,scrollbars,resizable=0,width=650,height=400');\n";
  	$JScode .="\t}\n";
  	$MyPage->addJS(JSP_FORM,"prodF",$JScode);

    //Boutons
    $MyPage->addFormButton(1,1,"valid",_("Valider"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Cdd-4");
    $MyPage->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdd-2");
    $MyPage->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $MyPage->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
  elseif( $SESSION_VARS["type_cor"] == 2) { // Suppression d'un dossier de crédit
    $MyPage = new HTML_GEN2(_("Suppression dossier de crédit"));
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
      $mnt = 0;
      $whereCond="WHERE (id_doss='".$id_doss."')";
      $echeance = getEcheancier($whereCond);

      //Tableau des echéances
      $nom_cli = getClientName($val_doss['id_client']);
      $MyPage->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Suppression du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
      $color = $colb_tableau;
      $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
      $retour .= "<TR bgcolor=\"$color\">\n";
      $retour .= "<TD colspan=7 align=\"left\"><b>"._("Echéances du crédit")."</b></TD>\n";
      $retour .= "</TR>\n";
      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
      $retour .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Date")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Capital restant du")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Intérêts restants dus")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Garantie restante due")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Pénalités dues")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Total du")."</TD>\n";
      $retour .= "</TR>\n";

      // Affichage
      $total_cap=0;
      $total_int=0;
      $total_pen=0;
      $total_gar=0;
      $infoEch=array();

      if (is_array($echeance)) {
        while (list($key,$info) = each($echeance)) {
          $total_cap +=$info["solde_cap"]; //Somme du capital dû
          $total_int +=$info["solde_int"]; //Somme des intérêts dûs
          $total_gar +=$info["solde_gar"]; //Somme de la garantie dûe
          $total_pen +=$info["solde_pen"]; //Somme des pénalités dûes

          array_push($infoEch,$info["solde_cap"]+$info["solde_int"]+$info["solde_pen"]+$info["solde_gar"]); //Montant par échéance

          $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);
          $retour .= "<TR bgcolor=\"$color\">\n";
          $retour .= "<TD align=\"center\">".$info["id_ech"]."</TD>\n";
          $retour .= "<TD align=\"left\">".pg2phpDate($info["date_ech"])."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_cap"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_int"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_gar"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_pen"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant (($info["solde_cap"]+$info["solde_pen"]+$info["solde_int"]+$info["solde_gar"]),false)."</TD>\n";
          $retour .= "</TR>\n";
        }
      }
      $mnt_total_du = $total_cap + $total_int + $total_pen + $total_gar;
      $SESSION_VARS["infoEch"][$id_doss] = $infoEch;

      $retour .= "</TABLE>\n";
      $MyPage->addHTMLExtraCode("ech1".$id_doss,$retour);
      $MyPage->setHTMLExtraCodeProperties("ech1".$id_doss, HTMP_IN_TABLE, true);

      // Calcul et affichage du montant à rembourser par membre dans le cas de GS avec un seul dossier réel
      if ($val_doss["gs_cat"] ==1 ) {
        $IdDossierReel = $id_doss;
        $WhereCF=" where id_dcr_grp_sol=$IdDossierReel ";
        $ListDossierFictif=getCreditFictif($WhereCF);
        $montant_ech_fic=array();
        $champHidden_tot="<input type=\"hidden\" name=\"nb_mem_tot\" value=\"".sizeof($ListDossierFictif)."\">";
        $MyPage->addHTMLExtraCode("champ_hidden_nb_mem_tot".$id_doss,$champHidden_tot);
        $echeance_encours = $echeance[0]["id_ech"];
        $somme_tot = 0;
        $nb_client=0;
        foreach($ListDossierFictif as $cle=>$valeur ) {
          $idClient= $valeur["id_membre"];
          $nomClient=getClientName($idClient);
          $echeancier=calcul_echeancier_theorique($val_doss["id_prod"],recupMontant($valeur["mnt_dem"]),$val_doss["duree_mois"],$val_doss["differe_jours"],$val_doss["differe_ech"], NULL, 1, $id_doss);
          $nb_client++;
          $montant_tot_ech_fic=0;
          for ($j=$echeance_encours;$j<=$val_doss["duree_mois"];$j++) {
            $montant_tot_ech_fic+=recupMontant($echeancier[$j]["mnt_cap"])+recupMontant($echeancier[$j]["mnt_int"]);
          }
          $montant_ech_fic[$idClient][0]=$nomClient;
          $montant_ech_fic[$idClient][1]=$montant_tot_ech_fic;
          $somme_tot += $montant_tot_ech_fic;
        }
        $diff = $somme_tot - recupMontant($mnt);
        foreach($montant_ech_fic as $cle=>$valeur ) {
          $montant_ech_fic[$cle][1]=$montant_ech_fic[$cle][1]-($diff/$nb_client);
        }

        //Fin affichage des dossier remboursements par membre
      }  //Fin montant à rembourser par membre
    }

    //Boutons
    $MyPage->addFormButton(1,1,"valid",_("Valider"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Cdd-7");
    $MyPage->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdd-2");
    $MyPage->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $MyPage->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  } // Fin si  typ_corr = 2 :suppression dossier de credit
}
/*}}}*/

/*{{{ Cdd-4 : Choix des remboursements à supprimer */
else if ($global_nom_ecran == "Cdd-4") {

	$formRemb = new HTML_GEN2(_("Les remboursements à corriger"));
	if (strstr($global_nom_ecran_prec,"Cdd-3")) {
	unset($SESSION_VARS['infos_remb']);
	foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
		//chercher date de reprise du crédit
		$date_reprise_parm=getDateCreditRepris($id_doss);
		 	$date_reprise=$date_reprise_parm->param[0];
		 	//FIXME : on peut pas remboursé le credit à la meme date que la reprise de credit.
		 	if( !is_null($date_reprise)) {
		 		$whereCondDateRepris="WHERE (id_doss='".$id_doss."') AND  date_remb > date('$date_reprise') AND annul_remb is null AND id_his is null ";
		 	} else {
		 		$whereCondDateRepris="WHERE (id_doss='".$id_doss."') AND annul_remb is null AND id_his is null";
		 	}

		//récupérer les remboursements
		foreach($SESSION_VARS['infos_doss'][$id_doss]["echeance"] as $key=>$val_ech){
			$id_ech = $val_ech["id_ech"];
			if (isset(${'echeance'.$id_doss."_".$id_ech})){
				$whereCond = $whereCondDateRepris." and id_ech = ".$id_ech;
				$rembs = getRemboursement($whereCond);

				$SESSION_VARS['infos_remb'][$id_doss][$id_ech] = $rembs;
			}
		}

	}
	}

	if(!isset($SESSION_VARS['infos_remb'])){
		$MyformErr = new HTML_erreur(_("Suppression remboursement d'un dossier de crédit. "));
    $MyformErr->setMessage(sprintf(_("Aucune échéance n'est sélectionnée, veuillez choisir une échéance")));
    $MyformErr->addButton("BUTTON_OK", 'Cdd-3');
    $MyformErr->buildHTML();
    echo $MyformErr->getHTML();
    die();
	}
	// Affichage des remboursements à corriger
  foreach($SESSION_VARS['infos_remb'] as $id_doss=>$val_doss) {

  	$formRemb->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Correction dossier N° %s"),$id_doss)."</b></p>");
    // Afficher les remboursements à supprimer
    foreach($val_doss as $id_ech=>$val_ech){

        $retour1 = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
        $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
        $retour1 .= "<TD colspan=8 align=\"left\"><b>".sprintf(_("Remboursements associés à l'échéance %s"),$id_ech)."</b></TD>\n";
        $retour1 .= "</TR>\n";
        $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
        $retour1 .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Date")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Capital remboursé")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Intérêts remboursés")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Garantie remboursée")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Pénalités remboursées")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Total remboursé")."</TD>\n";
        $retour1 .= "<TD align=\"center\">"._("Corriger remboursement")."</TD>\n";
        $retour1 .= "</TR>\n";
//        $whereCond = "where id_doss = $id_doss and id_ech = ".$id_ech;
//				$rembs = getRemboursement($whereCond);
//				// mettre les informations de remboursements dans la session pour l'ecran Cdd-5
//				$SESSION_VARS['infos_remb'][$id_doss][$id_ech] = $rembs;

        foreach ($val_ech as $key=>$val_remb) {
          // Affichage
          $num_remb = $val_remb['num_remb'];
          $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
          $retour1 .= "<TD align=\"center\">".$val_remb['num_remb']."</TD>\n";
          $retour1 .= "<TD align=\"left\">".pg2phpDate($val_remb['date_remb'])."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_cap'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_int'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_gar'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_pen'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_pen']+$val_remb['mnt_remb_cap']+$val_remb['mnt_remb_int']+$val_remb['mnt_remb_gar'],true)."</TD>\n";
          $retour1 .= "<TD align=\"center\"><input type=\"checkbox\" name=\"remboursement".$id_doss."_".$val_remb['id_ech']."_".$val_remb['num_remb']."\" $checked \" /></TD>";
          $retour1 .= "</TR>\n";
        }
        $retour1 .= "</TABLE><BR>";
      $formRemb->addHTMLExtraCode("remb".$id_doss.$id_ech, $retour1);

    }

  }

  // les boutons ajoutés
  $formRemb->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formRemb->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
  $formRemb->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

  $formRemb->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN,"Cdd-5");

  $formRemb->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formRemb->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $formRemb->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdd-3");
  $formRemb->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $formRemb->buildHTML();
  //echo  $HTML_code;
  echo $formRemb->getHTML();
}
/*}}}*/

/*{{{ Cdd-5 : confirmation annulation */
else if ($global_nom_ecran == "Cdd-5") {

	$formRemb = new HTML_GEN2(_("annulation remboursements"));
	$formRemb->addHTMLExtraCode("espace","<br /><b><p align=center><b>"._("Vous allez annuler les remboursements suivants.")."</b></p>");

	if (strstr($global_nom_ecran_prec,"Cdd-4")) {
		unset($SESSION_VARS['infos_remb_annul']);
		foreach($SESSION_VARS['infos_remb'] as $id_doss=>$val_doss) {
			foreach($val_doss as $id_ech=>$infos_remb){
				foreach($infos_remb as $key=>$val_remb){
					$num_remb = $val_remb['num_remb'];
					if (isset(${'remboursement'.$id_doss."_".$id_ech."_".$num_remb})){
						$SESSION_VARS['infos_remb_annul'][$id_doss][$id_ech][$num_remb] = $val_remb;
					}
				}
			}
		}

	}

	if(!isset($SESSION_VARS['infos_remb_annul'])){
		$MyformErr = new HTML_erreur(_("Suppression remboursement d'un dossier de crédit."));
    $MyformErr->setMessage(sprintf(_("Aucun remboursement n'est sélectionné, veuillez choisir un remboursement à supprimer")));
    $MyformErr->addButton("BUTTON_OK", 'Cdd-4');
    $MyformErr->buildHTML();
    echo $MyformErr->getHTML();
    die();
	}
	//debug($SESSION_VARS['infos_remb'], "Informations remboursements1");
  foreach($SESSION_VARS['infos_remb_annul'] as $id_doss=>$val_doss) {

    // Afficher les remboursements à supprimer
    $retour1 = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
    $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
    $retour1 .= "<TD colspan=8 align=\"left\"><b>"._("Remboursements associés au dossier")." ".$id_doss."</b></TD>\n";
    $retour1 .= "</TR>\n";
    $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
    $retour1 .= "<TD align=\"center\">"._("Numéro échéance")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Numéro remboursement")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Date")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Capital remboursé")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Intérêts remboursés")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Garantie remboursée")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Pénalités remboursées")."</TD>\n";
    $retour1 .= "<TD align=\"center\">"._("Total remboursé")."</TD>\n";
    $retour1 .= "</TR>\n";
    foreach($val_doss as $id_ech=>$infos_remb){
     foreach($infos_remb as $key=>$val_remb){

				$num_remb = $val_remb['num_remb'];
				if (isset(${'remboursement'.$id_doss."_".$id_ech."_".$num_remb})){
					$SESSION_VARS['infos_remb_annul'][$id_doss][$id_ech][$num_remb] = $val_remb;

          // Affichage
          $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
          $retour1 .= "<TD align=\"center\">".$id_ech."</TD>\n";
          $retour1 .= "<TD align=\"center\">".$val_remb['num_remb']."</TD>\n";
          $retour1 .= "<TD align=\"left\">".pg2phpDate($val_remb['date_remb'])."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_cap'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_int'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_gar'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_pen'],true)."</TD>\n";
          $retour1 .= "<TD align=\"right\">".afficheMontant ($val_remb['mnt_remb_pen']+$val_remb['mnt_remb_cap']+$val_remb['mnt_remb_int']+$val_remb['mnt_remb_gar'],true)."</TD>\n";
        	}

    }
  }
  $retour1 .= "</TR>\n";
  $retour1 .= "</TABLE><BR>";
  $formRemb->addHTMLExtraCode("infos_remb".$id_doss, $retour1);

  }

  // les boutons ajoutés
  $formRemb->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formRemb->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
  $formRemb->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

  $formRemb->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN,"Cdd-6");

  $formRemb->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formRemb->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $formRemb->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdd-4");
  $formRemb->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $formRemb->buildHTML();
  //echo  $HTML_code;
  echo $formRemb->getHTML();
}

/*}}}*/

/*{{{ Cdd-6 : Annulation remboursement */
else if ($global_nom_ecran == "Cdd-6") {
	  $formRemb = new HTML_GEN2(_("Annulation de remboursements"));
		$source = $SESSION_VARS['source'];
		$DATA_REMB = $SESSION_VARS['infos_remb_annul'];
    $myErr = annuleRemb($source, $global_id_guichet, $DATA_REMB);
    if ($myErr->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec lors de l'annulation des remboursements'. "));
      $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Gen-11');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }

    $formRemb = new HTML_message(_("Annulation de remboursements"));
    $formRemb->setMessage(_("Les remboursements ont été bien annulés"));
    $formRemb->addButton(BUTTON_OK, "Gen-11");
    $formRemb->buildHTML();
    echo $formRemb->HTML_code;


}
/*}}}*/

/*{{{ Cdd-7 : Suppression d'un dossier de crédit */
else if ($global_nom_ecran == "Cdd-7") {
    $MyPage = new HTML_GEN2(_("Suppression dossier de crédit"));
    $MyPage->addHTMLExtraCode("attention","<br /><b><p align=center><b> "._("Vous allez supprimer le(s) dossier(s) de crédit suivant").":</b></p>");
    $debit_count = array();
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    	//contrôle si origine fonds = "compte lié"
    	if($SESSION_VARS['source'] == 2){
    		$data_cpt = getAccountDatas($val_doss["cpt_liaison"]);
    		if($val_doss["cre_mnt_octr"] > $data_cpt["solde"]){
					$debit_count[$id_doss] = $val_doss["cpt_liaison"];
    		}
    	}

      $mnt = 0;
      $whereCond="WHERE (id_doss='".$id_doss."')";
      $echeance = getEcheancier($whereCond);

      //Tableau des echéances
      $nom_cli = getClientName($val_doss['id_client']);
      $MyPage->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Suppression du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
      $color = $colb_tableau;
      $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
      $retour .= "<TR bgcolor=\"$color\">\n";
      $retour .= "<TD colspan=7 align=\"left\"><b>"._("Echéances du crédit")."</b></TD>\n";
      $retour .= "</TR>\n";
      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
      $retour .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Date")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Capital restant du")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Intérêts restants dus")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Garantie restante due")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Pénalités dues")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Total du")."</TD>\n";
      $retour .= "</TR>\n";

      // Affichage
      $total_cap=0;
      $total_int=0;
      $total_pen=0;
      $total_gar=0;
      $infoEch=array();

      if (is_array($echeance)) {
        while (list($key,$info) = each($echeance)) {
          $total_cap +=$info["solde_cap"]; //Somme du capital dû
          $total_int +=$info["solde_int"]; //Somme des intérêts dûs
          $total_gar +=$info["solde_gar"]; //Somme de la garantie dûe
          $total_pen +=$info["solde_pen"]; //Somme des pénalités dûes

          array_push($infoEch,$info["solde_cap"]+$info["solde_int"]+$info["solde_pen"]+$info["solde_gar"]); //Montant par échéance

          $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);
          $retour .= "<TR bgcolor=\"$color\">\n";
          $retour .= "<TD align=\"center\">".$info["id_ech"]."</TD>\n";
          $retour .= "<TD align=\"left\">".pg2phpDate($info["date_ech"])."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_cap"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_int"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_gar"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_pen"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant (($info["solde_cap"]+$info["solde_pen"]+$info["solde_int"]+$info["solde_gar"]),false)."</TD>\n";
          $retour .= "</TR>\n";
        }
      }
      $mnt_total_du = $total_cap + $total_int + $total_pen + $total_gar;
      $SESSION_VARS["infoEch"][$id_doss] = $infoEch;

      $retour .= "</TABLE>\n";
      $MyPage->addHTMLExtraCode("ech1".$id_doss,$retour);
      $MyPage->setHTMLExtraCodeProperties("ech1".$id_doss, HTMP_IN_TABLE, true);

      // Calcul et affichage du montant à rembourser par membre dans le cas de GS avec un seul dossier réel
      if ($val_doss["gs_cat"] ==1 ) {
        $IdDossierReel = $id_doss;
        $WhereCF=" where id_dcr_grp_sol=$IdDossierReel ";
        $ListDossierFictif=getCreditFictif($WhereCF);
        $montant_ech_fic=array();
        $champHidden_tot="<input type=\"hidden\" name=\"nb_mem_tot\" value=\"".sizeof($ListDossierFictif)."\">";
        $MyPage->addHTMLExtraCode("champ_hidden_nb_mem_tot".$id_doss,$champHidden_tot);
        $echeance_encours = $echeance[0]["id_ech"];
        $somme_tot = 0;
        $nb_client=0;
        foreach($ListDossierFictif as $cle=>$valeur ) {
          $idClient= $valeur["id_membre"];
          $nomClient=getClientName($idClient);
          $echeancier=calcul_echeancier_theorique($val_doss["id_prod"],recupMontant($valeur["mnt_dem"]),$val_doss["duree_mois"],$val_doss["differe_jours"],$val_doss["differe_ech"], NULL, 1, $id_doss);
          $nb_client++;
          $montant_tot_ech_fic=0;
          for ($j=$echeance_encours;$j<=$val_doss["duree_mois"];$j++) {
            $montant_tot_ech_fic+=recupMontant($echeancier[$j]["mnt_cap"])+recupMontant($echeancier[$j]["mnt_int"]);
          }
          $montant_ech_fic[$idClient][0]=$nomClient;
          $montant_ech_fic[$idClient][1]=$montant_tot_ech_fic;
          $somme_tot += $montant_tot_ech_fic;
        }
        $diff = $somme_tot - recupMontant($mnt);
        foreach($montant_ech_fic as $cle=>$valeur ) {
          $montant_ech_fic[$cle][1]=$montant_ech_fic[$cle][1]-($diff/$nb_client);
        }
        //Fin affichage des dossier remboursements par membre
      }  //Fin montant à rembourser par membre
    }
    if(sizeof($debit_count) > 0){
    	$html_code = "<br /><b><p align=center><font color=\"".$colt_error."\"><b> "._("Le ou les comptes de liaison  de(s) dossier(s) ci dessous vont être débiteur(s)").":</b>";
    	foreach($debit_count as $key => $value)
    	$html_code .= "<br /><b> N° ".$key."</b>";
    	$html_code .= _("<br />Etes-vous sûr de réellement vouloir continuer?")."</p>";
    	$MyPage->addHTMLExtraCode("attention_debit", $html_code);
    }

    //Boutons
    $MyPage->addFormButton(1,1,"valid",_("Valider"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Cdd-8");
    $MyPage->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
/*}}}*/

/*{{{ Cdd-8 : Suppression dossier de crédit */
else if ($global_nom_ecran == "Cdd-8") {
	$Myform = new HTML_GEN2(_("Suppression d'un dossier de crédit"));
	$source = $SESSION_VARS['source'];
	$myErr = supprimeDossier($source, $global_id_guichet, $SESSION_VARS['infos_doss']);
  if ($myErr->errCode != NO_ERR) {
   $Myform = new HTML_erreur(_("Echec lors de la suppression du dossier "));
   $Myform->setMessage("Erreur : ".$myErr->param);
   $Myform->addButton(BUTTON_OK, "Gen-11");
   $Myform->buildHTML();
   echo $Myform->HTML_code;
   exit();
    }
	 $Myform = new HTML_message(_("Suppression des dossiers de crédit"));
   $Myform->setMessage(_("Les dossiers de crédit ont été bien supprimés"));
   $Myform->addButton(BUTTON_OK, "Gen-11");
   $Myform->buildHTML();
   echo $Myform->HTML_code;
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
