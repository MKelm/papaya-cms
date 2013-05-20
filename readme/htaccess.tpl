RewriteEngine On

#remove session id
RewriteRule ^/?sid[a-z]*([a-zA-Z0-9,-]{20,40})(/.*) $2 [QSA]

#admin pages
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^/?{%webpath_pages%}papaya/module\_([a-z0-9\_]+)\.[a-z]{3,4} /{%webpath_pages%}papaya/module.php?p_module=$1 [QSA,L]

#media files - public / static
RewriteRule ^/?{%webpath_pages%}[^./]*\.(thumb)\.((.).*) - [E=mediaFile:/{%webpath_pages%}papaya-files/thumbs/$3/$2]
RewriteRule ^/?{%webpath_pages%}[^./]*\.(media)\.((.).*) - [E=mediaFile:/{%webpath_pages%}papaya-files/files/$3/$2]
RewriteCond %{DOCUMENT_ROOT}%{%webpath_pages%}{ENV:mediaFile} -f
RewriteRule ^/?{%webpath_pages%}[^./]*\.(thumb|media)\.((.).*) /{%webpath_pages%}%{ENV:mediaFile} [L]

#media files - wrapper script
#RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^/?{%webpath_pages%}([a-fA-F0-9]/)*[a-zA-Z0-9_-]+\.(media|thumb|download|popup|image)(\.(preview))?((\.([a-zA-Z0-9_]+))?(\.[a-zA-Z0-9_]+))$  /{%webpath_pages%}index.php [QSA,L]

#output pages
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^/?{%webpath_pages%}[a-zA-Z0-9_-]+((\.[0-9]+)?\.[0-9]+)((\.[a-z]{2,5})?\.[a-z]+)((\.[0-9]+)?.preview)?$ /{%webpath_pages%}index.php [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^/?{%webpath_pages%}index((\.[a-z]{2,5})?\.[a-z]+)((\.[0-9]+)?.preview)?$ /{%webpath_pages%}index.php [QSA,L]

ErrorDocument 404 /{%webpath_pages%}index.php

#optimize cache headers for static content (if possible)
FileETag none
<IfModule mod_expires.c>
  ExpiresActive on
  ExpiresDefault A5184000
</IfModule>
<IfModule headers_module>
  <FilesMatch "\.(?!(php[345]?|phtml|cgi)$)">
    Header set Cache-Control "public, max-age=5184000, pre-check=5184000"
  </FilesMatch>
</IfModule>
