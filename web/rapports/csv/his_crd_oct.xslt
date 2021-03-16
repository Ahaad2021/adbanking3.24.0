<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="histo_credit_oct"> 
 	<xsl:apply-templates select="header"/>   
 	<xsl:apply-templates select="header_contextuel"/> 
 	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template> 
 	Numero client;Nom client;Numéro dossier;Produit de credit;Montant demandé;Montant Octroyé;Devise;Date octroi;Durée;Type durée;Gestionnaire; 
 	<xsl:apply-templates select="ligneCredit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="ligneCredit"> 
 	<xsl:apply-templates select="infosCreditSolidiaire"/> 
 	<xsl:apply-templates select="detailCredit"/> 
</xsl:template> 
	 
<xsl:template match="infosCreditSolidiaire"> 
  <xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(no_dossier,';','')"/>;<xsl:value-of select="translate(libel_prod,';','')"/>;<xsl:value-of select="translate(mnt_dem,';','')"/>;<xsl:value-of select="translate(mnt_octr,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(date_oct,';','')"/>;<xsl:value-of select="translate(duree,';','')"/>;<xsl:value-of select="translate(type_duree,';','')"/>;<xsl:value-of select="translate(agent_gest,';','')"/>; 
</xsl:template> 
	 
<xsl:template match="detailCredit"> 
	  <xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(no_dossier,';','')"/>;<xsl:value-of select="translate(libel_prod,';','')"/>;<xsl:value-of select="translate(mnt_dem,';','')"/>;<xsl:value-of select="translate(mnt_octr,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(date_oct,';','')"/>;<xsl:value-of select="translate(duree,';','')"/>;<xsl:value-of select="translate(type_duree,';','')"/>;<xsl:value-of select="translate(agent_gest,';','')"/>;
</xsl:template>

</xsl:stylesheet>
