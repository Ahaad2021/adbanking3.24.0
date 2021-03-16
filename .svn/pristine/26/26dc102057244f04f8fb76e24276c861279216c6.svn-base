#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.18.0"
VERSION="3.18.1"
let ${DBUSER:=adbanking}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
unset DB
echo
echo -e "Please enter the database to update:"

    unset DB
    read  DB

    let ${DBNAME:=$DB}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DB') "`

    if [[ "$TEST" >0 ]]
    then

        echo -e "Mise à jour ADbanking \033[1mv${OLD_VERSION} -> v${VERSION}\033[0m"

        # Mise à jour structure
        execute_psql 0 updata3.18.1.sql

        DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
        source /usr/share/adbanking/db/info_systeme.sh

        ###############################################################################
        echo -e "---- DB VACUUM in progress......... ----"
        # vacuum de la base
        execute_psql 'cmd' "VACUUM FULL ANALYZE"
        echo -e "----- FIN TRAITEMENT -----"
    else
        unset DB
        unset DBNAME
        source /usr/share/adbanking/db/update3.18.1/update_db3.18.1.sh
    fi





