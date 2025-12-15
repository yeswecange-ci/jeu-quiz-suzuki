@extends('layouts.app')

@section('title', 'Modifier le Concours')
@section('page-title', 'Modifier : ' . $contest->title)

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('contests.update', $contest) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Titre -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre du Concours *
                    </label>
                    <input type="text"
                           name="title"
                           id="title"
                           value="{{ old('title', $contest->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description"
                              id="description"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $contest->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Numéro WhatsApp -->
                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro WhatsApp
                    </label>
                    <input type="text"
                           name="whatsapp_number"
                           id="whatsapp_number"
                           value="{{ old('whatsapp_number', $contest->whatsapp_number) }}"
                           placeholder="+225XXXXXXXXXX"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('whatsapp_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Configuration des gagnants -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="max_winners" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre Maximum de Gagnants *
                        </label>
                        <input type="number"
                               name="max_winners"
                               id="max_winners"
                               value="{{ old('max_winners', $contest->max_winners) }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                        @error('max_winners')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="min_score_to_win" class="block text-sm font-medium text-gray-700 mb-2">
                            Score Minimum pour Gagner *
                        </label>
                        <input type="number"
                               name="min_score_to_win"
                               id="min_score_to_win"
                               value="{{ old('min_score_to_win', $contest->min_score_to_win) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                        @error('min_score_to_win')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Date de Début
                        </label>
                        <input type="datetime-local"
                               name="start_date"
                               id="start_date"
                               value="{{ old('start_date', $contest->start_date?->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Date de Fin
                        </label>
                        <input type="datetime-local"
                               name="end_date"
                               id="end_date"
                               value="{{ old('end_date', $contest->end_date?->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Statut *
                    </label>
                    <select name="status"
                            id="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="draft" {{ old('status', $contest->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="active" {{ old('status', $contest->status) == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="completed" {{ old('status', $contest->status) == 'completed' ? 'selected' : '' }}>Terminé</option>
                        <option value="archived" {{ old('status', $contest->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('contests.show', $contest) }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Enregistrer les Modifications
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
