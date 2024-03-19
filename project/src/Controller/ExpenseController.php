<?php

namespace App\Controller;

use App\DTO\ExpenseDTO;
use App\Service\ExpenseService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExpenseController extends AbstractController
{
    public function __construct(
        private readonly ExpenseService $expenseService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    )
    {
    }

    #[Route('/expenses/{id}', methods: 'get')]
    public function show(int $id): Response
    {
        try {
            $expense = $this->expenseService->getById($id);
        } catch (EntityNotFoundException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }

        return new Response($this->serializer->serialize($expense, 'json'));
    }

    #[Route('/expenses', methods: 'get')]
    public function index(): Response
    {
        $expenses = $this->expenseService->getAll();

        return new Response($this->serializer->serialize($expenses, 'json'));
    }

    #[Route('/expenses', methods: 'post')]
    public function create(Request $request): Response
    {
        $expenseDto = $this->serializer->deserialize(
            $request->getContent(),
            ExpenseDTO::class,
            'json'
        );

        $violations = $this->validator->validate($expenseDto);

        if ($violations->count() > 0) {
            return new Response((string) $violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response(
            $this->serializer->serialize(
                $this->expenseService->create($expenseDto),
                'json'
            ),
            Response::HTTP_CREATED
        );
    }

    #[Route('/expenses/{id}', methods: 'put')]
    public function update(int $id, Request $request): Response
    {
        $expenseDto = $this->serializer->deserialize(
            $request->getContent(),
            ExpenseDTO::class,
            'json'
        );

        $violations = $this->validator->validate($expenseDto);

        if ($violations->count() > 0) {
            return new Response((string) $violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $expense = $this->expenseService->update($id, $expenseDto);
        } catch (EntityNotFoundException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }

        return new Response($this->serializer->serialize($expense, 'json'), Response::HTTP_OK);
    }

    #[Route('/expenses/{id}', methods: 'delete')]
    public function delete(int $id): Response
    {
        try {
            $this->expenseService->deleteExpense($id);
        } catch (EntityNotFoundException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }

        return new Response('', Response::HTTP_OK);
    }
}