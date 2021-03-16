<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="extrait_cpte_netbank">
	<xsl:apply-templates select="header"/>
	<xsl:apply-templates select="details"/>			
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="details">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations détaillées'"/></xsl:call-template>;
 ;
 id_extrait_cpte; id_his; id_cpte; num_complet_cpte; intitule_compte; num_client; nom_client; montant; devise; date_exec; date_valeur; libel_operation; communication; eft_id_extrait; eft_id_mvt; eft_id_client; eft_annee_oper; eft_dern_solde; eft_nouv_solde; eft_dern_date; eft_sceau; taux; mnt_frais; mnt_comm_change; cptie_mnt; cptie_devise; cptie_num_cpte; cptie_nom; cptie_adresse; cptie_cp; cptie_ville; cptie_pays; information;
<xsl:apply-templates select="client"/>
</xsl:template>

<xsl:template match="client">
 <xsl:value-of select="translate(id_extrait_cpte,';','')"/>;<xsl:value-of select="translate(id_his,';','')"/>;<xsl:value-of select="translate(id_cpte,';','')"/>;<xsl:value-of select="translate(translate(num_complet_cpte,';',''),'-','')"/>;<xsl:value-of select="translate(intitule_compte,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(montant,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(date_exec,';','')"/>;<xsl:value-of select="translate(date_valeur,';','')"/>;<xsl:value-of select="translate(libel_operation,';','')"/>;<xsl:value-of select="translate(communication,';','')"/>;<xsl:value-of select="translate(eft_id_extrait,';','')"/>;<xsl:value-of select="translate(eft_id_mvt,';','')"/>;<xsl:value-of select="translate(eft_id_client,';','')"/>;<xsl:value-of select="translate(eft_annee_oper,';','')"/>;<xsl:value-of select="translate(eft_dern_solde,';','')"/>;<xsl:value-of select="translate(eft_nouv_solde,';','')"/>;<xsl:value-of select="translate(eft_dern_date,';','')"/>;<xsl:value-of select="translate(eft_sceau,';','')"/>;<xsl:value-of select="translate(taux,';','')"/>;<xsl:value-of select="translate(mnt_frais,';','')"/>;<xsl:value-of select="translate(mnt_comm_change,';','')"/>;<xsl:value-of select="translate(cptie_mnt,';','')"/>;<xsl:value-of select="translate(cptie_devise,';','')"/>;<xsl:value-of select="translate(translate(cptie_num_cpte,';',''),'-','')"/>;<xsl:value-of select="translate(cptie_nom,';','')"/>;<xsl:value-of select="translate(cptie_adresse,';','')"/>;<xsl:value-of select="translate(cptie_cp,';','')"/>;<xsl:value-of select="translate(cptie_ville,';','')"/>;<xsl:value-of select="translate(cptie_pays,';','')"/>;<xsl:value-of select="translate(information,';','')"/>; 
</xsl:template>

</xsl:stylesheet>
