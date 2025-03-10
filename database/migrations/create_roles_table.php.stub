<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DefaultStatus;

return new class extends Migration
{
    public function up()
    {
        $name = config('shinobi.tables.roles');

        Schema::create($name, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', array_column(DefaultStatus::cases(), 'value'))->default(DefaultStatus::DRAFT->value);
            $table->boolean('special')->default(false)->nullable(); 
            $table->ulid('tenant_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
