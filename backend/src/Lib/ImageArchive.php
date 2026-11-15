<?php

namespace App\Lib;

use Cake\Cache\Cache;
use Cake\Http\Client;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class ImageArchive
{
    public const INDEX = 'https://s3.us-west-2.amazonaws.com/languageconservancy.org/Images/Web/files.yml';
    private const BASE = 'https://s3.us-west-2.amazonaws.com/languageconservancy.org/';

    public function files(): array
    {
        if (!Cache::getConfig('imageArchive')) {
            Cache::setConfig('imageArchive', [
                'className' => 'File', 'path' => CACHE, 'prefix' => 'image_archive_',
                'duration' => '+1 hour',
            ]);
        }

        return Cache::remember('index', function () {
            $response = (new Client())->get(self::INDEX, [], ['timeout' => 15]);
            if (!$response->isOk()) {
                throw new RuntimeException('The image archive is unavailable. Please try again later.');
            }

            return self::parse($response->getStringBody());
        }, 'imageArchive');
    }

    public static function parse(string $yaml): array
    {
        $document = Yaml::parse($yaml);
        if (!is_array($document) || !isset($document['files']) || !is_array($document['files'])) {
            throw new RuntimeException('The image archive index has an invalid format.');
        }
        $files = [];
        foreach ($document['files'] as $entry) {
            $key = $entry['key'] ?? '';
            if (
                !is_string($key) || !str_starts_with($key, 'Images/Web/')
                || preg_match('#(?:^|/)\.\.(?:/|$)#', $key)
                || !preg_match('/\.(?:png|jpe?g|gif|webp|avif)$/i', $key)
            ) {
                continue;
            }
            $files[] = [
                'key' => $key,
                'name' => basename($key),
                'url' => self::BASE . implode('/', array_map('rawurlencode', explode('/', $key))),
            ];
        }

        return $files;
    }
}
