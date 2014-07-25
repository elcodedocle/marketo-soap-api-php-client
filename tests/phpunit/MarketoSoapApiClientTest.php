<?php
/**
 * Test MarketoSoapApiClient
 */


require_once 'mockups/SoapClientMockup.php';
require_once '../../MarketoSoapApiClient.php';

/**
 * Class MarketoSoapApiClientTest
 * @package au\com\hooshmarketing\marketoconnector\MarketoSoapApiClient\tests
 */
class MarketoSoapApiClientTest extends PHPUnit_Framework_TestCase {
    
    private function getMarketoSoapApiClient($soapClientMockupResponse){
        
        $soapClientMockup = new SoapClientMockup('path/to/wsdl');
        $soapClientMockup->expectedResponse = $soapClientMockupResponse;
        
        $marketoSoapApiClient = new MarketoSoapApiClient(
            'someUserId',
            'someSecretKey',
            $soapClientMockup
        );

        
        return $marketoSoapApiClient;
        
    }

    public function testGetLeadByOk(){

        $soapClientMockupResponse = null;
        $expectedResponse = null;

        // set $expectedResponse and $soapClientMockupResponse
        require "mockups/responses/getLead.php";
        
        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->getLeadBy('COOKIE','someLeadId')
        );
        
    }
    
    public function testGetLeadByNotFound(){

        $expectedResponse = false;
        $soapClientMockupResponse = new SoapFault(
            "20103",
            "20103 - Lead not found",
            null,
            (object)(array(
                'serviceException'=>
                    (object)array(
                        'code'=>'20103'
                    )
            )
            )
        );

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->getLeadBy('COOKIE','someLeadId')
        );
        
    }

    /**
     * @expectedException SoapFault
     */
    public function testGetLeadByFail(){

        $soapClientMockupResponse = new SoapFault("500","500");

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $marketoSoapApiClient->getLeadBy('COOKIE','someLeadId');
        
    }

    public function testSyncLeadOk(){

        $leadAttributes = null;
        $expectedResponse = null;
        $soapClientMockupResponse = null;

        // set $leadAttributes, $expectedResponse and $soapClientMockupResponse
        require "mockups/responses/syncLead.php";

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->syncLead($leadAttributes,1234)
        );

        // set $leadAttributes, $expectedResponse and $soapClientMockupResponse
        require "mockups/responses/syncLead.php";

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->syncLead($leadAttributes,null,'someLeadCookie')
        );

    }

    /**
     * @expectedException SoapFault
     */
    public function testSyncLeadFail(){

        $soapClientMockupResponse = new SoapFault("500","500");

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );
        $lead = $marketoSoapApiClient->buildLeadRecord(
            array(
                'FirstName'=>'Some Other First Name',
            ),
            1234
        );

        $marketoSoapApiClient->syncLead($lead,1234);

    }

    public function testGetCampaignsOk(){

        $expectedResponse = null;
        $soapClientMockupResponse = null;
        
        // set $expectedResponse and $soapClientMockupResponse
        require "mockups/responses/getCampaignsForSources.php";

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->getCampaigns()
        );

    }

    /**
     * @expectedException SoapFault
     */
    public function testGetCampaignsFail(){
        
        $soapClientMockupResponse = new SoapFault("500","500");

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $marketoSoapApiClient->getCampaigns();

    }

    public function testRunCampaignOnLeadsOk(){

        $expectedResponse = true;
        $soapClientMockupResponse = true;

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        // try adding one lead, identifying campaign by name
        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->runCampaignOnLeads(
                'someCampaignName', // the campaign name
                array('IDNUM' => '123456') // the lead id
            )
        );

        $expectedResponse = true;
        $soapClientMockupResponse = true;

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        // adding 2 leads (one id'd by SFDCLEADID), identifying campaign by id
        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->runCampaignOnLeads(
                '123', // campaign id
                $leads = array(
                    array('SFDCLEADID' => '001d000000FXkBt'), // another lead
                    array('IDNUM' => '123456') // lead id
                )
            )
        );

    }

    /**
     * @expectedException SoapFault
     */
    public function testRunCampaignOnLeadsFail(){

        $soapClientMockupResponse = new SoapFault("500","500");

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $marketoSoapApiClient->runCampaignOnLeads(
            'someCampaignName',
            array('IDNUM' => '123456')
        );
        
    }

    public function testScheduleCampaignOk(){

        $expectedResponse = true;
        $soapClientMockupResponse = true;

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $this->assertEquals(
            $expectedResponse,
            $marketoSoapApiClient->scheduleCampaign(
                'someProgramName',
                'someCampaignName',
                array()
            )
        );
        
    }

    /**
     * @expectedException SoapFault
     */
    public function testScheduleCampaignFail(){

        $soapClientMockupResponse = new SoapFault("500","500");

        $marketoSoapApiClient = $this->getMarketoSoapApiClient(
            $soapClientMockupResponse
        );

        $marketoSoapApiClient->scheduleCampaign(
            'someProgramName',
            'someCampaignName',
            array()
        );

    }
    
}
 