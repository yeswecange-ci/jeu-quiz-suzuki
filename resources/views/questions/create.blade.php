@extends('layouts.app')

@section('title', 'Nouvelle Question')
@section('page-title', 'Ajouter une Question')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('contests.questions.store', $contest) }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Informations sur le concours -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Concours :</strong> {{ $contest->title }}
                    </p>
                </div>

                <!-- Ordre -->
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                        Ordre de la Question *
                    </label>
                    <input type="number"
                           name="order"
                           id="order"
                           value="{{ old('order', $nextOrder) }}"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    <p class="mt-1 text-xs text-gray-500">L'ordre suggéré est {{ $nextOrder }}</p>
                    @error('order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Texte de la question -->
                <div>
                    <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Texte de la Question *
                    </label>
                    <textarea name="question_text"
                              id="question_text"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Ex: ⚽ La CAN existe depuis combien de temps ?"
                              required>{{ old('question_text') }}</textarea>
                    @error('question_text')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Options de réponse -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Options de Réponse</h3>

                    <div>
                        <label for="option_1" class="block text-sm font-medium text-gray-700 mb-2">
                            Option 1 *
                        </label>
                        <input type="text"
                               name="option_1"
                               id="option_1"
                               value="{{ old('option_1') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Plus de 10 ans"
                               required>
                        @error('option_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="option_2" class="block text-sm font-medium text-gray-700 mb-2">
                            Option 2 *
                        </label>
                        <input type="text"
                               name="option_2"
                               id="option_2"
                               value="{{ old('option_2') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Plus de 20 ans"
                               required>
                        @error('option_2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="option_3" class="block text-sm font-medium text-gray-700 mb-2">
                            Option 3 *
                        </label>
                        <input type="text"
                               name="option_3"
                               id="option_3"
                               value="{{ old('option_3') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Plus de 60 ans"
                               required>
                        @error('option_3')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Configuration -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="correct_answer" class="block text-sm font-medium text-gray-700 mb-2">
                            Réponse Correcte *
                        </label>
                        <select name="correct_answer"
                                id="correct_answer"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <option value="">Sélectionner...</option>
                            <option value="1" {{ old('correct_answer') == 1 ? 'selected' : '' }}>Option 1</option>
                            <option value="2" {{ old('correct_answer') == 2 ? 'selected' : '' }}>Option 2</option>
                            <option value="3" {{ old('correct_answer') == 3 ? 'selected' : '' }}>Option 3</option>
                        </select>
                        @error('correct_answer')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="points" class="block text-sm font-medium text-gray-700 mb-2">
                            Points *
                        </label>
                        <input type="number"
                               name="points"
                               id="points"
                               value="{{ old('points', 1) }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                        @error('points')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type *
                        </label>
                        <select name="type"
                                id="type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                            <option value="marketing" {{ old('type') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Période d'activité -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Période d'Activité (optionnel)</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Définissez une période pour que cette question soit active uniquement pendant certaines dates (ex: semaine 1, semaine 2, etc.).
                        Si laissé vide, la question sera active en permanence.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Date de Début
                            </label>
                            <input type="datetime-local"
                                   name="start_date"
                                   id="start_date"
                                   value="{{ old('start_date') }}"
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
                                   value="{{ old('end_date') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Statut -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Question active</span>
                    </label>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('contests.questions.index', $contest) }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Créer la Question
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
