<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:fo="http://www.w3.org/1999/XSL/Format"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage">
            </xsl:call-template>
            <xsl:apply-templates select="recouvrement_credit"/>
        </fo:root>
    </xsl:template>
    
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    
    <xsl:template match="recouvrement_credit">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"></xsl:call-template>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="infos_synthetique"/>
                <xsl:apply-templates select="recap_par_classe"/>
                <xsl:apply-templates select="details_recouvrement"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    
    <!-- Start : infos_synthetique -->
    <xsl:template match="infos_synthetique">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Informations synthétiques'"/>
        </xsl:call-template>
        
        <fo:list-block>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total capital restant dû à la fin de période: <xsl:value-of select="cap_restant_tot"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>            
            <!-- <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total capital restant dû théorique : <xsl:value-of select="cap_theorique_tot"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                       Total Intérêts normaux impayés : <xsl:value-of select="interets_tot"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total pénalités impayées : <xsl:value-of select="penalites_tot"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            
             <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                
                    <xsl:choose>
                  <xsl:when test="contains(capital_total,'-')">
                       <fo:block>
                       <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total capital en retard : 0 </fo:block>
                       </xsl:when>
                      <xsl:otherwise>
                       <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total capital en retard : <xsl:value-of select="capital_total"/>
                    </fo:block>
                      </xsl:otherwise>
                  </xsl:choose>
       
                </fo:list-item-body>
            </fo:list-item> -->

            <!-- ticket 720 : synthetique grand total -->
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Capital attendu pour la periode : <xsl:value-of select="capital_attendu_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Capital remboursé pour la periode : <xsl:value-of select="capital_rembourse_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Capital impayé pour la periode : <xsl:value-of select="capital_impaye_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Intérêts attendus pour la periode : <xsl:value-of select="interet_attendu_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Intérêts remboursés pour la periode : <xsl:value-of select="interet_rembourse_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Intérêts impayés pour la periode : <xsl:value-of select="interet_impaye_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Pénalités remboursées pour la periode : <xsl:value-of select="penalite_rembourse_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Pénalités impayées pour la periode : <xsl:value-of select="penalite_impaye_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Montant remboursé pour la periode : <xsl:value-of select="total_rembourse_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <!--ticket 720 -->
            
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                 <xsl:choose>
                  <xsl:when test="contains(montant_tot,'-')">
                       <fo:block>
                       <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Montant impayé pour la periode : 0 </fo:block>
                       </xsl:when>
                      <xsl:otherwise>
                       <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                           Total Montant impayé pour la periode : <xsl:value-of select="montant_tot"/>
                    </fo:block>
                      </xsl:otherwise>
                  </xsl:choose>

                </fo:list-item-body>
            </fo:list-item>
            
             <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                       Total Coefficient de recouvrement : <xsl:value-of select="coeff_tot"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            
        </fo:list-block>
        
    </xsl:template>
    <!-- End : infos_synthetique -->
    
    <!-- Start : recap_par_classe -->
    <xsl:template match="recap_par_classe">
        
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre"><xsl:value-of select="entete_recap"/></xsl:with-param>
        </xsl:call-template>
        
        <fo:table border-collapse="separate" border-separation.inline-progression-direction="8.5pt" width="100%" table-layout="fixed">
            
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <!-- <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/> -->
            <!--ticket 720 : recap -->
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <!--ticket 720 : recap -->
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
                       
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell>
                        <fo:block  text-align="left">Etat</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Capital restant dû à la fin de période</fo:block>
                    </fo:table-cell>
                    <!-- <fo:table-cell>
                        <fo:block  text-align="right">Capital restant dû théorique</fo:block>
                    </fo:table-cell>
                     <fo:table-cell>
                        <fo:block  text-align="right">Intérêts normaux impayés</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Pénalités impayées</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Capital en retard</fo:block>
                    </fo:table-cell> -->
                    <!--ticket 720 : recap -->
                    <fo:table-cell>
                        <fo:block  text-align="right">Capital attendu pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Capital remboursé pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Capital impayé pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Intérêts attendus pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Intérêts remboursés pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Intérêts impayés pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Pénalités remboursées pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Pénalités impayées pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Montant total remboursé pour la periode</fo:block>
                    </fo:table-cell>
                    <!--ticket 720 : recap -->
                    <fo:table-cell>
                        <fo:block  text-align="right">Montant total impayé pour la periode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block  text-align="right">Coeff. de recouvrement</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            
            <fo:table-body>
                <xsl:apply-templates select="details_recap"/>
            </fo:table-body>
            
        </fo:table>
        
    </xsl:template>
    <!-- End : recap_par_classe -->
    
    <!--  details des recap par classe credit  -->
    <xsl:template match="details_recap">    
        <xsl:for-each select="ligne_recap">
        	<fo:table-row>
	            <fo:table-cell>
	                <fo:block text-align="left"><xsl:value-of select="etat"/></fo:block>
	            </fo:table-cell>	            
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="cap_restant_recap"/></fo:block>
	            </fo:table-cell>	            
	            <!-- <fo:table-cell>
	                <fo:block text-align="right"> <xsl:value-of select="cap_theorique_recap"/></fo:block>                
	            </fo:table-cell>	            
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="interets_recap"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="penalites_recap"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
                    <xsl:choose>
                  <xsl:when test="contains(capital_recap,'-')">
                       <fo:block text-align="right">0</fo:block>
                       </xsl:when>
                      <xsl:otherwise>
                      <fo:block text-align="right"><xsl:value-of select="capital_recap"/></fo:block>
                      </xsl:otherwise>
                  </xsl:choose>
                </fo:table-cell> -->
                <!-- ticket 720 : recap -->
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="capital_attendu_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="capital_rembourse_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="capital_impaye_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="interet_attendu_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="interet_rembourse_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="interet_impaye_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="penalite_rembourse_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="penalite_impaye_recap"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="total_rembourse_recap"/></fo:block>
                </fo:table-cell>
                <!-- ticket 720 : recap -->
            
                <fo:table-cell>
                    <xsl:choose>
                  <xsl:when test="contains(montant_recap,'-')">
                       <fo:block text-align="right">0</fo:block>
                       </xsl:when>
                      <xsl:otherwise>
                      <fo:block text-align="right"><xsl:value-of select="montant_recap"/></fo:block>
                      </xsl:otherwise>
                  </xsl:choose>
                </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="coeff_recap"/></fo:block>
	            </fo:table-cell>
	        </fo:table-row>
        </xsl:for-each>    
    </xsl:template>
    
    <!-- Start : details des recouvrements par dossiers -->
    
    <xsl:template match="details_recouvrement">
       
       <xsl:for-each select="recouvrements_par_classe">
	        <fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="solid" border-bottom-style="solid" space-before="0.5in">Classe de crédits : <xsl:value-of select="classe_credit"/></fo:block>     
	        <xsl:for-each select="recouvrements_par_produits">
	        <xsl:call-template name="titre_niv1">
	            <xsl:with-param name="titre"><xsl:value-of select="libel_prod"/></xsl:with-param>
	        </xsl:call-template>
	        <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">			    
			   <!--  <fo:table-column column-width="proportional-column-width(1)"/>
			    <fo:table-column column-width="proportional-column-width(1)"/>
			    <fo:table-column column-width="proportional-column-width(2.5)"/>
			    <fo:table-column column-width="proportional-column-width(2.5)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/> -->
			    
			     <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(2)"/>
			    <fo:table-column column-width="proportional-column-width(6)"/>
			    <fo:table-column column-width="proportional-column-width(5)"/>
			    <!--<fo:table-column column-width="proportional-column-width(4)"/>-->
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
                <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    <fo:table-column column-width="proportional-column-width(4)"/>
			    
			    <fo:table-header>
			        <fo:table-row font-weight="bold">
			            <fo:table-cell>
			                <fo:block text-align="left">Num prêt</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="left">Num client</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="left">Nom client</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="left">Gestionnaire</fo:block>
			            </fo:table-cell>
			            <!--<fo:table-cell>
			                <fo:block text-align="right">Montant déboursé</fo:block>
			            </fo:table-cell>-->
			            <fo:table-cell>
			                <fo:block text-align="right">Capital restant dû à la fin de période</fo:block>
			            </fo:table-cell>
			            <!--<fo:table-cell>
			                <fo:block text-align="left">Date fin</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="left">Dernier rembours.</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Capital restant dû théorique</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Intérêts normaux impayés</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Pénalités impayées</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Montant capital en retard</fo:block>
			            </fo:table-cell>-->
                        <!--ticket 720 : les montants attendus et remboursés-->
                        <fo:table-cell>
			                <fo:block text-align="right">Capital attendu pour la periode</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Capital remboursé pour la periode</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Intérêts attendus pour la periode</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Intérêts remboursées pour la periode</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Pénalités remboursées pour la periode</fo:block>
			            </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Pénalités impayées pour la periode</fo:block>
			            </fo:table-cell>
                        <fo:table-cell>
                            <fo:block text-align="right">Montant total remboursé pour la periode</fo:block>
                        </fo:table-cell>
			            <fo:table-cell>
			                <fo:block text-align="right">Montant total impayé pour la periode</fo:block>
			            </fo:table-cell>
                        <!--ticket 720 : les montants attendus et remboursés-->
			            <fo:table-cell>
			                <fo:block text-align="right">Coeff. de recouvrement</fo:block>
			            </fo:table-cell>
			        </fo:table-row>
			    </fo:table-header>
			    
			    <fo:table-body>
			        <xsl:apply-templates select="dossiers_recouvrement"/>
			             <fo:table-row>
                             <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold"> Total</fo:block>
                             </fo:table-cell>
                             <fo:table-cell padding-before="8pt">
                                <fo:block/>
                             </fo:table-cell>
                             <fo:table-cell padding-before="8pt">
                                <fo:block/>
                             </fo:table-cell>
                             <fo:table-cell padding-before="8pt">
                                <fo:block/>
                             </fo:table-cell>
                              <!--<fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="montant_debourse_tot"/>
                                </fo:block>
                              </fo:table-cell>-->
                              <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="cap_restant_tot"/>
                                </fo:block>
                              </fo:table-cell>
                               <!--<fo:table-cell padding-before="8pt">
                                <fo:block/>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                <fo:block/>
                              </fo:table-cell>
                               <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="cap_theorique_tot"/>
                                </fo:block>
                              </fo:table-cell>
                               <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="interets_tot"/>
                                </fo:block>
                              </fo:table-cell>
                               <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="penalites_tot"/>
                                </fo:block>
                              </fo:table-cell>
                               <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="capital_retard_tot"/>
                                </fo:block>
                              </fo:table-cell>-->
                             <!--Ticket 720 : les montants attendus et remboursés-->
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                      <xsl:value-of select="capital_attendu_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                      <xsl:value-of select="capital_rembourse_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                    <xsl:value-of select="interet_attendu_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                    <xsl:value-of select="interet_rembourse_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                    <xsl:value-of select="penalite_rembourse_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                    <xsl:value-of select="penalite_impaye_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                  <fo:block font-weight="bold" text-align="right">
                                      <xsl:value-of select="total_rembourse_tot"/>
                                  </fo:block>
                              </fo:table-cell>
                              <!--Ticket 720 : les montants attendus et remboursés-->
                              <fo:table-cell padding-before="8pt">
                                <fo:block font-weight="bold" text-align="right">
                                  <xsl:value-of select="montant_retard_tot"/>
                                </fo:block>
                              </fo:table-cell>
                              <fo:table-cell padding-before="8pt">
                                <fo:block/>
                              </fo:table-cell>
       
                         </fo:table-row>
			        
			    </fo:table-body>
			    
			</fo:table>
	        
	        
	     </xsl:for-each>  
	        
	        
	        
	        
       </xsl:for-each>       
        
    </xsl:template>    
    
     <!-- End : details des recouvrements par dossiers -->
    
    <xsl:template match="dossiers_recouvrement">
    	
    	<xsl:for-each select="ligne_recouvrement">    	
    		<fo:table-row>
	            <fo:table-cell>
	                <fo:block text-align="left"><xsl:value-of select="num_pret"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="left"><xsl:value-of select="num_client"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="left">    <xsl:value-of select="nom_client"/></fo:block>                
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="left"><xsl:value-of select="gestionnaire"/></fo:block>                
	            </fo:table-cell>
	            <!--<fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="montant_debourse"/></fo:block>
	            </fo:table-cell>-->
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="cap_restant"/></fo:block>
	            </fo:table-cell>
	            <!--<fo:table-cell>
	                <fo:block text-align="left"><xsl:value-of select="date_dernier_ech"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="left"><xsl:value-of select="date_dernier_remb"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="cap_theorique"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="interets"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="penalites"/></fo:block>
	            </fo:table-cell>
	           <fo:table-cell>
                    <xsl:choose>
                  <xsl:when test="contains(capital_retard,'-')">
                       <fo:block text-align="right">0</fo:block>
                       </xsl:when>
                      <xsl:otherwise>
                      <fo:block text-align="right"><xsl:value-of select="capital_retard"/></fo:block>
                      </xsl:otherwise>
                  </xsl:choose>
                </fo:table-cell>-->
                <!--Ticket 720 : les montants attendus et remboursés-->
                <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="capital_attendu"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="capital_rembourse"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="interet_attendu"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="interet_rembourse"/></fo:block>
	            </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="penalite_rembourse"/></fo:block>
	            </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="penalite_impaye"/></fo:block>
                </fo:table-cell>
                <fo:table-cell>
                    <fo:block text-align="right"><xsl:value-of select="total_rembourse"/></fo:block>
                </fo:table-cell>
                <!--Ticket 720 : les montants attendus et remboursés-->

                 <fo:table-cell>
                    <xsl:choose>
                  <xsl:when test="contains(montant_retard,'-')">
                       <fo:block text-align="right">0</fo:block>
                       </xsl:when>
                      <xsl:otherwise>
                      <fo:block text-align="right"><xsl:value-of select="montant_retard"/></fo:block>
                      </xsl:otherwise>
                  </xsl:choose>
                </fo:table-cell>
	            <fo:table-cell>
	                <fo:block text-align="right"><xsl:value-of select="coeff"/></fo:block>
	            </fo:table-cell>   	                  
	        </fo:table-row> 
	        
	        <fo:table-row>
	        	<fo:table-cell number-columns-spanned="14">
	        		<fo:block text-align="left" font-style="italic">Etat  : <xsl:value-of select="etat_credit"/></fo:block>
	        	</fo:table-cell>	        	
	       </fo:table-row> 	
	       	        
	        <fo:table-row>
	        	<fo:table-cell number-columns-spanned="14">
              		<fo:block text-align="center" wrap-option="no-wrap" >----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</fo:block>
         		</fo:table-cell>
	        </fo:table-row>   
		        
    	</xsl:for-each>   	
        
    </xsl:template>
    
    
</xsl:stylesheet>
