<?php

namespace Lacus\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Lacus\MainBundle\Mapper\MapperDataTree;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Validator\Constraint;

class MapperDataValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }
        try {
            $value = Yaml::parse($value);
        } catch (\Exception $e) {
            $this->context->addViolation($constraint->yamlInvalid, array('{{ error }}' => $e->getMessage()));
        }

        try {
            MapperDataTree::filter($value);
        } catch (\Exception $e) {
            $this->context->addViolation($constraint->formatInvalid, array('{{ error }}' => $e->getMessage()));
        }
    }

}