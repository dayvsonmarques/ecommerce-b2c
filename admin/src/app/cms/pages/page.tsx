"use client";

import Link from "next/link";
import { useEffect, useState } from "react";

type Page = {
  id: number;
  title: string;
  slug: string;
  section: string;
  sort_order: number;
  is_active: boolean;
};

const sectionLabel: Record<string, string> = {
  quick_links: "Quick Links",
  support: "Suporte",
  custom: "Customizada",
};

export default function CmsPagesListPage() {
  const [pages, setPages] = useState<Page[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/pages`, {
      headers: { Accept: "application/json" },
    })
      .then((r) => r.json())
      .then((d) => setPages(d.data ?? []))
      .finally(() => setLoading(false));
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm("Remover esta página?")) return;
    await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/pages/${id}`, {
      method: "DELETE",
      headers: { Accept: "application/json" },
    });
    setPages((prev) => prev.filter((p) => p.id !== id));
  };

  return (
    <div className="mx-auto max-w-screen-xl">
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-dark dark:text-white">Páginas CMS</h2>
          <p className="text-sm text-gray-500">Gerencie as páginas exibidas no footer da loja.</p>
        </div>
        <Link
          href="/cms/pages/new"
          className="rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primaryho"
        >
          + Nova Página
        </Link>
      </div>

      <div className="rounded-[10px] border border-stroke bg-white shadow-1 dark:border-dark-3 dark:bg-gray-dark">
        {loading ? (
          <div className="p-10 text-center text-gray-400">Carregando...</div>
        ) : pages.length === 0 ? (
          <div className="p-10 text-center text-gray-400">
            Nenhuma página cadastrada.{" "}
            <Link href="/cms/pages/new" className="text-primary hover:underline">
              Criar a primeira
            </Link>
          </div>
        ) : (
          <table className="w-full table-auto">
            <thead>
              <tr className="border-b border-stroke dark:border-dark-3">
                <th className="px-6 py-4 text-left text-sm font-semibold text-dark dark:text-white">Título</th>
                <th className="px-6 py-4 text-left text-sm font-semibold text-dark dark:text-white">Slug</th>
                <th className="px-6 py-4 text-left text-sm font-semibold text-dark dark:text-white">Seção</th>
                <th className="px-6 py-4 text-center text-sm font-semibold text-dark dark:text-white">Ordem</th>
                <th className="px-6 py-4 text-center text-sm font-semibold text-dark dark:text-white">Status</th>
                <th className="px-6 py-4 text-right text-sm font-semibold text-dark dark:text-white">Ações</th>
              </tr>
            </thead>
            <tbody>
              {pages.map((page) => (
                <tr key={page.id} className="border-b border-stroke last:border-0 dark:border-dark-3">
                  <td className="px-6 py-4 text-sm font-medium text-dark dark:text-white">{page.title}</td>
                  <td className="px-6 py-4 text-sm text-gray-500">/{page.slug}</td>
                  <td className="px-6 py-4 text-sm">
                    <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                      {sectionLabel[page.section] ?? page.section}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-center text-sm text-gray-500">{page.sort_order}</td>
                  <td className="px-6 py-4 text-center">
                    <span
                      className={`rounded-full px-3 py-1 text-xs font-medium ${
                        page.is_active
                          ? "bg-green-100 text-green-700"
                          : "bg-red-100 text-red-600"
                      }`}
                    >
                      {page.is_active ? "Ativo" : "Inativo"}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-3">
                      <Link
                        href={`/cms/pages/${page.id}/edit`}
                        className="text-sm text-primary hover:underline"
                      >
                        Editar
                      </Link>
                      <button
                        onClick={() => handleDelete(page.id)}
                        className="text-sm text-red-500 hover:underline"
                      >
                        Remover
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}
