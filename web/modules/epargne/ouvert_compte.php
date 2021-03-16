<?Php
/*

Ajout d'un nouveau compte pour un client

Description :
  Ce module crée 3 écrans :
  * Ocp-1 : Choix d'un produit d'épargne
  * Ocp-2 : Versement initial sur le compte à créer
  * Ocp-Z : Transfert à partir du compte d'origine
  * Ocp-5 : Confirmation du montant initial à verser
  * Ocp-6 : Confirmation de la création du compte

Fonctions définies :
  function depot_cpte_ouverture($id_guichet, $id_cpte, $montant)
  function depot_cpte_ouverture_par_transfert($id_cpte_source, $id_cpte_destination, $montant)

Notes :
  Utiliser SESSION_VARS[] pour la manipulation de variables globales entre écrans

Auteur :
  HD - Créé le : 25/01/2002


*/

require_once 'lib/dbProcedures/interface.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/html/HTML_message.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'modules/epargne/recu.php';
require_once 'modules/rapports/xml_devise.php';

global $global_monnaie_courante, $global_id_agence;

/*error_reporting(E_ALL);
ini_set("display_errors", "on");*/

//-------------------------------------------------------------------
//----------- Ocp-1 Choix du type de produit ------------------------
//-------------------------------------------------------------------
if ($global_nom_ecran == "Ocp-1") {
  $SESSION_VARS=array();
  $html = new HTML_GEN2();
  $html->setTitle(_("Choix d'un produit d'épargne"));
  //ajout des tables de la base : comptes et produits d'épargne
  $include_comptes = array("id_prod","dat_num_certif");
  $html->addTable("ad_cpt", OPER_INCLUDE, $include_comptes);

  $html->addField("devise",_("Devise"),TYPC_TXT);
  $html->setFieldProperties("devise",FIELDP_IS_LABEL, true);

  //choix encaissement guichet ou par transfert
  $html->addField("TypeEncaisse", _("Type d'encaissement"), TYPC_LSB);
  $choix = array(1=>_("Au guichet"), 2=>_("Par transfert"));
  $html->setFieldProperties("TypeEncaisse", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("TypeEncaisse", FIELDP_IS_REQUIRED, true);
  //choix encaissement guichet ou par transfertdu rang

  $Where="WHERE id_ag = $global_id_agence AND id_titulaire = $global_id_client";
  $DATA =getComptesClients($Where);
  
  //recupération des infos sur l'agence
  $info_agence = getAgenceDatas($global_id_agence);

  $rang1=array();
  if (is_array($DATA)) {
    foreach($DATA as $key=>$value)
    array_push($rang1, $value["num_cpte"]);
  }

  for ($i=1;$i<500;$i++)
    if (!in_array($i,$rang1))
      $choix1[$i]=sprintf("%02d",$i);

  $html->addField("rang", _("Rang"), TYPC_LSB);
  $html->setFieldProperties("rang", FIELDP_ADD_CHOICES, $choix1);
  $html->setFieldProperties("rang", FIELDP_HAS_CHOICE_AUCUN,false);
  $html->setFieldProperties("rang", FIELDP_IS_REQUIRED, true);

  $DATA=getAgenceDatas($global_id_agence);
  //Activation et déactivation du rang
  if ($DATA["type_numerotation_compte"] == 1)
    $html->setFieldProperties("rang", FIELDP_IS_LABEL,"false");

  //sélectionner les produits d'épargne possibles pour ce client. On respecte le nombre d'occurrences permises en fonction des produits que possède déjà le client en question
  $ListProdEpargne = getListProdEpargneDispo($global_id_client);
  $choix = array();

  if (is_array($ListProdEpargne)) {
    foreach($ListProdEpargne as $value) array_push($choix, $value["id"]);
  };

  // javacript pour le choix du produit d'épargne
  $codeJs="
          function getInfoProdEpar() {
          ";

  if (isset($ListProdEpargne)) {
    foreach($ListProdEpargne as $value) {
      $codeJs .= "
                 if (document.ADForm.HTML_GEN_LSB_id_prod.value == ".$value["id"].")
                 {
                 document.ADForm.devise.value = '".$value["devise"]."';";
      if ($value["certif"] == 'f') {
        $codeJs .= "
                   document.ADForm.dat_num_certif.disabled = true;";
      }
      if ($value["certif"] == 't') {
        $codeJs .= "
                   document.ADForm.dat_num_certif.disabled = false;";
      }
      $codeJs .= "
               }";
    }
  }
  $codeJs.= "
          }";

  $html->setFieldProperties("id_prod", FIELDP_INCLUDE_CHOICES, $choix);
  $html->setFieldProperties("id_prod", FIELDP_JS_EVENT, array("onChange"=>"getInfoProdEpar();"));
  $html->addJS(JSP_FORM, "JS1", $codeJs);
  $html->addLink("id_prod","produit",_("Détails produit"),"#");
  $html->setLinkProperties("produit",LINKP_JS_EVENT,array("onClick"=>"open_produit();return false;"));
  $codeJs="function open_produit()
        {
          id_prod = document.ADForm.HTML_GEN_LSB_id_prod.value;
          if (id_prod > 0) {
          url='$http_prefix/lib/html/prodEpargne.php?m_agc=".$_REQUEST['m_agc']."&id=' + id_prod;
          EpargneWindow=window.open(url,'Produit Epargne','always Raised=1,dependant=1,scrollbars,resizable=0,width=550,height=600');
        }
          return false;
        }";
  $html->addJS(JSP_FORM, "JS2", $codeJs);
  $ordre=array("id_prod", "devise","rang","TypeEncaisse","dat_num_certif");
  $html->setOrder(NULL,$ordre);

  //modification pour export Netbank
  if($info_agence['utilise_netbank'] == 't'){
  	$html->addField("export_netbank", _("Compte Netbank ?"), TYPC_BOL);
  	$html->setFieldProperties("export_netbank", FIELDP_DEFAULT, false);
  	//on les stocke dans des variables session	
		$SESSION_VARS["utilise_netbank"] = $info_agence['utilise_netbank'];
  }

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ocp-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();
}

//-------------------------------------------------------------------
//----------- Ocp-2 Intitulé du compte ------------------------------
//-------------------------------------------------------------------
else if ($global_nom_ecran == "Ocp-2") {

  global $adsys;

  // Verifier les droits de modification sur les parametres d'epargne
  $hasAccessModify = check_access(58);

  if ($global_nom_ecran_prec == "Ocp-1") { // Si on vient de Ocp-1
    //création du num_cpte_complet
    $num_complet_cpte = makeNumCpte($global_id_client,$rang);
    $SESSION_VARS["num_complet_cpte"] = $num_complet_cpte; //comme le champ est disabled, on ne peut pas poster la variable sur le prochain formulaire, on doit donc la préserver
    $SESSION_VARS["num_cpte"] = $rang;
    if($export_netbank){
  	$SESSION_VARS["export_netbank"] = 't';
	  } else {
	  	$SESSION_VARS["export_netbank"] = 'f';
	  }		
  }

  unset($SESSION_VARS['NumCpteSource']);

  $SESSION_VARS['change']=array();

  if (isset($id_prod))
    $SESSION_VARS["id_prod"] = $id_prod; //produit du compte à créer
  if (isset($TypeEncaisse))
    $SESSION_VARS["TypeEncaisse"] = $TypeEncaisse; //gestion du type d'encaissement : guichet ou transfert
  if (isset($dat_num_certif))
    $SESSION_VARS["dat_num_certif"] = $dat_num_certif;

  //chercher les renseignements pour le produit choisi correspondant au compte à créer afin de contrôler le versement initial
  $InfoProduit = getProdEpargne($SESSION_VARS["id_prod"]);//produit du compte à créer
  setMonnaieCourante($InfoProduit['devise']);

  $taux_int_prod = $InfoProduit['tx_interet'];
  $taux_int_prod_affiche = affichePourcentage($taux_int_prod, 1, false, ".");
  $terme_prod = $InfoProduit['terme'];
  $freq_calc_int_prod = $InfoProduit['freq_calcul_int'];
  $mode_calc_int_prod = $InfoProduit['mode_calcul_int'];

  $isDepotAVue = false;
  if($InfoProduit['classe_comptable'] == 1) $isDepotAVue = true;

  if ( ($InfoProduit["certif"] == 't') && (empty($SESSION_VARS["dat_num_certif"]))) {
    $html_err = new HTML_erreur(_("Echec lors de l'ouverture du compte "));
    $html_err->setMessage(_("Le numéro de certificat du DAT n'a pas été renseigné"));
    $html_err->addButton("BUTTON_OK", 'Ocp-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    // récupération des données sur le produit sélectionné pour insertion dans la base de données
    $SESSION_VARS["num_cpte"] = $rang;
    $SESSION_VARS["id_titulaire"] = $global_id_client;
    $SESSION_VARS["mnt_min_cpte"] = $InfoProduit["mnt_min"];
    $SESSION_VARS["date_ouvert"] = date("d/m/Y");
    $SESSION_VARS["utilis_crea"] = $global_id_utilisateur;
    $SESSION_VARS["etat_cpte"] = 1; //compte ouvert
    $SESSION_VARS["dat_prolongation"] = 'f'; //false pour PostgreSQL.Si c'est un compte à terme le mettre à l'état non prolongé

    // #537 : set the default values for the added fields :
    $SESSION_VARS["tx_interet_cpte"] = $taux_int_prod_affiche;
    $SESSION_VARS["freq_calcul_int_cpte"] = $InfoProduit["freq_calcul_int"];
    $SESSION_VARS["terme_cpte"] = $InfoProduit["terme"];
    $SESSION_VARS["mode_calcul_int_cpte"] = $InfoProduit["mode_calcul_int"];

    $tx_interet_max = $InfoProduit["tx_interet_max"];
    if(empty($tx_interet_max)) $tx_interet_max = 0;
    $SESSION_VARS["tx_interet_max"] = $tx_interet_max;

    if ($InfoProduit["terme"] > 0)
      $SESSION_VARS["terme"] = $InfoProduit["terme"];
    if (isset($InfoProduit["ep_source_date_fin"]))
      $SESSION_VARS["ep_source_date_fin"] = $InfoProduit["ep_source_date_fin"];

    // déterminer les champs à afficher en fonction du produit et remplir le code javascript qui va permettre de calculer le montant net à verser = montant versé moins frais éventuels
    $fieldslabel = array();

    $html = new HTML_GEN2(_("Intitulé du compte"));
    $include_fields = array("num_complet_cpte","intitule_compte");
    $order = array("num_complet_cpte", "intitule_compte");

    /* Si c'est un compte à terme, indiqueer le compte de virement à la clôture */
    if ($InfoProduit["terme"] > 0)
      array_push($include_fields,"cpte_virement_clot");

    if ($InfoProduit["tx_interet"] > 0)
      array_push($include_fields,"cpt_vers_int");

    //#537 : Ajout champs indicatif du produit d'epargne, modifiable ici :
    array_push($include_fields,"tx_interet_cpte");
    //array_push($include_fields,"freq_calcul_int_cpte");
    array_push($include_fields,"terme_cpte");

    array_push($fieldslabel,"tx_interet_cpte");
    //array_push($fieldslabel,"freq_calcul_int_cpte");
    array_push($fieldslabel,"terme_cpte");

    $html->addTable("ad_cpt", OPER_INCLUDE, $include_fields);
    $html->setFieldProperties("num_complet_cpte", FIELDP_DEFAULT, $SESSION_VARS["num_complet_cpte"]);
    $html->setFieldProperties("num_complet_cpte", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("intitule_compte", FIELDP_DEFAULT, getClientName($global_id_client));

    // #537 : champs valeurs par defaut
    $html->setFieldProperties("tx_interet_cpte", FIELDP_DEFAULT, $SESSION_VARS["tx_interet_cpte"]);
    $html->setFieldProperties("tx_interet_cpte", FIELDP_TYPE, TYPC_PRC);
    $html->setFieldProperties("tx_interet_cpte", FIELDP_CAN_MODIFY, true);
    $html->setFieldProperties("tx_interet_cpte", FIELDP_JS_EVENT, array("onchange"=>"processChangeTauxInt(); return false;"));

    if($hasAccessModify) {
      $html->addLink("tx_interet_cpte","modify_tx_interet_cpte",_("Modifier"),"#");
      $html->setLinkProperties("modify_tx_interet_cpte", LINKP_JS_EVENT, array("onClick"=>"document.ADForm.tx_interet_cpte.disabled='';ADFormValid=false;"));
    }

    $html->addField("freq_calcul_int_cpte", _("Fréquence de calcul des interêts"), TYPC_LSB);
    $choix_freq = $adsys["adsys_freq"];
    $html->setFieldProperties("freq_calcul_int_cpte", FIELDP_ADD_CHOICES, $choix_freq);
    $html->setFieldProperties("freq_calcul_int_cpte", FIELDP_DEFAULT, $SESSION_VARS["freq_calcul_int_cpte"]);

    // Gesstion acces freq_calcul_int_cpte
    if($hasAccessModify) {
      $html->addLink("freq_calcul_int_cpte","modify_freq_calcul",_("Modifier"),"#");
      $js = "";
      $js = "if (document.ADForm.tx_interet_cpte.value == '') document.ADForm.tx_interet_cpte.value = 0;";
      $js .= "isdisabled = (document.ADForm.tx_interet_cpte.value == 0);"; // if 0, we disable
      $js .= "if (isdisabled) {";
      $js .= "alert ('Le taux d\'intérêt doit être supérieur à zéro pour pouvoir choisir la fréquence de calcul des intérêts !'); ADFormValid=false;";
      $js .= "} else { ";
      $js .= "document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.disabled='';";
      $js .= "}  return false; ";
      $html->setLinkProperties("modify_freq_calcul", LINKP_JS_EVENT, array("onClick" => $js));
    }

    $html->addField("mode_calcul_int_cpte", _("Mode de calcul d'interêt"), TYPC_LSB);
    $choix_mode_calcul = $adsys["adsys_mode_calcul_int_epargne"];
    $html->setFieldProperties("mode_calcul_int_cpte", FIELDP_ADD_CHOICES, $choix_mode_calcul);
    $html->setFieldProperties("mode_calcul_int_cpte", FIELDP_DEFAULT, $SESSION_VARS["mode_calcul_int_cpte"]);


    // Gesstion acces mode_calcul_int_cpte
    if($hasAccessModify) {
      $html->addLink("mode_calcul_int_cpte","modify_mode_calcul_int",_("Modifier"),"#");

      $js = "";
      $js = "if (document.ADForm.tx_interet_cpte.value == '') document.ADForm.tx_interet_cpte.value = 0;";
      $js .= "isdisabled = (document.ADForm.tx_interet_cpte.value == 0);"; // if 0, we disable
      $js .= "if (isdisabled) {";
      $js .= "alert ('Le taux d\'intérêt doit être supérieur à zéro pour pouvoir choisir le mode de calcul d\'interêt !'); ADFormValid=false;";
      $js .= "} else { ";
      $js .= "document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.disabled='';";
      $js .= "}";

      $html->setLinkProperties("modify_mode_calcul_int", LINKP_JS_EVENT, array("onClick"=>$js));
    }

    /*
    * Controls additionnels sur les mode de calculs et la frequence des interets
    */
    //Afficher le mode de calcul en fonction de la fréquence
    //Feq mensuelle : le solde ne peut être que : solde journalier le plus bas(2),solde courant le plus bas(3) ,le solde courant(7), le solde moyen mensuel(8) ou solde pour épargne à la source(12)
    $js_code = "\n function ModeCalcParFreq(){";
    $js_code .= "\n if (document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 1){";
    $js_code .= "\n if ((document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 0) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 2) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 3) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 7) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 8) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 12) ) {";
    $js_code .= "alert('"._("Ce mode de calcul est impossible pour une fréquence mensuelle !")."');";
    //$js_code .= "document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value = 0;\n";
    $js_code .= "resetToDefault('mode_calcul_int_cpte');";
    $js_code .= "\n} ";

    //Freq trimestrielle : solde peut être :solde journalier le + bas, solde courant le plus bas,solde mens le + bas(4),le solde courant ou le solde moyen trim(9)
    $js_code .= "\n}else if (document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 2){";
    $js_code .= "\n if((document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 0) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 2) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 3) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 4) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 7) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 9)) {";
    $js_code .= "alert('"._("Ce mode de calcul est impossible pour une fréquence trimestrielle !")."');";
    //$js_code .= "document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value = 0;\n";
    $js_code .= "resetToDefault('mode_calcul_int_cpte');";
    $js_code .= "\n} ";

    //Freq semestrielle:solde journ le + bas(2),solde courant le plus bas(3),solde mens le + bas(4),solde trim le + bas(5),solde courant(7) ou solde moyen sem(10)
    $js_code .= "\n} else if(document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 3){";
    $js_code .= "\n if ((document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 0) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 2) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 3) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 4) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 5) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 7) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 10)) {";
    $js_code .= "alert('"._("Ce mode de calcul est impossible pour une fréquence semestrielle !")."');";
     //$js_code .= "document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value = 0;\n";
    $js_code .= "resetToDefault('mode_calcul_int_cpte');";
    $js_code .= "\n} ";

    // Freq annuelle :solde journ le + bas(2),solde courant le plus bas(3), solde mens le + bas(4), solde trim le + bas(5), solde sem le + bas(6), solde courant(7), solde moyen annuel(11)
    $js_code .= "\n} else if(document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 4){";
    $js_code .= "\n if ((document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 0) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 2) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 3) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 4) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 5) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 6) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 7) && (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value != 11)) {";
    $js_code .= "alert('"._("Ce mode de calcul est impossible pour une fréquence annuelle !")."');";
    //$js_code .= "document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value = 0;\n";
    $js_code .= "resetToDefault('mode_calcul_int_cpte');";
    $js_code .= "\n} ";
    $js_code .= "\n} else if(document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 0){";
    $js_code .= "\n if (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value > 0){";
    $js_code .= "alert('"._("Il faut choisir une fréquence avant le mode de calcul des intérêts !")."');";
    //$js_code .= "document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value = 0;\n";
    $js_code .= "resetToDefault('mode_calcul_int_cpte');";
    $js_code .= "\n} ";
    $js_code .= "\n}";

    $js_code .= "\n}";

    $html->setFieldProperties("mode_calcul_int_cpte", FIELDP_JS_EVENT, array("onchange"=>"ModeCalcParFreq();"));
    $html->setFieldProperties("freq_calcul_int_cpte", FIELDP_JS_EVENT, array("onchange"=>"ModeCalcParFreq();"));


    $html->setFieldProperties("terme_cpte", FIELDP_DEFAULT, $SESSION_VARS["terme_cpte"]);
    $html->setFieldProperties("terme_cpte", FIELDP_CAN_MODIFY, true);

    // Cannot change terme for DAV
    if($hasAccessModify && !$isDepotAVue) {
      $html->addLink("terme_cpte","modify_terme",_("Modifier"),"#");
      $html->setLinkProperties("modify_terme", LINKP_JS_EVENT, array("onClick"=>"document.ADForm.terme_cpte.disabled='';return false;"));
    }

    // Controls additionnels pour le terme du compte :
    /* Contrôle du terme et de la fréquence pour les comptes à terme */
    if ($InfoProduit["classe_comptable"] == 2 or $InfoProduit["classe_comptable"] == 5) {
      /* Le terme doit être un multiple de la fréquence */
      $termeParFreq = "\n function TermeParFreq(){";
      $termeParFreq .= "\n if (document.ADForm.terme_cpte.value > 0) {";
      $termeParFreq .= "\n if((document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 2) && ((document.ADForm.terme_cpte.value % 3) !=0)){ ";
      $termeParFreq .= "alert('"._("Le terme doit être un multiple de la fréquence !")."'); resetToDefault('terme_cpte');\n}";
      $termeParFreq .= "\n if ((document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 3) && ((document.ADForm.terme_cpte.value % 6) !=0)){ ";
      $termeParFreq .= "alert('"._("Le terme doit être un multiple de la fréquence !")."'); resetToDefault('terme_cpte'); \n}";
      $termeParFreq .= "\n if ((document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 4) && ((document.ADForm.terme_cpte.value % 12) !=0)){";
      $termeParFreq .= "alert('"._("Le terme doit être un multiple de la fréquence !")."'); resetToDefault('terme_cpte'); \n}";
      $termeParFreq .= "\n} ";
      $termeParFreq .= "\n} ";
      $html->setFieldProperties("terme_cpte", FIELDP_JS_EVENT, array("onchange"=>"TermeParFreq();"));

      /* La fréquence doit être un diviseur du terme si le terme est saisi  */
      $freqParTerme = "\n function FreqCalcParTerme(){";
      $freqParTerme .= "\n if (document.ADForm.terme_cpte.value > 0) {";
      $freqParTerme .= "\n if ((document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 2) && ((document.ADForm.terme_cpte.value % 3) !=0)){ ";
      $freqParTerme .= "alert('"._("Le terme doit être un multiple de la fréquence !")."'); resetToDefault('freq_calcul_int_cpte');\n}";
      $freqParTerme .= "\n if ((document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 3) && ((document.ADForm.terme_cpte.value % 6) !=0)){ ";
      $freqParTerme .= "alert('"._("Le terme doit être un multiple de la fréquence !")."'); resetToDefault('freq_calcul_int_cpte');\n}";
      $freqParTerme .= "\n if ((document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value == 4) && ((document.ADForm.terme_cpte.value % 12) !=0)){";
      $freqParTerme .= "alert('"._("Le terme doit être un multiple de la fréquence !")."'); resetToDefault('freq_calcul_int_cpte');\n}";
      $freqParTerme .= "\n} ";
      $freqParTerme .= "\n} ";
      $html->setFieldProperties("freq_calcul_int_cpte", FIELDP_JS_EVENT, array("onchange"=>"FreqCalcParTerme();"));
    }

    $CPTS = getAccounts($global_id_client);
    $CPT_AFF = array();

    while (list($key, $CPT) = each($CPTS)) {
      /* On n ajoute que les comptes de service financiers = t, sans terme et de meme devise que le produit  */
      if (($CPT["service_financier"] == 't') && ($CPT["devise"] == $InfoProduit["devise"])){ //&& ($CPT["terme_cpte"] <= 0)){
        if ($CPT["terme_cpte"] <= 0){
          array_push($CPT_AFF, $CPT["id_cpte"]);
        }
        $soldeDispo = getSoldeDisponible($key);
        if ($CPT["terme_cpte"] > 0 && $soldeDispo == 0){
          array_push($CPT_AFF, $CPT["id_cpte"]);
        }
      }
    }

    /* Gestion du compte de versement des intérêts */
    if ($InfoProduit["tx_interet"] > 0) {
      $html->setFieldProperties("cpt_vers_int", FIELDP_INCLUDE_CHOICES, $CPT_AFF);
      $html->setFieldProperties("cpt_vers_int", FIELDP_LONG_NAME, "Autre compte");
      $html->setFieldProperties("cpt_vers_int", FIELDP_IS_REQUIRED, true);
      $html->setFieldProperties("cpt_vers_int", FIELDP_IS_LABEL, true);

      $html->addField("type_cpt_vers_int", _("Compte de versement des intérets"), TYPC_LSB);
      $CHOICES = array();
      $CHOICES[1] = _("Compte lui-meme");
      if ($InfoProduit["classe_comptable"] != 6) // compte d'épargne à la source prend le compte lui même comme compte de versement des intérêts
      $CHOICES[2] = "Autre compte";
      $html->setFieldProperties("type_cpt_vers_int", FIELDP_ADD_CHOICES, $CHOICES);
      $html->setFieldProperties("type_cpt_vers_int", FIELDP_IS_REQUIRED, true);
      $js = "if(document.ADForm.HTML_GEN_LSB_type_cpt_vers_int.value==0 || document.ADForm.HTML_GEN_LSB_type_cpt_vers_int.value==1)
          {
            document.ADForm.HTML_GEN_LSB_cpt_vers_int.selectedIndex=0;
            document.ADForm.HTML_GEN_LSB_cpt_vers_int.disabled = true;
          }
            else
            document.ADForm.HTML_GEN_LSB_cpt_vers_int.disabled = false;";
      $html->setFieldProperties("type_cpt_vers_int", FIELDP_JS_EVENT, array("onchange" => $js));
      array_push($order,"type_cpt_vers_int", "cpt_vers_int");
    }

    /* Gestion du compte de virement à la clôture */
    if ($InfoProduit["terme"] > 0) {
      $html->setFieldProperties("cpte_virement_clot", FIELDP_INCLUDE_CHOICES, $CPT_AFF);
      $html->setFieldProperties("cpte_virement_clot", FIELDP_IS_REQUIRED, false);
      array_push($order,"cpte_virement_clot");
    } 
    
    if(( ucwords($InfoProduit['retrait_unique']) == 'true' || $InfoProduit['retrait_unique'] == 't') && 
       (ucwords($InfoProduit['depot_unique']) == 'true' || $InfoProduit['depot_unique'] == 't')) {
        if ( $InfoProduit['dat_prolongeable'] == 't' ) {
          $html->addField("dat_prolongation", _("Réconduire le DAT?"), TYPC_BOL);
          $html->addField("dat_nb_reconduction", _("Nombre de reconduction du DAT?"), TYPC_INT);
          array_push($order, "dat_prolongation");
          array_push($order, "dat_nb_reconduction");
        }
    }


    // order :
    array_push($order,"tx_interet_cpte", "terme_cpte", "freq_calcul_int_cpte", "mode_calcul_int_cpte");

    $html->addField("frais_ouvert",_("Frais d'ouverture de compte"),TYPC_MNT);
    $html->setFieldProperties("frais_ouvert", FIELDP_DEFAULT,$InfoProduit["frais_ouverture_cpt"]);
    $html->setFieldProperties("frais_ouvert", FIELDP_CAN_MODIFY, true);
    $versementMinimum+=$InfoProduit['frais_ouverture_cpt'];
    $versementMaximum+=$InfoProduit['frais_ouverture_cpt'];
    array_push($fieldslabel, "frais_ouvert");
    array_push($order,"frais_ouvert");

    if ($SESSION_VARS['TypeEncaisse'] == 1) {
      $html->addField("frais_depot",_("Frais de dépôt sur le compte"),TYPC_MNT);
      $html->setFieldProperties("frais_depot", FIELDP_DEFAULT, $InfoProduit["frais_depot_cpt"]);
      $html->setFieldProperties("frais_depot", FIELDP_CAN_MODIFY, true);
      $versementMinimum+=$InfoProduit['frais_depot_cpt'];
      $versementMaximum+=$InfoProduit['frais_depot_cpt'];
      array_push($fieldslabel, "frais_depot");
      array_push($order,"frais_depot");

    }
    if ($InfoProduit["mnt_min"] > 0) {
      $html->addField("mnt_min",_("Montant minimum sur le compte"),TYPC_MNT);
      $html->setFieldProperties("mnt_min", FIELDP_DEFAULT, $InfoProduit["mnt_min"]);

      $versementMinimum+=$InfoProduit['mnt_min'];
      array_push($fieldslabel, "mnt_min");
      array_push($fieldslabel,"versMin");
      array_push($order,"mnt_min", "versMin");

      $html->addField("versMin",_("Versement Minimum"),TYPC_MNT);
      $html->setFieldProperties("versMin",FIELDP_DEFAULT,$versementMinimum);

      if ($SESSION_VARS['TypeEncaisse'] == 1) {
        $html->setFieldProperties("frais_depot",FIELDP_JS_EVENT,array("onchange"=>"document.ADForm.versMin.value=formateMontant(recupMontant(document.ADForm.frais_depot.value)+recupMontant(document.ADForm.mnt_min.value)+recupMontant(document.ADForm.frais_ouvert.value));"));
        $html->setFieldProperties("frais_ouvert",FIELDP_JS_EVENT,array("onchange"=>"document.ADForm.versMin.value=formateMontant(recupMontant(document.ADForm.frais_ouvert.value)+recupMontant(document.ADForm.mnt_min.value)+recupMontant(document.ADForm.frais_depot.value));"));
      } else {
        $html->setFieldProperties("frais_ouvert",FIELDP_JS_EVENT,array("onchange"=>"document.ADForm.versMin.value=formateMontant(recupMontant(document.ADForm.frais_ouvert.value)+recupMontant(document.ADForm.mnt_min.value));"));
      }
    }

    if ($InfoProduit["mnt_max"] > 0) {
      $html->addField("mnt_max",_("Montant maximum sur le compte"),TYPC_MNT);
      $html->setFieldProperties("mnt_max", FIELDP_DEFAULT, $InfoProduit["mnt_max"]);

      array_push($fieldslabel, "mnt_max");
      array_push($order,"mnt_max");
      $versementMaximum+=$InfoProduit['mnt_max'];
      array_push($fieldslabel,"versMax");
      array_push($order,"versMax");
      $html->addField("versMax",_("Versement Maximum"),TYPC_MNT);
      $html->setFieldProperties("versMax",FIELDP_DEFAULT,$versementMaximum);

      if ($SESSION_VARS['TypeEncaisse'] == 1) {
        $html->setFieldProperties("frais_depot",FIELDP_JS_EVENT,array("onchange"=>"document.ADForm.versMax.value=formateMontant(recupMontant(document.ADForm.frais_depot.value)+recupMontant(document.ADForm.mnt_max.value)+recupMontant(document.ADForm.frais_ouvert.value));"));
        $html->setFieldProperties("frais_ouvert",FIELDP_JS_EVENT,array("onchange"=>"document.ADForm.versMax.value=formateMontant(recupMontant(document.ADForm.frais_ouvert.value)+recupMontant(document.ADForm.mnt_max.value)+recupMontant(document.ADForm.frais_depot.value));"));
      } else {
        $html->setFieldProperties("frais_ouvert",FIELDP_JS_EVENT,array("onchange"=>"document.ADForm.versMax.value=formateMontant(recupMontant(document.ADForm.frais_ouvert.value)+recupMontant(document.ADForm.mnt_max.value));"));
      }
    }


    // hidden field for classe_comptable:
    $html->addHiddenType("classe_comptable", $InfoProduit['classe_comptable']);

    //metre les champs en labels
    foreach($fieldslabel as $value) {
      $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
      $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
    }

    // Code JS pour le checkform
    $js_check = "";

    if($InfoProduit["tx_interet"] > 0) {
        $js_check = "if(document.ADForm.HTML_GEN_LSB_type_cpt_vers_int.value == 2)
                        if (document.ADForm.HTML_GEN_LSB_cpt_vers_int.value == 0)
                        {
                        ADFormValid = false;
                        msg += '- "._("Le numéro du compte de versement des intérets doit être précisé")."\\n';
                    }";
    }

    $tx_interet_max_affiche = affichePourcentage($tx_interet_max, 1, false, ".");

    // check for max interest
    $js_check  = "";

    // la verification n'est pas faite dans le cas des depots a vue (classe_comptable = 1)
    if(!$isDepotAVue && $tx_interet_max > 0) {
      $js_check .= "\n if(parseFloat(document.ADForm.tx_interet_cpte.value / 100 ) > $tx_interet_max) {
                    \n alert ('Le taux d\'intérêt ne doit pas dépasser $tx_interet_max_affiche % !');
                    \n ADFormValid = false;
                  }";
    }

    // disable this field on page load
    $js_form = "\n";
    $js_form .= "document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.disabled = 'true';";
    $js_form .= "\n document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.disabled = 'true';";

    /*
     Contrôle de la freq et du mode de calcul des intérêts en fonction du taux
     Si le taux n'est pas renseigné, il faut desactiver les listes deroulantes frequence/mode de calcul
     Verifier le control d'acces aussi !
   */

    $js_form .= "function processChangeTauxInt() {
                    if (document.ADForm.tx_interet_cpte.value == '') {
                      document.ADForm.tx_interet_cpte.value = 0;
                    }

                    isdisabled = (document.ADForm.tx_interet_cpte.value == 0);

                    if (isdisabled) {
                      document.getElementsByName('HTML_GEN_LSB_freq_calcul_int_cpte')[0].selectedIndex = 0;
                      document.getElementsByName('HTML_GEN_LSB_mode_calcul_int_cpte')[0].selectedIndex = 0;
                      document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.disabled = true;
                      document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.disabled = true;
                      ADFormValid=false;
                    }
                }
    ";


    $js_form .= "\n function resetToDefault(thisField) {

                      //alert (thisField);

                     /* switch(thisField) {
                      case 'freq_calcul_int_cpte':
                          document.getElementsByName('HTML_GEN_LSB_freq_calcul_int_cpte')[0].selectedIndex = $freq_calc_int_prod;
                          break;
                      case 'mode_calcul_int':
                          document.getElementsByName('HTML_GEN_LSB_mode_calcul_int_cpte')[0].selectedIndex = $mode_calc_int_prod;
                          break;
                      case 'tx_interet_cpte':
                         document.ADForm.tx_interet_cpte.value = $taux_int_prod_affiche;
                         break;
                      case 'terme_cpte':
                        document.ADForm.terme_cpte.value = $terme_prod;
                        break;
                  }*/

                  document.getElementsByName('HTML_GEN_LSB_freq_calcul_int_cpte')[0].selectedIndex = $freq_calc_int_prod;
                  document.getElementsByName('HTML_GEN_LSB_mode_calcul_int_cpte')[0].selectedIndex = $mode_calc_int_prod;
                  document.ADForm.tx_interet_cpte.value = $taux_int_prod_affiche;
                  document.ADForm.terme_cpte.value = $terme_prod;
    }";


    //$js_end .= "\n alert(document.ADForm.tx_interet_cpte.value)";

    $js_end .= "\n if (document.ADForm.tx_interet_cpte.value > 0) {";
    $js_end .= "\n if(document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.value==0 && document.ADForm.classe_comptable.value==1){";
    $js_end .= "alert('"._("Le champ Fréquence des calculs des intérêts doit être renseigné !")."');ADFormValid=false;\n";
    $js_end .= "\n} ";
    $js_end .= "\n if (document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.value == 0 && document.ADForm.classe_comptable.value==1){";
    $js_end .= "alert('"._("Le champ mode de calcul des intérêts doit être renseigné !")."');ADFormValid=false;\n";
    $js_end .= "\n} ";
    $js_end .= "\n} ";

    $js_end .= "\n if ( (document.ADForm.classe_comptable.value == 2 || document.ADForm.classe_comptable.value == 5 || document.ADForm.classe_comptable.value == 6) && (document.ADForm.terme_cpte.value <= 0)) {";
    $js_end .= "alert('"._("Il faut renseigner le terme du produit !")."');ADFormValid=false;\n";
    $js_end .="\n}";


    // remove the disabled attributes on form submit
    $js_end .= "
        \n if(ADFormValid == true) {
              document.ADForm.HTML_GEN_LSB_freq_calcul_int_cpte.removeAttribute('disabled');
              document.ADForm.HTML_GEN_LSB_mode_calcul_int_cpte.removeAttribute('disabled');
              document.ADForm.terme_cpte.removeAttribute('disabled');
              document.ADForm.tx_interet_cpte.removeAttribute('disabled');
            }
    ";

    $html->addJS(JSP_FORM, "js_load", $js_form);
    $html->addJS(JSP_FORM, "funct", $js_code);

    if ($InfoProduit["classe_comptable"] == 2 or $InfoProduit["classe_comptable"] == 5) {
      $html->addJS(JSP_FORM, "termeParFreq", $termeParFreq);
      $html->addJS(JSP_FORM, "freqParTerme", $freqParTerme);
    }

    $html->addJS(JSP_BEGIN_CHECK, "js_check", $js_check);
    $html->addJS(JSP_END_CHECK, "js_submit", $js_end);

    $html->setOrder(NULL, $order);

    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    if ($SESSION_VARS["TypeEncaisse"] == 1)
      $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ocp-4');
    if ($SESSION_VARS["TypeEncaisse"] == 2)
      $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ocp-3');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ocp-1');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);


    $html->buildHTML();
    echo $html->getHTML();
  }
}
//-------------------------------------------------------------------
//----------- Ocp-3 Choix du compte source --------------------------
//-------------------------------------------------------------------
else if ($global_nom_ecran == 'Ocp-3') {

  if (isset($_POST['intitule_compte'])) {
    $SESSION_VARS['intitule_compte']=$_POST['intitule_compte'];
    $SESSION_VARS['type_cpt_vers_int']=$_POST['typ_cpt_vers_int'];
    $SESSION_VARS['cpt_vers_int']=$_POST['cpt_vers_int'];
    $SESSION_VARS['cpte_virement_clot']=$_POST['cpte_virement_clot'];
    $SESSION_VARS['dat_prolongation'] = ($_REQUEST['HTML_GEN_BOL_dat_prolongation'] =='on')?'TRUE':'FALSE';
    $SESSION_VARS['dat_nb_reconduction'] = $_REQUEST['dat_nb_reconduction'];
  }
  $InfoProduit = getProdEpargne($SESSION_VARS["id_prod"]); // produit du compte à créer
  // Si les frais d'ouverture ont été modifiés, prendre ceux-ci en compte
  if (check_access(299)) {
    if (isset($frais_ouvert)) {
      $InfoProduit["frais_ouverture_cpt"] = recupMontant($frais_ouvert);
      $SESSION_VARS["frais_ouverture_cpt"] = recupMontant($frais_ouvert);
    } else
      $SESSION_VARS["frais_ouverture_cpt"] = $InfoProduit["frais_ouverture_cpt"];
  }
  $SESSION_VARS["InfoProduit"] = $InfoProduit;

  $html = new HTML_GEN2(_("Choix du compte source pour le transfert initial sur le compte"));
  //   affichage de tous les comptes du client et s'il n'y a rien...ne rien faire
  $TempListeComptes = get_comptes_epargne($global_id_client);
  //retirer de la liste les comptes à retrait unique
  $choix = array();
  if (isset($TempListeComptes)) {
    $ListeComptes = getComptesRetraitPossible($TempListeComptes);
    if (is_array($ListeComptes)) {
      foreach($ListeComptes as $key=>$value) $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];//index par id_cpte pour la listbox
    }
  }
  // Infos compte source
  $html->addHTMLExtraCode("src","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Information compte source")."</b></td></tr></table>\n");

  $html->addField("NumCpteSource", _("Numéro de compte source"), TYPC_LSB);
  $html->setFieldProperties("NumCpteSource", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("NumCpteSource", FIELDP_IS_REQUIRED, true);
  if (isset($SESSION_VARS['NumCpteSource']))
    $html->setFieldProperties("NumCpteSource", FIELDP_DEFAULT, $SESSION_VARS['NumCpteSource']);

  $html->addField("dispo", _("Solde disponible"), TYPC_MNT);
  $html->setFieldProperties("dispo", FIELDP_DEVISE,"");

  $html->addField("dev_source",_("Devise"), TYPC_TXT);
  $html->setFieldProperties("dev_source", FIELDP_IS_LABEL, true);

  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel", "frais_transfert","duree_min_retrait_jour"));

  $html->setFieldProperties("frais_transfert", FIELDP_DEVISE,"");
  $html->setFieldProperties("frais_transfert", FIELDP_CAN_MODIFY,true);

  $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

  // Infos compte destination
  $html->addHTMLExtraCode("dest","<br /><table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Information compte destination")."</b></td></tr></table>\n");

  //ajout table ad_cpt intitulé du compte
  $html->addTable("ad_cpt", OPER_INCLUDE, array("num_complet_cpte","intitule_compte"));
  $html->setFieldProperties("num_complet_cpte", FIELDP_DEFAULT, $SESSION_VARS['num_complet_cpte']);
  $html->setFieldProperties("intitule_compte", FIELDP_DEFAULT, $SESSION_VARS['intitule_compte']);
  $html->addField("dev_dest", _("Devise"), TYPC_TXT);
  $html->setFieldProperties("dev_dest", FIELDP_DEFAULT, $InfoProduit['devise']);
  $html->setFieldProperties("dev_dest", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("num_complet_cpte", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("intitule_compte", FIELDP_IS_LABEL, true);

  //ajout des champs du formulaire

  $fieldslabel = array();
  if ($InfoProduit["mnt_min"] > 0) {
    $html->addField("mnt_min_dest",_("Dépôt minimum sur le compte"),TYPC_MNT);
    $html->setFieldProperties("mnt_min_dest", FIELDP_DEFAULT, $InfoProduit["mnt_min"]);
    $html->setFieldProperties("mnt_min_dest", FIELDP_DEVISE,$InfoProduit['devise']);
    array_push($fieldslabel, "mnt_min_dest");
  }
  if ($InfoProduit["mnt_max"] > 0) {
    $html->addField("mnt_max_dest",_("Montant maximum"),TYPC_MNT);
    $html->setFieldProperties("mnt_max_dest", FIELDP_DEFAULT, $InfoProduit['mnt_max']);
    $html->setFieldProperties("mnt_max_dest", FIELDP_DEVISE,$InfoProduit['devise']);
    array_push($fieldslabel, "mnt_max_dest");
  }
  if ($InfoProduit["frais_ouverture_cpt"] > 0) {
    $html->addField("frais_ouvert_dest",_("Frais d'ouverture de compte"),TYPC_MNT);
    $html->setFieldProperties("frais_ouvert_dest", FIELDP_DEFAULT,$InfoProduit["frais_ouverture_cpt"]);
    $html->setFieldProperties("frais_ouvert_dest", FIELDP_DEVISE,$InfoProduit['devise']);
    array_push($fieldslabel, "frais_ouvert_dest");
  }

  // Ordre des champs
  if ($InfoProduit["duree_min_retrait_jour"]!=0)
    $html->setOrder("NumCpteSource", array("libel","dispo", "dev_source","frais_transfert","duree_min_retrait_jour", "dest"));
  else
    $html->setOrder("NumCpteSource", array("libel","dispo", "dev_source","frais_transfert", "dest"));


  //mettre les champs en label
  $fieldslabel = array_merge($fieldslabel, array("libel", "dispo", "duree_min_retrait_jour", "frais_transfert"));
  foreach($fieldslabel as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  //javascript pour afficher les infos du compte source

  $codejs = "
            function getInfoCompte()
          {";
  if (is_array($ListeComptes)) {
    foreach($ListeComptes as $key=>$value) {
      setMonnaieCourante($value["devise"]);
      $codejs .= "
                 if (document.ADForm.HTML_GEN_LSB_NumCpteSource.value == " . $key .")
                 {
                 document.ADForm.libel.value = '".$value["libel"] . "';
                 document.ADForm.dev_source.value = '".$value['devise']."';";
      if ($value["duree_min_retrait_jour"] !=0)
        $codejs .="
                  document.ADForm.duree_min_retrait_jour.value = ".$value["duree_min_retrait_jour"].";";
      $codejs .= "
                 document.ADForm.dispo.value = '".afficheMontant(getSoldeDisponible($key))."';";
      $codejs .= "
                 document.ADForm.frais_transfert.value = ".$value['frais_transfert'].";

               }";
    }
    $codejs .= "
               if (document.ADForm.HTML_GEN_LSB_NumCpteSource.value == 0)
             {
               document.ADForm.libel.value = '';
               document.ADForm.dev_source.value = '';
               document.ADForm.duree_min_retrait_jour.value = '';
               document.ADForm.frais_transfert.value='';
               document.ADForm.dispo.value = '';
             }";
  }
  $codejs .= "
           }getInfoCompte();";


  $html->setFieldProperties("NumCpteSource", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte();"));
  $html->addJS(JSP_FORM, "JS4", $codejs);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ocp-4');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ocp-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $html->buildHTML();

  // Pass around the session variables
  if(isset($_POST['tx_interet_cpte'])) {
    $SESSION_VARS['tx_interet_cpte'] = $_POST['tx_interet_cpte'] / 100;
  }

  if(isset($_POST['terme_cpte'])) {
    $SESSION_VARS['terme_cpte'] = $_POST['terme_cpte'];
  }

  if(isset($_POST['freq_calcul_int_cpte'])) {
    $SESSION_VARS['freq_calcul_int_cpte'] = $_POST['freq_calcul_int_cpte'];
  }

  if(isset($_POST['mode_calcul_int_cpte'])) {
    $SESSION_VARS['mode_calcul_int_cpte'] = $_POST['mode_calcul_int_cpte'];
  }

  echo $html->getHTML();
}

//-------------------------------------------------------------------
//----------- Ocp-4 Versement sur le compte -------------------------
//-------------------------------------------------------------------
else if ($global_nom_ecran == "Ocp-4") {

  //Mémorisation du num compte de source
  if (isset($_POST['NumCpteSource']))
    $SESSION_VARS['NumCpteSource']=$_POST['NumCpteSource'];
  if(isset($_REQUEST['HTML_GEN_BOL_dat_prolongation'])) {
   $SESSION_VARS['dat_prolongation'] = ($_REQUEST['HTML_GEN_BOL_dat_prolongation'] =='on')?'TRUE':'FALSE';
  }
  if(isset($_REQUEST['dat_nb_reconduction'])) {
    $SESSION_VARS['dat_nb_reconduction'] = $_REQUEST['dat_nb_reconduction'];
  }

  // Mémorisation du numéro de compte de versement des intérets
  if (isset($_POST['type_cpt_vers_int'])) {
    $SESSION_VARS['type_cpt_vers_int']=$_POST['type_cpt_vers_int'];
    $SESSION_VARS['cpt_vers_int']=$_POST['cpt_vers_int'];
  }

  // Mémorisation du numéro de compte de virement à la clôture si c'est un compte à terme
  if (isset($_POST['cpte_virement_clot']))
    $SESSION_VARS['cpte_virement_clot']=$_POST['cpte_virement_clot'];

  // Recherche des montants minimum et maximums à verser sur le nouveau compte
  if ($SESSION_VARS["TypeEncaisse"] == 1) { // On vient de Ocp-2, ouverture au guichet
    $SESSION_VARS['intitule_compte']=$_POST['intitule_compte'];
    $InfoProduit = getProdEpargne($SESSION_VARS["id_prod"]);//produit du compte à créer
    // Récupération s'il y a lieu du montant des frais d'ouverture et/ou des frais de dépt
    if (check_access(299)) {
      if (isset($frais_ouvert)) {
        $InfoProduit["frais_ouverture_cpt"] = recupMontant($frais_ouvert);
      }
      if (isset($frais_depot)) {
        $InfoProduit["frais_depot_cpt"] = recupMontant($frais_depot);
      }
    }

    // Calcul du montant minimum et maximum à verser

    if ($InfoProduit["mnt_max"] > 0) {
      $versMax = $InfoProduit["mnt_max"];
      $versMax += $InfoProduit['frais_ouverture_cpt'];
      $versMax += $InfoProduit['frais_depot_cpt'];
    }

    $versMin = $InfoProduit["mnt_min"];
    $versMin+=$InfoProduit['frais_depot_cpt'];
    $versMin+=$InfoProduit['frais_ouverture_cpt'];

    $SESSION_VARS["InfoProduit"] = $InfoProduit;
  } else { // On vient de Ocp-3, ouverture par transfert
    $InfoProduit = $SESSION_VARS["InfoProduit"];
    // Recherche infos sur le compte source
    $cpteSource=getAccountDatas($SESSION_VARS['NumCpteSource']);
    if (check_access(299)) {
      if (isset($frais_transfert))
        $SESSION_VARS["frais_transfert_cpt_source"] = recupMontant($frais_transfert);
      else
        $SESSION_VARS["frais_transfert_cpt_source"] = $cpteSource["frais_transfert"];
    }
    $versMin = $InfoProduit["mnt_min"] + $InfoProduit["frais_ouverture_cpt"];

    // Le solde disponible correspond au solde disponible moins les frais de transfert (qui seront prélevés en plus du montant transféré).
    $soldeDispo=getSoldeDisponible($SESSION_VARS['NumCpteSource']);
    $retraitMaxCptSrc = $soldeDispo - $SESSION_VARS['frais_transfert_cpt_source'];

    // Recherche du montant maximum à verser sur le compte destination
    if ($InfoProduit["mnt_max"] > 0)
      $versMax = $InfoProduit["mnt_max"] + $InfoProduit["frais_ouverture_cpt"];
  }

  $SESSION_VARS["versMin"] = $versMin;
  $SESSION_VARS["versMax"] = $versMax;
  if (isset($retraitMaxCptSrc))
    $SESSION_VARS["retraitMaxCptSrc"] = $retraitMaxCptSrc;

  $html = new HTML_GEN2(_("Versement initial sur le compte"));

  if ($SESSION_VARS["TypeEncaisse"] == 2) { // Ouverture par transfert
    if (isset($NumCpteSource))
      //sauvegarder de Ocp-2 pour Ocp-6

      $MANDATS = getListeMandatairesActifs($NumCpteSource);
    if ($MANDATS != NULL) {
      foreach ($MANDATS as $key=>$value) {
        if ($value['limitation'] != NULL) {
          $JS_check .=
            "if (document.ADForm.HTML_GEN_LSB_mandat.value == $key && recupMontant(document.ADForm.mnt.value) > ".$value['limitation'].")
          {
            msg += \"- "._("Le montant est supérieur à la limitation du donneur d'ordre")."\\n\";
            ADFormValid=false;
          }";
        }
        $MANDATS_LSB[$key] = $value['libelle'];
      }
    }
    $html->addField("mandat", _("Donneur d'ordre"), TYPC_LSB);
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("0" => _("Titulaire")));
    if ($MANDATS_LSB != NULL) {
      $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, $MANDATS_LSB);
    }
    $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_TOUS, false);
    $html->addJS(JSP_BEGIN_CHECK, "limitation_check", $JS_check);

    $html->addHTMLExtraCode("mandat_sep","<br/>");

    $html->addHTMLExtraCode("src","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Information compte source")."</b></td></tr></table>\n");

    $html->addField("NumCpteSource", _("Numéro de compte source"), TYPC_TXT);
    $html->setFieldProperties("NumCpteSource", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("NumCpteSource", FIELDP_DEFAULT, $cpteSource['num_complet_cpte']);

    $html->addField("libelSource", _("Compte source"), TYPC_TXT);
    $html->setFieldProperties("libelSource", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("libelSource", FIELDP_DEFAULT, $cpteSource['intitule_compte']);

    $html->addField("dispo", _("Retrait maximum autorisé"), TYPC_MNT);
    $html->setFieldProperties("dispo", FIELDP_DEVISE,$cpteSource['devise']);
    $html->setFieldProperties("dispo", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("dispo", FIELDP_DEFAULT, $retraitMaxCptSrc);

    $html->addField("dev_source",_("Devise"), TYPC_TXT);
    $html->setFieldProperties("dev_source", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("dev_source", FIELDP_DEFAULT, $cpteSource['devise']);


    if ($cpteSource['devise'] != $InfoProduit['devise']) {
      $cvMinimum=getChangeFinal($SESSION_VARS['versMin'],$cpteSource['devise'],$InfoProduit['devise']);
      $cvMinimum=$cvMinimum['montant_debite'];
      $html->addField("cvMin", _("Contrevaleur théorique du versement minimum"), TYPC_MNT);
      $html->setFieldProperties("cvMin", FIELDP_DEVISE,$cpteSource['devise']);
      $html->setFieldProperties("cvMin", FIELDP_IS_LABEL, true);
      $html->setFieldProperties("cvMin", FIELDP_DEFAULT, $cvMinimum);

    }

    // Infos compte destination
    $html->addHTMLExtraCode("dest","<br /><table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Information compte destination")."</b></td></tr></table>\n");

  }

  // Infos compte destination

  //ajout table ad_cpt intitulé du compte
  $html->addTable("ad_cpt", OPER_INCLUDE, array("num_complet_cpte","intitule_compte"));
  $html->setFieldProperties("num_complet_cpte", FIELDP_DEFAULT, $SESSION_VARS['num_complet_cpte']);
  $html->setFieldProperties("num_complet_cpte", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("intitule_compte", FIELDP_DEFAULT, $SESSION_VARS['intitule_compte']);
  $html->setFieldProperties("intitule_compte", FIELDP_IS_LABEL, true);
  $html->addField("dev_dest", _("Devise"), TYPC_TXT);
  $html->setFieldProperties("dev_dest", FIELDP_DEFAULT, $InfoProduit['devise']);
  $html->setFieldProperties("dev_dest", FIELDP_IS_LABEL, true);

  $html->addField("dptMin", _("Dépôt initial minimum"),TYPC_MNT);
  $html->setFieldProperties("dptMin", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("dptMin", FIELDP_DEFAULT, $InfoProduit['mnt_dpt_min']);

  $html->addField("versMin",_("Versement Minimum"),TYPC_MNT);
  $html->setFieldProperties("versMin", FIELDP_DEFAULT, $SESSION_VARS['versMin']);
  $html->setFieldProperties("versMin", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("versMin", FIELDP_DEVISE,$InfoProduit['devise']);
  if (isset($SESSION_VARS['versMax'])) {
    $html->addField("versMax",_("Versement Maximum"),TYPC_MNT);
    $html->setFieldProperties("versMax", FIELDP_DEFAULT, $SESSION_VARS['versMax']);
    $html->setFieldProperties("versMax", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("versMax", FIELDP_DEVISE,$InfoProduit['devise']);
  }
  $ChkJSChange='';
  if ($SESSION_VARS["TypeEncaisse"] == 2) {
    $html->addHTMLExtraCode("transfert","<br /><table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Montant du transfert du compte source vers le compte destination")."</b></td></tr></table>\n");
    $html->addField("mnt", _("Versement initial"), TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_DEVISE,$InfoProduit['devise']);
    $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);

      if(isset($SESSION_VARS['mnt'])){
          $html->setFieldProperties("mnt", FIELDP_DEFAULT,$SESSION_VARS['mnt'] );
      }

    if ($cpteSource['devise'] != $InfoProduit['devise']) { // Les devises du compte source et destination sont différentes
      $html->addField("mntDevise", _("Versement dans la devise du compte source"), TYPC_MNT);
      $html->setFieldProperties("mntDevise", FIELDP_DEVISE,$cpteSource['devise']);
      $html->setFieldProperties("mntDevise", FIELDP_IS_REQUIRED, true);
      $html->addLink("mntDevise","changeDevise",_("changer"),"#");
      $html->setLinkProperties("changeDevise",LINKP_JS_EVENT,array("onclick"=>"HTML_GEN_dvr_mntDevise_popup();"));
      $html->setFieldProperties("mnt", FIELDP_JS_EVENT, array("onfocus"=>"document.ADForm.mntDevise.value='';document.ADForm.MONTANT_LIE_mntDevise_comm_nette.value='';document.ADForm.MONTANT_LIE_mntDevise_taux.value='';document.ADForm.MONTANT_LIE_mntDevise_dest_reste.value='';","onchange"=>"document.ADForm.mnt.value=formateMontant(document.ADForm.mnt.value);"));
      $html->setFieldProperties("mntDevise", FIELDP_JS_EVENT, array("onfocus"=>"document.ADForm.mnt.value='';document.ADForm.MONTANT_LIE_mntDevise_comm_nette.value='';document.ADForm.MONTANT_LIE_mntDevise_taux.value='';document.ADForm.MONTANT_LIE_mntDevise_dest_reste.value='';","onchange"=>"document.ADForm.mntDevise.value=formateMontant(document.ADForm.mntDevise.value);"));
      $ChkJSChange .= "
                     function HTML_GEN_dvr_mntDevise_popup()
                   {
                     if (document.ADForm.mntDevise.value!='' || document.ADForm.mnt.value!='')
                     open_change(document.ADForm.mnt.value,'".$InfoProduit['devise']."',document.ADForm.mntDevise.value,'".$cpteSource['devise']."','mnt','mntDevise','MONTANT_LIE_mntDevise_comm_nette','MONTANT_LIE_mntDevise_taux','','MONTANT_LIE_mntDevise_dest_reste','achat',2);
                   };
                     ";

      $html->addHiddenType("MONTANT_LIE_mntDevise",$cpteSource['devise']);
      $html->addHiddenType("MONTANT_LIE_mntDevise_comm_nette");
      $html->addHiddenType("MONTANT_LIE_mntDevise_taux");
      $html->addHiddenType("MONTANT_LIE_mntDevise_dest_reste");

      // On en profite pour renseigner le nom de la variable contenant le montant à vérifier par rappoirt au montant max à prélever sur le compte source
      // Si les devises sont les memes, alors c'est mnt sinon c'est mntDevise
      $nom_champ_verif_js = "mntDevise";
    } else
      $nom_champ_verif_js = "mnt"; // Cfr 3 lignes plus haut
  } else { // Si type encaisse = 1 (ouverture au guichet)
    $html->addField("mnt", _("Versement initial"), TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_DEVISE,$InfoProduit['devise']);
    if ($SESSION_VARS['versMin']> 0)
      $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
    else
      $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, false);
    isset($SESSION_VARS['mnt'])?$html->setFieldProperties("mnt", FIELDP_DEFAULT,$SESSION_VARS['mnt'] ):$html->setFieldProperties("mnt", FIELDP_DEFAULT, 0);

      $html->addField("mntDevise",_("Versement dans une autre devise"),TYPC_DVR);
      if(isset($SESSION_VARS['change']['cv'])){
          $html->setFieldProperties("mntDevise", FIELDP_DEFAULT, $SESSION_VARS['change']['cv']);
      }

    $html->linkFieldsChange("mnt","mntDevise","achat",1,false);
  }
    if(isset($SESSION_VARS['change']['devise'])){
        $html->setFieldProperties("mntDevise", FIELDP_DEVISE, $SESSION_VARS['change']['devise']);

        $ChkJSChange .= " activateChampDeviseRef();if (document.ADForm.HTML_GEN_dvr_mntDevise.value == '".$InfoProduit['devise']."') {document.ADForm.mntDevise.disabled=true;} else {document.ADForm.mntDevise.disabled=false;};";
    }
  $html->addField("communication", _("Communication"), TYPC_TXT);
  $html->setFieldProperties("communication", FIELDP_DEFAULT, $SESSION_VARS['communication']);
  $html->addField("remarque", _("Remarque"), TYPC_ARE);
  $html->setFieldProperties("remarque", FIELDP_DEFAULT, $SESSION_VARS['remarque']);

  //ajout du code javascript pour vérifier si le versement initial est entre les montants mini et maxi

    //vérifier si l'option multidevise a été activé et si le montant en devise par defaut a été renseigné
  $ChkJS = "";
    if (($SESSION_VARS["versMin"] == 0)) {
    $ChkJS .="
            if (document.ADForm.mnt.value=='' && document.ADForm.mntDevise.disabled == false)
            {
                 msg += '-"._("le champ Versement initial doit être renseigné")."\\n';
                  ADFormValid=false;
            };";
    }

  if ($retraitMaxCptSrc > 0) {
    setMonnaieCourante($cpteSource['devise']);
    //Si on procède à une ouverture par transfert d'un compte d'une devise différente, on vérifie que la case correspondante n'excède pas le solde disponible.
    $ChkJS .="
             if (recupMontant(document.ADForm.$nom_champ_verif_js.value) > ".$retraitMaxCptSrc.")";
    $ChkJS .="
           {
             msg += '"._("Le montant du versement initial ne doit pas excéder le solde disponible sur le compte source:")." ".afficheMontant($retraitMaxCptSrc,true)."\\n';
             ADFormValid=false;
           };       ";
  }
  if (($SESSION_VARS["versMin"] >= 0)) {
    setMonnaieCourante($InfoProduit['devise']);
    $ChkJS .= "
              if (recupMontant(document.ADForm.mnt.value) < ".$SESSION_VARS['versMin'].")
            {
              msg += '"._("Le montant du versement initial doit être supérieur au versement minimum :")." ".afficheMontant($SESSION_VARS['versMin'],true)."\\n';
              ADFormValid=false;
            };";
  }

  if (isset($SESSION_VARS["versMax"])) {
    setMonnaieCourante($InfoProduit['devise']);
    $ChkJS .= "
              if (recupMontant(document.ADForm.mnt.value) > ".$SESSION_VARS['versMax'].")
            {
              msg += '-"._("Le montant du versement initial doit être inférieur au montant maximal :")." ".afficheMontant($SESSION_VARS['versMax'], true)."\\n';
              ADFormValid=false;
            };";
  }
  // Vérification du montant du dépôt initial (#535)
  if (isset($InfoProduit["mnt_dpt_min"]) && $InfoProduit["mnt_dpt_min"] != 0) {
    setMonnaieCourante($InfoProduit['devise']);
    $ChkJS .= "
              if (recupMontant(document.ADForm.mnt.value) < ".$InfoProduit['mnt_dpt_min'].")
            {
              msg += '"._("Le montant du versement initial doit être superieur au montant du dépôt initial :")." ".afficheMontant($InfoProduit['mnt_dpt_min'], true)."\\n';
              ADFormValid=false;
            };";
  }

  //vérifier que le montant est positif
  $ChkJS .= "
            if ((".$SESSION_VARS["TypeEncaisse"]."==2 && recupMontant(document.ADForm.mnt.value) == 0) ||(".$SESSION_VARS["TypeEncaisse"]."==1 && ".$SESSION_VARS['versMin']."> 0 && recupMontant(document.ADForm.mnt.value) == 0))
          {
            msg += '-"._("Vous devez saisir une valeur")."\\n';
            ADFormValid=false;
          };";

  $html->addJS(JSP_BEGIN_CHECK, "JS3",$ChkJS);

    //Vérifier que si l'option de la devise de reference a été choisie, faire en sorte de reactiver le champs mnt.
    $ChkJSChange .= "
                     document.ADForm.HTML_GEN_dvr_mntDevise.onclick = function() { activateChampDeviseRef(); };

                        function activateChampDeviseRef() {
                            if (document.ADForm.HTML_GEN_dvr_mntDevise.value == '".$InfoProduit['devise']."')
                            {
                                document.ADForm.mnt.readOnly = false;

                            } else {document.ADForm.mnt.readOnly = true;}

                                        };
                     ";
  $html->addJS(JSP_FORM,"JS4",$ChkJSChange);
  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ocp-5');
  if ($SESSION_VARS["TypeEncaisse"] == 1)
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ocp-2');
  if ($SESSION_VARS["TypeEncaisse"] == 2)
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ocp-3');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);


  if(isset($_POST['tx_interet_cpte'])) {
    $SESSION_VARS['tx_interet_cpte'] = $_POST['tx_interet_cpte'] / 100;
  }

  if(isset($_POST['terme_cpte'])) {
    $SESSION_VARS['terme_cpte'] = $_POST['terme_cpte'];
  }

  if(isset($_POST['freq_calcul_int_cpte'])) {
    $SESSION_VARS['freq_calcul_int_cpte'] = $_POST['freq_calcul_int_cpte'];
  }

  if(isset($_POST['mode_calcul_int_cpte'])) {
    $SESSION_VARS['mode_calcul_int_cpte'] = $_POST['mode_calcul_int_cpte'];
  }

  $html->buildHTML();
  echo $html->getHTML();

} //fin Ocp-4

//-------------------------------------------------------------------
//----------- Ocp-5 Confirmation du montant du versement  -----------
//-------------------------------------------------------------------
else if ($global_nom_ecran == "Ocp-5") {

  if ($mandat != 0 && $mandat != 'CONJ') {
    $SESSION_VARS['id_mandat'] = $mandat;
  }
  $SESSION_VARS['communication'] = $communication;
  $SESSION_VARS['remarque'] = $remarque;

// $mnt contient le montant déboursé par le client au guichet dans la devise en question.
  if (isset($mnt))
    $SESSION_VARS["mnt"] = recupMontant($mnt);
  $mnt_cv=$SESSION_VARS['mnt'];
  $mnt_enc=$SESSION_VARS['mnt'];
  $html = new HTML_GEN2(_("Confirmation du versement initial"));
  $InfoProduit = getProdEpargne($SESSION_VARS["id_prod"]);
  $operation_change=false;
  if (isset($_POST['mntDevise']['cv'])) {
    $SESSION_VARS['change']=$_POST['mntDevise'];
    $operation_change=true;
  }
  // affichage de l'intitulé du compte dans Prochain Ecran
  $html->addTable("ad_cpt", OPER_INCLUDE, array("num_complet_cpte","intitule_compte"));
  $html->setFieldProperties("num_complet_cpte", FIELDP_DEFAULT, $SESSION_VARS['num_complet_cpte']);
  $html->setFieldProperties("num_complet_cpte", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("intitule_compte", FIELDP_DEFAULT, $SESSION_VARS["intitule_compte"]);
  $html->setFieldProperties("intitule_compte", FIELDP_IS_LABEL, true);
  $deviseVersement=$InfoProduit['devise'];
  if ($operation_change) {
    //affichage du montant à verser sur le compte dans la devise du compte.
    $html->addField("mnt_cv", _("Dépôt sur le compte"), TYPC_MNT);
    $html->setFieldProperties("mnt_cv",FIELDP_DEFAULT,$mnt_cv);
    $html->setFieldProperties("mnt_cv",FIELDP_IS_LABEL, true);
    $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $InfoProduit['devise']);
    $deviseVersement=$SESSION_VARS['change']['devise'];
    $mnt_enc=recupMontant($SESSION_VARS['change']['cv']);
  }

  if ($SESSION_VARS["TypeEncaisse"] == 1) {
    $html->addField("mnt_enc", _("Versement à encaisser"), TYPC_MNT);//tmp_mnt net inclut les frais ici
    $html->addField("mnt_reel", _("Versement effectivement encaissé"), TYPC_MNT);
    if ($mnt_enc > 0)
      $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);
    else
      $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, false);

    if ($global_billet_req)
      $html->setFieldProperties("mnt_reel", FIELDP_HAS_BILLET, true);
  }
  if ($SESSION_VARS["TypeEncaisse"] == 2) {
    $html->addField("mnt_enc", _("Transfert à effectuer"), TYPC_MNT);//tmp_mnt net inclut les frais ici
    $html->addField("mnt_reel", _("Confirmation du montant"), TYPC_MNT);
    $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);
  }
  $html->setFieldProperties("mnt_enc", FIELDP_DEFAULT, $mnt_enc);
  $html->setFieldProperties("mnt_enc",FIELDP_IS_LABEL, true);
  $html->setFieldProperties("mnt_enc", FIELDP_DEVISE, $deviseVersement);


  $html->setFieldProperties("mnt_reel", FIELDP_DEVISE, $deviseVersement);

  $ChkJS = "
           if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt_enc.value))
         {
           msg += '"._("Le montant versé ne correspond pas au montant à encaisser")."\\n';
           ADFormValid=false;
         };
           ";

  $html->addJS(JSP_BEGIN_CHECK, "JS6",$ChkJS);


  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ocp-6');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ocp-4');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();

  // Enregistrement du libel du produit pour l'écran suivant
  $SESSION_VARS["libel_prod"] = $InfoProduit["libel"];
  $SESSION_VARS["classe_comptable"] = $InfoProduit["classe_comptable"];
}

