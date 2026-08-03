const { getDefaultConfig } = require('expo/metro-config');
const { createProxyMiddleware } = require('http-proxy-middleware');

/** @type {import('expo/metro-config').MetroConfig} */
const config = getDefaultConfig(__dirname);

const FASTAPI_DEV_TARGET = process.env.NUTRIKIDS_FASTAPI_PROXY_TARGET ?? 'http://127.0.0.1:8000';
const API_PROXY_PREFIX = '/api-proxy';

// Reanimated 4 / Worklets requiere inlineRequires para evitar carga eager que provoca
// SIGSEGV nativo en libworklets.so al iniciar (Expo Go + Hermes).
config.transformer.getTransformOptions = async () => ({
  transform: {
    inlineRequires: true,
  },
});

/**
 * En desarrollo web, las peticiones van al mismo origen (Expo) y Metro las reenvía a FastAPI.
 * Evita errores de CORS / "Sin conexión con el servidor" en el navegador.
 */
config.server = {
  ...config.server,
  enhanceMiddleware: (metroMiddleware) => {
    const apiProxy = createProxyMiddleware({
      target: FASTAPI_DEV_TARGET,
      changeOrigin: true,
      pathRewrite: (path) => path.replace(new RegExp(`^${API_PROXY_PREFIX}`), ''),
      logLevel: 'warn',
    });

    return (req, res, next) => {
      if (req.url?.startsWith(API_PROXY_PREFIX)) {
        return apiProxy(req, res, next);
      }
      return metroMiddleware(req, res, next);
    };
  },
};

module.exports = config;
