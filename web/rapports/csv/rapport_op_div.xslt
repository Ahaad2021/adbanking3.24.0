<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="rapport_op_div">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/> 
		Numéro Transaction;Login;Date;Libellé opération;Numéro Client;Montant
		<xsl:apply-templates select="ligne"/>
		Total pour la période :; <xsl:value-of select="translate(total,';','')"/>;
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>
<xsl:template match="header_contextuel">
		<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>
<xsl:template match="ligne">
		<xsl:apply-templates select="details"/>
</xsl:template>

<xsl:template match="details">
	<xsl:value-of select="translate(num_transaction,';','')"/>;<xsl:value-of select="translate(login,';','')"/>;<xsl:value-of select="translate(date,';','')"/>;<xsl:value-of select="translate(libel_ecriture,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(montant,';','')"/>;
</xsl:template>

</xsl:stylesheet>
