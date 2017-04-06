<?php

namespace Pixers\SalesManagoAPI\Entitiy;

/**
 * Represents a SalesManago entity
 *
 * Interface RepresentativeEntityInterface
 * @package Pixers\SalesManagoAPI\Entitiy
 */
interface ConvertableEntityInterface extends \ArrayAccess
{
    /**
     * @return array
     */
    public function getInRequestFormat();
    //FIXME: createFromArray
}