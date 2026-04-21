"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";

export default function NewCmsPage() {
  const router = useRouter();
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    title: "",
    slug: "",
    content: "",
    section: "quick_links",
    sort_order: 0,
    is_active: true,
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: type === "checkbox" ? (e.target as HTMLInputElement).checked : value,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/pages`, {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify(form),
    });
    setSaving(false);
    if (res.ok) router.push("/cms/pages");
  };

  return (
    <div className="mx-auto max-w-2xl">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-dark dark:text-white">Nova Página</h2>
        <p className="text-sm text-gray-500">Crie uma página para exibir no footer da loja.</p>
      </div>

      <div className="rounded-[10px] border border-stroke bg-white p-8 shadow-1 dark:border-dark-3 dark:bg-gray-dark">
        <form onSubmit={handleSubmit} className="space-y-5">
          <div>
            <label className="mb-2 block text-sm font-medium text-dark dark:text-white">Título *</label>
            <input
              name="title"
              value={form.title}
              onChange={handleChange}
              required
              placeholder="Ex: Política de Privacidade"
              className="w-full rounded-lg border border-stroke px-4 py-3 text-sm outline-none focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-dark dark:text-white">
              Slug <span className="text-gray-400">(gerado automaticamente se vazio)</span>
            </label>
            <input
              name="slug"
              value={form.slug}
              onChange={handleChange}
              placeholder="politica-de-privacidade"
              className="w-full rounded-lg border border-stroke px-4 py-3 text-sm outline-none focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-dark dark:text-white">Seção do footer *</label>
            <select
              name="section"
              value={form.section}
              onChange={handleChange}
              className="w-full rounded-lg border border-stroke px-4 py-3 text-sm outline-none focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white"
            >
              <option value="quick_links">Quick Links</option>
              <option value="support">Suporte</option>
              <option value="custom">Customizada</option>
            </select>
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-dark dark:text-white">Conteúdo</label>
            <textarea
              name="content"
              value={form.content}
              onChange={handleChange}
              rows={8}
              placeholder="Conteúdo da página em HTML ou texto..."
              className="w-full rounded-lg border border-stroke px-4 py-3 text-sm outline-none focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white"
            />
          </div>

          <div className="flex gap-6">
            <div className="flex-1">
              <label className="mb-2 block text-sm font-medium text-dark dark:text-white">Ordem</label>
              <input
                type="number"
                name="sort_order"
                value={form.sort_order}
                onChange={handleChange}
                min={0}
                className="w-full rounded-lg border border-stroke px-4 py-3 text-sm outline-none focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white"
              />
            </div>
            <div className="flex items-end pb-3">
              <label className="flex cursor-pointer items-center gap-3">
                <input
                  type="checkbox"
                  name="is_active"
                  checked={form.is_active}
                  onChange={handleChange}
                  className="h-5 w-5 rounded border-stroke accent-primary"
                />
                <span className="text-sm font-medium text-dark dark:text-white">Página ativa</span>
              </label>
            </div>
          </div>

          <div className="flex gap-4 pt-2">
            <button
              type="submit"
              disabled={saving}
              className="rounded-lg bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primaryho disabled:opacity-60"
            >
              {saving ? "Salvando..." : "Criar Página"}
            </button>
            <button
              type="button"
              onClick={() => router.push("/cms/pages")}
              className="rounded-lg border border-stroke px-6 py-3 text-sm font-medium text-dark hover:border-primary hover:text-primary dark:border-dark-3 dark:text-white"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
