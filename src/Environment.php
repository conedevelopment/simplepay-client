<?php

namespace Cone\SimplePay;

enum Environment: string
{
    case SECURE = 'secure';
    case SANDBOX = 'sandbox';
}
