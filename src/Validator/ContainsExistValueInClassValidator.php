<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainsExistValueInClassValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContainsExistValueInClass) {
            throw new UnexpectedTypeException($constraint, ContainsExistValueInClass::class);
        }

        $existed = call_user_func_array('\\Pimcore\\Model\\DataObject\\'. $constraint->class .'::getBy'. ucfirst($constraint->field), [$value, ['limit' => 1,'unpublished' => false]]);

        if ($existed) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}