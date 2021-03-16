<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="ajustements_caisse">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
			<xsl:call-template name="titre1">
				<xsl:with-param name="titre" select="'Ajustements de caisse'"/>
             </xsl:call-template>&#160;
			<xsl:call-template name="ajustements"/>
		
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
	;
</xsl:template>

<xsl:template name="ajustements">
    ;Utlisateur;Date ajustement;Excédent;Manquant;Total;
    <xsl:apply-templates select="ajustement"/>
	
</xsl:template>


<xsl:template match="ajustement">
   ;<xsl:value-of select="translate(utilisateur,';','')"/>;<xsl:value-of select="translate(date_ajustement,';','')"/>;<xsl:value-of select="translate(excedent,';','')"/>;<xsl:value-of select="translate(manquant,';','')"/>;<xsl:value-of select="translate(total,';','')"/>;
</xsl:template>

</xsl:stylesheet>