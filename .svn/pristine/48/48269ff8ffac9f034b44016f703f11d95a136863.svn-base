<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="commission_agent">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="infos_syn"/>
        <xsl:apply-templates select="agent"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="infos_syn">
        Information synthétique
        Devise;<xsl:value-of select="translate(devise,';','')"/>;
        Total montant dépôts;<xsl:value-of select="translate(tot_depot,';','')"/>;
        Total montant retraits;<xsl:value-of select="translate(tot_retrait,';','')"/>;
        Total commissions pour agent;<xsl:value-of select="translate(tot_comm_agent,';','')"/>;
        <xsl:if test="tot_comm_inst">Total commissions pour institution;</xsl:if><xsl:if test="tot_comm_inst"><xsl:value-of select="translate(tot_comm_inst,';','')"/>;</xsl:if>
        <xsl:if test="tot_commission">Total commissions;</xsl:if><xsl:if test="tot_commission"><xsl:value-of select="translate(tot_commission,';','')"/>;</xsl:if>
        Total clients créés;<xsl:value-of select="translate(tot_client,';','')"/>;
    </xsl:template>
    <xsl:template match="agent">
        <xsl:if test="depot | retrait | client">
            <xsl:value-of select="translate(nom_agent,';','')"/>;
        </xsl:if>
        <xsl:if test="depot">
            Type de transaction : Dépôt;
            N°;Date;Compte client;Nom client;Montant;Commission pour l’agent;<xsl:if test="his/mnt_comm_inst">Commission pour l’institution</xsl:if>;<xsl:if test="his/mnt_comm_agent_inst">Commission total</xsl:if>;
            <xsl:apply-templates select="depot"/>
        </xsl:if>
        <xsl:if test="retrait">
            Type de transaction : Retrait;
            N°;Date;Compte client;Nom client;Montant;Commission pour l’agent;<xsl:if test="his/mnt_comm_inst">Commission pour l’institution</xsl:if>;<xsl:if test="his/mnt_comm_agent_inst">Commission total</xsl:if>;
            <xsl:apply-templates select="retrait"/>
        </xsl:if>
        <xsl:if test="client">
            Type de transaction : Création client;
            N°;Date;Compte client;Nom client;Montant;Commission pour l’agent;<xsl:if test="his/mnt_comm_inst">Commission pour l’institution</xsl:if>;<xsl:if test="his/mnt_comm_agent_inst">Commission total</xsl:if>;
            <xsl:apply-templates select="client"/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="depot | retrait | client">
        <xsl:apply-templates select="his"/>
    </xsl:template>

    <xsl:template match="his">
        <xsl:value-of select="translate(num_transac,';','')"/>;<xsl:value-of select="translate(date_mod,';','')"/>;<xsl:value-of select="translate(num_complet_cpte,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(mnt_transac,';','')"/>;<xsl:value-of select="translate(mnt_comm_agent,';','')"/>;<xsl:if test="mnt_comm_inst"><xsl:value-of select="translate(mnt_comm_inst,';','')"/></xsl:if>;<xsl:if test="mnt_comm_agent_inst"><xsl:value-of select="translate(mnt_comm_agent_inst,';','')"/></xsl:if>;
    </xsl:template>

</xsl:stylesheet>


