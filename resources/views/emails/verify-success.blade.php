<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Email vérifié – Mye</title>
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
      background-color: #e8f5e9;
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
    .highlight {
      font-weight: 600;
      color: #0d0d0d;
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
        <!-- checkmark -->
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="12" r="12" fill="#4caf50"/>
          <path d="M7 12.5l3.5 3.5 6.5-7" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <h1>Email vérifié&nbsp;!</h1>
      <p>
        Bonjour <span class="highlight">{{ $name }}</span>,<br/>
        votre adresse email a bien été confirmée.<br/>
        Votre compte <span class="highlight">Mye</span> est maintenant actif.
      </p>

      <p style="margin-top:20px;font-size:14px;color:#888888;">
        Vous pouvez fermer cette page et vous connecter depuis l'application.
      </p>

    </div>

    <div class="card-footer">
      <p>© {{ date('Y') }} <strong>Mye</strong> — Tous droits réservés</p>
    </div>

  </div>

</body>
</html>
