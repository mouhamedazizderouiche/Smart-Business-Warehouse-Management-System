<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateEntreeConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DateEntreeConstraint) {
            throw new UnexpectedTypeException($constraint, DateEntreeConstraint::class);
        }

        // Si la valeur est null, on ne valide pas (géré par d'autres contraintes comme NotBlank)
        if (null === $value) {
            return;
        }

        // Récupérer la date du jour
        $today = new \DateTime();

        // Comparer la date d'entrée avec la date du jour
        if ($value > $today) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ date }}', $value->format('Y-m-d'))
                ->addViolation();
        }
    }
}