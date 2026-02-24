# Tester les notifications push avec Postman (FCM HTTP v1)

L’API **FCM Legacy** (clé serveur) renvoie désormais une erreur 404. Le projet utilise l’**API FCM HTTP v1** avec un **compte de service**.

---

## 1. Configurer FCM v1 (une seule fois)

### 1.1 Créer le fichier de compte de service

1. Ouvre **[Firebase Console](https://console.firebase.google.com)** → ton projet.
2. Icône **⚙️ Paramètres du projet** → onglet **Comptes de service**.
3. Clique sur **« Générer une nouvelle clé privée »** (ou « Generate new private key ») → un fichier JSON est téléchargé.
4. Renomme ce fichier en `firebase-credentials.json` et place-le dans ton projet Laravel, par exemple :
   - `storage/app/firebase-credentials.json`
   - **Important :** ajoute `storage/app/firebase-credentials.json` à ton `.gitignore` pour ne pas le versionner.

### 1.2 Récupérer l’ID du projet

- Dans Firebase : **Paramètres du projet** → onglet **Général** → **ID du projet** (ex. `mye-app-12345`).

### 1.3 Configurer le `.env`

Ajoute (en adaptant les chemins) :

```env
# ID du projet Firebase (Paramètres > Général)
FCM_PROJECT_ID=ton-id-projet-firebase

# Chemin absolu vers le fichier JSON téléchargé
# Exemple Windows :
FCM_CREDENTIALS_PATH=C:\Users\HP\Documents\PROJECTS\mye-api\storage\app\firebase-credentials.json
# Exemple Linux/Mac :
# FCM_CREDENTIALS_PATH=/var/www/mye-api/storage/app/firebase-credentials.json
```

Redémarre le serveur Laravel après modification du `.env`.

---

## 2. Tester avec Postman

### Étape 1 – Se connecter

- **Méthode :** `POST`
- **URL :** `http://votre-domaine/api/auth/login`
- **Body (JSON) :**
  ```json
  {
    "email": "votre@email.com",
    "password": "votre_mot_de_passe"
  }
  ```
- Copie le **token** dans la réponse.

### Étape 2 – Envoyer une push de test

- **Méthode :** `POST`
- **URL :** `http://votre-domaine/api/notifications/send-test-push`
- **Headers :**
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer VOTRE_TOKEN_ICI`
- **Body (optionnel) :**
  ```json
  {
    "title": "Mon titre",
    "body": "Mon message de test"
  }
  ```

La notification doit apparaître sur l’appareil où l’utilisateur est connecté (app ouverte ou en arrière-plan).

---

## 3. Dépannage

| Message | Action |
|--------|--------|
| "Config FCM v1 manquante" | Vérifier `FCM_PROJECT_ID` et `FCM_CREDENTIALS_PATH` dans `.env`. Le chemin doit être **absolu** et le fichier doit exister. |
| "Impossible d'obtenir le token d'accès" | Fichier JSON invalide ou endommagé. Télécharger à nouveau la clé depuis Firebase (Comptes de service > Générer une nouvelle clé). |
| "FCM v1 HTTP 403" | Activer **Firebase Cloud Messaging API** (pas Legacy) dans [Google Cloud Console](https://console.cloud.google.com) → APIs & Services → Bibliothèque. |
| "Aucun token FCM enregistré" | Ouvrir l’app, se connecter, laisser l’app enregistrer le token (voir les logs Flutter/Laravel). |
| Pas de notification sur l’appareil | Vérifier les permissions de notification sur l’appareil et que l’app utilise le même projet Firebase (`google-services.json` / `GoogleService-Info.plist`). |
