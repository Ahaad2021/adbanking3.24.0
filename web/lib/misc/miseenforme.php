<?php
require_once 'lib/misc/divers.php';

function mise_en_forme_MONTANT_LIE(&$post) {
  $champs_lie=array();
  foreach ($post as $variable => $valeur)
  // Champs 'devise variable': détection des champs
  // attention ! l'opérateur !== ne correspond pas à != et l'inverse n'est pas ===
  if (strpos($variable,"MONTANT_LIE_") !== false) //Si trouvé
    $champs_lie[substr($variable,strlen("MONTANT_LIE_"))] = $valeur;

  if (count($champs_lie)>0) {
    ksort($champs_lie);
    reset($champs_lie);
    $lie = key($champs_lie);
    $post[$lie] = array( 	"cv"		=> recupMontant($post[$lie]),
                          "devise"	=> $champs_lie[$lie],
                          "comm_nette"	=> $champs_lie["${lie}_comm_nette"],
                          "taux"		=> $champs_lie["${lie}_taux"],
                          "dest_reste"	=> $champs_lie["${lie}_dest_reste"],
                          "reste"		=> $champs_lie["${lie}_reste_hidden"]
                       );

  }

  foreach ($post as $key => $poubelle )
  if (strpos($key,"MONTANT_LIE_") !== false ) // Si trouvé
    unset($post["$key"]);
};

function mise_en_forme_HTML_GEN2(&$post) {
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Champs de devise variable
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $champs_dvr=array();
  foreach ($post as $variable => $valeur)
  // Champs 'devise variable': détection des champs
  // attention ! l'opérateur !== ne correspond pas à != et l'inverse n'est pas ===
  if (strpos($variable,"HTML_GEN_dvr_") !== false) //Si trouvé
    $champs_dvr[substr($variable,strlen("HTML_GEN_dvr_"))] = $valeur;

  $save_nombre_entrees=count($champs_dvr)+1;
  while ((count($champs_dvr) > 0) && ($save_nombre_entrees>count($champs_dvr))) {	// Tant qu'on a trouvé des champs DVR...

    $save_nombre_entrees=count($champs_dvr);
    ksort($champs_dvr);
    reset($champs_dvr);
    $dvr = key($champs_dvr); // ShortName du DVR

    $post[$dvr] = array(	"cv" 		=> recupMontant($post[$dvr]),
                         "devise"	=> $champs_dvr[$dvr],
                         "comm_nette"	=> $champs_dvr["${dvr}_comm_nette"],
                         "taux"		=> $champs_dvr["${dvr}_taux"],
                         "dest_reste"	=> $champs_dvr["${dvr}_dest_reste"],
                         "reste"		=> $champs_dvr["${dvr}_reste_hidden"]
                       );

    unset($champs_dvr[$dvr],$champs_dvr["${dvr}_comm_nette"],$champs_dvr["${dvr}_taux"],
          $champs_dvr["${dvr}_dest_reste"],$champs_dvr["${dvr}_reste_hidden"]);
  }

  if (count($champs_dvr) > 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "POST incorrect"

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Champs "texte traduits"
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  foreach ($post as $variable => $valeur)
  if (strpos($variable,"HTML_GEN_ttr_strid_") !== false) {
    $variable = substr($variable,strlen("HTML_GEN_ttr_strid_"));
    $post["$variable"] = recup_champ_ttr($variable, $post);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Champs "date"
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  foreach ($post as $variable => $valeur)
  // Traitement des champs 'date'
  if (strpos($variable,"HTML_GEN_date_") !== false) { //Si trouvé
    $variable = substr($variable,strlen("HTML_GEN_date_"));
    $post["$variable"]=localiser_date($valeur);
  }

  reset($post);
  foreach ($post as $variable => $valeur)
  // Traitement des champs 'LSB' (ListBox)
  // Les valeurs 0 correspondent en fait à une absence de choix
  if (strpos($variable,"HTML_GEN_LSB_") !== false) { //Si trouvé
    $variable = substr($variable,strlen("HTML_GEN_LSB_"));
    $post["$variable"] = ($valeur == "0"? NULL : $valeur);
  }
  reset($post);
  foreach ($post as $variable => $valeur)
  // Champs de type BOL (Cases à cocher)
  // La valeur "on" correspond à true, toute autre valeur à false
  if (strpos($variable,"HTML_GEN_BOL_") !== false) { //Si trouvé
    $variable = substr($variable,strlen("HTML_GEN_BOL_"));
    $post["$variable"] = ($valeur == "on"? true : false);
  }

  // Champs image (HTML_GEN_IMG)
  // Ces champs sont mis dans un talbeau global $IMAGES pour accès plus aisé
  reset($post);
  global $IMAGES;
  foreach ($post as $variable => $valeur)
  if (strpos($variable,"HTML_GEN_IMG_") !== false) { //Si trouvé
    $variable = substr($variable,strlen("HTML_GEN_IMG_"));
    $IMAGES["$variable"] = $valeur;
  }

  // Nettoyage des éventuels résidus
  foreach ($post as $key => $poubelle )
  if (strpos($key,"HTML_GEN_") !== false) // Si trouvé
    unset($post["$key"]);
}

