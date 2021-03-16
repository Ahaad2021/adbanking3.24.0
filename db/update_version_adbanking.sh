#!/bin/bash
############################################################
# Variables utiles
############################################################

#TRES IMPORANT : cette variable version doit correspondre à la dernier version en cours de Adbanking => à mettre à jour manuellement
VERSION="3.24.0-beta3"
let ${DBUSER:=adbanking}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
unset DB
echo -e
echo -e "----- DEBUT TRAITEMENT -----"
echo -e
echo -e "Veuillez entrer le nom de la base de données à mettre à jour : "

    unset DB
    read  DB

    let ${DBNAME:=$DB}

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DB') "`

if [[ "$TEST" >0 ]]; then

    version_os=`cat /etc/*-release`
    version_php=`php -v`
    version_appache=`httpd -v`


	RECUP_CURRENT_VERSION_APP=`return_psql "SELECT version_rpm FROM adsys_infos_systeme where is_active = 'true'"`

	echo -e "Mise à jour ADbanking \033[1mv${RECUP_CURRENT_VERSION_APP} -> v${VERSION}\033[0m"

	output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Votre version actuelle est anterieur à la version adbanking3.20.1. Veuillez mettre à jour jusqu'a la version adbanking3.20.1 pour pouvoir proceder à la mise à jour automatique."
        exit
    fi

	if [[ "$RECUP_CURRENT_VERSION_APP" == "$VERSION" ]]; then
		echo -e "Votre application est déjà à jour!"
	fi

    echo -e "Verification de votre multi agence!"
    pathUpdateExist="/usr/share/adbanking/db/Dump/update_multi_agence.sql"
    if [ -e $pathUpdateExist ]
	then
		execute_psql 0 /usr/share/adbanking/db/Dump/update_multi_agence.sql
    fi

    #Application de la traduction acutelle
    #echo -e "Traduction de l'application Adbanking"
    #traductionExist="/usr/share/adbanking/web/locale/en_GB/LC_MESSAGES/adbanking.po"
    #if [ -e traductionExist ]
    #then
    #    sh /usr/share/adbanking/multilingue/creationmot.sh
    #fi


	#Mise à jour 3.20.2-alpha1
	output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2-alpha1" "<"`
	if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2-alpha1"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2-alpha1/update_db3.20.2-alpha1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2-alpha1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2-alpha1"
	fi

	echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2-alpha2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2-alpha2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2-alpha2"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2-alpha2/update_db3.20.2-alpha2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2-alpha2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2-alpha2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2-RC1"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2-RC1/update_db3.20.2-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2-RC2"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2-RC2/update_db3.20.2-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2-RC2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2-RC3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2-RC3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2-RC3"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2-RC3/update_db3.20.2-RC3.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2-RC3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2-RC3"
    fi
    
    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2-RC4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2-RC4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2-RC4"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2-RC4/update_db3.20.2-RC4.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2-RC4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2-RC4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2"
        sh /usr/share/adbanking/db/update3.20.2/update3.20.2/update_db3.20.2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.1-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.1-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.1-RC1"
        sh /usr/share/adbanking/db/update3.20.2.1/update3.20.2.1-RC1/update_db3.20.2.1-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.1-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.1-RC1"
    fi
    
    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.1"
        sh /usr/share/adbanking/db/update3.20.2.1/update3.20.2.1/update_db3.20.2.1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.2"
        sh /usr/share/adbanking/db/update3.20.2.2/update3.20.2.2/update_db3.20.2.2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.3-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.3-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.3-RC1"
        sh /usr/share/adbanking/db/update3.20.2.3/update3.20.2.3-RC1/update_db3.20.2.3-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.3-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.3-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.3-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.3-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.3-RC2"
        sh /usr/share/adbanking/db/update3.20.2.3/update3.20.2.3-RC2/update_db3.20.2.3-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.3-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.3-RC2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.3"
        sh /usr/share/adbanking/db/update3.20.2.3/update3.20.2.3/update_db3.20.2.3.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.4-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.4-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.4-RC1"
        sh /usr/share/adbanking/db/update3.20.2.4/update3.20.2.4-RC1/update_db3.20.2.4-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.4-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.4-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.4"
        sh /usr/share/adbanking/db/update3.20.2.4/update3.20.2.4/update_db3.20.2.4.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.5-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.5-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.5-beta1"
        sh /usr/share/adbanking/db/update3.20.2.5/update3.20.2.5-beta1/update_db3.20.2.5-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.5-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.5-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.5-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.5-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.5-beta2"
        sh /usr/share/adbanking/db/update3.20.2.5/update3.20.2.5-beta2/update_db3.20.2.5-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.5-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.5-beta2"
    fi


    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.5-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.5-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.5-RC1"
        sh /usr/share/adbanking/db/update3.20.2.5/update3.20.2.5-RC1/update_db3.20.2.5-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.5-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.5-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.5"
        sh /usr/share/adbanking/db/update3.20.2.5/update3.20.2.5/update_db3.20.2.5.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.5"
    fi

    echo -e "------------------------------------------------------------";

     #Mise à jour 3.20.2.6-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta1"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta1/update_db3.20.2.6-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta2"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta2/update_db3.20.2.6-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta3"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta3/update_db3.20.2.6-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta4"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta4/update_db3.20.2.6-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta5"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta5/update_db3.20.2.6-beta5.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta5"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.6-beta6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta6"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta6/update_db3.20.2.6-beta6.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta6"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta7
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta7" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta7"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta7/update_db3.20.2.6-beta7.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta7"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta7"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta8
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta8" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta8"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta8/update_db3.20.2.6-beta8.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta8"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta8"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-beta9
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-beta9" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-beta9"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-beta9/update_db3.20.2.6-beta9.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-beta9"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-beta9"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6-RC1"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6-RC1/update_db3.20.2.6-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.6"
        sh /usr/share/adbanking/db/update3.20.2.6/update3.20.2.6/update_db3.20.2.6.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.6"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.7-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.7-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.7-beta1"
        sh /usr/share/adbanking/db/update3.20.2.7/update3.20.2.7-beta1/update_db3.20.2.7-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.7-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.7-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.7-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.7-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.7-RC1"
        sh /usr/share/adbanking/db/update3.20.2.7/update3.20.2.7-RC1/update_db3.20.2.7-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.7-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.7-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.7
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.7" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.7"
        sh /usr/share/adbanking/db/update3.20.2.7/update3.20.2.7/update_db3.20.2.7.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.7"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.7"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.8-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.8-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.8-beta1"
        sh /usr/share/adbanking/db/update3.20.2.8/update3.20.2.8-beta1/update_db3.20.2.8-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.8-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.8-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.8-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.8-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.8-beta2"
        sh /usr/share/adbanking/db/update3.20.2.8/update3.20.2.8-beta2/update_db3.20.2.8-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.8-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.8-beta2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.8-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.8-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.8-RC1"
        sh /usr/share/adbanking/db/update3.20.2.8/update3.20.2.8-RC1/update_db3.20.2.8-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.8-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.8-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.8
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.8" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.8"
        sh /usr/share/adbanking/db/update3.20.2.8/update3.20.2.8/update_db3.20.2.8.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.8"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.8"
    fi

    echo -e "------------------------------------------------------------";
    
    #Mise à jour 3.20.2.9-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-beta1"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-beta1/update_db3.20.2.9-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.9-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-beta2"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-beta2/update_db3.20.2.9-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-beta2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.9-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-beta3"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-beta3/update_db3.20.2.9-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-beta3"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-beta4"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-beta4/update_db3.20.2.9-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-beta4"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-beta5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-beta5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-beta5"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-beta5/update_db3.20.2.9-beta5.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-beta5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-beta5"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-beta6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-beta6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-beta6"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-beta6/update_db3.20.2.9-beta6.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-beta6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-beta6"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-RC1"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-RC1/update_db3.20.2.9-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-RC1"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-RC2"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-RC2/update_db3.20.2.9-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-RC2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-RC3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-RC3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-RC3"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-RC3/update_db3.20.2.9-RC3.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-RC3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-RC3"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-RC4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-RC4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-RC4"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-RC4/update_db3.20.2.9-RC4.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-RC4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-RC4"
    fi

    echo -e "------------------------------------------------------------";
	

    #Mise à jour 3.20.2.9-RC5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-RC5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-RC5"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-RC5/update_db3.20.2.9-RC5.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-RC5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-RC5"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9-RC6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9-RC6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9-RC6"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9-RC6/update_db3.20.2.9-RC6.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9-RC6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9-RC6"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.9
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.9" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.9"
        sh /usr/share/adbanking/db/update3.20.2.9/update3.20.2.9/update_db3.20.2.9.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.9"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.9"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.10-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.10-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.10-RC1"
        sh /usr/share/adbanking/db/update3.20.2.10/update3.20.2.10-RC1/update_db3.20.2.10-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.10-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.10-RC1"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.10
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.10" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.10"
        sh /usr/share/adbanking/db/update3.20.2.10/update3.20.2.10/update_db3.20.2.10.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.10"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.10"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.11-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11-beta1"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11-beta1/update_db3.20.2.11-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.11-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11-beta2"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11-beta2/update_db3.20.2.11-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11-beta2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.11-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11-beta3"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11-beta3/update_db3.20.2.11-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11-beta3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.11-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11-beta4"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11-beta4/update_db3.20.2.11-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11-beta4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.11-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11-RC1"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11-RC1/update_db3.20.2.11-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.20.2.11-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11-RC2"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11-RC2/update_db3.20.2.11-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11-RC2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.20.2.11
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.20.2.11" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.20.2.11"
        sh /usr/share/adbanking/db/update3.20.2.11/update3.20.2.11/update_db3.20.2.11.sh $DB
        DIR="/usr/share/adbanking/db/update3.20.2.11"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.20.2.11"
    fi

    echo -e "------------------------------------------------------------";
        
    #Mise à jour 3.22.0-alpha1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-alpha1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-alpha1"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-alpha1/update_db3.22.0-alpha1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-alpha1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-alpha1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-alpha2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-alpha2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-alpha2"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-alpha2/update_db3.22.0-alpha2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-alpha2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-alpha2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta1"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta1/update_db3.22.0-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta2"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta2/update_db3.22.0-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta3"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta3/update_db3.22.0-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta4"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta4/update_db3.22.0-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta5"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta5/update_db3.22.0-beta5.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta5"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta6"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta6/update_db3.22.0-beta6.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta6"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta7
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta7" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta7"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta7/update_db3.22.0-beta7.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta7"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta7"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-beta8
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-beta8" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-beta8"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-beta8/update_db3.22.0-beta8.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-beta8"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-beta8"
    fi


    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-RC1"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-RC1/update_db3.22.0-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-RC2"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-RC2/update_db3.22.0-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-RC2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.0-RC3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0-RC3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0-RC3"
        sh /usr/share/adbanking/db/update3.22/update3.22.0-RC3/update_db3.22.0-RC3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0-RC3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0-RC3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.0
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.0" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.0"
        sh /usr/share/adbanking/db/update3.22/update3.22.0/update_db3.22.0.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.0"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.0"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.1-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.1-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.1-beta1"
        sh /usr/share/adbanking/db/update3.22.1/update3.22.1-beta1/update_db3.22.1-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.1-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.1-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.1-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.1-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.1-RC1"
        sh /usr/share/adbanking/db/update3.22.1/update3.22.1-RC1/update_db3.22.1-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.1-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.1-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.1"
        sh /usr/share/adbanking/db/update3.22.1/update3.22.1/update_db3.22.1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.2-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.2-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.2-beta1"
        sh /usr/share/adbanking/db/update3.22.2/update3.22.2-beta1/update_db3.22.2-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.2-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.2-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.2-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.2-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.2-RC1"
        sh /usr/share/adbanking/db/update3.22.2/update3.22.2-RC1/update_db3.22.2-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.2-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.2-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.2"
        sh /usr/share/adbanking/db/update3.22.2/update3.22.2/update_db3.22.2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.2"
    fi


    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.2.1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.2.1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.2.1"
        sh /usr/share/adbanking/db/update3.22.2.1/update3.22.2.1/update_db3.22.2.1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.2.1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.2.1"
    fi
    
     echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-beta1"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-beta1/update_db3.22.3-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-beta2"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-beta2/update_db3.22.3-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-beta2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-beta3"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-beta3/update_db3.22.3-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-beta3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-RC1"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-RC1/update_db3.22.3-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-RC1"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.3-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-RC2"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-RC2/update_db3.22.3-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-RC2"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-RC3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-RC3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-RC3"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-RC3/update_db3.22.3-RC3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-RC3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-RC3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-RC4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-RC4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-RC4"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-RC4/update_db3.22.3-RC4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-RC4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-RC4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-RC5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-RC5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-RC5"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-RC5/update_db3.22.3-RC5.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-RC5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-RC5"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3-RC6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3-RC6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3-RC6"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3-RC6/update_db3.22.3-RC6.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3-RC6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3-RC6"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.3"
        sh /usr/share/adbanking/db/update3.22.3/update3.22.3/update_db3.22.3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.3"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.4-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4-beta1"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4-beta1/update_db3.22.4-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4-beta1"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.4-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4-beta2"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4-beta2/update_db3.22.4-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4-beta2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.4-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4-beta3"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4-beta3/update_db3.22.4-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4-beta3"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.4-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4-beta4"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4-beta4/update_db3.22.4-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4-beta4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.4-beta5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4-beta5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4-beta5"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4-beta5/update_db3.22.4-beta5.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4-beta5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4-beta5"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.4-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4-RC1"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4-RC1/update_db3.22.4-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4-RC1"
    fi

    echo -e "------------------------------------------------------------";
        #Mise à jour 3.22.4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4"
        sh /usr/share/adbanking/db/update3.22.4/update3.22.4/update_db3.22.4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4"
    fi

    echo -e "------------------------------------------------------------";

     echo -e "------------------------------------------------------------";
        #Mise à jour 3.22.4.1-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.1-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.1-beta1"
        sh /usr/share/adbanking/db/update3.22.4.1/update3.22.4.1-beta1/update_db3.22.4.1-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.1-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.1-beta1"
    fi

    echo -e "------------------------------------------------------------";
        #Mise à jour 3.22.4.1-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.1-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.1-beta2"
        sh /usr/share/adbanking/db/update3.22.4.1/update3.22.4.1-beta2/update_db3.22.4.1-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.1-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.1-beta2"
    fi

    echo -e "------------------------------------------------------------";
        #Mise à jour 3.22.4.1-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.1-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.1-beta3"
        sh /usr/share/adbanking/db/update3.22.4.1/update3.22.4.1-beta3/update_db3.22.4.1-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.1-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.1-beta3"
    fi

    echo -e "------------------------------------------------------------";
        #Mise à jour 3.22.4.1-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.1-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.1-beta4"
        sh /usr/share/adbanking/db/update3.22.4.1/update3.22.4.1-beta4/update_db3.22.4.1-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.1-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.1-beta4"
    fi

    echo -e "------------------------------------------------------------";
        #Mise à jour 3.22.4.1-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.1-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.1-RC1"
        sh /usr/share/adbanking/db/update3.22.4.1/update3.22.4.1-RC1/update_db3.22.4.1-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.1-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.1-RC1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.4.1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.1"
        sh /usr/share/adbanking/db/update3.22.4.1/update3.22.4.1/update_db3.22.4.1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.1"
    fi

    echo -e "------------------------------------------------------------";

            #Mise à jour 3.22.4.2-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.2-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.2-beta1"
        sh /usr/share/adbanking/db/update3.22.4.2/update3.22.4.2-beta1/update_db3.22.4.2-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.2-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.2-beta1"
    fi

        echo -e "------------------------------------------------------------";
    #Mise à jour 3.22.4.2-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.2-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.2-beta2"
        sh /usr/share/adbanking/db/update3.22.4.2/update3.22.4.2-beta2/update_db3.22.4.2-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.2-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.2-beta2"
    fi

    echo -e "------------------------------------------------------------";

        #Mise à jour 3.22.4.2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.2"
        sh /usr/share/adbanking/db/update3.22.4.2/update3.22.4.2/update_db3.22.4.2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.2"
    fi

    echo -e "------------------------------------------------------------";
    
    
        #Mise à jour 3.22.4.3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.3"
        sh /usr/share/adbanking/db/update3.22.4.3/update3.22.4.3/update_db3.22.4.3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.3"
    fi

    echo -e "------------------------------------------------------------";

        #Mise à jour 3.22.4.4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.4"
        sh /usr/share/adbanking/db/update3.22.4.4/update3.22.4.4/update_db3.22.4.4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.4"
    fi

    echo -e "------------------------------------------------------------";


        #Mise à jour 3.22.4.5-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.5-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.5-beta1"
        sh /usr/share/adbanking/db/update3.22.4.5/update3.22.4.5-beta1/update_db3.22.4.5-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.5-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.5-beta1"
    fi

    echo -e "------------------------------------------------------------";

        #Mise à jour 3.22.4.5-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.5-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.5-beta2"
        sh /usr/share/adbanking/db/update3.22.4.5/update3.22.4.5-beta2/update_db3.22.4.5-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.5-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.5-beta2"
    fi

    echo -e "------------------------------------------------------------";


        #Mise à jour 3.22.4.5-RC1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.5-RC1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.5-RC1"
        sh /usr/share/adbanking/db/update3.22.4.5/update3.22.4.5-RC1/update_db3.22.4.5-RC1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.5-RC1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.5-RC1"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.4.5-RC2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.5-RC2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.5-RC2"
        sh /usr/share/adbanking/db/update3.22.4.5/update3.22.4.5-RC2/update_db3.22.4.5-RC2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.5-RC2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.5-RC2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.4.5-RC3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.5-RC3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.5-RC3"
        sh /usr/share/adbanking/db/update3.22.4.5/update3.22.4.5-RC3/update_db3.22.4.5-RC3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.5-RC3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.5-RC3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.4.5-RC4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.4.5-RC4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.4.5-RC4"
        sh /usr/share/adbanking/db/update3.22.4.5/update3.22.4.5-RC4/update_db3.22.4.5-RC4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.4.5-RC4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.4.5-RC4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.5-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta1"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta1/update_db3.22.5-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta1"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.5-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta2"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta2/update_db3.22.5-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta2"
    fi

    echo -e "------------------------------------------------------------";


    #Mise à jour 3.22.5-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta3"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta3/update_db3.22.5-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta3"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.5-beta4
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta4" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta4"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta4/update_db3.22.5-beta4.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta4"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta4"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.5-beta5
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta5" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta5"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta5/update_db3.22.5-beta5.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta5"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta5"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.5-beta6
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta6" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta6"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta6/update_db3.22.5-beta6.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta6"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta6"
    fi

    echo -e "------------------------------------------------------------";

    #Mise à jour 3.22.5-beta7
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta7" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta7"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta7/update_db3.22.5-beta7.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta7"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta7"
    fi

    echo -e "------------------------------------------------------------";

        #Mise à jour 3.22.5-beta8
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta8" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta8"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta8/update_db3.22.5-beta8.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta8"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta8"
    fi

    echo -e "------------------------------------------------------------";

        #Mise à jour 3.22.5-beta9
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.22.5-beta9" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.22.5-beta9"
        sh /usr/share/adbanking/db/update3.22.5/update3.22.5-beta9/update_db3.22.5-beta9.sh $DB
        DIR="/usr/share/adbanking/db/update3.22.5-beta9"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.22.5-beta9"
    fi

    echo -e "------------------------------------------------------------";


        #Mise à jour 3.24.0-beta1
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.24.0-beta1" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.24.0-beta1"
        sh /usr/share/adbanking/db/update3.24/update3.24.0-beta1/update_db3.24.0-beta1.sh $DB
        DIR="/usr/share/adbanking/db/update3.24.0-beta1"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.24.0-beta1"
    fi

    echo -e "------------------------------------------------------------";


     #Mise à jour 3.24.0-beta2
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.24.0-beta2" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.24.0-beta2"
        sh /usr/share/adbanking/db/update3.24/update3.24.0-beta2/update_db3.24.0-beta2.sh $DB
        DIR="/usr/share/adbanking/db/update3.24.0-beta2"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.24.0-beta2"
    fi

    echo -e "------------------------------------------------------------";


     #Mise à jour 3.24.0-beta3
    output=`php  /usr/share/adbanking/db/function_compare.php $RECUP_CURRENT_VERSION_APP "3.24.0-beta3" "<"`
    if [[ "$output" == true ]]; then
        echo -e "Debut mise a jour adbanking 3.24.0-beta3"
        sh /usr/share/adbanking/db/update3.24/update3.24.0-beta3/update_db3.24.0-beta3.sh $DB
        DIR="/usr/share/adbanking/db/update3.24.0-beta3"
        return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"
        echo -e "Fin mise a jour adbanking 3.24.0-beta3"
    fi

    echo -e "------------------------------------------------------------";



    ###############################################################################
	## AT-31 : donner full persmission aux repertoire du module acu/compensation siege et le Job Talennd y relié
	echo -e
	echo -e "---------------------------------------------------------------------------------------------------"
	echo -e "Donner permission aux repertoire du Module ACU/Compensation Siege et le Job Talend y relié en cours..."
	sh set_permission_acu_compensationSiege.sh
	echo -e "Donner permission aux repertoire du Module ACU/Compensation Siege et le Job Talend y relié terminé!"
	echo -e "---------------------------------------------------------------------------------------------------"
    ## AT-31 : Mise à jour mot de passe
    sh update_encrypted_password.sh
    ## AT-31 : Mise à jour Licence
    execute_psql 0 /usr/share/adbanking/db/update3.16.2/updata3.16.2.sql
	###############################################################################
	echo -e
    echo -e "---- DB VACUUM in progress......... ----"
    # vacuum de la base
    execute_psql 'cmd' "VACUUM FULL ANALYZE"
    echo -e "---- DB VACUUM finished............ ----"
    echo -e
    echo -e "----- FIN TRAITEMENT -----"

else
	unset DB
	unset DBNAME
	source /usr/share/adbanking/db/update_version_adbanking.sh
fi