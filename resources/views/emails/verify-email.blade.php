<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vérifiez votre adresse email – Mye</title>
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
              <img
                src="{{ config('app.url') }}/images/LOGO-MYE-Dark.png"
                alt="Mye"
                width="100"
                style="display:block;height:auto;"
              />
            </td>
          </tr>

          <!-- Corps du message -->
          <tr>
            <td style="padding:40px 40px 32px;">

              <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0d0d0d;">
                Bonjour {{ $name }}&nbsp;!
              </h1>
              <p style="margin:0 0 24px;font-size:15px;color:#555555;line-height:1.6;">
                Merci de vous être inscrit sur <strong>Mye</strong>. Pour activer votre compte et commencer à utiliser la plateforme, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous.
              </p>

              <!-- Bouton CTA -->
              <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                <tr>
                  <td align="center" style="background-color:#0d0d0d;border-radius:8px;">
                    <a
                      href="{{ $url }}"
                      target="_blank"
                      style="display:inline-block;padding:14px 36px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:0.3px;"
                    >
                      Vérifier mon email
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Avertissement lien -->
              <p style="margin:0 0 16px;font-size:13px;color:#888888;line-height:1.5;">
                Ce lien est valable <strong>60&nbsp;minutes</strong>. Passé ce délai, vous devrez en demander un nouveau depuis l'application.
              </p>
              <p style="margin:0;font-size:13px;color:#888888;line-height:1.5;">
                Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email en toute sécurité.
              </p>

            </td>
          </tr>

          <!-- Séparateur -->
          <tr>
            <td style="padding:0 40px;">
              <hr style="border:none;border-top:1px solid #eeeeee;margin:0;" />
            </td>
          </tr>

          <!-- Lien de secours -->
          <tr>
            <td style="padding:20px 40px;">
              <p style="margin:0;font-size:12px;color:#aaaaaa;line-height:1.6;">
                Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur&nbsp;:
              </p>
              <p style="margin:6px 0 0;font-size:11px;word-break:break-all;">
                <a href="{{ $url }}" style="color:#0d0d0d;text-decoration:underline;">{{ $url }}</a>
              </p>
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
