<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2017.03.09.
 * Time: 15:20
 */

namespace Pixers\SalesManagoAPI\Entitiy\Contact;

class Tag
{
    public $tag;
    public $tagName;
    public $score;
    public $createdOn;
    public $tagWithScore;

    /**
     * Tag constructor.
     * @param string|array|\stdClass $tagData string (simple tag), array or stdClass (with proper tag properties)
     */
    public function __construct($tagData = [])
    {
        if (!empty($tagData)) {
            self::fill($this, $tagData);
        }
    }

    public static function fromResponse(\stdClass $tagData)
    {
        $t = new Tag();
        self::fill($t, $tagData);
        return $t;
    }

    protected static function fill(Tag &$tagObject, $rawData)
    {
        $isString = is_string($rawData);
        $isArray = is_array($rawData);
        $isStdClass = $rawData instanceof \stdClass;

        if (!$isString && !$isStdClass && !$isArray)
        {
            throw new \InvalidArgumentException("Tag data cannot be interpreted as array or stdClass.");
        }

        if ($isString) {
            //a single tag
            $tagObject->tag = $rawData;
            return;
        }

        foreach ($rawData as $property => $value)
        {
            if (!property_exists($tagObject, $property)) {
                throw new \InvalidArgumentException("Tag property: " . $property . " is not a valid property.");
            }
            $tagObject->{$property} = $value;
        }
    }
}