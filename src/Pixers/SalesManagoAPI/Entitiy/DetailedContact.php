<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.07.
 * Time: 11:30
 */

namespace Pixers\SalesManagoAPI\Entitiy;

use Pixers\SalesManagoAPI\Entitiy\Contact\Address;
use Pixers\SalesManagoAPI\Entitiy\Contact\Tag;
use Pixers\SalesManagoAPI\Exception\InvalidArgumentException;


/**
 * Class Contact
 *
 * Represents a SalesManago Contact that can be used in requests towards the API and converting the response into a Entity\Contact for unified handling and management.
 *
 * @package Pixers\SalesManagoAPI\Entitiy
 */
class DetailedContact extends Contact implements \ArrayAccess
{
    /**
     * @var array
     */
    public $contact;

    /**
     * @var Tag[]
     */
    public $tags = [];

    /**
     * @var string[] keys for $tags
     */
    private $removeTags = [];

    /**
     * @var string
     */
    public $newEmail;

    /**
     * @var array Extra properties (thats not handled SalesManago by default, e.g: nickname, sex, etc.)
     */
    public $properties;

    /**
     * @var string Contact's e-mail address
     */
    public $name;

    /**
     * @var string Contact's name
     */
    public $phone;

    /**
     * @var string
     */
    public $fax;

    /**
     * @var float User SalesManago's score
     */
    public $score;

    /**
     * @var string Possible values: PROSPECT|CLIENT|PARTNER|OTHER
     */
    public $state;

    /**
     * @var bool
     */
    public $deleted;

    /**
     * @var bool
     */
    public $invalid;

    /**
     * @var int
     */
    public $birthdayYear;

    /**
     * @var int
     */
    public $birthdayMonth;

    /**
     * @var int
     */
    public $birthdayDay;

    /**
     * @var string
     */
    public $province;

    /**
     * @var string
     */
    public $mainContactOwner;

    /**
     * @var array
     */
    public $contactVisits = [];

    public $contactEvents = [];

    public $emailMessages = [];

    /**
     * @var string
     */
    public $company;

    /**
     * @var string
     */
    public $externalId;

    /**
     * @var Address
     */
    public $address;

    /**
     * @var bool Force opt-out user (not set by default)
     */
    public $forceOptOut;

    /**
     * @var bool Force opt-in user (e.g. for upsert or for a new subscription event)
     */
    public $forceOptIn;

    /**
     * @var bool User opted out on phone
     */
    public $forcePhoneOptOut;

    /**
     * @var bool User opted in on phone
     */
    public $forcePhoneOptIn;

    /**
     * @var bool User current subscription state according to SalesManago
     */
    public $optedOut;

    /**
     * @var bool User current "subscription" state according to SalesManago
     */
    public $optedOutPhone;

    /**
     * @var
     */
    public $contactFunnels;
    public $contactNotes;
    public $contactTasks;
    public $incomingEmailMessages;
    public $contactExtEvents;
    public $coupons;
    public $smsMessages;
    /**
     * @var \DateTime
     */
    public $modifiedOn;
    /**
     * @var \DateTime
     */
    public $createdOn;

    /**
     * @param array $requestArray
     *
     * @return DetailedContact
     */
    public static function createFromRequest(array $requestArray)
    {
        $t = new DetailedContact();

        if (!empty($requestArray))
        {
            if (!empty($requestArray['contact']))
            {
                foreach ($requestArray['contact'] as $field => $value)
                {
                    if (property_exists($t, $field))
                    {
                        if (is_string($value))
                        {
                            $t[$field] = $value;
                        }
                        elseif (is_null($value))
                        {
                            $t[$field] = '';
                        }
                        elseif (is_array($value) && $field == 'address')
                        {
                            $t->address = new Address($value);
                        }
                    }
                }
            }
            if (!empty($requestArray['tags']))
            {
                foreach ($requestArray['tags'] as $tag)
                {
                    $t->addTag($tag);
                }
            }
            if (!empty($requestArray['removeTags']))
            {
                $t->removeTags($requestArray['removeTags']);
            }
            if (!empty($requestArray['properties']))
            {
                foreach ($requestArray['properties'] as $name => $property)
                {
                    $t->setProperty($name, $property);
                }
            }

            foreach ($requestArray as $property => $value)
            {
                //complex types are handled above
                if (is_array($value) || is_object($value))
                {
                    continue;
                }

                switch ($property)
                {
                    case 'birthday':
                        $t->setBirthday(\DateTime::createFromFormat('Ymd', $value));
                        continue 2;
                        break;
                }
                try
                {
                    $t[$property] = $value;
                }
                catch (InvalidArgumentException $e)
                {
                    var_dump($property, $value);
                }
            }
        }

        return $t;
    }

    /**
     * @param array $responseContactFormat
     */
    public static function createFromResponse(\stdClass $responseContact)
    {
        $t = new DetailedContact();

        foreach ($responseContact as $property => $value)
        {
            $t[$property] = $value;
        }

        return $t;
    }

