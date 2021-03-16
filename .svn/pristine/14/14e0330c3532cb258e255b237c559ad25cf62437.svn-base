<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Assistant à la récupération des données : permet de générer le fichier de configuration traduction.conf automatiquement.
 * @package Recupdata
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'DB.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'lib/dbProcedures/compta.php';

global $adsys;
// Fichier de configuration à générer
$traducFile = "traduction.conf";
$etat_perte=getIdEtatPerte();
?>

<html>
<head>
<title><?=_("Assistant de configuration du module de reprise des données")?> </title>
<LINK REL="SHORTCUT ICON" HREF="../images/travaux.gif">
                               </head>
                               <body bgcolor=white>
                                             <table width="100%" cellpadding=5 cellspacing=0 border=0>
                                                                             <tr>
                                                                             <td><a target=_blank href="http://www.aquadev.org"><img border=0 title="<?= _("ADbanking Logo")?>" alt="<?= _("ADbanking Logo")?>" width=400 height=40 src="../images/ADbanking_logo.jpg"></a></td>
                                                                                                       <td valign=bottom align=center><font face="helvetica,verdana" size="+2"><?= _("Module de reprise des données")?></font></td>
                                                                                                                               </tr>
                                            </table>

<?php

// ECRAN DE SAISIE
if (!isset($ecran)) {
  echo "<FORM name='ADForm' method='post' action='assistant_rd.php?ecran=2'>";
  echo "<p align=\"center\">"._("Le système a détecté le paramétrage suivant. Veuillez entrer les acronymes correspondants")." <p>";

  //Affichage garder ancien numéro ou pas

  echo "<p align=\"center\"><font face=\"helvetica,verdana\"><b>"._("Numéro de client")."</b></font>";
  echo "<p align=\"center\">";

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  echo "<tr><td>"._("Utilisation de l'ancien numéro de client")."</td>";
  echo "<td>\n";
  echo "<select NAME=\"use_anc_num\">\n";
  echo "<option value=\"0\">"._("Non")."</option>\n";
  echo "<option value=\"1\">"._("Oui")."</option>\n";
  echo "</select>\n";
  echo "</td>\n";
  echo "</table>";
  echo "</p>";

  echo "<p align=\"center\"><font face=\"helvetica,verdana\"><b>"._("Paramètres autodétectés")."</b></font>";
  echo "<p align=\"center\">";



  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  $fields = array(); // Liste de tous les champs du formulaire, utilisé notamment pour la génération du JS

  //Affichage valeur PS et récupere le compte comptable de parts sociales
  $sql="SELECT val_nominale_part_sociale, code_devise_reference, langue_systeme_dft, exercice, type_structure, type_numerotation_compte FROM ad_agc WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  $tmprow = $result->fetchrow();
  $vnps = $tmprow[0];
  $dev_ref = $tmprow[1];
  $langue_systeme_dft = $tmprow[2];
  $exo_courant = $tmprow[3];
  $type_structure = $tmprow[4];
  $type_num_cpte = $tmprow[5];
  echo "<INPUT type=\"hidden\" name=\"type_structure\" value=\"$type_structure\">";
  echo "<INPUT type=\"hidden\" name=\"type_num_cpte\" value=\"$type_num_cpte\">";

  setMonnaieCourante($dev_ref);

  //Affichage compte asoscié à l'épargne libre et aux parts sociales
  $sql = "SELECT cpte_cpta_prod_ep, devise FROM adsys_produit_epargne WHERE id = 1 OR id = 2 ORDER BY id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }

  /* Compte comptable et devise de l'épargne libre */
  $tmprow = $result->fetchrow();
  $cpte_el = $tmprow[0];
  $dev_el = $tmprow[1];
  if ($cpte_el == '') {
    echo "<P><FONT color=red>"._("Aucun compte n'a été associé à l'épargne libre.")."<br />"._("Veuillez revoir le paramétrage")."</FONT></P>";
    die();
  }

  $cpte_el = checkCptDeviseOK($cpte_el, $dev_el);

  /* Compte comptable et devise des parts sociales */
  $tmprow = $result->fetchrow();
  $cpte_ps = $tmprow[0];
  $dev_ps = $tmprow[1];
  if ($type_structure == 1) {
    if ($cpte_ps == '') {
      echo "<P><FONT color=red>"._("Aucun compte n'a été associé aux parts sociales.")."<BR/>"._("Veuillez revoir le paramétrage")."</FONT></P>";
      die();
    }

    $cpte_ps = checkCptDeviseOK($cpte_ps, $dev_ps);
  }

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  echo "<tr><td>"._("Exercice courant")."</td><td>$exo_courant</td></tr>";
  if ($type_structure == 1) {
    echo "<tr><td>"._("Valeur nominale de la part sociale")."</td><td>".afficheMontant($vnps, true)."</td></tr>";
  }
  echo "<tr><td>"._("Devise de référence")."</td><td>$dev_ref</td></tr>";
  echo "<tr><td>"._("Compte lié à l'épargne libre")."</td><td>$cpte_el</td></tr>";
  if ($type_structure == 1) {
    echo "<tr><td>"._("Compte lié aux parts sociales")."</td><td>$cpte_ps</td></tr>";
  }
  echo "<tr><td>"._("Langue système par défaut")."</td><td>$langue_systeme_dft</td></tr>";
  echo "</table>";
  echo "</p>";
  echo "<INPUT type=\"hidden\" name=\"exo_courant\" value=\"$exo_courant\">";
  echo "<INPUT type=\"hidden\" name=\"dev_ref\" value=\"$dev_ref\">";
  echo "<INPUT type=\"hidden\" name=\"dev_el\" value=\"$dev_el\">";
  echo "<INPUT type=\"hidden\" name=\"cpte_el\" value=\"$cpte_el\">";
  if ($type_structure == 1) {
    echo "<INPUT type=\"hidden\" name=\"cpte_ps\" value=\"$cpte_ps\">";
    echo "<INPUT type=\"hidden\" name=\"dev_ps\" value=\"$dev_ps\">";
    echo "<INPUT type=\"hidden\" name=\"vnps\" value=\"".afficheMontant($vnps)."\">";
  }
  echo "<INPUT type=\"hidden\" name=\"langue_systeme_dft\" value=\"$langue_systeme_dft\">";

  //Affichage secteur activité
  echo "<p align=\"center\"><font face=\"helvetica,verdana\"><b>"._("Section secteurs d activité")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id, libel FROM adsys_sect_activite order by id";
  $result = $db->query($sql);
  // print_r($result);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    echo "<tr><td>$id</td><td>$libel</td>";
    echo "<td><INPUT type=\"text\" name=\"sect_act_$id\" value=\"\" size=\"3\"></td></tr>";
    array_push($fields, "sect_act_$id");
  }
  echo "</table>";
  echo "</p>";

  // Affichage type pièce
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section Type de pièce d'identité")."</b></font>";
  echo "<p align='center'>";

  if ($langue_systeme_dft == '') // si la langue par défaut n'est pas paramétrée
    $sql = "SELECT id, libel FROM adsys_type_piece_identite order by id";
  else
    $sql = "SELECT id, traduction(libel, '$langue_systeme_dft') FROM adsys_type_piece_identite order by id";

  $result = $db->query($sql);
  // print_r($result);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    echo "<tr><td>$id</td><td>$libel</td>";
    echo "<td><INPUT type=\"text\" name=\"type_piece_id_$id\" value=\"\" size=\"3\"></td></tr>";
    array_push($fields, "type_piece_id_$id");
  }
  echo "</table>";
  echo "</p>";

  //Affichage gestionnaire
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section gestionnaires")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id_utilis, nom, prenom FROM ad_uti order by id_utilis";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $nom = $tmprow[1];
    $prenom = $tmprow[2];
    echo "<tr><td>$id</td><td>$prenom</td><td>$nom</td>";
    echo "<td><INPUT type=\"text\" name=\"gestionnaire_$id\" value=\"\" size=\"3\"></td></tr>";
    array_push($fields, "gestionnaire_$id");
  }
  echo "</table>";
  echo "</p>";

  // Affichage produit épargne
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section produits d'épargne")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id,libel,devise FROM adsys_produit_epargne WHERE service_financier = 't' order by id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $produit = $tmprow[1];
    $devise = $tmprow[2];
    echo "<tr><td>$id</td><td>$produit ($devise)</td>";
    echo "<td><INPUT type=\"text\" name=\"produit_$id\" value=\"\" size=\"6\"></td></tr>";
    array_push($fields, "produit_$id");

  }
  echo "</table>";
  echo "</p>";

  //***********************************
  // Affichage produit de crédit
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section produits de crédit")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id,libel,devise FROM adsys_produit_credit  order by id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $produit = $tmprow[1];
    $devise = $tmprow[2];
    echo "<tr><td>$id</td><td>$produit ($devise)</td>";
    echo "<td><INPUT type=\"text\" name=\"produit_credit_$id\" value=\"\" size=\"6\"></td></tr>";
    array_push($fields, "produit_credit_$id");
  }
  echo "</table>";
  echo "</p>";
  //************************************

  //Affichage compte comptable

  //Recupére le compte comptable asocié aux DAV
  $sql = "SELECT cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id = 1 ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  $tmprow = $result->fetchrow();
  $cpte_ep = $tmprow[0];

  // Recupére tous les comptes comptables, elle va permettre de choisir les comptes à utiliser pour équilibre les opérations de reprises de données
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section Comptabilité")."</b></font><br>";

  /* Compte de substitut des produits d'épargne et de parts sociales  */
  if ($type_structure == 1) {
    echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des produits d'épargne et de parts sociales")."</b></font>";
    echo "<p align='center'>";
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id != 3 and id != 4 order by id";
  } else {
    echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des produits d'épargne")."</b></font>";
    echo "<p align='center'>";
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id != 2 and id != 3 and id != 4 order by id";
  }
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $compte = $tmprow[3];
    $produit = $tmprow[1];
    $devise = $tmprow[2];
    echo "<tr><td>$produit ($compte)</td>";
    echo "<td>\n";
    echo "<select NAME=\"cpte_ep_$id\">\n";
    echo "<option value=\"0\">[Aucun]</option>\n";
    $CC = getComptesComptables();
    if (isset($CC))
      foreach($CC as $key=>$value) {
      echo "<option value=$key >".$key." ".$value['libel_cpte_comptable']."</option>\n";
    }
    echo "</td></tr>\n";
    array_push($fields, "cpte_ep_$id");
  }
  echo "</table>";
  echo "</p>";

    $sql_type_perte = "select passage_perte_automatique from ad_agc";
	$result_type_perte = $db->query($sql_type_perte);
	if (DB::isError($result_type_perte)) {
	$dbHandler->closeConnection(false);
	signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$row_perte = $result_type_perte->fetchrow();
	$type_radiation_credit = $row_perte[0];
  /* Compte de substitut des produits de crédits  */
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des produits de crédit")."</b></font>";
  echo "<p align='center'>";
  if ($type_radiation_credit == 't')
  	$sql = "SELECT DISTINCT(num_cpte_comptable) FROM adsys_etat_credit_cptes WHERE id_etat_credit < $etat_perte ";
  else
  	$sql = "SELECT DISTINCT(num_cpte_comptable) FROM adsys_etat_credit_cptes";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  $CC = getComptesComptables();
  $i=0;
  while ($tmprow = $result->fetchrow()) {
    $compte = $tmprow[0];

    echo "<tr><td>".$CC[$compte]['libel_cpte_comptable']." ( $compte )</td>";
    echo "<td>\n";
    echo "<select NAME=\"cpte_cr_$i\">\n";
    echo "<option value=\"0\">["._("Aucun")."]</option>\n";
    if (isset($CC))
      foreach($CC as $key=>$value)
      echo "<option value=$key >".$key." ".$value['libel_cpte_comptable']."</option>\n";
    echo "</td></tr>\n";
    array_push($fields, "cpte_cr_$i");

    $i++;

  }
  echo "</table>";
  echo "</p>";

  /* Comptes de substitut des comptes de garantie */
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des comptes de garantie")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id,libel,cpte_cpta_prod_cr_gar FROM adsys_produit_credit WHERE prc_gar_num > 0 ORDER BY id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  $i=0;
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    $compte = $tmprow[2];
    echo "<tr><td>$libel ( $compte )</td>";
    echo "<td>\n";
    echo "<select NAME=\"cpte_gar_$id\">\n";
    echo "<option value=\"0\">[Aucun]</option>\n";
    $CC = getComptesComptables();
    if (isset($CC))
      foreach($CC as $key=>$value) {
      echo "<option value=$key >".$key." ".$value['libel_cpte_comptable']."</option>\n";
    }
    echo "</td></tr>\n";
    array_push($fields, "cpte_gar_$id");
    $i++;

  }
  echo "</table>";
  echo "</p>";

  // Section pays
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section Pays")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id_pays, libel_pays, code_pays FROM adsys_pays order by id_pays";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    $code = $tmprow[2];
    echo "<tr><td>$id</td><td>$libel</td><td>$code</td></tr>";
  }
  echo "</table>";
  echo "</p>";

  //bouton de validation
  echo "<p align='center'>";
  echo "<INPUT type='submit' name='ok' value='"._("Visualiser la saisie")."' onclick=\"return checkForm();\">";
  echo "</p>";
  echo "</FORM>";

  // Génération du code JS vérifiant que tous les champs du formulaire ont bien été remplis
  $js = "function checkForm()\n{";
  foreach ($fields as $value) {
    $js .= "if (document.ADForm.$value.value == '')
           {
           alert('"._("Saisie incomplète")."');return false;
         }\n";
  }
  $js .= " return true;}";
  echo "<script type=\"text/javascript\">$js</script>";
  $dbHandler->closeConnection(true);
}
elseif ($ecran == 3) { //GENERATION DU FICHIER DE CONFIGURATION
  /* Récupération de tous les comptes comptables */
  $CC = array();
  $CC = getComptesComptables();

  $output = "# ".$ProjectName."
# Fichier permettant de traduire les acronymes utilisés dans le fichier de reprise des données
# en numéros compréhensibles par la base de données

# Ne modifiez pas les lignes qui suivent !!! Elles sont utilisées par ADbanking

[stat_jur]
PP	1	      # Personne physique
PM	2	      # Personne morale
GI	3	      # Groupe informel
GS	4	      # Groupe solidaire

[nat_jur]
BQ	1	      # Banque
IF	2	      # Institution financière
OL	3	      # Organisme public
OR	4	      # Organisme privé

[qualite]
MA	1	      # Membre auxiliaire
MO	2	      # Membre ordinaire
ME	3	      # Membre employé
MD	4	      # Membre dirigeant

[pp_sexe]
M	1	      # Masculin
F	2	      # Féminin

[etat_client]
AC	2	      # Actif
DC	3	      # Décédé
DM	5	      # Démissionnaire
RA	6	      # Radié
";

  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  //Section secteurs d'activité
  $output .= "\n[sect_act]
# Insérez ici vos secteurs d'activite
";
  $sql = "SELECT id, libel FROM adsys_sect_activite order by id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $i = $row[0];
    $dynvar = "sect_act_$i";
    $output .= $ {$dynvar}."\t$i\t# ".$row[1]."\n";
  }

  // Section types de pièces d'identité
  $output .= "\n[pp_type_piece_id]
