from datetime import date, datetime, timezone

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import or_
from sqlalchemy.exc import SQLAlchemyError
from sqlalchemy.orm import Session

from database import get_db
from deps import get_current_user, require_roles
from models import Cita, Usuario
from schemas import CitaAsignarIn, CitaCreateIn, CitaEstadoIn, CitaOut

router = APIRouter(prefix="/citas", tags=["citas"])


def _utc_now_naive() -> datetime:
    """PostgreSQL guarda timestamp sin zona; Laravel no pone DEFAULT en created_at/updated_at."""
    return datetime.now(timezone.utc).replace(tzinfo=None)


def enrich_cita(row: Cita, db: Session) -> CitaOut:
    padre = db.query(Usuario).filter(Usuario.id_usuario == row.id_padre).first()
    nut = (
        db.query(Usuario).filter(Usuario.id_usuario == row.id_nutriologo).first()
        if row.id_nutriologo
        else None
    )
    pn = f"{padre.nombre} {padre.apellido_paterno}".strip() if padre else None
    nn = f"{nut.nombre} {nut.apellido_paterno}".strip() if nut else None
    ca = row.created_at or _utc_now_naive()
    ua = row.updated_at or row.created_at or _utc_now_naive()
    return CitaOut(
        id=row.id,
        id_padre=row.id_padre,
        id_nutriologo=row.id_nutriologo,
        fecha_preferida=row.fecha_preferida,
        franja=row.franja,
        telefono=row.telefono,
        mensaje=row.mensaje,
        estado=row.estado,
        created_at=ca,
        updated_at=ua,
        padre_nombre=pn,
        nutriologo_nombre=nn,
    )


@router.post("", response_model=CitaOut)
def crear_cita(
    payload: CitaCreateIn,
    user: Usuario = Depends(get_current_user),
    db: Session = Depends(get_db),
):
    if user.rol != "padre":
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Solo padres pueden solicitar citas",
        )
    if payload.fecha_preferida < date.today():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="La fecha preferida debe ser hoy o una fecha futura",
        )
    now = _utc_now_naive()
    row = Cita(
        id_padre=user.id_usuario,
        fecha_preferida=payload.fecha_preferida,
        franja=payload.franja,
        telefono=payload.telefono,
        mensaje=payload.mensaje,
        estado="pendiente",
        created_at=now,
        updated_at=now,
    )
    try:
        db.add(row)
        db.commit()
        db.refresh(row)
    except SQLAlchemyError as exc:
        db.rollback()
        err = str(exc.orig) if hasattr(exc, "orig") and exc.orig else str(exc)
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail=(
                "No se pudo guardar la cita. Si acabas de desplegar el proyecto, ejecuta "
                "`php artisan migrate` en Laravel (debe existir id_padre, fecha_preferida, etc. en la tabla citas). "
                f"Detalle técnico: {err[:400]}"
            ),
        ) from exc
    return enrich_cita(row, db)


@router.get("", response_model=list[CitaOut])
def listar_citas(user: Usuario = Depends(get_current_user), db: Session = Depends(get_db)):
    if user.rol == "admin":
        rows = db.query(Cita).order_by(Cita.id.desc()).all()
    elif user.rol == "nutriologo":
        rows = (
            db.query(Cita)
            .filter(
                or_(
                    Cita.id_nutriologo == user.id_usuario,
                    (Cita.estado == "pendiente") & (Cita.id_nutriologo.is_(None)),
                )
            )
            .order_by(Cita.id.desc())
            .all()
        )
    else:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="No autorizado")
    return [enrich_cita(r, db) for r in rows]


@router.patch("/{cita_id}/asignar", response_model=CitaOut)
def asignar_cita(
    cita_id: int,
    body: CitaAsignarIn,
    _user: Usuario = Depends(require_roles("admin")),
    db: Session = Depends(get_db),
):
    row = db.query(Cita).filter(Cita.id == cita_id).first()
    if not row:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Cita no encontrada")
    target = db.query(Usuario).filter(Usuario.id_usuario == body.id_nutriologo).first()
    if not target or target.rol != "nutriologo":
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Debe indicar un usuario con rol nutriólogo",
        )
    row.id_nutriologo = body.id_nutriologo
    row.estado = "asignada"
    db.commit()
    db.refresh(row)
    return enrich_cita(row, db)


@router.post("/{cita_id}/tomar", response_model=CitaOut)
def tomar_cita(
    cita_id: int,
    user: Usuario = Depends(require_roles("nutriologo")),
    db: Session = Depends(get_db),
):
    row = db.query(Cita).filter(Cita.id == cita_id).first()
    if not row:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Cita no encontrada")
    if row.estado != "pendiente" or row.id_nutriologo is not None:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Esta cita ya no está disponible para tomar",
        )
    row.id_nutriologo = user.id_usuario
    row.estado = "asignada"
    db.commit()
    db.refresh(row)
    return enrich_cita(row, db)


@router.patch("/{cita_id}/estado", response_model=CitaOut)
def cambiar_estado_cita(
    cita_id: int,
    body: CitaEstadoIn,
    _user: Usuario = Depends(require_roles("admin")),
    db: Session = Depends(get_db),
):
    row = db.query(Cita).filter(Cita.id == cita_id).first()
    if not row:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Cita no encontrada")
    row.estado = body.estado
    db.commit()
    db.refresh(row)
    return enrich_cita(row, db)
