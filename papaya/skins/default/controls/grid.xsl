<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="grid">
  <xsl:param name="title" select="@title" />
  <xsl:param name="hint" select="@hint" />
  <xsl:param name="width" select="@width"/>
  <xsl:variable name="data">
    <table class="grid" style="width: 100%">
      <xsl:for-each select="group">
        <xsl:variable name="rowcount" select="count(row)"/>
        <xsl:variable name="grouppos" select="position()"/>
        <xsl:variable name="grouphref" select="@href"/>
        <xsl:for-each select="row">
          <tr>
            <xsl:attribute name="class">
              <xsl:choose>
                <xsl:when test="position() = 1">top odd</xsl:when>
                <xsl:when test="not(position() mod 2)">even</xsl:when>
                <xsl:otherwise>odd</xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <xsl:if test="position() = 1">
              <th rowspan="{$rowcount}">
                <xsl:attribute name="class">
                  <xsl:choose>
                    <xsl:when test="not($grouppos mod 2)">even</xsl:when>
                    <xsl:otherwise>odd</xsl:otherwise>
                  </xsl:choose>
                </xsl:attribute>
                <a href="{$grouphref}"><xsl:value-of select="$grouppos"/></a>
              </th>
            </xsl:if>
            <xsl:for-each select="cell">
              <td><xsl:apply-templates /></td>
            </xsl:for-each>
          </tr>
        </xsl:for-each>
      </xsl:for-each>
    </table>
  </xsl:variable>
  <xsl:call-template name="panel">
    <xsl:with-param name="title" select="$title" />
    <xsl:with-param name="hint" select="$hint" />
    <xsl:with-param name="width"><xsl:value-of select="$width"/></xsl:with-param>
    <xsl:with-param name="data" select="$data"/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>