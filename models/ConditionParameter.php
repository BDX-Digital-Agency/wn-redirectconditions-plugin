<?php

declare(strict_types=1);

namespace Bdx\RedirectConditions\Models;

use CreativeSizzle\Redirect\Models\Redirect;
use Winter\Storm\Database\Model;

class ConditionParameter extends Model
{
    public $belongsTo = [
        'redirect' => Redirect::class,
    ];

    protected $table = 'bdx_redirectconditions_condition_parameters';

    protected $guarded = [];

    protected $jsonable = [
        'parameters',
    ];
}
