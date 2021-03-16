#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.16.1"
VERSION="3.16.2"
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh 

echo -e "Mise à jour ADbanking \033[1mv${OLD_VERSION} -> v${VERSION}\033[0m"

# Mise à jour structure
execute_psql 0 updata3.16.2.sql

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source /usr/share/adbanking/db/info_systeme.sh

  echo
  
###############################################################################
echo -e "---- DB VACUUM in progress......... ----"
# vacuum de la base
execute_psql 'cmd' "VACUUM FULL ANALYZE"

echo -e "----- FIN TRAITEMENT -----"
