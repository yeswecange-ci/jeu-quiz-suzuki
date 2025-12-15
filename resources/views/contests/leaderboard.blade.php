@extends('layouts.app')

@section('title', 'Semaine ' . $week['number'] . ' - ' . $contest->title)
@section('page-title', $contest->title . ' - Semaine ' . $week['number'])

@section('header-actions')
    <a href="{{ route('contests.show', $contest) }}"
       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
        ← Retour au Concours
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Info Semaine -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $week['label'] }}</h2>
                <p class="text-gray-500 mt-1">
                    Du {{ $week['start_date']->format('d/m/Y') }} au {{ $week['end_date']->format('d/m/Y') }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Gagnants Sélectionnés</p>
                <p class="text-3xl font-bold text-yellow-600">{{ $winners->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Stats de la semaine -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Participants</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $leaderboard->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Score Moyen</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">
                {{ $leaderboard->avg('total_score') ? number_format($leaderboard->avg('total_score'), 1) : 0 }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Meilleur Score</p>
            <p class="text-3xl font-bold text-green-600 mt-2">
                {{ $leaderboard->max('total_score') ?? 0 }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Notifiés</p>
            <p class="text-3xl font-bold text-purple-600 mt-2">
                {{ $winners->where('notified', true)->count() }}/{{ $winners->count() }}
            </p>
        </div>
    </div>

    <!-- Gagnants de la semaine -->
    @if($winners->isNotEmpty())
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">🏆 Gagnants de la Semaine {{ $week['number'] }}</h3>
                <form action="{{ route('contests.notify-week-winners', $contest) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="week_number" value="{{ $week['number'] }}">
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                        📲 Notifier Tous les Gagnants
                    </button>
                </form>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($winners as $winner)
                        <div class="border-2 rounded-lg p-4
                            @if($winner->rank === 1) border-yellow-500 bg-yellow-50
                            @elseif($winner->rank === 2) border-gray-400 bg-gray-50
                            @elseif($winner->rank === 3) border-orange-500 bg-orange-50
                            @else border-gray-200 bg-white
                            @endif">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white
                                        @if($winner->rank === 1) bg-yellow-500
                                        @elseif($winner->rank === 2) bg-gray-400
                                        @elseif($winner->rank === 3) bg-orange-600
                                        @else bg-blue-500
                                        @endif">
                                        #{{ $winner->rank }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900">
                                            {{ $winner->participant->name ?? 'Participant ' . $winner->participant->id }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $winner->participant->whatsapp_number }}</p>
                                    </div>
                                </div>
                                @if($winner->notified)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                        ✓ Notifié
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                        En attente
                                    </span>
                                @endif
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-2xl font-bold text-gray-900">{{ $winner->total_score }} points</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Classement complet de la semaine -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Classement Complet - Semaine {{ $week['number'] }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leaderboard as $index => $entry)
                        @php
                            $isWinner = $winners->firstWhere('participant_id', $entry->participant_id);
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isWinner ? 'bg-yellow-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="w-8 h-8 rounded-full
                                    @if($index === 0) bg-yellow-500
                                    @elseif($index === 1) bg-gray-400
                                    @elseif($index === 2) bg-orange-600
                                    @elseif($isWinner) bg-green-500
                                    @else bg-blue-500
                                    @endif
                                    flex items-center justify-center text-white font-semibold text-sm">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $entry->participant->name ?? $entry->participant->whatsapp_number }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1
                                    @if($isWinner) bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800
                                    @endif
                                    text-sm font-semibold rounded-full">
                                    {{ $entry->total_score }} pts
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $entry->questions_answered }}
                            </td>
                            <td class="px-6 py-4">
                                @if($isWinner)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                        🏆 Gagnant
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Aucune participation pour cette semaine
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
