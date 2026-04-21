"use client";

import { cartApi, type Cart } from "@/lib/api";
import { createContext, useCallback, useContext, useEffect, useState, type PropsWithChildren } from "react";
import { useAuth } from "./AuthContext";

interface CartContextValue {
  cart: Cart | null;
  itemCount: number;
  loading: boolean;
  addItem: (productId: number, quantity: number, skuId?: number) => Promise<void>;
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
  const [cart, setCart] = useState<Cart | null>(null);
  const [loading, setLoading] = useState(false);

  const refresh = useCallback(async () => {
    if (!user) return;
    setLoading(true);
    cartApi.get().then((r) => setCart(r.data)).finally(() => setLoading(false));
  }, [user]);

  useEffect(() => { if (user) refresh(); else setCart(null); }, [user, refresh]);

  const addItem = useCallback(async (productId: number, quantity: number, skuId?: number) => {
    await cartApi.addItem(productId, quantity, skuId);
    await refresh();
  }, [refresh]);

  const updateItem = useCallback(async (id: number, quantity: number) => {
    await cartApi.updateItem(id, quantity);
    await refresh();
  }, [refresh]);

  const removeItem = useCallback(async (id: number) => {
    await cartApi.removeItem(id);
    await refresh();
  }, [refresh]);

  const clearCart = useCallback(async () => {
    await cartApi.clear();
    setCart(null);
  }, []);

  const applyCoupon = useCallback(async (code: string) => {
    const r = await cartApi.applyCoupon(code);
    setCart(r.data);
  }, []);

  const itemCount = cart?.items.reduce((sum, item) => sum + item.quantity, 0) ?? 0;

  return (
    <CartContext.Provider value={{ cart, itemCount, loading, addItem, updateItem, removeItem, clearCart, applyCoupon, refresh }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() { return useContext(CartContext); }
