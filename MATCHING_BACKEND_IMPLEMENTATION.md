# Backend Matching Géolocalisé - Implémentation

## 🎯 Objectif

Système de matching géolocalisé permettant de trouver des utilisateurs dans un rayon de 500m avec score de compatibilité.

---

## ✅ Ce qui a été implémenté

### 1. Migrations

#### `2026_02_23_175824_add_location_to_users_table.php`

Ajout de la géolocalisation à la table `users` :

```php
$table->decimal('latitude', 10, 8)->nullable();
$table->decimal('longitude', 11, 8)->nullable();
$table->timestamp('location_updated_at')->nullable();
$table->index(['latitude', 'longitude']); // Index pour optimisation
```

**Champs** :
- `latitude` : Latitude GPS (précision 8 décimales = ~1mm)
- `longitude` : Longitude GPS (précision 8 décimales = ~1mm)
- `location_updated_at` : Date de dernière mise à jour
- **Index** : Optimise les requêtes géographiques

---

#### `2026_02_23_175857_create_matches_table.php`

Table pour stocker les interactions de matching :

```php
$table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
$table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
$table->enum('user1_action', ['none', 'like', 'pass'])->default('none');
$table->enum('user2_action', ['none', 'like', 'pass'])->default('none');
$table->boolean('is_mutual')->default(false);
$table->decimal('distance', 8, 2)->nullable();
$table->integer('compatibility_score')->nullable();
$table->timestamp('matched_at')->nullable();
```

**Champs** :
- `user1_id`, `user2_id` : Les deux utilisateurs
- `user1_action`, `user2_action` : Actions (none/like/pass)
- `is_mutual` : `true` si match mutuel (les deux ont liké)
- `distance` : Distance entre les utilisateurs (mètres)
- `compatibility_score` : Score 0-100
- `matched_at` : Date du match mutuel

---

### 2. Modèles

#### `UserMatch.php`

Modèle Eloquent pour la table `matches` :

**Relations** :
```php
public function user1(): BelongsTo
public function user2(): BelongsTo
```

**Méthodes utiles** :
```php
getOtherUser($currentUserId)  // Obtenir l'autre utilisateur
isMutual()                     // Vérifier si match mutuel
getUserAction($userId)         // Obtenir l'action d'un user
```

**Scopes** :
```php
forUser($userId)               // Matchs d'un utilisateur
mutual()                       // Matchs mutuels uniquement
likesSent($userId)             // Likes envoyés
likesReceived($userId)         // Likes reçus
```

---

#### Mise à jour `User.php`

Ajout des champs de géolocalisation :

```php
protected $fillable = [
    // ...
    'latitude',
    'longitude',
    'location_updated_at',
];

protected $casts = [
    // ...
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8',
    'location_updated_at' => 'datetime',
];
```

---

### 3. MatchController

Contrôleur complet avec 5 endpoints :

#### **POST `/api/matching/location`**
Mettre à jour la position de l'utilisateur

**Body** :
```json
{
  "latitude": 48.8566,
  "longitude": 2.3522
}
```

**Réponse** :
```json
{
  "success": true,
  "message": "Position mise à jour avec succès",
  "data": {
    "latitude": "48.85660000",
    "longitude": "2.35220000",
    "location_updated_at": "2026-02-23T18:30:00Z"
  }
}
```

---

#### **GET `/api/matching/nearby`**
Obtenir les utilisateurs dans un rayon de 500m

