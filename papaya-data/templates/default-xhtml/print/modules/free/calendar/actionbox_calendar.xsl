<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="./calendar.xsl"/>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template match="monthcalendar">
  <table class="calenderBoxNavigation">
    <thead>
      <tr class="calendarBoxMonthNaviagation">
        <xsl:for-each select="monthnav/month">
          <th>
            <xsl:if test="position() = 2">
              <xsl:attribute name="colspan">5</xsl:attribute>
              <xsl:attribute name="class">middleCell</xsl:attribute>
            </xsl:if>
            <xsl:if test="position() = last()">
              <xsl:attribute name="class">rightCell</xsl:attribute>
            </xsl:if>
            <a href="{@href}" title="{@hint}">
              <xsl:choose>
                <xsl:when test="position() = 1 or position() = last()">
                  <xsl:choose>
                    <xsl:when test="position() = 1">
                      <xsl:text>&lt;</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:text>&gt;</xsl:text>
                    </xsl:otherwise>
                  </xsl:choose>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:call-template name="language-text">
                    <xsl:with-param name="text">
                      <xsl:call-template name="ascii-to-upper">
                        <xsl:with-param name="text" select="@title"/>
                      </xsl:call-template>
                    </xsl:with-param>
                  </xsl:call-template>
                </xsl:otherwise>
              </xsl:choose>
            </a>
          </th>
        </xsl:for-each>
      </tr>
      <tr class="calendarBoxWeekDayNavigation">
        <xsl:for-each select="weekdays/wday">
          <xsl:variable name="hint">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">
                <xsl:call-template name="ascii-to-upper">
                  <xsl:with-param name="text" select="@hint"/>
                </xsl:call-template>
              </xsl:with-param>
            </xsl:call-template>
          </xsl:variable>
          <th>
            <a href="{@href}" title="{$hint}">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">
                  <xsl:call-template name="ascii-to-upper">
                    <xsl:with-param name="text" select="@title"/>
                  </xsl:call-template>
                </xsl:with-param>
              </xsl:call-template>
            </a>
          </th>
        </xsl:for-each>
      </tr>
    </thead>
    <tbody class="calendarBoxDayNavigation">
      <xsl:for-each select="weeks/week">
        <tr class="calendarBoxNavigationOneWeek">
          <xsl:for-each select="day">
            <td>
              <xsl:choose>
                <xsl:when test="@type = 'spacer'">
                  <xsl:attribute name="colspan">
                    <xsl:value-of select="@dayspan" />
                  </xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:choose>
                    <xsl:when test="@href">
                      <xsl:choose>
                        <xsl:when test="@selected = 'selected'">
                          <strong>
                            <a href="{@href}" title="{@hint}">
                              <xsl:value-of select="text()" />
                            </a>
                          </strong>
                        </xsl:when>
                        <xsl:otherwise>
                          <a href="{@href}" title="{@hint}">
                            <xsl:value-of select="text()" />
                          </a>
                        </xsl:otherwise>
                      </xsl:choose>
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:choose>
                        <xsl:when test="@selected = 'selected'">
                          <strong>
                            <xsl:value-of select="text()" />
                          </strong>
                        </xsl:when>
                        <xsl:otherwise>
                          <xsl:value-of select="text()" />
                        </xsl:otherwise>
                      </xsl:choose>
                    </xsl:otherwise>
                  </xsl:choose>
                </xsl:otherwise>
              </xsl:choose>
            </td>
          </xsl:for-each>
        </tr>
      </xsl:for-each>
    </tbody>
  </table>
</xsl:template>

<xsl:template match="dategroup">
  
  <div>
    <xsl:call-template name="calendar-date-format-slash">
      <xsl:with-param name="date" select="title"/>
    </xsl:call-template>
  </div>
  <br />
  <xsl:for-each select="date">
      <div>
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="position() mod 2">oneBoxDate odd</xsl:when>
            <xsl:otherwise>oneBoxDate even</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <h2><xsl:apply-templates select="datetitle/a" /></h2>
        <div class="dateStr">
          <strong><xsl:value-of select="datestr" /></strong>
        </div>
        <div class="dateDetail">
          <xsl:text> </xsl:text>
          <xsl:value-of select="datedetail/text/node()" />
        </div>
      </div>
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>