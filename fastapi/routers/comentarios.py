from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from database import get_db
from deps import get_current_user
from models import Comentario, Usuario
from schemas import ComentarioIn, ComentarioOut

router = APIRouter(prefix="/comentarios", tags=["comentarios"])


@router.get("", response_model=list[ComentarioOut])
def list_items(db: Session = Depends(get_db)):
    return db.query(Comentario).order_by(Comentario.id_comentario.desc()).all()


@router.post("", response_model=ComentarioOut)
def create_item(payload: ComentarioIn, user: Usuario = Depends(get_current_user), db: Session = Depends(get_db)):
    if user.rol != "padre":
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Solo padres pueden publicar comentarios")
    if payload.id_usuario is not None and payload.id_usuario != user.id_usuario:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Usuario no coincide")
    row = Comentario(
        nombre=payload.nombre,
        apellido=payload.apellido,
        comentario=payload.comentario,
        id_usuario=user.id_usuario,
    )
    db.add(row)
    db.commit()
    db.refresh(row)
    return row
