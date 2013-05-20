<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_stickers
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="sticker">
  <div class="stickerBox">
    <xsl:if test="text/text() != ''">
      <div class="stickerText"><xsl:value-of select="text" /></div>
    </xsl:if>
    <xsl:if test="image/img/@src != ''">
      <div class="stickerImage"><xsl:apply-templates select="image/img" /></div>
    </xsl:if>
    <xsl:call-template name="sticker-author">
      <xsl:with-param name="stickerAuthor" select="@author" />
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="sticker-author">
  <xsl:param name="stickerAuthor" />
  <xsl:if test="$stickerAuthor != ''">
    <div class="stickerAuthor">
      - <xsl:value-of select="$stickerAuthor" />
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>