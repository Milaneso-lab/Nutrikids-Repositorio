from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from database import get_db
from deps import InMemoryRateLimiter, require_roles
from models import Contacto
from schemas import ContactoIn, ContactoOut

router = APIRouter(prefix="/contactos", tags=["contactos"])

# Limitador de tasa: Máximo 3 envíos de contacto por minuto (60s)
contacto_rate_limiter = InMemoryRateLimiter(requests_limit=3, window_seconds=60)


@router.get(
    "",
    response_model=list[ContactoOut],
    dependencies=[Depends(require_roles("admin", "nutriologo"))],
)
def list_contactos(db: Session = Depends(get_db)):
    return db.query(Contacto).order_by(Contacto.id_contacto.desc()).all()


@router.post("", response_model=ContactoOut, dependencies=[Depends(contacto_rate_limiter)])
def create_contacto(payload: ContactoIn, db: Session = Depends(get_db)):
    row = Contacto(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row

