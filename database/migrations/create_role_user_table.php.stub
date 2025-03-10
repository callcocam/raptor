<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $name = config('shinobi.tables.role_user');

        Schema::create($name, function (Blueprint $table) {
            $table->foreignUlid('role_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->primary(['role_id', 'user_id']);
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
};
