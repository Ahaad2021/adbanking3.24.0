<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="stat_agence">
  <xsl:apply-templates select="header"/>
  <xsl:apply-templates select="infos_globales"/>
  <xsl:apply-templates select="ratio_prud"/>
  <xsl:apply-templates select="indic_perf"/>
  <xsl:apply-templates select="couverture"/>
  <xsl:apply-templates select="indic_productivite"/>
  <xsl:apply-templates select="indic_impact"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="infos_globales">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations administratives'"/></xsl:call-template>
	Début de période ; <xsl:value-of select="translate(debut_periode,';','')"/>;
	Fin de période ; <xsl:value-of select="translate(fin_periode,';','')"/>;
	Responsable ; <xsl:value-of select="translate(responsable,';','')"/>;
	Type structure ; <xsl:value-of select="translate(type_structure,';','')"/>;
	Date agrément ; <xsl:value-of select="translate(date_agrement,';','')"/>;
	N° agrément ; <xsl:value-of select="translate(num_agrement,';','')"/>;
;
</xsl:template>

<xsl:template match="ratio_prud">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Ratios prudentiels (%)'"/></xsl:call-template>
	;Limitation des prêts aux dirigeants ;Limitation du risque pris sur un seul membre ;Taux de transformation de l'épargne en crédit ;
	; <xsl:value-of select="translate(limit_prets_dirig,';','')"/>; <xsl:value-of select="translate(limit_risk_membre,';','')"/>; <xsl:value-of select="translate(tx_transform,';','')"/>;
;
</xsl:template>

<xsl:template match="indic_perf">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Qualité du portefeuille (%)'"/></xsl:call-template>
	;Portefeuille à risque sur 1 échéance ;Portefeuille à risque sur 2 échéances ;Portefeuille à risque sur 3 échéances ;Portefeuille à risque à 30 jours ;Provisions pour créances douteuses ;Taux de rééchelonnement des prêts ;Taux d'abandon de créances ;
	;<xsl:value-of select="translate(risk_1_ech,';','')"/>;<xsl:value-of select="translate(risk_2_ech,';','')"/>; <xsl:value-of select="translate(risk_3_ech,';','')"/>; <xsl:value-of select="translate(risk_30j_ech,';','')"/>; <xsl:value-of select="translate(tx_provisions,';','')"/>; <xsl:value-of select="translate(tx_reech,';','')"/>;<xsl:value-of select="translate(tx_perte,';','')"/>;
;
</xsl:template>

<xsl:template match="couverture">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Couverture'"/></xsl:call-template>
	Nombre de crédits en cours ; <xsl:value-of select="translate(nbr_credits,';','')"/>;
	En-cours total de crédit ; <xsl:value-of select="translate(portefeuille,';','')"/>;
	Nombre de comptes d'épargne actifs ; <xsl:value-of select="translate(nbre_cpt_epargne,';','')"/>;
	En-cours total de l'épargne ; <xsl:value-of select="translate(total_epargne,';','')"/>;
	Taux de renouvellement de crédits (%) ; <xsl:value-of select="translate(tx_renouvellement_credits,';','')"/>;
	Montant moyen des premiers crédits ; <xsl:value-of select="translate(first_credit_moyen,';','')"/>;
	Montant médian des premiers crédits ; <xsl:value-of select="translate(first_credit_median,';','')"/>;
	Montant moyen des crédits ; <xsl:value-of select="translate(credit_moyen,';','')"/>;
	Montant médian des crédits ; <xsl:value-of select="translate(credit_median,';','')"/>;
	Solde moyen des comptes d'épargne ; <xsl:value-of select="translate(epargne_moyen_cpte,';','')"/>;
	Solde médian des comtpes d'épargne ; <xsl:value-of select="translate(epargne_median_cpte,';','')"/>;
	Volumes moyen d'épargne des clients ;<xsl:value-of select="translate(epargne_moyen_client,';','')"/>;
	Volume médian d'épargne des clients ; <xsl:value-of select="translate(epargne_median_client,';','')"/>;
;
</xsl:template>



<xsl:template match="indic_productivite">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Productivité'"/></xsl:call-template>
	;Rendement du portefeuille (%) ;Rendement théorique du portefeuille (%) ;Ecart de rendement (%) ;
	; <xsl:value-of select="translate(rendement_portefeuille,';','')"/>;<xsl:value-of select="translate(rendement_theorique,';','')"/>;<xsl:value-of select="translate(ecart_rendement,';','')"/>;     
;
</xsl:template>

<xsl:template match="indic_impact">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Indicateur Impact'"/></xsl:call-template>        
        Statut juridique;Epargnants ; Emprunteurs ; Total
        Personnes physiques;<xsl:value-of select="translate(epargnants/nbre_individus/nbre_pp,';','')"/>
             <xsl:value-of select="';'"/>
         <xsl:value-of select="translate(emprunteurs/nbre_individus/nbre_pp,';','')"/>
            <xsl:value-of select="';'"/>
         <xsl:value-of select="translate(clients/nbre_individus/nbre_pp,';','')"/> 
           <xsl:value-of select="';'"/>&#160;
        (%) Hommes;<xsl:value-of select="translate(epargnants/nbre_individus/prc_homme,';','')"/>
           <xsl:value-of select="'%;'"/>
         <xsl:value-of select="translate(emprunteurs/nbre_individus/prc_homme,';','')"/>
          <xsl:value-of select="'%;'"/>
         <xsl:value-of select="translate(clients/nbre_individus/prc_homme,';','')"/>
          <xsl:value-of select="'%;'"/>&#160;
        (%) Femmes; <xsl:value-of select="translate(epargnants/nbre_individus/prc_femme,';','')"/>
          <xsl:value-of select="'%;'"/>
        <xsl:value-of select="translate(emprunteurs/nbre_individus/prc_femme,';','')"/>
 	      <xsl:value-of select="'%;'"/>
        <xsl:value-of select="translate(clients/nbre_individus/prc_femme,';','')"/>
	      <xsl:value-of select="'%;'"/>&#160;
        Personnes morales; <xsl:value-of select="translate(epargnants/nbre_individus/nbre_pm,';','')"/>
 	       <xsl:value-of select="';'"/>
        <xsl:value-of select="translate(emprunteurs/nbre_individus/nbre_pm,';','')"/>
 	      <xsl:value-of select="';'"/>
        <xsl:value-of select="translate(clients/nbre_individus/nbre_pm,';','')"/>
 	      <xsl:value-of select="';'"/>&#160;
        Groupes Informels; <xsl:value-of select="translate(epargnants/nbre_individus/nbre_gi,';','')"/>
 	      <xsl:value-of select="';'"/>
        <xsl:value-of select="translate(emprunteurs/nbre_individus/nbre_gi,';','')"/>
 	      <xsl:value-of select="';'"/>
        <xsl:value-of select="translate(clients/nbre_individus/nbre_gi,';','')"/>
 	      <xsl:value-of select="';'"/>&#160;
        Groupes solidaires ;<xsl:value-of select="translate(epargnants/nbre_individus/nbre_gs,';','')"/>
	      <xsl:value-of select="';'"/>
        <xsl:value-of select="translate(emprunteurs/nbre_individus/nbre_gs,';','')"/>
 	      <xsl:value-of select="';'"/>
        <xsl:value-of select="translate(clients/nbre_individus/nbre_gs,';','')"/>
 	     <xsl:value-of select="';'"/>        
  </xsl:template>

</xsl:stylesheet>
