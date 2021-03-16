<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="frais_attente"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="frais_attente">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/>
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>
			<fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>

				<fo:table-header>
					<fo:table-row font-weight="bold">
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Type frais</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Date mise en attente</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Montant</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Compte</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">N° client</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Nom client</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-header>

				<fo:table-body>
					<xsl:apply-templates select="attente"/>
				</fo:table-body>
			</fo:table>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>


<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
	<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>


<xsl:template match="infos_synthetiques">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
		<fo:list-block>
			<fo:list-item>
				<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
				<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre de frais en attente : <xsl:value-of select="total_attente"/></fo:block></fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
				<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Montant des frais en attente : <xsl:value-of select="total_frais"/></fo:block></fo:list-item-body>
			</fo:list-item>
		</fo:list-block>
</xsl:template>


<xsl:template match="attente">
	<fo:table-row>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="type_frais"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="date_frais"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="mnt_frais"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="num_compte"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="num_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="nom_client"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

</xsl:stylesheet>
