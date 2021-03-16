<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>
    <xsl:template match="budget_etatbudgetaire">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="infos_etat"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="infos_etat">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Etat d éxécution budgetaire'"/></xsl:call-template>;
        Poste;Description;Budget annuel;Budget de la période;Réalisation de la période;Performance de la période (en %);Performance par rapport au budget annuelle(en %);
        <xsl:apply-templates select="type_budget/details"/>;
    </xsl:template>

    <xsl:template match="type_budget/details">
        <xsl:value-of select="translate(poste,';','')"/>;<xsl:value-of select="translate(description,';','')"/>;<xsl:value-of select="translate(budget_annuel,';','')"/>;<xsl:value-of select="translate(budget_periode,';','')"/>;<xsl:value-of select="translate(realisation_period,';','')"/>;<xsl:value-of select="translate(performance_period,';','')"/>;<xsl:value-of select="translate(performance_annuelle,';','')"/>;
    </xsl:template>


    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>


</xsl:stylesheet>
