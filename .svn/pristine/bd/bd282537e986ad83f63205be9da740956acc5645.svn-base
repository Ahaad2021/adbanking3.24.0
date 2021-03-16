<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="etat_chequiers_imprime">
        <xsl:apply-templates select="header"/>
        ;
        <xsl:apply-templates select="infos_synthetique"/>
        <xsl:apply-templates select="calc_int_recevoir_data"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="infos_synthetique">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Récapitulatif'"/></xsl:call-template>;
        Total intérêts à recevoir :;<xsl:value-of select="etat_cheq"/>;<xsl:value-of select="total_int_recevoir"/>;
        ;
    </xsl:template>

    <xsl:template match="calc_int_recevoir_data">
        <!-- Loop each product -->
        <xsl:for-each select="prod">
            <xsl:call-template name="titre2">
                <xsl:with-param name="titre"><xsl:value-of select="libel"/></xsl:with-param>
            </xsl:call-template>
            N° client;Nom du client;N° du dossier;Capital restant du;Date deblocage du crédit;Intérêts attendus sur l'échéance;Date début théorique;Nombre de jours échus des intérêts à recevoir;Intérêts à recevoir sur l'échéance;Intérêts à recevoir non  payer sur les échéances précédentes;Intérêts à recevoir cumulés;Intérêts à recevoir
            ;
            <xsl:for-each select="ligne_int_recevoir">
                <xsl:value-of select="num_client" />;<xsl:value-of select="nom_client" />;<xsl:value-of select="num_dossier" />;<xsl:value-of select="capital" />;<xsl:value-of select="date_debloc" />;<xsl:value-of select="int_att_ech" />;<xsl:value-of select="date_th" />;<xsl:value-of select="nb_jours" />;<xsl:value-of select="montant_int" />;<xsl:value-of select="int_non_paye" />;<xsl:value-of select="iar_cumule" />;
            </xsl:for-each>
            Total intérêts;<xsl:value-of select="total_int_prod" />
            ;
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
