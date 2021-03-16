<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [130] Modification dossier de crédit
 * Cette opération comprends les écrans :
 * - Mdd-1 : sélection d'un dossier de crédit
 * - Mdd-2 : modificaiton d'un dossier de crédit
 * - Mdd-3 : échéancier théorique
 * - Mdd-4 : confirmation modification d'un dossier de crédit
 * - Mdd-6 : gestion des garanties mobilisées
 * - Mdd-7 : ajout d'une garantie
 * - Mdd-8 : modification d'un garantie
 * - Mdd-9 : suppression d'un garantie
 * - Mdd-10 : confirmation d'ajout, de modification ou de suppression de d'un garantie
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/html/HTML_champs_extras.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';

global $global_id_client;

/*{{{ Mdd-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Mdd-1") {
  // Récupération des infos du client
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);
  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste des dossiers à afficher
  $i = 1;

  // Récupération des dossiers individuels dans ad_dcr en attente de décision ou en attente de Rééch/Moratoire
  $whereCl=" AND is_ligne_credit='f' AND mode_calc_int!=5 AND ((etat=1) OR (etat=2) OR (etat=5) OR (etat=13))";
  $dossiers_reels = getIdDossier($global_id_client,$whereCl);
  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
    if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être approuvés via le groupe
      $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
      $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
      $dossiers[$i] = $value;

      $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
      $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
      $codejs .= "}\n";
      $i++;
    }

  // SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
    // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
    $whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
    $dossiers_fictifs = getCreditFictif($whereCl);

    // Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val)
      if (($val['is_ligne_credit'] != 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 1 OR $val['etat'] == 2 OR $val['etat'] == 5)) {
        $date_dem = $date = pg2phpDate($val['date_dem']);
        $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
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
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Mdd-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Mdd-2 : Modification d'un dossier de crédit */
else if ($global_nom_ecran == "Mdd-2") {

  global $adsys, $global_id_agence;

  $dataAgence = getAgenceDatas($global_id_agence);
  $SESSION_VARS["is_cpte_remb"] =  'f';

  // Si on vient de Apd-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"Mdd-1")) {
    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
      // Infos dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
        $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
        $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
      }
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
      // id du dossier fictif : id du dossier du groupe
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);

      $js_7 =" ";
      foreach($dossiers_membre as $id_doss=>$val) {
        if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==1 or $val['etat']==2 or $val['etat']==5)) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
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
     //Gestion des champs supplémentaire 
    // champsextras
      if ( !isset($SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues'])) {
      	  $SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues'] = getChampsExtrasDCRValues($id_doss);
      }
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
    // Source de financement
    $SESSION_VARS['id_bailleur'] = getListBailleur();
  } //fin si on vient de Apd-1

  // Gestion de la devise
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
  $id_prod  = $SESSION_VARS['infos_prod']['id'];
  //recuperation de la precision de la devise du produit de credit
	$devise_prod=$SESSION_VARS['infos_prod']['devise'];
  $DEV = getInfoDevise($devise_prod);// recuperation d'info sur la devise'
  debug($DEV["precision"]);
  $precision_devise=pow(10,$DEV["precision"]);
  // Création du formulaire
  $Myform = new HTML_GEN2(_("Modification d'un dossier de crédit"));

  $js_check = ""; // Javascript de validation de la saisie
  $js_copie_mnt_dem = ""; // sauvegare de mnt_dem si le champ est désactivé
  $gar_mob = false; // déterinantion du prochain écran

  //Récuperation des Détails objets de crédits
  $det_dem = getDetailsObjCredit();
  $det_dem2 = getDetailsObjCredit2();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

    $nom_cli = getClientName($val_doss['id_client']);
    $Myform->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=\"center\"><b> ".sprintf(_("Modification du dossier N° %s de %s"), $id_doss , $nom_cli)."</b></p>") ;

    $Myform->addField("id_doss".$id_doss, _("Numéro de dossier"), TYPC_TXT);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_DEFAULT,$val_doss['id_doss']);
    $Myform->addField("id_prod".$id_doss, _("Produit de crédit"), TYPC_LSB);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_ADD_CHOICES, array("$id_prod"=>$SESSION_VARS['infos_prod']['libel']));
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_DEFAULT, $id_prod);
    // Ajout de liens
    $Myform->addLink("id_prod".$id_doss, "produit".$id_doss,_("Détail produit"), "#");
    $Myform->setLinkProperties("produit".$id_doss,LINKP_JS_EVENT,array("onClick"=>"open_produit(".$id_prod.",".$id_doss.");"));
    $Myform->addField("periodicite".$id_doss, _("Périodicité"), TYPC_INT);
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_type_periodicite"][$SESSION_VARS['infos_prod']['periodicite']]));
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_IS_LABEL,true);

    $Myform->addField("id_bailleur".$id_doss, _("Source de financement"), TYPC_LSB);
    $Myform->setFieldProperties("id_bailleur".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['id_bailleur']);
    $Myform->setFieldProperties("id_bailleur".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("id_bailleur".$id_doss,FIELDP_DEFAULT,$val_doss['id_bailleur']);

    $Myform->addField("obj_dem".$id_doss, _("Objet de la demande"), TYPC_LSB);
    $Myform->setFieldProperties("obj_dem".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['obj_dem']);

    if (isDcrDetailObjCreditLsb()) {

      if (in_array($val_doss['etat'], array(1,5))) {

        $Myform->setFieldProperties("obj_dem" . $id_doss, FIELDP_JS_EVENT, array("onchange" => "setDetailObj$id_doss();"));
        $Myform->addField("detail_obj_dem_bis" . $id_doss, _("Détail objet demande 1"), TYPC_LSB);
        $Myform->addField("detail_obj_dem_2" . $id_doss, _("Détail objet demande 2"), TYPC_LSB);

        $JS_3 = " ";
        $JS_3 .= "function lookup( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value[0] == name )
                    {
                    arrret[ arr[ i ].key]=arr[ i ].value[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;}

            };\n

            function lookup2( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value2[0] == name )
                    {
                    arrret[ arr[ i ].key2]=arr[ i ].value2[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;}

            };\n
            ";

        $JS_3 .= "function setDetailObj$id_doss(){ \n var myArray = [\n";

        foreach ($det_dem as $key => $value) {

          $JS_3 .= "{ key: $key, value: ['" . $value['id_obj'] . "','" . $value['libel'] . "'] },";
        }

        $JS_3 .= "];\n ";

        $JS_3 .= " if( lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_doss.value, myArray ) != false ) { \n";
        $JS_3 .= " var select = document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss;
              select.options.length=0;";
        $JS_3 .= "var det_cred=(lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_doss.value, myArray ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.length] = new Option(det_cred[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
        //$JS_3 .= "}\n";


        $JS_3 .= " var myArray2 = [\n";
        foreach ($det_dem2 as $key2 => $value2) {

          $JS_3 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
        }

        $JS_3 .= "];\n ";

        $JS_3 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_doss.value, myArray2 ) != false ) { \n";
        $JS_3 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss;
              select2.options.length=0;";
        $JS_3 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_doss.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
        $JS_3 .= "}\n";
        $JS_3 .= "setDetailObj$id_doss();document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.value = '".$val_doss['detail_obj_dem_2']."';";
        $JS_3 .= "document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_doss.value = '".$val_doss['detail_obj_dem_bis']."';";


        // Ajout du code JS
        $Myform->addJS(JSP_FORM, "det_obj" . $id_doss, $JS_3);

      } else {
        $val_doss['detail_obj_dem_bis'] = $det_dem[$val_doss['detail_obj_dem_bis']]['libel'];
        $val_doss['detail_obj_dem_2'] = $det_dem2[$val_doss['detail_obj_dem_2']]['libelle'];

        $Myform->addField("detail_obj_dem_bis" . $id_doss, _("Détail objet demande 1"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_bis".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("detail_obj_dem_bis".$id_doss,FIELDP_DEFAULT,$val_doss['detail_obj_dem_bis']);
        $Myform->addField("detail_obj_dem_2" . $id_doss, _("Détail objet demande 2"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_2".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("detail_obj_dem_2".$id_doss,FIELDP_DEFAULT,$val_doss['detail_obj_dem_2']);
      }
    }
    else {
      //AT97
      if (in_array($val_doss['etat'], array(1,5))) {

        $Myform->setFieldProperties("obj_dem" . $id_doss, FIELDP_JS_EVENT, array("onchange" => "setDetailObj$id_doss();"));
        $Myform->addField("detail_obj_dem" . $id_doss, _("Détail objet demande 1"), TYPC_TXT);
        $Myform->addField("detail_obj_dem_2" . $id_doss, _("Détail objet demande 2"), TYPC_LSB);
        $JS_3 = " ";
        $JS_3 .= "
            function lookup2( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value2[0] == name )
                    {
                    arrret[ arr[ i ].key2]=arr[ i ].value2[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;}

            };\n
            ";
        $JS_3 .= "function setDetailObj$id_doss(){ \n";
        $JS_3 .= " var myArray2 = [\n";
        foreach ($det_dem2 as $key2 => $value2) {

          $JS_3 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
        }

        $JS_3 .= "];\n ";

        $JS_3 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_doss.value, myArray2 ) != false ) { \n";
        $JS_3 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss;
              select2.options.length=0;";
        $JS_3 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_doss.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
        $JS_3 .= "}\n";
        $JS_3 .= "setDetailObj$id_doss();document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_doss.value = '".$val_doss['detail_obj_dem_2']."';";



        // Ajout du code JS
        $Myform->addJS(JSP_FORM, "det_obj" . $id_doss, $JS_3);
      }
      else {
        $val_doss['detail_obj_dem_2'] = $det_dem[$val_doss['detail_obj_dem_2']]['libelle'];

        $Myform->addField("detail_obj_dem_2" . $id_doss, _("Détail objet demande 2"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_2" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("detail_obj_dem_2" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem_2']);
      }
    }

    $Myform->addField("date_dem".$id_doss, _("Date demande"), TYPC_DTE);
    $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_DEFAULT,$val_doss['date_dem']);
   // $Myform->addField("cre_date_debloc".$id_doss, _("Date de déblocage"), TYPC_DTE);
    //$Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);
    $Myform->addField("mnt_dem".$id_doss, _("Montant demandé"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_DEFAULT, $val_doss['mnt_dem']);
    // Evenement JavaScript des champs
    $Myform->setFieldProperties("mnt_dem".$id_doss, FIELDP_JS_EVENT, array("OnFocus"=>"resetMnt($id_doss);"));
    $Myform->setFieldProperties("mnt_dem".$id_doss, FIELDP_JS_EVENT, array("OnChange"=>"initMnt($id_doss);"));
    $Myform->addHiddenType("hid_mnt_dem".$id_doss, $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem']);
    // Copie de de mnt dem au cas ou mnt_dem est désactivéut
    $js_copie_mnt_dem .= "document.ADForm.hid_mnt_dem".$id_doss.".value = recupMontant(document.ADForm.mnt_dem".$id_doss.".value);";
    $Myform->addField("num_cre".$id_doss, _("Numéro de crédit"), TYPC_INT);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_DEFAULT,$val_doss['num_cre']);
    $Myform->addField("cpt_liaison".$id_doss, _("Compte de liaison"), TYPC_LSB);
    if($SESSION_VARS["is_cpte_remb"] == "t")
        $Myform->addField("cpt_remb".$id_doss, _("Compte de remboursement"), TYPC_LSB);
    $cptes_liaison = getComptesLiaison ($val_doss['id_client'], $SESSION_VARS['infos_prod']["devise"]);
    $CPT_LIE = array(); // compte de liaison pour un client

    foreach($cptes_liaison as $id_cpte=>$compte) {
      $CPT_LIE[$id_cpte] = $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
      $CPT_PRELEV_FRAIS[$id_cpte] =  $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
    }

    $Myform->setFieldProperties("cpt_liaison".$id_doss, FIELDP_ADD_CHOICES, $CPT_LIE);
    $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_IS_LABEL,true);

    /******* AT-200 *******/
      if($SESSION_VARS["is_cpte_remb"] == 't') {
          if (!empty($val_doss['cpt_remb'])) {
              $cpt_remboursement = getAccountDatas($val_doss['cpt_remb']);
              $cpt_remb_lsb = array($val_doss['cpt_remb'] => $cpt_remboursement['num_complet_cpte'] . " " . $cpt_remboursement["intitule_compte"]);
              $Myform->setFieldProperties("cpt_remb" . $id_doss, FIELDP_ADD_CHOICES, $cpt_remb_lsb);
              $Myform->setFieldProperties("cpt_remb" . $id_doss, FIELDP_IS_LABEL, true);
              $Myform->setFieldProperties("cpt_remb" . $id_doss, FIELDP_DEFAULT, $val_doss['cpt_remb']);
          }
      }
    /******* AT-200 *******/

    ////$cpt_lie = getAccountDatas($val_doss['cpt_liaison']);
    ///$Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_DEFAULT,$cpt_lie["num_complet_cpte"]." ".$cpt_lie['intitule_compte']);
    $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_DEFAULT, $val_doss['cpt_liaison']);
    $Myform->addField("id_agent_gest".$id_doss, _("Agent gestionnaire"), TYPC_LSB);
    $Myform->setFieldProperties("id_agent_gest".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_DEFAULT,$val_doss['id_agent_gest']);
    $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
    $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
    if ($val_doss['etat'] == 2)  $gar_mob = true; // On peut encore mobiliser ou modifier des garanties
    if ($val_doss['etat'] == 2 or $val_doss['etat'] == 5 ) { // Dossier accepté ou Fonds déboursés
      $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,true);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnFocus"=>"reset($id_doss);"));
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnChange"=>"init($id_doss);"));
      $Myform->addHiddenType("mnt_octr".$id_doss, $val_doss['cre_mnt_octr']);
      if ($val_doss['gs_cat'] ==1 )
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->addField("cre_date_approb".$id_doss, _("Date approbation"), TYPC_DTE);
      $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_REQUIRED,true);
      $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_approb']);

    }
    if ($val_doss['etat'] == 5 ) { // Fonds déboursés
      $Myform->addField("cre_date_debloc".$id_doss, _("Date de déblocage"), TYPC_DTE);
      $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);
      $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->addField("cre_etat".$id_doss, _("Etat crédit"), TYPC_INT);
      $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_DEFAULT,$val_doss['cre_etat']);
      $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);
      //$Myform->setFieldProperties("id_bailleur".$id_doss,FIELDP_IS_LABEL,false);
      $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,false);
      if (!isDcrDetailObjCreditLsb()) {
        $Myform->setFieldProperties("detail_obj_dem" . $id_doss, FIELDP_IS_LABEL, false);
      }
