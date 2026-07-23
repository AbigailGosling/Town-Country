<?php

namespace App\Http\Controllers;

use App\Models\Intake;
use App\Models\IntakeScanningFile;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntakeScanningController extends Controller
{
    public function depots(Request $request): JsonResponse
    {
        $depots = Location::where('disabled', 0)
            ->where('name', 'not like', '%Missing%')
            ->where('name', 'not like', '%transit%')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->filter(static fn (Location $location): bool => !empty($location->name))
            ->map(static fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
            ])
            ->values()
            ->all();

        return response()->json(['depots' => $depots]);
    }

    public function intakes(Request $request): JsonResponse
    {
        $intakes = Intake::where('approved', 0)
            ->orderByDesc('id')
            ->pluck('id')
            ->filter(static fn ($id): bool => !empty($id))
            ->values()
            ->all();

        return response()->json(['intakes' => $intakes]);
    }

    public function ocr(Request $request): JsonResponse
    {
        ini_set('memory_limit', '1G');
        $payload = $request->json()->all();
        if ($payload === [] && $request->all() !== []) {
            $payload = $request->all();
        }

        $images = $payload['images'] ?? [];
        if (!is_array($images) || $images === []) {
            return response()->json(['error' => 'Missing images'], 400);
        }

        $intakeIdRaw = $payload['intakeId'] ?? $request->input('intakeId', 'unknown');
        $intakeId = $this->sanitizeIdentifier((string) $intakeIdRaw, 'unknown');

        $uploadSessionRaw = (string) ($payload['uploadSessionId'] ?? '');
        $uploadSessionId = $this->sanitizeIdentifier($uploadSessionRaw, '');
        if ($uploadSessionId === '') {
            $uploadSessionId = $intakeId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        }

        $batchNumber = max(1, (int) ($payload['batchNumber'] ?? 1));
        $totalBatches = max($batchNumber, (int) ($payload['totalBatches'] ?? $batchNumber));
        $isFinalBatch = !empty($payload['isFinalBatch']) || $batchNumber >= $totalBatches;
        $instructions = trim((string) ($payload['instructions'] ?? ''));

        $job = IntakeScanningFile::loadJobState($uploadSessionId);

        if (!is_array($job)) {
            $job = [
                'jobId' => $uploadSessionId,
                'intakeId' => $intakeId,
                'instructions' => $instructions,
                'timestamp' => date('Ymd_His'),
                'status' => 'uploading',
                'totalBatches' => $totalBatches,
                'uploadedBatches' => [],
                'images' => [],
                'results' => [],
                'errors' => [],
                'createdAt' => date('c'),
                'updatedAt' => date('c'),
            ];
        } else {
            if ($instructions !== '') {
                $job['instructions'] = $instructions;
            }
            $job['totalBatches'] = max((int) ($job['totalBatches'] ?? 0), $totalBatches);
            $job['updatedAt'] = date('c');
        }

        $currentCount = IntakeScanningFile::imageRecordsForSession($uploadSessionId)->count();
        $savedThisBatch = 0;

        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }

            $mime = (string) ($image['mimeType'] ?? 'image/jpeg');
            $base64 = (string) ($image['base64'] ?? '');
            if ($base64 === '') {
                continue;
            }

            $decoded = base64_decode($base64, true);
            if ($decoded === false) {
                continue;
            }

            $sequence = $currentCount + $savedThisBatch + 1;
            $extension = $this->extensionFromMime($mime);
            $fileName = $intakeId . '_' . $job['timestamp'] . '_' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT) . '.' . $extension;
            $storedImage = $this->storeScanningFile(
                $intakeIdRaw,
                $uploadSessionId,
                $fileName,
                $mime,
                $sequence,
                $decoded,
                IntakeScanningFile::ROLE_IMAGE
            );

            if ($storedImage === null) {
                continue;
            }

            $savedThisBatch++;
        }

        $job['images'] = IntakeScanningFile::imageMetadataForSession($uploadSessionId);

        $job['uploadedBatches'][] = $batchNumber;
        $job['uploadedBatches'] = array_values(array_unique(array_map('intval', $job['uploadedBatches'])));
        sort($job['uploadedBatches']);
        $job['updatedAt'] = date('c');

        if ($isFinalBatch) {
            $job['status'] = 'queued';
            $job['queuedAt'] = date('c');
        } else {
            $job['status'] = 'uploading';
        }

        if (IntakeScanningFile::saveJobState($uploadSessionId, $job, $intakeIdRaw) === null) {
            return response()->json(['error' => 'Failed to persist upload job'], 500);
        }

        if (!$isFinalBatch) {
            return response()->json([
                'ok' => true,
                'jobId' => $uploadSessionId,
                'status' => 'uploading',
                'batchNumber' => $batchNumber,
                'totalBatches' => $totalBatches,
                'savedThisBatch' => $savedThisBatch,
                'totalSaved' => count($job['images']),
            ]);
        }

        return response()->json([
            'ok' => true,
            'jobId' => $uploadSessionId,
            'status' => 'queued',
            'uploadedImages' => count($job['images']),
            'message' => 'Photo Upload Success. Background processing will continue.',
        ]);
    }

    public function jobStatus(Request $request, ?string $jobId = null): JsonResponse
    {
        $jobIdValue = $this->sanitizeIdentifier((string) ($jobId ?? $request->query('jobId', '')), '');
        if ($jobIdValue === '') {
            return response()->json(['ok' => false, 'error' => 'Missing jobId'], 400);
        }

        $job = IntakeScanningFile::loadJobState($jobIdValue);
        if (!is_array($job)) {
            return response()->json(['ok' => false, 'error' => 'Job not found', 'jobId' => $jobIdValue], 404);
        }

        return response()->json($this->jobSummary($job, $jobIdValue));
    }

    public function jobsList(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 25);
        $limit = max(1, min(100, $limit));

        $jobs = array_map(function (array $job): array {
            return $this->jobSummary($job, $job['jobId'] ?? null);
        }, IntakeScanningFile::listJobStates($limit));

        return response()->json([
            'ok' => true,
            'count' => count($jobs),
            'jobs' => $jobs,
        ]);
    }

    public function processJob(Request $request, string $jobId): JsonResponse
    {
        $jobIdValue = $this->sanitizeIdentifier($jobId, '');
        if ($jobIdValue === '') {
            return response()->json(['ok' => false, 'error' => 'Missing jobId'], 400);
        }

        if (!is_array(IntakeScanningFile::loadJobState($jobIdValue))) {
            return response()->json(['ok' => false, 'error' => 'Job not found', 'jobId' => $jobIdValue], 404);
        }

        $processed = $this->processStoredJob($jobIdValue, 6);
        if (!$processed['ok']) {
            return response()->json($processed, $processed['statusCode'] ?? 500);
        }

        return response()->json($processed);
    }

    public function processQueue(Request $request): JsonResponse
    {
        return response()->json($this->runQueuedJobs(12));
    }

    public function acceptFile(Request $request, IntakeScanningFile $intakeScanningFile): JsonResponse
    {
        $targetFiles = $this->relatedScanningFiles($intakeScanningFile);

        foreach ($targetFiles as $file) {
            $file->accepted = true;
            $file->save();
        }

        $primaryFile = $intakeScanningFile->fresh(['user', 'sourceFileRecord.user', 'responseFileRecord']);
        $userName = $primaryFile?->user?->name
            ?? $primaryFile?->sourceFileRecord?->user?->name
            ?? 'Unknown';

        return response()->json([
            'ok' => true,
            'fileId' => $intakeScanningFile->id,
            'accepted' => true,
            'userName' => $userName,
        ]);
    }

    public function deleteFile(Request $request, IntakeScanningFile $intakeScanningFile): JsonResponse
    {
        $targetFiles = $this->relatedScanningFiles($intakeScanningFile);

        foreach ($targetFiles as $file) {
            $file->deleted = true;
            $file->save();
        }

        return response()->json([
            'ok' => true,
            'fileId' => $intakeScanningFile->id,
            'deleted' => true,
        ]);
    }

    public function runQueuedJobs(int $maxAttempts = 12): array
    {
        $processedJobs = [];
        foreach (IntakeScanningFile::queuedJobIds() as $jobId) {
            $processedJobs[] = $this->processStoredJob($jobId, $maxAttempts);
        }

        return [
            'ok' => true,
            'processedCount' => count($processedJobs),
            'jobs' => $processedJobs,
        ];
    }

    private function processStoredJob(string $jobId, int $maxAttempts): array
    {
        $job = IntakeScanningFile::loadJobState($jobId);
        if (!is_array($job)) {
            return ['ok' => false, 'error' => 'Job not found', 'jobId' => $jobId, 'statusCode' => 404];
        }
        $apiKey = $this->openAiApiKey();
        if ($apiKey === null || $apiKey === '') {
            $job['status'] = 'failed';
            $job['errors'][] = ['error' => 'Server API key not configured'];
            $job['updatedAt'] = date('c');
            $job['completedAt'] = date('c');
            IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);

            return ['ok' => false, 'error' => 'Server API key not configured', 'jobId' => $jobId, 'statusCode' => 500];
        }

        $job['status'] = 'processing';
        $job['startedAt'] = date('c');
        $job['updatedAt'] = date('c');
        IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);

        $instructions = trim((string) ($job['instructions'] ?? ''));
        if ($instructions === '') {
            $instructions = 'Read the delivery label and extract fields to JSON.';
        }

        foreach (IntakeScanningFile::imageRecordsForSession($jobId) as $storedImage) {
            if ($storedImage->responseFileRecord !== null) {
                continue;
            }

            $imageFile = (string) ($storedImage->file?->original_name ?? ('scan_' . $storedImage->id));

            $binary = $this->loadStoredImageBinary($storedImage);
            if ($binary === null) {
                $errorMessage = 'Stored image file not found';
                $job['errors'][] = ['file' => $imageFile, 'error' => $errorMessage];
                $this->markStoredImageError($storedImage, $errorMessage);
                $job['updatedAt'] = date('c');
                IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);
                continue;
            }

            $mimeType = $storedImage->resolvedMimeType();
            $imageNumber = max(1, (int) $storedImage->sequence);
            $body = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You convert delivery labels into structured JSON files.',
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $instructions],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:' . $mimeType . ';base64,' . base64_encode($binary),
                                    'detail' => 'high',
                                ],
                            ],
                            ['type' => 'text', 'text' => 'Image ' . $imageNumber],
                        ],
                    ],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'max_tokens' => 800,
            ];

            $ai = $this->callOpenAiWithRetry($body, $apiKey, $maxAttempts);
            if (!$ai['ok']) {
                $errorMessage = $ai['error'] ?? 'Unknown OpenAI error';
                $job['errors'][] = [
                    'file' => $imageFile,
                    'status' => $ai['status'] ?? null,
                    'error' => $errorMessage,
                ];
                $this->markStoredImageError($storedImage, $errorMessage);
                $job['updatedAt'] = date('c');
                IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);
                continue;
            }

            $structuredResponse = $this->extractStructuredResponse($ai['data']);
            $responseFile = $this->storeJsonResponseFile($storedImage, $structuredResponse);
            if ($responseFile === null) {
                $errorMessage = 'Failed to store OCR JSON response';
                $job['errors'][] = [
                    'file' => $imageFile,
                    'error' => $errorMessage,
                ];
                $this->markStoredImageError($storedImage, $errorMessage);
                $job['updatedAt'] = date('c');
                IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);
                continue;
            }

            $this->markStoredImageProcessed($storedImage);

            $job['results'][] = [
                'file' => $imageFile,
                'scanFileId' => $storedImage->id,
                'responseFileId' => $responseFile->id,
            ];
            $job['updatedAt'] = date('c');
            $job['images'] = IntakeScanningFile::imageMetadataForSession($jobId);
            IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);
            usleep(350000);
        }

        $job = IntakeScanningFile::loadJobState($jobId);
        if (is_array($job)) {
            $job['status'] = 'completed';
            $job['completedAt'] = date('c');
            $job['updatedAt'] = date('c');
            $job['images'] = IntakeScanningFile::imageMetadataForSession($jobId);
            IntakeScanningFile::saveJobState($jobId, $job, $job['intakeId'] ?? null);
        }

        return [
            'ok' => true,
            'jobId' => $jobId,
            'status' => 'completed',
            'summary' => is_array($job) ? $this->jobSummary($job, $jobId) : null,
        ];
    }

    private function callOpenAiWithRetry(array $body, string $apiKey, int $maxAttempts): array
    {
        $lastError = null;
        $lastStatus = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $curl = curl_init('https://api.openai.com/v1/chat/completions');
            if ($curl === false) {
                return ['ok' => false, 'error' => 'Failed to initialize cURL', 'status' => null];
            }

            $options = [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => 120,
            ];

            $certificatePath = $this->certificatePath();
            if ($certificatePath !== null) {
                $options[CURLOPT_CAINFO] = $certificatePath;
            }

            curl_setopt_array($curl, $options);

            $rawResponse = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($rawResponse === false) {
                $responseHeaders = '';
                $responseBody = '';
            } else {
                $responseHeaders = substr($rawResponse, 0, $headerSize);
                $responseBody = substr($rawResponse, $headerSize);
            }

            $lastStatus = $httpCode;

            if ($curlError === '' && $httpCode >= 200 && $httpCode < 300) {
                $decoded = json_decode($responseBody, true);
                if (is_array($decoded)) {
                    return ['ok' => true, 'data' => $decoded, 'status' => $httpCode];
                }

                $lastError = 'Invalid JSON response from OpenAI';
            } else {
                $lastError = $curlError !== '' ? $curlError : $responseBody;
            }

            $retryableHttp = in_array($httpCode, [429, 500, 502, 503, 504], true);
            $retryable = $curlError !== '' || $retryableHttp;
            if (!$retryable || $attempt >= $maxAttempts) {
                break;
            }

            usleep($this->retryDelayMilliseconds($attempt, $responseHeaders, $responseBody) * 1000);
        }

        return ['ok' => false, 'error' => $lastError ?: 'Unknown OpenAI error', 'status' => $lastStatus];
    }

    private function retryDelayMilliseconds(int $attempt, string $headersRaw, string $responseBody): int
    {
        $fromHeaders = $this->parseRetryAfterMillisecondsFromHeaders($headersRaw);
        if ($fromHeaders !== null) {
            return min(30000, max(200, $fromHeaders));
        }

        $fromBody = $this->parseRetryAfterMillisecondsFromBody($responseBody);
        if ($fromBody !== null) {
            return min(30000, max(200, $fromBody));
        }

        $base = (int) min(30000, 500 * pow(2, max(0, $attempt - 1)));

        return $base + random_int(0, 250);
    }

    private function parseRetryAfterMillisecondsFromHeaders(string $headersRaw): ?int
    {
        if ($headersRaw === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $headersRaw) ?: [];
        foreach ($lines as $line) {
            if (stripos($line, 'Retry-After:') !== 0) {
                continue;
            }

            $value = trim(substr($line, strlen('Retry-After:')));
            if ($value === '') {
                return null;
            }

            if (is_numeric($value)) {
                return max(0, (int) $value * 1000);
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return max(0, ($timestamp - time()) * 1000);
            }
        }

        return null;
    }

    private function parseRetryAfterMillisecondsFromBody(string $responseBody): ?int
    {
        if ($responseBody === '') {
            return null;
        }

        $decoded = json_decode($responseBody, true);
        $message = $decoded['error']['message'] ?? '';
        if (!is_string($message) || $message === '') {
            return null;
        }

        if (preg_match('/Please try again in\s*([0-9]+)\s*ms/i', $message, $match) === 1) {
            return max(0, (int) $match[1]);
        }

        if (preg_match('/Please try again in\s*([0-9]+(?:\.[0-9]+)?)\s*s/i', $message, $match) === 1) {
            return max(0, (int) round(((float) $match[1]) * 1000));
        }

        return null;
    }

    private function openAiApiKey(): ?string
    {
        $envKey = env('OPENAI_API_KEY');
        if (is_string($envKey) && $envKey !== '') {
            return $envKey;
        }

        $configFile = base_path('api/config.local.php');
        if (!is_file($configFile)) {
            return null;
        }

        $config = @include $configFile;
        if (!is_array($config)) {
            return null;
        }

        $apiKey = $config['OPENAI_API_KEY'] ?? null;

        return is_string($apiKey) && $apiKey !== '' ? $apiKey : null;
    }

    private function certificatePath(): ?string
    {
        $paths = [
            base_path('certs/cacert.pem'),
            'C:\\inetpub\\intakemaster\\certs\\cacert.pem',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function jobSummary(array $job, ?string $fallbackJobId): array
    {
        $jobId = (string) ($job['jobId'] ?? $fallbackJobId ?? '');
        $imageStats = $this->imageStatsMap($jobId !== '' ? [$jobId] : []);
        $stats = $jobId !== '' ? ($imageStats[$jobId] ?? null) : null;

        $totalImages = (int) (($stats['storedImageCount'] ?? 0) ?: count($job['images'] ?? []));
        $processedCount = (int) (($stats['resolvedImageCount'] ?? 0) ?: (count($job['results'] ?? []) + count($job['errors'] ?? [])));
        if ($processedCount > $totalImages) {
            $processedCount = $totalImages;
        }

        $errorCount = (int) (($stats['dbErrorCount'] ?? 0) ?: count($job['errors'] ?? []));
        $progressPercent = $totalImages > 0 ? (int) round(($processedCount / $totalImages) * 100) : 0;
        $status = (string) ($job['status'] ?? 'unknown');
        if ($status === 'completed') {
            $progressPercent = 100;
        }
        if ($progressPercent > 100) {
            $progressPercent = 100;
        }

        return [
            'ok' => true,
            'jobId' => $jobId,
            'intakeId' => $job['intakeId'] ?? null,
            'status' => $status,
            'totalImages' => $totalImages,
            'processedCount' => $processedCount,
            'errorCount' => $errorCount,
            'progressPercent' => $progressPercent,
            'storedImageCount' => (int) ($stats['storedImageCount'] ?? $totalImages),
            'processedImageCount' => (int) ($stats['processedImageCount'] ?? max(0, $processedCount - $errorCount)),
            'pendingImageCount' => (int) ($stats['pendingImageCount'] ?? max(0, $totalImages - $processedCount)),
            'errorMessages' => $stats['errorMessages'] ?? [],
            'latestError' => $stats['latestError'] ?? null,
            'createdAt' => $job['createdAt'] ?? null,
            'queuedAt' => $job['queuedAt'] ?? null,
            'startedAt' => $job['startedAt'] ?? null,
            'updatedAt' => $job['updatedAt'] ?? null,
            'completedAt' => $job['completedAt'] ?? null,
        ];
    }

    private function storeScanningFile($intakeIdRaw, string $uploadSessionId, string $fileName, string $mimeType, int $sequence, string $binary, string $fileRole, ?IntakeScanningFile $sourceFileRecord = null, ?array $jsonPayload = null): ?IntakeScanningFile
    {
        return IntakeScanningFile::createStored(
            $intakeIdRaw,
            $uploadSessionId,
            $fileName,
            $mimeType,
            $sequence,
            $binary,
            $fileRole,
            $sourceFileRecord,
            $jsonPayload
        );
    }

    private function loadStoredImageBinary(IntakeScanningFile $storedImage): ?string
    {
        return $storedImage->readBinaryContents();
    }

    private function storeJsonResponseFile(IntakeScanningFile $storedImage, array $structuredResponse): ?IntakeScanningFile
    {
        return $storedImage->storeJsonResponseFile($structuredResponse);
    }

    private function imageStatsMap(array $uploadSessionIds): array
    {
        return IntakeScanningFile::statsForUploadSessions($uploadSessionIds);
    }

    private function markStoredImageProcessed(?IntakeScanningFile $storedImage): void
    {
        if ($storedImage === null) {
            return;
        }

        $storedImage->markProcessed();
    }

    private function markStoredImageError(?IntakeScanningFile $storedImage, string $errorMessage): void
    {
        if ($storedImage === null) {
            return;
        }

        $storedImage->markError($errorMessage);
    }

    private function extractStructuredResponse(array $response): array
    {
        $content = $response['choices'][0]['message']['content'] ?? null;
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return is_array($content) ? $content : [];
    }

    private function relatedScanningFiles(IntakeScanningFile $intakeScanningFile): array
    {
        $intakeScanningFile->loadMissing(['sourceFileRecord', 'responseFileRecord']);

        $files = collect([
            $intakeScanningFile,
            $intakeScanningFile->sourceFileRecord,
            $intakeScanningFile->responseFileRecord,
        ])->filter();

        if ($intakeScanningFile->file_role === IntakeScanningFile::ROLE_JSON && $intakeScanningFile->sourceFileRecord !== null) {
            $intakeScanningFile->sourceFileRecord->loadMissing('responseFileRecord');
            $files->push($intakeScanningFile->sourceFileRecord->responseFileRecord);
        }

        if ($intakeScanningFile->file_role === IntakeScanningFile::ROLE_IMAGE && $intakeScanningFile->responseFileRecord !== null) {
            $intakeScanningFile->responseFileRecord->loadMissing('sourceFileRecord');
            $files->push($intakeScanningFile->responseFileRecord->sourceFileRecord);
        }

        return $files
            ->filter()
            ->unique('id')
            ->values()
            ->all();
    }


    private function sanitizeIdentifier(string $value, string $fallback): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_-]+/', '_', $value);

        return $sanitized === null || $sanitized === '' ? $fallback : $sanitized;
    }

    private function extensionFromMime(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/gif' => 'gif',
        ];

        return $map[strtolower($mime)] ?? 'jpg';
    }

    private function mimeTypeFromExtension(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
        ];

        return $map[$extension] ?? 'image/jpeg';
    }
}
