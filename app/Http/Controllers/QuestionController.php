<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Liste des questions d'un concours
     */
    public function index(Contest $contest)
    {
        $questions = $contest->questions()->with('responses')->get();

        return view('questions.index', compact('contest', 'questions'));
    }

    /**
     * Formulaire de création
     */
    public function create(Contest $contest)
    {
        $nextOrder = $contest->questions()->max('order') + 1;

        return view('questions.create', compact('contest', 'nextOrder'));
    }

    /**
     * Enregistrer une nouvelle question
     */
    public function store(Request $request, Contest $contest)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_1' => 'required|string|max:255',
            'option_2' => 'required|string|max:255',
            'option_3' => 'required|string|max:255',
            'correct_answer' => 'required|integer|min:1|max:3',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:quiz,marketing',
            'order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $options = [
            $validated['option_1'],
            $validated['option_2'],
            $validated['option_3'],
        ];

        $question = $contest->questions()->create([
            'question_text' => $validated['question_text'],
            'options' => $options,
            'correct_answer' => $validated['correct_answer'],
            'points' => $validated['points'],
            'type' => $validated['type'],
            'order' => $validated['order'],
            'is_active' => $validated['is_active'] ?? true,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return redirect()->route('contests.questions.index', $contest)
            ->with('success', 'Question créée avec succès !');
    }

    /**
     * Afficher une question avec ses stats
     */
    public function show(Contest $contest, Question $question)
    {
        $stats = $question->getStats();
        $distribution = $question->getResponseDistribution();

        return view('questions.show', compact('contest', 'question', 'stats', 'distribution'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Contest $contest, Question $question)
    {
        return view('questions.edit', compact('contest', 'question'));
    }

    /**
     * Mettre à jour une question
     */
    public function update(Request $request, Contest $contest, Question $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_1' => 'required|string|max:255',
            'option_2' => 'required|string|max:255',
            'option_3' => 'required|string|max:255',
            'correct_answer' => 'required|integer|min:1|max:3',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:quiz,marketing',
            'order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $options = [
            $validated['option_1'],
            $validated['option_2'],
            $validated['option_3'],
        ];

        $question->update([
            'question_text' => $validated['question_text'],
            'options' => $options,
            'correct_answer' => $validated['correct_answer'],
            'points' => $validated['points'],
            'type' => $validated['type'],
            'order' => $validated['order'],
            'is_active' => $validated['is_active'] ?? true,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return redirect()->route('contests.questions.index', $contest)
            ->with('success', 'Question mise à jour avec succès !');
    }

    /**
     * Supprimer une question
     */
    public function destroy(Contest $contest, Question $question)
    {
        $question->delete();

        return redirect()->route('contests.questions.index', $contest)
            ->with('success', 'Question supprimée avec succès !');
    }

    /**
     * Réorganiser les questions
     */
    public function reorder(Request $request, Contest $contest)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['questions'] as $item) {
            Question::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'Ordre mis à jour']);
    }
}