//      if (check_access(300))
//          $Myform->setFieldProperties("cre_etat".$id_doss, FIELDP_CAN_MODIFY, true);
//      $Myform->addField("cre_retard_etat_max_jour".$id_doss, _("Nombre de jour"), TYPC_INT);
//      $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_DEFAULT,$val_doss['cre_retard_etat_max_jour']);
//      $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_IS_LABEL, true);
//      if (check_access(300))
//        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss, FIELDP_CAN_MODIFY, true);
    }

    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['infos_prod']["type_duree_credit"];
    $libelle_duree = mb_strtolower(adb_gettext($adsys["adsys_type_duree_credit"][$type_duree])); // libellé type durée en minuscules
    $Myform->addField("duree_mois".$id_doss, sprintf(_("Durée en %s"),$libelle_duree), TYPC_INT);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['duree_mois']);
    $Myform->addField("differe_jours".$id_doss, _("Différé en jours"), TYPC_INN);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_DEFAULT,$val_doss['differe_jours']);
    $Myform->addField("differe_ech".$id_doss, _("Différé en échéance"), TYPC_INT);
    $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_DEFAULT,$val_doss['differe_ech']);
    $Myform->addField("delai_grac".$id_doss, _("Délai de grace"), TYPC_INT);
    $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_DEFAULT,$val_doss['delai_grac']);
    $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->addField("nb_jr_bloq_cre_avant_ech".$id_doss, _("Nombre de jours pour bloquer le crédit avant échéance"), TYPC_INT);
    $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_doss,FIELDP_DEFAULT,$val_doss['nb_jr_bloq_cre_avant_ech']);
    $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->addField("prelev_auto".$id_doss, _("Prélèvement automatique"), TYPC_BOL);
    $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_DEFAULT,($val_doss['prelev_auto']=="t"?true:false));
    $Myform->addField("mnt_commission".$id_doss, _("Montant commission"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$val_doss['mnt_commission']);
    $Myform->addField("mnt_assurance".$id_doss, _("Montant assurance"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["mnt_dem"]*$SESSION_VARS['infos_prod']['prc_assurance']);
    if ($val_doss['etat'] == 1) { // Les champs modifiables si le dossier est en attente de décision
      $gar_mob  = true ; // On peut enore mobiliser des garanties

      $Myform->setFieldProperties("id_bailleur".$id_doss,FIELDP_IS_LABEL,false);
      $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,false);
      if (!isDcrDetailObjCreditLsb()) {
        $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_IS_LABEL,false);
      }
      if ($val_doss['gs_cat'] != 1) {
        $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_LABEL,false);
      }
      $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,false);
      $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,false);
      $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_IS_LABEL,false);
      // Les champs obligatoires
      $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_REQUIRED,true);
      if (!isDcrDetailObjCreditLsb()) {
        $Myform->setFieldProperties("detail_obj_dem".$id_doss, FIELDP_IS_REQUIRED, true);
      }
      $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_REQUIRED,true);
      $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
    }

    if ($val_doss['cre_mnt_octr'] > 0) {
      $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr'] * $SESSION_VARS['infos_prod']['prc_assurance']);
      $comm_values = calculCommissionDeboursement($val_doss['id_prod'],$val_doss['cre_mnt_octr'],$id_doss);
      $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$comm_values["mnt_comm"]);
    }
    elseif($val_doss['mnt_dem'] > 0 ) {
      $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss['mnt_dem'] * $SESSION_VARS['infos_prod']['prc_assurance']);
      $comm_values = calculCommissionDeboursement($val_doss['id_prod'],$val_doss['mnt_dem'],$id_doss);
      $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$comm_values["mnt_comm"]);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
      $Myform->addField("gar_num".$id_doss, _("Garantie numéraire attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num']);
      $Myform->addField("gar_mat".$id_doss, _("Garantie matérielle attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_DEFAULT,$val_doss['gar_mat']);
      $Myform->addField("gar_tot".$id_doss, _("Garantie totale attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_DEFAULT,$val_doss['gar_tot']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
      $Myform->addField("gar_num_encours".$id_doss, _("Garantie numéraire encours"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num_encours']);
    }
    $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr']=$val_doss['cre_mnt_octr'];
    $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
    $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées
    if (is_array($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']))
      foreach($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] == 1)
        $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] += recupMontant($value['valeur']);
      elseif($value['type'] == 2)
        $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] += recupMontant($value['valeur']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
      $Myform->addField("gar_num_mob".$id_doss, _("Garantie numéraire mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);
      $Myform->addField("gar_mat_mob".$id_doss, _("Garantie matérielle mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob']);
    }
   //Gestipn des champs supplémentaire
   	$objChampsExtras = new HTML_Champs_Extras ($Myform,'ad_dcr',$id_doss); 
   	$objChampsExtras->buildChampsExtras($SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues']);
   	$SESSION_VARS['infos_doss'][$id_doss]['champsExtras']= $objChampsExtras-> getChampsExtras();
    //
   
    // Affichage des dossiers fictifs dans le cas d'un GS avec dossier réel unique
    if ($SESSION_VARS['infos_doss'][$id_doss]['gs_cat'] == 1) {
      $js_mnt_dem = "function calculeMontant() {"; // function de calcule du demandé
      $js_mnt_dem .= "var tot_mnt_dem = 0;\n";

      $js_2 = " ";
      $js_onload = "";

      foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
        $Myform->addHTMLExtraCode("espace_fic".$id_fic,"<BR>");
        $Myform->addField("membre".$id_fic, _("Membre"), TYPC_TXT);
        $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("membre".$id_fic,FIELDP_DEFAULT,$val_fic['id_membre']." ".getClientName($val_fic['id_membre']));

        $Myform->addField("id_bailleur_fic".$id_fic, _("Source de financement"), TYPC_LSB);
        $Myform->setFieldProperties("id_bailleur_fic".$id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['id_bailleur']);
        $Myform->setFieldProperties("id_bailleur_fic".$id_fic, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("id_bailleur_fic".$id_fic, FIELDP_DEFAULT, $val_fic['id_bailleur']);

        $Myform->addField("obj_dem_fic".$id_fic, _("Objet demande"), TYPC_LSB);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['obj_dem']);
        if (isDcrDetailObjCreditLsb()) {

          if (in_array($val_doss['etat'], array(1,5))) {
            $Myform->setFieldProperties("obj_dem_fic" . $id_fic, FIELDP_JS_EVENT, array("onchange" => "setDetailObj$id_fic();"));
            $Myform->addField("detail_obj_dem_bis_fic" . $id_fic, _("Détail demande 1"), TYPC_LSB);
            $Myform->addField("detail_obj_dem_2_fic" . $id_fic, _("Détail demande 2"), TYPC_LSB);

              $js_2 .= "function lookup( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value[0] == name )
                    {
                    arrret[ arr[ i ].key]=arr[ i ].value[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;} \n

            };\n

            function lookup2( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value2[0] == name )
                    {
                    arrret[ arr[ i ].key2]=arr[ i ].value2[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;} \n

            };\n";

              $js_2 .= "function setDetailObj$id_fic(){ \n var myArray2 = [\n";


              foreach ($det_dem2 as $key2 => $value2) {

                $js_2 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
              }

              $js_2 .= "];\n ";

              $js_2 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem_fic$id_fic.value, myArray2 ) != false ) { \n";
              $js_2 .= "     var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic;
                      select2.options.length=0;";
              $js_2 .= "    var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem_fic$id_fic.value, myArray2 ));\n
                     document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.length] = new Option(\"[Aucun]\" ,0, false, false);
                     for(var i in det_cred2)
                     {
                        document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.length] = new Option(det_cred2[i] ,i, false, false);
                    }
                }
             else
             {
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.options.length=0;
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
            $js_2 .="var myArray = [\n";

            foreach ($det_dem as $key => $value) {

              $js_2 .= "{ key: $key, value: ['" . $value['id_obj'] . "','" . $value['libel'] . "'] },";
            }

            $js_2 .= "];\n ";

            $js_2 .= " if( lookup(document.ADForm.HTML_GEN_LSB_obj_dem_fic$id_fic.value, myArray ) != false ) { \n";
            $js_2 .= "     var select = document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic;
                      select.options.length=0;";
            $js_2 .= "    var det_cred=(lookup(document.ADForm.HTML_GEN_LSB_obj_dem_fic$id_fic.value, myArray ));\n
                     document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.length] = new Option(\"[Aucun]\" ,0, false, false);
                     for(var i in det_cred)
                     {
                        document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.length] = new Option(det_cred[i] ,i, false, false);
                    }
                }
             else
             {
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options.length=0;
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
              $js_2 .= "}\n";

              $js_onload .= "\n setDetailObj$id_fic();document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.value = '".$val_fic['detail_obj_dem_bis']."';document.ADForm.HTML_GEN_LSB_detail_obj_dem_2_fic$id_fic.value = '".$val_fic['detail_obj_dem_2']."';";

          }
          else {
            $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_LABEL, true);

            $val_fic['detail_obj_dem_bis'] = $det_dem[$val_fic['detail_obj_dem_bis']]['libel'];
            $val_fic['detail_obj_dem_2'] = $det_dem2[$val_fic['detail_obj_dem_2']]['libelle'];

            $Myform->addField("detail_obj_dem_bis_fic" . $id_fic, _("Détail demande 1"), TYPC_TXT);
            $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_IS_LABEL, true);
            $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem_bis']);

            $Myform->addField("detail_obj_dem_2_fic" . $id_fic, _("Détail demande 2"), TYPC_TXT);
            $Myform->setFieldProperties("detail_obj_dem_2_fic" . $id_fic, FIELDP_IS_LABEL, true);
            $Myform->setFieldProperties("detail_obj_dem_2_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem_2']);
          }
        } else {
          $Myform->addField("detail_obj_dem_fic" . $id_fic, _("Détail demande"), TYPC_TXT);
          $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true);
          $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
        }
        $Myform->addField("mnt_dem_fic".$id_fic, _("Montant demandé"), TYPC_MNT);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_DEFAULT,$val_fic['mnt_dem']);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_JS_EVENT,array("OnChange"=>"calculeMontant();"));

        if ($val_doss['etat']!=1) {

          $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_LABEL, true);
        }

        if($val_doss['etat'] == 1 or $val_doss['etat'] == 5){
          $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_LABEL, false);
          if (!isDcrDetailObjCreditLsb()) {
            $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_LABEL, false);
          }
        }

        $js_mnt_dem .= "tot_mnt_dem = tot_mnt_dem + recupMontant(document.ADForm.mnt_dem_fic".$id_fic.".value);\n";
      }

      if (isDcrDetailObjCreditLsb() && in_array($val_doss['etat'], array(1,5))) {
        $js_2 .= "\nwindow.onload = function() {".$js_onload."}\n";

        $Myform->addJS(JSP_FORM, "det_obj_gs".$id_doss, $js_2);
      }

      $js_mnt_dem .= "document.ADForm.mnt_dem".$id_doss.".value = formateMontant(tot_mnt_dem);\n";
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $js_mnt_dem .="\n\tdocument.ADForm.gar_num".$id_doss.".value =formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."*parseFloat(tot_mnt_dem))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $js_mnt_dem .="\n\tdocument.ADForm.gar_mat".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."*parseFloat(tot_mnt_dem))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $js_mnt_dem .="\n\tdocument.ADForm.gar_tot".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."*parseFloat(tot_mnt_dem))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $js_mnt_dem .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."*parseFloat(tot_mnt_dem))*".$precision_devise.")/".$precision_devise.";";
      }

      $js_mnt_dem .= "}\n";
      $Myform->addJS(JSP_FORM,"js_mnt_dem".$id_doss,$js_mnt_dem);
    }


    // Contrôle Javascript
    // Vérifier que le montant totat mobilisé est supérieur ou égal au montant attendu
    if ($SESSION_VARS['infos_doss'][$id_doss]['gar_tot'] > 0) {
      $gar_num_mob = "document.ADForm.gar_num_mob" . $id_doss;
      $gar_mat_mob = "document.ADForm.gar_mat_mob" . $id_doss;
      $gar_tot = "document.ADForm.gar_tot" . $id_doss;

      if (in_array($val_doss['etat'], array(1,2,13))) {
        if ($SESSION_VARS['infos_doss'][$id_doss]['gar_num'] > 0) {
          $gar_num = "document.ADForm.gar_num" . $id_doss;
          // Vérifer que les garanties numéraires mobilisées sont supérieues aux garanties numéraires attendues
          $js_check .= "
        if (recupMontant($gar_num.value) > recupMontant($gar_num_mob.value))
                     {
                         msg += '- " . sprintf(_("Les garanties numéraires mobilisées par le dossier %s sont insuffisantes"), $id_doss) . "\\n';
                         ADFormValid = false;
                     }";
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]['gar_mat'] > 0) {
          $gar_mat = "document.ADForm.gar_mat" . $id_doss;
          // Vérifer que les garanties matérielles mobilisées sont supérieues aux garanties matérielles attendues
          $js_check .= "
        if (recupMontant($gar_mat.value) > recupMontant($gar_mat_mob.value))
                     {
                         msg += '- " . sprintf(_("Les garanties matérielles mobilisées par le dossier %s sont insuffisantes"), $id_doss) . "\\n';
                         ADFormValid = false;
                     }";
        }
        $js_check .= "
        gar_tot_mob = recupMontant($gar_num_mob.value)+recupMontant($gar_mat_mob.value);
        if (recupMontant($gar_tot.value) > gar_tot_mob)
                     {
                         msg += '- " . sprintf(_("Le montant total des garanties numéraires et matérielles mobilisées par le dossier %s est insuffisant"), $id_doss) . "\\n';
                         ADFormValid = false;
                     }";
      }
    }

    // Vérification de la durée en mois
    $js_check .="\tif(parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois'].")>0){\n";
    $js_check .="\t\tif((parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) || (parseInt(document.ADForm.duree_mois".$id_doss.".value) > parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois']."))) { msg+=' - La durée du crédit doit être comprise entre ".$SESSION_VARS['infos_prod']['duree_min_mois']." et ".$SESSION_VARS['infos_prod']['duree_max_mois']." comme définie dans le produit.\\n';ADFormValid=false;}\n";
    $js_check .="\t}else\n";
    $js_check.="\t\tif(parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) { msg+=' - ".sprintf(_("La durée du crédit doit être au moins égale à %s"),$SESSION_VARS['infos_prod']['duree_min_mois']).". "._("comme définie dans le produit").".\\n';ADFormValid=false;}\n";

    //Verification du nombre de jour pour bloquer le credit avant échéance par rapport au maximum
    $nb_jr_bloq_cre_avant_ech_max = $SESSION_VARS['infos_prod']['nb_jr_bloq_cre_avant_ech_max'];
    $js_check .= "if (parseInt(document.ADForm.nb_jr_bloq_cre_avant_ech".$id_doss.".value) > parseInt(".$nb_jr_bloq_cre_avant_ech_max."))
                 {
                 msg +='- ".sprintf(_("Le nombre de jours pour bloquer le crédit avant échéance doit être inférieur ou égal à %s jours"),$nb_jr_bloq_cre_avant_ech_max)." \\n';
                 ADFormValid=false;
               }";

  }

  $Myform->addJS(JSP_BEGIN_CHECK, "test", $js_check);

  // Initialisation de champs dèsque le champ mnt_octr est activé
  $js_mnt_reset = "function resetMnt(id_doss) { \n";
  $js_mnt_reset .= "var mnt_dem = 'mnt_dem'+id_doss;\n";
  $js_mnt_reset .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
  $js_mnt_reset .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
  $js_mnt_reset .= "var gar_num ='gar_num'+id_doss;\n";
  $js_mnt_reset .= "var gar_mat ='gar_mat'+id_doss;\n";
  $js_mnt_reset .= "var gar_tot ='gar_tot'+id_doss;\n";
  $js_mnt_reset .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_dem).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_assurance).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_commission).value ='';\n";
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_num).value ='';\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_mat).value ='';\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_tot).value ='';\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_num_encours).value ='';\n";
  }
  $js_mnt_reset .= "}\n";
  $Myform->addJS(JSP_FORM,"js_mnt_reset",$js_mnt_reset);

  // Calule du montant de l'assurance, de la commission et des garanties en fonction du montant demander
  $prc_assurance = $SESSION_VARS['infos_prod']["prc_assurance"];
  $prc_commission = $SESSION_VARS['infos_prod']["prc_commission"];
  $mnt_commission = $SESSION_VARS['infos_prod']["mnt_commission"];
  $prc_gar_num = $SESSION_VARS['infos_prod']["prc_gar_num"];
  $prc_gar_mat = $SESSION_VARS['infos_prod']["prc_gar_mat"];
  $prc_gar_tot = $SESSION_VARS['infos_prod']["prc_gar_tot"];
  $prc_gar_encours = $SESSION_VARS['infos_prod']["prc_gar_encours"];
  $js_mnt_init = "function initMnt(id_doss) { \n";
  $js_mnt_init .= "var mnt_dem = 'mnt_dem'+id_doss;\n";
  $js_mnt_init .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
  $js_mnt_init .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
  $js_mnt_init .= "var gar_num ='gar_num'+id_doss;\n";
  $js_mnt_init .= "var gar_mat ='gar_mat'+id_doss;\n";
  $js_mnt_init .= "var gar_mat ='gar_tot'+id_doss;\n";
  $js_mnt_init .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";
  $js_mnt_init .="\t\tdocument.ADForm.eval(mnt_assurance).value = Math.round(".$prc_assurance."*parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(mnt_assurance).value =formateMontant(document.ADForm.eval(mnt_assurance).value);\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(mnt_commission).value = Math.round((".$prc_commission."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))+ ".$mnt_commission.")*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(mnt_commission).value =formateMontant(document.ADForm.eval(mnt_commission).value);\n";
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num).value =Math.round(".$prc_gar_num."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num).value =formateMontant(document.ADForm.eval(gar_num).value);\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_mat).value =Math.round(".$prc_gar_mat."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_mat).value =formateMontant(document.ADForm.eval(gar_mat).value);\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_tot).value =Math.round(".$prc_gar_tot."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_tot).value =formateMontant(document.ADForm.eval(gar_tot).value);\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num_encours).value =Math.round(".$prc_gar_encours."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num_encours).value = formateMontant(document.ADForm.eval(gar_num_encours).value);\n";
  }
  $js_mnt_init .= "}";
  $Myform->addJS(JSP_FORM,"js_mnt_init",$js_mnt_init);

  //Les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  if ($gar_mob) {
    // On peut modifier les garanties bloquées
    $Myform->addFormButton(1,2,"mobiliser_gar", _("Mobilisation garanties"), TYPB_SUBMIT);
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_PROCHAIN_ECRAN, "Mdd-6");
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_CHECK_FORM, false);
    $Myform->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
    $Myform->setFormButtonProperties("valider",BUTP_PROCHAIN_ECRAN,"Mdd-5");
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_JS_EVENT, array("onclick" => $js_copie_mnt_dem));
  } else {
    $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN,"Mdd-5");
  }

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN,"Gen-11");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $Myform->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $js_copie_mnt_dem));

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Mdd-5 : Echéancier théorique */
else if ($global_nom_ecran == "Mdd-5") {
  // détermination du prochain écran
  $gar_mob = false;

  // Remplissage de $SESSION_VARS avec les données postées au serveur, données qui seront ensuite transférées dans DATA .
  $HTML_code = '';
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Si on vient de Mdd-2 alors mémoriser les données modifiables
    if (strstr($global_nom_ecran_prec,"Mdd-2")) {
//      $SESSION_VARS['infos_doss'][$id_doss]["cre_etat"] = $ {'cre_etat'.$id_doss};
//      $SESSION_VARS['infos_doss'][$id_doss]["cre_retard_etat_max_jour"] = $ {'cre_retard_etat_max_jour'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["delai_grac"] = $ {'delai_grac'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["nb_jr_bloq_cre_avant_ech"] = $ {'nb_jr_bloq_cre_avant_ech'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["id_agent_gest"] = $ {'HTML_GEN_LSB_id_agent_gest'.$id_doss};

      if (isset($ {'prelev_auto'.$id_doss}))
        $SESSION_VARS['infos_doss'][$id_doss]["prelev_auto"] = "t";
      else
        $SESSION_VARS['infos_doss'][$id_doss]["prelev_auto"] ="f";

      if ($val_doss["etat"] == 1) {
        $SESSION_VARS['infos_doss'][$id_doss]['cpt_liaison'] = $ {'cpt_liaison'.$id_doss};
        if($SESSION_VARS["is_cpte_remb"] == "t") {
            $SESSION_VARS['infos_doss'][$id_doss]['cpt_remb'] = ${'cpt_remb' . $id_doss}; // AT-200
        }else{
            $SESSION_VARS['infos_doss'][$id_doss]['cpt_remb'] = null;
        }
        $SESSION_VARS['infos_doss'][$id_doss]['id_bailleur'] = $ {'id_bailleur'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['obj_dem'] = $ {'obj_dem'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem_bis'] = $ {'detail_obj_dem_bis'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem_2'] = $ {'detail_obj_dem_2'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'] = recupMontant($ {'hid_mnt_dem'.$id_doss}); // champ caché
        $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"] = $ {'duree_mois'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"] = $ {'differe_jours'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"] = $ {'differe_ech'.$id_doss};

        // Récupérations des dossiers fictifs dans le cas de GS avec dossier unique
        if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"] == 1) {
          foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
            //$SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['id_bailleur'] = $ {'id_bailleur_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['obj_dem'] = $ {'obj_dem_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem'] = $ {'detail_obj_dem_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_bis'] = $ {'detail_obj_dem_bis_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_2'] = $ {'detail_obj_dem_2_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['mnt_dem'] = recupMontant($ {'mnt_dem_fic'.$id_fic});
          }

          // Calcul des garanties numéraires
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
            $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_num"], $global_monnaie_prec);
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
            $SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_mat"], $global_monnaie_prec);
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
            $SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_tot"], $global_monnaie_prec);
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
            $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_encours"], $global_monnaie_prec);
          }
        }
      }

      if ($val_doss["etat"] == 5) {
        $SESSION_VARS['infos_doss'][$id_doss]['id_bailleur'] = ${'id_bailleur' . $id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['obj_dem'] = ${'obj_dem' . $id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem'] = ${'detail_obj_dem' . $id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis' . $id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem_2'] = ${'detail_obj_dem_2' . $id_doss};
        if($SESSION_VARS["is_cpte_remb"] == "t") {
            $SESSION_VARS['infos_doss'][$id_doss]['cpt_remb'] = $ {'cpt_remb'.$id_doss};//AT-200
        }else{
            $SESSION_VARS['infos_doss'][$id_doss]['cpt_remb'] = null;
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"] == 1) {
          foreach ($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic => $val_fic) {
            //$SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['id_bailleur'] = ${'id_bailleur_fic' .$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['obj_dem'] = ${'obj_dem_fic' . $id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem'] = ${'detail_obj_dem_fic' . $id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis_fic' . $id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_2'] = ${'detail_obj_dem_2_fic' . $id_fic};
          }
        }
      }

      //champsextras
      if(sizeof( $SESSION_VARS['infos_doss'][$id_doss]['champsExtras'])> 0 ) {
    	$SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues'] = HTML_Champs_Extras::buildDataChampsEXtrasValues($SESSION_VARS['infos_doss'][$id_doss]['champsExtras'],$_POST);
      }
    }
  
   //code_commenter suite à ticket 513: nouveau code inserez
  /* *********************************************************************

    // Appel de la fonction echéancier théorique
    $echeancier = calcul_echeancier_theorique($val_doss['id_prod'], $SESSION_VARS['infos_doss'][$id_doss]["mnt_dem"], $SESSION_VARS['infos_doss'][$id_doss]['duree_mois'], $SESSION_VARS['infos_doss'][$id_doss]['differe_jours'],$SESSION_VARS['infos_doss'][$id_doss]['differe_ech']);

    //Appel de l'affichage de l'échéancier
    $parametre["lib_date"]= _("Date du jour");//Libellé de la date théorique de déboursement
    $parametre["index"]= 0; //Index de début des n° d'echéance
    $parametre["titre"]= _("Echéancier théorique de remboursement");//Titre de l'éc\héancier
    $parametre["nbre_jour_mois"]= 30;//Durée théorique du mois en nombre de jour
    $parametre["montant"]= recupMontant($SESSION_VARS['infos_doss'][$id_doss]["mnt_dem"]);//Montant en capital
    $parametre["mnt_reech"]= 0; //Montant rééchelonnement
    $parametre["mnt_octr"]= recupMontant($SESSION_VARS['infos_doss'][$id_doss]["mnt_octr"]);//Montant octroyé
    $parametre["garantie"]= $SESSION_VARS['infos_doss'][$id_doss]['infos_doss'][$id_doss]["gar_num"]+$SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"];
    $parametre["duree"]= $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"];//Durée du crédit (en mois)
    $parametre["date"]= date("d/m/Y");// Date théorique de déboursement
    $parametre["id_prod"]= $val_doss['id_prod'];
    $parametre["id_doss"]= -1; // Si id_doss=-1 alors l'echéancier n'est pas sauvegardé (sera enregistré lors de l'approbation)
    $parametre["differe_jours"] = $SESSION_VARS['infos_doss'][$id_doss]['differe_jours'];
    $parametre["differe_ech"] = $SESSION_VARS['infos_doss'][$id_doss]['differe_ech'];
    $parametre["EXIST"]=0;//Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
    $parametre["id_client"]=$SESSION_VARS['infos_doss'][$id_doss]["id_client"];

    $HTML_code .= HTML_echeancier($parametre,$echeancier);

    if ($val_doss["etat"] == 1 AND $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0)
      $gar_mob = true; 
   ***************************************************************************** */


  $parametre = array();

    //nouveau gestion : 513 _utilisation de getEcheancier au lieu de calculEcheancier car le dossier existe deja
    //ensuite que les dossier avec ces 4 etat on droit a modification du dossier
  if(($val_doss["etat"] == 5) || ($val_doss["etat"] == 13) || ($val_doss["etat"] == 2) || ($val_doss["etat"] == 1)) {
      // Appel de l'affichage de l'échéancier
      $parametre["id_client"] = $SESSION_VARS['infos_doss'][$id_doss]['id_client'];
      $parametre["lib_date"]=_("Date de déboursement");
      $parametre["index"]= '0';
      $parametre["titre"]= _("Echéancier théorique de remboursement");
      $parametre["nbre_jour_mois"]= 30;
      $parametre["mnt_reech"]= '0';

      if ($SESSION_VARS['infos_doss'][$id_doss]['cre_nbre_reech'] <=0 ) { //Pas eu de Rééch/MoratoireXXXXXX

        // Pour les dossiers en attente de decision ou acceptés, on affiche l'écheancier théorique
        if(($val_doss["etat"] == 2) || ($val_doss["etat"] == 1))
        {
          if($val_doss["etat"] == 1) { // si dossier en attente de decision, on prend le montant demandé
            $parametre["montant"]= $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
            $parametre["mnt_octr"]= $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
            $echeancier = calcul_echeancier_theorique($val_doss['id_prod'], $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'], $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"], $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"], $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"], NULL, 1, $id_doss);
          }
          else { // si dossier accepté, on prend le montant octroyé
            $parametre["montant"]= $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
            $parametre["mnt_octr"]= $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
            $echeancier = calcul_echeancier_theorique($val_doss['id_prod'], $val_doss['mnt_dem'],$val_doss['duree_mois'], $val_doss['differe_jours'], $val_doss['differe_ech'], NULL, 1, $id_doss);
          }

          $parametre["date"]= date("d/m/Y");
          $parametre["EXIST"]=0; // Vaut 0 si l'échéancier n'est pas stocké dans la BD 1 sinon
        }
        else {
          $parametre["montant"]= $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
          $parametre["mnt_octr"] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr']; //Montant octroyé
          $parametre["date"] = pg2phpDate($SESSION_VARS['infos_doss'][$id_doss]['cre_date_debloc']);
          $parametre["EXIST"]=1; // Vaut 0 si l'échéancier n'est pas stocké dans la BD 1 sinon
          $whereCond = "WHERE (id_doss='$id_doss')";
          $echeancier = getEcheancier($whereCond);
          reset($echeancier);
        }
      }

      if ($val_doss["cre_nbre_reech"]>0) { // Rééch/Moratoire
        $infos_reech = getLastRechMorHistorique(145,$SESSION_VARS['infos_doss'][$id_doss]['id_client']); //Renvoie l'historique du dernier  Rééch/Moratoire
        $datereech = $infos_reech["date"]; // Date de rééch/moratoire
        $parametre["date"] = pg2phpDate($SESSION_VARS['infos_doss'][$id_doss]['cre_date_debloc']);
        $parametre["montant"] = getSoldeCapital($id_doss);
        $parametre["mnt_reech"] = $infos_reech["infos"]; //Montant rééchelonnement
        $parametre["mnt_octr"] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr']; //Montant octroyé
        $parametre["EXIST"]=1; // Vaut 0 si l'échéancier n'est pas stocké dans la BD 1 sinon
        $whereCond = "WHERE (id_doss='$id_doss') AND (date(date_ech)>date('$datereech'))"; // Sélection les échéances après Rééch/Moratoire
        $echeancier = getEcheancier($whereCond);
        reset($echeancier);
      }

      $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]['garantie'] + $SESSION_VARS['infos_doss'][$id_doss]['garantie_encours'];
      $parametre["duree"] = $SESSION_VARS['infos_doss'][$id_doss]['duree_mois'];
      $parametre["id_prod"] = $SESSION_VARS['infos_doss'][$id_doss]['id_prod'];
      $parametre["id_doss"] = -1;//$SESSION_VARS["id_doss"];
      $parametre["differe_jours"]= $SESSION_VARS['infos_doss'][$id_doss]['differe_jours'];
      $parametre["differe_ech"]= $SESSION_VARS['infos_doss'][$id_doss]['differe_ech'];

      $HTML_code .= HTML_echeancier($parametre,$echeancier,$id_doss);
      
      if ($val_doss["etat"] == 1 AND $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0)
      	$gar_mob = true;
    }

  }

  $formEcheancier = new HTML_GEN2();

  // les boutons ajoutés
  $formEcheancier->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  if ($gar_mob)
    $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN,"Mdd-3");
  else
    $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN,"Mdd-4");

  $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Mdd-2");
  $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $formEcheancier->buildHTML();
  echo  $HTML_code;
  echo $formEcheancier->getHTML();
}
/*}}}*/

