<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="risques_credits"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="risques_credits">
	<fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/>
			<xsl:apply-templates select="etat_credit"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="etat_credit">
	<fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="solid" border-bottom-style="solid" space-before="0.5in">Etat des crédits : <xsl:value-of select="lib_etat_credit"/></fo:block>
	<xsl:apply-templates select="produit"/>
</xsl:template>



<xsl:template match="produit">
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre"><xsl:value-of select="lib_prod"/></xsl:with-param></xsl:call-template>
			<fo:table border-collapse="separate" border-separation.inline-progression-direction="3pt" width="100%">
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(0.5)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(0.5)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-header>
					<fo:table-row>
						<fo:table-cell number-columns-spanned="6">
							<fo:block></fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="2">
							<fo:block font-weight="bold" text-align="center"> Déboursement</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="2">
							<fo:block font-weight="bold" text-align="center">Remboursement</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="1">
							<fo:block font-weight="bold" text-align="center">Solde</fo:block>
						</fo:table-cell>
						<fo:table-cell number-columns-spanned="3">
							<fo:block font-weight="bold" text-align="center">Retard</fo:block>
						</fo:table-cell>
						<fo:table-cell >
							<fo:block font-weight="bold" text-align="right">Epargne</fo:block>
						</fo:table-cell>
						<fo:table-cell >
							<fo:block ></fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row>
						<fo:table-cell>
							<fo:block font-weight="bold">Dossier</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold">Durée</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right" font-weight="bold">Client</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold">Nom client</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right" font-weight="bold">Statut juridique</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right" font-weight="bold">Sexe</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold">Date</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="right">Montant</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold">Date dernier remb</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold">Date dernier ech remb</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="right">Capital restant</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="right">Capital</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="center">échéances</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="center">jours</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="right">nantie</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" text-align="right">provision</fo:block>
						</fo:table-cell>
					</fo:table-row>

				</fo:table-header>
				<fo:table-body>
					<xsl:apply-templates select="risque_credit"/>
              <xsl:apply-templates select="xml_total"/>
			</fo:table-body>
			</fo:table>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
	<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>

<xsl:template match="infos_synthetiques">
    <xsl:apply-templates select="criteres_recherche"/>
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre de crédits en cours: <xsl:value-of select="nbre_credits"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre de credits en retard et sain: <xsl:value-of select="nbre_credits_retard"/> (<xsl:value-of select="prc_credits_retard"/>)</fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Portefeuille total: <xsl:value-of select="portefeuille"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body>
			<fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Portefeuille en retard: <xsl:value-of select="portefeuille_retard"/> (<xsl:value-of select="prc_portefeuille_retard"/>)</fo:block>
			</fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total solde intérêts des crédits en retard et sain: <xsl:value-of select="total_solde_int"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total solde pénalités des crédits en retard et sain: <xsl:value-of select="total_solde_pen"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total retard capital: <xsl:value-of select="total_retard_cap"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total retard intérêts: <xsl:value-of select="total_retard_int"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total épargne nantie des crédits en retard et sain: <xsl:value-of select="total_epargne_nantie"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
</xsl:template>

<xsl:template match="risque_credit">
	<fo:table-row>
		<fo:table-cell>
			<fo:block><xsl:value-of select="num_doss"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="duree"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="num_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="left"><xsl:value-of select="nom_client"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="statut_jur"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="sexe"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block><xsl:value-of select="date_debloc"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="mnt_debloc"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="date_dernier_remb"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="date_dernier_ech_remb"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="solde_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="retard_cap"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center"><xsl:value-of select="nbre_ech_retard"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center"><xsl:value-of select="nbre_jours_retard"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="epargne_nantie"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="prov_mnt"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="right"><xsl:value-of select="gestionnaire"/></fo:block>
		</fo:table-cell>
            </fo:table-row>
</xsl:template>

<xsl:template match="xml_total">

        <fo:table-row>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold"> Total</fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                 <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                 <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                 <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                  <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_mnt_debloc"/></fo:block>
                </fo:table-cell>
                 <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                 <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_solde_cap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                    <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_retard_cap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                      <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_epargne_nantie"/></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                      <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_prov_mnt"/></fo:block>
                </fo:table-cell>

        </fo:table-row>
</xsl:template>

</xsl:stylesheet>