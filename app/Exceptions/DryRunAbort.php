<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown on purpose, at the very end of a dry-run import, purely to make
 * Laravel's DB::transaction() roll back everything that transaction just
 * did. It is always caught immediately by whoever started the transaction
 * -- it never represents a real error.
 */
class DryRunAbort extends RuntimeException
{
}
