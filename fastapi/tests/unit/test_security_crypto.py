"""Pruebas de módulos de seguridad."""

from app.security.crypto import (
    create_access_token,
    hash_password,
    hash_token,
    validate_password_policy,
    verify_password,
)


def test_password_hash_and_verify():
    h = hash_password("Segura123")
    assert verify_password("Segura123", h)
    assert not verify_password("incorrecta", h)


def test_password_policy_rejects_weak():
    issues = validate_password_policy("abc")
    assert len(issues) >= 2


def test_access_token_contains_jti():
    token, jti = create_access_token(1, "admin")
    assert token
    assert len(jti) == 36


def test_hash_token_deterministic():
    assert hash_token("abc") == hash_token("abc")
    assert hash_token("abc") != hash_token("xyz")
