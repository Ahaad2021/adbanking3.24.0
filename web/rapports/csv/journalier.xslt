<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>


<xsl:template match="journalier">
     <xsl:apply-templates select="header"/>
     <xsl:apply-templates select="adhesions"/>
     <xsl:apply-templates select="defections"/>
     <xsl:apply-templates select="ouvertures"/>
     <xsl:apply-templates select="clotures"/>
     <xsl:apply-templates select="dat_prolonges"/>
     <xsl:apply-templates select="dat_non_prolonges"/>
     <xsl:apply-templates select="dossiers_credit"/>
     <xsl:apply-templates select="dcr_approuves"/>
     <xsl:apply-templates select="dcr_rejetes"/>
     <xsl:apply-templates select="dcr_annules"/>
     <xsl:apply-templates select="dcr_debourses"/>
     <xsl:apply-templates select="dcr_repris"/>
     <xsl:apply-templates select="ps_repris"/>
     <xsl:apply-templates select="comptes_ajustes"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>
<xsl:template match="adhesions">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Adhésions du jour : ', @nombre)"/></xsl:call-template>        ;
	<xsl:if test="@nombre > 0">
	;Numero client; Nom client; Statut juridique ;Secteur activité; Gestionnaire;
		<xsl:apply-templates select="detail_adhesion"/>
	</xsl:if>
</xsl:template>

<xsl:template match="detail_adhesion">
  ;<xsl:value-of select="translate(id_client,';','')"/> ; <xsl:value-of select="translate(nom_client,';','')"/> ; <xsl:value-of select="translate(stat_jur,';','')"/>; <xsl:value-of select="translate(sect_act,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;
;
</xsl:template>

<xsl:template match="defections">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Défections du jour : ', @nombre)"/></xsl:call-template>       ;
	<xsl:if test="@nombre > 0">
	;Numero client; Nom client; Statut juridique ;Secteur activité; Gestionnaire; Date adhesion; Raison défection;
	    <xsl:apply-templates select="detail_defection"/>
	</xsl:if>
</xsl:template>

<xsl:template match="detail_defection">
   ;<xsl:value-of select="translate(id_client,';','')"/>; <xsl:value-of select="translate(nom_client,';','')"/>; <xsl:value-of select="translate(stat_jur,';','')"/>; <xsl:value-of select="translate(sect_act,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;<xsl:value-of select="translate(date_adh,';','')"/>; <xsl:value-of select="translate(raison_defection,';','')"/>;
;
</xsl:template>

<xsl:template match="ouvertures">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Ouvertures de comptes du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	; Numéro compte; Numéro client; Nom client; Produit épargne; Solde;
		<xsl:apply-templates select="detail_ouverture"/>
	</xsl:if>
</xsl:template>

<xsl:template match="detail_ouverture">
     ;<xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>; <xsl:value-of select="translate(nom_client,';','')"/>; <xsl:value-of select="translate(produit_epargne,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;
;
</xsl:template>

<xsl:template match="clotures">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Clôtures de comptes du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	; Numéro compte; Numéro client; Nom client; Produit épargne; Date ouverture; Raison cloture;Solde;
	    <xsl:apply-templates select="detail_cloture"/>
	</xsl:if>
</xsl:template>

<xsl:template match="detail_cloture">
     ;<xsl:value-of select="translate(num_cpte,';','')"/>; <xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>; <xsl:value-of select="translate(produit_epargne,';','')"/>; <xsl:value-of select="translate(date_ouverture,';','')"/>; <xsl:value-of select="translate(raison_cloture,';','')"/>; <xsl:value-of select="translate(solde,';','')"/>;
;
</xsl:template>

