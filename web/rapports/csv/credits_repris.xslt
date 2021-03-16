<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="credits_repris">
  <xsl:apply-templates select="header"/>
	<xsl:apply-templates select="header_contextuel"/>
	<xsl:apply-templates select="produit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="produit">
		<xsl:call-template name="titre1"><xsl:with-param name="titre" select="libel"/></xsl:call-template>
		Numéro dossier;Numéro client;Ancien N° client;Nom client;Montant repris;Etat actuel;date reprise;
		<xsl:apply-templates select="credit_repris"/>
</xsl:template>

<xsl:template match="credit_repris">
	<xsl:value-of select="translate(num_doss,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(ancien_num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(mnt_repris,';','')"/>;<xsl:value-of select="translate(etat,';','')"/>;<xsl:value-of select="translate(date_reprise ,';','')"/>;
</xsl:template>

</xsl:stylesheet>
