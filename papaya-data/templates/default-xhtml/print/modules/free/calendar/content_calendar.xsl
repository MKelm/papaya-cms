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

<xsl:param name="CALENDER_LIST_VEIW" select="false()" />

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_calendar.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_calendar_editor'">
      <xsl:call-template name="module-content-calendar-editor">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_calendar_tag'">
      <xsl:call-template name="module-content-calendar-tag">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-default">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-calendar-tag">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  
  <xsl:choose>
    <xsl:when test="$pageContent/calendar/date">
      <xsl:call-template name="content-calendar-tag-get-date-detail">
        <xsl:with-param name="dateDetail" select="$pageContent/calendar/date" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="$pageContent/calendar/monthcalendar">
        <xsl:choose>
          <xsl:when test="$CALENDER_LIST_VEIW">
            <xsl:call-template name="content-calendar-tag-get-month-calendar-list">
              <xsl:with-param name="monthCalendar" select="$pageContent/calendar/monthcalendar" />
            </xsl:call-template>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="content-calendar-tag-get-month-calendar-table">
              <xsl:with-param name="monthCalendar" select="$pageContent/calendar/monthcalendar" />
            </xsl:call-template>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
      <xsl:call-template name="get-from-to-date-periode">
        <xsl:with-param name="pageContent" select="$pageContent" />
      </xsl:call-template>
      <xsl:call-template name="float-fix" />
      <xsl:if test="$pageContent/calendar/dategroup">
        <xsl:call-template name="content-calendar-tag-get-date-group">
          <xsl:with-param name="dateGroup" select="$pageContent/calendar/dategroup" />
        </xsl:call-template>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="get-from-to-date-periode">
  <xsl:param name="pageContent" />
  <xsl:if test="$pageContent/calendar/dategroup">
    <div class="calenderFromToDatePeriode">
      <h1>
        <xsl:choose>
          <xsl:when test="$pageContent/calendar/dategroup[@mode='month']">
            <xsl:call-template name="calendar-month-year">
              <xsl:with-param name="month-year" select="$pageContent/calendar/dategroup/title"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="$pageContent/calendar/dategroup[@mode='day']">
            <xsl:call-template name="calendar-date-format-slash">
              <xsl:with-param name="date" select="$pageContent/calendar/dategroup/title"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$pageContent/calendar/dategroup/title"/>
          </xsl:otherwise>
        </xsl:choose>
      </h1>
	    <strong>
        <xsl:call-template name="language-text">
          <xsl:with-param name="text" select="'PERIOD'"/>
        </xsl:call-template>
        <xsl:text>: </xsl:text>
      </strong>
	    <xsl:call-template name="format-date">
	      <xsl:with-param name="date">
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-from/@year" />
	        <xsl:text>-</xsl:text>
          <xsl:if test="string-length($pageContent/calendar/dategroup/date-from/@month) = 1">
            <xsl:text>0</xsl:text>
          </xsl:if>
          <xsl:value-of select="$pageContent/calendar/dategroup/date-from/@month" />
	        <xsl:text>-</xsl:text>
          <xsl:if test="string-length($pageContent/calendar/dategroup/date-from/@day) = 1">
            <xsl:text>0</xsl:text>
          </xsl:if>
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-from/@day" />
	      </xsl:with-param>
	    </xsl:call-template>
	    <xsl:text> - </xsl:text>
	    <xsl:call-template name="format-date">
	      <xsl:with-param name="date">
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-to/@year" />
	        <xsl:text>-</xsl:text>
          <xsl:if test="string-length($pageContent/calendar/dategroup/date-to/@month) = 1">
            <xsl:text>0</xsl:text>
          </xsl:if>
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-to/@month" />
	        <xsl:text>-</xsl:text>
          <xsl:if test="string-length($pageContent/calendar/dategroup/date-to/@day) = 1">
            <xsl:text>0</xsl:text>
          </xsl:if>
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-to/@day" />
	      </xsl:with-param>
	    </xsl:call-template>
	    <br />
	    <strong>
        <xsl:call-template name="language-text">
          <xsl:with-param name="text" select="'SELECTED DATE'"/>
        </xsl:call-template>
        <xsl:text>: </xsl:text>
      </strong>
	    <xsl:call-template name="format-date">
	      <xsl:with-param name="date">
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-selected/@year" />
	        <xsl:text>-</xsl:text>
          <xsl:if test="string-length($pageContent/calendar/dategroup/date-selected/@month) = 1">
            <xsl:text>0</xsl:text>
          </xsl:if>
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-selected/@month" />
	        <xsl:text>-</xsl:text>
          <xsl:if test="string-length($pageContent/calendar/dategroup/date-selected/@day) = 1">
            <xsl:text>0</xsl:text>
          </xsl:if>
	        <xsl:value-of select="$pageContent/calendar/dategroup/date-selected/@day" />
	      </xsl:with-param>
	    </xsl:call-template>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="content-calendar-tag-get-month-calendar-table">
  <xsl:param name="monthCalendar" />
  
  <table class="calenderNavigation">
    <thead>
      <tr class="calendarMonthNaviagation">
        <xsl:for-each select="$monthCalendar/monthnav/month">
          <xsl:variable name="month">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">
                <xsl:call-template name="ascii-to-upper">
                  <xsl:with-param name="text" select="@title"/>
                </xsl:call-template>
              </xsl:with-param>
            </xsl:call-template>
          </xsl:variable>
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
                  <xsl:value-of select="$month" />
                </xsl:otherwise>
              </xsl:choose>
            </a>
          </th>
        </xsl:for-each>
      </tr>
      <tr class="calendarWeekDayNavigation">
        <xsl:for-each select="$monthCalendar/weekdays/wday">
          <xsl:variable name="wday-hint">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">
                <xsl:call-template name="ascii-to-upper">
                  <xsl:with-param name="text" select="@hint"/>
                </xsl:call-template>
              </xsl:with-param>
            </xsl:call-template>
          </xsl:variable>
          <th>
            <a href="{@href}" title="{$wday-hint}">
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
    <tbody class="calendarDayNavigation">
      <xsl:for-each select="$monthCalendar/weeks/week">
        <tr class="calendarNavigationOneWeek">
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

