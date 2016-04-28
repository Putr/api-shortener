API Documentation
-----------------

Api can be used on any of the configured domains. It does not matter which you use, so I suggest you stick to one, and use it. 

## Create short url

**POST /api/v1/url/[domain]**

Paramaters:
- access_code
- shortUrl
- target_url

**Note**: Short URLs are configured *per domain*, that's why you have to specify the domain you wish to use in the URL path. 

## Get information about short url

GET /api/v1/url/[domain]/[shortUrl]

Returned fields:
- creator label
- timestamp
- target_url
- Number of hits today, last 7 days, last 30 days, alltime

## Delete short url

Can only be done by the creator of the short url!

DELETE /api/v1/url/[domain]/[shortUrl]

Paramater:
- access_code
