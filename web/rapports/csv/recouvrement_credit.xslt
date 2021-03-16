<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
       
<xsl:template match="recouvrement_credit">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="infos_synthetique"/>
<xsl:apply-templates select="recap_par_classe"/>
<xsl:apply-templates select="details_recouvrement"/>
</xsl:template>    
    
<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>   
   
 <!-- Start : infos_synthetique -->   
<xsl:template match="infos_synthetique">
	Total capital restant dû à la fin de période;<xsl:value-of select="translate(cap_restant_tot,';','')"/>;
	Total capital attendu pour la periode;<xsl:value-of select="translate(capital_attendu_total,';','')"/>;
	Total capital remboursé pour la periode;<xsl:value-of select="translate(capital_rembourse_total,';','')"/>;
	Total capital impayé pour la periode;<xsl:value-of select="translate(capital_impaye_total,';','')"/>;
	Total Intérêts attendus pour la periode;<xsl:value-of select="translate(interet_attendu_total,';','')"/>;
	Total Intérêts remboursés pour la periode;<xsl:value-of select="translate(interet_rembourse_total,';','')"/>;
	Total Intérêts impayés pour la periode;<xsl:value-of select="translate(interet_impaye_total,';','')"/>;
	Total Penalités remboursées pour la periode;<xsl:value-of select="translate(penalite_rembourse_total,';','')"/>;
	Total Penalités impayées pour la periode;<xsl:value-of select="translate(penalite_impaye_total,';','')"/>;
	Total Montant remboursé pour la periode;<xsl:value-of select="translate(total_rembourse_total,';','')"/>;
	Total Montant impayé pour la periode;<xsl:value-of select="translate(montant_tot,';','')"/>;
Total coefficient de recouvrement;<xsl:value-of select="translate(coeff_tot,';','')"/>;
;
</xsl:template>

 <!-- Start : recap_par_classe -->
<xsl:template match="recap_par_classe">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="entete_recap"/></xsl:call-template>
	<xsl:apply-templates select="details_recap"/>
</xsl:template>

 <!-- Start : details_recap -->
<xsl:template match="details_recap">
	Etat;Capital restant dû à la fin de période;Capital attendu pour la periode;Capital remboursé pour la periode;Capital impayé pour la periode;Intérêts attendus pour la periode;Intérêts remboursés pour la periode;Intérêts impayés pour la periode;Penalités remboursées pour la periode;Penalités impayées pour la periode;Total Montant remboursé pour la periode;Total Montant impayé pour la periode;Coefficient de recouvrement;
<xsl:for-each select="ligne_recap">
<xsl:value-of select="translate(etat,';','')"/>;<xsl:value-of select="translate(cap_restant_recap,';','')"/>;<xsl:value-of select="translate(capital_attendu_recap,';','')"/>;<xsl:value-of select="translate(capital_rembourse_recap,';','')"/>;<xsl:value-of select="translate(capital_impaye_recap,';','')"/>;<xsl:value-of select="translate(interet_attendu_recap,';','')"/>;<xsl:value-of select="translate(interet_rembourse_recap,';','')"/>;<xsl:value-of select="translate(interet_impaye_recap,';','')"/>;<xsl:value-of select="translate(penalite_rembourse_recap,';','')"/>;<xsl:value-of select="translate(penalite_impaye_recap,';','')"/>;<xsl:value-of select="translate(total_rembourse_recap,';','')"/>;<xsl:value-of select="translate(montant_recap,';','')"/>;<xsl:value-of select="translate(coeff_recap,';','')"/>;
;	
</xsl:for-each> 
</xsl:template>
  
 <!-- Start : details des recouvrements par dossiers -->
<xsl:template match="details_recouvrement">
	<xsl:for-each select="recouvrements_par_classe">
		<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_detail"/></xsl:call-template>;
		<xsl:call-template name="titre1"><xsl:with-param name="titre" select="classe_credit"/></xsl:call-template>;
		<xsl:for-each select="recouvrements_par_produits">;
			<xsl:call-template name="titre1"><xsl:with-param name="titre" select="libel_prod"/></xsl:call-template>
			<xsl:apply-templates select="dossiers_recouvrement"/>
			Total;<xsl:value-of select="'#'"/>;<xsl:value-of select="'#'"/>;<xsl:value-of select="'#'"/>;<xsl:value-of select="'#'"/>;<xsl:value-of select="translate(cap_restant_tot,';','')"/>;<xsl:value-of select="translate(capital_attendu_tot , ';','')"/>;<xsl:value-of select="translate(capital_rembourse_tot , ';','')"/>;<xsl:value-of select="translate(interet_attendu_tot , ';','')"/>;<xsl:value-of select="translate(interet_rembourse_tot , ';','')"/>;<xsl:value-of select="translate(penalite_rembourse_tot , ';','')"/>;<xsl:value-of select="translate(penalite_impaye_tot , ';','')"/>;<xsl:value-of select="translate(total_rembourse_tot , ';','')"/>;<xsl:value-of select="translate(montant_retard_tot , ';','')"/>;<xsl:value-of select="'#'"/>;
		</xsl:for-each>
	</xsl:for-each>
</xsl:template>  
 
<!-- Start : dossiers_recouvrement -->
<xsl:template match="dossiers_recouvrement">
	Num prêt;Etat;Num client;Nom client;Gestionnaire;Capital restant dû à la fin de période;Capital attendu pour la periode;Capital remboursé pour la periode;Intérêts attendus pour la periode;Intérêts remboursés pour la periode;Penalités remboursées pour la periode;Penalités impayées pour la periode;Total Montant remboursé pour la periode;Total Montant impayé pour la periode;Coefficient de recouvrement;
	<xsl:for-each select="ligne_recouvrement">
		<xsl:value-of select="translate(num_pret,';','')"/>;<xsl:value-of select="translate(etat_credit,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(gestionnaire,';','')"/>;<xsl:value-of select="translate(cap_restant,';','')"/>;<xsl:value-of select="translate(capital_attendu , ';','')"/>;<xsl:value-of select="translate(capital_rembourse , ';','')"/>;<xsl:value-of select="translate(interet_attendu , ';','')"/>;<xsl:value-of select="translate(interet_rembourse , ';','')"/>;<xsl:value-of select="translate(penalite_rembourse , ';','')"/>;<xsl:value-of select="translate(penalite_impaye , ';','')"/>;<xsl:value-of select="translate(total_rembourse , ';','')"/>;<xsl:value-of select="translate(montant_retard,';','')"/>;<xsl:value-of select="translate(coeff,';','')"/>;
	</xsl:for-each>
</xsl:template>
    
</xsl:stylesheet>
