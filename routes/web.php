<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\MyProgress;
use App\Livewire\UserFormSelect;
use App\Livewire\UserTodo;
use App\Livewire\FormIndex;
use App\Livewire\FormRenderer;
use App\Http\Controllers\FormTemplateController;
use App\Livewire\Admin\FormTemplatesIndex;
use App\Livewire\HealthSummary;
use App\Livewire\Dashboards\UserDashboard;
use App\Livewire\Profiles\UserProfile;
use App\Livewire\Dashboards\ResearcherDashboard;
use App\Livewire\Profiles\ResearcherProfile;
use App\Livewire\Researcher\ResearcherForms;
use App\Livewire\Researcher\ResearcherReports;
use App\Livewire\Dashboards\AdminDashboard;
use App\Livewire\Profiles\AdminProfile;
use App\Livewire\Admin\AuditLog;
use App\Livewire\Admin\DatabaseManagement;
use App\Livewire\Admin\ReportReview;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        if (Auth::user()->hasRole('user')) {
            return redirect('/dashboard-user');
        } elseif (Auth::user()->hasRole('researcher')) {
            return redirect('/dashboard-researcher');
        } elseif (Auth::user()->hasRole('admin')) {
            return redirect('/dashboard-admin');
        }
    });
    Route::get('/user-profile', UserProfile::class)
        ->middleware('role:user')
        ->name('user-profile');
    Route::get('/dashboard-user', UserDashboard::class)
        ->middleware('role:user')
        ->name('dashboards.user');
    Route::get('/my-progress', MyProgress::class)
        ->middleware('role:user')
        ->name('my-progress');
    Route::get('/user-form-select', UserFormSelect::class)
        ->middleware('role:user')
        ->name('user-form-select');
    Route::get('/user-todo', UserTodo::class)
        ->middleware('role:user')
        ->name('user-todo');
    Route::get('/forms', FormIndex::class)
        ->middleware('role:user')
        ->name('forms.index');
    Route::get('/forms/{form}', FormRenderer::class)
        ->middleware('role:user')
        ->name('forms.show');
    Route::get('/health-summary', HealthSummary::class)
        ->middleware('role:user')
        ->name('health-summary');

    Route::get('/researcher-profile', ResearcherProfile::class)
        ->middleware('role:researcher')
        ->name('researcher-profile');
    Route::get('/dashboard-researcher', ResearcherDashboard::class)
        ->middleware('role:researcher')
        ->name('dashboards.researcher');
    Route::get('/researcher-forms', ResearcherForms::class)
        ->middleware('role:researcher')
        ->name('researcher.forms');
    Route::get('/researcher-reports', ResearcherReports::class)
        ->middleware('role:researcher')
        ->name('researcher.reports');

    Route::get('/admin-profile', AdminProfile::class)
        ->middleware('role:admin')
        ->name('admin-profile');
    Route::get('/dashboard-admin', AdminDashboard::class)
        ->middleware('role:admin')
        ->name('dashboards.admin');
    Route::get('/admin-audit-log', AuditLog::class)
        ->middleware('role:admin')
        ->name('admin.audit-log');
    Route::get('/admin-database-management', DatabaseManagement::class)
        ->middleware('role:admin')
        ->name('admin.database-management');
    Route::get('/admin-report-review', ReportReview::class)
        ->middleware('role:admin')
        ->name('admin.report-review');
    //admin UI page (Livewire)
    Route::get('/admin/forms', FormTemplatesIndex::class)
        ->middleware('role:admin')
        ->name('admin.forms.index');

    Route::prefix('form-templates')->group(function () {
        Route::post('/', [FormTemplateController::class, 'store'])->name('form-templates.store');
        Route::put('{template}', [FormTemplateController::class, 'update'])->name('form-templates.update');
    });
});
