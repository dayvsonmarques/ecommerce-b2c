"use client";

import { productsApi, type Product } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

export default function ProductsPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [products, setProducts] = useState<Product[]>([]);
  const [pagination, setPagination] = useState({ total: 0, per_page: 20, current_page: 1, last_page: 1 });
  const [search, setSearch] = useState("");
  const [dataLoading, setDataLoading] = useState(true);

  const fetchProducts = (page = 1, q = search) => {
    setDataLoading(true);
    productsApi
      .list({ page, per_page: 20, ...(q ? { search: q } : {}) })
      .then((r) => {
        setProducts(r.data);
        setPagination(r.pagination);
      })
      .finally(() => setDataLoading(false));
  };

  useEffect(() => {
    if (user) fetchProducts();
  }, [user]);

  const handleDelete = async (id: number) => {
    if (!confirm("Confirma a exclusão do produto?")) return;
    await productsApi.delete(id);
    fetchProducts(pagination.current_page);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-2xl font-bold text-dark dark:text-white">Produtos</h2>
        <button
          onClick={() => router.push("/products/new")}
          className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90"
        >
          + Novo Produto
        </button>
      </div>

      {/* Search */}
      <div className="flex gap-3">
        <input
          type="text"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          onKeyDown={(e) => e.key === "Enter" && fetchProducts(1)}
          placeholder="Buscar por nome..."
          className="w-full max-w-sm rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
        <button
          onClick={() => fetchProducts(1)}
          className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-opacity-90"
        >
          Buscar
        </button>
      </div>

      <div className="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 dark:border-gray-700">
                {["ID", "Nome", "SKU", "Preço", "Estoque", "Categoria", "Ativo", "Ações"].map((h) => (
                  <th key={h} className="px-4 py-3 text-left font-medium text-dark-4 dark:text-dark-6">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {dataLoading
                ? Array.from({ length: 6 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100 dark:border-gray-800">
                      {Array.from({ length: 8 }).map((_, c) => (
                        <td key={c} className="px-4 py-3">
                          <div className="h-4 w-20 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
                        </td>
                      ))}
                    </tr>
                  ))
                : products.map((p) => (
                    <tr
                      key={p.id}
                      className="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                    >
                      <td className="px-4 py-3 text-dark-4">{p.id}</td>
                      <td className="px-4 py-3 font-medium text-dark dark:text-white">{p.name}</td>
                      <td className="px-4 py-3 font-mono text-xs text-dark-4">{p.sku}</td>
                      <td className="px-4 py-3">
                        R$ {Number(p.price).toFixed(2).replace(".", ",")}
                      </td>
                      <td className="px-4 py-3">
                        <span
                          className={`font-medium ${p.quantity <= (p.min_quantity_alert ?? 5) ? "text-red-500" : "text-green-600"}`}
                        >
                          {p.quantity}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-dark-4">{p.category?.name ?? "—"}</td>
                      <td className="px-4 py-3">
                        <span
                          className={`rounded-full px-2 py-0.5 text-xs font-medium ${p.is_active ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"}`}
                        >
                          {p.is_active ? "Ativo" : "Inativo"}
                        </span>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex gap-2">
                          <button
                            onClick={() => router.push(`/products/${p.id}`)}
                            className="text-primary hover:underline"
                          >
                            Editar
                          </button>
                          <button
                            onClick={() => handleDelete(p.id)}
                            className="text-red-500 hover:underline"
                          >
                            Excluir
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
          <p className="text-sm text-dark-4">
            Total: {pagination.total} produtos
          </p>
          <div className="flex gap-2">
            <button
              disabled={pagination.current_page <= 1}
              onClick={() => fetchProducts(pagination.current_page - 1)}
              className="rounded px-3 py-1 text-sm disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              ← Anterior
            </button>
            <span className="px-3 py-1 text-sm">
              {pagination.current_page} / {pagination.last_page}
            </span>
            <button
              disabled={pagination.current_page >= pagination.last_page}
              onClick={() => fetchProducts(pagination.current_page + 1)}
              className="rounded px-3 py-1 text-sm disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              Próxima →
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
