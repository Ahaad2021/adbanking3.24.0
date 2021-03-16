<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * HTML_TABLE_GEN
 * @author Olivier LUYCKX <olivier.luyckx@adfinance.org>
 * @version 1.00 (11/02/2005)
 * @package Ifutilisateur
 */

/**
 * Cette fonction renvoie une référence vers le dernier élément d'un tableau
 * @param array &last tableau à référencer
 * @return reference vers le dernier élément
 */
function &last(&$array) {
  if (!count($array))
    return null;
  end($array);
  return $array[key($array)];
}

////////////////////////////////////////////////////////////////////////////
// Classe HTML_TABLE_properties ////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////
//
// Cette classe recence toutes les méthodes et propriétés communes aux
// objets tables, lignes et cellules. Les tables, lignes et cellules
// récupèrent ces valeurs par héritage (mot clé extends)
//
class HTML_TABLE_properties {
  // {{{ properties

  var $protected_pere;			// Objet parent

  var $private_properties = array();	// Array contenant toutes les proprietes (class, bold, bgcolor, etc...)
  var $private_proprietes_enfants = array(); // Array avec les propriétés à faire hériter aux enfants
  var $private_child_number;		// Pendant un gen_HTML, =n°de l'enfant dont la génération HTML est en cours

  // }}}
  // {{{ constructor

  function HTML_TABLE_properties(&$pere) {
    // Constucteur. On sauve qui est le "père" de l'objet. Le père d'une table est NULL, le père d'une ligne
    //		est la table et le père d'une cellule est la ligne
    $this->set_pere($pere);
  }

  // }}}
  /*{{{ set_pere */

  function set_pere(&$pere) {
    $this->protected_pere =& $pere;
  }

  /*}}}*/

  function set_property($name, $value) {
    // Propriété locale de l'objet (table, ligne ou cellule)
    $this->private_properties[$name] = $value;
  }

  function set_child_property($name, $value) {
    // Propriétés des enfants de l'objets. Les enfants d'une table sont des lignes et les enfants d'une ligne
    // sont les cellules
    $this->private_proprietes_enfants[$name] = $value;
  }

  function zero_child_number() {
    // Lors de la génération de l'HTML, private_child_number vaut le numéro de l'objet fils qui est
    // en cours de génération
    // Cette fonction remet à zéro la variable private_child_number
    $this->private_child_number = 0;
  }

  function inc_child_number() {
    // Lors de la génération de l'HTML, private_child_number vaut le numéro de l'objet fils qui est
    // en cours de génération
    // Cette fonction incrémente la variable private_child_number
    $this->private_child_number++;
  }

  function get_child_number() {
    return $this->private_child_number;
  }

  function get_properties() {
    // Merge entre les propriétés locales et les propriétés héritées (child_property du parent)
    if ($this->protected_pere != NULL)
      $proprietes = array_merge($this->protected_pere->get_child_properties(),$this->private_properties);
    else
      $proprietes = $this->private_properties;

    // Merge entre les propriétés du style appliqué et les autres propriétés
    return array_merge($this->private_get_style_properties(), $proprietes);
  }

  function get_child_properties() {
    // Récupère les propriétés qui doivent être appliquées aux enfants.
    // Si la valeur d'une propriété est un array, le n ième enfant recoit la n ième valeur de l'array
    $retour = $this->private_proprietes_enfants;

    foreach ($retour AS $propriete => $valeur)
    if (is_array($valeur))
      $retour[$propriete] = $valeur[$this->get_child_number()%count($valeur)];
    return $retour;
  }

  function get_property($name) {
    // Récupère la propriété dont le nom est $name
    $properties = $this->get_properties();
    return $properties[$name];
  }

  function get_indent() {
    // Renvoie des tabulations, en fonction de la hiérarchie de l'objet (pour indentation du code HTML généré)
    if ($this->protected_pere == NULL)
      return ''; // Cas de l'objet HTML_TABLE
    else
      return $this->protected_pere->get_indent()."\t";
  }

  function get_style() {
    // Le style est stocké dans le parent de tous les parents, c'est à dire dans HTML_TABLE_table
    if ($this->protected_pere == NULL)
      return $this->private_style; // Retourne la variable défini dans l'objet HTML_TABLE_table
    else
      return $this->protected_pere->get_style();
  }
}

