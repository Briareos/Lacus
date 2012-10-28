<?php

namespace Lacus\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class MapperData extends Constraint
{
    public $yamlInvalid = 'Yaml data passed is invalid: {{ error }}.';

    public $formatInvalid = 'Given mapper data is invalid: {{ error }}.';
}