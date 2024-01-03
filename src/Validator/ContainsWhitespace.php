<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsWhitespace extends Constraint
{
    public $message = '';

	public function validatedBy()
	{
	    return static::class.'Validator';
	}
}
