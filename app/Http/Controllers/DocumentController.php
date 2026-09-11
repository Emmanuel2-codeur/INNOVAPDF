<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class DocumentController extends Controller
{
    /**
     * Dashboard : liste des documents de l'utilisateur connecté.
     * (Cahier des charges §"Dashboard utilisateur" — vue des documents,
     * recherche, filtrage et tri.)
     */
    public function index(Request $request)
    {
        $query = $request->user()->documents()->latest('updated_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        return Inertia::render('Dashboard', [
            'documents' => $query->get(['id', 'title', 'type', 'template', 'status', 'updated_at']),
            'filters' => $request->only('search', 'type'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Editor', [
            'document' => null,
        ]);
    }

    public function edit(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        return Inertia::render('Editor', [
            'document' => $document,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDocument($request);

        $document = $request->user()->documents()->create($validated);

        return redirect()->route('editor.edit', $document)->with('success', 'Document créé.');
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $this->validateDocument($request);

        $document->update($validated);

        return back()->with('success', 'Document enregistré.');
    }

    public function destroy(Request $request, Document $document)
    {
        $this->authorize('delete', $document);

        $document->delete(); // Suppression douce (SoftDeletes) : va dans la corbeille

        return back()->with('success', 'Document déplacé dans la corbeille.');
    }

    /**
     * Corbeille : documents supprimés (logiquement) par l'utilisateur courant.
     */
    public function trash(Request $request)
    {
        $documents = $request->user()->documents()
            ->onlyTrashed()
            ->latest('deleted_at')
            ->get(['id', 'title', 'type', 'template', 'status', 'deleted_at']);

        return Inertia::render('Trash', ['documents' => $documents]);
    }

    public function restore(Request $request, Document $document)
    {
        $this->authorize('restore', $document);

        $document->restore();

        return back()->with('success', 'Document restauré.');
    }

    public function forceDelete(Request $request, Document $document)
    {
        $this->authorize('forceDelete', $document);

        $document->forceDelete();

        return back()->with('success', 'Document supprimé définitivement.');
    }

    /**
     * Export PDF haute fidélité du document (rendu serveur Dompdf, format A4).
     * Cahier des charges §14 — Export et impression.
     */
    public function export(Document $document)
    {
        $this->authorize('view', $document);

        $view = match ("{$document->type}.{$document->template}") {
            'cv.minimal' => 'pdf.cv-minimal',
            'cv.modern' => 'pdf.cv-modern',
            'invoice.classic' => 'pdf.invoice-classic',
            default => null,
        };

        abort_if($view === null, 422, "Aucun gabarit d'export PDF pour {$document->type}/{$document->template}.");

        $filename = str($document->title ?: 'document')->slug()->append('.pdf')->toString();

        return Pdf::view($view, ['doc' => $document])
            ->format('a4')
            ->driver('dompdf')
            ->download($filename);
    }

    /**
     * Upload d'un média (photo de profil ou logo d'entreprise).
     * Cahier des charges §3 "Médias" — validation type/taille, prévisualisation instantanée.
     */
    public function uploadMedia(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // URL publique du média précédent, à supprimer s'il appartient bien à l'utilisateur.
            'previous_url' => ['nullable', 'string'],
        ]);

        $userMediaPrefix = 'media/' . $request->user()->id . '/';

        if (! empty($validated['previous_url'])) {
            $previousPath = ltrim(parse_url($validated['previous_url'], PHP_URL_PATH) ?? '', '/');
            $previousPath = preg_replace('#^storage/#', '', $previousPath);

            // On ne supprime que si le fichier appartient bien au dossier de l'utilisateur courant,
            // pour éviter qu'un utilisateur ne fasse supprimer le média d'un autre.
            if (str_starts_with($previousPath, $userMediaPrefix) && Storage::disk('public')->exists($previousPath)) {
                Storage::disk('public')->delete($previousPath);
            }
        }

        $path = $request->file('file')->store('media/' . $request->user()->id, 'public');

        return response()->json([
            'status' => 'success',
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    private function validateDocument(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:cv,cover_letter,invoice,quote,certificate,attestation',
            'template' => 'required|string|max:100',
            'content' => 'required|array',
            'style' => 'required|array',
        ]);
    }
}