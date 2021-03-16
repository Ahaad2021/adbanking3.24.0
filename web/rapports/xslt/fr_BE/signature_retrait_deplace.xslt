<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

    <xsl:template name="signature">
        <fo:table width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block text-align="center"><fo:inline font-size="14pt"><xsl:value-of select="$crayon" disable-output-escaping="yes"/></fo:inline> Signature opérateur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center"><fo:inline font-size="14pt"><xsl:value-of select="$crayon" disable-output-escaping="yes"/></fo:inline> Nom et signature pour autorisation </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>
    </xsl:template>
</xsl:stylesheet>
