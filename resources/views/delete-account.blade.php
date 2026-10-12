<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suppression de compte et des données — MyeApp</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.65;
        }

        .container {
            max-width: 820px;
            margin: 0 auto;
            padding: 48px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 28px;
            border-bottom: 2px solid #e2e8f0;
        }

        .app-badge {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 6px 14px;
            border-radius: 9999px;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 2.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.25;
        }

        .subtitle {
            font-size: 16px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
        }

        h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 .icon {
            font-size: 1.4rem;
        }

        p {
            color: #334155;
            margin-bottom: 14px;
        }

        /* Méthode In-App */
        .steps {
            list-style: none;
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            border-left: 4px solid #6366f1;
        }

        .step-num {
            background: #6366f1;
            color: #ffffff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .step-text strong {
            color: #0f172a;
        }

        /* Formulaire */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background-color: #ffffff;
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            color: #475569;
            cursor: pointer;
            margin-top: 16px;
        }

        .checkbox-label input {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            font-weight: 600;
            font-size: 16px;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .alert {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 15px;
        }

        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Tableau données */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 14px;
        }

        .data-table th, .data-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }

        .badge-delete {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .badge-retain {
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .footer a {
            color: #6366f1;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <span class="app-badge">MyeApp — Protection des Données</span>
            <h1>Demande de suppression de compte et de données</h1>
            <p class="subtitle">
                Conformément aux exigences de Google Play et aux règles de confidentialité (RGPD), vous disposez d'un contrôle total sur vos données personnelles.
            </p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <strong>✓ Demande envoyée avec succès :</strong><br>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Erreur :</strong>
                <ul style="margin-left: 20px; margin-top: 6px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- OPTION 1 : DIRECTEMENT DANS L'APPLICATION -->
        <div class="card">
            <h2><span class="icon">📱</span> Méthode 1 : Supprimer directement depuis l'application mobile Mye</h2>
            <p>
                Si vous avez accès à votre smartphone, la méthode la plus rapide et instantanée consiste à supprimer votre compte depuis l'application mobile :
            </p>
            <ol class="steps">
                <li class="step-item">
                    <span class="step-num">1</span>
                    <div class="step-text">
                        <strong>Ouvrez l'application Mye</strong> et connectez-vous avec vos identifiants.
                    </div>
                </li>
                <li class="step-item">
                    <span class="step-num">2</span>
                    <div class="step-text">
                        Rendez-vous dans la section <strong>Profil</strong> (en bas à droite de l'écran).
                    </div>
                </li>
                <li class="step-item">
                    <span class="step-num">3</span>
                    <div class="step-text">
                        Sélectionnez <strong>Paramètres du compte</strong> puis cliquez sur <strong>Supprimer mon compte</strong>.
                    </div>
                </li>
                <li class="step-item">
                    <span class="step-num">4</span>
                    <div class="step-text">
                        Confirmez la suppression. Vos sessions sont immédiatement déconnectées et vos données personnelles sont effacées.
                    </div>
                </li>
            </ol>
        </div>

        <!-- OPTION 2 : FORMULAIRE EN LIGNE (GOOGLE PLAY REQUIREMENT) -->
        <div class="card">
            <h2><span class="icon">🌐</span> Méthode 2 : Formulaire de demande de suppression en ligne</h2>
            <p>
                Si vous avez désinstallé l'application mobile ou n'avez plus accès à votre appareil, vous pouvez soumettre une demande formelle via le formulaire ci-dessous :
            </p>

            <form action="{{ route('account.delete.submit') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="email">Adresse e-mail associée à votre compte Mye *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="exemple@domaine.com" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">Numéro de téléphone (optionnel, si vous vous êtes inscrit par téléphone)</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="+33 6 12 34 56 78" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="reason">Motif de suppression (optionnel)</label>
                    <textarea id="reason" name="reason" class="form-control" placeholder="Indiquez brièvement pourquoi vous souhaitez supprimer votre compte...">{{ old('reason') }}</textarea>
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="confirmation" value="1" {{ old('confirmation') ? 'checked' : '' }} required>
                    <span>
                        Je confirme vouloir supprimer définitivement mon compte MyeApp ainsi que toutes mes données personnelles associées (profil, conversations, contacts et fichiers).
                    </span>
                </label>

                <button type="submit" class="btn-submit">
                    Demander la suppression de mon compte et de mes données
                </button>
            </form>
        </div>

        <!-- TRANSPARENCE SUR LES DONNEES SUPPRIMEES (EXIGENCE GOOGLE PLAY) -->
        <div class="card">
            <h2><span class="icon">🔒</span> Données supprimées et durée de conservation</h2>
            <p>
                Conformément aux règles de sécurité des données de Google Play, voici le détail du traitement de vos données lors d'une demande de suppression :
            </p>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Catégorie de données</th>
                        <th>Types de données</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Identité & Profil</strong></td>
                        <td>Nom, prénom, adresse email, numéro de téléphone, date de naissance, photo de profil (avatar)</td>
                        <td><span class="badge-delete">Suppression définitive</span></td>
                    </tr>
                    <tr>
                        <td><strong>Profil Professionnel</strong></td>
                        <td>Métier, compétences, bio, CV, données d'entreprise</td>
                        <td><span class="badge-delete">Suppression définitive</span></td>
                    </tr>
                    <tr>
                        <td><strong>Communications</strong></td>
                        <td>Messages textuels, notes vocales, historique des conversations</td>
                        <td><span class="badge-delete">Suppression définitive</span></td>
                    </tr>
                    <tr>
                        <td><strong>Réseau & Matchs</strong></td>
                        <td>Demandes de connexion, relations, correspondances professionnelles</td>
                        <td><span class="badge-delete">Suppression définitive</span></td>
                    </tr>
                    <tr>
                        <td><strong>Données Techniques</strong></td>
                        <td>Jetons de session (tokens), identifiants de notifications push (FCM)</td>
                        <td><span class="badge-delete">Révocation & Suppression immédiate</span></td>
                    </tr>
                    <tr>
                        <td><strong>Historique de Facturation</strong></td>
                        <td>Reçus d'abonnements, transactions de paiement</td>
                        <td><span class="badge-retain">Conservation légale (30 jours à 10 ans selon obligations comptables)</span></td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 20px; font-size: 14px; color: #475569; background: #f1f5f9; padding: 16px; border-radius: 10px;">
                <strong>Délai d'exécution :</strong> La révocation de vos accès est immédiate. L'effacement complet des données sur l'ensemble de nos serveurs et copies de sauvegarde est exécuté dans un délai maximal de <strong>30 jours calendaires</strong>.
            </div>
        </div>

        <div class="footer">
            <p>Une question ou une demande d'assistance directe ? Contactez notre support : <a href="mailto:support@myeapp.com">support@myeapp.com</a></p>
            <p style="margin-top: 8px;">
                <a href="{{ route('privacy-policy') }}">Consulter notre Politique de Confidentialité</a> &bull; &copy; {{ date('Y') }} MyeApp. Tous droits réservés.
            </p>
        </div>

    </div>
</body>
</html>
