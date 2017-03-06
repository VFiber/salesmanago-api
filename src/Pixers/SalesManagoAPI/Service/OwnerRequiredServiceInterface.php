<?php

namespace Pixers\SalesManagoAPI\Service;

interface OwnerRequiredServiceInterface
{
    /**
     * Sets the owner of the upcoming API calls.
     *
     * @param $owner string E-mail addresss
     * @return mixed $this Has to be "fluent".
     */
    function setOwner($owner);

    /**
     * @return mixed
     */
    function getOwner();

    /**
     * @return bool
     */
    function hasOwner();
}