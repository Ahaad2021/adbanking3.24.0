<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="cptes_epargne_cloture"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="cptes_epargne_cloture">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/>
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations globales'"/></xsl:call-template>
			<xsl:apply-templates select="total"/>
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>

			<fo:table width="100%" table-layout="fixed">
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(3)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1.5)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>



				<fo:table-header>
					<fo:table-row font-weight="bold" border-width="0.3mm" border-style="solid">
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">Numéro compte</fo:block>
						</fo:table-cell>
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">N°Client</fo:block>
						</fo:table-cell>
						<fo:table-cell border-width="0.3mm" border-style="solid" >
							<fo:block text-align="center">Nom client</fo:block>
						</fo:table-cell>
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">Solde clôture</fo:block>
						</fo:table-cell>
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">solde clôture C/V(<xsl:value-of select="total/devise"/>)</fo:block>
						</fo:table-cell >
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">date clôture</fo:block>
						</fo:table-cell>
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">Raison</fo:block>
						</fo:table-cell>
						<fo:table-cell border-width="0.3mm" border-style="solid">
							<fo:block text-align="center">Produit</fo:block>
						</fo:table-cell>

					</fo:table-row>
				</fo:table-header>
				<fo:table-body>
					<xsl:apply-templates select="ligne_cpte"/>


				</fo:table-body>
			</fo:table>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="ligne_cpte">
	<fo:table-row>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="center"><xsl:value-of select="num_cpte"/></fo:block>
		</fo:table-cell>
 		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="center"><xsl:value-of select="num_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="left"><xsl:value-of select="nom_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="right"><xsl:value-of select="solde_clot"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="right"><xsl:value-of select="solde_clot_cv"/>  </fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="center"><xsl:value-of select="date_clot"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="left"><xsl:value-of select="raison_clot"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="left"><xsl:value-of select="produit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-width="0.1mm" border-style="solid">
			<fo:block text-align="left"><xsl:value-of select="classe_comptable"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>



<xsl:template match="total">
        <fo:table border-collapse="separate" width="50%" table-layout="fixed" >
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-body>
              <fo:table-row font-weight="bold" >
							<fo:table-cell>
								<fo:block>Nombre total comptes clôturés   :</fo:block>
							</fo:table-cell>
							<fo:table-cell>
								<fo:block text-align="right"><xsl:value-of select="total_nombre"/></fo:block>
							</fo:table-cell>
							</fo:table-row>
							<fo:table-row font-weight="bold" >
								<fo:table-cell>
									<fo:block>Solde total clôture en (<xsl:value-of select="devise"/>) :</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block text-align="right"><xsl:value-of select="total_montant"/></fo:block>
								</fo:table-cell>
							</fo:table-row>
            </fo:table-body>
      </fo:table>
</xsl:template>
<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

</xsl:stylesheet>
