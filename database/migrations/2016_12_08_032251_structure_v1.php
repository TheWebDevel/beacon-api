<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StructureV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('users', function (Blueprint $table) {
          $table->increments('id');
          $table->string('user_slug', 50);
          $table->string('user_mobile');
          $table->string('user_otp');
          $table->string('user_auth_token')->nullable();
          $table->string('user_fname');
          $table->string('user_lname')->nullable();
          $table->string('user_profile_pic')->nullable();
          $table->string('user_fcm_token');
          $table->integer('user_isverified');
          $table->integer('user_isactive');
          $table->integer('user_isregistered');
          $table->string('user_password');
          $table->string('user_current_location');
          $table->timestamps();
          $table->softDeletes();
      });

      Schema::create('friends', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->unsigned();
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          $table->integer('friend_id')->unsigned();
          $table->foreign('friend_id')->references('id')->on('users')->onDelete('cascade');
          $table->string('friend_slug');
          $table->string('friend_fname');
          $table->string('friend_lname');
          $table->integer('location_isallowed');
          $table->timestamps();
          $table->softDeletes();
      });


      Schema::create('groups', function (Blueprint $table) {
          $table->increments('id');
          $table->string('group_slug');
          $table->string('name');
          $table->string('icon');
          $table->timestamps();
          $table->softDeletes();
      });

      Schema::create('group_users', function(Blueprint $table) {
          $table->increments('id');
          $table->string('group_slug');
          $table->integer('user_id')->unsigned();
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          $table->string('location')->nullable();
          $table->string('user_updated_at');
          $table->integer('group_id')->unsigned();
          $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
          $table->integer('user_isin');
          $table->string('group_name');
          $table->string('group_role');
          $table->string('user_fname');
          $table->string('user_lname');
          $table->timestamps();
      });

      Schema::create('events', function(Blueprint $table) {
          $table->increments('id');
          $table->string('event_slug');
          $table->string('name');
          $table->integer('user_count');
          $table->string('location');
          $table->timestamp('date_time');
          $table->timestamps();
          $table->softDeletes();
      });

      Schema::create('event_users', function(Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->unsigned();
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          $table->integer('event_id')->unsigned();
          $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
          $table->timestamps();
          $table->softDeletes();
      });

      Schema::create('event_groups', function(Blueprint $table) {
          $table->increments('id');
          $table->integer('event_id')->unsigned();
          $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
          $table->integer('group_id')->unsigned();
          $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
          $table->timestamps();
          $table->softDeletes();
      });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
        Schema::drop('friends');
        Schema::drop('groups');
        Schema::drop('events');
        Schema::drop('event_users');
        Schema::drop('event_groups');
        Schema::drop('group_users');
    }
}
