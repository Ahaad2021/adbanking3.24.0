<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [148] Suspension et ajustement des pénalités.
 *
 * Cette opération comprends les écrans :
 * - Rga-1 : sélection d'un dossier de crédit
 * - Rga-2 : sélection de la garantie
 * - Rga-3 : demande de confirmation réalisation de la garantie
 * - Rga-4 : confirmation de l'opération
 * @package Credit
 */

require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xml_credits.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';

/*{{{ Rga-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Rga-1") {
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

	$AG = getAgenceDatas($global_id_agence);
  if($AG['realisation_garantie_sain'] == 't'){
  	/* recupère l'id de l'état en perte */
  	$idEtatPerte = getIDEtatPerte();
  	/* Recherche des dossiers individuels déboursés ou en attente de Rééch/Moratoire ni en perte(ici on ajoute crédits sains voir #1859) */
  	$whereCl=" AND is_ligne_credit='f' or is_ligne_credit is null AND mode_calc_int!=5 AND ((etat=5) OR (etat=9) OR (etat=13)) AND cre_etat != $idEtatPerte";
  } else {
  	/* Recherche des dossiers individuels déboursés ou en attente de Rééch/Moratoire ni sain ni en perte */
        $whereCl=" AND is_ligne_credit='f' or is_ligne_credit is null AND mode_calc_int!=5 AND ((etat=5) OR (etat=9) OR (etat=13)) AND cre_etat > 1";
  }

  $dossiers_reels = getIdDossier($global_id_client, $whereCl);

  if (is_array($dossiers_reels)){
  	foreach($dossiers_reels as $id_doss=>$value){
  		if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe
	      // Liste des garanties du dossier
	      $liste_gars = getListeGaranties($id_doss);
	      
	      //verifier que le dossier à une garantie encours 
 	      if ($value['cpte_gar_encours'] >0 AND $value['gar_num_encours']>0 ) {
 	 
 	      } 
	      	     
	 	    $date = pg2phpDate($value["date_dem"]); // Fonction renvoie des dates au format jj/mm/aaaa 
	 	    $liste[$i] ="n° $id_doss du $date"; // Construit la liste en affichant N° dossier + date 
	
	      // Récupérer le dossier si au moins une garantie numéraire ou matérielle est à l'état 'Mobilisée'
	      foreach($liste_gars as $cle=>$valeur){
	      	if ( ( $valeur['etat_gar'] == 3) OR  //garantie mobilisée 
 	           ( $value['gar_num_encours']>0 AND$value['cpt_gar_encours'] == $valeur['gar_num_id_cpte_nantie']) ) {//garantie numaire à constituer 
 	            $dossiers[$i] = $value; 
 	            $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t"; 
 	            $codejs .= "{\n\t\tdocument.ADForm.id_prod.value ='" . $value["libelle"] . "';"; 
 	            $codejs .= "}\n"; 
 	            $i++; 
 	        } 
	      }      
	    }
    }
 }

    

  // SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
    // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
    $whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
    $dossiers_fictifs = getCreditFictif($whereCl);

    // Récupération des dossiers des membres
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    // Ajout dans la liste box, pour chaque dossier fictif du GS, les dossiers réels des membres du GS
    foreach($dossiers_fictifs as $id_fictif=>$dossier_fictif)
    {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss => $dossier_membre)
      {
        if(($dossier_membre['is_ligne_credit'] != 't') AND ($dossier_membre['id_dcr_grp_sol'] == $id_fictif)
            AND ($dossier_membre['etat'] == 5 or $dossier_membre['etat'] == 9 or $dossier_membre['etat'] == 13 )
            AND ( ($AG['realisation_garantie_sain'] == 't' AND  $dossier_membre['cre_etat'] != $idEtatPerte )
                OR ($AG['realisation_garantie_sain'] == 'f' AND  $dossier_membre['cre_etat'] > 1)
                )
        )
        {
            // Liste des garanties du dossier
            $liste_gars = getListeGaranties($id_doss);

            // Récupérer le dossier si au moins une garantie numéraire ou matérielle est à l'état 'Mobilisée'
            foreach($liste_gars as $id_gar=>$garantie)
            {
              if (($garantie['etat_gar'] == 3) OR  //garantie mobilisée
                   ($dossier_membre['gar_num_encours'] > 0 AND $dossier_membre['cpt_gar_encours'] == $garantie['gar_num_id_cpte_nantie']) )  //garantie numéraire à constituer
              {
                    $date_dem = $date = pg2phpDate($dossier_membre['date_dem']);
                    $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
              }
            }
        }
      }

      if ($infos != '') { // Si au moins on 1 dossier
        $infos .= "du $date_dem";
        $liste[$i] = $infos;
        $dossiers[$i] = $dossier_fictif; // on garde les infos du dossier fictif

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $dossier_membre["libelle"] . "\";";
        $codejs .= "}\n";
        $i++;
      }
    }
  }

  $SESSION_VARS['dossiers'] = $dossiers;

  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
  $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
  $codejs .= "\n\t}\n";
  $codejs .= "}\ngetInfoDossier();";

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
  $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
  $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);

  $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
  $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

  $Myform->setFieldProperties("id_doss",FIELDP_ADD_CHOICES,$liste);
  $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange"=>"getInfoDossier();"));
  $Myform->addJS(JSP_FORM, "JS3", $codejs);

  // Javascript : vérifie qu'un dossier est sélectionné
  $JS_1 = "";
  $JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné")." .\\n';ADFormValid=false;}\n";
  $Myform->addJS(JSP_BEGIN_CHECK,"testdos",$JS_1);

  // Ordre d'affichage des champs
  $order = array("id_doss","id_prod");

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rga-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Rga-2 : Sélection de la garantie */
elseif($global_nom_ecran == "Rga-2") {
  // Si on vient de Rga-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"Rga-1")) {
    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
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
      // infos dossier fictif
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];  // id du dossier fictif (dossier du groupe)
      $whereCond = " WHERE id = $id_doss_fic";
      $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);

      foreach($dossiers_membre as $id_doss=>$val) {
        if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==5 or $val['etat']==9)
            and $val['cre_etat'] > 1) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

    /* Récupération des garanties déjà mobilisées pour ce dossier */
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$infos_doss) {
      $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = array();
      $liste_gar = getListeGaranties($id_doss);
      foreach($liste_gar as $key=>$value ) {
        $num = count($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']) + 1; // indice du tableau
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type'] = $value['type_gar'];
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

        /* Les garanties doivent être à l'état 'Prête' ou mobilisé  au moment de l'approbation  */
        if ($value['etat_gar'] !=2  and $value['etat_gar'] != 3)
          $gar_pretes = false;

        /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
        if ($value['type_gar'] == 1) /* Garantie numéraire */
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
        elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { /* garantie matérielle */
          $id_bien = $value['gar_mat_id_bien'];
          $infos_bien = getInfosBien($id_bien);
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
        }
      } /* Fin foreach garantie */
    } /* Fin foreach infos dossiers */

    // Les informations sur le produit de crédit
    $Produit = getProdInfo(" where id =".$id_prod, $id_doss);
    $SESSION_VARS['infos_prod'] = $Produit[0];

    // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
    $SESSION_VARS['utilisateurs'] = array();
    $utilisateurs = getUtilisateurs();
    foreach($utilisateurs as $id_uti=>$val_uti)
      $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
    //Tri par ordre alphabétique des utilisateurs
    natcasesort($SESSION_VARS['utilisateurs']);
    // Objet demande de crédit
    $SESSION_VARS['obj_dem'] = getObjetsCredit();
  } //fin si on vient de Rga-1

  // Gestion de la devise
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
  $id_prod  = $SESSION_VARS['infos_prod']['id'];

  // Création formulaire
  $Myform = new HTML_GEN2(_("Sélection de la garantie"));

  // Infos sur les crédits
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $whereCond = "WHERE id_doss = $id_doss";
    $SESSION_VARS['infos_doss'][$id_doss]['echeancier'] = getEcheancier($whereCond); // L'échéancier

    $SESSION_VARS['infos_doss'][$id_doss]['cap_rest'] =0;  //Capital restant
    $SESSION_VARS['infos_doss'][$id_doss]['int_rest'] =0;  //Intérêts restants
    $SESSION_VARS['infos_doss'][$id_doss]['gar_rest'] =0;  //Garanties restantes
    $SESSION_VARS['infos_doss'][$id_doss]['pen_rest'] =0;  //Pénalités restantes
    $SESSION_VARS['infos_doss'][$id_doss]['nbr_ech_rest'] =0; //Nbre d'échéance restant

    while (list($key,$value) = each($SESSION_VARS['infos_doss'][$id_doss]['echeancier'])) {
      if ($value["remb"]=='f') {
        $SESSION_VARS['infos_doss'][$id_doss]['cap_rest'] += $value["solde_cap"];  //Capital restant à payer
        $SESSION_VARS['infos_doss'][$id_doss]['int_rest'] += $value["solde_int"];  //Intérêts restant à payer
        $SESSION_VARS['infos_doss'][$id_doss]['gar_rest'] += $value["solde_gar"];  //Garanties restant à payer
        $SESSION_VARS['infos_doss'][$id_doss]['pen_rest'] += $value["solde_pen"];  //Pénalités restant à payer
        $SESSION_VARS['infos_doss'][$id_doss]['nbr_ech_rest']++;
      }
    }

    $Myform->addHTMLExtraCode("espace".$id_doss,"<br />");
    $Myform->addHTMLExtraCode("cred.$id_doss","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>".sprintf(_("Informations sur le Crédit du dossier N° %s"),$id_doss)."</b></td></tr></table>\n");

    $Myform->addField("cap_rest".$id_doss,_("Capital restant"), TYPC_TXT);
    $Myform->setFieldProperties("cap_rest".$id_doss, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("cap_rest".$id_doss,FIELDP_DEFAULT, afficheMontant($SESSION_VARS['infos_doss'][$id_doss]['cap_rest'], true));

    $Myform->addField("int_rest".$id_doss,_("Intérêts restants"), TYPC_TXT);
    $Myform->setFieldProperties("int_rest".$id_doss, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("int_rest".$id_doss,FIELDP_DEFAULT, afficheMontant($SESSION_VARS['infos_doss'][$id_doss]['int_rest'], true));

    $Myform->addField("gar_rest".$id_doss,_("Garanties restantes"), TYPC_TXT);
    $Myform->setFieldProperties("gar_rest".$id_doss, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("gar_rest".$id_doss,FIELDP_DEFAULT, afficheMontant($SESSION_VARS['infos_doss'][$id_doss]['gar_rest'], true));

    $Myform->addField("pen_rest".$id_doss,_("Pénalités restantes"), TYPC_TXT);
    $Myform->setFieldProperties("pen_rest".$id_doss, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("pen_rest".$id_doss,FIELDP_DEFAULT, afficheMontant($SESSION_VARS['infos_doss'][$id_doss]['pen_rest'], true));

    $Myform->addField("nbr_ech_rest".$id_doss,_("Le nombre d'échéances non remboursés"), TYPC_TXT);
    $Myform->setFieldProperties("nbr_ech_rest".$id_doss, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("nbr_ech_rest".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['nbr_ech_rest'], true);

    ////$Myform->addHTMLExtraCode("espace","<br />");
    $Myform->addHTMLExtraCode("gar".$id_doss,"<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Informations sur les garanties réalisables")."</b></td></tr></table>\n");

    /* Récupération des infos sur les garantie du crédit */
    $liste_gars = getListeGaranties($id_doss);

    /* Creation d'un tableau contenant toutes les garanties du dossier de crédit */
    $xtHTML = "<table align=\"center\">";

    /* En-tête tableau :  Type | Description du bien ou compte de garantie | Valeur | Devise | Etat | Lien realiser */
    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
    $xtHTML .= "<td><b>"._("Type")."</b></td>";
    $xtHTML .= "<td><b>"._("Description/compte de garantie")." </b></td>";
    $xtHTML .= "<td><b>"._("Valeur")."</b></td>";
    $xtHTML .= "<td><b>"._("Devise")."</b></td>";
    $xtHTML .= "<td><b>"._("Etat")."</b></td>";
    $xtHTML .= "<td>&nbsp</td>";
    $xtHTML .= "</tr>";

    /* Contenu du tableau */
    foreach($liste_gars as $key=>$value) {
      /* Seules les garanties dont l'état est à 'Mobilisé' peuvent être réalisées */

      /* Si garanties numéraires alors récupérer le num complet et le solde du compte de garantie */
      if (( $value['type_gar'] == 1 and $value['etat_gar'] == 3 AND  $value['gar_num_id_cpte_nantie'] != '' and $value['gar_num_id_cpte_prelev'] !='')
      ) {
        $CPT_PRELEV_GAR = getAccountDatas($value['gar_num_id_cpte_nantie']);
        $origine = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;
        $mnt_vente = $CPT_PRELEV_GAR["solde"];

        $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type_gar']])."</td>";
        $xtHTML .= "<td>".$description."</td>";
        $xtHTML .= "<td>".afficheMontant($mnt_vente, true)."</td>";
        $xtHTML .= "<td>".$value['devise_vente']."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat_gar']])."</td>";
        $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rga-3&num_gar=".$key."&type_gar=".$value['type_gar']."&mnt_gar=".$value['montant_vente']."&is_gar_constituee=true\">"._("Réaliser")."</A></td>";
        $xtHTML .= "</TR> ";
      }
      elseif( $value['type_gar'] == 2 and  $value['etat_gar'] == 3 AND $value['gar_mat_id_bien'] != '') {
        $id_bien = $value['gar_mat_id_bien'];
        $infos_bien = getInfosBien($id_bien);
        $origine = $infos_bien['description'];
        $mnt_vente = $value['montant_vente'];

        $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type_gar']])."</td>";
        $xtHTML .= "<td>".$origine."</td>";
        $xtHTML .= "<td>".afficheMontant($mnt_vente, true)."</td>";
        $xtHTML .= "<td>".$value['devise_vente']."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat_gar']])."</td>";
        $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rga-3&num_gar=".$key."&type_gar=".$value['type_gar']."&mnt_gar=".$value['montant_vente']."\">Réaliser</A></td>";
        $xtHTML .= "</tr>";
      }
      elseif( $SESSION_VARS['infos_doss'][$id_doss]['gar_num_encours']>0 AND $SESSION_VARS['infos_doss'][$id_doss]['cpt_gar_encours'] == $value['gar_num_id_cpte_nantie']){

        $CPT_PRELEV_GAR = getAccountDatas($value['gar_num_id_cpte_nantie']);

        $mnt_vente = $CPT_PRELEV_GAR["solde"];
        if ($mnt_vente > 0 ) {
          $description=_(" Garantie constituée en cours");
          $origine = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;
          $is_gar_constituee=true;

          $xtHTML .= "\n<TR bgcolor=\"$colb_tableau\">";
          $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type_gar']])."</td>";
          $xtHTML .= "<td>".$description."</td>";
          $xtHTML .= "<td> ".afficheMontant($mnt_vente, true)."</td>";
          $xtHTML .= "<td>".$value['devise_vente']."</td>";
          $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat_gar']])."</td>";
          $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rga-3&num_gar=".$key."&type_gar=".$value['type_gar']."&mnt_gar=".$mnt_vente."&is_gar_constituee=true\">"._("Réaliser")."</A></td>";
          $xtHTML .= "</TR> ";
        }
      }

    }

    /* Fin tableau */
    $xtHTML .= "</table><br><br>";
  }

  /* Les  boutons  */
  $Myform->addFormButton(1,1,"retour",_("Retour Menu"),TYPB_SUBMIT);

  /* Propriétés des boutons */
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  //$Myform->setOrder(NULL,$order);
  $Myform->addHTMLExtraCode ("garanties", $xtHTML);
  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Rga-3 : Demande de confirmation réalisation de la garantie */
elseif($global_nom_ecran == "Rga-3") {
  $Myform = new HTML_GEN2(_("Confirmation de la Réalisation de la garantie"));

  /* Récupération du numéro de la garantie */
  $SESSION_VARS['id_gar'] = $num_gar;
  $SESSION_VARS['type_gar'] = $type_gar;

  /* Devise courante : devise du produit de crédit associé */
  setMonnaieCourante($SESSION_VARS["devise"]);

  /* Infos sur la garantie à réaliser  */
  if ($type_gar == 1) {
    /* Champs à afficher */
    $includeFields = array("type_gar","gar_num_id_cpte_nantie","etat_gar","montant_vente","devise_vente");
    if($is_gar_constituee) {
    	/* Ajout d'un champ pour le solde réel de la garantie constituée */
	    $Myform->addField("solde_constitue", _("Solde Constitué"), TYPC_MNT);
	    $Myform->setFieldProperties("solde_constitue",FIELDP_DEFAULT, $mnt_gar);
	    $Myform->setFieldProperties("solde_constitue",FIELDP_IS_LABEL, true);
	     /* Ordre d'affichage des champs */
      $order = array("type_gar","gar_num_id_cpte_nantie","etat_gar","montant_vente","solde_constitue","devise_vente");
    } else {
    	 /* Ordre d'affichage des champs */
    	 $order = array("type_gar","gar_num_id_cpte_nantie","etat_gar","montant_vente","devise_vente");
    }
  }
  elseif($type_gar == 2) {
    /* Champs à afficher */
    $includeFields = array("type_gar","gar_mat_id_bien","etat_gar","montant_vente","devise_vente");

    /* Ajout d'un champ pour la valeur réelle du matéril */
    $Myform->addField("montant_net", _("Montant net du matériel"), TYPC_MNT);
    $Myform->setFieldProperties("montant_net",FIELDP_DEFAULT, $mnt_gar);
    $Myform->setFieldProperties("montant_net",FIELDP_IS_REQUIRED, true);

    /* Ordre d'affichage des champs */
    $order = array("type_gar","gar_mat_id_bien","etat_gar","montant_vente","montant_net","devise_vente");
  }

  $Myform->addTable("ad_gar", OPER_INCLUDE, $includeFields);
  $defaultVal = new FILL_HTML_GEN2();
  $defaultVal->addFillClause("id_gar","ad_gar");
  $defaultVal->addCondition("id_gar","id_gar", $num_gar);
  $defaultVal->addManyFillFields("id_gar", OPER_INCLUDE, $includeFields);
  $defaultVal->fill($Myform);

  /* Griser les champs */
  foreach($includeFields as $key=>$value)
  $Myform->setFieldProperties($value, FIELDP_IS_LABEL, true);

  /* Les  boutons  */
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  /* Propriétés des boutons */
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Rga-2");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rga-4");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Rga-4 : Confirmation de l'opération */
elseif($global_nom_ecran == "Rga-4") { /* */
  /* Devise courante : devise du produit de crédit associé */
  setMonnaieCourante($SESSION_VARS['infos_prod']["devise"]);

  /* Réalisation de la gaarantie */
  if ($SESSION_VARS['type_gar'] == 1)
    $myErr = realiseGarantie($SESSION_VARS['id_gar'], NULL);
  else if ($SESSION_VARS['type_gar'] == 2)
    $myErr = realiseGarantie($SESSION_VARS['id_gar'], recupMontant($montant_net));

  if ($myErr->errCode == NO_ERR) { /* La garantie a été réalisée avec succès */
    $MyPage = new HTML_message(_("Réalisation de la garantie"));
    $MyPage->setMessage(_("La garantie a été réalisée avec succès"));
    $MyPage->addButton(BUTTON_OK, "Gen-11");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Echec de la réalisation. "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Rga-2');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>