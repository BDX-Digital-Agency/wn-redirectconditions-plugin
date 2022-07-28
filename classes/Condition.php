<?php

declare(strict_types=1);

namespace Bdx\RedirectConditions\Classes;

use Bdx\RedirectConditions\Models\ConditionParameter;
use CreativeSizzle\Redirect\Classes\Contracts\RedirectConditionInterface;

abstract class Condition implements RedirectConditionInterface
{
    protected function getParameters(int $redirectId): array
    {
        $conditionParameter = ConditionParameter::query()
            ->where('redirect_id', '=', $redirectId)
            ->where('condition_code', '=', $this->getCode())
            ->first();

        if ($conditionParameter !== null) {
            if ($conditionParameter->getAttribute('is_enabled') === null) {
                return [];
            }

            return (array) $conditionParameter->getAttribute('parameters');
        }

        return [];
    }
}
