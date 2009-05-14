<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:str="http://exslt.org/strings" extension-element-prefixes="str">
	<xsl:import href="../../utilities/format-date.xsl" />
	
	<xsl:output method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		omit-xml-declaration="yes"
		encoding="UTF-8"
		indent="yes"
	/>
	
	<xsl:param name="column" select="'name'" />
	<xsl:param name="direction" select="'descending'" />
	
	<xsl:template match="/index">
		<html>
			<head>
				<title>
					<xsl:value-of select="@remote-path" />
				</title>
				<link rel="stylesheet" type="text/css" media="screen" href="{@resource-path}/views/standard/view.css" />
			</head>
			<body>
				<h1>
					<xsl:text>Directory listing of </xsl:text>
					
					<xsl:apply-templates select="@remote-path" />
				</h1>
				
				<xsl:if test="readme">
					<pre><xsl:value-of select="readme" /></pre>
				</xsl:if>
				
				<table cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							<th class="name">Name</th>
							<th class="size">Size</th>
							<th class="date">Date</th>
							<th class="mime">Mime</th>
						</tr>
					</thead>
					
					<tbody>
						<xsl:choose>
							<xsl:when test="$column = 'name'">
								<xsl:apply-templates select="item[@type = 'directory']">
									<xsl:sort select="name" order="{$direction}" />
								</xsl:apply-templates>
								<xsl:apply-templates select="item[@type = 'file']">
									<xsl:sort select="name" order="{$direction}" />
								</xsl:apply-templates>
							</xsl:when>
							<xsl:when test="$column = 'size'">
								<xsl:apply-templates select="item[@type = 'directory']">
									<xsl:sort select="name" order="{$direction}" />
								</xsl:apply-templates>
								<xsl:apply-templates select="item[@type = 'file']">
									<xsl:sort select="@size" data-type="number" order="{$direction}" />
								</xsl:apply-templates>
							</xsl:when>
							<xsl:when test="$column = 'date'">
								<xsl:apply-templates select="item[@type = 'directory']">
									<xsl:sort select="date/@timestamp" data-type="number" order="{$direction}" />
								</xsl:apply-templates>
								<xsl:apply-templates select="item[@type = 'file']">
									<xsl:sort select="date/@timestamp" data-type="number" order="{$direction}" />
								</xsl:apply-templates>
							</xsl:when>
							<xsl:when test="$column = 'mime'">
								<xsl:apply-templates select="item[@type = 'directory']">
									<xsl:sort select="date/@mime" order="{$direction}" />
								</xsl:apply-templates>
								<xsl:apply-templates select="item[@type = 'file']">
									<xsl:sort select="date/@mime" order="{$direction}" />
								</xsl:apply-templates>
							</xsl:when>
						</xsl:choose>
					</tbody>
				</table>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="item[@type = 'directory']">
		<tr class="directory">
			<xsl:if test="position() = 1">
				<xsl:attribute name="class">
					<xsl:text>directory first</xsl:text>
				</xsl:attribute>
			</xsl:if>
			
			<td>
				<a href="{@remote-path}">
					<xsl:value-of select="@name" />
					<xsl:text>/</xsl:text>
				</a>
			</td>
			<td>
				<xsl:text>&#x2013;</xsl:text>
			</td>
			<td>
				<xsl:apply-templates select="date" />
			</td>
			<td>
				<xsl:apply-templates select="@mime" />
			</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="item[@type = 'file']">
		<tr class="file">
			<xsl:if test="position() = 1">
				<xsl:attribute name="class">
					<xsl:text>file first</xsl:text>
				</xsl:attribute>
			</xsl:if>
			
			<td>
				<a href="{@remote-path}">
					<xsl:value-of select="@name" />
				</a>
			</td>
			<td>
				<xsl:apply-templates select="@size" />
			</td>
			<td>
				<xsl:apply-templates select="date" />
			</td>
			<td>
				<xsl:apply-templates select="@mime" />
			</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="@remote-path">
		<xsl:variable name="tokens" select="str:tokenize(., '/')" />
		
		<a href="/">/root/</a>
		
		<xsl:for-each select="$tokens">
			<a>
				<xsl:attribute name="href">
					<xsl:text>/</xsl:text>
					<xsl:for-each select="preceding-sibling::*">
						<xsl:value-of select="." />
						<xsl:text>/</xsl:text>
					</xsl:for-each>
					<xsl:value-of select="." />
					<xsl:text>/</xsl:text>
				</xsl:attribute>
				
				<xsl:value-of select="." />
				<xsl:text>/</xsl:text>
			</a>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="@size">
		<xsl:choose>
			<xsl:when test=". &gt;= 1073741824">
				<xsl:value-of select="format-number(. div 1073741824, '#.0')" />
				<xsl:text>GB</xsl:text>
			</xsl:when>
			<xsl:when test=". &gt;= 1048576">
				<xsl:value-of select="format-number(. div 1048576, '#.0')" />
				<xsl:text>MB</xsl:text>
			</xsl:when>
			<xsl:when test=". &gt;= 1024">
				<xsl:value-of select="format-number(. div 1024, '#.0')" />
				<xsl:text>KB</xsl:text>
			</xsl:when>
			<xsl:when test=". &lt; 1024">
				<xsl:value-of select="." />
				<xsl:text>B</xsl:text>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="@mime">
		<xsl:choose>
			<xsl:when test="normalize-space(.)">
				<xsl:value-of select="." />
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>&#x2013;</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="date">
		<xsl:call-template name="format-date">
			<xsl:with-param name="date" select="." />
			<xsl:with-param name="format" select="'%d; %m+; %y+;, #h;:#0m;#ts;'" />
		</xsl:call-template>
	</xsl:template>
</xsl:stylesheet>