<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="frais_attente">
  <xsl:apply-templates select="header"/>  
  <xsl:apply-templates select="header_contextuel"/>
Type frais;Date frais;Montant frais;Compte;Numéro client;Nom client;
  <xsl:apply-templates select="attente"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>

<xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
	<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>

<xsl:template match="infos_synthetiques">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
Nombre total attentes;Montant total frais;
<xsl:value-of select="translate(total_attente,';','')"/>;<xsl:value-of select="translate(total_frais,';','')"/>;
</xsl:template>

<xsl:template match="attente">    	
	<xsl:value-of select="translate(type_frais,';','')"/>;<xsl:value-of select="translate(date_frais,';','')"/>;<xsl:value-of select="translate(mnt_frais,';','')"/>;<xsl:value-of select="translate(num_compte,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;
</xsl:template>

</xsl:stylesheet>
