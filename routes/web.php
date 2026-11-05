<?php

use App\Http\Controllers\Admin\CalibrationController;
use App\Http\Controllers\Admin\ConfigurationAuditController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\CustomerRequestController;
use App\Http\Controllers\Admin\DeliveryRecordController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentManufacturerController;
use App\Http\Controllers\Admin\KplcEquipmentTypeController;
use App\Http\Controllers\Admin\QualityController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Models\Calibration;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserGuideController;
use App\Http\Controllers\Admin\UserGuideController as AdminUserGuideController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\QualityDashboardController;
use App\Http\Controllers\QualityObjectiveController;
use App\Http\Controllers\QuarterlyAchievementController;
use App\Http\Controllers\CorrectiveActionController;
use App\Http\Controllers\QualityReportController;
use App\Http\Controllers\CertificateCheckController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\LaboratoryConfigurationController;
use App\Http\Controllers\Admin\KplcServiceTypeController;
use App\Http\Controllers\Admin\EquipmentCategoryController;
use App\Http\Controllers\Admin\AiAssistantController;
use App\Http\Controllers\Payments\MpesaPaymentController;
use App\Http\Controllers\PublicQuoteRequestController as PublicQuoteRequestSubmissionController;
use App\Http\Controllers\Admin\PublicQuoteRequestController as AdminPublicQuoteRequestController;
use App\Http\Controllers\SystemDocumentController;
use App\Http\Controllers\Admin\CustomerVatVerificationController;
use App\Http\Controllers\Admin\EquipmentEnvironmentalConditionsController;
use App\Http\Controllers\Admin\EquipmentTestReviewController;
use App\Http\Controllers\Admin\EquipmentTestReportController;

// Add this temporary route at the top of your routes file
Route::get('/test-bulk', function() {
    return 'Bulk upload test route is working!';
});

// Redirect root to login
// Route::get('/', function () {
//     return redirect()->route('login');

// });

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role === 'customer') {
        return redirect()->route('customer.dashboard');
    }
    return redirect()->route('login');
});

// Route::view('/', 'welcome')->name('home');

Route::get('/', [HomeController::class, 'index'])
    ->name('home');
// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});

// Route::post('/request-calibration-quote',[PublicQuoteRequestController::class,'store',])->name('public.quote-request.store');
Route::post('/quote-request',[PublicQuoteRequestSubmissionController::class, 'store'])->name('public.quote-request.store');
// ============================================ //
// AUTHENTICATION ROUTES                        //
// ============================================ //

// Authentication Routes - Laravel UI
Auth::routes();

