#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="Version 2.0"
VERSION="pour le ticket AT-145"
let ${DBUSER:=adbanking}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
unset DB
echo
echo -e "|====>> Processing Script \033[1m${OLD_VERSION} ${VERSION}\033[0m <<====|"
echo
echo -e "Entrez le nom de la base des données :"

    unset DB
    read  DB

    let ${DBNAME:=$DB}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DB') "`

    if [[ "$TEST" >0 ]]
    then

        echo        

        # Les scripts SQL
        echo
        execute_psql 0 ./files/AT_145_creation_champ.sql
        execute_psql 0 ./files/AT_145_traite_inactif.sql
        execute_psql 0 ./files/AT_145_prelevFraisBNR.sql
        execute_psql 0 ./files/setdormant.sql  
		echo -e "[ Mise à jour de la base de données!!! ]"

		# Les Fichiers PHP
        echo

        cp  -fp ./files/menu.php     /usr/share/adbanking/web/modules/menus/menu.php
        cp  -fp ./files/activer_compte_dormant.php     /usr/share/adbanking/web/modules/epargne/activer_compte_dormant.php
        cp  -fp ./files/access.php     /usr/share/adbanking/web/lib/misc/access.php
        cp  -fp ./files/traite_epargne.php     /usr/share/adbanking/web/batch/traite_epargne.php
        cp  -fp ./files/epargne.php     /usr/share/adbanking/web/lib/dbProcedures/epargne.php
        cp  -fp ./files/rapports.php     /usr/share/adbanking/web/lib/dbProcedures/rapports.php
        cp  -fp ./files/tableSys.php     /usr/share/adbanking/web/lib/misc/tableSys.php
        cp  -fp ./files/tables.php     /usr/share/adbanking/web/modules/parametrage/tables.php
        cp  -fp ./files/rapports_epargne.php     /usr/share/adbanking/web/modules/rapports/rapports_epargne.php
        cp  -fp ./files/xml_epargne.php     /usr/share/adbanking/web/modules/rapports/xml_epargne.php
        cp  -fp ./files/comptes_inactifs.xslt     /usr/share/adbanking/web/rapports/xslt/fr_BE/comptes_inactifs.xslt

		echo -e "[ Mise à jour des Fichiers AT-145 PHP!!! ]"
		echo        

        ###############################################################################
        echo -e "---- DB VACUUM in progress......... ----"
        # vacuum de la base
        execute_psql 'cmd' "VACUUM FULL ANALYZE"
        echo -e "----- DB VACUUM Finished -----"
		echo
		echo -e "|====>> Processing Script \033[1m${OLD_VERSION} ${VERSION}\033[0m Done!<<====|"
    else
        unset DB
        unset DBNAME

		echo -e "Le nom de la base a été mal saisie !!"
        source /usr/share/adbanking/web/patch_AT_145/script_AT_145.sh
    fi





