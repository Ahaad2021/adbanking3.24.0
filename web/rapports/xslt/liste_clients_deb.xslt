<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="liste_clients_deb"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="liste_clients_deb">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
		<xsl:apply-templates select="header_contextuel"/>
			<xsl:apply-templates select="total"/>
			<xsl:apply-templates select="details"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="total">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations globales'"/></xsl:call-template>
	<fo:table>
		<fo:table-column column-width="10cm"/>
		<fo:table-column column-width="6cm"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Encours total de crédit : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="right"><xsl:value-of select="encours_total"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Encours des dossiers ci-dessous : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block  text-align="right"><xsl:value-of select="encours_clients_deb"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Ratio : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="right"><xsl:value-of select="ratio"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Encours en retard sur ces dossiers : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block  text-align="right"><xsl:value-of select="encours_retard_deb"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Ratio encours en retard : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block  text-align="right"><xsl:value-of select="ratio_retard"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Total des crédits sain : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block  text-align="right"><xsl:value-of select="total_sain"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="libel_etat">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre"><xsl:value-of select="etat"/></xsl:with-param></xsl:call-template>
        <fo:table border-collapse="separate" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(1)"/>
                <fo:table-column column-width="proportional-column-width(2)"/>
                <fo:table-column column-width="proportional-column-width(2)"/>
                <fo:table-column column-width="proportional-column-width(4)"/>
                <fo:table-column column-width="proportional-column-width(4)"/>
                <fo:table-column column-width="proportional-column-width(5)"/>
                <fo:table-column column-width="proportional-column-width(3)"/>
                <fo:table-column column-width="proportional-column-width(3)"/>
                <xsl:if test="@exist_gestionnaire='1'">
		   <fo:table-cell>
			   <fo:block>Gestionnaire</fo:block>
		   </fo:table-cell>
		  </xsl:if>
		<fo:table-header>
			<fo:table-row font-weight="bold">
				<fo:table-cell>
					<fo:block text-align="right">Rang</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right">Client</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="center">Doss</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Nom client</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right">Encours crédit</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right">C/V Encours crédit</fo:block>
				</fo:table-cell>
                                <fo:table-cell>
					<fo:block text-align="center">Etat crédit</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right">Pénalités attendues</fo:block>
				</fo:table-cell>
			</fo:table-row>
		</fo:table-header>
		<fo:table-body>
			<xsl:apply-templates select="client"/>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="details">
	<xsl:apply-templates select="libel_etat"/>
</xsl:template>

<xsl:template match="client">
	<fo:table-row>
		<xsl:choose>
			<xsl:when test="groupe_gs='groupe'">
				<fo:table-cell border-width="0.2mm" border-style="solid" text-align="right">
					<fo:block font-weight="bold"><xsl:value-of select="index"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid" text-align="right">
					<fo:block font-weight="bold"><xsl:value-of select="id_client"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid" text-align="center">
					<fo:block font-weight="bold"><xsl:value-of select="id_doss"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid">
					<fo:block font-weight="bold"><xsl:value-of select="nom"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid">
					<fo:block font-weight="bold" text-align="right"><xsl:value-of select="encours_client"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid">
					<fo:block font-weight="bold" text-align="right"><xsl:value-of select="cv_encours_client"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid">
					<fo:block font-weight="bold" text-align="center"><xsl:value-of select="cre_etat"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.2mm" border-style="solid">
					<fo:block font-weight="bold" text-align="right"><xsl:value-of select="mnt_pen"/></fo:block>
				</fo:table-cell>
				</xsl:when>
				<xsl:when test="membre_gs='membre'">
					<fo:table-cell border-width="0.02mm" border-style="solid" text-align="right">
						<fo:block font-style="oblique" font-weight="500"><xsl:value-of select="index"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid" text-align="right">
						<fo:block font-style="oblique" font-weight="500"><xsl:value-of select="id_client"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid" text-align="center">
						<fo:block font-style="oblique" font-weight="500"><xsl:value-of select="id_doss"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid">
						<fo:block font-style="oblique" font-weight="500"><xsl:value-of select="nom"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid">
						<fo:block font-style="oblique" font-weight="500" text-align="right"><xsl:value-of select="encours_client"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid">
						<fo:block font-style="oblique" font-weight="500" text-align="right"><xsl:value-of select="cv_encours_client"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid">
						<fo:block font-style="oblique" font-weight="500" text-align="center"><xsl:value-of select="cre_etat"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.02mm" border-style="solid">
						<fo:block font-style="oblique" font-weight="500" text-align="right"><xsl:value-of select="mnt_pen"/></fo:block>
					</fo:table-cell>
				</xsl:when>
				<xsl:otherwise>
					<fo:table-cell border-width="0.1mm" border-style="solid" text-align="right">
						<fo:block><xsl:value-of select="index"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid" text-align="right">
						<fo:block><xsl:value-of select="id_client"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
						<fo:block><xsl:value-of select="id_doss"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid">
						<fo:block><xsl:value-of select="nom"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid">
						<fo:block text-align="right"><xsl:value-of select="encours_client"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid">
						<fo:block text-align="right"><xsl:value-of select="cv_encours_client"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid">
						<fo:block text-align="center"><xsl:value-of select="cre_etat"/></fo:block>
					</fo:table-cell>
					<fo:table-cell border-width="0.1mm" border-style="solid">
						<fo:block text-align="right"><xsl:value-of select="mnt_pen"/></fo:block>
					</fo:table-cell>
				</xsl:otherwise>
		</xsl:choose>
	</fo:table-row>
</xsl:template>

</xsl:stylesheet>
