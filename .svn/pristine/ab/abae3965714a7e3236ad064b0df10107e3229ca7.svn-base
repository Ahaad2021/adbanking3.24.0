<?php
/**
 * Main.php : page principale, définit les frames et leur contenu.
 * Accédé uniquement après le login.
 * Initialise les variables de session.
 * @package Systeme
 */

// Permet d'afficher les dates/heures en langue française
setlocale(LC_ALL, "fr_FR");

// On charge les variables globales
require_once 'lib/dbProcedures/login_func.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/guichet_lib.php';

if (get_sess_status(session_id()) == 0) { //Si on vient de login.php
  $global_nom_ecran = $start_screen; //Pour menu_gen qui peut être chargé avant mainframe et qui va chercher la valeur dans global_nom_ecran

  $valeurs = get_login_info(session_id()); //On va chercher toutes les infos concernant le poste client

  $global_agence = $valeurs['libel_ag'];
  $global_id_agence = $valeurs['id_ag'];
  $global_guichet = $valeurs['libel_guichet'];
  $global_id_guichet = $valeurs['id_guichet'];
  $global_id_utilisateur = $valeurs['id_utilisateur'];
  $global_nom_utilisateur = $valeurs['nom_utilisateur'];
  $global_conn_agc = $valeurs['conn_agc'];
  $global_nom_login = $valeurs['login'];
  $global_monnaie = $valeurs['monnaie'];
  $global_monnaie_prec = $valeurs['monnaie_prec'];
  $global_monnaie_courante_prec = $valeurs['monnaie_prec'];
  $global_monnaie_courante = $valeurs['monnaie'];
  $global_multidevise = $valeurs['multidevise'];
  $global_id_profil = $valeurs['id_profil'];
  $global_billet_req = $valeurs['billet_req'];
  $global_billets = $valeurs['billets'];
  $global_statut_agence = $valeurs['statut_ag'];
  $global_timeout = $valeurs['timeout'];
  $global_last_axs = time();
  $global_profil_axs = $valeurs['profil_axs'];
  $global_menus_struct = get_menus_struct();
  $global_ecrans_struct = get_ecrans_struct();
  $global_institution = $valeurs['institution'];
  $global_type_structure = $valeurs['type_structure'];
  $global_have_left_frame = $valeurs['have_left_frame'];
  $global_id_exo = $valeurs['exercice'];
  $global_langue_systeme_dft = $valeurs['langue_systeme_dft'];
  $global_langue_utilisateur = $valeurs['langue'];
  //nom de fichier  des frames
  $extra_frame="../extra_gen/extra_gen.php?m_agc=".$_REQUEST['m_agc'];
  $menu_frame="../menu_gen/menu_gen.php?m_agc=".$_REQUEST['m_agc'];
  //si la durée de mot de passe est renseigné,verifiez si le mot mot de passe est expiré
  $AG=getAgenceDatas($valeurs['id_ag']);
  $diff_jour=nbreDiffJours(pg2phpDate($valeurs['date_mod_pwd']),date("d/m/Y"));//nbre de jour depuis le dernier changement du mot de passe
  if($AG['duree_pwd']>0 &&
	   $diff_jour>$AG['duree_pwd'] &&
	   $valeurs['pwd_non_expire']!='t' ){ // traitement de modification du mot de passse lors de la connexion
		 $start_screen="Mdp-0";
     //nom de fichier  des frames
  	$extra_frame="../login/left_login.php";
  	$menu_frame="../login/left_login.php";
    $global_modif_pwd_login=true;

  }
  $global_nom_ecran = $start_screen; //Pour menu_gen qui peut être chargé avant mainframe et qui va chercher la valeur dans global_nom_ecran
  
  //Vérifie sil s'agit d'un caissier central
  $global_caissier_central = isCaisseCentrale($global_id_guichet, $global_profil_axs);

  set_sess_status(session_id(), 1);
} else {
  // Normalement, main.php est chargé 1 seule fois lors de l'initialisation de la session ADbanking
  // Si c'est un rechargement de page alors on considère que la session est perdue
  $global_nom_ecran = "Pse-1";
  $start_screen = "Pse-1";
}


//Document HTML proprement dit :
//En-tête doc
echo "<html><head><title>$ProjectName</title></head>";

//Division haut/bas
//echo '<frameset rows="80,*" border='.$bord_horz_size.' frameborder="yes" bordercolor='.$col_bord_horz.'>';
echo '<frameset rows="80,*" frameborder=$screen_frameborder border=$screen_border framespacing=$screen_framespacing bordercolor=$col_bord marginheight=$screen_margin_height marginwidth=$screen_margin_width>';

//Définition frame du haut (statut)
echo '<frame name="status_frame" noresize scrolling="no" src="../status_gen/status_gen.php?m_agc='.$_REQUEST['m_agc'].'">';

if ($valeurs['have_left_frame']) {
  //Division gauche/droite dans le bas
  echo '<frameset cols="250,*">';

  //Définition frame gauche, division haut/bas
  echo '<frameset rows="*,50">';
  echo '<frame name="menu_frame" noresize scrolling="no" src="../menu_gen/menu_gen.php?m_agc='.$_REQUEST['m_agc'].'">';
  echo '<frame name="extra_frame" noresize scrolling="no" src="../extra_gen/extra_gen.php?m_agc='.$_REQUEST['m_agc'].'">';
  echo '</frameset>';

  //Définition frame droit (principal)
  echo '<frame name="main_frame" noresize src="../mainframe/mainframe.php?m_agc='.$_REQUEST['m_agc'].'&prochain_ecran='.$start_screen.'">';

  //Fin division gauche/droite
  echo "</frameset>";
} else {
  //Division haut/bas
  echo '<frameset rows="*,50">';
  //Définition frame droit (principal)
  echo '<frame name="main_frame" noresize src="../mainframe/mainframe.php?m_agc='.$_REQUEST['m_agc'].'&prochain_ecran='.$start_screen.'">';
  //Définition du frame "quitter"
  echo '<frame name="extra_frame" noresize scrolling="no" src="../extra_gen/extra_gen.php?m_agc='.$_REQUEST['m_agc'].'">';
  echo '</frameset>';
}

// On ferme la session explicitement pour pouvoir faire des flush() {@link PHP_MANUAL#flush}
// dans le frame principal lors des longs traitements (ouverture d'agence).
session_write_close();

//Fin division haut/bas
echo "</frameset>";

//Fin doc
echo "<noframes><body><p>"._("ADbanking nécessite un navigateur supportant les frames.")."</p></body></noframes></html>";

?>
