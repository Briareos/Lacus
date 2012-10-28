<?php

namespace Lacus\MainBundle\Content\Segment;

abstract class AbstractSegment
{
    protected $name;

    protected $value;

    protected $defaultOptions = array(
        'display_on_list' => true,
        'visible_on_list' => true,
        'required' => true,
    );

    protected $options;

    function __construct($name, array $options = array())
    {
        $this->name = $name;
        $availableOptions = array_merge($this->defaultOptions, $this->getDefaultOptions());

        $newOptions = array_diff_key($options, $availableOptions);
        if (count($newOptions)) {
            throw new \Exception(sprintf('Invalid options specified: "%s"; available options are: "%s".', implode('", "', $newOptions), implode('", "', array_keys($availableOptions))));
        }
        $this->options = array_merge($availableOptions, $options);
    }

    public function getDefaultOptions()
    {
        return array();
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getValue(array $options = array())
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    abstract public function getType();
}