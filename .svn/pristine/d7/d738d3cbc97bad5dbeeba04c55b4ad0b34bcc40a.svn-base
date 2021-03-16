<?php
/* Fonctions génériques pour la génération de l'XML des rapports */
require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/domxml-php4-to-php5.php';
require_once 'lib/misc/divers.php';

/**
 * Génère un en-tete XML commun à tous les rapports et reçus
 * @author Unknown
 * @param &XMLNode $root Racine du document XML
 * @param char(7) $code_rapport Code du type de rapport (définis dans adsys_rapports)
 * @param text $info_suppl String éventuel à afficher après le titre du rapport
 * @return text Référence du rapport / reçu
 */
function gen_header(&$root, $code_rapport, $info_supp="") {

  global $global_agence, $global_nom_utilisateur, $global_nom_login, $global_institution, $adsys, $global_id_agence;

  if (substr($code_rapport, 0, 3) == 'REC') {
    // Il s'agit d'un reçu
    global $global_id_guichet, $dbHandler;
    $db = $dbHandler->openConnection();
    //Vérifier si le login a un guichet
    if($global_id_guichet){
    	$GUI = get_guichet_infos($global_id_guichet);
	    $num_recu = $GUI["last_num_recu"] + 1;
	    $sql = "UPDATE ad_gui SET last_num_recu = last_num_recu + 1 WHERE id_ag=$global_id_agence AND id_gui = $global_id_guichet";
	    $result = $db->query($sql);
	    if (DB::isError($result)) {
	      $dbHandler->closeConnection(false);
	      signalErreur(__FILE__,__LINE__,__FUNCTION__);
	    }
	    $dbHandler->closeConnection(true);
	    $ref = sprintf("%02d-%06d", $global_id_guichet, $num_recu);
    }
  } else {
    // Il s'agit d'un rapport
    $ref = $code_rapport;
  }

  $infos = getAgenceDatas ($global_id_agence);

  $header = $root->new_child("header", "");
  $footer = $root->new_child("footer", "");
  $PATHS=imageLocationLogo();
  $image_logo = $PATHS['logo_chemin_local'];
  if(file_exists($image_logo)){
  	$header->new_child("logo_existe", "true");
  }else{
  	$header->new_child("logo_existe", "false");
  }
  $header->new_child("logo_ag", $image_logo);
  $header->new_child("institution", $global_institution);
  $header->new_child("agence", $infos["libel_ag"]);
  $header->new_child("telephone", $infos['tel']);
  $footer->new_child("email", $infos['email']); 
 	$footer->new_child("num_agrement", $infos['num_agrement']); 
 	$footer->new_child("num_tva", $infos['num_tva']); 
 	$footer->new_child("code_swift_banque", $infos['code_swift_banque']); 
  $header->new_child("date", date("d/m/Y"));
  $header->new_child("heure", date("H:i"));
  $header->new_child("heure", date("H:i"));
  $header->new_child("utilisateur", $global_nom_login);//Au lieu du nom on imprime le code utilisateur Voir #1960.
  $header->new_child("idrapport", $ref);
  $header->new_child("titre", adb_gettext($adsys['adsys_rapport'][$code_rapport]).$info_supp);
  //Coordonnées et réfèrences à afficher
  $footer->new_child("adresse", $infos['adresse']);
  $footer->new_child("fax", $infos['fax']);
  $footer->new_child("telephone", $infos['tel']);
  if($infos['imprim_coordonnee']=='t')
     $footer->new_child("affiche", "true");


  return $ref;
}

function gen_criteres_recherche(&$root, $list_criteres) { //Génère la partie critères de recherche
  //la liste des critères est un tableau associatif : champs=>valeur

  $criteres = $root->new_child("criteres_recherche","");

  reset($list_criteres);
  while (list($key, $value) = each($list_criteres)) {
    $critere = $criteres->new_child("critere","");
    $critere->new_child("champs", $key);
    $critere->new_child("valeur", $value);
  }
}

function gen_informations_synthetiques(&$root, $list_criteres) { //Génère la partie critères de recherche
  //la liste des critères est un tableau associatif : champs=>valeur

  $criteres = $root->new_child("informations_synthetiques","");

  reset($list_criteres);
  while (list($key, $value) = each($list_criteres)) {
    $critere = $criteres->new_child("critere","");
    $critere->new_child("champs", $key);
    $critere->new_child("valeur", $value);
  }
}


