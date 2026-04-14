<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\Api\Reports\DashboardReportController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\Provider\ProviderFeedbackController;
use App\Livewire\Admin\AuditLog;
use App\Livewire\Admin\DatabaseManagement;
use App\Livewire\Admin\FormTemplatesIndex;
use App\Livewire\Admin\ReportReview;
use App\Livewire\Admin\SchemaManagement;
use App\Livewire\Admin\UserRoleManagement;
use App\Livewire\Dashboards\AdminDashboard;
use App\Livewire\Dashboards\ProviderDashboard;
use App\Livewire\Dashboards\ResearcherDashboard;
use App\Livewire\Dashboards\UserDashboard;
use App\Livewire\FormIndex;
use App\Livewire\FormRenderer;
use App\Livewire\HealthGoals;
use App\Livewire\HealthSummary;
use App\Livewire\MyProgress;
use App\Livewire\Notifications;
use App\Livewire\PersonalComparison;
use App\Livewire\PersonalComparisonChart;
use App\Livewire\Profiles\AdminProfile;
use App\Livewire\Profiles\ProviderProfile;
use App\Livewire\Profiles\ResearcherProfile;
use App\Livewire\Profiles\UserProfile;
use App\Livewire\Provider\PatientIndex;
use App\Livewire\Provider\PatientRenderer;
use App\Livewire\Provider\ProviderPatients;
use App\Livewire\Provider\ProviderReports;
use App\Livewire\Researcher\CohortBuilder;
use App\Livewire\Researcher\ReportIndex;
use App\Livewire\Researcher\ResearcherForms;
use App\Livewire\Researcher\ResearcherReportGenerator;
use App\Livewire\Researcher\ResearcherReports;
use App\Livewire\UserFormSelect;
use App\Livewire\UserSuggestions;
use App\Livewire\UserTodo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect('/admin/dashboard');
        } elseif ($user->hasRole('researcher')) {
            return redirect('/researcher/dashboard');
        } elseif ($user->hasRole('provider')) {
            return redirect('/provider/dashboard');
        } elseif ($user->hasRole('user')) {
            return redirect('/user/dashboard');
        }

        abort(403);
    })->name('dashboard');

    // User routes
    Route::get('/user/profile', UserProfile::class)->middleware('role:user')->name('user-profile');
    Route::get('/user/dashboard', UserDashboard::class)->middleware('role:user')->name('dashboards.user');
    Route::get('/user/my-progress', MyProgress::class)->middleware('role:user')->name('my-progress');
    Route::get('/user/form-select', UserFormSelect::class)->middleware('role:user')->name('user-form-select');
    Route::get('/user/todo', UserTodo::class)->middleware('role:user')->name('user-todo');
    Route::get('/user/forms', FormIndex::class)->middleware('role:user')->name('forms.index');
    Route::get('/user/forms/{form}', FormRenderer::class)->middleware('role:user')->name('forms.show');
    Route::get('/user/health-summary', HealthSummary::class)->middleware('role:user')->name('health-summary');
    Route::get('/user/suggestions', UserSuggestions::class)->middleware('role:user')->name('user-suggestions');

    // Researcher routes
    Route::get('/researcher/profile', ResearcherProfile::class)->middleware('role:researcher')->name('researcher-profile');
    Route::get('/researcher/dashboard', ResearcherDashboard::class)->middleware('role:researcher')->name('dashboards.researcher');
    Route::get('/researcher/forms', ResearcherForms::class)->middleware('role:researcher')->name('researcher.forms');
    Route::get('/researcher/reports', ResearcherReports::class)->middleware('role:researcher')->name('researcher.reports');
    Route::get('/researcher/report-generator', ResearcherReportGenerator::class)->middleware('role:researcher')->name('researcher.report-generator');
    Route::get('/researcher/report-index', ReportIndex::class)->middleware('role:researcher')->name('researcher.report-index');
    Route::get('/researcher/reports/{report}', ResearcherReports::class)->middleware('role:researcher')->name('researcher.reports.show');
    Route::get('/researcher/cohort', CohortBuilder::class)->middleware('role:researcher')->name('researcher.cohort');

    // Admin routes
    Route::get('/admin/profile', AdminProfile::class)->middleware('role:admin')->name('admin-profile');
    Route::get('/admin/dashboard', AdminDashboard::class)->middleware('role:admin')->name('dashboards.admin');
    Route::get('/admin/audit-log', AuditLog::class)->middleware('role:admin')->name('admin.audit-log');
    Route::get('/admin/database-management', DatabaseManagement::class)->middleware('role:admin')->name('admin.database-management');
    Route::get('/admin/report-review', ReportReview::class)->middleware('role:admin')->name('admin.report-review');
    Route::get('/admin/forms', FormTemplatesIndex::class)->middleware('role:admin')->name('admin.forms.index');
    Route::get('/admin/user-roles', UserRoleManagement::class)->middleware('role:admin')->name('admin.user-roles');
    Route::get('/admin/schema-management', SchemaManagement::class)->middleware('role:admin')->name('admin.schema-management');

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('/forms/{template}', [FormTemplateApprovalController::class, 'show'])
            ->name('livewire.admin.show');
    });

    Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/audit-log/export.csv', [AdminAuditLogController::class, 'exportCsv'])
            ->name('audit-log.export');
    });

    // Provider routes
    Route::get('/provider/patients/{patient}/feedback', [ProviderFeedbackController::class, 'create'])
        ->middleware('role:provider')
        ->name('provider.feedback');

    Route::get('/provider/profile', ProviderProfile::class)->middleware('role:provider')->name('provider-profile');
    Route::get('/provider/dashboard', ProviderDashboard::class)->middleware('role:provider')->name('dashboards.provider');
    Route::get('/provider/patients', ProviderPatients::class)->middleware('role:provider')->name('provider.patients');
    Route::get('/provider/reports', ProviderReports::class)->middleware('role:provider')->name('provider.reports');
    Route::get('/provider/patient-index', PatientIndex::class)->middleware('role:provider')->name('provider.patient-index');
    Route::get('/provider/patients/{patient}', PatientRenderer::class)->middleware('role:provider')->name('provider.patients.show');

    // Form templates create/update
    Route::prefix('form-templates')->group(function () {
        Route::post('/', [FormTemplateController::class, 'store'])->name('form-templates.store');
        Route::put('{template}', [FormTemplateController::class, 'update'])->name('form-templates.update');
    });

    // Reports
    Route::get('/reports/dashboard/trends', [DashboardReportController::class, 'trends'])->name('dashboard.trends');
    Route::get('/reports/dashboard/trends/export.csv', [DashboardReportController::class, 'exportTrendsCsv'])->name('dashboard.trends.export');

    // Health goals
    Route::get('/health-goals', HealthGoals::class)->name('health-goals');

    // Comparison
    Route::get('/comparison', PersonalComparison::class)->middleware(['auth'])->name('comparison');
    Route::get('/comparison/chart', PersonalComparisonChart::class)->middleware(['auth'])->name('comparison.chart');

    // Notifications
    Route::get('/notifications', Notifications::class)->middleware(['auth'])->name('notifications.index');

    Route::get('/notifications/{notification}/open', function (\App\Models\Notification $notification) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!isset($user->account_id) || $notification->account_id !== $user->account_id) {
            abort(403);
        }

        $notification->status = 'read';
        $notification->save();

        $link = $notification->link ?? null;
        if (is_string($link) && $link !== '') {
            return redirect($link);
        }

        return redirect()->route('notifications.index');
    })->middleware(['auth'])->name('notifications.open');
});
