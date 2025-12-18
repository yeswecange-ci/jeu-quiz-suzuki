@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Concours</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_contests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Concours Actifs</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_contests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Participants</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_participants'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Réponses</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_responses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Gagnants</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_winners'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Contests and Top Participants -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Contests -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Concours Récents</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentContests as $contest)
                        <a href="{{ route('contests.show', $contest) }}" class="block p-4 hover:bg-gray-50 rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $contest->title }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $contest->questions_count }} questions • {{ $contest->unique_participants_count ?? $contest->participants_count ?? 0 }} participants
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-xs font-medium rounded-full
                                    @if($contest->status === 'active') bg-green-100 text-green-800
                                    @elseif($contest->status === 'completed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($contest->status) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500 text-center py-8">Aucun concours pour le moment</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Top Participants -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Participants</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topParticipants as $index => $entry)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900">
                                        {{ $entry->participant->name ?? $entry->participant->whatsapp_number }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $entry->total_answers }} réponses</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                                {{ $entry->total_points }} pts
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-8">Aucun participant pour le moment</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
