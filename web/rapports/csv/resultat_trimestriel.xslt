<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="resultat_trimestriel">
	    <xsl:apply-templates select="header"/>
		<xsl:apply-templates select="infos_globales"/>	;
		<xsl:apply-templates select="usagers"/>
		<xsl:apply-templates select="epargnants"/>
			<xsl:apply-templates select="credits_accordes"/>
			<xsl:apply-templates select="encoursepargnes"/>
			<xsl:apply-templates select="montant_credits_accordes"/>;
			 <xsl:apply-templates select="produit_exploitation"/>
			 <xsl:apply-templates select="charge_exploitation"/>;
</xsl:template>

<xsl:include href="lib.xslt"/>
<xsl:include href="header.xslt"/>

<xsl:template match="infos_globales">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations administratives'"/></xsl:call-template>
	Début de période ; <xsl:value-of select="translate(debut_periode,';','')"/>;
	Fin de période ; <xsl:value-of select="translate(fin_periode,';','')"/>;
</xsl:template>

<xsl:template match="usagers">
<xsl:call-template name="titre1"></xsl:call-template>
	;;;1er trimestre;2ème trimestre; 3ème trimestre; 4ème trimestre;
	NOMBRE ;Usagers (tous les membres et auxiliaires);Hommes;<xsl:value-of select="nbre_homme_t1"/>;<xsl:value-of select="nbre_homme_t2"/>;<xsl:value-of select="nbre_homme_t3"/>;<xsl:value-of select="nbre_homme_t4"/>;
	 ;;Femmes;<xsl:value-of select="nbre_femme_t1"/>;<xsl:value-of select="nbre_femme_t2"/>;<xsl:value-of select="nbre_femme_t3"/>;<xsl:value-of select="nbre_femme_t4"/>;
	 ;;Groupement Hommes;<xsl:value-of select="nbre_g_homme_t1"/>;<xsl:value-of select="nbre_g_homme_t2"/>;<xsl:value-of select="nbre_g_homme_t3"/>;<xsl:value-of select="nbre_g_homme_t4"/>;
	 ;;Groupement Femmes;<xsl:value-of select="nbre_g_femme_t1"/>;<xsl:value-of select="nbre_g_femme_t2"/>;<xsl:value-of select="nbre_g_femme_t3"/>;<xsl:value-of select="nbre_g_femme_t4"/>;
	 ;;Groupement Mixtes;<xsl:value-of select="nbre_g_mixte_t1"/>;<xsl:value-of select="nbre_g_mixte_t2"/>;<xsl:value-of select="nbre_g_mixte_t3"/>;<xsl:value-of select="nbre_g_mixte_t4"/>;
   ;;TOTAL;<xsl:value-of select="TOTAL_t1"/>;<xsl:value-of select="TOTAL_t2"/>;<xsl:value-of select="TOTAL_t3"/>;<xsl:value-of select="TOTAL_t4"/>;

</xsl:template>
<xsl:template match="epargnants">

	;;;1er trimestre;2ème trimestre; 3ème trimestre; 4ème trimestre;
	 ; Epargnants (membres ayant au moins un compte épargne);Hommes;<xsl:value-of select="nbre_homme_t1"/>;<xsl:value-of select="nbre_homme_t2"/>;<xsl:value-of select="nbre_homme_t3"/>;<xsl:value-of select="nbre_homme_t4"/>;
	 ;;Femmes;<xsl:value-of select="nbre_femme_t1"/>;<xsl:value-of select="nbre_femme_t2"/>;<xsl:value-of select="nbre_femme_t3"/>;<xsl:value-of select="nbre_femme_t4"/>;
	 ;;Groupement Hommes;<xsl:value-of select="nbre_g_homme_t1"/>;<xsl:value-of select="nbre_g_homme_t2"/>;<xsl:value-of select="nbre_g_homme_t3"/>;<xsl:value-of select="nbre_g_homme_t4"/>;
	 ;;Groupement Femmes;<xsl:value-of select="nbre_g_femme_t1"/>;<xsl:value-of select="nbre_g_femme_t2"/>;<xsl:value-of select="nbre_g_femme_t3"/>;<xsl:value-of select="nbre_g_femme_t4"/>;
	 ;;Groupement Mixtes;<xsl:value-of select="nbre_g_mixte_t1"/>;<xsl:value-of select="nbre_g_mixte_t2"/>;<xsl:value-of select="nbre_g_mixte_t3"/>;<xsl:value-of select="nbre_g_mixte_t4"/>;
	 ;;TOTAL;<xsl:value-of select="TOTAL_t1"/>;<xsl:value-of select="TOTAL_t2"/>;<xsl:value-of select="TOTAL_t3"/>;<xsl:value-of select="TOTAL_t4"/>;


</xsl:template>

