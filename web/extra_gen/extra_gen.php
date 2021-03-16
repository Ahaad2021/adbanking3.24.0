<?php
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/html/stylesheet.php';

//Début page HTML
echo "<HTML> <BODY TEXT=$colt_menu BGCOLOR=$colb_menu>\n";

echo "<TABLE align=\"center\" valign=\"middle\">";
echo '<TR><TD align="center"><INPUT TYPE="button" value="'._("Menu principal")."\" onclick=\"window.parent.main_frame.location = '$SERVER_NAME/mainframe/mainframe.php?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3';\"></TD></TR>";
echo "</TABLE>";


//Fin HTML
echo "</BODY></HTML>\n";
?>