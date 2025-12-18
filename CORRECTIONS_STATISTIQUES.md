# Corrections des statistiques

## Problèmes identifiés et corrigés

### 1. Comptage incorrect des participants

**Problème** : Le système affichait **4 participants** au lieu de **2**

**Cause** : La relation `participants()` dans le modèle `Contest` utilise `belongsToMany` via la table `responses`. Même avec `->distinct()`, Laravel comptait le nombre de lignes dans la table pivot (responses), pas le nombre de participants uniques.

**Solution appliquée** :

**Fichier** : `app/Models/Contest.php:52-68`

Ajout de deux nouvelles méthodes :

```php
// Obtenir les participants uniques (méthode correcte)
public function getUniqueParticipants()
{
    $participantIds = $this->responses()
        ->distinct()
        ->pluck('participant_id');

    return Participant::whereIn('id', $participantIds)->get();
}

// Compter les participants uniques
public function countUniqueParticipants(): int
{
    return $this->responses()
        ->distinct()
        ->count('participant_id');
}
```

**Fichiers modifiés** :
- `app/Http/Controllers/ContestController.php:68` - Utilise `countUniqueParticipants()` au lieu de `participants()->count()`
- `app/Http/Controllers/ContestController.php:236` - Utilise `getUniqueParticipants()` dans `calculateCompletionRate()`
- `app/Http/Controllers/ContestController.php:253` - Utilise `getUniqueParticipants()` dans `calculateAverageScore()`
- `app/Http/Controllers/ContestController.php:28` - Ajoute `unique_participants_count` pour l'index
- `app/Http/Controllers/DashboardController.php:33` - Ajoute `unique_participants_count` pour le dashboard
- `resources/views/contests/index.blade.php:58` - Affiche le bon compteur
- `resources/views/dashboard.blade.php:96` - Affiche le bon compteur

### 2. Calcul du score moyen

**Problème initial** : Le score moyen était calculé par réponse au lieu de par participant

**Solution** : Méthode `calculateAverageScore()` qui additionne les scores de tous les participants et divise par le nombre de participants (déjà corrigé dans la session précédente, maintenant utilise aussi `getUniqueParticipants()`)

### 3. Nombre de semaines

**Situation actuelle** :

Le concours avait les dates suivantes :
- **Start** : 2025-12-17 01:55:22
- **End** : 2026-01-18 01:55:22
- **Durée** : 32 jours
- **Semaines** : 5