# Insérez ici vos types de pièce d'identité
";
  $sql = "SELECT id, libel FROM adsys_type_piece_identite order by id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $i = $row[0];
    $dynvar = "type_piece_id_$i";
    $output .= $ {$dynvar}."\t$i\t# ".$row[1]."\n";
  }

  // Section gestionnaire
  $output .= "\n[gestionnaires]
# Insérez ici vos gestionnaires
";
  $sql = "SELECT id_utilis, nom, prenom FROM ad_uti order by id_utilis";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $i = $row[0];
    $dynvar = "gestionnaire_$i";
    $output .= $ {$dynvar}."\t$i\t# ".$row[2]." ".$row[1]."\n";
  }


  // Section pays
  $output .= "\n[pays]
# Insérez ici vos pays
";
  $sql = "SELECT id_pays, libel_pays, code_pays FROM adsys_pays order by id_pays";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $i = $row[0];
    $output .= $row[2]."\t$i\t# ".$row[1]."\n";
  }


  // Section produits d'épargne
  $output .= "\n[produits]
# Insérez ici vos produits d'épargne en précisant pour chacun d'eux le terme (en mois) et la devise
";
  $sql = "SELECT id, libel, terme, devise, decouvert_max FROM adsys_produit_epargne WHERE service_financier = 't' order by id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $i = $row[0];
    $dynvar = "produit_$i";
    $output .= "prod_ep_".$ {$dynvar}."\t$i\t".$row[2]."\t".$row[3]."\t".$row[4]."\t# ".$row[1]."\n";
  }
  // Section produits de crédit
  $output .= "\n[produit_credit]
