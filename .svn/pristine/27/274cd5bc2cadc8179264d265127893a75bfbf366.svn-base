<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Utilitaires pour la gestion multilingue
 * @package Multilingue
 */

require_once "lib/multilingue/traductions.php";
require_once 'lib/dbProcedures/multilingue.php';

/**
 * Insertion des objets Trad.
 *
 * @param string $table nom d'une table
 * @param &$vecteur vecteur associatif : nomchamp - data
 * @return Modifie le vecteur $vecteur, pour les champs traduits, remplace les identificateurs de string par des objets Trad.
 */
function table_get_traductions($table, &$vecteur) {
  foreach ($vecteur AS $champ => $data)
  if (is_champ_traduit($table,$champ) && (!is_trad($data)))
    $data = new Trad($data);
};

/**
 * Cette fonction récupère les valeurs des champs de type 'texte traduit', postés par un formulaire généré par HTML_GEN2
 * Elle ne devrait être appellée que depuis la fonction {@link #mise_en_forme_HTML_GEN2 mise_en_forme_HTML_GEN2}
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @param string $variable L'identifieur "shortName" passé à HTML_GEN2.
 * @param array $post le vecteur $_POST qui contient notemment les champs postés par le formulaire
 * @return objet "Trad"
 */
function recup_champ_ttr($variable,$post) {
  $retour = new Trad();

  foreach (get_langues_installees() as $langue => $poubelle)
  $retour->set_traduction($langue, $post["HTML_GEN_ttr_${variable}_${langue}"]);

  if (isset($post["HTML_GEN_ttr_strid_$variable"]))
    if ($post["HTML_GEN_ttr_strid_$variable"] != '')
      $retour->set_id_str($post["HTML_GEN_ttr_strid_$variable"]);
  return $retour;
};
/**
 * Cette fonction redéfinit gettext pour éviter de traduire les chaines vident qui dont la traduction renvoi l'entête (header) généré par Gettext
 * @author Ibou NDIAYE
 * @param string $chaine à traduire
 * @return String chaîne traduite ou NULL
 *   
 */
function adb_gettext($chaine=NULL){
	return ($chaine!='' && $chaine!=NULL)?_($chaine):$chaine;
}
?>