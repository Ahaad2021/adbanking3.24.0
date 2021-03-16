<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="informations_synthetiques.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="inventaire_credit">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="body"/>
    </xsl:template>

    <xsl:template match="body">
        <xsl:apply-templates select="produit_credit"/>
    </xsl:template>
    <xsl:template match="produit_credit">
        <xsl:call-template name="titre1">
            <xsl:with-param name="titre">
                ;
                <xsl:value-of select="credit"/>
            </xsl:with-param>
        </xsl:call-template>
        <xsl:value-of select="';'"/>
        Numéro client;Numéro dossier;Nom client;Capital début période;Capital deboursé au cours de la periode;Capital remboursé au cours de la période;Intérêts ordinaires remboursés au cours de la période;Intérêts de retard remboursés au cours de la période;Montant total remboursé au cours de la période;<xsl:if test="etat_radie='false'">Capital restant dû à la fin de période;</xsl:if><xsl:if test="etat_radie='true'">Capital Passé en Perte;</xsl:if><xsl:if test="etat_tous='true'">Etat Dossier;</xsl:if>
        <xsl:apply-templates select="ligne_credit"/>
        TOTAUX;;;;<xsl:apply-templates select="totals"/>;
    </xsl:template>
    <xsl:template match="ligne_credit">
        ;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(num_dossier,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(cap_deb_prd,';','')"/>;<xsl:value-of select="translate(cap_deb,';','')"/>;<xsl:value-of select="translate(cap_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(interet_ord_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(interet_ret_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(mnt_total_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(cap_rest_du_fin_period,';','')"/>;<xsl:if test="../etat_tous='true'"><xsl:value-of select="translate(etat_dossier,';','')"/></xsl:if>
    </xsl:template>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="informations_synthetiques"/>
    </xsl:template>
    <xsl:template match="totals">
        <xsl:value-of select="translate(tot_cap_deb_prd,';','')"/>;<xsl:value-of select="translate(tot_cap_deb,';','')"/>;<xsl:value-of select="translate(tot_cap_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(tot_interet_ord_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(tot_interet_ret_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(tot_mnt_total_remb_en_cours_period,';','')"/>;<xsl:value-of select="translate(tot_cap_rest_du_fin_period,';','')"/>;
    </xsl:template>
</xsl:stylesheet>