/*{{{ Mdd-3 : Blocage des garanties */
else if ($global_nom_ecran == "Mdd-3") {

  $formConf = new HTML_GEN2(_("Blocage des garanties"));

  $msg = '';
  $order = array();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $valeur_totale_bloquee = $val_doss["gar_num"] + $val_doss["gar_mat"]; // total à bloquer
    $valeur_totale_mobilisee = 0; /* la valeur totale mobilisée : garanties numéraires + garanties matérielless */

    /* Récupération des garanties qui étaient mobilisée lors de la mise en place du dossier de crédit */
    $liste_gar = getListeGaranties($id_doss);

    // Bloquer chaque garantie numéraire sur son compte de prélèvement
    foreach($val_doss['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] == 1) { // Garanties numéraires
        $mnt_gar_mob = recupMontant($value['valeur']);
        $valeur_totale_mobilisee += recupMontant($value['valeur']);

        $cpt_prelev_gar = $value['descr_ou_compte'];
        $cpteInfo = getAccountDatas($cpt_prelev_gar);

        /* Solde disponible du compte de prélèvement de la garantie = solde dipso + ancienne garantie bloquée */
        $soldeB = getSoldeDisponible($cpt_prelev_gar);

        /* Si un montant avait été bloqué sur ce compte lors de la mise en place du dossier alors l'ajouter dans le dispo */
        if ($value['id_gar'] != NULL)
          $soldeB += $liste_gar[$value['id_gar']]['montant_vente'];

        /* Si le montant nouvellement mobilisé est > au solde disponible du compte de prélèvement */
        if ($mnt_gar_mob > $soldeB)
          $msg = _("Impossible de continuer cette opération, le solde du compte de prélèvement des garanties est insuffisant.")."<br/><UL><LI>"._("Montant de la garantie")." : ".afficheMontant($mnt_gar_mob, true)."</LI><LI>"._("Solde du compte de prélèvement")." : ".afficheMontant($soldeB, true)."</LI></UL>";

        /* Ligne de séparation des garanties numéraires */
        $formConf->addHTMLExtraCode("gar".$id_doss."_".$key,"<Table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Garanties numéraires")."</b></td></tr></Table>\n");

        /* Affichage */
        $formConf->addField("intitule_compte".$id_doss."_".$key,_("Intitule du compte"),TYPC_TXT);
        $formConf->addField("num_complet_cpte".$id_doss."_".$key,_("Numéro du compte"),TYPC_TXT);
        $formConf->addField("solde".$id_doss."_".$key,_("Solde du compte"),TYPC_MNT);
        $formConf->addField("mnt_gar".$id_doss."_".$key,_("Montant des garanties"),TYPC_MNT);

        /* Ordre d'affichage des champs */
        array_push($order,"gar".$id_doss."_".$key);
        array_push($order,"intitule_compte".$id_doss."_".$key);
        array_push($order,"num_complet_cpte".$id_doss."_".$key);
        array_push($order,"solde".$id_doss."_".$key);
        array_push($order,"mnt_gar".$id_doss."_".$key);

        /* Remplissage du formulaire */
        $formConf->setFieldProperties("intitule_compte".$id_doss."_".$key, FIELDP_DEFAULT, $cpteInfo["intitule_compte"]);
        $formConf->setFieldProperties("num_complet_cpte".$id_doss."_".$key, FIELDP_DEFAULT, $cpteInfo["num_complet_cpte"]);
        $formConf->setFieldProperties("solde".$id_doss."_".$key,FIELDP_DEFAULT,$soldeB);
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key,FIELDP_DEFAULT, $mnt_gar_mob);

        /* Griser les champs */
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("solde".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("num_complet_cpte".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("intitule_compte".$id_doss."_".$key,FIELDP_IS_LABEL,true);

      }
      elseif($value['type'] == 2)
      // Garanties matérielles
      {
        $mnt_gar_mob = recupMontant($value['valeur']);
        $valeur_totale_mobilisee += recupMontant($value['valeur']);

        /* Ligne de séparation des garanties */
        $formConf->addHTMLExtraCode("gar".$id_doss."_".$key,"<Table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Garanties matérielles")."</b></td></tr></Table>\n");

        $formConf->addField("libel_gar".$id_doss."_".$key,_("Libellé des garanties"),TYPC_TXT);
        $formConf->setFieldProperties("libel_gar".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("libel_gar".$id_doss."_".$key, FIELDP_DEFAULT, $value['descr_ou_compte']);

        $formConf->addField("mnt_gar".$id_doss."_".$key,_("Valeur des garanties"),TYPC_MNT);
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key, FIELDP_DEFAULT, $mnt_gar_mob);

        /* Ordre d'affichage des champs */
        array_push($order,"gar".$id_doss."_".$key);
        array_push($order,"libel_gar".$id_doss."_".$key);
        array_push($order,"mnt_gar".$id_doss."_".$key);
      }
    } // Fin foreach garantie

    /* Vérifier que le solde total disponible n'est pas inférieur à la garantie numéraire à bloquer */
    if ($valeur_totale_bloquee > $valeur_totale_mobilisee)
      $msg = _("Impossible de continuer cette opération, le montant mobilisé est insuffisant pour le dossier")." $id_doss.<br /><UL><LI>"._("Montant de la garantie")." : ".afficheMontant($valeur_totale_bloquee, true)."</LI><LI>"._("Valeur totale mobilisée")." : ".afficheMontant($valeur_totale_mobilisee, true)."</LI></UL>";

  } // Foreach dossiers

  /* Si une erreur s'est produite */
  if ($msg != '') {
    $erreur = new HTML_erreur(_("Blocage des garanties"));

    $erreur->setMessage($msg);
    $erreur->addButton(BUTTON_OK,"Gen-11");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    exit();
  }

  // les boutons ajoutés
  $formConf->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formConf->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $formConf->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Mdd-4");
  $formConf->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");

  //$order = array("num_complet_cpte","solde","mnt_bloq","mnt","soldeF");
  $formConf->setOrder(NULL,$order);
  $formConf->buildHTML();
  echo $formConf->getHTML();

}
/*}}}*/

