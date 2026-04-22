"use client";

import { addressesApi, ordersApi, type Address } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useCart } from "@/contexts/CartContext";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import toast from "react-hot-toast";

function fmt(v: string | number) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export default function CheckoutPage() {
  const { user } = useAuth();
  const { cart, clearCart } = useCart();
  const router = useRouter();

  const [addresses, setAddresses] = useState<Address[]>([]);
  const [selectedAddress, setSelectedAddress] = useState<number | null>(null);
  const [paymentMethod, setPaymentMethod] = useState("pix");
  const [cardToken, setCardToken] = useState("");
  const [installments, setInstallments] = useState(1);
  const [notes, setNotes] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!user) { router.push("/auth/entrar?redirect=/finalizar-compra"); return; }
    addressesApi.list().then((r) => {
      setAddresses(r.data);
      const def = r.data.find((a) => a.is_default && a.type === "shipping");
      if (def) setSelectedAddress(def.id);
    });
  }, [user, router]);

  if (!user || !cart || cart.items.length === 0) {
    return (
      <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28 text-center">
        <h1 className="mb-4 text-3xl font-bold text-black dark:text-white">Checkout</h1>
        <p className="mb-6 text-waterloo">Seu carrinho está vazio.</p>
        <Link href="/produtos" className="rounded-full bg-primary px-6 py-3 text-white">Ir às compras</Link>
      </main>
    );
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedAddress) { toast.error("Selecione um endereço de entrega."); return; }
    setSubmitting(true);
    try {
      const order = await ordersApi.create({
        shipping_address_id: selectedAddress,
        payment_method: paymentMethod,
        card_token: paymentMethod === "credit_card" ? cardToken : undefined,
        installments: paymentMethod === "credit_card" ? installments : undefined,
        notes: notes || undefined,
      });
      await clearCart();
      toast.success("Pedido criado com sucesso!");
      router.push(`/conta/pedidos/${order.data.id}`);
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Erro ao finalizar pedido.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <h1 className="mb-8 text-3xl font-bold text-black dark:text-white">Checkout</h1>

      <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          {/* Address */}
          <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
            <h2 className="mb-4 font-semibold text-black dark:text-white">Endereço de Entrega</h2>
            {addresses.length === 0 ? (
              <div>
                <p className="mb-3 text-sm text-waterloo">Nenhum endereço cadastrado.</p>
                <Link href="/conta/perfil" className="text-sm text-primary hover:underline">Adicionar endereço →</Link>
              </div>
            ) : (
              <div className="space-y-3">
                {addresses.filter((a) => a.type === "shipping" || a.type === "billing").map((addr) => (
                  <label key={addr.id} className={`flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition ${selectedAddress === addr.id ? "border-primary bg-primary/5" : "border-stroke dark:border-strokedark"}`}>
                    <input type="radio" name="address" value={addr.id} checked={selectedAddress === addr.id} onChange={() => setSelectedAddress(addr.id)} className="mt-0.5 accent-primary" />
                    <div className="text-sm">
                      <p className="font-medium text-black dark:text-white">{addr.street}, {addr.number}</p>
                      {addr.complement && <p className="text-waterloo">{addr.complement}</p>}
                      <p className="text-waterloo">{addr.neighborhood} — {addr.city}/{addr.state}</p>
                      <p className="text-waterloo">CEP {addr.postal_code}</p>
                    </div>
                  </label>
                ))}
              </div>
            )}
          </div>

          {/* Payment */}
          <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
            <h2 className="mb-4 font-semibold text-black dark:text-white">Forma de Pagamento</h2>
            <div className="space-y-3">
              {[
                { value: "pix", label: "PIX — desconto instantâneo" },
                { value: "boleto", label: "Boleto — vence em 3 dias úteis" },
                { value: "credit_card", label: "Cartão de Crédito" },
              ].map((m) => (
                <label key={m.value} className={`flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition ${paymentMethod === m.value ? "border-primary bg-primary/5" : "border-stroke dark:border-strokedark"}`}>
                  <input type="radio" name="payment" value={m.value} checked={paymentMethod === m.value} onChange={() => setPaymentMethod(m.value)} className="accent-primary" />
                  <span className="text-sm font-medium text-black dark:text-white">{m.label}</span>
                </label>
              ))}
            </div>

            {paymentMethod === "credit_card" && (
              <div className="mt-4 space-y-3">
                <input type="text" placeholder="Token do cartão" value={cardToken} onChange={(e) => setCardToken(e.target.value)} required className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
                <select value={installments} onChange={(e) => setInstallments(Number(e.target.value))} className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white">
                  {Array.from({ length: 12 }, (_, i) => i + 1).map((n) => (
                    <option key={n} value={n}>{n}× de {fmt(Number(cart.total) / n)}</option>
                  ))}
                </select>
              </div>
            )}
          </div>

          {/* Notes */}
          <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
            <h2 className="mb-3 font-semibold text-black dark:text-white">Observações</h2>
            <textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} placeholder="Instruções especiais para entrega (opcional)" className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
          </div>
        </div>

        {/* Order Summary */}
        <div>
          <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
            <h2 className="mb-4 font-semibold text-black dark:text-white">Resumo do Pedido</h2>
            <div className="mb-4 max-h-48 space-y-2 overflow-y-auto">
              {cart.items.map((item) => (
                <div key={item.id} className="flex justify-between text-sm">
                  <span className="text-waterloo dark:text-manatee">{item.product.name} × {item.quantity}</span>
                  <span className="font-medium text-black dark:text-white">{fmt(item.subtotal)}</span>
                </div>
              ))}
            </div>
            <div className="space-y-2 border-t border-stroke pt-3 text-sm dark:border-strokedark">
              <div className="flex justify-between text-waterloo dark:text-manatee"><span>Subtotal</span><span>{fmt(cart.subtotal)}</span></div>
              {Number(cart.discount) > 0 && <div className="flex justify-between text-green-600"><span>Desconto</span><span>- {fmt(cart.discount)}</span></div>}
              <div className="flex justify-between text-waterloo dark:text-manatee"><span>Frete</span><span>{Number(cart.shipping) > 0 ? fmt(cart.shipping) : "Grátis"}</span></div>
              <div className="flex justify-between border-t border-stroke pt-2 font-bold text-black dark:border-strokedark dark:text-white">
                <span>Total</span><span className="text-lg text-primary">{fmt(cart.total)}</span>
              </div>
            </div>

            <button type="submit" disabled={submitting} className="mt-6 w-full rounded-full bg-primary py-3 font-medium text-white transition hover:bg-opacity-90 disabled:opacity-60">
              {submitting ? "Processando..." : "Confirmar Pedido"}
            </button>
          </div>
        </div>
      </form>
    </main>
  );
}
