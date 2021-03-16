<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

    <xsl:include href="page_layout.xslt" />
    <xsl:include href="header.xslt" />
    <xsl:include href="criteres_recherche.xslt" />
    <xsl:include href="footer.xslt" />
    <xsl:include href="lib.xslt" />

    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage" />
            <xsl:apply-templates select="equilibre_inventaire_comptabilite" />
        </fo:root>
    </xsl:template>

    <xsl:template match="equilibre_inventaire_comptabilite">
        <fo:page-sequence master-reference="main" font-size="6pt" font-family="Helvetica">
            <xsl:apply-templates select="header" />
            <xsl:call-template name="footer" />
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel" />
                <xsl:apply-templates select="ecarts" />                         
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="ecarts">
            
        <fo:table border-collapse="collapse" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(3)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />            
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />
            <fo:table-column column-width="proportional-column-width(1)" />      
            
            <fo:table-header> 
                <!-- Empty row -->              
                <fo:table-row column-number="14">
                    <fo:table-cell display-align="center">
                        <fo:block text-align="left"> <fo:leader /> </fo:block>
                    </fo:table-cell>
                </fo:table-row>  
                                     
                 <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Date</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Compte</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Libellé</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Devise</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Solde comptes internes</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Solde comptes comptable</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Ecart</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Login</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Historique</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Dossier</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Etat</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Solde crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Solde compta</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">                        
                        <fo:block text-align="center">Ecart crédits</fo:block>
                    </fo:table-cell>                 
                </fo:table-row>
                                            
            </fo:table-header>
            
             <fo:table-body>
                 <xsl:for-each select="ecart">
                    <fo:table-row>
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="date_ecart" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="numero_compte_comptable" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                <xsl:value-of select="libel_cpte_comptable" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="devise" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="solde_cpte_int" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="solde_cpte_comptable" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="ecart" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="login" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="id_his" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="id_doss" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="cre_etat" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="solde_credit" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="solde_cpt" />
                            </fo:block>
                        </fo:table-cell>  
                        
                         <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="ecart_credits" />
                            </fo:block>
                        </fo:table-cell> 
                        
                    </fo:table-row>   
                 </xsl:for-each>            
             </fo:table-body>
            
        </fo:table>
    </xsl:template>
    
</xsl:stylesheet>
