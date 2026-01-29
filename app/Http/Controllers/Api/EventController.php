<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Jobs\ProcessEvent;

class EventController extends Controller
{
    public function store(StoreEventRequest $request)
    {
    $data = $request->validated();

    $event = Event::create($data);

    ProcessEvent::dispatch($event->id);

    return response()->json([
        'id' => $event->id,
        'status' => 'created',
    ], 201);
    }

    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source'));
        }

        $query->orderByDesc('id');

        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100)); 

        return response()->json(
            $query->paginate($perPage)
        );
    }

    public function show(Event $event)
    {
        return response()->json($event);
    }
}