// ============================================ //
// PASSWORD RESET ROUTES - Must be outside auth //
// middleware for users to request reset        //
// ============================================ //
Route::get('/password/reset', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
Route::post('/password/resend', [PasswordResetController::class, 'resendLink'])->name('password.resend');

// After login, redirect to dashboard
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')
    ->prefix('laboratory-configuration')
    ->group(function () {

        Route::get(
            '/',
            [LaboratoryConfigurationController::class, 'index']
        )
            ->middleware(
                'permission:view_service_types|view_equipment_types|view_equipment_categories|view_equipment_manufacturers'
            )
            ->name('laboratory-configuration.index');

       Route::get(
    '/laboratory-configuration/audit',
    [ConfigurationAuditController::class, 'index']
  )
    ->middleware([
        'auth',
        'permission:view_laboratory_configuration_audit',
    ])
    ->name('laboratory-configuration.audit');

        /*
        |--------------------------------------------------------------------------
        | Equipment Types and Pricing
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/equipment-types',
            [KplcEquipmentTypeController::class, 'index']
        )
            ->middleware('permission:view_equipment_types')
            ->name('equipment-types.index');

        Route::get(
            '/equipment-types/create',
            [KplcEquipmentTypeController::class, 'create']
        )
            ->middleware('permission:create_equipment_types')
            ->name('equipment-types.create');

        Route::post(
            '/equipment-types',
            [KplcEquipmentTypeController::class, 'store']
        )
            ->middleware('permission:create_equipment_types')
            ->name('equipment-types.store');

        Route::get(
            '/equipment-types/{equipmentType}',
            [KplcEquipmentTypeController::class, 'show']
        )
            ->middleware('permission:view_equipment_types')
            ->name('equipment-types.show');

        Route::get(
            '/equipment-types/{equipmentType}/edit',
            [KplcEquipmentTypeController::class, 'edit']
        )
            ->middleware('permission:edit_equipment_types')
            ->name('equipment-types.edit');

        Route::put(
            '/equipment-types/{equipmentType}',
            [KplcEquipmentTypeController::class, 'update']
        )
            ->middleware('permission:edit_equipment_types')
            ->name('equipment-types.update');

        Route::delete(
            '/equipment-types/{equipmentType}',
            [KplcEquipmentTypeController::class, 'destroy']
        )
            ->middleware('permission:delete_equipment_types')
            ->name('equipment-types.destroy');

        /*
        |--------------------------------------------------------------------------
        | Laboratory Service Types
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/service-types',
            [KplcServiceTypeController::class, 'index']
        )
            ->middleware('permission:view_service_types')
            ->name('service-types.index');

        Route::get(
            '/service-types/create',
            [KplcServiceTypeController::class, 'create']
        )
            ->middleware('permission:create_service_types')
            ->name('service-types.create');

        Route::post(
            '/service-types',
            [KplcServiceTypeController::class, 'store']
        )
            ->middleware('permission:create_service_types')
            ->name('service-types.store');

        Route::get(
            '/service-types/{serviceType}',
            [KplcServiceTypeController::class, 'show']
        )
            ->middleware('permission:view_service_types')
            ->name('service-types.show');

        Route::get(
            '/service-types/{serviceType}/edit',
            [KplcServiceTypeController::class, 'edit']
        )
            ->middleware('permission:edit_service_types')
            ->name('service-types.edit');

        Route::put(
            '/service-types/{serviceType}',
            [KplcServiceTypeController::class, 'update']
        )
            ->middleware('permission:edit_service_types')
            ->name('service-types.update');

        Route::patch(
            '/service-types/{serviceType}/toggle-status',
            [KplcServiceTypeController::class, 'toggleStatus']
        )
            ->middleware('permission:edit_service_types')
            ->name('service-types.toggle-status');

        Route::delete(
            '/service-types/{serviceType}',
            [KplcServiceTypeController::class, 'destroy']
        )
            ->middleware('permission:delete_service_types')
            ->name('service-types.destroy');

        /*
        |--------------------------------------------------------------------------
        | Equipment Categories
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/equipment-categories',
            [EquipmentCategoryController::class, 'index']
        )
            ->middleware('permission:view_equipment_categories')
            ->name('equipment-categories.index');

        Route::get(
            '/equipment-categories/create',
            [EquipmentCategoryController::class, 'create']
        )
            ->middleware('permission:create_equipment_categories')
            ->name('equipment-categories.create');

        Route::post(
            '/equipment-categories',
            [EquipmentCategoryController::class, 'store']
        )
            ->middleware('permission:create_equipment_categories')
            ->name('equipment-categories.store');

        Route::get(
            '/equipment-categories/{equipmentCategory}',
            [EquipmentCategoryController::class, 'show']
        )
            ->middleware('permission:view_equipment_categories')
            ->name('equipment-categories.show');

        Route::get(
            '/equipment-categories/{equipmentCategory}/edit',
            [EquipmentCategoryController::class, 'edit']
        )
            ->middleware('permission:edit_equipment_categories')
            ->name('equipment-categories.edit');

        Route::put(
            '/equipment-categories/{equipmentCategory}',
            [EquipmentCategoryController::class, 'update']
        )
            ->middleware('permission:edit_equipment_categories')
            ->name('equipment-categories.update');

        Route::patch(
            '/equipment-categories/{equipmentCategory}/toggle-status',
            [EquipmentCategoryController::class, 'toggleStatus']
        )
            ->middleware('permission:edit_equipment_categories')
            ->name('equipment-categories.toggle-status');

        Route::delete(
            '/equipment-categories/{equipmentCategory}',
            [EquipmentCategoryController::class, 'destroy']
        )
            ->middleware('permission:delete_equipment_categories')
            ->name('equipment-categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Equipment Manufacturers
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/equipment-manufacturers',
            [EquipmentManufacturerController::class, 'index']
        )
            ->middleware('permission:view_equipment_manufacturers')
            ->name('equipment-manufacturers.index');

        Route::get(
            '/equipment-manufacturers/create',
            [EquipmentManufacturerController::class, 'create']
        )
            ->middleware('permission:create_equipment_manufacturers')
            ->name('equipment-manufacturers.create');

        Route::post(
            '/equipment-manufacturers',
            [EquipmentManufacturerController::class, 'store']
        )
            ->middleware('permission:create_equipment_manufacturers')
            ->name('equipment-manufacturers.store');

        Route::get(
            '/equipment-manufacturers/{equipmentManufacturer}',
            [EquipmentManufacturerController::class, 'show']
        )
            ->middleware('permission:view_equipment_manufacturers')
            ->name('equipment-manufacturers.show');

        Route::get(
            '/equipment-manufacturers/{equipmentManufacturer}/edit',
            [EquipmentManufacturerController::class, 'edit']
        )
            ->middleware('permission:edit_equipment_manufacturers')
            ->name('equipment-manufacturers.edit');

        Route::put(
            '/equipment-manufacturers/{equipmentManufacturer}',
            [EquipmentManufacturerController::class, 'update']
        )
            ->middleware('permission:edit_equipment_manufacturers')
            ->name('equipment-manufacturers.update');

        Route::patch(
            '/equipment-manufacturers/{equipmentManufacturer}/toggle-status',
            [EquipmentManufacturerController::class, 'toggleStatus']
        )
            ->middleware('permission:edit_equipment_manufacturers')
            ->name('equipment-manufacturers.toggle-status');

        Route::delete(
            '/equipment-manufacturers/{equipmentManufacturer}',
            [EquipmentManufacturerController::class, 'destroy']
        )
            ->middleware('permission:delete_equipment_manufacturers')
            ->name('equipment-manufacturers.destroy');

    });

    /*
|--------------------------------------------------------------------------
| System Documentation
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
])->prefix('system-documents')
    ->name('system-documents.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Library
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [
                SystemDocumentController::class,
                'index'
            ]
        )
            ->middleware(
                'permission:view_system_documents'
            )
            ->name('index');

        Route::get(
            '/create',
            [
                SystemDocumentController::class,
                'create'
            ]
        )
            ->middleware(
                'permission:upload_system_documents'
            )
            ->name('create');

        Route::post(
            '/',
            [
                SystemDocumentController::class,
                'store'
            ]
        )
            ->middleware(
                'permission:upload_system_documents'
            )
            ->name('store');

        /*
        |--------------------------------------------------------------------------
        | Download
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{document}/download',
            [
                SystemDocumentController::class,
                'download'
            ]
        )
            ->middleware(
                'permission:download_system_documents'
            )
            ->name('download');

        /*
        |--------------------------------------------------------------------------
        | Approval
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/{document}/approve',
            [
                SystemDocumentController::class,
                'approve'
            ]
        )
            ->middleware(
                'permission:approve_system_documents'
            )
            ->name('approve');

        /*
        |--------------------------------------------------------------------------
        | Acknowledge
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/{document}/acknowledge',
            [
                SystemDocumentController::class,
                'acknowledge'
            ]
        )
            ->middleware(
                'permission:view_system_documents'
            )
            ->name('acknowledge');

        /*
        |--------------------------------------------------------------------------
        | Archive
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/{document}/archive',
            [
                SystemDocumentController::class,
                'archive'
            ]
        )
            ->middleware(
                'permission:archive_system_documents'
            )
            ->name('archive');

        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/{document}',
            [
                SystemDocumentController::class,
                'destroy'
            ]
        )
            ->middleware(
                'permission:delete_system_documents'
            )
            ->name('destroy');

/*
|--------------------------------------------------------------------------
| Edit document metadata
|--------------------------------------------------------------------------
*/

Route::get(
    '/{document}/edit',
    [
        SystemDocumentController::class,
        'edit'
    ]
)
    ->middleware('permission:edit_system_documents')
    ->name('edit');


Route::put(
    '/{document}',
    [
        SystemDocumentController::class,
        'update'
    ]
)
    ->middleware('permission:edit_system_documents')
    ->name('update');


/*
|--------------------------------------------------------------------------
| Upload new version
|--------------------------------------------------------------------------
*/

Route::get(
    '/{document}/versions/create',
    [
        SystemDocumentController::class,
        'createVersion'
    ]
)
    ->middleware('permission:edit_system_documents')
    ->name('versions.create');


Route::post(
    '/{document}/versions',
    [
        SystemDocumentController::class,
        'storeVersion'
    ]
)
    ->middleware('permission:edit_system_documents')
    ->name('versions.store');


/*
|--------------------------------------------------------------------------
| Download historical version
|--------------------------------------------------------------------------
*/

Route::get(
    '/{document}/versions/{version}/download',
    [
        SystemDocumentController::class,
        'downloadVersion'
    ]
)
    ->middleware('permission:download_system_documents')
    ->name('versions.download');


/*
|--------------------------------------------------------------------------
| Acknowledgement compliance
|--------------------------------------------------------------------------
*/

Route::get(
    '/{document}/acknowledgements',
    [
        SystemDocumentController::class,
        'acknowledgements'
    ]
)
    ->middleware('permission:manage_system_documents')
    ->name('acknowledgements');



/*
|--------------------------------------------------------------------------
| Restore archived document
|--------------------------------------------------------------------------
*/

Route::post(
    '/{document}/restore',
    [
        SystemDocumentController::class,
        'restore'
    ]
)
    ->middleware('permission:archive_system_documents')
    ->name('restore');

/*
|--------------------------------------------------------------------------
| Document Review
|--------------------------------------------------------------------------
*/

Route::get(
    '/{document}/review',
    [
        SystemDocumentController::class,
        'reviewForm'
    ]
)
    ->middleware(
        'permission:review_system_documents'
    )
    ->name('review');


Route::post(
    '/{document}/review',
    [
        SystemDocumentController::class,
        'storeReview'
    ]
)
    ->middleware(
        'permission:review_system_documents'
    )
    ->name('review.store');


/*
|--------------------------------------------------------------------------
| Review History
|--------------------------------------------------------------------------
*/

Route::get(
    '/{document}/reviews',
    [
        SystemDocumentController::class,
        'reviewHistory'
    ]
)
    ->middleware(
        'permission:view_system_documents'
    )
    ->name('reviews');

    Route::get(
    '/dashboard/overview',
    [
        SystemDocumentController::class,
        'dashboard'
    ]
)
    ->middleware(
        'permission:manage_system_documents'
    )
    ->name('dashboard');
    /*
|--------------------------------------------------------------------------
| Documentation Reports
|--------------------------------------------------------------------------
*/

Route::get(
    '/reports/overview',
    [
        SystemDocumentController::class,
        'reports'
    ]
)
    ->middleware('permission:manage_system_documents')
    ->name('reports');


Route::get(
    '/reports/acknowledgements/export',
    [
        SystemDocumentController::class,
        'exportAcknowledgements'
    ]
)
    ->middleware('permission:manage_system_documents')
    ->name('reports.acknowledgements.export');


Route::get(
    '/reports/reviews/export',
    [
        SystemDocumentController::class,
        'exportReviews'
    ]
)
    ->middleware('permission:manage_system_documents')
    ->name('reports.reviews.export');


Route::get(
    '/reports/register/export',
    [
        SystemDocumentController::class,
        'exportRegister'
    ]
)
    ->middleware('permission:manage_system_documents')
    ->name('reports.register.export');

        /*
        |--------------------------------------------------------------------------
        | Show MUST remain after /create
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{document}',
            [
                SystemDocumentController::class,
                'show'
            ]
        )
            ->middleware(
                'permission:view_system_documents'
            )
            ->name('show');
});

    /*
|--------------------------------------------------------------------------
| AI Assistant
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:view_ai_assistant',
])
    ->prefix('ai-assistant')
    ->name('ai-assistant.')
    ->group(function () {
        Route::get(
            '/',
            [AiAssistantController::class, 'index']
        )->name('index');

        Route::post(
            '/',
            [AiAssistantController::class, 'store']
        )
            ->middleware('permission:use_ai_assistant')
            ->name('store');

        Route::post(
            '/start-and-send',
            [AiAssistantController::class, 'startAndSend']
        )
            ->middleware([
        'permission:use_ai_assistant',
        'throttle:ai-assistant',
    ])
    ->name('start-and-send');

        Route::get(
            '/{conversation}',
            [AiAssistantController::class, 'show']
        )
            ->whereNumber('conversation')
            ->middleware('permission:view_ai_conversations')
            ->name('show');

        Route::post(
            '/{conversation}/send',
            [AiAssistantController::class, 'send']
        )
            ->whereNumber('conversation')
             ->middleware([
        'permission:use_ai_assistant',
        'throttle:ai-assistant',
    ])
    ->name('send');

        Route::patch(
            '/{conversation}',
            [AiAssistantController::class, 'update']
        )
            ->whereNumber('conversation')
            ->middleware('permission:use_ai_assistant')
            ->name('update');

        Route::delete(
            '/{conversation}',
            [AiAssistantController::class, 'destroy']
        )
            ->whereNumber('conversation')
            ->middleware('permission:delete_ai_conversations')
            ->name('destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/profile/signature', [ProfileController::class, 'uploadSignature'])
        ->name('profile.signature.upload');

    Route::delete('/profile/signature', [ProfileController::class, 'deleteSignature'])
        ->name('profile.signature.delete');

    Route::put('/profile/auto-signature', [ProfileController::class, 'updateAutoSignature'])
        ->name('profile.auto-signature.update');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');
});

// ============================================ //
// PROTECTED ROUTES - Require Authentication    //
// ============================================ //
Route::middleware(['auth'])->group(function () {

    // ============================================ //
    // DASHBOARD ROUTE                             //
    // ============================================ //
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================ //
    // CUSTOMER MANAGEMENT                         //
    // ============================================ //
    Route::resource('customers', CustomerController::class)
        ->names('customers')
        ->except(['show']);

    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    // Customer quick actions
    Route::post('customers/quick-add', [CustomerController::class, 'quickAdd'])->name('customers.quick-add');
    Route::get('customers/{customer}/details', [CustomerController::class, 'getDetails'])->name('customers.details');
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
// Customer Dashboard Routes
   Route::prefix('customer')->name('customer.')->middleware('role:customer')->group(function () {

        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

        Route::get('/calibrations', [CustomerDashboardController::class, 'calibrations'])->name('calibrations');

        Route::get('/calibrations/{id}', [CustomerDashboardController::class, 'viewCalibration'])
            ->whereNumber('id')->name('calibrations.show');

        Route::get('/quotes', [CustomerDashboardController::class, 'quotes'])->name('quotes');

        Route::get('/quotes/{id}', [CustomerDashboardController::class, 'viewQuote'])->whereNumber('id')->name('quotes.show');

        Route::get('/certificates', [CustomerDashboardController::class, 'certificates'])->name('certificates');

        Route::get('/certificates/{id}', [CustomerDashboardController::class, 'viewCertificate'])->whereNumber('id')->name('certificates.show');

        Route::get('/job-cards', [CustomerDashboardController::class, 'jobCards'])->name('job-cards');

        Route::get('/delivery-notes', [CustomerDashboardController::class, 'deliveryNotes'])->name('delivery-notes');

        Route::get('/collection-notes', [CustomerDashboardController::class, 'collectionNotes'])->name('collection-notes');

        Route::get('/request-forms', [CustomerDashboardController::class, 'requestForms'])->name('request-forms');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::get(
    '/calibrations/lookup-by-serial',
    [CalibrationController::class, 'lookupBySerial']
)->name('calibrations.lookup-by-serial');

    // ============================================ //
    // CALIBRATION MANAGEMENT                      //
    // ============================================ //
    Route::prefix('calibrations')->name('calibrations.')->group(function () {
    //    Route::get('/equipment/lookup',[CalibrationController::class, 'lookupEquipment'])->name('equipment.lookup');

        // ============================================ //
        // INDEX ROUTES                                //
        // ============================================ //
        Route::get('/', [CalibrationController::class, 'index'])->name('index');

        // ============================================ //
        // BATCH MANAGEMENT ROUTES                     //
        // ============================================ //
        Route::get('/batches', [CalibrationController::class, 'batches'])->name('batches');
        Route::get('/batches/{batch_id}', [CalibrationController::class, 'batchDetails'])->name('batch.details');
        Route::post('/batches/{batch_id}/delete', [CalibrationController::class, 'deleteBatch'])->name('batch.delete');
        Route::post('/batches/{batch_id}/process', [CalibrationController::class, 'processBatchAction'])->name('batch.process');

        // ============================================ //
        // BULK UPLOAD ROUTES                          //
        // ============================================ //
        Route::get('/bulk-upload', [CalibrationController::class, 'bulkUpload'])->name('bulk-upload');
        Route::post('/bulk-upload', [CalibrationController::class, 'processBulkUpload'])->name('bulk-upload.process');
        Route::get('/download-template', [CalibrationController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/bulk-upload/summary', [CalibrationController::class, 'bulkUploadSummary'])->name('bulk-upload.summary');
        Route::get('/bulk-upload/progress', [CalibrationController::class, 'getBulkUploadProgress'])->name('bulk-upload.progress');

        // ============================================ //
        // BULK ACTION ROUTES                          //
        // ============================================ //
        Route::post('/bulk/payment/{batch_id}', [CalibrationController::class, 'bulkProcessPayment'])->name('bulk.payment');
        Route::post('/bulk/assign/{batch_id}', [CalibrationController::class, 'bulkAssignTechnicians'])->name('bulk.assign');
        Route::post('/bulk/generate-certs/{batch_id}', [CalibrationController::class, 'bulkGenerateCertificates'])->name('bulk.generate-certs');
        Route::post('/bulk/quote/{batch_id}', [CalibrationController::class, 'bulkGenerateQuotes'])->name('bulk.quote');

        Route::post('/{calibration}/complete-without-tests', [CalibrationController::class, 'completeWithoutTests'])->name('complete-without-tests');

        // ============================================ //
        // CREATE ROUTES                              //
        // ============================================ //
        Route::get('/create', [CalibrationController::class, 'create'])->name('create');
        Route::post('/', [CalibrationController::class, 'store'])->name('store');

        // ============================================ //
        // QUOTATION ROUTES                            //
        // ============================================ //
        Route::get('/{calibration}/quotation-form', [CalibrationController::class, 'showQuotationForm'])->name('quotation-form');
        Route::post('/{calibration}/generate-quotation', [CalibrationController::class, 'generateQuotation'])->name('generate-quotation');
        Route::post('/{calibration}/store-quotation', [CalibrationController::class, 'storeQuotation'])->name('store-quotation');
        Route::post('/{calibration}/accept-quotation', [CalibrationController::class, 'acceptQuotation'])->name('accept-quotation');
        Route::post('/{calibration}/process-payment', [CalibrationController::class, 'processPayment'])->name('process-payment');
        Route::post('/{calibration}/mark-payment', [CalibrationController::class, 'markPaymentReceived'])->name('mark-payment');
        Route::post('/{calibration}/send-quotation',[CalibrationController::class, 'sendQuotation'])->middleware(['auth','permission:send_quotations',])->name('send-quotation');
        // ============================================ //
        // WORKFLOW & ACTION ROUTES                   //
        // ============================================ //
        Route::post('/{calibration}/approve', [CalibrationController::class, 'approve'])->name('approve');
        Route::post('/{calibration}/reject', [CalibrationController::class, 'reject'])->name('reject');
        Route::post('/{calibration}/test', [CalibrationController::class, 'storeTestResult'])->name('test');
        Route::post('/{calibration}/complete', [CalibrationController::class, 'complete'])->name('complete');
        Route::post('/{calibration}/assign', [CalibrationController::class, 'assign'])->name('assign');
        Route::post('/{calibration}/start-work', [CalibrationController::class, 'startWork'])->name('start-work');
        Route::post('/{calibration}/certify', [CalibrationController::class, 'certify'])->name('certify');
        Route::post('/{calibration}/test-parameters',[CalibrationController::class,'storeTestParameters'])->name('test-parameters.store');
        // ============================================ //
        // DOCUMENT GENERATION ROUTES                 //
        // ============================================ //
        Route::get('/{calibration}/request-form', [CalibrationController::class, 'generateRequestForm'])->name('request-form');
        Route::get('/{calibration}/quotation', [CalibrationController::class, 'generateQuotationPDF'])->name('quotation');
        Route::get('/{calibration}/delivery-note/{type?}', [CalibrationController::class, 'generateDeliveryNote'])->name('delivery-note');
        Route::get('/{calibration}/job-card', [CalibrationController::class, 'generateJobCard'])->name('job-card');
        Route::get('/{calibration}/collection-note', function ($id) {
            $calibration = Calibration::findOrFail($id);
            return app(CalibrationController::class)->generateDeliveryNote($calibration, 'collection');
        })->name('collection-note');

        // Batch Document Routes
        Route::get('/batches/{batch_id}/job-card', [CalibrationController::class, 'batchJobCard'])->name('batch.job-card');
        Route::get('/batches/{batch_id}/request-form', [CalibrationController::class, 'batchRequestForm'])->name('batch.request-form');
        Route::get('/batches/{batch_id}/delivery-note', [CalibrationController::class, 'batchDeliveryNote'])->name('batch.delivery-note');
        Route::get('/batches/{batch_id}/collection-note', [CalibrationController::class, 'batchCollectionNote'])->name('batch.collection-note');

        // Batch Quotation PDF Routes
        Route::get('/batches/{batch_id}/quotation/download', [CalibrationController::class, 'viewBatchQuotation'])->name('batch.quotation.download');
        Route::get('/batches/{batch_id}/quotation/preview', [CalibrationController::class, 'previewBatchQuotation'])->name('batch.quotation.preview');

        // ============================================ //
        // EDIT & UPDATE ROUTES                       //
        // ============================================ //
        Route::get('/{calibration}/edit', [CalibrationController::class, 'edit'])->name('edit');
        Route::put('/{calibration}', [CalibrationController::class, 'update'])->name('update');
        Route::delete('/{calibration}', [CalibrationController::class, 'destroy'])->name('destroy');
        Route::put('/{calibration}/reassign', [CalibrationController::class, 'reassign'])->name('reassign');

        // ============================================ //
        // SHOW ROUTE - MUST BE LAST!                 //
        // ============================================ //
        Route::get('/{calibration}', [CalibrationController::class, 'show'])->name('show');
    });

    // ============================================ //
    // CERTIFICATE MANAGEMENT                      //
    // ============================================ //

Route::prefix('certificates')
    ->name('certificates.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Certificate Listing
        |--------------------------------------------------------------------------
        */
        Route::get('/', [
            CertificateController::class,
            'index'
        ])->name('index');

        /*
        |--------------------------------------------------------------------------
        | Generate Certificate from Calibration
        |--------------------------------------------------------------------------
        */
        Route::post('/generate/{calibration}', [
            CertificateController::class,
            'generate'
        ])
        ->whereNumber('calibration')
        ->name('generate');

        /*
        |--------------------------------------------------------------------------
        | Certificate Details
        |--------------------------------------------------------------------------
        | Restrict certificate parameters to numeric IDs so that
        | /certificates/check cannot be matched as a certificate.
        */
        Route::get('/{certificate}', [
            CertificateController::class,
            'show'
        ])
        ->whereNumber('certificate')
        ->name('show');

        /*
        |--------------------------------------------------------------------------
        | Test Report Upload and Download
        |--------------------------------------------------------------------------
        */
        Route::get('/{certificate}/upload-test-report', [
            CertificateController::class,
            'uploadTestReport'
        ])
        ->whereNumber('certificate')
        ->name('upload-test-report');

        Route::post('/{certificate}/store-test-report', [
            CertificateController::class,
            'storeTestReport'
        ])
        ->whereNumber('certificate')
        ->name('store-test-report');

        Route::get('/{certificate}/download-test-report', [
            CertificateController::class,
            'downloadTestReport'
        ])
        ->whereNumber('certificate')
        ->name('download-test-report');

        Route::get('/{certificate}/combined', [
            CertificateController::class,
            'downloadCombined'
        ])
        ->whereNumber('certificate')
        ->name('combined');

        Route::get('/{certificate}/download-combined', [
            CertificateController::class,
            'downloadCombined'
        ])
        ->whereNumber('certificate')
        ->name('download-combined');

        /*
        |--------------------------------------------------------------------------
        | Certificate Approval Workflow
        |--------------------------------------------------------------------------
        */
        Route::post('/{certificate}/check', [
            CertificateController::class,
            'check'
        ])
        ->whereNumber('certificate')
        ->name('check');

         Route::get('/export', [
            CertificateCheckController::class,
            'export'
        ])->name('export');

        Route::post('/{certificate}/approve', [
            CertificateController::class,
            'approve'
        ])
        ->whereNumber('certificate')
        ->name('approve');

        Route::post('/{certificate}/send', [
            CertificateController::class,
            'send'
        ])
        ->whereNumber('certificate')
        ->name('send');

        Route::post('/{certificate}/send-to-customer', [
            CertificateController::class,
            'sendToCustomer'
        ])
        ->whereNumber('certificate')
        ->name('send-to-customer');

        Route::post('/generate-item/{item}',[\App\Http\Controllers\Admin\CertificateController::class, 'generateForItem'])->name('generate-item');
Route::post('/{certificate}/return-for-statement-review',[\App\Http\Controllers\Admin\CertificateController::class, 'returnForStatementReview'])->name('return-for-statement-review');

        /*
        |--------------------------------------------------------------------------
        | Certificate PDF
        |--------------------------------------------------------------------------
        */
        Route::get('/{certificate}/download', [
            CertificateController::class,
            'download'
        ])
        ->whereNumber('certificate')
        ->name('download');

        Route::get('/{certificate}/preview', [
            CertificateController::class,
            'preview'
        ])
        ->whereNumber('certificate')
        ->name('preview');

        Route::get('/{certificate}/download/force', [
            CertificateController::class,
            'download'
        ])
        ->whereNumber('certificate')
        ->name('download.force');

        /*
        |--------------------------------------------------------------------------
        | Regenerate Certificate PDF
        |--------------------------------------------------------------------------
        */
        Route::post('/{certificate}/regenerate', [
            CertificateController::class,
            'regenerate'
        ])
        ->whereNumber('certificate')
        ->name('regenerate');
    });


    // ============================================ //
    // CALIBRATION TESTING DETAILS                  //
    // ============================================ //
    Route::prefix('calibrations/{calibration}/items/{item}')->name('calibrations.items.')->group(function () {
        Route::post('/submit-testing',[EquipmentTestReviewController::class, 'submit'])->name('submit-testing');

        Route::post('/review-testing',[EquipmentTestReviewController::class, 'review'])->name('review-testing');

        Route::post('/return-testing',[EquipmentTestReviewController::class, 'returnForCorrection'])->name('return-testing');

        Route::post('/reopen-testing',[EquipmentTestReviewController::class, 'reopen'])->name('reopen-testing');

        Route::post('/approve-testing',[EquipmentTestReviewController::class, 'approve'])->name('approve-testing');
    });

    Route::post('calibrations/{calibration}/items/{item}/test-report',[EquipmentTestReportController::class, 'store'])->name('calibrations.items.test-report.store');
    Route::get('calibrations/{calibration}/items/{item}/test-report/download',[EquipmentTestReportController::class, 'download'])->name('calibrations.items.test-report.download');

    // ============================================ //
    // USER MANAGEMENT                             //
    // ============================================ //
    Route::resource('users', UserController::class)->names('users');

    // ============================================ //
    // EQUIPMENT MANAGEMENT                        //
    // ============================================ //
    Route::resource('equipment', EquipmentController::class)->names('equipment');
    Route::get('/equipment/{equipment}/export', [EquipmentController::class, 'export'])->name('equipment.export')->middleware('auth');

    // ============================================ //
    // DELIVERY RECORDS                            //
    // ============================================ //
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [DeliveryRecordController::class, 'index'])->name('index');
        Route::get('/create', [DeliveryRecordController::class, 'create'])->name('create');
        Route::post('/', [DeliveryRecordController::class, 'store'])->name('store');
        Route::get('/{delivery}', [DeliveryRecordController::class, 'show'])->name('show');
        Route::get('/{delivery}/edit', [DeliveryRecordController::class, 'edit'])->name('edit');
        Route::put('/{delivery}', [DeliveryRecordController::class, 'update'])->name('update');
        Route::delete('/{delivery}', [DeliveryRecordController::class, 'destroy'])->name('destroy');
        Route::post('/{delivery}/dispatch', [DeliveryRecordController::class, 'dispatch'])->name('dispatch');
        Route::post('/{delivery}/deliver', [DeliveryRecordController::class, 'markDelivered'])->name('deliver');
        Route::post('/{delivery}/collect', [DeliveryRecordController::class, 'markCollected'])->name('collect');
        Route::post('/{delivery}/cancel', [DeliveryRecordController::class, 'cancel'])->name('cancel');
    });

    // ============================================ //
    // CUSTOMER REQUESTS                           //
    // ============================================ //
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [CustomerRequestController::class, 'index'])->name('index');
        Route::get('/create', [CustomerRequestController::class, 'create'])->name('create');
        Route::post('/', [CustomerRequestController::class, 'store'])->name('store');
        Route::get('/{request}', [CustomerRequestController::class, 'show'])->name('show');
        Route::get('/{request}/edit', [CustomerRequestController::class, 'edit'])->name('edit');
        Route::put('/{request}', [CustomerRequestController::class, 'update'])->name('update');

        // Quote routes
        Route::get('/{request}/generate-quote', [CustomerRequestController::class, 'generateQuote'])->name('generate-quote');
        Route::post('/{request}/store-quote', [CustomerRequestController::class, 'storeQuote'])->name('store-quote');
        Route::post('/{request}/approve-quote', [CustomerRequestController::class, 'approveQuote'])->name('approve-quote');

        // Job Card routes
        Route::get('/{request}/create-jobcard', [CustomerRequestController::class, 'createJobCard'])->name('create-jobcard');
        Route::post('/{request}/store-jobcard', [CustomerRequestController::class, 'storeJobCard'])->name('store-jobcard');

        // Laboratory receiving
        Route::get('/{request}/receive-equipment', [CustomerRequestController::class, 'receiveEquipment'])->name('receive-equipment');
        Route::post('/{request}/receive-equipment', [CustomerRequestController::class, 'storeReceivedEquipment'])->name('receive-equipment.store');

        // On-site scheduling
        Route::get('/{request}/schedule-site-visit', [CustomerRequestController::class, 'scheduleSiteVisit'])->name('schedule-site-visit');
        Route::post('/{request}/schedule-site-visit', [CustomerRequestController::class, 'storeSiteVisit'])->name('site-visit.store');

        // Collection
        Route::get('/{request}/collect-equipment', [CustomerRequestController::class, 'collectEquipment'])->name('collect-equipment');
        Route::post('/{request}/collect-equipment', [CustomerRequestController::class, 'storeCollection'])->name('collect.store');
    });

      // ============================================ //
    // USER GUIDE ROUTES                           //
    // ============================================ //
    Route::prefix('user-guide')->name('user-guide.')->group(function () {
        Route::get('/', [UserGuideController::class, 'index'])->name('index');
        Route::get('/search', [UserGuideController::class, 'search'])->name('search');
        Route::get('/category/{slug}', [UserGuideController::class, 'category'])->name('category');
        Route::get('/{slug}', [UserGuideController::class, 'show'])->name('show');

        // Feedback routes (requires authentication)
        Route::post('/feedback/{id}', [UserGuideController::class, 'feedback'])
            ->name('feedback')
            ->middleware('auth');
    });

    // Redirect to guide
    Route::get('/help', function () {
        return redirect()->route('user-guide.index');
    })->name('help');

    // ============================================ //
    // ADMIN USER GUIDE ROUTES (Admin only)        //
    // ============================================ //
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])
    ->middleware('permission:view_users')
    ->name('users.index');

