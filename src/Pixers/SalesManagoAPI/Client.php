<?php

namespace Pixers\SalesManagoAPI;

use GuzzleHttp\Client as GuzzleClient;
use Pixers\SalesManagoAPI\Exception\InvalidRequestException;
use Pixers\SalesManagoAPI\Exception\InvalidArgumentException;

/**
 * SalesManago API implementation.
 *
 * @author Sylwester Łuczak <sylwester.luczak@pixers.pl>
 * @author Michał Kanak <michal.kanak@pixers.pl>
 */
class Client
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * @var bool Get response as stdobject or assoc array
     */
    protected $responseInAssocArray = false;

    /**
     * Initialization.
     */
    public function __construct($clientId, $endPoint, $apiSecret, $apiKey)
    {
        $this->config = [
            'client_id' => $clientId,
            'endpoint' => rtrim($endPoint, '/') . '/',
            'api_secret' => $apiSecret,
            'api_key' => $apiKey
        ];

        foreach ($this->config as $key => $parameter)
        {
            if (empty($parameter))
            {
                throw new InvalidArgumentException($key . ' parameter is required', $parameter);
            }
        }
    }

    /**
     * Sets GuzzleClient.
     *
     * @param GuzzleClient $guzzleClient
     */
    public function setGuzzleClient(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Gets GuzzleClient.
     *
     * @return GuzzleClient
     */
    public function getGuzzleClient()
    {
        if (!$this->guzzleClient)
        {
            $this->guzzleClient = new GuzzleClient();
        }

        return $this->guzzleClient;
    }

    /**
     * Send POST request to SalesManago API.
     *
     * @param  string $method API Method
     * @param  array  $data   Request data
     *
     * @return \stdClass|array
     *
     * @see Client::setResponseInAssocArray()
     */
    public function doPost($method, array $data)
    {
        return $this->doRequest(self::METHOD_POST, $method, $data);
    }

    /**
     * Send GET request to SalesManago API.
     *
     * @param  string $method API Method
     * @param  array  $data   Request data
     *
     * @return \stdClass|array
     *
     * @see Client::setResponseInAssocArray()
     */
    public function doGet($method, array $data)
    {
        return $this->doRequest(self::METHOD_GET, $method, $data);
    }

    /**
     * Send request to SalesManago API.
     *
     * @param  string $method    HTTP Method
     * @param  string $apiMethod API Method
     * @param  array  $data      Request data
     *
     * @return \stdClass|array
     *
     * @see Client::setResponseInAssocArray()
     */
    protected function doRequest($method, $apiMethod, array $data = [])
    {
        $url = $this->config['endpoint'] . $apiMethod;
        $data = $this->mergeData($this->createAuthData(), $data);

        $response = $this->getGuzzleClient()->request($method, $url, ['json' => $data, 'debug' => true]);

        $responseContent = \GuzzleHttp\json_decode($response->getBody(), $this->responseInAssocArray);

        $is_array = is_array($responseContent);
        $is_object = is_object($responseContent);

        if (($this->responseInAssocArray && $is_array) && (!isset($responseContent['success']) || !$responseContent['success']))
        {
            throw new InvalidRequestException($method, $url, $data, $response);
        }
        elseif ($is_object)
        {
            if (!property_exists($responseContent, 'success') || !$responseContent->success)
            {
                throw new InvalidRequestException($method, $url, $data, $response);
            }
        }

        return $responseContent;
    }

    /**
     * Returns an array of authentication data.
     *
     * @return array
     */
    protected function createAuthData()
    {
        return [
            'clientId' => $this->config['client_id'],
            'apiKey' => $this->config['api_key'],
            'requestTime' => time(),
            'sha' => sha1($this->config['api_key'] . $this->config['client_id'] . $this->config['api_secret'])
        ];
    }

    /**
     * Merge data and removing null values.
     *
     * @param  array $base         The array in which elements are replaced
     * @param  array $replacements The array from which elements will be extracted
     *
     * @return array
     */
    private function mergeData(array $base, array $replacements)
    {
        return array_filter(array_merge($base, $replacements), function ($value)
        {
            return $value !== null;
        });
    }

    /**
     * @return bool
     */
    public function isResponseInAssocArray()
    {
        return $this->responseInAssocArray;
    }

    /**
     * @param bool $responseInAssocArray
     */
    public function setResponseInAssocArray($responseInAssocArray)
    {
        $this->responseInAssocArray = $responseInAssocArray;
    }
}
