<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainsDateNotPastValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContainsDateNotPast) {
            throw new UnexpectedTypeException($constraint, ContainsDateNotPast::class);
        }

        $date = strtotime($value . ' 00:00:00');
        $today = strtotime(date('Y-m-d') . ' 00:00:00');

        $valid = $date >= $today;
        if ($valid) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}