<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template match="surfers-online">
  <p class="communityUserOnlineCount">
    <span>
      <xsl:value-of select="@title" />
      <xsl:text>: </xsl:text>
    </span>
    <xsl:value-of select="@count" />
  </p>
  <xsl:if test="count(surfer) &gt; 0">
    <ul class="communityUserOnlineList">
      <xsl:for-each select="surfer">
        <li><a href="{@link}"><xsl:value-of select="text()" /></a></li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template match="contact-status">
  <div>Testausgabe für Contact Status</div>
</xsl:template>

<xsl:template match="contacts"> 
  <xsl:if test="count(contact) &gt; 0">
    <div class="boxUserContactListContainer">
      <ul class="boxUserContactList">
        <xsl:for-each select="contact">
          <li>
            <a href="{@href}"><xsl:value-of select="@name" /></a>
          </li>
        </xsl:for-each>
      </ul>
      <div class="boxUserContactListNavigation">
        <xsl:if test="navigation or showpartial">
          <ul>
            <xsl:if test="navigation">
              <xsl:if test="navigation/backward">
                <li class="navigationBack">
                  <a href="{navigation/backward/@href}">
                    <xsl:value-of select="navigation/backward/@caption" />
                  </a>
                </li>
              </xsl:if>
              <xsl:if test="navigation/forward">
                <li class="navigationNext">
                  <a href="{navigation/forward/@href}">
                    <xsl:value-of select="navigation/forward/@caption" />
                  </a>
                </li>
              </xsl:if>
              <xsl:if test="navigation/showall">
                <li class="showAllSurfer">
                  <a href="{navigation/showall/@href}">
                    <xsl:value-of select="navigation/showall/@caption" />
                  </a>
                </li>
              </xsl:if>
            </xsl:if>
            <xsl:if test="showpartial">
              <li class="showPartialSurfer">
                <a href="{showpartial/@href}">
                  <xsl:value-of select="showpartial/@caption" />
                </a>
              </li>
            </xsl:if>
          </ul>
        </xsl:if>
      </div>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>