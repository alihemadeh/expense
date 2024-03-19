<?php

namespace App\Service;

use App\DTO\ExpenseDTO;
use App\Entity\Expense;
use App\Enum\ExpenseType;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class ExpenseService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ExpenseRepository $repository,
    )
    {
    }

    public function getById(int $id): Expense
    {
        $expense = $this->repository->findOneBy([
            'id' => $id,
            'deletedAt' => null,
        ]);

        if (!$expense) {
            throw new EntityNotFoundException('entities.expense');
        }

        return $expense;
    }

    public function create(ExpenseDTO $dto): Expense
    {
        $expense = new Expense();
        $expense->setDescription($dto->description)
            ->setValue($dto->value)
            ->setType(ExpenseType::from($dto->type));

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        return $expense;
    }

    public function update(int $id, ExpenseDTO $dto): Expense
    {
        $expense = $this->getById($id);

        $expense->setDescription($dto->description)
            ->setValue($dto->value)
            ->setType(ExpenseType::from($dto->type));

        $this->entityManager->flush();

        return $expense;
    }

    public function deleteExpense(int $id): void
    {
        $expense = $this->getById($id);

        $expense->softDelete();

        $this->entityManager->flush();
    }

    /**
     * @return Expense[]
     */
    public function getAll(): array
    {
        return $this->repository->findBy(['deletedAt' => null]);
    }
}