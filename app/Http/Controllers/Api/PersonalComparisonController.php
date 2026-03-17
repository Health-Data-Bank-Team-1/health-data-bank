<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PersonalComparisonService;
use Illuminate\Http\Request;

class PersonalComparisonController extends Controller
{
    public function __construct(
        private PersonalComparisonService $service
    ) {}

    public function show(Request $request)
    {
        $request->validate([
            'metric_key' => ['required'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date']
        ]);

        $result = $this->service->compare(
            $request->metric_key,
            $request->from,
            $request->to,
            $request->only([
                'gender',
                'location',
                'age_min',
                'age_max'
            ])
        );

        return response()->json($result->toArray());
    }
}
