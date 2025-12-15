@extends('layouts.app')

@section('title', $contest->title)
@section('page-title', $contest->title)

@section('header-actions')
    <div class="flex space-x-3">
        <a href="{{ route('contests.questions.index', $contest) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            Questions
        </a>
        <a href="{{ route('contests.edit', $contest) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Infos du concours -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Description</h3>
                <p class="mt-2 text-gray-900">{{ $contest->description ?? 'Aucune description' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Période</h3>
                <p class="mt-2 text-gray-900">
                    @if($contest->start_date)
                        Du {{ $contest->start_date->format('d/m/Y') }}
                        @if($contest->end_date)
                            au {{ $contest->end_date->format('d/m/Y') }}
                        @endif
                        <br>
                        <span class="text-sm text-blue-600">{{ $weeks->count() }} semaine(s) au total</span>
                    @else
                        Non définie
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Gagnants</h3>
                <p class="mt-2 text-gray-900">{{ $contest->max_winners }} gagnants par semaine</p>
                <p class="text-sm text-gray-500">Score minimum: {{ $contest->min_score_to_win }} points</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Statut</h3>
                <p class="mt-2">
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        @if($contest->status === 'active') bg-green-100 text-green-800
                        @elseif($contest->status === 'completed') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($contest->status) }}
                    </span>
                    @if($currentWeek)
                        <span class="ml-2 text-sm text-blue-600">
                            📅 Semaine {{ $currentWeek['number'] }} en cours
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Participants</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_participants'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Réponses</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_responses'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Taux de Complétion</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['completion_rate'] }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Score Moyen</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['average_score'], 1) }}</p>
        </div>
    </div>

    <!-- Gestion des Gagnants par Semaine -->
    @if($weeks->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Gagnants par Semaine</h3>
                <form action="{{ route('contests.select-all-week-winners', $contest) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Sélectionner les gagnants pour toutes les semaines passées ?')"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm">
                        🏆 Sélectionner Toutes les Semaines Passées
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                @foreach($weeks as $week)
                    @php
                        $weekWinners = $winnersByWeek->get($week['number'], collect());
                        $isPast = $week['end_date']->lt(now());
                        $isCurrent = $currentWeek && $currentWeek['number'] === $week['number'];
                        $isFuture = $week['start_date']->gt(now());
                    @endphp

                    <div class="border rounded-lg p-4
                        @if($isCurrent) border-blue-500 bg-blue-50
                        @elseif($isPast) border-gray-300 bg-white
                        @else border-gray-200 bg-gray-50
                        @endif">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-gray-900">
                                    {{ $week['label'] }}
                                    @if($isCurrent)
                                        <span class="ml-2 px-2 py-1 bg-blue-500 text-white text-xs rounded-full">En cours</span>
                                    @elseif($isPast)
                                        <span class="ml-2 px-2 py-1 bg-gray-500 text-white text-xs rounded-full">Terminée</span>
                                    @else
                                        <span class="ml-2 px-2 py-1 bg-gray-300 text-gray-700 text-xs rounded-full">À venir</span>
                                    @endif
                                </h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $weekWinners->count() }} gagnant(s) sélectionné(s)
                                    @if($weekWinners->where('notified', false)->count() > 0)
                                        | {{ $weekWinners->where('notified', false)->count() }} non notifié(s)
                                    @endif
                                </p>
                            </div>

                            <div class="flex space-x-2">
                                @if($isPast || $isCurrent)
                                    <form action="{{ route('contests.select-week-winners', $contest) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="week_number" value="{{ $week['number'] }}">
                                        <button type="submit"
                                                onclick="return confirm('Sélectionner les gagnants pour la semaine {{ $week['number'] }} ?')"
                                                class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 transition">
                                            🏆 Sélectionner
                                        </button>
                                    </form>
                                @endif

                                @if($weekWinners->isNotEmpty())
                                    <form action="{{ route('contests.notify-week-winners', $contest) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="week_number" value="{{ $week['number'] }}">
                                        <button type="submit"
                                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                                            📲 Notifier
                                        </button>
                                    </form>

                                    <a href="{{ route('contests.week-leaderboard', [$contest, $week['number']]) }}"
                                       class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                        👁️ Voir
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Afficher les gagnants s'il y en a -->
                        @if($weekWinners->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($weekWinners->take(6) as $winner)
                                        <div class="flex items-center space-x-2 text-sm">
                                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-yellow-500 text-white font-bold text-xs">
                                                {{ $winner->rank }}
                                            </span>
                                            <span class="text-gray-900">
                                                {{ $winner->participant->name ?? Str::limit($winner->participant->whatsapp_number, 15) }}
                                            </span>
                                            <span class="text-gray-500">({{ $winner->total_score }} pts)</span>
                                            @if($winner->notified)
                                                <span class="text-green-600">✓</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @if($weekWinners->count() > 6)
                                    <p class="text-sm text-gray-500 mt-2">
                                        et {{ $weekWinners->count() - 6 }} autre(s)...
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <p class="text-yellow-800">
                ⚠️ Aucune semaine définie. Veuillez configurer les dates de début et de fin du concours.
            </p>
        </div>
    @endif

    <!-- Classement Général (toutes semaines confondues) -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Classement Général</h3>
            <p class="text-sm text-gray-500">Toutes semaines confondues</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leaderboard as $index => $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="w-8 h-8 rounded-full
                                    @if($index === 0) bg-yellow-500
                                    @elseif($index === 1) bg-gray-400
                                    @elseif($index === 2) bg-orange-600
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
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                                    {{ $entry->total_score }} pts
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $entry->questions_answered }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                Aucune participation pour le moment
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
