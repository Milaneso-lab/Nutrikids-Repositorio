import React, { useMemo } from 'react';
import { Dimensions, Pressable, Text, View } from 'react-native';

import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';

export interface CalendarDay {
  date: string;
  completed: number;
  total: number;
  hasActivity: boolean;
}

interface ProgressCalendarProps {
  year: number;
  month: number;
  days: CalendarDay[];
  onDayPress?: (date: string) => void;
}

const WEEKDAYS = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
const GRID_GAP = 4;

export function ProgressCalendar({ year, month, days, onDayPress }: ProgressCalendarProps): React.JSX.Element {
  const { colors } = useKidTheme();
  const styles = useThemedKidStyles((t) => {
    const horizontalPadding = t.spacing.md * 2;
    const screenWidth = Dimensions.get('window').width;
    const calendarWidth = screenWidth - t.spacing.lg * 2 - horizontalPadding;
    const cellSize = Math.floor((calendarWidth - GRID_GAP * 6) / 7);

    return {
      wrapper: {
        backgroundColor: t.colors.surface,
        borderRadius: t.radii.card,
        padding: t.spacing.md,
        gap: t.spacing.sm,
        ...t.shadow.card,
      },
      monthTitle: {
        fontFamily: t.fonts.extraBold,
        fontSize: 18,
        color: t.colors.ink,
        textTransform: 'capitalize',
        textAlign: 'center',
      },
      weekRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        gap: GRID_GAP,
      },
      weekday: {
        fontFamily: t.fonts.bold,
        fontSize: 12,
        color: t.colors.inkSoft,
        textAlign: 'center',
        width: cellSize,
      },
      grid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: GRID_GAP,
      },
      cell: {
        borderRadius: 10,
        backgroundColor: t.colors.progressTrack,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 2,
        width: cellSize,
        height: cellSize + 8,
      },
      cellEmpty: {
        width: cellSize,
        height: cellSize + 8,
      },
      cellFull: {
        backgroundColor: t.colors.mint,
      },
      cellPartial: {
        backgroundColor: t.colors.sunshine,
      },
      cellToday: {
        borderWidth: 2,
        borderColor: t.colors.grape,
      },
      cellDay: {
        fontFamily: t.fonts.semiBold,
        fontSize: 13,
        color: t.colors.inkSoft,
      },
      cellDayActive: {
        color: t.colors.ink,
        fontFamily: t.fonts.bold,
      },
      activityDot: {
        width: 6,
        height: 6,
        borderRadius: 3,
        backgroundColor: t.colors.grape,
      },
      activityDotFull: {
        backgroundColor: t.colors.ink,
      },
      legend: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: t.spacing.sm,
        marginTop: t.spacing.xs,
        justifyContent: 'center',
      },
      legendItem: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
      },
      legendDot: {
        width: 10,
        height: 10,
        borderRadius: 5,
      },
      legendLabel: {
        fontFamily: t.fonts.medium,
        fontSize: 10,
        color: t.colors.inkSoft,
      },
    };
  });

  const firstDay = new Date(year, month - 1, 1).getDay();
  const offset = firstDay === 0 ? 6 : firstDay - 1;
  const monthLabel = new Date(year, month - 1).toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });

  const leading = Array.from({ length: offset }, () => null);
  const trailingCount = (7 - ((offset + days.length) % 7)) % 7;
  const trailing = Array.from({ length: trailingCount }, () => null);
  const cells: (CalendarDay | null)[] = [...leading, ...days, ...trailing];

  const legendItems = useMemo(
    () => [
      { color: colors.mint, label: '¡Día completo!' },
      { color: colors.sunshine, label: 'Con actividad' },
      { color: colors.progressTrack, label: 'Sin actividad' },
    ],
    [colors],
  );

  return (
    <View style={styles.wrapper}>
      <Text style={styles.monthTitle}>{monthLabel}</Text>
      <View style={styles.weekRow}>
        {WEEKDAYS.map((d) => (
          <Text key={d} style={styles.weekday}>
            {d}
          </Text>
        ))}
      </View>
      <View style={styles.grid}>
        {cells.map((day, index) => {
          if (!day) {
            return <View key={`empty-${index}`} style={styles.cellEmpty} />;
          }

          const dayNum = parseInt(day.date.slice(8, 10), 10);
          const full = day.total > 0 && day.completed >= day.total;
          const partial = day.hasActivity && !full;
          const isToday = day.date === new Date().toISOString().slice(0, 10);

          return (
            <Pressable
              key={day.date}
              accessibilityRole="button"
              accessibilityLabel={`Día ${dayNum}, ${day.completed} hábitos completados`}
              onPress={() => onDayPress?.(day.date)}
              style={[
                styles.cell,
                full && styles.cellFull,
                partial && styles.cellPartial,
                isToday && styles.cellToday,
              ]}
            >
              <Text style={[styles.cellDay, (full || partial) && styles.cellDayActive]}>{dayNum}</Text>
              {day.hasActivity ? <View style={[styles.activityDot, full && styles.activityDotFull]} /> : null}
            </Pressable>
          );
        })}
      </View>
      <View style={styles.legend}>
        {legendItems.map((item) => (
          <View key={item.label} style={styles.legendItem}>
            <View style={[styles.legendDot, { backgroundColor: item.color }]} />
            <Text style={styles.legendLabel}>{item.label}</Text>
          </View>
        ))}
      </View>
    </View>
  );
}
