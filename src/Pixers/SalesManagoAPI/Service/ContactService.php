<?php

namespace Pixers\SalesManagoAPI\Service;

use Pixers\SalesManagoAPI\Entitiy\Contact;
use Pixers\SalesManagoAPI\Entitiy\DetailedContact;
use Pixers\SalesManagoAPI\Entitiy\APIResponse;
use Pixers\SalesManagoAPI\Exception\InvalidArgumentException;
use Pixers\SalesManagoAPI\Exception\InvalidResponseException;

/**
 * @author Sylwester Łuczak <sylwester.luczak@pixers.pl>
 */
class ContactService extends OwnerRequiredAbstractService
{
    /**
     * Adding a new contact.
     *
     * @param  array $data Contact data
     *
     * @return \stdClass
     */
    public function create($data)
    {

        if ($data instanceof DetailedContact)
        {
            throw new InvalidArgumentException("Given parameter is not a valid contact array or Contact instance.", $data);
        }

        $data['owner'] = $this->getOwner();

        return $this->client->doPost('contact/insert', $data);
    }

    /**
     * Update contact data.
     *
     * @param  string $email Contact e-mail address
     * @param  array  $data  Contact data
     *
     * @return \stdClass
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
     * @param  array  $data  Contact data
     *
     * @return \stdClass
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
     * Runs a batch upsert command on the given list of contacts
     *
     * @param Contact[] $contacts Array of Contacts or DetailedContact. should be $contacts[$contact->email] = $contact if you want to use the return values.
     *
     * @see Contact
     * @see DetailedContact
     * @return array
     */
    public function batchUpsert(array &$contacts)
    {
        $requestData = [
            'owner' => $this->getOwner(),
            'upsertDetails' => []
        ];

        $canProcessReturnValues = true;

        foreach ($contacts as $key => &$contact)
        {
            if (!($contact instanceof Contact))
            {
                throw new InvalidArgumentException("BatchUpsert contact list elements has to be Contact or DetailedContact objects, got", $contact);
            }
            //checking if its indexed by mail adresses
            $canProcessReturnValues &= ($key == $contact->email);

            $requestData['upsertDetails'][] = $contact->getInRequestFormat();
        }

        //email addresses may contain chars that is not allowed in members
        $this->client->setResponseInAssocArray(false);
        $apiResponse = APIResponse::createFromRawResponse($this->client->doPost('contact/batchupsert', $requestData), ['contactIds']);
        $this->client->setResponseInAssocArray(true);

        if (!$canProcessReturnValues)
        {
            return $apiResponse->getPayLoad('contactIds');
        }

        foreach ($apiResponse->getPayLoad('contactIds') as $email => $contactId)
        {
            if (!isset($contacts[$email]))
            {
                throw new InvalidResponseException("Response contains extra adresses which was not in the request.");
            }

            $contacts[$email]->id = $contactId;
        }

        return $apiResponse->getPayLoad('contactIds');
    }

    /**
     * Deleting contact.
     *
     * @param  string|Contact $email       Contact e-mail address
     * @param  array          $permanently Is it a permanent delete
     *
     * @return \stdClass
     */
    public function delete($email, $permanently = true)
    {
        if ($email instanceof Contact)
        {
            $email = $email->email;
        }

        $data = [
            'owner' => $this->getOwner(),
            'email' => $email,
            'permanently' => $permanently
        ];

        return $this->client->doPost('contact/delete', $data);
    }

    /**
     * Checking whether the contact is already registered.
     *
     * @param  string|Contact $email Contact email address
     *
     * @return array
     */
    public function has($email)
    {
        if ($email instanceof Contact)
        {
            $email = $email->email;
        }

        return $this->client->doPost('contact/hasContact', [
            'owner' => $this->getOwner(),
            'email' => $email,
        ]);
    }

    /**
     * Registering contact use discount code.
     *
     * @param  string|Contact $email  Contact email address
     * @param  string         $coupon Coupon
     *
     * @return array
     */
    public function useCoupon($email, $coupon)
    {
        if ($email instanceof Contact)
        {
            $email = $email->email;
        }

        return $this->client->doPost('contact/useContactCoupon', [
            'email' => $email,
            'coupon' => $coupon,
        ]);
    }

    /**
     * Import contacts list by the e-mail addresses.
     *
     * @param  string[]|Contact[] $data Request data
     *
     * @return array
     */
    public function listByEmails(array $emails)
    {
        $requestEmails = [];
        foreach ($emails as $contact)
        {
            if ($contact instanceof Contact)
            {
                $requestEmails[] = $contact->email;
            }
            else
            {
                $requestEmails[] = $contact;
            }
        }
        return $this->client->doPost('contact/list', [
            'owner' => $this->getOwner(),
            'email' => $emails
        ]);
    }

    /**
     * Import contacts list by the SalesManago IDS.
     *
     * @param  int[]|Contact[] $data Request data
     *
     * @return array
     */
    public function listByIds(array $contactList)
    {
        $requestEmails = [];
        foreach ($contactList as $contact)
        {
            if ($contact instanceof Contact)
            {
                $requestEmails[] = $contact->id;
            }
            else
            {
                $requestEmails[] = $contact;
            }
        }

        return $this->client->doPost('contact/listById', [
            'owner' => $this->getOwner(),
            'contactId' => $contactList
        ]);
    }

    /**
     * Import list of last modified contacts.
     *
     * @param  \DateTime $from      Start datetime for last modification interval ($endDate - 10 minutes by default)
     * @param  \DateTime $to        End datetime of last modification interval (current datetime by default)
     * @param bool       $allVisits on true, lists visit details about pages opened by the customer in a given period (false by default)
     * @param bool       $ipDetails on true, lists the visitors IPs on visit source lists (requires allVisits)* (false by default)
     *
     * @return array
     */
    public function listRecentlyModified(\DateTime $from = null, \DateTime $to = null, $allVisits = false, $ipDetails = false)
    {
        if (empty($to))
        {
            $to = new \DateTime();
        }

        if (empty($from))
        {
            $from = $to->sub(new \DateInterval('P10M'));
        }

        $requestData =
            [
                'owner' => $this->getOwner(),
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
                'allVisits' => $allVisits
            ];

        if ($allVisits && $ipDetails)
        {
            $requestData['ipDetails'] = true;
        }

        return $this->client->doPost('contact/modifiedContacts', $requestData);
    }

    /**
     * Import data about recently active contacts.
     *
     * @param  array $data Request data
     *
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
     *
     * @return string Its inconclusive, according to API docs it requires miliseconds but in Unit tests it uses ISO 8601 date format for specifiying Datetime.
     */
    private function formatDateTime(\DateTime $dt)
    {
        return $dt->format('U') * 1000;
    }
}