////////////////////////////////////////////////////////////////////////////
// Classe HTML_TABLE_table /////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////
// Cet objet représente une table HTML. Il hérite de l'objet
// HTML_TABLE_properties, c'est à dire que toutes les fonctions et attributs
// de HTML_TABLE_properties sont disponibles pour HTML_TABLE_table.
//
class HTML_TABLE_table extends HTML_TABLE_properties {
  var $private_rows;		// Array
  var $private_nb_colonnes;	// Nombres de colonnes de la table
  var $private_free_cols;		// Array, une ligne par ligne dans la table. Valeur=nbre de colonnes libres.
  var $private_style;		// Style prédéfini pour la mise en forme de la table

  function HTML_TABLE_table($nb_colonnes, $style = 0) {
    // Constructeur
    $null = NULL;
    parent::HTML_TABLE_properties($null);		// Appel du constructeur de HTML_TABLE_properties
    $this->private_style		= $style;
    $this->private_nb_colonnes	= $nb_colonnes;
    $this->private_add_row();			// On crée une ligne vide
  }

  function gen_HTML() {
    // Cette fonction génère le code HTML propre à la table, puis appelle les fonctions de génération du
    // code HTML de toutes les lignes

    $this->zero_child_number();						// Ligne numéro 0
    $html = $this->private_gen_html_titre();
    $html.= $this->private_gen_html_tag_table();
    foreach($this->private_rows AS $ligne)
    $html .= $ligne->gen_HTML();					// Génération code HTML des lignes
    $html.= $this->get_indent()."</table>\n";
    return $html;
  }

  function private_add_row() {
    // Ajouter une ligne à la table
    $this->private_rows[] 		= new HTML_TABLE_row($this);
    $this->private_free_cols[]	=  $this->private_nb_colonnes;
  }

  function add_cell($cell) {
    // Ajoute une cellule à la ligne en cours de construction
    $free_row =& $this->private_calc_free_cols($cell->get_property("rowspan"),
               $cell->get_property("colspan"));
    $free_row->add_cell($cell);
  }

  function &private_calc_free_cols($rowspan, $colspan) {
    // Cette fonction, appellée à chaque ajout de cellule, recalcule le nb de colonnes libres pour chaque ligne
    // de la table. Si la table ne contient pas assez de lignes, elle crée des lignes supplémentaires.
    // Retour : ligne dans laquelle la cellule doit être insérée

    // Recherche de la première ligne 'libre'
    reset($this->private_rows);
    reset($this->private_free_cols);
    while ((current($this->private_free_cols) !== false)  && (current($this->private_free_cols) < $colspan)) {
      next($this->private_free_cols);
      next($this->private_rows);
    }

    $premiere_ligne_libre = NULL;

    // Recalcul du nombre de cellules libres
    for ($i = 1; $i <= $rowspan; $i++) {
      if (current($this->private_free_cols) === false) {
        // Pas de ligne libre: on crée une nouvelle ligne
        $this->private_add_row();
        end($this->private_free_cols);
        end($this->private_rows);
      }

      if ($premiere_ligne_libre == NULL)
        $premiere_ligne_libre =& $this->private_rows[key($this->private_rows)];

      $this->private_free_cols[key($this->private_free_cols)] -= $colspan;

      next($this->private_free_cols);
      next($this->private_rows);
    }
    return $premiere_ligne_libre;
  }


  function &private_get_row_en_construction() {
    // Renvoie la ligne en cours de construction
    reset ($this->private_rows);
    reset ($this->private_free_cols);
    while ($this->private_free_cols[1+key($this->private_free_cols)] === 0) {
      // Tant que la ligne suivante existe ET qu'elle n'a plus de col libres(les 3= sont importants)
      next ($this->private_free_cols);
      next ($this->private_rows);
    }

    if (key($this->private_rows)+1 < count($this->private_rows)) {
      // Si on est pas à la dernière ligne (càd la ligne courante est complète et la suivante pas)
      $next_row =& $this->private_rows[1+key($this->private_rows)];
      if ($next_row->get_nb_cells() >= 1) {
        // Si la ligne suivante contient au moins une cellule
        next ($this->private_free_cols);
        next ($this->private_rows);
      }
    }
    return $this->private_rows[key($this->private_rows)];
  }

  function set_row_property($name, $value = '') {
    // Mettre à jour une proprité de l'objet ligne en cours de construction
    $free_row =& $this->private_get_row_en_construction();
    $free_row->set_property($name, $value);
  }

  function set_row_childs_property($name, $value = '') {
    // Mettre à jour une propriété de toutes les cellules de la ligne en cours de construction
    $free_row =& $this->private_get_row_en_construction();
    $free_row->set_child_property($name, $value);
  }

