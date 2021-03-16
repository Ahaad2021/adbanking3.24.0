<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Récupération des parts sociales.
 * @package Recupdata
 */

require_once("lib/html/HTML_GEN2.php");
require_once("lib/dbProcedures/handleDB.php");
require_once("lib/dbProcedures/guichet.php");
require_once("lib/dbProcedures/agence.php");
require_once("lib/misc/divers.php");
require_once("lib/misc/VariablesGlobales.php");

setMonnaieCourante($global_monnaie);
debug($_POST);
$global_id_agence=getNumAgence();
?>

<html>
<head>
<title>
<?php echo $ProjectName; ?>
</title>
<script type="text/javascript" src="<?php echo "$http_prefix/lib/java/scp.php?m_agc=$global_id_agence&http_prefix=$http_prefix";?>"></script>
                                   </head>
                                   <body bgcolor="white">
                                                 <table width="100%" cellpadding=5 cellspacing=0 border=0>
                                                                                 <tr>
                                                                                 <td><a target="_blank"  href="http://www.aquadev.org"><img border=0 title="<?php echo _("ADbanking Logo");?>" alt="<?php echo _("ADbanking Logo");?>" width='400' height='40' src="../../images/ADbanking_logo.jpg"></a></td>
                                                                                                           <td valign="bottom" align="center" ><font face="helvetica,verdana" size="+2"> <?php echo _("Module de reprise des parts sociales des clients");?></font></td>
                                                                                                                                   </tr>
                                                                                                                                   </table>
                                                                                                                                   <hr>
                                                                                                                                   <br><br><P>
                                                                                                                                   <?php

