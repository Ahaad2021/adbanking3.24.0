<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait_no_region"></xsl:call-template>
		<xsl:apply-templates select="recucpt"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="signature.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="recucpt">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header" mode="no_region"/>
			<fo:block space-before.optimum="2cm"/>
			<xsl:apply-templates select="body"/>
			<fo:block space-before.optimum="5.2cm"/>
			<fo:block text-align="center">
                          <xsl:value-of select="$ciseaux" disable-output-escaping="yes"/>--------------------------------------------------------------------------------------------------------------------------------------------------------------
                        </fo:block>
			<fo:block space-before.optimum="0.5cm"/>
			<xsl:apply-templates select="header" mode="no_region"/>
			<fo:block space-before.optimum="2cm"/>
			<xsl:apply-templates select="body"/>
			<fo:block space-before.optimum="2cm"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="body">
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Numéro de client: <xsl:value-of select="num_client"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Nom: <xsl:value-of select="nom_client"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Numéro de compte: <xsl:value-of select="num_cpte"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Produit d'épargne : <xsl:value-of select="libelprod"/></fo:block></fo:list-item-body>
		</fo:list-item>
		
                 <fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Solde : <xsl:value-of select="solde"/></fo:block></fo:list-item-body>
		</fo:list-item>
                <fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block space-before.optimum="0.3cm">Numéro de transaction : <xsl:value-of select="num_trans"/></fo:block></fo:list-item-body>
		</fo:list-item>


	</fo:list-block>

	<fo:block space-before.optimum="1cm"/>

        <xsl:call-template name="signature"/>
</xsl:template>

</xsl:stylesheet>
