<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="recu"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="recu">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
         <fo:block space-before.optimum="0.2cm"/>
        <xsl:apply-templates select="body"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="body">
    <fo:list-block>
      <xsl:if test="nom_client">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Client name : <xsl:value-of select="nom_client"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <xsl:if test="donneur_ordre">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Originator : <xsl:value-of select="donneur_ordre"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <xsl:if test="num_cpte">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Account number : <xsl:value-of select="num_cpte"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <xsl:if test="num_carte_ferlo">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Ferlo card number : <xsl:value-of select="num_carte_ferlo"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Amount : <xsl:value-of select="montant"/><xsl:if test="/recu/@type='7'"><fo:inline font-weight="bold">  (sous réserve d'encaissement)</fo:inline></xsl:if></fo:block>
        </fo:list-item-body>
      </fo:list-item>
       <xsl:if test="mntEnLettre">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Amount in words : <xsl:value-of select="mntEnLettre"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
      <xsl:if test="/recu/@type='6' or /recu/@type='8' or /recu/@type='40'">
        <xsl:if test="frais">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Deposit fee : <xsl:value-of select="frais"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
          <xsl:if test="fraisDureeMin">
              <fo:list-item>
                  <fo:list-item-label>
                      <fo:block/>
                  </fo:list-item-label>
                  <fo:list-item-body>
                      <fo:block space-before.optimum="0.3cm">Charge of Minimum Duration between two withdrawals not achieved : <xsl:value-of select="fraisDureeMin"/></fo:block>
                  </fo:list-item-body>
              </fo:list-item>
          </xsl:if>
        <xsl:if test="frais_attente">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Fees on hold : <xsl:value-of select="frais_attente"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="solde">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">New balance : <xsl:value-of select="solde"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="remarque">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Remark : <xsl:value-of select="remarque"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="communication">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Communication / remark : <xsl:value-of select="communication"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
      </xsl:if>
      <xsl:if test="/recu/@type='7' or /recu/@type='40'">
        <xsl:apply-templates select="info_cheque"/>
      </xsl:if>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Transaction number : <xsl:value-of select="num_trans"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="0.3cm"/>
   
   <xsl:if test="hasBilletage">
   	    <fo:list-item>
	            <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
	            <fo:list-item-body><fo:block space-before.optimum="0.3cm">Billetage : </fo:block></fo:list-item-body>
	    </fo:list-item>
	    
	    <fo:block space-before.optimum="0.3cm" />
		<xsl:call-template name="tableau_billettage" />
		<fo:block space-before.optimum="0.5cm" />
   </xsl:if>
    
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
            
                <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="6pt">
                 <fo:block text-align="left">Notes and Coins</fo:block>
                </fo:table-cell>

                <xsl:if test="libel_billet_0 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_0" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_1 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_1" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_2 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_2" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_3 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_3" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_4 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_4" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_5 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_5" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_6 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_6" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_7 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_7" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_8 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_8" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_9 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_9" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_10 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_10" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_11 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_11" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_12 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_12" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


                <xsl:if test="libel_billet_13 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_billet_13" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>




            </fo:table-row>
            <fo:table-row>
            
                <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                    <fo:block text-align="left">Quantity</fo:block>
                </fo:table-cell>

                <xsl:if test="valeur_billet_0 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_0" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_1 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_1" />
                        </fo:block>
                    </fo:table-cell>

                </xsl:if>
                <xsl:if test="valeur_billet_2 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_2" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_3 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_3" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_4 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_4" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_5 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_5" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_6 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_6" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_7 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_7" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_8 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_8" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_9 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_9" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_10 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_10" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_11 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_11" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_12 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_12" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="valeur_billet_13 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="valeur_billet_13" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>



            </fo:table-row>

            <fo:table-row>
                <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                    <fo:block text-align="left">Total</fo:block>
                </fo:table-cell>
                <xsl:if test="total_billet_0 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_0" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_1 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_1" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_2 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_2" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_3 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_3" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_4 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_4" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_5 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_5" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_6 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_6" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_7 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_7" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_8 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_8" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_9 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_9" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_10 !=''">
                    <fo:table-cell border-width="1pt" border-color="black" border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_10" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_11 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_11" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_12 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_12" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>
                <xsl:if test="total_billet_13 !=''">
                    <fo:table-cell border-width="1pt" border-color="black"  border-style="solid" padding="6pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="total_billet_13" />
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


            </fo:table-row>

        </fo:table-body>
    </fo:table>

</xsl:template>       
  <xsl:template match="solde">
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">New balance : <xsl:value-of select="solde"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>
  <xsl:template match="info_cheque">
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Numéro chèque : <xsl:value-of select="num_cheque"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Bank : <xsl:value-of select="banque_cheque"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Date chèque : <xsl:value-of select="date_cheque"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Beneficiary : <xsl:value-of select="beneficiaire"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>
</xsl:stylesheet>
