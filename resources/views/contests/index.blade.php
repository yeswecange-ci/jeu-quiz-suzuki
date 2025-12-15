@extends('layouts.app')

@section('title', 'Concours')
@section('page-title', 'Gestion des Concours')

@section('header-actions')
    <a href="{{ route('contests.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        + Nouveau Concours
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Titre
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Questions
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Participants
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Gagnants
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Statut
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Dates
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contests as $contest)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <a href="{{ route('contests.show', $contest) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    {{ $contest->title }}
                                </a>
                                @if($contest->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit($contest->description, 50) }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $contest->questions_count }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $contest->participants_count }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $contest->winners_count }}/{{ $contest->max_winners }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                @if($contest->status === 'active') bg-green-100 text-green-800
                                @elseif($contest->status === 'completed') bg-blue-100 text-blue-800
                                @elseif($contest->status === 'archived') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($contest->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($contest->start_date)
                                {{ $contest->start_date->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                            <a href="{{ route('contests.show', $contest) }}" class="text-blue-600 hover:text-blue-900">
                                Voir
                            </a>
                            <a href="{{ route('contests.edit', $contest) }}" class="text-indigo-600 hover:text-indigo-900">
                                Modifier
                            </a>
                            <a href="{{ route('contests.questions.index', $contest) }}" class="text-green-600 hover:text-green-900">
                                Questions
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2">Aucun concours pour le moment</p>
                            <a href="{{ route('contests.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                Créer votre premier concours →
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($contests->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $contests->links() }}
        </div>
    @endif
</div>
@endsection
