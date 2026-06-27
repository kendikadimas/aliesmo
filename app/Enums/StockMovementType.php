<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Initial = 'initial';
    case Restock = 'restock';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case Return = 'return';
}
