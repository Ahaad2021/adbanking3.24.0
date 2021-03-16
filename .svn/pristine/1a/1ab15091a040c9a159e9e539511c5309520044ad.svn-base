<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="credits_perte">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="total"/>
		<xsl:apply-templates select="details"/>
</xsl:template>

<xsl:template match="total">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations globales'"/></xsl:call-template>;
Montant total passé en perte ; <xsl:value-of select="total_perte"/>;
Montant total recouvert  ; <xsl:value-of select="total_perte_rec"/>;
Montant total de capital récupéré  ; <xsl:value-of select="total_cap_recupere"/>;
Montant total des intérêts récupérés  ; <xsl:value-of select="total_int_recupere"/>;
Montant total des pénalités récupérées  ; <xsl:value-of select="total_pen_recupere"/>;
Montant total provisionné ; <xsl:value-of select="total_prov_mnt"/>;
</xsl:template>

<xsl:template match="details">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations détaillées'"/></xsl:call-template>;
Rang;Numéro dossier;Numéro client;Nom client;Produit;Objet demande;Montant en perte;Capital récupéré;Intérêts récupérés;Pénalités recupérés;Date passé en perte;Provision;Date provision;
<xsl:apply-templates select="credit"/>
<xsl:apply-templates select="credit_gs"/>
</xsl:template>
<xsl:template match="credit">
	<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(produit,';','')"/>;<xsl:value-of select="translate(obj_dem,';','')"/>;<xsl:value-of select="translate(mnt_perte,';','')"/>;<xsl:value-of select="translate(mnt_rec,';','')"/>;<xsl:value-of select="translate(int_rec,';','')"/>;<xsl:value-of select="translate(pen_rec,';','')"/>;<xsl:value-of select="translate(date,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;<xsl:value-of select="translate(prov_date,';','')"/>;
</xsl:template>

<xsl:template match="credit_gs">
<xsl:choose>
<xsl:when test="membre_gs='true'">
<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(produit,';','')"/>;<xsl:value-of select="translate(obj_dem,';','')"/>;<xsl:value-of select="translate(mnt_perte,';','')"/>;<xsl:value-of select="translate(mnt_rec,';','')"/>;<xsl:value-of select="translate(int_rec,';','')"/>;<xsl:value-of select="translate(pen_rec,';','')"/>;<xsl:value-of select="translate(date,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;<xsl:value-of select="translate(prov_date,';','')"/>;
</xsl:when>
<xsl:otherwise>
<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(produit,';','')"/>;<xsl:value-of select="translate(obj_dem,';','')"/>;<xsl:value-of select="translate(mnt_perte,';','')"/>;<xsl:value-of select="translate(mnt_rec,';','')"/>;<xsl:value-of select="translate(int_rec,';','')"/>;<xsl:value-of select="translate(pen_rec,';','')"/>;<xsl:value-of select="translate(date,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;<xsl:value-of select="translate(prov_date,';','')"/>;
</xsl:otherwise>
</xsl:choose>
</xsl:template>
	<!--
        <xsl:template match="credit">
            <xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(produit,';','')"/>;<xsl:value-of select="translate(obj_dem,';','')"/>;<xsl:value-of select="translate(mnt_perte,';','')"/>;<xsl:value-of select="translate(date,';','')"/>;<xsl:value-of select="translate(mnt_rec,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;<xsl:value-of select="translate(prov_date,';','')"/>;
        </xsl:template>
        -->
</xsl:stylesheet>