function gen_research_criteria(&$root, $list_criteres) { //Génère la partie critères de recherche en Anglais(research_criteria)
  //la liste des critères est un tableau associatif : champs=>valeur

  $criteres = $root->new_child("research_criteria","");

  reset($list_criteres);
  while (list($key, $value) = each($list_criteres)) {
    $critere = $criteres->new_child("critere","");
    $critere->new_child("champs", $key);
    $critere->new_child("valeur", $value);
  }
}

function create_xml_doc($root_element, $dtd=NULL) {
  global $doc_prefix;

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
  if (isset($dtd))
    $xml .= "<!DOCTYPE $root_element SYSTEM \"file://$doc_prefix/rapports/dtd/$dtd\">";
  $xml .= "<$root_element></$root_element>";

  if (function_exists("xmldoc"))
    return xmldoc($xml);
  else
    die(_("DOMXML non installé"));
}

/**
 * Parseur XML
 *
 * @copyright http://www.galle.fr/dev/parserxml.php
 */
function GetChildren ($vals, &$i, $type) {
  if ($type == 'complete') {
    if (isset ($vals [$i]['value']))
      return ($vals [$i]['value']);
    else
      return '';
  }

  $children = array (); // Contains node data

  /* Loop through children */
  while ($vals [++$i]['type'] != 'close') {
    $type = $vals [$i]['type'];
    // first check if we already have one and need to create an array
    if (isset ($children [$vals [$i]['tag']])) {
      if (is_array ($children [$vals [$i]['tag']])) {
        $temp = array_keys ($children [$vals [$i]['tag']]);
        // there is one of these things already and it is itself an array
        if (is_string ($temp [0])) {
          $a = $children [$vals [$i]['tag']];
          unset ($children [$vals [$i]['tag']]);
          $children [$vals [$i]['tag']][0] = $a;
        }
      } else {
        $a = $children [$vals [$i]['tag']];
        unset ($children [$vals [$i]['tag']]);
        $children [$vals [$i]['tag']][0] = $a;
      }

      $children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
    } else
      $children [$vals [$i]['tag']] = GetChildren ($vals, $i, $type);
    // I don't think I need attributes but this is how I would do them:
    if (isset ($vals [$i]['attributes'])) {
      $attributes = array ();
      foreach (array_keys ($vals [$i]['attributes']) as $attkey)
      $attributes [$attkey] = $vals [$i]['attributes'][$attkey];
      // now check: do we already have an array or a value?
      if (isset ($children [$vals [$i]['tag']])) {
        // case where there is an attribute but no value, a complete with an attribute in other words
        if ($children [$vals [$i]['tag']] == '') {
          unset ($children [$vals [$i]['tag']]);
          $children [$vals [$i]['tag']] = $attributes;
        }
        // case where there is an array of identical items with attributes
        elseif (is_array ($children [$vals [$i]['tag']])) {
          $index = count ($children [$vals [$i]['tag']]) - 1;
          // probably also have to check here whether the individual item is also an array or not or what... all a bit messy
          if ($children [$vals [$i]['tag']][$index] == '') {
            unset ($children [$vals [$i]['tag']][$index]);
            $children [$vals [$i]['tag']][$index] = $attributes;
          }
          $children [$vals [$i]['tag']][$index] = array_merge ($children [$vals [$i]['tag']][$index], $attributes);
        } else {
          $value = $children [$vals [$i]['tag']];
          unset ($children [$vals [$i]['tag']]);
          $children [$vals [$i]['tag']]['value'] = $value;
          $children [$vals [$i]['tag']] = @array_merge ($children [$vals [$i]['tag']], $attributes);
        }
      } else
        $children [$vals [$i]['tag']] = $attributes;
    }
  }
  return $children;
}

/**
 * Parseur XML
 *
 * @param array $xmldata
 * @param Booleen $entete
 * @return unknown
 * @copyright http://www.galle.fr/dev/parserxml.php
 * @since mai 2007
 * @version 2.10
 * @author Stefano A.
 */
