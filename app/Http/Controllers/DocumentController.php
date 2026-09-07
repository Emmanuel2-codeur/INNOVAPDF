<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function create()
    {
        return Inertia::render('Editor', [
            'document' => null,
        ]);
    }

    public function edit(Document $document)
    {
        return Inertia::render('Editor', [
            'document' => $document,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'template' => 'required|string',
            'content' => 'required|array',
            'style' => 'required|array',
        ]);

        $document = $request->user()->documents()->create($validated);

        return response()->json(['status' => 'success', 'document' => $document]);
    }
}