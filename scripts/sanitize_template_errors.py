"""Reemplaza bloques catch técnicos por NutriKidsMessages.fromCatch en plantillas web."""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DIRS = [ROOT / "flask" / "templates", ROOT / "resources" / "views"]

CATCH_BLOCK = re.compile(
    r"let displayError = 'No se pudo completar la acción\. Inténtalo de nuevo\.';\s*"
    r".*?"
    r"mensajeError\.textContent = displayError;",
    re.DOTALL,
)

REPLACEMENT = (
    "mensajeError.textContent = (window.NutriKidsMessages "
    "? NutriKidsMessages.fromCatch(error) "
    ": 'No se pudo completar la acción. Inténtalo de nuevo.');"
)

HTTP_THROW = re.compile(
    r"throw new Error\(`HTTP error! status: \$\{response\.status\}`\);"
    r"|throw new Error\('HTTP error! status: ' \+ response\.status\);"
    r"|throw new Error\('HTTP ' \+ response\.status\);"
)

HTTP_THROW_REPL = "throw { success: false };"

ERR_MSG_LINE = re.compile(
    r"if \(err && err\.message\) msg = err\.message;"
    r"|mensajeError\.textContent = 'Error';\s*// fallback"
)

ERR_MSG_REPL = (
    "msg = (window.NutriKidsMessages ? NutriKidsMessages.fromCatch(err) "
    ": 'No se pudo completar la acción. Inténtalo de nuevo.');"
)


def main() -> None:
    updated = 0
    for base in DIRS:
        if not base.exists():
            continue
        for path in base.rglob("*"):
            if path.suffix not in {".html", ".php"}:
                continue
            text = path.read_text(encoding="utf-8")
            orig = text
            text = CATCH_BLOCK.sub(REPLACEMENT, text)
            text = HTTP_THROW.sub(HTTP_THROW_REPL, text)
            text = text.replace(
                "if (err && err.message) msg = err.message;",
                ERR_MSG_REPL,
            )
            text = text.replace(
                "displayError = error.message;",
                "displayError = (window.NutriKidsMessages && NutriKidsMessages.isUserMessage(error.message)) ? error.message : (window.NutriKidsMessages ? NutriKidsMessages.GENERIC : 'No se pudo completar la acción. Inténtalo de nuevo.');",
            )
            if text != orig:
                path.write_text(text, encoding="utf-8")
                updated += 1
                print(path.relative_to(ROOT))
    print(f"Updated {updated} files")


if __name__ == "__main__":
    main()
