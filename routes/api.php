<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SPKController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\UserController;
use Symfony\Component\Process\Process;
use Illuminate\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/quotations/{cabang}', [QuotationController::class, 'getDataQuotation']);
Route::get('/inventories/{cabang}', [QuotationController::class, 'getInventories']);
Route::post('/warehouse/spk/{id}', [SPKController::class, 'sendWarehouse']);
Route::post('/finance/spk/{id}', [SPKController::class, 'sendFinance']);
Route::post('/production/spk/{id}', [SPKController::class, 'sendProduction']);
Route::get('/users/production', [UserController::class, 'getUserProduction']);
Route::get('/finance/spk-detail/{id}', [FinanceController::class, 'getDataINV']);
Route::post('/finance/payment/{id}', [FinanceController::class, 'payment']);
Route::post('/spk-manufacture/laporan/{id}', [SPKController::class, 'sendLaporan']);
Route::post('/cari-affiliator', function (Request $request) {

    $python = '/var/www/venv/bin/python'; 
    $script = '/var/www/python/buka_browser.py';

    $inputData = json_encode($request->all());

    // =====================================================
    // DATA DARI N8N
    // =====================================================

    $payload = [
        'keyword' => $request->input(
            'keyword',
            'skincare'
        ),

        'minimal_follower' => (int) $request->input(
            'minimal_follower',
            5000
        ),

        'maximal_follower' => (int) $request->input(
            'maximal_follower',
            100000
        ),

        'target' => (int) $request->input(
            'target',
            100
        ),
    ];

    // =====================================================
    // VALIDASI
    // =====================================================

    if (
        $payload['maximal_follower']
        < $payload['minimal_follower']
    ) {
        return response()->json([
            'status' => 'error',
            'message' =>
                'maximal_follower tidak boleh lebih kecil dari minimal_follower.',
            'data' => []
        ], 422);
    }

    if ($payload['target'] < 1) {
        $payload['target'] = 1;
    }

    $debugMessage = "\n[DEBUG PAYLOAD MASUK]: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    file_put_contents('php://stdout', $debugMessage);

    // =====================================================
    // CEK FILE PYTHON
    // =====================================================

    if (!file_exists($script)) {

        return response()->json([
            'status' => 'error',
            'message' => 'File Python tidak ditemukan.',
            'path' => $script,
            'data' => []
        ], 500);
    }

    // =====================================================
    // JSON UNTUK PYTHON
    // =====================================================

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
    );

    // =====================================================
    // JALANKAN PYTHON
    // =====================================================

    $descriptorSpec = [
        0 => ['pipe', 'r'], // STDIN
        1 => ['pipe', 'w'], // STDOUT
        2 => ['pipe', 'w'], // STDERR
    ];

    // Ubah command menjadi Array [python_path, script_path]
    $command = [$python, $script];

    $env = [
        'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        'HOME' => base_path('storage'),
        'PLAYWRIGHT_BROWSERS_PATH' => base_path('storage/playwright-cache')
    ];

    $process = proc_open(
        $command,
        $descriptorSpec,
        $pipes,
        base_path(),
        $env 
    );

    if (!is_resource($process)) {

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menjalankan Python.',
            'data' => []
        ], 500);
    }

    // =====================================================
    // KIRIM JSON KE PYTHON
    // =====================================================

    fwrite($pipes[0], $inputData);

    fclose(
        $pipes[0]
    );

    // =====================================================
    // BACA STDOUT
    // =====================================================

    $output = stream_get_contents(
        $pipes[1]
    );

    fclose(
        $pipes[1]
    );

    // =====================================================
    // BACA STDERR
    // =====================================================

    $error = stream_get_contents(
        $pipes[2]
    );

    fclose(
        $pipes[2]
    );

    // =====================================================
    // TUNGGU PROSES SELESAI
    // =====================================================

    $exitCode = proc_close(
        $process
    );

    // =====================================================
    // PYTHON ERROR
    // =====================================================

    if ($exitCode !== 0) {

        $logMessage = "Python Error [Exit Code: {$exitCode}] | STDERR: {$error} | STDOUT: {$output}";
        
        // Catat ke file storage/logs/laravel.log agar bisa dibaca
        \Log::error($logMessage);

        return response()->json([
            'status' => 'error',
            'message' => 'Python mengembalikan error.',
            'exit_code' => $exitCode,
            'error' => $error ?: 'Exit code 127/lainnya (Cek storage/logs/laravel.log)',
            'output' => $output,
            'data' => []
        ], 500);
    }

    // =====================================================
    // PARSE JSON DARI PYTHON
    // =====================================================

    $result = json_decode(
        trim($output),
        true
    );

    if (
        json_last_error()
        !== JSON_ERROR_NONE
    ) {

        return response()->json([
            'status' => 'error',
            'message' =>
                'Output Python bukan JSON yang valid.',
            'json_error' =>
                json_last_error_msg(),
            'python_output' => $output,
            'python_log' => $error,
            'data' => []
        ], 500);
    }

    return response()->json(
        $result, 
        200, 
        ['Content-Type' => 'application/json']
    );
});