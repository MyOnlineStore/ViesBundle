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
    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
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
            $isValid = $this->viesApi->validateVat($format, str_replace($format, '', $value))->isValid();
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
