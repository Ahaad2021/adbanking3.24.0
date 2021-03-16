<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Recherche d'un beneficiaire dans la BD
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
require_once('lib/dbProcedures/engrais_chimiques.php');
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

/*{{{ KO : Critères de recherche d'un beneficiaire */

if (!isset($Recherche) || $Recherche == "KO") {
  if (!isset($field_name))
    $field_name="num_benef"; //Nom du champs dans lequel va être inscrit le résultat

  $Title = _("Recherche d'un bénéficiaire");
  $myForm = new HTML_GEN2($Title);

  $myForm->addHTMLExtraCode("db", "<H4> "._("Identificateur/Détails du bénéficiaire")." </H4>");

  $myForm->addHiddenType("Recherche");
  $myForm->addHiddenType("field_name");
  $checkIdBenef = "if (document.ADForm.num_benef.value != '' && !isIntPos(document.ADForm.num_benef.value)) {alert('"._("Le format du champ N° Beneficiaire est incorrect : il doit être un nombre naturel")."');return false;}";

  $condi="type_localisation = 1";
  $loc_province = getListelocalisationPNSEB($condi);
  natcasesort($loc_province);
  $color = $colb_tableau;
  $html = "<link rel=\"stylesheet\" href=\"/lib/misc/js/chosen/css/chosen.css\">";
  $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";

  //$html .= "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=0 cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("N° Bénéficiaire")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($num_benef) && $num_benef != null){
    $html .= "<input type=\"text\" ID=\"num_benef\"  NAME=\"num_benef\" style=\"width:250px\" VALUE=\"$num_benef\" ";
  }
  else{
    $html .= "<input type=\"text\" ID=\"num_benef\" NAME=\"num_benef\" style=\"width:250px\" ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Nom et Prenom")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($nom_prenom) && $nom_prenom != null){
    $html .= "<input type=\"text\" ID=\"nom_prenom\" NAME=\"nom_prenom\" style=\"width:250px\" VALUE=\"$nom_prenom\" ";
  }
  else{
    $html .= "<input type=\"text\" ID=\"nom_prenom\" NAME=\"nom_prenom\" style=\"width:250px\" ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("NIC")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($nic) && $nic != null){
    $html .= "<input type=\"text\" ID=\"nic\" NAME=\"nic\" style=\"width:250px\" VALUE=\"$nic\" ";
  }
  else{
    $html .= "<input type=\"text\" ID=\"nic\" NAME=\"nic\" style=\"width:250px\" ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Province")."</b></TD>";
  $html .= "<TD>\n";
  $html .= "<select class=\"chosen-select\" ID=\"id_province\" NAME=\"id_province\" style=\"width:185px\" ";
  $html .= "onchange=\"reload_page();\">\n";
  if (!isset($id_province) && $id_province == null) {
    $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
  }
  if (isset($loc_province))
    foreach($loc_province as $key=>$value){
      if (isset($id_province) && $id_province != null && $key == $id_province){
        $html .= "<option value=$key selected>".$value."</option>\n";
      }
      else {
        $html .= "<option value=$key>".$value."</option>\n";
      }
    }
  $html .= "</select>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  if (isset($id_province) && $id_province != null){
    $condi="type_localisation = 2 AND parent = ".$id_province;
    $loc_commune = getListelocalisationPNSEB($condi);
    if ($loc_commune != null){
      natcasesort($loc_commune);
    }
    $html .= "<TR bgcolor=$color>";
    $html.="<TD align=\"left\"><b>"._("Commune")."</b></TD>";
    $html .= "<TD>\n";
    $html .= "<select class=\"chosen-select\" ID=\"id_commune\" NAME=\"id_commune\" style=\"width:250px\" ";
    $html .= "onchange=\"reload_page();\">\n";
    if (!isset($id_commune) && $id_commune == null) {
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    }
    if (isset($loc_commune))
      foreach($loc_commune as $key=>$value){
        if (isset($id_commune) && $id_commune != null && $key == $id_commune){
          $html .= "<option value=$key selected>".$value."</option>\n";
        }
        else {
          $html .= "<option value=$key>".$value."</option>\n";
        }
      }
    $html .= "</select>\n";
    $html .= "</TD>";
    $html .= "</TR>\n";
  }
  if (isset($id_commune) && $id_commune != null){
    $condi="type_localisation = 3 AND parent = ".$id_commune;
    $loc_zone = getListelocalisationPNSEB($condi);
    if ($loc_zone != null){
      natcasesort($loc_zone);
    }
    $html .= "<TR bgcolor=$color>";
    $html.="<TD align=\"left\"><b>"._("Zone")."</b></TD>";
    $html .= "<TD>\n";
    $html .= "<select class=\"chosen-select\" ID=\"id_zone\" NAME=\"id_zone\" style=\"width:250px\" ";
    $html .= "onchange=\"reload_page();\">\n";
    if (!isset($id_zone) && $id_zone == null) {
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    }
    if (isset($loc_zone))
      foreach($loc_zone as $key=>$value){
        if (isset($id_zone) && $id_zone != null && $key == $id_zone){
          $html .= "<option value=$key selected>".$value."</option>\n";
        }
        else {
          $html .= "<option value=$key>".$value."</option>\n";
        }
      }
    $html .= "</select>\n";
    $html .= "</TD>";
    $html .= "</TR>\n";
  }
  if (isset($id_zone) && $id_zone != null){
    $condi="type_localisation = 4 AND parent = ".$id_zone;
    $loc_colline = getListelocalisationPNSEB($condi);
    if ($loc_colline != null){
      natcasesort($loc_colline);
    }
    $html .= "<TR bgcolor=$color>";
    $html.="<TD align=\"left\"><b>"._("Colline")."</b></TD>";
    $html .= "<TD>\n";
    $html .= "<select class=\"chosen-select\" ID=\"id_colline\" NAME=\"id_colline\" style=\"width:250px\" ";
    $html .= "onchange=\"reload_page();\">\n";
    if (!isset($id_colline) && $id_colline == null) {
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    }
    if (isset($loc_colline))
      foreach($loc_colline as $key=>$value){
        if (isset($id_colline) && $id_colline != null && $key == $id_colline){
          $html .= "<option value=$key selected>".$value."</option>\n";
        }
        else {
          $html .= "<option value=$key>".$value."</option>\n";
        }
      }
    $html .= "</select>\n";
    $html .= "</TD>";
    $html .= "</TR>\n";
  }
  $html .= "</TABLE>\n";

  $html .= "<script type=\"text/javascript\">\n";
  $html .= "var config = { '.chosen-select' : {} }\n";
  $html .= "for (var selector in config) {\n";
  $html .= "$(selector).chosen(config[selector]); }\n";
  $html .= "</script>\n";

  $m_agc=$_REQUEST['m_agc'];

  $html .= "<script type=\"text/javascript\">\n";
  $html .= "function reload_page() { \n";
  $html .= " var url; var commune; var zone; var colline; var id_benef = document.getElementById('num_benef').value; \n";
  $html .= " var nom = document.getElementById('nom_prenom').value; \n";
  $html .= " var nic = document.getElementById('nic').value; \n";
  $html .= " var province = document.getElementById('id_province').value; \n";
  $html .= " if (province != '') { \n";
  $html .= " url = '$SERVER_NAME/modules/clients/rech_beneficiaire.php?m_agc=$m_agc&field_name=id_beneficiaire&num_benef='+id_benef+'&nom_prenom='+nom+'&nic='+nic+'&id_province='+province; \n";
  $html .= " } \n";
  $html .= " if (document.getElementById('id_commune')) { \n";
  $html .= " var commune = document.getElementById('id_commune').value; \n";
  $html .= " url = '$SERVER_NAME/modules/clients/rech_beneficiaire.php?m_agc=$m_agc&field_name=id_beneficiaire&num_benef='+id_benef+'&nom_prenom='+nom+'&nic='+nic+'&id_province='+province+'&id_commune='+commune; \n";
  $html .= " } \n";
  $html .= " if (document.getElementById('id_zone')) { \n";
  $html .= " var zone = document.getElementById('id_zone').value; \n";
  $html .= " url = '$SERVER_NAME/modules/clients/rech_beneficiaire.php?m_agc=$m_agc&field_name=id_beneficiaire&num_benef='+id_benef+'&nom_prenom='+nom+'&nic='+nic+'&id_province='+province+'&id_commune='+commune+'&id_zone='+zone; \n";
  $html .= " } \n";
  $html .= " if (document.getElementById('id_colline')) { \n";
  $html .= " var colline = document.getElementById('id_colline').value; \n";
  $html .= " url = '$SERVER_NAME/modules/clients/rech_beneficiaire.php?m_agc=$m_agc&field_name=id_beneficiaire&num_benef='+id_benef+'&nom_prenom='+nom+'&nic='+nic+'&id_province='+province+'&id_commune='+commune+'&id_zone='+zone+'&id_colline='+colline; \n";
  $html .= " } \n";
  $html .= " window.location = url; \n";
  $html .= " } \n";
  $html .= "</script>\n";

  $myForm->addFormButton(1, 1, "rech", _("Rechercher"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ann", _("Annuler"), TYPB_BUTTON);
  $myForm->setFormButtonProperties("ann", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $js = "document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';document.ADForm.Recherche.value = 'OK';document.ADForm.field_name.value='$field_name';";
  $myForm->setFormButtonProperties("rech", BUTP_JS_EVENT, array("onclick" => $js));
  $myForm->setFormButtonProperties("ann", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("rech", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("rech", BUTP_JS_EVENT, array("onclick" => $checkIdBenef));

  $myForm->addHTMLExtraCode("html",$html);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ OK : Résultat de recherche d'un beneficiaire */
else if ($Recherche == "OK") {
  require_once('lib/misc/divers.php');
  $Where = array();
  $Where["nom_prenom"] = $nom_prenom;
  $Where["nic"] = $nic;
  $Where["id_province"] = $id_province;
  $Where["id_commune"] = $id_commune;
  $Where["id_zone"] = $id_zone;
  $Where["id_colline"] = $id_colline;

  $num_benef=$_POST["num_benef"];
  $pos = strrpos($num_benef, "-");
  if ($pos === false) { // note: three equal signs
    // not found...
    //Le numero client ne change pas
  } else {
    //Si c'est une numéroration dans laquelle il y'a un -
    $tabNumCli=explode("-",$num_benef);
    $num_benef=$tabNumCli[1];
  }

  $Where["id_beneficiaire"] = $num_benef;

  $myForm = new HTML_GEN2();
  $nombre = countMatchedBeneficiaire($Where, "*");
  if ($nombre > 300)
    $myForm->setTitle("$nombre "._("beneficiaires correspondent à vos critères, Veuillez affiner votre recherche"));
  else if ($nombre == 0)
    $myForm->setTitle(_("Aucun Beneficiaire ne satisfait vos critères"));
  else {
    $DATAS = getMatchedBeneficiaire($Where, "*");
    $existPP = false;
    while (list($key, $CLI) = each($DATAS))
      if ($CLI['nom_prenom'] != '')
        $existPP = true;
    reset($DATAS);
    if ($existPP) {
      $xtHTML = "<h1 align=\"center\"> "._("Résultats de la recherche")." </h1>";
      $xtHTML .= "<br><h3> "._("Beneficiaire(s)")."</h3>";
      $xtHTML .= "<table align=\"center\">";
      $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Nom-Prenom")."</b></td><td><b>"._("NIC")."</b></td></tr>";
      $i = 0;
      while (is_array($DATAS[$i])) {
        if (isset($DATAS[$i]["nom_prenom"]))
          $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><a OnClick=\"validateSearch(".addslashes($DATAS[$i]["id_beneficiaire"]).")\" href=\"rech_beneficiaire.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=".$SESSION_VARS['choixCompte']."&num_client_dest=".$num_client_dest."&client=".$DATAS[$i]["id_beneficiaire"]."\">".$DATAS[$i]["id_beneficiaire"]."</a></td><td>".$DATAS[$i]["nom_prenom"]."</td><td>".$DATAS[$i]["nic"]."</td></tr>";
        $i++;
      }
      $xtHTML .= "</table>";
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

else signalErreur(__FILE__,__LINE__,__FUNCTION__, _("[rech_beneficiaire.php] Problème de propagation des variables"));

require("lib/html/HtmlFooter.php");
?>