  function set_cell_property($name, $value = '') {
    // Mettre à jour une propriété de la dernière cellule construite
    $free_row =& $this->private_get_row_en_construction();
    $free_row->set_cell_property($name, $value);
  }

  function private_gen_html_tag_table() {
    // Génère le code HTML du tag <TABLE>
    // C'est ici qu'il faut ajouter le code pour les options qui doivent avoir un output dans le tag <table>
    $table = $this->get_indent().'<table';

    foreach ($this->get_properties() AS $propriete => $valeur)
    if ($valeur != "")
      switch ($propriete) {
      case "class"	:
        $table .= " class=\"$valeur\"";
        break;
      case "bgcolor"	:
        $table .= " bgcolor=\"$valeur\"";
        break;
      case "valign"	:
        $table .= " valign=\"$valeur\"";
        break;
      case "border"	:
        $table .= " border=\"$valeur\"";
        break;
      case "align"	:
        $table .= " align=\"$valeur\"";
        break;
      }
    $table.=">\n";
    return $table;
  }

  function private_gen_html_titre() {
    // Génération du titre de la table
    $titre = $this->get_indent();
    foreach ($this->get_properties() AS $propriete => $valeur)
    if ($propriete == "title")
      $titre = "<center><h1>$valeur</h1></center><br/><br/>";
    $titre.="\n";
    return $titre;
  }

  function get_style() {
    return $this->private_style;
  }

  function private_get_style_properties() {
    // Définit les propriétés de la table pour chaque style
    // C'est ici qu'il faut ajouter des propriétés, si nécessaire
    $proprietes = array();
    switch ($this->get_style()) {
    case TABLE_STYLE_CLASSIC:
      $proprietes["class"] = "tableclassic";
      break;
    case TABLE_STYLE_ALTERN:
      $proprietes["class"] = "tablealtern";
      break;
    }
    return $proprietes;
  }

}

//////////////////////////////////////////////////////////////////////////
// Classe HTML_TABLE_row /////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
//
// Cet objet représente une ligne d'une table HTML. Il hérite de l'objet
// HTML_TABLE_properties, c'est à dire que toutes les fonctions et attributs
// de HTML_TABLE_properties sont disponibles pour HTML_TABLE_row.
//
class HTML_TABLE_row extends HTML_TABLE_properties {
  var $private_cells = array();

  function HTML_TABLE_row(&$pere) {
    // Constructeur
    parent::HTML_TABLE_properties($pere);			// Appel du constructeur de HTML_TABLE_properties
  }

  function add_cell(&$cell) {
    // Ajoute une cellule à cette ligne
    $this->private_cells[] =& $cell;
    $cell->set_pere($this);
  }

  function get_nb_cells() {
    // Renvoie le nombre de cellules qui ont déjà été ajoutées à cette ligne (les rowspan ne comptent donc pas)
    return count ($this->private_cells);
  }

  function gen_HTML() {
    // Génère le code HTML de la ligne, puis appelle les fonctions de génération du code HTML de chaque cellule
    // de cette ligne
    $this->zero_child_number();						// Cellule n° 0
    $html = $this->private_gen_html_tag_tr();
    foreach($this->private_cells AS $cell)
    $html .= $cell->gen_HTML();
    $html.= $this->get_indent()."</tr>\n";
    $this->protected_pere->inc_child_number();				// On passe à la ligne suivante
    return $html;
  }

