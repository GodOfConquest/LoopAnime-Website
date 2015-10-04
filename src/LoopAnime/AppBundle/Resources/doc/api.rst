Api & Authorization Codes
******************************

**Crient a new Client:**

php app/console loopanime:oauth-server:client:create --redirect-uri="http://dev.loop-anime.com/" --grant-type="authorization_code" --grant-type="password" --grant-type="refresh_token" --grant-type="token" --grant-type="client_credentials"

**Returns**

*Added a new client with public id 2_612gb9senysc4ocgwkckkkkokccgcwk04wgsg0k4oc4c00sskg, secret 1vki2y11pcboso4ksw8co80ccsc4css80ksockwgwokk04o048*

**Getting the authorization code:**

/oauth/v2/token?client_id=CLIENT_ID&client_secret=CLIENT_SECRET&grant_type=client_credentials

**Using the access code:**

/api/v1/animes.json?access_token=ACCESS_TOKEN

**API Docs can be accessed with your Access token to the url:**

/api/docs

**You can see the documentation of a specific request using the _doc parameter:**

1. /api/v1/animes.json?_doc
2. /api/v1/1.json?_doc
