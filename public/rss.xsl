<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">

  <xsl:output method="html" version="5.0" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/">
    <html lang="ko">
      <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>RSS 피드</title>
        <style>
          * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
          }
          
          body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
          }
          
          .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
          }
          
          h1 {
            color: #2a6ebb;
            font-size: 28px;
            margin-bottom: 10px;
          }
          
          .feed-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 30px;
          }
          
          .feed-info p {
            margin: 5px 0;
            color: #666;
          }
          
          .feed-info strong {
            color: #333;
          }
          
          .rss-icon {
            display: inline-block;
            background: #ff6600;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
            transition: background 0.3s;
          }
          
          .rss-icon:hover {
            background: #ff8833;
          }
          
          table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
          }
          
          th {
            background: #2a6ebb;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
          }
          
          td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
          }
          
          tr:hover {
            background: #f8f9fa;
          }
          
          .item-title a {
            color: #2a6ebb;
            text-decoration: none;
            font-weight: 500;
          }
          
          .item-title a:hover {
            text-decoration: underline;
          }
          
          .item-desc {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
          }
          
          .item-meta {
            color: #999;
            font-size: 12px;
          }
          
          .category {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
          }
          
          @media (max-width: 768px) {
            body {
              padding: 10px;
            }
            
            .container {
              padding: 15px;
            }
            
            table {
              font-size: 12px;
            }
            
            th, td {
              padding: 8px;
            }
          }
        </style>
      </head>
      <body>
        <div class="container">
          <xsl:apply-templates/>
        </div>
      </body>
    </html>
  </xsl:template>

  <!-- RSS 2.0 템플릿 -->
  <xsl:template match="rss">
    <h1>
      <xsl:value-of select="channel/title"/>
    </h1>
    
    <div class="feed-info">
      <p><strong>링크:</strong> <a href="{channel/link}"><xsl:value-of select="channel/link"/></a></p>
      <p><strong>설명:</strong> <xsl:value-of select="channel/description"/></p>
      <p><strong>언어:</strong> <xsl:value-of select="channel/language"/></p>
      <p><strong>최종 업데이트:</strong> <xsl:value-of select="channel/lastBuildDate"/></p>
      
      <a href="{channel/atom:link/@href}" class="rss-icon">🔶 RSS 구독</a>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width: 50%;">제목</th>
          <th style="width: 15%;">카테고리</th>
          <th style="width: 15%;">작성자</th>
          <th style="width: 20%;">날짜</th>
        </tr>
      </thead>
      <tbody>
        <xsl:for-each select="channel/item">
          <tr>
            <td>
              <div class="item-title">
                <a href="{link}" target="_blank">
                  <xsl:value-of select="title"/>
                </a>
              </div>
              <div class="item-desc">
                <xsl:value-of select="description"/>
              </div>
            </td>
            <td>
              <span class="category">
                <xsl:value-of select="category"/>
              </span>
            </td>
            <td class="item-meta">
              <xsl:value-of select="dc:creator"/>
            </td>
            <td class="item-meta">
              <xsl:value-of select="substring(pubDate, 1, 16)"/>
            </td>
          </tr>
        </xsl:for-each>
      </tbody>
    </table>
    
    <p style="text-align: center; margin-top: 30px; color: #999; font-size: 12px;">
      총 <strong><xsl:value-of select="count(channel/item)"/></strong>개 항목
    </p>
  </xsl:template>

  <!-- RSS Index 템플릿 -->
  <xsl:template match="rssindex">
    <h1>RSS 피드 인덱스</h1>
    
    <div class="feed-info">
      <p><strong>사이트:</strong> <xsl:value-of select="//rss[1]/loc"/></p>
      <p>이 페이지는 사이트의 모든 RSS 피드 목록을 제공합니다.</p>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width: 50%;">피드 제목</th>
          <th style="width: 30%;">URL</th>
          <th style="width: 20%;">최종 수정</th>
        </tr>
      </thead>
      <tbody>
        <xsl:for-each select="rss">
          <tr>
            <td>
              <strong><xsl:value-of select="title"/></strong>
            </td>
            <td>
              <a href="{loc}" target="_blank" class="rss-icon" style="display: inline-block; padding: 4px 12px; margin: 0;">
                🔶 구독
              </a>
            </td>
            <td class="item-meta">
              <xsl:value-of select="substring(lastmod, 1, 10)"/>
            </td>
          </tr>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:template>

</xsl:stylesheet>
