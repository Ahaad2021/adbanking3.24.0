#!/bin/bash
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

let ${DBNAME:=$1}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$1') "`

if [[ "$TEST" >0 ]]; then

        echo -e "Mise à jour ADbanking pour mobile lending"

        # Mise à jour structure
        execute_psql 0 /usr/share/adbanking/db/update_mobile_lending/updata_mobile_lending.sql
        execute_psql 0 /usr/share/adbanking/db/update_mobile_lending/ml_combinaison_global.sql

        echo -e "----- FIN TRAITEMENT -----"
    else
        unset DB
        unset DBNAME
        source /usr/share/adbanking/db/update_mobile_lending/update_mobile_lending.sh $1
    fi