  function set_cell_property($name, $value) {
    // Mise à jour d'une propriété de la dernière cellule construite dans cette ligne
    $last_cell =& last($this->private_cells);
    if ($last_cell == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //erreur
    $last_cell->set_property($name, $value);
  }

  function private_gen_html_tag_tr() {
    $tr = $this->get_indent().'<tr';
    foreach ($this->get_properties() AS $propriete => $valeur)
    if ($valeur != "")
      switch ($propriete) {
      case "bgcolor"	:
        $tr .= " bgcolor=\"$valeur\"";
        break;
      case "class"	:
        $tr .= " class=\"$valeur\"";
        break;
      case "height"	:
        $tr .= " style=\"height:$valeur;\"";
        break;
      }
    $tr.=">\n";
    return $tr;
  }

  function private_get_style_properties() {
    // Définit les propriétés de la ligne pour chaque style
    // C'est ici qu'il faut ajouter les propriétés qui dépendent du style et qui influancent la ligne
    $proprietes = array();
    switch ($this->get_style()) {
    case TABLE_STYLE_CLASSIC:
      if ($this->protected_pere->get_child_number() == 0)
        $proprietes["class"] = "tableclassicheader";
      break;
    case TABLE_STYLE_ALTERN:
      if ((($this->protected_pere->get_child_number()) % 2) == 0)
        // Si je suis une ligne paire (ligne n°0, n°2, n°4, etc...)
        $proprietes["class"] = "tablealternlignepaire";
      else
        // Si je suis une ligne impaire (ligne n°1, n°3, etc...)
        $proprietes["class"] = "tablealternligneimpaire";

      if ($this->protected_pere->get_child_number() == 0)
        // Si je suis la première ligne
        $proprietes["class"] = "tablealternheader";
      break;
    }
    return $proprietes;
  }
}

/////////////////////////////////////////////////////////////////////
// Classe TABLE_cell ////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//
// Cet objet représente une cellule d'une ligne d'une table HTML.
//  Il hérite de l'objet HTML_TABLE_properties, c'est à dire que
// toutes les fonctions et attributs de HTML_TABLE_properties sont
// disponibles pour HTML_TABLE_cell.
//
class TABLE_cell extends HTML_TABLE_properties {
  var $protected_contenu;

  function TABLE_cell($contenu, $colspan = 1, $rowspan = 1) {
    // Constructeur
    $null = NULL;
    parent::HTML_TABLE_properties($null); // Devra être écrasé plus tard avec un set_pere()

    $this->protected_contenu = $contenu;			// Contenu (ce qu'il y a entre <td> et </td>)
    $this->set_property("colspan",$colspan);		// Toute cellule à au moins la propriété colspan
    $this->set_property("rowspan",$rowspan);		// Toute cellule à au moins la propriété rowspan
  }

  function gen_HTML() {
    // Cette fonction génère le code HTML propre à la cellule

    $html = $this->private_gen_html_tag_td();
    $html.= $this->private_gen_html_tag_header();
    $html.= $this->protected_contenu;
    $html.= $this->private_gen_html_tag_footer();
    $html.= "</td>\n";
    $this->protected_pere->inc_child_number();		// On passe à la cellule suivante
    return $html;
  }

  function private_gen_html_tag_td() {
    // Cette fonction génère le code HTML du tag <td>
    // C'est ici qu'il faut ajouter le code des propriétés qui ont une influance sur l'intérieur du tag <td>
    $td = $this->get_indent().'<td';

    foreach ($this->get_properties() AS $propriete => $valeur)
    if ($valeur != "")
      switch ($propriete) {
      case "bgcolor":
        $td .= " bgcolor=\"$valeur\"";
        break;
      case "width":
        $td .= " width=\"$valeur\"";
        break;
      case "align":
        $td .= " align=\"$valeur\"";
        break;
      case "colspan":
        if ($valeur > 1) $td .= " colspan=\"$valeur\"";
        break;
      case "rowspan":
        if ($valeur > 1) $td .= " rowspan=\"$valeur\"";
        break;
      }
    $td.=">";
    return $td;
  }

  function private_gen_html_tag_header() {
    // Cette fonction génère ce qui se trouve juste après le tag <td>
    // C'est ici qu'il faut ajouter le code des propriétés qui ont une influance sur l'intérieur de la cellule
    $retour = "";
    foreach ($this->get_properties() AS $propriete => $valeur)
    switch ($propriete) {
    case "bold":
      $retour .= "<b>";
      break;
    case "color":
      if ($valeur != "") $retour .= "<font color=\"$valeur\">";
      break;
    }
    return $retour;
  }

  function private_gen_html_tag_footer() {
    // Cette fonction génère ce qui se trouve juste avant le tag </td>
    // C'est ici qu'il faut ajouter le code des propriétés qui ont une influance sur l'intérieur de la cellule
    $retour = "";
    foreach ($this->get_properties() AS $propriete => $valeur)
    switch ($propriete) {
    case "bold":
      $retour .= "</b>";
      break;
    case "color":
      if ($valeur != "") $retour .= "</font>";
      break;
    }
    return $retour;
  }

  function private_get_style_properties() {
    // Cette fonction génère les propriétés dues au style choisi, et qui ont une influance sur la cellule
    return array(); // Pour l'instant, les styles n'ont pas d'influance sur les propriétés des cellules
  }
}

/////////////////////////////////////////////////////////////////////
// Classe TABLE_cell_link ///////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//
// On arrive dans la partie intéressante !!! (courage)
// Ici, on va étendre (par héritage) le type "cellule". Par étendre,
// j'entends ajouter des propriétés, des fonctions, ou surcharger des
// fonctions.
// Cette classe crée une cellule qui contient un lien html
// (du type <A HREF>). C'est donc un cas particulier de "TABLE_cell"
//
class TABLE_cell_link extends TABLE_cell {
  var $private_lien		= "";
  var $privare_lien_display	= "";

