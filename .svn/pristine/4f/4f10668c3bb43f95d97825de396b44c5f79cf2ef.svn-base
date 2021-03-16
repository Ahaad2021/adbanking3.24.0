<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="simulation_intermediaire"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="simulation_intermediaire">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
				<fo:table-column column-width="3.5cm"/>
				<fo:table-column column-width="7.5cm"/>	
				<fo:table-column column-width="3cm"/>
		                <fo:table-column column-width="3cm"/>
				<fo:table-column column-width="3cm"/>
				<fo:table-header>
					<fo:table-row font-weight="bold">
						<fo:table-cell>
							<fo:block>N°</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block>Libellé</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block  text-align="right">Solde DN</fo:block>
						</fo:table-cell>
		                                <fo:table-cell>
							<fo:block  text-align="right">Solde DE</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block  text-align="right">Total</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-header>	
				<fo:table-body>
					<xsl:apply-templates select="compte"/>
				</fo:table-body>
			</fo:table>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="compte">
	<fo:table-row>
		<fo:table-cell>
			<fo:block><xsl:value-of select="numero"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="libel"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block  text-align="right"><xsl:value-of select="mn"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block  text-align="right"><xsl:value-of select="me"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block  text-align="right"><xsl:value-of select="tot"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template> 

</xsl:stylesheet>
