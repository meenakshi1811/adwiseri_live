<?php

namespace App\Http\Controllers;

use App\Services\DocumentChecklistUploadService;
use Illuminate\Http\Request;
use RuntimeException;

class DocumentChecklistUploadController extends Controller
{
    public function show(int $application, string $token, DocumentChecklistUploadService $uploadService)
    {
        $record = $uploadService->resolveApplication($application, $token);
        if (!$record) {
            abort(404, 'This documents checklist upload link is invalid or has expired.');
        }

        try {
            $pageData = $uploadService->buildUploadPageData($record);
        } catch (RuntimeException $exception) {
            abort(404, $exception->getMessage());
        }

        return view('web.document_checklist_upload', array_merge($pageData, [
            'token' => $token,
            'uploadAction' => route('document_checklist_upload.store', [
                'application' => $record->id,
                'token' => $token,
            ]),
        ]));
    }

    public function store(Request $request, int $application, string $token, DocumentChecklistUploadService $uploadService)
    {
        $record = $uploadService->resolveApplication($application, $token);
        if (!$record) {
            abort(404, 'This documents checklist upload link is invalid or has expired.');
        }

        $result = $uploadService->handleUpload($record, $request);
        if (!$result['success']) {
            return back()->withInput()->with('upload_error', $result['message']);
        }

        return redirect()
            ->route('document_checklist_upload', [
                'application' => $record->id,
                'token' => $token,
            ])
            ->with('upload_success', $result['message']);
    }
}
