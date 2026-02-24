# 🧪 Tests Postman - API Connexions

## 📝 Prérequis

1. **Créer 2 utilisateurs** pour tester les connexions
2. **Récupérer les tokens** de chaque utilisateur après login
3. **Remplacer** `{token_user1}` et `{token_user2}` dans les exemples

## 🔗 Base URL
```
http://192.168.100.46:8000/api
```

---

## 1️⃣ Envoyer une demande de connexion

**Endpoint** : `POST /connections/send`

**Headers** :
```
Authorization: Bearer {token_user1}
Content-Type: application/json
```

**Body** :
```json
{
  "receiver_id": 2
}
```

**Réponse attendue (201)** :
```json
{
  "success": true,
  "message": "Demande de connexion envoyée",
  "data": {
    "id": 1,
    "sender_id": 1,
    "receiver_id": 2,
    "status": "pending",
    "created_at": "2026-02-21T21:00:00.000000Z",
    "updated_at": "2026-02-21T21:00:00.000000Z",
    "sender": {
      "id": 1,
      "name": "Alice",
      "email": "alice@example.com",
      ...
    },
    "receiver": {
      "id": 2,
      "name": "Bob",
      "email": "bob@example.com",
      ...
    }
  }
}
```

**Erreurs possibles** :
- 400 : "Vous ne pouvez pas vous connecter à vous-même"
- 400 : "Une demande de connexion existe déjà"
- 404 : Utilisateur introuvable

---

## 2️⃣ Vérifier le statut avec un utilisateur

**Endpoint** : `GET /connections/status/{userId}`

**Headers** :
```
Authorization: Bearer {token_user1}
```

**Exemple** : `GET /connections/status/2`

**Réponse - Aucune connexion** :
```json
{
  "success": true,
  "data": {
    "status": "none",
    "connection": null
  }
}
```

**Réponse - Demande envoyée** :
```json
{
  "success": true,
  "data": {
    "status": "pending",
    "connection": {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "status": "pending",
      ...
    },
    "is_sender": true
  }
}
```

**Réponse - Connexion acceptée** :
```json
{
  "success": true,
  "data": {
    "status": "accepted",
    "connection": {...},
    "is_sender": true
  }
}
```

---

## 3️⃣ Voir les demandes reçues

**Endpoint** : `GET /connections/pending`

**Headers** :
```
Authorization: Bearer {token_user2}
```

**Réponse (200)** :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "status": "pending",
      "created_at": "2026-02-21T21:00:00.000000Z",
      "sender": {
        "id": 1,
        "name": "Alice",
        "avatar_url": "...",
        ...
      }
    }
  ]
}
```

---

## 4️⃣ Accepter une demande

**Endpoint** : `POST /connections/{id}/accept`

**Headers** :
```
Authorization: Bearer {token_user2}
Content-Type: application/json
```

**Exemple** : `POST /connections/1/accept`

**Body** : (vide)

**Réponse (200)** :
```json
{
  "success": true,
  "message": "Connexion acceptée",
  "data": {
    "id": 1,
    "sender_id": 1,
    "receiver_id": 2,
    "status": "accepted",
    "updated_at": "2026-02-21T21:05:00.000000Z",
    "sender": {...},
    "receiver": {...}
  }
}
```

**Erreurs possibles** :
- 404 : "Demande de connexion introuvable"
- 403 : Seul le destinataire peut accepter

---

## 5️⃣ Rejeter une demande

**Endpoint** : `POST /connections/{id}/reject`

**Headers** :
```
Authorization: Bearer {token_user2}
Content-Type: application/json
```

**Exemple** : `POST /connections/1/reject`

**Réponse (200)** :
```json
{
  "success": true,
  "message": "Connexion rejetée",
  "data": {
    "id": 1,
    "status": "rejected",
    ...
  }
}
```

---

## 6️⃣ Annuler une demande (par l'envoyeur)

**Endpoint** : `DELETE /connections/{id}/cancel`

**Headers** :
```
Authorization: Bearer {token_user1}
```

**Exemple** : `DELETE /connections/1/cancel`

**Réponse (200)** :
```json
{
  "success": true,
  "message": "Demande annulée"
}
```

**Erreurs possibles** :
- 404 : "Demande de connexion introuvable"
- 403 : Seul l'envoyeur peut annuler

---

## 7️⃣ Voir mes connexions acceptées

**Endpoint** : `GET /connections/my-connections`

**Headers** :
```
Authorization: Bearer {token_user1}
```

**Réponse (200)** :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "status": "accepted",
      "created_at": "2026-02-21T21:00:00.000000Z",
      "sender": {...},
      "receiver": {...},
      "connected_user": {
        "id": 2,
        "name": "Bob",
        ...
      }
    }
  ]
}
```

