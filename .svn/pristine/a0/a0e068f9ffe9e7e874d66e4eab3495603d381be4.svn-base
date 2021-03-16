#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.16.0"
VERSION="3.16.1"
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh 

echo -e "Mise à jour ADbanking \033[1mv${OLD_VERSION} -> v${VERSION}\033[0m"

# Mise à jour structure
execute_psql 0 updata3.16.1.sql
execute_psql 0 getportfeuilleview.sql

# Mise a jour structure
execute_psql 0 patch_reprise_676/patch_ticket_676.sql

#########################################################################################################
# Reprise de données de ad_cpt pour les comptes DAT - ref. #544: ticket 676
echo
#reprise de données pour compte DAT
 echo -e "*------------------------------------------------------*"
 echo -e "\033[1m*---REPRISE DE DONNEES POUR COMPTE DAT(Ticket 676)---*\033[0m"
 echo -e "*------------------------------------------------------*"

echo
 #user prompt
 read -p "Procéder à la reprise de données pour compte DAT ? : (y/n) pour oui / non ? :" CHOIX
echo
#echo $CHOIX
 if [ "$CHOIX" == "y" ] || [ "$CHOIX" == "Y" ]
 then


    TEST=`return_psql "SELECT count(id_his) from ad_his where type_fonction =501"`
    #echo $TEST

    if [ "$TEST" -eq 1 ]
       then
         #Cas 1: Une seule id_his pour la fonction de reprise
         echo -e "****Un seul id_his pour la fonction de reprise(501)- Reprise generique****"
         execute_psql 0 patch_reprise_676/script_reprise_generique.sql

         echo -e "Reprise terminé !"

    else if [ "$TEST" -gt 1 ]
         then
         #Cas 2: Plusieurs id_his pour la fonction de reprise
         echo -e "****Plusieurs id_his pour la fonction de reprise(501)- Reprise par id_his specifique***"
         echo
         echo -e "Démarrage de la reprise partie 1-Insertion des données avant la reprise"
         #execution pour la partie 1
         execute_psql 0 patch_reprise_676/script_reprise_specifique_par_id_his_part1.sql

         #execution de la reprise
         return_psql "SELECT reprise_dat_part1();"
          #Drop function quand la reprise est terminé
         return_psql "DROP FUNCTION reprise_dat_part1()"
        echo -e "Reprise partie 1 terminé : Function reprise dropped. "
        echo
        echo
       echo -e "Démarrage de la reprise partie 2 -Insertion des données aprés la reprise"
      
       echo
         #user prompt
         read -p "Procéder à la saisie des id_his : (y/n) pour oui / non ? :" CHOIX

        #echo $CHOIX
         if [ "$CHOIX" == "y" ] || [ "$CHOIX" == "Y" ]
         then
         
          TEST=`return_psql "SELECT id_his from ad_his where type_fonction =501"`
       echo
       echo -e "Voici les id_his présents dans la base pour la fonction 501:"
       echo -e $TEST
         
         #variable declaration for the loop
         COUNTER=1
         declare -A arrayid
         ID_HIS=1
             # traitement prompt
             while [ "$ID_HIS" != "x" ]
           do
             #user prompt
             echo
             read -p "Inserez un id_his pour la reprise :(Tapez 'x' pour finir) :" ID_HIS
        #echo $COUNTER
        #echo $ID_HIS
             while [ "$ID_HIS" != "x" ]
           do
             VERIFExist=`return_psql "SELECT count(id_his) from ad_his where type_fonction =501 AND  id_his ='${ID_HIS}'"`
             if [ "$VERIFExist" -eq 1 ]
             then
              arrayid[$COUNTER]=$ID_HIS
              COUNTER=$((COUNTER+1))
              else
              echo
              echo -e "-----Mauvaise saisie : id_his $ID_HIS n'existe pas ----"
              echo
             fi

             #user prompt
             #read -p "Y-a-t'il encore des id_his à insérer?: (y/n)  pour oui / non ? :" CHOIX
             read -p "Inserez un id_his pour la reprise :(Tapez 'x' pour finir) :" ID_HIS
             done

           done
           fi

            #echo ${!arrayid[@]}
            #echo ${arrayid[*]}
           echo
           echo -e "Fin de Saisie des id_his !! Démarrage de la reprise par id_his "
           echo
            #${!name[@]} and ${!name[*]}

            #echo ${#arrayid[@]}
           xc=${#arrayid[@]}

           if [ "$xc" -ne 0 ]
              then
                # creation/replace function reprise_ad_part_sociale_his
                execute_psql 0 patch_reprise_676/script_reprise_specifique_par_id_his_part2.sql

            for i in "${!arrayid[@]}"
              do
    #echo "Clé id_his :" $i
                echo "Reprise pour id_his:" ${arrayid[$i]}
    #echo "SELECT reprise_dat_part2('${arrayid[$i]}');"
               return_psql "SELECT reprise_dat_part2('${arrayid[$i]}');"
             done
           fi

      #Drop function quand la reprise est terminé
      return_psql "DROP FUNCTION reprise_dat_part2(integer)"
      echo -e "Reprise partie 2 terminé : Function reprise dropped. "
      echo -e "Reprise terminé !"
      echo
      echo
    else
        echo -e "**** Pas de id_his pour la fonction de reprise DAT (501) : Aucun traitement ***"

    fi
    fi

 fi

    #Fin MAJ reprise de donnees pour compte DAT: ticket 676
     echo
     echo -e "\033[1m---FIN REPRISE DE DONNEES POUR COMPTE DAT(Ticket 676)---\033[0m"
     echo
     echo

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source /usr/share/adbanking/db/info_systeme.sh

  echo
  
###############################################################################
echo -e "---- DB VACUUM in progress......... ----"
# vacuum de la base
execute_psql 'cmd' "VACUUM FULL ANALYZE"

echo -e "----- FIN TRAITEMENT -----"
