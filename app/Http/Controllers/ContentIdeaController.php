<?php

namespace App\Http\Controllers;

use App\Models\ContentIdea;
use Illuminate\Http\Request;

class ContentIdeaController extends Controller
{
    /**
     * Affiche la liste des idées de contenu (partagées entre tous les clients)
     */
    public function index()
    {
        $contentIdeas = ContentIdea::orderBy('created_at', 'desc')->get();
        return $this->viewForRole('content-ideas.index', compact('contentIdeas'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return view('content-ideas.create');
    }

    /**
     * Enregistre une nouvelle idée de contenu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:vidéo,image,texte'],
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être vidéo, image ou texte.',
        ]);

        $contentIdea = ContentIdea::create($validated);

        // Redirection intelligente : si on vient d'un formulaire qui nécessitait cette idée, on y retourne
        if ($request->has('return_to')) {
            return redirect($request->input('return_to'))
                ->with('success', 'Idée de contenu créée avec succès. Vous pouvez maintenant l\'utiliser.')
                ->with('created_content_idea_id', $contentIdea->id);
        }

        return redirect()->route('content-ideas.index')
            ->with('success', 'Idée de contenu créée avec succès.');
    }

    /**
     * Affiche les détails d'une idée de contenu
     */
    public function show(ContentIdea $contentIdea)
    {
        return $this->viewForRole('content-ideas.show', compact('contentIdea'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(ContentIdea $contentIdea)
    {
        return view('content-ideas.edit', compact('contentIdea'));
    }

    /**
     * Met à jour une idée de contenu
     */
    public function update(Request $request, ContentIdea $contentIdea)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:vidéo,image,texte'],
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être vidéo, image ou texte.',
        ]);

        $contentIdea->update($validated);

        return redirect()->route('content-ideas.index')
            ->with('success', 'Idée de contenu modifiée avec succès.');
    }

    /**
     * Supprime une idée de contenu
     */
    public function destroy(ContentIdea $contentIdea)
    {
        $contentIdea->delete();

        return redirect()->route('content-ideas.index')
            ->with('success', 'Idée de contenu supprimée avec succès.');
    }
}
