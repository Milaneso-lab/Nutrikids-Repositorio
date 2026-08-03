from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from database import get_db
from deps import require_roles
from models import Menu
from schemas import MenuIn, MenuOut

router = APIRouter(prefix="/menus", tags=["menus"])


@router.get("", response_model=list[MenuOut], dependencies=[Depends(require_roles("nutriologo", "admin"))])
def list_items(db: Session = Depends(get_db)):
    return db.query(Menu).order_by(Menu.id.desc()).all()


@router.post("", response_model=MenuOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def create_item(payload: MenuIn, db: Session = Depends(get_db)):
    row = Menu(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@router.put("/{item_id}", response_model=MenuOut, dependencies=[Depends(require_roles("nutriologo", "admin"))])
def update_item(item_id: int, payload: MenuIn, db: Session = Depends(get_db)):
    row = db.query(Menu).filter(Menu.id == item_id).first()
    if not row:
        raise HTTPException(status_code=404, detail="Menú no encontrado")
    for key, value in payload.model_dump().items():
        setattr(row, key, value)
    db.commit()
    db.refresh(row)
    return row
