<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>


<xsl:template match="echeancier">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="infos_doss"/>	
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="infos_doss">
	<xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Echeances'"/></xsl:call-template>;
	<xsl:apply-templates select="echeance"/>			
</xsl:template>

<xsl:template match="header_contextuel">
Numéro client; <xsl:value-of select="num_client"/>;
Nom client; <xsl:value-of select="nom_client"/>;
Numero crédit; <xsl:value-of select="num_credit"/>;
Etat crédit; <xsl:value-of select="etat_credit"/>;
Date demande; <xsl:value-of select="date_demande"/>;
Date approbation; <xsl:value-of select="date_approb"/>;
Date déboursement; <xsl:value-of select="date_debours"/>;
Produit; <xsl:value-of select="produit"/>;
Montant; <xsl:value-of select="montant"/>;
Taux d'intérêts; <xsl:value-of select="taux_int"/>%;
Delais de grâce; <xsl:value-of select="delais_grace"/> jour(s);
Devise; <xsl:value-of select="devise"/>;
</xsl:template>

<xsl:template match="echeance">
    Date échéance;Capital dû;Intérêt du;Garantie dûe;Total dû;Capital restant du;interet restant du;garantie du;pénalités du;Total restant du;
	<xsl:apply-templates select="ech_theo"/>
	Date suivi;Capital;Intérets;Garanties;Pénalités;Montant Total;Solde capital;solde interets;solde garanties;solde pénalités;Solde total;
		<xsl:apply-templates select="suivi_remb"/>      
</xsl:template>

<xsl:template match="ech_theo">
	<xsl:value-of select="translate(date_ech,';','')"/>;<xsl:value-of select="translate(cap_du,';','')"/>;<xsl:value-of select="translate(int_du,';','')"/>;<xsl:value-of select="translate(gar_du,';','')"/>;<xsl:value-of select="translate(total_du,';','')"/>;<xsl:value-of select="translate(solde_cap,';','')"/>;<xsl:value-of select="translate(solde_int,';','')"/>;<xsl:value-of select="translate(solde_gar,';','')"/>;<xsl:value-of select="translate(solde_pen,';','')"/>;<xsl:value-of select="translate(solde_total,';','')"/>;
</xsl:template>

<xsl:template match="suivi_remb">
	<xsl:value-of select="translate(date_suivi,';','')"/>;	<xsl:value-of select="translate(mnt_cap,';','')"/>;	<xsl:value-of select="translate(mnt_int,';','')"/>;	<xsl:value-of select="translate(mnt_gar,';','')"/>;	<xsl:value-of select="translate(mnt_pen,';','')"/>;	<xsl:value-of select="translate(mnt_total,';','')"/>;<xsl:value-of select="translate(solde_cap,';','')"/>;	<xsl:value-of select="translate(solde_int,';','')"/>;	<xsl:value-of select="translate(solde_gar,';','')"/>;	<xsl:value-of select="translate(solde_pen,';','')"/>;	<xsl:value-of select="translate(solde_total,';','')"/>;
</xsl:template>

<xsl:template match="xml_total">
  <xsl:for-each select="*">
   <xsl:value-of select="translate(.,';','')"/>
   <xsl:if test="position() != last()">
    <xsl:value-of select="';'"/>
   </xsl:if>
  </xsl:for-each>
<xsl:text disable-output-escaping="yes">
</xsl:text>
</xsl:template>


</xsl:stylesheet>
