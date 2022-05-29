# Authentication
Authentication would be supported by multiple providers such as:
* Twitch
* YouTube
* **Instagram?**

From each provider, we will generate a [[Refresh Token]]
# Refresh Token
This is a token which lasts 7 days. It will be used to get an [[Access Token]]
We do not need to keep the provider token as we are only using the provider in order to get the information necessary to determine the user and provide them with a Refresh Token.
Refresh Tokens are not site-specific unlike Access Tokens

## Structure
```json
{
	"accountId": "USER_ID_HERE",
	"provider": "PROVIDER_HERE"
}
```

## Providers
### Twitch
### YouTube
### Instagram
### Facebook

# Access Token
Access Tokens are site-specific.