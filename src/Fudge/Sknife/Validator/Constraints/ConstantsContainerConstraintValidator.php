<?php
namespace Fudge\Sknife\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * ConstantsContainerValidator
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 22/10/13
 */
abstract class ConstantsContainerConstraintValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function validate($value, Constraint $constraint)
    {
        if (!isset($this->getAll()[$value])) {
            $this->context->addViolation($constraint->message, ['%list%' => implode(', ', $this->getAll())]);
        }
    }

    abstract public function getAll();
}
