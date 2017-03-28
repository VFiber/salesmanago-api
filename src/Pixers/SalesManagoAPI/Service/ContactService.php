<?php

namespace Pixers\SalesManagoAPI\Service;

use Pixers\SalesManagoAPI\Entitiy\Contact;
use Pixers\SalesManagoAPI\Entitiy\DetailedContact;
use Pixers\SalesManagoAPI\Entitiy\APIResponse;
use Pixers\SalesManagoAPI\Exception\FailedOperationException;
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
     * @param  Contact|DetailedContact $contactData Contact data
     *
     * @throws FailedOperationException
     *
     * @return string contactId SalesManago contact id
     */
    public function create(Contact &$contactData)
    {
        $request = $contactData->getInRequestFormat();

        $request['owner'] = $this->getOwner();

        $response = $this->client->doPost('contact/insert', $request);

        $apiResponse = APIResponse::createFromRawResponse($response, ['contactId']);

        $contactData->id = $apiResponse->getPayLoad(['contactId']);

        return $contactData->id;
    }

    /**
     * Update contact data.
     *
     * @param  Contact|DetailedContact $contactData Contact data
     *
     * @return string contactId SalesManago contact id
     */
    public function update(Contact &$contactData)
    {
        $request = $contactData->getInRequestFormat();
        $request['owner'] = $this->getOwner();

        $response = $this->client->doPost('contact/update', $request);

        $apiResponse = APIResponse::createFromRawResponse($response, ['contactId']);

        $contactData->id = $apiResponse->getPayLoad(['contactId']);

        return $contactData->id;
    }

    /**
     * Deleting contact.
     *
     * @param  Contact|DetailedContact $contactData Contact data
     *
     * @return string contactId SalesManago contact id
     */
    public function upsert(Contact &$contactData)
    {
        $request = $contactData->getInRequestFormat();

        $request['owner'] = $this->getOwner();

        $response = $this->client->doPost('contact/upsert', $request);

        $apiResponse = APIResponse::createFromRawResponse($response, ['contactId']);

        $contactData->id = $apiResponse->getPayLoad(['contactId']);

        return $contactData->id;
    }

    /**
     * Runs a batch upsert command on the given list of contacts. If the $contacts array indexed by e-mail address,
     * the response data (contactId-s) is going to be applied on the &$contacts array.
     *
     * @param Contact[] $contacts Array of Contacts or DetailedContact. should be $contacts[$contact->email] = $contact
     *                            if you want to use the return values.
     *
     * @throws InvalidArgumentException
     * @throws InvalidResponseException
     *
     * @see Contact
     * @see DetailedContact
     *
     * @return array contactIds in associative array format -> ['email' => Contact ]
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
        $this->client->setResponseInAssocArray(true);
        $apiResponse = APIResponse::createFromRawResponse($this->client->doPost('contact/batchupsert', $requestData), ['contactIds']);
        $this->client->setResponseInAssocArray(false);

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
     * @param  bool           $permanently Is it a permanent delete
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
     * @return bool
     */
    public function useCoupon($email, $coupon)
    {
        if ($email instanceof Contact)
        {
            $email = $email->email;
        }

        $response = $this->client->doPost('contact/useContactCoupon', [
            'email' => $email,
            'coupon' => $coupon,
        ]);

        try
        {
            APIResponse::createFromRawResponse($response);
        }
        catch (FailedOperationException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * @param array $emails
     *
     * @return array
     */
    public function listByEmails(array $emails)
    {
        return $this->listContacts($emails);
    }

    /**
     * Import contacts list by the e-mail addresses.
     *
     * @param  string[]|Contact[] $contacts Request data, strings has to be e-mail adressess
     *
     * @return array
     */
    public function listContacts(array $contacts)
    {

        $request['owner'] = $this->getOwner();

        foreach ($contacts as $contact)
        {
            if ($contact instanceof Contact)
            {
                if (empty($contact->id))
                {
                    $request['email'][] = $contact->email;
                }
                else
                {
                    $request['contactId'][] = $contact->id;
                }
            }
            else
            {
                $request['email'][] = $contact;
            }
        }

        //FIXME: return values parsed and returned as Contact[]
        return $this->client->doPost('contact/list', $request);
    }

    /**
     * Import contacts list by the SalesManago IDS.
     *
     * @param  string[]|Contact[] $contactList Request data
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
        //FIXME: return values parsed and returned as Contact[]
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
