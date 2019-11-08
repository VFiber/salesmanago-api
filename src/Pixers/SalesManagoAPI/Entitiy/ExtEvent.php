<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.04.03.
 * Time: 14:56
 */

namespace Pixers\SalesManagoAPI\Entitiy;

use Pixers\SalesManagoAPI\Exception\InvalidArgumentException;

/**
 * Class ExtEvent
 *
 * @property \DateTime $date
 * @property string    $type
 * @package Pixers\SalesManagoAPI\Entitiy
 */
class ExtEvent implements ConvertableEntityInterface
{
    const EVENT_TYPE_OTHER = 'OTHER';
    const EVENT_TYPE_PURCHASE = 'PURCHASE';
    const EVENT_TYPE_CART = 'CART';
    const EVENT_TYPE_VISIT = 'VISIT';
    const EVENT_TYPE_PHONE_CALL = 'PHONE_CALL';

    const REQUEST_CREATE = 0;
    const REQUEST_UPDATE = 1;
    const REQUEST_DELETE = 2;

    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var $contact Contact
     */
    protected $contact;

    /**
     * @var string
     * @see ExtEvent::$allowedEventTypes
     */
    protected $type = ExtEvent::EVENT_TYPE_OTHER;

    /**
     * @var string max 2048 chars
     */
    public $description;

    public $location;
    /**
     * @var $value =
     */
    public $value;

    /**
     * @var string[] Product list max sum string lenght: 512
     */
    protected $products = [];

    /**
     * @var string[] Event details, e.g.: "Payment by credit card", "BANK Transaction ID: 798017289" Maximum 20 item, 255 char length per item
     */
    protected $details = [];

    /**
     * List of allowed event types by SalesManago
     *
     * @var array
     */
    protected $allowedEventTypes =
        [
            self::EVENT_TYPE_OTHER,
            self::EVENT_TYPE_CART,
            self::EVENT_TYPE_PHONE_CALL,
            self::EVENT_TYPE_PURCHASE,
            self::EVENT_TYPE_VISIT
        ];

    /**
     * @var string eventId from SalesManago
     */
    public $id;

    /**
     * @var string 255 optional event ID, eg. ID from a teller system, etc.
     */
    public $externalId;

    public function __construct(Contact $contact, \DateTime $eventDT = null, $extEventType = self::EVENT_TYPE_OTHER)
    {
        $this->setContact($contact)
             ->setDate($eventDT)
             ->setType($extEventType);
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        return (property_exists($this, $offset) ? $this->{$offset} : null);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset == 'date')
        {
            $this->setDate($value);
            return;
        }

        if ($offset == 'type')
        {
            $this->setType($value);
            return;
        }

        if (!property_exists($this, $offset))
        {
            throw new \InvalidArgumentException("Unknown contact property: '" . $offset . "' in " . __CLASS__);
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset))
        {
            unset($this->{$offset});
        }
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return ExtEvent
     */
    public function setDate(\DateTime $date = null)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     *
     * @return ExtEvent
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return string Event type
     * @see ExtEvent::$allowedEventTypes
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $extEventType
     *
     * @see ExtEvent::$allowedEventTypes
     *
     * @return ExtEvent
     */
    public function setType($extEventType = self::EVENT_TYPE_OTHER)
    {
        if (!in_array($extEventType, $this->allowedEventTypes))
        {
            throw new \InvalidArgumentException("ExtEventType '" . $extEventType . "' invalid, it has to be: " . implode(', ', $this->allowedEventTypes) . " in" . __CLASS__);
        }

        $this->type = $extEventType;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedEventTypes()
    {
        return $this->allowedEventTypes;
    }

    /**
     * @return \string[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param string $product
     *
     * @return $this
     * @throws \Exception
     */
    public function addProduct($product)
    {
        $this->products[] = $product;

        if (mb_strlen(implode(', ', $this->products)) >= 255)
        {
            throw new \Exception("Product list length exceeds 255 chars");
        }

        return $this;
    }

    /**
     * @param string $product removes product from list
     *
     * @return bool
     */
    public function removeProduct($product)
    {
        $key = array_search($product, $this->products);

        if ($key === false)
        {
            return false;
        }

        unset($this->products[$key]);

        return true;
    }

    /**
     * @param $detail
     */
    public function addDetail($detail)
    {
        if (count($this->details) >= 20)
        {
            throw new \Exception("Maximum 20 detail allowed per event.");
        }

        $this->details[] = $detail;

        return $this;
    }

    public function removeDetail($detail)
    {
        $key = array_search($detail, $this->details);

        if ($key === false)
        {
            return false;
        }

        unset($this->details[$key]);

        return true;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return ExtEvent
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     *
     * @return ExtEvent
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return ExtEvent
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $eventId from SalesManago
     *
     * @return ExtEvent
     */
    public function setId($eventId)
    {
        $this->id = $eventId;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     *
     * @return ExtEvent
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getInRequestFormat($requestType = self::REQUEST_CREATE)
    {
        $response = [];

        if ($requestType == self::REQUEST_DELETE)
        {
            if (empty($this->id))
            {
                throw new InvalidArgumentException("Invalid request type 'REQUEST_CREATE', when no SalesManago eventId was provided. ", $this);
            }
            $response['eventId'] = $this->id;

            return $response;
        }

        if (!$requestType && empty($this->date))
        {
            throw new InvalidArgumentException("At least date and event type required for " . __CLASS__ . " to create a request object. ", $this);
        }

        if ($this->contact->email)
        {
            $response['email'] = $this->contact->email;
        }

        if ($this->contact->id)
        {
            $response['contactId'] = $this->contact->id;
        }

        if ($this->id)
        {
            $response['contactEvent']['eventId'] = $this->id;
        }

        $response['contactEvent']['date'] = $this->formatDateTime($this->date);

        if (!empty($this->description))
        {
            $response['contactEvent']['description'] = $this->description;
        }

        if (!empty($this->value))
        {
            $response['contactEvent']['value'] = $this->value;
        }

        $response['contactEvent']['contactExtEventType'] = $this->type;

        if (!empty($this->products))
        {
            $response['contactEvent']['products'] = implode(', ', $this->products);
        }

        if (!empty($this->location))
        {
            $response['contactEvent']['location'] = $this->location;
        }

        if (!empty($this->details))
        {
            foreach ($this->details as $key => $detail)
            {
                $response['contactEvent']['detail' . ($key + 1)] = $detail;
            }
        }
        if (!empty($this->externalId))
        {
            $response['contactEvent']['externalId'] = $this->externalId;
        }

        return $response;
    }

    private function formatDateTime(\DateTime $dt)
    {
        return $dt->format('U.u') * 1000;
    }
}