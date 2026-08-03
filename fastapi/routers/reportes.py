from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from database import get_db
from deps import require_roles
from models import Reporte
from schemas import ReporteIn, ReporteOut

router = APIRouter(prefix="/reportes", tags=["reportes"])


@router.get("", response_model=list[ReporteOut], dependencies=[Depends(require_roles("nutriologo", "admin"))])
def list_items(db: Session = Depends(get_db)):
    return db.query(Reporte).order_by(Reporte.id.desc()).all()


@router.post("", response_model=ReporteOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def create_item(payload: ReporteIn, db: Session = Depends(get_db)):
    row = Reporte(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@router.get("/{item_id}", response_model=ReporteOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def get_item(item_id: int, db: Session = Depends(get_db)):
    row = db.query(Reporte).filter(Reporte.id == item_id).first()
    if not row:
        raise HTTPException(status_code=404, detail="Reporte no encontrado")
    return row
