<?php
/**
 * Mocks the SoapClient class to simulate Marketo SOAP API responses
 */

namespace CodeCrafts\MarketoSoap\tests\phpunit\mockups;

use \SoapFault;

/**
 * Class SoapClientMockup
 * @package CodeCrafts\MarketoSoap\tests\phpunit\mockups
 */
class SoapClientMockup {

    /**
     * @var mixed $expectedResponse
     */
    public $expectedResponse;

    /**
     * @var array $lastRequest
     */
    private $lastRequest;

    /**
     * @var string $wsdl
     */
    private $wsdl;

    /**
     * @var array $options
     */
    private $options;

    public function __construct($wsdl, $options = null){
        $this->wsdl = $wsdl;
        $this->options = $options;
    }

    /**
     * @param $function_name
     * @param array $arguments
     * @param array $options
     * @param null $input_headers
     * @param array $output_headers
     * @throws SoapFault
     * @return mixed
     */
    public function __soapCall(
        $function_name,
        $arguments,
        $options = null,
        $input_headers = null,
        &$output_headers = null
    ){
        $this->lastRequest = array(
            'function_name' => $function_name,
            'arguments' => $arguments,
            'options' => $options,
            'input_headers' => $input_headers,
            'output_headers' => $output_headers,
        );

        if ($this->expectedResponse instanceof SoapFault){
            throw $this->expectedResponse;
        }

        return $this->expectedResponse;

    }

    /**
     * @return string
     */
    public function __getLastRequest(){
        return
            "TODO: Mock the XML string containing the last SOAP request\n"
            . var_export($this->lastRequest);
    }

    /**
     * @return string
     */
    public function __getLastResponse(){
        return
            "TODO: Mock the XML string containing the last SOAP response\n"
            . var_export($this->expectedResponse);
    }

}
