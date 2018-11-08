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
            $table->text('parent_for')->nullable();

            $table->string("entity_type")->nullable();
            $table->unsignedBigInteger("entity_id")->nullable();

            $table->string('action')->nullable();
            $table->text('seo')->nullable();

            $table->softDeletes();

            $table->timestamps();

            $table->index(["entity_type", "entity_id"]);
            $table->unique(['slug', 'parent_id']);
            $table->unique(['parent_for']);
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
