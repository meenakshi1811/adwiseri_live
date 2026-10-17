<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;

class InternalMessageAttachments
{
    public const MAX_FILES = 5;

    public const MAX_TOTAL_BYTES = 26214400; // 25 MB

    /** @var string[] */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

    /**
     * @param  array<int, UploadedFile|null>  $files
     * @return array<int, array{name: string, file: string, size: int, mime: string}>
     */
    public static function storeBatch(int $subscriberId, int $messageId, array $files): array
    {
        $files = array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));

        if ($files === []) {
            return [];
        }

        self::assertWithinLimits($files);

        $relativeDir = 'web_assets/users/message_attachments/' . $subscriberId . '/' . $messageId;
        $absoluteDir = public_path($relativeDir);

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new InvalidArgumentException('Unable to prepare attachment storage.');
        }

        $stored = [];

        foreach ($files as $upload) {
            $extension = strtolower($upload->getClientOriginalExtension() ?: '');
            if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                throw new InvalidArgumentException('One or more files use a format that is not allowed.');
            }

            $safeBase = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME));
            if ($safeBase === '') {
                $safeBase = 'attachment';
            }

            $storedName = time() . '_' . Str::random(8) . '_' . $safeBase . '.' . $extension;
            $originalName = $upload->getClientOriginalName();
            $size = (int) $upload->getSize();
            $mime = (string) ($upload->getClientMimeType() ?: $upload->getMimeType());
            $upload->move($absoluteDir, $storedName);

            $stored[] = [
                'name' => $originalName,
                'file' => $storedName,
                'size' => $size,
                'mime' => $mime,
            ];
        }

        return $stored;
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    public static function assertWithinLimits(array $files): void
    {
        $files = array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));

        if (count($files) > self::MAX_FILES) {
            throw new InvalidArgumentException('You can attach up to ' . self::MAX_FILES . ' documents.');
        }

        $totalBytes = 0;
        foreach ($files as $file) {
            $totalBytes += (int) $file->getSize();
        }

        if ($totalBytes > self::MAX_TOTAL_BYTES) {
            throw new InvalidArgumentException('Total attachment size must not exceed 25 MB.');
        }
    }

    public static function publicUrl(int $subscriberId, int $messageId, string $storedName): string
    {
        return asset('web_assets/users/message_attachments/' . $subscriberId . '/' . $messageId . '/' . $storedName);
    }

    /**
     * @param  mixed  $attachments
     * @return array<int, array{name: string, file: string, size?: int, mime?: string}>
     */
    public static function normalizeList($attachments): array
    {
        if (!is_array($attachments)) {
            return [];
        }

        $list = [];
        foreach ($attachments as $item) {
            if (!is_array($item)) {
                continue;
            }
            $file = trim((string) ($item['file'] ?? ''));
            $name = trim((string) ($item['name'] ?? $file));
            if ($file === '') {
                continue;
            }
            $list[] = [
                'name' => $name !== '' ? $name : $file,
                'file' => $file,
                'size' => isset($item['size']) ? (int) $item['size'] : 0,
                'mime' => (string) ($item['mime'] ?? ''),
            ];
        }

        return $list;
    }
}
