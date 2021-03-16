<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="changetaux"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="changetaux">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="body"/>
        <fo:block space-before.optimum="4cm"/>
        <fo:block text-align="center"><xsl:value-of select="$ciseaux" disable-output-escaping="yes"/>--------------------------------------------------------------------------------------------------------------------------------------------------------------                        </fo:block>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="body"/>
        <fo:block space-before.optimum="2cm"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="body">
    <fo:table>
      <fo:table-column column-width="7cm"/>
      <fo:table-column column-width="3,5cm"/>
      <fo:table-column column-width="3,5cm"/>
      <fo:table-column column-width="2cm"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Montant acheté du client : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block/>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="mnt_achat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="devise_achat"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block font-weight="bold">Source achat</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="source_achat"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block font-weight="bold"/>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right"/>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:block space-before.optimum="0.5cm"/>
    <fo:table>
      <fo:table-column column-width="7cm"/>
      <fo:table-column column-width="3,5cm"/>
      <fo:table-column column-width="3,5cm"/>
      <fo:table-column column-width="2cm"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <fo:block font-weight="bold">Montant vendu au client  : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block/>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="mnt_vente"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="devise_vente"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block font-weight="bold">Destination vente: </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="dest_vente"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <xsl:if test="@affiche_reste = '1'">
      <fo:block space-before.optimum="0.5cm"/>
      <fo:table>
        <fo:table-column column-width="7cm"/>
        <fo:table-column column-width="3,5cm"/>
        <fo:table-column column-width="3,5cm"/>
        <fo:table-column column-width="2cm"/>
        <fo:table-body>
          <fo:table-row>
            <fo:table-cell>
              <fo:block font-weight="bold">Liquidation différence en :<xsl:value-of select="devise_ref"/></fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">
                <xsl:value-of select="deste_reste"/>
              </fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">
                <xsl:value-of select="reste"/>
              </fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">
                <xsl:value-of select="devise_ref"/>
              </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-body>
      </fo:table>
    </xsl:if>
    <fo:block space-before.optimum="0.5cm"/>
    <fo:table>
      <fo:table-column column-width="7cm"/>
      <fo:table-column column-width="3,5cm"/>
      <fo:table-column column-width="3,5cm"/>
      <fo:table-column column-width="2cm"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Taux <xsl:value-of select="devise_achat"/><xsl:value-of select="$icone_fleche" disable-output-escaping="yes"/><xsl:value-of select="devise_vente"/> </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block/>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="taux"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Commission de change : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block/>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="commission"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="devise_achat"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:block space-before.optimum="0.3cm">Numéro de transaction: <xsl:value-of select="id_his"/></fo:block>
    
    <xsl:if test="hasBilletage">
   		<fo:list-item>
        	<fo:list-item-label><fo:block></fo:block></fo:list-item-label>
            <fo:list-item-body><fo:block space-before.optimum="0.3cm">Billetage : </fo:block></fo:list-item-body>
		</fo:list-item>
		    
	    <fo:block space-before.optimum="0.3cm" />
		<xsl:call-template name="tableau_billettage" />    
    </xsl:if>
    
    <fo:block space-before.optimum="0.5cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
  
  <xsl:template name="tableau_billettage">

		<fo:table width="100%" border-collapse="collapse" table-layout="fixed">
	
			<fo:table-column column-width="proportional-column-width(1)" />
	
			<xsl:if test="libel_billet_0 !=''">
				<fo:table-column column-width="proportional-column-width(1.2)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_1 !=''">
				<fo:table-column column-width="proportional-column-width(1.2)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_2 !=''">
				<fo:table-column column-width="proportional-column-width(1.2)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_3 !=''">
				<fo:table-column column-width="proportional-column-width(1.2)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_4 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_5 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_6 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_7 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_8 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_9 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_10 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_11 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_12 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
	
			<xsl:if test="libel_billet_13 !=''">
				<fo:table-column column-width="proportional-column-width(1)" />
			</xsl:if>
			<fo:table-body>
				<fo:table-row>
	
					<fo:table-cell border-width="1pt" border-color="black"
						border-style="solid" padding="6pt">
						<fo:block text-align="left">Billets et pièces de monnaie
						</fo:block>
					</fo:table-cell>
	
					<xsl:if test="libel_billet_0 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_0" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_1 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_1" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_2 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_2" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_3 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_3" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_4 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_4" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_5 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_5" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_6 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_6" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_7 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_7" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_8 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_8" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_9 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_9" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_10 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_10" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_11 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_11" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_12 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_12" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
	
					<xsl:if test="libel_billet_13 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="10pt">
							<fo:block text-align="center">
								<xsl:value-of select="libel_billet_13" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
				</fo:table-row>
	
				<fo:table-row>
	
					<fo:table-cell border-width="1pt" border-color="black"
						border-style="solid" padding="6pt">
						<fo:block text-align="left">Nombre </fo:block>
					</fo:table-cell>
	
					<xsl:if test="valeur_billet_0 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_0" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_1 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_1" />
							</fo:block>
						</fo:table-cell>
	
					</xsl:if>
					<xsl:if test="valeur_billet_2 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_2" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_3 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_3" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_4 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_4" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_5 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_5" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_6 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_6" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_7 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_7" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_8 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_8" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_9 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_9" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_10 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_10" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_11 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_11" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_12 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_12" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="valeur_billet_13 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="valeur_billet_13" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
				</fo:table-row>
	
				<fo:table-row>
					<fo:table-cell border-width="1pt" border-color="black"
						border-style="solid" padding="6pt">
						<fo:block text-align="left">Total </fo:block>
					</fo:table-cell>
					<xsl:if test="total_billet_0 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_0" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_1 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_1" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_2 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_2" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_3 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_3" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_4 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_4" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_5 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_5" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_6 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_6" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_7 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_7" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_8 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_8" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_9 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_9" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_10 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_10" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_11 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_11" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_12 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_12" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="total_billet_13 !=''">
						<fo:table-cell border-width="1pt" border-color="black"
							border-style="solid" padding="6pt">
							<fo:block text-align="left">
								<xsl:value-of select="total_billet_13" />
							</fo:block>
						</fo:table-cell>
					</xsl:if>
	
				</fo:table-row>
	
			</fo:table-body>
		</fo:table>

</xsl:template> 
  
  
</xsl:stylesheet>
