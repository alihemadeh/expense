<?php

namespace App\DTO;

use App\Enum\ExpenseType;
use Symfony\Component\Validator\Constraints as Assert;

class ExpenseDTO
{
    #[Assert\NotBlank]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public float $value;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getExpenseTypes')]
    public string $type;

    public static function getExpenseTypes(): array
    {
        return array_column(ExpenseType::cases(), 'value');
    }
}