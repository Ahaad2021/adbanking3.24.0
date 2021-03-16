<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="extrait_compte"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="extrait_compte">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="info"/>
        <xsl:call-template name="titre_niv1">
          <xsl:with-param name="titre" select="'EXTRAIT DE COMPTE'"/>
        </xsl:call-template>
        <xsl:apply-templates select="balance"/>
        <xsl:apply-templates select="extrait"/>
        <fo:block space-before.optimum="1.5cm"/>
        <fo:block text-align="center"><xsl:value-of select="$ciseaux" disable-output-escaping="yes"/>           Veuillez conserver cet extrait. Les écritures s'entendent sauf erreur ou omission. Tout désaccord est à signaler avant un moins.                         </fo:block>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>

    <xsl:template match="balance">
        <fo:table border-collapse="separate" width="50%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block>Solde reporté en date du  <xsl:value-of select="eft_dern_date"/> = <xsl:value-of select="eft_dern_solde"/> </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>
    </xsl:template>

    <xsl:template match="info">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Informations globales'"/>
        </xsl:call-template>
        <fo:table border-collapse="separate" width="50%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block>ID Client : </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">
                            <xsl:value-of select="id_client"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block>Nom du client : </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">
                            <xsl:value-of select="nom_client"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block>No compte : </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">
                            <xsl:value-of select="num_cpte"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>
    </xsl:template>


    <xsl:template match="extrait">
        <fo:table border-collapse="separate" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(7)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Date de valeur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">N° transaction</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Opération</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Donneur d'ordre</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Tireur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Communication</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Dépôt</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Retrait</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-width="0.1mm" border-style="solid">
                        <fo:block text-align="center">Solde</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="transaction"/>
                <xsl:apply-templates select="total"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <xsl:template match="transaction">
        <fo:table-row>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="left">
                    <xsl:value-of select="date_valeur"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="left">
                    <xsl:value-of select="n_ref"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="left">
                    <xsl:value-of select="information"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="left">
                    <xsl:value-of select="donneur_ordre"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="left">
                    <xsl:value-of select="tireur"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="left">
                    <xsl:value-of select="communication"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="right">
                    <xsl:value-of select="depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="right">
                    <xsl:value-of select="retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="right">
                    <xsl:value-of select="solde"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
    <xsl:template match="total">
        <fo:table-row font-weight="bold">
            <fo:table-cell border-width="0.05mm" border-style="solid" number-columns-spanned="6">
                <fo:block text-align="center">
                    Total mouvements
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="right">
                    <xsl:value-of select="total_depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="right">
                    <xsl:value-of select="total_retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="right">

                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>


</xsl:stylesheet>
