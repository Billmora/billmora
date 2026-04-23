<?php

namespace App;

enum OrderItemType: string
{
    case Service = 'service';
    case Domain = 'domain';
}