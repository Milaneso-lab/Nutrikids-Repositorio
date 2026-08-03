from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from database import get_db
from deps import require_roles
from models import Usuario
from schemas import UsuarioIn, UsuarioOut
from security import hash_password

router = APIRouter(prefix="/usuarios", tags=["usuarios"])


@router.get("", response_model=list[UsuarioOut], dependencies=[Depends(require_roles("admin"))])
def list_users(db: Session = Depends(get_db)):
    return db.query(Usuario).all()


@router.post("", response_model=UsuarioOut, dependencies=[Depends(require_roles("admin"))])
def create_user(payload: UsuarioIn, db: Session = Depends(get_db)):
    exists = db.query(Usuario).filter(Usuario.email == payload.email).first()
    if exists:
        raise HTTPException(status_code=400, detail="Email ya registrado")
    user = Usuario(
        nombre=payload.nombre,
        apellido_paterno=payload.apellido_paterno,
        apellido_materno=payload.apellido_materno,
        email=payload.email,
        contrasena=hash_password(payload.contrasena),
        rol=payload.rol,
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    return user