//-------------------------------------------------------------------
//----------- Ocp-6 Confirmation de la création du compte  ----------
//-------------------------------------------------------------------
else if ($global_nom_ecran == "Ocp-6") {
  $CHANGE=NULL;
  if (isset($SESSION_VARS['change']['cv']))
    $CHANGE = $SESSION_VARS['change'];

  $DATA["id_titulaire"] = $SESSION_VARS["id_titulaire"];
  $DATA["date_ouvert"] = $SESSION_VARS["date_ouvert"];
  $DATA["date_solde_calcul_interets"] = $SESSION_VARS["date_ouvert"];
  //$DATA["date_calcul_interets"] = $SESSION_VARS["date_ouvert"];
  $DATA["date_calcul_interets"] = NULL;
  $DATA["utilis_crea"] = $SESSION_VARS["utilis_crea"];
  $DATA["etat_cpte"] = $SESSION_VARS["etat_cpte"];  //inutile
  $DATA["solde"] = "0";
  $DATA["mnt_bloq"] = "0";
  $DATA["mnt_bloq_cre"] = "0";
  $DATA["interet_annuel"] = "0";
  $DATA["num_cpte"] = $SESSION_VARS["num_cpte"];
  $DATA["mnt_min_cpte"] = $SESSION_VARS["mnt_min_cpte"];
  $DATA["num_complet_cpte"] = $SESSION_VARS["num_complet_cpte"];
  $DATA["id_prod"] = $SESSION_VARS["id_prod"];
  $DATA["dat_prolongation"] = $SESSION_VARS["dat_prolongation"];
  $DATA["dat_nb_prolong"] = 0;
  $DATA["intitule_compte"] = $SESSION_VARS["intitule_compte"];
  $DATA['dat_prolongation'] = $SESSION_VARS['dat_prolongation'];
  $DATA['dat_nb_reconduction'] = $SESSION_VARS['dat_nb_reconduction'];
// Renseignement du numéro de compte de versement des intérets
  $DATA["cpt_vers_int"] = $SESSION_VARS["cpt_vers_int"];
  $DATA["type_cpt_vers_int"] = $SESSION_VARS["type_cpt_vers_int"];
  $DATA["cpte_virement_clot"] = $SESSION_VARS["cpte_virement_clot"];
  //Information sur l'export netbank
  if($SESSION_VARS["utilise_netbank"] == 't'){
  	$DATA["export_netbank"] = $SESSION_VARS["export_netbank"];
  }  
  // recupére des infos héritées du produit
  $value = getProdEpargne($SESSION_VARS["id_prod"]);
  $DATA["devise"] = $value["devise"];
  $DATA["mode_calcul_int_cpte"] = $value["mode_calcul_int"];
  $DATA["mode_paiement_cpte"] = $value["mode_paiement"];
  $DATA["decouvert_max"] = $value["decouvert_max"];

  // # 537 - overwrite from session

  $DATA["has_changed_param_epargne"] = 0;

  if(isset($SESSION_VARS['tx_interet_cpte'])) {
    $DATA["tx_interet_cpte"] = $SESSION_VARS['tx_interet_cpte'];

    if(floatval($DATA["tx_interet_cpte"]) !== floatval($value["tx_interet"])) {
      $DATA["has_changed_param_epargne"] = 1;
    }
  }
  else
    $DATA["tx_interet_cpte"] = $value["tx_interet"];

  if(isset($SESSION_VARS['terme_cpte'])) {
    $DATA["terme_cpte"] = $SESSION_VARS['terme_cpte'];

    if(intval($DATA["terme_cpte"]) !== intval($value["terme"])) {
      $DATA["has_changed_param_epargne"] = 1;
    }
  }
  else
    $DATA["terme_cpte"] = $value["terme"];

  if(isset($SESSION_VARS['freq_calcul_int_cpte'])) {
    $DATA["freq_calcul_int_cpte"] = $SESSION_VARS['freq_calcul_int_cpte'];

    if(intval($DATA['freq_calcul_int_cpte']) !== intval($value["freq_calcul_int"])) {
      $DATA["has_changed_param_epargne"] = 1;
    }
  }
  else
    $DATA["freq_calcul_int_cpte"] = $value["freq_calcul_int"];

  if(isset($SESSION_VARS['mode_calcul_int_cpte'])) {
    $DATA["mode_calcul_int_cpte"] = $SESSION_VARS['mode_calcul_int_cpte'];

    if(intval($DATA["mode_calcul_int_cpte"]) !== intval($value["mode_calcul_int"])) {
      $DATA["has_changed_param_epargne"] = 1;
    }
  }
  else
    $DATA["mode_calcul_int_cpte"] = $value["mode_calcul_int_cpte"];


  if ($DATA["mode_calcul_int_cpte"] >= 2 and  $DATA["mode_calcul_int_cpte"] <= 7) { // le mode de calcul est 'Sur solde .... le plus bas'
    $DATA["solde_calcul_interets"] = $SESSION_VARS["mnt"] - $SESSION_VARS['InfoProduit']['frais_ouverture_cpt']; //pour empêcher que dès le départ le solde le plus bas de la période soit 0
    if ($SESSION_VARS['TypeEncaisse'] == 1) {
      $DATA["solde_calcul_interets"] -= $SESSION_VARS['InfoProduit']['frais_depot_cpt'];
    }
  } else if ( $DATA["mode_calcul_int_cpte"]>= 8 and  $DATA["mode_calcul_int_cpte"] <= 12) { // le mode de calcul est 'Sur solde moyen ...' ou 'Pour épargne à la source'
    $DATA["solde_calcul_interets"] = 0;
  } else if ( $DATA["mode_calcul_int_cpte"] == NULL or  $DATA["mode_calcul_int_cpte"] == 0) { /* Si pas de mode de calcul. Prendre le solde de depart */
    $DATA["solde_calcul_interets"] = $SESSION_VARS["mnt"] - $SESSION_VARS['InfoProduit']['frais_ouverture_cpt'];
    if ($SESSION_VARS['TypeEncaisse'] == 1) {
      $DATA["solde_calcul_interets"] -= $SESSION_VARS['InfoProduit']['frais_depot_cpt'];
    }
  }

  $soldeCompte = $SESSION_VARS['mnt'] - $SESSION_VARS['InfoProduit']['frais_ouverture_cpt'];
  if ($SESSION_VARS['TypeEncaisse'] == 1)
    $soldeCompte -= $SESSION_VARS['InfoProduit']['frais_depot_cpt'];

  $data_ext['communication'] = $SESSION_VARS['communication'];
  $data_ext['remarque'] = $SESSION_VARS['remarque'];
  $data_ext['sens'] = "---";

  if (isset($SESSION_VARS["terme"]) and ($SESSION_VARS["classe_comptable"] == 2 or $SESSION_VARS["classe_comptable"] == 5)) {
    $DATA["dat_date_fin"] = date("d/m/Y", mktime(0,0,0,date("n")+$DATA["terme_cpte"],date("d"),date("Y")));
  }
  elseif(isset($SESSION_VARS["ep_source_date_fin"]) and ($SESSION_VARS["classe_comptable"] == 6)) {
    $DATA["dat_date_fin"] = $SESSION_VARS["ep_source_date_fin"];
  }
  else {
    $DATA["dat_date_fin"] = '';
  }

  //FIXME: màj date_next_int
  $DATA["dat_decision_client"] = 'f';
  $DATA["dat_num_certif"] = $SESSION_VARS["dat_num_certif"];

  if ($SESSION_VARS["TypeEncaisse"] == 1) {
    //encaissement au guichet
    //mnt contient montant net à créditer sur le cpte client
    //appel DBS
    if (check_access(299)) {
      $erreur = ouverture_cpte_guichet($DATA, $SESSION_VARS["mnt"], $SESSION_VARS["InfoProduit"], $data_ext, $CHANGE);
    } else {
      $erreur = ouverture_cpte_guichet($DATA, $SESSION_VARS["mnt"], NULL, $data_ext, $CHANGE);
    }

    if ($erreur->errCode == NO_ERR) {
      // Impression éventuelle du reçu de change
      if (isset($CHANGE))
        printRecuChange($erreur->param, $CHANGE['cv'], $CHANGE['devise'], $global_guichet, $SESSION_VARS["mnt"], $DATA["devise"], $CHANGE['comm_nette'], $CHANGE["taux"], $CHANGE["reste"], $SESSION_VARS["num_complet_cpte"], $CHANGE["dest_reste"], true);
      setMonnaieCourante($DATA["devise"]);
      print_recu_ouverture_compte($SESSION_VARS["id_titulaire"],$SESSION_VARS["num_complet_cpte"],$SESSION_VARS["libel_prod"],$soldeCompte,$SESSION_VARS['intitule_compte'], $erreur->param);
      setMonnaieCourante($DATA["devise"]);
      $html_msg =new HTML_message(_("Confirmation de création de compte"));
      $msg = sprintf(_("Le compte N° %s de type %s a été créé avec succès."),"<b>".$SESSION_VARS["num_complet_cpte"]."</b><br>","<b>".$SESSION_VARS["libel_prod"]."</b>");
      $msg .= "<br />".sprintf(_("Le solde est de %s"),"<b>".afficheMontant($soldeCompte, true)."</b>");
      $msg .= "<br /><br />"._("N° de transaction")." : <B><CODE>".sprintf("%09d", $erreur->param)."</CODE></B>";
      $html_msg->setMessage($msg);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->addCustomButton("mandats", _("Ajouter des mandats"), 'Man-1');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec de la création du compte.")." ");
      $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."<br />"._("Paramètre")." : ".$erreur->param);
      $html_err->addButton("BUTTON_OK", 'Ocp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    };
  }

  else if ($SESSION_VARS["TypeEncaisse"] == 2) {
    //transfert d'un compte

    //appel DB
    if (check_access(299)) {
      $frais = array("frais_transfert" => $SESSION_VARS["frais_transfert_cpt_source"], "frais_ouverture_cpt" => $SESSION_VARS["frais_ouverture_cpt"]);
    } else {
      $frais = NULL;
    }
    $erreur = ouverture_cpte_transfert($DATA, $SESSION_VARS["NumCpteSource"], $SESSION_VARS["mnt"], $SESSION_VARS['id_mandat'], $data_ext, $frais, $CHANGE);
    if ($erreur->errCode == NO_ERR) {
      setMonnaieCourante($DATA["devise"]);
      // Recherche du numéro complet du compte source
      $SRC = getAccountDatas($SESSION_VARS['NumCpteSource']);
      $num_complet_cpte_source = $SRC["num_complet_cpte"];
      if (isset($CHANGE))
        printRecuChange($erreur->param, $CHANGE['cv'], $CHANGE['devise'], $num_complet_cpte_source, $SESSION_VARS["mnt"], $DATA["devise"], $CHANGE['comm_nette'], $CHANGE["taux"], $CHANGE["reste"], $SESSION_VARS["num_complet_cpte"], $CHANGE["dest_reste"], true);
      $html_msg =new HTML_message(_("Confirmation de création de compte"));

      if ($DATA["devise"]==$CHANGE['devise'] || $CHANGE['devise']=="" || $DATA["devise"]=="") {
        $montant_a_afficher=$soldeCompte;
      }
      elseif($DATA["devise"]!=$CHANGE['devise'] && $CHANGE['devise']!="" && $DATA["devise"]!="") {
        $montant_a_afficher=calculeCV($DATA["devise"],$CHANGE['devise'],$soldeCompte);
      }
      $msg = sprintf(_("Le compte n° %s de type %s a été créé avec succès"),"<b>".$SESSION_VARS["num_complet_cpte"]."</b><br />","<b>".$SESSION_VARS["libel_prod"]."</b>")."<br />".sprintf(_("Le solde est de %s"),"<b> ".afficheMontant( $montant_a_afficher, true)."</b>");
      $msg .= "<br /><br />"._("N° de transaction")." : <B><CODE>".sprintf("%09d", $erreur->param)."</CODE></B>";
      $html_msg->setMessage($msg);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->addCustomButton("mandats", _("Ajouter des mandats"), 'Man-1');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec de la création du compte. "));
      $html_err->setMessage("Erreur : ".$error[$erreur->errCode]." ".$erreur->param);
      $html_err->addButton("BUTTON_OK", 'Ocp-2');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran $global_nom_ecran n'a pas été trouvé"
?>