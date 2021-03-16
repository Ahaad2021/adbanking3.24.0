<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="registrecredit"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="registrecredit">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="ligneCredit"/>
        <xsl:apply-templates select="total"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
  <xsl:template match="ligneCredit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="lib_prod"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block text-align="left">Client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Nom</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Dossier</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Montant octroyé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Montant déboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Date déblocage</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Etat</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Durée</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Capital remboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Interêt remboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Garantie remboursée</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Pénalité remboursée</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Total remboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Capital restant dû</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Intérêt restant dû</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Montant provision</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="infosCreditSolidiaire"/>
        <xsl:apply-templates select="xml_total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>

  <xsl:template match="infosCreditSolidiaire">
    <xsl:choose>
      <xsl:when test="no_dossier = '0'">
        <fo:table-row font-weight="bold">
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="left">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right">
              <xsl:value-of select="no_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right">
              <xsl:value-of select="cre_mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right">
              <xsl:value-of select="cre_mnt_deb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="cre_date_debloc"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="duree_mois"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_pen"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_total"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="int_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_prov"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:when>
      <xsl:otherwise>
        <fo:table-row>
          <fo:table-cell border-width="0.1mm" border-style="solid">          <fo:block text-align="left">
            <xsl:value-of select="num_client"/>
          </fo:block>
        </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>

          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right">
              <xsl:value-of select="no_dossier"/>
            </fo:block>
          </fo:table-cell>

          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right">
              <xsl:value-of select="cre_mnt_octr"/>
            </fo:block>
          </fo:table-cell>

          <fo:table-cell border-width="0.1mm" border-style="solid">          <fo:block text-align="right">
            <xsl:value-of select="cre_mnt_deb"/>
          </fo:block>
        </fo:table-cell>

          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="cre_date_debloc"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right">
              <xsl:value-of select="duree_mois"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">          <fo:block text-align="right" wrap-option="no-wrap">
            <xsl:value-of select="mnt_remb_pen"/>
          </fo:block>
        </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_remb_total"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="int_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="mnt_prov"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="xml_total">
    <fo:table-row font-weight="bold">
      <fo:table-cell number-columns-spanned="2">
        <fo:block text-align="left">Nombre de crédits : <xsl:value-of select="prod_nombre"/></fo:block>
      </fo:table-cell>
      <fo:table-cell number-columns-spanned="2">
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_montant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell number-columns-spanned="1">
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_montant_deb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell number-columns-spanned="2">
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_capital"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_interet"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_garantie"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_penalite"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_total_remb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_capital_du"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_int_du"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right" wrap-option="no-wrap">
          <xsl:value-of select="prod_prov_mnt"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>

  <xsl:template match="total">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Total'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="40%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Nombre de crédits</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="nombre"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Montant octroyé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="montant"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Montant déboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="montant_deb"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Capital remboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="capital"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Interêt remboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="interet"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Garantie remboursée</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="garantie"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Pénalité remboursée</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="penalite"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Total remboursé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_remb"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Capital restant dû</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Intérêt restant dû</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="int_du"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Montant provision</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="prov_mnt"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
</xsl:stylesheet>
