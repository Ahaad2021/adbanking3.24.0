<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="echeancier"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="echeancier">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/>
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Echeancier'"/></xsl:call-template>
			<fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt">
				<fo:table-column column-width="2cm"/>
				<fo:table-column column-width="3cm"/>
				<fo:table-column column-width="4cm"/>
				<fo:table-column column-width="4cm"/>
				<fo:table-column column-width="4cm"/>
				<fo:table-column column-width="4cm"/>
				<fo:table-header>
					<fo:table-row font-weight="bold">
						<fo:table-cell padding-after="5pt">
							<fo:block>N°</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center">Date</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center">Montant en capital</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center">Montant en intérêts</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center">Total de l'échéance</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center">Solde restant dû</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-header>
				<fo:table-body>
					<xsl:apply-templates select="echeance"/>
					<xsl:apply-templates select="total"/>
				</fo:table-body>
			</fo:table>
		</fo:flow>	
	</fo:page-sequence>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>	
</xsl:template>

<xsl:template match="criteres_recherche">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations générales sur le crédit'"/></xsl:call-template>
	<fo:table>
		<fo:table-column column-width="10cm"/>
		<fo:table-column column-width="10cm"/>
		<fo:table-body>
			<xsl:apply-templates select="critere"/>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="critere">
	<fo:table-row>
		<fo:table-cell>
			<fo:block><xsl:value-of select="champs"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="valeur"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

<xsl:template match="echeance">
	<xsl:apply-templates select="ech_theo"/>
</xsl:template>

<xsl:template match="echeance">
	<xsl:apply-templates select="ech_theo"/>
</xsl:template>

<xsl:template match="ech_theo">
	<fo:table-row>
		<fo:table-cell>
			<fo:block><xsl:value-of select="id_ech"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center"><xsl:value-of select="date_ech"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="cap_du"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="int_du"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="total_du"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_total"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

<xsl:template match="total">
	<fo:table-row font-weight="bold">
		<fo:table-cell padding-before="5pt">
			<fo:block>Total</fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="5pt">
			<fo:block></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="5pt">
			<fo:block text-align="right"><xsl:value-of select="total_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="5pt">
			<fo:block text-align="right"><xsl:value-of select="total_int"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="5pt">
			<fo:block text-align="right"><xsl:value-of select="total_credit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="5pt">
			<fo:block></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>


</xsl:stylesheet>
