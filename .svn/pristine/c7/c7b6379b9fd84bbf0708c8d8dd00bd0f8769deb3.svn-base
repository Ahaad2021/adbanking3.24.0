<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:apply-templates select="listedepots"/>
	</fo:root>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="listedepots">
	N°Client; Nom Client; N°Compte; Date ouverture; Produit épargne; Etat compte; Dépot initial; Devise;
			<xsl:apply-templates select="details/devise"/>
	  	<xsl:apply-templates select="total"/>		
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="devise">
	<xsl:value-of select="translate(num_client,';','')"/>; <xsl:value-of select="translate(nom_client,';','')"/>; <xsl:value-of select="translate(num_cpte,';','')"/>;	<xsl:value-of select="translate(date_ouvert,';','')"/>;	<xsl:value-of select="translate(libel_prod_ep,';','')"/>;	<xsl:value-of select="translate(etat_cpte,';','')"/>;	<xsl:value-of select="translate(montant,';','')"/>; <xsl:value-of select="translate(devise,';','')"/>; 
</xsl:template>

<xsl:template match="total">
	; ; ; ; ; Total; <xsl:value-of select="translate(total_solde,';','')"/>;
</xsl:template>

</xsl:stylesheet>


