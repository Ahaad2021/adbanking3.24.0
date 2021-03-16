#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

############################################################
# Fonctions utiles
############################################################
terminer()
{
  dialog --clear --title "Erreur" --msgbox "L'installation ne s'est pas terminée correctement" 0 0
  rm -f $RESULT
  exit 1
}

############################################################
# main()
############################################################

# Vérification de l'utilisateur
#------------------------------
if [ $USER != 'root' ];
then
  echo "Erreur : il faut être root pour exécuter cette commande"
  exit 1
fi

# Vérification du répertoire d'installation
#------------------------------------------
if [ ! -e $ADB_INSTALL_DIR ];
then
  dialog --clear --title "Erreur" --msgbox "Le répertoire $ADB_INSTALL_DIR n'existe pas" 0 0
  exit 1;
fi

# Choix des langues dans lesquelles ADbanking sera installé
#----------------------------------------------------------
LANGUES_POSSIBLES=""
echo `/bin/ls $ADB_INSTALL_DIR/web/locale`
for langue in `/bin/ls $ADB_INSTALL_DIR/web/locale`
do
  #pour chaque sous-répertoire de $ADB_INSTALL_DIR/web/locale
  if [ -d $ADB_INSTALL_DIR/web/locale/$langue ]
  then
    get_nom_langue $langue
    LANGUES_POSSIBLES="$LANGUES_POSSIBLES $langue $nom_langue on"
  fi
done

LANG_INSTALLEES=`dialog --stdout --clear --backtitle "Installation de la base de données ADbanking" \
    --title "Choix des langues d'installation" \
    --separate-output \
    --checklist "Veuillez cocher les codes langues des langues dans lesquelles ADbanking doit être installé" 0 0 0 $LANGUES_POSSIBLES`
if [ ! "$LANG_INSTALLEES" ]; then
  terminer
fi

# Choix de la langue système par défaut
#--------------------------------------
LANGUES_POSSIBLES=""
for langue in $LANG_INSTALLEES
do
  get_nom_langue $langue
  LANGUES_POSSIBLES="$LANGUES_POSSIBLES $langue $nom_langue"
done

# Pour le moment, la langue système par défaut est toujours fr_BE
# Il faudrait que les traductions dans les autres langues soient fiables
# et disponibles de manière complète pour que l'on puisse proposer une autre langue
LANG_SYST_DFT="fr_BE"

#LANG_SYST_DFT=`dialog --stdout --backtitle "Installation de la base de données ADbanking" \
#    --title "Choix de la langue système par défaut" \
#    --menu "Veuillez choisir le code langue de la langue système par défaut" \
#    0 0 0 $LANGUES_POSSIBLES`
#if [ ! "$LANG_SYST_DFT" ]; then
#  terminer
#fi

echo -n "Installation d'ADbanking en"
for langue in $LANG_INSTALLEES
do
  echo -n " $langue"
done
echo " avec par défaut $LANG_SYST_DFT"

# Création d'une BD initiale vide
#--------------------------------
$ADB_INSTALL_DIR/db/new_empty_db.sh

echo -e "\033[1mNous continuons la procédure de création !\033[0m"
echo -e "\033[1m==========\033[0m Creation des \033[1mtriggers\033[0m"
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/triggers.sql
echo -e "\033[1m==========\033[0m Création des \033[1mtables\033[0m"
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/DB.sql
echo -e "\033[1m==========\033[0m Création des \033[1mfonctions SQL\033[0m"
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/fonctions.sql
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/frais_tenue_cpt.sql
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/calcul_interets_debiteurs.sql
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/fonction_comptable.sql
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/arrete_compte.sql
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/report_fonctions.sql


# Création de la langue système par défaut et chargement des menus et tables de paramétrage
#------------------------------------------------------------------------------------------
echo -e "\033[1m==========\033[0m Création de la \033[1mlangue système\033[0m par défaut"
get_nom_langue $LANG_SYST_DFT
SQL_LANGUE="INSERT INTO ad_str DEFAULT VALUES;
        INSERT INTO adsys_langues_systeme(code, langue) SELECT '$LANG_SYST_DFT', max(ad_str.id_str) FROM ad_str;
        INSERT INTO ad_traductions (id_str, langue, traduction) SELECT max(ad_str.id_str), '$LANG_SYST_DFT', '$nom_langue' FROM ad_str;"
execute_psql 'cmd' "$SQL_LANGUE"

# Initialisation des données de l'agence
#---------------------------------------
echo -e "\033[1m==========\033[0m \033[1mInitialisation\033[0m des données"
echo -e "Entrez l'identifiant de l'\033[1magence\033[0m à créer (entier) :"
read agence
echo $agence | grep [^0-9] > /dev/null 2>&1
while [[ "$?" -eq "0" ]]; do
  # Si grep a trouvé autre chose que 0-9, alors c'est pas un entier
  echo -e "\033[1mL'identifiant d'une agence doit être un entier\033[0m"
  echo -e "Entrez l'identifiant de l'\033[1magence\033[0m à créer (entier) :"
  read agence
  echo $agence | grep [^0-9] > /dev/null 2>&1
