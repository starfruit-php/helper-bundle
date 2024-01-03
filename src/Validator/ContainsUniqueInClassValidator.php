<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainsUniqueInClassValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContainsUniqueInClass) {
            throw new UnexpectedTypeException($constraint, ContainsUniqueInClass::class);
        }

        $existed = call_user_func_array('\\Pimcore\\Model\\DataObject\\'. $constraint->class .'::getBy'. ucfirst($constraint->field), [$value, ['limit' => 1,'unpublished' => true]]);

        if (!$existed) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}