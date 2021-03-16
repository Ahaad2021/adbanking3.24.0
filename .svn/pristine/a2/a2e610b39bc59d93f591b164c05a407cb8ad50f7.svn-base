<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="risque_credit_activite"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="research_criteria.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="risque_credit_activite">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
		<xsl:apply-templates select="header_contextuel"/>
  		<xsl:apply-templates select="globals"/>
			<xsl:apply-templates select="details"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="globals">
	<fo:table border-collapse="separate" width="100%" table-layout="fixed">
		<fo:table-column column-width="50%"/>
		<fo:table-column column-width="50%"/>
		<fo:table-body>
			<fo:table-row>
			<fo:table-cell>
				<fo:block> </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block  text-align="right">Amounts in <xsl:value-of select="devise"/></fo:block>
			</fo:table-cell>
		</fo:table-row>

		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="details">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Detailed of Risks Situation by Sector'"/></xsl:call-template>
        <fo:table border-collapse="separate" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(3)"/>
                <fo:table-column column-width="proportional-column-width(8)"/>
                <fo:table-column column-width="proportional-column-width(4)"/>
                <fo:table-column column-width="proportional-column-width(3)"/>
                <fo:table-column column-width="proportional-column-width(3)"/>
                <fo:table-column column-width="proportional-column-width(4)"/>
                <fo:table-column column-width="proportional-column-width(3)"/>
                <xsl:if test="@exist_gestionnaire='1'">
		   <fo:table-cell>
			   <fo:block>Agent</fo:block>
		   </fo:table-cell>
		  </xsl:if>
		<fo:table-header>
			<fo:table-row font-weight="bold">
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Line Code</fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Branch of Activity</fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Total amount of loans by each  Sector or Branch of activity</fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Number of Individuals Debtors</fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Number of debtors legal entities and groups</fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Number of Loans Benef. in Legal Entities or Groups</fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" padding-right="2px">
					<fo:block text-align="left">Total number of loans beneficiaries</fo:block>
				</fo:table-cell>
			</fo:table-row>
		</fo:table-header>
		<fo:table-body>
			<xsl:apply-templates select="activ"/>
			<xsl:apply-templates select="total"/>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="research_criteria"/>
</xsl:template>

<xsl:template match="activ">
	<fo:table-row>

				<fo:table-cell border-width="0.1mm" border-style="solid" text-align="right">
					<fo:block text-align="center" color="blue"><xsl:value-of select="index"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
					<fo:block text-align="left"><xsl:value-of select="libel_act"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid">
					<fo:block text-align="right"><xsl:value-of select="mnt_cred"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="nbr_ind_deb"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="nbr_grp_deb"/></fo:block>
				</fo:table-cell>
				<fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
					<fo:block text-align="center"><xsl:value-of select="nbr_grp_benef_pret"/></fo:block>
				</fo:table-cell>
				<fo:table-cell background-color="#CCFFFF" border-width="0.1mm" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="nbr_benef_act"/></fo:block>
				</fo:table-cell>
	</fo:table-row>
</xsl:template>

<xsl:template match="total">

			<fo:table-row>
			<fo:table-cell border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold"> </fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold" text-align="center">TOTAL</fo:block>
			</fo:table-cell>
			<fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_mnt_cred"/></fo:block>
			</fo:table-cell>
			<fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold" text-align="center"><xsl:value-of select="tot_ind_deb"/></fo:block>
			</fo:table-cell>
			<fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold" text-align="center"><xsl:value-of select="tot_grp_deb"/></fo:block>
			</fo:table-cell>
			<fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold" text-align="center"><xsl:value-of select="tot_grp_benef_pret"/> </fo:block>
			</fo:table-cell>
			<fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
				<fo:block font-weight="bold" text-align="center"><xsl:value-of select="tot__benef_pret"/></fo:block>
			</fo:table-cell>
		</fo:table-row>

</xsl:template>

</xsl:stylesheet>
