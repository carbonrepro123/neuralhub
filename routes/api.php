<?php

use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\ComplianceController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\VideoRoomController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('clinics', ClinicController::class)->only(['index', 'store', 'show']);
    Route::apiResource('patients', PatientController::class);

    Route::post('/patients/{patient}/documents', [DocumentController::class, 'store']);
    Route::get('/patients/{patient}/documents', [DocumentController::class, 'index']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::post('/documents/{document}/analyze', [DocumentController::class, 'analyze']);

    Route::post('/ai/report-summary', [AIController::class, 'reportSummary']);
    Route::post('/ai/extract-vitals', [AIController::class, 'extractVitals']);
    Route::post('/ai/generate-soap-note', [AIController::class, 'generateSoapNote']);
    Route::post('/ai/suggest-questions', [AIController::class, 'suggestQuestions']);
    Route::get('/ai/tasks/{task}', [AIController::class, 'showTask']);

    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/start-call', [AppointmentController::class, 'startCall']);
    Route::post('/appointments/{appointment}/end-call', [AppointmentController::class, 'endCall']);

    Route::post('/video/rooms', [VideoRoomController::class, 'store']);
    Route::post('/video/rooms/{videoRoom}/token', [VideoRoomController::class, 'token']);
    Route::get('/video/rooms/{videoRoom}', [VideoRoomController::class, 'show']);

    Route::get('/compliance', [ComplianceController::class, 'index']);
    Route::post('/compliance', [ComplianceController::class, 'store']);
    Route::get('/compliance/{complianceItem}', [ComplianceController::class, 'show']);
    Route::put('/compliance/{complianceItem}', [ComplianceController::class, 'update']);
    Route::post('/compliance/{complianceItem}/upload-document', [ComplianceController::class, 'uploadDocument']);
    Route::post('/compliance/{complianceItem}/analyze-document', [ComplianceController::class, 'analyzeDocument']);
    Route::post('/compliance/{complianceItem}/create-reminder', [ComplianceController::class, 'createReminder']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});
