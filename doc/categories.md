Categories
===============
These are the different categories for the vulnerabilities. There is no enforcing that theses are the only ones used, they are matched by their name and any value for any category can be used.

XSS
----------------
### Input
- GET
- POST
- COOKIE
- HEADER
- STORED

### Sanitation
- NONE
- BLACKLIST
- WHITELIST
- ENCODING

### Output
- SCRIPT_TAG
- STYLE_TAG
- NORMAL_TAG
- TAG_NAME
- HTML_COMMENT
- ATTRIBUTE_NAME
- NONJS_NOQUOTES
- NONJS_SINGLEQUOTES
- NONJS_DOUBLEQUOTES
- URL_NOQUOTES
- URL_SINGLEQUOTES
- URL_DOUBLEQUOTES
- JSATTRIBUTE_NOQUOTES
- JSATTRIBUTE_SINGLEQUOTES
- JSATTRIBUTE_DOUBLEQUOTES

### Mutated
- YES
- NO


CSRF
----------------------
- NONE
- ONLY_POST
- REFERER
- COMPUTABLE
