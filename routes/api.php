<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Landing\SettingController as LandingSettingController;
use App\Http\Controllers\Api\Landing\SectionController as LandingSectionController;
use App\Http\Controllers\Api\Landing\PortfolioController as LandingPortfolioController;
use App\Http\Controllers\Api\Landing\ServiceController as LandingServiceController;
use App\Http\Controllers\Api\Landing\TestimonialController as LandingTestimonialController;
use App\Http\Controllers\Api\Landing\ContactController as LandingContactController;
use App\Http\Controllers\Api\Landing\MediaController as LandingMediaController;
use App\Http\Controllers\Api\Landing\UploadController as LandingUploadController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\CustomerPortalController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\BonusModulesController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/quotations/view/{token}', [QuotationController::class, 'viewByToken']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    // Users
    Route::apiResource('users', UserController::class);
    Route::get('/users-sales-team', [UserController::class, 'salesTeam']);
    Route::get('/users-engineers', [UserController::class, 'engineers']);
    Route::get('/notifications', [UserController::class, 'notifications']);
    Route::put('/notifications/{notification}/read', [UserController::class, 'markNotificationRead']);
    Route::put('/notifications/read-all', [UserController::class, 'markAllNotificationsRead']);

    // Leads
    Route::apiResource('leads', LeadController::class);
    Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity']);
    Route::put('/leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::get('/leads-statistics', [LeadController::class, 'statistics']);

    // Site Visits
    Route::apiResource('site-visits', SiteVisitController::class);
    Route::post('/site-visits/{siteVisit}/photos', [SiteVisitController::class, 'uploadPhotos']);
    Route::post('/site-visits/{siteVisit}/measurements', [SiteVisitController::class, 'addMeasurement']);
    Route::post('/site-visits/{siteVisit}/report', [SiteVisitController::class, 'createReport']);
    Route::get('/site-visits-today', [SiteVisitController::class, 'todayVisits']);
    Route::get('/site-visits-engineer/{engineerId}', [SiteVisitController::class, 'engineerSchedule']);

    // Quotations
    Route::apiResource('quotations', QuotationController::class);
    Route::post('/quotations/{quotation}/items', [QuotationController::class, 'addItem']);
    Route::put('/quotations/{quotation}/items/{item}', [QuotationController::class, 'updateItem']);
    Route::delete('/quotations/{quotation}/items/{item}', [QuotationController::class, 'deleteItem']);
    Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send']);
    Route::post('/quotations/{quotation}/pdf', [QuotationController::class, 'generatePdf']);
    Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate']);
    Route::get('/quotation-templates', [QuotationController::class, 'templates']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::put('/projects/{project}/phases/{phase}', [ProjectController::class, 'updatePhase']);
    Route::post('/projects/{project}/phases', [ProjectController::class, 'addPhase']);
    Route::post('/projects/{project}/photos', [ProjectController::class, 'uploadPhotos']);
    Route::post('/projects/{project}/updates', [ProjectController::class, 'addUpdate']);
    Route::post('/projects/{project}/team', [ProjectController::class, 'addTeamMember']);
    Route::delete('/projects/{project}/team/{userId}', [ProjectController::class, 'removeTeamMember']);
    Route::get('/projects/{project}/timeline', [ProjectController::class, 'timeline']);
    Route::get('/projects-statistics', [ProjectController::class, 'statistics']);

    // Inventory
    Route::apiResource('materials', InventoryController::class);
    Route::post('/materials/{material}/adjust', [InventoryController::class, 'adjustStock']);
    Route::get('/materials/{material}/transactions', [InventoryController::class, 'transactions']);
    Route::get('/material-categories', [InventoryController::class, 'categories']);
    Route::post('/material-categories', [InventoryController::class, 'storeCategory']);
    Route::get('/materials-low-stock', [InventoryController::class, 'lowStock']);
    Route::get('/projects/{projectId}/materials', [InventoryController::class, 'projectMaterials']);
    Route::post('/project-materials', [InventoryController::class, 'addProjectMaterial']);
    Route::get('/inventory-statistics', [InventoryController::class, 'statistics']);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/prices', [SupplierController::class, 'addPrice']);
    Route::post('/suppliers/{supplier}/ratings', [SupplierController::class, 'addRating']);
    Route::get('/purchase-orders', [SupplierController::class, 'purchaseOrders']);
    Route::post('/purchase-orders', [SupplierController::class, 'createPurchaseOrder']);
    Route::get('/purchase-orders/{purchaseOrder}', [SupplierController::class, 'showPurchaseOrder']);
    Route::put('/purchase-orders/{purchaseOrder}/status', [SupplierController::class, 'updatePurchaseOrderStatus']);
    Route::post('/purchase-orders/{purchaseOrder}/receive', [SupplierController::class, 'receivePurchaseOrderItems']);
    Route::get('/suppliers-statistics', [SupplierController::class, 'statistics']);

    // Contracts
    Route::apiResource('contracts', ContractController::class);
    Route::post('/contracts/{contract}/payment-terms', [ContractController::class, 'addPaymentTerm']);
    Route::post('/contracts/{contract}/amendments', [ContractController::class, 'createAmendment']);
    Route::put('/contract-amendments/{amendment}/approve', [ContractController::class, 'approveAmendment']);
    Route::post('/contracts/{contract}/documents', [ContractController::class, 'uploadDocument']);
    Route::post('/contracts/{contract}/sign', [ContractController::class, 'sign']);
    Route::post('/contracts/{contract}/pdf', [ContractController::class, 'generatePdf']);

    // Payments & Finance
    Route::get('/invoices', [PaymentController::class, 'invoices']);
    Route::post('/invoices', [PaymentController::class, 'createInvoice']);
    Route::get('/invoices/{invoice}', [PaymentController::class, 'showInvoice']);
    Route::put('/invoices/{invoice}', [PaymentController::class, 'updateInvoice']);
    Route::post('/invoices/{invoice}/send', [PaymentController::class, 'sendInvoice']);
    Route::post('/invoices/{invoice}/pdf', [PaymentController::class, 'generateInvoicePdf']);
    Route::get('/payments', [PaymentController::class, 'payments']);
    Route::post('/payments', [PaymentController::class, 'recordPayment']);
    Route::get('/expenses', [PaymentController::class, 'expenses']);
    Route::post('/expenses', [PaymentController::class, 'createExpense']);
    Route::put('/expenses/{expense}/approve', [PaymentController::class, 'approveExpense']);
    Route::get('/projects/{projectId}/budget', [PaymentController::class, 'projectBudget']);
    Route::put('/projects/{projectId}/budget', [PaymentController::class, 'updateProjectBudget']);
    Route::get('/financial-report', [PaymentController::class, 'financialReport']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/checklists', [TaskController::class, 'addChecklist']);
    Route::put('/task-checklists/{checklist}/toggle', [TaskController::class, 'toggleChecklist']);
    Route::delete('/task-checklists/{checklist}', [TaskController::class, 'deleteChecklist']);
    Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment']);
    Route::get('/my-tasks', [TaskController::class, 'myTasks']);
    Route::get('/tasks-today', [TaskController::class, 'todayTasks']);
    Route::get('/tasks-overdue', [TaskController::class, 'overdueTasks']);
    Route::get('/tasks-statistics', [TaskController::class, 'statistics']);

    // Communication
    Route::get('/message-templates', [CommunicationController::class, 'templates']);
    Route::post('/message-templates', [CommunicationController::class, 'storeTemplate']);
    Route::get('/call-logs', [CommunicationController::class, 'callLogs']);
    Route::post('/call-logs', [CommunicationController::class, 'logCall']);
    Route::post('/send-whatsapp', [CommunicationController::class, 'sendWhatsapp']);
    Route::get('/whatsapp-logs', [CommunicationController::class, 'whatsappLogs']);
    Route::post('/send-sms', [CommunicationController::class, 'sendSms']);
    Route::post('/send-email', [CommunicationController::class, 'sendEmail']);

    // Customer Portal
    Route::prefix('portal')->group(function () {
        Route::get('/projects', [CustomerPortalController::class, 'myProjects']);
        Route::get('/projects/{projectId}', [CustomerPortalController::class, 'projectDetails']);
        Route::get('/projects/{projectId}/photos', [CustomerPortalController::class, 'projectPhotos']);
        Route::get('/projects/{projectId}/updates', [CustomerPortalController::class, 'projectUpdates']);
        Route::get('/invoices', [CustomerPortalController::class, 'myInvoices']);
        Route::get('/pending-payments', [CustomerPortalController::class, 'pendingPayments']);
        Route::post('/projects/{projectId}/message', [CustomerPortalController::class, 'sendMessage']);
    });

    // Reports
    Route::get('/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/leads', [ReportController::class, 'leadsReport']);
    Route::get('/reports/projects', [ReportController::class, 'projectsReport']);
    Route::get('/reports/financial', [ReportController::class, 'financialReport']);
    Route::get('/reports/sales-performance', [ReportController::class, 'salesPerformance']);

    // Bonus Modules
    Route::post('/attendance/check-in', [BonusModulesController::class, 'checkIn']);
    Route::post('/attendance/check-out', [BonusModulesController::class, 'checkOut']);
    Route::get('/attendance', [BonusModulesController::class, 'attendanceReport']);
    Route::get('/quality-checklists', [BonusModulesController::class, 'qualityChecklists']);
    Route::post('/quality-inspections', [BonusModulesController::class, 'createInspection']);
    Route::put('/quality-inspections/{inspection}', [BonusModulesController::class, 'submitInspection']);
    Route::post('/quality-issues', [BonusModulesController::class, 'reportIssue']);
    Route::get('/maintenance-requests', [BonusModulesController::class, 'maintenanceRequests']);
    Route::post('/maintenance-requests', [BonusModulesController::class, 'createMaintenanceRequest']);
    Route::put('/maintenance-requests/{maintenance}', [BonusModulesController::class, 'updateMaintenanceRequest']);
    Route::get('/marketing-campaigns', [BonusModulesController::class, 'marketingCampaigns']);
    Route::post('/marketing-campaigns', [BonusModulesController::class, 'createCampaign']);
    Route::get('/marketing-dashboard', [BonusModulesController::class, 'marketingDashboard']);
});

// Landing Page Public Routes
Route::prefix('landing')->group(function () {
    Route::get('/settings', [LandingSettingController::class, 'index']);
    Route::get('/sections', [LandingSectionController::class, 'index']);
    Route::get('/portfolio', [LandingPortfolioController::class, 'index']);
    Route::get('/portfolio/{portfolio}', [LandingPortfolioController::class, 'show']);
    Route::get('/testimonials', [LandingTestimonialController::class, 'index']);
    Route::get('/services', [LandingServiceController::class, 'index']);
    Route::post('/contact', [LandingContactController::class, 'store']);
});

// Landing Page Admin Routes (Protected)
Route::middleware('auth:sanctum')->prefix('landing')->group(function () {
    Route::post('/upload', [LandingUploadController::class, 'store']);
    Route::delete('/upload', [LandingUploadController::class, 'destroy']);
    
    Route::put('/settings/bulk', [LandingSettingController::class, 'bulkUpdate']);
    Route::put('/settings/{key}', [LandingSettingController::class, 'update']);
    
    Route::post('/sections', [LandingSectionController::class, 'store']);
    Route::put('/sections/{section}', [LandingSectionController::class, 'update']);
    Route::delete('/sections/{section}', [LandingSectionController::class, 'destroy']);
    Route::post('/sections/reorder', [LandingSectionController::class, 'reorder']);
    
    Route::post('/portfolio', [LandingPortfolioController::class, 'store']);
    Route::put('/portfolio/{portfolio}', [LandingPortfolioController::class, 'update']);
    Route::delete('/portfolio/{portfolio}', [LandingPortfolioController::class, 'destroy']);
    
    Route::post('/media', [LandingMediaController::class, 'store']);
    Route::put('/media/{media}', [LandingMediaController::class, 'update']);
    Route::delete('/media/{media}', [LandingMediaController::class, 'destroy']);
    
    Route::post('/testimonials', [LandingTestimonialController::class, 'store']);
    Route::put('/testimonials/{testimonial}', [LandingTestimonialController::class, 'update']);
    Route::delete('/testimonials/{testimonial}', [LandingTestimonialController::class, 'destroy']);
    
    Route::post('/services', [LandingServiceController::class, 'store']);
    Route::put('/services/{service}', [LandingServiceController::class, 'update']);
    Route::delete('/services/{service}', [LandingServiceController::class, 'destroy']);
    
    Route::get('/contact-submissions', [LandingContactController::class, 'index']);
    Route::put('/contact-submissions/{id}/read', [LandingContactController::class, 'markAsRead']);
    Route::delete('/contact-submissions/{id}', [LandingContactController::class, 'destroy']);
});
