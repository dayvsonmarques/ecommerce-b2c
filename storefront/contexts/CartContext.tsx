"use client";

import { cartApi, type Cart, type CartItem, type Product } from "@/lib/api";
import { createContext, useCallback, useContext, useEffect, useRef, useState, type PropsWithChildren } from "react";
import { useAuth } from "./AuthContext";

const LOCAL_KEY = "guest_cart";

function loadLocal(): CartItem[] {
  if (typeof window === "undefined") return [];
  try { return JSON.parse(localStorage.getItem(LOCAL_KEY) ?? "[]"); } catch { return []; }
}
function saveLocal(items: CartItem[]) {
  localStorage.setItem(LOCAL_KEY, JSON.stringify(items));
}
function clearLocal() {
  localStorage.removeItem(LOCAL_KEY);
}
function localId(productId: number, skuId?: number) {
  return productId * 10000 + (skuId ?? 0);
}
function buildLocalCart(items: CartItem[]): Cart {
  const subtotal = items.reduce((s, i) => s + Number(i.unit_price) * i.quantity, 0);
  return { id: 0, items, subtotal: subtotal.toFixed(2), discount: "0.00", shipping: "0.00", total: subtotal.toFixed(2), coupon_code: null };
}

interface CartContextValue {
  cart: Cart | null;
  itemCount: number;
  loading: boolean;
  addItem: (productId: number, quantity: number, skuId?: number, product?: Product) => Promise<void>;
  updateItem: (id: number, quantity: number) => Promise<void>;
  removeItem: (id: number) => Promise<void>;
  clearCart: () => Promise<void>;
  applyCoupon: (code: string) => Promise<void>;
  refresh: () => Promise<void>;
}

const CartContext = createContext<CartContextValue>({
  cart: null, itemCount: 0, loading: false,
  addItem: async () => {}, updateItem: async () => {}, removeItem: async () => {},
  clearCart: async () => {}, applyCoupon: async () => {}, refresh: async () => {},
});

export function CartProvider({ children }: PropsWithChildren) {
  const { user } = useAuth();
  const [serverCart, setServerCart] = useState<Cart | null>(null);
  const [localItems, setLocalItems] = useState<CartItem[]>([]);
  const [loading, setLoading] = useState(false);
  const mergedRef = useRef(false);

  // Sync local state on mount
  useEffect(() => {
    setLocalItems(loadLocal());
  }, []);

  const refresh = useCallback(async () => {
    if (!user) return;
    setLoading(true);
    cartApi.get().then((r) => setServerCart(r.data)).finally(() => setLoading(false));
  }, [user]);

  // When user logs in: merge local cart into server, then refresh
  useEffect(() => {
    if (user && !mergedRef.current) {
      mergedRef.current = true;
      const local = loadLocal();
      if (local.length > 0) {
        const mergePromises = local.map((item) =>
          cartApi.addItem(item.product_id, item.quantity, item.product_sku_id ?? undefined).catch(() => {})
        );
        Promise.all(mergePromises).then(() => { clearLocal(); setLocalItems([]); refresh(); });
      } else {
        refresh();
      }
    }
    if (!user) {
      mergedRef.current = false;
      setServerCart(null);
      setLocalItems(loadLocal());
    }
  }, [user, refresh]);

  const addItem = useCallback(async (productId: number, quantity: number, skuId?: number, product?: Product) => {
    if (user) {
      await cartApi.addItem(productId, quantity, skuId);
      await refresh();
      return;
    }
    // Guest: update localStorage
    const id = localId(productId, skuId);
    setLocalItems((prev) => {
      const existing = prev.find((i) => i.id === id);
      let updated: CartItem[];
      if (existing) {
        updated = prev.map((i) => i.id === id
          ? { ...i, quantity: i.quantity + quantity, subtotal: (Number(i.unit_price) * (i.quantity + quantity)).toFixed(2) }
          : i
        );
      } else {
        const price = product ? String(product.price) : "0";
        const newItem: CartItem = {
          id,
          product_id: productId,
          product_sku_id: skuId ?? null,
          quantity,
          unit_price: price,
          subtotal: (Number(price) * quantity).toFixed(2),
          product: product ?? ({ id: productId, name: "Produto", slug: "", price, quantity: 99, is_active: true, description: "", short_description: null, category: { id: 0, name: "" } } as Product),
        };
        updated = [...prev, newItem];
      }
      saveLocal(updated);
      return updated;
    });
  }, [user, refresh]);

  const updateItem = useCallback(async (id: number, quantity: number) => {
    if (user) { await cartApi.updateItem(id, quantity); await refresh(); return; }
    setLocalItems((prev) => {
      const updated = prev.map((i) => i.id === id
        ? { ...i, quantity, subtotal: (Number(i.unit_price) * quantity).toFixed(2) }
        : i
      );
      saveLocal(updated);
      return updated;
    });
  }, [user, refresh]);

  const removeItem = useCallback(async (id: number) => {
    if (user) { await cartApi.removeItem(id); await refresh(); return; }
    setLocalItems((prev) => { const u = prev.filter((i) => i.id !== id); saveLocal(u); return u; });
  }, [user, refresh]);

  const clearCart = useCallback(async () => {
    if (user) { await cartApi.clear(); setServerCart(null); return; }
    clearLocal();
    setLocalItems([]);
  }, [user]);

  const applyCoupon = useCallback(async (code: string) => {
    const r = await cartApi.applyCoupon(code);
    setServerCart(r.data);
  }, []);

  const cart = user ? serverCart : (localItems.length > 0 ? buildLocalCart(localItems) : null);
  const itemCount = cart?.items.reduce((s, i) => s + i.quantity, 0) ?? 0;

  return (
    <CartContext.Provider value={{ cart, itemCount, loading, addItem, updateItem, removeItem, clearCart, applyCoupon, refresh }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() { return useContext(CartContext); }
