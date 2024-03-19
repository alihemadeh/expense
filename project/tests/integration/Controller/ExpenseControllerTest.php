<?php

namespace App\Tests\integration\Controller;

use App\Controller\ExpenseController;
use App\Entity\Expense;
use App\Enum\ExpenseType;
use App\Repository\ExpenseRepository;
use App\Tests\integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpenseControllerTest extends IntegrationTestCase
{
    private EntityManagerInterface $entityManager;
    private ExpenseController $controller;
    private ExpenseRepository $repository;
    protected function setUp(): void
    {
        self::bootKernel();
        parent::setUp();

        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->controller = $this->getContainer()->get(ExpenseController::class);
        $this->repository = $this->getContainer()->get(ExpenseRepository::class);
    }

    public function testShowExpenseShouldReturnExpense(): void
    {
        $description = 'description';
        $type = ExpenseType::BILLS;
        $value = 20.0;
        $expense = new Expense();
        $expense->setType($type)
            ->setDescription($description)
            ->setValue($value);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $response = $this->controller->show($expense->getId());
        $showExpense = (array) json_decode($response->getContent());

        $this->assertSame($expense->getId(), $showExpense['id']);
        $this->assertSame($value, (float) $showExpense['value']);
        $this->assertSame($description, $showExpense['description']);
        $this->assertSame($type->value, $showExpense['type']);
    }

    public function testShowExpenseShouldReturnEntityNotFoundIfEntityIsDeleted(): void
    {
        $description = 'description';
        $type = ExpenseType::BILLS;
        $value = 20.0;
        $expense = new Expense();
        $expense->setType($type)
            ->setDescription($description)
            ->setValue($value)
            ->setDeletedAt();

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $response = $this->controller->show($expense->getId());

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testIndexShouldReturnExpenses(): void
    {
        $description1 = 'description';
        $type1 = ExpenseType::BILLS;
        $value1 = 20.0;
        $expense1 = new Expense();
        $expense1->setType($type1)
            ->setDescription($description1)
            ->setValue($value1);

        $this->entityManager->persist($expense1);

        $description2 = 'description2';
        $type2 = ExpenseType::ENTERTAINMENT;
        $value2 = 30.0;
        $expense2 = new Expense();
        $expense2->setType($type2)
            ->setDescription($description2)
            ->setValue($value2);

        $this->entityManager->persist($expense2);
        $this->entityManager->flush();

        $response = $this->controller->index();
        $expenses = (array) json_decode($response->getContent());

        $this->assertSame($expense1->getId(), $expenses[0]->id);
        $this->assertSame($value1, (float) $expenses[0]->value);
        $this->assertSame($description1, $expenses[0]->description);
        $this->assertSame($type1->value, $expenses[0]->type);

        $this->assertSame($expense2->getId(), $expenses[1]->id);
        $this->assertSame($value2, (float) $expenses[1]->value);
        $this->assertSame($description2, $expenses[1]->description);
        $this->assertSame($type2->value, $expenses[1]->type);
    }

    public function testCreateInvalidExpenseShouldReturn422(): void
    {
        $request = new Request(content: json_encode([
            'description' => 'description',
            'value' => 20.0,
            'type' => 'not a valid type',
        ]));

        $response = $this->controller->create($request);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateExpenseShouldReturnExpense(): void
    {
        $description = 'description';
        $type = ExpenseType::BILLS;
        $value = 20.0;

        $request = new Request(content: json_encode([
            'description' => $description,
            'value' => $value,
            'type' => $type,
        ]));

        $response = $this->controller->create($request);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $content = $response->getContent();
        $data = json_decode($content);

        $this->assertSame($description, $data->description);
        $this->assertSame($value, (float) $data->value);
        $this->assertSame($type->value, $data->type);
    }

    public function testDeleteExpenseShouldThrow404IfEntityNotFound(): void
    {
        $description = 'description';
        $type = ExpenseType::BILLS;
        $value = 20.0;
        $expense = new Expense();
        $expense->setType($type)
            ->setDescription($description)
            ->setValue($value)
            ->setDeletedAt();

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $response = $this->controller->delete($expense->getId());

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteExpenseShouldSoftDeleteExpense(): void
    {
        $description = 'description';
        $type = ExpenseType::BILLS;
        $value = 20.0;
        $expense = new Expense();
        $expense->setType($type)
            ->setDescription($description)
            ->setValue($value);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $this->assertNull($expense->getDeletedAt());

        $response = $this->controller->delete($expense->getId());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var Expense $expenseAfterDelete */
        $expenseAfterDelete = $this->repository->find($expense->getId());

        $this->assertNotNull($expenseAfterDelete->getDeletedAt());
    }

    public function testUpdateInvalidExpenseShouldReturn422(): void
    {
        $request = new Request(content: json_encode([
            'description' => 'description',
            'value' => 20.0,
            'type' => 'not a valid type',
        ]));

        $response = $this->controller->update(rand(1, 10), $request);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateExpenseShouldThrow404IfEntityNotFound(): void
    {
        $description = 'description';
        $type = ExpenseType::BILLS;
        $value = 20.0;
        $expense = new Expense();
        $expense->setType($type)
            ->setDescription($description)
            ->setValue($value)
            ->setDeletedAt();

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $request = new Request(content: json_encode([
            'description' => 'new description',
            'value' => 30.0,
            'type' => ExpenseType::ENTERTAINMENT,
        ]));

        $response = $this->controller->update($expense->getId(), $request);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateExpenseShouldReturnUpdatedExpense(): void
    {
        $expense = new Expense();
        $expense->setType(ExpenseType::BILLS)
            ->setDescription('old description')
            ->setValue(20);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $newDescription = 'new description';
        $newType = ExpenseType::ENTERTAINMENT;
        $newValue = 30.0;

        $request = new Request(content: json_encode([
            'description' => $newDescription,
            'value' => $newValue,
            'type' => $newType,
        ]));

        $response = $this->controller->update($expense->getId(), $request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = $response->getContent();
        $data = json_decode($content);

        $this->assertSame($newDescription, $data->description);
        $this->assertSame($newValue, (float) $data->value);
        $this->assertSame($newType->value, $data->type);
    }
}