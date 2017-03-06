<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.06.
 * Time: 14:59
 */

namespace Pixers\SalesManagoAPI\Service;


use Pixers\SalesManagoAPI\Client;

abstract class OwnerRequiredAbstractService extends AbstractService implements OwnerRequiredServiceInterface
{
    use OwnerRequiredServiceTrait;

    public function __construct(Client $client, $owner = '')
    {
        parent::__construct($client);
        if (!empty($owner)) {
            $this->setOwner($owner);
        }
    }
}