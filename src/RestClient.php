<?php

namespace Stephenmudere\Mobipaid;

class RestClient
{
    /**
     * @var string
     */
    const HTTP_GET = 'GET';
    /**
     * @var string
     */
    const HTTP_POST = 'POST';
    /**
     * @var string
     */
    private $baseRestUri;
    private $apiToken;
    private $client;
    private $apiId;
    private $apiSecret;

    /**
     * Create a new API connection
     *
     * @param string $apiToken The token found on your integration
     */
    public function __construct($details)
    {
        $this->client = new \GuzzleHttp\Client;
        //$this->apiId = $apiId;
        $this->apiToken = $details->mode=='test'?$details->test_key:$details->live_key;
        $this->baseRestUri = $details->mode=='test'?"https://test.mobipaid.io/v2/":"https://live.mobipaid.io/v2/";
    }

    
    /**
     * Submit a Payment Request
     *
     * @link https://docs.mobipaid.com/
     * @param array $options
     * @return array
     */
    public function payment_requests(array $options) : array
    {
        $data=$this->curl_test($options,'payment_requests',static::HTTP_POST);
        dd($data);
        dd([
            'json' => $options,
            'http_errors' => true,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);
        $response = $this->client->request(static::HTTP_POST, $this->baseRestUri . 'payment_requests', [
            'json' => $options,
            'http_errors' => true,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);

        return ( $this->getResponse((string) $response->getBody()));
    }

    /**
     * Submit API request to send SMS
     *
     * @link https://docs.mobipaid.com/
     * @param array $options
     * @return array
     */
    public function sendToGroup(array $options) : array 
    {
        $response = $this->client->request(static::HTTP_POST, $this->baseRestUri . 'GroupMessages', [
            'json' => $options,
            'http_errors' => false,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);
        return $this->getResponse((string) $response->getBody());
    }

    /**
     * Get sms credit balance
     *
     * @link https://docs.smsportal.com/reference#balance
     * @return string
     */
    public function balance() : string
    {
        $response = $this->client->request(static::HTTP_GET, $this->baseRestUri . 'Balance', [
            'http_errors' => false,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);
        $responseData = $this->getResponse((string) $response->getBody());
        return $responseData['balance'];
    }

    /**
     * Tranform response string to responseData
     *
     * @param string $responseBody
     * @return array
     */
    private function getResponse(string $responseBody) : array
    {
        return json_decode($responseBody, true);
    }

    public function curl_test(array $options,string $link,string $verb) : array
     {  //dd($this->apiToken);

        $options = array(
    "request_methods" => [ "SMS"],
    "reference_number" => "123",
    "email" => "example@example.com",
    "merchant_phone_number" => null,
    "mobile_number" => "+12345678901",
    "customer_id" => "",
    "customer_salutation" => "Mr",
    "customer_first_name" => "John",
    "customer_last_name"  => "Preston",
    "redirect_url"  => "https://mobipaid.com",
    "response_url"  => "https://mobipaid.com",
    "cancel_url"  => "https://mobipaid.com",
    "fixed_amount" => true,
    "currency" => "ZAR",
    "amount" => 1500.12,
    "tax_id" => "",
    "template_id" => "",
    "moto_enabled" => false,
    "shipping_enabled" => false,
    "send_mms_invoice" => true,
    "attach_invoice" => true,
    "invoice_url" => "https://mp-fixed-assets.s3.amazonaws.com/logo.png",
    "attach_receipt" => true,
    "receipt_file_type" => "pdf",
    "payment_type"=>"DB",
    "payment_methods" => [
        "APPLE_PAY",
        "GOOGLE_PAY",
        "NEDBANK_EFT",
        "AMEX"
    ],
    "expiry_date" => ""
);
       $auth= array(
        "Content-Type: application/json",
        "Authorization: Bearer mp_test_Rq6u5lvySttUiYeCtf2Y",//.$this->apiToken
        );
       //dd("{$this->baseRestUri}{$link}");
       $curl = \curl_init();
        \curl_setopt_array($curl, array(
        CURLOPT_URL => "{$this->baseRestUri}/{$link}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "{$verb}",
        CURLOPT_POSTFIELDS => json_encode($options),
        CURLOPT_HTTPHEADER => $auth,
        ));

        $response = \curl_exec($curl);
        dd($response);
        \curl_close($curl);
        return $this->getResponse($response);

    }
}
