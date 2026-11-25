<?php

namespace App\Models;

enum ClientType: string
{
    case CUSTOMER = 'customer';
    case SUPPLIER = 'supplier';
}
