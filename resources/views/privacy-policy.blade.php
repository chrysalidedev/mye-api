<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Politique de Confidentialité — MyeApp</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafb;
            color: #1f2937;
            line-height: 1.7;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 48px 24px;
        }

        .header {
            text-align: center;
            margin-bottom: 48px;
            padding-bottom: 32px;
            border-bottom: 2px solid #e5e7eb;
        }

        .app-name {
            font-size: 14px;
            font-weight: 600;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 12px;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .last-updated {
            font-size: 14px;
            color: #6b7280;
        }

        section {
            margin-bottom: 40px;
        }

        h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
            padding-left: 12px;
            border-left: 3px solid #6366f1;
        }

        p {
            color: #374151;
            margin-bottom: 12px;
        }

        ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 12px;
        }

        ul li {
            color: #374151;
            padding: 4px 0 4px 20px;
            position: relative;
        }

        ul li::before {
            content: "•";
            color: #6366f1;
            position: absolute;
            left: 0;
        }

        .contact-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 20px 24px;
            margin-top: 12px;
        }

        .contact-box p {
            margin-bottom: 4px;
        }

        .contact-box a {
            color: #4f46e5;
            text-decoration: none;
        }

        .contact-box a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            margin-top: 64px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <div class="app-name">MyeApp</div>
            <h1>Politique de Confidentialité</h1>
            <p class="last-updated">Dernière mise à jour : {{ \Carbon\Carbon::parse('2026-04-09')->translatedFormat('d F Y') }}</p>
        </div>

        <section>
            <h2>1. Introduction</h2>
            <p>
                Bienvenue sur <strong>MyeApp</strong>. Nous accordons une grande importance à la protection
                de vos données personnelles. Cette politique de confidentialité explique quelles informations
                nous collectons, comment nous les utilisons et les droits dont vous disposez à leur égard.
            </p>
            <p>
                En utilisant notre application, vous acceptez les pratiques décrites dans ce document.
            </p>
        </section>

        <section>
            <h2>2. Données collectées</h2>
            <p>Nous collectons les données suivantes :</p>
            <ul>
                <li><strong>Informations d'identification :</strong> nom, prénom, adresse e-mail.</li>
                <li><strong>Données de profil :</strong> photo de profil, préférences.</li>
                <li><strong>Données d'utilisation :</strong> historique des actions dans l'application, abonnements.</li>
                <li><strong>Données techniques :</strong> adresse IP, type d'appareil, système d'exploitation, version de l'application.</li>
                <li><strong>Notifications :</strong> jeton de notification push (FCM token) si vous avez autorisé les notifications.</li>
            </ul>
        </section>

        <section>
            <h2>3. Finalités du traitement</h2>
            <p>Vos données sont utilisées pour :</p>
            <ul>
                <li>Créer et gérer votre compte utilisateur.</li>
                <li>Vous fournir les fonctionnalités de l'application.</li>
                <li>Gérer vos abonnements et paiements.</li>
                <li>Vous envoyer des notifications importantes liées à votre compte.</li>
                <li>Améliorer nos services et analyser l'utilisation de l'application.</li>
                <li>Assurer la sécurité et prévenir les fraudes.</li>
            </ul>
        </section>

        <section>
            <h2>4. Base légale du traitement</h2>
            <p>Le traitement de vos données repose sur :</p>
            <ul>
                <li>L'exécution du contrat lors de l'utilisation de nos services.</li>
                <li>Votre consentement (ex. : notifications push).</li>
                <li>Notre intérêt légitime à améliorer et sécuriser nos services.</li>
            </ul>
        </section>

        <section>
            <h2>5. Partage des données</h2>
            <p>
                Nous ne vendons ni ne louons vos données personnelles à des tiers.
                Nous pouvons toutefois les partager avec :
            </p>
            <ul>
                <li><strong>Prestataires techniques :</strong> hébergement, paiement en ligne, services de notifications (Firebase).</li>
                <li><strong>Autorités compétentes :</strong> si la loi l'exige.</li>
            </ul>
            <p>Tout prestataire est soumis à des obligations strictes de confidentialité.</p>
        </section>

        <section>
            <h2>6. Conservation des données</h2>
            <p>
                Vos données sont conservées pendant toute la durée d'activité de votre compte.
                En cas de suppression du compte, vos données sont effacées dans un délai de <strong>30 jours</strong>,
                sauf obligation légale de conservation.
            </p>
        </section>

        <section>
            <h2>7. Sécurité</h2>
            <p>
                Nous mettons en œuvre des mesures techniques et organisationnelles appropriées
                (chiffrement, contrôle d'accès, HTTPS) pour protéger vos données contre tout
                accès non autorisé, perte ou divulgation.
            </p>
        </section>

        <section>
            <h2>8. Vos droits</h2>
            <p>Conformément à la réglementation applicable, vous disposez des droits suivants :</p>
            <ul>
                <li><strong>Droit d'accès :</strong> obtenir une copie de vos données.</li>
                <li><strong>Droit de rectification :</strong> corriger des données inexactes.</li>
                <li><strong>Droit à l'effacement :</strong> demander la suppression de vos données.</li>
                <li><strong>Droit d'opposition :</strong> vous opposer à certains traitements.</li>
                <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré.</li>
            </ul>
            <p>Pour exercer ces droits, contactez-nous via les coordonnées ci-dessous.</p>
        </section>

        <section>
            <h2>9. Cookies</h2>
            <p>
                L'application mobile n'utilise pas de cookies. L'API backend peut utiliser des
                cookies de session uniquement à des fins techniques d'authentification.
            </p>
        </section>

        <section>
            <h2>10. Modifications de cette politique</h2>
            <p>
                Nous nous réservons le droit de mettre à jour cette politique de confidentialité.
                Toute modification sera signalée dans l'application et la date de mise à jour
                sera révisée en haut de cette page.
            </p>
        </section>

        <section>
            <h2>11. Nous contacter</h2>
            <p>Pour toute question relative à cette politique ou à vos données :</p>
            <div class="contact-box">
                <p><strong>MyeApp</strong></p>
                <p>E-mail : <a href="mailto:support@myeapp.com">support@myeapp.com</a></p>
            </div>
        </section>

        <footer>
            &copy; {{ date('Y') }} MyeApp. Tous droits réservés.
        </footer>

    </div>
</body>
</html>
