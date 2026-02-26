<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action
     */
    public function log($action, $modelType, $modelId, $oldValues = null, $newValues = null, $userId = null)
    {
        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    /**
     * Get audit logs
     */
    public function getLogs($modelType = null, $modelId = null, $action = null, $userId = null, $startDate = null, $endDate = null, $limit = 100)
    {
        $query = AuditLog::query();

        if ($modelType) {
            $query->where('model_type', $modelType);
        }
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        if ($action) {
            $query->where('action', $action);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->orderByDesc('created_at')->limit($limit)->get();
    }
}
