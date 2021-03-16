#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.12.2"
VERSION="3.14.0"
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh 

echo -e "Mise à jour ADbanking \033[1mv${OLD_VERSION} -> v${VERSION}\033[0m"

# Est-ce toujours utile ? -- antoine
psql7=`return_psql "SELECT COUNT(*) FROM pg_catalog.pg_trigger WHERE tgconstrname = (SELECT CHR(36)||'1') AND tgargs LIKE '%1%menus%ad_str%libel_menu%id_str%';"`
if [[ $psql7 > 0 ]]; then
  # Mise à jour de préparation au passage vers PostgreSQL 8 (donc également compatible avec la 7.4)
  # On fait cette mise à jour uniquement si elle n'a pas déjà eu lieu dans le passé.
  # On ignore les messages d'erreurs sur triggers.so et trig_suppr_trad car ils ne sont plus utilisés, voir #1056.
  (execute_psql 0 ../psql8/updata-psql8.sql) 3>&1 1>&2 2>&3 | grep -v triggers.so | grep -v suppr_trad | grep -v del_dans_ad_trad
fi

# Relecture des fonctions et triggers
execute_psql 0 ../Dump/triggers.sql

# Relecture des fonction et des écrans
execute_psql 0 ../Dump/fonction_comptable.sql
execute_psql 0 ../Dump/fonctions.sql
execute_psql 0 ../Dump/tableliste.sql
execute_psql 0 ../Dump/menus.sql

# Mise à jour structure
execute_psql 0 updata3.14.sql
execute_psql 0 fix_getportefeuilleview2.sql

execute_psql 0 patch_tickets/patch_ticket_491.sql
execute_psql 0 patch_tickets/patch_ticket_527.sql
execute_psql 0 patch_tickets/patch_ticket_507.sql
execute_psql 0 patch_tickets/patch_ticket_549.sql
execute_psql 0 patch_tickets/patch_ticket_567.sql
execute_psql 0 patch_tickets/creation_ecrans_menus_437.sql


#disable triggers-537
return_psql "ALTER TABLE ad_cpt DISABLE TRIGGER trig_before_update_ad_cpt;"

#537 :
execute_psql 0 patch_tickets/patch_ticket_537.sql
execute_psql 0 patch_tickets/fix_arrete_compte_537.sql

#enable triggers-537
return_psql "ALTER TABLE ad_cpt ENABLE TRIGGER trig_before_update_ad_cpt;"



# Evolutions adbanking pour le SMS Banking/API #475 + #476:
execute_psql 0 script_evo_api_adbanking.sql

# Ligne de credit
execute_psql 0 update490/patch_script_490.sql

# Change mode
chmod 755 /usr/share/adbanking/web/multiagence/batchs/ALIM_SIEGE.sh
chmod 755 /usr/share/adbanking/web/multiagence/jobs/ALIM_SIEGE_0.1/ALIM_SIEGE/ALIM_SIEGE_run.sh


# Rechargement des traductions
source $ADB_INSTALL_DIR/bin/reloadlangues.sh

#########################################################################################################################
# Gestion de part sociales: ticket 361
 echo
#reprise ancienne souscriptions
 echo -e "*------------------------------------------------------*"
 echo -e "\033[1m*---REPRISE DES ANCIENNES SOUSCRIPTIONS(Ticket 361)--*\033[0m"
 echo -e "*------------------------------------------------------*"

execute_psql 0 update361/patch_script_361.sql

     echo
#user prompt
     read -p "Procéder à la reprise des anciennes souscriptions ? : (y/n) pour oui / non ? :" CHOIX
     echo
