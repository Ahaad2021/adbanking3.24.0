<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="clients">
  <xsl:apply-templates select="statut_juridique"/>
</xsl:template>
<xsl:template match="statut_juridique">
  <xsl:apply-templates select="header"/>
  <xsl:apply-templates select="criteres_recherche"/>
  <xsl:apply-templates select="header_contextuel"/>
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="stat_jur"/></xsl:call-template>
  Numéro client;Nom client; Sexe;Date adhésion;Date de naissance;Statut juridique;Secteur activité;Gestionnaire;Date création;Nombre de membres;Etat;
  <xsl:apply-templates select="client"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>

<xsl:template match="infos_synthetiques">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	Nombre total clients ;<xsl:value-of select="translate(nbre_total,';','')"/>;
	Nombre hommes ; <xsl:value-of select="translate(nbre_homme,';','')"/>;
	Nombre femmes; <xsl:value-of select="translate(nbre_femme,';','')"/>;
	Nombre personnes morales; <xsl:value-of select="translate(nbre_pm,';','')"/>;
	Nombre groupes informels; <xsl:value-of select="translate(nbre_gi,';','')"/>;
	Total membres groupe informel; <xsl:value-of select="translate(total_mbre_gi,';','')"/>;
	Nombre groupes solidaires;<xsl:value-of select="translate(nbre_gs,';','')"/>;
	Total membres groupe solidaire;<xsl:value-of select="translate(total_mbre_gs,';','')"/>;
	;
</xsl:template>

<xsl:template match="client">
     <xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(sexe,';','')"/>;<xsl:value-of select="normalize-space(translate(date_adhesion,';',''))"/>;<xsl:value-of select="translate(date_naissance,';','')"/>;<xsl:if test="../@exist_statut_juridique='1'"><xsl:value-of select="translate(statut_juridique,';','')"/></xsl:if>;<xsl:if test="../@exist_sect_activite='1'"><xsl:value-of select="translate(sect_activite,';','')"/></xsl:if>;<xsl:if test="../@exist_gestionnaire='1'"><xsl:value-of select="translate(gestionnaire,';','')"/></xsl:if>;<xsl:if test="../@exist_date_crea='1'"><xsl:value-of select="translate(date_crea,';','')"/></xsl:if>;<xsl:if test="../@exist_nbr_membres='1'"><xsl:value-of select="translate(nbr_membres,';','')"/></xsl:if>;<xsl:if test="../@exist_etat='1'"><xsl:value-of select="translate(etat,';','')"/></xsl:if>;
</xsl:template>

</xsl:stylesheet>
