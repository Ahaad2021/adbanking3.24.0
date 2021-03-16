<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="his_ligne_credit">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="infos_synthetiques"/>
        Date;Montant déboursé;Capital remboursé;Intérêts remboursés;Frais remboursés;Pénalités remboursés;Capital restant dû;
        <xsl:apply-templates select="ligneCredit"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="infos_synthetiques">
        Numéro client;<xsl:value-of select="translate(num_client,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Nom client;<xsl:value-of select="translate(nom_client,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Numéro crédit;<xsl:value-of select="translate(num_doss,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Etat;<xsl:value-of select="translate(etat,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Date demande;<xsl:value-of select="translate(date_dem,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Date approbation;<xsl:value-of select="translate(date_approb,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Produit crédit;<xsl:value-of select="translate(libel_prod,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Montant octroyé;<xsl:value-of select="translate(montant_octroye,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Devise;<xsl:value-of select="translate(devise,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Taux d'intérêts;<xsl:value-of select="translate(taux_interet,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Taux frais;<xsl:value-of select="translate(taux_frais,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        Date fin échéance;<xsl:value-of select="translate(date_fin_ech,';','')"/><xsl:text disable-output-escaping="yes"></xsl:text>
        ;;<xsl:text disable-output-escaping="yes"></xsl:text>
        Détails;<xsl:text disable-output-escaping="yes"></xsl:text>
    </xsl:template>

    <xsl:template match="ligneCredit">
        <xsl:apply-templates select="infosCredit"/>
        <xsl:apply-templates select="xml_total"/>
    </xsl:template>

    <xsl:template match="infosCredit">
        <xsl:value-of select="translate(date_evnt,';','')"/>;<xsl:value-of
            select="translate(mnt_deb,';','')"/>;<xsl:value-of select="translate(cap_remb,';','')"/>;<xsl:value-of
            select="translate(int_remb,';','')"/>;<xsl:value-of
            select="translate(frais_remb,';','')"/>;<xsl:value-of
            select="translate(pen_remb,';','')"/>;<xsl:value-of select="translate(cap_restant_du,';','')"/>;
    </xsl:template>

    <xsl:template match="xml_total">
        TOTAL;<xsl:value-of
            select="translate(mnt_deb_tot,';','')"/>;<xsl:value-of
            select="translate(cap_remb_tot,';','')"/>;<xsl:value-of
            select="translate(int_remb_tot,';','')"/>;<xsl:value-of
            select="translate(frais_remb_tot,';','')"/>;<xsl:value-of
            select="translate(pen_remb_tot,';','')"/>;;
    </xsl:template>

</xsl:stylesheet>
