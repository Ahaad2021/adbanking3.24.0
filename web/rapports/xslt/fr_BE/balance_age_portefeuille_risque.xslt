<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="balanceportefeuillerisque"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="balanceportefeuillerisque">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="totalprcentage"/>
        <xsl:apply-templates select="recapilatif"/>
        <xsl:apply-templates select="detailsretard"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="recapilatif">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="entete_recap"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <xsl:if test="@exist_gestionnaire='1'">
        <fo:table-cell>
          <fo:block>Gestionnaire</fo:block>
        </fo:table-cell>
      </xsl:if>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block text-align="left">Etat</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Nombre prêt</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Montant</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Pourcentage à risque</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="detail_recap"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="detail_recap">
    <fo:table-row>
      <fo:table-cell>
        <fo:block text-align="left">
          <xsl:value-of select="lib_etat"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="nombre_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="montant_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="prcentagerisque"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="detailsretard">
         <fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="solid" border-bottom-style="solid" space-before="0.5in">Etat des crédits :  <xsl:value-of select="lib_detail"/></fo:block>     
    <xsl:apply-templates select="produits"/>
    </xsl:template>
    <xsl:template match="produits">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
     <xsl:value-of select="lib_prod"/>    
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block text-align="left">Num prêt</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Montant prêt</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Solde</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Principal</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Intérêts</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Garantie</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Pénalités</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Impayés</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">provision</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="dossiersretard"/>
        <fo:table-row>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold"> Total</fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="montant_pret_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="solde_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="principalretard_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="interetretard_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="garantieretard_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="penaliteretard_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
       <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="prov_mnt_prod"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="totalprcentage">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Informations synthétiques'"/>
    </xsl:call-template>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Encours total des crédits en retard : <xsl:value-of select="totalenretard"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Encours total des crédits sains : <xsl:value-of select="totalsain"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Portefeuille total : <xsl:value-of select="portefeuilltotal"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Total des échéances en retard : <xsl:value-of select="totalprincipal"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Pourcentage à risque : <xsl:value-of select="pourcentagerisque"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="dossiersretard">
    <fo:table-row>
      <fo:table-cell>
        <fo:block text-align="left">
          <xsl:value-of select="numpret"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="montantpret"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="principalretard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="interetretard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="garantieretard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="penaliteretard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="impayesprcentage"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="prov_mnt"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell>
        <fo:block font-weight="bold" font-style="italic" wrap-option="no-wrap" text-align="right">Num Client:</fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-style="italic" wrap-option="no-wrap" text-align="left">
          <xsl:value-of select="idclient"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-weight="bold" font-style="italic" wrap-option="no-wrap" text-align="right">Nom Client:</fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-style="italic" wrap-option="no-wrap" text-align="left">
          <xsl:value-of select="nomclient"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-weight="bold" font-style="italic" wrap-option="no-wrap" text-align="right">Devise:</fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-style="italic" wrap-option="no-wrap" text-align="left">
          <xsl:value-of select="devise"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-weight="bold" font-style="italic" wrap-option="no-wrap" text-align="right">Gestionnaire:</fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block font-style="italic" wrap-option="no-wrap" text-align="left">
          <xsl:value-of select="gest"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="groupe_gs='groupe'">
          <fo:table-cell number-columns-spanned="8">
            <fo:block text-align="center" wrap-option="no-wrap">=  =  =  =  =  =  =  =  =  =  =  =  =  =  =  = =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:when test="membre_gs='membre'">
          <fo:table-cell number-columns-spanned="8">
            <fo:block text-align="center" wrap-option="no-wrap">  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -</fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell number-columns-spanned="8">
            <fo:block text-align="center" wrap-option="no-wrap">-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
