<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="balance_comptable"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="balance_comptable">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="comptable"/>			
			<xsl:apply-templates select="agences"/>		
		</fo:flow>
	</fo:page-sequence>
</xsl:template>
<xsl:template match="comptable">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre"><xsl:value-of select="@type"/></xsl:with-param></xsl:call-template>

			<fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
				<fo:table-column column-width="4cm"/>
				<fo:table-column column-width="2cm"/>
				<fo:table-column column-width="2.5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.5cm"/>
				<fo:table-column column-width="2.5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.5cm"/>
				<fo:table-column column-width="2.5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.5cm"/>
				<fo:table-column column-width="2.5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.5cm"/>
				<fo:table-column column-width="2cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>

				<fo:table-header>
					<fo:table-row font-weight="bold">
						<fo:table-cell>
							<fo:block>Libellé compte</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block>N°</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="2">
							<fo:block text-align="center">Solde début</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="2">
							<fo:block text-align="center">Mouvements</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="2">
							<fo:block text-align="center">
								Total période
							</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="2">
							<fo:block text-align="center">Solde fin</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block>Variation</fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row font-weight="bold">
						<fo:table-cell number-columns-spanned="2" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block></fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Débit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Crédit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Débit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Crédit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Débit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Crédit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Débit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block text-align="right" space-before.optimum="0.2cm">Crédit</fo:block>
						</fo:table-cell>
						<fo:table-cell border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
							<fo:block></fo:block>
						</fo:table-cell>
					</fo:table-row>				
				</fo:table-header>

				<fo:table-body>
					<xsl:apply-templates select="compte"/>
				</fo:table-body>
			</fo:table>
</xsl:template>

<xsl:template match="agences">
<xsl:if test="enreg_agence/is_siege='true'">
<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Liste des agences consolidées'"/></xsl:call-template>
	<fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
				<fo:table-column column-width="4cm"/>
				<fo:table-column column-width="15cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="6cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
	<fo:table-header>
		<fo:table-row font-weight="bold">
			<fo:table-cell>
				<fo:block text-align="center">Identifiant agence </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="center"> Libellé agence  </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="center"> Date dernier mouvement </fo:block>
			</fo:table-cell>
	</fo:table-row>
	</fo:table-header>
	<fo:table-body>
	  <xsl:apply-templates select="enreg_agence"/>
	</fo:table-body>
</fo:table>	
</xsl:if>	
</xsl:template>

<xsl:template match="enreg_agence">
 <fo:table-row>
			<fo:table-cell>
				<fo:block text-align="center"><xsl:value-of select="id_ag"/></fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="center"><xsl:value-of select="libel_ag"/></fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="center"><xsl:value-of select="date_max"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
</xsl:template>

<xsl:template match="compte">
	<xsl:if test="@total = '1'">
	<fo:table-row font-weight="bold">
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="left" space-before.optimum="0.2cm"><xsl:value-of select="libel"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="left" space-before.optimum="0.2cm"><xsl:value-of select="num"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_debut_deb"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_debut_cre"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="total_debits"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="total_credits"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="calcul_debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="calcul_credit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_fin_deb"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_fin_cre"/></fo:block>
		</fo:table-cell>
		<fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
			<fo:block text-align="left" space-before.optimum="0.2cm"><xsl:value-of select="variation"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
	</xsl:if>
	<xsl:if test="@total = '0'">
	<fo:table-row>
		<fo:table-cell>
			<fo:block text-align="left" space-before.optimum="0.2cm"><xsl:value-of select="libel"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="left" space-before.optimum="0.2cm"><xsl:value-of select="num"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_debut_deb"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_debut_cre"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="total_debits"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="total_credits"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="calcul_debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="calcul_credit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_fin_deb"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right" space-before.optimum="0.2cm"><xsl:value-of select="solde_fin_cre"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="left" space-before.optimum="0.2cm"><xsl:value-of select="variation"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
	</xsl:if>
</xsl:template>
</xsl:stylesheet>
