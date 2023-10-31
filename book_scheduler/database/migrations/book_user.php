<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("subscriber_data", function (Blueprint $table) {
            $table
                ->id("id")
                ->unique()
                ->autoIncrement();
            $table->string("username");
            $table->string("whatsapp_number");
            $table->string("telegram_number")->nullable();;
            $table->string("whatsapp_key")->nullable();
            $table->string("telegram_key")->nullable();
        });

        Schema::create("subscriber_jobs", function (Blueprint $table) {
            $table
                ->id()
                ->unique()
                ->autoIncrement();
            $table->unsignedBigInteger("user_id");
            $table
                ->foreign("user_id")
                ->references("id")
                ->on("subscriber_data")
                ->cascadeOnDelete();
            $table->integer("incremental_factor");
            $table->integer("current_page")->default(1);
            $table->string("job_time");
            $table->string("pdf_path");
            // Everyday means 0 , 1 Every Sunday -- 7 Every Saturday
            $table->integer("interval");
            $table->string("receivers");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists("subscriber_jobs");
        Schema::dropIfExists("subscriber_data");
    }
};
