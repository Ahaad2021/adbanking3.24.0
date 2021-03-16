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
			<xsl:apply-templates select="situation_compensation" />
		</fo:root>
	</xsl:template>

	<xsl:template match="situation_compensation">
		<fo:page-sequence master-reference="main" font-size="6pt" font-family="Helvetica">
			<xsl:apply-templates select="header" />
			<xsl:call-template name="footer" />
			<fo:flow flow-name="xsl-region-body">
				<xsl:apply-templates select="header_contextuel" />
				<xsl:apply-templates select="compensations_par_agence" />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<xsl:template match="compensations_par_agence">
		<xsl:for-each select="situation_agence">

			<fo:table border-collapse="collapse" width="100%" table-layout="fixed">
				<fo:table-column column-width="proportional-column-width(5)" />
				<fo:table-column column-width="proportional-column-width(2)" />
				<fo:table-column column-width="proportional-column-width(0.5)" />
				<fo:table-column column-width="proportional-column-width(5)" />
				<fo:table-column column-width="proportional-column-width(2)" />

				<fo:table-header>
					<!-- Empty row -->
					<fo:table-row column-number="3">
						<fo:table-cell display-align="center">
							<fo:block text-align="left"> <fo:leader /> </fo:block>
						</fo:table-cell>
					</fo:table-row>

					<!-- Empty row -->
					<fo:table-row column-number="3">
						<fo:table-cell display-align="center">
							<fo:block text-align="left"> <fo:leader /> </fo:block>
						</fo:table-cell>
					</fo:table-row>

					<fo:table-row font-weight="bold">
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Situation <xsl:value-of select="situation_local/donnees_agence/agence_local" /></fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center"><xsl:value-of select="situation_local/donnees_agence/code_devise_local" /></fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">Situation <xsl:value-of select="situation_distant/donnees_agence/agence_distant" /></fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center"><xsl:value-of select="situation_distant/donnees_agence/code_devise_distant" /></fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-header>

				<fo:table-body>
					<!-- solde debut -->
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Solde début de période compte de liaison <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_local/donnees_agence/solde_deb" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Solde début de période compte de liaison <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_distant/donnees_agence/agence_local" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_distant/donnees_agence/solde_deb" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>

					<!-- total depot -->
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Total dépôts dans <xsl:value-of select="situation_local/donnees_agence/agence_distant" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_local/donnees_agence/total_depot" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Total dépôts dans <xsl:value-of select="situation_distant/donnees_agence/agence_local" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_distant/donnees_agence/total_depot" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>

					<!-- total retraits -->
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Total retraits dans <xsl:value-of select="situation_local/donnees_agence/agence_distant" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_local/donnees_agence/total_retrait" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Total retraits dans <xsl:value-of select="situation_distant/donnees_agence/agence_local" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_distant/donnees_agence/total_retrait" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>

					<!-- mvmts debiteurs -->
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Autres mouvements débiteurs <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_local/donnees_agence/mvmts_deb" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Autres mouvements débiteurs <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_local" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_distant/donnees_agence/mvmts_deb" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>

					<!-- mvmts crediteurs -->
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Autres mouvements créditeurs <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_local/donnees_agence/mvmts_cred" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Autres mouvements créditeurs <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_local" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_distant/donnees_agence/mvmts_cred" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>

					<!-- solde fin cpte liaison -->
					<fo:table-row>
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Solde fin de période compte de liaison <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_local/donnees_agence/solde_fin" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left"> </fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="left">
								Solde fin de période compte de liaison <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_local" />
							</fo:block>
						</fo:table-cell>

						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="right">
								<xsl:value-of select="situation_distant/donnees_agence/solde_fin" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>

				</fo:table-body>

			</fo:table>

			<fo:table border-collapse="collapse" width="100%" table-layout="fixed">
				<fo:table-column column-width="proportional-column-width(1)" />

				<fo:table-body>
					<fo:table-row  font-weight="bold">
						<fo:table-cell border="0.1pt solid gray">
							<fo:block text-align="left">
								<xsl:value-of select="synthese" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>

		</xsl:for-each>

		<!-- Sommaire -->
		<xsl:if test="summary != ''">
			<xsl:for-each select="summary">
				<fo:block text-align="left" font-size="7pt" font-weight="bold" border-top-style="solid" border-top-width="0.3pt" border-bottom-style="solid" border-bottom-width="0.3pt" space-before="0.3in">
					<xsl:value-of select="summary_info"/>
				</fo:block>
			</xsl:for-each>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>
