<?php

namespace App\Services\Payments;

use RuntimeException;

/** A payment rule was broken (wrong state, expired …); the message is safe to show. */
class PaymentException extends RuntimeException {}
