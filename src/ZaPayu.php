<?php

namespace Stephenmudere\Mobipaid;

/**
 *
 */
class ZaPayu
{
    private $baseUrl;
    private $soapUsername;
    private $safeKey;
    private $soapPassword;
    private $currencyCode;
    
    public function __construct()
    {
        $this->baseUrl = config('zapayu.mode') == "test"?'https://staging.payu.co.za':'https://secure.payu.co.za';
        $this->soapUsername = config('zapayu.mode') == "test"?config('zapayu.soapusername_test'):config('zapayu.soapusername_live');
        $this->safeKey = config('zapayu.mode') == "test"?config('zapayu.safekey_test'):config('zapayu.safekey_live');
        $this->soapPassword = config('zapayu.mode') == "test"?config('zapayu.soappassword_test'):config('zapayu.soappassword_live');
        $this->currencyCode = config('zapayu.currency_code');
    }

    public function verify_card($data)
    {
        $soapWdslUrl = $this->baseUrl.'/service/PayUAPI?wsdl';
        $payuRppUrl = $this->baseUrl.'/rpp.do?PayUReference=';
        $apiVersion = 'ONE_ZERO';
        $doTransactionArray = [];
        $doTransactionArray['Api'] = $apiVersion;
        $doTransactionArray['Safekey'] = $this->safeKey;
        $doTransactionArray['TransactionType'] = 'PAYMENT';

        $doTransactionArray['AdditionalInformation']['merchantReference'] = $data['merchantReference'];
        //$doTransactionArray['AdditionalInformation']['notificationUrl'] = ''	;
        $doTransactionArray['Basket']['description'] = $data['description'];
        $doTransactionArray['Basket']['amountInCents'] = $data['amountInCents'];
        $doTransactionArray['Basket']['currencyCode'] = $this->currencyCode;
        $doTransactionArray['Customer']['merchantUserId'] = $data['merchantUserId'];
        $doTransactionArray['Customer']['email'] = $data['email'];
        $doTransactionArray['Customer']['firstName'] = $data['firstName'];
        $doTransactionArray['Customer']['lastName'] = $data['lastName'];
        $doTransactionArray['Customer']['mobile'] = $data['mobile'];
        $doTransactionArray['Customer']['regionalId'] = $data['regionalId'];
        $doTransactionArray['Customer']['countryCode'] = $data['countryCode'];
        $doTransactionArray['Creditcard']['nameOnCard'] = $data['nameOnCard'];
        //$doTransactionArray['Creditcard']['cardNumber'] = $data['amountInCents'];
        $doTransactionArray['Creditcard']['cardNumber'] = $data['cardNumber'];
        $doTransactionArray['Creditcard']['cardExpiry'] = $data['cardExpiry'];
        $doTransactionArray['Creditcard']['cvv'] = $data['cvv'];
        $doTransactionArray['Creditcard']['amountInCents'] = $doTransactionArray['Basket']['amountInCents'];

        try {
            // 1. Building the Soap array  of data to send - This will make it into the xml as described in the documentation
            $soapDataArray = [];
            $soapDataArray = array_merge($soapDataArray, $doTransactionArray);
            // 2. Creating a XML header for sending in the soap heaeder (creating it raw a.k.a xml mode)
            $headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
            $headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
            $headerXml .= '<wsse:Username>'.$this->soapUsername.'</wsse:Username>';
            $headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->soapPassword.'</wsse:Password>';
            $headerXml .= '</wsse:UsernameToken>';
            $headerXml .= '</wsse:Security>';
            $headerbody = new \SoapVar($headerXml, XSD_ANYXML, null, null, null);
            // 3. Create Soap Header.
            $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS.
            $header = new \SOAPHeader($ns, 'Security', $headerbody, true);
            // 4. Make new instance of the PHP Soap client
            $soap_client = new \SoapClient($soapWdslUrl, ["trace" => 1, "exception" => 0]);
            // 5. Set the Headers of soap client.
            $soap_client->__setSoapHeaders($header);
            // 6. Do the setTransaction soap call to PayU
            $soapCallResult = $soap_client->doTransaction($soapDataArray);
            // 7. Decode the Soap Call Result
            $returnData = json_decode(json_encode($soapCallResult), true);

            return $returnData;
        } catch (Exception $e) {
            //var_dump($e);
            //die();
            return ['error' => 1,'message' => $e->getMessage()];
        }
    }
}
