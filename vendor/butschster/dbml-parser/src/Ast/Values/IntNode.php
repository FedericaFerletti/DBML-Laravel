<?php
declare(strict_types=1);

namespace Butschster\Dbml\Ast\Values;

class IntNode extends AbstractValue
{
    public function __construct(int $offset, $value)
    {
        parent::__construct($offset, (int) $value);
    }
}
