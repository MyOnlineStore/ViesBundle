<?php

namespace Sandwich\ViesBundle\Tests\Validator\Constraint;

use DragonBe\Vies\CheckVatResponse;
use DragonBe\Vies\HeartBeat;
use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesException;
use DragonBe\Vies\ViesServiceException;
use Sandwich\ViesBundle\Validator\Constraint\VatNumber;
use Sandwich\ViesBundle\Validator\Constraint\VatNumberValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VatNumberValidatorTest extends \PHPUnit_Framework_TestCase
{
    const FORMAT = 'NL';
    const MESSAGE = 'horribly wrong';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Vies
     */
    private $api;

    private $constraint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConstraintViolationBuilderInterface
     */
    private $constraintViolationBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExecutionContextInterface
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CheckVatResponse
     */
    private $response;

    /**
     * @var VatNumberValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->constraint = new VatNumber(['format' => self::FORMAT, 'message' => self::MESSAGE]);
        $this->api = $this->createMock(Vies::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->response = $this->createMock(CheckVatResponse::class);

        $this->validator = new VatNumberValidator($this->api);
        $this->validator->initialize($this->context);
    }

    public function testValidateWithEmptyValueWillReturnWithNoViolation()
    {
        $this->api->expects(self::never())->method('getHeartBeat');
        $this->api->expects(self::never())->method('validateVat');
        $this->context->expects(self::never())->method('addViolation');

        $this->validator->validate(null, $this->constraint);
    }

    public function testValidateWithInValidConstraintWillReturnNoViolation()
    {
        $this->api->expects(self::never())->method('getHeartBeat');
        $this->context->expects(self::never())->method('addViolation');

        self::assertNull(
            $this->validator->validate(
                'foobar',
                new class() extends Constraint
                {
                }
            )
        );
    }

    public function testValidateWithInValidVatNumberWillReturnWithViolation()
    {
        $heartBeat = $this->createMock(HeartBeat::class);
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn($heartBeat);
        $heartBeat->expects(self::once())->method('isAlive')->willReturn(true);

        $this->api->expects(self::once())
            ->method('validateVat')
            ->with(self::FORMAT, 'foobar')
            ->willReturn($this->response);

        $this->context->expects(self::once())
            ->method('addViolation')
            ->with(self::MESSAGE, ['%format%' => self::FORMAT])
            ->willReturn($this->constraintViolationBuilder);

        $this->response->expects(self::once())->method('isValid')->willReturn(false);

        self::assertNull($this->validator->validate('foobar', $this->constraint));
    }

    public function testValidateWithNoViesServiceAvailableWillReturnWithNoViolation()
    {
        $heartBeat = $this->createMock(HeartBeat::class);
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn($heartBeat);
        $heartBeat->expects(self::once())->method('isAlive')->willReturn(false);

        $this->api->expects(self::never())->method('validateVat');
        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate('foobar', $this->constraint));
    }

    public function testValidateWithValidVatNumberWillReturnWithNoViolation()
    {
        $heartBeat = $this->createMock(HeartBeat::class);
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn($heartBeat);
        $heartBeat->expects(self::once())->method('isAlive')->willReturn(true);

        $this->api->expects(self::once())
            ->method('validateVat')
            ->with(self::FORMAT, 'foobar')
            ->willReturn($this->response);

        $this->response->expects(self::once())->method('isValid')->willReturn(true);

        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate('foobar', $this->constraint));
    }

    public function testValidateWithVieServiceExceptionWillReturnWithNoViolation()
    {
        $heartBeat = $this->createMock(HeartBeat::class);
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn($heartBeat);
        $heartBeat->expects(self::once())->method('isAlive')->willReturn(true);

        $this->api->expects(self::once())->method('validateVat')
            ->with(self::FORMAT, 'foobar')
            ->willThrowException(new ViesServiceException());

        $this->context->expects(self::never())->method('addViolation');

        self::assertNull($this->validator->validate(self::FORMAT.'foobar', $this->constraint));
    }

    public function testValidateWithViesExceptionWillReturnWithViolation()
    {
        $heartBeat = $this->createMock(HeartBeat::class);
        $this->api->expects(self::once())->method('getHeartBeat')->willReturn($heartBeat);
        $heartBeat->expects(self::once())->method('isAlive')->willReturn(true);

        $this->api->expects(self::once())->method('validateVat')
            ->with(self::FORMAT, 'foobar')
            ->willThrowException(new ViesException());

        $this->context->expects(self::once())
            ->method('addViolation')
            ->with(self::MESSAGE, ['%format%' => self::FORMAT])
            ->willReturn($this->constraintViolationBuilder);

        $this->validator->validate('foobar', $this->constraint);
    }
}
