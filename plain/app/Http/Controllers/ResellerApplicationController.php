<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResellerApplicationRequest;
use App\Models\ActivityLog;
use App\Models\ResellerApplication;
use App\Support\LegalContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResellerApplicationController extends Controller
{
    public function create(): View
    {
        return view('reseller.create', [
            'termsHtml' => LegalContent::html('reseller'),
            'salesChannels' => ResellerApplication::SALES_CHANNELS,
            'monthlyEstimates' => ResellerApplication::MONTHLY_ESTIMATES,
        ]);
    }

    public function store(ResellerApplicationRequest $request): RedirectResponse
    {
        $application = new ResellerApplication($request->safe()->except(['accept_reseller_terms', 'cf-turnstile-response']));
        $application->user()->associate($request->user());
        $application->save();

        if ($request->user()) {
            ActivityLog::record($request->user(), 'reseller_apply', $request);
        }

        return redirect()->route('reseller.create')
            ->with('status', 'Pendaftaran reseller terkirim. Admin akan menghubungi kamu lewat WhatsApp.');
    }
}