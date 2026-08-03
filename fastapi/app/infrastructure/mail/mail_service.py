"""Envío de correos transaccionales."""

from __future__ import annotations

import logging
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from urllib.parse import quote

from app.infrastructure.mail.settings import mail_settings

logger = logging.getLogger("nutrikids.mail")


def _build_reset_deep_link(email: str, code: str) -> str:
    base = mail_settings.reset_deep_link_base.rstrip("/")
    return f"{base}?email={quote(email)}&token={quote(code)}"


def send_password_reset_email(
    to_email: str,
    recipient_name: str,
    code: str,
    expires_minutes: int,
) -> None:
    subject = "Código para restablecer tu contraseña — NutriKids"
    deep_link = _build_reset_deep_link(to_email, code)
    greeting = recipient_name.strip() or "Usuario"

    text_body = (
        f"Hola {greeting},\n\n"
        "Recibimos una solicitud para restablecer tu contraseña en NutriKids.\n\n"
        f"Tu código de verificación es: {code}\n\n"
        f"Este código expira en {expires_minutes} minutos.\n\n"
        f"Si tienes la app instalada, también puedes abrir este enlace:\n{deep_link}\n\n"
        "Si no solicitaste este cambio, ignora este correo.\n\n"
        "— Equipo NutriKids\n"
    )

    html_body = f"""\
<!DOCTYPE html>
<html lang="es">
  <body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hola <strong>{greeting}</strong>,</p>
    <p>Recibimos una solicitud para restablecer tu contraseña en <strong>NutriKids</strong>.</p>
    <p style="font-size: 28px; letter-spacing: 6px; font-weight: bold; color: #2e7d32;">{code}</p>
    <p>Este código expira en <strong>{expires_minutes} minutos</strong>.</p>
    <p>Abre la app, ve a <em>Restablecer contraseña</em> e ingresa el código junto con tu nueva contraseña.</p>
    <p style="font-size: 13px; color: #6b7280;">Si no solicitaste este cambio, puedes ignorar este correo.</p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;" />
    <p style="font-size: 12px; color: #9ca3af;">Equipo NutriKids</p>
  </body>
</html>
"""

    if mail_settings.mailer == "log":
        logger.info(
            "PASSWORD RESET | to=%s | code=%s | expires=%s min | deep_link=%s",
            to_email,
            code,
            expires_minutes,
            deep_link,
        )
        return

    if mail_settings.mailer != "smtp":
        raise RuntimeError(f"Mailer no soportado: {mail_settings.mailer}")

    if not mail_settings.smtp_host:
        raise RuntimeError("NUTRIKIDS_MAIL_SMTP_HOST no está configurado")

    message = MIMEMultipart("alternative")
    message["Subject"] = subject
    message["From"] = f"{mail_settings.from_name} <{mail_settings.from_address}>"
    message["To"] = to_email
    message.attach(MIMEText(text_body, "plain", "utf-8"))
    message.attach(MIMEText(html_body, "html", "utf-8"))

    with smtplib.SMTP(mail_settings.smtp_host, mail_settings.smtp_port, timeout=30) as server:
        if mail_settings.use_tls:
            server.starttls()
        if mail_settings.smtp_user:
            server.login(mail_settings.smtp_user, mail_settings.smtp_password)
        server.sendmail(mail_settings.from_address, [to_email], message.as_string())

    logger.info("Password reset email sent to %s", to_email)