Route::get('/users/create', [UserController::class, 'create'])
    ->middleware('permission:create_users')
    ->name('users.create');

Route::post('/users', [UserController::class, 'store'])
    ->middleware('permission:create_users')
    ->name('users.store');

Route::get('/users/{user}', [UserController::class, 'show'])
    ->middleware('permission:view_users')
    ->name('users.show');

Route::get('/users/{user}/edit', [UserController::class, 'edit'])
    ->middleware('permission:edit_users')
    ->name('users.edit');

// Route::put('/users/{user}', [UserController::class, 'update'])
//     ->middleware('permission:edit_users')
//     ->name('users.update');

// Route::patch('/users/{user}', [UserController::class, 'update'])
//     ->middleware('permission:edit_users');

    Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])
    ->middleware('permission:edit_users')
    ->name('users.update');

Route::delete('/users/{user}', [UserController::class, 'destroy'])
    ->middleware('permission:delete_users')
    ->name('users.destroy');

Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
    ->middleware('permission:edit_users')
    ->name('users.toggle-status');
        Route::get('/customers', [CustomerController::class, 'index'])
    ->middleware('permission:view_customers')
    ->name('customers.index');

Route::get('/customers/create', [CustomerController::class, 'create'])
    ->middleware('permission:create_customers')
    ->name('customers.create');

Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware('permission:create_customers')
    ->name('customers.store');

