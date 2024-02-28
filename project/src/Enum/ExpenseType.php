<?php

namespace App\Enum;
enum ExpenseType: string
{
    case ENTERTAINMENT = 'Entertainment';
    case FOOD = 'Food';
    case BILLS = 'Bills';
    case TRANSPORT = 'Transport';
    case OTHER = 'Other';
}