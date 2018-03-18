<?php

namespace Sandwich\ViesBundle\Tests\Validator\Constraint;

use Sandwich\ViesBundle\Validator\Constraint\VatNumber;
use Sandwich\ViesBundle\Validator\Constraint\VatNumberValidator;

final class VatNumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VatNumber
     */
    private $constraint;

    protected function setUp()
    {
        $this->constraint = new VatNumber();
    }

    public function testContainsCorrectInitialFormat()
    {
        self::assertSame('NL', $this->constraint->getFormat());
    }

    public function testContainsCorrectMessage()
    {
        self::assertSame('This is not a valid %format% vat number.', $this->constraint->message);
    }

    public function testValidatedByHasCorrectValidator()
    {
        self::assertSame(VatNumberValidator::class, $this->constraint->validatedBy());
    }
}
