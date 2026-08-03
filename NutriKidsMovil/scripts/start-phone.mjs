/**
 * Arranque Expo para celular (sin depender de npm.ps1 / ExecutionPolicy).
 * Uso: node scripts/start-phone.mjs
 *      node scripts/start-phone.mjs --tunnel
 */
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import net from 'node:net';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const useTunnel = process.argv.includes('--tunnel');
const useClear = process.argv.includes('--clear');

function getWifiIPv4() {
  const nets = os.networkInterfaces();
  for (const name of Object.keys(nets)) {
    if (!/wi-fi|wlan|wireless/i.test(name)) {
      continue;
    }
    for (const net of nets[name] ?? []) {
      if (net.family === 'IPv4' && !net.internal && !net.address.startsWith('169.254.')) {
        return net.address;
      }
    }
  }
  for (const name of Object.keys(nets)) {
    for (const net of nets[name] ?? []) {
      if (net.family === 'IPv4' && !net.internal && !net.address.startsWith('169.254.')) {
        return net.address;
      }
    }
  }
  return null;
}

function updateEnvFile(lanIp) {
  const envPath = path.join(projectRoot, '.env');
  if (!fs.existsSync(envPath) || !lanIp) {
    return;
  }
  const content = fs.readFileSync(envPath, 'utf8');
  const updated = content.replace(
    /EXPO_PUBLIC_API_BASE_URL=.*/g,
    `EXPO_PUBLIC_API_BASE_URL=http://${lanIp}:8000`,
  );
  fs.writeFileSync(envPath, updated.endsWith('\n') ? updated : `${updated}\n`);
}

function readEnvFlag(name) {
  const envPath = path.join(projectRoot, '.env');
  if (!fs.existsSync(envPath)) {
    return null;
  }
  const match = fs.readFileSync(envPath, 'utf8').match(new RegExp(`^${name}=(.+)$`, 'm'));
  if (!match) {
    return null;
  }
  const value = match[1].trim();
  if (value === 'true' || value === '1') {
    return true;
  }
  if (value === 'false' || value === '0') {
    return false;
  }
  return null;
}

function isPortAvailable(port) {
  return new Promise((resolve) => {
    const socket = net.createConnection({ port, host: '127.0.0.1' });
    const finish = (available) => {
      socket.removeAllListeners();
      socket.destroy();
      resolve(available);
    };

    socket.setTimeout(400);
    socket.once('connect', () => finish(false));
    socket.once('timeout', () => finish(true));
    socket.once('error', () => finish(true));
  });
}

async function resolveMetroPort() {
  const preferred = Number(process.env.EXPO_METRO_PORT ?? 8081);
  if (await isPortAvailable(preferred)) {
    return preferred;
  }
  for (const port of [8082, 8083, 19000, 19001]) {
    if (await isPortAvailable(port)) {
      console.warn(`Puerto ${preferred} ocupado. Usando ${port} para Metro.\n`);
      return port;
    }
  }
  return preferred;
}

const lanIp = getWifiIPv4();
const metroPort = await resolveMetroPort();

if (!useTunnel && lanIp) {
  process.env.REACT_NATIVE_PACKAGER_HOSTNAME = lanIp;
  updateEnvFile(lanIp);
  console.log('\n=== NutriKids — Expo Go (celular) ===');
  console.log(`IP Wi-Fi: ${lanIp}`);
  console.log(`URL manual: exp://${lanIp}:${metroPort}`);
  console.log(`API: http://${lanIp}:8000/api/v1`);
  console.log('(No uses localhost en el teléfono)\n');
  console.log('Login contra PostgreSQL (FastAPI :8000).');
  console.log('Prueba: padre@nutrikids.com / Padre123*\n');
  console.log('Navegador en esta PC: npm run start:web:api (usa localhost:8000).');
  console.log('Celular: este script (IP Wi-Fi en .env).\n');
  if (!useClear) {
    console.log('Si la app no refleja cambios, reinicia con: node scripts/start-phone.mjs --clear\n');
  }
} else if (!useTunnel) {
  console.warn('No se detectó IP Wi-Fi. Usa: node scripts/start-phone.mjs --tunnel\n');
}

const expoArgs = ['start', useTunnel ? '--tunnel' : '--lan', '--port', String(metroPort)];
if (useClear) {
  expoArgs.push('--clear');
}

/**
 * En Windows, spawn('npx.cmd', …, { shell: false }) falla con EINVAL (Node 20+),
 * sobre todo con rutas que contienen espacios. Usamos el binario local de Expo.
 */
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

const child = spawnExpo(expoArgs);

child.on('error', (err) => {
  console.error('\nNo se pudo iniciar Expo:', err.message);
  console.error('Prueba: cd NutriKidsMovil && npm install && npm run start:api');
  console.error('Alternativa: npx expo start --lan --clear\n');
  process.exit(1);
});

child.on('exit', (code) => process.exit(code ?? 0));
