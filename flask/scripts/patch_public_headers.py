"""Actualiza plantillas públicas para usar public_header.html."""
import re
from pathlib import Path

TEMPLATES = Path(__file__).resolve().parent.parent / "templates"

HEADER_BLOCK = re.compile(r"\s*<header class=\"encabezado\">.*?</header>\s*", re.DOTALL)

PAGES = {
    "Obesidad.html": "obesidad",
    "calculadora.html": "calculadora",
    "nutriologos.html": "nutriologos",
    "Comentarios.html": "comentarios",
    "Foros.html": "foros",
    "conocenos.html": "conocenos",
}

HEADER_CSS_END = re.compile(
    r"(<style>\s*)"
    r"(?:/\*[^*]*\*/\s*)?"
    r"\.encabezado-container\s*\{.*?"
    r"(?=\s*/\* Estilos)",
    re.DOTALL,
)


def patch_file(name: str, active: str) -> None:
    path = TEMPLATES / name
    text = path.read_text(encoding="utf-8")

    if 'class="public-site"' not in text:
        text = text.replace("<body>", '<body class="public-site">', 1)

    text = re.sub(
        r"\s*<link rel=\"preconnect\"[^>]+>\s*"
        r"<link rel=\"preconnect\"[^>]+>\s*"
        r"<link href=\"https://fonts.googleapis.com/css2\?family=Source\+Sans\+3[^\"]+\" rel=\"stylesheet\">\s*",
        "\n",
        text,
    )

    match = HEADER_BLOCK.search(text)
    if match:
        include = f"    {{% include 'partials/public_header.html' with active_page='{active}' %}}\n"
        text = text[: match.start()] + include + text[match.end() :]

    text, n = HEADER_CSS_END.subn(r"\1", text, count=1)
    path.write_text(text, encoding="utf-8")
    print(f"{name}: header={'ok' if match else 'missing'}, css_removed={n}")


if __name__ == "__main__":
    for fname, active in PAGES.items():
        patch_file(fname, active)
