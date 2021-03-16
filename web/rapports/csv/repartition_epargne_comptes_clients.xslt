<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>


    <xsl:include href="header.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="concentration_epargne">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="niveau1"/>
    </xsl:template>

    <xsl:template match="header_contextuel">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
        Critères ; <xsl:value-of select="translate(critere,';','')"/>;
        Nombre total de comptes ; <xsl:value-of select="translate(nb1,';','')"/>;
        Nombre total de clients ; <xsl:value-of select="translate(nbc1,';','')"/>;
        Solde total comptes ; <xsl:value-of select="translate(nb2,';','')"/>;
        Solde total clients ; <xsl:value-of select="translate(nbc2,';','')"/>
    </xsl:template>

    <xsl:template match="niveau1">
        ;
        <xsl:value-of select="translate(lib_niveau1,';','')"/>;
        Libellé;Nbre de comptes;Nbre de clients;% nbre cptes;% nbre clients;Solde Cptes;Solde clients;% solde cptes;%solde clients
        <xsl:apply-templates select="niveau2"/>
        <xsl:apply-templates select="total"/>
    </xsl:template>

    <xsl:template match="niveau2">
        <xsl:value-of select="translate(lib_niveau2,';','')"/>;<xsl:value-of select="nb_compte"/>;<xsl:value-of select="nb_client"/>;<xsl:value-of select="nb_prc"/>;<xsl:value-of select="nb_prc_client"/>;<xsl:value-of select="solde_compte"/>;<xsl:value-of select="solde_client"/>;<xsl:value-of select="solde_prc"/>;<xsl:value-of select="solde_prc_client"/>;
    </xsl:template>

    <xsl:template match="total">
        Total général;<xsl:value-of select="tot_nb_compte"/>;<xsl:value-of select="tot_nb_client"/>;<xsl:value-of select="tot_nb_prc"/>;<xsl:value-of select="tot_nb_prc_client"/>;<xsl:value-of select="tot_solde_compte"/>;<xsl:value-of select="tot_solde_client"/>;<xsl:value-of select="tot_solde_prc"/>;<xsl:value-of select="tot_solde_prc_client"/>
    </xsl:template>

</xsl:stylesheet>