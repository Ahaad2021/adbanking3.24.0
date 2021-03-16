<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [607] Remboursement d'un dossier de crédit
 *
 * Cette opération comprends les écrans :
 * - LRcr-1 : sélection d'un dossier de crédit
 * - LRcr-2 : choix mode de remboursement
 * - LRcr-3 : saisie montant du remboursement
 * - LRcr-4 : confirmation montant du remboursement
 * - LRcr-5 : remboursement d'une échéance
 * - LRcr-6 : confirmation
 * @package Credit
 */

require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xml_credits.php';

/*{{{ LRcr-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "LRcr-1") {
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);
  $dossiers = array(); // Tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Listbox des dossiers à afficher
  $i = 1; // Clé de la liste box

  // En fonction du choix du numéro de dossier (id_doss=id_client) , afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état déboursé, en perte ou en déboursement progressif
  $whereCl = " AND is_ligne_credit='t' AND mode_calc_int=5 AND (etat=5 or etat=9)";
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
    $whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
    $dossiers_fictifs = getCreditFictif($whereCl);

    // Récupération des dossiers des membres
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    // Ajout dans la liste box, pour chaque dossier fictif du GS, les dossiers réels des membres du GS
    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val) {
        if (($val['is_ligne_credit'] == 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 5)) {
          $date_dem = $date = pg2phpDate($val['date_dem']);
          $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
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

  $SESSION_VARS['dossiers'] = $dossiers;
  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
  $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
  $codejs .= "\n\t}\n";
  $codejs .= "}\ngetInfoDossier();";

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
  $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
  $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);
  $Myform->addField("rembour_credit",_("Crédits multiples Sains et en Souffrances"), TYPC_BOL);

  $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("rembour_credit", FIELDP_DEFAULT, true);

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
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LRcr-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ LRcr-2 : Choix mode de remboursemnt */
else if ($global_nom_ecran == "LRcr-2") {
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
  $infos_dossier = getIdDossier($global_id_client,$whereCl_dossier);
  $etat = $infos_dossier[$SESSION_VARS["id_doss"]]['etat'];
  $gs_cat = $infos_dossier[$SESSION_VARS["id_doss"]]['gs_cat'];


  //si le crédit n'est pas en perte
  if ((($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2) and ($etat !=9)) or (($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2)  and $HTML_GEN_BOL_rembour_credit==true)) {
    // Si on vient de LRcr-1, on récupère les infos de la BD
    if (strstr($global_nom_ecran_prec,"LRcr-1")) {
      // Récupération des dossiers à approuver
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
            if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==5)) {
              $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
              $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
              $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
              $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);
              $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
            }
          }
        }
      }
      $dossier_multiple_encours=0;
      if (sizeof($SESSION_VARS['infos_doss']) >0) {
        $dossier_multiple_encours=1;
        $SESSION_VARS['gar_num_mob'] = array();
        // Les informations sur le produit de crédit
        $Produit = getProdInfo(" where mode_calc_int=5 AND id=".$id_prod, $id_doss);
        $SESSION_VARS['infos_prod'] = $Produit[0];
        $remb_cpt_gar = true; //il sera possible de rembourser par compte de garantie que si tous les dossiers ont des garanties mobilisées
        /* Récupération des garanties déjà mobilisées pour ce dossier */
        foreach($SESSION_VARS['infos_doss'] as $id_doss=>$infos_doss) {
            $gar_num_mob = getGarantieNumMob($id_doss);
            if(($gar_num_mob != NULL) and ($SESSION_VARS['infos_prod']["remb_cpt_gar"]) == 't') {
                $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = $gar_num_mob;
            } else{ 
                $remb_cpt_gar = false;
            }
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = array();
          $liste_gar = getListeGaranties($id_doss);
          foreach($liste_gar as $key=>$value ) {
            $num = count($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']) + 1; // indice du tableau
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type'] = $value['type_gar'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['etat'] = $value['etat_gar'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['gar_num_id_cpte_nantie'] = $value['gar_num_id_cpte_nantie'];
            /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
            if ($value['type_gar'] == 1) { /* Garantie numéraire */
              $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
            }
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

        // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
        $SESSION_VARS['utilisateurs'] = array();
        $utilisateurs = getUtilisateurs();

        foreach($utilisateurs as $id_uti=>$val_uti) {
          $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
        }

        //Tri par ordre alphabétique des utilisateurs
        natcasesort($SESSION_VARS['utilisateurs']);

        // Objet demande de crédit
        $SESSION_VARS['obj_dem'] = getObjetsCredit();
      }
    } //fin si on vient de Apd-1
    if ($dossier_multiple_encours==1) {
      // Gestion de la devise
      setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
      $id_prod  = $SESSION_VARS['infos_prod']['id'];

      // Création du formulaire pour dossier unique
      $Myform = new HTML_GEN2(); // _("Sélection du mode de remboursement")

      /*
      $Myform->addField("mode",_("Mode de remboursement"), TYPC_LSB);
      $Myform->setFieldProperties("mode",FIELDP_ADD_CHOICES, array(2 => _("Remboursement d'un montant"))); // 1=> _("Remboursement d'une échéance"),
      $Myform->setFieldProperties("mode", FIELDP_IS_REQUIRED, true);
      */
      $Myform->addHiddenType("mode", 2);
      
      /*
      $Myform->addField("source",_("Origine du remboursement"), TYPC_LSB);
    
      $source = array();
      //if (profil_has_guichet($global_id_profil)) {
      //  $source = $source + array(1 => _("Guichet"));
      //}

      $source = $source + array(2 => _("Compte lié"));

      if($remb_cpt_gar ){
        // remboursement par compte de garantie possible que s'il ya une garantie numéraire mobiblisée pour tous les dossier
        //$source = $source + array(3 => _("Compte de garantie"));
      }

      $Myform->setFieldProperties("source", FIELDP_ADD_CHOICES, $source);
      $Myform->setFieldProperties("source", FIELDP_IS_REQUIRED, true);
      */
      $Myform->addHiddenType("source", 2);
      
      $codeJs = "window.onload=function(){\n\n
            function submitform(){\n
              document.ADForm.prochain_ecran.value=\"LRcr-3\";\n
              document.ADForm.m_agc.value=\"".$_REQUEST['m_agc']."\";\n
              document.forms[0].submit();\n
            }\n\n
            var auto = setTimeout(function(){ submitform(); });\n
        }\n";
      $Myform->addJS(JSP_FORM, "JS_post", $codeJs);

      // les boutons ajoutés
      //$Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
      //$Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

      // Propriétés des boutons
      //$Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
      //$Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LRcr-3");
      //$Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    } else {
      $html_err = new HTML_erreur(_("Dossier en perte.")." ");
      $html_err->setMessage(sprintf(_("Veuillez décocher: <BR> Crédits mutilples Sains et en Souffrances")));
      $html_err->addButton("BUTTON_OK", 'LRcr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      die();
    }
    $Myform->buildHTML();
    echo $Myform->getHTML();
  }

  //si le crédit est passé  à l'etat en perte
  if ((($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2) and ($infos_dossier[$SESSION_VARS["id_doss"]]['etat'] ==9)) or (($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2)  and $HTML_GEN_BOL_rembour_credit==false)) {
    // Récupération des dossiers
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2) { // dossier individuel
      // Les informations sur le dossier
      $SESSION_VARS['gs_cat'] = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'];
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
      $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);
      $SESSION_VARS['id_doss']=$id_doss;
      // Infos dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
        $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
        $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);
      }
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
      // infos dossier fictif
      $SESSION_VARS['gs_cat'] =2;
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];  // id du dossier fictif (dossier du groupe)
      $whereCond = " WHERE id = $id_doss_fic";
      $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);
      foreach($dossiers_membre as $id_doss=>$val) {
        if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==9)) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

    $MyPage = new HTML_GEN2(_("Remboursement crédit en perte"));
    if (sizeof($SESSION_VARS['infos_doss'])>0) {
      $mnt_total_du = 0;
      foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
        $mnt = 0;
        $whereCond="WHERE (remb='f') AND (id_doss=$id_doss )";
        $echeance = getEcheancier($whereCond);

        //Tableau des echéances
        $nom_cli = getClientName($val_doss['id_client']);
        $MyPage->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Remboursement du dossier %s de %s"),$id_dossier,$nom_cli)."</b></p>");
        $color = $colb_tableau;
        $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
        $retour .= "<tr bgcolor=\"$color\">\n";
        $retour .= "<td colspan=7 align=\"left\"><b>"._("Solde restant dû pour échéances non remboursées")."</b></td>\n";
        $retour .= "</tr>\n";
        $retour .= "<tr bgcolor=\"$colb_tableau\">\n";
        $retour .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
        $retour .= "<TD align=\"center\">"._("Date")."</TD>\n";
        $retour .= "<TD align=\"center\">"._("Capital restant du")."</TD>\n";
        $retour .= "<TD align=\"center\">"._("Frais restants dus")."</TD>\n";
        $retour .= "<TD align=\"center\">"._("Intérêts restants dus")."</TD>\n";
        $retour .= "<TD align=\"center\">"._("Pénalités dues")."</TD>\n";
        $retour .= "<TD align=\"center\">"._("Total du")."</TD>\n";
        $retour .= "</tr>\n";

        // Affichage
        $total_cap=0;
        $total_frais=getCalculFraisLcr($id_doss, php2pg((date("d/m/Y"))));
        $total_int=0;
        $total_pen=0;
        $infoEch=array();

        if (is_array($echeance)) {
          while (list($key,$info) = each($echeance)) {
            $total_cap +=$info["solde_cap"]; //Somme du capital dû
            $total_int +=$info["solde_int"]; //Somme des intérêts dûs
            $total_pen +=$info["solde_pen"]; //Somme des pénalités dûes

            array_push($infoEch,$info["solde_cap"]+$total_frais+$info["solde_int"]+$info["solde_pen"]+$info["solde_gar"]); //Montant par échéance

            $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);
            $retour .= "<TR bgcolor=\"$color\">\n";
            $retour .= "<TD align=\"center\">".$info["id_ech"]."</TD>\n";
            $retour .= "<TD align=\"left\">".pg2phpDate($info["date_ech"])."</TD>\n";
            $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_cap"],false)."</TD>\n";
            $retour .= "<TD align=\"right\">".afficheMontant ($total_frais,false)."</TD>\n";
            
            $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_int"],false)."</TD>\n";
            $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_pen"],false)."</TD>\n";
            $retour .= "<TD align=\"right\">".afficheMontant (($info["solde_cap"]+$total_frais+$info["solde_pen"]+$info["solde_int"]),false)."</TD>\n";
            $retour .= "</TR>\n";
          }
        }

        $mnt_total_du = $total_cap + $total_frais + $total_int + $total_pen ;
        $SESSION_VARS["infoEch"][$id_doss] = $infoEch;

        $retour .= "</TABLE>\n";
        $MyPage->addHTMLExtraCode("ech1".$id_doss,$retour);
        $MyPage->setHTMLExtraCodeProperties("ech1".$id_doss, HTMP_IN_TABLE, true);

      }

      $etat = $infos_dossier[$SESSION_VARS["id_doss"]]['etat'];
      $JS="";
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2) {
        $solde_capital = getSoldeCapital($SESSION_VARS["id_doss"]);
        //$int_pen = getRetardInteretGar($SESSION_VARS["id_doss"]);
        $mnt_credit = $mnt_total_du;
        $cpt_liaison = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['cpt_liaison'];
        $CPT_LIAISON = getAccountDatas($cpt_liaison);
        setMonnaieCourante($CPT_LIAISON['devise']);
        $MyPage->addField("mnt_cap", _("Montant du crédit"), TYPC_MNT);
        $MyPage->setFieldProperties("mnt_cap", FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties("mnt_cap", FIELDP_DEFAULT, $mnt_credit);
        $MyPage->addField("solde_disp", _("Solde du compte de liaison"), TYPC_MNT);
        $MyPage->setFieldProperties("solde_disp", FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties("solde_disp", FIELDP_DEFAULT, getSoldeDisponible($cpt_liaison));
        $MyPage->addField("mnt_remb", _("Montant du remboursement"), TYPC_MNT);
        $MyPage->setFieldProperties("mnt_remb", FIELDP_IS_REQUIRED, true);
        $JS .=  "if (recupMontant(document.ADForm.mnt_remb.value) > recupMontant(document.ADForm.solde_disp.value))\n";
        $JS .= "{\n";
        $JS .= "  ADFormValid = false;\n";
        $JS .= "  msg += '- "._("Le montant à rembourser dépasse le solde disponible sur le compte de liaison")."\\n';\n";
        $JS .= "}\n";
        $JS .= "if (recupMontant(document.ADForm.mnt_remb.value) > recupMontant(document.ADForm.mnt_cap.value))\n";
        $JS .= "{\n";
        $JS .= "  ADFormValid = false;\n";
        $JS .= "  msg += '- "._("Le montant à rembourser dépasse le solde restant dû pour ce crédit")."\\n';\n";
        $JS .= "}\n";
      } else {
        $solde = 0;
        $SESSION_VARS['mnt_credit'] = 0;
        foreach($dossiers_membre as $id_doss=>$val) {
          if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==9))         {
            $i = $val['id_doss'];
            $solde_capital = getSoldeCapital($val['id_doss']);
            $whereCond="WHERE (remb='f') AND (id_doss=$id_doss )";
            $echeance = getEcheancier($whereCond);
            $int_total = 0;
            $pen_total = 0;
            foreach($echeance as $ech=>$value) {
              $int_total += $value["solde_int"];
              $pen_total += $value["solde_pen"];
            }
            $mnt_credit = $solde_capital + $int_total + $pen_total;
            $SESSION_VARS['mnt_credit'] += $mnt_credit;
            $cpt_liaison = $val['cpt_liaison'];
            $CPT_LIAISON = getAccountDatas($cpt_liaison);
            setMonnaieCourante($CPT_LIAISON['devise']);
            $MyPage->addField("mnt_cre$i", _("Montant du crédit"), TYPC_MNT);
            $MyPage->setFieldProperties("mnt_cre$i", FIELDP_IS_LABEL, true);
            $MyPage->setFieldProperties("mnt_cre$i", FIELDP_DEFAULT, $mnt_credit);
            $MyPage->addField("solde_disp$i", _("Solde du compte de liaison"), TYPC_MNT);
            $MyPage->setFieldProperties("solde_disp$i", FIELDP_IS_LABEL, true);
            $MyPage->setFieldProperties("solde_disp$i", FIELDP_DEFAULT, getSoldeDisponible($cpt_liaison));
            $MyPage->addField("mnt_remb$i", _("Montant du remboursement"), TYPC_MNT);
            $MyPage->setFieldProperties("mnt_remb$i", FIELDP_IS_REQUIRED, true);
            //Javascript pour controle du montant
            $JS .=  "if (recupMontant(document.ADForm.mnt_remb".$i.".value) > recupMontant(document.ADForm.solde_disp".$i.".value))\n";
            $JS .= "{\n";
            $JS .= "  ADFormValid = false;\n";
            $JS .= "  msg += '- "._("Le montant à rembourser dépasse le solde disponible sur le compte de liaison")."\\n';\n";
            $JS .= "}\n";
            $JS .= "if (recupMontant(document.ADForm.mnt_remb".$i.".value) > recupMontant(document.ADForm.mnt_cre".$i.".value))\n";
            $JS .= "{\n";
            $JS .= "  ADFormValid = false;\n";
            $JS .= "  msg += '- "._("Le montant à rembourser dépasse le solde restant dû pour ce crédit")."\\n';\n";
            $JS .= "}\n";
          }
        }
      }

      $MyPage->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
      $MyPage->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
      //controle pour envoyer le formulaire
      $SESSION_VARS['envoi'] = 0 ; 
      $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LRcr-6");
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    } else {
      $html_err = new HTML_erreur(_("Dossiers en perte")." ");
      $html_err->setMessage(sprintf(_("Il n'y a pas de dossier en perte dans la sélection")));
      $html_err->addButton("BUTTON_OK", 'Lcr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      die();
    }

    $MyPage->addJS(JSP_BEGIN_CHECK, "js", $JS);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }// crédit à l'état perte

}

