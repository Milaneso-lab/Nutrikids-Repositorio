import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

const WATERMARKS: Array<{ emoji: string; top: `${number}%`; left: `${number}%`; size: number; rotate: string }> = [
  { emoji: '🥦', top: '6%', left: '4%', size: 56, rotate: '-18deg' },
  { emoji: '🍊', top: '12%', left: '78%', size: 64, rotate: '14deg' },
  { emoji: '🥕', top: '38%', left: '-2%', size: 72, rotate: '22deg' },
  { emoji: '🍎', top: '52%', left: '82%', size: 68, rotate: '-12deg' },
  { emoji: '🌟', top: '72%', left: '8%', size: 52, rotate: '8deg' },
  { emoji: '💧', top: '78%', left: '70%', size: 48, rotate: '-20deg' },
  { emoji: '🏃', top: '28%', left: '88%', size: 44, rotate: '16deg' },
  { emoji: '🥗', top: '88%', left: '42%', size: 60, rotate: '-8deg' },
];

export function LoginScreenBackground(): React.JSX.Element {
  return (
    <View style={StyleSheet.absoluteFill} pointerEvents="none">
      <LinearGradient
        colors={['#0D47A1', '#2E7D32', '#43A047', '#F9A825', '#FF9800']}
        locations={[0, 0.35, 0.55, 0.82, 1]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={StyleSheet.absoluteFill}
      />
      <LinearGradient
        colors={['rgba(255,255,255,0.18)', 'rgba(255,255,255,0.05)', 'rgba(13,71,161,0.25)']}
        start={{ x: 0.2, y: 0 }}
        end={{ x: 0.8, y: 1 }}
        style={StyleSheet.absoluteFill}
      />

      <View style={[styles.orb, styles.orbGreen]} />
      <View style={[styles.orb, styles.orbOrange]} />
      <View style={[styles.orb, styles.orbBlue]} />
      <View style={[styles.orb, styles.orbYellow]} />

      {WATERMARKS.map((item) => (
        <Text
          key={`${item.emoji}-${item.top}-${item.left}`}
          style={[
            styles.watermark,
            {
              top: item.top,
              left: item.left,
              fontSize: item.size,
              transform: [{ rotate: item.rotate }],
            },
          ]}
        >
          {item.emoji}
        </Text>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  orb: {
    position: 'absolute',
    borderRadius: 999,
  },
  orbGreen: {
    width: 280,
    height: 280,
    top: -80,
    right: -60,
    backgroundColor: 'rgba(129, 199, 132, 0.45)',
  },
  orbOrange: {
    width: 220,
    height: 220,
    bottom: 120,
    left: -70,
    backgroundColor: 'rgba(255, 183, 77, 0.4)',
  },
  orbBlue: {
    width: 180,
    height: 180,
    top: '34%',
    right: -40,
    backgroundColor: 'rgba(100, 181, 246, 0.35)',
  },
  orbYellow: {
    width: 140,
    height: 140,
    bottom: -30,
    right: '28%',
    backgroundColor: 'rgba(255, 241, 118, 0.38)',
  },
  watermark: {
    position: 'absolute',
    opacity: 0.14,
    includeFontPadding: false,
  },
});
