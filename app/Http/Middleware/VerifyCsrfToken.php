<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
          '/approval-data',
          'getInitialGroups',
          'getSubGroups',
          'ledgers.search',
        'loan/home-loan-create-update',
        'loan/vehicle-loan-create-update',
        'loan/term-loan-create-update',
        'getBalanceSheetLedgers',
        'getPLGroupLedgers',
        'getSubGroupsMultiple',
        'legal/send-message',
        '/land/lease/tax-calculation'
    ];
}