<xsl:template name="content-calendar-tag-get-month-calendar-list">
  <xsl:param name="monthCalendar" />
  
  <ul class="calendarMonthNaviagation">
    <xsl:for-each select="$monthCalendar/monthnav/month">
      <li>
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
              <xsl:value-of select="@title" />
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </li>
    </xsl:for-each>
  </ul>
  <xsl:call-template name="float-fix" />
  <ul class="calendarWeekDayNavigation">
    <xsl:for-each select="$monthCalendar/weekdays/wday">
      <li><a href="{@href}" title="{@hint}"><xsl:value-of select="@title" /></a></li>
    </xsl:for-each>
  </ul>
  <xsl:call-template name="float-fix" />
  <ul class="calendarDayNavigation">
    <xsl:for-each select="$monthCalendar/weeks/week">
      <li>
	      <ul class="calendarNavigationOneWeek">
	        <xsl:if test="(position() = 1) and (day[1]/@type = 'spacer')">
	          <xsl:variable name="firstDaySpan" 
	                        select="number(day[1]/@dayspan) * 20" />
	          <xsl:attribute name="style">
	            <xsl:text>margin-left: </xsl:text>
	            <xsl:value-of select="$firstDaySpan" />
	            <xsl:text>px;</xsl:text>
	          </xsl:attribute>
	        </xsl:if>
	        <xsl:for-each select="day">
	          <xsl:if test="@type = 'empty' or @type = 'filled'">
	            <li>
	              <xsl:choose>
	                <xsl:when test="@href">
	                  <a href="{@href}" title="{@hint}"><xsl:value-of select="text()" /></a>
	                </xsl:when>
	                <xsl:otherwise>
	                  <xsl:value-of select="text()" />
	                </xsl:otherwise>
	              </xsl:choose>
	            </li>
	          </xsl:if>
	        </xsl:for-each>
	      </ul>
	      <xsl:call-template name="float-fix" />
	    </li>
    </xsl:for-each>
  </ul>
  <xsl:call-template name="float-fix" />
</xsl:template>

<xsl:template name="content-calendar-tag-get-date-group">
  <xsl:param name="dateGroup" />
  
  <div class="dateGroup">
    <xsl:for-each select="$dateGroup/date">
      <div>
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="position() mod 2">oneDate odd</xsl:when>
            <xsl:otherwise>oneDate even</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <h2><xsl:copy-of select="datetitle/a" /></h2>
        <!-- <div class="dateDay"><xsl:value-of select="day" /></div> -->
        <div class="dateStr">
          <strong><xsl:value-of select="datestr" /></strong>
        </div>
        <div class="dateDetail">
          <xsl:value-of select="datedetail/text/node()" />
        </div>
      </div>
    </xsl:for-each>
  </div>
