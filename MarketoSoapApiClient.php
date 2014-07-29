<?php
/**
 * Core file of the Marketo SOAP API Client project
 * 
 * @category   SOAP_API_Client
 * @package    au\com\hooshmarketing\marketoconnector\modules\marketosoapapiclient
 * @author     Gael Abadin
 * @version    v0.2.0-beta
 * @copyright  2014 Gael Abadin
 * @license    MIT Expat http://en.wikipedia.org/wiki/Expat_License
 * @link       http://github.com/elcodedocle/marketo-soap-api-client
 * 
 */

namespace au\com\hooshmarketing\marketoconnector\modules\marketosoapapiclient;

use \stdClass;
use \SoapClient;
use \SoapHeader;
use \SoapFault;
use \Exception;
use \DateTimeZone;
use \DateTime;

/**
 * Class MarketoSoapApiClient
 *
 * Provides an interface to call various Marketo SOAP API methods.
 *
 * @category   SOAP_API_Client
 * @package    au\com\hooshmarketing\marketoconnector\modules\marketosoapapiclient
 * @author     Gael Abadin
 * @version    v0.2.0-beta
 * @copyright  2014 Gael Abadin
 * @license    MIT Expat http://en.wikipedia.org/wiki/Expat_License
 * @link       http://github.com/elcodedocle/marketo-soap-api-client
 *
 */
class MarketoSoapApiClient {
    
    /**
     * @var string Marketo User Id
     */
    protected $userId;

    /**
     * @var string Marketo Secret API Key
     */
    protected $secretKey;

    /**
     * @var string Marketo SOAP Namespace ( E.g. http://www.marketo.com/mktows/ )
     */
    protected $namespace;

    /**
     * @var SoapClient Marketo SOAP Client
     */
    protected  $soapClient;

    /**
     * @var DateTimeZone A DateTimeZone object for signing and scheduling
     */
    protected $dateTimeZone;

    /**
     * @var array Marketo SOAP Client and SOAP Client Call options
     */
    protected  $options;

    /**
     * Sets and returns Marketo SOAP API Client options
     *
     * @param string $soapEndpoint Marketo SOAP API end point URL
     * @param int $connectionTimeout
     * @param boolean $debug whether or not to include debug info on Marketo 
     * SOAP API method responses (defaults to false)
     * @return array an array of options for creating a PHP SoapClient object
     */
    public static function buildOptionsArray(
        $soapEndpoint,
        $connectionTimeout = 20,
        $debug = false
    ){

        $options = array(
            "connection_timeout" => $connectionTimeout,
            "location" => $soapEndpoint
        );

        if ($debug){
            $options["trace"] = true;
        }
        
        return $options;
        
    }

    /**
     * Sets required Marketo SOAP API parameters.
     *
     * @param string $userId
     * @param string $secretKey
     * @param SoapClient $soapClient
     * @param array $options
     * @param string $namespace
     * @param null|DateTimeZone $dateTimeZone
     */
    public function __construct(
        $userId, 
        $secretKey, 
        $soapClient,
        $options = array(),
        $namespace = 'http://www.marketo.com/mktows/', 
        $dateTimeZone = null
    ){
        
        $this->userId = $userId;
        $this->secretKey = $secretKey;
        $this->soapClient = $soapClient;
        $this->namespace = $namespace;
        if ($dateTimeZone !== null){
            $this->dateTimeZone = $dateTimeZone;
        } else {
            $this->dateTimeZone = new DateTimeZone('America/Los_Angeles');
        }
        
    }
    
    /**
     * Returns Marketo SOAP API required request signature for including in 
     * SOAP message header
     *
     * @return array a string containing a signature for Marketo SOAP API SOAP 
     * message header
     */
    protected function createMarketoSoapHeaderSignature(){
        
        $dtObj  = new DateTime('now', $this->dateTimeZone);
        $timeStamp = $dtObj->format(DATE_W3C);
        $encryptString = $timeStamp . $this->userId;
        
        $signature['hash'] = hash_hmac(
            'sha1', 
            $encryptString, 
            $this->secretKey
        );
        $signature['timeStamp'] = $timeStamp;
        
        return $signature;
        
    }
    
