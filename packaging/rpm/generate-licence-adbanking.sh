#!/bin/bash
# Name: generate-licence-adbanking.sh
# Description: Script d'aide à la génération de la licence d'ADbanking
# Notes:
#

# User defined constants
ANSWER=""
LICENCE_DATE=""
MODE_AGENCE=""
MODE_COMPENSATION=""
AGENCE_SIEGE=""
CODE_IDENTIFIER=""
NB_CLIENTS=""
COUNT_CLIENT_ALERT=""
ENGRAIS_CHIMIQUES=""
PASSWORD_ADBANKING=-1
AGENCY_BANKING=""
TEST_DATE1=""
TEST_DATE2=""
CURR_DATE=""

echo
echo -e "\033[1mADbanking license generation process\033[0m"
echo -e "\033[1m====================================\033[0m"

unset ANSWER
echo
echo -en "Do you want to generate a new \033[1mlicense\033[0m?"
read -p " (y/n) " -n 1 ANSWER
echo
if [[ "$ANSWER" == "Y" || "$ANSWER" == "y" ]]; then
	unset ANSWER
	echo

	echo -e "Please enter the license expiration date :"
	unset ANSWER
	read -p "(format : yyyy-mm-dd) " ANSWER

	while [[ "$LICENCE_DATE" == "" ]]; do
	
		# Validate date
		TEST_DATE1=`date -d "$ANSWER" "+%Y-%m-%d"`
		TEST_DATE2=`date -d "$ANSWER" "+%Y%m%d"`
		CURR_DATE=`date "+%Y%m%d"`
		
		if [ "$TEST_DATE1" == "$ANSWER" ] && [ $TEST_DATE2 -ge $CURR_DATE ] ; then
			LICENCE_DATE=$ANSWER
			echo -e "\033[1mLicense expiration date : ${LICENCE_DATE}\033[0m"
		else
			echo
			echo -e "\033[1mError !\033[0m Invalid date !"
			echo
			echo -e "Please enter a valid license expiration date :"
			unset ANSWER
			read -p "(format : yyyy-mm-dd) " ANSWER
		fi
	done
	
	echo
	echo -e "Please enter the installation mode :"
	unset ANSWER
	read -p "(mode : mono / multi) " ANSWER
	
	while [[ "$MODE_AGENCE" == "" ]]; do

		if [ "$ANSWER" == "mono" ] || [ "$ANSWER" == "multi" ]; then
			MODE_AGENCE=$ANSWER
			echo -e "\033[1mInstallation mode : ${MODE_AGENCE}\033[0m"
			echo
		else
			echo
			echo -e "\033[1mError !\033[0m Invalid installation mode !"
			echo
			echo -e "Please enter a valid installation mode :"
			unset ANSWER
			read -p "(mode : mono / multi) " ANSWER
		fi
	done
	
	if [ "$MODE_AGENCE" == "multi" ]; then
		unset ANSWER
		echo -en "Activate dropdown list to access all branches ?"
		read -p " (y/n) " -n 1 ANSWER
		echo
		if [[ "$ANSWER" == "Y" || "$ANSWER" == "y" ]]; then
			AGENCE_SIEGE="y"
			echo -e "\033[1mAccess all branches : yes\033[0m"
			echo
		else
			AGENCE_SIEGE="n"
			echo -e "\033[1mAccess all branches : no\033[0m"
			echo
		fi
	else
		AGENCE_SIEGE="n"
	fi
	
	if [ "$MODE_AGENCE" == "multi" ]; then
		echo -e "Please enter the compensation mode :"
		unset ANSWER
		read -p "(mode : interagence / siege ) " ANSWER
		
		while [[ "$MODE_COMPENSATION" == "" ]]; do

			if [ "$ANSWER" == "interagence" ] || [ "$ANSWER" == "siege" ]; then
				MODE_COMPENSATION=$ANSWER
				echo -e "\033[1mCompensation mode : ${MODE_COMPENSATION}\033[0m"
				echo
			else
				echo
				echo -e "\033[1mError !\033[0m Invalid Compensation mode !"
				echo
				echo -e "Please enter a valid Compensation mode :"
				unset ANSWER
				read -p "(mode : interagence / siege) " ANSWER
			fi
		done
	else
		MODE_COMPENSATION="interagence"
	fi

	echo -e "Please enter a code identifier :"
	unset ANSWER
	read -p "(format : [code_institution]-[annee]) " ANSWER

	while [[ "$CODE_IDENTIFIER" == "" ]]; do

		if [ "$ANSWER" != "" ]; then
			CODE_IDENTIFIER=$ANSWER
			echo -e "\033[1mCode identifier : ${CODE_IDENTIFIER}\033[0m"
			echo
		else
			echo
			echo -e "\033[1mError !\033[0m Invalid code identifier !"
			echo
			echo -e "Please enter a valid code identifier :"
			unset ANSWER
			read -p "(format : [code_institution]-[annee]) " ANSWER
		fi
	done

	unset ANSWER
	echo -en "Do you want to specify the maximum number of allowed clients ?"
	read -p " (y/n) " -n 1 ANSWER
	echo
	if [[ "$ANSWER" == "Y" || "$ANSWER" == "y" ]]; then

		echo
		echo -e "Please enter the maximum number of clients :"
		unset ANSWER
		read -p "(numbers only) " ANSWER

		while [[ "$NB_CLIENTS" == "" ]]; do

			if [[ $ANSWER =~ ^-?[0-9]+$ && $ANSWER > 0 ]]; then
				NB_CLIENTS=$ANSWER
				echo -e "\033[1mMaximum number of clients : ${NB_CLIENTS}\033[0m"
				echo
			else
				echo
				echo -e "\033[1mError !\033[0m Invalid number of clients !"
				echo
				echo -e "Please enter a valid number of clients :"
				unset ANSWER
				read -p "(numbers only) " ANSWER
			fi
		done
	else
		NB_CLIENTS=99999999
		echo
	fi

	if [[ $NB_CLIENTS != 99999999 && $NB_CLIENTS > 0 ]]; then
		echo -e "Please enter number of client creation left to display alert message :"
		unset ANSWER
		read -p "(numbers only) " ANSWER

		while [[ "$COUNT_CLIENT_ALERT" == "" ]]; do

			if [[ $ANSWER =~ ^-?[0-9]+$ && $ANSWER > 0 ]]; then
				COUNT_CLIENT_ALERT=$ANSWER
				echo -e "\033[1mNumber of client creation left : ${COUNT_CLIENT_ALERT}\033[0m"
				echo
			else
				echo
				echo -e "\033[1mError !\033[0m Invalid number !"
				echo
				echo -e "Please enter number of client creation left :"
				unset ANSWER
				read -p "(numbers only) " ANSWER
			fi
		done
	else
		COUNT_CLIENT_ALERT=30
		echo
	fi

    unset ANSWER
    echo -e "Do you want to activate engrais chimique module?"
    read -p "(y/n) " ANSWER
    echo
    while [[ "$ENGRAIS_CHIMIQUES" == "" ]]; do
    if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "N" ] ||[ "$ANSWER" == "n" ]; then
        ENGRAIS_CHIMIQUES=$ANSWER
        echo -e "\033[1mModule Engrais Chimiques : ${ENGRAIS_CHIMIQUES}\033[0m"
        echo
    else
        echo
        echo -e "\033[1mError !\033[0m Invalid input !"
        echo
        echo -e "Please enter a valid answer :"
        unset ANSWER
        read -p "(y / n) " ANSWER
    fi
    done

    unset ANSWER
    echo -e "Please enter the password of the database/database user: "
    echo -n "(by default it will be 'public') "
    read -s ANSWER
    echo
    #Gestion MDP par défault
    if [[ -z $ANSWER ]]
    then
    	PASSWORD_ADBANKING="public"
    fi
    while [[ "$PASSWORD_ADBANKING" -lt 0 ]]; do
        PASSWORD_ADBANKING=$ANSWER
        unset lenpwd
        lenpwd=${#PASSWORD_ADBANKING}
        #Gestion ';' dans le mot de passe
        if [[ ! "$ANSWER" == "" && "$ANSWER" == *";"* ]]
        then
            echo
            echo -e "\033[1mError !\033[0m Password should not contain the character ';'!"
            PASSWORD_ADBANKING=-1
        fi
        #Gestion des caractères speciales
        if [[ ! "$ANSWER" == "" && "$ANSWER" == *['!@#\$%^\&*()_+']* ]]
        then
            echo
            echo -e "\033[1mError !\033[0m Password should not contain special characters!"
            PASSWORD_ADBANKING=-1
        fi
        #Gestion nombres de caractères dans le mot de passe
        if [[ $lenpwd > 0 && $lenpwd -lt 8 ]]
        then
            echo
            echo -e "\033[1mError !\033[0m Password should contain at least 8 characters!"
            echo -e "The number of characters is $lenpwd which is less than 8!"
            PASSWORD_ADBANKING=-1
        fi
        if [[ "$PASSWORD_ADBANKING" -lt 0 ]]
        then
            unset ANSWER
            echo
            echo -e "Please re-enter a valid password: "
            echo -n "(by default it will be 'public') "
            read -s ANSWER
            echo
        else
            PASSWORD_ADBANKING=$ANSWER
            echo -e "\033[1mPassword for the database/database user has been registered!\033[0m"
            echo
        fi
    done

    unset ANSWER
    echo -e "Do you want to activate agency banking module?"
    read -p "(y/n) " ANSWER
    echo
    while [[ "$AGENCY_BANKING" == "" ]]; do
    if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "N" ] ||[ "$ANSWER" == "n" ]; then
        AGENCY_BANKING=$ANSWER
        echo -e "\033[1mModule Agency Banking : ${AGENCY_BANKING}\033[0m"
        echo
    else
        echo
        echo -e "\033[1mError !\033[0m Invalid input !"
        echo
        echo -e "Please enter a valid answer :"
        unset ANSWER
        read -p "(y / n) " ANSWER
    fi
    done

	# delete license files
	rm -rf ./licence.txt
	rm -rf ./licence2.txt

	# generate licence.txt
	make_license --passphrase 'adbankingpublic' --expire-on ${LICENCE_DATE} > licence.txt

	# generate licence2.txt
	php -f gen_licence2.php ${LICENCE_DATE} ${MODE_AGENCE} ${CODE_IDENTIFIER} ${AGENCE_SIEGE} ${MODE_COMPENSATION} ${NB_CLIENTS} ${COUNT_CLIENT_ALERT} ${ENGRAIS_CHIMIQUES} ${PASSWORD_ADBANKING} ${AGENCY_BANKING}
	echo

else
	echo -e "\033[1mExit !\033[0m"
	echo
fi
