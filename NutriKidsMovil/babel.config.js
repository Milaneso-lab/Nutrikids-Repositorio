module.exports = function (api) {
  api.cache(true);
  return {
    presets: ['babel-preset-expo'],
    plugins: [
      [
        'module-resolver',
        {
          root: ['./'],
          alias: {
            '@': './src',
            '@providers': './src/providers',
            '@core': './src/core',
            '@shared': './src/shared',
            '@features': './src/features',
            '@navigation': './src/navigation',
            '@services': './src/services',
            '@state': './src/state',
          },
          extensions: ['.ts', '.tsx', '.js', '.jsx', '.json'],
        },
      ],
      // Debe ser el último plugin (Reanimated 4).
      'react-native-reanimated/plugin',
    ],
  };
};