    /**
     * Sets SOAP message header for Marketo SOAP API method call
     *
     * @return SoapHeader a SOAP message header for the SoapClient Marketo SOAP
     * API method call
     */
    protected function createMarketoSoapHeader(){
        
        $signature = $this->createMarketoSoapHeaderSignature(
            $this->userId, 
            $this->secretKey
        );
        $attrs = new stdClass();
        $attrs->mktowsUserId = $this->userId;
        $attrs->requestSignature = $signature['hash'];
        $attrs->requestTimestamp = $signature['timeStamp'];
        
        $soapHeader =  new SoapHeader(
            $this->namespace, 
            'AuthenticationHeader', 
            $attrs
        );
        
        return $soapHeader;
        
    }

    /**
     * Sets and returns Marketo SOAP API params for method call
     *
     * @param string $type
     * @param string $value 
     * @return array an array of parameters to construct a Marketo SOAP API 
     * getLead method call
     */
    protected function createMarketoGetLeadParams($type, $value){
        
        $leadKey = array("keyType" => $type, "keyValue" => $value);
        $leadKeyParams = array("leadKey" => $leadKey);
        return array("paramsGetLead" => $leadKeyParams);
        
    }

    /**
     * Build a lead object for syncing
     *
     * @param array $leadAttributes Associative array of lead attributes
     * @param null|string $leadKey Optional, The key being used to identify the
     * lead, either an email or Marketo ID
     * @return stdClass an object with the prepared lead
     */
    public function buildLeadRecord($leadAttributes, $leadKey = null){

        $record = new stdClass;

        // Identify the lead if it is known
        if ($leadKey){
            if (is_numeric($leadKey)){
                $record->Id = $leadKey;
            } else {
                $record->Email = $leadKey;
            }
        }

        $record->leadAttributeList = new stdClass;
        $record->leadAttributeList->attribute = array();

        foreach ($leadAttributes as $attribute => $value){
            $type = null;

            // Booleans have to be '1' or '0'
            if (is_bool($value))
            {
                $value = strval(intval($value));
                $type = 'boolean';
            }

            $lead_attribute = new stdClass;
            $lead_attribute->attrName  = $attribute;
            $lead_attribute->attrValue = $value;
            $lead_attribute->attrType  = $type;

            array_push($record->leadAttributeList->attribute, $lead_attribute);
        }

        return $record;

    }

    /**
     * Format Marketo lead object into something easier to work with
     *
     * @param object $result The result of a get_lead call
     * @param bool $flattenAttributes (defaults to true)
     * @return array An array of formatted lead objects
     */
    protected function formatLeads($result, $flattenAttributes = true){

        $leads = array();

        // One record comes back as an object but two comes as an array of 
        // objects, just make them both arrays of objects
        if (is_object($result->result->leadRecordList->leadRecord)){

            $result->result->leadRecordList->leadRecord = array(
                $result->result->leadRecordList->leadRecord
            );

        }

        if ($flattenAttributes){
            foreach ($result->result->leadRecordList->leadRecord as $lead){
    
                $lead->attributes = $this->flattenAttributes(
                    $lead->leadAttributeList->attribute
                );
                unset($lead->leadAttributeList);
    
                array_push($leads, $lead);
    
            }
        }

        return $leads;

    }

    /**
     * Helper for formatLeads. Formats attribute objects to a simple
     * associative array
     *
     * @param array $attributes Attribute objects from a get_lead call
     * @return array A flattened array of attributes
     */
    protected function flattenAttributes($attributes){

        $types = array('integer', 'string', 'boolean', 'float');
        $flattenedAttributes = array();

        foreach ($attributes as $attribute){
            if (is_object($attribute)){
                if (in_array($attribute->attrType, $types)){
                    // Cast marketo type to supported php types
                    settype($attribute->attrValue, $attribute->attrType);
                }
                $flattenedAttributes[$attribute->attrName] =
                    $attribute->attrValue;
            }
        }

        return $flattenedAttributes;

    }

