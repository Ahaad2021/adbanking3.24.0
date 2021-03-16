<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="schemas_comptables"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="schemas_comptables">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="schema_compta"/>
		</fo:flow>	
	</fo:page-sequence>
</xsl:template>

<xsl:template match="schema_compta">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Liste des schémas comptables'"/></xsl:call-template>
		<fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
			<fo:table-column column-width="2cm"/>
			<fo:table-column column-width="12cm"/>
			<fo:table-column column-width="7cm"/>
			<fo:table-column column-width="7cm"/>
					
			<fo:table-header>
				<fo:table-row font-weight="bold">
					<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
						<fo:block>Opération </fo:block>
					</fo:table-cell>
					<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
						<fo:block>Libellé opération</fo:block>
					</fo:table-cell>
					<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
						<fo:block>Compte au débit</fo:block>
					</fo:table-cell>
					<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
						<fo:block>Compte au crédit</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-header>

			<fo:table-body>
				<xsl:apply-templates select="detail_schema"/>
			</fo:table-body>
		</fo:table>
</xsl:template>

<xsl:template match="detail_schema">
	<fo:table-row>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="type_ope"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="libel_ope"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="cpte_debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="cpte_credit"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

</xsl:stylesheet>
