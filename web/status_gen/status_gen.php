<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Génère la barre de statut (frame supérieure)
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/dbProcedures/main_func.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/carte_atm.php';
require_once 'lib/multilingue/locale.php';
require_once 'lib/dbProcedures/client.php';

// Début page HTML
echo "<HTML>\n<head>\n<script type=\"text/javascript\" src=\"$http_prefix/lib/java/scp.php?m_agc=".$_REQUEST['m_agc']."&http_prefix=$http_prefix\"></script>\n";
require_once 'lib/html/stylesheet.php';
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
echo "<META Http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n";
echo "<META Http-Equiv=\"Pragma\" Content=\"no-cache\">\n";
echo "<META Http-Equiv=\"Cache\" Content=\"no store\">\n";
echo "<META Http-Equiv=\"Expires\" Content=\"0\">\n";
echo "</head>\n";
echo "<BODY TEXT=$colt_statut BGCOLOR=$colb_statut BACKGROUND=\"$http_prefix/images/ADbanking_logo_background.jpg\" >\n";

// Début du tableau
echo "<p><TABLE WIDTH=100% ALIGN=center VALIGN=top BORDER=0 CELLPADDING=0 CELLSPACING=0>\n";

// Première et unique ligne
echo "<TR>";

// Première cellule
echo "<TD height=50>
<TABLE border=0 VALIGN=middle>
<TR>         <TD height=25 CLASS=\"statut\">"._("Agence").": <B>".$global_agence."</B> (".adb_gettext($adsys["adsys_statut_agence"][$global_statut_agence]).")</TD>
</TR>
<TR>
<TD height=25 CLASS=\"statut\">"._("Guichet").": <B>$global_guichet</B> ($global_nom_utilisateur)</TD>
</TR>
</TABLE>
</TD>";

// Seconde cellule
echo "<TD>
<TABLE border=0 VALIGN=top CELLPADDING=0>
<TR>";
if ($global_id_client != ""){ // Num Tel Client validation alert msg : Ticket 768
  $isValidNumTel = isValidNumTelClient($global_id_client);
  if ($isValidNumTel === false){
    echo "<TD CLASS=\"statut\"> <FONT color=$colt_error size='2.5'>"._("Numéro de téléphone n’existe pas ou Invalide")."</TD>";
  }
}
echo "</TR>";
echo "<TR>";
if ($global_id_client != "")
  echo "<TD CLASS=\"statut\">"._("Client").": <B>$global_id_client_formate</B> ($global_client)</TD>";
echo "
</TR>
<TR>
<TD CLASS=\"statut\"> <FONT color=$colt_error size='2.5'>";

// Client débituer ?
if ($global_client_debiteur == true)
  echo _("Client débiteur ");

// Echéance d'un DAT ?
if ($global_alerte_DAT == true)
  echo _("Echéance DAT   ");

//double affiliation
if((!$double_affiliation) && ($global_id_client != "")){
	$data_client = getClientDatas($global_id_client);
	if($data_client['pp_type_piece_id'] != "" && $data_client['pp_nm_piece_id'] != ""){
		$numero_client = getNumPieceIdClient($data_client['pp_type_piece_id'], $data_client['pp_nm_piece_id']);
		if(count($numero_client) > 1){
	    echo _("Double affiliation")."   ";
	  }
	}	
}
if (($global_id_client != "")){
    $condition = " id_client = ".$global_id_client." and etat_carte = 5 and now() between date_carte_expiration - interval '30 days' and date_carte_expiration ";
    $data = getCarteATM($condition);
    if(sizeof($data) > 0){
        echo _("Votre carte ATM arrive a expiration dans moins de 30 jours!");
    }
}

// Crédit en retard ?
if (is_array($global_credit_niveau_retard)) {
  $ET = getTousEtatCredit(); // tous les état de crédit paramétrés
  $etat_plus_avance = array_keys($global_credit_niveau_retard);
  // Si l'état le plus avancé est au moins en retart, l'afficher et les dossiers concernés
  if ($etat_plus_avance[0] > 1) {
    $infos = _("Crédit ").$ET[$etat_plus_avance[0]]["libel"]." (";
    foreach($global_credit_niveau_retard[$etat_plus_avance[0]] as $cle=>$id_doss)
    $infos .= $id_doss.", ";

    $infos = substr($infos, 0, -2);
    $infos .= ")";
    echo $infos;

    if ($global_suspension_pen)
      echo "("._("pénalités suspendues").")";
    echo "   ";
  }
}

// Epargne obligatoire ?
if ($global_cli_epar_obli == true)
  echo _("Epargne obligatoire")."   ";

// Gestion licence
echo afficheAlerteLicence();

echo "    </TD>
</TR>
</TABLE>
</TD>";

// Troisième cellule : la photo
echo "<TD>";
if ($global_photo_client != "")
  echo "<A href=\"#\" onclick=\"open_image_manager('photo','"._("Photographie")."','".$global_photo_client."',0);\"><IMG style='position:absolute;top:10vh;right:19vw;' WIDTH=50 HEIGHT=60 src=\"".$global_photo_client."\"></IMG></A>";
echo "</TD>";

// Quatrième cellule : la date et la signature
echo "<TD align=right>
<TABLE border=0>
<TR>
<TD CLASS=\"statut\" ALIGN=right VALIGN=middle><b>".strftime("%A %d %B %Y")."</b></TD>
</TR>
<TR>
<TD CLASS=\"statut\" ALIGN=right VALIGN=middle>";
if ($global_signature_client != "")
  echo "<A href=\"#\" onclick=\"open_image_manager('signature','"._("Spécimen de signature")."','".$global_signature_client."',0);\"><IMG WIDTH=100 HEIGHT=20 src=\"".$global_signature_client."\"></IMG></A>";
echo "
</TD>
</TR>
</TABLE>
</TD>";


//Fin du tableau & HTML
echo "</TR></TABLE></p></BODY></HTML>\n";

// On ferme la session explicitement pour pouvoir faire des flush() {@link PHP_MANUAL#flush}
// dans le frame principal lors des longs traitements (batch).
session_write_close();

?>
