from typing import Any


class AppException(Exception):
    """Excepción de dominio con formato de error uniforme (04_API.md §3.2)."""

    def __init__(
        self,
        code: str,
        message: str,
        status_code: int = 400,
        details: list[dict[str, str]] | None = None,
    ) -> None:
        self.code = code
        self.message = message
        self.status_code = status_code
        self.details = details or []
        super().__init__(message)

    def to_dict(self) -> dict[str, Any]:
        return {
            "error": {
                "code": self.code,
                "message": self.message,
                "details": self.details,
            }
        }


class ValidationError(AppException):
    def __init__(self, message: str = "Errores de validación", details: list[dict[str, str]] | None = None):
        super().__init__("VALIDATION_ERROR", message, 400, details)


class NotFoundError(AppException):
    def __init__(self, message: str = "Recurso no encontrado"):
        super().__init__("NOT_FOUND", message, 404)


class ConflictError(AppException):
    def __init__(self, message: str = "Conflicto con el estado actual del recurso"):
        super().__init__("CONFLICT", message, 409)