    /**
     * Gets an array of one or more leads from a lead cookie, lead id or lead
     * email address
     *
     * @param string $type 'COOKIE', 'IDNUM' or 'EMAIL'
     * @param string $value tracking cookie, email address or lead id value
     * @param bool $flattenAttributes (defaults to true) whether to process the 
     * result leads through flattenAttributes
     * @throws Exception
     * @throws SoapFault
     * @return bool|array a Marketo lead object or false on lead not found
     */
    public function getLeadBy($type, $value, $flattenAttributes = true){
        
        $params = $this->createMarketoGetLeadParams($type, $value);
        $header = $this->createMarketoSoapHeader();
        
        try {
            $leads = $this->soapClient->__soapCall(
                'getLead', 
                $params, 
                $this->options, 
                $header
            );
            
            return $this->formatLeads($leads, $flattenAttributes);
            
        } catch(SoapFault $ex) {
            if (
                isset($ex->detail) 
                && $ex->detail->serviceException->code === '20103'
            ){
                return false;
            }
            error_log ("Error Accessing Marketo SOAP API: ".$ex->getMessage());
            throw ($ex);
        }
        
    }

    /**
     * Create or update lead information
     *
     * Examples
     *
     * When no $lead_key or $cookie is given a new lead will be created
     *
     * `$client->syncLead(array('Email' => 'ben@benubois.com'));`
     *
     * When a $leadKey or $cookie is specified, Marketo will attempt to
     * identify the lead and update it
     *
     * `$client->syncLead(
     *     array('Unsubscribed' => false),
     *     'ben@benubois.com', $_COOKIE['_mkto_trk']
     * );`
     *
     * @param array $leadAttributes Associative array of lead attributes
     * @param null|string $leadKey Optional, The key being used to identify the
     * lead, this can be either an email or Marketo ID
     * @param null|string $cookie Optional, The entire _mkto_trk cookie the
     * lead will be associated with
     * @param bool $flattenAttributes (defaults to true)
     * @return object An object containing the lead info
     */
    public function syncLead(
        $leadAttributes, 
        $leadKey = null, 
        $cookie = null, 
        $flattenAttributes = true
    ){
        
        $params = new stdClass;
        $params->marketoCookie = $cookie;
        $params->returnLead = true;
        $params->leadRecord = $this->buildLeadRecord(
            $leadAttributes, 
            $leadKey
        );

        $result = $this->soapClient->__soapCall(
            'syncLead',
            array("paramsSyncLead" => $params),
            $this->options,
            $this->createMarketoSoapHeader()
        );
        
        $result = $result->result;
        if ($flattenAttributes){
            $result->leadRecord->attributes = $this->flattenAttributes(
                $result->leadRecord->leadAttributeList->attribute
            );
            unset($result->leadRecord->leadAttributeList);
        }

        return $result;
        
    }
    
    /**
     * Get available campaigns
     * 
     * @param null|string $name Optional, the exact name of the campaign to get
     * @return object An object containing all available campaigns
     */
    public function getCampaigns($name = null){
        
        $params = new stdClass;
        $params->source = 'MKTOWS';

        if ($name !== null){
            $params->name = $name;
            $params->exactName = true;
        }

        return $this->soapClient->__soapCall(
            'getCampaignsForSource',
            array($params),
            $this->options,
            $this->createMarketoSoapHeader()
        );
        
    }

