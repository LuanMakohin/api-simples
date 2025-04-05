<?php

use App\Exceptions\UnauthorizedTransferException;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Http;

it('authorizes a transfer successfully', function () {
    Http::fake([
        'https://util.devi.tools/api/v2/authorize' => Http::response([
            'data' => [
                'authorization' => true,
            ]
        ], 200),
    ]);

    $service = new AuthorizationService();

    expect($service->authorize())->toBeTrue();
});

it('throws exception if transfer is not authorized', function () {
    Http::fake([
        'https://util.devi.tools/api/v2/authorize' => Http::response([
            'data' => [
                'authorization' => false,
            ]
        ], 200),
    ]);

    $service = new AuthorizationService();

    $this->expectException(UnauthorizedTransferException::class);

    $service->authorize();
});

it('throws exception if the authorization service fails', function () {
    Http::fake([
        'https://util.devi.tools/api/v2/authorize' => Http::response(null, 500),
    ]);

    $service = new AuthorizationService();

    $this->expectException(UnauthorizedTransferException::class);

    $service->authorize();
});
