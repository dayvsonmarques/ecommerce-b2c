"use client";

import { ordersApi, type Order } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

const STATUS_LABELS: Record<string, string> = {
  pending: "Pendente", paid: "Pago", processing: "Em processamento",
  shipped: "Enviado", delivered: "Entregue", cancelled: "Cancelado",
};
const STATUS_COLORS: Record<string, string> = {
  pending: "bg-yellow-100 text-yellow-800", paid: "bg-blue-100 text-blue-800",
  processing: "bg-purple-100 text-purple-800", shipped: "bg-indigo-100 text-indigo-800",
  delivered: "bg-green-100 text-green-800", cancelled: "bg-red-100 text-red-800",
};

function fmt(v: string) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export default function OrdersPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [orders, setOrders] = useState<Order[]>([]);
  const [pagination, setPagination] = useState({ total: 0, per_page: 10, current_page: 1, last_page: 1 });
  const [page, setPage] = useState(1);
  const [dataLoading, setDataLoading] = useState(true);

  useEffect(() => {
    if (!loading && !user) router.push("/auth/entrar");
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    setDataLoading(true);
    ordersApi.list(page)
      .then((r) => { setOrders(r.data); setPagination(r.pagination); })
      .finally(() => setDataLoading(false));
  }, [user, page]);

  if (loading || !user) return null;

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <h1 className="mb-8 text-3xl font-bold text-black dark:text-white">Meus Pedidos</h1>

      {dataLoading ? (
        <div className="space-y-4">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-24 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700" />
          ))}
        </div>
      ) : orders.length === 0 ? (
        <div className="py-12 text-center">
          <p className="mb-4 text-waterloo dark:text-manatee">Você ainda não tem pedidos.</p>
          <Link href="/produtos" className="rounded-full bg-primary px-6 py-3 text-white hover:bg-opacity-90">
            Começar a comprar
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {orders.map((order) => (
            <Link
              key={order.id}
              href={`/conta/pedidos/${order.id}`}
              className="flex items-center justify-between rounded-xl border border-stroke bg-white p-5 transition hover:border-primary dark:border-strokedark dark:bg-blacksection"
            >
              <div>
                <p className="font-mono text-sm font-medium text-black dark:text-white">{order.number}</p>
                <p className="mt-0.5 text-xs text-waterloo dark:text-manatee">
                  {new Date(order.created_at).toLocaleDateString("pt-BR", { day: "2-digit", month: "long", year: "numeric" })}
                </p>
              </div>
              <span className={`rounded-full px-3 py-1 text-xs font-medium ${STATUS_COLORS[order.status] ?? ""}`}>
                {STATUS_LABELS[order.status] ?? order.status}
              </span>
              <p className="font-bold text-primary">{fmt(order.total)}</p>
              <span className="text-sm text-primary">Ver →</span>
            </Link>
          ))}

          <div className="mt-6 flex items-center justify-center gap-4">
            <button disabled={page <= 1} onClick={() => setPage((p) => p - 1)} className="rounded-full border border-stroke px-5 py-2 text-sm disabled:opacity-40 hover:border-primary hover:text-primary dark:border-strokedark dark:text-white">← Anterior</button>
            <span className="text-sm text-waterloo">{page} / {pagination.last_page}</span>
            <button disabled={page >= pagination.last_page} onClick={() => setPage((p) => p + 1)} className="rounded-full border border-stroke px-5 py-2 text-sm disabled:opacity-40 hover:border-primary hover:text-primary dark:border-strokedark dark:text-white">Próxima →</button>
          </div>
        </div>
      )}
    </main>
  );
}