  function TABLE_cell_link($contenu, $lien, $colspan = 1, $rowspan = 1) {
    // Constructeur
    parent::TABLE_cell("",$colspan,$rowspan);			// Appel du constructeur de TABLE_cell
    $this->private_lien 		= $lien;			// où va le lien ?
    $this->private_lien_display	= $contenu;			// Ce qui est entre <A HREF> et </A>
  }

  function private_gen_contenu() {
    // Génération du code HTML du lien (ce qui se trouvera entre <A HREF> et </A>
    // (on écrase le contenu de la variable protected_contenu de TABLE_cell)
    $this->protected_contenu 	= '<a href="'.$this->private_lien.'">'.$this->private_lien_display.'</a>';
  }

  function gen_HTML() {
    // ATTENTION : ceci est une surcharge de la fonction gen_HTML de TABLE_cell.
    //	       c'est à dire que lorsqu'on appelle gen_HTML dans un objet TABLE_cell_link, c'est cette
    //	       fonction-ci qui est appellée, et non la fonction gen_HTML de TABLE_cell.

    $this->private_gen_contenu();					// Traitements particuliers pour un lien
    return parent::gen_HTML();					// Génération du code HTML de la cellule
  }
}


/////////////////////////////////////////////////////////////////////
// Classe TABLE_cell_date ///////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
//
// Ici, on va étendre (par héritage) le type "cellule". Par étendre,
// j'entends ajouter des propriétés, des fonctions, ou surcharger des
// fonctions.
// Cette classe crée une cellule qui contient une date
// C'est donc un cas particulier de "TABLE_cell"
// MULTILINGUE: cette cellule formate automatiquement la date
//		en JJ/MM/AAAA ou MM/JJ/AAAA en fonction de la langue
//		de l'interface utilisateur
//
class TABLE_cell_date extends TABLE_cell {
  var $private_date;

  function TABLE_cell_date($contenu, $colspan = 1, $rowspan = 1) {
    // Constructeur
    parent::TABLE_cell("",$colspan,$rowspan);			// Appel du constructeur de TABLE_cell
    $this->private_date = $contenu;					// Date à afficher (format JJ/MM/AAAA)
  }

  function private_gen_contenu() {
    // Génère le code HTML de ce qu'il faut mettre dans la cellule.
    // Dans notre cas, on affiche juste la date dans le bon format
    $this->protected_contenu = localiser_date($this->private_date);
  }

  function gen_HTML() {
    // ATTENTION : ceci est une surcharge de la fonction gen_HTML de TABLE_cell.
    //	       c'est à dire que lorsqu'on appelle gen_HTML dans un objet TABLE_cell_date, c'est cette
    //	       fonction-ci qui est appellée, et non la fonction gen_HTML de TABLE_cell.

    $this->private_gen_contenu();					// Traitements propre à la date
    return parent::gen_HTML();					// Génération du code HTML de la cellule
  }
}

/**
 * Créé une cellule contenant un élément <INPUT>
 *
 * @uses TABLE_cell
 * @version 1.0.0
 * @copyright ADbanking
 * @author Antoine Delvaux
 * @since 2.1
 */
class TABLE_cell_input extends TABLE_cell {
  var $private_type;
  var $private_name;
  var $private_value;
  var $private_onchange;
  var $private_onclick;
  var $private_align;
  var $private_disabled;
  var $private_extra_attributes;

  /**
   * Constructeur
   *
   * @param string $type Type de l'élément
   * @param string $name Nom de l'élément
   * @param string $value Valeur de l'élément
   * @param string $onchange Javascript à associer avec l'élément lors d'une modification de sa valeur
   * @param string $onclick Javascript à associer avec l'élément lors d'un click au cas d'un bouton
   * @param string $align Alignement du texte dans l'élément
   * @param bool $disabled Drapeau indiquant si l'élément doit être désactivé
   * @param string $extra_attributes Attributs supplémentaires à ajouter à l'élément (par exemple utilisé par les classes filles)
   * @param int $colspan Nombre de colonnes sur lesquelles s'étendent la cellule
   * @param int $rowspan Nombre de ligne sur lesquelles s'étendent la cellule
   * @access public
   * @return void
   */
  function TABLE_cell_input($type, $name, $value = "", $onchange = "", $align = "", $disabled = false, $extra_attributes = "", $colspan = 1, $rowspan = 1, $onclick="") {
    parent::TABLE_cell("", $colspan, $rowspan);
    $this->private_type = $type;
    $this->private_name = $name;
    $this->private_value = $value;
    $this->private_onchange = $onchange;
    $this->private_onclick = $onclick;
    $this->private_align = $align;
    $this->private_disabled = $disabled;
    $this->private_extra_attributes = $extra_attributes;
  }

