<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
	
<xsl:template match="histo_credit">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>;
<xsl:apply-templates select="ligne_produit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
	<xsl:apply-templates select="total"/>
</xsl:template>
<xsl:template match="ligne_produit">
<xsl:apply-templates select="lib_prod"/>;
Numéro client;Nom client;Montant pret anterieur;Date réglé;A temps(%);Etat;Jours sans prêt;Produit de credit;
<xsl:apply-templates select="ligne_histo"/>;
<xsl:apply-templates select="ligne_histo_credit_gs"/>;
<xsl:apply-templates select="prod_total"/>;
</xsl:template>
<xsl:template match="ligne_histo">
	<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(mnt_credit,';','')"/>;<xsl:value-of select="translate(date_reglt,';','')"/>;<xsl:value-of select="translate(taux_retard,';','')"/>;<xsl:value-of select="translate(etat_credit,';','')"/>;<xsl:value-of select="translate(jours_sans_pret,';','')"/>;<xsl:value-of select="translate(prd_credit,';','')"/>;
</xsl:template>

<xsl:template match="prod_total">
Total en devise;<xsl:value-of select="translate(tot_mnt_octr,';','')"/>;
</xsl:template>

<xsl:template match="ligne_histo_credit_gs">
<xsl:choose>
	<xsl:when test="membre_gs='true'">
	<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(mnt_credit,';','')"/>;<xsl:value-of select="translate(date_reglt,';','')"/>;<xsl:value-of select="translate(taux_retard,';','')"/>;<xsl:value-of select="translate(etat_credit,';','')"/>;<xsl:value-of select="translate(jours_sans_pret,';','')"/>;<xsl:value-of select="translate(prd_credit,';','')"/>;
	</xsl:when>
	<xsl:otherwise>
	<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(mnt_credit,';','')"/>;<xsl:value-of select="translate(date_reglt,';','')"/>;<xsl:value-of select="translate(taux_retard,';','')"/>;<xsl:value-of select="translate(etat_credit,';','')"/>;<xsl:value-of select="translate(jours_sans_pret,';','')"/>;<xsl:value-of select="translate(prd_credit,';','')"/>;
	</xsl:otherwise>
</xsl:choose>
</xsl:template>
<xsl:template match="total">
Montant total en (<xsl:value-of select="translate(devise,';','')"/>);<xsl:value-of select="translate(mnt_credit,';','')"/>;
</xsl:template>

</xsl:stylesheet>
