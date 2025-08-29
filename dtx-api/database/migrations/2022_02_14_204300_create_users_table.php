<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->string('password', 200);
            $table->string('email')->unique();

            $table->string('name', 100);
            $table->string('lastname', 100)->nullable();
            $table->string('display_name', 100)->nullable();
            $table->timestamp('admission_date')->nullable()->comment('Date of hiring of the Company');
            $table->timestamp('withdrawal_date')->nullable()->comment('Date of withdrawal from the company');
            $table->string('position', 255)->nullable()->comment('Your position within your company');

            $table->string('city', 100)->nullable();
            $table->string('area', 100)->nullable();
            $table->string('group', 100)->nullable();
            $table->string('leavel', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('status', 1)->comment('A: Activo, I: Inactivo');
            $table->string('origin', 4)->comment('ldap: Directorio Activo, app: Aplicación');

            // laves foraneas
            $table->foreignId('rol_id')->constrained();
            $table->foreignId('specialty_id')->nullable()->constrained();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
