<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Recherche d'un client dans la BD
 * Cette opération comprends les écrans (définis par la variable postée $Recherche) :
 * - KO     : Critères de recherche d'un client (ou si $Recherche est vide)
 * - OK     : Résultats de recherche d'un client
 * - Compte : Comptes du client trouvé
 * Si la variable $choixCompte est passée, alors on ira jusqu'à la sélection d'un compte du client
 * (3e écran), sinon la recherche s'arrêtera au choix du client.
 * @package Clients
 **/

require_once('lib/html/HTML_GEN2.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/misc/VariablesGlobales.php');
require("lib/html/HtmlHeader.php");

echo "<script language=\"javascript\">";
echo "opener.onfocuso = react;\n";
echo "function react() { window.focus(); }\n";
echo "</script>";

/*{{{ Gestion des variables de session */

/* champ contenant la lien et la valeur choisie : grisé en principe */

if (isset($cpt_dest))
  $SESSION_VARS['cpt_dest'] = $cpt_dest ;

/* champ caché contenant aussi la valeur saisie : défini car le 1er est grisé */

if (isset($id_cpt_dest))
  $SESSION_VARS['id_cpt_dest'] = $id_cpt_dest;

if ($choixCompte !='')
  $SESSION_VARS['choixCompte']=true;

else
  if (!isset($Recherche))
    // Premier appel de l'écran
    unset($SESSION_VARS['choixCompte']);

/* Champ caché pour récupérer le id du client dans le cas ou le champ contenant le lien est grisé  */

if ($num_client_dest !='')
  $SESSION_VARS['num_client_dest'] = $num_client_dest;

else
  unset($SESSION_VARS['num_client_dest']);

if (isset($devise)) $SESSION_VARS["devise_rech"] = $devise;

if (isset($client)) $Recherche = 'Compte';
if (isset($devise_cpte_dest)) $SESSION_VARS['devise_cpte_dest']=$devise_cpte_dest ;  
 	if (isset ($is_depot))  
 	   $SESSION_VARS["is_depot"]=$is_depot;

if (isset($devise_cpte_dest)) $SESSION_VARS['devise_cpte_dest']=$devise_cpte_dest ;
if (isset ($is_depot))
	$SESSION_VARS["is_depot"]=$is_depot;


/*}}}*/

/*{{{ KO : Critères de recherche d'un client */

