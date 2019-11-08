<?php


namespace Pixers\SalesManagoAPI\Service;

use Pixers\SalesManagoAPI\Entitiy\APIResponse;
use Pixers\SalesManagoAPI\Entitiy\ExtEvent;

/**
 * @author Sylwester Łuczak <sylwester.luczak@pixers.pl>
 */
class EventService extends OwnerRequiredAbstractService
{
    /**
     * Creating a new external event.
     *
     * @param  string $email Contact e-mail address
     * @param  array  $data  Contact event data
     *
     * @return array
     */
    public function create(ExtEvent &$event)
    {
        $request = $event->getInRequestFormat();

        $request['owner'] = $this->getOwner();

        $response = $this->client->doPost('contact/addContactExtEvent', $request);

        $a = APIResponse::createFromRawResponse($response, ['eventId']);

        $event->setId($a->getPayLoad('eventId'));

        return $response;
    }

    /**
     * Updating external event.
     *
     * @param  string $owner   Contact owner e-mail address
     * @param  string $eventId Ext event identifier
     * @param  array  $data    New event data
     *
     * @return array
     */
    public function update(ExtEvent &$event)
    {
        $request = $event->getInRequestFormat(ExtEvent::REQUEST_UPDATE);

        $request['owner'] = $this->getOwner();

        $response = $this->client->doPost('contact/updateContactExtEvent', $request);

        $a = APIResponse::createFromRawResponse($response, ['eventId']);

        $event->setId($a->getPayLoad('eventId'));

        return $response;
    }

    /**
     * Deleting contact external event.
     *
     * @param  string $owner   Contact owner e-mail address
     * @param  string $eventId Ext event identifier
     *
     * @return array
     */
    public function delete(ExtEvent &$event)
    {
        $request = $event->getInRequestFormat(ExtEvent::REQUEST_DELETE);

        $request['owner'] = $this->getOwner();

        $response = $this->client->doPost('contact/deleteContactExtEvent', $request);

        $a = APIResponse::createFromRawResponse($response, ['result']);

        return $response;
    }
}