if ((! isset($etape)) || ($etape == 1)) { /* Ecran de choix du client à traiter */
  /* Création du formulaire */
  $myform = new HTML_GEN2();
  $html  = "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";

  /* Saisie du numéro du client */
  $html .= "<TABLE align =\"center\"><TR><TD><font face=\"helvetica,verdana\"><b>Choix du client</b></font></TD></TR></TABLE>\n";
  $html .= "<BR><TABLE align=\"center\"  border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $html .= "<TR bgcolor='#e0e0ff'><TD>N°Client</TD><TD><INPUT TYPE=\"text\" NAME=\"num_client\" size=10>";
  $html .= "<FONT size=\"2\"><A href=# onclick=\"OpenBrw('../../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client','"._("Recherche")."');return false;\">"._("RECHERCHE")."</A></FONT></TD></TR></TABLE>\n";
  $html .= "<BR>";

  /* Boutons */
  $html .= "<BR><TABLE align=\"center\"><TR>";
  $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Valider")."\" onclick=\"document.ADForm.etape.value = 2;\"></TD>";
  $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Annuler")."\" onclick=\"document.ADForm.etape.value = 5;\"></TD>";
  $html .= "</TR></TABLE>\n";

  /* Variables postées */
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"etape\">\n";

  /* Génération du HTML */
  $myform->addHTMLExtraCode("html",$html);
  $myform->buildHTML();
  echo $myform->getHTML();
} else if ($etape == 2) { /* Ecran de saisie des parts sociales */
  /* Si on ne vient pas de l'étape 3, alors on vient de l'étape 1. Donc, récupérer le numéro du client saisi */
  if ($vientde !=3)
    $numero = $num_client;

  /* Vérification du numéro saisi */
  $msg ="";
  if (is_numeric($numero) || (intval($numero) < 0)) {
    if (!client_exist($numero)) /* Existance du client */
      $msg = _("Il n'existe aucun client ayant ce numéro");
  } else {
    $msg = _("Le numéro du client est incorrect")." ";
  }


  /* S'il y a une erreur afficher le message d'erreur et reprendre la saisie */
  if ($msg != "") {
    $htmlmess="<Table align=\"center\" valign=\"middle\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("ERREUR")."</b></font></TD></TR></Table>";
    $htmlmess .= "<br><br><FORM NAME=\"ADForm\" ACTION=\"$PHP_SELF\" METHOD=\"POST\" onsubmit=\"if (! isSubmit){ isSubmit=true; return true;} else {return false;}\">\n";
    $htmlmess .=  "<TABLE ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">\n";
    $htmlmess .= "<TR BGCOLOR=$colb_tableau>\n<TD ALIGN=\"CENTER\><P ALIGN =\"CENTER\"><b>".$msg."</b></P></TD></TR>\n";
    $htmlmess .="<TR BGCOLOR=$colb_tableau>\n<TD ALIGN=\"CENTER\"></TD></TR>\n";

    /* Boutons */
    $htmlmess .="<TR>\n<TD ALIGN=\"CENTER\" COLSPAN=\"1\">\n<INPUT TYPE=SUBMIT NAME=\"OK\" VALUE=\""._("OK")."\" ONCLICK=\"document.ADForm.etape.value = '1';\">\n</TD>\n</TR>\n</TABLE>\n";

    echo $htmlmess;
    exit();
  }

  /* Si pas d'erreur */

  /* Connection à la base de donnée */
  $db = $dbHandler->openConnection();

  /* Infos sur le client */
  $infosClient = getClientDatas($numero);

  /* Valeur nominale de la part sociale */
  $AG = getAgenceDatas($global_id_agence);
  $valeur = $AG['val_nominale_part_sociale'];

  /* Devise et compte comptable des parts sociales */
  $prod_ps = getProdEpargne(2);
  $devise = $prod_ps['devise'];
  $cpte_ps = checkCptDeviseOK($prod_ps['cpte_cpta_prod_ep'],$devise);

  /* Vérifcation de l'existence d'un compte de PS du client.*/
  $sql = "SELECT num_complet_cpte, date_ouvert, solde, id_cpte from ad_cpt where id_ag = $global_id_agence and id_prod = 2 and id_titulaire = $numero";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(_('Reprise des PS'),_('Existence compte PS'),$result->getMessage());
  }
  if ($result->numRows()>0) {
    $ok = true;
    $rows = $result->fetchrow();
    $num_complet_cpte = $rows[0];
    $date_ouvert = $rows[1];
    $id_cpte_ps = $row[3];
    $solde_ps = $rows[2];
    // Nbre de parts sociales
    $nbre = $rows[2]/$valeur;
    $nbre = ceil($nbre);
  } else
    $ok = false;

  /* Déconnection à la base de données */
  $dbHandler->closeConnection(true);

  /* Création du formulaire */
  $mon_form = new HTML_GEN2();
  $html  = "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";

  /* En-tête */
  $html .= "\n<Table align=\"center\" valign =\"middle\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("Ajout de parts sociales")."</b></font></TD></TR></Table>\n";

  /* Informations sur le client */
  $html="<P><TABLE align=\"center\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("Client")."</b></font></TD></TR></TABLE>\n";
  $html .="<BR>";
  $html .="<TABLE align = \"center\" border=\"1\"><TR bgcolor='#e0e0ff'><TD>"._("Numéro client")."</TD><TD></TD><TD><b>$numero</b></TD></TR>";
  if ($infosClient["statut_juridique"] == 1) {
    $html .="<TR bgcolor='#e0e0ff'><TD>"._("Nom du client")."</TD><TD></TD><TD><b>".$infosClient["pp_prenom"]." ".$infosClient["pp_nom"]."</b></TD></TR>";
    $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Date de naissance")."</TD><TD></TD><TD><b>".pg2phpdate($infosClient["pp_date_naissance"])."</b></TD></TR>";
    $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Lieu de naissance")."</TD><TD></TD><TD><b>".$infosClient["pp_lieu_naissance"]."</b></TD></TR>";
    $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Numéro pièce d'identité")."</TD><TD></TD><TD><b>".$infosClient["pp_nm_piece_id"]."</b></TD></TR>";
  } else if ($infosClient["statut_juridique"] == 2)
    $html .= "<TR bgcolor='#e0e0ff'><TD>R"._("aison sociale")."</TD><TD></TD><TD><b>".$infosClient["pm_raison_sociale"]."</b></TD></TR>";
  else if ($infosClient["statut_juridique"] == 3 || $infosClient["statut_juridique"] == 4)
    $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Groupe informel ou solidaire")."</TD><TD></TD><TD><b>".$infosClient["gi_nom"]."</b></TD></TR>";

  $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Nombre de parts renseignées dans le logiciel")."</TD><TD></TD><TD><b>".$infosClient["nbre_parts"]."</b></TD></TR></TABLE>";

  /* Si le client a déjà un compte de parts sociales, donner les infos du compte  */
  if ($ok) {
    $html .= "<BR>";
    $html .= "<TABLE align = \"center\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("Informations sur le compte PS du client")."</b></font></TD></TR></TABLE><BR>";
    $html .= "<TABLE align = \"center\" border=\"1\"><TR bgcolor='#e0e0ff'><TD>"._("Numéro compte")." </TD><TD></TD><TD><b>".$num_complet_cpte."</b></TD></TR>";
    $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Date d'ouverture")." </TD><TD></TD><TD><b>".pg2phpdate($date_ouvert)."</b></TD></TR>";
    $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Nombre de Parts sociales")." </TD><TD></TD><TD><b>".$infosClient["nbre_parts"]."</b></TD></TR></TABLE>";
  }

  /* Saisie du nombre de parts sociales et éventuellement du montant à ajouter */
  $html .= "<BR>";
  $html .= "<Table align = \"center\"><TR align = \"center\" ><TD><font face=\"helvetica,verdana\"><b>"._("Saisissez le nombre de ps à ajouter")."</TD></TR></font></Table>";

  $html .= "<BR><TABLE align=\"center\" border=\"1\"><TR bgcolor='#e0e0ff' align =\"center\"><TD><INPUT TYPE=\"text\" NAME=\"nb_ps\" size=5></TD></TR></TABLE>\n";

  $html .= "<Table align = \"center\"><TR align = \"center\" ><TD><font face=\"helvetica,verdana\"><b>"._("Saisissez le montant des ps à ajouter")."</TD></TR></font></Table>";

  $html .= "<BR><TABLE align=\"center\" border=\"1\"><TR bgcolor='#e0e0ff' align =\"center\"><TD><INPUT TYPE=\"text\" NAME=\"montant_ps\" size=10></TD></TR></TABLE>\n";

