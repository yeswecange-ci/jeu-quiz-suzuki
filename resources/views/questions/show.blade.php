@extends('layouts.app')

@section('title', 'Détails Question #' . $question->order)
@section('page-title', 'Question #' . $question->order)

@section('header-actions')
    <div class="flex space-x-3">
        <a href="{{ route('contests.questions.index', $contest) }}"
           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
            ← Retour aux Questions
        </a>
        <a href="{{ route('contests.questions.edit', [$contest, $question]) }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Question Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="space-y-4">
            <div>
                <span class="text-sm text-gray-500">Question</span>
                <p class="text-xl font-medium text-gray-900 mt-1">{{ $question->question_text }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($question->options as $index => $option)
                    <div class="p-4 rounded-lg border-2
                        {{ ($index + 1) === $question->correct_answer ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full text-sm font-semibold
                                {{ ($index + 1) === $question->correct_answer ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                                {{ $index + 1 }}
                            </span>
                            <p class="ml-3 text-sm text-gray-900">{{ $option }}</p>
                        </div>
                        @if(($index + 1) === $question->correct_answer)
                            <p class="mt-2 text-xs text-green-600 font-medium">✓ Réponse Correcte</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex items-center space-x-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $question->type === 'quiz' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($question->type) }}
                    </span>
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    {{ $question->points }} point(s)
                </div>
                <div class="flex items-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $question->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $question->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Réponses</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_responses'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Réponses Correctes</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['correct_responses'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Réponses Incorrectes</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['incorrect_responses'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Taux de Réussite</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['success_rate'] }}%</p>
        </div>
    </div>

    <!-- Distribution des réponses -->
    @if($stats['total_responses'] > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribution des Réponses</h3>

            <div class="space-y-4">
                @foreach($distribution as $optionNum => $count)
                    @php
                        $percentage = $stats['total_responses'] > 0 ? round(($count / $stats['total_responses']) * 100, 1) : 0;
                        $isCorrect = $optionNum === $question->correct_answer;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                Option {{ $optionNum }}: {{ $question->options[$optionNum - 1] }}
                                @if($isCorrect)
                                    <span class="ml-2 text-green-600">✓</span>
                                @endif
                            </span>
                            <span class="text-sm text-gray-500">{{ $count }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all
                                {{ $isCorrect ? 'bg-green-500' : 'bg-blue-500' }}"
                                style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="mt-4 text-gray-500">Aucune réponse pour cette question pour le moment</p>
        </div>
    @endif
</div>
@endsection
