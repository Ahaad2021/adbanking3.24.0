<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="compte_epargne">
  <xsl:apply-templates select="header"/>  
  <xsl:apply-templates select="header_contextuel"/>
 

  <xsl:apply-templates select="mouvement"/>
</xsl:template>

<xsl:template match="header_contextuel">

    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:apply-templates select="infos_synthetiques"/>
    ;Date mouvement;Numéro transaction;Opération;Crédit;Débit;Nombre de jours inactifs;Solde<xsl:if test="isset_ps_csv">;Nb PS mvntés </xsl:if>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>


<xsl:template match="infos_synthetiques">
Produit d'épargne ;<xsl:value-of select="translate(produit,';','')"/>;
Taux d'intérêt ;<xsl:value-of select="translate(taux_int,';','')"/>;
Date d'ouverture ;<xsl:value-of select="translate(date_ouverture,';','')"/>;
Solde ;<xsl:value-of select="translate(substring(solde,1,string-length(solde)-3),';','')"/>;
Montant bloqué ;<xsl:value-of select="translate(substring(mnt_bloq,1,string-length(mnt_bloq)-3),';','')"/>;
Montant minimum ;<xsl:value-of select="translate(substring(mnt_min,1,string-length(mnt_min)-3),';','')"/>;
Solde disponible ;<xsl:value-of select="translate(substring(solde_disp,1,string-length(solde_disp)-3),';','')"/>;
Base pour le calcul des intérêts ;<xsl:value-of select="translate(substring(solde_min,1,string-length(solde_min)-3),';','')"/>;
<xsl:if test="ps_souscrites">
Nombre de PS souscrites ;<xsl:value-of select="translate(ps_souscrites,';','')"/>;
</xsl:if>
<xsl:if test="ps_lib">
Nombre de PS libérées ;<xsl:value-of select="translate(ps_lib,';','')"/>;
</xsl:if>
Devise du compte ;<xsl:value-of select="translate(devise,';','')"/>;
		
</xsl:template>

<xsl:template match="mouvement">    
	;<xsl:value-of select="translate(date_mouv,';','')"/>;<xsl:value-of select="translate(num_trans,';','')"/>;<xsl:value-of select="translate(libel_ope,';','')"/>;<xsl:value-of select="translate(mnt_depot,';','')"/>;<xsl:value-of select="translate(mnt_retrait,';','')"/>;<xsl:value-of select="translate(nbre_jour_inactivite,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>
	<xsl:if test="nbre_ps_mouvementer">
	;<xsl:value-of select="translate(nbre_ps_mouvementer,';','')"/>
	 </xsl:if>
</xsl:template>

</xsl:stylesheet>
