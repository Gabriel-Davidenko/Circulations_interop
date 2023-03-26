<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output method="html" encoding="UTF-8" indent="yes" />

        <xsl:template match="/">
                <table>
                        <thead>
                                <tr>
                                        <td>Journée</td>
                                        <td>Température</td>
                                        <td>Pluie</td>
                                        <td>Vent</td>
                                        <td>Neige</td>
                                </tr>
                        </thead>
                        <tbody>
                                <xsl:apply-templates></xsl:apply-templates>
                        </tbody>
                </table>

        </xsl:template>


        <xsl:template match="echeance">
                <xsl:choose>
                        <xsl:when test="@hour=6">
                                <tr>
                                        <td>
                                                <xsl:value-of select="@hour"></xsl:value-of>h </td>
                                        <xsl:apply-templates></xsl:apply-templates>
                                </tr>
                        </xsl:when>
                </xsl:choose>                
                <xsl:choose>
                        <xsl:when test="@hour=12">
                                <tr>
                                        <td>
                                                <xsl:value-of select="@hour"></xsl:value-of>h </td>
                                        <xsl:apply-templates></xsl:apply-templates>
                                </tr>
                        </xsl:when>
                </xsl:choose>   
                <xsl:choose>
                        <xsl:when test="@hour=18">
                                <tr>
                                        <td>
                                                <xsl:value-of select="@hour"></xsl:value-of>h </td>
                                        <xsl:apply-templates></xsl:apply-templates>
                                </tr>
                        </xsl:when>
                </xsl:choose>
        </xsl:template>

        <xsl:template match="temperature">
                <xsl:apply-templates></xsl:apply-templates>
        </xsl:template>

        <xsl:template match="level">
                <xsl:choose>
                        <xsl:when test='@val="sol"'>
                                <td>
                                        <xsl:value-of select="format-number((. - 273.15),'#.#')" />
                °C </td>
                        </xsl:when>
                </xsl:choose>

        </xsl:template>


        <xsl:template match="vent_moyen">
                <td>
                        <xsl:value-of select="." />km/h </td>
        </xsl:template>

        <xsl:template match="pluie">
                <td>
                        <xsl:value-of select="." />mm </td>
        </xsl:template>
        <xsl:template match="text()" />


        <xsl:template match="risque_neige">
                <td>
                        <xsl:value-of select="." />
                </td>
        </xsl:template>


        <!-- 6h 12h 18h -->
</xsl:stylesheet>