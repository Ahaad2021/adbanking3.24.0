<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template name="signature_autorisation">
        <fo:table width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block text-align="center"><fo:inline font-size="14pt"><xsl:value-of select="$crayon" disable-output-escaping="yes"/></fo:inline> Operator Signature</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center"><fo:inline font-size="14pt"><xsl:value-of select="$crayon" disable-output-escaping="yes"/></fo:inline> Name and Signature for approval</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>
    </xsl:template>
</xsl:stylesheet>