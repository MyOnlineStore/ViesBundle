<?php
declare(strict_types=1);

namespace Sandwich\ViesBundle\Validator\Constraint;

use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesException;
use DragonBe\Vies\ViesServiceException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class VatNumberValidator extends ConstraintValidator
{
    public function __construct(
        private Vies $viesApi,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (empty($value) || !\is_string($value)) {
            return;
        }

        if (!$constraint instanceof VatNumber) {
            return;
        }

        if (!$this->viesApi->getHeartBeat()->isAlive()) {
            //VIES service is not available
            return;
        }

        $format = $constraint->getFormat();
        $isValid = false;

        try {
            $isValid = $this->viesApi->validateVat($format, \str_replace($format, '', $value))->isValid();
        } catch (ViesServiceException) {
            //There is probably a temporary problem with back-end VIES service
            return;
        } catch (ViesException) {
        }

        if ($isValid) {
            return;
        }

        $this->context->addViolation($constraint->message, ['%format%' => $format]);
    }
}
