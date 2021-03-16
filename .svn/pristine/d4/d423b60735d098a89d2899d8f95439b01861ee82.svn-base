#!/bin/bash
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}

echo -e "Reset des tables \033[1mmenus\033[0m et \033[1mecrans\033[0m depuis \033[1mDump/menus.sql\033[0m"
execute_psql "cmd" "DELETE FROM ecrans;DELETE FROM menus;"
execute_psql 0  ${ADB_INSTALL_DIR}/db/Dump/menus.sql

echo -e "Reset de \033[1mtableliste\033[0m et \033[1md_tableliste\033[0m depuis \033[1mDump/tableliste.sql\033[0m"
execute_psql "cmd" "DELETE FROM d_tableliste;DELETE FROM tableliste;"
execute_psql 0  ${ADB_INSTALL_DIR}/db/Dump/tableliste.sql
