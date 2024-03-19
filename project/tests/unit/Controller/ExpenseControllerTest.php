<?php

namespace App\Tests\unit\Controller;

use App\Controller\ExpenseController;
use App\DTO\ExpenseDTO;
use App\Entity\Expense;
use App\Service\ExpenseService;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExpenseControllerTest extends TestCase
{
    private ExpenseService&MockObject $expenseServiceMock;
    private SerializerInterface&MockObject $serializerMock;
    private ValidatorInterface&MockObject $validatorMock;
    private ExpenseController $controller;
    protected function setUp(): void
    {
        parent::setUp();

        $this->expenseServiceMock = $this->createMock(ExpenseService::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->controller = new ExpenseController(
            $this->expenseServiceMock,
            $this->serializerMock,
            $this->validatorMock,
        );
    }

    public function testShowExpenseShouldCallOnlyServiceIfEntityNotFound(): void
    {
        $randId = rand(1, 5);
        $this->expenseServiceMock
            ->expects($this->once())
            ->method('getById')
            ->with($randId)->willThrowException(new EntityNotFoundException());
        $this->serializerMock->expects($this->never())->method('serialize');
        $this->validatorMock->expects($this->never())->method('validate');

        $response = $this->controller->show($randId);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testShowExpenseShouldCallServiceAndSerializer(): void
    {
        $randId = rand(1, 5);
        $this->expenseServiceMock->expects($this->once())->method('getById')->with($randId);
        $this->serializerMock->expects($this->once())->method('serialize');
        $this->validatorMock->expects($this->never())->method('validate');

        $response = $this->controller->show($randId);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testIndexShouldCallServiceAndSerializer(): void
    {
        $this->expenseServiceMock->expects($this->once())->method('getAll');
        $this->serializerMock->expects($this->once())->method('serialize');
        $this->validatorMock->expects($this->never())->method('validate');

        $response = $this->controller->index();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testCreateExpenseWillReturn201(): void
    {
        $expense = new Expense();
        $dto = new ExpenseDTO();
        $this->serializerMock->expects($this->once())->method('deserialize')->willReturn($dto);
        $this->expenseServiceMock->expects($this->once())->method('create')->with($dto)->willReturn($expense);
        $this->validatorMock->expects($this->once())->method('validate')->with($dto);
        $this->serializerMock->expects($this->once())->method('serialize')->with($expense, 'json');

        $response = $this->controller->create(new Request());

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateExpenseWillReturn422IfNotValidRequest(): void
    {
        $violations = new ConstraintViolationList();
        $violations->add(
            new ConstraintViolation('error', '', [], null, '', null)
        );

        $dto = new ExpenseDTO();
        $this->serializerMock->expects($this->once())->method('deserialize')->willReturn($dto);
        $this->validatorMock->expects($this->once())->method('validate')->with($dto)->willReturn($violations);
        $this->expenseServiceMock->expects($this->never())->method('create');
        $this->serializerMock->expects($this->never())->method('serialize');

        $response = $this->controller->create(new Request());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateExpenseWillReturn200(): void
    {
        $id = rand(1, 10);
        $expense = new Expense();
        $dto = new ExpenseDTO();
        $this->serializerMock->expects($this->once())->method('deserialize')->willReturn($dto);
        $this->expenseServiceMock->expects($this->once())->method('update')->with($id, $dto)->willReturn($expense);
        $this->validatorMock->expects($this->once())->method('validate')->with($dto);
        $this->serializerMock->expects($this->once())->method('serialize')->with($expense, 'json');

        $response = $this->controller->update($id, new Request());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateExpenseWillReturn422IfNotValidRequest(): void
    {
        $violations = new ConstraintViolationList();
        $violations->add(
            new ConstraintViolation('error', '', [], null, '', null)
        );

        $dto = new ExpenseDTO();
        $this->serializerMock->expects($this->once())->method('deserialize')->willReturn($dto);
        $this->validatorMock->expects($this->once())->method('validate')->with($dto)->willReturn($violations);
        $this->expenseServiceMock->expects($this->never())->method('update');
        $this->serializerMock->expects($this->never())->method('serialize');

        $response = $this->controller->update(rand(1, 10), new Request());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateExpenseWillReturn404IfEntityNotFound(): void
    {
        $id = rand(1, 10);
        $dto = new ExpenseDTO();
        $this->serializerMock->expects($this->once())->method('deserialize')->willReturn($dto);
        $this->expenseServiceMock
            ->expects($this->once())
            ->method('update')
            ->with($id, $dto)
            ->willThrowException(new EntityNotFoundException());
        $this->validatorMock->expects($this->once())->method('validate')->with($dto);
        $this->serializerMock->expects($this->never())->method('serialize');


        $response = $this->controller->update($id, new Request());

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteExpenseWillReturn404IfEntityNotFound(): void
    {
        $id = rand(1, 10);
        $this->expenseServiceMock
            ->expects($this->once())
            ->method('deleteExpense')
            ->with($id)
            ->willThrowException(new EntityNotFoundException());

        $response = $this->controller->delete($id);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteExpenseWillReturn200(): void
    {
        $id = rand(1, 10);
        $this->expenseServiceMock
            ->expects($this->once())
            ->method('deleteExpense')
            ->with($id);

        $response = $this->controller->delete($id);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}