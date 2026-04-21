"use client";

import { dashboardApi, ordersApi, type Order, type SummaryData } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

const STATUS_LABELS: Record<string, string> = {
  pending: "Pendente",
  paid: "Pago",
  processing: "Processando",
  shipped: "Enviado",
  delivered: "Entregue",
  cancelled: "Cancelado",
};

const STATUS_COLORS: Record<string, string> = {
  pending: "bg-yellow-100 text-yellow-800",
  paid: "bg-blue-100 text-blue-800",
  processing: "bg-purple-100 text-purple-800",
  shipped: "bg-indigo-100 text-indigo-800",
  delivered: "bg-green-100 text-green-800",
  cancelled: "bg-red-100 text-red-800",
};

function fmt(value: number) {
  return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(value);
}

export default function Dashboard() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [summary, setSummary] = useState<SummaryData | null>(null);
  const [recentOrders, setRecentOrders] = useState<Order[]>([]);
  const [dataLoading, setDataLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    Promise.all([
      dashboardApi.summary().then((r) => setSummary(r.data)),
      ordersApi.list({ per_page: 8 }).then((r) => setRecentOrders(r.data)),
    ]).finally(() => setDataLoading(false));
  }, [user]);

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-dark dark:text-white">Dashboard</h2>
        <p className="text-sm text-dark-4 dark:text-dark-6">
          {summary ? `${summary.period.from} → ${summary.period.to}` : "Carregando..."}
        </p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard title="Total de Pedidos" value={dataLoading ? "..." : String(summary?.total_orders ?? 0)} sub="no período" color="blue" />
        <StatCard title="Receita Total" value={dataLoading ? "..." : fmt(summary?.total_revenue ?? 0)} sub="pedidos não cancelados" color="green" />
        <StatCard title="Ticket Médio" value={dataLoading ? "..." : fmt(summary?.average_order_value ?? 0)} sub="por pedido" color="purple" />
        <StatCard title="Cancelados" value={dataLoading ? "..." : String(summary?.orders_by_status?.cancelled ?? 0)} sub="no período" color="red" />
      </div>

      {summary && (
        <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
          <h3 className="mb-4 font-semibold text-dark dark:text-white">Pedidos por Status</h3>
          <div className="flex flex-wrap gap-3">
            {Object.entries(summary.orders_by_status).map(([status, count]) => (
              <span key={status} className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium ${STATUS_COLORS[status] ?? "bg-gray-100 text-gray-800"}`}>
                {STATUS_LABELS[status] ?? status} <span className="ml-1 font-bold">{count}</span>
              </span>
            ))}
          </div>
        </div>
      )}

      <div className="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
        <div className="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700">
          <h3 className="font-semibold text-dark dark:text-white">Pedidos Recentes</h3>
          <button onClick={() => router.push("/orders")} className="text-sm text-primary hover:underline">Ver todos →</button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 dark:border-gray-700">
                {["Número", "Status", "Total", "Data"].map((h) => (
                  <th key={h} className="px-6 py-3 text-left font-medium text-dark-4 dark:text-dark-6">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {dataLoading
                ? Array.from({ length: 5 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100 dark:border-gray-800">
                      {[1,2,3,4].map((c) => <td key={c} className="px-6 py-4"><div className="h-4 w-24 animate-pulse rounded bg-gray-200 dark:bg-gray-700" /></td>)}
                    </tr>
                  ))
                : recentOrders.map((order) => (
                    <tr key={order.id} onClick={() => router.push(`/orders/${order.id}`)} className="cursor-pointer border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                      <td className="px-6 py-4 font-mono text-xs">{order.number}</td>
                      <td className="px-6 py-4">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${STATUS_COLORS[order.status] ?? ""}`}>{STATUS_LABELS[order.status] ?? order.status}</span>
                      </td>
                      <td className="px-6 py-4 font-medium">{fmt(Number(order.total))}</td>
                      <td className="px-6 py-4 text-dark-4">{new Date(order.created_at).toLocaleDateString("pt-BR")}</td>
                    </tr>
                  ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

function StatCard({ title, value, sub, color }: { title: string; value: string; sub: string; color: "blue"|"green"|"purple"|"red" }) {
  const colors = { blue: "text-blue-600", green: "text-green-600", purple: "text-purple-600", red: "text-red-600" };
  return (
    <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
      <p className="mb-1 text-sm text-dark-4 dark:text-dark-6">{title}</p>
      <p className={`mb-1 text-2xl font-bold ${colors[color]}`}>{value}</p>
      <p className="text-xs text-dark-4 dark:text-dark-6">{sub}</p>
    </div>
  );
}
