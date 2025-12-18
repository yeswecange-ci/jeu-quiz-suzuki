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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Durée du Concours
                    </label>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" onclick="setDuration(7)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            1 Semaine
                        </button>
                        <button type="button" onclick="setDuration(14)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            2 Semaines
                        </button>
                        <button type="button" onclick="setDuration(21)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            3 Semaines
                        </button>
                        <button type="button" onclick="setDuration(30)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            1 Mois
                        </button>
                        <button type="button" onclick="setDuration(60)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            2 Mois
                        </button>
                        <button type="button" onclick="setDuration(90)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            3 Mois
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Date de Début
                            </label>
                            <input type="datetime-local"
                                   name="start_date"
                                   id="start_date"
                                   value="{{ old('start_date', $contest->start_date?->format('Y-m-d\TH:i')) }}"
                                   onchange="calculateWeeks()"
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
                                   onchange="calculateWeeks()"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="weeks-info" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800" style="display: none;">
                        <strong>ℹ️ Information:</strong> <span id="weeks-text"></span>
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

@push('scripts')
<script>
function setDuration(days) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // Ajuster pour timezone local

    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    // Définir la date de début à maintenant
    startDate.value = now.toISOString().slice(0, 16);

    // Calculer la date de fin
    const end = new Date(now.getTime() + days * 24 * 60 * 60 * 1000);
    endDate.value = end.toISOString().slice(0, 16);

    calculateWeeks();
}

function calculateWeeks() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const weeksInfo = document.getElementById('weeks-info');
    const weeksText = document.getElementById('weeks-text');

    if (!startDate || !endDate) {
        weeksInfo.style.display = 'none';
        return;
    }

    const start = new Date(startDate);
    const end = new Date(endDate);

    if (end <= start) {
        weeksInfo.style.display = 'none';
        return;
    }

    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    // Calculer le nombre de semaines calendaires (Lundi-Dimanche)
    const startOfWeek = new Date(start);
    startOfWeek.setDate(start.getDate() - start.getDay() + (start.getDay() === 0 ? -6 : 1));

    const endOfWeek = new Date(end);
    endOfWeek.setDate(end.getDate() - end.getDay() + (end.getDay() === 0 ? 0 : 7));

    const weeksDiff = Math.ceil((endOfWeek - startOfWeek) / (1000 * 60 * 60 * 24 * 7));

    weeksText.textContent = `Le concours durera ${diffDays} jours sur ${weeksDiff} semaine(s) calendaire(s). Il y aura donc ${weeksDiff} sélection(s) de gagnants hebdomadaires.`;
    weeksInfo.style.display = 'block';
}

// Calculer au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    calculateWeeks();
});
</script>
@endpush
@endsection
