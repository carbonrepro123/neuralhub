<?php

use App\Http\Controllers\Portal\AIPortalController;
use App\Http\Controllers\Portal\AppointmentPortalController;
use App\Http\Controllers\Portal\ClinicOperationsController;
use App\Http\Controllers\Portal\CompliancePortalController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\HomeController;
use App\Http\Controllers\Portal\OrganizationPortalController;
use App\Http\Controllers\Portal\PatientPortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/doctor/portal', DashboardController::class)->middleware('role:doctor')->name('portal.doctor');
    Route::get('/clinic-admin/portal', DashboardController::class)->middleware('role:clinic_admin')->name('portal.clinic-admin');
    Route::get('/super-admin/portal', DashboardController::class)->middleware('role:super_admin')->name('portal.super-admin');
    Route::get('/patient/portal', DashboardController::class)->middleware('role:patient')->name('portal.patient');
    Route::get('/organizations', [OrganizationPortalController::class, 'index'])->middleware('role:super_admin,clinic_admin')->name('portal.organizations.index');
    Route::post('/organizations/clinics', [OrganizationPortalController::class, 'storeClinic'])->middleware('role:super_admin')->name('portal.organizations.clinics.store');
    Route::post('/organizations/clinics/{clinic}/members', [OrganizationPortalController::class, 'storeMember'])->middleware('role:super_admin,clinic_admin')->name('portal.organizations.members.store');
    Route::get('/operations', [ClinicOperationsController::class, 'index'])->middleware('role:super_admin,clinic_admin')->name('portal.operations.index');
    Route::post('/operations/doctors/{doctor}/agents', [ClinicOperationsController::class, 'assignAgent'])->middleware('role:super_admin,clinic_admin')->name('portal.operations.agents.assign');
    Route::post('/operations/clinics/{clinic}/features', [ClinicOperationsController::class, 'toggleFeature'])->middleware('role:super_admin,clinic_admin')->name('portal.operations.features.toggle');

    Route::get('/patients', [PatientPortalController::class, 'index'])->name('portal.patients.index');
    Route::get('/patients/create', [PatientPortalController::class, 'create'])->name('portal.patients.create');
    Route::post('/patients', [PatientPortalController::class, 'store'])->name('portal.patients.store');
    Route::get('/patients/{patient}', [PatientPortalController::class, 'show'])->name('portal.patients.show');
    Route::get('/patients/{patient}/edit', [PatientPortalController::class, 'edit'])->name('portal.patients.edit');
    Route::put('/patients/{patient}', [PatientPortalController::class, 'update'])->name('portal.patients.update');
    Route::post('/patients/{patient}/documents', [PatientPortalController::class, 'uploadDocument'])->name('portal.patients.documents.store');
    Route::post('/patients/{patient}/documents/{document}/analyze', [PatientPortalController::class, 'analyzeDocument'])->name('portal.patients.documents.analyze');
    Route::get('/patients/{patient}/documents/{document}', [PatientPortalController::class, 'downloadDocument'])->name('portal.patients.documents.download');

    Route::get('/appointments', [AppointmentPortalController::class, 'index'])->name('portal.appointments.index');
    Route::get('/appointments/create', [AppointmentPortalController::class, 'create'])->name('portal.appointments.create');
    Route::post('/appointments', [AppointmentPortalController::class, 'store'])->name('portal.appointments.store');
    Route::get('/appointments/{appointment}', [AppointmentPortalController::class, 'show'])->name('portal.appointments.show');
    Route::post('/appointments/{appointment}/room', [AppointmentPortalController::class, 'generateRoom'])->name('portal.appointments.room');
    Route::post('/appointments/{appointment}/start', [AppointmentPortalController::class, 'startMeeting'])->name('portal.appointments.start');
    Route::get('/appointments/{appointment}/consult', [AppointmentPortalController::class, 'consult'])->name('portal.appointments.consult');
    Route::get('/appointments/{appointment}/join', [AppointmentPortalController::class, 'patientJoin'])->name('portal.appointments.join');
    Route::post('/appointments/{appointment}/soap', [AppointmentPortalController::class, 'generateSoap'])->name('portal.appointments.soap');
    Route::post('/appointments/{appointment}/complete', [AppointmentPortalController::class, 'complete'])->name('portal.appointments.complete');

    Route::get('/compliance', [CompliancePortalController::class, 'index'])->name('portal.compliance.index');
    Route::get('/compliance/create', [CompliancePortalController::class, 'create'])->name('portal.compliance.create');
    Route::post('/compliance', [CompliancePortalController::class, 'store'])->name('portal.compliance.store');
    Route::get('/compliance/{compliance}', [CompliancePortalController::class, 'show'])->name('portal.compliance.show');
    Route::post('/compliance/{compliance}/documents', [CompliancePortalController::class, 'uploadDocument'])->name('portal.compliance.documents.store');
    Route::post('/compliance/{compliance}/analyze', [CompliancePortalController::class, 'analyze'])->name('portal.compliance.analyze');

    Route::get('/ai-activity', [AIPortalController::class, 'index'])->name('portal.ai.index');
    Route::post('/ai-activity', [AIPortalController::class, 'run'])->name('portal.ai.run');
    Route::get('/ai-activity/{task}', [AIPortalController::class, 'show'])->name('portal.ai.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
