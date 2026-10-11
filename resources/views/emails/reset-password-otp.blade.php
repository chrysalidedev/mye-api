<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Réinitialisation de mot de passe – Mye</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:40px 16px;">
    <tr>
      <td align="center">

        <!-- Carte principale -->
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">

          <!-- En-tête avec logo -->
          <tr>
            <td align="center" style="background-color:#0d0d0d;padding:32px 40px;">
              @php
                $logoPath = public_path('images/LOGO-MYE-Dark.png');
              @endphp
              @if (isset($message) && file_exists($logoPath))
                <img
                  src="{{ $message->embed($logoPath) }}"
                  alt="Mye"
                  width="110"
                  style="display:block;height:auto;margin:0 auto;"
                />
              @elseif (file_exists($logoPath))
                <img
                  src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                  alt="Mye"
                  width="110"
                  style="display:block;height:auto;margin:0 auto;"
                />
              @else
                <div style="font-size:26px;font-weight:bold;color:#CBA948;letter-spacing:2px;font-family:sans-serif;">MYE</div>
              @endif
            </td>
          </tr>

          <!-- Corps du message -->
          <tr>
            <td style="padding:40px 40px 32px;">

              <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0d0d0d;">
                Bonjour {{ $name }}&nbsp;!
              </h1>
              <p style="margin:0 0 24px;font-size:15px;color:#555555;line-height:1.6;">
                Vous avez demandé la réinitialisation de votre mot de passe sur <strong>Mye</strong>. Utilisez le code de vérification à 6 chiffres ci-dessous pour finaliser l'opération&nbsp;:
              </p>

              <!-- Bloc Code OTP -->
              <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;width:100%;">
                <tr>
                  <td align="center">
                    <div style="display:inline-block;background-color:#f8f9fa;border:2px solid #CBA948;border-radius:12px;padding:18px 36px;letter-spacing:10px;font-size:32px;font-weight:800;color:#0d0d0d;font-family:monospace;box-shadow:0 4px 10px rgba(203,169,72,0.15);">
                      {{ $code }}
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Avertissement expiration -->
              <p style="margin:0 0 16px;font-size:13px;color:#888888;line-height:1.5;">
                ⏱ Ce code est valable pendant <strong>15&nbsp;minutes</strong>. Passé ce délai, vous devrez faire une nouvelle demande depuis l'application.
              </p>
              <p style="margin:0;font-size:13px;color:#888888;line-height:1.5;">
                🔒 Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email. Votre mot de passe restera inchangé et sécurisé.
              </p>

            </td>
          </tr>

          <!-- Séparateur -->
          <tr>
            <td style="padding:0 40px;">
              <hr style="border:none;border-top:1px solid #eeeeee;margin:0;" />
            </td>
          </tr>

          <!-- Pied de page -->
          <tr>
            <td align="center" style="background-color:#f4f6f9;padding:20px 40px;border-top:1px solid #eeeeee;">
              <p style="margin:0;font-size:12px;color:#aaaaaa;">
                © {{ date('Y') }} <strong>Mye</strong> — Tous droits réservés
              </p>
            </td>
          </tr>

        </table>
        <!-- Fin carte -->

      </td>
    </tr>
  </table>

</body>
</html>
