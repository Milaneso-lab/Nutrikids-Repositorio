export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

export interface ApiRequestConfig {
  params?: Record<string, string | number | boolean | undefined>;
  headers?: Record<string, string>;
  signal?: AbortSignal;
}

/** Formato paginado FastAPI v1 (`PaginatedResponse`). */
export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    page: number;
    per_page: number;
    total: number;
    total_pages: number;
  };
}

/** @deprecated Usar PaginatedResponse para la API v1. */
export interface ApiListResponse<T> {
  items: T[];
  total: number;
  page: number;
  pageSize: number;
}

export interface ApiMessageResponse {
  message: string;
}

export type TokenProvider = () => Promise<string | null>;

export type UnauthorizedHandler = () => void | Promise<void>;
