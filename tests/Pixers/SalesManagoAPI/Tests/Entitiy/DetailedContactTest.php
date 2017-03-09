<?php

namespace Pixers\SalesManagoAPI\Tests\Entitiy;

use PHPUnit\Framework\TestCase;
use Pixers\SalesManagoAPI\Entitiy\DetailedContact;
use Pixers\SalesManagoAPI\Tests\Service\AbstractServiceTest;

class DetailedContactTest extends TestCase
{
    protected $requestData = [
            'contact' => [
                'company' => 'Test',
                'email' => 'contactest@salesmanago.pl',
                'fax' => '000000000',
                'name' => 'John Example',
                'phone' => AbstractServiceTest::CONTACT_TEST_PHONE
            ],
            'tags' => ['TAG_1', 'TAG_2', 'TAG_3']
        ];

    /**
     * @var string Real response data in JSON format
     */
    protected $responseData = '{"success":true,"message":[],"contacts":[{"id":"d51bd57f-f9ab-11e6-b840-0cc47a1254ce","name":"Example contact name","email":"excont@mailprovider.com","phone":null,"fax":null,"score":0,"state":"PROSPECT","optedOut":false,"optedOutPhone":false,"deleted":false,"invalid":false,"company":null,"externalId":null,"address":{"streetAddress":null,"zipCode":null,"city":"New York","country":null},"birthdayYear":null,"birthdayMonth":null,"birthdayDay":null,"province":null,"mainContactOwner":"example@contactowner.com","contactVisits":[],"contactTags":[{"tag":"IGEN","tagName":"IGEN","score":1,"createdOn":1487842731000,"tagWithScore":"IGEN (1)"},{"tag":"STARTERKIT","tagName":"STARTERKIT","score":1,"createdOn":1487842730000,"tagWithScore":"STARTERKIT (1)"}],"contactEvents":[],"emailMessages":[],"properties":[{"name":"Nem","value":"N\\u0151"},{"name":"Email megnyit\\u00e1sok sz\\u00e1ma","value":"3"},{"name":"Regisztr\\u00e1ci\\u00f3 d\\u00e1tuma","value":"10\\/21\\/2015 10:06:56"}],"contactFunnels":[],"contactNotes":[],"contactTasks":[],"incomingEmailMessages":[],"contactExtEvents":[],"coupons":[],"smsMessages":[],"modifiedOn":1487842709000,"createdOn":1487842709000},{"id":"d518857f-f9ab-11e6-b840-0cc47a1254ce","name":null,"email":"justemail@contact.hu","phone":null,"fax":null,"score":0,"state":"PROSPECT","optedOut":false,"optedOutPhone":false,"deleted":false,"invalid":false,"company":null,"externalId":null,"address":null,"birthdayYear":null,"birthdayMonth":null,"birthdayDay":null,"province":null,"mainContactOwner":"example@contactowner.com","contactVisits":[],"contactTags":[{"tag":"IGEN","tagName":"IGEN","score":1,"createdOn":1487842731000,"tagWithScore":"IGEN (1)"},{"tag":"STARTERKIT","tagName":"STARTERKIT","score":1,"createdOn":1487842730000,"tagWithScore":"STARTERKIT (1)"}],"contactEvents":[],"emailMessages":[],"properties":[{"name":"Email megnyit\\u00e1sok sz\\u00e1ma","value":"5"}],"contactFunnels":[],"contactNotes":[],"contactTasks":[],"incomingEmailMessages":[],"contactExtEvents":[],"coupons":[],"smsMessages":[],"modifiedOn":1487842709000,"createdOn":1487842709000},{"id":"d51d9993-f9ab-11e6-b840-0cc47a1254ce","name":null,"email":"someone@example.com","phone":null,"fax":null,"score":0,"state":"PROSPECT","optedOut":false,"optedOutPhone":false,"deleted":false,"invalid":false,"company":null,"externalId":null,"address":null,"birthdayYear":null,"birthdayMonth":null,"birthdayDay":null,"province":null,"mainContactOwner":"example@contactowner.com","contactVisits":[],"contactTags":[{"tag":"IGEN","tagName":"IGEN","score":1,"createdOn":1487842731000,"tagWithScore":"IGEN (1)"},{"tag":"STARTERKIT","tagName":"STARTERKIT","score":1,"createdOn":1487842730000,"tagWithScore":"STARTERKIT (1)"}],"contactEvents":[],"emailMessages":[],"properties":[{"name":"Email megnyit\\u00e1sok sz\\u00e1ma","value":"1"}],"contactFunnels":[],"contactNotes":[],"contactTasks":[],"incomingEmailMessages":[],"contactExtEvents":[],"coupons":[],"smsMessages":[],"modifiedOn":1487842709000,"createdOn":1487842709000}]}';
    /**
     * @var string request data from manual in JSON format (contains every possible fields for batchupsert)
     */
    protected $batchRequestExample = '{"contact":{"email":"batchtest2@benhauer.pl","name":"Konrad Test1","phone":"+48123321123","fax":"+48345543345","state" : "PROSPECT","company":"Benhauer Sp. z o.o. Sp. K.","externalId":null,"address":{"streetAddress":"Brzyczyńska 123","zipCode":"43-305","city":"Bielsko-Biała","country":"PL"}},"tags":["API", "ADmanago"],"removeTags":["Test_tag", "New"],"properties":{"custom.nickname":"Konri1","custom.sex":"M"},"birthday": "19801017"}';
    protected $exampleUpsertData = '{"apiKey" : "your-api-key-123","clientId" : "your-client-id-123","contact" : { "company" : "Benhauer Sp. z o.o. Sp. K.","email" : "konrad-test-1@konri.com","fax" : "+48345543345","name" : "Konrad Test","phone" : "+48123321123","state" : "PROSPECT","address":{"streetAddress":"Brzyczyńska 123","zipCode":"43-305","city":"Bielsko-Biała","country":"PL"}},"owner" : "admin@vendor.pl","newEmail" : "","forceOptOut" : false,"forcePhoneOptOut" : false,"requestTime" : 1327059355361,"sha" : "08924f45afc2e4fb8b652c53cdb493c7ddb846a1","tags" : [ "API","ADmanago"],"properties":{"custom.nickname":"Konri","custom.sex":"M"},"birthday": "19801017","useApiDoubleOptIn":true,"lang":"PL"}';

    public function testFromRequestData()
    {
        $c = DetailedContact::createFromRequest($this->requestData);

        $this->assertEquals($this->requestData['tags'],$c->getTags());
        $this->assertEquals($this->requestData['contact']['company'],$c->company);
        $this->assertEquals($this->requestData, $c->getInRequestFormat());

        $requestTest = json_decode($this->batchRequestExample,true);
        $c = DetailedContact::createFromRequest($requestTest);
        $this->assertEquals($requestTest, $c->getInRequestFormat());
    }

    public function testFromResponseData()
    {
        $responseData = json_decode($this->responseData);

        $c = DetailedContact::createFromResponse($responseData->contacts[0]);
        $this->assertEquals(1487842709, $c->createdOn->format('U'));
        $this->assertEquals(1487842709, $c->modifiedOn->format('U'));
        $this->assertEquals('d51bd57f-f9ab-11e6-b840-0cc47a1254ce', $c->id);
        $this->assertEquals('excont@mailprovider.com', $c->email);
        $this->assertEquals(0, $c->score);
    }
}
