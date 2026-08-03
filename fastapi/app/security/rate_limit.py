"""Rate limiting distribuido (Redis) con fallback en memoria."""

import time
from collections import defaultdict
from typing import Protocol

from fastapi import HTTPException, status
from fastapi.responses import JSONResponse
from starlette.requests import Request

from app.security.settings import security_settings


class RateLimitBackend(Protocol):
    def hit(self, key: str, limit: int, window_seconds: int) -> bool: ...


class InMemoryRateLimitBackend:
    def __init__(self) -> None:
        self._history: dict[str, list[float]] = defaultdict(list)

    def hit(self, key: str, limit: int, window_seconds: int) -> bool:
        now = time.time()
        self._history[key] = [t for t in self._history[key] if now - t < window_seconds]
        if len(self._history[key]) >= limit:
            return False
        self._history[key].append(now)
        return True


class RedisRateLimitBackend:
    def __init__(self, redis_url: str) -> None:
        import redis

        self.client = redis.from_url(redis_url, decode_responses=True)

    def hit(self, key: str, limit: int, window_seconds: int) -> bool:
        pipe = self.client.pipeline()
        pipe.incr(key)
        pipe.expire(key, window_seconds)
        count, _ = pipe.execute()
        return int(count) <= limit


_backend: RateLimitBackend | None = None


def get_rate_limit_backend() -> RateLimitBackend:
    global _backend
    if _backend is None:
        if security_settings.redis_url:
            try:
                _backend = RedisRateLimitBackend(security_settings.redis_url)
            except Exception:
                _backend = InMemoryRateLimitBackend()
        else:
            _backend = InMemoryRateLimitBackend()
    return _backend


def client_ip(request: Request) -> str:
    forwarded = request.headers.get("x-forwarded-for")
    if forwarded:
        return forwarded.split(",")[0].strip()
    return request.client.host if request.client else "unknown"


class RateLimiter:
    def __init__(
        self,
        limit: int | None = None,
        window_seconds: int = 60,
        prefix: str = "rl",
        requests_limit: int | None = None,
    ):
        self.limit = limit if limit is not None else (requests_limit or 60)
        self.window_seconds = window_seconds
        self.prefix = prefix

    async def __call__(self, request: Request) -> None:
        blocked = self.check(request)
        if blocked:
            raise HTTPException(
                status_code=status.HTTP_429_TOO_MANY_REQUESTS,
                detail="Demasiadas solicitudes. Intente más tarde.",
            )

    def check(self, request: Request) -> bool:
        """True si la solicitud debe bloquearse por exceder el límite."""
        ip = client_ip(request)
        key = f"{self.prefix}:{ip}"
        return not get_rate_limit_backend().hit(key, self.limit, self.window_seconds)


def rate_limit_json_response() -> JSONResponse:
    return JSONResponse(
        status_code=status.HTTP_429_TOO_MANY_REQUESTS,
        content={
            "error": {
                "code": "RATE_LIMITED",
                "message": "Demasiadas solicitudes. Intente más tarde.",
                "details": [],
            }
        },
    )


login_rate_limiter = RateLimiter(security_settings.rate_limit_login_per_min, prefix="login")
register_rate_limiter = RateLimiter(security_settings.rate_limit_register_per_min, prefix="register")
contact_rate_limiter = RateLimiter(security_settings.rate_limit_contact_per_min, prefix="contact")
global_rate_limiter = RateLimiter(security_settings.effective_global_rate_limit, prefix="global")
