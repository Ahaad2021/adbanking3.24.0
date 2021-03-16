<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="activite_agence">
	    <xsl:apply-templates select="header"/>
		<xsl:apply-templates select="infos_globales"/>	;	
		<xsl:apply-templates select="clients"/>;		
		<xsl:apply-templates select="credits"/>;		
		<xsl:apply-templates select="epargnes"/>;
</xsl:template>

<xsl:include href="lib.xslt"/>
<xsl:include href="header.xslt"/>

<xsl:template match="infos_globales">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations administratives'"/></xsl:call-template>
	Début de période ; <xsl:value-of select="translate(debut_periode,';','')"/>;
	Fin de période ; <xsl:value-of select="translate(fin_periode,';','')"/>;
</xsl:template>

<xsl:template match="clients">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Evolution des membres'"/></xsl:call-template>
	;Membres; Hommes; Femmes; Personnes morales; Total;
	Date début: <xsl:value-of select="date_deb"/>;<xsl:value-of select="nbre_hom_deb"/>;<xsl:value-of select="nbre_fem_deb"/>;<xsl:value-of select="nbre_pm_deb"/>;<xsl:value-of select="total_client_deb"/>;
	Date fin : <xsl:value-of select="date_fin"/>;<xsl:value-of select="nbre_hom_fin"/>;<xsl:value-of select="nbre_fem_fin"/>;<xsl:value-of select="nbre_pm_fin"/>;<xsl:value-of select="total_client_fin"/>;
	Accroissement; <xsl:value-of select="diff_hom"/>;<xsl:value-of select="diff_fem"/>;<xsl:value-of select="diff_pm"/>;<xsl:value-of select="diff_tot"/>;
	Pourcentage; <xsl:value-of select="prc_hom"/>;<xsl:value-of select="prc_fem"/>;<xsl:value-of select="prc_pm"/>;<xsl:value-of select="prc_tot"/>;
</xsl:template>

<xsl:template match="credits">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Evolution du crédit'"/></xsl:call-template>
	Mois;Nombre;Montant;
	Janvier ;<xsl:value-of select="cred_jan"/>;<xsl:value-of select="mnt_jan"/>;
	Février ;<xsl:value-of select="cred_fev"/>;<xsl:value-of select="mnt_fev"/>;
    Mars ;<xsl:value-of select="cred_mars"/>;<xsl:value-of select="mnt_mars"/>;
    Avril ;<xsl:value-of select="cred_av"/>;<xsl:value-of select="mnt_av"/>;
    Mai ;<xsl:value-of select="cred_mai"/>;<xsl:value-of select="mnt_mai"/>;
    Juin ;<xsl:value-of select="cred_juin"/>;<xsl:value-of select="mnt_juin"/>;
    Juillet ;<xsl:value-of select="cred_jui"/>;<xsl:value-of select="mnt_juin"/>;
    Aout ;<xsl:value-of select="cred_aout"/>;<xsl:value-of select="mnt_aout"/>;
    Septembre ;<xsl:value-of select="cred_sept"/>;<xsl:value-of select="mnt_sept"/>;
    Octobre ;<xsl:value-of select="cred_oc"/>;<xsl:value-of select="mnt_oc"/>;
    Novembre ;<xsl:value-of select="cred_nov"/>;<xsl:value-of select="mnt_nov"/>;
Décembre ;<xsl:value-of select="cred_dec"/>;<xsl:value-of select="mnt_dec"/>;
Total ;<xsl:value-of select="cred_tot"/>;<xsl:value-of select="mnt_tot"/>;
</xsl:template>

<xsl:template match="epargnes">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Evolution de epargne'"/></xsl:call-template>
	Mois; Epargne collectée; Encours épargne;
	Janvier ; <xsl:value-of select="epargne_jan"/>;<xsl:value-of select="encour_jan"/>;
	Février ;<xsl:value-of select="epargne_fev"/>;<xsl:value-of select="encour_fev"/>;
    Mars ;<xsl:value-of select="epargne_mars"/>;<xsl:value-of select="encour_mars"/>;
    Avril ;<xsl:value-of select="epargne_av"/>;<xsl:value-of select="encour_av"/>;
    Mai ;<xsl:value-of select="epargne_mai"/>;<xsl:value-of select="encour_mai"/>;
    Juin ;<xsl:value-of select="epargne_juin"/>;<xsl:value-of select="encour_juin"/>;
    Juillet ;<xsl:value-of select="epargne_jui"/>;<xsl:value-of select="encour_juin"/>;
    Aout ;<xsl:value-of select="epargne_aout"/>;<xsl:value-of select="encour_aout"/>;
    Septembre ;<xsl:value-of select="epargne_sept"/>;<xsl:value-of select="encour_sept"/>;
    Octobre ;<xsl:value-of select="epargne_oc"/>;<xsl:value-of select="encour_oc"/>;
    Novembre ;<xsl:value-of select="epargne_nov"/>;<xsl:value-of select="encour_nov"/>;
Décembre ;<xsl:value-of select="epargne_dec"/>;<xsl:value-of select="encour_dec"/>;
Total ;<xsl:value-of select="epargne_tot"/>;<xsl:value-of select="encour_tot"/>;
</xsl:template>

</xsl:stylesheet>
