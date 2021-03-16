<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
	
<xsl:template match="creditactif">
		<xsl:apply-templates select="header"/>
	        <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="gestionnaire"/>
                <xsl:apply-templates select="total"/>
                
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="gestionnaire">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="nom_gestionnaire"/></xsl:call-template>;
Numéro client;Nom client;Produit;Montant demandé;Montant octroyé;N° Dossier;Etat crédit;Capital restant dû;Intérêts restant dû;Adresse;Localités;Date d'octroi du crédit;Date de la dernière échéance;
<xsl:apply-templates select="client"/>
Nombre crédits; Montant octroyé; Devise; Capital remboursé; Interêts remboursé;Garanties remboursées;Pénalités remboursées;Total remboursé;Capital restant dû;
<xsl:apply-templates select="sous_total"/>
</xsl:template>

<xsl:template match="client">
<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(libel_prod,';','')"/>;<xsl:value-of select="translate(mnt_dem,';','')"/>;<xsl:value-of select="translate(cre_mnt_octr,';','')"/>;<xsl:value-of select="translate(num_dossier,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(capital_du,';','')"/>;<xsl:value-of select="translate(solde_int,';','')"/>;<xsl:value-of select="translate(adresse,';','')"/>;<xsl:value-of select="translate(localite,';','')"/>;<xsl:value-of select="translate(cre_date_approb,';','')"/>;<xsl:value-of select="translate(delai,';','')"/>;
</xsl:template>

<xsl:template match="sous_total">
    <xsl:value-of select="translate(prod_nombre,';','')"/>;<xsl:value-of select="translate(prod_montant,';','')"/>;<xsl:value-of select="translate(prod_devise,';','')"/>;<xsl:value-of select="translate(prod_capital,';','')"/>;<xsl:value-of select="translate(prod_interet,';','')"/>;<xsl:value-of select="translate(prod_garantie,';','')"/>;<xsl:value-of select="translate(prod_penalite,';','')"/>;<xsl:value-of select="translate(prod_total_remb,';','')"/>;<xsl:value-of select="translate(prod_capital_du,';','')"/>;
</xsl:template>

    <!--
<xsl:template match="total">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Total'"/></xsl:call-template>;
<xsl:for-each select="*">
   <xsl:value-of select="translate(.,';','')"/>
   <xsl:if test="position() != last()">
    <xsl:value-of select="';'"/>
   </xsl:if>
  </xsl:for-each>
<xsl:text disable-output-escaping="yes">
</xsl:text>
</xsl:template>-->

<xsl:template match="total">
    TOTAL;
    Nombre de crédits;Montant octroyé;;Capital remboursé;Interêts remboursés;Garanties remboursées;Pénalités remboursées;Total remboursé;Capital restant dû;
    <xsl:value-of select="translate(nombre,';','')"/>;<xsl:value-of select="translate(montant,';','')"/>;;<xsl:value-of select="translate(capital,';','')"/>;<xsl:value-of select="translate(interet,';','')"/>;<xsl:value-of select="translate(garantie,';','')"/>;<xsl:value-of select="translate(penalite,';','')"/>;<xsl:value-of select="translate(total_remb,';','')"/>;<xsl:value-of select="translate(capital_du,';','')"/>;
</xsl:template>

</xsl:stylesheet>
