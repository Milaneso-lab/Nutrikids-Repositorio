from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session, joinedload

from database import get_db
from deps import get_current_user
from models import Comentario, Usuario
from schemas import ComentarioIn, ComentarioOut

router = APIRouter(prefix="/comentarios", tags=["comentarios"])


@router.get("", response_model=list[ComentarioOut])
def list_items(db: Session = Depends(get_db)):
    return (
        db.query(Comentario)
        .filter(Comentario.id_comentario_padre.is_(None))
        .options(joinedload(Comentario.respuestas))
        .order_by(Comentario.id_comentario.desc())
        .all()
    )


@router.post("", response_model=ComentarioOut)
def create_item(payload: ComentarioIn, user: Usuario = Depends(get_current_user), db: Session = Depends(get_db)):
    if user.rol != "padre":
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Solo padres pueden publicar comentarios")
    if payload.id_usuario is not None and payload.id_usuario != user.id_usuario:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Usuario no coincide")

    parent_id = payload.id_comentario_padre
    if parent_id is not None:
        parent = db.query(Comentario).filter(Comentario.id_comentario == parent_id).first()
        if not parent:
            raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Comentario padre no encontrado")
        if parent.id_comentario_padre is not None:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Solo se puede responder al comentario principal",
            )

    texto = payload.comentario.strip()
    if len(texto) < 5:
        raise HTTPException(status_code=status.HTTP_422_UNPROCESSABLE_ENTITY, detail="Mínimo 5 caracteres")
    if len(texto) > 1000:
        raise HTTPException(status_code=status.HTTP_422_UNPROCESSABLE_ENTITY, detail="Máximo 1000 caracteres")

    row = Comentario(
        nombre=payload.nombre,
        apellido=payload.apellido,
        comentario=texto,
        id_usuario=user.id_usuario,
        id_comentario_padre=parent_id,
    )
    db.add(row)
    db.commit()
    db.refresh(row)
    return row
