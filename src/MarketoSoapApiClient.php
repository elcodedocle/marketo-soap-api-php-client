<?php
/**
 * Core file of the Marketo SOAP API Client project
 *
 * @category   SOAP_API_Client
 * @package    CodeCrafts\MarketoSoap
 * @author     Gael Abadin
 * @version    v0.4.0-beta
 * @copyright  2014 Gael Abadin
 * @license    MIT Expat http://en.wikipedia.org/wiki/Expat_License
 * @link       http://github.com/elcodedocle/marketo-soap-api-client
 *
 */

namespace CodeCrafts\MarketoSoap;

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
 * @package    CodeCrafts\MarketoSoap
 * @author     Gael Abadin
 * @version    v0.4.0-beta
 * @copyright  2014 Gael Abadin
 * @license    MIT Expat http://en.wikipedia.org/wiki/Expat_License
 * @link       http://github.com/elcodedocle/marketo-soap-api-client
 *
 */
class MarketoSoapApiClient implements MarketoSoapApiClientInterface {

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
     * (@inheritdoc}
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
     * (@inheritdoc}
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
        $this->dateTimeZone = !empty($dateTimeZone)
            ? $dateTimeZone
            : new DateTimeZone(date_default_timezone_get());
    }

    /**
     * Determines lead key type for a given key.
     *
     * @param string $key
     *   The key to examine
     *
     * @return string
     *   Lead key type
     */
    protected function keyType($key) {
        if (filter_var($key, FILTER_VALIDATE_EMAIL)) {
            $type = 'EMAIL';
        }
        elseif (is_int($key) || (is_string($key) && ctype_digit($key))) {
            $type = 'IDNUM';
        }
        elseif (filter_var($key, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^id:.*&token:.*/')))) {
            $type = 'COOKIE';
        }
        else {
            $type = 'UNKNOWN';
        }

        return $type;
    }

    /**
     * Sends request to Marketo.
     *
     * @param string $operation
     *   The operation to execute
     * @param object $params
     *   Parameters to be sent with the request
     *
     * @return object
     *   Response object
     */
    protected function request($operation, $params) {
        return $this->soapClient->__soapCall(
            $operation, array($params), array(), $this->createMarketoSoapHeader()
        );
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

        return array(
            'hash' => hash_hmac('sha1', $encryptString, $this->secretKey),
            'timeStamp' => $timeStamp,
        );

    }

    /**
     * Sets SOAP message header for Marketo SOAP API method call
     *
     * @return SoapHeader a SOAP message header for the SoapClient Marketo SOAP
     * API method call
     */
    protected function createMarketoSoapHeader(){

        $signature = $this->createMarketoSoapHeaderSignature();
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
     * (@inheritdoc}
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
     * (@inheritdoc}
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
            if ( $this->getErrorCode($ex) === MarketoSoapError::ERR_LEAD_NOT_FOUND
            ){
                return false;
            }
            error_log ("Error Accessing Marketo SOAP API: ".$ex->getMessage());
            throw ($ex);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getLeadActivity($key, $type = NULL) {
        $lead = new stdClass();
        $lead->leadKey = new stdClass();
        $lead->leadKey->keyType = (is_null($type)) ? $this->keyType($key) : strtoupper($type);
        $lead->leadKey->keyValue = $key;
        $lead->activityFilter = new stdClass();
        $lead->startPosition = new stdClass();
        $lead->batchSize = 100;

        try {
            $result = $this->request('getLeadActivity', $lead);
            $activity = $this->prepareLeadActivityResults($result);
        }
        catch (SoapFault $e) {

            if ($this->getErrorCode($e) == MarketoSoapError::ERR_LEAD_NOT_FOUND) {
                // No leads were found.
                $activity = array();
            }
            else {
                throw new Exception($e);
            }
        }

        return $activity;
    }


    /**
     * Parses lead activity results into a more useful format.
     *
     * @param object $marketo_result
     *   SOAP response
     *
     * @return array
     *   An array of objects defining lead activity data
     */
    protected function prepareLeadActivityResults($marketo_result) {
        if ($marketo_result->leadActivityList->returnCount > 1) {
            $activity = $marketo_result->leadActivityList->activityRecordList->activityRecord;
        }
        elseif ($marketo_result->leadActivityList->returnCount == 1) {
            $activity[] = $marketo_result->leadActivityList->activityRecordList->activityRecord;
        }
        else {
            $activity = array();
        }

        foreach ($activity as &$event) {
            $event->attributes = array();
            foreach ($event->activityAttributes->attribute as $attribute) {
                $event->attributes[$attribute->attrName] = $attribute->attrValue;
            }
            unset($event->activityAttributes);
        }

        return $activity;
    }

    /**
     * (@inheritdoc}
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
     * (@inheritdoc}
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
     * (@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getFields() {
        $params = new stdClass();
        $params->objectName = 'LeadRecord';

        try {
            $result = $this->request('describeMObject', $params);
            $fields = $this->prepareFieldResults($result);
        }
        catch (Exception $e) {
            $fields = array();
        }

        return $fields;
    }


    /**
     * Converts response into a more useful structure.
     *
     * @param object $data
     *   LeadRecord object definition
     *
     * @return array
     *   Key value pairs of fields
     */
    protected function prepareFieldResults($data) {
        $fields = array();

        foreach ($data->result->metadata->fieldList->field as $field) {
            $fields[$field->name] = $field->displayName;
        }

        return $fields;
    }

    /**
     * (@inheritdoc}
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
            $runAt = new DateTime('now', $this->dateTimeZone);
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

    /**
     * Gets a SOAP exception error code.
     *
     * @see http://php.net/manual/en/class.soapfault.php
     *
     * @param SoapFault $ex
     *   The SOAP fault object.
     * @return int
     *   The error code.
     */
    protected function getErrorCode(SoapFault $ex) {
        return !empty($ex->detail->serviceException->code)
            ? $ex->detail->serviceException->code
            : 1;
    }

}
