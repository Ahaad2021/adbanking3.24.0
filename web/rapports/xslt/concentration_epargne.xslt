<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="concentration_epargne"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="concentration_epargne">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
			<fo:table-column column-width="5cm"/>
			<fo:table-column column-width="2cm"/>	
			<fo:table-column column-width="2cm"/>	
			<fo:table-column column-width="3cm"/>	
			<fo:table-column column-width="2cm"/>	
			<fo:table-column column-width="3cm"/>	
			<fo:table-header border-separation.block-progression-direction="5pt">
				<fo:table-row font-weight="bold">
					<fo:table-cell>
						<fo:block></fo:block>
					</fo:table-cell>
					<fo:table-cell number-columns-spanned="2">
						<fo:block text-align="center">Comptes</fo:block>
					</fo:table-cell>
					<fo:table-cell number-columns-spanned="2">
						<fo:block text-align="center">Solde</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="right">Solde</fo:block>
					</fo:table-cell>
				</fo:table-row>
				<fo:table-row font-weight="bold">
					<fo:table-cell>
						<fo:block>Type de compte</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="center">Nombre</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="right">%</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="center">Solde</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="right">%</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="right">moyen</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-header>
			<fo:table-body>
				<xsl:apply-templates select="produit"/>
			</fo:table-body>
			</fo:table>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="produit">
	<fo:table-row>
		<fo:table-cell number-columns-spanned="6" padding-before="15pt">
			<fo:block font-weight="bold"><xsl:value-of select="@libel"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
	<xsl:apply-templates select="tranche"/>
</xsl:template>

<xsl:template match="tranche">
	<fo:table-row>
		<xsl:choose>
			<xsl:when test="@libel='Total'">
				<fo:table-cell padding-start="10pt" padding-before="8pt">
					<fo:block><xsl:value-of select="@libel"/></fo:block>
				</fo:table-cell>
				<fo:table-cell padding-before="8pt">
					<fo:block text-align="right"><xsl:value-of select="comptes/@nombre"/></fo:block>
				</fo:table-cell>
				<fo:table-cell padding-before="8pt">
					<fo:block text-align="right"><xsl:value-of select="comptes/@prc"/></fo:block>
				</fo:table-cell>
				<fo:table-cell padding-before="8pt">
					<fo:block text-align="right"><xsl:value-of select="solde/@montant"/></fo:block>
				</fo:table-cell>
				<fo:table-cell padding-before="8pt">
					<fo:block text-align="right"><xsl:value-of select="solde/@prc"/></fo:block>
					</fo:table-cell>
				<fo:table-cell padding-before="8pt">
					<fo:block text-align="right"><xsl:value-of select="solde_moyen"/></fo:block>
				</fo:table-cell>
			</xsl:when>
			<xsl:otherwise>
				<fo:table-cell padding-start="10pt">
					<fo:block><xsl:value-of select="@libel"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="comptes/@nombre"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="comptes/@prc"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="solde/@montant"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="solde/@prc"/></fo:block>
					</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="solde_moyen"/></fo:block>
				</fo:table-cell>
			</xsl:otherwise>
		</xsl:choose>
	</fo:table-row>
</xsl:template>

</xsl:stylesheet>
