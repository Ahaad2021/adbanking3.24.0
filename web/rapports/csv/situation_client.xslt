<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="situation_client">
  <xsl:apply-templates select="header"/>
  <xsl:apply-templates select="header_contextuel"/>
   <xsl:apply-templates select="epargnes"/>
	<xsl:apply-templates select="ord"/>
   <xsl:apply-templates select="garanties"/>
   <xsl:apply-templates select="credits"/>
 </xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails du client'"/></xsl:call-template>
	Numéro ; <xsl:value-of select="translate(num_client,';','')"/>;
	Nom ; <xsl:value-of select="translate(nom_client,';','')"/>;
	<xsl:if test="@stat_jur='1'">
	  Date de naissance ;  <xsl:value-of select="translate(pp_date_naissance,';','')"/>;
	  Lieu de naissance ;  <xsl:value-of select="translate(pp_lieu_naissance,';','')"/>
	</xsl:if>  
	  Statut juridique  ; <xsl:value-of select="translate(statut_juridique,';','')"/>;
	  Qualité           ; <xsl:value-of select="translate(qualite,';','')"/>;
	  Etat              ; <xsl:value-of select="translate(etat_client,';','')"/>;
	  Date d'adhésion   ; <xsl:value-of select="translate(date_adhesion,';','')"/>;
	  Gestionnaire      ; <xsl:value-of select="translate(gestionnaire,';','')"/>;
;
</xsl:template>

<xsl:template match="epargnes">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Situation épargne'"/></xsl:call-template>;
 Numéro compte; Intitulé;numéro client; Date ouverture;Produit épargne;Solde;Montant bloqué;Montant disponible;solde calcul interets;Dernier mouvement;
 <xsl:apply-templates select="situation_epargne"/>
;
</xsl:template>

<xsl:template match="situation_epargne">
	<xsl:value-of select="translate(num_complet_cpte,';','')"/>;<xsl:value-of select="translate(intitule_compte,';','')"/>;	<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(date_ouvert,';','')"/>;<xsl:value-of select="translate(prod_epargne,';','')"/>;<xsl:value-of select="translate(solde_cpte,';','')"/>;<xsl:value-of select="translate(mnt_bloq,';','')"/>;<xsl:value-of select="translate(mnt_disp,';','')"/>;<xsl:value-of select="translate(solde_calcul_interets,' ','')"/>;<xsl:value-of select="translate(date_dern_mvt,';','')"/>;
</xsl:template>

<xsl:template match="ord">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Situation ordre permanent'"/></xsl:call-template>;
	Numéro compte destinataire; Produit; Date ouverture; Cotisation/Mise; Périodicité; Date Fin; Solde Actuel;
	<xsl:apply-templates select="situation_ord"/>
	;
</xsl:template>

<xsl:template match="situation_ord">
	<xsl:value-of select="translate(num_cpte_ord,';','')"/>;<xsl:value-of select="translate(prod,';','')"/>;	<xsl:value-of select="translate(date_ouverture,';','')"/>;<xsl:value-of select="translate(montant,';','')"/>;<xsl:value-of select="translate(periodicite,';','')"/>;<xsl:value-of select="translate(date_fin,';','')"/>;<xsl:value-of select="translate(mnt_solde,';','')"/>;
</xsl:template>

<xsl:template match="garanties">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Situation garantie'"/></xsl:call-template>;
Numéro dossier;Numéro client;Nom client;Compte;Montant garanties;Montant crédit;Etat crédit;
 <xsl:apply-templates select="situation_garant"/>
;
</xsl:template>

<xsl:template match="credits">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Situation crédit'"/></xsl:call-template>;
 Numéro dossier;Produit;Numéro client;Date demande;Montant demandé;Montant octroyé;Echéances;Echéances remboursées;Etat;
 <xsl:apply-templates select="situation_credit"/>
;
</xsl:template>


<xsl:template match="situation_garant">
  	<xsl:value-of select="translate(id_doss,';','')"/>;	<xsl:value-of select="translate(id_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(num_cpte,';','')"/>;	<xsl:value-of select="translate(gar_num,';','')"/>;	<xsl:value-of select="translate(mnt,';','')"/>;	<xsl:value-of select="etat"/>;
</xsl:template>

<xsl:template match="situation_credit">
	<xsl:value-of select="translate(id_doss,';','')"/>;	<xsl:value-of select="translate(libel_prod,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(date_dem,';','')"/>;	<xsl:value-of select="translate(mnt_dem,';','')"/>;<xsl:if test="@exist_mnt_octr='1'"><xsl:value-of select="translate(cre_mnt_octr,';','')"/></xsl:if>;<xsl:value-of select="translate(nbre_ech,';','')"/>;<xsl:value-of select="translate(nbre_ech_remb,';','')"/>;<xsl:value-of select="translate(translate(etat,';',''),',',',')"/>;
</xsl:template>


</xsl:stylesheet>
