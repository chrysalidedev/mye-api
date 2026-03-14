<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lien invalide – Mye</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: #f4f6f9;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
    }
    .card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      max-width: 480px;
      width: 100%;
      overflow: hidden;
    }
    .card-header {
      background-color: #0d0d0d;
      padding: 32px 40px;
      text-align: center;
    }
    .card-header img {
      height: 40px;
      width: auto;
    }
    .card-body {
      padding: 48px 40px 40px;
      text-align: center;
    }
    .icon-wrap {
      width: 72px;
      height: 72px;
      background-color: #fdecea;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 28px;
    }
    .icon-wrap svg {
      width: 36px;
      height: 36px;
    }
    h1 {
      font-size: 22px;
      font-weight: 700;
      color: #0d0d0d;
      margin-bottom: 12px;
    }
    p {
      font-size: 15px;
      color: #555555;
      line-height: 1.65;
    }
    .card-footer {
      background-color: #f4f6f9;
      border-top: 1px solid #eeeeee;
      padding: 18px 40px;
      text-align: center;
    }
    .card-footer p {
      font-size: 12px;
      color: #aaaaaa;
    }
  </style>
</head>
<body>

  <div class="card">

    <div class="card-header">
      <img src="{{ asset('images/LOGO-MYE-Dark.png') }}" alt="Mye" />
    </div>

    <div class="card-body">

      <div class="icon-wrap">
        <!-- X mark -->
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="12" r="12" fill="#f44336"/>
          <path d="M8 8l8 8M16 8l-8 8" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round"/>
        </svg>
      </div>

      <h1>Lien invalide ou expiré</h1>
      <p>{{ $message }}</p>

      <p style="margin-top:20px;font-size:14px;color:#888888;">
        Connectez-vous à l'application et demandez un nouveau lien de vérification depuis votre profil.
      </p>

    </div>

    <div class="card-footer">
      <p>© {{ date('Y') }} <strong>Mye</strong> — Tous droits réservés</p>
    </div>

  </div>

</body>
</html>
