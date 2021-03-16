#!/bin/sh
let ${datapass:=$(awk -F "=" '/DB_pass/ {print $2}' /usr/share/adbanking/web/multiagence/properties/adbanking.ini)}
let ${DBPASS:=$datapass}

#AT-31 : Gestion mot de passe encode
decrypted_pass=`php  /usr/share/adbanking/web/lib/misc/get_decrypted_password.php $DBPASS`

cd `dirname $0`
 ROOT_PATH=`pwd`
 java -Xms256M -Xmx1024M -cp $ROOT_PATH/../lib/dom4j-1.6.1.jar:$ROOT_PATH/../lib/ini4j-0.5.1.jar:$ROOT_PATH/../lib/postgresql-8.3-603.jdbc3.jar:$ROOT_PATH/../lib/talendcsv.jar:$ROOT_PATH/../lib/talend_file_enhanced_20070724.jar:$ROOT_PATH:$ROOT_PATH/../lib/systemRoutines.jar:$ROOT_PATH/../lib/userRoutines.jar::.:$ROOT_PATH/alim_siege_0_2.jar:$ROOT_PATH/recup_data_agence_0_2.jar: alim_siege.alim_siege_0_2.ALIM_SIEGE --context=Linux --context_param DB_pass=$decrypted_pass "$@"