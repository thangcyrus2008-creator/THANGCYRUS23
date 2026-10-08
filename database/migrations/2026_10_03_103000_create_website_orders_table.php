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
        if (!Schema::hasTable('website_orders')) {
            Schema::create('website_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('order_id')->nullable()->index();
                $table->string('service_id')->nullable();
                $table->string('service_name')->nullable();
                $table->string('service_slug')->nullable()->index();
                $table->string('domain')->index();
                $table->integer('duration_months')->default(1);
                $table->bigInteger('price')->default(0);
                $table->bigInteger('original_price')->default(0);
                $table->string('shop_account')->nullable();
                $table->string('shop_password')->nullable();
                $table->text('cloudflare_nameservers')->nullable();
                $table->string('status')->default('pending')->index();
                $table->text('status_message')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->text('meta_data')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_orders');
    }
};
