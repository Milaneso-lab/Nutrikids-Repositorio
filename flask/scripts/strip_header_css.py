"""Elimina CSS inline duplicado del header en plantillas públicas."""
import re
from pathlib import Path

TEMPLATES = Path(__file__).resolve().parent.parent / "templates"
FILES = [
    "calculadora.html",
    "nutriologos.html",
    "Comentarios.html",
    "Foros.html",
    "conocenos.html",
]


def strip_header_css(text: str) -> str:
    start = text.find(".encabezado-container")
    if start == -1:
        return text
    style_start = text.rfind("<style>", 0, start)
    marker = re.search(r"/\* Estilos para", text[start:])
    if not marker:
        return text
    end = start + marker.start()
    return text[: style_start + len("<style>\n")] + text[end:]


for name in FILES:
    path = TEMPLATES / name
    original = path.read_text(encoding="utf-8")
    updated = strip_header_css(original)
    path.write_text(updated, encoding="utf-8")
    print(name, "changed" if updated != original else "unchanged")
