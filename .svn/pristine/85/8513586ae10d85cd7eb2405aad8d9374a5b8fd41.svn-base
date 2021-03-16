<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="equilibre_inventaire_comptabilite">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="ecarts"/>
</xsl:template> 

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>   

<!-- Start : ecarts -->
<xsl:template match="ecarts">
Date;Compte;Libellé;Devise;Solde comptes internes;Solde comptes comptable;Ecart;Login;Historique;Dossier;Etat;Solde crédit;Solde compta;Ecarts crédit;   
<xsl:for-each select="ecart">
<xsl:value-of select="translate(date_ecart,';','')"/>;<xsl:value-of select="translate(numero_compte_comptable,';','')"/>;<xsl:value-of select="translate(libel_cpte_comptable,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(solde_cpte_int,';','')"/>;<xsl:value-of select="translate(solde_cpte_comptable,';','')"/>;<xsl:value-of select="translate(ecart,';','')"/>;<xsl:value-of select="translate(login,';','')"/>;<xsl:value-of select="translate(id_his,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(solde_credit,';','')"/>;<xsl:value-of select="translate(solde_cpt,';','')"/>;<xsl:value-of select="translate(ecart_credits,';','')"/>;
</xsl:for-each>
</xsl:template>

</xsl:stylesheet>