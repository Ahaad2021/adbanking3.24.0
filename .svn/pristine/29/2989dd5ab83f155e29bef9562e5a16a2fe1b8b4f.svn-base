<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="ps_reprises"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>


<xsl:template match="ps_reprises">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/>
			<xsl:apply-templates select="liste_ps_reprise"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>


<xsl:template match="liste_ps_reprise">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations détaillées'"/></xsl:call-template>
        <fo:table border-collapse="collapse"  table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(3)"/>
         <!-- <fo:table-column column-width="proportional-column-width(4)"/> --> <!-- ligne nbre ps -->
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>


		<fo:table-header>
			<fo:table-row font-weight="bold">
				<fo:table-cell>
					<fo:block text-align="center" border="0.1pt solid gray">Numéro client</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="center" border="0.1pt solid gray">Ancien N° client</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="center" border="0.1pt solid gray">Nom client</fo:block>
				</fo:table-cell>
<!-- 				<fo:table-cell>
					<fo:block text-align="center" border="0.1pt solid gray">Nbre Parts sociales</fo:block>
				</fo:table-cell> -->    <!-- bloc nbre ps -->
				<fo:table-cell>
					<fo:block text-align="center" border="0.1pt solid gray">Montant repris</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="center" border="0.1pt solid gray">Date reprise</fo:block>
				</fo:table-cell>
				</fo:table-row>
		</fo:table-header>
		<fo:table-body>
			<xsl:apply-templates select="ps_reprise"/>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="ps_reprise">
	<fo:table-row>
		<fo:table-cell>
			<fo:block text-align="center" border="0.1pt solid gray"><xsl:value-of select="num_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" border="0.1pt solid gray"><xsl:value-of select="ancien_num_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block  border="0.1pt solid gray"><xsl:value-of select="nom_client"/></fo:block>
		</fo:table-cell>
	<!-- 	<fo:table-cell>
			<fo:block border="0.1pt solid gray"><xsl:value-of select="nbre_ps"/></fo:block>
		</fo:table-cell> -->                     <!-- bloc nbre ps -->
		<fo:table-cell>
			<fo:block text-align="right" border="0.1pt solid gray"><xsl:value-of select="mnt_ps_repris"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" border="0.1pt solid gray"><xsl:value-of select="date_reprise"/></fo:block>
		</fo:table-cell>

	</fo:table-row>
</xsl:template>

</xsl:stylesheet>
