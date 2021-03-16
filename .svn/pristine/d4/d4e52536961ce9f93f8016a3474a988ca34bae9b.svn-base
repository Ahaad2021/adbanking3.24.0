<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="listecomptes">
		<xsl:apply-templates select="header"/>
		Rang;Numéro client;Nom client;Numéro compte;Produit d'épargne;Solde;
		<xsl:apply-templates select="details"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="details">
	<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(idclient,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(numcpt,';','')"/>;<xsl:value-of select="translate(libel,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;
</xsl:template>

</xsl:stylesheet>
