<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/filament/bulk-forms/download', function () {
    $path = decrypt(request('path'));

    abort_unless(Storage::disk('local')->exists($path), 404);

    return Storage::disk('local')->download($path);
})
->middleware(['web', 'auth'])
->name('filament.bulk-forms.download');

Route::middleware(['web', 'auth'])->get('/form-template-exports/download', function () {
    $path = decrypt(request('file'));

    abort_unless(
        str_starts_with($path, 'form_exports/'),
        403
    );

    abort_unless(Storage::disk('local')->exists($path), 404);

    return Storage::disk('local')->download($path, basename($path));
})->name('form-template-exports.download');

Route::get('/', function () {
    return view('welcome');
});
