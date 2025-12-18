@extends('layouts.app')

@section('title', 'Modifier le Participant')
@section('page-title', 'Modifier : ' . ($participant->profile_name ?: ($participant->name ?: 'Participant #' . $participant->id)))

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('participants.update', $participant) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Numéro WhatsApp -->
                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro WhatsApp *
                    </label>
                    <input type="text"
                           name="whatsapp_number"
                           id="whatsapp_number"
                           value="{{ old('whatsapp_number', $participant->whatsapp_number) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('whatsapp_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Name -->
                <div>
                    <label for="profile_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Profile Name (WhatsApp)
                    </label>
                    <input type="text"
                           name="profile_name"
                           id="profile_name"
                           value="{{ old('profile_name', $participant->profile_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nom du profil WhatsApp">
                    @error('profile_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Le nom tel qu'il apparaît sur le profil WhatsApp</p>
                </div>

                <!-- Nom -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom personnalisé
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $participant->name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nom personnalisé (optionnel)">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Vous pouvez attribuer un nom personnalisé au participant</p>
                </div>

                <!-- Conversation SID (Read-only) -->
                @if($participant->conversation_sid)
                <div>
                    <label for="conversation_sid" class="block text-sm font-medium text-gray-700 mb-2">
                        Conversation ID (Twilio)
                    </label>
                    <input type="text"
                           value="{{ $participant->conversation_sid }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                           readonly
                           disabled>
                    <p class="mt-1 text-xs text-gray-500">Identifiant de la conversation Twilio (non modifiable)</p>
                </div>
                @endif

                <!-- Date de création -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date d'inscription
                    </label>
                    <input type="text"
                           value="{{ $participant->created_at->format('d/m/Y à H:i') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                           readonly
                           disabled>
                </div>

                <!-- Statistiques -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-blue-600 font-medium">Réponses</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $participant->responses->count() }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-green-600 font-medium">Score Total</p>
                            <p class="text-2xl font-bold text-green-900">{{ $participant->getTotalScore() }}</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <p class="text-sm text-yellow-600 font-medium">Victoires</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ $participant->getWinsCount() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                <div>
                    <form action="{{ route('participants.destroy', $participant) }}" method="POST" class="inline"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce participant et toutes ses réponses ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Supprimer le participant
                        </button>
                    </form>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('participants.show', $participant) }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
