<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="situation_client"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="situation_client">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="ps"/>
        <xsl:apply-templates select="epargnes"/>
        <xsl:apply-templates select="ord"/>
        <xsl:apply-templates select="garanties"/>
        <xsl:apply-templates select="credits"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Détails du client'"/>
    </xsl:call-template>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Numéro : <xsl:value-of select="num_client"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nom : <xsl:value-of select="nom_client"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <xsl:if test="@stat_jur='1'">
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date de naissance :  <xsl:value-of select="pp_date_naissance"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Lieu de naissance :  <xsl:value-of select="pp_lieu_naissance"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </xsl:if>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Statut juridique :  : <xsl:value-of select="statut_juridique"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Qualité : <xsl:value-of select="qualite"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Etat : <xsl:value-of select="etat_client"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date d'adhésion : <xsl:value-of select="date_adhesion"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre parts sociales : <xsl:value-of select="nbre_ps"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Gestionnaire : <xsl:value-of select="gestionnaire"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>
  <xsl:template match="ps">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Situation parts sociales'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed" border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Numéro de compte</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Intitulé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Date d'ouverture</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Produit d'épargne</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Solde</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Mnt. bloqué</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Mnt. dispo</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="situation_ps"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="situation_ps">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_complet_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="intitule_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_ouvert"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="prod_epargne"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt_bloq"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt_disp"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="epargnes">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Situation épargne'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed" border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Numéro de compte</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Intitulé</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Date d'ouverture</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Produit d'épargne</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Solde</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Mnt. bloqué</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Mnt. dispo</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Solde calcul intérêts</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Date dernier mouvement</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="situation_epargne"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="situation_epargne">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_complet_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="intitule_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_ouvert"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="prod_epargne"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt_bloq"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt_disp"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="solde_calcul_interets"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_dern_mvt"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>

  <xsl:template match="ord">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Situation ordre permanent'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed" border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Numéro compte destinataire</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Produit</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Date Ouverture</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Cotisation/Mise</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Périodicité</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Date Fin</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Solde Actuel</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="situation_ord"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="situation_ord">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_cpte_ord"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_ouverture"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="montant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="periodicite"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_fin"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt_solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>

  <xsl:template match="garanties">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Situation garanties'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed" border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Dossier</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Nom du client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Compte</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Mnt. garantie</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Mnt. crédit</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Etat du crédit</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="situation_garant"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="credits">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Situation crédits'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed" border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Dossier</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Produit</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Date demande</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Montant </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Ech.</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Ech. remb.</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Etat</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="situation_credit"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="situation_credit">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="libel_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="date_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:choose>
            <xsl:when test="@exist_mnt_octr='1'">
              <xsl:value-of select="cre_mnt_octr"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="mnt_dem"/>
            </xsl:otherwise>
          </xsl:choose>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nbre_ech"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nbre_ech_remb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="etat"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="situation_garant">
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
        <fo:block>
          <xsl:value-of select="num_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="gar_num"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="etat"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
