<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Génère le menu (frame gauche)
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/multilingue/traductions.php'; // La classe doit être déclarée avant l'ouverture de la session
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/access.php';

function recherche_menu_fils($menu) { //Recherche le fils si possible, sinon renvoie $menu
  global $global_menus_struct;

  reset($global_menus_struct);
  while (list($key, $value) = each($global_menus_struct)) {
    if ($value['nom_pere'] == $menu) return $key; //On prend le 1er fils trouvé : sans importance
  }

  return $menu;
}

function recherche_frere($menu_pere) {
  global $global_menus_struct;

  $result = array();
  reset($global_menus_struct);
  while (list($key, $value) = each($global_menus_struct)) {
    if ($value['nom_pere'] == $menu_pere) {
      array_push($result, $value);
    }
  }

  return $result;
}

function affiche_menu($p_menu, $niv, $is_menu, $nom_menu, $nivfin) {
  /* Affiche le menu, cette fonction fait des appels récursifs pour chaque sous-menu
  	$p_menu : contient le menu, array dynamique dont une case est lui-même un array dynamique contenant le sous-menu etc...
  	$niv : Niveau auquel on se situe (départ = 1)
  	$is_menu : Le niveau que l'on va afficher est-il un menu (ou bien le dernier niveau c.à.d. une suite d'écrans)
  	$nom_menu : nom du menu sur lequel on se situe
  	$niv_fin : si niv_fin[i] = vrai alors le dernier item du niveau i a été affiché
  						(et donc il ne faut plus dessiner de ligne horizontale à ce niveau)
  */
  global $colst_menu;
  global $http_prefix;
  global $SERVER_NAME;

  for ($i=0; (isset($p_menu[$i]['nom_ecran'])); ++$i) {
    //Affiche les icones
    for ($j=2; $j<=$niv-1; ++$j) {
      if ($nivfin[$j] != true) echo "<IMG SRC=\"$http_prefix/images/line.gif\" BORDER=0>";
      else echo "<IMG SRC=\"$http_prefix/images/vide.gif\" BORDER=0>";
    }
    if ($is_menu) {
      if ($niv > 1) {
        if (isset($p_menu[$i+1]['nom_ecran'])) echo "<IMG SRC=\"$http_prefix/images/lineT.gif\" BORDER=0>";
        else {
          echo "<IMG SRC=\"$http_prefix/images/lineL.gif\" BORDER=0>";
          $nivfin[$niv] = true;
        }
      }
      if ($p_menu[$i]['is_cliquable']) {
        $lien_prochain_ecran=$p_menu[$i]['nom_ecran'];
        if (strpos($lien_prochain_ecran,"-") === false)
          $lien_prochain_ecran.="-1";
        echo "<A HREF=\"$SERVER_NAME/mainframe/mainframe.php?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=$lien_prochain_ecran\" target=\"main_frame\"> ";
      }
      if (isset($p_menu[$i]['sous-menu'])) echo "<IMG SRC=\"$http_prefix/images/open.gif\" BORDER=0>"; //S'il contient un sous-menu
      else echo "<IMG SRC=\"$http_prefix/images/close.gif\" BORDER=0>";
    } else {
      echo "<IMG SRC=\"$http_prefix/images/vide.gif\" BORDER=0>";
      echo "<IMG SRC=\"$http_prefix/images/vide.gif\" BORDER=0>";
      if (isset($p_menu[$i+1]['nom_ecran'])) echo "<IMG SRC=\"$http_prefix/images/lineT.gif\" BORDER=0>";
      else {
        echo "<IMG SRC=\"$http_prefix/images/lineL.gif\" BORDER=0>";
        $nivfin[$niv] = true;
      }
    }

    //Affiche le texte
    if ($p_menu[$i]['nom_ecran'] == $nom_menu) echo "<font color=$colst_menu>"; //Si c'est la position courante : couleur spéciale
    echo "  ".$p_menu[$i]['libel_menu']->traduction();
    if (($is_menu) && ($p_menu[$i]['is_cliquable'])) echo"</A>";

    echo "<br>";
    if ($p_menu[$i]['nom_ecran'] == $nom_menu) echo "</font>"; //Si c'était le couleur courante : on revient en font normal

    //Affiche les sous-menus si existants
    if (isset($p_menu[$i]['sous-menu']))
      affiche_menu($p_menu[$i]['sous-menu'], $niv+1, $p_menu[$i]['sous-menu'][0]['is_menu'], $nom_menu, $nivfin);
  }
}