# Insérez ici vos produits de crédit en précisant le libellé, la devise et la périodicité
";
  $sql = "SELECT id, libel, devise, periodicite FROM adsys_produit_credit  order by id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $i = $row[0];
    $dynvar = "produit_credit_$i";
    $periodicite = $row[3];
    $output .= $ {$dynvar}."\t$i\t".$row[0]."\t".$row[1]."\t".$row[2]."\t".$adsys["adsys_duree_periodicite"][$periodicite]."\n";
  }

  /* Section comptes de substitue des produit d'épargne : adaptée au scripts perl*/
  $output .= "\n[sub_ep]
# Insérez ici vos comptes de substitut pour les produits d'épargne
";
  if ($type_structure == 1) {
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE (id != 3 and id != 4) order by id";
  } else {
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE (id != 2 and id != 3 and id != 4) order by id";
  }
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $id = $row[0];
    $libel = $row[1];
    $devise_pr = $row[2];
    $compte = $row[3];
    /* Compte comptable dans la devise du produit */
    $dynvar = checkCptDeviseOK($compte, $devise_pr);
    /* Compte de substitut saisi */
    $nom_zone_text = "cpte_ep_$id";
    $val_zone_text = $ {$nom_zone_text};

    /* Compte de substitut dans la devise du produit d'épargne */
    if ($val_zone_text != 0 and $val_zone_text != '')
      $cpte_subs = checkCptDeviseOK($val_zone_text, $devise_pr);
    else
      $cpte_subs = $val_zone_text;

    $output .= $dynvar."\t".$cpte_subs."\t# ".$libel."\n";

    /* On récupère le compte substitut des parts sociales pour la section [general] */
    if ( $id==2)
      $cpte_subs_ps = $cpte_subs;
  }

  /* Section comptes de substitue des produit d'épargne : adaptée au scripts php*/
  $output .= "\n[sub_ep_php]
