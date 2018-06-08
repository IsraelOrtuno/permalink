<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermalinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permalinks', function (Blueprint $table) {
            $table->increments('id');

            $table->string('slug');
            $table->unsignedInteger('parent_id')->nullable();
            $table->text('parameters')->nullable(); // TODO: Consider removing this?

            $table->string("permalinkable_type")->nullable();
            $table->unsignedBigInteger("permalinkable_id")->nullable();

            $table->string('action')->nullable();
            $table->text('seo')->nullable();

            $table->timestamps();

            $table->index(["permalinkable_type", "permalinkable_id"]);
            $table->unique(['slug', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permalinks');
    }
}
