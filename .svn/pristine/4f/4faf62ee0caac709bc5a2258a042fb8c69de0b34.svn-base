<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="grandlivre"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="grandlivre">
	<xsl:apply-templates select="compte"/>
</xsl:template>

<xsl:template match="compte">
	<xsl:apply-templates select="ligne"/>
	<xsl:value-of select="$table-end" disable-output-escaping="yes"/>
	<xsl:value-of select="$flow-end" disable-output-escaping="yes"/>
	<xsl:value-of select="$page-sequence-end" disable-output-escaping="yes"/>
</xsl:template>

<xsl:template match="ligne">
	<xsl:if test="@numero mod 417 = '0'">
	  <xsl:if test="(@numero != '0')">
			<xsl:value-of select="$table-end" disable-output-escaping="yes"/>
			<xsl:value-of select="$flow-end" disable-output-escaping="yes"/>
			<xsl:value-of select="$page-sequence-end" disable-output-escaping="yes"/>
	</xsl:if>
		<xsl:value-of select="$page-sequence-start" disable-output-escaping="yes"/>
		<xsl:apply-templates select="/grandlivre/header"/>
		<xsl:call-template name="footer"/>
		<xsl:value-of select="$flow-start" disable-output-escaping="yes"/>
	<!-- 	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="../libel_cpte"/>  </xsl:call-template>
	-->
	<fo:block  font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
	<fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
			<fo:table-row>
				<fo:table-cell>
					<fo:block ><xsl:value-of  select="../libel_cpte"/></fo:block>
				</fo:table-cell>
				<fo:table-cell font-size="10pt" font-style="italic">
					<fo:block text-align="right"> Solde Fin Période : <xsl:value-of  select="../solde_fin_periode"/></fo:block>
				</fo:table-cell>
			</fo:table-row>
		</fo:table-body>

	</fo:table>
	</fo:block>
	<fo:block></fo:block>
<fo:block></fo:block>
		<xsl:if test="@condense = '1'">
			<xsl:value-of select="$table-start" disable-output-escaping="yes"/>
		</xsl:if>
		<xsl:if test="@condense = '2'">
			<xsl:value-of select="$table-start_condense" disable-output-escaping="yes"/>
		</xsl:if>

	</xsl:if>

	<xsl:if test="@niveau = '0'">
	<fo:table-row>
		<fo:table-cell>
			<fo:block text-align="left"><xsl:value-of select="piece"/></fo:block>
		</fo:table-cell>
		<xsl:if test="@condense = '1'">
		<fo:table-cell>
			<fo:block><xsl:value-of select="histo"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="client"/></fo:block>
		</fo:table-cell>
		</xsl:if>
		<fo:table-cell>
			<fo:block><xsl:value-of select="date"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="libel"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="credit"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
	</xsl:if>
	<xsl:if test="@niveau = '1'">
	<fo:table-row font-weight="bold" font-style="italic">
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="left"><xsl:value-of select="piece"/></fo:block>
		</fo:table-cell>
		<xsl:if test="@condense = '1'">
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block><xsl:value-of select="histo"/></fo:block>
		</fo:table-cell>
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block><xsl:value-of select="client"/></fo:block>
		</fo:table-cell>
		</xsl:if>
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block><xsl:value-of select="date"/></fo:block>
		</fo:table-cell>
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block><xsl:value-of select="libel"/></fo:block>
		</fo:table-cell>
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right"><xsl:value-of select="debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right"><xsl:value-of select="credit"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
	</xsl:if>
	<xsl:if test="@niveau = '2'">
	<fo:table-row font-weight="bold" font-style="italic">
		<fo:table-cell>
			<fo:block text-align="left"><xsl:value-of select="piece"/></fo:block>
		</fo:table-cell>
		<xsl:if test="@condense = '1'">
		<fo:table-cell>
			<fo:block><xsl:value-of select="histo"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="client"/></fo:block>
		</fo:table-cell>
		</xsl:if>
		<fo:table-cell>
			<fo:block><xsl:value-of select="date"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="libel"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="credit"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
	</xsl:if>
</xsl:template>

<xsl:variable name="page-sequence-start">
	&lt;fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
</xsl:variable>

<xsl:variable name="page-sequence-end">
	&lt;/fo:page-sequence>
</xsl:variable>

<xsl:variable name="flow-start">
	&lt;fo:flow flow-name="xsl-region-body">
</xsl:variable>

<xsl:variable name="flow-end">
	&lt;/fo:flow>
</xsl:variable>

<xsl:template match="entete_table">
	<xsl:value-of select="$table-start" disable-output-escaping="yes"/>
</xsl:template>

<xsl:variable name="table-start">
	&lt;fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt">
		&lt;fo:table-column column-width="3cm" border-left-width="0.3pt"/>
		&lt;fo:table-column column-width="2cm" border-left-width="0.1pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="4cm" border-left-width="0.1pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="3cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="6cm" border-left-width="0.1pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="4cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="4cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>

		&lt;fo:table-header>
			&lt;fo:table-row font-weight="bold">
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">N° pièce&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">N° hist&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Client&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Date opération&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Libellé&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Débit&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Crédit&lt;/fo:block>
				&lt;/fo:table-cell>
			&lt;/fo:table-row>
		&lt;/fo:table-header>
		&lt;fo:table-body>
</xsl:variable>


<xsl:variable name="table-start_condense">
	&lt;fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt">
		&lt;fo:table-column column-width="3cm" border-left-width="0.3pt"/>
		&lt;fo:table-column column-width="3cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="10cm" border-left-width="0.1pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-column column-width="5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
		&lt;fo:table-header>
			&lt;fo:table-row font-weight="bold">
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">N° Ordre&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Date opération&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Libellé&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Débit&lt;/fo:block>
				&lt;/fo:table-cell>
				&lt;fo:table-cell>
					&lt;fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Crédit&lt;/fo:block>
				&lt;/fo:table-cell>
			&lt;/fo:table-row>
		&lt;/fo:table-header>
		&lt;fo:table-body>
</xsl:variable>

<xsl:variable name="table-end">
		&lt;/fo:table-body>
	&lt;/fo:table>
</xsl:variable>

</xsl:stylesheet>