# Insérez ici vos comptes de substitut pour les produits d'épargne pour les scripts php
";
  if ($type_structure == 1) {
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE (id !=3 and id != 4) order by id";
  } else {
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE (id != 2 and id !=3 and id != 4) order by id";
  }
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  while ($row = $result->fetchrow()) {
    $id = $row[0];
    $libel = $row[1];
    $devise_pr = $row[2];
    $compte = $row[3];
    $dynvar = "prod_sub_$id";
    /* Compte de substitut saisi */
    $nom_zone_text = "cpte_ep_$id";
    $val_zone_text = $ {$nom_zone_text};
    $subs = "cpte_ep_$id";

    /* Compte de substitut dans la devise du produit d'épargne */
    if ($val_zone_text != 0 and $val_zone_text !='')
      $cpte_subs = checkCptDeviseOK($val_zone_text, $devise_pr);
    else
      $cpte_subs = $val_zone_text;

    $output .= $dynvar."\t".$cpte_subs."\t# ".$libel."\n";

    /* On récupère le compte substitut des parts sociales pour la section [general] */
    if ( $id==2)
      $cpte_subs_ps = $cpte_subs;
  }

  /* Section comptes de substitue des produit de crédit */
  $cptes_etat_credit = array();
  $output .= "\n[sub_cr]
