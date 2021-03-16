<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="engraischimiques_repartition_qtite_zone">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="list_quantite"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <!-- list beneficiaires payant -->
    <xsl:template match="list_quantite">;
        Province;Commune;Bureau/coopec;Zone;Agriculteur;<xsl:for-each select="nbre_colonne/colonne"><xsl:value-of select="translate(text(),';','')"/>;</xsl:for-each>Montant;
        <xsl:for-each select="province">
            <xsl:for-each select="commune">
                <xsl:for-each select="coopec">
                    <xsl:for-each select="zone">
                                <xsl:value-of select="translate(../../../nom_province,';','')"/>;<xsl:value-of select="translate(../../nom_commune,';','')"/>;<xsl:value-of select="translate(../nom_coopec,';','')"/>;<xsl:value-of select="translate(nom_zone,';','')"/>;<xsl:value-of select="translate(agriculteur,';','')"/>;<xsl:for-each select="detail_produit/qty_produit"><xsl:value-of select="translate(text(),';','')"/>;</xsl:for-each><xsl:value-of select="translate(montant,';','')"/>;
                            </xsl:for-each>
                        </xsl:for-each>
                    </xsl:for-each>
                </xsl:for-each>

    </xsl:template>

</xsl:stylesheet>
