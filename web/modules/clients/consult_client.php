<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */
/* [25] Ecran de consultation d'un client et de ses relations
 * Cette opération comprends les écrans :
 * - Ccl-1 : Consultation d'un client
 * - Ccl-2 : Affichage des informations financières
 * - Ccl-3 : Affichage des frais en attente
 * - Ccl-4 : Impression fiche client
 * - Ccl-5 : Impression situation analytique client
 * @since 06/12/2001
 * @package Clients
 **/

require_once('lib/dbProcedures/client.php');
require_once('lib/misc/tableSys.php');
require_once('lib/dbProcedures/historique.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once 'lib/html/HTML_champs_extras.php';
require_once('lib/algo/ech_theorique.php');
require_once('lib/html/echeancier.php');
require_once('lib/html/suiviCredit.php');
require_once('lib/misc/divers.php');
require_once "modules/rapports/xml_clients.php";
require_once 'modules/rapports/xslt.php';


/*{{{ Ccl-1 : Consultation client */
if ($global_nom_ecran == "Ccl-1") {
  //recuperation des données de l'agence'
  global $global_id_agence;
  $AG = getAgenceDatas($global_id_agence);
  // Ajout dans l'historique, si c'est la première fois qu'on arrive à cet écran
  if (!isset($VuRelations))
    ajout_historique (25, $global_id_client, '', $global_nom_login, date("r"), NULL);
  $CLI = getClientDatas($global_id_client);
  $AGC = getAgenceDatas($global_id_agence);
  $myForm = new HTML_GEN2(_("Consultation du client"));
  $Order = array ("etat", "nbre_parts", "statut_juridique", "qualite", "id_client", "anc_id_client","matricule", "date_adh", "date_crea", "langue_correspondance", "gestionnaire");
  $labels = array("id_client" => "", "statut_juridique" => "", "etat" => "");
  if ( $CLI["statut_juridique"] == 1) {     // Personne physique
    global $global_photo_client, $global_signature_client;
    $myForm->addField("signature",_("Spécimen de signature"),TYPC_IMG);
    if ($global_signature_client != "")
      $myForm->setFieldProperties('signature', FIELDP_IMAGE_URL, $global_signature_client);
    $myForm->addField("photo",_("Photographie"),TYPC_IMG);
    if ($global_photo_client != "")
      $myForm->setFieldProperties('photo', FIELDP_IMAGE_URL, $global_photo_client);
    array_push($Order, "pp_nom", "pp_prenom", "pp_date_naissance");
    array_push($Order, "pp_lieu_naissance", "pp_nationalite", "pp_pays_naiss", "pp_sexe", "pp_type_piece_id", "pp_nm_piece_id", "pp_date_piece_id", "pp_lieu_delivrance_id", "pp_date_exp_id", "pp_etat_civil", "pp_nbre_enfant");
    if ($AGC['identification_client'] == 2){
      array_push($Order, "adresse", "code_postal", "ville", "pays","num_tel", "num_fax", "num_port", "email","province","district","secteur","cellule","village", "client_zone", "education", "classe_socio_economique");
    }else{
      array_push($Order, "adresse", "code_postal", "ville", "pays","num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "client_zone");
    }    
    
    array_push($Order, "sect_act", "pp_pm_activite_prof", "pp_fonction", "pp_employeur","pp_partenaire","categorie","classe", "langue", "pp_revenu", "pp_pm_patrimoine", "pp_casier_judiciaire", "pp_id_gi", "nb_imf", "nb_bk", "commentaires_cli","mnt_quotite","id_card");

  } else if ($CLI["statut_juridique"] == 2) { // Personne morale
    if ($AGC['identification_client'] == 2){
      array_push($Order, "pm_raison_sociale", "pm_abreviation", "adresse", "code_postal", "ville", "pays", "num_tel", "pm_tel2", "pm_tel3", "num_fax", "num_port", "email", "pm_email2","province","district","secteur","cellule","village", "client_zone");
    }else{
      array_push($Order, "pm_raison_sociale", "pm_abreviation", "adresse", "code_postal", "ville", "pays", "num_tel", "pm_tel2", "pm_tel3", "num_fax", "num_port", "email", "pm_email2", "id_loc1", "id_loc2", "loc3", "client_zone");
    }    
    
    array_push($Order, "pm_categorie", "pm_nature_juridique", "sect_act", "pp_pm_activite_prof", "pp_pm_patrimoine", "nb_imf", "nb_bk", "nbre_hommes_grp", "nbre_femmes_grp", "pm_date_notaire", "pm_date_depot_greffe", "pm_lieu_depot_greffe", "pm_date_expiration", "pm_numero_reg_nat", "pm_numero_nric", "pm_lieu_nric", "pm_date_constitution", "pm_agrement_nature", "pm_agrement_autorite", "pm_agrement_numero", "pm_agrement_date", "commentaires_cli");

  } else if ( $CLI["statut_juridique"] == 3) { // Groupe informel
    if ($AGC['identification_client'] == 2){
      array_push($Order, "gi_nom", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email","province","district","secteur","cellule","village", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nbre_hommes_grp", "nbre_femmes_grp", "nb_imf", "nb_bk", "gi_date_agre");
    }else{
      array_push($Order, "gi_nom", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nbre_hommes_grp", "nbre_femmes_grp", "nb_imf", "nb_bk", "gi_date_agre");
    }  
  } else if ( $CLI["statut_juridique"] == 4) { // Groupe solidaire
    if ($AGC['identification_client'] == 2){
      array_push($Order, "gi_nom","gs_responsable", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "province","district","secteur","cellule","village", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nb_imf", "nb_bk", "gi_date_agre", "commentaires_cli");
    }else{
      array_push($Order, "gi_nom","gs_responsable", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nb_imf", "nb_bk", "gi_date_agre", "commentaires_cli");
    }  
  }
   if (isFusion()) {
       array_push($Order, "n_agence");
       array_push($Order, "n_guichet");
   }
  if ($CLI["etat"] != 2) // Client ayant subi une défection
    array_push($Order, "raison_defection");

  $myForm->addTable("ad_cli", OPER_INCLUDE, $Order);
  if ( $CLI["statut_juridique"] == 1) {       // Personne physique
    array_push($Order, "photo");
    array_push($Order, "signature");
  }
  $myForm->setOrder(NULL, $Order);

  while (list($key, $value) = each($Order)) {
    $myForm->setFieldProperties($value, FIELDP_IS_LABEL, true);
  }

  if ( $CLI["statut_juridique"] == 1) {       // Personne physique
    array_pop($Order);
    array_pop($Order);
  }

  //Affichage des groupes solidaires
  if ($CLI["statut_juridique"] == 1 || $CLI["statut_juridique"] == 2 || $CLI["statut_juridique"] == 3) {
    $listeGroupSol=getGroupSol($global_id_client);
    if (!empty($listeGroupSol->param)) {

      //Affichage des groupes solidaires
      $myForm->addHTMLExtraCode("espace_grp_sol","<br/><p align=\"center\"><font size=\"3\"><strong>"._("Appartenance à un groupe solidaire")."<strong></font></p><br/>");
      foreach($listeGroupSol->param as $cle => $valeur) {
        $id_group=$valeur["id_grp_sol"];
        $myForm->addField("group".$id_group,_("Nom du groupe"),TYPC_TXT);
        $enregGroup=getNomGroup($id_group);
        $myForm->setFieldProperties("group".$id_group,FIELDP_DEFAULT,$enregGroup->param[0]["gi_nom"]);
        $myForm->setFieldProperties("group".$id_group,FIELDP_IS_LABEL,true);
        $myForm->addLink("group".$id_group,$id_group , _("Visualiser"), "#");
        $myForm->setLinkProperties($id_group, LINKP_JS_EVENT, array("OnClick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client_gi.php?m_agc=".$_REQUEST['m_agc']."&Recherche=$id_group&fermer=yes', '"._("Recherche")."');return false; "));

      }

    }
  }
  if ($CLI["statut_juridique"] == 4) {
    // Groupe solidaire : affichage des membres
    $myForm->addHTMLExtraCode("espace","<br/>");
    $myForm->addHTMLExtraCode("membres","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Membres du groupe")."</b></td></tr></table>\n");
    $result = getListeMembresGrpSol($CLI["id_client"]);
    $membres_grp_sol = $result->param;
    for ($i=1 ;  $i<=sizeof($membres_grp_sol) ; $i++) {
      $myForm->addField("num_client$i", _("Membre $i"), TYPC_INT);
      $myForm->setFieldProperties("num_client$i", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("num_client$i", FIELDP_DEFAULT, $membres_grp_sol[$i-1]);
    }
  }
  
   //Traitement pour les champs extras
   $objChampsExtras = new HTML_Champs_Extras ($myForm,'ad_cli',$id_cli);
   $objChampsExtras->buildChampsExtras(getChampsExtrasCLIENTValues($global_id_client),TRUE);
  
  $myForm->addFormButton(1, 1, "ok", _("Retour Menu"), TYPB_SUBMIT);
  if ($CLI['etat'] == 2 || $CLI["etat"] == 7) {
    $myForm->addFormButton(1, 2, "finances", _("Informations financières"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("finances", BUTP_PROCHAIN_ECRAN, 'Ccl-2');
  }
  if (hasFraisAttente($global_id_client)) {
    $myForm->addFormButton(1, 3, "attentes", _("Frais en attente"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("attentes", BUTP_PROCHAIN_ECRAN, 'Ccl-3');
  }

  $myForm->addFormButton(1, 4, "fiche_client", _("Impression fiche client"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("fiche_client", BUTP_PROCHAIN_ECRAN, 'Ccl-4');

  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $fill = new FILL_HTML_GEN2();
  $fill->addFillClause ("client", "ad_cli");
  $fill->addCondition("client", "id_client", $global_id_client);
  $fill->addManyFillFields("client", OPER_INCLUDE, $Order);

  $fill->fill($myForm);
  $myForm->buildHTML();
  echo $myForm->getHTML();
  $SESSION_VARS['statut_juridique'] = $CLI['statut_juridique'];

}
/*}}}*/

/*{{{ Ccl-2 : Affichage des informations financières */
else if ($global_nom_ecran == "Ccl-2") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Informations financières"));
  $ACCOUNTS = getAccountsInfoFinancier($global_id_client);
  $crePro = getCreditProductID($global_id_agence);
  $xtHTML = "<h3> "._("Comptes d'épargne"). "</h3>
            <br /><table align=\"center\" cellpadding=\"5\" width=\"95%\">
            <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("Numéro compte")."</th><th>"._("Produit")."</th><th>"._("Date ouverture")."</th><th>"._("Etat")."</th><th>"._("Devise")."</th><th>"._("Solde")."</th><th>"._("Montant minimum")."</th><th>"._("Montant bloqué")."</th><th>"._("Solde disponible")."</th></tr>";
  if (is_array($ACCOUNTS)) {
    $color = $colb_tableau;
    while (list($key, $ACC) = each($ACCOUNTS)) {
      if ($ACC['id_prod'] != $crePro) { // On n'affiche pas le compte de crédit
      	$access_solde = get_profil_acces_solde($global_id_profil, $ACC["id_prod"]);
      	$access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);

        if(manage_display_solde_access($access_solde, $access_solde_vip)) {
            $solde = $ACC['solde'];
            $soldeDisp = getSoldeDisponible($ACC["id_cpte"]);            
        }

        $mnt_min = $ACC["mnt_min_cpte"];
        $mnt_bloq = $ACC["mnt_bloq"] + $ACC["mnt_bloq_cre"];
        $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
        setMonnaieCourante($ACC['devise']);
        $prod_select = "'Produit sélectionné'";
        $IdProd = $ACC['id_prod'];
        $xtHTML .= "\n<tr bgcolor=\"$color\"><td><a href='#'";
        $xtHTML .= ' onClick = "open_compte_mvts('.$ACC['id_cpte'].',0);" >';
        $xtHTML .= $ACC['num_complet_cpte']."</a></td><td>".$ACC['libel']."</td><td>".pg2phpDate($ACC['date_ouvert'])."</td><td>".adb_gettext($adsys['adsys_etat_cpt_epargne'][$ACC['etat_cpte']])."</td><td>".$ACC['devise']."</td><td>".afficheMontant($solde)."</td><td>".afficheMontant($mnt_min)."</td><td>".afficheMontant($mnt_bloq)."</td><td><b>".afficheMontant($soldeDisp)."</b></td></tr>";
      } else {
      	unset($ACCOUNTS[$key]);
      }

    }
  }
  $xtHTML .= "</table>";
  // Liste Ordre Permanents
  $ORDRE_PERMANENTS = getOrdresPermParClientInfo($global_id_client);
  $xtHTML .= "<h3> "._("Ordres permanents de virement"). "</h3>
            <br /><table align=\"center\" cellpadding=\"5\" width=\"95%\">
            <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("Numéro Compte Destination")."</th><th>"._("Produit")."</th><th>"._("Date Ouverture")."</th><th>"._("Cotisation/Mise")."</th><th>"._("Périodicité")."</th><th>"._("Date Fin")."</th><th>"._("Solde Actuel")."</th></tr>";
  if (is_array($ORDRE_PERMANENTS)) {
    $color = $colb_tableau;
    while (list($key, $ORD) = each($ORDRE_PERMANENTS)) {
      if ($ORD['id_prod'] != $crePro) { // On n'affiche pas le compte de crédit
        $access_solde = get_profil_acces_solde($global_id_profil, $ORD["id_prod"]);
        $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);

        if(manage_display_solde_access($access_solde, $access_solde_vip)) {
          $solde_total = $ORD['solde']; // + $ORD['montant_virement'];
          $soldeDisp = getSoldeDisponible($ORD["cpt_to"]);
        }


        $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
        $xtHTML .= "\n<tr bgcolor=\"$color\"><td><a href='#'";
        $xtHTML .= ' onClick = "open_compte_mvts('.$ORD['cpt_to'].',1);" >';
        $xtHTML .= $ORD['cpte_destination']."</td><td>".$ORD['prod_libel']."</td><td>".pg2phpDate($ORD['date_ouverture'])."</td><td>".afficheMontant($ORD['montant_virement'])."</td><td>".adb_gettext($adsys["adsys_periodicite_ordre_perm"][$ORD['periodicite']])."</td><td>".pg2phpDate($ORD['date_fin'])."</td><td><b>".afficheMontant($solde_total)."</b></td></tr>";
      } else {
        unset($ORDRE_PERMANENTS[$key]);
      }

    }
  }
  $SESSION_VARS['suivi_epargne'] = $ACCOUNTS;
  $SESSION_VARS['suivi_ordre_permanent'] = $ORDRE_PERMANENTS;
  $SESSION_VARS['suivi_credit'] = array();
  $xtHTML .= "</table>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 13 OR etat = 14 OR etat = 15)"; //Ajout etat soldé pour le ticket JIRA MAE-14 //AT-115 retiré OR etat = 6 pour la recuperation des credits
  $dossiers = getIdDossier($global_id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value_doss) {

    // if ($id_doss == true) // Afficher le suivi de remboursement pour le crédit en cours
    //  {
    $PRODS = getProdInfo(" where id =".$value_doss["id_prod"], $id_doss);// Retourne les informations sur le produit sélectionné dans le crédit
    $PROD = $PRODS[0];
    $parametre=array();
    setMonnaieCourante($PROD["devise"]);

    $whereCond = "WHERE id_doss = $id_doss";
    $echeancier = getEcheancier($whereCond); // L'échéancier
    $reechMorat = getRechMorHistorique (145,$global_id_client,$value_doss["date_dem"]); //Date demande car l date  rééch > date de demande

    $cap_du =0;  //Capital dû (Cap remb. + Cap restant dû)
    $int_du =0;  //Intérêt dû
    $gar_du =0;  //Garantie dûe
    $Nbre_Ech =0; //Nbre d'échéance

    $cap_rest =0;  //Capital restant
    $int_rest =0;  //Intérêt restant
    $gar_rest =0;  //Garantie restante
    $Nbre_rest =0; //Nbre d'échéance restant
    $i=0;

    $today = (date('d/m/Y'));

    //Echéancier de remboursement
    if (is_array($reechMorat)) {
      reset($reechMorat);
      list($key,$historique) = each($reechMorat);
    }
    while (list($key,$value)=each($echeancier)) {
      $AMJ_ech = pg2phpDatebis($value["date_ech"]);//Tableau aaaa/mm/jj de la date
      $sdEch = gmmktime(0,0,0,$AMJ_ech[0],$AMJ_ech[1],$AMJ_ech[2],1);  //0 mois 1 jour 2 année


      $AMJ_his = pg2phpDatebis($historique["date"]);
      $sdhis = gmmktime(0,0,0,$AMJ_his[0],$AMJ_his[1],$AMJ_his[2],1);  //0 mois 1 jour 2 année

      if (($sdEch>=$sdhis) && ($sdhis>0) && ($sdhis!=$lasthis) && ($value_doss["cre_nbre_reech"]>0)) {  //Réechelonnement /Moratoire
        $lasthis=$sdhis;
        list($key,$historique) = each($reechMorat);
      }


      //Calcul des pénalités attendues
      /*$pen_remb='0'; //Pénalité remboursé
      $cap_remb='0'; //Capital remboursé pour l'échéance i
      $int_remb='0'; //Intérêt remboursé pour l'échéance i
      $gar_remb='0'; //Garantie remboursée pour l'échéance i
      $som_cap_remb='0';//Capital remboursé
      $som_int_remb='0';//Intérêt remboursé
      $som_gar_remb='0';//Garantie remboursée

      $REMB=getRemboursement("WHERE id_doss = ".$id_doss." AND id_ech = ".$value["id_ech"]); //Les remboursements
      // Cas particlier des crédits repris
      if ((sizeof($REMB) == 0) && ($value["remb"] == 't')) {
        $cap_remb="N/A"; // Capital remboursé
        $int_remb="N/A"; // Intérêt remboursé
        $gar_remb="N/A"; // Garanrie remboursée
        $pen_remb="N/A"; // Pénalité remboursé
      } else {
        reset($REMB); //Les remboursements
        while (list($key1,$value1)=each($REMB)) {
          $som_cap_remb +=$value1["mnt_remb_cap"]; //Somme des capitaux remboursés
          $som_int_remb +=$value1["mnt_remb_int"]; //Somme des intérêts remboursés
          $som_gar_remb +=$value1["mnt_remb_gar"]; //Somme des garanties remboursées
          if ($value1["id_ech"]==$value["id_ech"]) {
            $pen_remb +=$value1["mnt_remb_pen"]; //Pénalités remboursées pour l'échéance i
            $cap_remb +=$value1["mnt_remb_cap"]; //Capital remboursé pour l'échéance i
            $int_remb +=$value1["mnt_remb_int"]; //Intérêts remboursés pour l'échéance i
            $gar_remb +=$value1["mnt_remb_gar"]; //Garanties remboursées pour l'échéance i
          }
        }
      }*/

      if ($value["remb"]=='f') {
        $cap_rest += $value["solde_cap"];  //Capital restant à payer
        $int_rest += $value["solde_int"];  //Intérêt restant à payer
        $gar_rest += $value["solde_gar"];  //Garantie restante à payer
      }

      $cap_du = $cap_du + $value["mnt_cap"];
      $int_du = $int_du + $value["mnt_int"];
      $gar_du = $gar_du + $value["mnt_gar"];
      $Nbre_Ech=$value["id_ech"];
      if ($value["remb"]=='t') $i++;
    }
    $Nbre_rest= $Nbre_Ech-$i; //Nbre d'échéances non clôturé

    $ET = getTousEtatCredit();

    if ($value_doss["is_ligne_credit"] == 't') {
        $parametre["titre"]= _("Suivi ligne de crédit")." ".$id_doss;
        $parametre["id_doss"] = $id_doss;
        $parametre["cre_mnt_octr"] = $value_doss["cre_mnt_octr"];
        $parametre["gar_du"] = $gar_du; //$som_gar_remb+$gar_rest;
        $parametre["cap_rest"] = $cap_rest;
        $parametre["cre_mnt_deb"] = $value_doss["cre_mnt_deb"];
        $parametre["duree_nettoyage_lcr"]=$value_doss["duree_nettoyage_lcr"];

        $HTML_code = HTML_suiviCredit_lcr($parametre,null);

        $parametre["cap_du"] = (int)$value_doss["cre_mnt_deb"];
        $parametre["int_du"] = getCalculInteretsLcr($id_doss, $today, 0);
        $parametre["int_rest"] = getCalculInteretsLcr($id_doss, $today);
        $parametre["gar_rest"] = $gar_rest;
        $parametre["cre_retard_etat_max"] = $ET[$value_doss['cre_retard_etat_max']]["libel"];
        $parametre["cre_retard_etat_max_jour"] = $value_doss['cre_retard_etat_max_jour'];
    } else {

        // Appel de l'affichage de suivi du dossier de crédit
        $parametre["id_doss"] = $id_doss;
        $parametre["cap_du"] =  $cap_du;
        $parametre["int_du"] =  $int_du;
        $parametre["gar_du"] =  $gar_du;
        $parametre["Nbre_Ech"] = $Nbre_Ech;
        $parametre["Nbre_rest"] = $Nbre_rest;
        $parametre["cap_rest"] = $cap_rest;
        $parametre["int_rest"] = $int_rest;
        $parametre["gar_rest"] = $gar_rest;

        $parametre["cre_retard_etat_max"] = $ET[$value_doss['cre_retard_etat_max']]["libel"];
        $parametre["cre_retard_etat_max_jour"] = $value_doss['cre_retard_etat_max_jour'];
        if ($value_doss['etat']== 13) {
            $parametre["cre_mnt_deb"] = $value_doss['cre_mnt_deb'];
        }

        $HTML_code = "<h3>"._("Suivi du crédit");
        $HTML_code .= HTML_suiviCredit($parametre,null);
    }



    array_push($SESSION_VARS['suivi_credit'], $parametre);

    $HTML_code .= "<br/><br/>";
    // Permet d'ouvrir la page de détail du remboursement
    $JScode="";
    $JScode .="\nfunction open_remb(id_doss,id_ech)";
    $JScode .="\t{\n";
    $JScode .="\t	// Construction de l'URL : de type ./lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_ech=id\n";
    $JScode .="\t\turl = '$http_prefix/lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_doss='+id_doss+'&id_ech='+id_ech;\n";
    $JScode .="\t\tRembWindow = window.open(url, '"._("Produit sélectionné")."', 'alwaysRaised=1,dependent=1,scrollbars,resizable=0,width=650,height=400');\n";
    $JScode .="\t}\n";
    $myForm->addJS(JSP_FORM,"prodF".$id_doss,$JScode);

    $myForm->addHTMLExtraCode ("FormEcheancier".$id_doss, $HTML_code);
  }
  $myForm->addFormButton(1,1, "retour", _("Retour"), TYPB_SUBMIT);
  if ($orig == 'menu') // Si on vient du menu clientèle
    $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-8');
  else // On vient de l'écran de consultation d'un client
    $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ccl-1');
  $myForm->addFormButton(1,2, "pdf", _("Rapport PDF"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("pdf", BUTP_PROCHAIN_ECRAN, 'Ccl-5');
  $myForm->buildHTML();
  echo $myForm->getHTML();

}
/*}}}*/

/*{{{ Ccl-3 : Affichage des frais en attente */
else if ($global_nom_ecran == "Ccl-3") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Frais en attente"));

  $COMPTES = get_comptes_epargne($global_id_client);

  foreach ($COMPTES as $key => $value) {

    $result = getFraisAttente($key);
    $FRAIS = $result->param;
    if (count($FRAIS) > 0) {
      setMonnaieCourante($value['devise']);
      $table =& $myForm->addHTMLTable($key."_1", 3, TABLE_STYLE_ALTERN);
      $table->add_cell(new TABLE_cell($value['num_complet_cpte']."/".$value['devise']." ".$value['libel'], 3, 1));
      $table->add_cell(new TABLE_cell(_("Date"), 1, 1));
      $table->add_cell(new TABLE_cell(_("Type de frais"), 1, 1));
      $table->add_cell(new TABLE_cell(_("Montant"), 1, 1));
      
      foreach ($FRAIS as $key => $value) {
      
        $temp = array();
        
        
        $operationz = getOperations($value['type_frais'])->param['libel'];
       
        $libel_operationetlanguage = db_get_traductions($operationz);
   
         if ($global_langue_utilisateur ="fr_BE"){
         	 $libel_operation = $libel_operationetlanguage['fr_BE'];
         }elseif ($global_langue_utilisateur ="en_GB")
         {
         	$libel_operation = $libel_operationetlanguage['en_GB'];
         }

        $table->add_cell(new TABLE_cell(pg2phpDate($value['date_frais']), 1, 1));
        $table->add_cell(new TABLE_cell($libel_operation, 1, 1));
        $table->add_cell(new TABLE_cell(afficheMontant($value['montant']), 1, 1));

      }
      
      $myForm->addHTMLExtraCode($key."_ExtraCode", "<br>");
    }
  }

  $myForm->addFormButton(1,1, "retour", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ccl-1');

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ Ccl-4 : Impression fiche client */
else if ($global_nom_ecran == "Ccl-4") {
  $liste_criteres = array();
  $liste_criteres[_('Numéro client')] = $global_nom_ecran ;

  // Infos sur le client
  $InfoClient = getClientDatas($global_id_client);

  switch ($InfoClient['statut_juridique']) {
  case 1 :
    $xml = xml_fiche_personne_physique($InfoClient); //Génération du code XML
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'fiche_personne_physique.xslt'); //Génération du XSL-FO et du PDF
    break;
  case 2 :
    $xml = xml_fiche_personne_morale($InfoClient); //Génération du code XML
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'fiche_personne_morale.xslt'); //Génération du XSL-FO et du PDF
    break;
  case 3 :
    $xml = xml_fiche_groupe_informel($InfoClient); //Génération du code XML
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'fiche_groupe_informel.xslt'); //Génération du XSL-FO et du PDF
    break;
  case 4 :
    $xml = xml_fiche_groupe_solidaire($InfoClient); //Génération du code XML
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'fiche_groupe_solidaire.xslt'); //Génération du XSL-FO et du PDF
    break;

  }

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html("Gen-9", $fichier_pdf);

}
/*}}}*/
/*{{{ Ccl-5 : Impression situation analytique du client pour épargne et crédit */
else if ($global_nom_ecran == "Ccl-5") {
  $ACCOUNTS = getAccountsInfoFinancier($global_id_client);
  if (is_array($ACCOUNTS)) {
    while (list($key, $ACC) = each($ACCOUNTS)) {
      if ($ACC['id_prod'] = $crePro) { // On n'affiche pas le compte de crédit
        unset($ACCOUNTS[$key]);
      }
    }
  }
  $DATA = array();
  /**
   * AT-115
   * Amelioration apporté pour les crédits soldés qui devraient etre affiché dans le rapport PDF
   * La recuperation des crédits soldés doit etre fait sur cette écran au lieu de l'écran Informations Financieres
   */
  $whereCl=" AND (etat = 6)";
  $dossiers = getIdDossier($global_id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value_doss) {

    $PRODS = getProdInfo(" where id =".$value_doss["id_prod"], $id_doss);// Retourne les informations sur le produit sélectionné dans le crédit
    $PROD = $PRODS[0];
    $parametre=array();
    setMonnaieCourante($PROD["devise"]);

    $whereCond = "WHERE id_doss = $id_doss";
    $echeancier = getEcheancier($whereCond); // L'échéancier
    //$reechMorat = getRechMorHistorique (145,$global_id_client,$value_doss["date_dem"]); //Date demande car l date  rééch > date de demande

    $cap_du =0;  //Capital dû (Cap remb. + Cap restant dû)
    $int_du =0;  //Intérêt dû
    $gar_du =0;  //Garantie dûe
    $Nbre_Ech =0; //Nbre d'échéance

    $cap_rest =0;  //Capital restant
    $int_rest =0;  //Intérêt restant
    $gar_rest =0;  //Garantie restante
    $Nbre_rest =0; //Nbre d'échéance restant
    $i=0;

    $today = (date('d/m/Y'));

    //Echéancier de remboursement
    /*if (is_array($reechMorat)) {
      reset($reechMorat);
      list($key,$historique) = each($reechMorat);
    }*/
    while (list($key,$value)=each($echeancier)) {

      if ($value["remb"]=='f') {
        $cap_rest += $value["solde_cap"];  //Capital restant à payer
        $int_rest += $value["solde_int"];  //Intérêt restant à payer
        $gar_rest += $value["solde_gar"];  //Garantie restante à payer
      }

      $cap_du = $cap_du + $value["mnt_cap"];
      $int_du = $int_du + $value["mnt_int"];
      $gar_du = $gar_du + $value["mnt_gar"];
      $Nbre_Ech=$value["id_ech"];
      if ($value["remb"]=='t') $i++;
    }
    $Nbre_rest= $Nbre_Ech-$i; //Nbre d'échéances non clôturé

    //$ET = getTousEtatCredit();

    if ($value_doss["is_ligne_credit"] == 't') {
      $parametre["titre"]= _("Suivi ligne de crédit")." ".$id_doss;
      $parametre["id_doss"] = $id_doss;
      $parametre["cre_mnt_octr"] = $value_doss["cre_mnt_octr"];
      $parametre["gar_du"] = $gar_du; //$som_gar_remb+$gar_rest;
      $parametre["cap_rest"] = $cap_rest;
      $parametre["cre_mnt_deb"] = $value_doss["cre_mnt_deb"];
      $parametre["duree_nettoyage_lcr"]=$value_doss["duree_nettoyage_lcr"];

      $parametre["cap_du"] = (int)$value_doss["cre_mnt_deb"];
      $parametre["int_du"] = getCalculInteretsLcr($id_doss, $today, 0);
      $parametre["int_rest"] = getCalculInteretsLcr($id_doss, $today);
      $parametre["gar_rest"] = $gar_rest;
      $parametre["cre_retard_etat_max"] = _('Crédit Soldé');
      $parametre["cre_retard_etat_max_jour"] = $value_doss['cre_retard_etat_max_jour'];
    } else {

      // Appel de l'affichage de suivi du dossier de crédit
      $parametre["id_doss"] = $id_doss;
      $parametre["cap_du"] =  $cap_du;
      $parametre["int_du"] =  $int_du;
      $parametre["gar_du"] =  $gar_du;
      $parametre["Nbre_Ech"] = $Nbre_Ech;
      $parametre["Nbre_rest"] = $Nbre_rest;
      $parametre["cap_rest"] = $cap_rest;
      $parametre["int_rest"] = $int_rest;
      $parametre["gar_rest"] = $gar_rest;

      $parametre["cre_retard_etat_max"] = _('Crédit Soldé');
      $parametre["cre_retard_etat_max_jour"] = $value_doss['cre_retard_etat_max_jour'];
      if ($value_doss['etat']== 13) {
        $parametre["cre_mnt_deb"] = $value_doss['cre_mnt_deb'];
      }
    }

    array_push($SESSION_VARS['suivi_credit'], $parametre);
  }
  /****************************************************************************************************************/
  $DATA['credit'] = $SESSION_VARS['suivi_credit'];
  $DATA['epargne'] = $SESSION_VARS['suivi_epargne'];
  $DATA['ord_permanent'] = $SESSION_VARS['suivi_ordre_permanent'];
  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
 	$xml = xml_situation_analytique_client($DATA, null);
 	//Génération du fichier pdf
 	$fichier_pdf = xml_2_xslfo_2_pdf($xml, "situation_analytique_client.xslt");
 	//Message de confirmation + affichage du rapport dans une nouvelle fenêtre
 	echo get_show_pdf_html("Gen-8", $fichier_pdf);
 	ajout_historique(330, NULL, NULL, $global_nom_login, date("r"), NULL);

}
/*}}}*/


else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
