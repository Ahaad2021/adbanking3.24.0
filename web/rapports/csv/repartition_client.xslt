<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="repartition_client">
  <xsl:apply-templates select="header"/> 
   <xsl:apply-templates select="detail_etat"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="detail_etat">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@type"/></xsl:call-template>;<xsl:value-of select="';'"/>
Num client;Nom client;Date d'adhésion;Date défection;Statut;
<xsl:apply-templates select="clients"/>
</xsl:template>

<xsl:template match="clients">
	<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(date_adh,';','')"/>;<xsl:value-of select="translate(date_etat,';','')"/>;<xsl:value-of select="translate(statut,';','')"/>;
</xsl:template>

</xsl:stylesheet>




















