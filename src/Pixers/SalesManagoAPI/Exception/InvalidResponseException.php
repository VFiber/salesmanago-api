<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.10.
 * Time: 12:10
 */

namespace Pixers\SalesManagoAPI\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 *  If the response cannot be interpreted as SalesManago API response (e.g.: communication error, network error, etc)
 *
 * Class InvalidResponseException
 *
 * @package Pixers\SalesManagoAPI\Exception
 */
class InvalidResponseException extends SalesManagoAPIException
{
    /**
     * @var array|null
     */
    public $request = [];
    /**
     * @var ResponseInterface
     */
    public $response = null;

    public function __construct($message = "", array $requestData = [], ResponseInterface $response = null)
    {
        parent::__construct($message);
        $this->request = $requestData;
        $this->response = $response;
    }
}