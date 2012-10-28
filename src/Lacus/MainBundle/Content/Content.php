<?php

namespace Lacus\MainBundle\Content;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Content implements \IteratorAggregate
{
    private $id;

    private $url;

    private $fields = array();

    private $width;

    private $provider;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getUuid()
    {
        return $this->getProvider() . '-' . $this->getId();
    }

    public function getId()
    {
        return $this->id;
    }

    public function set($fieldName, $fieldValue)
    {
        $this->getField($fieldName)->setValue($fieldValue);
    }

    public function get($fieldName, array $options = array())
    {
        return $this->getField($fieldName)->getValue($options);
    }

    public function has($fieldName)
    {
        return isset($this->fields[$fieldName]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * @param $fieldName
     * @return AbstractSegment
     */
    public function getField($fieldName)
    {
        if (!$this->has($fieldName)) {
            throw new \Exception(sprintf('Non-existent field name specified: "%s".', $fieldName));
        }
        return $this->fields[$fieldName];
    }

    public function addField(AbstractSegment $segment)
    {
        $this->fields[$segment->getName()] = $segment;
        return $this;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    function __clone()
    {
        $fields = array();
        foreach ($this->fields as $fieldId => $field) {
            $fields[$fieldId] = clone $field;
        }
        $this->setFields($fields);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    public function getSerialized()
    {
        return base64_encode(serialize($this));
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public static function getUnserialized($serialized)
    {
        return unserialize(base64_decode($serialized));
    }
}