<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class EmailHelper {

  private static function mailer() {
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host       = SMTP_HOST;
      $mail->SMTPAuth   = true;
      $mail->Username   = SMTP_USER;
      $mail->Password   = SMTP_PASS;
      $mail->SMTPSecure = SMTP_SECURE;   // 'tls' or 'ssl'
      $mail->Port       = SMTP_PORT;

      $mail->setFrom(SMTP_FROM_EMAIL, 'TB-MAS (Baguio CESU)');
      $mail->addReplyTo(SMTP_FROM_EMAIL, 'TB-MAS Support');
      $mail->isHTML(true);
      $mail->CharSet = 'UTF-8';
    } catch (Exception $e) {
      // If mailer fails to construct, throw so callers can log
      throw $e;
    }
    return $mail;
  }

  public static function sendVerificationEmail($email, $token) {
    try {
      $mail = self::mailer();
      $mail->addAddress($email);

      $link = rtrim(BASE_URL, '/') . '/?route=auth/verify&token=' . urlencode($token);

      $html = '
      <html>
      <body style="font-family:Arial,Helvetica,sans-serif;color:#222">
        <div style="max-width:600px;margin:0 auto;padding:20px;">
          <div style="text-align:center;margin-bottom:20px;">
            <h2 style="margin:0;color:#0d6efd;">TB-MAS — Email verification</h2>
            <p style="color:#666;margin-top:6px;">Baguio City CESU</p>
          </div>

          <div style="background:#fff;border-radius:8px;padding:18px;border:1px solid #eee;">
            <p>Hi —</p>
            <p>This is the TB Monitoring & Adherence System (TB-MAS). Click the button below to verify your email and set your password.</p>

            <p style="text-align:center;margin:20px 0;">
              <a href="'.htmlspecialchars($link).'" style="background:#0d6efd;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;">
                Verify my account
              </a>
            </p>

            <p style="color:#666;font-size:13px">If the button does not work, copy and paste this link into your browser:</p>
            <p style="word-break:break-all;color:#0d6efd;font-size:13px">'.htmlspecialchars($link).'</p>

            <hr style="border:none;border-top:1px solid #f1f1f1;margin:18px 0;">
            <p style="font-size:12px;color:#888">This is an automated message from TB-MAS. If you didn\'t request this, ignore this email.</p>
          </div>
        </div>
      </body>
      </html>';

      $mail->Subject = 'TB-MAS — Verify your email';
      $mail->Body = $html;
      $mail->AltBody = "Open this link to verify your TB-MAS account: $link";

      $mail->send();
      return true;
    } catch (Exception $e) {
      // Bubble up so caller can log or save to audit logs
      error_log("EmailHelper::sendVerificationEmail error: " . $e->getMessage());
      return false;
    }
  }

  public static function sendReminder($email, $subject, $body) {
    try {
      $mail = self::mailer();
      $mail->addAddress($email);
      $mail->Subject = $subject;
      $mail->Body = '<div style="font-family:Arial,sans-serif;color:#222">'.$body.'</div>';
      $mail->AltBody = strip_tags($body);
      $mail->send();
      return true;
    } catch (Exception $e) {
      error_log("EmailHelper::sendReminder error: " . $e->getMessage());
      return false;
    }
  }
}
