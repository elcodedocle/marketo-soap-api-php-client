Marketo SOAP API PHP (micro-) Client by elcodedocle
===================================================
#####*Because you don't wanna waste your time writing API wrappers*

 Copyright (C) 2014 Gael Abadin<br/>
 License: [MIT Expat][1]<br />
 Version: 0.2.0-beta<br />
 [![Build Status](https://travis-ci.org/elcodedocle/marketo-soap-api-php-client.svg?branch=master)](https://travis-ci.org/elcodedocle/marketo-soap-api-php-client)<br />
 [![Code Climate](https://codeclimate.com/github/elcodedocle/marketo-soap-api-php-client.png)](https://codeclimate.com/github/elcodedocle/marketo-soap-api-php-client)

### Motivation

I was working on a project which, after doubling in functionality from the 
specifications of the first release, needed some serious tidying up of the API 
calling code but had no way to justify the work of rewriting it all to use calls
to an API client module instead, other than:

 - It's cleaner. 
 - It's modular.
 - Less complex. 
 - Extendable. 
 - Scalable. 
 - Reusable. 
 - It saves quite a few lines of code. 
 
Since there was no API client implementing the methods I needed and considering
how useful would have been to me if somebody had published a FOS one, I decided
that, instead of charging the client for changes introducing benefits that will
not be perceived directly or immediatly, I would go and write a basic but more 
useful open source API client with wrappers for the methods that I use, then 
release it to the public in case somebody else wants to use it and maybe 
even extend it.

I started with ~150 lines of code from [Ben Ubois's work for flickerbox](
https://github.com/flickerbox/marketo/blob/master/marketo.php), 
introducing some improvements, implementation changes and design changes, 
mostly to fit my project's design and coding style guidelines. Then I added 
some extra functionality, fixed a few bugs, did some integration testing... 
And that was it: first (beta) version ready to ship.

### Requirements

 - PHP >= 5.3.0 with the SOAP extension enabled (and cURL extension for SSL 
support)
 
### Installation

You can simply add MarketoSoapClient.php to your project or, if your project
uses composer, add the dependency to your composer.json file to retrieve  
the code from packagist on install:

```json
...
"require": {
        ...
        "elcodedocle/marketo-soap-api-php-client": "0.2.*@beta"
    }
...
```

### Usage example

Create a MarketoSoapApiClient object:

```php
require_once 'route/to/MarketoSoapApiClient.php';

use au\com\hooshmarketing\marketoconnector\modules\marketosoapapiclient\MarketoSoapApiClient;

// replace with your Marketo soap endpoint (without ?WSDL at the end)
$soapEndpoint = 'https://<YOUR-MUNCHKIN-ID>.mktoapi.com/soap/mktows/2_2';

try {
    $marketoSoapApiClient = new MarketoSoapApiClient(
        '<YOUR-MARKETO-API-USER-ID>',
        '<YOUR-MARKETO-SECRET-KEY>',
        new SoapClient(
            $soapEndpoint."?WSDL", 
            MarketoSoapApiClient::buildOptionsArray($soapEndpoint)
        )
    );
} catch (SoapFault $ex){
    // Error connecting to Marketo SOAP Endpoint
    // ...
}
```

Invoke any of the implemented methods, e.g.:

```php
$leadCookie = ''; // fill in with some lead cookie value you want to test

var_export(
    $marketoSoapApiClient->getLeadBy(
        'COOKIE',
        $leadCookie
    )
);
```

will echo the processed lead obtained for `$leadCookie`. 

(Check the phpdoc on MarketoSoapApiClient.php for info on the implemented 
methods and their parameters)

### TODO:

- Implement wrappers for all the missing methods. (This API implements 
wrappers for only 5 out of the 23 methods marketo SOAP API provides: getLead, 
syncLead, getCampaignsForSource, requestCampaign and scheduleCampaign. I don't 
need to use any more methods right now, but I'll be implementing more as I need
them, and any requests will be considered and implemented in order of 
popularity, so don't hessitate on opening/+1 a ticket or a pull request if you 
like this code and would like to request some particular extension or implement
one yourself and have it merged into this project).

### Acks

[Ben Ubois](https://github.com/benubois), the developer behind 
[Marketo](https://github.com/flickerbox/marketo), 
"A PHP client for the Marketo SOAP API"


Enjoy!

(

And, if you're happy with this product, donate! 

bitcoin: 1DMD3ymSTKoe16kNme87UnYcrXyZdkWSjD 

dogecoin: D9jDo3XPyALJH63N39wct6eDSeaL4ba5QB 

paypal: http://goo.gl/28iuK3

)

[1]: https://raw.githubusercontent.com/elcodedocle/marketo-soap-api-php-client/master/LICENSE