#echo $CHOIX
 if [ "$CHOIX" == "y" ] || [ "$CHOIX" == "Y" ]
 then

        TEST=`return_psql "SELECT count(id_his) from ad_his where type_fonction =500"`

        if [ "$TEST" -eq 1 ]
           then
             #Cas 1: Une seule id_his pour la fonction de reprise
             echo -e "****Un seul id_his pour la fonction de reprise(500)- Reprise generique****"
             execute_psql 0 update361/script_reprise_anc_souscription.sql

             echo -e "Reprise terminé !"
        else if [ "$TEST" -gt 1 ]
            then
             #Cas 2: Plusieurs id_his pour la fonction de reprise
             echo -e "****Plusieurs id_his pour la fonction de reprise(500)- Reprise par id_his specifique***"
             echo
             echo -e "Démarrage de la reprise partie 1-Insertion des données avant la reprise"
             #execution pour la partie 1
             execute_psql 0 update361/script_reprise_anc_souscription_par_id_his_part1.sql

             #execution de la reprise
             return_psql "SELECT reprise_ad_part_sociale_his_part1();"
              #Drop function quand la reprise est terminé
          return_psql "DROP FUNCTION reprise_ad_part_sociale_his_part1()"
          echo -e "Reprise partie 1 terminé : Function reprise dropped. "
          echo
          echo
           echo -e "Démarrage de la reprise partie 2 -Insertion des données aprés la reprise"
           
           echo
             #user prompt
             read -p "Procéder à la saisie des id_his : (y/n) pour oui / non ? :" CHOIX

             if [ "$CHOIX" == "y" ] || [ "$CHOIX" == "Y" ]
             then
             TEST=`return_psql "SELECT id_his from ad_his where type_fonction =500"`
           echo
           echo -e "Voici les id_his présents dans la base pour la fonction 500:"
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

                 while [ "$ID_HIS" != "x" ]
               do
                 VERIFExist=`return_psql "SELECT count(id_his) from ad_his where type_fonction =500 AND  id_his ='${ID_HIS}'"`
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
                 read -p "Inserez un id_his pour la reprise :(Tapez 'x' pour finir) :" ID_HIS
                 done

               done
               fi

               echo
               echo -e "Fin de Saisie des id_his !! Démarrage de la reprise par id_his "
               echo

               xc=${#arrayid[@]}

               if [ "$xc" -ne 0 ]
                  then
                    # creation/replace function reprise_ad_part_sociale_his
                    execute_psql 0 update361/script_reprise_anc_souscription_par_id_his_part2.sql

                for i in "${!arrayid[@]}"
                  do
                    echo "Reprise pour id_his:" ${arrayid[$i]}
                   return_psql "SELECT reprise_ad_part_sociale_his_part2('${arrayid[$i]}');"
                 done
               fi

          #Drop function quand la reprise est terminé
          return_psql "DROP FUNCTION reprise_ad_part_sociale_his_part2(integer)"
          echo -e "Reprise partie 2 terminé : Function reprise dropped. "
          echo -e "Reprise terminé !"
          echo
          echo
           else
            echo -e "**** Pas de id_his pour la fonction de reprise PS (500) : Aucun traitement ***"
         fi
        fi


 fi
        #Fin MAJ Gestion de part sociales: ticket 361
        echo
        echo -e "\033[1m---FIN de la REPRISE DES ANCIENNES SOUSCRIPTIONS(ticket 361)---\033[0m"
        echo
        echo


#########################################################################################################
# Reprise de données de ad_cpt pour les comptes DAT - ref. #544: ticket 558
echo
#reprise de données pour compte DAT
 echo -e "*------------------------------------------------------*"
 echo -e "\033[1m*---REPRISE DE DONNEES POUR COMPTE DAT(Ticket 558)---*\033[0m"
 echo -e "*------------------------------------------------------*"

execute_psql 0 update558/patch_ticket_558.sql
echo
 #user prompt
 read -p "Procéder à la reprise de données pour compte DAT ? : (y/n) pour oui / non ? :" CHOIX
echo
#echo $CHOIX
 if [ "$CHOIX" == "y" ] || [ "$CHOIX" == "Y" ]
 then


    TEST=`return_psql "SELECT count(id_his) from ad_his where type_fonction =501"`

    if [ "$TEST" -eq 1 ]
       then
         #Cas 1: Une seule id_his pour la fonction de reprise
         echo -e "****Un seul id_his pour la fonction de reprise(501)- Reprise generique****"
         execute_psql 0 update558/script_reprise_generique.sql

         echo -e "Reprise terminé !"

    else if [ "$TEST" -gt 1 ]
         then
         #Cas 2: Plusieurs id_his pour la fonction de reprise
         echo -e "****Plusieurs id_his pour la fonction de reprise(501)- Reprise par id_his specifique***"
         echo
         echo -e "Démarrage de la reprise partie 1-Insertion des données avant la reprise"
         #execution pour la partie 1
         execute_psql 0 update558/script_reprise_specifique_par_id_his_part1.sql

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
             read -p "Inserez un id_his pour la reprise :(Tapez 'x' pour finir) :" ID_HIS
             done

           done
           fi

           echo
           echo -e "Fin de Saisie des id_his !! Démarrage de la reprise par id_his "
           echo

           xc=${#arrayid[@]}

           if [ "$xc" -ne 0 ]
              then
                # creation/replace function reprise_ad_part_sociale_his
                execute_psql 0 update558/script_reprise_specifique_par_id_his_part2.sql

            for i in "${!arrayid[@]}"
              do
                echo "Reprise pour id_his:" ${arrayid[$i]}
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

    #Fin MAJ reprise de donnees pour compte DAT: ticket 558
     echo
     echo -e "\033[1m---FIN REPRISE DE DONNEES POUR COMPTE DAT(Ticket 558)---\033[0m"
     echo
     echo
###############################################################################
# Bug gestion des arrondis des soldes dans adbanking: ticket 514
 echo
#lanceur script de correction d'arrondis
echo -e "*--------------------------------------------------------*"
echo -e "\033[1m*---VERIFICATION DES ARRONDIS A CORRIGER(Ticket 514)---*\033[0m"
echo -e "*--------------------------------------------------------*"

 TEST=`return_psql "select count(id_cpte) from ad_cpt where solde %1 >0"`

 if [ "$TEST" -gt 0 ]
  then
    echo -e
    echo -e "***** \033[1mALERTE :\033[0m Des arrondis ont été detecté dans les comptes clients ****"
    echo -e "**** \033[1mIMPORTANT :\033[0m Il faudra effectuer une correction de ces arrondis pour eviter les incoherences ***"
    echo -e
    read -p "Entrez le numéro du compte comptable à utiliser en contre-partie pour ré-équilibrer les soldes arrondies : " COMPTE
#echo $COMPTE

    TEST=`return_psql "select count(num_cpte_comptable) from ad_cpt_comptable where num_cpte_comptable = '${COMPTE}'"`

   while [ "$TEST" -ne 1 ]
    do
     read -p "Ce compte n'existe pas en base. Veuillez entrer un autre numéro de compte : " COMPTE
      TEST=`return_psql "select count(num_cpte_comptable) from ad_cpt_comptable where num_cpte_comptable = '${COMPTE}'"`
    done

# Mise a jour structure
    execute_psql 0 update514/patch_ticket_514.sql
    return_psql "SELECT correction_arrondis('${COMPTE}');"

     echo
     echo -e "\033[1mCorrection d'arrondis terminé !\033[0m"
  else
    echo
    echo -e "**** Pas de correction arrondis à effectuer : Aucun traitement ***"
 fi

 #Fin VERIFICATION D'ARRONDIS : ticket 514
  echo
  echo -e "\033[1m---FIN de la VERIFICATION D'ARRONDIS(ticket 514)---\033[0m"
  echo
  echo
  
###############################################################################
echo -e "---- DB VACUUM in progress......... ----"
# vacuum de la base
execute_psql 'cmd' "VACUUM FULL ANALYZE"

echo -e "----- FIN TRAITEMENT -----"
