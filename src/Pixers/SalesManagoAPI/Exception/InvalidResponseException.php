<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.10.
 * Time: 12:10
 */

namespace Pixers\SalesManagoAPI\Exception;

use Exception;

/**
 *  If the response cannot be interpreted as SalesManago API response (e.g.: communication error, network error, etc)
 *
 * Class InvalidResponseException
 *
 * @package Pixers\SalesManagoAPI\Exception
 */
class InvalidResponseException extends SalesManagoAPIException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}