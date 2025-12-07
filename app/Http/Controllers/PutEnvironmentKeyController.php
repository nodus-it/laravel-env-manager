<?php

namespace App\Http\Controllers;

use App\Data\EnvironmentData;
use App\Http\Requests\PutEnvironmentKeyRequest;
use App\Models\Environment;
use App\Services\EnvironmentService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PutEnvironmentKeyController extends Controller
{
    public function __construct(private EnvironmentService $service) {}

    public function __invoke(PutEnvironmentKeyRequest $request): JsonResponse
    {
        /** @var Environment $environment */
        $environment = auth()->user();

        try {
            $this->service->setKeys($environment, $request->items());

            $showSecrets = (bool) $request->boolean('show_secrets', false);
            $data = EnvironmentData::fromEnvironment($environment->fresh(), $showSecrets);

            return response()->json([
                'success' => true,
                'message' => __('Environment variables updated successfully.'),
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => __('Failed to update environment variables.'),
                'errors' => [
                    'exception' => class_basename($e),
                ],
            ], 500);
        }
    }
}
