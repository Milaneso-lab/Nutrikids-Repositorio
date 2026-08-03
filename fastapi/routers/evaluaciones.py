from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from database import get_db
from deps import require_roles
from models import Evaluacion
from schemas import EvaluacionIn, EvaluacionOut

router = APIRouter(prefix="/evaluaciones", tags=["evaluaciones"])


@router.get("", response_model=list[EvaluacionOut], dependencies=[Depends(require_roles("nutriologo", "admin"))])
def list_items(db: Session = Depends(get_db)):
    return db.query(Evaluacion).order_by(Evaluacion.id.desc()).all()


@router.post("", response_model=EvaluacionOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def create_item(payload: EvaluacionIn, db: Session = Depends(get_db)):
    row = Evaluacion(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@router.put("/{item_id}", response_model=EvaluacionOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def update_item(item_id: int, payload: EvaluacionIn, db: Session = Depends(get_db)):
    row = db.query(Evaluacion).filter(Evaluacion.id == item_id).first()
    if not row:
        raise HTTPException(status_code=404, detail="Evaluación no encontrada")
    for key, value in payload.model_dump().items():
        setattr(row, key, value)
    db.commit()
    db.refresh(row)
    return row
