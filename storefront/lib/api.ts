const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem("token");
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
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
  register: (data: RegisterData) => api.post<AuthResponse>("/auth/register", data),
  login: (email: string, password: string) =>
    api.post<AuthResponse>("/auth/login", { email, password }),
  logout: () => api.post("/auth/logout"),
  profile: () => api.get<{ user: User }>("/auth/profile"),
  updateProfile: (data: Partial<User & { current_password?: string; password?: string; password_confirmation?: string }>) =>
    api.put<{ user: User; message: string }>("/auth/profile", data),
};

// ─── Catalog ─────────────────────────────────────────────────────────────────
export const catalogApi = {
  categories: () => api.get<{ data: Category[] }>("/categories"),
  category: (slug: string) => api.get<{ data: Category }>(`/categories/${slug}`),
  products: (params?: Record<string, string | number>) => {
    const q = params ? "?" + new URLSearchParams(params as Record<string, string>).toString() : "";
    return api.get<PaginatedResponse<Product>>(`/products${q}`);
  },
  product: (slug: string) => api.get<{ data: Product }>(`/products/${slug}`),
  related: (slug: string) => api.get<{ data: Product[] }>(`/products/${slug}/related`),
};

// ─── Cart ────────────────────────────────────────────────────────────────────
export const cartApi = {
  get: () => api.get<{ data: Cart }>("/cart"),
  addItem: (productId: number, quantity: number, skuId?: number) =>
    api.post<{ data: CartItem; message: string }>("/cart/items", {
      product_id: productId,
      quantity,
      product_sku_id: skuId ?? null,
    }),
  updateItem: (id: number, quantity: number) =>
    api.put<{ data: CartItem; message: string }>(`/cart/items/${id}`, { quantity }),
  removeItem: (id: number) => api.delete<{ message: string }>(`/cart/items/${id}`),
  clear: () => api.post<{ message: string }>("/cart/clear"),
  applyCoupon: (coupon_code: string) =>
    api.post<{ data: Cart; message: string }>("/cart/coupon", { coupon_code }),
  estimateShipping: (postal_code: string) =>
    api.post<{ data: { shipping_cost: string } }>("/cart/shipping", { postal_code }),
};

// ─── Orders ──────────────────────────────────────────────────────────────────
export const ordersApi = {
  list: (page = 1) => api.get<PaginatedResponse<Order>>(`/orders?per_page=10&page=${page}`),
  get: (id: number) => api.get<{ data: Order }>(`/orders/${id}`),
  create: (data: CreateOrderData) => api.post<{ data: Order; message: string }>("/orders", data),
  cancel: (id: number, reason?: string) =>
    api.post<{ data: Order; message: string }>(`/orders/${id}/cancel`, { reason }),
  pay: (id: number, method: string, cardToken?: string, installments?: number) =>
    api.post<{ data: Payment; message: string }>(`/orders/${id}/pay`, {
      method,
      card_token: cardToken,
      installments,
    }),
};

// ─── Addresses ───────────────────────────────────────────────────────────────
export const addressesApi = {
  list: () => api.get<{ data: Address[] }>("/addresses"),
  create: (data: Omit<Address, "id">) =>
    api.post<{ data: Address; message: string }>("/addresses", data),
  update: (id: number, data: Partial<Address>) =>
    api.put<{ data: Address; message: string }>(`/addresses/${id}`, data),
  delete: (id: number) => api.delete<{ message: string }>(`/addresses/${id}`),
};

// ─── Types ───────────────────────────────────────────────────────────────────
export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  cpf?: string;
}

export interface AuthResponse {
  message: string;
  user: User;
  token: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  cpf?: string | null;
  is_active: boolean;
  is_admin?: boolean;
  created_at: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image_url: string | null;
  children?: Category[];
  children_count?: number;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  description: string;
  short_description: string | null;
  price: string;
  quantity: number;
  is_active: boolean;
  category: { id: number; name: string };
  variations?: Variation[];
  skus?: Sku[];
}

export interface Variation {
  id: number;
  name: string;
  values: { id: number; value: string; label: string | null }[];
}

export interface Sku {
  id: number;
  sku: string;
  price: string;
  quantity: number;
  is_active: boolean;
}

export interface Cart {
  id: number;
  items: CartItem[];
  subtotal: string;
  discount: string;
  shipping: string;
  total: string;
  coupon_code: string | null;
}

export interface CartItem {
  id: number;
  product_id: number;
  product_sku_id: number | null;
  quantity: number;
  unit_price: string;
  subtotal: string;
  product: Product;
}

export interface Address {
  id: number;
  type: "billing" | "shipping";
  street: string;
  number: string;
  complement: string | null;
  neighborhood: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  is_default: boolean;
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
  items?: OrderItem[];
  payment?: Payment;
  timeline?: { status: string; created_at: string; comment?: string }[];
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
  pix_qr_code?: string;
  pix_qr_code_text?: string;
  pix_expires_at?: string;
  boleto_url?: string;
  boleto_barcode?: string;
  boleto_expires_at?: string;
  gateway_payment_id?: string;
}

export interface CreateOrderData {
  shipping_address_id: number;
  payment_method: string;
  card_token?: string;
  installments?: number;
  notes?: string;
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
