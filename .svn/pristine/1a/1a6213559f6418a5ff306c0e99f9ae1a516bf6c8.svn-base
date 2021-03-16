<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait_no_region"></xsl:call-template>
               	<xsl:apply-templates select="autorisation"/>
        </fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="signature.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="autorisation">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header" mode="no_region"/>
         <fo:block space-before.optimum="0.7cm"/>
			<xsl:apply-templates select="body"/>
		 <fo:block space-before.optimum="3cm"/>
         <fo:block text-align="center">
            <xsl:value-of select="$ciseaux" disable-output-escaping="yes"/>--------------------------------------------------------------------------------------------------------------------------------------------------------------
         </fo:block>
         <fo:block space-before.optimum="0.5cm"/>
            <xsl:apply-templates select="header" mode="no_region"/>
         <fo:block space-before.optimum="0.7cm"/>
            <xsl:apply-templates select="body"/>
         <fo:block space-before.optimum="2cm"/>
   	</fo:flow>
	</fo:page-sequence>
</xsl:template>


<xsl:template match="body">

	<fo:list-block>
	<!--Numéro de séquence d'enregistrement de l'autorisation-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Numéro de séquence d'enregistrement de l'autorisation: <xsl:value-of select="numSeqAuto"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--ode de l'antenne-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Code de l'antenne: <xsl:value-of select="codeAntenne"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--Code de l'agence-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Code de l'agence: <xsl:value-of select="codeAgence"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--code de la carte du client-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Code de la carte du client: <xsl:value-of select="numCarte"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--numéro complet du compte du client-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Numéro complet du compte du client: <xsl:value-of select="compteRecharge"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--Identifiant tutulaire du Compte-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Identifiant tutulaire du Compte: <xsl:value-of select="codeTitulaire"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--montant demandé-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Montant demandé: <xsl:value-of select="montant"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--devise du montant demandé-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Devise de la transaction: <xsl:value-of select="devise"/></fo:block></fo:list-item-body>
		</fo:list-item>
	<!--date de la demande-->
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Date de la demande: <xsl:value-of select="dateDmde"/></fo:block></fo:list-item-body>
		</fo:list-item>
		
	</fo:list-block>

	<fo:block space-before.optimum="0.7cm"/>

        <xsl:call-template name="signature"/>
</xsl:template>


</xsl:stylesheet>
