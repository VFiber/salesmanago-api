<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.07.
 * Time: 15:09
 */

namespace Pixers\SalesManagoAPI\Entitiy\Contact;


class Address
{

    public function __construct($addressData = [])
    {
        foreach ($addressData as $field => $value) {
            if (property_exists($this, $field) && (is_string($value) || is_numeric($value))) {
                $this->{$field} = $value;
            }
        }
    }

    public function getInRequestFormat()
    {
        $r = [];
        foreach ($this as $property => $value) {
            $r[$property] = $value;
        }

        return $r;
    }

    /**
     * @var string
     */
    public $streetAddress;
    /**
     * @var string
     */
    public $zipCode;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $country;
}