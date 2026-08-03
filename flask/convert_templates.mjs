import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dir = path.join(__dirname, "templates");
const files = fs.readdirSync(dir).filter((f) => f.endsWith(".html"));

function convert(content) {
  let c = content;
  c = c.replace(/@csrf\s*/g, "");
  c = c.replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, "");
  c = c.replace(/\{\{\s*asset\('([^']*)'\)\s*\}\}/g, "{{ url_for('static', filename='$1') }}");
  const routes = [
    ["index", "index"],
    ["obesidad", "obesidad"],
    ["calculadora", "calculadora"],
    ["nutriologos", "nutriologos"],
    ["comentarios", "comentarios"],
    ["foros", "foros"],
    ["conocenos", "conocenos"],
    ["login", "login"],
    ["contacto.store", "insertar_contacto"],
    ["comentarios.index", "obtener_comentarios"],
    ["comentarios.store", "insertar_comentario"],
    ["discusiones.index", "obtener_discusiones"],
    ["discusiones.store", "insertar_discusion"],
    ["auth.login", "iniciar_sesion"],
    ["usuarios.store", "registrar_usuario"],
    ["register", "login"],
  ];
  for (const [from, to] of routes) {
    const re = new RegExp(
      `\\{\\{\\s*route\\(['"]${from}['"]\\)\\s*\\}\\}`,
      "g"
    );
    c = c.replace(re, `{{ url_for('${to}') }}`);
  }
  c = c.replace(
    /\{\{\s*route\(['"]discusiones\.update['"],\s*([^)]+)\)\s*\}\}/g,
    "{{ url_for('actualizar_discusion', id=$1) }}"
  );
  c = c.replace(
    /\{\{\s*route\(['"]discusiones\.destroy['"],\s*([^)]+)\)\s*\}\}/g,
    "{{ url_for('eliminar_discusion', id=$1) }}"
  );
  c = c.replace(
    /fetch\('\{\{ url_for\('([^']+)'\) \}\}'/g,
    "fetch({{ url_for('$1')|tojson }})"
  );
  c = c.replace(
    /fetch\("\{\{ url_for\('([^']+)'\) \}\}"/g,
    'fetch({{ url_for("$1")|tojson }})'
  );
  return c;
}

for (const f of files) {
  const p = path.join(dir, f);
  const raw = fs.readFileSync(p, "utf8");
  fs.writeFileSync(p, convert(raw), "utf8");
  console.log("converted", f);
}
