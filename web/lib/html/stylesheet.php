<?php

/**
 * stylesheet Définition des styles (stylesheet CSS)
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php'; //pour la définition des couleurs

echo "<style type=\"text/css\">\n";

//Global :
echo "p {margin-left:0px;margin-right:0px;margin-top:0px;margin-bottom:0px;}\n";
echo "p {font:12pt arial;margin-top:0px;margin-bottom:0px;}\n";
echo "tr {font:12pt arial;margin-top:0px;margin-bottom:0px;}\n";
echo "h1 {font:16pt arial;margin-top:15px;margin-bottom:15px;}\n";

//Contextualisés:
echo "p.login_gauche 	{	font:20pt arial; color:".$color_def['blanc'].";}\n";
echo "td.login_droite	{	font:12pt arial; color:".$color_def['rouge bordeau'].";}\n";
echo "p.login_droite	{	font:10pt arial; color:".$color_def['noir'].";}\n";
//echo "td.statut		{	font:12pt arial; font-weight:bold; color:".$color_def['cyan sombre'].";}\n";
echo "td.statut		{	font:12pt arial; color:".$color_def['cyan sombre'].";}\n";
echo "p.menugauche	{	font:8pt arial;}\n";
echo "p.aquadev1         {font:15pt arial;}\n";
echo "p.aquadev2         {font:6pt arial;}\n";
echo "div.batch p {font:9pt arial;}\n";

//Styles des tables
echo "
table.tablealtern {	border:1pt;
width:95%;
text-align: center;
padding:5pt;
}
tr.tablealternheader {	background-color : ".$color_def['orange clair aquadev'].";
font-weight : bold;
}
tr.tablealternligneimpaire  {	background-color : ".$color_def['gris clair'].";
}
tr.tablealternlignepaire {	background-color : ".$color_def['orange clair aquadev'].";
}

table.tableclassic {	border:1pt;
width:95%;
text-align:center;
background-color: ".$color_def['orange clair aquadev'].";
padding:5pt;
}
tr.tableclassicheader {	font-weight : bold;
}

";

// Couleur de fond du menu de navigation (cadre gauche)
echo "table#menu_nav { background-color: $colb_login_gauche; }\n";

echo "a:link			{color:".$color_def['cyan sombre']."; }\n";
echo "a:active			{color:".$color_def['cyan sombre']."; }\n";
echo "a:visited			{color:".$color_def['cyan sombre']."; }\n";

echo "p.menugauche a:link	{color:".$color_def['blanc']."; text-decoration: none;}\n";
echo "p.menugauche a:active	{color:".$color_def['blanc']."; text-decoration: none;}\n";
echo "p.menugauche a:visited	{color:".$color_def['blanc']."; text-decoration: none;}\n";
echo "p.menugauche a:hover	{text-decoration: underline; }\n";

// Création d'une classe spéciale pour les champs 'Label'
echo "INPUT[disabled] {color:".$color_def['bleu marine']."}\n";
echo "SELECT[disabled] {color:".$color_def['bleu marine']."}\n";

// On affiche les champs READONLY comme les champs DISABLED
echo "INPUT[readonly] {color:".$color_def['bleu marine']."; background-color: #EFEBE7;}\n";

// affichage des variables transmises dans le debug
echo "
#cacheS, #cacheP, #cacheG {
position : absolute;
top : 0pt;
left : 0pt;
visibility : hidden;
margin-left : 3pt;
padding : 15pt;
color : ".$color_def['cyan sombre'].";
background-color: ".$color_def['orange clair aquadev'].";
border : solid 2px ".$color_def['cyan sombre'].";
}
";

echo "</style>\n";
?>