/*{{{ Mdd-4 : Confirmation modification d'un dossier de crédit */
else if ($global_nom_ecran == "Mdd-4") {

  // Préparation des dossnées à passer à modifDossier
  $last_id_doss = NULL; // Si GS avec dossiers multiples, on va mettre le dossier fictif dans le dernier dossier
  $mnt_dem_tot = 0; // montant total demandé
  $id_doss_fic = NULL; // id du dossier fictif dans le cas de GS avec dossiers multiples

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

    if(!empty($val_doss['id_agent_gest'])) {
      $DATA[$id_doss]['id_agent_gest'] = $val_doss['id_agent_gest'];
    }

    //  $DATA[$id_doss]['cre_etat'] = $val_doss['cre_etat'];
    //  $DATA[$id_doss]['cre_retard_etat_max_jour'] = $val_doss['cre_retard_etat_max_jour'];
    $DATA[$id_doss]['delai_grac'] = $val_doss['delai_grac'];
    $DATA[$id_doss]['nb_jr_bloq_cre_avant_ech'] = $val_doss['nb_jr_bloq_cre_avant_ech'];
    $DATA[$id_doss]['prelev_auto'] = $val_doss['prelev_auto'];
    $DATA[$id_doss]['id_bailleur'] = $val_doss['id_bailleur'];
    $DATA[$id_doss]['obj_dem'] = $val_doss['obj_dem'];
    $DATA[$id_doss]['detail_obj_dem'] = $val_doss['detail_obj_dem'];
    $DATA[$id_doss]['detail_obj_dem_bis'] = $val_doss['detail_obj_dem_bis'];
    $DATA[$id_doss]['detail_obj_dem_2'] = $val_doss['detail_obj_dem_2'];
    $DATA[$id_doss]['mnt_dem'] = $val_doss['mnt_dem'];
    $DATA[$id_doss]['cre_date_debloc'] = $val_doss['cre_date_debloc'];
    $DATA[$id_doss]['differe_jours'] = $val_doss['differe_jours'];
    $DATA[$id_doss]['differe_ech'] = $val_doss['differe_ech'];
    $DATA[$id_doss]['duree_mois'] = $val_doss['duree_mois'];
    $DATA[$id_doss]['gar_num'] = $val_doss['gar_num'];
    $DATA[$id_doss]['gar_mat'] = $val_doss['gar_mat'];
    $DATA[$id_doss]['gar_tot'] = $val_doss['gar_tot'];
    $DATA[$id_doss]['cpt_remb'] = $val_doss['cpt_remb'];
    $DATA[$id_doss]['gar_num_encours'] = $val_doss['gar_num_encours'];
    $DATA[$id_doss]["gs_cat"] = $val_doss["gs_cat"];
    $DATA[$id_doss]["id_client"] = $val_doss["id_client"];
    $DATA[$id_doss]['DATA_GAR'] = $val_doss['DATA_GAR'];
    $DATA[$id_doss]['doss_fic'] = $val_doss['doss_fic'];

    //gestion champs supplémentaire
    $DATA[$id_doss]['champsExtras'] = $val_doss['champsExtrasValues'];

    if ($val_doss['gs_cat'] == 2) {
      $last_id_doss = $id_doss;
      $mnt_dem_tot += $val_doss['mnt_dem'];
    }
  } // fin parcours des dossiers

  // Si Gs avec dossiers réels multiples et un seul dossier fictif, Ajout du dossier fictif dans le dernier dossier réel
  if ($SESSION_VARS['infos_prod']['gs_cat'] == 2) {
    $id_doss_fic = $SESSION_VARS['infos_doss'][$last_id_doss]['id_dcr_grp_sol'];
    $DATA[$last_id_doss]['doss_fic'][$id_doss_fic]['id_bailleur'] = $SESSION_VARS['infos_doss'][$last_id_doss]['id_bailleur'];
    $DATA[$last_id_doss]['doss_fic'][$id_doss_fic]['obj_dem'] = $SESSION_VARS['infos_doss'][$last_id_doss]['obj_dem'];
    $DATA[$last_id_doss]['doss_fic'][$id_doss_fic]['detail_obj_dem'] = $SESSION_VARS['infos_doss'][$last_id_doss]['detail_obj_dem'];
    $DATA[$last_id_doss]['doss_fic'][$id_doss_fic]['detail_obj_dem_bis'] = $SESSION_VARS['infos_doss'][$last_id_doss]['detail_obj_dem_bis'];
    $DATA[$last_id_doss]['doss_fic'][$id_doss_fic]['detail_obj_dem_2'] = $SESSION_VARS['infos_doss'][$last_id_doss]['detail_obj_dem_2'];
    $DATA[$last_id_doss]['doss_fic'][$id_doss_fic]['mnt_dem'] = $mnt_dem_tot;
  }

  //Mise à jour du dossier de crédit et sauvegarde des garanties
  if (modifDossier($DATA)) {
    $msg = new HTML_message(_("Confirmation de modification du dossier "));
    $msg->setMessage(_("Le dossier de crédit a été modifié avec succès !"));
    $msg->addButton(BUTTON_OK,"Gen-11");
    $msg->buildHTML();
    echo $msg->HTML_code;
  }
}

