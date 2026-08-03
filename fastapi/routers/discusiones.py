from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from database import get_db
from deps import get_current_user
from models import Discusion, Usuario
from schemas import DiscusionIn, DiscusionOut

router = APIRouter(prefix="/discusiones", tags=["discusiones"])


def _ensure_owner(row: Discusion, user: Usuario) -> None:
    if row.id_usuario != user.id_usuario and user.rol != "admin":
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="No autorizado")


@router.get("", response_model=list[DiscusionOut])
def list_items(db: Session = Depends(get_db)):
    return db.query(Discusion).order_by(Discusion.id_discusion.desc()).all()


@router.post("", response_model=DiscusionOut)
def create_item(payload: DiscusionIn, user: Usuario = Depends(get_current_user), db: Session = Depends(get_db)):
    if user.rol != "padre":
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Solo usuarios padre pueden crear discusiones")
    row = Discusion(tema=payload.tema, descripcion=payload.descripcion, id_usuario=user.id_usuario)
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@router.put("/{discusion_id}", response_model=DiscusionOut)
def update_item(
    discusion_id: int,
    payload: DiscusionIn,
    user: Usuario = Depends(get_current_user),
    db: Session = Depends(get_db),
):
    row = db.query(Discusion).filter(Discusion.id_discusion == discusion_id).first()
    if not row:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="No encontrado")
    _ensure_owner(row, user)
    row.tema = payload.tema
    row.descripcion = payload.descripcion
    db.commit()
    db.refresh(row)
    return row


@router.delete("/{discusion_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_item(
    discusion_id: int,
    user: Usuario = Depends(get_current_user),
    db: Session = Depends(get_db),
):
    row = db.query(Discusion).filter(Discusion.id_discusion == discusion_id).first()
    if not row:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="No encontrado")
    _ensure_owner(row, user)
    db.delete(row)
    db.commit()
    return None
