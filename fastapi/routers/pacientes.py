from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from database import get_db
from deps import require_roles
from models import Paciente
from schemas import PacienteIn, PacienteOut

router = APIRouter(prefix="/pacientes", tags=["pacientes"])


@router.get("", response_model=list[PacienteOut], dependencies=[Depends(require_roles("nutriologo", "admin"))])
def list_items(db: Session = Depends(get_db)):
    return db.query(Paciente).order_by(Paciente.id.desc()).all()


@router.post("", response_model=PacienteOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def create_item(payload: PacienteIn, db: Session = Depends(get_db)):
    row = Paciente(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@router.put("/{item_id}", response_model=PacienteOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def update_item(item_id: int, payload: PacienteIn, db: Session = Depends(get_db)):
    row = db.query(Paciente).filter(Paciente.id == item_id).first()
    if not row:
        raise HTTPException(status_code=404, detail="Paciente no encontrado")
    for key, value in payload.model_dump().items():
        setattr(row, key, value)
    db.commit()
    db.refresh(row)
    return row
