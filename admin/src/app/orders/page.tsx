"use client";

import { ordersApi, type Order } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from "next/navigation";
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

function fmt(v: number) {
  return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(v);
}

export default function OrdersPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [orders, setOrders] = useState<Order[]>([]);
  const [pagination, setPagination] = useState({ total: 0, per_page: 20, current_page: 1, last_page: 1 });
  const [status, setStatus] = useState("");
  const [search, setSearch] = useState("");
  const [dataLoading, setDataLoading] = useState(true);

  const fetch = (page = 1) => {
    setDataLoading(true);
    ordersApi
      .list({ page, per_page: 20, ...(status ? { status } : {}), ...(search ? { search } : {}) })
      .then((r) => { setOrders(r.data); setPagination(r.pagination); })
      .finally(() => setDataLoading(false));
  };

  useEffect(() => { if (user) fetch(); }, [user]);

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-dark dark:text-white">Pedidos</h2>

      <div className="flex flex-wrap gap-3">
        <input
          type="text" value={search} onChange={(e) => setSearch(e.target.value)}
          onKeyDown={(e) => e.key === "Enter" && fetch(1)}
          placeholder="Buscar por número..."
          className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
        <select
          value={status} onChange={(e) => { setStatus(e.target.value); }}
          className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
          <option value="">Todos os status</option>
          {Object.entries(STATUS_LABELS).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
        </select>
        <button onClick={() => fetch(1)} className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-opacity-90">Filtrar</button>
      </div>

      <div className="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 dark:border-gray-700">
                {["Número", "Cliente", "Status", "Total", "Data", "Ações"].map((h) => (
                  <th key={h} className="px-4 py-3 text-left font-medium text-dark-4 dark:text-dark-6">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {dataLoading
                ? Array.from({ length: 6 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100 dark:border-gray-800">
                      {Array.from({ length: 6 }).map((_, c) => (
                        <td key={c} className="px-4 py-3"><div className="h-4 w-20 animate-pulse rounded bg-gray-200 dark:bg-gray-700" /></td>
                      ))}
                    </tr>
                  ))
                : orders.map((o) => (
                    <tr key={o.id} className="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                      <td className="px-4 py-3 font-mono text-xs">{o.number}</td>
                      <td className="px-4 py-3 text-dark dark:text-white">{o.user?.name ?? "—"}</td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${STATUS_COLORS[o.status] ?? ""}`}>
                          {STATUS_LABELS[o.status] ?? o.status}
                        </span>
                      </td>
                      <td className="px-4 py-3 font-medium">{fmt(Number(o.total))}</td>
                      <td className="px-4 py-3 text-dark-4">{new Date(o.created_at).toLocaleDateString("pt-BR")}</td>
                      <td className="px-4 py-3">
                        <button onClick={() => router.push(`/orders/${o.id}`)} className="text-primary hover:underline">Ver</button>
                      </td>
                    </tr>
                  ))}
            </tbody>
          </table>
        </div>
        <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
          <p className="text-sm text-dark-4">Total: {pagination.total}</p>
          <div className="flex gap-2">
            <button disabled={pagination.current_page <= 1} onClick={() => fetch(pagination.current_page - 1)} className="rounded px-3 py-1 text-sm disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700">← Anterior</button>
            <span className="px-3 py-1 text-sm">{pagination.current_page} / {pagination.last_page}</span>
            <button disabled={pagination.current_page >= pagination.last_page} onClick={() => fetch(pagination.current_page + 1)} className="rounded px-3 py-1 text-sm disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700">Próxima →</button>
          </div>
        </div>
      </div>
    </div>
  );
}
