<?php
$leadAttributes = array (
    'AnonymousIP' => '10.10.10.10',
    'Company' => 'Some Company',
    'FirstName' => 'Some First Name',
    'InferredCompany' => 'Some Internet Provider',
    'InferredCountry' => 'Some Country',
    'LeadScore' => 71,
    'Marketo_Role' => 'Other',
    'Phone' => '123456786543',
);

$soapClientMockupResponse = (object)(array(
    'result' =>
        (object)(array(
            'leadId' => 1234,
            'syncStatus' =>
                (object)(array(
                    'leadId' => 1234,
                    'status' => 'UPDATED',
                    'error' => NULL,
                )),
            'leadRecord' =>
                (object)(array(
                    'Id' => 1234,
                    'Email' => 'someaddress@someserver.com',
                    'ForeignSysPersonId' => NULL,
                    'ForeignSysType' => NULL,
                    'leadAttributeList' =>
                        (object)(array(
                            'attribute' =>
                                array (
                                    0 =>
                                        (object)(array(
                                            'attrName' => 'AnonymousIP',
                                            'attrType' => 'string',
                                            'attrValue' => '10.10.10.10',
                                        )),
                                    1 =>
                                        (object)(array(
                                            'attrName' => 'Company',
                                            'attrType' => 'string',
                                            'attrValue' => 'Some Company',
                                        )),
                                    2 =>
                                        (object)(array(
                                            'attrName' => 'FirstName',
                                            'attrType' => 'string',
                                            'attrValue' => 'Some First Name',
                                        )),
                                    3 =>
                                        (object)(array(
                                            'attrName' => 'InferredCompany',
                                            'attrType' => 'string',
                                            'attrValue' => 'Some Internet Provider',
                                        )),
                                    4 =>
                                        (object)(array(
                                            'attrName' => 'InferredCountry',
                                            'attrType' => 'string',
                                            'attrValue' => 'Some Country',
                                        )),
                                    5 =>
                                        (object)(array(
                                            'attrName' => 'LeadScore',
                                            'attrType' => 'integer',
                                            'attrValue' => '71',
                                        )),
                                    6 =>
                                        (object)(array(
                                            'attrName' => 'Marketo_Role',
                                            'attrType' => 'string',
                                            'attrValue' => 'Other',
                                        )),
                                    7 =>
                                        (object)(array(
                                            'attrName' => 'Phone',
                                            'attrType' => 'phone',
                                            'attrValue' => '123456786543',
                                        )),
                                ),
                        )),
                )),
        )),
));

$expectedResponse = (object)(array(
    'leadId' => 1234,
    'syncStatus' =>
        (object)(array(
            'leadId' => 1234,
            'status' => 'UPDATED',
            'error' => NULL,
        )),
    'leadRecord' =>
        (object)(array(
            'Id' => 1234,
            'Email' => 'someaddress@someserver.com',
            'ForeignSysPersonId' => NULL,
            'ForeignSysType' => NULL,
            'attributes' =>
                array (
                    'AnonymousIP' => '10.10.10.10',
                    'Company' => 'Some Company',
                    'FirstName' => 'Some First Name',
                    'InferredCompany' => 'Some Internet Provider',
                    'InferredCountry' => 'Some Country',
                    'LeadScore' => 71,
                    'Marketo_Role' => 'Other',
                    'Phone' => '123456786543',
                ),
        )),
));