<?php

namespace Pixers\SalesManagoAPI\Exception;

use Prophecy\Exception\Exception;

/**
 * This exception is throwed when an API operation failes (got success false as a response)
 *
 * Class FailedOperationException
 *
 * @package Pixers\SalesManagoAPI\Exception
 */
class FailedOperationException extends SalesManagoAPIException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}