    public function getInRequestFormat()
    {
        $contactFields = ['email', 'name', 'phone', 'fax', 'state', 'company', 'externalId'];
        $simpleFields = ["newEmail", "forceOptIn", "forceOptOut", "forcePhoneOptIn", "forcePhoneOptOut"];

        $requestFormat = [];
        foreach ($contactFields as $fieldProp)
        {
            $fieldVal = $this[$fieldProp];
            if (!is_null($fieldVal))
            {
                $requestFormat['contact'][$fieldProp] = $fieldVal;
            }
        }

        if (!empty($this->address))
        {
            $requestFormat['contact']['address'] = $this->address->getInRequestFormat();
        }

        if (!empty($this->tags))
        {
            $requestFormat['tags'] = array_keys($this->tags);
        }

        if (!empty($this->birthdayDay))
        {
            $requestFormat['birthday'] = $this->birthdayYear . $this->birthdayMonth . $this->birthdayDay;
        }

        if (!empty($this->removeTags))
        {
            $requestFormat['removeTags'] = array_values($this->removeTags);
        }
        if (!empty($this->properties))
        {
            $requestFormat['properties'] = $this->properties;
        }

        foreach ($simpleFields as $field)
        {
            if ($this[$field] != null)
            {
                $requestFormat[$field] = $this[$field];
            }
        }

        return $requestFormat;
    }

    public function setProperty($propertyName, $propertyValue)
    {
        if (!is_numeric($propertyValue) && !is_string($propertyValue))
        {
            throw new InvalidArgumentException("Property named " . $propertyName . " is not a simple value, cannot be added to contact properties", $propertyValue);
        }

        $this->properties[$propertyName] = $propertyValue;

        return $this;
    }

    public function getProperty($propertyName)
    {
        if (empty($this->properties[$propertyName]))
        {
            return null;
        }

        return $this->properties[$propertyName];
    }

    /**
     * @param $tag string|array|\stdClass short for Contact::addTag(Tag("TagAsString"));
     *
     * @return $this
     * @throws InvalidArgumentException
     *
     * @see @Tag::__construct($tag)
     */
    public function addTag($tag)
    {
        $isTagInstance = $tag instanceof Tag;
        $isStdObject = $tag instanceof \stdClass;

        if (is_null($tag))
        {
            throw new InvalidArgumentException("Invalid tag type: ", gettype($tag));
        }

        if ($isTagInstance)
        {
            $tagName = ($isTagInstance ? $tag->tag : $tag);
        }
        elseif ($isStdObject)
        {
            $tagName = $tag->tag;
        }
        else
        {
            $tagName = $tag;
        }

        if (!array_key_exists($tagName, $this->tags))
        {
            if ($isTagInstance)
            {
                $this->tags[$tagName] = $tag;
            }
            else
            {
                $this->tags[$tagName] = new Tag($tag);
            }
        }

        return $this;
    }

    public function getTags()
    {
        return array_keys($this->tags);
    }

    public function removeTag($tag)
    {
        $this->removeTags[$tag] = $tag;
    }

    public function hasTag($tag)
    {
        $isTagInstance = $tag instanceof Tag;
        $isStdObject = $tag instanceof \stdClass;

        if ($isTagInstance || $isStdObject)
        {
            $tagName = $tag->tag;
        }
        else
        {
            $tagName = $tag;
        }

        return array_key_exists($tagName, $this->tags);
    }

    /**
     * @param string[] $tags
     */
    public function removeTags(array $tags)
    {
        foreach ($tags as $tag)
        {
            $this->removeTags[$tag] = $tag;
        }
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
        if (is_null($offset))
        {
            throw new \InvalidArgumentException("No offset was given, " . __CLASS__ . " cannot be used as an incremental numeric array");
        }

        //comes from response, has to be treated differently
        $specialTags = [
            'contactTags'
        ];

        if (!property_exists($this, $offset) && !in_array($offset, $specialTags))
        {
            throw new \InvalidArgumentException("Unknown contact property: '" . $offset . "' in " . __CLASS__);
        }

        switch ($offset)
        {
            case 'address':
                $this->address = new Address($value);
                break;
            case 'contactTags':
                foreach ($value as $tag)
                {
                    $this->addTag($tag);
                }
                break;
            case 'modifiedOn' :
                $this->modifiedOn = new \DateTime("@" . floor($value / 1000));
                break;
            case 'createdOn' :
                $this->createdOn = new \DateTime("@" . $value / 1000);
                break;
            default:
                $this->{$offset} = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset))
        {
            unset($this->{$offset});
        }
    }

    public function setBirthday(\DateTime $birthday)
    {
        $this->birthdayDay = $birthday->format('d');
        $this->birthdayYear = $birthday->format('Y');
        $this->birthdayMonth = $birthday->format('m');
    }

    /**
     * @return string
     */
    public function getNewEmail()
    {
        return $this->newEmail;

    }

    /**
     * @param string $newEmail
     */
    public function setNewEmail($newEmail)
    {
        $this->newEmail = $newEmail;
        return $this;
    }
}