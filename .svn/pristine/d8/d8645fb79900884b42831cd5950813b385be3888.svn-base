<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:include href="header.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="mobile_lending">
        <xsl:apply-templates select="header"/>
        <!-- 	<xsl:apply-templates select="header_contextuel"/> -->
        <xsl:apply-templates select="analytics"/>
    </xsl:template>

    <!-- <xsl:template match="header_contextuel">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
        Critères ; <xsl:value-of select="translate(critere,';','')"/>;
        Nombre total de comptes ; <xsl:value-of select="translate(header_tot_compte,';','')"/>;
        Solde total des comptes ; <xsl:value-of select="translate(substring(header_tot_solde,1,string-length(header_tot_solde)-3),';','')"/>;
        Devise; <xsl:value-of select="translate(substring(header_tot_solde,string-length(header_tot_solde)-3),';','')"/>;
    </xsl:template> -->

    <xsl:template match="analytics">
        N° Client;IMF;Agence;ID agent;Localisation;Tranche localisation;Sexe;Tranche sexe;Salaire moyen;Tranche Salaire moyen;Longueur historique;Tranche longueur historique;Somme total emprunter;Tranche somme total emprunter;Nombre de crédits;Tranche nombre de crédits;Age;Tranche age;Taux irrégularité;Tranche taux irrégularité;Montant demandé;Date de déboursement;Nombre d'échéances;Retard échéance 1;Retard échéance 2;Retard échéance 3;Montant restant dû;Pénalité;Score retard crédit;Score client;Commentaire agent;
        <xsl:apply-templates select="infos"/>
    </xsl:template>


    <xsl:template match="infos">
        <xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(imf,';','')"/>;<xsl:value-of select="translate(agence,';','')"/>;<xsl:value-of select="translate(id_agent,';','')"/>;<xsl:value-of select="translate(localisation,';','')"/>;<xsl:value-of select="translate(tranche_localisation,';','')"/>;<xsl:value-of select="translate(sexe,';','')"/>;<xsl:value-of select="translate(tranche_sexe,';','')"/>;<xsl:value-of select="translate(sal_moy,';','')"/>;<xsl:value-of select="translate(tranche_sal_moy,';','')"/>;<xsl:value-of select="translate(lg_histo,';','')"/>;<xsl:value-of select="translate(tranche_lg_histo,';','')"/>;<xsl:value-of select="translate(somm_tot_emprunter,';','')"/>;<xsl:value-of select="translate(tranche_somm_tot_emprunter,';','')"/>;<xsl:value-of select="translate(nbre_credit,';','')"/>;<xsl:value-of select="translate(tranche_nbre_credit,';','')"/>;<xsl:value-of select="translate(age,';','')"/>;<xsl:value-of select="translate(tranche_age,';','')"/>;<xsl:value-of select="translate(tx_irregularite,';','')"/>;<xsl:value-of select="translate(tranche_tx_irregularite,';','')"/>;<xsl:value-of select="translate(mnt_dem,';','')"/>;<xsl:value-of select="translate(date_deboursement,';','')"/>;<xsl:value-of select="translate(nbre_ech,';','')"/>;<xsl:value-of select="translate(retard_ech_1,';','')"/>;<xsl:value-of select="translate(retard_ech_2,';','')"/>;<xsl:value-of select="translate(retard_ech_3,';','')"/>;<xsl:value-of select="translate(mnt_rest_du,';','')"/>;<xsl:value-of select="translate(penalite,';','')"/>;<xsl:value-of select="translate(score_retard_credit,';','')"/>;<xsl:value-of select="translate(score_client,';','')"/>;<xsl:value-of select="translate(commentaire,';','')"/>;
    </xsl:template>

</xsl:stylesheet>
