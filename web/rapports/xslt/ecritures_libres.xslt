<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="ecritures_libres"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="ecritures_libres">
	<fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="ecritures_devise"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="ecritures_devise">
	<xsl:apply-templates select="infos_globales"/>
	<xsl:apply-templates select="detail"/>
</xsl:template>

<xsl:template match="infos_globales">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="concat('Informations globales ', ../@devise)"/></xsl:call-template>

	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<xsl:choose>
	    <xsl:when test="sans_gui=1">
	    <fo:table-row>
			<fo:table-cell>
				<fo:block>Agent sans guichet : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block><xsl:value-of select="nom_uti"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Login : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block><xsl:value-of select="login"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</xsl:when>
		<xsl:otherwise>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Agent : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block><xsl:value-of select="nom_uti"/></fo:block>
			</fo:table-cell>
		</fo:table-row>

		<fo:table-row>
			<fo:table-cell>
				<fo:block>Login : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block><xsl:value-of select="login"/></fo:block>
			</fo:table-cell>
		</fo:table-row>

				<fo:table-row>
			<fo:table-cell>
				<fo:block>Encaisse début de journée : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block><xsl:value-of select="encaisse_deb"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell>
				<fo:block>Encaisse fin de journée : </fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block><xsl:value-of select="encaisse_fin"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</xsl:otherwise>
		</xsl:choose>
		</fo:table-body>
	</fo:table>
	<xsl:apply-templates select="resume_transactions"/>
</xsl:template>

<xsl:template match="resume_transactions">
	<fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>

		<fo:table-header>
			<fo:table-row font-weight="bold">
				<fo:table-cell>
					<fo:block space-before.optimum="0.5cm" space-after.optimum="0.2cm">Opération</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-before.optimum="0.5cm" space-after.optimum="0.2cm" text-align="right">Nombre</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-before.optimum="0.5cm" space-after.optimum="0.2cm" text-align="right">Montant</fo:block>
				</fo:table-cell>
			</fo:table-row>
		</fo:table-header>
		<fo:table-body>
			<xsl:apply-templates select="ligne_resume_transactions"/>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="ligne_resume_transactions">
	<xsl:if test="@total = '0'">
		<fo:table-row>
			<fo:table-cell>
				<fo:block><xsl:value-of select="libel_operation"/></fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="right"><xsl:value-of select="nombre"/></fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block text-align="right"><xsl:value-of select="montant"/></fo:block>
		</fo:table-cell>
		</fo:table-row>
	</xsl:if>
	<xsl:if test="@total = '1'">
		<fo:table-row font-weight="bold">
			<fo:table-cell>
				<fo:block space-before.optimum="0.2cm"><xsl:value-of select="libel_operation"/></fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block space-before.optimum="0.2cm" text-align="right"><xsl:value-of select="nombre"/></fo:block>
			</fo:table-cell>
			<fo:table-cell>
				<fo:block space-before.optimum="0.2cm" text-align="right"><xsl:value-of select="montant_debit"/></fo:block>
		</fo:table-cell>
			<fo:table-cell>
				<fo:block space-before.optimum="0.2cm" text-align="right"><xsl:value-of select="montant_credit"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:if>
</xsl:template>

<xsl:template match="detail">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="concat('Détail des transactions ', ../@devise)"/></xsl:call-template>
	<fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(3)"/>
		<fo:table-column column-width="proportional-column-width(7)"/>
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(2)"/>

		<fo:table-header>
			<fo:table-row font-weight="bold">
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm">Num trans</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm">Client</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm">Date/Heure</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm">Opération</fo:block>
				</fo:table-cell>
				<fo:table-cell padding-end="0.2cm">
					<fo:block space-after.optimum="0.1cm" text-align="right">Cpte debit</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm">Cpte credit</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm" text-align="right">Mnt débit</fo:block>
				</fo:table-cell>
				<fo:table-cell>
					<fo:block space-after.optimum="0.1cm" text-align="right">Mnt crédit</fo:block>
				</fo:table-cell>

			</fo:table-row>
		</fo:table-header>
		<fo:table-body>
			<xsl:apply-templates select="ligne_detail"/>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="ligne_detail">
	<fo:table-row>
		<fo:table-cell>
			<fo:block><xsl:value-of select="num_trans"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="heure"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block wrap-option="wrap"><xsl:value-of select="libel_operation"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-end="0.2cm">
			<fo:block text-align="right"><xsl:value-of select="compte_debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="compte_credit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="montant_debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="montant_credit"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

</xsl:stylesheet>
