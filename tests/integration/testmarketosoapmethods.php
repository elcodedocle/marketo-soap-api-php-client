<?php
/**
 * Integration tests for getLeadBy and scheduleCampaign methods
 *
 * Don't forget to edit config.php.dist on the tests folder and save it as
 * config.php
 *
 */
use CodeCrafts\MarketoSoap\MarketoSoapApiClient;

require_once '../../MarketoSoapApiClient.php';
require_once '../config.php';

$connectionTimeout = 20; // optional, defaults to 20
$debugSoapResponse = true; // optional, defaults to true;
$options = MarketoSoapApiClient::buildOptionsArray(
    $soapEndpoint,
    $connectionTimeout,
    $debugSoapResponse
);

try {
    $soapClient = new SoapClient(
        $soapEndpoint."?WSDL",
        $options
    );
} catch (SoapFault $ex){
    error_log(
        "Error connecting to Marketo SOAP Endpoint: "
        .$ex->getMessage()
    );
    throw $ex;
}

// optional, defaults to 'http://www.marketo.com/mktows/'
$namespace = 'http://www.marketo.com/mktows/';

// optional, defaults to 'America/Los_Angeles'
$dateTimeZone = new DateTimeZone('America/Los_Angeles');

$soapExamples = new MarketoSoapApiClient(
    $userid,
    $secretkey,
    $soapClient,
    $options,
    $namespace,
    $dateTimeZone
);

echo "If you are running this script on browser see source code (CTRL+u in Chrome) for pretty formatted output\n";

echo "getLeadBy output:\n\n";

$lead = $soapExamples->getLeadBy(
    'COOKIE',
    $leadCookie
);

// output the processed lead obtained from $leadCookie
var_export($lead);

echo "\n\nsyncLead output:\n\n";

// output the processed lead returned by syncLead
var_export(
    $soapExamples->syncLead(
        $lead[0]->attributes,
        null,
        $leadCookie
    )
);

echo "\n\ngetCampaigns output:\n\n";

// output the processed lead returned by getCampaigns
var_export(
    $soapExamples->getCampaigns()
);

echo "\n\nscheduleCampaign output:\n\n";

// If ommited, $runAt will take the default value:
// new DateTime('now',$this->dateTimeZone);
$runAt = new DateTime('now',$dateTimeZone);

// output true if the campaign was scheduled, otherwise raise an Exception
var_export(
    $soapExamples->scheduleCampaign(
        $programName,
        $campaignName,
        $tokens,
        $runAt
    )
);
