<?php

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
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        if (!$constraint instanceof VatNumber) {
            return;
        }

        $format = $constraint->getFormat();

        $isValid = false;

        try {
            $isValid = $this->viesApi->validateVat($format, str_replace($format, '', $value))->isValid();
        } catch (ViesServiceException $exception) {
            // if SOAP-ERROR is found clear it, SoapClient __construct can throw two duplicate type of error on one failure
            $error = error_get_last();
            if( ! is_null($error) && strpos($error['message'], 'SOAP-ERROR') !== false) {
                error_clear_last();
            }
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
