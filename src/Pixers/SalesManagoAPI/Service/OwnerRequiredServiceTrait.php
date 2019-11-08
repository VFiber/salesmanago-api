<?php

namespace Pixers\SalesManagoAPI\Service;

use Pixers\SalesManagoAPI\Exception\InvalidArgumentException;

trait OwnerRequiredServiceTrait
{
    protected $contactOwner = null;

    /**
     * Sets the owner e-mail address of the upcoming API calls.
     *
     * @param $owner string E-mail addresss
     * @return mixed $this Has to be "fluent".
     */
    public function setOwner($owner)
    {
        $this->contactOwner = $owner;

        return $this;
    }

    /**
     * @return mixed
     */
    function getOwner()
    {
        if (!$this->hasOwner())
        {
            throw new InvalidArgumentException("No owner was set before owner related call!", $this->contactOwner);
        }

        return $this->contactOwner;
    }

    /**
     * @return bool
     */
    function hasOwner()
    {
        return !empty($this->contactOwner);
    }
}