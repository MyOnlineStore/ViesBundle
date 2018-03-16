<?php

namespace Sandwich\ViesBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class VatNumber extends Constraint
{
    /**
     * @var string
     */
    public $format = 'NL';

    /**
     * @var string
     */
    public $message = 'This is not a valid %format% vat number.';

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @inheritDoc
     */
    public function validatedBy(): string
    {
        return VatNumberValidator::class;
    }
}
