<?php

require_once 'lib/misc/VariablesGlobales.php';
echo "<html><head><title>$ProjectName</title></head>";

//Script d'ouverture d'une nouvelle fenêtre de browser afin qu'on puisse supprimer les barres de navigation et autre
//On vérifie que l'on ne vienne pas du logout en comparant le nom de la fenêtre (window.name =? $window_name)
echo "<script type=\"text/javascript\">\n";
echo "if (window.name != '$window_name'){";
echo "window.open(\"$SERVER_NAME/login/login2.php\", '$window_name', \"menubar=no,resizable=yes,status=yes,toolbar=no,location=no\");\n";
echo "window.close();\n";
echo "}\n";
echo "else{\n";
echo "window.location = \"$SERVER_NAME/login/login2.php\"";
echo "}\n";
echo "</script>\n";

echo "</html>";
?>