import type {
  ProgressionEventHandler,
  ProgressionEventPayload,
  ProgressionEventType,
} from '../types/events.types';

type Handler = (payload: ProgressionEventPayload<ProgressionEventType>) => void;

class ProgressionEventBus {
  private listeners = new Map<ProgressionEventType, Set<Handler>>();

  on<T extends ProgressionEventType>(event: T, handler: ProgressionEventHandler<T>): () => void {
    const set = this.listeners.get(event) ?? new Set<Handler>();
    const wrapped: Handler = (payload) => handler(payload as ProgressionEventPayload<T>);
    set.add(wrapped);
    this.listeners.set(event, set);
    return () => set.delete(wrapped);
  }

  emit<T extends ProgressionEventType>(event: T, payload: ProgressionEventPayload<T>): void {
    const set = this.listeners.get(event);
    set?.forEach((handler) => handler(payload));
  }

  clear(): void {
    this.listeners.clear();
  }
}

export const progressionEventBus = new ProgressionEventBus();
