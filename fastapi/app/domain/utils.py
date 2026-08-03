"""Utilidades de dominio puras (sin dependencias de infraestructura)."""

from __future__ import annotations

from datetime import date
from decimal import Decimal, ROUND_HALF_UP


def calcular_imc(peso_kg: float | Decimal, talla_cm: float | Decimal) -> Decimal | None:
    if not peso_kg or not talla_cm or float(talla_cm) <= 0:
        return None
    talla_m = Decimal(str(talla_cm)) / Decimal("100")
    imc = Decimal(str(peso_kg)) / (talla_m * talla_m)
    return imc.quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)


def validar_edad_nino(fecha_nacimiento: date, referencia: date | None = None) -> None:
    ref = referencia or date.today()
    edad_anios = ref.year - fecha_nacimiento.year
    if (ref.month, ref.day) < (fecha_nacimiento.month, fecha_nacimiento.day):
        edad_anios -= 1
    if edad_anios < 0 or edad_anios > 18:
        raise ValueError("La edad del niño debe estar entre 0 y 18 años")


def generar_codigo_vinculacion() -> str:
    import secrets
    import string

    alphabet = string.ascii_uppercase + string.digits
    return "".join(secrets.choice(alphabet) for _ in range(8))
