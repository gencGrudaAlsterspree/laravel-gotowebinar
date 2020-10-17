<?php

// Route::prefix('api/goto')->group(function() {
//  // @todo: register webhooks
//  // @todo: register default redirect URI
//});

Route::prefix('_goto')->group(function () {
    require_once __DIR__.'/state.php';
    require_once __DIR__.'/webinars.php';
    require_once __DIR__.'/registrants.php';
    require_once __DIR__.'/attendees.php';
    require_once __DIR__.'/sessions.php';
});
