%~d0
 cd %~dp0
 java -Xms256M -Xmx1024M -cp ../lib/dom4j-1.6.1.jar;../lib/ini4j-0.5.1.jar;../lib/postgresql-8.3-603.jdbc3.jar;../lib/talendcsv.jar;../lib/talend_file_enhanced_20070724.jar;../lib/systemRoutines.jar;../lib/userRoutines.jar;.;alim_siege_0_2.jar;recup_data_agence_0_2.jar; alim_siege.alim_siege_0_2.ALIM_SIEGE --context=Linux %* 