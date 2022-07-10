<?php

namespace Stephenmudere\Mobipaid;

use Log;

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
        $doTransactionArray['AuthenticationType'] = 'TOKEN';
        $doTransactionArray['AdditionalInformation']['merchantReference'] = $data['merchantReference'];
        //$doTransactionArray['AdditionalInformation']['notificationUrl'] = ''	;
        //$doTransactionArray['AdditionalInformation']['demoMode'] = 'true';
        $doTransactionArray['AdditionalInformation']['secure3d'] = 'true';
        $doTransactionArray['AdditionalInformation']['supportedPaymentMethods'] = 'CREDITCARD';
        $doTransactionArray['AdditionalInformation']['storePaymentMethod'] = 'true';
        //$payment['AdditionalInformation']['secure3d'] = 'true';
        $doTransactionArray['AdditionalInformation']['notificationUrl'] = trim(env('APP_URL'), '/').'/api/payu/payresponse';
        $doTransactionArray['AdditionalInformation']['returnUrl'] = str_replace("http://", "https://", trim(env('APP_URL'), '/').'/api/payu/payresult') ;

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
        $doTransactionArray['Customfield']['key'] = 'processingType';
        $doTransactionArray['Customfield']['value'] = 'REAL_TIME_RECURRING';

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
            if (config('zapayu.debug')) {
                $this->log_request($soap_client);
            }
            $returnData = json_decode(json_encode($soapCallResult), true);

            return $returnData;
        } catch (\Exception $e) {
            //var_dump($e);
            //die();
            return ['error' => 1,'message' => $e->getMessage()];
        }
    }

    public function card_result($data)
    {

        //$soapWdslUrl = $this->baseUrl.'/service/PayUAPI?wsdl';
        //$payuRppUrl  = $this->baseUrl.'/rpp.do?PayUReference=';
        $apiVersion = 'ONE_ZERO';
        //$doTransactionArray = array();
        //$doTransactionArray['Api'] = $apiVersion;
        //$doTransactionArray['Safekey'] = $this->safeKey;

        $soapWdslUrl = $this->baseUrl.'/service/PayUAPI?wsdl';
        $payuRppUrl = $this->baseUrl.'/rpp.do?PayUReference=';
        $apiVersion = 'ONE_ZERO';
        $safeKey = $this->safeKey;
        $soapUsername = $this->soapUsername;
        $soapPassword = $this->soapPassword;
        $payment = [];
        $payment['Api'] = $apiVersion;
        $payment['Safekey'] = $this->safeKey;
        
        $payment['AdditionalInformation']['payUReference'] = $data['PayUReference'];

        try {
            $soapDataArray = [];
            $soapDataArray['Api'] = $apiVersion;
            $soapDataArray['Safekey'] = $this->safeKey;
            $soapDataArray = array_merge($soapDataArray, $payment);
            
            $headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
            $headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
            $headerXml .= '<wsse:Username>'.$soapUsername.'</wsse:Username>';
            $headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$soapPassword.'</wsse:Password>';
            $headerXml .= '</wsse:UsernameToken>';
            $headerXml .= '</wsse:Security>';
            $headerbody = new \SoapVar($headerXml, XSD_ANYXML, null, null, null);

            $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

            $header = new \SOAPHeader($ns, 'Security', $headerbody, true);
            $soap_client = new \SoapClient($soapWdslUrl, ["trace" => 1, "exception" => 0]);
            $soap_client->__setSoapHeaders($header);
            $soapCallResult = $soap_client->gettransaction($soapDataArray);
            $return = json_decode(json_encode($soapCallResult), true);

            return $return;
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    public function log_request(\SoapClient $soap_client)
    {
        $log = "";
        if (is_object($soap_client)) {
            //print "Request Header:";
            //echo str_replace( '&gt;&lt;' , '&gt;<br />&lt;', htmlspecialchars( $httpClient->getLastRequestHeaders(), ENT_QUOTES));
            $log .= "-----------------------------------------------\r\n";
            $log .= "Request:\r\n";
            $log .= "-----------------------------------------------\r\n";
            $requestString = str_replace('&gt', '>', $soap_client->__getLastRequest());
            $requestString = str_replace('&gt', '>', $requestString);
            $requestString = str_replace('>', ">\r\n", $requestString);
            $requestString = str_replace('</', "\r\n</", $requestString);
            $requestString = str_replace("\r\n\r\n", "\r\n", $requestString);
            $requestString = str_replace("\r\n\r\n", "\r\n", $requestString);
            $log .= $requestString;
            $log .= "\r\n";
            $log .= "-----------------------------------------------\r\n";
            $log .= "Response:\r\n";
            $log .= "-----------------------------------------------\r\n";
            $responseString = str_replace('&gt', '>', $soap_client->__getLastResponse());
            $responseString = str_replace('&gt', '>', $responseString);
            $responseString = str_replace('>', ">\r\n", $responseString);
            $responseString = str_replace('</', "\r\n</", $responseString);
            $responseString = str_replace("\r\n\r\n", "\r\n", $responseString);
            $log .= $responseString;
        }
        Log::channel('payulog')->info($log);
    }
}
