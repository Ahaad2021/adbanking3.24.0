<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="liste_clients_deb">
	<xsl:apply-templates select="header"/>
	<xsl:apply-templates select="details"/>			
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="details">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations détaillées'"/></xsl:call-template>;
Encours total de crédit ;<xsl:value-of select="translate(../total/encours_total,';','')"/>;
Encours des dossiers ci-dessous ;<xsl:value-of select="translate(../total/encours_clients_deb,';','')"/>;
Ratio ;<xsl:value-of select="translate(../total/ratio,';','')"/>;
Encours en retard sur ces dossiers ;<xsl:value-of select="translate(../total/encours_retard_deb,';','')"/>;
Ratio encours en retard ;<xsl:value-of select="translate(../total/ratio_retard,';','')"/>;
Total des crédits sain ;<xsl:value-of select="translate(../total/total_sain,';','')"/>;
;
Index;Numéro client;Numéro dossier;Nom client;Encours de crédit;C/V encours de crédit;Etat crédit;Pénalités attendues;
<xsl:apply-templates select="libel_etat/client"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(encours_client,';','')"/>;<xsl:value-of select="translate(cv_encours_client,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(mnt_pen,';','')"/>;
</xsl:template>

</xsl:stylesheet>