<xsl:template match="dat_prolonges">
       	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Comptes à terme prolongés dans la journée : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	; Numéro compte; Numéro client; Nom client; Produit épargne; Solde; Terme initial; Intérets; prochain terme;
	     <xsl:apply-templates select="detail_dat_prolonge"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="detail_dat_prolonge">
    ; <xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(produit_epargne,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;<xsl:value-of select="translate(terme_initial,';','')"/>;	<xsl:value-of select="translate(interets,';','')"/>;<xsl:value-of select="translate(prochain_terme,';','')"/>;
;
</xsl:template>

<xsl:template match="dat_non_prolonges">
       	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Comptes à terme non-prolongés dans la journée : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	; Numéro compte; Numéro client; Nom client; Produit épargne; Solde; Terme ; Intérets; Nombre de jours;
	     <xsl:apply-templates select="detail_dat_non_prolonge"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="detail_dat_non_prolonge">
	;<xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(produit_epargne,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;<xsl:value-of select="translate(terme,';','')"/>;<xsl:value-of select="translate(interets,';','')"/>;<xsl:value-of select="translate(nbre_jours,';','')"/>;
;
</xsl:template>
<xsl:template match="dossiers_credit">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Demandes de crédit du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	
	    <xsl:apply-templates select="detail_dossier_credit_sans_mnt_octr"/>
	</xsl:if>	
</xsl:template>
<xsl:template match="dcr_approuves">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Crédits approuvés du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	    <xsl:apply-templates select="detail_dossier_credit_avec_mnt_octr"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="dcr_rejetes">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Crédits rejetés du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	   <xsl:apply-templates select="detail_dossier_credit_rejete"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="dcr_annules">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Crédits annulés du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
		<xsl:apply-templates select="detail_dossier_credit_rejete"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="dcr_debourses">
       	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Crédits déboursés du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
	   <xsl:apply-templates select="detail_dossier_credit_avec_mnt_octr"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="detail_dossier_credit_sans_mnt_octr">
    
    ;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(produit_credit,';','')"/>;<xsl:value-of select="translate(montant_demande,';','')"/>;<xsl:value-of select="translate(duree,';','')"/>;<xsl:value-of select="translate(objet_dem,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;
;
</xsl:template>


<xsl:template match="detail_dossier_credit_avec_mnt_octr">
     ;Numéro dossier; Numéro client;Nom client; produit de crédit; Montant demandé; Montant octroyé; Durée; Objet demande; Gestionnaire;
    ;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(produit_credit,';','')"/>;<xsl:value-of select="translate(montant_demande,';','')"/>;<xsl:value-of select="translate(montant_octroye,';','')"/>;<xsl:value-of select="translate(duree,';','')"/>;<xsl:value-of select="translate(objet_dem,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;
;
</xsl:template>

<xsl:template match="detail_dossier_credit_rejete">
     ;Numéro dossier; Numéro client;Nom client; produit de crédit; Montant demandé; Durée; Objet demande; Gestionnaire;Motif;
    ;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(produit_credit,';','')"/>;<xsl:value-of select="translate(montant_demande,';','')"/>;<xsl:value-of select="translate(duree,';','')"/>;<xsl:value-of select="translate(objet_dem,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;<xsl:value-of select="translate(motif,';','')"/>;
;
</xsl:template>



<xsl:template match="dcr_repris">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Crédits repris du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
		<xsl:apply-templates select="detail_crd_repris"/>
                      
	</xsl:if>
</xsl:template>

<xsl:template match="detail_crd_repris">
     ;Numéro dossier; Numéro client;Nom client; Etat du crédit; Libellé produit;Montant octroyé;Capital remboursé; Interet remboursement;Pénalités de remboursement;Capital restant; Intérets restants; Penalités restants;
    ;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(libel_prod,';','')"/>;<xsl:value-of select="translate(mnt_octr,';','')"/>;<xsl:value-of select="translate(cap_remb,';','')"/>;<xsl:value-of select="translate(int_remb,';','')"/>;<xsl:value-of select="translate(pen_remb,';','')"/>;<xsl:value-of select="translate(cap_restant,';','')"/>;<xsl:value-of select="translate(int_restant,';','')"/>;<xsl:value-of select="translate(pen_restant,';','')"/>;
;
</xsl:template>


<xsl:template match="ps_repris">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Parts sociales reprises du jour : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
		<xsl:apply-templates select="detail_ps_repris"/>
		
	</xsl:if>
</xsl:template>

<xsl:template match="detail_ps_repris">
     ;Numéro client;Nom client; Secteur activité;Nombre de parts sociales; Gestionnaire;
    ;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>; <xsl:value-of select="translate(sect_act,';','')"/>; <xsl:value-of select="translate(nbre_parts,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;
;
</xsl:template>



      	
<xsl:template match="comptes_ajustes">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Ajustements de soldes de comptes clients : ', @nombre)"/></xsl:call-template>
        ;
	<xsl:if test="@nombre > 0">
		<xsl:apply-templates select="detail_compte_ajuste"/>
	</xsl:if>	
</xsl:template>

<xsl:template match="detail_compte_ajuste">
    ;login; heure; Numéro client; Nom client; Numéro compte; Ancien compte; Nouveau compte;
    ;<xsl:value-of select="translate(login,';','')"/>;<xsl:value-of select="translate(heure,';','')"/>;	<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(num_cpte,';','')"/>; <xsl:value-of select="translate(anc_solde,';','')"/>;<xsl:value-of select="translate(nouv_solde,';','')"/>;
;
</xsl:template>

</xsl:stylesheet>