/*}}}*/

/*{{{ Mdd-6 : Affichage des garanties mobilisées */
else if ($global_nom_ecran == "Mdd-6") {
  // Affichage des garanties mobilisées pour ce dossier */
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Mobilisation des garanties"));

  // Creation d'un tableau contenant toutes les garanties des dossiers de crédit
  $xtHTML = "<br><TABLE align=\"center\">";

  // En-tête tableau : Bénéficiaire | Type | Description/compte de prélèvement | Valeur | Mod | Sup
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
  $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";
  $xtHTML .= "<td><b>"._("Type")."</b></td>";
  $xtHTML .= "<td><b>"._("Description/compte de prélèvement")."</b></td>";
  $xtHTML .= "<td><b>"._("Valeur")."</b></td>";
  $xtHTML .= "<td><b>"._("Etat")."</b></td>";
  $xtHTML .= "<td>&nbsp</td>";
  $xtHTML .= "<td>&nbsp</td></tr>";

  // Parcours des dossiers
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Si on vient de Mdd-2 alors mémoriser les données modifiables
    if (strstr($global_nom_ecran_prec,"Mdd-2")) {
      $SESSION_VARS['infos_doss'][$id_doss]["delai_grac"] = $ {'delai_grac'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["nb_jr_bloq_cre_avant_ech"] = $ {'nb_jr_bloq_cre_avant_ech'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["id_agent_gest"] = $ {'HTML_GEN_LSB_id_agent_gest'.$id_doss};
      if (isset($ {'prelev_auto'.$id_doss}))
        $SESSION_VARS['infos_doss'][$id_doss]["prelev_auto"] = "t";
      else
        $SESSION_VARS['infos_doss'][$id_doss]["prelev_auto"] ="f";

      if ($val_doss["etat"] == 1) {
        $SESSION_VARS['infos_doss'][$id_doss]['cpt_liaison'] = $ {'cpt_liaison'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['id_bailleur'] = $ {'id_bailleur'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['obj_dem'] = $ {'obj_dem'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['detail_obj_dem_bis'] = $ {'detail_obj_dem_bis'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'] = recupMontant($ {'hid_mnt_dem'.$id_doss}); // champ caché
        $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"] = $ {'duree_mois'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"] = $ {'differe_jours'.$id_doss};
        $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"] = $ {'differe_ech'.$id_doss};

        // Récupérations des dossiers fictifs dans le cas de GS avec dossier unique
        if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"] == 1) {
          foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
            //$SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['id_bailleur'] = $ {'id_bailleur_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['obj_dem'] = $ {'obj_dem_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem'] = $ {'detail_obj_dem_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_bis'] = $ {'detail_obj_dem_bis_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['mnt_dem'] = recupMontant($ {'mnt_dem_fic'.$id_fic});
          }

          // Calcul des garanties numéraires
          $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_num"], $global_monnaie_prec);
          $SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_mat"], $global_monnaie_prec);
          $SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_tot"], $global_monnaie_prec);
          $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] = round(recupMontant($ {'hid_mnt_dem'.$id_doss}) * $SESSION_VARS['infos_prod']["prc_gar_encours"], $global_monnaie_prec);
        }
      }
    } // fin si on vient de Mdd-2

    // Contenu du tableau */
    foreach($val_doss['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] != '') { /* Si la garantie n'est pas supprimée */
        /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
        if ($value['type'] ==1) { /* Garantie numéraire */
          /* Infos du compte de prélèvement des garanties */
          $CPT_PRELEV_GAR = getAccountDatas($value['descr_ou_compte']);
          $origine  = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;

        }
        elseif($value['type'] == 2) { /* garantie matérielle */
          $origine = $value['descr_ou_compte'];
        }

        $id_client = $val_doss['id_client'];
        $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
        $xtHTML .= "<td>".$id_client." ".getClientName($id_client)."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type']])."</td>";
        $xtHTML .= "<td>".$origine."</td>";
        $xtHTML .= "<td>".$value["valeur"]."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat']])."</td>";
        $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdd-8&benef=".$id_doss."&num_gar=".$key."\">Mod</A></td>";
        $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdd-9&benef=".$id_doss."&num_gar=".$key."\">Sup</A></td></tr>";
      }
    }

    //$xtHTML .= "</table><br><br>";
  } // Fin parcours dossiers
  $xtHTML .= "</table><br><br>";//AT-97 - correction affichage garanties

  $Myform->addHTMLExtraCode ("garanties", $xtHTML);
  $Myform->addFormButton(1,1,"ajout_gar", _("Nouvelle garantie"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Mdd-2');
  $Myform->setFormButtonProperties("ajout_gar", BUTP_PROCHAIN_ECRAN, 'Mdd-7');

  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Mdd-7 : Ajout d'une garantie */
else if ($global_nom_ecran == "Mdd-7") {
  // Devise du produit de crédit
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);

  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Ajout d'une garantie"));

  // Liste de choix du client (ou dossier) bénéficiare
  $choix = array();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
  $choix[$id_doss] = $val_doss['id_client']." ".getClientName($val_doss['id_client']);

  $Myform->addField ("benef", _("Bénéficiaire"), TYPC_LSB);
  $Myform->setFieldProperties("benef", FIELDP_ADD_CHOICES, $choix);
  $Myform->setFieldProperties("benef",FIELDP_IS_REQUIRED, true);

  // Ajout de certains champs de ad_gar
  $exclude = array("devise_vente","gar_num_id_cpte_prelev","gar_mat_id_bien","gar_num_id_cpte_nantie","id_doss","id_gar","etat_gar");
  $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
  $Myform->setFieldProperties("type_gar", FIELDP_JS_EVENT, array("OnChange"=>"check_type_gar()"));

  // Numéro du compte de prélèvement si garantie numéraire
  $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);
  $Myform->addLink("gar_num_id_cpte_prelev","rech_cpt",_("Rechercher"), "#");
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);
  $Myform->setLinkProperties("rech_cpt",LINKP_JS_EVENT,array("OnClick"=>"open_compte();return false;"));
  $Myform->addHiddenType("num_id_cpte_prelev", "");
  $Myform->addHiddenType("devise_vente", $SESSION_VARS['infos_prod']['devise']);

  // Etat de la garantie
  $etats_gar = array();
  // $etats_gar[1] = $adsys["adsys_etat_gar"][1];
  $etats_gar[2] = adb_gettext($adsys["adsys_etat_gar"][2]);
  $etats_gar[3] = adb_gettext($adsys["adsys_etat_gar"][3]);
  $Myform->addField ("etat_gar", _("Etat de la garantie"), TYPC_LSB);
  $Myform->setFieldProperties("etat_gar", FIELDP_ADD_CHOICES, $etats_gar);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_REQUIRED, true);

  /* Libellé de la garantie matérielle */
  $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);
  $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);

  /* Types de bien */
  $types_biens = getTypesBiens();
  $Myform->addField ("type_bien", _("Type de bien"), TYPC_LSB);
  $Myform->setFieldProperties("type_bien", FIELDP_ADD_CHOICES, $types_biens);
  $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);

  /* Numéro du client si garantie du matérielle */
  $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);
  $Myform->addHiddenType("num_client_rel");

  $Myform->addLink("num_client", "rech_client", _("Rechercher"), "#");
  $Myform->setLinkProperties("rech_client",LINKP_JS_EVENT,array("OnClick"=>"rech_client();"));
  $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);

  /* Pièce justificative si garanties matérielles */
  $Myform->addField ("piece_just", _("Pièce justificative"), TYPC_TXT);
  $Myform->setFieldProperties("piece_just",FIELDP_IS_LABEL,true);

  /* Remarque si garanties matérilles */
  $Myform->addField ("remarq", _("Remarque"), TYPC_TXT);
  $Myform->setFieldProperties("remarq",FIELDP_IS_LABEL,true);

  /* Traitement à effectuer : ajout, modification ou suppression de garantie */
  $Myform->addHiddenType("traitement", "ajout");

  /* Order d'affichage des champs */
  $order = array ("benef","type_gar","gar_num_id_cpte_prelev","libel_gar_mat","type_bien","piece_just","remarq","num_client","montant_vente", "etat_gar");
  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Mdd-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Mdd-10');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .="\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == '')) ";
  $JS_valide .="\n\t{msg+='"._("La description, le type du matériel et le cleint garant doivent être renseignés")."'; ADFormValid = false;}";

  $Myform->addJS(JSP_BEGIN_CHECK, "valJS", $JS_valide);

  /* JS : active ou désactive des champs selon le type de garantie */
  $JS_active = "";
  $JS_active .="\nfunction check_type_gar()";
  $JS_active .="\n{";
  $JS_active .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1)";
  $JS_active .="\n\t{";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=2;"; /* Garanties numéraires prêtes */
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse if(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;"; /* Garanties mat en cours de mobilisation ou prête */
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;"; /* Garanties mat en cours de mobilisation ou prête */
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\n\t}";
  $JS_active .="\n}";

  /* JS : recherche du compte de prélèvement si garantie numéraire */
  $JS_prelev="";
  $JS_prelev .="\nfunction open_compte()\n";
  $JS_prelev .="{\n";
  $JS_prelev .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1)";
  $JS_prelev .="\n{url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=gar_num_id_cpte_prelev&id_cpt_dest=num_id_cpte_prelev&devise=".$SESSION_VARS["devise"]."';\n";
  $JS_prelev .="\t\tgarant = OpenBrwXY(url, 'Compte de prélèvement', 400, 500);\n";
  $JS_prelev .="\t}\n";
  $JS_prelev .="\telse return false;\n";
  $JS_prelev .="}\n";

  /* JS : recherche du client si garantie numéraire */
  $JS_cli = "";
  $JS_cli .="\nfunction rech_client()\n";
  $JS_cli .="{\n";
  $JS_cli .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_cli .="\n{OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client&num_client_dest=num_client_rel', '"._("Recherche'").");";
  $JS_cli .="\t}\n";
  $JS_cli .="\telse return false;\n";
  $JS_cli .="}\n";

  /* Ajout du code JavaScript  */
  $Myform->addJS(JSP_FORM,"check", $JS_active);
  $Myform->addJS(JSP_FORM,"rech", $JS_prelev);
  $Myform->addJS(JSP_FORM,"rech_clt", $JS_cli);

  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Mdd-8 : Modification d'une garantie */