function GetXMLTree ($xmldata,$entete=true) {
  if ( ! $entete)
    $xmldata = "<AZERTYUIOPQSDFGHJKL>".$xmldata."</AZERTYUIOPQSDFGHJKL>";
  // we want to know if an error occurs

  ini_set ('track_errors', '1');

  $xmlreaderror = false;

  $parser = xml_parser_create ('ISO-8859-1');
  xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
  /**
   * Tableau de valeurs à passer en référence à {@see xml_parse_into_struct}
   */
  $vals = array ();
  /**
   * Tableau d'index à passer en référence à {@see xml_parse_into_struct}
   */
  $index = array ();
  if (!xml_parse_into_struct($parser, $xmldata, $vals, $index)) {
    $xmlreaderror = true;
    echo "-1";
  }
  xml_parser_free($parser);

  if (!$xmlreaderror) {
    $result = array ();
    $i = 0;
    if (isset ($vals [$i]['attributes'])){
      foreach (@array_keys ($vals [$i]['attributes']) as $attkey)
      $attributes [$attkey] = $vals [$i]['attributes'][$attkey];
      $result [$vals [$i]['tag']] = @array_merge ($attributes, GetChildren ($vals, $i, 'open'));
    }
    else
      $result [$vals [$i]['tag']] = GetChildren ($vals, $i, 'open');
  }
  ini_set ('track_errors', '0');
  if (!$entete)
    $result = $result['AZERTYUIOPQSDFGHJKL'];
  return $result;
}

/**
 * Transformation des fichier XML en tableau PHP
 *
 * @param text $xmlfile : Nom du fichier xml
 * @return array contenant les données du fichier XML
 * @since  Mai 2007
 * @version 2.10
 * @author Stefano A.
 */
function traiteFichierXML($xmlfile,$entete=true) {
  $fp = fopen($xmlfile,"r");
  while (!feof($fp)) {
    $donnees_xml .= fgets($fp);
  }
  fclose($fp);
  return GetXMLTree($donnees_xml,$entete);
}
/**
 * @author Saourou
*  Return a file list transactions qui sont dans le répertoire echange/ferlo
*
* @param $dir the target directory
* @param $getExt if you want get just one type of files (ex : php or .php)
*
* @return bool|array  false on fail files array on success
*/
function listFiles_trans( $dir, $getExt = '') {
  if (!is_dir($dir))
    return false;
  elseif(substr($dir,-1) !== DIRECTORY_SEPARATOR)
  $dir .= DIRECTORY_SEPARATOR;

  if (!empty($getExt) && $getExt[0] !== '.')
    $getExt = '.'.$getExt;

  $ret = array();
  foreach(glob($dir.'*'.$getExt, GLOB_NOSORT) as $contents) {
    if (is_file($contents) && strncmp(str_replace($dir,'',$contents),'Trans',5) == 0 )
      $ret[] = $contents;
  }
  return $ret;
}
/**
 * @author Saourou
 * Fonction permettant de passer les opérations comptables
 * Créditer ou débiter un compte
 * @param $XMLarray tableau contenant les données des transactions
 */
function ecrituresCompbleXml($XMLarray,$is_batch=true) {
  global $global_id_guichet,$transaction_ferlo;
  if (is_array($XMLarray['XMLFILE']['transaction']))
    foreach($XMLarray['XMLFILE']['transaction'] as $cle => $valeur) {
    if ($is_batch==true)
      array_push($transaction_ferlo,$valeur);	//Récupèration des transaction pour le rapport de batch
    $id_cpte = getBaseAccountID($valeur['codeTitulaire']);
    $InfoCpte = getAccountDatas($id_cpte);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
    $SESSION_VARS['id_mandat'] = $valeur['numTransaction'];
   // debug($valeur,"tableau des données");
    switch ($valeur['typeTransaction']) {
    case '01': //Retrait
      $cptes_substitue["int"] = array();
      $cptes_substitue["cpta"] = array();
      $comptable=array();
      //Passage d'écriture
      $myErr =  passageEcrituresComptablesAuto(142,$valeur["montant"],$comptable,$cptes_substitue);
      if ($myErr->errCode != NO_ERR) {

        return $myErr;
      }
      $myErr = ajout_historique(142,NULL,$InfoProduit['id'], 'admin', date("r"), $comptable, NULL,NULL);
      if ($myErr->errCode != NO_ERR) {

        return $myErr;
      }
      break;
    case '02': //Dépot
    case '03': //Payement

      $cptes_substitue["int"] = array();
      $cptes_substitue["cpta"] = array();
      $comptable = array();
      $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
      $cptes_substitue["int"]["credit"]  = $id_cpte;
      $myErr = passageEcrituresComptablesAuto(162,$valeur["montant"],$comptable,$cptes_substitue);
      if ($myErr->errCode != NO_ERR) {

        return $myErr;
      }
      $myErr = ajout_historique(162,NULL,$InfoProduit['id'], 'admin', date("r"), $comptable, NULL,NULL);
      if ($myErr->errCode != NO_ERR) {

        return $myErr;
      }
      break;

    default:
    }
  }
}
?>