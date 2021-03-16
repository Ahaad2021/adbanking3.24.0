<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:output method="text"/>
	
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="credit_echeance">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>
<xsl:apply-templates select="ligne_credit"/>
<xsl:apply-templates select="total_general1"/>
</xsl:template>

<xsl:template match="ligne_credit">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="echeance"/></xsl:call-template>;
;
Numéro dossier;Numéro client;Nom client;Date échéance;Capital;Intérêts;Garanties;Montant réécholonnement;Solde capital;Total échéance;Devise;
<xsl:apply-templates select="ligne"/>
<xsl:apply-templates select="sous_total1"/> 
</xsl:template>

<xsl:template match="ligne">
	<xsl:value-of select="translate(num_doss,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(date_ech,';','')"/>;<xsl:value-of select="translate(mnt_cap,';','')"/>;<xsl:value-of select="translate(mnt_int,';','')"/>;<xsl:value-of select="translate(mnt_gar,';','')"/>;<xsl:value-of select="translate(mnt_reech,';','')"/>;<xsl:value-of select="translate(solde_cap,';','')"/>;<xsl:value-of select="translate(cap_rest,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;
</xsl:template>

<xsl:template match="sous_total">
<xsl:for-each select="*">
   <xsl:value-of select="translate(.,';','')"/>
   <xsl:if test="position() != last()">
    <xsl:value-of select="';'"/>
   </xsl:if>
  </xsl:for-each>
<xsl:text disable-output-escaping="yes">
</xsl:text>
</xsl:template>

<xsl:template match="total_general">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'TOTAL'"/></xsl:call-template>
<xsl:for-each select="*">
   <xsl:value-of select="translate(.,';','')"/>
   <xsl:if test="position() != last()">
    <xsl:value-of select="';'"/>
   </xsl:if>
  </xsl:for-each>
<xsl:text disable-output-escaping="yes">
</xsl:text>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>


</xsl:stylesheet>
