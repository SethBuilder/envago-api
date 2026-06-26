<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Strip domain prefix from media URLs stored as full URLs.
        // e.g. https://painel.envago.live/storage/activities/49/file.jpg → activities/49/file.jpg
        DB::table('media')
            ->where('url', 'like', 'http%')
            ->get()
            ->each(function ($row) {
                if (preg_match('#/storage/(.+)$#', $row->url, $m)) {
                    DB::table('media')->where('id', $row->id)->update(['url' => $m[1]]);
                }
            });
    }

    public function down(): void {}
};
