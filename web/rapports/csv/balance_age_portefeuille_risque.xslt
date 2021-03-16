<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="balanceportefeuillerisque">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="totalprcentage"/>
<xsl:apply-templates select="recapilatif"/>
<xsl:apply-templates select="detailsretard"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="totalprcentage">
Encours total des crédits en retard ; <xsl:value-of select="translate(substring(totalenretard,1,string-length(totalenretard)-3),';','')"/>;
Encours total des crédits sains ; <xsl:value-of select="translate(substring(totalsain,1,string-length(totalsian)-3),';','')"/>;
Portefeuille total ; <xsl:value-of select="translate(substring(portefeuilltotal,1,string-length(portefeuilltotal)-3),';','')"/>;
Total des échéances en retard ; <xsl:value-of select="translate(substring(totalprincipal,1,string-length(totalprincipal)-3),';','')"/>;
Pourcentage à risque ; <xsl:value-of select="translate(pourcentagerisque,';','')"/>;
Devise; <xsl:value-of select="translate(substring(totalprincipal,string-length(totalprincipal)-3),';','')"/>;
;
</xsl:template>

<xsl:template match="recapilatif">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="entete_recap"/></xsl:call-template>;
<xsl:apply-templates select="detail_recap"/>
</xsl:template>

<xsl:template match="detail_recap">
  Etat;<xsl:value-of select="translate(lib_etat,';','')"/>;
  Nombre prêt;<xsl:value-of select="translate(nombre_tot,';','')"/>;
  Montant;<xsl:value-of select="translate(substring(montant_tot,1,string-length(montant_tot)-3),';','')"/>;
  Pourcentage à risque;<xsl:value-of select="translate(prcentagerisque,';','')"/>;
  ;
</xsl:template>

<xsl:template match="detailsretard">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_detail"/></xsl:call-template>;
 <xsl:apply-templates select="produits"/>
</xsl:template>
<xsl:template match="produits">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod"/></xsl:call-template>;
Nom client;Num prêt;Devise;Montant prêt;Solde;Principal;Intérêts;Garantie;Pénalités;Impayés;provision;Gestionnaire;
<xsl:apply-templates select="dossiersretard"/>
  Total;<xsl:value-of select="'#'"/>;<xsl:value-of select="'#'"/>;<xsl:value-of select="translate(montant_pret_prod,';','')"/>;<xsl:value-of select="translate(solde_prod,';','')"/>;<xsl:value-of select="translate(principalretard_prod,';','')"/>;<xsl:value-of select="translate(interetretard_prod,';','')"/>;<xsl:value-of select="translate(garantieretard_prod,';','')"/>;<xsl:value-of select="translate(penaliteretard_prod,';','')"/>;<xsl:value-of select="'%'"/>;<xsl:value-of select="translate(prov_mnt_prod,';','')"/>;

</xsl:template>

<xsl:template match="dossiersretard">
  <xsl:value-of select="translate(nomclient,';','')"/>;<xsl:value-of select="translate(numpret,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(montantpret,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;<xsl:value-of select="translate(principalretard,';','')"/>;<xsl:value-of select="translate(interetretard,';','')"/>;<xsl:value-of select="translate(garantieretard,';','')"/>;<xsl:value-of select="translate(penaliteretard,';','')"/>;<xsl:value-of select="translate(impayesprcentage,';','')"/>;<xsl:value-of select="translate(prov_mnt,';','')"/>;<xsl:value-of select="translate(gest,';','')"/>;
</xsl:template>


</xsl:stylesheet>
