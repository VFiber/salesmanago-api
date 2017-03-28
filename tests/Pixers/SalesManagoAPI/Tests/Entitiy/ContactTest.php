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

class ContactTest extends TestCase
{
    protected $data = [
        'email' => 'info@salesmanago.pl',
        'contactId' => '21c252a6-6de0-436b-bae8-9d0142363266'
    ];

    public function testInstance()
    {
        $c = new Contact($this->data['email'], $this->data['contactId']);
        $this->assertEquals(['contact' => $this->data], $c->getInRequestFormat());

        $data['contact'] = ['email' => $this->data['email']];

        $c = new Contact($this->data['email']);
        $this->assertEquals($data, $c->getInRequestFormat());

        $data['contact'] = ['contactId' => $this->data['contactId']];
        $c = new Contact(null, $this->data['contactId']);
        $this->assertEquals($data, $c->getInRequestFormat());
    }

}