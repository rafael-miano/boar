<?php

use App\Http\Controllers\BoarReservationController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('home');
});

// Fallback for hosts that don't expose the /public/storage symlink.
// If the symlink exists, the web server will serve files before Laravel.
Route::get('/storage/{path}', function (string $path) {
    if (Str::contains($path, '..')) {
        abort(404);
    }

    $path = ltrim($path, '/');

    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
})
    ->where('path', '.*')
    ->name('storage.public');

// Serve public-disk files even when /public/storage symlink isn't available on the host.
Route::get('/media/public/{path}', function (string $path) {
    if (Str::contains($path, '..')) {
        abort(404);
    }

    $path = ltrim($path, '/');

    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $fullPath = Storage::disk('public')->path($path);

    // Detect the real MIME type from file content (not extension) so a JPEG
    // saved with a .png extension still renders correctly in all browsers.
    $mime = function_exists('mime_content_type')
        ? (mime_content_type($fullPath) ?: Storage::disk('public')->mimeType($path))
        : Storage::disk('public')->mimeType($path);

    return response()->file($fullPath, ['Content-Type' => $mime]);
})
    ->where('path', '.*')
    ->name('media.public');

// Customer profile – must be registered early so it isn’t caught by Filament’s SPA catch-all
Route::get('/customer/profile', function () {
    return redirect()->route('filament.customer.auth.profile');
})->middleware(['web', 'auth'])->name('customer.profile');

Route::post('/admin/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/'); // or route('login')
})->name('filament.admin.auth.logout');


// Email verification bridge routes to support Laravel's default notification links
Route::middleware(['web', 'auth'])->group(function () {
    // Default notice route -> forward to Filament admin verification prompt
    Route::get('/verify-email', function () {
        return redirect()->route('filament.admin.auth.email-verification.prompt');
    })->name('verification.notice');

    // Default verify route name expected by MustVerifyEmail notification
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        $user = $request->user();
        if ($user && $user->role === 'customer') {
            return redirect()->route('filament.customer.pages.customer-dashboard');
        }
        if ($user && $user->role === 'boar-raiser') {
            return redirect()->route('filament.admin.pages.boar-raiser-dashboard');
        }

        return redirect()->route('filament.admin.pages.admin-dashboard');
    })->middleware(['signed'])->name('verification.verify');

    // Resend route name expected by some flows (optional)
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back();
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->name('verification.send');
});

Route::get('/admin/email-verification/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    Auth::login($user);

    return match ($user->role) {
        'customer' => redirect()->route('filament.customer.pages.customer-dashboard'),
        'boar-raiser' => redirect()->route('filament.admin.pages.boar-raiser-dashboard'),
        default => redirect()->route('filament.admin.pages.admin-dashboard'),
    };
})->middleware('signed')->name('filament.admin.auth.email-verification.verify');

// Boar Reservation Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/boar-reservation-request', [BoarReservationController::class, 'store'])->name('boar-reservation.request');
});

app()->booted(function () {
    $panel = Filament::getPanel('admin');

    Route::middleware($panel->getMiddleware())
        ->group(function () use ($panel) {
            Route::get('/login', $panel->getLoginRouteAction())
                ->name('login');

            if ($panel->hasRegistration()) {
                Route::get('/register', $panel->getRegistrationRouteAction())
                    ->name('register');
            }

            if ($panel->hasPasswordReset()) {
                Route::name('password-reset.')
                    ->prefix('password-reset')
                    ->group(function () use ($panel) {
                        Route::get($panel->getRequestPasswordResetRouteSlug(), $panel->getRequestPasswordResetRouteAction())
                            ->name('request');
                        Route::get($panel->getResetPasswordRouteSlug(), $panel->getResetPasswordRouteAction())
                            ->middleware(['signed'])
                            ->name('reset');
                    });
            }
        });
});

// Route::get('/themes', fn() => abort(404))
//     ->middleware(['web']) // add your Filament panel middleware if needed
//     ->name('filament.admin.pages.themes');
// Route::get('/themes', fn() => abort(404))
//     ->middleware(['web']) // add your Filament panel middleware if needed
//     ->name('filament.customer.pages.themes');