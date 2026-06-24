<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->boolean('is_departmental')->default(false)->after('type');
            $table->string('timeline')->nullable()->after('is_departmental');
            $table->unsignedBigInteger('certifying_authority_user_id')->nullable()->after('certifying_authority');
            
            $table->foreign('certifying_authority_user_id', 'obj_cert_auth_fk')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropForeign('obj_cert_auth_fk');
            $table->dropColumn(['is_departmental', 'certifying_authority_user_id']);
        });
    }
};
