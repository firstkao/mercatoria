<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\SplitBoxBatch;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SplitBoxBatchController extends Controller
{
    public function __construct(private SplitBoxService $splitBox)
    {
        $this->middleware('auth');
        $this->middleware('can:manage-split-box');
    }

    /**
     * PRD 49.11: Tutup Kloter & Refund Semua.
     */
    public function closeRefund(Request $request, SplitBoxBatch $split_box_batch): RedirectResponse
    {
        // Guard: jangan double-close
        if ($split_box_batch->status !== SplitBoxBatch::STATUS_OPEN) {
            return redirect()->back()->with('error', 'Kloter ini sudah ditutup.');
        }

        $before = $split_box_batch->only(['status']);
        $count = $this->splitBox->closeBatchAndRefund($split_box_batch, $request->user());

        AdminLog::record($request->user(), 'update', 'SplitBoxBatch', $split_box_batch->id,
            $before, ['status' => $split_box_batch->fresh()->status],
            "Tutup kloter & refund {$count} order");

        return redirect()->back()->with('status', "Kloter ditutup. {$count} permintaan refund dibuat (transfer manual).");
    }
}
