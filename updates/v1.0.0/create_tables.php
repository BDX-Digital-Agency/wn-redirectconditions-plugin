<?php

declare(strict_types=1);

namespace Bdx\RedirectConditions\Updates;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateTables extends Migration
{
    public function up(): void
    {
        Schema::create('vdlp_redirectconditions_condition_parameters', static function (Blueprint $table): void {
            // Table configuration
            $table->engine = 'InnoDB';

            // Columns
            $table->increments('id');
            $table->unsignedInteger('redirect_id');
            $table->dateTime('is_enabled')->nullable();
            $table->string('condition_code');
            $table->text('parameters')->nullable();
            $table->timestamps();

            if (Schema::hasTable('vdlp_redirect_redirects')) {
                $redirectsTable = 'vdlp_redirect_redirects';
            } else {
                $redirectsTable = 'creativesizzle_redirect_redirects';
            }

            // Foreign keys
            $table->foreign('redirect_id', 'vdlp_redirectconditions_redirect')
                ->references('id')
                ->on($redirectsTable)
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Indices
            $table->unique([
                'redirect_id',
                'condition_code',
            ], 'vdlp_redirectconditions_unique');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vdlp_redirectconditions_condition_parameters');
        Schema::enableForeignKeyConstraints();
    }
}
