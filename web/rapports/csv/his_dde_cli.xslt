<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="text"/>

	<xsl:template match="histo_demande_credit">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
		<xsl:apply-templates select="ligneCredit"/>
	</xsl:template>

	<xsl:include href="header.xslt"/>
	<xsl:include href="criteres_recherche.xslt"/>
	<xsl:include href="lib.xslt"/>

	<xsl:template match="header_contextuel">
		<xsl:apply-templates select="criteres_recherche"/>
		<xsl:call-template name="titre1">
			<xsl:with-param name="titre" select="'Informations synthétiques'"/>
		</xsl:call-template>
		<xsl:apply-templates select="infos_synthetiques"/>
	</xsl:template>

	<xsl:template match="infos_synthetiques">;
		<xsl:value-of select="libel"/>; <xsl:value-of select="valeur"/>
	</xsl:template>

	<xsl:template match="ligneCredit">
		<xsl:call-template name="titre1">
			<xsl:with-param name="titre">
				;
				<xsl:value-of select="lib_prod"/>
			</xsl:with-param>
		</xsl:call-template>
		;
		Numero client;Nom client;Numéro dossier;Produit de credit;Date demande;Montant demandé;Devise;Objet demande;Détail objet demande;Durée;Gestionnaire;Etat;Date décision;Montant octroyé;Motif;
		<xsl:apply-templates select="infosCreditSolidiaire"/>
		<xsl:apply-templates select="detailCredit"/>
		<xsl:apply-templates select="xml_total"/>
	</xsl:template>
	<xsl:template match="infosCreditSolidiaire">
		<xsl:value-of select="translate(num_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(no_dossier,';','')"/>;	<xsl:value-of select="translate(prd_credit,';','')"/>;	<xsl:value-of select="translate(date_dde,';','')"/>;	<xsl:value-of select="translate(montant_dde,';','')"/>;	<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(obj_dde,';','')"/>;	<xsl:value-of select="translate(detail_obj_dde,';','')"/>;	<xsl:value-of select="translate(duree,';','')"/>;	<xsl:value-of select="translate(agent_gest,';','')"/>;	<xsl:value-of select="translate(etat,';','')"/>;	<xsl:value-of select="translate(date_decision,';','')"/>;	<xsl:value-of select="translate(montant_octr,';','')"/>;	<xsl:value-of select="translate(motif,';','')"/>;
	</xsl:template>
	<xsl:template match="detailCredit">
		<xsl:choose>
			<xsl:when test="membre_gs=&quot;OUI&quot;">
				<xsl:value-of select="translate(num_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(no_dossier,';','')"/>;	<xsl:value-of select="translate(prd_credit,';','')"/>;	<xsl:value-of select="translate(date_dde,';','')"/>;	<xsl:value-of select="translate(montant_dde,';','')"/>;	<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(obj_dde,';','')"/>;	<xsl:value-of select="translate(detail_obj_dde,';','')"/>;	<xsl:value-of select="translate(duree,';','')"/>;	<xsl:value-of select="translate(agent_gest,';','')"/>;	<xsl:value-of select="translate(etat,';','')"/>;	<xsl:value-of select="translate(date_decision,';','')"/>;	<xsl:value-of select="translate(montant_octr,';','')"/>;	<xsl:value-of select="translate(motif,';','')"/>;
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="translate(num_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(no_dossier,';','')"/>;	<xsl:value-of select="translate(prd_credit,';','')"/>;	<xsl:value-of select="translate(date_dde,';','')"/>;	<xsl:value-of select="translate(montant_dde,';','')"/>;	<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(obj_dde,';','')"/>;	<xsl:value-of select="translate(detail_obj_dde,';','')"/>;	<xsl:value-of select="translate(duree,';','')"/>;	<xsl:value-of select="translate(agent_gest,';','')"/>;	<xsl:value-of select="translate(etat,';','')"/>;	<xsl:value-of select="translate(date_decision,';','')"/>;	<xsl:value-of select="translate(montant_octr,';','')"/>;	<xsl:value-of select="translate(motif,';','')"/>;
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="xml_total">Total;-;-;-;-;<xsl:value-of select="translate(tot_mnt_dem,';','')"/>;-;-;-;-;-;-;-;<xsl:value-of select="translate(tot_mnt_octr,';','')"/>;-;
	</xsl:template>
</xsl:stylesheet>
