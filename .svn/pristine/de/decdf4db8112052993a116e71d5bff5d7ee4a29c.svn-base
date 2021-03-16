<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template name="footer">
		<fo:static-content flow-name="xsl-region-after">
			<fo:table width="100%" table-layout="fixed">
			<fo:table-column column-width="proportional-column-width(15)"/> 
				<fo:table-body font-size="8pt">
				   <fo:table-row>
						<fo:table-cell>
							<fo:block text-align="center">@:&#160;<xsl:value-of select="//adresse"/> 
 	               - <xsl:value-of select="$icone_tel" disable-output-escaping="yes"/>&#160;<xsl:value-of select="//telephone"/> 
 	               - Fax:&#160;<xsl:value-of select="//fax"/> 
 	               - Email:&#160;<xsl:value-of select="//email"/> 
 	               - N° Agrément:&#160;<xsl:value-of select="//num_agrement"/> 
 	               - Code Swift:&#160;<xsl:value-of select="//code_swift_banque"/> 
 	               - N° TVA:&#160;<xsl:value-of select="//num_tva"/>&#160;&#160; 
 	               -&#160;&#160;Page <fo:page-number/> - 
 	               &#160;&#169;&#160;ADbanking 	                
 	              </fo:block> 
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:static-content>
</xsl:template>
</xsl:stylesheet>
