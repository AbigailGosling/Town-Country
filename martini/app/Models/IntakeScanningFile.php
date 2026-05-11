<?php

namespace App\Models;

use App\Http\Controllers\FileController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class IntakeScanningFile extends Model
{
    public const ROLE_IMAGE = 'image';

    public const ROLE_JSON = 'json';

    public const ROLE_JOB = 'job';

    protected $connection = 'tandc_live';

    protected $table = 'intake_scanning_file';

    public $timestamps = true;

    protected $fillable = [
        'intake_id',
        'user_id',
        'upload_session_id',
        'file_id',
        'sequence',
        'file_role',
        'source_file_id',
        'json_payload',
        'accepted',
        'deleted',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'intake_id' => 'integer',
        'user_id' => 'integer',
        'file_id' => 'integer',
        'sequence' => 'integer',
        'source_file_id' => 'integer',
        'json_payload' => 'array',
        'accepted' => 'boolean',
        'deleted' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function intake()
    {
        return $this->belongsTo(Intake::class, 'intake_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    public function sourceFileRecord()
    {
        return $this->belongsTo(self::class, 'source_file_id');
    }

    public function responseFileRecord()
    {
        return $this->hasOne(self::class, 'source_file_id', 'id')
            ->where('file_role', self::ROLE_JSON);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted', false);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('file_role', self::ROLE_IMAGE);
    }

    public function scopeForUploadSessions(Builder $query, array $uploadSessionIds): Builder
    {
        return $query->whereIn('upload_session_id', $uploadSessionIds);
    }

    public function scopeJobRecords(Builder $query): Builder
    {
        return $query->where('file_role', self::ROLE_JOB);
    }

    public static function createStored(
        $intakeIdRaw,
        string $uploadSessionId,
        string $fileName,
        string $mimeType,
        int $sequence,
        string $binary,
        string $fileRole,
        ?self $sourceFileRecord = null,
        ?array $jsonPayload = null
    ): ?self {
        $tmpPath = tempnam(sys_get_temp_dir(), 'intake_scan_');
        if ($tmpPath === false) {
            return null;
        }

        if (@file_put_contents($tmpPath, $binary) === false) {
            @unlink($tmpPath);

            return null;
        }

        try {
            $uploadedFile = new UploadedFile($tmpPath, $fileName, $mimeType, null, true);
            $storedFile = FileController::PROCESS_ACTUAL_FILE($uploadedFile);

            return self::create([
                'intake_id' => is_numeric($intakeIdRaw) ? (int) $intakeIdRaw : null,
                'user_id' => $sourceFileRecord?->user_id ?? (auth()->check() ? (int) auth()->id() : null),
                'upload_session_id' => $uploadSessionId,
                'file_id' => $storedFile->id,
                'sequence' => $sequence,
                'file_role' => $fileRole,
                'source_file_id' => $sourceFileRecord?->id,
                'json_payload' => $jsonPayload,
                'accepted' => false,
                'deleted' => false,
                'processed_at' => $fileRole === self::ROLE_JSON ? now() : null,
            ]);
        } catch (\Throwable $throwable) {
            return null;
        } finally {
            if (is_file($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

    public static function findActiveByScanFileId(int $scanFileId): ?self
    {
        if ($scanFileId <= 0) {
            return null;
        }

        return self::with('file')
            ->active()
            ->find($scanFileId);
    }

    public static function imageRecordsForSession(string $uploadSessionId): Collection
    {
        return self::query()
            ->with(['file', 'responseFileRecord.file'])
            ->active()
            ->images()
            ->where('upload_session_id', $uploadSessionId)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();
    }

    public static function imageMetadataForSession(string $uploadSessionId): array
    {
        return self::imageRecordsForSession($uploadSessionId)
            ->map(static function (self $storedImage): array {
                return [
                    'scanFileId' => $storedImage->id,
                    'fileId' => $storedImage->file_id,
                    'index' => max(0, ((int) $storedImage->sequence) - 1),
                    'file' => (string) ($storedImage->file?->original_name ?? ('scan_' . $storedImage->id)),
                    'mimeType' => (string) ($storedImage->file?->mime_type ?? 'image/jpeg'),
                    'savedAt' => $storedImage->created_at?->toAtomString(),
                ];
            })
            ->values()
            ->all();
    }

    public static function findJobRecord(string $uploadSessionId): ?self
    {
        return self::query()
            ->active()
            ->jobRecords()
            ->where('upload_session_id', $uploadSessionId)
            ->latest('id')
            ->first();
    }

    public static function loadJobState(string $uploadSessionId): ?array
    {
        $jobRecord = self::findJobRecord($uploadSessionId);
        if ($jobRecord === null || !is_array($jobRecord->json_payload)) {
            return null;
        }

        return $jobRecord->json_payload;
    }

    public static function saveJobState(string $uploadSessionId, array $job, $intakeIdRaw = null): ?self
    {
        $jobRecord = self::findJobRecord($uploadSessionId);
        $attributes = [
            'intake_id' => is_numeric($intakeIdRaw) ? (int) $intakeIdRaw : (is_numeric($job['intakeId'] ?? null) ? (int) $job['intakeId'] : null),
            'upload_session_id' => $uploadSessionId,
            'file_id' => null,
            'sequence' => 0,
            'file_role' => self::ROLE_JOB,
            'source_file_id' => null,
            'json_payload' => $job,
        ];

        if ($jobRecord !== null) {
            $jobRecord->fill($attributes);

            return $jobRecord->save() ? $jobRecord : null;
        }

        return self::create($attributes + [
            'user_id' => auth()->check() ? (int) auth()->id() : null,
            'accepted' => false,
            'deleted' => false,
            'error_message' => null,
            'processed_at' => null,
        ]);
    }

    public static function listJobStates(int $limit = 25): array
    {
        return self::query()
            ->active()
            ->jobRecords()
            ->orderByDesc('updated_at')
            ->limit(max(1, $limit))
            ->get()
            ->map(static function (self $jobRecord): array {
                $payload = is_array($jobRecord->json_payload) ? $jobRecord->json_payload : [];

                return array_merge([
                    'jobId' => $jobRecord->upload_session_id,
                ], $payload);
            })
            ->values()
            ->all();
    }

    public static function queuedJobIds(): array
    {
        return self::query()
            ->active()
            ->jobRecords()
            ->orderBy('updated_at')
            ->get()
            ->filter(static function (self $jobRecord): bool {
                return ($jobRecord->json_payload['status'] ?? null) === 'queued';
            })
            ->pluck('upload_session_id')
            ->values()
            ->all();
    }

    public function readBinaryContents(): ?string
    {
        if ($this->file === null) {
            return null;
        }

        $path = storage_path('app/public/uploads/' . $this->file->uuid);
        if (!is_file($path)) {
            return null;
        }

        $binary = @file_get_contents($path);

        return $binary === false ? null : $binary;
    }

    public function resolvedMimeType(?string $fallbackMimeType = null): string
    {
        if ($this->file?->mime_type) {
            return (string) $this->file->mime_type;
        }

        return (string) ($fallbackMimeType ?: 'image/jpeg');
    }

    public function storeJsonResponseFile(array $structuredResponse): ?self
    {
        $fileName = pathinfo((string) ($this->file?->original_name ?? ('scan_' . $this->id)), PATHINFO_FILENAME) . '_response.json';
        $json = json_encode($structuredResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return null;
        }

        return self::createStored(
            $this->intake_id,
            (string) $this->upload_session_id,
            $fileName,
            'application/json',
            (int) $this->sequence,
            $json,
            self::ROLE_JSON,
            $this,
            $structuredResponse
        );
    }

    public function markProcessed(): void
    {
        $this->error_message = null;
        $this->processed_at = now();
        $this->save();
    }

    public function markError(string $errorMessage): void
    {
        $this->error_message = $errorMessage;
        $this->processed_at = now();
        $this->save();
    }

    public static function statsForUploadSessions(array $uploadSessionIds): array
    {
        $uploadSessionIds = array_values(array_unique(array_filter(array_map(static function ($value): string {
            return trim((string) $value);
        }, $uploadSessionIds))));

        if ($uploadSessionIds === []) {
            return [];
        }

        $stats = [];
        foreach ($uploadSessionIds as $uploadSessionId) {
            $stats[$uploadSessionId] = [
                'storedImageCount' => 0,
                'processedImageCount' => 0,
                'dbErrorCount' => 0,
                'resolvedImageCount' => 0,
                'pendingImageCount' => 0,
                'errorMessages' => [],
                'latestError' => null,
            ];
        }

        $files = self::query()
            ->forUploadSessions($uploadSessionIds)
            ->active()
            ->orderBy('upload_session_id')
            ->orderBy('file_role')
            ->orderBy('sequence')
            ->get(['id', 'upload_session_id', 'file_role', 'source_file_id', 'error_message']);

        foreach ($files as $storedFile) {
            $sessionId = (string) $storedFile->upload_session_id;
            if (!isset($stats[$sessionId])) {
                continue;
            }

            if ($storedFile->file_role === self::ROLE_JOB) {
                continue;
            }

            if ($storedFile->file_role === self::ROLE_JSON) {
                $sourceFileId = (int) ($storedFile->source_file_id ?? 0);
                if ($sourceFileId > 0 && !isset($stats[$sessionId]['processedSourceIds'][$sourceFileId])) {
                    $stats[$sessionId]['processedSourceIds'][$sourceFileId] = true;
                    $stats[$sessionId]['processedImageCount']++;
                    $stats[$sessionId]['resolvedImageCount']++;
                }

                continue;
            }

            $stats[$sessionId]['storedImageCount']++;
            $errorMessage = trim((string) ($storedFile->error_message ?? ''));

            if ($errorMessage !== '') {
                $stats[$sessionId]['dbErrorCount']++;
                $stats[$sessionId]['resolvedImageCount']++;
                $stats[$sessionId]['latestError'] = $errorMessage;

                if (!in_array($errorMessage, $stats[$sessionId]['errorMessages'], true)) {
                    $stats[$sessionId]['errorMessages'][] = $errorMessage;
                }
            }
        }

        foreach ($stats as $sessionId => $sessionStats) {
            $resolved = $sessionStats['resolvedImageCount'];
            $stored = $sessionStats['storedImageCount'];
            $stats[$sessionId]['pendingImageCount'] = max(0, $stored - $resolved);
            $stats[$sessionId]['errorMessages'] = array_slice($sessionStats['errorMessages'], 0, 3);
            unset($stats[$sessionId]['processedSourceIds']);
        }

        return $stats;
    }
}
