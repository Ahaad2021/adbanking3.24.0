<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="engraisChimiques_listbenefpayant">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="list_beneficiaires"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <!-- list beneficiaires payant -->
    <xsl:template match="list_beneficiaires">;
        Province;Commune;Bureau/coopec;Zone;Colline;Id carte;Beneficiaire;<xsl:for-each select="nbre_colonne/colonne"><xsl:value-of select="translate(text(),';','')"/>;</xsl:for-each>Montant;
        <xsl:for-each select="province">
            <xsl:for-each select="commune">
                <xsl:for-each select="coopec">
                    <xsl:for-each select="zone">
                        <xsl:for-each select="colline">
                            <xsl:for-each select="details_benef">
                                <xsl:value-of select="translate(../../../../../nom_province,';','')"/>;<xsl:value-of select="translate(../../../../nom_commune,';','')"/>;<xsl:value-of select="translate(../../../nom_coopec,';','')"/>;<xsl:value-of select="translate(nom_zone1,';','')"/>;<xsl:value-of select="translate(nom_colline1,';','')"/>;<xsl:value-of select="translate(id_card,';','')"/>;<xsl:value-of select="translate(nom_benef,';','')"/>;<xsl:for-each select="detail_produit/qty_produit"><xsl:value-of select="translate(text(),';','')"/>;</xsl:for-each><xsl:value-of select="translate(montant,';','')"/>;
                            </xsl:for-each>
                        </xsl:for-each>
                    </xsl:for-each>
                </xsl:for-each>
            </xsl:for-each>
        </xsl:for-each>

    </xsl:template>

</xsl:stylesheet>
