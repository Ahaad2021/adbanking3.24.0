<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="rapport_compte_dormant">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="ligneCompteDormant"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="ligneCompteDormant">
        <xsl:value-of select="translate(lib_prod,';','')"/>;;;;;
        N° client;N° compte;Nom du client;Solde;Date blocage;
        <xsl:apply-templates select="infosCompteDormant"/>
        <xsl:apply-templates select="xml_total"/>
    </xsl:template>

    <xsl:template match="infosCompteDormant">
        <xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of
            select="translate(num_compte,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of
            select="translate(solde_compte,';','')"/>;<xsl:value-of select="translate(date_blocage,';','')"/>;
    </xsl:template>

    <xsl:template match="xml_total">
        ;;Sous Total;<xsl:value-of
            select="translate(tot_solde_cpte,';','')"/>;;
    </xsl:template>

</xsl:stylesheet>
