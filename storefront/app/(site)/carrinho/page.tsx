"use client";

import { useCart } from "@/contexts/CartContext";
import { useAuth } from "@/contexts/AuthContext";
import { cartApi } from "@/lib/api";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import toast from "react-hot-toast";

function fmt(v: string | number) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export default function CartPage() {
  const { cart, updateItem, removeItem, clearCart, applyCoupon, loading } = useCart();
  const { user } = useAuth();
  const router = useRouter();
  const [coupon, setCoupon] = useState("");
  const [postalCode, setPostalCode] = useState("");
  const [shipping, setShipping] = useState<string | null>(null);
  const [applyingCoupon, setApplyingCoupon] = useState(false);

  if (loading) {
    return <main className="pt-28 text-center text-waterloo">Carregando...</main>;
  }

  if (!cart || cart.items.length === 0) {
    return (
      <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28 text-center">
        <h1 className="mb-4 text-3xl font-bold text-black dark:text-white">Carrinho</h1>
        <p className="mb-6 text-waterloo dark:text-manatee">Seu carrinho está vazio.</p>
        <Link href="/produtos" className="rounded-full bg-primary px-6 py-3 text-white hover:bg-opacity-90">
          Continuar comprando
        </Link>
      </main>
    );
  }

  const handleApplyCoupon = async () => {
    setApplyingCoupon(true);
    try {
      await applyCoupon(coupon);
      toast.success("Cupom aplicado!");
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Cupom inválido.");
    } finally {
      setApplyingCoupon(false);
    }
  };

  const handleEstimateShipping = async () => {
    try {
      const r = await cartApi.estimateShipping(postalCode);
      setShipping(r.data.shipping_cost);
    } catch {
      toast.error("CEP inválido.");
    }
  };

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <h1 className="mb-8 text-3xl font-bold text-black dark:text-white">Carrinho</h1>

      <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {/* Items */}
        <div className="lg:col-span-2 space-y-4">
          {cart.items.map((item) => (
            <div key={item.id} className="flex items-center gap-4 rounded-xl border border-stroke bg-white p-4 dark:border-strokedark dark:bg-blacksection">
              <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-2xl dark:bg-gray-800">
                🛍️
              </div>
              <div className="flex-1">
                <Link href={`/produtos/${item.product.slug}`}>
                  <p className="font-medium text-black hover:text-primary dark:text-white">{item.product.name}</p>
                </Link>
                <p className="text-sm text-waterloo dark:text-manatee">{fmt(item.unit_price)} cada</p>
              </div>
              <div className="flex items-center gap-2 rounded-full border border-stroke dark:border-strokedark">
                <button onClick={() => item.quantity > 1 ? updateItem(item.id, item.quantity - 1) : removeItem(item.id)} className="px-3 py-1 text-lg hover:text-primary">−</button>
                <span className="w-8 text-center text-sm font-medium text-black dark:text-white">{item.quantity}</span>
                <button onClick={() => updateItem(item.id, item.quantity + 1)} className="px-3 py-1 text-lg hover:text-primary">+</button>
              </div>
              <p className="w-24 text-right font-bold text-primary">{fmt(item.subtotal)}</p>
              <button onClick={() => removeItem(item.id)} className="text-red-400 hover:text-red-600">✕</button>
            </div>
          ))}

          <button onClick={clearCart} className="text-sm text-red-400 hover:text-red-600 hover:underline">
            Limpar carrinho
          </button>
        </div>

        {/* Summary */}
        <div className="space-y-4">
          <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
            <h2 className="mb-4 font-semibold text-black dark:text-white">Resumo</h2>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between text-waterloo dark:text-manatee"><span>Subtotal</span><span>{fmt(cart.subtotal)}</span></div>
              {Number(cart.discount) > 0 && <div className="flex justify-between text-green-600"><span>Desconto</span><span>- {fmt(cart.discount)}</span></div>}
              <div className="flex justify-between text-waterloo dark:text-manatee"><span>Frete</span><span>{Number(cart.shipping) > 0 ? fmt(cart.shipping) : "A calcular"}</span></div>
              <div className="flex justify-between border-t border-stroke pt-2 font-bold text-black dark:border-strokedark dark:text-white">
                <span>Total</span><span className="text-primary">{fmt(cart.total)}</span>
              </div>
            </div>

            {/* Cupom — apenas para usuários logados */}
            {user && (
              <>
                {!cart.coupon_code && (
                  <div className="mt-4 flex gap-2">
                    <input
                      type="text" value={coupon} onChange={(e) => setCoupon(e.target.value.toUpperCase())}
                      placeholder="CUPOM"
                      className="flex-1 rounded-lg border border-stroke bg-transparent px-3 py-2 text-sm uppercase outline-none focus:border-primary dark:border-strokedark dark:text-white"
                    />
                    <button onClick={handleApplyCoupon} disabled={applyingCoupon} className="rounded-lg bg-primary px-3 py-2 text-sm text-white hover:bg-opacity-90 disabled:opacity-60">OK</button>
                  </div>
                )}
                {cart.coupon_code && <p className="mt-2 text-xs text-green-600">✓ Cupom {cart.coupon_code} aplicado</p>}
              </>
            )}

            {/* Cálculo de frete — disponível para todos */}
            <div className="mt-4 flex gap-2">
              <input
                type="text" value={postalCode} onChange={(e) => setPostalCode(e.target.value)}
                placeholder="CEP"
                className="flex-1 rounded-lg border border-stroke bg-transparent px-3 py-2 text-sm uppercase outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
              <button onClick={handleEstimateShipping} className="rounded-lg bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600">Calcular</button>
            </div>
            {shipping && <p className="mt-1 text-xs text-waterloo">Frete estimado: {fmt(shipping)}</p>}

            <button
              onClick={() => user ? router.push("/finalizar-compra") : router.push("/auth/entrar?redirect=/finalizar-compra")}
              className="mt-6 w-full rounded-full bg-primary py-3 font-medium text-white transition hover:bg-opacity-90"
            >
              {user ? "Finalizar Compra →" : "Entrar para finalizar →"}
            </button>
          </div>

          <Link href="/produtos" className="block text-center text-sm text-waterloo hover:text-primary dark:text-manatee">
            ← Continuar comprando
          </Link>
        </div>
      </div>
    </main>
  );
}
