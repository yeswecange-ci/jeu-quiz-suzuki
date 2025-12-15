<?php

namespace Database\Seeders;

use App\Models\Contest;
use App\Models\Question;
use App\Models\Participant;
use App\Models\Response;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data
     */
    public function run(): void
    {
        // Créer un concours de démo
        $contest = Contest::create([
            'title' => 'Scan & Gagne – Spécial CAN by Suzuki',
            'description' => 'Jeu-concours WhatsApp pour la CAN 2024',
            'whatsapp_number' => '+2250700000000',
            'max_winners' => 10,
            'min_score_to_win' => 2,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        // Créer les questions
        $questions = [
            [
                'order' => 1,
                'question_text' => '⚽ La CAN existe depuis combien de temps ?',
                'options' => ['Plus de 10 ans', 'Plus de 20 ans', 'Plus de 60 ans'],
                'correct_answer' => 3,
                'points' => 1,
                'type' => 'quiz',
            ],
            [
                'order' => 2,
                'question_text' => '🐘 Combien de fois la Côte d\'Ivoire a gagné la CAN ?',
                'options' => ['1 fois', '2 fois', '3 fois'],
                'correct_answer' => 2,
                'points' => 1,
                'type' => 'quiz',
            ],
            [
                'order' => 3,
                'question_text' => '🚗 Prévois-tu d\'acheter une voiture prochainement ?',
                'options' => ['Oui dans les 3 prochain mois', 'Au cours de l\'année', 'Plus tard'],
                'correct_answer' => 1, // Pas de bonne réponse pour marketing
                'points' => 1,
                'type' => 'marketing',
            ],
            [
                'order' => 4,
                'question_text' => '🛣 Tu utilises une voiture surtout pour…',
                'options' => ['La ville', 'Le travail / VTC', 'La famille'],
                'correct_answer' => 1, // Pas de bonne réponse pour marketing
                'points' => 1,
                'type' => 'marketing',
            ],
        ];

        foreach ($questions as $questionData) {
            $contest->questions()->create($questionData);
        }

        // Créer des participants de test
        $participants = [
            ['whatsapp_number' => '+2250701234567', 'name' => 'Kouassi Jean'],
            ['whatsapp_number' => '+2250707654321', 'name' => 'Aya Marie'],
            ['whatsapp_number' => '+2250709876543', 'name' => 'Konan Serge'],
            ['whatsapp_number' => '+2250705555555', 'name' => 'Adjoua Grace'],
            ['whatsapp_number' => '+2250706666666', 'name' => 'Yao Patrick'],
        ];

        foreach ($participants as $participantData) {
            $participant = Participant::create($participantData);

            // Générer des réponses aléatoires pour chaque participant
            foreach ($contest->questions as $question) {
                $answer = rand(1, 3);
                $isCorrect = $question->isCorrect($answer);
                $pointsEarned = $isCorrect ? $question->points : 0;

                Response::create([
                    'contest_id' => $contest->id,
                    'participant_id' => $participant->id,
                    'question_id' => $question->id,
                    'answer' => $answer,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                    'answered_at' => now()->subMinutes(rand(1, 60)),
                ]);
            }
        }

        $this->command->info('✅ Données de démo créées avec succès !');
        $this->command->info('📊 Concours: ' . $contest->title);
        $this->command->info('❓ Questions: ' . $contest->questions()->count());
        $this->command->info('👥 Participants: ' . count($participants));
    }
}
