<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  xmlns:papaya-fn="http://www.papaya-cms.com/ns/functions"
  extension-element-prefixes="func"
  exclude-result-prefixes="#default papaya-fn"
>

<func:function name="papaya-fn:javascript-escape-string">
  <xsl:param name="string"></xsl:param>
  <xsl:param name="quoteChar">'</xsl:param>
  <xsl:param name="addQuotes" select="true()" />
  <func:result>
    <xsl:call-template name="javascript-escape-string">
      <xsl:with-param name="string" select="$string"/>
      <xsl:with-param name="quoteChar" select="$quoteChar"/>
      <xsl:with-param name="addQuotes" select="$addQuotes" />
    </xsl:call-template>
  </func:result>
</func:function>

<xsl:template name="javascript-escape-string">
  <xsl:param name="string"></xsl:param>
  <xsl:param name="quoteChar">'</xsl:param>
  <xsl:param name="addQuotes" select="true()" />
  <xsl:if test="$addQuotes">
    <xsl:value-of select="$quoteChar"/>
  </xsl:if>
  <xsl:call-template name="javascript-escape-string-ltslash">
    <xsl:with-param name="string">
      <xsl:choose>
        <xsl:when test="contains($string, '--')">
          <xsl:variable name="stringBefore" select="substring-before($string, '--')" />
          <xsl:call-template name="javascript-escape-string-linebreaks">
            <xsl:with-param name="string">
              <xsl:call-template name="javascript-escape-string-quotes">
                <xsl:with-param name="string" select="$stringBefore"/>
                <xsl:with-param name="quoteChar" select="$quoteChar"/>
              </xsl:call-template>
            </xsl:with-param>
          </xsl:call-template>
          <xsl:text>-</xsl:text>
          <xsl:value-of select="$quoteChar"/>
          <xsl:text> + </xsl:text>
          <xsl:value-of select="$quoteChar"/>
          <xsl:call-template name="javascript-escape-string">
            <xsl:with-param name="string" select="substring($string, string-length($stringBefore) + 2)"/>
            <xsl:with-param name="quoteChar" select="$quoteChar"/>
            <xsl:with-param name="addQuotes" select="false()" />
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="javascript-escape-string-linebreaks">
            <xsl:with-param name="string">
              <xsl:call-template name="javascript-escape-string-quotes">
                <xsl:with-param name="string" select="$string"/>
                <xsl:with-param name="quoteChar" select="$quoteChar"/>
              </xsl:call-template>
            </xsl:with-param>
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:with-param>
  </xsl:call-template>
  <xsl:if test="$addQuotes">
    <xsl:value-of select="$quoteChar"/>
  </xsl:if>
</xsl:template>

<xsl:template name="javascript-escape-string-linebreaks">
  <xsl:param name="string"></xsl:param>
  <xsl:variable name="nextLF" select="string-length(substring-before($string, '&#10;'))" />
  <xsl:variable name="nextCR" select="string-length(substring-before($string, '&#13;'))" />
  <xsl:variable name="hasLF" select="contains($string, '&#10;')" />
  <xsl:variable name="hasCR" select="contains($string, '&#13;')" />
  <xsl:choose>
    <xsl:when test="contains($string, '&#10;') and ($nextLF &lt;= $nextCR or not($hasCR))">
      <xsl:value-of select="substring($string, 1, $nextLF)" />
      <xsl:text>\n</xsl:text>
      <xsl:call-template name="javascript-escape-string-linebreaks">
        <xsl:with-param name="string" select="substring($string, $nextLF + 2)"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="contains($string, '&#13;')">
      <xsl:value-of select="substring($string, 1, $nextCR)" />
      <xsl:text>\r</xsl:text>
      <xsl:call-template name="javascript-escape-string-linebreaks">
        <xsl:with-param name="string" select="substring($string, $nextCR + 2)"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$string" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="javascript-escape-string-quotes">
  <xsl:param name="string"></xsl:param>
  <xsl:param name="quoteChar">'</xsl:param>
  <xsl:variable name="nextQuoteChar" select="string-length(substring-before($string, $quoteChar))" />
  <xsl:variable name="nextBackslash" select="string-length(substring-before($string, '\'))" />
  <xsl:variable name="hasQuoteChar" select="contains($string, $quoteChar)" />
  <xsl:variable name="hasBackslash" select="contains($string, '\')" />
  <xsl:choose>
    <xsl:when test="$hasQuoteChar and ($nextQuoteChar &lt; $nextBackslash or not($hasBackslash))">
      <xsl:value-of select="substring($string, 1, $nextQuoteChar)" />
      <xsl:text>\</xsl:text><xsl:value-of select="$quoteChar"/>
      <xsl:call-template name="javascript-escape-string-quotes">
        <xsl:with-param name="string" select="substring($string, $nextQuoteChar + 2)"/>
        <xsl:with-param name="quoteChar" select="$quoteChar"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$hasBackslash">
      <xsl:value-of select="substring($string, 1, $nextBackslash)" />
      <xsl:text>\\</xsl:text>
      <xsl:call-template name="javascript-escape-string-quotes">
        <xsl:with-param name="string" select="substring($string, $nextBackslash + 2)"/>
        <xsl:with-param name="quoteChar" select="$quoteChar"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$string" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="javascript-escape-string-ltslash">
  <!-- avoid that the browser sees </script> -->
  <xsl:param name="string"></xsl:param>
  <xsl:variable name="nextLtChar" select="string-length(substring-before($string, '&lt;/'))" />
  <xsl:variable name="hasLtChar" select="contains($string, '&lt;/')" />
  <xsl:choose>
    <xsl:when test="$hasLtChar">
      <xsl:value-of select="substring($string, 1, $nextLtChar)" />
      <xsl:text>&lt;\</xsl:text>
      <xsl:call-template name="javascript-escape-string-ltslash">
        <xsl:with-param name="string" select="substring($string, $nextLtChar + 2)"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$string" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
