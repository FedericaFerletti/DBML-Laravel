<?php

/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phplrt\Contracts\Source;

interface FileInterface extends ReadableInterface
{
    /**
     * Returns the physical path to the source file.
     *
     * @return non-empty-string
     */
    public function getPathname(): string;
}
