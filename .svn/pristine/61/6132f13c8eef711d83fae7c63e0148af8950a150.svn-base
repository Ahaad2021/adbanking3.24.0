<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="registrecredit">
		<xsl:apply-templates select="header"/>
	        <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="ligneCredit"/>
		<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="ligneCredit">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod"/></xsl:call-template>;
	Numéro client;Nom client;Numéro dossier;Montant octroyé;Montant	déboursé;Date;Etat crédit;Durée;Capital remboursé;Intérêts remboursés;Garanties remboursées;Penalités remboursées;Total remboursé;Capital dû;Intérêt restant dû;Montant provision;
<xsl:apply-templates select="infosCreditSolidiaire"/>
<xsl:apply-templates select="xml_total"/>

</xsl:template>

<xsl:template match="infosCreditSolidiaire"><xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(no_dossier,';','')"/>;<xsl:value-of select="translate(cre_mnt_octr,';','')"/>;<xsl:value-of select="translate(cre_mnt_deb,';','')"/>;<xsl:value-of select="translate(cre_date_debloc,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(duree_mois,';','')"/>;<xsl:value-of select="translate(mnt_remb_cap,';','')"/>;<xsl:value-of select="translate(mnt_remb_int,';','')"/>;<xsl:value-of select="translate(mnt_remb_gar,';','')"/>;<xsl:value-of select="translate(mnt_remb_pen,';','')"/>;<xsl:value-of select="translate(mnt_remb_total,';','')"/>;<xsl:value-of select="translate(capital_du,';','')"/>;<xsl:value-of select="translate(int_du,';','')"/>;<xsl:value-of select="translate(mnt_prov,';','')"/>;
</xsl:template>

<xsl:template match="xml_total">
	Nombre de crédits;<xsl:value-of select="translate(prod_nombre,';','')"/>;;<xsl:value-of select="translate(prod_montant,';','')"/>;<xsl:value-of select="translate(prod_montant_deb,';','')"/>;;;;<xsl:value-of select="translate(prod_capital,';','')"/>;<xsl:value-of select="translate(prod_interet,';','')"/>;<xsl:value-of select="translate(prod_garantie,';','')"/>;<xsl:value-of select="translate(prod_penalite,';','')"/>;<xsl:value-of select="translate(prod_total_remb,';','')"/>;<xsl:value-of select="translate(prod_capital_du,';','')"/>;<xsl:value-of select="translate(prod_int_du,';','')"/>;<xsl:value-of select="translate(prod_prov_mnt,';','')"/>;
</xsl:template>

<xsl:template match="total">
	Total General;
	Nombre de crédits;Montant octroyé;Montant déboursé;Capital remboursé;Interêt remboursé;Garantie remboursée;Pénalité remboursée;Total remboursé;Capital restant dû;Intérêt restant dû;Montant provision;
	<xsl:value-of select="translate(nombre,';','')"/>;<xsl:value-of select="translate(montant,';','')"/>;<xsl:value-of select="translate(montant_deb,';','')"/>;<xsl:value-of select="translate(capital,';','')"/>;<xsl:value-of select="translate(interet,';','')"/>;<xsl:value-of select="translate(garantie,';','')"/>;<xsl:value-of select="translate(penalite,';','')"/>;<xsl:value-of select="translate(total_remb,';','')"/>;<xsl:value-of select="translate(capital_du,';','')"/>;<xsl:value-of select="translate(int_du,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;
</xsl:template>

</xsl:stylesheet>
