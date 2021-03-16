<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="commission_agent">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        Nom agent;Total montant dépôt;Total montant retraits;Total commissions pour agent;Total commissions pour institution;Total commissions;Total clients créés
        <xsl:apply-templates select="agent"/>

    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="agent">
        <xsl:value-of select="translate(nom_agent,';','')"/>;<xsl:value-of select="translate(total_mnt_depot,';','')"/>;<xsl:value-of select="translate(total_mnt_retrait,';','')"/>;<xsl:value-of select="translate(total_mnt_comm_agent,';','')"/>;<xsl:value-of select="translate(total_mnt_comm_inst,';','')"/>;<xsl:value-of select="translate(total_mnt_comm_agent_inst,';','')"/>;<xsl:value-of select="translate(total_client,';','')"/>;
    </xsl:template>

</xsl:stylesheet>
