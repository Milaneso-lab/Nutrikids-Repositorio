from fastapi import APIRouter

from app.api.v1.endpoints import auth, clinico, identidad, operaciones

api_v1_router = APIRouter()

api_v1_router.include_router(auth.router)
api_v1_router.include_router(identidad.router)
api_v1_router.include_router(clinico.router)
api_v1_router.include_router(operaciones.router)
