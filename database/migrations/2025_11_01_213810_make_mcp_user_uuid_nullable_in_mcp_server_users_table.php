<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mcp_server_users', function (Blueprint $table) {
            $table->uuid('mcp_user_uuid')->nullable()->change();
        });
    }
};