# Insérez ici vos comptes de substitut pour les produits de crédit
";
  if($type_radiation_credit == 't')
  	$sql = "SELECT DISTINCT (num_cpte_comptable) FROM adsys_etat_credit_cptes WHERE id_etat_credit < $etat_perte ";
  else
  	$sql = "SELECT DISTINCT (num_cpte_comptable) FROM adsys_etat_credit_cptes";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  $i=0;
  while ($row = $result->fetchrow()) {
    $id = $row[0];
    $compte = $row[0];
    /* Compte comptable dans la devise du produit */
    $dynvar = checkCptDeviseOK($compte, $devise_pr);
    /* Compte de substitut saisi */
    $subs = "cpte_cr_$i";
    /* Compte de substitut dans la devise du produit sera connu lors de la reprise des crédits  */
    //$subs = checkCptDeviseOK($subs, $CC[$compte]['devise']);
    $output .= $dynvar."\t".$ {$subs}."\t# ".$row[0]."\n";
    $i++;
    $cptes_etat_credit[$compte] = $ {$subs};
  }

  /* Section comptes de substitue des comptes de garantie : adaptée au scripts php */
  $output .= "\n[sub_gar_php]
# Insérez ici vos comptes de substitut des comptes de garantie pour les scripts php
";
  $sql = "SELECT id,libel,cpte_cpta_prod_cr_gar,devise FROM adsys_produit_credit WHERE prc_gar_num > 0 ORDER BY id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  while ($row = $result->fetchrow()) {
    $id = $row[0];
    $libel = $row[1];
    $compte = $row[2];
    $devise_pr = $row[3];
    $dynvar = "cpte_gar_$id";
    /* Compte de substitut saisi */
    $nom_zone_text = "cpte_gar_$id";
    $val_zone_text = $ {$nom_zone_text};
    //$subs = "cpte_gar_$id";

    /* Compte de substitut dans la devise du produit de crédit  */
    if ($val_zone_text !=0 and $val_zone_text !='' )
      $cpte_subs = checkCptDeviseOK($val_zone_text, $devise_pr);
    else
      $cpte_subs = $val_zone_text;

    $output .= $dynvar."\t".$cpte_subs."\t# "._("Compte de substitut du compte de garantie")." ".$compte."\n";

  }

/* Section comptes de substitut des comptes de garantie : adaptée au scripts perl*/
  $output .= "\n[sub_gar]
# Insérez ici vos comptes de substitut pour les comptes de garantie
";
   $sql = "SELECT id,libel,cpte_cpta_prod_cr_gar,devise FROM adsys_produit_credit WHERE prc_gar_num > 0 ORDER BY id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  while ($row = $result->fetchrow())
  {
    $id = $row[0];
    $libel = $row[1];
    $compte = $row[2];
    $devise_pr = $row[3];
    $dynvar = checkCptDeviseOK($compte, $devise_pr);
    /* Compte de substitut saisi */
    $nom_zone_text = "cpte_gar_$id";
    $val_zone_text = ${$nom_zone_text};

    /* Compte de substitut dans la devise du produit de crédit  */
    if($val_zone_text !=0 and $val_zone_text !='' )
      $cpte_subs = checkCptDeviseOK($val_zone_text, $devise_pr);
    else
      $cpte_subs = $val_zone_text;

    $output .= $dynvar."\t".$cpte_subs."\t# "._("Compte de substitut du compte de garantie")." ".$compte."\n";

  }

  /* Section comptes de substitue des produit de crédit : adaptée au scripts php */
  $output .= "\n[sub_cr_php]
