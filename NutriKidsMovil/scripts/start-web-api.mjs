/**
 * Arranque Expo Web apuntando a FastAPI en localhost (misma PC).
 * Uso: node scripts/start-web-api.mjs
 */
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const useClear = process.argv.includes('--clear');

function ensureWebEnv() {
  const envPath = path.join(projectRoot, '.env');
  const lines = fs.existsSync(envPath)
    ? fs.readFileSync(envPath, 'utf8').split(/\r?\n/)
    : [];

  const setLine = (key, value) => {
    const idx = lines.findIndex((line) => line.startsWith(`${key}=`));
    const next = `${key}=${value}`;
    if (idx >= 0) {
      lines[idx] = next;
    } else {
      lines.push(next);
    }
  };

  setLine('EXPO_PUBLIC_API_BASE_URL', 'http://localhost:8000');
  if (!lines.some((line) => line.startsWith('EXPO_PUBLIC_API_VERSION='))) {
    setLine('EXPO_PUBLIC_API_VERSION', 'v1');
  }

  const content = lines.filter((line, index, arr) => line.length > 0 || index < arr.length - 1).join('\n');
  fs.writeFileSync(envPath, content.endsWith('\n') ? content : `${content}\n`);
}

function spawnExpo(args) {
  const expoBinJs = path.join(projectRoot, 'node_modules', 'expo', 'bin', 'cli');
  const expoBinWin = path.join(projectRoot, 'node_modules', '.bin', 'expo.cmd');

  if (fs.existsSync(expoBinJs)) {
    return spawn(process.execPath, [expoBinJs, ...args], {
      cwd: projectRoot,
      stdio: 'inherit',
      env: process.env,
    });
  }

  if (process.platform === 'win32' && fs.existsSync(expoBinWin)) {
    return spawn(expoBinWin, args, {
      cwd: projectRoot,
      stdio: 'inherit',
      shell: true,
      env: process.env,
    });
  }

  return spawn(process.platform === 'win32' ? 'npx.cmd' : 'npx', ['expo', ...args], {
    cwd: projectRoot,
    stdio: 'inherit',
    shell: process.platform === 'win32',
    env: process.env,
  });
}

ensureWebEnv();

console.log('\n=== NutriKids — Web + API (localhost) ===');
console.log('API FastAPI: http://localhost:8000/api/v1');
console.log('Web dev usa proxy /api-proxy → sin CORS en el navegador');
console.log('Asegúrate de tener Docker en marcha: docker compose up -d fastapi\n');

const expoArgs = ['start', '--web'];
if (useClear) {
  expoArgs.push('--clear');
}

const child = spawnExpo(expoArgs);

child.on('error', (err) => {
  console.error('\nNo se pudo iniciar Expo Web:', err.message);
  process.exit(1);
});

child.on('exit', (code) => process.exit(code ?? 0));
