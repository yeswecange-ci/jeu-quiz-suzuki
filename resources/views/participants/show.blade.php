@extends('layouts.app')

@section('title', 'Détails Participant')
@section('page-title')
    {{ $participant->profile_name ?: ($participant->name ?: 'Participant #' . $participant->id) }}
@endsection

@section('header-actions')
    <div class="flex gap-2">
        <a href="{{ route('participants.edit', $participant) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Modifier
        </a>
        <a href="{{ route('participants.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            Retour à la liste
        </a>
    </div>
@endsection

@section('content')
<!-- Informations du participant -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Informations du participant</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Numéro WhatsApp</p>
            <p class="text-lg font-medium">{{ $participant->whatsapp_number }}</p>
        </div>
        @if($participant->profile_name)
        <div>
            <p class="text-sm text-gray-500">Profile Name</p>
            <p class="text-lg font-medium">{{ $participant->profile_name }}</p>
        </div>
        @endif
        @if($participant->name)
        <div>
            <p class="text-sm text-gray-500">Nom</p>
            <p class="text-lg font-medium">{{ $participant->name }}</p>
        </div>
        @endif
        <div>
            <p class="text-sm text-gray-500">Date d'inscription</p>
            <p class="text-lg font-medium">{{ $participant->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Concours</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total_contests'] }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Réponses</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['total_responses'] }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Score Total</p>
                <p class="text-3xl font-bold text-purple-600">{{ $stats['total_score'] }}</p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Victoires</p>
                <p class="text-3xl font-bold text-yellow-600">{{ $stats['total_wins'] }}</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full">
                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Réponses par concours -->
@foreach($responsesByContest as $contestData)
<div class="bg-white rounded-lg shadow mb-6">
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold">
                    <a href="{{ route('contests.show', $contestData['contest']) }}" class="text-blue-600 hover:text-blue-800">
                        {{ $contestData['contest']->title }}
                    </a>
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Score: <span class="font-medium text-gray-900">{{ $contestData['total_score'] }} points</span> •
                    Réponses correctes: <span class="font-medium text-green-600">{{ $contestData['correct_answers'] }}/{{ $contestData['total_questions'] }}</span> •
                    Complétion: <span class="font-medium text-blue-600">{{ round($contestData['completion_rate']) }}%</span>
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Question
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Réponse donnée
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Réponse correcte
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Résultat
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Points
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($contestData['responses'] as $response)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">Q{{ $response->question->order }}:</span>
                                <span class="text-gray-600">{{ Str::limit($response->question->question_text, 60) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($response->is_correct) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                Option {{ $response->answer }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                Option {{ $response->question->correct_answer }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($response->is_correct)
                                <span class="flex items-center text-green-600 text-sm font-medium">
                                    <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Correct
                                </span>
                            @else
                                <span class="flex items-center text-red-600 text-sm font-medium">
                                    <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Incorrect
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <span class="@if($response->points_earned > 0) text-green-600 @else text-gray-400 @endif">
                                +{{ $response->points_earned }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $response->answered_at ? $response->answered_at->format('d/m/Y H:i') : $response->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

@if($responsesByContest->isEmpty())
    <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-2">Ce participant n'a pas encore répondu à de questions</p>
    </div>
@endif
@endsection
