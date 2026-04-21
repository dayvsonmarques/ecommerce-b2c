"use client";

import { usersApi, type AdminUser } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

export default function UsersPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [pagination, setPagination] = useState({ total: 0, per_page: 20, current_page: 1, last_page: 1 });
  const [search, setSearch] = useState("");
  const [dataLoading, setDataLoading] = useState(true);

  const fetch = (page = 1) => {
    setDataLoading(true);
    usersApi
      .list({ page, per_page: 20, ...(search ? { search } : {}) })
      .then((r) => { setUsers(r.data); setPagination(r.pagination); })
      .finally(() => setDataLoading(false));
  };

  useEffect(() => { if (user) fetch(); }, [user]);

  const toggle = async (id: number) => {
    await usersApi.toggleActive(id);
    setUsers((prev) => prev.map((u) => u.id === id ? { ...u, is_active: !u.is_active } : u));
  };

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-dark dark:text-white">Usuários</h2>

      <div className="flex gap-3">
        <input
          type="text" value={search} onChange={(e) => setSearch(e.target.value)}
          onKeyDown={(e) => e.key === "Enter" && fetch(1)}
          placeholder="Buscar por nome, e-mail ou CPF..."
          className="w-full max-w-sm rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
        <button onClick={() => fetch(1)} className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-opacity-90">Buscar</button>
      </div>

      <div className="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 dark:border-gray-700">
                {["ID", "Nome", "E-mail", "CPF", "Admin", "Ativo", "Desde", "Ações"].map((h) => (
                  <th key={h} className="px-4 py-3 text-left font-medium text-dark-4 dark:text-dark-6">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {dataLoading
                ? Array.from({ length: 6 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100 dark:border-gray-800">
                      {Array.from({ length: 8 }).map((_, c) => (
                        <td key={c} className="px-4 py-3"><div className="h-4 w-20 animate-pulse rounded bg-gray-200 dark:bg-gray-700" /></td>
                      ))}
                    </tr>
                  ))
                : users.map((u) => (
                    <tr key={u.id} className="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                      <td className="px-4 py-3 text-dark-4">{u.id}</td>
                      <td className="px-4 py-3 font-medium text-dark dark:text-white">{u.name}</td>
                      <td className="px-4 py-3 text-dark-4">{u.email}</td>
                      <td className="px-4 py-3 text-dark-4 font-mono text-xs">{u.cpf ?? "—"}</td>
                      <td className="px-4 py-3">
                        {u.is_admin && <span className="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">Admin</span>}
                      </td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${u.is_active ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"}`}>
                          {u.is_active ? "Ativo" : "Inativo"}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-dark-4">{new Date(u.created_at).toLocaleDateString("pt-BR")}</td>
                      <td className="px-4 py-3">
                        <button
                          onClick={() => toggle(u.id)}
                          className={`text-sm ${u.is_active ? "text-red-500 hover:underline" : "text-green-600 hover:underline"}`}
                        >
                          {u.is_active ? "Desativar" : "Ativar"}
                        </button>
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
