<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Ganti review menjadi comment
            $table->renameColumn('review', 'comment');

            // Hapus user_id karena review sekarang terkait dengan book
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Tambahkan book_id
            $table->foreignId('book_id')
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Rating cukup 1-5
            $table->unsignedTinyInteger('rating')->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Balikin rating
            $table->unsignedBigInteger('rating')->change();

            // Hapus relasi book
            $table->dropForeign(['book_id']);
            $table->dropColumn('book_id');

            // Balikin comment menjadi review
            $table->renameColumn('comment', 'review');

            // Balikin user_id
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};