**Réponse** :
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "name": "Jean Dupont",
      "avatar": "https://...",
      "role": "worker",
      "profession": "Développeur",
      "bio": "Passionné de tech",
      "city": "Paris",
      "distance": 245,
      "compatibility_score": 75,
      "my_action": "none",
      "their_action": "none",
      "is_mutual": false
    }
  ],
  "count": 3
}
```

**Algorithme** :
- Utilise la **formule Haversine** pour calculer la distance
- Filtre les utilisateurs dans un rayon de 500m
- Calcule le score de compatibilité
- Trie par distance croissante

---

#### **POST `/api/matching/like/{userId}`**
Liker un utilisateur

**Réponse** :
```json
{
  "success": true,
  "message": "C'est un match ! 🎉",
  "data": {
    "is_mutual": true,
    "matched_at": "2026-02-23T18:35:00Z",
    "compatibility_score": 75,
    "distance": 245
  }
}
```

**Logique** :
- Crée ou met à jour le match
- Si les deux ont liké → `is_mutual = true`
- Calcule distance et score de compatibilité

---

#### **POST `/api/matching/pass/{userId}`**
Passer un utilisateur (ne pas matcher)

**Réponse** :
```json
{
  "success": true,
  "message": "Utilisateur passé"
}
```

---

#### **GET `/api/matching/matches`**
Obtenir tous les matchs mutuels

**Réponse** :
```json
{
  "success": true,
  "data": [
    {
      "match_id": 12,
      "user": {
        "id": 5,
        "name": "Jean Dupont",
        "avatar": "https://...",
        "role": "worker",
        "profession": "Développeur",
        "bio": "Passionné de tech"
      },
      "distance": 245,
      "compatibility_score": 75,
      "matched_at": "2026-02-23T18:35:00Z"
    }
  ],
  "count": 5
}
```

---

### 4. Algorithme Haversine

Calcul de distance entre deux points GPS :

```php
private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000; // Rayon de la Terre en mètres

    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return $angle * $earthRadius; // Distance en mètres
}
```

**Précision** : ~1 mètre

**Utilisation** :
- Calcul de distance pour filtrage
- Bonus de compatibilité si < 100m

---

### 5. Score de compatibilité

Algorithme de scoring (0-100) :

```php
private function calculateCompatibilityScore(User $user1, User $user2)
{
    $score = 0;

    // Même profession : +30 points
    if ($user1->profession === $user2->profession) {
        $score += 30;
    }

    // Même ville : +20 points
    if ($user1->city === $user2->city) {
        $score += 20;
    }

    // Compétences communes : +25 points max
    $commonSkills = array_intersect($user1->skills, $user2->skills);
    $score += min(count($commonSkills) * 5, 25);

    // Disponibilité compatible : +15 points
    if ($user1->availability && $user2->availability) {
        $score += 15;
    }

    // Bonus proximité < 100m : +10 points
    $distance = $this->calculateDistance(...);
    if ($distance < 100) {
        $score += 10;
    }

    return min($score, 100);
}
```

**Critères** :
| Critère | Points | Description |
|---------|--------|-------------|
| Même profession | 30 | Compatibilité professionnelle |
| Même ville | 20 | Proximité géographique |
| Compétences communes | 25 | 5 points par compétence (max 5) |
| Disponibilité | 15 | Les deux disponibles |
| Proximité < 100m | 10 | Bonus très proche |

**Total max** : 100 points

---

### 6. Routes API

```php
Route::middleware('auth:sanctum')->prefix('matching')->group(function () {
    Route::post('/location', [MatchController::class, 'updateLocation']);
    Route::get('/nearby', [MatchController::class, 'getNearbyUsers']);
    Route::post('/like/{userId}', [MatchController::class, 'likeUser']);
    Route::post('/pass/{userId}', [MatchController::class, 'passUser']);
    Route::get('/matches', [MatchController::class, 'getMatches']);
});
```

**Toutes protégées** par `auth:sanctum`

---

## 🔄 Flux de matching

### Scénario 1 : Premier matching

```
1. User A ouvre l'app
2. App envoie sa position → POST /api/matching/location
3. App récupère les users à proximité → GET /api/matching/nearby
4. User A voit User B (245m, score 75%)
5. User A like User B → POST /api/matching/like/5
6. Réponse : "Like envoyé" (pas encore mutuel)
```

---

### Scénario 2 : Match mutuel

```
1. User B ouvre l'app
2. App récupère les users à proximité → GET /api/matching/nearby
3. User B voit User A avec "their_action": "like"
4. User B like User A → POST /api/matching/like/3
5. Réponse : "C'est un match ! 🎉"
6. is_mutual = true, matched_at = now()
7. Les deux peuvent maintenant chatter
```

---

### Scénario 3 : Consulter ses matchs

```
1. User A ouvre l'onglet "Matchs"
2. App récupère les matchs → GET /api/matching/matches
3. Affiche la liste des matchs mutuels
4. User A peut cliquer pour chatter
```

---

## 📊 Structure de la base de données

### Table `users` (ajouts)

| Colonne | Type | Description |
|---------|------|-------------|
| latitude | decimal(10,8) | Latitude GPS |
| longitude | decimal(11,8) | Longitude GPS |
| location_updated_at | timestamp | Date MAJ position |

**Index** : `(latitude, longitude)`

---

### Table `matches`

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | ID auto |
| user1_id | bigint | Premier utilisateur |
| user2_id | bigint | Deuxième utilisateur |
| user1_action | enum | none/like/pass |
| user2_action | enum | none/like/pass |
| is_mutual | boolean | Match mutuel ? |
| distance | decimal(8,2) | Distance (m) |
| compatibility_score | integer | Score 0-100 |
| matched_at | timestamp | Date du match |
| created_at | timestamp | Date création |
| updated_at | timestamp | Date MAJ |

**Index** :
- `user1_id`
- `user2_id`
- `is_mutual`
- `(user1_id, user2_id)` (unique)

---

## 🎯 Exemples d'utilisation

### Mettre à jour la position

```bash
curl -X POST http://localhost:8000/api/matching/location \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 48.8566,
    "longitude": 2.3522
  }'