# Insérez ici vos comptes de substitut des produits de crédit pour les scripts php
";
	if ($type_radiation_credit == 't')
  		$sql = "SELECT id,libel,id_etat_credit,num_cpte_comptable,devise FROM adsys_produit_credit,adsys_etat_credit_cptes WHERE id=id_prod_cre  and id_etat_credit < $etat_perte order by id";
    else
  		$sql = "SELECT id,libel,id_etat_credit,num_cpte_comptable,devise FROM adsys_produit_credit,adsys_etat_credit_cptes WHERE id=id_prod_cre order by id";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
  {
    $id = $row["id"];
    $libel = $row["libel"];
    $etat = $row["id_etat_credit"];
    $compte = $row["num_cpte_comptable"];
    $devise_pr = $row['devise'];
    $dynvar = "cpt_etat_cr_".$id."_".$etat;
    /* Compte de substitut saisi */
    $subs = $cptes_etat_credit[$compte];
    /* Compte de substitut dans la devise du produit de crédit  */
    if ($subs !=0 and $subs !='')
      $subs = checkCptDeviseOK($subs, $devise_pr);

    $output .= $dynvar."\t".$subs."\t# ".sprintf(_("Compte de substitut de %s pour l'état "),$libel)." ".$etat."\n";
  }


  //Section générale - val nom d'une PS
  $output .= "
[general]
dev_ref $dev_ref \t# Code ISO de la devise de référence
cpt_el $cpte_el \t# Compte lié à l'épargne libre
dev_el $dev_el \t# Code ISO de la devise du DAV\n";
  if ($type_structure == 1) {
    $output .= "
cpt_ps $cpte_ps \t# Compte lié aux parts sociales
dev_ps $dev_ps \t# Code ISO de la devise des parts sociales
cpte_subs_ps $cpte_subs_ps \t# Compte de substitut des parts sociales
val_nominale_ps $vnps \t# Valeur nominale de la part sociale\n";
  }
  $output .= "
