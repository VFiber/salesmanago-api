<?php

namespace Pixers\SalesManagoAPI;

use Pixers\SalesManagoAPI\Client;
use Pixers\SalesManagoAPI\Service;

/**
 * SalesManago Services Locator.
 *
 * @author Sylwester Łuczak <sylwester.luczak@pixers.pl>
 */
class SalesManago
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $services;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->services = [];
    }

    /**
     * @return Service\ContactService
     */
    public function getContactService()
    {
        return $this->getService(Service\ContactService::class);
    }

    /**
     * @return Service\CouponService
     */
    public function getCouponService()
    {
        return $this->getService(Service\CouponService::class);
    }

    /**
     * @return Service\EmailService
     */
    public function getEmailService()
    {
        return $this->getService(Service\EmailService::class);
    }

    /**
     * @return Service\EventService
     */
    public function getEventService()
    {
        return $this->getService(Service\EventService::class);
    }

    /**
     * @return Service\MailingListService
     */
    public function getMailingListService()
    {
        return $this->getService(Service\MailingListService::class);
    }

    /**
     * @return Service\PhoneListService
     */
    public function getPhoneListService()
    {
        return $this->getService(Service\PhoneListService::class);
    }

    /**
     * @return Service\RuleService
     */
    public function getRuleService()
    {
        return $this->getService(Service\RuleService::class);
    }

    /**
     * @return Service\SystemService
     */
    public function getSystemService()
    {
        return $this->getService(Service\SystemService::class);
    }

    /**
     * @return Service\TagService
     */
    public function getTagService()
    {
        return $this->getService(Service\TagService::class);
    }

    /**
     * @return Service\TaskService
     */
    public function getTaskService()
    {
        return $this->getService(Service\TaskService::class);
    }

    /**
     * @param  string $className
     * @return mixed
     */
    protected function getService($className)
    {
        if (!isset($this->services[$className])) {
            $this->services[$className] = new $className($this->client);
        }

        return $this->services[$className];
    }
}