done
if [[ $agence == "" ]]; then
  agence=0
fi

unset codeantenne
echo -e "Entrez le code de l'\033[1mantenne\033[0m (entier).  Si pas d'\033[1mantenne laissez vide\033[0m :"
read codeantenne
echo $codeantenne | grep [^0-9] > /dev/null 2>&1
while [[ "$?" -eq "0" ]]; do
  # Si grep a trouvé autre chose que 0-9, alors c'est pas un entier
  echo -e "\033[1mLe code de l'antenne doit être un entier\033[0m"
  echo -e "Entrez le code de l'\033[1mantenne\033[0m (entier).  Si pas d'\033[1mantenne laissez vide\033[0m :"
  read codeantenne
  echo $codeantenne | grep [^0-9] > /dev/null 2>&1
done
if [[ $codeantenne == "" ]]; then
  codeantenne=0
fi

datedebdef='01/01/'`date +%Y`
datefindef='31/01/'`date +%Y`
echo -e "Entrez la date de \033[1mdébut\033[0m de l'exercice : (jj/mm/aaaa ou jj-mm-aaaa) - Défaut $datedebdef"
read datedeb
echo -e "Entrez la date de \033[1mfin\033[0m de l'exercice : (jj/mm/aaaa ou jj-mm-aaaa) - Défaut $datefindef"
read datefin
# Test validité des dates
TEST=`return_psql "SELECT date('${datedeb:=$datedebdef}') < date('${datefin:=$datefindef}');"`
while [[ "$TEST" != "t" ]]; do
  echo -e "\033[1mDates incorrectes !\033[0m"
  echo -e "Entrez la date de \033[1mdébut\033[0m de l'exercice : (jj/mm/aaaa ou jj-mm-aaaa)"
  read datedeb
  echo -e "Entrez la date de \033[1mfin\033[0m de l'exercice : (jj/mm/aaaa ou jj-mm-aaaa)"
  read datefin
  TEST=`return_psql "SELECT date('${datedeb}') < date('${datefin}');"`
done
execute_psql 0 "${ADB_INSTALL_DIR}/db/Dump/start.sql" "-v debutexo=\'${datedeb:=$datedebdef}\' -v finexo=\'${datefin:=$datefindef}\' -v agence=${agence} -v codeAntenne=\'${codeantenne}\'"

# Création des autres langues systèmes et chargement des menus et tables de paramétrage
#--------------------------------------------------------------------------------------
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/tableliste.sql
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/menus.sql
echo -e "\033[1m==========\033[0m Création des autres langues"
for langue in $LANG_INSTALLEES
do
  if [ $langue != $LANG_SYST_DFT ];
  then
    get_nom_langue $langue
    SQL_LANGUE="INSERT INTO ad_str DEFAULT VALUES;
                INSERT INTO adsys_langues_systeme(code, langue) SELECT '$langue', max(ad_str.id_str) FROM ad_str;
                INSERT INTO ad_traductions (id_str, langue, traduction) SELECT max(ad_str.id_str), '$LANG_SYST_DFT', '$nom_langue' FROM ad_str;"
    execute_psql 'cmd' "$SQL_LANGUE"
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/${langue}.sql
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/tableliste.sql
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/menus.sql
  fi
done

echo -e "\033[1m==========\033[0m Création du \033[1mprofil administrateur\033[0m"
execute_psql 0 $ADB_INSTALL_DIR/db/Dump/administrateur.sql "-v agence=${agence}"

echo -e "\033[1m==========\033[0m Création du \033[1mplan comptable\033[0m"
# Il ne faut pas obliger à créer le plan PARMEC standard
echo -e "Voulez-vous créer un exemple de \033[1mplan PARMEC\033[0m ? (O pour Oui et \033[1mN pour Non\033[0m)"
read TEST
if [[ "${TEST:=N}" != "o" && "${TEST:=N}" != "O" && "${TEST:=N}" != "y" && "${TEST:=N}" != "Y" ]] ; then
  # Si non, demander s'il faut charger un autre plan comptable
  ask_file "d'un \033[1mautre plan comptable\033[0m (ou n'entrez rien pour ne charger aucun plan comptable)"
  if [[ "$FILE_NAME" != "" ]] ; then
    execute_psql 0 $FILE_NAME
  fi
else
  echo -e "Création du \033[1mplan comptable PARMEC\033[0m"
  execute_psql 0 $ADB_INSTALL_DIR/db/Dump/PARMEC.sql "-v agence=${agence}"
fi

# Fin de l'installation
#----------------------
echo
echo
echo
echo -e "\033[1m==========\033[0m"
echo -e "L'installation d'\033[1mADbanking v3\033[0m est terminée."
echo -e "Vous pouvez aller à \033[1mhttp://localhost/\033[0m pour vous connecter et faire le paramétrage."
echo -e "\033[1m==========\033[0m"
echo
