const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem("admin_token");
}

async function request<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const token = getToken();

  const res = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options.headers ?? {}),
    },
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({ message: "Erro desconhecido" }));
    throw Object.assign(new Error(error.message ?? "Erro na requisição"), {
      status: res.status,
      errors: error.errors,
    });
  }

  return res.json() as Promise<T>;
}

export const api = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, body?: unknown) =>
    request<T>(path, { method: "POST", body: JSON.stringify(body) }),
  put: <T>(path: string, body?: unknown) =>
    request<T>(path, { method: "PUT", body: JSON.stringify(body) }),
  patch: <T>(path: string, body?: unknown) =>
    request<T>(path, { method: "PATCH", body: JSON.stringify(body) }),
  delete: <T>(path: string) => request<T>(path, { method: "DELETE" }),
};

// ─── Auth ────────────────────────────────────────────────────────────────────
export const authApi = {
  login: (email: string, password: string) =>
    api.post<{ token: string; user: AdminUser }>("/auth/login", { email, password }),
  logout: () => api.post("/auth/logout"),
  profile: () => api.get<{ user: AdminUser }>("/auth/profile"),
};

// ─── Dashboard ───────────────────────────────────────────────────────────────
export const dashboardApi = {
  summary: (from?: string, to?: string) => {
    const q = from ? `?from=${from}&to=${to ?? ""}` : "";
    return api.get<{ data: SummaryData }>(`/admin/reports/summary${q}`);
  },
};

// ─── Products ────────────────────────────────────────────────────────────────
export const productsApi = {
  list: (params?: Record<string, string | number>) => {
    const q = params ? "?" + new URLSearchParams(params as Record<string, string>).toString() : "";
    return api.get<PaginatedResponse<Product>>(`/admin/products${q}`);
  },
  get: (id: number) => api.get<{ data: Product }>(`/admin/products/${id}`),
  create: (data: unknown) => api.post<{ data: Product; message: string }>("/admin/products", data),
  update: (id: number, data: unknown) =>
    api.put<{ data: Product; message: string }>(`/admin/products/${id}`, data),
  delete: (id: number) => api.delete<{ message: string }>(`/admin/products/${id}`),
  adjustStock: (id: number, delta: number, skuId?: number) =>
    api.patch(`/admin/products/${id}/stock`, { delta, product_sku_id: skuId ?? null }),
};

// ─── Orders ──────────────────────────────────────────────────────────────────
export const ordersApi = {
  list: (params?: Record<string, string | number>) => {
    const q = params ? "?" + new URLSearchParams(params as Record<string, string>).toString() : "";
    return api.get<PaginatedResponse<Order>>(`/admin/orders${q}`);
  },
  get: (id: number) => api.get<{ data: Order }>(`/admin/orders/${id}`),
  updateStatus: (id: number, data: { status: string; tracking_code?: string; carrier?: string; comment?: string }) =>
    api.patch<{ data: Order; message: string }>(`/admin/orders/${id}/status`, data),
};

// ─── Users ───────────────────────────────────────────────────────────────────
export const usersApi = {
  list: (params?: Record<string, string | number>) => {
    const q = params ? "?" + new URLSearchParams(params as Record<string, string>).toString() : "";
    return api.get<PaginatedResponse<AdminUser>>(`/admin/users${q}`);
  },
  get: (id: number) => api.get<{ data: AdminUser }>(`/admin/users/${id}`),
  toggleActive: (id: number) =>
    api.patch<{ data: AdminUser; message: string }>(`/admin/users/${id}/toggle-active`),
};

// ─── Types ───────────────────────────────────────────────────────────────────
export interface AdminUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  cpf: string | null;
  is_active: boolean;
  is_admin: boolean;
  created_at: string;
  addresses?: Address[];
}

export interface Address {
  id: number;
  type: string;
  street: string;
  number: string;
  complement: string | null;
  neighborhood: string;
  city: string;
  state: string;
  postal_code: string;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  description: string;
  short_description: string | null;
  price: string;
  cost_price: string | null;
  sku: string;
  quantity: number;
  min_quantity_alert: number;
  is_active: boolean;
  category: { id: number; name: string };
  skus?: ProductSku[];
}

export interface ProductSku {
  id: number;
  sku: string;
  price: string;
  quantity: number;
  is_active: boolean;
}

export interface Order {
  id: number;
  number: string;
  status: string;
  subtotal: string;
  shipping: string;
  discount: string;
  total: string;
  created_at: string;
  user?: AdminUser;
  items?: OrderItem[];
  payment?: Payment;
  timeline?: StatusHistory[];
}

export interface OrderItem {
  id: number;
  product_name: string;
  quantity: number;
  unit_price: string;
  subtotal: string;
}

export interface Payment {
  id: number;
  method: string;
  status: string;
  amount: string;
}

export interface StatusHistory {
  status: string;
  comment: string | null;
  created_at: string;
}

export interface SummaryData {
  period: { from: string; to: string };
  total_orders: number;
  total_revenue: number;
  average_order_value: number;
  orders_by_status: Record<string, number>;
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}
