<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainsIsPhoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContainsIsPhone) {
            throw new UnexpectedTypeException($constraint, ContainsIsPhone::class);
        }

        $valid = is_numeric($value)
            && (substr($value, 0, 1) == '0' || substr($value, 0, 2) == '84');

        if ($valid) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}