</xsl:template>

<xsl:template name="content-calendar-tag-get-date-detail">
  <xsl:param name="dateDetail" />
  
  <div class="oneDate">
    <h2><xsl:value-of select="$dateDetail/datetitle" /></h2>
    <div class="dateDay"><xsl:value-of select="$dateDetail/day" /></div>
    <div class="dateStr">
      <strong><xsl:value-of select="$dateDetail/datestr" /></strong>
    </div>
    <div class="dateDetail">
      <xsl:value-of select="$dateDetail/datedetail/text/node()" />
    </div>
  </div>
</xsl:template>

<xsl:template name="module-content-calendar-editor">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  
  <xsl:if test="$pageContent/calendar">
    <div class="calendarAddNewDate">
      <a href="{$pageContent/calendar/dates/add/@href}">
        <xsl:value-of select="$pageContent/calendar/dates/add" />
      </a>
    </div>
  </xsl:if>
  
  <xsl:if test="$pageContent/calendar/error">
    <div class="error">
      <xsl:value-of select="$pageContent/calendar/error" />
    </div>
  </xsl:if>
  <xsl:if test="$pageContent/calendar/message">
    <div class="message">
      <xsl:value-of select="$pageContent/calendar/message" />
    </div>
  </xsl:if>
  
  <xsl:if test="$pageContent/calendar/dialog">
    <xsl:for-each select="$pageContent/calendar/dialog">
      <div class="dateDialog">
        <xsl:call-template name="dialog">
          <xsl:with-param name="dialog" select="." />
        </xsl:call-template>
      </div>
    </xsl:for-each>
  </xsl:if>
  
  <div class="calendarOneDataSection">
    <h2><xsl:value-of select="$pageContent/calendar/dates/created/@caption"/></h2>
    <xsl:call-template name="calender-editor-get-dates">
      <xsl:with-param name="pageContent" select="$pageContent" />
      <xsl:with-param name="dates" select="$pageContent/calendar/dates/created/date" />
    </xsl:call-template>
  </div>
  <div class="calendarOneDataSection">
    <h2><xsl:value-of select="$pageContent/calendar/dates/published/@caption"/></h2>
    <xsl:call-template name="calender-editor-get-dates">
      <xsl:with-param name="pageContent" select="$pageContent" />
      <xsl:with-param name="dates" select="$pageContent/calendar/dates/published/date" />
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="calender-editor-get-dates">
  <xsl:param name="pageContent" />
  <xsl:param name="dates" />
  
  <xsl:for-each select="$dates">
    <div>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="position() mod 2">calendarDateContainer odd</xsl:when>
          <xsl:otherwise>calendarDateContainer even</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <h3><xsl:value-of select="title" /></h3>
      <div>
        <xsl:value-of select="from" /> - <xsl:value-of select="to" />
      </div>
      <xsl:if test="time != ''">
        <div class="shortDescription">
          <xsl:value-of select="time" />
        </div>
      </xsl:if>
      <xsl:if test="text != ''">
        <div class="longDescription">
          <xsl:value-of select="text" />
        </div>
      </xsl:if>
      <xsl:if test="count(edit|delete|deletetrans) &gt; 0">
        <ul>
          <xsl:for-each select="edit|delete|deletetrans">
            <li>
              <a href="{@href}">
                <xsl:value-of select="." />
              </a>
            </li>
          </xsl:for-each>
        </ul>
        <xsl:call-template name="float-fix" />
      </xsl:if>
    </div>
  </xsl:for-each>
</xsl:template>

<xsl:template name="calendar-month-year">
  <xsl:param name="month-year"/>
  <xsl:variable name="month">
    <xsl:call-template name="ascii-to-upper">
      <xsl:with-param name="text" select="substring-before($month-year, ' ')"/>
    </xsl:call-template>
  </xsl:variable>
  <xsl:call-template name="language-text">
    <xsl:with-param name="text" select="$month"/>
  </xsl:call-template>
  <xsl:text> </xsl:text>
  <xsl:value-of select="substring-after($month-year, ' ')"/>
</xsl:template>

</xsl:stylesheet>