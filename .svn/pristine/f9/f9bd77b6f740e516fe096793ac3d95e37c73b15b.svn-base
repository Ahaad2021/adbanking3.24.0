<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="echeancier"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="echeancier">
			<xsl:apply-templates select="infos_doss"/>	
</xsl:template>


<xsl:template match="infos_doss">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
	<xsl:apply-templates select="header_contextuel"/>

			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Echeances'"/></xsl:call-template>
			<fo:table border-collapse="separate" width="100%" table-layout="fixed">
				<fo:table-column column-width="proportional-column-width(1)"/> 
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray" />
				<fo:table-column column-width="proportional-column-width(1)"/>
                                <fo:table-column column-width="proportional-column-width(1)"/> 
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
                                <fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
                                <fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-header>
					<fo:table-row font-weight="bold">
                                                <fo:table-cell>
							<fo:block></fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="4">
							<fo:block text-align="center">Attendu</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="5">
							<fo:block text-align="center">Remboursé</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="5">
							<fo:block text-align="center">Restant dû</fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row font-weight="bold">
						<fo:table-cell>
							<fo:block>Date</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Capital</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Intérêts</fo:block>
						</fo:table-cell>
                                                <fo:table-cell>
							<fo:block text-align="right">Garantie</fo:block>
						</fo:table-cell>
						<fo:table-cell >
							<fo:block text-align="right">Total</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Capital</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Intérêts</fo:block>
						</fo:table-cell>
                                                <fo:table-cell>
							<fo:block text-align="right">Garantie</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Pénalités</fo:block>
						</fo:table-cell>
						<fo:table-cell >
							<fo:block text-align="right">Total</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Capital</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Intérêts</fo:block>
						</fo:table-cell>
                                                <fo:table-cell>
							<fo:block text-align="right">Garantie</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Pénalités</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right">Total</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-header>
				<fo:table-body>
					<xsl:apply-templates select="echeance"/>
				</fo:table-body>
			</fo:table>
	</fo:flow>
	</fo:page-sequence>
</xsl:template>


<xsl:template match="header_contextuel">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Numéro client: <xsl:value-of select="num_client"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nom client: <xsl:value-of select="nom_client"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Numéro crédit: <xsl:value-of select="num_credit"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Etat: <xsl:value-of select="etat_credit"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date demande: <xsl:value-of select="date_demande"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date approbation: <xsl:value-of select="date_approb"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date déboursement: <xsl:value-of select="date_debours"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Produit: <xsl:value-of select="produit"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Montant: <xsl:value-of select="montant"/></fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Taux d'intérêts: <xsl:value-of select="taux_int"/>%</fo:block></fo:list-item-body>
		</fo:list-item>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Delais de grâce: <xsl:value-of select="delais_grace"/> jour(s)</fo:block></fo:list-item-body>
		</fo:list-item>

		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Devise: <xsl:value-of select="devise"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
</xsl:template>

<xsl:template match="echeance">
	<xsl:apply-templates select="ech_theo"/>
	<xsl:apply-templates select="suivi_remb"/>
      	<xsl:apply-templates select="xml_total"/>
	<fo:table-row>
		<fo:table-cell number-columns-spanned="15">
			<fo:block>&#160;</fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

<xsl:template match="ech_theo">
	<fo:table-row>
		<fo:table-cell>
			<fo:block ><xsl:value-of select="date_ech"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="cap_du"/></fo:block>
			</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="int_du"/></fo:block>
		</fo:table-cell>
                <fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="gar_du"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block font-weight="bold"  text-align="right"><xsl:value-of select="total_du"/></fo:block>
		</fo:table-cell>
                <fo:table-cell number-columns-spanned="5">
			<fo:block></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_int"/></fo:block>
		</fo:table-cell>
                <fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_gar"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_pen"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block font-weight="bold" text-align="right"><xsl:value-of select="solde_total"/></fo:block>
		</fo:table-cell>
	</fo:table-row>	
</xsl:template>

<xsl:template match="suivi_remb">
	<fo:table-row color="gray">
		
		<fo:table-cell>
			<fo:block text-align="left"><xsl:value-of select="date_suivi"/></fo:block>
		</fo:table-cell>
                <fo:table-cell number-columns-spanned="4">
			<fo:block></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="mnt_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="mnt_int"/></fo:block>
		</fo:table-cell>
                <fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="mnt_gar"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="mnt_pen"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block font-weight="bold" text-align="right"><xsl:value-of select="mnt_total"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_int"/></fo:block>
		</fo:table-cell>
                <fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_gar"/></fo:block>
		</fo:table-cell> 
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_pen"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block font-weight="bold" text-align="right"><xsl:value-of select="solde_total"/></fo:block>
		</fo:table-cell>
	</fo:table-row>	
</xsl:template>
<xsl:template match="xml_total">
	<fo:table-row>
		<fo:table-cell padding-before="20pt">
			<fo:block font-weight="bold">TOTAL</fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap" ><xsl:value-of select="tot_cap_du"/></fo:block>
			</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_int_du"/></fo:block>
		</fo:table-cell>
                <fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_gar_du"/></fo:block>
		</fo:table-cell> 
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_total_du"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_remb_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_remb_int"/></fo:block>
		</fo:table-cell>
                <fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_remb_gar"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_remb_pen"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_remb_total"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_int"/></fo:block>
		</fo:table-cell>
                <fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_gar"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_pen"/></fo:block>
		</fo:table-cell>
		<fo:table-cell padding-before="20pt">
			<fo:block text-align="right" font-weight="bold" wrap-option="no-wrap"><xsl:value-of select="tot_total"/></fo:block>
		</fo:table-cell>
	</fo:table-row>	
</xsl:template>
</xsl:stylesheet>