/*}}}*/

/*{{{ LRcr-3 : Saisie montant du remboursement */
else if ($global_nom_ecran == "LRcr-3") {
  $SESSION_VARS["mode"] = $mode;
  $SESSION_VARS["source"] = $source;

  if($mode == 2) { // Remboursement d'un montant
    $MyPage = new HTML_GEN2(_("Remboursement crédit"));

    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
      $mnt = 0;
      $whereCond="WHERE (remb='f') AND (id_doss='".$id_doss."')";
      $echeance = getEcheancier($whereCond);

      //Tableau des echéances
      $nom_cli = getClientName($val_doss['id_client']);
     
      $MyPage->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Remboursement du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
      $color = $colb_tableau;
      $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
      $retour .= "<TR bgcolor=\"$color\">\n";
      $retour .= "<TD colspan=8 align=\"left\"><b>"._("Solde restant dû pour échéances non remboursées")."</b></TD>\n";
      $retour .= "</TR>\n";
      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
      $retour .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Date")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Capital restant du")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Frais restants dus")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Intérêts restants dus")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Garantie restante due")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Pénalités dues")."</TD>\n";
      $retour .= "<TD align=\"center\">"._("Total du")."</TD>\n";
      $retour .= "</TR>\n";

      // Affichage
      $total_cap=0;
      $total_frais=getCalculFraisLcr($id_doss, php2pg((date("d/m/Y"))));
      $total_int=getCalculInteretsLcr($id_doss, php2pg((date("d/m/Y"))));
      $total_pen=0;
      $total_gar=0;
      $infoEch=array();

      if (is_array($echeance)) {
        while (list($key,$info) = each($echeance)) {
          $total_cap +=$info["solde_cap"]; //Somme du capital dû
          //$total_int +=$info["solde_int"]; //Somme des intérêts dûs
          $total_gar +=$info["solde_gar"]; //Somme de la garantie dûe
          $total_pen +=$info["solde_pen"]; //Somme des pénalités dûes

          array_push($infoEch,$info["solde_cap"]+$total_frais+$total_int+$info["solde_pen"]+$info["solde_gar"]); //Montant par échéance

          $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);
          $retour .= "<TR bgcolor=\"$color\">\n";
          $retour .= "<TD align=\"center\">".$info["id_ech"]."</TD>\n";
          $retour .= "<TD align=\"left\">".pg2phpDate($info["date_ech"])."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_cap"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($total_frais,false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($total_int,false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_gar"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_pen"],false)."</TD>\n";
          $retour .= "<TD align=\"right\">".afficheMontant (($info["solde_cap"]+$total_frais+$info["solde_pen"]+$total_int+$info["solde_gar"]),false)."</TD>\n";
          $retour .= "</TR>\n";
        }
      }
      $mnt_total_du = $total_cap + $total_frais + $total_int + $total_pen + $total_gar;
      $SESSION_VARS["infoEch"][$id_doss] = $infoEch;

      $retour .= "</TABLE>\n";
      $MyPage->addHTMLExtraCode("ech1".$id_doss,$retour);
      $MyPage->setHTMLExtraCodeProperties("ech1".$id_doss, HTMP_IN_TABLE, true);

      $MyPage->addHTMLExtraCode("a1".$id_doss,"<br>");
      $MyPage->addHTMLExtraCode("a2".$id_doss,"<b>Remboursement</b>");
      $MyPage->setHTMLExtraCodeProperties("a2".$id_doss, HTMP_IN_TABLE, true);

      //Le solde du compte lié
      $CPT_LIAISON = getAccountDatas ($val_doss['cpt_liaison']);
      $solde1 = getSoldeDisponible($val_doss['cpt_liaison']);

      $JS_1= "";
      if ($SESSION_VARS["source"] == 2) { // Remboursement sur le compte lié
        $JS_1 .= "if (recupMontant(document.ADForm.mnt_remb".$id_doss.".value) > ".$solde1.")\n";
        $JS_1 .= "{\n";
        $JS_1 .= "  msg +='- "._("Le montant saisi est supérieur au solde du compte lié.")."\\n';\n";
        $JS_1 .= "  ADFormValid=false;\n";
        $JS_1 .= "}\n";
      } /* Fin if ($SESSION_VARS["source"] == 2) */

      $JS_1 .= "if (recupMontant(document.ADForm.mnt_remb".$id_doss.".value) > recupMontant(document.ADForm.mnt_credit".$id_doss.".value))\n";
      $JS_1 .= "{\n";
      $JS_1 .= "  msg +='- ".sprintf(_("Le montant saisi pour le dossier N° %s est supérieur au montant total du crédit."),$id_doss)."\\n';\n";
      $JS_1 .= "  ADFormValid=false;\n";
      $JS_1 .= "}\n";

      $MyPage->addJS(JSP_BEGIN_CHECK,"test".$id_doss,$JS_1);

      //Ajout des champs
      $MyPage->addField("date_remb".$id_doss, _("Date du remboursement"), TYPC_DTE);
      $MyPage->setFieldProperties("date_remb".$id_doss, FIELDP_DEFAULT, date("d/m/Y"));
      $MyPage->addField("mnt_credit".$id_doss, _("Montant total du crédit"), TYPC_MNT);
      $MyPage->setFieldProperties("mnt_credit".$id_doss,FIELDP_DEFAULT,$mnt_total_du);
      $MyPage->setFieldProperties("mnt_credit".$id_doss, FIELDP_IS_LABEL, true);

      $SESSION_VARS["OK"] = false;
      $mnt = $mnt_total_du;

      if ($SESSION_VARS["source"] == 2) { // Source = compte lié
        $MyPage->addField("num_cpt_liaison".$id_doss, _("Numéro compte lié"), TYPC_TXT);
        $MyPage->setFieldProperties("num_cpt_liaison".$id_doss, FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties("num_cpt_liaison".$id_doss, FIELDP_DEFAULT, $CPT_LIAISON["num_complet_cpte"]);

        $MyPage->addField("mnt_cpt_liaison".$id_doss, _("Solde compte lié"), TYPC_MNT);
        $MyPage->setFieldProperties("mnt_cpt_liaison".$id_doss, FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties("mnt_cpt_liaison".$id_doss, FIELDP_DEFAULT, $solde1);
      }

      $MyPage->addField("mnt_remb".$id_doss, _("Montant du remboursement"), TYPC_MNT);
      $MyPage->setFieldProperties("mnt_remb".$id_doss, FIELDP_DEFAULT,recupMontant($mnt));
      //A partir de la dernière échéance ou à la première
      //$MyPage->addField("derniereech".$id_doss,_("A partir de la derniére échéance"), TYPC_BOL);
      // Calcul et affichafe du montant à rembourser par membre dans le cas de GS avec un seul dossier réel
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
        //Affichage des remboursements par membre
        $tableauCreditFic="<br><table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Détails du remboursement par membre")."</b></td></tr></table>\n";
        $MyPage->addHTMLExtraCode("tab_cre_fic".$id_doss,$tableauCreditFic);
        $indice=0;
        foreach($montant_ech_fic as $cle=>$valeur ) {
          $champHidden_recup_tot .="<input type=\"hidden\" name=\"hid_cre_fic_tot_".$indice."\" value=\"".$valeur[1]."\" />";

          $MyPage->addField("membre_".$indice, _("Membre"), TYPC_TXT);
          $MyPage->setFieldProperties("membre_".$indice, FIELDP_IS_LABEL, true);
          $MyPage->setFieldProperties("membre_".$indice, FIELDP_DEFAULT,$id_cli." ".$valeur[0]);

          $MyPage->addField("cre_fic_tot_".$indice, _("Montant"), TYPC_TXT);
          $MyPage->setFieldProperties("cre_fic_tot_".$indice, FIELDP_IS_LABEL, true);
          $MyPage->setFieldProperties("cre_fic_tot_".$indice, FIELDP_DEFAULT,afficheMontant($valeur[1],false));
          $MyPage->addHTMLExtraCode("epace".$indice,"<BR>");
          $indice++;
        }

        $MyPage->addHTMLExtraCode("champ_hidden_mnt_rem_hidden_tot",$champHidden_recup_tot);
        //Fin affichage des dossier remboursements par membre
      }  //Fin montant à rembourser par membre
    }

    //Boutons
    $MyPage->addFormButton(1,1,"valid",_("Valider"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "LRcr-4");
    $MyPage->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "LRcr-2");
    $MyPage->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $MyPage->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  } // Fin si  mode = 2 :remboursement d'un montant
}
/*}}}*/