---

## 8️⃣ Voir les demandes envoyées

**Endpoint** : `GET /connections/sent`

**Headers** :
```
Authorization: Bearer {token_user1}
```

**Réponse (200)** :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "status": "pending",
      "receiver": {
        "id": 2,
        "name": "Bob",
        ...
      }
    }
  ]
}
```

---

## 9️⃣ Supprimer une connexion établie

**Endpoint** : `DELETE /connections/{id}/remove`

**Headers** :
```
Authorization: Bearer {token_user1}
```

**Exemple** : `DELETE /connections/1/remove`

**Réponse (200)** :
```json
{
  "success": true,
  "message": "Connexion supprimée"
}
```

---

## 🧪 Scénario de test complet

### Étape 1 : Connexion des utilisateurs
```bash
# User 1 (Alice)
POST /auth/login
{
  "email": "alice@example.com",
  "password": "password123"
}
→ Récupérer token_alice

# User 2 (Bob)
POST /auth/login
{
  "email": "bob@example.com",
  "password": "password123"
}
→ Récupérer token_bob
```

### Étape 2 : Alice envoie une demande à Bob
```bash
POST /connections/send
Authorization: Bearer {token_alice}
{
  "receiver_id": 2
}
→ ✅ Status 201, connection créée
```

### Étape 3 : Vérifier le statut (Alice)
```bash
GET /connections/status/2
Authorization: Bearer {token_alice}
→ ✅ Status: "pending", is_sender: true
```

### Étape 4 : Bob voit les demandes reçues
```bash
GET /connections/pending
Authorization: Bearer {token_bob}
→ ✅ Liste avec 1 demande d'Alice
```

### Étape 5 : Bob accepte la demande
```bash
POST /connections/1/accept
Authorization: Bearer {token_bob}
→ ✅ Status 200, connection.status = "accepted"
```

### Étape 6 : Vérifier les connexions (Alice)
```bash
GET /connections/my-connections
Authorization: Bearer {token_alice}
→ ✅ Liste avec 1 connexion (Bob)
```

### Étape 7 : Vérifier les connexions (Bob)
```bash
GET /connections/my-connections
Authorization: Bearer {token_bob}
→ ✅ Liste avec 1 connexion (Alice)
```

---

## 📊 Collection Postman

Créez une collection avec ces variables d'environnement :

```json
{
  "base_url": "http://192.168.100.46:8000/api",
  "token_alice": "...",
  "token_bob": "...",
  "user_alice_id": "1",
  "user_bob_id": "2"
}
```

Puis utilisez `{{base_url}}` et `{{token_alice}}` dans vos requêtes.

---

## ✅ Checklist de tests

- [ ] Envoyer une demande
- [ ] Vérifier qu'on ne peut pas envoyer à soi-même
- [ ] Vérifier qu'on ne peut pas envoyer 2 fois au même utilisateur
- [ ] Voir les demandes reçues
- [ ] Voir les demandes envoyées
- [ ] Accepter une demande
- [ ] Refuser une demande
- [ ] Annuler une demande
- [ ] Voir mes connexions
- [ ] Vérifier le statut avec un utilisateur
- [ ] Supprimer une connexion établie