    /**
     * Run an existing smart campaign on a list of existing leads
     *
     * The available lead id types are:
     *
     * - IDNUM - The Marketo lead ID
     * - SFDCCONTACTID - The Salesforce Contact ID
     * - SFDCLEADID - The Salesforce Lead ID
     *
     * Examples
     *
     * Add one lead to a campaign
     *
     *     $client->runCampaignOnLeads(123, array('IDNUM' => '123456'));
     *
     * Add multiple leads to a campaign with mixed id types
     *
     *     $leads = array(
     *        array('IDNUM' => '123456'),
     *        array('SFDCLEADID' => '001d000000FXkBt')
     *     );
     *     $client->runCampaignOnLeads(123, $leads);
     *
     * @param int|string $campaignKey Wither the campaign id or the campaign
     * name
     * @param array $leads associative array of lead values using lead ids as
     * keys
     * @param null|array $tokens (optional) The array of tokens
     * @return bool true if successful, false if not and no exception is thrown
     */
    public function runCampaignOnLeads($campaignKey, $leads, $tokens = null){
        
        $leadKeys = array();
        foreach ($leads as $key => $element){
            if (is_array($element)){
                reset($element);
                $type = key($element);
                $value = current($element);
            } else {
                $type = $key;
                $value = $element;
            }

            $leadKey = new stdClass;
            $leadKey->keyType  = strtoupper($type);
            $leadKey->keyValue = $value;

            array_push($leadKeys, $leadKey);
        }

        $params  = new stdClass;
        $params->leadList = $leadKeys;
        $params->source = 'MKTOWS';
        
        if ($tokens !== null){
            $params->programTokenList = array("attrib"=>$tokens);
        }

        if (is_numeric($campaignKey)){
            $params->campaignId = $campaignKey;
        } else {
            $params->campaignName = $campaignKey;
        }

        return $this->soapClient->__soapCall(
            'requestCampaign',
            array($params),
            $this->options,
            $this->createMarketoSoapHeader()
        );
        
    }

    /**
     *
     * Marketo Campaign Scheduler
     *
     * It will schedule an existing Marketo campaign, setting the campaign's
     * tokens to the provided values
     *
     * How to build the tokens array:
     *
     * ```
     * $tokens = array();
     * $token = new stdClass();
     * $token->name = '{{my.post name}}'; // token id, must be an existing
     * token for the scheduled campaign
     * $token->value = 'Gael Abadin'; // the token value
     * $tokens[] = $token;
     * ```
     *
     * @param string $programName program name (must be an existing program)
     * @param string $campaignName campaign name (must be an existing campaign
     * of the program)
     * @param array $tokens values for campaign tokens (must be existing tokens
     * of the campaign)
     * @param null|DateTime $runAt when to run the scheduled campaign
     * @throws Exception
     * @throws SoapFault
     * @return bool true on success (campaign scheduled), false otherwise
     * (campaign not scheduled because post was already published, or post tags
     * contained a block scheduling tag set in plugin settings, or campaign was
     * not found or any Marketo SOAP API call error)
     */
    public function scheduleCampaign(
        $programName,
        $campaignName,
        $tokens,
        $runAt = null
    ){

        if (!is_array($tokens)){
            throw new Exception('Marketo token values $tokens must be array');
        }

        if ($runAt === null){
            $runAt = new DateTime('now',$this->dateTimeZone);
        }

        // Create Request
        $header = $this->createMarketoSoapHeader();

        // Call Marketo SOAP API method
        $params = new stdClass();
        $params->programName = $programName;
        $params->campaignName = $campaignName;
        $params->campaignRunAt = $runAt->format(DATE_W3C);

        $params->programTokenList = array("attrib"=>$tokens);
        $params = array("paramsScheduleCampaign" => $params);
        try {
            $response = $this->soapClient->__soapCall(
                'scheduleCampaign',
                $params,
                $this->options,
                $header
            );
        }
        catch (SoapFault $ex){
            error_log (
                "Marketo SOAP API Error Scheduling Campaign: "
                .$ex->getMessage()
            );
            throw $ex;
        }
        if (
            isset($this->options['trace'])
            && $this->options['trace'] === true
        ){
            error_log(var_export($response, true));
            /*
             * error_log (
             *  "RAW request:\n" 
             *  .$this->soapClient->__getLastRequest() 
             *  ."\n"
             * );
            */
            error_log (
                "RAW response:\n"
                .$this->soapClient->__getLastResponse()
                ."\n"
            );
        }

        return true;

    }
    
}
