<?php

namespace Sandich\ViesBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class VatNumber extends Constraint
{
    /**
     * @var string
     */
    public $message = 'This is not a valid %format% vat number.';

    /**
     * @var string
     */
    public $format = 'NL';

    /**
     * @inheritDoc
     */
    public function validatedBy()
    {
        return 'vat_number';
    }
}