/*--------------L'exécution du module commence ici : on recherche d'abord la structure du menu avant de l'afficher------------*/
$menu = array();

//Etape 1 : construire le menu
if (! isset($global_ecrans_struct[$global_nom_ecran])) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu"

$current_menu = $global_ecrans_struct[$global_nom_ecran]; //Recherche du menu associé à l'écran
$current_low_menu = recherche_menu_fils($current_menu); //Recherche le fils si possible (sinon on reste sur le menu courant
$level = $global_menus_struct[$current_low_menu]['pos_hierarch']; //Recherche le niveau hierarchique du menu père

for ($current_screen = $current_low_menu; $level>0; --$level) { //On remonte du fils de l'écran actuel jusqu'à la racine
  $pere_commun = $global_menus_struct[$current_screen]['nom_pere']; //Recherche le pere du menu

  $result = recherche_frere($pere_commun);
  reset($result);
  for ($j=0; (list(,$current_result) = each($result)); ++$j) { //Pour chaque résultat on sauve
//    if ((! $global_menus_struct[$current_result['nom_menu']]['fonction']) || //Si aucune vérification d'accès nécessaire
//	(in_array($global_menus_struct[$current_result['nom_menu']]['fonction'], $global_profil_axs))){//OU si on a accès à cet écran
    if (check_access($global_menus_struct[$current_result['nom_menu']]['fonction'])) {
      $niv {$level}[$j]['nom_ecran'] = $current_result['nom_menu'];
      $niv {$level}[$j]['libel_menu'] = $current_result['libel_menu'];
      $niv {$level}[$j]['is_menu'] = ($current_result['is_menu']=='t');
      $niv {$level}[$j]['is_cliquable'] = ($current_result['is_cliquable']=='t');
      if ($current_result['nom_menu'] == $current_screen) {
        if (isset($niv {$level+1})) {//Si il existe un niveau en dessous
          $niv {$level}[$j]['sous-menu'] = $niv {$level+1};
        }
      }
    } else {
      --$j; //On reste sur place
    }
  }

  $current_screen = $pere_commun;
  if ($level == 1) $menu = $niv {$level}; //Sauve le menu car on va sortir du scope de $niv{$level}
}


//Etape 2 : Afficher le menu
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"
    \"http://www.w3.org/TR/html4/loose.dtd\">";

//Définition en-tête et titre de la page
echo "<html>\n <head>\n\t<title>$ProjectName - "._("Menu")."</title>\n";
require_once 'lib/html/stylesheet.php';
echo "<META http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
echo "<META Http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n";
echo "<META Http-Equiv=\"Pragma\" Content=\"no-cache\">\n";
echo "<META Http-Equiv=\"Cache\" Content=\"no store\">\n";
echo "<META Http-Equiv=\"Expires\" Content=\"0\">\n";
echo "</head>\n";

echo "<HTML> <BODY TEXT=$colt_menu BGCOLOR=$colb_menu>\n";
echo "<p CLASS=\"menugauche\">";
affiche_menu($menu, 1, TRUE, $current_menu, NULL); //Affiche le menu
echo "</p></BODY></HTML>";

// On ferme la session explicitement pour pouvoir faire des flush() {@link PHP_MANUAL#flush}
// dans le frame principal lors des longs traitements (ouverture d'agence).
session_write_close();

?>