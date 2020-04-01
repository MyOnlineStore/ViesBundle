<?php
declare(strict_types=1);

namespace Sandwich\ViesBundle\Tests\Validator\Constraint;

use PHPUnit\Framework\TestCase;
use Sandwich\ViesBundle\Validator\Constraint\VatNumber;
use Sandwich\ViesBundle\Validator\Constraint\VatNumberValidator;

final class VatNumberTest extends TestCase
{
    /**
     * @var VatNumber
     */
    private $constraint;

    protected function setUp(): void
    {
        $this->constraint = new VatNumber();
    }

    public function testContainsCorrectInitialFormat(): void
    {
        self::assertSame('NL', $this->constraint->getFormat());
    }

    public function testContainsCorrectMessage(): void
    {
        self::assertSame('This is not a valid %format% vat number.', $this->constraint->message);
    }

    public function testValidatedByHasCorrectValidator(): void
    {
        self::assertSame(VatNumberValidator::class, $this->constraint->validatedBy());
    }
}
