<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create('core_controls', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('table_name', 255);
            $table->string('row_id', 60);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            // Constraints
            $table->primary('id');
            $table->index('table_name');
            $table->index('row_id');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_modules', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('name', 255);
            $table->string('vendor', 255)->nullable();
            $table->string('title', 255);
            $table->string('version', 255)->default('1.0');
            $table->string('icon', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_visible_index')->default(true);
            $table->enum('install_type', ['hand', 'file'])->default('hand');
            $table->unsignedInteger('seq')->nullable();
            $table->boolean('isset_updates')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Constraints
            $table->primary('id');
            $table->unique('name');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_modules_available', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('name', 255);
            $table->string('vendor', 255)->nullable();
            $table->string('title', 255);
            $table->string('version', 255)->default('1.0');
            $table->string('icon', 255)->nullable();
            $table->unsignedInteger('count_downloads')->nullable();
            $table->string('home_page', 500)->nullable();
            $table->json('tags')->nullable();
            $table->text('description')->nullable();
            $table->string('repository', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            // Constraints
            $table->primary('id');
            $table->index('name');
            $table->index('vendor');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_modules_available_versions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('module_id');
            $table->enum('type', ['file', 'repo']);
            $table->string('version', 255);
            $table->string('hash', 255);
            $table->string('file_url', 500);
            $table->binary('file_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->primary('id');
            $table->index('module_id');
            $table->foreign('module_id', 'fk1_core_modules_available_versions')
                ->references('id')
                ->on('core_modules_available')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_modules_sections', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('module_id');
            $table->string('name', 255);
            $table->string('title', 255);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('seq')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary('id');
            $table->unique(['module_id', 'name']);
            $table->foreign('module_id', 'fk1_core_modules_actions')
                ->references('id')
                ->on('core_modules')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_options', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('parent_id');
            $table->string('code', 255);
            $table->text('value')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary('id');
            $table->index('parent_id');
            $table->index('code');
            $table->foreign('parent_id', 'fk1_core_options')
                ->references('id')
                ->on('core_options')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_settings', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('module', 255)->nullable();
            $table->string('code', 255);
            $table->string('title', 255)->nullable();
            $table->text('value')->nullable();
            $table->string('field_type', 255)->default('text');
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->string('author_modify', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary('id');
            $table->unique(['module', 'code']);
            $table->index('module');
            $table->index('code');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_roles', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('title', 255);
            $table->string('description', 255)->nullable();
            $table->json('privileges')->nullable();
            $table->string('author', 255)->nullable();
            $table->string('author_modify', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Constraints
            $table->primary('id');
            $table->unique('title');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_users', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('role_id')->nullable();
            $table->string('login', 255);
            $table->string('email', 255)->nullable();
            $table->string('pass', 36)->nullable();
            $table->string('pass_reset_token', 255)->nullable();
            $table->timestamp('pass_reset_date')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('fname', 255)->nullable();
            $table->string('lname', 255)->nullable();
            $table->string('mname', 255)->nullable();
            $table->enum('avatar_type', ['none', 'generate', 'upload'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Constraints
            $table->primary('id');
            $table->unique('login');
            $table->unique('email');
            $table->index('role_id');

            // Foreign key
            $table->foreign('role_id', 'fk1_core_users')
                ->references('id')
                ->on('core_roles')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_users_data', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('user_id');
            $table->string('name', 255);
            $table->json('value')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary('id');
            $table->index('name');
            $table->index('user_id');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_users_files', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('ref_id');
            $table->string('name', 255);
            $table->unsignedInteger('size');
            $table->string('hash', 128);
            $table->string('type', 255)->nullable();
            $table->string('object_type', 255)->nullable();
            $table->json('meta')->nullable();
            $table->json('storage')->nullable();
            $table->binary('thumb')->nullable();
            $table->binary('content')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            $table->primary('id');
            $table->index('object_type');
            $table->index('ref_id');
            $table->index('hash', 'file_hash');
            $table->foreign('ref_id', 'fk1_core_users_files')
                ->references('id')
                ->on('core_users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });

        Schema::create('core_users_sessions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('user_id');
            $table->string('fingerprint', 255);
            $table->string('token_hash', 100)->nullable();
            $table->string('client_ip', 60)->nullable();
            $table->string('agent_name', 500)->nullable();
            $table->string('agent_title', 255)->nullable();
            $table->unsignedInteger('count_requests')->default(0);
            $table->string('country_name', 255)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('region', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('lat', 255)->nullable();
            $table->string('lng', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('date_expired')->nullable();
            $table->timestamp('date_last_activity')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            $table->primary('id');
            $table->index('user_id');
            $table->index('date_expired');
            $table->index('is_active');
            $table->foreign('user_id', 'fk1_core_users_sessions')
                ->references('id')
                ->on('core_users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists('core_controls');
        Schema::dropIfExists('core_modules_available_versions');
        Schema::dropIfExists('core_modules_available');
        Schema::dropIfExists('core_modules_sections');
        Schema::dropIfExists('core_modules');
        Schema::dropIfExists('core_options');
        Schema::dropIfExists('core_settings');
        Schema::dropIfExists('core_users_data');
        Schema::dropIfExists('core_users_files');
        Schema::dropIfExists('core_users_sessions');
        Schema::dropIfExists('core_users');
        Schema::dropIfExists('core_roles');
    }
};