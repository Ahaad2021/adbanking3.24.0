<?php

/**
 * Gestion des dates
 * @package Multilingue
 */

function convert_date($date)
// Convertit une date du format JJ/MM/AAAA en MM/JJ/AAAA (et vice-versa)
// plutôt passer par localiser_date !!!
{
  if ($date == "")
    return "";
  list($j,$m,$a) = explode("/",$date);
  return "$m/$j/$a";
};

function convert_date_to_jjmmaaaa($date)
// Convertit une date du format MM/JJ/AAAA en JJ/MM/AAAA (et vice-versa)
// plutôt passer par localiser_date !!!
{
  if ($date == "")
    return "";
  list($m,$j,$a) = explode("/",$date);
  return "$j/$m/$a";
};

function locale_mmjjaaaa($locale="") {
  global $global_langue_utilisateur;
  if ($locale == "")
    $locale = $global_langue_utilisateur;
  return ($locale == 'false'); //en_GB//Utilisation de cette fonction a été modifié dans le cadre du ticket AT-117
  //Quel que soit la langue de l'utilisateur, le format de la date restera en jour/mois/année
};

function localiser_date($date) {
  if (locale_mmjjaaaa())
    return convert_date($date);
  else
    return $date;
}

function localiser_date_rpt($date) {
  global $global_langue_rapport;
  if (!isset($global_langue_rapport))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // La variable global_langue_rapport n'est pas définie

  if (locale_mmjjaaaa($global_langue_rapport))
    return convert_date($date);
  else
    return $date;
};

/**
 * Formate une chaîne de caractère suivant une date à répétition.
 *
 * Ex: tous les mardis du mois, tous les 18/12, etc.  Peut aussi retrourner une chaîne vide.
 * Si un paramètre a une valeur de 0, il est interprété comme "toutes les valeurs possibles".
 * @param int $jour_semaine jour de la semaine (0 à 7)
 * @param int $jour jour du mois (0 à 31)
 * @param int $mois mois de l'année (0 à 12)
 * @param int $annee année (1995 à 2025)
 * @return str chaîne décrivant la répétition de date.
 */
function date_to_texte($jour_semaine, $jour, $mois, $annee) {
  global $adsys;
  $date = localiser_date("$jour/$mois/$annee");  // date jj/mm/aaaa ou mm/jj/aaaa
  $date_short = substr($date, 0, strrpos($date, "/")); // date sans la partie aaaa
  if ($jour == 0 && $mois == 0 && $annee == 0)
    // Un jour de la semaine
    return _("Tous les")." ".$adsys["adsys_jour_semaine_pluriel"][$jour_semaine];
  if ($annee == 0 && $jour_semaine == 0 && $mois != 0)
    // Un jour de l'année
    return _("Tous les")." ".$date_short;
  if ($annee == 0 && $jour_semaine == 0 && $mois == 0)
    return _("Tous les")." ".$jour;
  if ($jour != 0 && $mois != 0 && $annee != 0)
    // Un jour précis
    return $date;
  // Cas général
  return "";
}

?>