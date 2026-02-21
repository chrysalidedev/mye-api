# ✅ Configuration de l'Upload d'Avatar - Terminée !

## 🎉 Ce qui a été fait

### 1. Contrôleur créé ✅
- **Fichier :** `app/Http/Controllers/UploadController.php`
- **Méthode :** `uploadAvatar()`
- **Fonctionnalités :**
  - Validation de l'image (jpeg, png, jpg, gif, max 2MB)
  - Suppression de l'ancien avatar
  - Upload dans `storage/app/public/avatars/`
  - Génération d'un nom unique (UUID + timestamp)
  - Retour de l'URL complète

### 2. Route ajoutée ✅
- **Route :** `POST /api/upload/avatar`
- **Middleware :** `auth:sanctum` (authentification requise)
- **Contrôleur :** `UploadController@uploadAvatar`

### 3. Lien symbolique créé ✅
```
C:\Users\HP\Documents\PROJECTS\mye-api\public\storage
→ C:\Users\HP\Documents\PROJECTS\mye-api\storage\app\public
```

### 4. Dossier avatars créé ✅
```
C:\Users\HP\Documents\PROJECTS\mye-api\storage\app\public\avatars\
```

---

## 🧪 Test avec Postman

### Étape 1 : Obtenir un token

```
POST http://173.249.58.42/api/auth/login
Content-Type: application/json

Body:
{
  "email": "votre@email.com",
  "password": "votre_mot_de_passe"
}

Response:
{
  "success": true,
  "data": {
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

**Copier le token !**

---

### Étape 2 : Upload un avatar

```
POST http://173.249.58.42/api/upload/avatar
Authorization: Bearer 1|xxxxxxxxxxxxx
Content-Type: multipart/form-data

Body (form-data):
  Key: avatar
  Type: File
  Value: [Sélectionner une image]
```

**Configuration Postman :**
1. Method: POST
2. URL: `http://173.249.58.42/api/upload/avatar`
3. Headers:
   - Key: `Authorization`
   - Value: `Bearer {votre_token}`
4. Body:
   - Sélectionner `form-data`
   - Ajouter clé `avatar`
   - Changer type de `Text` à `File`
   - Cliquer "Select Files"

**Response attendue :**
```json
{
  "success": true,
  "message": "Avatar uploadé avec succès",
  "data": {
    "avatar_url": "http://173.249.58.42/storage/avatars/9a7f8c2b-xxxx-xxxx-xxxx-xxxxxxxxxxxx_1708092000.jpg"
  }
}
```

---

## 🔍 Vérification

### 1. Vérifier que le fichier existe
```bash
ls storage/app/public/avatars/
```

### 2. Accéder à l'image dans le navigateur
```
http://173.249.58.42/storage/avatars/[nom_du_fichier].jpg
```

L'image devrait s'afficher !

---

## 📱 Test depuis l'application Flutter

1. Lancer l'app Flutter
2. Se connecter
3. Aller sur "Modifier le profil"
4. Cliquer sur l'icône caméra
5. Sélectionner une image
6. Attendre l'upload (loader visible)
7. Message de succès
8. Cliquer "Enregistrer"
9. ✅ Avatar mis à jour !

---

## 🐛 Dépannage

### Erreur 404
**Cause :** Route non trouvée
**Solution :** ✅ Déjà corrigé !

### Erreur 401 Unauthorized
**Cause :** Token manquant ou invalide
**Solution :** Se reconnecter pour obtenir un nouveau token

### Erreur 422 Validation
**Cause :** Fichier invalide (type ou taille)
**Solution :** 
- Vérifier que c'est une image (jpeg, png, jpg, gif)
- Vérifier que la taille < 2MB

### Image 404 après upload
**Cause :** Lien symbolique manquant
**Solution :** ✅ Déjà créé avec `php artisan storage:link`

### Erreur de permissions
**Solution :**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

---

## ✅ Checklist finale

- [x] UploadController créé
- [x] Route `/upload/avatar` ajoutée
- [x] Middleware `auth:sanctum` configuré
- [x] Lien symbolique créé
- [x] Dossier `avatars` créé
- [ ] Test avec Postman (à faire)
- [ ] Test depuis l'app Flutter (à faire)

---

## 📊 Structure des fichiers

```
mye-api/
├── app/
│   └── Http/
│       └── Controllers/
│           └── UploadController.php ✅ NOUVEAU
├── routes/
│   └── api.php ✅ MODIFIÉ
├── storage/
│   └── app/
│       └── public/
│           └── avatars/ ✅ NOUVEAU
└── public/
    └── storage/ ✅ LIEN SYMBOLIQUE
```

---

## 🚀 Prochaines étapes

1. **Tester avec Postman** (5 minutes)
2. **Tester depuis l'app Flutter** (5 minutes)
3. **Vérifier que l'avatar s'affiche partout** (2 minutes)

---

**Tout est prêt ! Vous pouvez maintenant tester l'upload d'avatar.** 🎉

---

**Date :** 16 février 2026  
**Statut :** ✅ Backend configuré et prêt
