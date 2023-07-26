<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('users', function (Blueprint $table) {
			$table->bigInteger('id', false, true)->primary();
			$table->string('username', 64);
			$table->string('first_name', 64);
			$table->string('last_name', 64);
			$table->string('badge_name', 64)->nullable();
			$table->tinyInteger('role')->default(0);
			$table->char('tg_setup_code', 32);
			$table->integer('tg_uid')->nullable()->unique();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('users');
	}
};