<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="chequiers_commande_envoye_impression">
    <xsl:apply-templates select="header"/>
    ;
    <xsl:apply-templates select="infos_synthetique"/>
    <xsl:apply-templates select="cmd_chequiers_data"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="infos_synthetique">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Récapitulatif'"/></xsl:call-template>
    État commande chéquier;Nombre chéquiers;
    <xsl:for-each select="ligne_synthese">
        <xsl:value-of select="etat_cmd_cheq"/>;<xsl:value-of select="nb_chequiers"/>;
    </xsl:for-each>
    ;
</xsl:template>

<xsl:template match="cmd_chequiers_data">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>;
    <xsl:if test="type_rapport=1">
N° client;N° Compte;Nom du client;Montant des frais;Date commande;
</xsl:if>
<xsl:if test="type_rapport=2">
N° client;N° Compte;Nom du client;Date commande;Date envoi impression;
</xsl:if>
<xsl:for-each select="ligne_cmd_chequier">
    <xsl:if test="../type_rapport=1">
        <xsl:value-of select="num_client" />;<xsl:value-of select="num_cpte" />;<xsl:value-of select="nom_client" />;<xsl:value-of select="frais" />;<xsl:value-of select="date_commande" />;
    </xsl:if>
    <xsl:if test="../type_rapport=2">
        <xsl:value-of select="num_client" />;<xsl:value-of select="num_cpte" />;<xsl:value-of select="nom_client" />;<xsl:value-of select="date_commande" />;<xsl:value-of select="date_envoi_impr" />;
    </xsl:if>
</xsl:for-each>

</xsl:template>

</xsl:stylesheet>
