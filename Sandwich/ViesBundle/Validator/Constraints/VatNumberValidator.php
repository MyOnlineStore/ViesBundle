<?php

namespace Sandwhich\ViesBundle\Validator\Constraints;

use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesException;
use DragonBe\Vies\ViesServiceException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class VatNumberValidator extends ConstraintValidator
{
    /**
     * @var Vies
     */
    private $viesApi;

    /**
     * @param Vies $viesApi
     */
    public function __construct(Vies $viesApi)
    {
        $this->viesApi = $viesApi;
    }

    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        if (!$this->viesApi->getHeartBeat()) {
            //VIES service is not available
            return;
        }

        $format = $constraint->format;
        $isValid = false;

        try {
            $result = $this->viesApi->validateVat($format, str_replace($format, '', $value));

            $isValid = $result->isValid();
        } catch (ViesServiceException $exception) {
            //There is probably a temporary problem with back-end VIES service
            return;
        } catch (ViesException $exception) {
        }

        if ($isValid) {
            return;
        }

        $this->context->addViolation($constraint->message, ['%format%' => $format]);
    }
}
