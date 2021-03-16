<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:output method="text"/>

<xsl:template match="credits_retard">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel/infos_synthetiques"/>
<xsl:apply-templates select="produit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="produit">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod"/></xsl:call-template>;
;Numéro dossier;Numéro client;Nom client;Date deboursement;Montant déboursé;Solde Capital;Solde Intérets;Solde garanties;Solde pénalités;Capital impayé;Intérets impayés;Garanties impayées;Retard échéances;Retard jours;Epargne nantie;provision;Etat crédit;
<xsl:apply-templates select="credit_retard"/>
<xsl:apply-templates select="xml_total"/>
</xsl:template>

<xsl:template match="header_contextuel/infos_synthetiques">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
Nombre de crédits en cours; <xsl:value-of select="nbre_credits"/>;
Nombre de credits en retard; <xsl:value-of select="nbre_credits_retard"/> (<xsl:value-of select="prc_credits_retard"/>);
Portefeuille total; <xsl:value-of select="portefeuille"/>;
Portefeuille en retard; <xsl:value-of select="total_solde_cap"/> (<xsl:value-of select="prc_portefeuille_retard"/>);
Total solde intérêts des crédits en retard; <xsl:value-of select="total_solde_int"/>;
Total solde pénalités des crédits en retard; <xsl:value-of select="total_solde_pen"/>;
Total retard capital; <xsl:value-of select="total_retard_cap"/>;
Total retard intérêts; <xsl:value-of select="total_retard_int"/>;
Total épargne nantie des crédits en retard; <xsl:value-of select="total_epargne_nantie"/>;
Total provision des crédits ; <xsl:value-of select="total_prov_mnt"/>;
</xsl:template>

<xsl:template match="credit_retard">
	;<xsl:value-of select="num_doss"/>;<xsl:value-of select="num_client"/>;<xsl:value-of select="nom_client"/>;<xsl:value-of select="date_debloc"/>;<xsl:value-of select="mnt_debloc"/>;<xsl:value-of select="solde_cap"/>;<xsl:value-of select="solde_int"/>;<xsl:value-of select="solde_gar"/>;<xsl:value-of select="solde_pen"/>;<xsl:value-of select="retard_cap"/>;<xsl:value-of select="retard_int"/>;<xsl:value-of select="retard_gar"/>;<xsl:value-of select="nbre_ech_retard"/>;<xsl:value-of select="nbre_jours_retard"/>;<xsl:value-of select="epargne_nantie"/>;<xsl:value-of select="prov_mnt"/>;<xsl:value-of select="etat"/>;
</xsl:template>


<xsl:template match="xml_total">
	;Total ;Total en devise;;<xsl:value-of select="tot_mnt_debloc"/>;<xsl:value-of select="tot_solde_cap"/>;<xsl:value-of select="tot_solde_int"/>;<xsl:value-of select="tot_solde_gar"/>;<xsl:value-of select="tot_solde_pen"/>;<xsl:value-of select="tot_retard_cap"/>;<xsl:value-of select="tot_retard_int"/>;<xsl:value-of select="tot_retard_gar"/>;;;<xsl:value-of select="tot_epargne_nantie"/>;<xsl:value-of select="tot_prov_mnt"/>;
</xsl:template>


</xsl:stylesheet>