langue_systeme_dft $langue_systeme_dft \t# Langue système par défaut
exo_courant $exo_courant \t# Numéro de l'exercice courant
use_anc_num $use_anc_num \t# Utiliser ancien numéro de client
type_num_cpte $type_num_cpte \t# Type de numérotation de compte de l'agence\n";

  // Ecriture du fichier output $traducfile
  $handler = fopen("/tmp/traduction.conf","w");
  var_dump($handler);
  fwrite($handler, $output);
  fclose($handler);

  echo "<p align=\"center\"><font face=\"helvetica,verdana\"><b>".sprintf(_("Le fichier %s a été généré et placé dans %s . Placer le dans le repertoire recup_data."), "<code> <a href=\"file:///tmp/traduction.conf\">$traducFile </a></code>", "<code>/tmp</code>!<br />" )."</b></font>";

  $dbHandler->closeConnection(true);
}
else { //ECRAN DE CONFIRMATION/MODIFICATION
  echo "<FORM name='ADForm' method='post' action='assistant_rd.php?ecran=3'>";
  echo "<INPUT type=\"hidden\" name=\"type_structure\" value=\"$type_structure\">";
  echo "<INPUT type=\"hidden\" name=\"type_num_cpte\" value=\"$type_num_cpte\">";
  echo "<p align=\"center\">"._("Confirmation de la saisie. Modifiez les données si nécessaire.")."<p>";
  echo "<p align=\"center\">";

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  echo "<tr><td>"._("Utiliser ancien numéro")."</td>";
  echo "<td>\n";
  if ($use_anc_num == 0) {
    echo "<select NAME=\"use_anc_num\">\n";
    echo "<option value=\"0\" selected>"._("Non")."</option>\n";
    echo "<option value=\"1\">"._("Oui")."</option>\n";
  } else {
    echo "<select NAME=\"use_anc_num\">\n";
    echo "<option value=\"0\">"._("Non")."</option>\n";
    echo "<option value=\"1\" selected>"._("Oui")."</option>\n";
  }
  echo "</select>\n";
  echo "</td>\n";
  echo "</table>";
  echo "</p>";

  echo "<p align=\"center\"><font face=\"helvetica,verdana\"><b>"._("Paramètres autodétectés")."</b></font>";
  echo "<p align=\"center\">";

  $fields = array(); // Liste de tous les champs du formulaire, utilisé notamment pour la génération du JS

  $db = $dbHandler->openConnection();

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  echo "<tr><td>Exercice courant</td><td>$exo_courant</td></tr>";
  if ($type_structure == 1) {
    echo "<tr><td>"._("Valeur nominale de la part sociale")."</td><td>".afficheMontant($vnps, true)."</td></tr>";
  }
  echo "<tr><td>"._("Devise de référence")."</td><td>$dev_ref</td></tr>";
  echo "<tr><td>"._("Compte lié à l'épargne libre")."</td><td>$cpte_el</td></tr>";
  if ($type_structure == 1) {
    echo "<tr><td>"._("Compte lié aux parts sociales")."</td><td>$cpte_ps</td></tr>";
  }
  echo "<tr><td>"._("Langue système par défaut")."</td><td>$langue_systeme_dft</td></tr>";
  echo "</table>";
  echo "</p>";
  echo "<INPUT type=\"hidden\" name=\"exo_courant\" value=\"$exo_courant\">";
  echo "<INPUT type=\"hidden\" name=\"dev_ref\" value=\"$dev_ref\">";
  echo "<INPUT type=\"hidden\" name=\"dev_el\" value=\"$dev_el\">";
  echo "<INPUT type=\"hidden\" name=\"cpte_el\" value=\"$cpte_el\">";
  if ($type_structure == 1) {
    echo "<INPUT type=\"hidden\" name=\"cpte_ps\" value=\"$cpte_ps\">";
    echo "<INPUT type=\"hidden\" name=\"dev_ps\" value=\"$dev_ps\">";
    echo "<INPUT type=\"hidden\" name=\"vnps\" value=\"".recupMontant($vnps)."\">";
  }
  echo "<INPUT type=\"hidden\" name=\"langue_systeme_dft\" value=\"$langue_systeme_dft\">";
  //Affichage secteur d'activité
  echo "<p align=\"center\"><font face=\"helvetica,verdana\"><b>"._("Section secteurs d activité")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id, libel FROM adsys_sect_activite order by id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    echo "<tr><td>$id</td><td>$libel</td>";
    $dynvar = "sect_act_$id";
    echo "<td><INPUT type=\"text\" name=\"sect_act_$id\" value=".$ {$dynvar}." size=\"3\"></td></tr>";
    array_push($fields, "sect_act_$id");
  }
  echo "</table>";
  echo "</p>";

  //Affichage type de pièce
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section Type de pièce d'identité")."</b></font>";
  echo "<p align='center'>";
  if ($langue_systeme_dft == '') // si la langue par défaut n'est pas paramétrée
    $sql = "SELECT id, libel FROM adsys_type_piece_identite order by id";
  else
    $sql = "SELECT id, traduction(libel, '$langue_systeme_dft') FROM adsys_type_piece_identite order by id";
  $result = $db->query($sql);
  // print_r($result);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    $dynvar = "type_piece_id_$id";
    echo "<tr><td>$id</td><td>$libel</td>";
    echo "<td><INPUT type=\"text\" name=\"type_piece_id_$id\" value=\"".$ {$dynvar}."\" size=\"3\"></td></tr>";
    array_push($fields, "type_piece_id_$id");
  }
  echo "</table>";
  echo "</p>";

  //Affichage gestionnaire
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section gestionnaires")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id_utilis, nom, prenom FROM ad_uti order by id_utilis";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $nom = $tmprow[1];
    $prenom = $tmprow[2];
    echo "<tr><td>$id</td><td>$prenom</td><td>$nom</td>";
    $dynvar = "gestionnaire_$id";
    echo "<td><INPUT type=\"text\" name=\"gestionnaire_$id\" value=\"".$ {$dynvar}."\" size=\"3\"></td></tr>";
    array_push($fields, "gestionnaire_$id");
  }
  echo "</table>";
  echo "</p>";

  //DEBUT AJOUT
  //Affichage produits épargne
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section produits d'épargne")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id,libel,devise FROM adsys_produit_epargne WHERE service_financier = 't' order by id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  $used_code = array();
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $produit = $tmprow[1];
    $devise = $tmprow[2];
    echo "<tr><td>$id</td><td>$produit ($devise)</td>";
    $dynvar = "produit_$id";
    echo "<td><INPUT type=\"text\" name=\"produit_$id\" value=\"".$ {$dynvar}."\" size=\"6\"></td></tr>";
    if(in_array($ {$dynvar}, $used_code)){
			 // Génération du code JS vérifiant que tous les champs du formulaire ont bien été remplis
		  $js_code = "function checkForm1()\n{";
	    $js_code .= " alert(\"Le code est utilisé\");return false;";
		  $js_code .= " return true;}";
    }

    echo "<script type=\"text/javascript\">$js_code</script>";
    array_push($used_code, $ {$dynvar});
    array_push($fields, "produit_$id");
  }

  echo "</table>";
  echo "</p>";

   //Affichage produits crédit
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section produits de crédit")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id,libel,devise FROM adsys_produit_credit order by id";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $produit = $tmprow[1];
    $devise = $tmprow[2];
    echo "<tr><td>$id</td><td>$produit ($devise)</td>";
    $dynvar = "produit_credit_$id";
    echo "<td><INPUT type=\"text\" name=\"produit_credit_$id\" value=\"".$ {$dynvar}."\" size=\"6\"></td></tr>";
    array_push($fields, "produit_credit_$id");
  }
  echo "</table>";
  echo "</p>";

  // Recupére tous les comptes comptables , elle va merttre de choisir à utiliser pour équilibre les opérations de reprises de données

  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section Comptabilité")."</b></font><br>";

  /* Compte de substitut des produits d'épargne et de parts sociales  */
  if ($type_structure == 1) {
    echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des produits d'épargne et de parts sociales")."</b></font>";
    echo "<p align='center'>";
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id != 3 and id != 4 order by id";
  } else {
    echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des produits d'épargne")."</b></font>";
    echo "<p align='center'>";
    $sql = "SELECT id,libel,devise,cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id != 2 and id != 3 and id != 4 order by id";
  }
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $compte = $tmprow[3];
    $produit = $tmprow[1];
    $devise = $tmprow[2];
    echo "<tr><td>$produit ($compte)</td>";
    echo "<td>\n";
    echo "<select NAME=\"cpte_ep_$id\">\n";
    echo "<option value=\"0\">["._("Aucun")."]</option>\n";
    $CC = getComptesComptables();
    if (isset($CC))
      foreach($CC as $key=>$value) {
      $compte = "cpte_ep_$id";
      if ($key == $ {$compte})
        $selected = "selected";
      else
        $selected = "";
      echo "<option value=$key $selected>".$key." ".$value['libel_cpte_comptable']."</option>\n";

      //echo "<option value=$key >".$key." ".$value['libel_cpte_comptable']."</option>\n";
    }
    echo "</td></tr>\n";
    array_push($fields, "cpte_ep_$id");
  }
  echo "</table>";
  echo "</p>";

  /* Compte de substitut des produits de crédits  */
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des produits de crédit")."</b></font>";
  echo "<p align='center'>";
  if($type_radiation_credit == 't')
  	$sql = "SELECT DISTINCT(num_cpte_comptable) FROM adsys_etat_credit_cptes WHERE id_etat_credit < $etat_perte ";
  else
  	$sql = "SELECT DISTINCT(num_cpte_comptable) FROM adsys_etat_credit_cptes";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  $CC = getComptesComptables();
  $i=0;
  while ($tmprow = $result->fetchrow()) {
    $compte = $tmprow[0];

    echo "<tr><td>".$CC[$compte]['libel_cpte_comptable']." ( $compte )</td>";
    echo "<td>\n";
    echo "<select NAME=\"cpte_cr_$i\">\n";
    echo "<option value=\"0\">["._("Aucun")."]</option>\n";

    if (isset($CC))
      foreach($CC as $key=>$value) {
      $compte = "cpte_cr_$i";
      if ($key == $ {$compte})
        $selected = "selected";
      else
        $selected = "";
      echo "<option value=$key $selected>".$key." ".$value['libel_cpte_comptable']."</option>\n";

    }
    echo "</td></tr>\n";
    array_push($fields, "cpte_cr_$i");

    $i++;

  }
  echo "</table>";
  echo "</p>";

  /* Comptes de substitut des comptes de garantie  */
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Comptes de substitut des comptes de garantie")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id,libel,cpte_cpta_prod_cr_gar FROM adsys_produit_credit  WHERE prc_gar_num > 0 ORDER BY id";
  $result = $db->query($sql);
  if (DB::isError($result))
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";

  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    $cpte = $tmprow[2];

    echo "<tr><td>$libel ( $cpte )</td>";
    echo "<td>\n";
    echo "<select NAME=\"cpte_gar_$id\">\n";
    echo "<option value=\"0\">["._("Aucun")."]</option>\n";
    $CC = getComptesComptables();
    if (isset($CC))
      foreach($CC as $key=>$value) {
      $compte = "cpte_gar_$id";
      if ($key == $ {$compte})
        $selected = "selected";
      else
        $selected = "";
      echo "<option value=$key $selected>".$key." ".$value['libel_cpte_comptable']."</option>\n";
    }
    echo "</td></tr>\n";
    array_push($fields, "cpte_gar_$id");
  }
  echo "</table>";
  echo "</p>";

  // Section pays
  echo "<p align='center'><font face='helvetica,verdana'><b>"._("Section Pays")."</b></font>";
  echo "<p align='center'>";
  $sql = "SELECT id_pays, libel_pays, code_pays FROM adsys_pays order by id_pays";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur("recup_data", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }
  echo "<table bgcolor='#e0e0ff' cellpadding=5 cellspacing=0 border=0>";
  while ($tmprow = $result->fetchrow()) {
    $id = $tmprow[0];
    $libel = $tmprow[1];
    $code = $tmprow[2];
    echo "<tr><td>$id</td><td>$libel</td><td>$code</td></tr>";
  }
  echo "</table>";
  echo "</p>";

  $js = "function checkForm()\n{";
  foreach ($fields as $value) {
    $js .= "if (document.ADForm.$value.value == '')
           {
           alert('"._("Saisie incomplète")."');return false;
         }\n";
  }
  $js .= " return true;}";
  echo "<script type=\"text/javascript\">$js</script>";

  echo "<p align='center'>";
  echo "<INPUT type='submit' name='ok' value='"._("Générer le fichier")."' onclick=\"return checkForm();\">";
  echo "</p>";
  echo "</FORM>";

  $dbHandler->closeConnection(true);
}
?>

</body>
</html>