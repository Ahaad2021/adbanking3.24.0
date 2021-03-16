<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="etat_chequiers_imprime">
    <xsl:apply-templates select="header"/>
    ;
    <xsl:apply-templates select="infos_synthetique"/>
    <xsl:apply-templates select="calc_int_paye_data"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="infos_synthetique">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Récapitulatif'"/></xsl:call-template>;
    Total intérêts à payer :;<xsl:value-of select="etat_cheq"/>;<xsl:value-of select="total_int_paye"/>;
    ;
</xsl:template>

<xsl:template match="calc_int_paye_data">
    <!-- Loop each product -->
    <xsl:for-each select="prod">
        <xsl:call-template name="titre2">
            <xsl:with-param name="titre"><xsl:value-of select="prod_name"/></xsl:with-param>
        </xsl:call-template>
        N° client;N° Compte;Nom du client;Capital;Date de mise en place de l'épargne;Date de maturité de l'épargne;Nombre de jours échus pour les intérêts à payer;Intérêts à payer;
        ;
        <xsl:for-each select="ligne_int_paye">
            <xsl:value-of select="num_client" />;<xsl:value-of select="num_cpte" />;<xsl:value-of select="nom_client" />;<xsl:value-of select="capital" />;<xsl:value-of select="date_ouvert" />;<xsl:value-of select="dat_date_fin" />;<xsl:value-of select="nb_jours_echus" />;<xsl:value-of select="montant_int" />;
        </xsl:for-each>
        Total intérêts;<xsl:value-of select="total_int_prod" />
        ;
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>
