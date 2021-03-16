<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="batch"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="batch">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="archivage"/>
        <xsl:apply-templates select="comptes_arretes"/>
        <xsl:apply-templates select="cat_dat_echeance"/>
        <xsl:apply-templates select="frais_tenue_cpte"/>
        <xsl:apply-templates select="interets_debiteurs"/>
        <xsl:apply-templates select="ordres_permanents"/>
        <xsl:apply-templates select="rembourse_auto"/>
        <xsl:apply-templates select="declasse_credit"/>
        <xsl:apply-templates select="transaction_ferlo"/>
        <xsl:apply-templates select="coherence_compta_cpte_interne_credit"/>
        <xsl:apply-templates select="coherence_cap_restant_cpte_interne_credit"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="archivage">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Clients archivés : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(4)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>Client</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client name</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Subscription date</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_archivage"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_archivage">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_adh"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="comptes_arretes">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Comptes arrêtés : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(4)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client name</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Balance</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Amount of interests</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Beneficiary account</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_comptes_arretes"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_comptes_arretes">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="montant_interets"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="compte_ben"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="cat_dat_echeance">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('CAT, DAT et épargnes à la source arrivés à échéance : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client name</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Balance</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Action</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Opening date</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Destination account</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_cat_dat_echeance"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_cat_dat_echeance">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="action"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="date_ouv"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="destination"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="frais_tenue_cpte">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Prélèvement frais de tenue de compte : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)" border-left-width="0.3pt" border-left-style="solid" border-left-color="black"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Solde initial</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Fee</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Solde initial</fo:block>
            </fo:table-cell>
            <fo:table-cell padding-end="0.2cm">
              <fo:block text-align="right">Fee</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_frais_tenue_cpte"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_frais_tenue_cpte">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_cpte1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_client1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-end="0.2cm">
        <fo:block text-align="right">
          <xsl:value-of select="frais1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_cpte2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_client2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="frais2"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="interets_debiteurs">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Prélèvement intérêts débiteurs : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)" border-left-width="0.3pt" border-left-style="solid" border-left-color="black"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Solde initial</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Fee</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Solde initial</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Fee</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_interets_debiteurs"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_interets_debiteurs">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_cpte1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="frais1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_cpte2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="frais2"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="ordres_permanents">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Ordres permanents : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
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
              <fo:block text-align="right">Source account</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Destination account</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Amount</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Fee</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="center">Periodicity</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="center">Interval</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="center">Status</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_ordres_permanents"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_ordres_permanents">
    <fo:table-row>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="num_cpte_src"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="num_cpte_dest"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="montant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="frais"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="center">
          <xsl:value-of select="periodicite"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="center">
          <xsl:value-of select="intervale"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="center">
          <xsl:value-of select="statut"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="rembourse_auto">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Remboursements automatiques échéances : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>File</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Due date</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client name</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Expected capital</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Interests</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Penalties</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Total</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_rembourse_auto"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_rembourse_auto">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_ech"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="cap"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="int"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="pen"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="tot"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="declasse_credit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Loans downgrade', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(5)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>File</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Client name</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Balance</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">Former status</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block text-align="right">New state</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_declasse_credit"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_declasse_credit">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="ancien"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="nouveau"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="transaction_ferlo">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Ferlo transactions processed : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(4)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>Type of transaction</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Amount</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_transaction_ferlo"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_transaction_ferlo">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="type"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="montant"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="coherence_compta_cpte_interne_credit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Ferlo transactions processed : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>File No.</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Solde compte interne </fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Solde comptable crédit </fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Difference solde  </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_coherence_compta_cpte_interne_credit"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_coherence_compta_cpte_interne_credit">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="solde_cpt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="solde_cap_compta"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="solde_diff"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="coherence_cap_restant_cpte_interne_credit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Ferlo transactions processed : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell>
              <fo:block>File No.</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Account number</fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Solde compte interne </fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Solde capital restant dû </fo:block>
            </fo:table-cell>
            <fo:table-cell>
              <fo:block>Difference solde  </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_coherence_cap_restant_cpte_interne_credit"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_coherence_cap_restant_cpte_interne_credit">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="solde_cpt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="solde_cap_compta"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="solde_diff"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
