<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="chequiers_en_opposition">
    <xsl:apply-templates select="header"/>
    ;
    <xsl:apply-templates select="infos_synthetique"/>
    <xsl:apply-templates select="chequiers_opposition_data"/>
    <xsl:apply-templates select="cheques_opposition_data"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="infos_synthetique">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Récapitulatif'"/></xsl:call-template>;
    État;Nombre;
    Nombre total de chéquiers mis en opposition;<xsl:value-of select="nb_chequiers_en_opposition"/>;
    Nombre total de chèques mis en opposition;<xsl:value-of select="nb_cheques_en_opposition"/>;
    ;
</xsl:template>

<xsl:template match="chequiers_opposition_data">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails Chéquiers'"/></xsl:call-template>;
    N° client;N° Compte;Nom du client;Date mis en opposition;N° chéquier;Numéro début chéquier;Numéro fin chéquier;Description;
    ;
    <xsl:for-each select="ligne_chequier">
        <xsl:value-of select="num_client" />;<xsl:value-of select="num_cpte" />;<xsl:value-of select="nom_client" />;<xsl:value-of select="date_opposition" />;<xsl:value-of select="id_chequier" />;<xsl:value-of select="num_deb_cheq" />;<xsl:value-of select="num_fin_cheq" />;<xsl:value-of select="description" />;
    </xsl:for-each>
    ;
</xsl:template>

<xsl:template match="cheques_opposition_data">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails Chèques'"/></xsl:call-template>;
    N° client;N° Compte;Nom du client;Date mis en opposition;N° chèque;Etat chèque;Description;
    ;
    <xsl:for-each select="ligne_cheque">
        <xsl:value-of select="num_client_ch" />;<xsl:value-of select="num_cpte_ch" />;<xsl:value-of select="nom_client_ch" />;<xsl:value-of select="date_opposition_ch" />;<xsl:value-of select="id_cheque_ch" />;<xsl:value-of select="libel_etat_cheque_ch" />;<xsl:value-of select="description_ch" />;
    </xsl:for-each>
    ;
</xsl:template>

</xsl:stylesheet>
