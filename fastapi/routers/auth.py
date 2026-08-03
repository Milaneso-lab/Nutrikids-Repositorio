from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from database import get_db
from deps import InMemoryRateLimiter
from models import Usuario
from schemas import LoginIn, TokenOut, UsuarioIn, UsuarioOut
from security import create_access_token, hash_password, verify_password

router = APIRouter(prefix="/auth", tags=["auth"])

# Limitadores de tasa en memoria por IP
login_rate_limiter = InMemoryRateLimiter(requests_limit=5, window_seconds=60)
register_rate_limiter = InMemoryRateLimiter(requests_limit=3, window_seconds=60)


@router.post("/login", response_model=TokenOut, dependencies=[Depends(login_rate_limiter)])
def login(payload: LoginIn, db: Session = Depends(get_db)):
    user = db.query(Usuario).filter(Usuario.email == payload.email).first()
    if not user or not verify_password(payload.contrasena, user.contrasena):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Credenciales inválidas")

    token = create_access_token(str(user.id_usuario), {"rol": user.rol})
    return TokenOut(
        access_token=token,
        rol=user.rol,
        id_usuario=user.id_usuario,
        nombre=user.nombre,
        apellido_paterno=user.apellido_paterno,
        email=user.email,
    )


@router.post("/register", response_model=UsuarioOut, dependencies=[Depends(register_rate_limiter)])
def register_padre(payload: UsuarioIn, db: Session = Depends(get_db)):
    if payload.rol != "padre":
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Solo se permite registro como padre")
    exists = db.query(Usuario).filter(Usuario.email == payload.email).first()
    if exists:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Email ya registrado")
    user = Usuario(
        nombre=payload.nombre,
        apellido_paterno=payload.apellido_paterno,
        apellido_materno=payload.apellido_materno,
        email=payload.email,
        contrasena=hash_password(payload.contrasena),
        rol="padre",
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    return user

