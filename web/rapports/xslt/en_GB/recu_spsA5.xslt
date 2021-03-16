<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="recu_sps"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="recu_sps">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="body"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
 <xsl:template match="body">
		<fo:list-block>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Client number:
						<xsl:value-of select="num_client" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Client Name:
						<xsl:value-of select="nom_client" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Shares account number:
						<xsl:value-of select="num_cpte_ps" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Nominal value of a share:
						<xsl:value-of select="prix_part" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
				<xsl:if test="lib_set">
					<fo:block space-before.optimum="0.3cm">
						Newly released shares:
						<xsl:value-of select="nbre_parts" />
					</fo:block>
				</xsl:if>
				<xsl:if test="sous_set">
					<fo:block space-before.optimum="0.3cm">
						New shares subscribed:
						<xsl:value-of select="nbre_parts" />
					</fo:block>
				</xsl:if>
				
				</fo:list-item-body>
				
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
				
					<fo:block space-before.optimum="0.3cm">
						Total number of shares subscribed:
						<xsl:value-of select="nbre_total_ps" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Total number of shares released:
						<xsl:value-of select="nbre_parts_lib" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
		  <xsl:if test="lib_set">
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Released amount:
						<xsl:value-of select="total_ps" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			</xsl:if>
			<fo:list-item>
				<fo:list-item-label>
					<fo:block />
				</fo:list-item-label>
				<fo:list-item-body>
					<fo:block space-before.optimum="0.3cm">
						Amount remaining to be released:
						<xsl:value-of select="total_ps_restant" />
					</fo:block>
				</fo:list-item-body>
			</fo:list-item>
			<fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Transaction number: <xsl:value-of select="num_trans"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
		</fo:list-block>
    <fo:block space-before.optimum="2cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
</xsl:stylesheet>