<?php

namespace Pixers\SalesManagoAPI\Service;

/**
 * @author Sylwester Łuczak <sylwester.luczak@pixers.pl>
 */
class ContactService extends OwnerRequiredAbstractService
{
    /**
     * Adding a new contact.
     *
     * @param  array $data Contact data
     * @return array
     */
    public function create(array $data)
    {
        $data['owner'] = $this->getOwner();

        return $this->client->doPost('contact/insert', $data);
    }

    /**
     * Update contact data.
     *
     * @param  string $email Contact e-mail address
     * @param  array $data Contact data
     * @return array
     */
    public function update($email, array $data)
    {
        $data = self::mergeData($data, [
            'owner' => $this->getOwner(),
            'email' => $email,
        ]);

        return $this->client->doPost('contact/update', $data);
    }

    /**
     * Deleting contact.
     *
     * @param  string $email Contact e-mail address
     * @param  array $data Contact data
     * @return array
     */
    public function upsert($email, array $data)
    {
        $data = self::mergeData($data, [
            'owner' => $this->getOwner(),
            'contact' => [
                'email' => $email
            ]
        ]);
        return $this->client->doPost('contact/upsert', $data);
    }

    /**
     * Deleting contact.
     *
     * @param  string $email Contact e-mail address
     * @param  array $data Client data
     * @return array
     */
    public function delete($email, array $data)
    {
        $data = self::mergeData($data, [
            'owner' => $this->getOwner(),
            'email' => $email,
        ]);

        return $this->client->doPost('contact/delete', $data);
    }

    /**
     * Checking whether the contact is already registered.
     *
     * @param  string $email Contact email address
     * @return array
     */
    public function has($email)
    {
        return $this->client->doPost('contact/hasContact', [
            'owner' => $this->getOwner(),
            'email' => $email,
        ]);
    }

    /**
     * Registering contact use discount code.
     *
     * @param  string $email Contact email address
     * @param  string $coupon Coupon
     * @return array
     */
    public function useCoupon($email, $coupon)
    {
        return $this->client->doPost('contact/useContactCoupon', [
            'email' => $email,
            'coupon' => $coupon,
        ]);
    }

    /**
     * Import contacts list by the e-mail addresses.
     *
     * @param  array $data Request data
     * @return array
     */
    public function listByEmails(array $emails)
    {
        return $this->client->doPost('contact/list', [
            'owner' => $this->getOwner(),
            'email' => $emails
        ]);
    }

    /**
     * Import contacts list by the SalesManago IDS.
     *
     * @param  array $data Request data
     * @return array
     */
    public function listByIds(array $idList)
    {
        return $this->client->doPost('contact/listById', [
            'owner' => $this->getOwner(),
            'contactId' => $idList
        ]);
    }

    /**
     * Import list of last modified contacts.
     *
     * @param  \DateTime $from Start datetime for last modification interval ($endDate - 10 minutes by default)
     * @param  \DateTime $to End datetime of last modification interval (current datetime by default)
     * @param bool $allVisits on true, lists visit details about pages opened by the customer in a given period (false by default)
     * @param bool $ipDetails on true, lists the visitors IPs on visit source lists (requires allVisits)* (false by default)
     * @return array
     */
    public function listRecentlyModified(\DateTime $from = null, \DateTime $to = null, $allVisits = false, $ipDetails = false)
    {
        if (empty($to)) {
            $to = new \DateTime();
        }

        if (empty($from)) {
            $from = $to->sub(new \DateInterval('P10M'));
        }

        $requestData =
            [
                'owner' => $this->getOwner(),
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
                'allVisits' => $allVisits
            ];

        if ($allVisits && $ipDetails) {
            $requestData['ipDetails'] = true;
        }

        return $this->client->doPost('contact/modifiedContacts', $requestData);
    }

    /**
     * Import data about recently active contacts.
     *
     * @param  array $data Request data
     * @return array
     */
    public function listRecentActivity(array $data)
    {
        return $this->client->doPost('contact/recentActivity', $data);
    }

    /**
     * Formats DateTime object to SALESManago API understandable format.
     *
     * @param \DateTime $dt Object to convert
     * @return string Its inconclusive, according to API docs it requires miliseconds but in Unit tests it uses ISO 8601 date format for specifiying Datetime.
     */
    private function formatDateTime(\DateTime $dt)
    {
        return $dt->format('u');
    }
}
