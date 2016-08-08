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
 * Interface MarketoSoapApiClient
 *
 * Provides an interface to call various Marketo SOAP API methods.
 *
 * @category   SOAP_API_Client
 * @package    CodeCrafts\MarketoSoap
 * @author     Gael Abadin
 * @version    v0.2.0-beta
 * @copyright  2014 Gael Abadin
 * @license    MIT Expat http://en.wikipedia.org/wiki/Expat_License
 * @link       http://github.com/elcodedocle/marketo-soap-api-client
 *
 */
interface MarketoSoapApiClientInterface {

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
    public function __construct (
        $userId,
        $secretKey,
        $soapClient,
        $options = array(),
        $namespace = 'http://www.marketo.com/mktows/',
        $dateTimeZone = null
    );

    /**
     * Retrieves list of defined fields.
     *
     * @return array
     *   An array of lead fields defined in marketo.
     */
    public function getFields();

    /**
     * Sets and returns Marketo SOAP API Client options
     *
     * @param string $soapEndpoint Marketo SOAP API end point URL
     * @param int $connectionTimeout
     * @param boolean $debug whether or not to include debug info on Marketo
     * SOAP API method responses (defaults to false)
     * @return array an array of options for creating a PHP SoapClient object
     */
    public static function buildOptionsArray (
        $soapEndpoint,
        $connectionTimeout = 20,
        $debug = false
    );

    /**
     * Build a lead object for syncing
     *
     * @param array $leadAttributes Associative array of lead attributes
     * @param null|string $leadKey Optional, The key being used to identify the
     * lead, either an email or Marketo ID
     * @return stdClass an object with the prepared lead
     */
    public function buildLeadRecord ($leadAttributes, $leadKey = null);

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
    public function getLeadBy ($type, $value, $flattenAttributes = true);

    /**
     * Retrieves lead activity information.
     *
     * @param string $key
     *   Lead Key, typically email address.
     * @param string $type
     *   Lead key type, auto-detection attempted if not supplied.
     */
    public function getLeadActivity($key, $type);

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
    public function syncLead (
        $leadAttributes,
        $leadKey = null,
        $cookie = null,
        $flattenAttributes = true
    );

    /**
     * Get available campaigns
     *
     * @param null|string $name Optional, the exact name of the campaign to get
     * @return object An object containing all available campaigns
     */
    public function getCampaigns ($name = null);

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
    public function runCampaignOnLeads ($campaignKey, $leads, $tokens = null);

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
    public function scheduleCampaign (
        $programName,
        $campaignName,
        $tokens,
        $runAt = null
    );

}
