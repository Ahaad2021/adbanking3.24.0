#!/bin/bash
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

let ${DBNAME:=$1}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$1') "`

if [[ "$TEST" >0 ]]; then
    execute_psql 0 /usr/share/adbanking/db/update3.20.2/update3.20.2-alpha2/updata3.20.2-alpha2.sql
else
    source /usr/share/adbanking/db/update3.20.2/update3.20.2-alpha2/update_db3.20.2-alpha2.sh $1
fi