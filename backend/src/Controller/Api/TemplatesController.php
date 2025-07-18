<?php

namespace App\Controller\Api;

use Cake\Http\Response;
use Cake\Event\EventInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Exception\NotFoundException;

class TemplatesController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['download']); // Make "download" public
    }

    public function download($filename): Response
    {
        // if ($this->request->getAttibute('identity') === null) {
            // throw new UnauthorizedException(__('You are not authorized to access this page'));
        // }

        $safeFilename = basename($filename);
        $filePath = WWW_ROOT . 'templates' . DS . $safeFilename;

        if (!file_exists($filePath)) {
            throw new NotFoundException(__('File not found'));
        }

        // Determine MIME type dynamically
        $mimeType = $this->getMimeType($safeFilename);

        return $this->response
            ->withType($mimeType)
            ->withFile($filePath, [
                'download' => true,
                'name' => $filename,
            ]);
    }

    private function getMimeType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'csv'  => 'text/csv',
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'txt'  => 'text/plain',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream'; // Default MIME type
    }
}
