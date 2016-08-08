Marketo SOAP API PHP (micro-) Client
====================================

 Copyright (C) 2014-2016 Gael Abadin<br/>
 License: [MIT Expat][1]<br />
 Version: 0.4.0-beta<br />
 [![Build Status](https://travis-ci.org/elcodedocle/marketo-soap-api-php-client.svg?branch=master)](https://travis-ci.org/elcodedocle/marketo-soap-api-php-client)<br />
 [![Code Climate](https://codeclimate.com/github/elcodedocle/marketo-soap-api-php-client.png)](https://codeclimate.com/github/elcodedocle/marketo-soap-api-php-client)

Unofficial PHP client for the Marketo.com SOAP API: http://developers.marketo.com/documentation/soap/. Requires PHP 5.3.0+ with the SOAP extension enabled (and cURL extension for SSL support)

### Installation

The recommended way of installing the client is via [Composer](http://getcomposer.org/). Simply run the following command to add the library to your composer.json file.

    composer require elcodedocle/marketo-soap-api-php-client

Alternatively, you can simply add MarketoSoapClient.php to your project.

### Usage example

Create a MarketoSoapApiClient object:

```php
require_once 'route/to/MarketoSoapApiClient.php';

use CodeCrafts\MarketoSoap\MarketoSoapApiClient;

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

(Check the phpdoc on MarketoSoapApiClientInterface.php for info on the implemented
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

bitcoin: 1DMD3ymSTKoe16kNme87UnYcrXyZdkWSjD

dogecoin: D9jDo3XPyALJH63N39wct6eDSeaL4ba5QB

paypal: http://goo.gl/28iuK3

)

[1]: https://raw.githubusercontent.com/elcodedocle/marketo-soap-api-php-client/master/LICENSE
