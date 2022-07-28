<?php

namespace Bdx\RedirectConditions\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

class RenameTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vdlp_redirectconditions_condition_parameters')) {
            Schema::rename('vdlp_redirectconditions_condition_parameters', 'bdx_redirectconditions_condition_parameters');

            Schema::table('bdx_redirectconditions_condition_parameters', function (Blueprint $table) {
               $table->renameIndex('vdlp_redirectconditions_unique', 'bdx_redirectconditions_unique');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('bdx_redirectconditions_condition_parameters')) {
            Schema::rename('bdx_redirectconditions_condition_parameters', 'vdlp_redirectconditions_condition_parameters');

            Schema::table('vdlp_redirectconditions_condition_parameters', function (Blueprint $table) {
                $table->renameIndex('bdx_redirectconditions_unique', 'vdlp_redirectconditions_unique');
            });
        }
    }
}
