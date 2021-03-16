<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="budget_revvisionhistorique">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="list_revision"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <!-- list revision budgetaire -->
    <xsl:template match="list_revision">;
        <xsl:for-each select="list_budget">
            <xsl:value-of select="translate(type_budget,';','')"/>;
            <xsl:for-each select="list_period">
                <xsl:value-of select="translate(period,';','')"/>;
                Date de Révision;Ligne budgétaire;Login qui a révisé le budget;Login qui a validé la Révision budgétaire;Ancien Montant Budgétisé;Nouveau Montant Budgétisé;Variation;
                <xsl:for-each select="ligne_revision">
                    <xsl:value-of select="translate(date_revision,';','')"/>;<xsl:value-of select="translate(ligne_budget,';','')"/>;<xsl:value-of select="translate(login_revise,';','')"/>;<xsl:value-of select="translate(login_valide,';','')"/>;<xsl:value-of select="translate(anc_montant,';','')"/>;<xsl:value-of select="translate(nouv_montant,';','')"/>;<xsl:value-of select="translate(variation,';','')"/>;
                </xsl:for-each>;
            </xsl:for-each>
            ;;
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
