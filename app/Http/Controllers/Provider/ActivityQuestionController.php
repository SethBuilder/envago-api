<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityQuestion;
use Illuminate\Http\Request;

class ActivityQuestionController extends Controller
{
    private function authorizedActivity(int $activityId): Activity
    {
        return Activity::where('id', $activityId)
            ->where('provider_id', auth()->user()->provider->id)
            ->firstOrFail();
    }

    public function store(Request $request, int $activityId)
    {
        $activity = $this->authorizedActivity($activityId);

        $data = $request->validate([
            'label'    => ['required', 'string', 'max:255'],
            'type'     => ['required', 'in:text,select'],
            'options'  => ['nullable', 'string'],
            'required' => ['boolean'],
        ]);

        $options = null;
        if ($data['type'] === 'select' && !empty($data['options'])) {
            $options = array_values(array_filter(array_map('trim', explode("\n", $data['options']))));
        }

        $maxOrder = $activity->questions()->max('sort_order') ?? -1;

        $question = $activity->questions()->create([
            'label'      => $data['label'],
            'type'       => $data['type'],
            'options'    => $options,
            'required'   => $data['required'] ?? true,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['id' => $question->id, 'label' => $question->label, 'type' => $question->type, 'options' => $question->options, 'required' => $question->required]);
    }

    public function destroy(int $activityId, int $questionId)
    {
        $activity = $this->authorizedActivity($activityId);
        $activity->questions()->where('id', $questionId)->firstOrFail()->delete();
        return response()->json(['ok' => true]);
    }
}