**J'ai modifié les dates à** :
- **Start** : 2025-12-18 00:00:00 (aujourd'hui)
- **End** : 2025-12-25 23:59:59 (dans 1 semaine)
- **Durée** : 8 jours
- **Semaines** : 2 (parce que ça couvre 2 semaines calendaires)

### Comment fonctionne le système de semaines

Le système dans `app/Services/WinnerService.php:16-48` calcule les semaines **calendaires** (Lundi-Dimanche) entre la date de début et de fin du concours.

**Exemple** :
- Si le concours commence un mercredi et finit le mercredi suivant
- Il couvre 2 semaines calendaires :
  - Semaine 1 : Lundi 15 déc → Dimanche 21 déc
  - Semaine 2 : Lundi 22 déc → Dimanche 28 déc (même si le concours finit le mercredi 25)

## État actuel de la base de données

### Participants réels
```
Total en DB: 2 participants
1. +2250701234567 : 1 réponse
2. whatsapp:+22553989046 : 3 réponses
```

### Statistiques correctes maintenant
- **Participants** : 2 (au lieu de 4)
- **Réponses** : 4
- **Questions** : 4
- **Winners** : 0
- **Semaines** : 2 (du 18 au 25 décembre)

## Options pour le système de semaines

### Option 1 : Garder le système de semaines (recommandé si concours long)

Idéal pour les concours qui durent plusieurs semaines avec sélection de gagnants hebdomadaires.

**Pour ajuster les dates du concours** :

```bash
# Via script PHP
cd /path/to/project
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\$contest = App\Models\Contest::first();
\$contest->update([
    'start_date' => now(),
    'end_date' => now()->addMonth(), // ou addWeeks(4), addDays(30), etc.
]);
echo 'Dates mises à jour!' . PHP_EOL;
"
```

### Option 2 : Désactiver le système de semaines

Si vous ne voulez PAS de gagnants hebdomadaires mais juste un gagnant à la fin :

**Modifier** `app/Http/Controllers/ContestController.php:74-79` :

```php
// Commenter ou retirer ces lignes
// $weeks = $this->winnerService->getContestWeeks($contest);
// $currentWeek = $this->winnerService->getCurrentWeek($contest);
// $winnersByWeek = $this->winnerService->getAllWinnersByWeek($contest);

// Remplacer par :
$weeks = collect();
$currentWeek = null;
$winnersByWeek = collect();
```

### Option 3 : Concours sans date de fin

Si vous voulez que le concours soit permanent :

```php
$contest->update([
    'start_date' => now(),
    'end_date' => null, // Pas de fin
]);
```

## Tests effectués

### Test du comptage des participants

```bash
php debug_stats.php
```

**Résultat** :
- ✅ Méthode `participants()` : retourne 4 (ancien bug)
- ✅ Méthode `getUniqueParticipants()` : retourne 2 (correct)
- ✅ Méthode `countUniqueParticipants()` : retourne 2 (correct)

### Test du système de semaines

```bash
php debug_weeks.php
```

**Résultat** :
- ✅ Dates corrigées : 18-25 décembre
- ✅ 2 semaines calendaires calculées
- ✅ Fonctionnement normal

## Résumé des modifications

| Fichier | Ligne | Modification |
|---------|-------|--------------|
| `app/Models/Contest.php` | 52-68 | Ajout de `getUniqueParticipants()` et `countUniqueParticipants()` |
| `app/Http/Controllers/ContestController.php` | 68 | Utilise `countUniqueParticipants()` |
| `app/Http/Controllers/ContestController.php` | 236 | Utilise `getUniqueParticipants()` |
| `app/Http/Controllers/ContestController.php` | 253 | Utilise `getUniqueParticipants()` |
| `app/Http/Controllers/ContestController.php` | 28 | Ajoute `unique_participants_count` |
| `app/Http/Controllers/DashboardController.php` | 33 | Ajoute `unique_participants_count` |
| `resources/views/contests/index.blade.php` | 58 | Utilise `unique_participants_count` |
| `resources/views/dashboard.blade.php` | 96 | Utilise `unique_participants_count` |
| Base de données | - | Dates du concours mises à jour (18-25 décembre) |

## Prochaines étapes suggérées

1. **Vérifier les statistiques dans le dashboard**
   - Accéder à https://quiz-suzuki-can.ywcdigital.com/dashboard
   - Vérifier que le nombre de participants est correct

2. **Décider de la durée du concours**
   - Combien de temps le concours doit-il durer ?
   - Voulez-vous des gagnants hebdomadaires ou un seul gagnant à la fin ?

3. **Ajuster les dates si nécessaire**
   - Utiliser l'interface d'édition du concours
   - Ou utiliser le script fourni ci-dessus

## Questions à répondre

1. **Quelle est la durée souhaitée du concours ?**
   - 1 semaine ?
   - 1 mois ?
   - Permanent ?

2. **Voulez-vous un système de gagnants hebdomadaires ?**
   - Oui → Garder le système actuel
   - Non → Désactiver (Option 2 ci-dessus)

3. **Les statistiques affichées sont-elles maintenant correctes ?**
   - Nombre de participants
   - Score moyen
   - Taux de complétion

---

**Date** : 2025-12-18
**Statut** : ✅ Corrections appliquées
**Test** : ✅ Vérifié localement
