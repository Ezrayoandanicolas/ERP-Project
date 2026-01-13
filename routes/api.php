<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test-print', function () {
    $connector = new CupsPrintConnector(env('THERMAL_PRINTER'));
    $printer = new Printer($connector);
    $printer->text("Test Laravel Ezra!\n");
    $printer->cut();
    $printer->close();

    return "Printed!";
});