  /**
   * Donne le(s) attribut(s) supplémentaires à l'élément INPUT,
   * les attributs supplémentaires déjà existants seront supprimés.
   *
   * @param string $attributes : le(s) attribut(s) supplémentaires
   * @access public
   * @return void
   */
  function set_extra_attributes($attributes) {
    $this->private_extra_attributes = $attributes;
  }

  /**
   * Ajoute un attribut supplémentaire à l'élément INPUT
   *
   * @param string $attribute : l'attribut à ajouter
   * @access public
   * @return void
   */
  function add_extra_attribute($attribute) {
    if ($this->private_extra_attributes) {
      $this->private_extra_attributes .= " ";
    }
    $this->private_extra_attributes .= $attribute;
  }

  /**
   * Supprime tous les attributs supplémentaires de l'élément INPUT
   *
   * @access public
   * @return void
   */
  function delete_extra_attributes() {
    $this->private_extra_attributes = "";
  }

  /**
   * Génère le code HTML de l'élément INPUT
   *
   * @access protected
   * @return void
   */
  function private_gen_contenu() {
    // Génère le code HTML de l'élément INPUT à l'intérieur de la cellule
    // Seuls les attributs type et name sont obligatoires
    $this->protected_contenu = "<input type=\"".$this->private_type."\" name=\"".$this->private_name."\"";
    if ($this->private_value) {
      $this->protected_contenu .= " value=\"".$this->private_value."\"";
    }
    if ($this->private_onchange) {
      $this->protected_contenu .= " onchange=\"".$this->private_onchange."\"";
    }
    if ($this->private_onclick) {
      $this->protected_contenu .= " onclick=\"".$this->private_onclick."\"";
    }
    if ($this->private_align) {
      $this->protected_contenu .= " align=\"".$this->private_align."\"";
    }
    if ($this->private_disabled) {
      $this->protected_contenu .= " disabled";
    }
    if ($this->private_extra_attributes) {
      $this->protected_contenu .= " ".$this->private_extra_attributes;
    }
    $this->protected_contenu .= "></input>";
  }

  /**
   * gen_HTML : Génère le code HTML de la cellule
   *
   * @access public
   * @return void
   */
  function gen_HTML() {
    // retourne le code HTML en veillant à bien construire sont contenu
    $this->private_gen_contenu();
    return parent::gen_HTML();
  }
}

/**
 * Créé une cellule contenant un élément <INPUT type="text">
 *
 * @uses TABLE_cell_input
 * @version 1.0.0
 * @copyright ADbanking
 * @author Antoine Delvaux
 * @since 2.1
 */
class TABLE_cell_input_text extends TABLE_cell_input {
  var $private_size;

  /**
   * Constructeur
   *
   * @param string $name Nom de l'élément
   * @param string $size La taille, en nombre de caractères, de l'élément
   * @param string $value Valeur de l'élément
   * @param string $onchange Javascript à associer avec l'élément lors d'une modification de sa valeur
   * @param string $align Alignement du texte dans l'élément
   * @param bool $disabled Drapeau indiquant si l'élément doit être désactivé
   * @param string $extra_attributes Attributs supplémentaires à ajouter à l'élément (par exemple utilisé par les classes filles)
   * @param int $colspan Nombre de colonnes sur lesquelles s'étendent la cellule
   * @param int $rowspan Nombre de ligne sur lesquelles s'étendent la cellule
   * @access public
   * @return void
   */
  function TABLE_cell_input_text($name, $size, $value = "", $onchange = "", $align = "", $disabled = false, $extra_attributes = "", $colspan = 1, $rowspan = 1) {
    $private_size = $size;
    parent::TABLE_cell_input("text", $name, $value, $onchange, $align, $disabled, $extra_attributes, $colspan, $rowspan);
    $this->add_extra_attribute("size=\"".$private_size."\"");
  }
}
?>