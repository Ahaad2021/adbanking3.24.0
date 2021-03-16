<?php
/*
 * Created on 23 Avril 09
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once("lib/html/HTML_GEN2.php");
require_once("lib/html/FILL_HTML_GEN2.php");
require_once("lib/dbProcedures/handleDB.php");
require_once("lib/dbProcedures/guichet.php");
require_once("lib/dbProcedures/agence.php");
require_once("lib/misc/divers.php");
require_once("lib/misc/VariablesGlobales.php");
require_once ("lib/misc/VariablesSession.php");

/**
 * Récupération de l'épargne d'une IMF.
 * @package Recupdata
 */

//Variable globale - connection à la base de données
global $dbHandler,$global_id_agence, $SESSION_VARS;

$global_id_agence=getNumAgence();
?>

<html>
<head>
<title>
<?php echo $ProjectName; ?>
</title>
<script type="text/javascript" src="<?php echo "$http_prefix/lib/java/scp.php?m_agc=$global_id_agence&http_prefix=$http_prefix";?>"></script>
</head>
<body bgcolor=white>
	<table width="100%" cellpadding=5 cellspacing=0 border=0>
		<tr>
			<td><a target=_blank href="http://www.aquadev.org"><img border=0 title="<?= _("ADbanking Logo") ?>" alt="<?= _("ADbanking Logo") ?>" width=400 height=40 src="../../images/ADbanking_logo.jpg"></a></td>
			<td valign=bottom align=center><font face="helvetica,verdana" size="+2"><?= _("Module de reprise des comptes des clients") ?></font></td>
		</tr>
	</table>

<hr>
<br><br><P>

<?php
// Etape 0 : Chargement du fichier csv
if (!isset($etape) || $etape == 0) {
// Fichier de configuration traduction.conf
$file_trad = fopen("../traduction.conf", 'r');
if ($file_trad == false)
{
  echo "<BR/><BR/><P align=center><FONT color=red>"._("Le fichier <CODE><B>traduction.conf</B></CODE> n'a pas été trouvé à l'endroit attendu")."</FONT></P>";
  die();
}

while ($ligne = fgets($file_trad, 1024))
{
		//recupération des comptes de substitut dans traduction.conf
  if (ereg("^prod_sub_", $ligne))
  {
    $ligne = ereg_replace("\t", "|", $ligne); // Remplacer tabulation par |
    $ligne = ereg_replace("\n", "|", $ligne); // Remplacer fin de ligne par |
    $tab = explode("|", $ligne); // Récupérer la ligne sous forme de tableau
    $tab[0] = ereg_replace("^prod_sub_", "", $tab[0]);
    $prod_sub[$tab[0]] = trim($tab[1]);
  }

  //recupération des produits d'épargne et leur code dans traduction.conf
  if (ereg("^prod_ep_", $ligne))
  {
    $ligne = ereg_replace("\t", "|", $ligne); // Remplacer tabulation par |
    $ligne = ereg_replace("\n", "|", $ligne); // Remplacer fin de ligne par |
    $tab = explode("|", $ligne); // Récupérer la ligne sous forme de tableau
    //$code_prod_ep = ereg_replace("^prod_ep_", "", $tab[0]);
    $code_prod_ep = $tab[0];
    $id_prod = trim($tab[1]);
    $prod_ep_id[$code_prod_ep] = $id_prod;
  }
}
fclose($file_trad);

// Récupération des produits d'épargne
$PROD = array();
$listProd = getListProdEpargne();
foreach($listProd as $key=>$value)
{
    $PROD[$value['id']] = $value;
    $PROD[$value['id']]['subs'] = $prod_sub[$value['id']];
    $PROD[$value['id']]['solde'] = 0;
    $PROD[$value['id']]['cpte_int'] = NULL;
}

// Fichier de données recup_epargne.csv
$file_epargne = fopen("recup_epargne.csv", 'r');

if ($file_epargne == false)
{
  echo "<BR/><BR/><P align=center><FONT color=red>"._("Le fichier <CODE><B>recup_epargne.csv</B></CODE> n'a pas été trouvé à l'endroit attendu : recup_data/recupe_epargne")."</FONT></P>";
  die();
}
$comptes = array();
$i = 0;
$msg = "";
$ligne = fgetcsv($file_epargne, 1024, ';');
while($ligne = fgetcsv($file_epargne, 1024, ';'))
{
	$i++;
  $comptes[$i]['num_client'] = $ligne[0];
  $code_prod_ep = $ligne[1];
  if($prod_ep_id[$code_prod_ep])
  $comptes[$i]['produit'] = $prod_ep_id[$code_prod_ep];
  else{
		$msg .= sprintf(_("Le code du produit de la ligne n°%s n est pas correct !"),$i+1 )."<br>";
  }
  $solde = recupMontant($ligne[2]);
  $comptes[$i]['solde_courant'] = $solde;
  $date_ouvert = $ligne[3];
  $comptes[$i]['date_ouverture'] = $date_ouvert;
  $solde_calcul_interets = recupMontant($ligne[4]);
  $comptes[$i]['solde_interet'] = $solde_calcul_interets;
  $date_calcul_interet = $ligne[5];
  $comptes[$i]['date_interet'] = $date_calcul_interet;
  $comptes[$i]['certificat'] = $ligne[6];
  $comptes[$i]['ancien_numero'] = $ligne[7];
  $comptes[$i]['montant'] = $ligne[8];
  $comptes[$i]['periodicite'] = $ligne[9];
  $comptes[$i]['periode'] = $ligne[10];
  $comptes[$i]['date_fin'] = $ligne[11];
}

if($msg != ""){
	 // Erreur rencontrée sur au moins une ligne
      // Afficher infos saisies
      $myForm = new HTML_GEN2(_("ERREUR"));

      $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
      $myTable->set_property("align", "center");
      $myTable->set_property("border", $tableau_border);
      $myTable->set_property("bgcolor", '#e0e0ff');

      // Entête tableau
      $myTable->add_cell(new TABLE_cell(_($msg), 1, 1));

      // Boutons
      $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '0';"));

      // Variables post
      $myForm->addHiddenType("etape");
      $myForm->addHiddenType("cpt");

      // Génération HTML
      $myForm->buildHTML();
      echo $myForm->getHTML();
}
else{
		  $SESSION_VARS['saisie'] = array(); // tableau des données saisies
    	$SESSION_VARS['saisie'] = $comptes;
    	$SESSION_VARS['liste_produits'] = $PROD;// liste des produits d'épargne type financier
      // Afficher infos saisies
      $myForm = new HTML_GEN2(_("Le fichier csv est bien chargé"));

      $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
      $myTable->set_property("align", "center");
      $myTable->set_property("border", $tableau_border);
      $myTable->set_property("bgcolor", '#e0e0ff');

      // Entête tableau
      $myTable->add_cell(new TABLE_cell(_("Ciquez sur Valider pour poursuivre l opération"), 1, 1));

    	$myForm->addHiddenType("etape");
		  $myForm->addHiddenType("vientde");
		  //$myForm->addHiddenType("ndeb");

		  $myForm->addFormButton(1, 1, "Valider", _("Valider"), TYPB_SUBMIT);
		  $myForm->addFormButton(1, 2, "Annuler", _("Annuler"), TYPB_SUBMIT);
		  $myForm->setFormButtonProperties("Valider", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));
		  $myForm->setFormButtonProperties("Annuler", BUTP_JS_EVENT, array("onclick"=>"etape.value= '0';vientde.value = '0';"));

		  // Génération HTML
		  $myForm->buildHTML();
		  echo $myForm->getHTML();
}

