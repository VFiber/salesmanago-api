<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.09.
 * Time: 17:40
 */

namespace Pixers\SalesManagoAPI\Entitiy;

/*
 * Simple contact representation for simpler contact related SalesManago requests
 */
class Contact implements \ArrayAccess
{

    public function __construct($email = null, $contactId = null)
    {
        $this->email = $email;
        $this->id = $contactId;
    }

    /**
     * @var string Contacts phone number
     */
    public $email;

    /**
     * @var string SalesMango contactId E.g.: d51bd07f-f9ab-13e6-b830-0cc97a12a4ce
     */
    public $id;

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        return (property_exists($this, $offset) ? $this->{$offset} : null);
    }

    public function offsetSet($offset, $value)
    {
        if (!property_exists($this, $offset)) {
            throw new \InvalidArgumentException("Unknown contact property: '" . $offset . "' in " . __CLASS__);
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            unset($this->{$offset});
        }
    }

    public function getInRequestFormat()
    {
        $t = [];
        foreach ($this as $property => $value) {
            if ($property == 'id') {
                //i feel sorry for this fast hack
                $property = 'contactId';
            }
            if (!is_null($value)) {
                $t['contact'][$property] = $value;
            }
        }

        return $t;
    }
}