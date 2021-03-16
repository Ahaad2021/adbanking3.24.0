<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:output method="text"/>

<xsl:template match="risques_credits">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>
	<xsl:apply-templates select="etat_credit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="etat_credit">
	<fo:block>Etat des crédits : <xsl:value-of select="lib_etat_credit"/></fo:block>;
	<xsl:apply-templates select="produit"/>
</xsl:template>

<xsl:template match="produit">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod"/></xsl:call-template>;
	<xsl:apply-templates select="zone"/>
</xsl:template>

<xsl:template match="zone">
	<xsl:if test="libelle_zone">
		Zone <xsl:call-template name="titre1"><xsl:with-param name="titre" select="libelle_zone"/></xsl:call-template>;
		;Numéro dossier;Durée;Numéro client;Nom client;Statut juridique;Sexe;Date deboursement;Montant déboursé;Date dernier remb;Date dernier ech remb;Solde Capital;Capital en retard;Retard échéances;Retard jours;Epargne nantie;provision;
	</xsl:if>
	<xsl:apply-templates select="risque_credit"/>
</xsl:template>

	<xsl:template match="header_contextuel">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
Nombre de crédits en cours; <xsl:value-of select="infos_synthetiques/nbre_credits"/>;
Nombre de credits en retard; <xsl:value-of select="infos_synthetiques/nbre_credits_retard"/> (<xsl:value-of select="infos_synthetiques/prc_credits_retard"/>);
Portefeuille total; <xsl:value-of select="infos_synthetiques/portefeuille"/>;
Portefeuille en retard; <xsl:value-of select="infos_synthetiques/portefeuille_retard"/> (<xsl:value-of select="infos_synthetiques/prc_portefeuille_retard"/>);
Total solde intérêts des crédits en retard; <xsl:value-of select="infos_synthetiques/total_solde_int"/>;
Total solde pénalités des crédits en retard; <xsl:value-of select="infos_synthetiques/total_solde_pen"/>;
Total retard capital; <xsl:value-of select="infos_synthetiques/total_retard_cap"/>;
Total retard intérêts; <xsl:value-of select="infos_synthetiques/total_retard_int"/>;
Total épargne nantie des crédits en retard; <xsl:value-of select="infos_synthetiques/total_epargne_nantie"/>;
Total provision des crédits ; <xsl:value-of select="infos_synthetiques/total_prov_mnt"/>;
</xsl:template>

<xsl:template match="risque_credit">
;<xsl:value-of select="num_doss"/>;<xsl:value-of select="duree"/>;<xsl:value-of select="num_client"/>;<xsl:value-of select="nom_client"/>;<xsl:value-of select="statut_jur"/>;<xsl:value-of select="sexe"/>;<xsl:value-of select="date_debloc"/>;<xsl:value-of select="mnt_debloc"/>;<xsl:value-of select="date_dernier_remb"/>;<xsl:value-of select="date_dernier_ech_remb"/>;<xsl:value-of select="solde_cap"/>;<xsl:value-of select="retard_cap"/>;<xsl:value-of select="nbre_ech_retard"/>;<xsl:value-of select="nbre_jours_retard"/>;<xsl:value-of select="epargne_nantie"/>;<xsl:value-of select="prov_mnt"/>;
</xsl:template>


</xsl:stylesheet>