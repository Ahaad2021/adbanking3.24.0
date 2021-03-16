<?php

/**
 * HTML_menu_gen
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/access.php';

class HTML_menu_gen {
  var $MenuItems;                // Array menu items in format MenuItem["label"] = "link"
  var $MenuImages;
  var $Title;                            // Menu Title
  var $HTMLCode;               // String containing the HTML code for the menu;
  var $HTML_header_code;      //String contenant le titre
  var $HTML_body_code;      //String contenant le corps
  var $align;			//Type d'alignement
  var $MenuKeys;               //Touches associées


  function HTML_menu_gen($title="") {
    $this->MenuItems = array();
    $this->HTMLCode = "";
    $this->Title = $title;
    $this->align = '"center"';
    return $this;
  }

  function addItem ($Label, $Ref, $fonction_axs=0, $Img=NULL, $keyval="-1") {
    global $global_profil_axs;
    if (check_access($fonction_axs)) {
      $this->MenuItems[$Label] = $Ref;
      if ($Img != NULL) {
        $this->MenuImages[$Label] = $Img;
        $this->align = '"left"';
      }
      $this->MenuKeys[$Label] = $keyval;
      return true;
    }
    return false;
  }

  function buildHTML() {
    global $PHP_SELF;
    $this->HTML_header_code = "<H1 align=\"center\"> ". $this->Title."</H1><br><br>\n";
    $this->HTML_body_code = "<TABLE align=\"center\" valign = \"middle\" border=0 cellpadding=5>";
    while (list($label, $link) = each($this->MenuItems)) { //Pour chaque item

      $this->HTML_body_code .= "\n<TR align=\"center\">";
      if ($this->MenuImages[$label] != NULL) { //Si il y a une image associée
        $this->HTML_body_code .= "<TD valign=\"middle\" align=\"right\"><A href=\"$link\">";
        $this->HTML_body_code .= "<IMG SRC=\"".$this->MenuImages[$label]."\" BORDER=0 HEIGHT=40 WIDTH=42 ALLIGN=middle>";
        $this->HTML_body_code .= "</A></TD>";
      }

      //Ecrit le link
      $this->HTML_body_code .= "<TD valign=middle align=$this->align><A href=\"$link\">";
      $this->HTML_body_code .= "$label </A>";
      //Si il y a une touche, on affiche
      if ($this->MenuKeys[$label] != -1) $this->HTML_body_code .= "(".$this->MenuKeys[$label].")";
      $this->HTML_body_code .= "</TD></TR>";
      //Si il y a une touche, on prévient javascript de son existence
      if ($this->MenuKeys[$label] != -1)
        echo '<SCRIPT type="text/javascript"> link'.$this->MenuKeys[$label].'Value="'.$link.'"; </SCRIPT>';
    }
    $this->HTML_body_code .= "</TABLE>";
    $this->HTMLCode = $this->HTML_header_code . $this->HTML_body_code;
    return true;
  }
}