if (!isset($Recherche) || $Recherche == "KO") {
  if (!isset($field_name))
    $field_name="num_client"; //Nom du champs dans lequel va être inscrit le résultat

  $Title = _("Recherche d'un client");
  $myForm = new HTML_GEN2($Title);
  $myForm->addHTMLExtraCode("idc", "<H4> "._("Identificateur de client")." </H4>");
  $myForm->addHTMLExtraCode("anc", "<H4> "._("Ancien identificateur de client")." </H4>");
  $myForm->addHTMLExtraCode("pp", "<H4> "._("Personne physique")." </H4>");
  $myForm->addHTMLExtraCode("pm", "<H4> "._("Personne morale")." </H4>");
  $myForm->addHTMLExtraCode("gi", "<H4> "._("Groupe informel ou solidaire")." </H4>");
  $extra=array("BEGIN" => array("0" => array("HTML" => "<H2> "._("Personne physique")." </H2>")),
               "pp_lieu_naissance" => array("0" => array("HTML" => "<HR><H2> "._("Personne morale")." </H2>")),

               "pm_raison_sociale" => array("0" => array("HTML" => "<HR><H2> "._("Groupe informel ou solidaire")." </H2>")));

  if (isFusion()){
      $include = array("pp_nom", "pp_prenom", "pp_date_naissance", "pp_lieu_naissance", "pm_raison_sociale", "gi_nom", "anc_id_client", "matricule", "n_agence", "n_guichet");
      $order = array("idc", "num_client", "anc", "anc_id_client", "matricule", "pp", "pp_nom", "pp_prenom", "pp_date_naissance", "pp_lieu_naissance", "pm", "pm_raison_sociale", "gi", "gi_nom", "n_agence", "n_guichet");
  }else {
      $include = array("pp_nom", "pp_prenom", "pp_date_naissance", "pp_lieu_naissance", "pm_raison_sociale", "gi_nom", "anc_id_client", "matricule");
      $order = array("idc", "num_client", "anc", "anc_id_client", "matricule", "pp", "pp_nom", "pp_prenom", "pp_date_naissance", "pp_lieu_naissance", "pm", "pm_raison_sociale", "gi", "gi_nom");
  }
  // on ajoute num_client au de id_client pour ne pas le confondre avec la varible de session id_client lors de la reprise des données
  $myForm->addField("num_client",_("N° Client"),TYPC_INT);
  $myForm->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);
  $myForm->addTable("ad_cli", OPER_INCLUDE, $include);
  $myForm->setOrder(NULL, $order);
  $myForm->addHiddenType("Recherche");
  $myForm->addHiddenType("field_name");
  $myForm->addHiddenType("choixCompte");
  $myForm->addHiddenType("num_client_dest");
	$checkIdClient = "if (document.ADForm.num_client.value != '' && !isIntPos(document.ADForm.num_client.value)) {alert('"._("Le format du champ N° Client est incorrect : il doit être un nombre naturel")."');return false;}";
  $checkDate = "if (document.ADForm.HTML_GEN_date_pp_date_naissance.value != '' && !isDate(document.ADForm.HTML_GEN_date_pp_date_naissance.value)) {alert('"._("Date de naissance n\'est pas une date valide")."');return false;}";
  $myForm->addFormButton(1, 1, "rech", _("Rechercher"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ann", _("Annuler"), TYPB_BUTTON);
  $myForm->setFormButtonProperties("ann", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $js = "document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';document.ADForm.Recherche.value = 'OK';document.ADForm.field_name.value='$field_name';";
  if ($SESSION_VARS['choixCompte'])
    $js .= "document.ADForm.choixCompte.value='".$SESSION_VARS['choixCompte']."';";
  if ($SESSION_VARS['num_client_dest'])
    $js .= "document.ADForm.num_client_dest.value='".$SESSION_VARS['num_client_dest']."';";

  /*****************************************************/
    if (isFusion()) {
        $nivArrayFenacobu = getNivFenacobuArray("type_niveau > 1");
        // --> Sélection des champs à afficher dans id_loc
        reset($nivArrayFenacobu);
        $includeChoicesFenacobu = array();
        while (list (, $value_fenacobu) = each($nivArrayFenacobu)) {
            if ($value_fenacobu['type_niveau'] == 2)
                array_push($includeChoicesFenacobu, $value_fenacobu['id']);
            //$arrayDisplay[$value_rwanda['id'] ] =$value_rwanda['libelle_localisation'];

        }

        //     $myForm->addField("n_agence", _("Niveau agence"), TYPC_LSB);
        $myForm->setFieldProperties("n_agence", FIELDP_INCLUDE_CHOICES, $includeChoicesFenacobu);
        $myForm->setFieldProperties("n_agence", FIELDP_IS_REQUIRED, true);

        $jsCodeNivFenacobu = "function displayNivFenacobu() {\n";
        $jsCodeNivFenacobu .= "for (i=0; i < document.ADForm.HTML_GEN_LSB_n_guichet.length; ++i) document.ADForm.HTML_GEN_LSB_n_guichet.options[i] = null;\n"; //Vide les choix
        $jsCodeNivFenacobu .= "document.ADForm.HTML_GEN_LSB_n_guichet.length = 0;";
        $jsCodeNivFenacobu .= "document.ADForm.HTML_GEN_LSB_n_guichet.options[document.ADForm.HTML_GEN_LSB_n_guichet.length] = new Option('[Aucun]', 0, true, true);\n"; //[Aucun]
        $jsCodeNivFenacobu .= "document.ADForm.HTML_GEN_LSB_n_guichet.selectedIndex = 0; document.ADForm.HTML_GEN_LSB_n_guichet.length = 1; \n";
        reset($nivArrayFenacobu);
        while (list (, $value_fenacobu) = each($nivArrayFenacobu)) {
            if ($value_fenacobu['parent'] != '') {
                $jsCodeNivFenacobu .= "\tif (document.ADForm.HTML_GEN_LSB_n_agence.value == " . $value_fenacobu['parent'] . ")\n";
                $jsCodeNivFenacobu .= "\t\tdocument.ADForm.HTML_GEN_LSB_n_guichet.options[document.ADForm.HTML_GEN_LSB_n_guichet.length] = new Option('" . $value_fenacobu['libelle_niveau'] . "', '" . $value_fenacobu['id'] . "', false, false);\n";
            }
        }
        $jsCodeNivFenacobu .= "\n}";
        // --> Ajout de la fonction dans le formulaire
        $myForm->addJS(JSP_FORM, "jsCodeNivFenacobu", $jsCodeNivFenacobu);
        // --> ajout des champs

        //AT-150
        // --> Page reloaded Constrution des choix disponibles pour le district
        if (isset($n_agence) && isset($n_guichet)) {
            $choices = array();
            reset($nivArrayFenacobu);
            while (list(, $value) = each($nivArrayFenacobu)) {
                if ($value['parent'] == $n_agence)
                    $choices[$value['id']] = $value['libelle_localisation'];
            }
            $myForm->setFieldProperties("n_guichet", FIELDP_ADD_CHOICES, $choices);
            $myForm->setFieldProperties("n_guichet", FIELDP_DEFAULT, $n_guichet);
        } else {
            $myForm->setFieldProperties("n_guichet", FIELDP_INCLUDE_CHOICES, array(
                "0" => "[Aucun]"
            ));
        }
        $myForm->setFieldProperties("n_guichet", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("n_agence", FIELDP_JS_EVENT, array("onchange" => "displayNivFenacobu()"));

    }
    /*******************************************************/


  $myForm->setFormButtonProperties("rech", BUTP_JS_EVENT, array("onclick" => $js));
  $myForm->setFormButtonProperties("ann", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("rech", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("rech", BUTP_JS_EVENT, array("onclick" => $checkIdClient));
  $myForm->setFormButtonProperties("rech", BUTP_JS_EVENT, array("onclick" => $checkDate));
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ OK : Résultat de recherche d'un client */
else if ($Recherche == "OK") {
  require_once('lib/misc/divers.php');
  $Where = array();
  $Where["gi_nom"] = $gi_nom;
  $Where["pm_raison_sociale"] = $pm_raison_sociale;
  $Where["pp_nom"] = $pp_nom;
  $Where["pp_prenom"] = $pp_prenom;
  $Where["pp_date_naissance"] =php2pg(substr($HTML_GEN_date_pp_date_naissance,0,10));
  $Where["pp_lieu_naissance"] =$pp_lieu_naissance;
  $Where["anc_id_client"] = $anc_id_client;
  $Where["matricule"] = $matricule;
    if (isFusion()) {
        $Where["n_agence"] = substr($HTML_GEN_LSB_n_agence, 0, 10);;
        $Where["n_guichet"] = substr($HTML_GEN_LSB_n_guichet, 0, 10);;
    }


  $num_client=$_POST["num_client"];
  $pos = strrpos($num_client, "-");
  if ($pos === false) { // note: three equal signs
    // not found...
    //Le numero client ne change pas
  } else {
    //Si c'est une numéroration dans laquelle il y'a un -
    $tabNumCli=explode("-",$num_client);
    $num_client=$tabNumCli[1];
  }

  $Where["id_client"] = $num_client;

  $myForm = new HTML_GEN2();
  $nombre = countMatchedClients($Where, "*");
  if ($nombre > 300)
    $myForm->setTitle("$nombre "._("clients correspondent à vos critères, Veuillez affiner votre recherche"));
  else if ($nombre == 0)
    $myForm->setTitle(_("Aucun client ne satisfait vos critères"));
  else {
    $DATAS = getMatchedClients($Where, "*");
    $existPP = false;
    while (list($key, $CLI) = each($DATAS))
      if ($CLI['pp_nom'] != '')
        $existPP = true;
    reset($DATAS);
    if ($existPP) {
      $xtHTML = "<h1 align=\"center\"> "._("Résultats de la recherche")." </h1>";
      $xtHTML .= "<br><h3> "._("Personnes physiques")."</h3>";
      $xtHTML .= "<table align=\"center\">";
        if (isFusion()) {
            $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Nom")."</b></td><td><b>"._("Prénom")."</b></td><td><b>"._("Date naissance")."</b></td><td><b>"._("Lieu naisance")."</b></td><td><b>"._("Agence")."</b></td><td><b>"._("Guichet")."</b></td></tr>";
        }else{
            $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Nom")."</b></td><td><b>"._("Prénom")."</b></td><td><b>"._("Date naissance")."</b></td><td><b>"._("Lieu naisance")."</b></td></tr>";
        }

      $i = 0;
      while (is_array($DATAS[$i])) {
          if (isFusion()) {
              if (isset($DATAS[$i]["n_agence"])) $libel_agence = getNiVFenacobuNom($DATAS[$i]["n_agence"]);
              if (isset($DATAS[$i]["n_guichet"])) $libel_guichet = getNiVFenacobuNom($DATAS[$i]["n_guichet"]);
          }
        if (isset($DATAS[$i]["pp_nom"]))
          $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><a OnClick=\"validateSearch(".addslashes($DATAS[$i]["id_client"]).")\" href=\"rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=".$SESSION_VARS['choixCompte']."&num_client_dest=".$num_client_dest."&client=".$DATAS[$i]["id_client"]."\">".$DATAS[$i]["id_client"]."</a></td><td>".$DATAS[$i]["pp_nom"]."</td><td>".$DATAS[$i]["pp_prenom"]."</td><td>".pg2phpDate($DATAS[$i]["pp_date_naissance"])."</td><td>".$DATAS[$i]["pp_lieu_naissance"]."</td>";
          if (isFusion()) {
              $xtHTML .= "<td > ".$libel_agence."</td ><td > ".$libel_guichet."</td >";
              }
          $xtHTML .= "</tr>";
        $i++;
      }
      $xtHTML .= "</table>";
    }
    $existPM = false;
    while (list($key, $CLI) = each($DATAS))
      if ($CLI['pm_raison_sociale'] != '')
        $existPM = true;
    reset($DATAS);
    if ($existPM) {
      $xtHTML .= "<br><h3> "._("Personnes morales")." </h3>";
      $xtHTML .= "<TABLE align=\"center\">";
        if (isFusion()) {
            $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Raison sociale")."</b></td><td><b>"._("Agence")."</b></td><td><b>"._("Guichet")."</b></td></tr>";
        }else{
            $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Raison sociale")."</b></td></tr>";
        }

      $i = 0;
      while (is_array($DATAS[$i])) {
          if (isFusion()) {
              if (isset($DATAS[$i]["n_agence"])) $libel_agence = getNiVFenacobuNom($DATAS[$i]["n_agence"]);
              if (isset($DATAS[$i]["n_guichet"])) $libel_guichet = getNiVFenacobuNom($DATAS[$i]["n_guichet"]);
          }
        if (isset($DATAS[$i]["pm_raison_sociale"]))
          $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><a OnClick=\"validateSearch(".addslashes($DATAS[$i]["id_client"]).")\" href=\"rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=".$SESSION_VARS['choixCompte']."&num_client_dest=".$num_client_dest."&client=".$DATAS[$i]["id_client"]."\">".$DATAS[$i]["id_client"]."</a></td><td>".$DATAS[$i]["pm_raison_sociale"]."</td>";
          if (isFusion()) {
              $xtHTML .= "<td>" . $libel_agence . "</td><td>" . $libel_guichet . "</td>";
          }
            $xtHTML .= "</tr>";
        $i++;
      }
      $xtHTML .= "</table>";
    }
    $existGI = false;
    while (list($key, $CLI) = each($DATAS))
      if ($CLI['gi_nom'] != '')
        $existGI = true;
    reset($DATAS);
    if ($existGI) {
      $xtHTML .= "<br><h3>"._("Groupes informels ou solidaires")."</h3>";
      $xtHTML .= "<table align=\"center\">";
      $numCpte = $value["num_complet_cpte"];
        if (isFusion()) {
            $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Nom")."</b></td><td><b>"._("Nombre de membres")."</b></td><td><b>"._("Date d'agrément")."</b></td><td><b>"._("Agence")."</b></td><td><b>"._("Guichet")."</b></td></tr>";
        }else{
            $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Nom")."</b></td><td><b>"._("Nombre de membres")."</b></td><td><b>"._("Date d'agrément")."</b></td></tr>";
        }
      $i = 0;
      while (is_array($DATAS[$i])) {
          if (isFusion()) {
              if (isset($DATAS[$i]["n_agence"])) $libel_agence = getNiVFenacobuNom($DATAS[$i]["n_agence"]);
              if (isset($DATAS[$i]["n_guichet"])) $libel_guichet = getNiVFenacobuNom($DATAS[$i]["n_guichet"]);
          }
        if (isset($DATAS[$i]["gi_nom"]))
          $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><a onclick=\"validateSearch(".addslashes($DATAS[$i]["id_client"]).")\" href=\"rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=".$SESSION_VARS['choixCompte']."&num_client_dest=".$num_client_dest."&client=".$DATAS[$i]["id_client"]."\">".$DATAS[$i]["id_client"]."</a></td><td>".$DATAS[$i]["gi_nom"]."</td><td>".$DATAS[$i]["gi_nbre_membr"]."</td><td>".(isset($DATAS[$i]["gi_date_agre"])? pg2phpDate($DATAS[$i]["gi_date_agre"]) : "&nbsp")."</td>|";

          if (isFusion()) {
              $xtHTML .= "<td>" . $libel_agence . "</td><td>" . $libel_guichet . "</td>";
          }

          $xtHTML .="</tr>";
        $i++;
      }
      $xtHTML .= "</table><br/>";
    }
  }
  $xtHTML .= "<br/>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
  $myForm->addFormButton(1,1, "new_search", _("Nouvelle recherche"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("cancel", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $myForm->addHiddenType("Recherche", "KO");
  $myForm->addHiddenType("field_name", $field_name);
  if ($SESSION_VARS['choixCompte']) {
    $JScode1 = "
               function validateSearch(id)
             {
               document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';
               document.ADForm.Recherche.value = 'choixCompte';
             }
               ";
  } else {
    if ($SESSION_VARS['num_client_dest'])
      $JScode1 =  "
                  function validateSearch(id)
                  {
                  window.opener.document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';
                  window.opener.document.ADForm.$field_name.value = id;
                  window.opener.document.ADForm.".$SESSION_VARS['num_client_dest'].".value = id;
                  window.close();\n
                }
                  ";
    else
      $JScode1 =  "
                  function validateSearch(id)
                  {
                  window.opener.document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';
                  window.opener.document.ADForm.$field_name.value = id;
                  window.close();\n
                }
                  ";

  }
  $myForm->addJS(JSP_FORM, "JScode1", $JScode1);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ Cpt : Comptes du client trouvé */
else if ($Recherche == "Compte") {
  if (! isset($field_name))
    $field_name = $nom_champ; //Nom du champs dans lequel va être inscrit le résultat

  if (isset($SESSION_VARS["devise_rech"]))
    $devise = $SESSION_VARS["devise_rech"];
  else
    $devise = NULL;

  $ListeComptes = get_comptes_epargne($client, $devise);

  //compte pr lequel on peut faire de dépôt
  if (isset($SESSION_VARS["is_depot"])) //compte de
     $ListeComptes = getComptesDepotPossible($ListeComptes);


  $title = _("Choix du compte");
  if ($devise != NULL)
    $title .= " ($devise)";
  $myForm = new HTML_GEN2($title);
  $choix = array();
  if (isset($ListeComptes)) {
    $xtHTML = "
              <h3> "._("Client")." : $client</h3>
              <table align=\"center\">
              <tr bgcolor=\"$colb_tableau\">
              <td><b>"._("Numéro de compte")."</b></td>
              <td><b>"._("Intitulé")."</b></td>
              <td><b>"._("Devise")."</b></td>
              </tr>";
    foreach($ListeComptes as $key=>$value) {
      $numCpte = $value["num_complet_cpte"];
      $intCpte = $value["intitule_compte"];
      $devCpte = $value["devise"];
      $choix[$key] = $numCpte." ".$intCpte;
      $numCpte=addslashes($numCpte);
      $key=addslashes($key);
      $xtHTML .= " <tr bgcolor=\"$colb_tableau\"> ";
      if (!isset($SESSION_VARS['devise_cpte_dest'])) {
            $xtHTML .=" <td><a onclick=\"validateSearch('$numCpte', $key, '$client')\" href=\"#\">$numCpte</a>";
      } else {
            $xtHTML .=" <td><a onclick=\"validateSearch('$numCpte', $key,'$devCpte', '$client')\" href=\"#\">$numCpte</a>";
      }

      $xtHTML .=" </td> ";
      $xtHTML .="
                 <td>$intCpte</td>
                 <td>$devCpte</td>
                 </tr>";
    }
    $xtHTML .= "</table>";
  }
  if (!isset($SESSION_VARS['devise_cpte_dest'])) {
  	 $JScode1 =  "
              function validateSearch(id, num, client)
            {
              window.opener.document.ADForm.".$SESSION_VARS['cpt_dest'].".value = id;
              if(window.opener.document.ADForm.contains(window.opener.document.ADForm.id_cli_prelev)){
                window.opener.document.ADForm.id_cli_prelev.value = client;
              }
              window.opener.document.ADForm.".$SESSION_VARS['id_cpt_dest'].".value = num;
              window.close();
            }
              ";
  } else {
  	 $JScode1 =  "
              function validateSearch(id, num,devise, client)
            {
              window.opener.document.ADForm.".$SESSION_VARS['cpt_dest'].".value = id;
              window.opener.document.ADForm.".$SESSION_VARS['id_cpt_dest'].".value = num;
              if(window.opener.document.ADForm.contains(window.opener.document.ADForm.id_cli_prelev)){
                window.opener.document.ADForm.id_cli_prelev.value = client;
              }
              window.opener.document.ADForm.".$SESSION_VARS['devise_cpte_dest'].".value = devise;
              window.close();
            }
              ";
  }
  unset($SESSION_VARS['devise_cpte_dest']);
  unset($SESSION_VARS["is_depot"]);

  // window.opener.document.ADForm.cpt_dest.value = id; // ne plus mettre ctp_dest en  dur
  //window.opener.document.ADForm.id_cpt_dest.value = num;

  $myForm->addJS(JSP_FORM, "JScode1", $JScode1);
  $xtHTML .= "<br>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
  $myForm->addFormButton(1,1, "new_search", _("Nouvelle recherche"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("cancel", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $myForm->addHiddenType("Recherche", "KO");
  $myForm->buildHTML();
  echo $myForm->getHTML();

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, _("[rech_client.php] Problème de propagation des variables"));

require("lib/html/HtmlFooter.php");
?>