<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:include href="page_layout.xslt" />
	<xsl:include href="header.xslt" />
	<xsl:include href="criteres_recherche.xslt" />
	<xsl:include href="footer.xslt" />
	<xsl:include href="lib.xslt" />

	<xsl:template match="/">
		<fo:root>
			<xsl:call-template name="page_layout_A4_portrait" />
			<xsl:apply-templates select="chequiers_commande_envoye_impression" />
		</fo:root>
	</xsl:template>

	<xsl:template match="chequiers_commande_envoye_impression">
		<fo:page-sequence master-reference="main" font-size="6pt" font-family="Helvetica">
			<xsl:apply-templates select="header" />
			<xsl:call-template name="footer" />
			<fo:flow flow-name="xsl-region-body">
				<xsl:apply-templates select="header_contextuel" />
				<xsl:apply-templates select="infos_synthetique"/>
				<xsl:apply-templates select="cmd_chequiers_data" />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	

	<!-- Infos synthetique -->
	<xsl:template match="infos_synthetique">
		<xsl:call-template name="titre_niv1">
			<xsl:with-param name="titre" select="'Summary'"/>
		</xsl:call-template>

		<fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="35%" table-layout="fixed">
			<fo:table-column column-width="proportional-column-width(2)"/>
			<fo:table-column column-width="proportional-column-width(1)"/>

			<fo:table-header>
				<fo:table-row font-weight="bold">
					<fo:table-cell>
						<fo:block text-align="left">Checkbook order status</fo:block>
					</fo:table-cell>
					<fo:table-cell>
						<fo:block text-align="left">No. of checkbooks</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-header>

			<fo:table-body>
				<xsl:for-each select="ligne_synthese">
					<fo:table-row>
						<fo:table-cell>
							<fo:block text-align="left"><xsl:value-of select="etat_cmd_cheq"/></fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="left"><xsl:value-of select="nb_chequiers"/></fo:block>
						</fo:table-cell>
					</fo:table-row>
				</xsl:for-each>
			</fo:table-body>
		</fo:table>

	</xsl:template>

	<!-- Body -->
	<xsl:template match="cmd_chequiers_data">

		<xsl:call-template name="titre_niv1">
			<xsl:with-param name="titre">Details</xsl:with-param>
		</xsl:call-template>

		<fo:table border-collapse="collapse" width="100%" table-layout="fixed">
			<fo:table-column column-width="proportional-column-width(1)" />
			<fo:table-column column-width="proportional-column-width(1)" />
			<fo:table-column column-width="proportional-column-width(2)" />
			<fo:table-column column-width="proportional-column-width(1)" />
			<fo:table-column column-width="proportional-column-width(1)" />

			<fo:table-header>
				<!-- Empty row -->
				<fo:table-row column-number="8">
					<fo:table-cell display-align="center">
						<fo:block text-align="left"> <fo:leader /> </fo:block>
					</fo:table-cell>
				</fo:table-row>

				<fo:table-row font-weight="bold">
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">Client N°</fo:block>
					</fo:table-cell>
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">Account N°</fo:block>
					</fo:table-cell>
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">Client name</fo:block>
					</fo:table-cell>
					<xsl:if test="type_rapport=1">
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Fee Amount</fo:block>
						</fo:table-cell>
					</xsl:if>
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">Order date</fo:block>
					</fo:table-cell>
				<xsl:if test="type_rapport=2">
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Print request date</fo:block>
						</fo:table-cell>
					</xsl:if>
				</fo:table-row>
			</fo:table-header>

			<fo:table-body>
				<xsl:for-each select="ligne_cmd_chequier">
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"><xsl:value-of select="num_client" /></fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"><xsl:value-of select="num_cpte" /></fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"><xsl:value-of select="nom_client" /></fo:block>
						</fo:table-cell>
						<xsl:if test="../type_rapport=1">
							<fo:table-cell display-align="center" border="0.1pt solid gray">
								<fo:block text-align="left"><xsl:value-of select="frais" /></fo:block>
							</fo:table-cell>
						</xsl:if>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"><xsl:value-of select="date_commande" /></fo:block>
						</fo:table-cell>
						<xsl:if test="../type_rapport=2">
							<fo:table-cell display-align="center" border="0.1pt solid gray">
								<fo:block text-align="left"><xsl:value-of select="date_envoi_impr" /></fo:block>
							</fo:table-cell>
						</xsl:if>

					</fo:table-row>
				</xsl:for-each>
			</fo:table-body>

		</fo:table>

	</xsl:template>

</xsl:stylesheet>