//  /* Saisie montant part sociale à ajouter si par tranche*/
//  $AGC = getAgenceDatas(getNumAgence());
//  if($AGC['tranche_part_sociale'] == "t"){
//  	$html .= "<Table align = \"center\"><TR align = \"center\" ><TD><font face=\"helvetica,verdana\"><b>Saisissez le montant des ps à ajouter</TD></TR></font></Table>";
//
//    $html .= "<BR><TABLE align=\"center\" border=\"1\"><TR bgcolor='#e0e0ff' align =\"center\"><TD><INPUT TYPE=\"text\" NAME=\"montant_ps\" size=10></TD></TR></TABLE>\n";
//  }

  /* Affichage la valeur d'une PS */
  $html .= "<BR><TABLE align=\"center\"><TR bgcolor='#e0e0ff'><TD>"._("Valeur nominale d'une part sociale")."</TD><TD></TD><TD><b>".afficheMontant($valeur,true)."</b></TD></TR></TABLE>\n";

  /* Récupération du compte de substitution du compte de parts sociales */
  $cpte_subs_ps = "";
  $fp=fopen("../traduction.conf",'r');
  if ($fp == false) {
    echo "<BR/><BR/><P align=center><FONT color=red>".sprintf(_("Le fichier %s n'a pas été trouvé à l'endroit attendu"), "<CODE><B>traduction.conf</B></CODE>" )."</FONT></P>";
    die();
  }
  while (!feof($fp)) {
    /* Récupération d'une ligne du fichier */
    $ligne=fgets($fp,1024);

    /* Si c'est le paramétrage du compte de part sociale */
    $debut = substr(trim($ligne),0,12);
    if ($debut =="cpte_subs_ps") {
      $ligne = ereg_replace("cpte_subs_ps","",$ligne); /* éliminer "cpt_ps" de la ligne */
      $ligne = ereg_replace("\t","|",$ligne); /* Remplacer tabulation par | */
      $ligne = ereg_replace("\n","|",$ligne); /* Remplacer fin de ligne par | */
      $tab = explode("|",$ligne); /* récupérer la ligne sous forme de tableau */
      $cpte_subs_ps = trim($tab[0]);  /* éliminer les espaces de début et de fin de ligne */
    }
  }
  fclose($fp);

  /* Si le compte de substitution n'est pas paramétré */
  if ($cpte_subs_ps == "")
    signalErreur(_("Le compte de substitut des parts sociales n'est pas paramétré"),"");
  else
    $cpte_subs_ps = checkCptDeviseOK($cpte_subs_ps,$devise);

  /* Informations comptables */
  $html .="<p align='center'><font face='helvetica,verdana'><b>"._("Section Comptes comptables")."</b></font><br><br>";
  $html .="<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  $html .="<tr><td>"._("Compte comptable des parts sociales")." : $cpte_ps</td></tr>";
  $html .="<tr><td>"._("Compte comptable de sustitution des parts sociales")." : $cpte_subs_ps</td></tr>";

  /* Boutons */
  $html .= "<BR>";
  $html .= "<TABLE align=\"center\" ><TR><TD><INPUT TYPE=\"submit\" VALUE=\"Valider\" onclick=\"document.ADForm.etape.value = 3;document.ADForm.numero.value = $numero;document.ADForm.valeur.value = $valeur;\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\"Retour\" onclick=\"document.ADForm.etape.value = '1';\"></TD></TR></TABLE>\n";

  /* Variables post */
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"etape\">";
  $html .="<INPUT type=\"hidden\" name=\"numero\">\n";
  $html .="<INPUT type=\"hidden\" name=\"nb\"  value=\" $nbre\">\n";
  $html .="<INPUT type=\"hidden\" name=\"valeur\">; \n";
  $html .="<INPUT type=\"hidden\" name=\"cpte_ps\"  value=\"$cpte_ps\">\n";
  $html .="<INPUT type=\"hidden\" name=\"cpte_subs_ps\"  value=\"$cpte_subs_ps\">\n";
  $html .="<INPUT type=\"hidden\" name=\"devise\"  value=\"$devise\">\n";
  $html .="<INPUT type=\"hidden\" name=\"ok\"  value=\"$ok\">\n";
  //$html .="<INPUT type=\"hidden\" name=\"id_cpte_ps\"  value=\"$devise\">\n";
  $SESSION_VARS["nb_ps"] = $infosClient["nbre_parts"];
  $mon_form->addHTMLExtraCode("text",$html);
  $mon_form->buildHTML();
  echo $mon_form->getHTML();

} else if ($etape == 3) { /* Affichage des informations saisies pour confirmation */
  $msg = "";
  $msg1 = "";
  debug($nb_ps,_("nbre de ps saisi"));
  debug($montant_ps,_("montant saisi"));
  /* Vérifier que le nombre des parts sociales et le montant ont été correctment saisi */
  if (!is_numeric($nb_ps) || ($nb_ps < 0) || ($nb_ps == '')) {
    $msg = _("Le nombre de ps n'est pas correctement renseigné.");
  }
  if (!is_numeric($montant_ps) || ($montant_ps < 0) || ($montant_ps == '')) {
    $msg1 = _("Le montant des parts sociales à ajouter n'a pas été renseigné");
  }

  /* Si erreur */
  if ($msg != "" || $msg1!="") {
    $htmlmess = "\n<Table align=\"center\" valign =\"middle\"><TR><TD><font face=\"helvetica,verdana\"><b>ERREUR</b></font></TD></TR></Table>\n";
    // Génération de l'entête de formulaire
    $htmlmess .= "<br><br><FORM NAME=\"ADForm\" ACTION=\"$PHP_SELF\" METHOD=\"POST\" onsubmit=\"if (! isSubmit){ isSubmit=true; return true;} else {return false;}\">\n";

    // On génère un tableau à 2 lignes, la première contenant le message et la seconde les boutons
    $htmlmess .=  "<TABLE ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">\n";
    $htmlmess .= "<TR BGCOLOR=$colb_tableau>\n";
    $htmlmess .= "<TD ALIGN=\"CENTER\>";
    $htmlmess .= "<P ALIGN =\"CENTER\"><b>".$msg."</b></P></TD></TR>\n";
    $htmlmess .= "<TD ALIGN=\"CENTER\>";
    $htmlmess .= "<P ALIGN =\"CENTER\"><b>".$msg1."</b></P></TD></TR>\n";

    // Lignes vides
    $htmlmess .="<TR BGCOLOR=$colb_tableau>\n<TD ALIGN=\"CENTER\"></TD></TR>\n";

    // Ligne des boutons
    $htmlmess .="<TR>\n";

    // Pour chaque bouton dans la liste. Les custom button sont submit par défaut
    $htmlmess .= "<TD ALIGN=\"CENTER\" COLSPAN=\"1\">\n";
    $htmlmess .= "<INPUT TYPE=SUBMIT NAME=\"OK\" VALUE=\"OK\" ONCLICK=\"document.ADForm.etape.value = 2;document.ADForm.numero.value = $numero;document.ADForm.vientde.value = 3;\">\n";
    $htmlmess .="</TD>\n</TR>\n</TABLE>\n</FROM>";

    // Variables post
    $htmlmess .= "<INPUT TYPE=\"hidden\" NAME=\"etape\">";
    $htmlmess .= "<INPUT TYPE=\"hidden\" NAME=\"numero\">";
    // $htmlmess .= "<INPUT TYPE=\"hidden\" NAME=\"nb\">";
    $htmlmess .= "<INPUT TYPE=\"hidden\" NAME=\"vientde\">";
    $htmlmess .= "<INPUT TYPE=\"hidden\" NAME=\"id_cpte_ps\">";

    // Générattion de la page
    echo $htmlmess;
    exit();
  }

  /* Si pas d'erreur */
  global $db;
  $db = $dbHandler->openConnection();

  /* Recupération des infos du compte de substitut */
  $temp['num_cpte_comptable']= $cpte_subs_ps;
  $detail_attente_ps = getComptesComptables($temp);
  $cpte_att_ps = $cpte_subs_ps." ".$detail_attente_ps[$cpte_subs_ps]['libel_cpte_comptable'];

  /* Informations du compte de parts sociales à créer ou à modifier */
  if (!$ok) { /* Si le client ne possède pas encore un compte de parts sociales alors créer le compte */
    // Type de numérotation des comptes
    $AGC = getAgenceDatas($global_id_agence);
    $type_num_cpte = $AGC['type_numerotation_compte'];

    // Rang du compte
    $num_cpte = getRangDisponible($numero);

    /* Numéro complet du compte à créer */
    $sql = "SELECT makeNumCompletCpte($type_num_cpte, $numero, $num_cpte);";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Calcul du numéro complet du compte'),$result->getMessage());
    }
    $row = $result->fetchrow();
    $num_cplet_cpte = $row[0];

    $today = date("d/m/Y"); /* Date ouverture */
    $date_ouvert = date("d/m/Y");
    $mnt_ps_ajoute = $montant_ps;
  } else { /* Le client possède un compte de parts sociales. Donc c'est une mise à jour */
    $AG = getAgenceDatas(getNumAgence());
    /* Num complet du compte */
    $sql = "SELECT num_complet_cpte,num_cpte,date_ouvert,solde from ad_cpt where id_ag = $global_id_agence and id_prod = 2 and id_titulaire = $numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout de PS'),$result->getMessage());
    }
    $row = $result->fetchrow();
    $num_cplet_cpte = $row[0];
    $num_cpte = $row[1];
    $date_ouvert = $row[2];
    $solde_ps = $row[3] + $montant_ps;
    $nb = $SESSION_VARS["nb_ps"]+$nb_ps;
  }

  /* Créer le formulaire */
  $mon_form = new HTML_GEN2();

  $html = "\n<Table align=\"center\" valign =\"middle\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("Création ou mise à jour du compte de PS du client")." $numero<b></font></TD></TR></Table>\n";
  $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";

  /* Affichage des infos du compte à créer ou à modifier  */
  $html .= "<P><TABLE align=\"center\" ><TR><TD><b>"._("Informations du compte de PS à créer ou à modifier")."</b></TD></TR></TABLE>\n";
  $html .= "<BR>";
  $html .="<TABLE align =\"center\" border=\"1\"><TR bgcolor='#e0e0ff'><TD>"._("Numéro Compte")."</TD><TD><b>".$num_cplet_cpte."</b></TD></TR>";
  $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Date création")."</TD><TD><b>".$date_ouvert."</b></TD></TR>";
  $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Nombre de parts sociales")."</TD><TD><b>".$nb."</b></TD></TR>";
  $html .= "<TR bgcolor='#e0e0ff'><TD>"._("Solde du compte<")."/TD><TD><b>".afficheMontant($solde_ps,true)."</b></TD></TR></TABLE>";
  $html .= "<BR>";

  /* Comptes comptables au débit et au crédit */
  $html .="<p align='center'><font face='helvetica,verdana'><b>"._("Section Comptes comptables<")."/b></font><br><br>";
  $html .="<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  $html .="<tr><td>"._("Compte comptable des parts sociales")." : $cpte_ps</td></tr>";
  $html .="<tr><td>"._("Compte comptable de substitution des parts sociales")." : $cpte_subs_ps</td></tr>";

  // Boutons
  $html .= "<BR>";
  $html .= "<TABLE align=\"center\" ><TR><TD><INPUT TYPE=\"submit\" VALUE=\"Valider\" onclick=\"document.ADForm.etape.value = 4;\"></TD><TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour")."\" onclick=\"document.ADForm.etape.value = 2;document.ADForm.numero.value = $numero;document.ADForm.vientde.value = 3;\"></TD></TR></TABLE>\n";

  // Variables post
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"etape\">";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"numero\" value=\"$numero\">";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"nb_ps\" value=\"$nb_ps\">";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"montant_ps\" value=\"$montant_ps\">";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"valeur\" value=\"$valeur\">";
  $html .="<INPUT type=\"hidden\" name=\"cpte_ps\"  value=\"$cpte_ps\">\n";
  $html .="<INPUT type=\"hidden\" name=\"cpte_subs_ps\"  value=\"$cpte_subs_ps\">\n";
  $html .="<INPUT type=\"hidden\" name=\"devise\"  value=\"$devise\">\n";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"vientde\">";

  // Gestion de l'ajout de ps
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"ok\" value=\"$ok\">";
  $html .= "<INPUT TYPE=\"hidden\" NAME=\"ajout\" value=\"$ajout\"></FORM>\n";

  // Génération de la page
  echo $html;

} else if ($etape == 4) { /* Création ou modification du compte de parts sociales */
  global $db;
  $db = $dbHandler->openConnection();
  debug($montant_ps,_("montant ps niveau 4"));
  debug($nb_ps,_("nd ps niveau 4"));
  debug($numero,_("num cli niveau 4"));
  debug($valeur,_("valeur d'une ps"));
  /* Exercice en cours */
  $AG = getAgenceDatas($global_id_agence);
  $exo = $AG["exercice"];
  //Montant des parts sociales
  $solde = $montant_ps;

  if (!$ok) {
    /* Le client ne possède pas de compte de parts sociales alors le créer et y ajouter le nbre de ps saisi et le montant */
    $today = date("r");

    // Type de numérotation des comptes
    $AGC = getAgenceDatas($global_id_agence);
    $type_num_cpte = $AGC['type_numerotation_compte'];

    // Rang du compte
    $num_cpte = getRangDisponible($numero);

    /* Numéro complet du compte à créer */
    $sql = "SELECT makeNumCompletCpte($type_num_cpte, $numero, $num_cpte);";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Calcul du numéro complet du compte'),$result->getMessage());
    }
    $row = $result->fetchrow();
    $num_cplet_cpte = $row[0];

    /*Insertion du nbre de ps saisi et du montant */
    $sql = "INSERT INTO ad_cpt (id_titulaire,id_ag,date_ouvert,solde,etat_cpte,num_complet_cpte,num_cpte,id_prod,devise,intitule_compte,cpt_vers_int) VALUES ($numero,$global_id_agence,'$today',$solde,1,'$num_cplet_cpte',$num_cpte,2,'$devise','Compte de parts sociales',currval('ad_cpt_id_cpte_seq'));";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible ce créer le compte de PS'),$result->getMessage());
    }

    /* Mise à jour du nombre total de parts sociales dans ad_agc */
    $sql = "UPDATE ad_agc SET nbre_part_sociale  = nbre_part_sociale + $nb_ps;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible mettre à jour le nbre de PS'),$result->getMessage());
    }

    /* Mise à jour de la qualité du client et du nombre de parts sociales */
    $sql = "UPDATE ad_cli SET qualite = 2, nbre_parts = nbre_parts + $nb_ps where id_ag = $global_id_agence AND id_client = $numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible changer la qualite du client'),$result->getMessage());
    }

    /* Récupération de l'ID du compte qui vient d'être créé */
    $sql= "SELECT currval('ad_cpt_id_cpte_seq');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      signalErreur("rp_ps.php",_("Reprise des parts sociales : Récupération id compte interne")." ",$result->getMessage());
      $dbHandler->closeConnection(false);
    }
    $tmp = $result->fetchrow();
    $id_cpte_ps = $tmp[0];

    /* FIXME : Vérifier que c'est bien le compte. Càd id_titulaire=$numero et id_prod=2  */

    /* Passation des écritures comptables */

    /* Recupération de la valeur de ad_his */
    $sql = "SELECT nextval('ad_his_id_his_seq');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible de trouver le numéro histo correspondant'),$result->getMessage());
    }
    $tmp = $result->fetchrow();
    $his = $tmp[0];

    /* Ajout dans l'historique */
    $sql = "INSERT INTO ad_his (id_his,id_ag,type_fonction,id_client,login,date,infos) VALUES ($his,$global_id_agence,502,$numero,'administrateur','$today','Reprise des PS des clients');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible de mettre à jour l\'historique'),$result->getMessage());
    }

    /* Création de l'écriture comptable */
    $sql ="INSERT INTO ad_ecriture(id_his,id_ag,date_comptable,libel_ecriture,id_jou,id_exo,ref_ecriture) VALUES ($his,$global_id_agence,'$today',makeTraductionLangSyst('Reprise de PS d\'un client'),1,$exo,makeNumEcriture(1, $exo));";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible de passer la première écriture : 103'),$result->getMessage());
    }

    /* Passage du mouvement au débit */
    $sql = "INSERT INTO ad_mouvement (id_ecriture,id_ag,compte,cpte_interne_cli,sens,montant,devise,date_valeur) VALUES ((SELECT currval('ad_ecriture_seq')),$global_id_agence,'$cpte_subs_ps',NULL,'d','$solde','$devise','$today');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible de passer la première écriture : 101'),$result->getMessage());
    }

    /* Passage du mouvement au crédit */
    $sql = "INSERT INTO ad_mouvement (id_ecriture,id_ag,compte,cpte_interne_cli,sens,montant,devise,date_valeur) VALUES ((SELECT currval('ad_ecriture_seq')),$global_id_agence,'$cpte_ps', $id_cpte_ps,'c','$solde','$devise','$today');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible passer la seconde écriture : 57'),$result->getMessage());
    }

    /* Mise à jour du solde du compte au débit */
    $sql = "UPDATE ad_cpt_comptable SET solde = solde - $solde WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte_subs_ps'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible mettre à jour le solde du 101'),$result->getMessage());
    }

    /* Mise à jour su solde du compte au credit */
    $sql = "UPDATE ad_cpt_comptable SET solde = solde + $solde WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte_ps'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Impossible mettre à jour le solde du 57'),$result->getMessage());
    }
  } else {
    /* Le client possède un compte de parts sociales alors il faut seulement y ajouter les nouvelles ps et le montant saisi*/
    $today = date("r");
    /* Mise à jour du nombre total de PS dans ad_agc */
    $sql = "UPDATE ad_agc SET nbre_part_sociale = nbre_part_sociale + $nb_ps;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible mettre à jour le nbre de PS de agence'),$result->getMessage());
    }

    /* Mise à jour du nbre de ps du client */
    $sql = "UPDATE ad_cli SET nbre_parts = nbre_parts + $nb_ps where id_ag = $global_id_agence AND id_client = $numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible metter à jour le nbre de PS du client'),$result->getMessage());
    }

    /* Mise à jour du solde du compte de PS */
    $sql = "UPDATE ad_cpt SET solde = solde + $solde where id_prod = 2 and id_titulaire = $numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible mettre à jour le solde du compte'),$result->getMessage());
    }

    /*Mise à jour du solde restant du compte de PS du client */
    $sql = "SELECT solde from ad_cpt where id_ag = $global_id_agence and id_prod = 2 and id_titulaire = $numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout de PS'),_('Erreur à la recupération du solde de PS'),$result->getMessage());
    }
    $row = $result->fetchrow();
    $solde_part_soc = $row[0];
    $cli_datas = getClientDatas($numero);
    $nb_part = $cli_datas['nbre_parts'];
    $solde_part_soc_restant = ($nb_part*$valeur) - $solde_part_soc;
    /* Mise à jour du montant restant des ps du client */
    $sql = "UPDATE ad_cpt SET solde_part_soc_restant = solde_part_soc_restant + '$solde_part_soc_restant' where id_titulaire = $numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible metter à jour le solde restant de PS du client'),$result->getMessage());
    }

    /* Récupération de l'id de ad_his */
    $sql = "SELECT nextval('ad_his_id_his_seq');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible de trouver le numéro histo correspondant'),$result->getMessage());
    }
    $tmp = $result->fetchrow();
    $his = $tmp[0];

    /* Ajout dans l'historique */
    $sql = "INSERT INTO ad_his (id_his,id_ag,type_fonction,id_client,login,date,infos) VALUES ($his,$global_id_agence,502,$numero,'administrateur','$today','Reprise des PS des clients - Ajout PS');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur('Reprise des PS','Ajout PS - Impossible de mettre à jour l\'historique',$result->getMessage());
    }

    /* Création de l'écriture comptable */
    $sql = "INSERT INTO ad_ecriture (id_his,id_ag,date_comptable,libel_ecriture,id_jou,id_exo,ref_ecriture) VALUES ($his,$global_id_agence,'$today',makeTraductionLangSyst('Reprise de PS d\'un client'),1,$exo,makeNumEcriture(1, $exo));";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible de passer la première écriture : 103'),$result->getMessage());
    }

    /* Passage du mouvement au débit */
    $sql = "INSERT INTO ad_mouvement (id_ecriture,id_ag,compte,cpte_interne_cli,sens,montant,devise,date_valeur) VALUES ((SELECT currval('ad_ecriture_seq')),$global_id_agence,'$cpte_subs_ps',NULL,'d','$solde','$devise','$today');";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible de passer la première écriture : 103'),$result->getMessage());
    }

    /* Récupération du id du compte de PS */
    $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence and id_prod = 2 AND id_titulaire=$numero;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible de récupérer le numéro du  compte'),$result->getMessage());
    }
    $row = $result->fetchrow();
    $id_cpte_int = $row[0];

    /* Passage du mouvement au crédit */
    $sql = "INSERT INTO ad_mouvement (id_ecriture,id_ag,compte,cpte_interne_cli,sens,montant,devise,date_valeur) VALUES ((SELECT currval('ad_ecriture_seq')),$global_id_agence,'$cpte_ps',$id_cpte_int,'c','$solde','$devise','$today');";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible passer la seconde écriture : 57'),$result->getMessage());
    }

    /* Mise à jour du solde du compte au débit */
    $sql = "UPDATE ad_cpt_comptable SET solde = solde - '$solde' WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte_subs_ps';";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible mettre à jour le solde du 103'),$result->getMessage());
    }

    /* Mise à jour du solde du compte au crédit */
    $sql = "UPDATE ad_cpt_comptable SET solde = solde + '$solde' WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte_ps';";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(_('Reprise des PS'),_('Ajout PS - Impossible mettre à jour le solde du 57'),$result->getMessage());
    }
  }


  /* Déconnection à la base de bonnées */
  $dbHandler->closeConnection(true);

  /* Génération de l'entête de formulaire */
  $htmlmess = "\n<Table align=\"center\" valign =\"middle\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("CONFIRMATION")."</b></font></TD></TR></Table>\n";
  if (!$ok)
    $msg = "<b>".sprintf(_("Le compte de parts sociales du client %s a été créé  avec succès."), $numero )."</b>";
  else
    $msg = "<b>".sprintf(_("Le compte de parts sociales du client %s a été mis à jour avec succès."), $numero )."</b>";

  $htmlmess .= "<FORM NAME=\"ADForm\" ACTION=\"$PHP_SELF\" METHOD=\"POST\" onsubmit=\"if (! isSubmit){ isSubmit=true; return true;} else {return false;}\">\n";

  // On génère un tableau à 2 lignes, la première contenant le message et la seconde les boutons
  $htmlmess .=  "<BR><BR><TABLE ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">\n";
  $htmlmess .= "<TR BGCOLOR=$colb_tableau>\n";
  $htmlmess .= "<TD>".$msg."</TD></TR>\n";

  // Lignes vides
  $htmlmess .="<TR ALIGN=\"CENTER\"></TR></TABLE>\n";

  // Ligne des boutons
  $htmlmess .="<BR><TABLE align =\"center\"><TR>\n";

  // Pour chaque bouton dans la liste. Les custom button sont submit par défaut
  $htmlmess .= "<TD  COLSPAN=\"3\">\n";
  $htmlmess .= "<INPUT TYPE=SUBMIT NAME=\"OK\" VALUE=\"OK\" ONCLICK=\"document.ADForm.etape.value = '1';\">\n";
  $htmlmess .="</TD></TR>\n\n";

  // Variables post
  $htmlmess .="<TR><INPUT type=\"hidden\" name=\"etape\"></TR></TABLE>\n";
  $htmlmess .="<TR><INPUT type=\"hidden\" name=\"vientde\"></TR></FROM>\n";

  // Génération de la page
  echo $htmlmess;

} else if ($etape == 5) { /* Ecran de sortie du module */
  $msg = "<b>"._("Voulez-vous quitter ce module ?")."</b>";
  $htmlmess = "\n<Table align=\"center\" valign =\"middle\"><TR><TD><font face=\"helvetica,verdana\"><b>"._("CONFIRMATION")."</b></font></TD></TR></Table>\n";
  $htmlmess .= "<FORM NAME=\"ADForm\" ACTION=\"$PHP_SELF\" METHOD=\"POST\" onsubmit=\"if (! isSubmit){ isSubmit=true; return true;} else {return false;}\">\n";
  $htmlmess .=  "<BR><BR><TABLE ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">\n";
  $htmlmess .= "<TR BGCOLOR=$colb_tableau>\n";
  $htmlmess .= "<TD>".$msg."</TD></TR>\n";
  $htmlmess .="<TR ALIGN=\"CENTER\"></TR></TABLE>\n";

  // Ligne des boutons
  $htmlmess .="<BR><TABLE align =\"center\"><TR>\n";

  // Pour chaque bouton dans la liste. Les custom button sont submit par défaut
  $htmlmess .= "<TD  COLSPAN=\"3\">\n";
  $htmlmess .= "<INPUT TYPE=SUBMIT NAME=\"OK\" VALUE=\""._("OUI")."\" ONCLICK=\"window.close();\">\n";
  $htmlmess .="</TD>";
  $htmlmess .= "<TD clospan = \"3\"><INPUT TYPE=SUBMIT NAME=\"NOK\" VALUE=\"NON\" ONCLICK=\"document.ADForm.etape.value = '1';\"></TD></TR>\n\n";

  // Variables post
  $htmlmess .="<TR><INPUT type=\"hidden\" name=\"etape\"></TR></TABLE></FROM>\n";

  // Génération de la page
  echo $htmlmess;
}

?>