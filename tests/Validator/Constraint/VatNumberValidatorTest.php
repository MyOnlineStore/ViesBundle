<?php

namespace Sandwich\ViesBundle\Tests\Validator\Constraint;

use DragonBe\Vies\CheckVatResponse;
use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesException;
use DragonBe\Vies\ViesServiceException;
use Sandwich\ViesBundle\Validator\Constraint\VatNumber;
use Sandwich\ViesBundle\Validator\Constraint\VatNumberValidator;

class VatNumberValidatorTest extends \PHPUnit_Framework_TestCase
{
    const FORMAT = 'NL';
    const MESSAGE = 'horribly wrong';

    /**
     * @var VatNumberValidator
     */
    private $validator;

    private $constraint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExecutionContextInterface
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConstraintViolationBuilderInterface
     */
    private $constraintViolationBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Vies
     */
    private $api;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CheckVatResponse
     */
    private $response;

    protected function setUp()
    {
        $this->constraint = new VatNumber(['format' => self::FORMAT, 'message' => self::MESSAGE]);
        $this->api = $this->getMockBuilder(Vies::class)->disableOriginalConstructor()->getMock();
        $this->context = $this->getMock(ExecutionContextInterface::class);
        $this->constraintViolationBuilder = $this->getMock(ConstraintViolationBuilderInterface::class);
        $this->response = $this->getMockBuilder(CheckVatResponse::class)->disableOriginalConstructor()->getMock();

        $this->validator = new VatNumberValidator($this->api);
    }

    public function testValidateWithEmptyValueWillReturnWithNoViolation()
    {
        $this->api->expects(self::never())->method('getHeartBeat');
        $this->api->expects(self::never())->method('validateVat');
        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate(null, $this->constraint));
    }

    public function testValidateWithNoViesServiceAvailableWillReturnWithNoViolation()
    {
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn(false);

        $this->api->expects(self::never())->method('validateVat');
        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate('foobar', $this->constraint));
    }

    public function testValidateWithVieServiceExceptionWillReturnWithNoViolation()
    {
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn(true);

        $this->api->expects(self::once())->method('validateVat')->with(self::FORMAT, 'foobar')->willThrowException(
            new ViesServiceException()
        );
        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate(self::FORMAT.'foobar', $this->constraint));
    }

    public function testValidateWithViesExceptionWillReturnWithViolation()
    {
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn(true);

        $this->api->expects(self::once())->method('validateVat')->with(self::FORMAT, 'foobar')->willThrowException(
            new ViesException()
        );

        $this->context->expects(self::once())->method('addViolation')->with(
            self::MESSAGE,
            ['%format%' => self::FORMAT]
        );

        $this->validator->validate('foobar', $this->constraint);
    }

    public function testValidateWithValidVatNumberWillReturnWithNoViolation()
    {
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn(true);

        $this->api->expects(self::once())->method('validateVat')->with(self::FORMAT, 'foobar')->willReturn(
            $this->response
        );

        $this->response->expects(self::once())->method('isValid')->willReturn(true);

        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate('foobar', $this->constraint));
    }

    public function testValidateWithInValidVatNumberWillReturnWithViolation()
    {
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn(true);

        $this->api->expects(self::once())->method('validateVat')->with(self::FORMAT, 'foobar')->willThrowException(
            new ViesException()
        );

        $this->context->expects(self::once())->method('addViolation')->with(
            self::MESSAGE,
            ['%format%' => self::FORMAT]
        );

        $this->response->expects(self::once())->method('isValid')->willReturn(false);

        $this->validator->validate('foobar', $this->constraint);
    }
}
