<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Middleware;

use ReactParallel\Pool\Worker\Work as WorkContract;

final class Work implements WorkContract
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function work(): Request
    {
        return $this->request;
    }
}
