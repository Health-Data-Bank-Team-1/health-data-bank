<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\ProviderFeedback;
use App\Services\AuditLogger;

class ProviderFeedbackController extends Controller
{
    public function create(string $patient)
    {
        $patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->firstOrFail();

        return view('provider.feedback', [
            'patient' => $patientAccount,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => ['required', 'uuid', 'exists:accounts,id'],
            'feedback' => ['required', 'string'],
            'recommended_actions' => ['nullable', 'string'],
        ]);

        $providerAccountId = Auth::user()->account_id;

        $feedback = ProviderFeedback::create([
            'patient_account_id' => $request->patient_id,
            'provider_account_id' => $providerAccountId,
            'feedback' => $request->feedback,
            'recommended_actions' => $request->recommended_actions,
        ]);

        AuditLogger::log(
            'provider_feedback_submitted',
            ['security', 'auth', 'outcome:success'],
            null,
            [],
            []
        );

        return response()->json($feedback, 201);
    }
}