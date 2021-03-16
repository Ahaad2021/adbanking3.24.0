<?php

/**
 * Gestion de la locale
 * @package Multilingue
 */

require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/multilingue.php';
require_once 'lib/multilingue/convertdate.php';

reset_langue();

function reset_langue()
// IN: -
// OUT: le code de la langue dans laquelle l'interface sera affichée
// EFFET : Si la variable global_langue_utilisateur est définie, on affiche l'interface dans cette langue, sinon
//         elle sera affichée dans la langue système par défaut
{
  global $global_langue_utilisateur;

  if (isset($global_langue_utilisateur))
    $langue_affichage = $global_langue_utilisateur;
  else
    $langue_affichage = get_langue_systeme_par_defaut();

  changer_langue($langue_affichage);

  return $langue_affichage;
};

/**
 * Pour changer la langue dans laquelle l'affichage est fait.
 * @param str $langue_affichage Le code de la langue dans laqulle on veut l'interface
 * @return null
 */
function changer_langue($langue_affichage) {
  global $doc_prefix;

  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $codeset = "UTF8";  // warning ! not UTF-8 with dash '-' 
    
    // set locale 
    $domain = "adbanking";
    bindtextdomain($domain, "$doc_prefix/locale/"); 
    setlocale(LC_ALL, "C");
    setlocale(LC_CTYPE, "$langue_affichage.$codeset", 'French_France.1252');
    if(setlocale(LC_MESSAGES, "$langue_affichage.$codeset", 'French_France.1252') == false)
    {
        putenv("LC_MESSAGES=French_France");
    }
    setlocale(LC_TIME, "$langue_affichage.$codeset", 'French_France.1252');
    textdomain($domain);
    /*
    echo getenv("LC_MESSAGES");
    
    $originalLocales = explode(";", setlocale(LC_ALL, 0));
  
    echo "<pre>";
    print_r($originalLocales);
    echo "</pre>";
    exit;
    */
  }
  else{
    if (setlocale(LC_ALL, "C") == false) // Afin de garder les chiffres décimaux en format "1.23" pour l'instant
      die (_("Locale C non implementée"));

    if (setlocale(LC_MESSAGES, "$langue_affichage.UTF-8") == false)
      die (sprintf(_("Locale %s non implementée"),$langue_affichage));
    putenv("LC_MESSAGES=".$langue_affichage);

    if (setlocale(LC_TIME, "$langue_affichage.UTF-8") == false)
      die (sprintf(_("Locale %s non implementée"),$langue_affichage));

    $domain = "adbanking";
    bindtextdomain($domain, "$doc_prefix/locale");
    textdomain($domain);
    bind_textdomain_codeset($domain,'UTF-8');
  }

  require 'lib/misc/tableSys.php'; // On relit adsys dans la bonne langue
};

function basculer_langue_rpt() {
  global $global_langue_rapport;
  changer_langue($global_langue_rapport);
};

?>