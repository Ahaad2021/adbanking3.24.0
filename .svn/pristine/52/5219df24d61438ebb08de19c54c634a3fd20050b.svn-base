<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="histo_credit_oct"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="histo_credit_oct">
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
      <xsl:with-param name="titre" select="'Informations synthétiques'"/>
    </xsl:call-template>
    <xsl:apply-templates select="infos_synthetiques"/>
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
      <fo:table-column column-width="proportional-column-width(1.5)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1.5)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1.5)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Dossier</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Nom</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Montant demandé</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Montant octr.</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Devise</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Date d'octroi</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Durée</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Type durée</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Produit</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block text-align="center">Agent gest.</fo:block>
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
    <xsl:choose>
   <xsl:when test="no_dossier = '0'">
 <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="no_dossier"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="left" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="devise"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="date_oct"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="type_duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="libel_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="agent_gest"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
   </xsl:when>
   <xsl:otherwise>
   <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="no_dossier"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="left" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="devise"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="date_oct"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="type_duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="libel_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="agent_gest"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
   </xsl:otherwise>
  </xsl:choose>
  </xsl:template>
  <xsl:template match="detailCredit">
    <xsl:choose>
      <xsl:when test="membre_gs=&quot;OUI&quot;">
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="no_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="left" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="mnt_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="devise"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="date_oct"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="duree"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="type_duree"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="libel_prod"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="agent_gest"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:when>
      <xsl:otherwise>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="0.5px" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="no_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="left" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="mnt_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="devise"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="date_oct"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="duree"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="type_duree"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="libel_prod"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-top-width="0.5px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="agent_gest"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="xml_total">
    <fo:table-row>
      <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold"> Total</fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold"> Total en devise</fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_octr"/>
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
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
