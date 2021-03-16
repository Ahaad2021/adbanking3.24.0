<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="etat_chequiers_imprime">
    <xsl:apply-templates select="header"/>
    ;
    <xsl:apply-templates select="infos_synthetique"/>
    <xsl:apply-templates select="etat_chequiers_data"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="infos_synthetique">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Récapitulatif'"/></xsl:call-template>;
    État chéquier;Nombre chéquiers;
    <xsl:for-each select="ligne_synthese">
        <xsl:value-of select="etat_cheq"/>;<xsl:value-of select="nb_chequiers"/>;
    </xsl:for-each>
    ;
</xsl:template>

<xsl:template match="etat_chequiers_data">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>;
    N° client;N° Compte;Nom du client;Date de livraison;N° chéquier;Numéro début chéquier;Numéro fin chéquier;Etat du chéquier;
    ;
    <xsl:for-each select="ligne_chequier">
        <xsl:value-of select="num_client" />;<xsl:value-of select="num_cpte" />;<xsl:value-of select="nom_client" />;<xsl:value-of select="date_livraison" />;<xsl:value-of select="id_chequier" />;<xsl:value-of select="num_deb_cheq" />;<xsl:value-of select="num_fin_cheq" />;<xsl:value-of select="etat_chequier" />;
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>
