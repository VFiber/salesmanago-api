<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.09.
 * Time: 17:58
 */

namespace Pixers\SalesManagoAPI\Tests\Entitiy;

use PHPUnit\Framework\TestCase;
use Pixers\SalesManagoAPI\Entitiy\Contact;
use Pixers\SalesManagoAPI\Entitiy\ExtEvent;

class ExtEventTest extends TestCase
{
    protected $data = [
        'email' => 'info@salesmanago.pl',
        'contactId' => '21c252a6-6de0-436b-bae8-9d0142363266'
    ];

    protected $rawRequestFormat = '{
	"email": "test@benhauer.com",
	"contactEvent": {
	    "eventId":"7284e317-3bb6-4505-afbe-55b9a101339a",
		"date": 1356180568153,
		"description": "Purchase card \"Super Bonus\"",
		"products": "p01, p02",
		"location": "Krupnicza 3, Kraków",
		"value": 1234.43,
		"contactExtEventType": "PURCHASE",
		"detail1": "C.ID: *** *** 234",
		"detail2": "Payment by credit card",
		"detail3": null,
		"externalId": "A-123123123"
	}
}';

    public function testInstance()
    {
        $request = json_decode($this->rawRequestFormat, true);

        $dt = \DateTime::createFromFormat('U.u', $request['contactEvent']['date'] / 1000);

        $c = new Contact($request['email']);

        $event = new ExtEvent($c, $dt, ExtEvent::EVENT_TYPE_PURCHASE);

        $event->value = $request['contactEvent']['value'];

        $event->description = $request['contactEvent']['description'];
        $event->location = $request['contactEvent']['location'];

        foreach (explode(', ', $request['contactEvent']['products']) as $product)
        {
            $event->addProduct($product);
        }

        $event->addDetail($request['contactEvent']['detail1']);
        $event->addDetail($request['contactEvent']['detail2']);
        $event->addDetail($request['contactEvent']['detail3']);

        $event->setId($request['contactEvent']['eventId']);

        $event['type'] = "PURCHASE";

        $event->externalId = $request['contactEvent']['externalId'];

        $this->assertEquals($request, $event->getInRequestFormat());

        $this->assertEquals(['eventId' => $request['contactEvent']['eventId']], $event->getInRequestFormat(ExtEvent::REQUEST_DELETE));
    }

}