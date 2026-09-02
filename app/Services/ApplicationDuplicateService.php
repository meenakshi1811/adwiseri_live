<?php

namespace App\Services;

use App\Models\Applications;
use Illuminate\Http\Request;

class ApplicationDuplicateService
{
    public function findDuplicate(int $clientId, string $applicationName, ?int $subscriberId = null): ?Applications
    {
        $applicationName = trim($applicationName);
        if ($applicationName === '') {
            return null;
        }

        $query = Applications::query()
            ->where('client_id', $clientId)
            ->where('application_name', $applicationName);

        if ($subscriberId) {
            $query->where('subscriber_id', $subscriberId);
        }

        return $query->orderByDesc('id')->first();
    }

    public function duplicateMessage(Applications $application): string
    {
        $reference = $application->application_id ?: $application->id;

        return sprintf(
            'An application of type "%s" already exists for this client (Application ID: %s). Do you still want to create another application?',
            $application->application_name,
            $reference
        );
    }

    public function validationError(Request $request, int $clientId, string $applicationName, ?int $subscriberId): ?string
    {
        if ($request->boolean('confirm_duplicate')) {
            return null;
        }

        $duplicate = $this->findDuplicate($clientId, $applicationName, $subscriberId);

        return $duplicate ? $this->duplicateMessage($duplicate) : null;
    }
}
