from decimal import Decimal

from app.domain.utils import calcular_imc, validar_edad_nino
from datetime import date


def test_calcular_imc():
    imc = calcular_imc(Decimal("40"), Decimal("120"))
    assert imc == Decimal("27.78")


def test_calcular_imc_invalido():
    assert calcular_imc(0, 120) is None


def test_validar_edad_nino_ok():
    validar_edad_nino(date(2020, 1, 1), referencia=date(2026, 1, 1))


def test_validar_edad_nino_fuera_rango():
    import pytest

    with pytest.raises(ValueError):
        validar_edad_nino(date(2000, 1, 1), referencia=date(2026, 1, 1))
