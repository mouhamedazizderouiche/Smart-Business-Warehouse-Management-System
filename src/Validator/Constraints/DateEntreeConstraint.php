<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Attribute;

/**
 * @Annotation
 */
#[Attribute]
class DateEntreeConstraint extends Constraint
{
    public $message = 'La date d\'entrée "{{ date }}" doit être égale ou antérieure à la date du jour.';
}