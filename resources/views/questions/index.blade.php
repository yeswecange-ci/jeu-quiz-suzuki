@extends('layouts.app')

@section('title', 'Questions - ' . $contest->title)
@section('page-title', 'Questions : ' . $contest->title)

@section('header-actions')
    <div class="flex space-x-3">
        <a href="{{ route('contests.show', $contest) }}"
           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
            ← Retour au Concours
        </a>
        <a href="{{ route('contests.questions.create', $contest) }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            + Nouvelle Question
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Résumé -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Total Questions</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $questions->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Questions Actives</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $questions->where('is_active', true)->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Questions Inactives</p>
                <p class="text-2xl font-bold text-gray-400 mt-1">{{ $questions->where('is_active', false)->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Liste des questions -->
    <div class="bg-white rounded-lg shadow">
        @if($questions->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4 text-gray-500">Aucune question pour ce concours</p>
                <a href="{{ route('contests.questions.create', $contest) }}"
                   class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                    Créer votre première question →
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Réponse Correcte</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Réponses</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($questions as $question)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold">
                                        {{ $question->order }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-xs">
                                        <p class="text-sm font-medium text-gray-900">{{ Str::limit($question->question_text, 60) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            @foreach($question->options as $index => $option)
                                                {{ $index + 1 }}. {{ Str::limit($option, 20) }}
                                                @if(!$loop->last) | @endif
                                            @endforeach
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $question->type === 'quiz' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($question->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    Option {{ $question->correct_answer }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                        {{ $question->points }} pts
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $question->responses->count() }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($question->is_active)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Actif</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">Inactif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('contests.questions.show', [$contest, $question]) }}"
                                       class="text-blue-600 hover:text-blue-900">Voir</a>
                                    <a href="{{ route('contests.questions.edit', [$contest, $question]) }}"
                                       class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                    <form action="{{ route('contests.questions.destroy', [$contest, $question]) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette question ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
