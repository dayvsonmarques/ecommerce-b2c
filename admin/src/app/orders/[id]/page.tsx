"use client";

import { ordersApi, type Order } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";

const STATUS_LABELS: Record<string, string> = {
  pending: "Pendente", paid: "Pago", processing: "Processando",
  shipped: "Enviado", delivered: "Entregue", cancelled: "Cancelado",
};
const STATUS_COLORS: Record<string, string> = {
  pending: "bg-yellow-100 text-yellow-800", paid: "bg-blue-100 text-blue-800",
  processing: "bg-purple-100 text-purple-800", shipped: "bg-indigo-100 text-indigo-800",
  delivered: "bg-green-100 text-green-800", cancelled: "bg-red-100 text-red-800",
};
const TRANSITIONS: Record<string, string[]> = {
  paid: ["processing"], processing: ["shipped"], shipped: ["delivered"],
  pending: ["cancelled"], paid2: ["cancelled"],
};

function fmt(v: number) {
  return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(v);
}

export default function OrderDetailPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const params = useParams();
  const id = Number(params.id);

  const [order, setOrder] = useState<Order | null>(null);
  const [newStatus, setNewStatus] = useState("");
  const [tracking, setTracking] = useState("");
  const [carrier, setCarrier] = useState("Correios");
  const [comment, setComment] = useState("");
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState("");

  useEffect(() => {
    if (!user) return;
    ordersApi.get(id).then((r) => setOrder(r.data));
  }, [user, id]);

  const handleStatusUpdate = async () => {
    if (!newStatus) return;
    setSaving(true);
    setMsg("");
    try {
      const r = await ordersApi.updateStatus(id, {
        status: newStatus,
        tracking_code: tracking || undefined,
        carrier: carrier || undefined,
        comment: comment || undefined,
      });
      setOrder(r.data);
      setMsg(r.message);
      setNewStatus(""); setTracking(""); setComment("");
    } catch (err: unknown) {
      setMsg(err instanceof Error ? err.message : "Erro ao atualizar status.");
    } finally {
      setSaving(false);
    }
  };

  if (loading || !user || !order) return null;

  const available = TRANSITIONS[order.status] ?? TRANSITIONS[order.status + "2"] ?? [];

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <button onClick={() => router.back()} className="text-dark-4 hover:text-primary">← Voltar</button>
        <h2 className="text-2xl font-bold text-dark dark:text-white">Pedido {order.number}</h2>
        <span className={`rounded-full px-3 py-1 text-sm font-medium ${STATUS_COLORS[order.status] ?? ""}`}>
          {STATUS_LABELS[order.status] ?? order.status}
        </span>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {/* Main info */}
        <div className="space-y-6 lg:col-span-2">
          {/* Items */}
          <div className="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <h3 className="border-b border-gray-200 px-6 py-4 font-semibold text-dark dark:border-gray-700 dark:text-white">Itens do Pedido</h3>
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-200 dark:border-gray-700">
                  {["Produto", "Qtd", "Preço Unit.", "Subtotal"].map((h) => (
                    <th key={h} className="px-6 py-3 text-left font-medium text-dark-4 dark:text-dark-6">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {order.items?.map((item) => (
                  <tr key={item.id} className="border-b border-gray-100 dark:border-gray-800">
                    <td className="px-6 py-3 font-medium text-dark dark:text-white">{item.product_name}</td>
                    <td className="px-6 py-3">{item.quantity}</td>
                    <td className="px-6 py-3">{fmt(Number(item.unit_price))}</td>
                    <td className="px-6 py-3 font-medium">{fmt(Number(item.subtotal))}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            <div className="space-y-1 px-6 py-4 text-sm">
              <div className="flex justify-between text-dark-4"><span>Subtotal</span><span>{fmt(Number(order.subtotal))}</span></div>
              <div className="flex justify-between text-dark-4"><span>Frete</span><span>{fmt(Number(order.shipping))}</span></div>
              <div className="flex justify-between text-dark-4"><span>Desconto</span><span>- {fmt(Number(order.discount))}</span></div>
              <div className="flex justify-between border-t border-gray-200 pt-2 font-bold text-dark dark:border-gray-700 dark:text-white">
                <span>Total</span><span>{fmt(Number(order.total))}</span>
              </div>
            </div>
          </div>

          {/* Timeline */}
          {order.timeline && order.timeline.length > 0 && (
            <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
              <h3 className="mb-4 font-semibold text-dark dark:text-white">Histórico</h3>
              <ol className="space-y-3">
                {order.timeline.map((t, i) => (
                  <li key={i} className="flex items-start gap-3">
                    <span className={`mt-0.5 h-2.5 w-2.5 rounded-full ${STATUS_COLORS[t.status]?.split(" ")[0] ?? "bg-gray-300"}`} />
                    <div>
                      <p className="text-sm font-medium text-dark dark:text-white">{STATUS_LABELS[t.status] ?? t.status}</p>
                      {t.comment && <p className="text-xs text-dark-4">{t.comment}</p>}
                      <p className="text-xs text-dark-4">{new Date(t.created_at).toLocaleString("pt-BR")}</p>
                    </div>
                  </li>
                ))}
              </ol>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Client */}
          {order.user && (
            <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
              <h3 className="mb-3 font-semibold text-dark dark:text-white">Cliente</h3>
              <p className="text-sm font-medium text-dark dark:text-white">{order.user.name}</p>
              <p className="text-sm text-dark-4">{order.user.email}</p>
            </div>
          )}

          {/* Payment */}
          {order.payment && (
            <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
              <h3 className="mb-3 font-semibold text-dark dark:text-white">Pagamento</h3>
              <p className="text-sm text-dark-4">Método: <span className="font-medium text-dark dark:text-white">{order.payment.method}</span></p>
              <p className="text-sm text-dark-4">Status: <span className="font-medium text-dark dark:text-white">{order.payment.status}</span></p>
            </div>
          )}

          {/* Status update */}
          {available.length > 0 && (
            <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
              <h3 className="mb-4 font-semibold text-dark dark:text-white">Atualizar Status</h3>
              {msg && <p className="mb-3 rounded bg-blue-50 p-2 text-xs text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{msg}</p>}
              <div className="space-y-3">
                <select value={newStatus} onChange={(e) => setNewStatus(e.target.value)} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                  <option value="">Selecionar status</option>
                  {available.map((s) => <option key={s} value={s}>{STATUS_LABELS[s]}</option>)}
                </select>
                {newStatus === "shipped" && (
                  <>
                    <input type="text" placeholder="Código de rastreamento" value={tracking} onChange={(e) => setTracking(e.target.value)} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    <input type="text" placeholder="Transportadora" value={carrier} onChange={(e) => setCarrier(e.target.value)} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                  </>
                )}
                <textarea placeholder="Comentário (opcional)" value={comment} onChange={(e) => setComment(e.target.value)} rows={2} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                <button onClick={handleStatusUpdate} disabled={!newStatus || saving} className="w-full rounded-lg bg-primary py-2 text-sm font-medium text-white hover:bg-opacity-90 disabled:opacity-60">
                  {saving ? "Salvando..." : "Confirmar"}
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
