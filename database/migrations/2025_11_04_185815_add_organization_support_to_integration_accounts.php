<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('integration_accounts', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->string('scope', 20)->default('personal')->after('type');
            $table->json('shared_with')->nullable()->after('scope');
            $table->foreignId('created_by')->nullable()->after('shared_with')->constrained('users')->onDelete('set null');

            $table->index(['organization_id', 'scope']);
            $table->index(['user_id', 'type', 'scope']);
        });

        DB::statement('
            ALTER TABLE integration_accounts
            ADD CONSTRAINT check_scope_consistency CHECK (
                (scope = \'personal\' AND organization_id IS NULL AND user_id IS NOT NULL)
                OR
                (scope = \'organization\' AND organization_id IS NOT NULL)
            )
        ');
    }

    public function down(): void
    {
        Schema::table('integration_accounts', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['organization_id', 'scope']);
            $table->dropIndex(['user_id', 'type', 'scope']);
            $table->dropColumn(['organization_id', 'scope', 'shared_with', 'created_by']);
        });

        DB::statement('ALTER TABLE integration_accounts DROP CONSTRAINT IF EXISTS check_scope_consistency');
    }
};
