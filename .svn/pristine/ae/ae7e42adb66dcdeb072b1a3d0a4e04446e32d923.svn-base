<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="ps_reprises">
  <xsl:apply-templates select="header"/>
   <xsl:apply-templates select="liste_ps_reprise"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="liste_ps_reprise">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@type"/></xsl:call-template>;<xsl:value-of select="';'"/>
Numéro client;Ancien N° client;Nom client;Montant repris;date reprise;
<xsl:apply-templates select="ps_reprise"/>
</xsl:template>

<xsl:template match="ps_reprise">
<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(ancien_num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(mnt_ps_repris,';','')"/>;<xsl:value-of select="translate(date_reprise ,';','')"/>;
</xsl:template>

</xsl:stylesheet>
