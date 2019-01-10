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
            $table->string('parent_for')->nullable();

            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->string('action')->nullable();

            $table->json('options')->nullable();

            $table->string('final_path')->nullable();

            $table->json('seo')->nullable();

            $table->softDeletes();

            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->unique(['slug', 'parent_id'], 'UNIQUE_SLUG_AND_PARENT');
            $table->unique(['final_path'], 'UNIQUE_FULL_PATH');
            $table->unique(['parent_for'], 'UNIQUE_PARENT_FOR');
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
