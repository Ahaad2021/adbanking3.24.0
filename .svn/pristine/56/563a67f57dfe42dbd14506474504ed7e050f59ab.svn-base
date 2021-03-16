#!/bin/bash
############################################################
# Variables utiles
############################################################

#let ${DBUSER:=adbanking}
let ${datapass:=$(awk -F "=" '/DB_pass/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

output=`php  /usr/share/adbanking/web/lib/misc/get_decrypted_password.php $datapass`
DBPASS=$output

echo
echo -e "Entrez le nom de la base des donnees :"

    unset DB
    read  DB

    let ${DBNAME:=$DB}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DB') "`

    if [[ "$TEST" >0 ]]
    then
    version_traduction=$(awk -F "=" '/version_traduction/ {print $2}' /usr/share/adbanking/db/update_traduction/traduction_parametre.ini)
    fileName="$version_traduction.csv"
    TEST2=`return_psql "SELECT version_rpm FROM adsys_infos_systeme WHERE is_active = true "`
    traductionPath="/usr/share/adbanking/db/update_traduction/$TEST2/$fileName"
    fileNom=$version_traduction

        if [ -e $traductionPath ]
        then
          # Execution du main script
          echo
          echo -e "Execution Main Script En Cours ..."
          php update_traduction/translate.php $DB $TEST2 $version_traduction $DBPASS
          return_psql "SELECT update_adsys_infos_systeme ('$TEST2','$fileNom');"

          else
          echo -e "le fichier traduction '$traductionPath' n'existe pas! "

        fi

  else
        unset DB
        unset DBNAME
        source /usr/share/adbanking/db/update_traduction.sh
  fi