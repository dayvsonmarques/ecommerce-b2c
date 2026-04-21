"use client";

import { ordersApi, type Order } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import toast from "react-hot-toast";

const STATUS_LABELS: Record<string, string> = {
  pending: "Pendente", paid: "Pago", processing: "Em processamento",
  shipped: "Enviado", delivered: "Entregue", cancelled: "Cancelado",
};
const STATUS_COLORS: Record<string, string> = {
  pending: "bg-yellow-100 text-yellow-800", paid: "bg-blue-100 text-blue-800",
  processing: "bg-purple-100 text-purple-800", shipped: "bg-indigo-100 text-indigo-800",
  delivered: "bg-green-100 text-green-800", cancelled: "bg-red-100 text-red-800",
};

function fmt(v: string | number) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export default function OrderDetailPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const params = useParams();
  const id = Number(params.id);

  const [order, setOrder] = useState<Order | null>(null);
  const [cancelling, setCancelling] = useState(false);

  useEffect(() => {
    if (!loading && !user) router.push("/auth/entrar");
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    ordersApi.get(id).then((r) => setOrder(r.data));
  }, [user, id]);

  const handleCancel = async () => {
    if (!confirm("Confirma o cancelamento do pedido?")) return;
    setCancelling(true);
    try {
      const r = await ordersApi.cancel(id);
      setOrder(r.data);
      toast.success("Pedido cancelado.");
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Não foi possível cancelar.");
    } finally {
      setCancelling(false);
    }
  };

  if (loading || !user || !order) return null;

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <nav className="mb-6 text-sm text-waterloo dark:text-manatee">
        <Link href="/conta/pedidos" className="hover:text-primary">Meus Pedidos</Link>
        {" / "}
        <span className="text-black dark:text-white">{order.number}</span>
      </nav>

      <div className="mb-6 flex items-center gap-4">
        <h1 className="text-2xl font-bold text-black dark:text-white">Pedido {order.number}</h1>
        <span className={`rounded-full px-3 py-1 text-sm font-medium ${STATUS_COLORS[order.status] ?? ""}`}>
          {STATUS_LABELS[order.status] ?? order.status}
        </span>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          {/* Items */}
          <div className="rounded-xl border border-stroke bg-white dark:border-strokedark dark:bg-blacksection">
            <h2 className="border-b border-stroke px-6 py-4 font-semibold text-black dark:border-strokedark dark:text-white">Itens</h2>
            <div className="divide-y divide-stroke dark:divide-strokedark">
              {order.items?.map((item) => (
                <div key={item.id} className="flex items-center justify-between px-6 py-4">
                  <div>
                    <p className="font-medium text-black dark:text-white">{item.product_name}</p>
                    <p className="text-sm text-waterloo">{fmt(item.unit_price)} × {item.quantity}</p>
                  </div>
                  <p className="font-bold text-primary">{fmt(item.subtotal)}</p>
                </div>
              ))}
            </div>
            <div className="space-y-1 px-6 py-4 text-sm">
              <div className="flex justify-between text-waterloo dark:text-manatee"><span>Subtotal</span><span>{fmt(order.subtotal)}</span></div>
              <div className="flex justify-between text-waterloo dark:text-manatee"><span>Frete</span><span>{fmt(order.shipping)}</span></div>
              {Number(order.discount) > 0 && <div className="flex justify-between text-green-600"><span>Desconto</span><span>- {fmt(order.discount)}</span></div>}
              <div className="flex justify-between border-t border-stroke pt-2 font-bold text-black dark:border-strokedark dark:text-white">
                <span>Total</span><span className="text-primary">{fmt(order.total)}</span>
              </div>
            </div>
          </div>

          {/* Timeline */}
          {order.timeline && order.timeline.length > 0 && (
            <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
              <h2 className="mb-4 font-semibold text-black dark:text-white">Acompanhamento</h2>
              <ol className="space-y-4">
                {order.timeline.map((t, i) => (
                  <li key={i} className="flex items-start gap-3">
                    <span className={`mt-1 h-3 w-3 shrink-0 rounded-full ${STATUS_COLORS[t.status]?.split(" ")[0] ?? "bg-gray-300"}`} />
                    <div>
                      <p className="font-medium text-black dark:text-white">{STATUS_LABELS[t.status] ?? t.status}</p>
                      {t.comment && <p className="text-sm text-waterloo">{t.comment}</p>}
                      <p className="text-xs text-waterloo">{new Date(t.created_at).toLocaleString("pt-BR")}</p>
                    </div>
                  </li>
                ))}
              </ol>
            </div>
          )}
        </div>

        {/* Actions */}
        <div className="space-y-4">
          {order.payment && (
            <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
              <h2 className="mb-3 font-semibold text-black dark:text-white">Pagamento</h2>
              <p className="text-sm text-waterloo"><span className="font-medium text-black dark:text-white">{order.payment.method?.toUpperCase()}</span></p>
              <p className="text-sm text-waterloo">Status: <span className="font-medium text-black dark:text-white">{order.payment.status}</span></p>
              {order.payment.pix_qr_code_text && (
                <div className="mt-3">
                  <p className="mb-1 text-xs text-waterloo">Código PIX:</p>
                  <p className="break-all rounded bg-gray-50 p-2 text-xs font-mono dark:bg-gray-800 dark:text-white">{order.payment.pix_qr_code_text}</p>
                </div>
              )}
              {order.payment.boleto_url && (
                <a href={order.payment.boleto_url} target="_blank" rel="noreferrer" className="mt-3 block rounded-full border border-primary py-2 text-center text-sm text-primary hover:bg-primary hover:text-white">
                  Visualizar Boleto
                </a>
              )}
            </div>
          )}

          {order.status === "pending" && (
            <button onClick={handleCancel} disabled={cancelling} className="w-full rounded-full border border-red-300 py-2.5 text-sm text-red-500 transition hover:bg-red-50 disabled:opacity-60 dark:hover:bg-red-900/20">
              {cancelling ? "Cancelando..." : "Cancelar Pedido"}
            </button>
          )}

          <Link href="/conta/pedidos" className="block text-center text-sm text-waterloo hover:text-primary">
            ← Voltar aos pedidos
          </Link>
        </div>
      </div>
    </main>
  );
}
