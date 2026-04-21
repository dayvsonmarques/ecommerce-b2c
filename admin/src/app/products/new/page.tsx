"use client";

import { productsApi } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

export default function NewProductPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  const [form, setForm] = useState({
    category_id: "",
    name: "",
    slug: "",
    description: "",
    short_description: "",
    price: "",
    cost_price: "",
    sku: "",
    quantity: "0",
    min_quantity_alert: "5",
    is_active: true,
  });

  const set = (k: string, v: string | boolean) => setForm((f) => ({ ...f, [k]: v }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setError("");
    try {
      await productsApi.create({
        ...form,
        category_id: Number(form.category_id),
        price: Number(form.price),
        cost_price: form.cost_price ? Number(form.cost_price) : null,
        quantity: Number(form.quantity),
        min_quantity_alert: Number(form.min_quantity_alert),
      });
      router.push("/products");
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : "Erro ao salvar produto.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <button onClick={() => router.back()} className="text-dark-4 hover:text-primary">← Voltar</button>
        <h2 className="text-2xl font-bold text-dark dark:text-white">Novo Produto</h2>
      </div>

      {error && (
        <div className="rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">{error}</div>
      )}

      <form onSubmit={handleSubmit}>
        <div className="rounded-[10px] bg-white p-6 shadow-1 dark:bg-gray-dark dark:shadow-card">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Field label="Nome *" required>
              <input type="text" required value={form.name} onChange={(e) => set("name", e.target.value)} className={inputCls} />
            </Field>
            <Field label="SKU *" required>
              <input type="text" required value={form.sku} onChange={(e) => set("sku", e.target.value)} className={inputCls} />
            </Field>
            <Field label="Category ID *" required>
              <input type="number" required value={form.category_id} onChange={(e) => set("category_id", e.target.value)} className={inputCls} placeholder="ID da categoria" />
            </Field>
            <Field label="Slug">
              <input type="text" value={form.slug} onChange={(e) => set("slug", e.target.value)} className={inputCls} placeholder="gerado automaticamente" />
            </Field>
            <Field label="Preço (R$) *" required>
              <input type="number" step="0.01" required value={form.price} onChange={(e) => set("price", e.target.value)} className={inputCls} />
            </Field>
            <Field label="Custo (R$)">
              <input type="number" step="0.01" value={form.cost_price} onChange={(e) => set("cost_price", e.target.value)} className={inputCls} />
            </Field>
            <Field label="Estoque inicial">
              <input type="number" value={form.quantity} onChange={(e) => set("quantity", e.target.value)} className={inputCls} />
            </Field>
            <Field label="Alerta mínimo de estoque">
              <input type="number" value={form.min_quantity_alert} onChange={(e) => set("min_quantity_alert", e.target.value)} className={inputCls} />
            </Field>
            <Field label="Descrição curta">
              <input type="text" value={form.short_description} onChange={(e) => set("short_description", e.target.value)} className={inputCls} />
            </Field>
            <Field label="Ativo">
              <label className="flex cursor-pointer items-center gap-2">
                <input type="checkbox" checked={form.is_active} onChange={(e) => set("is_active", e.target.checked)} className="h-4 w-4 accent-primary" />
                <span className="text-sm text-dark dark:text-white">Produto ativo</span>
              </label>
            </Field>
            <div className="md:col-span-2">
              <Field label="Descrição *" required>
                <textarea required rows={4} value={form.description} onChange={(e) => set("description", e.target.value)} className={inputCls} />
              </Field>
            </div>
          </div>
        </div>

        <div className="mt-4 flex gap-3">
          <button type="submit" disabled={saving} className="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90 disabled:opacity-60">
            {saving ? "Salvando..." : "Salvar Produto"}
          </button>
          <button type="button" onClick={() => router.back()} className="rounded-lg border border-gray-200 px-6 py-2.5 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  );
}

const inputCls = "w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white";

function Field({ label, children, required }: { label: string; children: React.ReactNode; required?: boolean }) {
  return (
    <div>
      <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">
        {label}{required && <span className="text-red-500">*</span>}
      </label>
      {children}
    </div>
  );
}
