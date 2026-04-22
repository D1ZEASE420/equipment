<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add category/tags to devices
        Schema::table('devices', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
        });

        // Add due_time, student info, and notification to borrowings
        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('due_time')->default('08:30')->after('due_date');
            $table->string('student_name')->nullable()->after('due_time');
            $table->string('student_email')->nullable()->after('student_name');
            $table->boolean('notification_sent')->default(false)->after('student_email');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('category');
        });
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['due_time', 'student_name', 'student_email', 'notification_sent']);
        });
    }
};
