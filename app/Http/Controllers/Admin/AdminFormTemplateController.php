<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use Illuminate\Http\Request;

class AdminFormTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = FormTemplate::query();

        //filter by approval status (pending/approved/rejected/draft)
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->string('approval_status'));
        }

        //search by title
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('title', 'like', '%' . $search . '%');
        }

        $allowedSorts = ['created_at', 'title', 'approval_status', 'version'];
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $query->orderBy($sortBy, $sortDir);

        //pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        return response()->json($query->paginate($perPage));
    }
}
