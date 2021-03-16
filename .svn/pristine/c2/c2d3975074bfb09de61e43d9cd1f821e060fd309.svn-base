<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="provisioncredit">
		<xsl:apply-templates select="header"/>
	        <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="produit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="produit">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="libel_prod"/></xsl:call-template>;
	Numéro client;Nom client;Numéro dossier;Etat;Date Etat;Capital restant dû;Garanties numéraires;Date provision;Montant provision;
	<xsl:apply-templates select="client"/>
	<xsl:apply-templates select="client_credit_gs"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(num_dossier,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(cre_etat_date,';','')"/>;<xsl:value-of select="translate(capital_du,';','')"/>;<xsl:value-of select="translate(gar_num,';','')"/>;<xsl:value-of select="translate(prov_date,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;
</xsl:template>

<xsl:template match="client_credit_gs">
   <xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(num_dossier,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(cre_etat_date,';','')"/>;<xsl:value-of select="translate(capital_du,';','')"/>;<xsl:value-of select="translate(gar_num,';','')"/>;<xsl:value-of select="translate(prov_date,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;
</xsl:template>


</xsl:stylesheet>
