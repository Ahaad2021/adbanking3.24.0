<?php

// dernier_rapport.php - Contient l'écran permettant d'afficher le dernier rapport
// TF - 20/08/2002
// thomas.fastenakel@aquadev.org

require_once 'modules/rapports/xslt.php';

if ($global_nom_ecran == "Dra-1") { // Affichage du dernier rapport
  echo get_show_pdf_html("Gen-13");
} else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"