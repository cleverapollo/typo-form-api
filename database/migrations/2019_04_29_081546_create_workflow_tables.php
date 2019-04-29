<?php

use App\Repositories\WorkflowRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('author_id');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('trigger');
            $table->json('trigger_config')->default('{}');
            $table->string('action');
            $table->json('action_config')->default('{}');
            $table->unsignedInteger('delay')->default(WorkflowRepository::WORKFLOW_STATUS_ACTIVE);
            $table->integer('status');
            $table->datetime('active_from');
            $table->datetime('active_to');
            $table->timestamps();
        });

        Schema::create('workflow_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('workflow_id');
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->datetime('scheduled_for');
            $table->json('data')->default('{}');
            $table->datetime('completed_at')->nullable();
            $table->integer('status')->default(WorkflowRepository::JOB_STATUS_ACTIVE);
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
        Schema::dropIfExists('workflow_jobs');
        Schema::dropIfExists('workflows');
    }
}
