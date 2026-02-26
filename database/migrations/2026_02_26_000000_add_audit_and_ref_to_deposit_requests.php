<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->string('ref_number', 50)->nullable()->after('id');
            $table->foreignId('reviewed_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->after('wallet_transaction_id')->constrained('users')->nullOnDelete();
            $table->timestamp('action_date')->nullable()->after('processed_by');
        });

        $this->backfillRefNumbers();
        DB::table('deposit_requests')->whereNotNull('approved_by')->update(['reviewed_by' => DB::raw('approved_by')]);
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->unique('ref_number');
        });
    }

    private function backfillRefNumbers(): void
    {
        $rows = DB::table('deposit_requests')->whereNull('ref_number')->orderBy('created_at')->orderBy('id')->get();
        $byMonth = [];
        foreach ($rows as $row) {
            $ym = date('Ym', strtotime($row->created_at));
            if (!isset($byMonth[$ym])) $byMonth[$ym] = 0;
            $byMonth[$ym]++;
            $seq = $byMonth[$ym];
            $ref = 'REQ-' . $ym . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            DB::table('deposit_requests')->where('id', $row->id)->update(['ref_number' => $ref]);
        }
    }

    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['ref_number', 'reviewed_by', 'processed_by', 'action_date']);
        });
    }
};