/*{{{ LRcr-4 : Confirmation montant du remboursement */
else if ($global_nom_ecran == "LRcr-4") {

  $html = new HTML_GEN2(_("Confirmation du montant à rembourser"));
  $SESSION_VARS['envoi'] =0;

  $mnt_remb_tot = 0 ; //  Vérifie qu'au moins un montant est saisie

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    if (isset($ {'mnt_remb'.$id_doss}) and recupMontant($ {'mnt_remb'.$id_doss}) > 0) {
      $mnt = $ {'mnt_remb'.$id_doss};
      $date = $ {'date_remb'.$id_doss};
      if ($date != date("d/m/Y")){
      	$SESSION_VARS['date'] = $date;
      } else {
      	$SESSION_VARS['date'] = NULL;
      }
      //On recupère la valeur du cpte de garantie postée
      $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = $ {'HTML_GEN_LSB_num_cpt_liaison'.$id_doss};
      $SESSION_VARS['gar_num_mob']['id_cpte_gar'] =$SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'];
      //Récupèration de l'échéance à payer
      $id_ech_remb=$ {'HTML_GEN_LSB_echeance'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["ech_paye"]=$id_ech_remb;
      //$SESSION_VARS['infos_doss'][$id_doss]["derniereech"]=$ {'derniereech'.$id_doss};
      //$info_credit=get_info_credit($id_doss,$id_ech_remb);

      $mnt = recupMontant($mnt);
      $mnt_remb_tot += $mnt;
      $SESSION_VARS['infos_doss'][$id_doss]['mnt_remb'] = $mnt; // montant saisi

      $nom_cli = getClientName($val_doss['id_client']);
      $html->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Remboursement du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");

      $html->addField("source".$id_doss,_("Origine du remboursement"),TYPC_TXT);
      $html->setFieldProperties("source".$id_doss, FIELDP_IS_LABEL, true);
      $html->addField("date".$id_doss,_("Date remboursement"),TYPC_DTE);
      $html->setFieldProperties("date".$id_doss, FIELDP_DEFAULT, $date);
      $html->setFieldProperties("date".$id_doss, FIELDP_IS_LABEL, true);
      $html->addField("mnt".$id_doss,_("Montant remboursé"),TYPC_MNT);
      $html->setFieldProperties("mnt".$id_doss, FIELDP_DEFAULT, $mnt);
      $html->setFieldProperties("mnt".$id_doss, FIELDP_IS_LABEL, true);

      if ($SESSION_VARS["source"] == 2) { // Remboursmeent via compte lié
      	$html->setFieldProperties("source".$id_doss, FIELDP_DEFAULT, "Compte lié");
        $CPT_LIAISON = getAccountDatas($val_doss['cpt_liaison']);
        $solde = getSoldeDisponible($val_doss['cpt_liaison']);

        $html->addField("num_complet_cpte".$id_doss,_("Compte de liaison"),TYPC_TXT);
        $html->addField("solde".$id_doss,_("Solde disponible"),TYPC_MNT);
        $html->setFieldProperties("num_complet_cpte".$id_doss, FIELDP_DEFAULT, $CPT_LIAISON["num_complet_cpte"]);
        ///$html->setFieldProperties("solde".$id_doss, FIELDP_LONG_NAME, "Solde disponible");
        $html->setFieldProperties("solde".$id_doss, FIELDP_DEFAULT, $solde);
        $html->addField("nouveau_solde".$id_doss, _("Solde après remboursement"), TYPC_MNT);
        $html->setFieldProperties("nouveau_solde".$id_doss, FIELDP_DEFAULT, $solde - $mnt);
        $html->setFieldProperties("num_complet_cpte".$id_doss, FIELDP_IS_LABEL, true);
        $html->setFieldProperties("solde".$id_doss, FIELDP_IS_LABEL, true);
        $html->setFieldProperties("nouveau_solde".$id_doss, FIELDP_IS_LABEL, true);
      }
      // $html->addHTMLExtraCode("hidden_mnt_reel".$id_doss,"<input type=\"hidden\" name=\"mnt_reel_hidden".$id_doss."\" value=\"".$mnt."\">");
      $html->addHiddenType("mnt_reel_hidden".$id_doss, $mnt);
      $html->addField("mnt_reel".$id_doss,_("Confirmation montant à rembourser"),TYPC_MNT);
      if ($SESSION_VARS["source"] == 1 && $global_billet_req) {
        $html->setFieldProperties("mnt_reel".$id_doss, FIELDP_HAS_BILLET, true);
      }
      $ChkJS .= "\t\tif (recupMontant(document.ADForm.mnt_reel$id_doss.value) != recupMontant(document.ADForm.mnt$id_doss.value))";
      $ChkJS.= "{\nmsg += '-".sprintf(_("Veuillez saisir le même montant pour le dossier N° %s"), $id_doss)."\\n'; ADFormValid=false;};\n";
      $html->addJS(JSP_BEGIN_CHECK, "JS3".$id_doss,$ChkJS);
    }
    else { // si ne le montant n'est pas renseigné, sortir le dossier de la liste des dossiers à rembourser
      unset($SESSION_VARS['infos_doss'][$id_doss]);
    }

  } // Fin parcours dossiers

  // Vérifier que le montant à rembourser n'est pas null
  if ($mnt_remb_tot <= 0 ) {
    $html_err = new HTML_erreur(_("Echec du remboursement."));
    $html_err->setMessage(_("Il faut au moins renseigner un montant pour le remboursement"));
    $html_err->addButton("BUTTON_OK", 'Lcr-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  $html->addFormButton(1,1,"valid",_("Valider"),TYPB_SUBMIT);
  $html->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "LRcr-5");
  $html->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "LRcr-2");
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $html->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
  $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ LRcr-5 : Remboursement d'une échéance ou d'un montant*/
else if ($global_nom_ecran == "LRcr-5") {
    //controle d'envoie du formulaire
    $SESSION_VARS['envoi']++;
    if( $SESSION_VARS['envoi'] != 1 ) {
            $html_err = new HTML_erreur(_("Confirmation"));
        $html_err->setMessage(_("Donnée dèjà envoyée"));
        $html_err->addButton("BUTTON_OK", 'Gen-8');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
    }
    //fin contrôle
  // Vérifier que les montants ont été résaisis correctement
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $montant_recup = recupMontant($ {'mnt_reel'.$id_doss});
    if ($montant_recup != $val_doss['mnt_remb']) {
      $html_err = new HTML_erreur(_("Echec du remboursement."));
      $html_err->setMessage(sprintf(_("Veuillez saisir le même montant pour le dossier N° %s."),$id_doss));
      $html_err->addButton("BUTTON_OK", 'Lcr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }
  }

  //require_once ('lib/misc/debug.php');
  //print_rn($SESSION_VARS["infos_doss"]);
  //print_rn($SESSION_VARS["source"]);
  //print_rn($SESSION_VARS['date']);
  //exit;

  if ($SESSION_VARS['date'] == NULL) {
    $myErr = rembourse_montantInt_lcr($SESSION_VARS["infos_doss"], $SESSION_VARS["source"], $global_id_guichet);
  } else {
    $myErr = rembourse_montantInt_lcr($SESSION_VARS["infos_doss"], $SESSION_VARS["source"], $global_id_guichet, $SESSION_VARS['date']);
  }

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec du remboursement."));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br/>"._("Paramètre")." : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Lcr-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $MyPage = new HTML_message(_("Remboursement crédit"));
    $msg = _("Le remboursement a été effectué avec succès.");
    if (is_array($myErr->param["RETSOLDECREDIT"])) { // Crédit soldé
      $msg .= "<br /><br />"._("Le crédit a été soldé");
    }
    $msg .= "<br /><br />"._("N° de transaction")." : <b><code>".sprintf("%09d", $myErr->param)."</code></b>";

    $MyPage->setMessage($msg);

    if ($SESSION_VARS["source"] == 1) {
    	$type_doc = 'REC-RMC';
    } else {
    	$type_doc = 'ATT-RMC';
    }

    if ($SESSION_VARS['date'] == NULL) {
        $SESSION_VARS['date'] = date("d/m/Y");
    }

    print_recu_remboursement_lcr($SESSION_VARS["infos_doss"], $SESSION_VARS['date'], $type_doc); // TODO
    $MyPage->addButton(BUTTON_OK, "Lcr-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }

}
/*}}}*/

/*{{{ LRcr-6 : Confirmation */
else if ($global_nom_ecran == 'LRcr-6') {
	$SESSION_VARS['envoi']++;
	if( $SESSION_VARS['envoi'] != 1 ) {
		$html_err = new HTML_erreur(_("Confirmation"));
	    $html_err->setMessage(_("Donnée dèjà envoyée"));
	    $html_err->addButton("BUTTON_OK", 'Gen-8');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;
	    exit();
	}
  if ($SESSION_VARS['gs_cat'] != 2 ) { // dossier individuel
    $global_mouvements_credit=array();
    $myErr = recouvrementCreditPerte($SESSION_VARS['id_doss'],recupMontant($ {'mnt_remb'.$id_doss}), $global_mouvements_credit, 607);
    if ($myErr->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec lors du recouvrement.")." ");
      $html_err->setMessage("Erreur : ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Lcr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }
    $MyPage = new HTML_message(_("Recouvrement crédit en perte"));
    $MyPage->setMessage(sprintf(_("Le recouvrement d'un montant de %s a bien été enregistré."),afficheMontant(recupMontant($ {'mnt_remb'.$id_doss}), true))."<br />".sprintf(_("Le nouveau solde pour ce crédit est de %s"),afficheMontant($myErr->param, true)));
    $MyPage->addButton(BUTTON_OK, "Lcr-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }

  else { // GS avec dossiers multiples
    $montant_remb = 0;
    $solde_capital = 0;
    $solde_restant = 0;
    $global_mouvements_credit=array();
    foreach($SESSION_VARS['infos_doss'] as $id_doss=> $infos_doss) {
      $myErr = recouvrementCreditPerte($infos_doss['id_doss'],recupMontant($ {'mnt_remb'.$id_doss}), $global_mouvements_credit, 607);
      $montant_remb += recupMontant($ {'mnt_remb'.$id_doss});
    }
    $solde_restant = $SESSION_VARS['mnt_credit'] - $montant_remb;
    if ($myErr->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec lors du recouvrement.")." ");
      $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Lcr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }

    $MyPage = new HTML_message(_("Recouvrement crédit en perte"));
    $MyPage->setMessage(sprintf(_("Le recouvrement d'un montant de %s a bien été enregistré."),afficheMontant($montant_remb, true))."<br />".sprintf(_("Le nouveau solde pour ce crédit est de %s"),afficheMontant($solde_restant, true)));
    $MyPage->addButton(BUTTON_OK, "Lcr-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>