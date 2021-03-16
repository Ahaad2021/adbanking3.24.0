<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="repartition_credit">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>

Libelle Tranche;Nombre de crédits;% des crédits;Montant portefeuille;% portefeuille;Montant portefeuille en retard;% portefeuille en retard;
<xsl:apply-templates select="tranche"/>
</xsl:template>
	
<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>;
Critère ;<xsl:value-of select="critere"/>;<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Concentration par ', concentration_par)"/></xsl:call-template>;
</xsl:template>

<xsl:template match="tranche">
	<xsl:value-of select="translate(translate(lib_tranche,';',''),',','')"/>;<xsl:value-of select="translate(nbre,';','')"/>;<xsl:value-of select="translate(nbre_prc,';','')"/>;<xsl:value-of select="translate(mnt,';','')"/>;<xsl:value-of select="translate(mnt_prc,';','')"/>;<xsl:value-of select="translate(retard,';','')"/>;<xsl:value-of select="translate(retard_prc,';','')"/>;
</xsl:template>

</xsl:stylesheet>
