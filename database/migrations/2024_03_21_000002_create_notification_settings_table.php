<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('budget_exceeded_email')->default(true);
            $table->boolean('budget_exceeded_database')->default(true);
            $table->boolean('savings_milestone_email')->default(true);
            $table->boolean('savings_milestone_database')->default(true);
            $table->integer('savings_milestone_percentage')->default(25); // Notify at 25%, 50%, 75%, 100%
            $table->boolean('recurring_transaction_reminder')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_settings');
    }
}; 