Route::get('/customers/{customer}', [CustomerController::class, 'show'])
    ->middleware('permission:view_customers')
    ->name('customers.show');

Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
    ->middleware('permission:edit_customers')
    ->name('customers.edit');

Route::match(['put', 'patch'], '/customers/{customer}', [CustomerController::class, 'update'])
    ->middleware('permission:edit_customers')
    ->name('customers.update');

/*
|--------------------------------------------------------------------------
| CUSTOMER ACCOUNT APPROVAL
|--------------------------------------------------------------------------
| Supervisor and Lab Manager only.
| Already inside prefix('admin')->name('admin.')
|--------------------------------------------------------------------------
*/

Route::middleware('role:supervisor|lab_manager')
    ->group(function () {

        Route::post(
            '/customers/{customer}/approve',
            [CustomerController::class, 'approve']
        )->name('customers.approve');

        Route::post(
            '/customers/{customer}/reject',
            [CustomerController::class, 'reject']
        )->name('customers.reject');
    });


/*
|--------------------------------------------------------------------------
| VAT EXEMPTION APPROVAL
|--------------------------------------------------------------------------
| Lab Manager only.
|--------------------------------------------------------------------------
*/

Route::middleware('role:lab_manager')
    ->group(function () {

        Route::post(
            '/customers/{customer}/vat/approve',
            [CustomerController::class, 'approveVat']
        )->name('customers.vat.approve');

        Route::post(
            '/customers/{customer}/vat/reject',
            [CustomerController::class, 'rejectVat']
        )->name('customers.vat.reject');
    });

    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
    ->middleware('permission:delete_customers')
    ->name('customers.destroy');

        Route::prefix('user-guide')->name('user-guide.')->group(function () {
            Route::get('/', [AdminUserGuideController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserGuideController::class, 'create'])->name('create');
            Route::post('/', [AdminUserGuideController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AdminUserGuideController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminUserGuideController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminUserGuideController::class, 'destroy'])->name('destroy');
        });


    Route::get('/quote-requests',[AdminPublicQuoteRequestController::class,'index'])->name('quote-requests.index');
    Route::get('/quote-requests/{quoteRequest}',[AdminPublicQuoteRequestController::class,'show'])->name('quote-requests.show');
    Route::post('/quote-requests/{quoteRequest}/convert',[AdminPublicQuoteRequestController::class,'convert'])->name('quote-requests.convert');
    Route::post('/quote-requests', [AdminPublicQuoteRequestController::class, 'store'])->middleware('throttle:10,1')->name('quote-request.store');
        // Toggle user status
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Quality Control Routes
       Route::prefix('quality')
    ->name('quality.')
    ->group(function () {

        Route::get('/dashboard', [QualityController::class, 'dashboard'])->middleware('permission:view_quality_dashboard')
            ->name('dashboard');

Route::get('/check/{calibration}', [QualityController::class, 'check'])
            ->middleware('permission:quality_check')->name('check');

        Route::post('/process-check/{calibration}', [QualityController::class, 'processCheck'])
            ->middleware('permission:quality_check|quality_approve|quality_reject')
            ->name('process-check');

        Route::get('/reports', [QualityController::class, 'reports'])
            ->middleware('permission:view_quality_reports')
            ->name('reports');

        Route::get('/export', [QualityController::class, 'exportReport'])
            ->middleware('permission:generate_quality_reports')
            ->name('export');

        Route::get('/statistics/{calibration}', [QualityController::class, 'getStatistics'])
            ->middleware('permission:view_quality_reports')
            ->name('statistics');
    });
    });

    // ============================================ //
    // AUDIT LOGS ROUTES                           //
    // ============================================ //
    Route::prefix('audit-logs')->name('audit-logs.')->middleware(['auth'])->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/export', [AuditLogController::class, 'export'])->name('export');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        Route::post('/clear', [AuditLogController::class, 'clear'])->name('clear');
        Route::delete('/{auditLog}', [AuditLogController::class, 'destroy'])->name('destroy');
    });

    // ============================================ //
    // REPORT ROUTES                              //
    // ============================================ //
    Route::prefix('reports')->name('reports.')->middleware(['auth'])->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/calibrations', [ReportController::class, 'calibrations'])->name('calibrations');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/certificates', [ReportController::class, 'certificates'])->name('certificates');
        Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment');
        Route::get('/equipment/export', [ReportController::class, 'exportEquipment'])->name('equipment.export');
        Route::get('/finance', [ReportController::class, 'finance'])->name('finance');
        Route::get('/finance/export', [ReportController::class, 'exportFinance'])->name('finance.export');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // ============================================ //
    // TEST MANAGEMENT ROUTES                     //
    // ============================================ //
    Route::prefix('calibrations/{calibration}/tests')->name('calibrations.tests.')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TestController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\TestController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\TestController::class, 'store'])->name('store');
        Route::get('/{testResult}/edit', [App\Http\Controllers\Admin\TestController::class, 'edit'])->name('edit');
        Route::put('/{testResult}', [App\Http\Controllers\Admin\TestController::class, 'update'])->name('update');
        Route::delete('/{testResult}', [App\Http\Controllers\Admin\TestController::class, 'destroy'])->name('destroy');
        Route::get('/results', [App\Http\Controllers\Admin\TestController::class, 'getResults'])->name('results');
        Route::post('/generate-certificate', [App\Http\Controllers\Admin\TestController::class, 'generateCertificate'])->name('generate-certificate');

    });

    // ============================================ //
    // QUALITY MANAGEMENT ROUTES                   //
    // ============================================ //
    Route::middleware(['auth', 'verified'])->prefix('quality')->name('quality.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [QualityDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [QualityDashboardController::class, 'getQuarterData'])->name('dashboard.data');

        // Quality Objectives
        Route::resource('objectives', QualityObjectiveController::class)
            ->names([
                'index' => 'objectives.index',
                'create' => 'objectives.create',
                'store' => 'objectives.store',
                'show' => 'objectives.show',
                'edit' => 'objectives.edit',
                'update' => 'objectives.update',
                'destroy' => 'objectives.destroy'
            ]);

        // Quarterly Achievements
        Route::resource('achievements', QuarterlyAchievementController::class)
            ->names([
                'index' => 'achievements.index',
                'create' => 'achievements.create',
                'store' => 'achievements.store',
                'edit' => 'achievements.edit',
                'update' => 'achievements.update',
                'destroy' => 'achievements.destroy'
            ]);
        Route::get('/achievements/bulk', [QuarterlyAchievementController::class, 'bulkEntry'])->name('achievements.bulk');
        Route::post('/achievements/bulk', [QuarterlyAchievementController::class, 'bulkStore']);

        // Corrective Actions
        Route::resource('corrective-actions', CorrectiveActionController::class)
            ->names([
                'index' => 'corrective-actions.index',
                'create' => 'corrective-actions.create',
                'store' => 'corrective-actions.store',
                'show' => 'corrective-actions.show',
                'edit' => 'corrective-actions.edit',
                'update' => 'corrective-actions.update',
                'destroy' => 'corrective-actions.destroy'
            ]);
        Route::post('/corrective-actions/{id}/status', [CorrectiveActionController::class, 'updateStatus'])->name('corrective-actions.status');

        // Reports
        Route::get('/reports', [QualityReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [QualityReportController::class, 'generate'])->name('reports.generate');
        Route::post('/reports/export', [QualityReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/quick-pdf', [QualityReportController::class, 'quickPDF'])->name('reports.quick-pdf');
        Route::get('/reports/generate', [QualityReportController::class, 'generate'])->name('reports.generate.get');
    });

    // ============================================ //
    // CHAT ROUTES                                //
    // ============================================ //
    Route::prefix('chat')->name('chat.')->middleware(['auth'])->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/conversations', [ChatController::class, 'getConversations'])->name('conversations');
        Route::get('/conversations/{conversation}', [ChatController::class, 'show'])->name('show');
        Route::post('/send', [ChatController::class, 'store'])->name('store');
        Route::post('/conversations/{conversation}/send', [ChatController::class, 'sendMessage'])->name('send');
        Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead'])->name('read');
        Route::post('/conversations/{conversation}/typing', [ChatController::class, 'typing'])->name('typing');
        Route::post('/conversations/{conversation}/upload', [ChatController::class, 'uploadFile'])->name('upload');
        Route::post('/groups', [ChatController::class, 'createGroup'])->name('groups.create');
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('unread-count');
    });

    // ============================================ //
    // CERTIFICATE CHECK ROUTES                   //
    // ============================================ //
    Route::middleware(['auth', 'role:supervisor|lab_manager|admin'])->prefix('certificates')->name('certificates.check.')->group(function () {
        // List certificates pending review
        Route::get('/check', [CertificateCheckController::class, 'index'])->name('index');

        // View a specific certificate
        Route::get('/check/{id}', [CertificateCheckController::class, 'show'])->name('show');

        // Check/verify a certificate (for supervisors)
        Route::post('/check/{id}/check', [CertificateCheckController::class, 'check'])->name('check');

        // Approve a certificate (for lab managers)
        Route::post('/check/{id}/approve', [CertificateCheckController::class, 'approve'])->name('approve');

        // Reject a certificate
        Route::post('/check/{id}/reject', [CertificateCheckController::class, 'reject'])->name('reject');

        // Request modifications to a certificate
        Route::post('/check/{id}/modify', [CertificateCheckController::class, 'requestModification'])->name('modify');

        // Issue a certificate (make it official)
        Route::post('/check/{id}/issue', [CertificateCheckController::class, 'issue'])->name('issue');

        // Send a certificate to customer
        Route::post('/check/{id}/send', [CertificateCheckController::class, 'send'])->name('send');

        // Bulk approve multiple certificates
        Route::post('/check/bulk/approve', [CertificateCheckController::class, 'bulkApprove'])->name('bulk.approve');
    });

    // Alternative route if you want to pass calibration ID instead of certificate ID
    Route::get('/calibrations/{calibration}/certificate', [CertificateCheckController::class, 'showByCalibration'])
        ->name('calibrations.certificate.show')
        ->middleware(['auth', 'role:supervisor|lab_manager|admin']);

    // ============================================ //
    // QUOTE ROUTES                               //
    // ============================================ //
    Route::middleware(['auth'])->prefix('admin/quotes')->name('admin.quotes.')->group(function (): void {
            Route::get('/{quote}', [QuoteController::class, 'show'])->name('show');
            Route::get('/{quote}/download', [QuoteController::class, 'download'])->name('download');
            Route::patch('/{quote}/approve', [QuoteController::class, 'approve'])->middleware('can:approve_quotes')->name('approve');
            Route::post('/{quote}/cancel-and-replace',[QuoteController::class, 'cancelAndReplace'])->middleware('role:supervisor|super_admin|lab_manager')->name('cancel-and-replace');
        });

        Route::middleware('auth')->group(function () {
            Route::get('/notifications',[NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/{notification}/read',[NotificationController::class, 'read'])->name('notifications.read');
            Route::patch('/notifications/mark-all-read',[NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        });

Route::prefix('pay')
    ->name('payments.')
    ->group(function () {
        Route::get(
            '/quote/{quote}',
            [MpesaPaymentController::class, 'show']
        )
            ->middleware('signed')
            ->name('quote.show');

        Route::post(
            '/quote/{quote}/mpesa',
            [MpesaPaymentController::class, 'initiate']
        )
            ->middleware([
                'signed',
                'throttle:5,1',
            ])
            ->name('quote.mpesa.initiate');

        Route::get(
            '/quote/{quote}/status/{payment}',
            [MpesaPaymentController::class, 'status']
        )
            ->middleware('signed')
            ->name('quote.mpesa.status');
    });

    // Add to routes/web.php, OUTSIDE any existing route groups that could override permissions.


Route::middleware(['auth', 'role:lab_manager|super_admin'])
    ->prefix('lab-manager/vat-verification')
    ->name('lab-manager.vat.')
    ->group(function () {
        Route::get('/', [CustomerVatVerificationController::class, 'index'])->name('index');
        Route::get('/{customer}', [CustomerVatVerificationController::class, 'show'])->name('show');
        Route::get('/{customer}/documents/{document}/download', [CustomerVatVerificationController::class, 'download'])->name('download');
        Route::patch('/{customer}/documents/{document}', [CustomerVatVerificationController::class, 'verifyDocument'])->name('document.verify');
        Route::post('/{customer}/approve', [CustomerVatVerificationController::class, 'approve'])->name('approve');
        Route::post('/{customer}/reject', [CustomerVatVerificationController::class, 'reject'])->name('reject');
        Route::post('/{customer}/revoke', [CustomerVatVerificationController::class, 'revoke'])->name('revoke');
    });

Route::get('/calibrations/{calibration}/items/{item}/environmental-conditions',[EquipmentEnvironmentalConditionsController::class, 'edit'])->name('calibrations.items.environment.edit');
Route::put('/calibrations/{calibration}/items/{item}/environmental-conditions',[EquipmentEnvironmentalConditionsController::class, 'update'])->name('calibrations.items.environment.update');

}); // End of auth middleware group
