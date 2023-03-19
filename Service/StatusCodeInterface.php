<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Service;

interface StatusCodeInterface
{
    public const DISABLE = 0;

    public const ENABLE = 1;

    public const CLOSED = 2;
}