fclose($file_epargne);
}

// Etape 1 : Visualisation des informations saisies dans le fichier csv
elseif ($etape == 1) {

  $SESSION_VARS['ErrLine'] = array(); // compteur d'erreur

  // Récupération du type de numérotation des comptes de l'agence
  $id_agc = getNumAgence();
  $AGD = getAgenceDatas($id_agc);
  $type_num_compte = $AGD['type_numerotation_compte'];

  // Création du formulaire
  $myForm = new HTML_GEN2();

  // Création tableau de 10 colonnes
  $myTable =& $myForm->addHTMLTable("recup_epargne", 13, TABLE_STYLE_ALTERN);
  $myTable->set_property("align", "center");
  $myTable->set_property("border", $tableau_border);
  $myTable->set_property("bgcolor", '#e0e0ff');


  // Entête tableau
  $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("N° client"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Produit"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Solde compte"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("date ouverture"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Solde calcul intérêt"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Date calcul intérêt"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("N° certificat"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Ancien n° compte"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Mise / dépôt / retenue"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Périodicité "), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Nombre de periodes"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Date de fin"), 1, 1));


  // Contenu du tableu :
  $nbr_cpt = count($SESSION_VARS['saisie']); // Premier lot de 40 lignes
	$nb_occ = 50; // nombre par page
	$num_pag_cour = 0; // n° de la fiche courante

	$Ndeb = @$_GET["num"]; // 1ère fiche transmise par l'URL
	// tant qu'il y a des fiches
	$cpt_i = $Ndeb+1;
	while (($cpt_i <= $nbr_cpt) && ($num_pag_cour < $nb_occ+$Ndeb))
	{

		if($num_pag_cour >= $Ndeb)
		   {
    // Numéro de ligne
    $myTable->add_cell(new TABLE_cell($cpt_i, 1, 1));
    // Numéro client
    $myTable->add_cell(new TABLE_cell_input(TYPC_INT, "num_client$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['num_client'], ""));
    // Produit
    $produit_associe = "<SELECT NAME=\"produit$cpt_i\">";
    $produit_associe .= "<option value=\"0\">[Aucun]</option>";
    if (is_array($SESSION_VARS['liste_produits'])) {
      foreach($SESSION_VARS['liste_produits'] as $key=>$value) {
        if ($value['id'] == $SESSION_VARS['saisie'][$cpt_i]['produit'])
          $produit_associe .= "<option value=".$value['id']." selected>".$value['libel']."</option>";
        else
          $produit_associe .= "<option value=".$value['id'].">".$value['libel']."</option>";
      }
    }
    $produit_associe .= "</SELECT>\n";
    $myTable->add_cell(new TABLE_cell($produit_associe, 1, 1));
    // Solde courant
    $myTable->add_cell(new TABLE_cell_input(TYPC_MNT, "solde_courant$cpt_i", afficheMontant($SESSION_VARS['saisie'][$cpt_i]['solde_courant']),
                                            "OnChange=value=formateMontant(value);"));
    // Date ouverture
    $myTable->add_cell(new TABLE_cell_input(TYPC_DTE, "date_ouverture$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['date_ouverture'], ""));
    // Solde calcul des intérêts
    $myTable->add_cell(new TABLE_cell_input(TYPC_MNT, "solde_interet$cpt_i", AfficheMontant($SESSION_VARS['saisie'][$cpt_i]['solde_interet']),
                                            "OnChange=value=formateMontant(value);"));
    // Date calcul des intérêt
    $myTable->add_cell(new TABLE_cell_input(TYPC_DTE, "date_interet$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['date_interet'], ""));
    // Numéro certificat
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "certificat$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['certificat'], ""));
    // Ancien numéro du compte
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "ancien_numero$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['ancien_numero'], ""));
     // Montant
     $myTable->add_cell(new TABLE_cell_input(TYPC_MNT, "montant$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['montant'], ""));
     // Periodicite
     $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "periodicite$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['periodicite'], ""));
     // nombre de periode
     $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "nb_periode$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['nb_periode'], ""));
     // Date fin traitement
     $myTable->add_cell(new TABLE_cell_input(TYPC_DTE, "date_fin$cpt_i", $SESSION_VARS['saisie'][$cpt_i]['date_fin'], ""));


  	$cpt_i++;
		}
		$num_pag_cour++;

	}

//---------------------Navigation dans les comptes par page---------------------------

	$html = "<br>	  <table cellpadding=3><tr>";
		 // Navigation des fiches avant
	if($Ndeb > 0) {
		$html .= "<td valign=top>";
		$html .= "<A href='?num=".($Ndeb-$nb_occ)."'><b> <<< ".sprintf(_("Précédent"))."</b></A>";
		$html .= "</td>";
	}
	$html .= "<td>";
	 // N° des pages
		$Npag = ceil($nbr_cpt/$nb_occ);
		for($i = 1;$i<=$Npag;$i++) {
		   // Page courante ?
		   if($Ndeb == ($i-1)*$nb_occ) {
		      $html .= "<b>".sprintf(_("Page"))." ".$i."</b>";
		    } else {
		      $html .="<A href=\"?num=".($i-1)*$nb_occ."\"";
		      $html .=">&nbsp;<b>".$i."</b>&nbsp;</A>";
		    }
		}
		  $html .= "</td>";
		 // Des fiches après ?
		if($cpt_i <= $nbr_cpt) {
		   $html .= "<td valign=top>";
		      $html .="<A href=\"?num=".$num_pag_cour."\"><b>".sprintf(_("Suivant"))." >>></b></A>";
		   $html .= "</td>";
		 }
		$html .= "</tr></table>";
	$html .="<hr>";
	$myForm->addHTMLExtraCode("navig_cpt",$html);
  //----------------------------------------------------------------------------------
  $myForm->addHiddenType("etape");
  $myForm->addHiddenType("vientde");
  $myForm->addHiddenType("ndeb");

  $myForm->addFormButton(1, 1, "Valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "Annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("Valider", BUTP_JS_EVENT, array("onclick"=>"etape.value= '2';ndeb.value = '".$Ndeb."';"));
  $myForm->setFormButtonProperties("Annuler", BUTP_JS_EVENT, array("onclick"=>"etape.value= '0';vientde.value = '1';"));

  // Génération HTML
  $myForm->buildHTML();
  echo $myForm->getHTML();

}

// Etape 2 : Contrôle des informations saisies
elseif ($etape == 2) {
  // Ne pas réinitialiser au cas ou on abandonne un quitter (annuler annuler)
  if ($vientde != 4) {
    // Récupération des informations
    $i = 1;
    $num_ligne = 0;
//    $ndeb = @$_GET["num"];
    //$i = $ndeb+1;

    $msg = "";
    $l = 0;
    // Tant que les infos obligatoires pour une ligne sont sasies et qu'on ait pas atteint la fin
    foreach ($SESSION_VARS['saisie'] as $i=>$compte) {

      $Err = 0; // compteur d'erreur
      $msg_i = ""; //message
			$num_ligne = $i+1; // numero de ligne dans le fichier csv
      // Recupération du num client
       // Dans le cas ou le numero du client n'est pas saisi
      if (strlen($compte['num_client']) == 0) {
        $msg_i .= sprintf(_("le numero du client de la ligne n°%s n est pas saisie, il est obligatoire  !") , $num_ligne." ".$compte['num_client'] )."<br>";
        $Err+=1;
      }
      else{
	      // Vérifie si le client existe
	      if (! client_exist($compte['num_client'])) {
	        $msg_i .= sprintf(_("Le client de la ligne n°%s n'existe pas !"),$num_ligne." ".$compte['num_client'])."<br>";
	        $Err+=1;
	      }
      }

      // Recupération du produit
        // Dans le cas ou le produit n'est pas saisi
      if (strlen($compte['produit']) == 0) {
        $msg_i .= sprintf(_("le produit de la ligne n°%s n est pas saisie, il est obligatoire  !") , $num_ligne." ".$compte['produit'] )."<br>";
        $Err+=1;
      }
      $PROD = getProdEpargne($compte['produit']);
      $terme = $PROD['terme'];
      $nb_occ = $PROD['nbre_occurrences'];
      $libele = $PROD['libel'];

      // Récupération du solde courant
       // Dans le cas ou le produit n'est pas saisi
      if (strlen($compte['solde_courant']) == 0) {
        $msg_i .= sprintf(_("le solde courant de la ligne n°%s n est pas saisie, il est obligatoire  !") , $num_ligne." ".$compte['solde_courant'] )."<br>";
        $Err+=1;
      }

      // Récupération date ouverture
        // Dans le cas ou la date n'est pas saisi
      if (strlen($compte['date_ouverture']) == 0) {
        $msg .= sprintf(_("La date de la ligne n°%s n est pas saisie, elle est obligatoire  !") , $num_ligne." ".$compte['date_ouverture'] )."<br>";
        $Err+=1;
      }
      $dummy = splitEuropeanDate($compte['date_ouverture']);
      // Dans le cas ou la date d'ouverture est postérieure à la date du jour
      if (gmmktime(0,0,0,$dummy[1],$dummy[0],$dummy[2]) >= gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
        $msg_i .= sprintf(_("La date de la ligne n°%s se situe après la date du jour !") , $num_ligne." ".$compte['date_ouverture'] )."<br>";
        $Err+=1;
      }
      // Dans le cas d'uin DAT, voir si la date de fin est postérieure à celle d'aujourd'hui
      if ($terme != 0) {
        if (gmmktime(0,0,0,$dummy[1]+$terme,$dummy[0],$dummy[2]) < gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
          $msg_i .= sprintf(_("A la date d'ouverture %s de la ligne n°%s, le compte serait déjà fermé !"),$compte['date_ouverture'],$num_ligne)."<br>";
          $Err+=1;
        }
      }

      // Récupération du solde de calcul interêts
       // Récupération de la date de mise à jour du solde calcul interêts
       $dummy = splitEuropeanDate($compte['date_interet']);
      if ($compte['date_interet'] != "") {
        $have_date=true;
        // Dans le cas ou la date est postérieure à la date
        if (gmmktime(0,0,0,$dummy[1],$dummy[0],$dummy[2]) >= gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
          $msg_i .= sprintf(_("La date calcul intérêts de la ligne n°%s se situe après la date du jour !") , $num_ligne." ".$compte['date_interet'] ) ."<br>";
          $Err+=1;
        }
        // Dans le cas ou la date de fin est antérieure à celle d'aujourd'hui
        if ($terme != 0) {
          if (gmmktime(0,0,0,$dummy[1]+$terme,$dummy[0],$dummy[2]) < gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
            $msg_i .= sprintf(_("A la date calcul intérêts %s de la ligne n°%s, le compte serait déjà fermé !"),$compte['date_interet'] , $num_ligne)."<br />";
            $Err+=1;
          }
        }
      }


      // Récupération numéro certificat

      // Recup ancien numéro de compte
      // Récupération Ancien numéro de compte
      if (strlen($compte['ancien_numero']) != 0) {
        if (isNumComplet($compte['ancien_numero']) == true) {
          if ($type_num_compte == 2)
            $num_cli = ereg_replace("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})","\\2",$compte['ancien_numero']);
          else
            $num_cli = ereg_replace("([[:digit:]]{3})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", "\\2",$compte['ancien_numero']);
          if ($num_cli != $compte['num_client']) {
            $msg_i .= _("Ligne")." ".$num_ligne." : ".sprintf(_("l'ancien numéro de compte %s ne correspond pas au client %s"),$compte['ancien_numero'],$compte['num_client'])."<br>";
            $Err += 1;
          }


          // Vérifie si l'ancien numéro n'existe pas déjà
          $sql = "select * from ad_cpt where id_ag = $global_id_agence and num_complet_cpte = '".$compte['ancien_numero']."';";
          $result = executeDirectQuery($sql);
          if ($result->errCode == NO_ERR)
            if ($result->param[0] > 0) {
              $msg_i .= _("Ligne")." ".$num_ligne." : ".sprintf(_("l'ancien numéro de compte %s existe déjà dans la base de donnée"),$compte['ancien_numero'])."<br />";
              $Err += 1;
            }
        }
        // Ancien numéro de compte non valide
        else {
          $msg_i .=_("Ligne")." ".$num_ligne.": ".sprintf(_("l'ancien numéro de compte %s n'est pas valide"),$compte['ancien_numero'])."<br/>";
          $Err += 1;
        }
      }

      // Vérifie qu'il y a pas d'erreur
      if ($msg_i != "") {
      	$msg .= $msg_i;
        $msg .= sprintf(_("Certains champs de la ligne n°%s ne sont pas correctement renseignés !"),$num_ligne )."<br>";
        $Err +=1;
      }

      //On récuppère les lignes ayant des erreurs ?
      if ($Err > 0) {
        $SESSION_VARS['ErrLine'][$l] = $num_ligne;
        $l++;
      }
      $i++;

    }  // Fin Tant que

  } // Fin si vient de

  // Des infos ont été saisies : nbre de lignes informé > 0
  $nbr_cpt = count($SESSION_VARS['saisie']); // Premier lot de 40 lignes
  if ($nbr_cpt != 0) {
    // Pas d'erreur constatée
    if ($msg == "") {
      // Afficher infos saisies
      $myForm = new HTML_GEN2(_("Choix des comptes internes"));

      $myTable =&  $myForm->addHTMLTable("recup_epargne",13, TABLE_STYLE_ALTERN);
      $myTable->set_property("align", "center");
      $myTable->set_property("border", $tableau_border);
      $myTable->set_property("bgcolor", '#e0e0ff');

      // Entête tableau
      $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("N° client"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Produit"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Solde compte"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("date ouverture"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Solde calcul intérêt"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Date calcul intérêt"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("N° certificat"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Ancien n° compte"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Mise / dépôt / retenue"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Périodicité "), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nombre de periodes"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Date de fin"), 1, 1));

		$nb_occ = 50; // nombre de comptes par page
		$num_pag_cour = 0; // n° de la fiche courante

		$Ndeb = @$_GET["num"]; // 1ère fiche transmise par l'URL
		$cpt_i = $Ndeb+1;
		// tant qu'il y a des fiches
		while (($cpt_i <= $nbr_cpt) && ($num_pag_cour < $nb_occ+$Ndeb))
		{
      if (isset($_POST["solde_interet".$cpt_i]) && $_POST["solde_interet".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['solde_interet'] = recupMontant($_POST["solde_interet".$cpt_i]);
      }
      if (isset($_POST["date_interet".$cpt_i]) && $_POST["date_interet".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['date_interet'] = $_POST["date_interet".$cpt_i];
      }
      if (isset($_POST["certificat".$cpt_i]) && $_POST["certificat".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['certificat'] = $_POST["certificat".$cpt_i];
      }
      if (isset($_POST["ancien_numero".$cpt_i]) && $_POST["ancien_numero".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['ancien_numero'] = $_POST["ancien_numero".$cpt_i];
      }
      if (isset($_POST["montant".$cpt_i]) && $_POST["montant".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['montant'] = $_POST["montant".$cpt_i];
      }
      if (isset($_POST["periodicite".$cpt_i]) && $_POST["periodicite".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['periodicite'] = $_POST["periodicite".$cpt_i];
      }
      if (isset($_POST["nb_periode".$cpt_i]) && $_POST["nb_periode".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['nb_periode'] = $_POST["nb_periode".$cpt_i];
      }
      if (isset($_POST["date_fin".$cpt_i]) && $_POST["date_fin".$cpt_i] != null){
        $SESSION_VARS['saisie'][$cpt_i]['date_fin'] = $_POST["date_fin".$cpt_i];
      }

			if($num_pag_cour >= $Ndeb)
			 {

        if ($cpt_i%2) $color = $colb_tableau;
        else $color = $colb_tableau_altern;
        // Numéro de ligne
        $myTable->add_cell(new TABLE_cell($cpt_i, 1, 1));
        // Numéro client
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['num_client'],1,1));
        // Produit
        $PROD = getProdEpargne($SESSION_VARS['saisie'][$cpt_i]['produit']);
        $myTable->add_cell(new TABLE_cell($PROD['libel'], 1, 1));
        // Solde courant
        $myTable->add_cell(new TABLE_cell(afficheMontant($SESSION_VARS['saisie'][$cpt_i]['solde_courant'],false),1,1));
        // Date ouverture
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['date_ouverture'],1,1));
        // Solde calcul des intérêts
        $myTable->add_cell(new TABLE_cell(afficheMontant($SESSION_VARS['saisie'][$cpt_i]['solde_interet'],false),1,1));
        // Date calcul des intérêt
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['date_interet'],1,1));
        // Numéro certificat
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['certificat'],1,1));
        // Ancien numéro du compte
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['ancien_numero'],1,1));
         // Mise / depot/retenue
         $myTable->add_cell(new TABLE_cell(afficheMontant($SESSION_VARS['saisie'][$cpt_i]['montant'],false),1,1));
         // periodicite
         $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['periodicite'],1,1));
         // nombre de periodes
         $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['nb_periode'],1,1));
         // date fin
         $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$cpt_i]['date_fin'],1,1));


        $cpt_i++;
      }
      $num_pag_cour++;
		}
//---------------------Navigation dans les comptes par page---------------------------

			$html = "<br>	  <table cellpadding=3><tr>";
				 // Navigation des fiches avant
			if($Ndeb > 0) {
				$html .= "<td valign=top>";
				$html .= "<A href='?etape=2&num=".($Ndeb-$nb_occ)."'><b> <<< ".sprintf(_("Précédent"))."</b></A>";
				$html .= "</td>";
			}
			$html .= "<td>";
			 // N° des pages
				$Npag = ceil($nbr_cpt/$nb_occ);
				for($i = 1;$i<=$Npag;$i++) {
				   // Page courante ?
				   if($Ndeb == ($i-1)*$nb_occ) {
				      $html .= "<b>".sprintf(_("Page"))." ".$i."</b>";
				    } else {
				      $html .="<A href=\"?etape=2&num=".($i-1)*$nb_occ."\"";
				      $html .=">&nbsp;<b>".$i."</b>&nbsp;</A>";
				    }
				}
				  $html .= "</td>";
				 // Des fiches après ?
				if($cpt_i <= $nbr_cpt) {
				   $html .= "<td valign=top>";
				      $html .="<A href=\"?etape=2&num=".$num_pag_cour."\"><b>".sprintf(_("Suivant"))." >>></b></A>";
				   $html .= "</td>";
				 }
				$html .= "</tr></table>";
			$html .="<hr>";
			$myForm->addHTMLExtraCode("navig_cpt",$html);
  //----------------------------------------------------------------------------------
      // Boutons
      $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
      $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick"=>"etape.value= '4';vientde.value = '2';"));
      $myForm->setFormButtonProperties("retour", BUTP_JS_EVENT,  array("onclick"=>"etape.value= '1';"));
      $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT,
                                       array("onclick"=>"etape.value= '1';vientde.value='2';"));

      // Variables post
      $myForm->addHiddenType("etape");
      $myForm->addHiddenType("vientde");
      $myForm->addHiddenType("cpt");
      $myForm->addHiddenType("estpartide");

      $myForm->buildHTML();
      echo  $myForm->getHTML();
    } else { // Erreur rencontrée sur au moins une ligne
      // Afficher infos saisies
      $myForm = new HTML_GEN2(_("ERREUR"));

      $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
      $myTable->set_property("align", "center");
      $myTable->set_property("border", $tableau_border);
      $myTable->set_property("bgcolor", '#e0e0ff');

      // Entête tableau
      $myTable->add_cell(new TABLE_cell(_($msg), 1, 1));

      // Boutons
      $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));

      // Variables post
      $myForm->addHiddenType("etape");
      $myForm->addHiddenType("cpt");

      // Génération HTML
      $myForm->buildHTML();
      echo $myForm->getHTML();
    }
  } else { //Aucune info n'a été saisie
    // Afficher infos saisies
    $myForm = new HTML_GEN2(_("ERREUR"));

    $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
    $myTable->set_property("align", "center");
    $myTable->set_property("border", $tableau_border);
    $myTable->set_property("bgcolor", '#e0e0ff');

    // Entête tableau
    $myTable->add_cell(new TABLE_cell(_("Aucune entrée n'a été renseignée !"), 1, 1));

    // Boutons
    $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));

    // Variables post
    $myForm->addHiddenType("etape");

    // Génération HTML
    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
}

// Etape 4 : Quitter
elseif ($etape == 4) {
  // Saisie du premier lot terminé
  if ($vientde == 2) {
	// Liste des produits d'épargne
  	$produits = array();
  	$produits = $SESSION_VARS['liste_produits'];
    // CumulDAV et CumulDAT
    $CumulDAV = 0;
    $CumulDAT = 0;
    $mnt_total =0;
    // Recup comptes comptables
//    $cpte_a_vue = $ {'cptecptable1'};
//    $cpte_a_terme = $ {'cptecptable2'};
//    $cpte_banq = $ {'cptecptable3'};
    // Les comptes ont été renseignés
    //if (($cpte_a_vue != "[Aucun]") && ($cpte_a_terme != "[Aucun]") && ($cpte_banq != "[Aucun]") ) {
      // Insertion des données dans la base
      $mvts_compta = array(); // Tableau des mouvements comptables
      global $db;
      $db = $dbHandler->openConnection(); // Connexion à la base de données
			$nbr_cpt = count($SESSION_VARS['saisie']); // Premier lot de 40 lignes
			$comptable = array(); // Passage des écritures comptables
		  $compteur_mv = 0;
		  $compteur_ecr = 1;
		  $AG = getAgenceDatas($global_id_agence);
      $exo = $AG['exercice'];
      for ($i=1;$i <= $nbr_cpt;++$i) {
        if ($SESSION_VARS['saisie'][$i]['ancien_numero'] == '') {
          // Recherche du compte max du client
          $sql = "SELECT max(num_cpte) from ad_cpt where id_ag = $global_id_agence and id_titulaire = ".$SESSION_VARS['saisie'][$i]['num_client'].";";
          $result = executeDirectQuery($sql, TRUE);
          if ($result->errCode == NO_ERR) {
            $numcpt = $result->param[0] + 1;
            // Récupérer le num complet du compte
            $numcptcpt = makeNumCpte($SESSION_VARS['saisie'][$i]['num_client'], $numcpt);
          } else {
            // Message à afficher
            $myForm = new HTML_GEN2(_("Erreur : ".$error[$result->errCode]));

            // Boutons
            $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
            $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));

            // Variables post
            $myForm->addHiddenType("etape");

            // Génération HTML
            $myForm->buildHTML();
            echo $myForm->getHTML();
            exit();
          }
        } else {
          if ($type_num_compte == 2) {
            $numcpt = ereg_replace("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})", "\\3",$SESSION_VARS['saisie'][$i]['ancien_numero']);
          } else {
            $numcpt = ereg_replace("([[:digit:]]{3})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", "\\3", $SESSION_VARS['saisie'][$i]['ancien_numero']);
          }
          $numcptcpt = $SESSION_VARS['saisie'][$i]['ancien_numero'];
        }

        // Récupérer l'id_prod
        $idtype = $SESSION_VARS['saisie'][$i]['produit'];

        // Préparation de la création du compte
        $DATA = array("id_titulaire"=>$SESSION_VARS['saisie'][$i]['num_client'],"intitule_compte"=>$produits[$idtype]['libel'],
                      "utilis_crea"=>1,"date_ouvert"=>$SESSION_VARS['saisie'][$i]['date_ouverture'],"etat_cpte"=>1,
                      "num_cpte"=>$numcpt,"solde"=>0,"num_complet_cpte"=>$numcptcpt,
                      "type_cpt_vers_int"=>1,"id_prod"=>$idtype,"tx_interet_cpte"=>$produits[$idtype]['tx_interet'],
                      "freq_calcul_int_cpte"=>$produits[$idtype]['freq_calcul_int'],"devise"=>$produits[$idtype]['devise'],
                      "mode_calcul_int_cpte"=>$produits[$idtype]['mode_calcul_int'],
                      "mode_paiement_cpte"=>$produits[$idtype]['mode_paiement']);

        // Contrôle sur les données à saisir non obligatoires
        if ($SESSION_VARS['saisie'][$i]['date_interet'] == "")
          $DATA["date_calcul_interets"] = $SESSION_VARS['saisie'][$i]['date_ouverture'];
        else
          $DATA["date_calcul_interets"] = $SESSION_VARS['saisie'][$i]['date_interet'];

        if ($SESSION_VARS['saisie'][$i]['solde_interet'] == "")
          $DATA["solde_calcul_interets"] = $SESSION_VARS['saisie'][$i]['solde_courant'];
        else
          $DATA["solde_calcul_interets"] = $SESSION_VARS['saisie'][$i]['solde_interet'];

        // Données propres aux compte à terme
        if ($produits[$idtype]['terme'] != 0) {
          // Récupérer la date fin
          $dummy = splitEuropeanDate($SESSION_VARS['saisie'][$i]['date_ouverture']);
          $datefin = date("d/m/Y", mktime(0,0,0,$dummy[1] + $produits[$idtype]['terme'], $dummy[0], $dummy[2]));
          $DATA = array_merge($DATA,array("dat_prolongation"=>"f","dat_nb_prolong"=>0,"dat_date_fin"=>$datefin,
                                          "dat_decision_client"=>"f","terme_cpte"=>$produits[$idtype]['terme']));

          if ($SESSION_VARS['saisie'][$i]['certificat'] != "")
            $DATA["dat_num_certif"] = $SESSION_VARS['saisie'][$i]['certificat'];
        }

        // Création du compte
        $id_cpte = creationCompte($DATA);

        $infos_client =	getClientDatas($SESSION_VARS['saisie'][$i]['num_client']);
        $statut_juridique = $infos_client['statut_juridique'];

        // Insertion du mandat si titulaire PP
        if ($statut_juridique == 1) {
          // Récupération de la personne extérieure du mandataire
          $WHERE['id_client'] = $SESSION_VARS['saisie'][$i]['num_client'];
          $PERS_EXT = getPersonneExt($WHERE);
          $id_pers_ext = $PERS_EXT[0]['id_pers_ext'];

          // Insertion du mandat
          $DATA_MAND = array("id_cpte"=>$id_cpte,"id_pers_ext"=>$id_pers_ext,"type_pouv_sign"=>1,"valide"=>'t');
          $result = ajouterMandat($DATA_MAND);
        }



        //enregistrement des ordres de paiements si existe
        if ($SESSION_VARS['saisie'][$i]['montant'] > 0){
          $id_cpte_from = getCompteData($SESSION_VARS['saisie'][$i]['num_client'],1);
          $date_prem_exe = getDateFinMois(date("Y-m-d"));
          $mnt_total_prevu = $SESSION_VARS['saisie'][$i]['montant'] * $SESSION_VARS['saisie'][$i]['nb_periode'];

          $DATA_ORD = array("cpt_from"=>$id_cpte_from["id_cpte"], "cpt_to"=>$id_cpte, "type_transfert"=> 2,"date_prem_exe"=>$date_prem_exe,"date_fin"=>$SESSION_VARS['saisie'][$i]['date_fin'],"montant"=>recupMontant($SESSION_VARS['saisie'][$i]['montant']),"actif"=>'t',"periodicite"=>$SESSION_VARS['saisie'][$i]['periodicite'],"nb_periode"=>$SESSION_VARS['saisie'][$i]['nb_periode'],"mnt_total_prevu"=>$mnt_total_prevu,"id_ag"=>1);

          $result = ajouterOrdrepermanentModule($DATA_ORD, $SESSION_VARS['saisie'][$i]['num_client'],1);
        }

        // Si un montant est saisi, créer les deux mouvements comptables pour chaque compte créé
        $mnt = recupMontant($SESSION_VARS['saisie'][$i]['solde_courant']);
        if ($mnt != 0) {
          //-------------------------------------------------------------------------------------------------
          // Mouvement au débit
          // S'assurer que le compte de substitut est dans la devise du produit
          $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($produits[$idtype]['subs'], $produits[$idtype]['devise']);
           // Vérifier que le compte comptable ou son compte de substitue a été paramétré
          if ( $comptable[$compteur_mv]["compte"] == '') {
            $dbHandler->closeConnection(false);
            signalErreur(_("Vérifiez que tous les comptes comptables et leurs substituts ont été bien paramétrés"),"");
          }    
              global $global_langue_utilisateur;
              $libel_ecriture = new Trad();
              $libel_ecriture->set_traduction($global_langue_utilisateur,  _('Reprise de compte épargne'));
                    
		      $comptable[$compteur_mv]["sens"] = 'd';
		      $comptable[$compteur_mv]["montant"] = $mnt;
		      $comptable[$compteur_mv]["devise"] = $produits[$idtype]['devise'];
		      $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
		      $comptable[$compteur_mv]["id"] = $compteur_ecr;
		      $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
					/* Renseignement des infos communes des mouvements */
        	$comptable[$compteur_mv]["jou"] = 1;
        	$comptable[$compteur_mv]["date_valeur"] = date("Y-m-d");
        	$comptable[$compteur_mv]["exo"] = $exo;
        	$comptable[$compteur_mv]["validation"] = 't';
        	$comptable[$compteur_mv]["date_comptable"] = date("r");
		      $compteur_mv++;
					// Mouvement au crédit
					// S'assurer que le compte comptable dans la devise du produit
		      $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($produits[$idtype]['cpte_cpta_prod_ep'], $produits[$idtype]['devise']);
		       // Vérifier que le compte comptable ou son compte de substitue a été paramétré
          if ( $comptable[$compteur_mv]["compte"] == '') {
            $dbHandler->closeConnection(false);
            signalErreur(_("Vérifiez que tous les comptes comptables et leurs substituts ont été bien paramétrés"),"");
          }
		      $comptable[$compteur_mv]["sens"] = 'c';
		      $comptable[$compteur_mv]["montant"] = $mnt;
		      $comptable[$compteur_mv]["devise"] = $produits[$idtype]['devise'];
		      $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
		      $comptable[$compteur_mv]["id"] = $compteur_ecr;
		      $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte;
		      /* Renseignement des infos communes des mouvements */
        	$comptable[$compteur_mv]["jou"] = 1;
        	$comptable[$compteur_mv]["date_valeur"] = date("Y-m-d");
        	$comptable[$compteur_mv]["exo"] = $exo;
        	$comptable[$compteur_mv]["validation"] = 't';
        	$comptable[$compteur_mv]["date_comptable"] = date("r");
		      $compteur_mv++;  /* incrémentation du id des mouvements  */
		      $compteur_ecr++; /* incrémentation du id des écritures */
          //-------------------------------------------------------------------------------------------------
        }
      }

      // Si y a des mouvements à passer
      if (count($comptable) > 0) {

        // Ajout dans l'historique

				$result = ajout_historique(501, NULL, _("Reprise épargne"), 'admin', date("r"), $comptable);
			  if ($result->errCode != NO_ERR) {
			    /* Génération du message de confirmation */
			    $dbHandler->closeConnection(false);
			     // Message à afficher
	        $myForm = new HTML_GEN2();

	        $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
	        $myTable->set_property("align", "center");
	        $myTable->set_property("border", $tableau_border);
	        $myTable->set_property("bgcolor", '#e0e0ff');

	        // Entête tableau
	        $myTable->add_cell(new TABLE_cell(_("Erreur : lors de l insertion dans la base de données !."), 1, 1));

	        // Boutons
	        $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
	        $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '2';"));
	        $SESSION_VARS['saisie'] = array();

	        // Variables post
	        $myForm->addHiddenType("etape");

	        // Génération HTML
	        $myForm->buildHTML();
	        echo $myForm->getHTML();
			  } else {
			    /* Génération du message de confirmation */
			    $dbHandler->closeConnection(true);
			    // Message à afficher
        	$myForm = new HTML_GEN2(_("L'opération a été effectuée avec succès"));

        	  // Boutons
        $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));
        $SESSION_VARS['saisie'] = array();

         // Variables post
        $myForm->addHiddenType("etape");

        // Génération HTML
        $myForm->buildHTML();
        echo $myForm->getHTML();

			  }
      } // fin si les comptes comptables sont paramétrés
      else {
        // Message à afficher
        $myForm = new HTML_GEN2();

        $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
        $myTable->set_property("align", "center");
        $myTable->set_property("border", $tableau_border);
        $myTable->set_property("bgcolor", '#e0e0ff');

        // Entête tableau
        $myTable->add_cell(new TABLE_cell(_("Erreur : vous avez omis de renseigner un(les) compte(s) !."), 1, 1));

        // Boutons
        $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '2';"));
        $SESSION_VARS['saisie'] = array();

        // Variables post
        $myForm->addHiddenType("etape");

        // Génération HTML
        $myForm->buildHTML();
        echo $myForm->getHTML();
      }  // fin sin non comptes non paramétrés
    //} // fin si y a des mouvements à passer
  } // fin si vient de
}
?>

</body>
</html>
