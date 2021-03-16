#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.18.1"
VERSION="3.20"
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
        execute_psql 0 updata3.20.sql
        execute_psql 0 ../update_mobile_banking/updata_mobile_banking.sql
        execute_psql 0 engrais_chimiques.sql
        execute_psql 0 ./script_budget/budget_tables_fonctions.sql
        execute_psql 0 ./script_budget/script_creation_menus_ecrans.sql
        execute_psql 0 ./script_budget/budget_fonctions.sql
        execute_psql 0 ./script_ma2e/gestion_quotite.sql
        execute_psql 0 ./script_ma2e/creation_menus_ecrans_mae17.sql
        execute_psql 0 ./script_ma2e/patch_ma2e.sql
        execute_psql 0 ./script_ma2e/patch_ticket_MAE_25_30.sql
        execute_psql 0 ./script_ma2e/patch_ma2e_reprise_credit.sql
        execute_psql 0 ./script_ma2e/creation_menus_ecrans_mae20.sql
        execute_psql 0 ./script_ma2e/creation_menus_ecrans_mae21.sql
        execute_psql 0 ./script_ma2e/mae_22.sql
        execute_psql 0 ./script_ma2e/getperiodecapitalisation_mae22.sql
        execute_psql 0 ./script_ma2e/arretecompte_mae22.sql
        execute_psql 0 ./script_ma2e/patch_ticket_MAE_23.sql


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
        source /usr/share/adbanking/db/update3.20/update_db3.20.sh
    fi