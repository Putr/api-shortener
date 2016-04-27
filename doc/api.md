API Documentation
-----------------

## Create short url

POST /api/v1/url

Paramaters:
- access_code
- shortUrl
- target_url

## Get information about short url

GET /api/v1/url/[shortUrl]

Returned fields:
- creator label
- timestamp
- target_url
- Number of hits today, last 7 days, last 30 days, alltime

## Delete short url

Can only be done by the owner!

DELETE /api/v1/url/[shortUrl]

Paramater:
- access_code