```

---

### Obtenir les users à proximité

```bash
curl -X GET http://localhost:8000/api/matching/nearby \
  -H "Authorization: Bearer TOKEN"
```

---

### Liker un utilisateur

```bash
curl -X POST http://localhost:8000/api/matching/like/5 \
  -H "Authorization: Bearer TOKEN"
```

---

### Obtenir les matchs

```bash
curl -X GET http://localhost:8000/api/matching/matches \
  -H "Authorization: Bearer TOKEN"
```

---

## 🔒 Sécurité

### Validations

- **Latitude** : Entre -90 et 90
- **Longitude** : Entre -180 et 180
- **User ID** : Existe dans la DB
- **Self-like** : Interdit

### Authentification

Toutes les routes nécessitent un token Sanctum valide.

### Logs

Toutes les actions importantes sont loggées :
- Mise à jour de position
- Likes envoyés
- Matchs mutuels

---

## 📈 Optimisations

### Index géographiques

```php
$table->index(['latitude', 'longitude']);
```

Accélère les requêtes de proximité.

---

### Requête optimisée

La formule Haversine est exécutée directement en SQL :

```php
->selectRaw(
    '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
    [$user->latitude, $user->longitude, $user->latitude]
)
->having('distance', '<=', $radius)
```

**Avantage** : Filtrage côté DB, pas en PHP

---

### Cache potentiel

Pour améliorer les performances :
- Cacher les résultats de `nearby` pendant 30s
- Invalider le cache à chaque mise à jour de position

---

## 🚀 Prochaines étapes (Frontend)

1. **Géolocalisation Flutter** : Capturer la position
2. **Permissions** : Demander accès GPS
3. **Interface Matching** : Swipe ou boutons
4. **Carte interactive** : Google Maps / OpenStreetMap
5. **Notifications** : Alertes de match mutuel

---

## 📝 Notes techniques

### Formule Haversine

Calcule la distance orthodromique (plus court chemin) sur une sphère.

**Précision** : ~1 mètre pour des distances < 1km

**Alternative** : Vincenty (plus précis mais plus lent)

---

### Rayon de 500m

Défini dans `getNearbyUsers()` :

```php
$radius = 0.5; // 500m = 0.5km
```

Facilement modifiable pour tester d'autres rayons.

---

### Score de compatibilité

Personnalisable selon les besoins :
- Ajouter d'autres critères
- Modifier les poids
- Utiliser du machine learning

---

## ✅ Résumé

Le backend est **100% fonctionnel** avec :

- ✅ Géolocalisation des utilisateurs
- ✅ Algorithme Haversine (rayon 500m)
- ✅ Score de compatibilité (0-100)
- ✅ Système de like/pass
- ✅ Détection de match mutuel
- ✅ API complète (5 endpoints)
- ✅ Optimisations (index, SQL)
- ✅ Sécurité (validation, auth)
- ✅ Logs détaillés

**Prêt pour le frontend !** 🚀

**Date d'implémentation** : 23 février 2026
**Status** : ✅ Backend complet et testé
