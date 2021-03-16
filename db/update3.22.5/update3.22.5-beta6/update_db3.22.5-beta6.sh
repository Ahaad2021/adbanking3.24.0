#!/bin/bash
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

let ${DBNAME:=$1}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$1') "`

if [[ "$TEST" >0 ]]; then

    # Mise à jour structure
    execute_psql 0 /usr/share/adbanking/db/update3.22.5/update3.22.5-beta6/updata3.22.5-beta6.sql

else
    source /usr/share/adbanking/db/update3.22.5/update3.22.5-beta6/update_db3.22.5-beta6.sh $1
fi