<xsl:template match="credits_accordes">

	;;;1er trimestre;2ème trimestre; 3ème trimestre; 4ème trimestre;
	 ; Crédits accordés (cumul du 1er Janvier précédent à la date retenue);Hommes;<xsl:value-of select="nbre_homme_t1"/>;<xsl:value-of select="nbre_homme_t2"/>;<xsl:value-of select="nbre_homme_t3"/>;<xsl:value-of select="nbre_homme_t4"/>;
	 ;;Femmes;<xsl:value-of select="nbre_femme_t1"/>;<xsl:value-of select="nbre_femme_t2"/>;<xsl:value-of select="nbre_femme_t3"/>;<xsl:value-of select="nbre_femme_t4"/>;
	 ;;Groupement Hommes;<xsl:value-of select="nbre_g_homme_t1"/>;<xsl:value-of select="nbre_g_homme_t2"/>;<xsl:value-of select="nbre_g_homme_t3"/>;<xsl:value-of select="nbre_g_homme_t4"/>;
	 ;;Groupement Femmes;<xsl:value-of select="nbre_g_femme_t1"/>;<xsl:value-of select="nbre_g_femme_t2"/>;<xsl:value-of select="nbre_g_femme_t3"/>;<xsl:value-of select="nbre_g_femme_t4"/>;
	 ;;Groupement Mixtes;<xsl:value-of select="nbre_g_mixte_t1"/>;<xsl:value-of select="nbre_g_mixte_t2"/>;<xsl:value-of select="nbre_g_mixte_t3"/>;<xsl:value-of select="nbre_g_mixte_t4"/>;
   ;;TOTAL;<xsl:value-of select="TOTAL_t1"/>;<xsl:value-of select="TOTAL_t2"/>;<xsl:value-of select="TOTAL_t3"/>;<xsl:value-of select="TOTAL_t4"/>;


</xsl:template>
<xsl:template match="encoursepargnes">

	;;;1er trimestre;2ème trimestre; 3ème trimestre; 4ème trimestre;
	Montant  ;Encours Epargne (compilation des fiches d'épargne);Hommes;<xsl:value-of select="montant_homme_t1"/>;<xsl:value-of select="montant_homme_t2"/>;<xsl:value-of select="montant_homme_t3"/>;<xsl:value-of select="montant_homme_t4"/>;
					 ;;Femmes;<xsl:value-of select="montant_femme_t1"/>;<xsl:value-of select="montant_femme_t2"/>;<xsl:value-of select="montant_femme_t3"/>;<xsl:value-of select="montant_femme_t4"/>;
					 ;;Groupement Hommes;<xsl:value-of select="montant_g_homme_t1"/>;<xsl:value-of select="montant_g_homme_t2"/>;<xsl:value-of select="montant_g_homme_t3"/>;<xsl:value-of select="montant_g_homme_t4"/>;
					 ;;Groupement Femmes;<xsl:value-of select="montant_g_femme_t1"/>;<xsl:value-of select="montant_g_femme_t2"/>;<xsl:value-of select="montant_g_femme_t3"/>;<xsl:value-of select="montant_g_femme_t4"/>;
					 ;;Groupement Mixtes;<xsl:value-of select="montant_g_mixte_t1"/>;<xsl:value-of select="montant_g_mixte_t2"/>;<xsl:value-of select="montant_g_mixte_t3"/>;<xsl:value-of select="montant_g_mixte_t4"/>;
	         ;;TOTAL;<xsl:value-of select="TOTAL_t1"/>;<xsl:value-of select="TOTAL_t2"/>;<xsl:value-of select="TOTAL_t3"/>;<xsl:value-of select="TOTAL_t4"/>;

</xsl:template>
<xsl:template match="montant_credits_accordes">

	;;;1er trimestre;2ème trimestre; 3ème trimestre; 4ème trimestre;
	        ;Crédits  accordés (dans l'année);Hommes;<xsl:value-of select="montant_homme_t1"/>;<xsl:value-of select="montant_homme_t2"/>;<xsl:value-of select="montant_homme_t3"/>;<xsl:value-of select="montant_homme_t4"/>;
	        ;;Femmes;<xsl:value-of select="montant_femme_t1"/>;<xsl:value-of select="montant_femme_t2"/>;<xsl:value-of select="montant_femme_t3"/>;<xsl:value-of select="montant_femme_t4"/>;
	        ;;Groupement Hommes;<xsl:value-of select="montant_g_homme_t1"/>;<xsl:value-of select="montant_g_homme_t2"/>;<xsl:value-of select="montant_g_homme_t3"/>;<xsl:value-of select="montant_g_homme_t4"/>;
	        ;;Groupement Femmes;<xsl:value-of select="montant_g_femme_t1"/>;<xsl:value-of select="montant_g_femme_t2"/>;<xsl:value-of select="montant_g_femme_t3"/>;<xsl:value-of select="montant_g_femme_t4"/>;
	        ;;Groupement Mixtes;<xsl:value-of select="montant_g_mixte_t1"/>;<xsl:value-of select="montant_g_mixte_t2"/>;<xsl:value-of select="montant_g_mixte_t3"/>;<xsl:value-of select="montant_g_mixte_t4"/>;
	        ;;TOTAL;<xsl:value-of select="TOTAL_t1"/>;<xsl:value-of select="TOTAL_t2"/>;<xsl:value-of select="TOTAL_t3"/>;<xsl:value-of select="TOTAL_t4"/>;


</xsl:template>
<xsl:template match="produit_exploitation">
	Produits d'exploitation;1er trimestre;2ème trimestre; 3ème trimestre; 4ème trimestre;
<xsl:apply-templates select="compteProduit"/>

</xsl:template>
<xsl:template match="charge_exploitation">
Charges d'exploitation;;;; ;
<xsl:apply-templates select="compteCharge"/>

</xsl:template>


<xsl:template match="compteProduit">


  <xsl:value-of select="libel_produit"/>;<xsl:value-of select="solde_produit_t1"/>;<xsl:value-of select="solde_produit_t2"/>;<xsl:value-of select="solde_produit_t3"/>;<xsl:value-of select="solde_produit_t4"/>;


</xsl:template>
<xsl:template match="compteCharge">


  <xsl:value-of select="libel_charge"/>;<xsl:value-of select="solde_charge_t1"/>;<xsl:value-of select="solde_charge_t2"/>;<xsl:value-of select="solde_charge_t3"/>;<xsl:value-of select="solde_charge_t4"/>;


</xsl:template>


</xsl:stylesheet>
