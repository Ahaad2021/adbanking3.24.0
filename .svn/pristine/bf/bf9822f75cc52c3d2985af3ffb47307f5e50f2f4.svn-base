<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="liste_societaires">
<xsl:apply-templates select="header"/>
    <xsl:apply-templates select="liste_societaires_init"/><xsl:value-of select="';'"/>;
 <xsl:apply-templates select="header"/><xsl:value-of select="';'"/>
 <xsl:value-of select="translate(' Complément liste des sociétaires de l’institution
   (Sociétaires dont la valeur de parts sociales libérées est moins que la valeur nominale)',';','')"/><xsl:value-of select="';'"/>;
              
<xsl:apply-templates select="liste_societaires_comp"/><xsl:value-of select="';'"/>
</xsl:template>

<xsl:template match="liste_societaires_init">
 <xsl:apply-templates select="header_contextuel/total"/>

 <xsl:apply-templates select="detail_stat_jur"/>
</xsl:template>

<xsl:template match="liste_societaires_comp">
 <xsl:apply-templates select="header_contextuel_comp/total_comp"/>

 <xsl:apply-templates select="detail_stat_jur_comp"/>
</xsl:template>


<xsl:template match="total">
    Nombre de sociétaires;<xsl:value-of select="translate(nbre_soc,';','')"/>;
    Nombre total de parts souscrites ;<xsl:value-of select="translate(nbre_ps,';','')"/>;
    Nombre total de parts libérées ;<xsl:value-of select="translate(nbre_ps_lib,';','')"/>;
    Capital social souscrites ;<xsl:value-of select="translate(capital_social_souscrites,';','')"/>;
    Capital social libérées ;<xsl:value-of select="translate(capital_social_lib,';','')"/>;
    Capital social restant ;<xsl:value-of select="translate(capital_social_restant,';','')"/>;
    Valeur nominale d'une part sociale ;<xsl:value-of select="translate(valeurnominale,';','')"/>;
</xsl:template>

<xsl:template match="total_comp">
    Nombre de sociétaires;<xsl:value-of select="translate(nbre_soc_comp,';','')"/>;
    Nombre total de parts souscrites ;<xsl:value-of select="translate(nbre_ps_comp,';','')"/>;
    Capital social souscrites ;<xsl:value-of select="translate(capital_social_souscrites_comp,';','')"/>;
    Capital social libérées ;<xsl:value-of select="translate(capital_social_lib_comp,';','')"/>;
    Capital social restant ;<xsl:value-of select="translate(capital_social_restant_comp,';','')"/>;
    Valeur nominale d'une part sociale ;<xsl:value-of select="translate(valeurnominale_comp,';','')"/>;
</xsl:template>

<xsl:template match="detail_stat_jur">
    Nombre de sociétaires : <xsl:value-of select="translate(nbre_soc,';','')"/>;
    Nombre total de parts souscrites : <xsl:value-of select="translate(nbre_ps,';','')"/>;
    Nombre total de parts libérées : <xsl:value-of select="translate(nbre_ps_lib,';','')"/>;
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@type"/></xsl:call-template>
Numéro client;Nom client;Nombre PS souscrites;Nombre PS libérées;Solde PS Souscrites;Solde PS libérées;Solde PS restant
<xsl:apply-templates select="client"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(nbre_ps,';','')"/>;<xsl:value-of select="translate(nbre_ps_lib,';','')"/>;<xsl:value-of select="translate(solde_ps_sous,';','')"/>;<xsl:value-of select="translate(solde_ps_lib,';','')"/>;<xsl:value-of select="translate(solde_ps_restant,';','')"/>;
</xsl:template>


<xsl:template match="detail_stat_jur_comp">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@type_comp"/></xsl:call-template>

Numéro client;Nom client;Valeur PS Souscrites;Valeur PS libérées;Valeur PS restant
<xsl:apply-templates select="client_comp"/>
</xsl:template>

<xsl:template match="client_comp">
	<xsl:value-of select="translate(id_client_comp,';','')"/>;<xsl:value-of select="translate(nom_comp,';','')"/>;<xsl:value-of select="translate(solde_ps_sous_comp,';','')"/>;<xsl:value-of select="translate(solde_ps_lib_comp,';','')"/>;<xsl:value-of select="translate(solde_ps_restant_comp,';','')"/>;
</xsl:template>

</xsl:stylesheet>
