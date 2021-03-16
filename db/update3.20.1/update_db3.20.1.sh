#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.20"
VERSION="3.20.1"
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

        if [ -d "/usr/share/adbanking/web/ad_acu" ]; then
            chmod 777 -R /usr/share/adbanking/web/ad_acu/
        fi

        if [ -d "/usr/share/adbanking/web/multiagence/jobs/ALIM_SIEGE_0.2_1.6" ]; then
            chmod 777 -R /usr/share/adbanking/web/multiagence/jobs/ALIM_SIEGE_0.2_1.6/
        fi

        if [ -d "/usr/share/adbanking/web/ad_compensation_siege/app" ]; then
         chmod 777 -R /usr/share/adbanking/web/ad_compensation_siege/app/
        fi

        chmod 777 -R /usr/share/adbanking/web/multiagence/properties/

        # Mise à jour structure
        execute_psql 0 updata3.20.1.sql
        execute_psql 0 ./engrais_chimiques/engrais_chimiques.sql
        # Condition pour savoir si l'agence est siege
        TEST1=`return_psql "SELECT id_ag FROM ad_agc"`
        TEST2=`return_psql "SELECT count(*) FROM adsys_multi_agence WHERE id_agc = $TEST1 and is_agence_siege = TRUE"`
        if [[ "$TEST2" >0 ]]
        then
            execute_psql 0 ./engrais_chimiques/fonction_table_siege.sql
            execute_psql 0 ./compensation_siege_auto/script_compensation_siege.sql
            execute_psql 0 ./compensation_siege_auto/script_fonction_column.sql
            execute_psql 0 ./compensation_siege_auto/creation_menus_ecrans_at32.sql
        else
            execute_psql 0 ./engrais_chimiques/fonction_globalisation.sql
        fi

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
        source /usr/share/adbanking/db/update3.20.1/update_db3.20.1.sh
    fi