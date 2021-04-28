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
        $this->apiToken = $details->mode == 'test'?$details->test_key:$details->live_key;
        $this->baseRestUri = $details->mode == 'test'?"https://test.mobipaid.io/v2/":"https://live.mobipaid.io/v2/";
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
        $response = $this->client->request(static::HTTP_POST, $this->baseRestUri . 'payment-requests', [
            'json' => $options,
            'http_errors' => false,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken],
        ]);

        return ($this->getResponse((string) $response->getBody()));
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
}
