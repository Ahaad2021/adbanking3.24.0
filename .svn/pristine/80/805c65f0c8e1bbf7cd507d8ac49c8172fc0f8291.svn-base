<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="credit_reech"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="credit_reech">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="ligneCredit"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Global informations'"/>
    </xsl:call-template>
    <xsl:apply-templates select="globalInfos"/>
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Summary information'"/>
    </xsl:call-template>
    <xsl:apply-templates select="infos_synthetiques"/>
  </xsl:template>
  <xsl:template match="globalInfos">
    <fo:block text-align="left">Montant total octroyé : <xsl:value-of select="mnt_tot_oct"/></fo:block>
    <fo:block text-align="left">Montant total crédits rééchelonnés : <xsl:value-of select="mnt_tot_crd_reech"/></fo:block>
    <fo:block text-align="left">Encours crédits rééchelonnés : <xsl:value-of select="encours_crd_reech"/></fo:block>
  </xsl:template>
  <xsl:template match="infos_synthetiques">
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/><xsl:value-of select="libel"/>: <xsl:value-of select="valeur"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="ligneCredit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="lib_prod"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1.5)"/>
      <fo:table-column column-width="proportional-column-width(1.5)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">File</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Name</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Amount granted</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Expected capital</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Remaining capital</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Currency</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Loan status</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Number of reschedulings</fo:block>
          </fo:table-cell>
          <fo:table-cell number-columns-spanned="2" display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Rescheduling</fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row font-weight="bold">
          <fo:table-cell number-columns-spanned="9" display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center"/>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Amount</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Date</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="infosCreditSolidiaire"/>
        <xsl:apply-templates select="detailCredit"/>
        <xsl:apply-templates select="xml_total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="infosCreditSolidiaire">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="no_dossier"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="cap_att"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="cap_rest"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="left" wrap-option="no-wrap">
          <xsl:value-of select="devise"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="lib_etat"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="cre_nbre_reech"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:table>
          <fo:table-column column-width="proportional-column-width(1.5)"/>
          <fo:table-header>     </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="list_mnt_reech"/>
          </fo:table-body>
        </fo:table>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
        <fo:table>
          <fo:table-column column-width="proportional-column-width(1.5)"/>
          <fo:table-header>     </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="list_date_reech"/>
          </fo:table-body>
        </fo:table>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="detailCredit">
    <xsl:choose>
      <xsl:when test="membre_gs=&quot;OUI&quot;">
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="no_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="cap_att"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="cap_rest"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="left" wrap-option="no-wrap">
              <xsl:value-of select="devise"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="lib_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="cre_nbre_reech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:table>
              <fo:table-column column-width="proportional-column-width(1.5)"/>
              <fo:table-header>     </fo:table-header>
              <fo:table-body>
                <xsl:apply-templates select="list_mnt_reech"/>
              </fo:table-body>
            </fo:table>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:table>
              <fo:table-column column-width="proportional-column-width(1.5)"/>
              <fo:table-header>     </fo:table-header>
              <fo:table-body>
                <xsl:apply-templates select="list_date_reech"/>
              </fo:table-body>
            </fo:table>
          </fo:table-cell>
        </fo:table-row>
      </xsl:when>
      <xsl:otherwise>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="no_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="cap_att"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="cap_rest"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="left" wrap-option="no-wrap">
              <xsl:value-of select="devise"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="lib_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="cre_nbre_reech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:table>
              <fo:table-column column-width="proportional-column-width(1.5)"/>
              <fo:table-header>     </fo:table-header>
              <fo:table-body>
                <xsl:apply-templates select="list_mnt_reech"/>
              </fo:table-body>
            </fo:table>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-right-width="0.5px" border-bottom-width="0.5px" border-style="solid">
            <fo:table>
              <fo:table-column column-width="proportional-column-width(1.5)"/>
              <fo:table-header>     </fo:table-header>
              <fo:table-body>
                <xsl:apply-templates select="list_date_reech"/>
              </fo:table-body>
            </fo:table>
          </fo:table-cell>
        </fo:table-row>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="list_mnt_reech">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_reech"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="list_date_reech">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_reech"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="xml_total">
    <fo:table-row>
      <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold"> </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold"> Total en devise</fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_cap_att"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_cap_rest"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_reech"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
