<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsIsPhone extends Constraint
{
    public $message = '';

    public function validatedBy()
    {
        return static::class.'Validator';
    } 
}
