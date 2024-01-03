<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsUniqueInClass extends Constraint
{
    public $message = '';

    public $class;
    public $field;

    public function validatedBy()
    {
        return static::class.'Validator';
    } 
}
