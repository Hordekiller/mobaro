<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
    xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html lang="fa" dir="rtl">
            <head>
                <meta charset="utf-8"/>
                <title>نقشه سایت</title>
                <style>
                    body { font-family: Vazirmatn, Tahoma, Arial, sans-serif; background: #fafafa; color: #27272a; margin: 0; padding: 32px 16px; }
                    .wrap { max-width: 860px; margin: 0 auto; }
                    h1 { color: #e11d48; font-size: 22px; margin: 0 0 4px; }
                    .meta { color: #71717a; font-size: 13px; margin: 0 0 20px; }
                    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(225,29,72,.07); }
                    th { text-align: right; background: #fff1f2; color: #9f1239; font-size: 13px; padding: 12px 16px; }
                    td { font-size: 13px; padding: 10px 16px; border-top: 1px solid #f4f4f5; }
                    a { color: #be185d; text-decoration: none; word-break: break-all; }
                    a:hover { text-decoration: underline; }
                    .num { text-align: center; color: #71717a; }
                </style>
            </head>
            <body>
                <div class="wrap">
                    <h1>نقشه سایت</h1>
                    <p class="meta">این فایل فقط برای موتورهای جستجو است. از آدرس داخل هر سطر برای مشاهدهٔ صفحه استفاده کنید.</p>
                    <xsl:apply-templates select="sm:sitemapindex"/>
                    <xsl:apply-templates select="sm:urlset"/>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sm:sitemapindex">
        <table>
            <thead>
                <tr><th>نقشه سایت</th><th>آخرین به‌روزرسانی</th></tr>
            </thead>
            <tbody>
                <xsl:for-each select="sm:sitemap">
                    <tr>
                        <td><a href="{sm:loc}"><xsl:value-of select="sm:loc"/></a></td>
                        <td><xsl:value-of select="sm:lastmod"/></td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>

    <xsl:template match="sm:urlset">
        <table>
            <thead>
                <tr>
                    <th>آدرس</th>
                    <th>آخرین به‌روزرسانی</th>
                    <th>تصویر</th>
                    <th>ویدیو</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="sm:url">
                    <tr>
                        <td><a href="{sm:loc}"><xsl:value-of select="sm:loc"/></a></td>
                        <td><xsl:value-of select="sm:lastmod"/></td>
                        <td class="num"><xsl:value-of select="count(image:image)"/></td>
                        <td class="num"><xsl:value-of select="count(video:video)"/></td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>
