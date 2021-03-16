<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="clients"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="clients">
    <xsl:apply-templates select="statut_juridique"/>
  </xsl:template>
  <xsl:template match="statut_juridique">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre_niv1">
          <xsl:with-param name="titre" select="stat_jur"/>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(4)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(3)"/>
          <fo:table-column column-width="proportional-column-width(3)"/>
          <xsl:if test="@exist_statut_juridique='1'">
            <fo:table-column column-width="proportional-column-width(4)"/>
          </xsl:if>
          <xsl:if test="@exist_sect_activite='1'">
            <fo:table-column column-width="proportional-column-width(4)"/>
          </xsl:if>
          <xsl:if test="@exist_gestionnaire='1'">
            <fo:table-column column-width="proportional-column-width(4)"/>
          </xsl:if>
          <xsl:if test="@exist_date_crea='1'">
            <fo:table-column column-width="proportional-column-width(4)"/>
          </xsl:if>
          <xsl:if test="@exist_nbr_membres='1'">
            <fo:table-column column-width="proportional-column-width(4)"/>
          </xsl:if>
          <xsl:if test="@exist_etat='1'">
            <fo:table-column column-width="proportional-column-width(4)"/>
          </xsl:if>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Number</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Name</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Gender</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Subscription date</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Birth date</fo:block>
              </fo:table-cell>
              <xsl:if test="@exist_statut_juridique='1'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Legal status</fo:block>
                </fo:table-cell>
              </xsl:if>
              <xsl:if test="@exist_sect_activite='1'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Activity sector</fo:block>
                </fo:table-cell>
              </xsl:if>
              <xsl:if test="@exist_gestionnaire='1'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Manager</fo:block>
                </fo:table-cell>
              </xsl:if>
              <xsl:if test="@exist_date_crea='1'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Creation date</fo:block>
                </fo:table-cell>
              </xsl:if>
              <xsl:if test="@exist_nbr_membres='1'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Number of members</fo:block>
                </fo:table-cell>
              </xsl:if>
              <xsl:if test="@exist_etat='1'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Status</fo:block>
                </fo:table-cell>
              </xsl:if>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="client"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:apply-templates select="infos_synthetiques"/>
  </xsl:template>
  <xsl:template match="infos_synthetiques">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Summary information'"/>
    </xsl:call-template>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre total clients: <xsl:value-of select="nbre_total"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre hommes: <xsl:value-of select="nbre_homme"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre femmes: <xsl:value-of select="nbre_femme"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre personnes morales: <xsl:value-of select="nbre_pm"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre groupes informels: <xsl:value-of select="nbre_gi"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total membres groupe informel: <xsl:value-of select="total_mbre_gi"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre groupes solidaires: <xsl:value-of select="nbre_gs"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block text-align="center"/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block start-indent="1cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total membres groupe solidaire: <xsl:value-of select="total_mbre_gs"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="client">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="sexe"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_adhesion"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_naissance"/>
        </fo:block>
      </fo:table-cell>
      <xsl:if test="../@exist_statut_juridique='1'">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="statut_juridique"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
      <xsl:if test="../@exist_sect_activite='1'">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="sect_activite"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
      <xsl:if test="../@exist_gestionnaire='1'">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="gestionnaire"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
      <xsl:if test="../@exist_date_crea='1'">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="date_crea"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
      <xsl:if test="../@exist_nbr_membres='1'">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="nbr_membres"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
      <xsl:if test="../@exist_etat='1'">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="etat"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
