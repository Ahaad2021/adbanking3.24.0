<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait_no_region"/>
            <xsl:apply-templates select="recu_approvisionnement_delestage"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="signature_autorisation_approvisionnement_delestage.xslt"/>
    <xsl:include href="signature_approvisionnement_delestage.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="recu_approvisionnement_delestage">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header" mode="no_region"/>
                <fo:block space-before.optimum="0.2cm"/>
                <xsl:apply-templates select="body"/>
                <fo:block space-before.optimum="1.1cm"/>
               <!-- <fo:block text-align="center">
                    <xsl:value-of select="$ciseaux" disable-output-escaping="yes"/></fo:block>
                <fo:block space-before.optimum="0.5cm"/>
                <xsl:apply-templates select="header" mode="no_region"/>
                <fo:block space-before.optimum="0.2cm"/>
                <xsl:apply-templates select="body"/>
                <fo:block space-before.optimum="1.5cm"/>-->
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="body">
        <fo:list-block>
            <xsl:if test="date_recu">
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Date : <xsl:value-of select="date_recu"/></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            </xsl:if>
            <xsl:if test="nom_operateur">
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Nom de l'opérateur : <xsl:value-of select="nom_operateur"/></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            </xsl:if>
            <xsl:if test="login">
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Login : <xsl:value-of select="login"/></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            </xsl:if>
            <xsl:if test="type_action='1'">
            <!--<xsl:if test="compte_coffre_debit">-->
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Compte coffre-fort crédité  : <xsl:for-each select="../compte_debit"> <xsl:value-of select="compte_coffre_debit"/>.  </xsl:for-each></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            <!--</xsl:if>-->
            <xsl:if test="compte_caisse_credit">
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Compte caisse débité  : <xsl:value-of select="compte_caisse_credit"/></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            </xsl:if>
            </xsl:if>
            <xsl:if test="type_action='2'">
                <!--<xsl:if test="compte_coffre_debit">-->
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Compte coffre-fort débité  : <xsl:for-each select="../compte_debit"> <xsl:value-of select="compte_coffre_debit"/>.  </xsl:for-each></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
                <!--</xsl:if>-->
                <xsl:if test="compte_caisse_credit">
                    <fo:list-item>
                        <fo:list-item-label>
                            <fo:block/>
                        </fo:list-item-label>
                        <fo:list-item-body>
                            <fo:block space-before.optimum="0.3cm">Compte caisse crédité  : <xsl:value-of select="compte_caisse_credit"/></fo:block>
                        </fo:list-item-body>
                    </fo:list-item>
                </xsl:if>
            </xsl:if>
            <xsl:if test="type_recu='1'">
            <xsl:if test="../transaction">
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Numéro demande : <xsl:for-each select="../transaction"> [<xsl:value-of select="num_transaction"/>] </xsl:for-each></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            </xsl:if>
            </xsl:if>
            <xsl:if test="type_recu='2'">
                <xsl:if test="../transaction">
                    <fo:list-item>
                        <fo:list-item-label>
                            <fo:block/>
                        </fo:list-item-label>
                        <fo:list-item-body>
                            <fo:block space-before.optimum="0.3cm">Numéro transaction :  <xsl:for-each select="../transaction"> <xsl:value-of select="num_transaction"/> </xsl:for-each></fo:block>
                        </fo:list-item-body>
                    </fo:list-item>
                </xsl:if>
            </xsl:if>
            <xsl:if test="type_action='1'">
            <!--<xsl:if test="montant">-->
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Montant  approvisionné :<xsl:for-each select="../montant_appro_delestage">  <xsl:value-of select="montant"/>.  </xsl:for-each></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            <!--</xsl:if>-->
            </xsl:if>
            <xsl:if test="type_action='2'">
                <!--<xsl:if test="montant">-->
                <fo:list-item>
                    <fo:list-item-label>
                        <fo:block/>
                    </fo:list-item-label>
                    <fo:list-item-body>
                        <fo:block space-before.optimum="0.3cm">Montant  délesté :<xsl:for-each select="../montant_appro_delestage">  <xsl:value-of select="montant"/>.  </xsl:for-each></fo:block>
                    </fo:list-item-body>
                </fo:list-item>
                <!--</xsl:if>-->
            </xsl:if>
        </fo:list-block>
        <fo:block space-before.optimum="0.3cm"/>
        <xsl:for-each select="../temp_devise">
        <xsl:if test="hasBilletage">
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body><fo:block space-before.optimum="0.3cm">Billetage en : <xsl:value-of select="devise" /> </fo:block></fo:list-item-body>
            </fo:list-item>

            <fo:block space-before.optimum="0.3cm" />
            <xsl:call-template name="tableau_billettage" />
            <fo:block space-before.optimum="0.5cm" />
        </xsl:if>
        </xsl:for-each>
        <xsl:if test="type_recu='2'">"
            <xsl:call-template name="signature"/>
        </xsl:if>
        <xsl:if test="type_recu='1'">"
            <xsl:call-template name="signature_autorisation"/>
        </xsl:if>
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

                    <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="6pt">
                        <fo:block text-align="left">Billets et pièces de monnaie</fo:block>
                    </fo:table-cell>

                    <xsl:if test="libel_billet_0 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_0" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_1 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_1" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_2 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_2" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_3 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_3" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_4 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_4" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_5 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_5" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_6 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_6" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_7 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_7" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_8 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_8" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_9 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_9" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_10 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_10" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_11 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_11" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_12 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_12" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>


                    <xsl:if test="libel_billet_13 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"  border-style="dashed" padding="10pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="libel_billet_13" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                </fo:table-row>

                <fo:table-row>

                    <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                        <fo:block text-align="left">Nombre </fo:block>
                    </fo:table-cell>

                    <xsl:if test="valeur_billet_0 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_0" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_1 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_1" />
                            </fo:block>
                        </fo:table-cell>

                    </xsl:if>
                    <xsl:if test="valeur_billet_2 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_2" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_3 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_3" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_4 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_4" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_5 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_5" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_6 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_6" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_7 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_7" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_8 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_8" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_9 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_9" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_10 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_10" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_11 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_11" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_12 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_12" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="valeur_billet_13 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="solid" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="valeur_billet_13" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                </fo:table-row>

                <fo:table-row>
                    <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                        <fo:block text-align="left">Total </fo:block>
                    </fo:table-cell>
                    <xsl:if test="total_billet_0 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_0" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_1 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_1" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_2 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_2" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_3 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_3" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_4 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_4" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_5 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_5" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_6 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_6" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_7 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_7" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_8 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_8" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_9 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_9" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_10 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey" border-style="solid" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_10" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_11 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_11" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_12 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_12" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                    <xsl:if test="total_billet_13 !=''">
                        <fo:table-cell border-width="0.5pt" border-color="grey"	border-style="dashed" padding="6pt">
                            <fo:block text-align="right">
                                <xsl:value-of select="total_billet_13" />
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>

                </fo:table-row>

            </fo:table-body>
        </fo:table>

    </xsl:template>
</xsl:stylesheet>

