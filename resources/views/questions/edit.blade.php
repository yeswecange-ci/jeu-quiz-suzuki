@extends('layouts.app')

@section('title', 'Modifier la Question')
@section('page-title', 'Modifier la Question #' . $question->order)

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('contests.questions.update', [$contest, $question]) }}" method="POST">
            @csrf
            @method('PUT')

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
                           value="{{ old('order', $question->order) }}"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
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
                              required>{{ old('question_text', $question->question_text) }}</textarea>
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
                               value="{{ old('option_1', $question->options[0] ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                               value="{{ old('option_2', $question->options[1] ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                               value="{{ old('option_3', $question->options[2] ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                            <option value="1" {{ old('correct_answer', $question->correct_answer) == 1 ? 'selected' : '' }}>Option 1</option>
                            <option value="2" {{ old('correct_answer', $question->correct_answer) == 2 ? 'selected' : '' }}>Option 2</option>
                            <option value="3" {{ old('correct_answer', $question->correct_answer) == 3 ? 'selected' : '' }}>Option 3</option>
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
                               value="{{ old('points', $question->points) }}"
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
                            <option value="quiz" {{ old('type', $question->type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                            <option value="marketing" {{ old('type', $question->type) == 'marketing' ? 'selected' : '' }}>Marketing</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Statut -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $question->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Question active</span>
                    </label>
                </div>

                <!-- Statistiques (si réponses existent) -->
                @if($question->responses->count() > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            ⚠️ <strong>Attention :</strong> Cette question a déjà {{ $question->responses->count() }} réponse(s).
                            Modifier la réponse correcte recalculera les scores.
                        </p>
                    </div>
                @endif

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('contests.questions.index', $contest) }}"
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
