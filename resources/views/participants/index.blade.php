@extends('layouts.app')

@section('title', 'Participants')
@section('page-title', 'Gestion des Participants')

@section('content')
<div class="mb-6">
    <form method="GET" action="{{ route('participants.index') }}" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Numéro, nom ou profile name..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filtrer par concours</label>
                <select name="contest_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous les concours</option>
                    @foreach($contests as $contest)
                        <option value="{{ $contest->id }}" {{ request('contest_id') == $contest->id ? 'selected' : '' }}>
                            {{ $contest->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Filtrer
                </button>
                @if(request('search') || request('contest_id'))
                    <a href="{{ route('participants.index') }}" class="ml-2 px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Participant
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        WhatsApp
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Réponses
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date d'inscription
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($participants as $participant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <a href="{{ route('participants.show', $participant) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    {{ $participant->profile_name ?: ($participant->name ?: 'Participant #' . $participant->id) }}
                                </a>
                                @if($participant->profile_name && $participant->name)
                                    <p class="text-xs text-gray-500 mt-1">{{ $participant->name }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $participant->whatsapp_number }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                {{ $participant->responses_count }} réponses
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $participant->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                            <a href="{{ route('participants.show', $participant) }}" class="text-blue-600 hover:text-blue-900">
                                Voir détails
                            </a>
                            <a href="{{ route('participants.edit', $participant) }}" class="text-indigo-600 hover:text-indigo-900">
                                Modifier
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="mt-2">Aucun participant trouvé</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($participants->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $participants->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
