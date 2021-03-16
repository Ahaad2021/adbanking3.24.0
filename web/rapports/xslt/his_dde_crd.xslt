<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="histo_demande_credit"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="histo_demande_credit">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/>
			<xsl:apply-templates select="ligneCredit"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>

<xsl:template match="infos_synthetiques">
	<fo:list-block>
		<fo:list-item>
			<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
			<fo:list-item-body><fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/><xsl:value-of select="libel"/>: <xsl:value-of select="valeur"/></fo:block></fo:list-item-body>
		</fo:list-item>
	</fo:list-block>
</xsl:template>

<xsl:template match="ligneCredit">
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre"><xsl:value-of select="lib_prod"/></xsl:with-param></xsl:call-template>
			<fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(4)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(3)"/>
				<fo:table-column column-width="proportional-column-width(3)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(1)"/>
				<fo:table-column column-width="proportional-column-width(3)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>
				<fo:table-column column-width="proportional-column-width(2)"/>

				<fo:table-header>
					<fo:table-row font-weight="bold">
		    		<fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Client</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Nom</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Doss</fo:block>
						</fo:table-cell>
		    		<fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Prod</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Date demande</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Montant demandé</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Dev</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Objet</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Etat le plus avancé</fo:block>
						</fo:table-cell>
						<fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Nbr. Ech. état avancé</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Durée (mois)</fo:block>
						</fo:table-cell>
		  		  <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Agent</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Etat dossier</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Date de décision</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Montant octroyé</fo:block>
						</fo:table-cell>
				    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px"  border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
							<fo:block text-align="center">Motif</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-header>

				<fo:table-body>
					<xsl:apply-templates select="infosCreditSolidiaire"/>
					<xsl:apply-templates select="detailCredit"/>
					<xsl:apply-templates select="xml_total"/>
				</fo:table-body>

			</fo:table>
</xsl:template>

<xsl:template match="infosCreditSolidiaire">
	<fo:table-row>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="num_client"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
    	<fo:block text-align="left" font-weight="bold"><xsl:value-of select="nom_client"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="no_dossier"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="prd_credit"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="date_dde"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="right" font-weight="bold"><xsl:value-of select="montant_dde"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="devise"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="left" font-weight="bold"><xsl:value-of select="obj_dde"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="left"  font-weight="bold"><xsl:value-of select="eta_avance"/></fo:block>
		</fo:table-cell>
		<fo:table-cell display-align="center" border="0.1pt solid gray">
			<fo:block text-align="left" font-weight="bold"><xsl:value-of select="nbr_ech_eta_avance"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="duree"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="left"  font-weight="bold"><xsl:value-of select="agent_gest"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="etat"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="date_decision"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
			<fo:block text-align="right" wrap-option="no-wrap" font-weight="bold"><xsl:value-of select="montant_octr"/></fo:block>
		</fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-right-width="0.5px" border-style="solid">
			<fo:block text-align="center" font-weight="bold"><xsl:value-of select="motif"/></fo:block>
		</fo:table-cell>
	</fo:table-row>
</xsl:template>

<xsl:template match="detailCredit">
	<xsl:choose>
  	<xsl:when test='membre_gs="OUI"'>
			<fo:table-row>
				<fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-style="solid">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="num_client"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="left" font-style="italic"><xsl:value-of select="nom_client"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="no_dossier"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="prd_credit"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="date_dde"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="right" font-style="italic"><xsl:value-of select="montant_dde"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="devise"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="left" font-style="italic"><xsl:value-of select="obj_dde"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="left" font-style="italic"><xsl:value-of select="eta_avance"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="nbr_ech_eta_avance"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="duree"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="left" wrap-option="no-wrap" font-style="italic"><xsl:value-of select="agent_gest"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="etat"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="date_decision"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid gray">
					<fo:block text-align="right" wrap-option="no-wrap" font-style="italic"><xsl:value-of select="montant_octr"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid black" border-right-width="0.5px" border-style="solid">
					<fo:block text-align="center" font-style="italic"><xsl:value-of select="motif"/></fo:block>
				</fo:table-cell>
			</fo:table-row>
		</xsl:when>

  	<xsl:otherwise>
			<fo:table-row>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="num_client"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
		    	<fo:block><xsl:value-of select="nom_client"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="no_dossier"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="prd_credit"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="date_dde"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="right"><xsl:value-of select="montant_dde"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="devise"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="left"><xsl:value-of select="obj_dde"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="left" wrap-option="no-wrap" font-style="italic"><xsl:value-of select="eta_avance"/></fo:block>
				</fo:table-cell>
				<fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center" wrap-option="no-wrap" font-style="italic"><xsl:value-of select="nbr_ech_eta_avance"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="duree"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="left" wrap-option="no-wrap"><xsl:value-of select="agent_gest"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="etat"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center" wrap-option="no-wrap"><xsl:value-of select="date_decision"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="right" wrap-option="no-wrap"><xsl:value-of select="montant_octr"/></fo:block>
				</fo:table-cell>
		    <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px"  border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
					<fo:block text-align="center"><xsl:value-of select="motif"/></fo:block>
				</fo:table-cell>
			</fo:table-row>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="xml_total">
        <fo:table-row>
                <fo:table-cell padding-before="8pt">
                        <fo:block></fo:block>
                </fo:table-cell>
                 <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold"> Total en devise</fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold"> </fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold"> </fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold"> </fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_mnt_dem"/></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                    <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                   <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                   <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                   <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                   <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                        <fo:block font-weight="bold" text-align="right"><xsl:value-of select="tot_mnt_octr"/></fo:block>
                </fo:table-cell>
                <fo:table-cell padding-before="8pt">
                   <fo:block font-weight="bold" text-align="right"></fo:block>
                </fo:table-cell>
        </fo:table-row>
</xsl:template>

</xsl:stylesheet>
