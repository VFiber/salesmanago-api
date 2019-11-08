<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.10.
 * Time: 12:02
 */

namespace Pixers\SalesManagoAPI\Entitiy;

use Pixers\SalesManagoAPI\Exception\FailedOperationException;
use Pixers\SalesManagoAPI\Exception\InvalidResponseException;

/**
 * Class Response
 *
 * API level response container.
 *
 * @package Pixers\SalesManagoAPI\Entitiy
 *
 */
class APIResponse
{
    /**
     * @var array Holds the response object.
     */
    private $responseObject;

    private function __construcct()
    {
    }

    /**
     * Creates and makes few validations on a response got from SM raw response.
     *
     * @param array|\stdClass $data
     * @param string[]        $requiredPayloadFields e.g.: ["contactIds","otherRequiredField"]
     *
     * @return APIResponse
     * @throws FailedOperationException
     * @throws InvalidResponseException
     */
    public static function createFromRawResponse($data, array $requiredPayloadFields = [])
    {

        if (!($data instanceof \stdClass) && !is_array($data))
        {
            throw new InvalidResponseException("The given response data cannot be interpreted as valid SalesManago API response.");
        }

        $t = new APIResponse();

        if ($data instanceof \stdClass)
        {
            //ugly hack to get stuff as assoc array
            $t->responseObject = json_decode(json_encode($data),true);
        }
        else
        {
            $t->responseObject = $data;
        }

        if (!isset($t->responseObject['success']) || !isset($t->responseObject['message']))
        {
            throw new InvalidResponseException("The given response data cannot be interpreted as valid SalesManago API response: Does not contain success and message fields.");
        }

        if (!$t->responseObject['success'])
        {
            throw new FailedOperationException("API Response: " . implode(" ", $t->responseObject['message']));
        }

        if (!empty($requiredPayloadFields))
        {
            foreach ($requiredPayloadFields as $field)
            {
                $t->setRequiredPayload($field);
            }
        }

        return $t;
    }

    public function getMessage()
    {
        return $this->responseObject['message'];
    }

    public function isSuccessfull()
    {
        return $this->responseObject['success'];
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getPayLoad($field)
    {
        if (!isset($this->responseObject[$field]))
        {
            return null;
        }

        return $this->responseObject[$field];
    }

    /**
     * @return array
     */
    public function getRawResponseObject()
    {
        return $this->responseObject;
    }

    /**
     * @param string $field Simple attribute checking in response
     */
    public function setRequiredPayload($field, $allowEmpty = true)
    {
        if (!isset($this->responseObject[$field]))
        {
            throw new InvalidResponseException('The response does not contains "' . $field . '" field');
        }
        if (!$allowEmpty && empty($this->responseObject[$field]))
        {
            throw new InvalidResponseException('The response field "' . $field . '" is empty.');
        }
    }
}