else if ($global_nom_ecran == "Mdd-8") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Modification d'une garantie"));

  // Liste de choix du client (ou dossier) bénéficiare
  $choix = array();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
  $choix[$id_doss] = $val_doss['id_client']." ".getClientName($val_doss['id_client']);

  $Myform->addField ("benef", _("Bénéficiaire"), TYPC_LSB);
  $Myform->setFieldProperties("benef", FIELDP_ADD_CHOICES, $choix);
  $Myform->setFieldProperties("benef",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties("benef",FIELDP_DEFAULT, $benef);

  $exclude = array("devise_vente", "gar_num_id_cpte_prelev", "gar_mat_id_bien", "gar_num_id_cpte_nantie", "id_doss", "id_gar","etat_gar");
  $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
  $Myform->setFieldProperties("type_gar", FIELDP_JS_EVENT, array("OnChange"=>"check_type_gar()"));

  /* Compte de prélèvement de la garantie numéraire */
  $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);
  $Myform->addLink("gar_num_id_cpte_prelev","rechercher",_("Rechercher"), "#");
  $Myform->setLinkProperties("rechercher",LINKP_JS_EVENT,array("OnClick"=>"open_compte();return false;"));
  $Myform->addHiddenType("num_id_cpte_prelev", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
  // ticket #392
  $Myform->addHiddenType("num_id_cpte_prelev_ancien", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);

  /* Etat de la garantie */
  $etats_gar = array();
  $etats_gar[2] = adb_gettext($adsys["adsys_etat_gar"][2]);
  $etats_gar[3] = adb_gettext($adsys["adsys_etat_gar"][3]);
  $Myform->addField ("etat_gar", _("Etat de la garantie"), TYPC_LSB);
  $Myform->setFieldProperties("etat_gar", FIELDP_ADD_CHOICES, $etats_gar);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_REQUIRED, true);

  /* Libellé du matériel */
  $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);

  /* Pièce justificative si garanties matérielles */
  $Myform->addField ("piece_just", _("Pièce justificative"), TYPC_TXT);

  /* Remarque si garanties matérilles */
  $Myform->addField ("remarq", _("Remarque"), TYPC_TXT);

  /* Types de bien */
  $types_biens = getTypesBiens();
  $Myform->addField ("type_bien", _("Type de bien"), TYPC_LSB);
  $Myform->setFieldProperties("type_bien", FIELDP_ADD_CHOICES, $types_biens);

  /* Numéro du client si garantie du matérielle */
  $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);
  $Myform->addHiddenType("num_client_rel", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['num_client']);

  /* Lien rechercher */
  $Myform->addLink("num_client", "rech_client", _("Rechercher"), "#");
  $Myform->setLinkProperties("rech_client",LINKP_JS_EVENT,array("OnClick"=>"rech_client();"));
  $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);

  $Myform->addHiddenType("traitement", "modification");
  $Myform->addHiddenType("num_gar", $num_gar);

  $Myform->setFieldProperties("type_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type']);
  $Myform->setFieldProperties("montant_vente", FIELDP_DEFAULT, recupMontant($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur']));
  
  // ticket #392
  $Myform->addHiddenType("montant_vente_ancien", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur']);
  
  $Myform->setFieldProperties("etat_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['etat']);
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);

  if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] ==1) { /* garanties numéraires */
    $Myform->setFieldProperties("etat_gar",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("piece_just",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("remarq",FIELDP_IS_LABEL,true);

    if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] != '') {
      $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev", FIELDP_DEFAULT, $CPT_PRELEV_GAR["num_complet_cpte"]);
    }
  }
  elseif($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] ==2) {
    $Myform->setFieldProperties("libel_gar_mat", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
    $Myform->setFieldProperties("type_bien", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type_bien']);
    $Myform->setFieldProperties("piece_just", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['piece_just']);
    $Myform->setFieldProperties("remarq", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['remarq']);
    $Myform->setFieldProperties("num_client", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['num_client']);
  }

  $order = array ("benef","type_gar", "gar_num_id_cpte_prelev", "libel_gar_mat", "type_bien", "num_client","piece_just","remarq","montant_vente", "etat_gar");
  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Mdd-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Mdd-10');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .="\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == ''))";
  $JS_valide .="\n\t{msg+='"._("Le libellé et le type du matériel doivent être renseignés")."'; ADFormValid = false;}";

  $Myform->addJS(JSP_BEGIN_CHECK, "valJS", $JS_valide);

  /* JS : recherche du compte de prélèvement des garanties numéraires */
  $JS_prelev ="";
  $JS_prelev .="\nfunction open_compte()\n";
  $JS_prelev .="{\n";
  $JS_prelev .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1){url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=gar_num_id_cpte_prelev&id_cpt_dest=num_id_cpte_prelev&devise=".$SESSION_VARS["devise"]."';\n";
  $JS_prelev .="\t\tgarant = OpenBrwXY(url, '"._("Autre personne garante")."', 400, 500);\n";
  $JS_prelev .="\t}\n";
  $JS_prelev .="\telse return false;\n";
  $JS_prelev .="}\n";

  /* JS : active ou désactive des champs selon le type de garantie */
  $JS_active = "";
  $JS_active .="\nfunction check_type_gar()";
  $JS_active .="\n{";
  $JS_active .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1)";
  $JS_active .="\n\t{";
  $JS_active .="\ndocument.ADForm.etat_gar.selectedIndex=2;";
  $JS_active .="\ndocument.ADForm.etat_gar.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse if(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.etat_gar.disabled = false;";
  $JS_active .="\ndocument.ADForm.etat_gar.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n}";

  /* JS : recherche du client si garantie numéraire */
  $JS_cli = "";
  $JS_cli .="\nfunction rech_client()\n";
  $JS_cli .="{\n";
  $JS_cli .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_cli .="\n{OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client&num_client_dest=num_client_rel', '"._("Recherche")."');";
  $JS_cli .="\t}\n";
  $JS_cli .="\telse return false;\n";
  $JS_cli .="}\n";

  /* Ajout du code JS  */
  $Myform->addJS(JSP_FORM,"prelev", $JS_prelev);
  $Myform->addJS(JSP_FORM,"actve", $JS_active);
  $Myform->addJS(JSP_FORM,"cli", $JS_cli);

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Mdd-9 : Suppression d'une garantie */
else if ($global_nom_ecran == "Mdd-9") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Suppression d'une garantie"));
  // Nom client bénéficiaire
  $nom_client = $SESSION_VARS['infos_doss'][$benef]['id_client']." ".getClientName($SESSION_VARS['infos_doss'][$benef]['id_client']);
  $Myform->addField ("beneficiaire", _("Bénéficiaire"), TYPC_TXT);
  $Myform->setFieldProperties("beneficiaire", FIELDP_DEFAULT, $nom_client);
  $Myform->setFieldProperties("beneficiaire",FIELDP_IS_LABEL,true);

  if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] == 1 ) {
    $exclude = array("gar_num_id_cpte_prelev", "gar_mat_id_bien","gar_num_id_cpte_nantie","id_doss","id_gar");
    $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
    $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);

    /* Si garantie numéraire, afficher le numéro complet du compte de prélèvement */
    if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] != '') {
      $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev", FIELDP_DEFAULT, $CPT_PRELEV_GAR["num_complet_cpte"]);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);
    }

    $order = array ("beneficiaire","type_gar", "gar_num_id_cpte_prelev","montant_vente", "devise_vente", "etat_gar");
  }
  elseif($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] == 2 ) {
    /* Champs à exclure */
    $exclude = array("gar_num_id_cpte_prelev", "gar_mat_id_bien" , "gar_num_id_cpte_nantie", "id_doss", "id_gar");

    $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
    $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);
    $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);

    $types_biens = getTypesBiens();
    $Myform->addField ("type_bien", _("Type de bien"), TYPC_TXT);

    $Myform->setFieldProperties("libel_gar_mat", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
    $id_type_bien = $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type_bien'];

    $Myform->setFieldProperties("type_bien", FIELDP_DEFAULT, $types_biens[$id_type_bien]);
    $Myform->setFieldProperties("num_client", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['num_client']);

    $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);
    $order = array ("beneficiaire","type_gar", "libel_gar_mat", "type_bien", "montant_vente", "etat_gar");
  }

  $Myform->addHiddenType("traitement", "suppression");
  $Myform->addHiddenType("num_gar", $num_gar);
  $Myform->addHiddenType("benef", $benef);

  /* Champs communs */
  $Myform->setFieldProperties("type_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type']);
  $Myform->setFieldProperties("montant_vente", FIELDP_DEFAULT, recupMontant($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur']));
  $Myform->setFieldProperties("etat_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['etat']);
  $Myform->setFieldProperties("devise_vente", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['devise_vente']);

  $Myform->setFieldProperties("type_gar",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("montant_vente",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("devise_vente",FIELDP_IS_LABEL,true);

  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"supprimer", _("Supprimer"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Mdd-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("supprimer", BUTP_PROCHAIN_ECRAN, 'Mdd-10');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Mdd-10 : Confirmation d'ajout, de modification ou de suppression d'une garantie */
else if ($global_nom_ecran == "Mdd-10") {
  $Myform = new HTML_message(_("Confirmation"));

  // Ajout ou modification de garantie
  if ($traitement == "ajout" or $traitement == "modification" ) {
    if ($traitement == "ajout") { // Ajout de garantie
      $Myform->setMessage(_("La garantie a été ajoutée avec succès"));
      $num_gar = 1 + count($SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR']);
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['id_gar'] = NULL;
    } else // Modification de garantie
      $Myform->setMessage(_("La garantie a été modifiée avec succès"));

    $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type'] = $type_gar ;
    if ($type_gar == 1) {
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['id_client'];
      
      // Recup ancien compte prelev et montant vente
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte_ancien'] = $num_id_cpte_prelev_ancien;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur_ancien'] = $montant_vente_ancien;
      
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $num_id_cpte_prelev;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = NULL;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = NULL;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = $montant_vente;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = 2; /* Prête pour les numéraires car bloquées dans le compte */
    } else if ($type_gar == 2) {
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['id_client'];
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $libel_gar_mat;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = $num_client_rel;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = $HTML_GEN_LSB_type_bien;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['piece_just'] = $piece_just;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['remarq'] = $remarq;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = $montant_vente;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = $etat_gar;
    }

    if ($traitement == "ajout")
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['devise_vente'] = $devise_vente;

  }
  elseif($traitement == "suppression") {
    /* Suppression de garantie */
    $Myform->setMessage(_("La garantie a été supprimée avec succès"));
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] = '' ;
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] ='';
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur'] = '';
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['etat'] = '';
  }

  $Myform->addButton(BUTTON_OK, 'Mdd-6');
  $Myform->buildHTML();
  echo $Myform->HTML_code;
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>