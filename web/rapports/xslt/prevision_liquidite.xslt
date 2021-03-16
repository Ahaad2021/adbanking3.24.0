<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="prevision_liquidite"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="prevision_liquidite">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="body"/>
			<xsl:if test="enreg_agence/is_siege='true'">
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Liste des agences consolidées'"/></xsl:call-template>
	 <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(3)"/>
		<fo:table-column column-width="proportional-column-width(3)"/>
		<fo:table-column column-width="proportional-column-width(3)"/>
  <fo:table-header>
		    <fo:table-row font-weight="bold">
			     <fo:table-cell display-align="center" border="0.1pt solid gray">
				      <fo:block text-align="center">Identifiant agence </fo:block>
			    </fo:table-cell>
			    <fo:table-cell display-align="center" border="0.1pt solid gray">
				      <fo:block text-align="center"> Libellé agence  </fo:block>
			    </fo:table-cell>
			    <fo:table-cell display-align="center" border="0.1pt solid gray">
				     <fo:block text-align="center"> Date dernier mouvement </fo:block>
			    </fo:table-cell>
	      </fo:table-row>
	    </fo:table-header>
	    <fo:table-body>
	        <xsl:apply-templates select="enreg_agence"/>
	    </fo:table-body>
     </fo:table>
     </xsl:if>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>


<xsl:template match="body">
	<xsl:apply-templates select="credit"/>
	<xsl:apply-templates select="epargne"/>
</xsl:template>


<xsl:template name="debut_table">
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-header>
			<fo:table-row font-weight="bold" text-align="right">
				<fo:table-cell>
					<fo:block></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Aujourd'hui</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Semaine +1</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Semaine +2</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Semaine +3</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Mois +1</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Mois +2</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Mois +3</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Mois +6</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Mois +9</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block>Mois +12</fo:block>
				</fo:table-cell>
			</fo:table-row>
			<fo:table-row font-weight="bold" text-align="right">
				<fo:table-cell>
					<fo:block></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/j"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/s1"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/s2"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/s3"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/m1"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/m2"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/m3"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/m6"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/m9"/></fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block text-align="right"><xsl:value-of select="/prevision_liquidite/body/dates/previsions/m12"/></fo:block>
				</fo:table-cell>
			</fo:table-row>
		</fo:table-header>
</xsl:template>

<xsl:template match="credit">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Crédit'"/></xsl:call-template>
	<fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
	<xsl:call-template name="debut_table"/>
        <fo:table-body>
	<xsl:apply-templates select="cap_attendu/previsions"><xsl:with-param name="titre" select="'Capital attendu'"/></xsl:apply-templates>
	<xsl:apply-templates select="int_attendu/previsions"><xsl:with-param name="titre" select="'Intérêts attendus'"/></xsl:apply-templates>
        </fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="epargne">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Epargne'"/></xsl:call-template>
	<fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
	<xsl:call-template name="debut_table"/>
        <fo:table-body>
	<xsl:apply-templates select="ep_nantie/previsions"><xsl:with-param name="titre" select="'Epargne nantie'"/></xsl:apply-templates>
	<xsl:apply-templates select="ep_terme/previsions"><xsl:with-param name="titre" select="'Epargne à terme'"/></xsl:apply-templates>
	<xsl:apply-templates select="ep_libre/previsions"><xsl:with-param name="titre" select="'Epargne libre'"/></xsl:apply-templates>
        </fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="previsions">
	<xsl:param name="titre"/>
	<fo:table-row>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="$titre"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="j"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="s1"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="s2"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="s3"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="m1"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="m2"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="m3"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="m6"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="m9"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.5pt solid gray">
			<fo:block text-align="center"><xsl:value-of select="m12"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

<xsl:template match="enreg_agence">

 <fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="id_ag"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="libel_ag"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="date_max"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</xsl:template>
		
</xsl:stylesheet>
