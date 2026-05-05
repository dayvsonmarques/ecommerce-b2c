"use client";

import { storeSettingsApi, type StoreSettings } from "@/lib/api";
import Image from "next/image";
import { useEffect, useState } from "react";

const ESTADOS_BR = [
  "AC","AL","AP","AM","BA","CE","DF","ES","GO","MA",
  "MT","MS","MG","PA","PB","PR","PE","PI","RJ","RN",
  "RS","RO","RR","SC","SP","SE","TO",
];

const COVERAGE_LABELS: Record<StoreSettings["shipping_coverage"], string> = {
  state:         "Apenas meu estado",
  national:      "Todo o Brasil",
  international: "Brasil + Exportação",
};

export default function StoreSettingsPage() {
  const [form, setForm] = useState<Partial<StoreSettings>>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [success, setSuccess] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    storeSettingsApi.get().then((r) => {
      setForm(r.data);
    }).finally(() => setLoading(false));
  }, []);

  const set = (field: keyof StoreSettings) =>
    (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) =>
      setForm((f) => ({ ...f, [field]: e.target.value }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setSuccess("");
    setError("");
    try {
      await storeSettingsApi.update(form);
      setSuccess("Configurações salvas com sucesso!");
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : "Erro ao salvar.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="flex min-h-40 items-center justify-center text-sm text-gray-500">Carregando…</div>;
  }

  return (
    <div className="mx-auto max-w-3xl">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-dark dark:text-white">Configurações da Loja</h1>
        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Informações públicas e regras de envio.
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Identidade */}
        <section className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-gray-dark">
          <h2 className="mb-4 text-base font-semibold text-dark dark:text-white">Identidade</h2>
          <div className="space-y-4">
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Nome da loja</label>
              <input
                type="text" value={form.store_name ?? ""} onChange={set("store_name")} required
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>

            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Descrição curta</label>
              <textarea
                value={form.store_description ?? ""} onChange={set("store_description")} rows={3}
                placeholder="Uma frase que resume o que sua loja vende…"
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>

            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">URL do Logo</label>
              <input
                type="url" value={form.logo_url ?? ""} onChange={set("logo_url")}
                placeholder="https://…"
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
              {form.logo_url && (
                <div className="mt-3 flex items-center gap-3">
                  <Image src={form.logo_url} alt="Logo preview" width={120} height={40} className="h-10 w-auto object-contain" unoptimized />
                  <span className="text-xs text-gray-400">Pré-visualização</span>
                </div>
              )}
            </div>
          </div>
        </section>

        {/* Endereço */}
        <section className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-gray-dark">
          <h2 className="mb-1 text-base font-semibold text-dark dark:text-white">Endereço da Loja</h2>
          <p className="mb-4 text-xs text-gray-500 dark:text-gray-400">Usado como origem no cálculo de frete.</p>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div className="sm:col-span-2">
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Rua / Avenida</label>
              <input type="text" value={form.address_street ?? ""} onChange={set("address_street")}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Número</label>
              <input type="text" value={form.address_number ?? ""} onChange={set("address_number")}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Complemento</label>
              <input type="text" value={form.address_complement ?? ""} onChange={set("address_complement")}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Bairro</label>
              <input type="text" value={form.address_neighborhood ?? ""} onChange={set("address_neighborhood")}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Cidade</label>
              <input type="text" value={form.address_city ?? ""} onChange={set("address_city")}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">Estado (UF)</label>
              <select value={form.address_state ?? ""} onChange={set("address_state")}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white">
                <option value="">Selecione…</option>
                {ESTADOS_BR.map((uf) => <option key={uf} value={uf}>{uf}</option>)}
              </select>
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-dark dark:text-white">CEP</label>
              <input type="text" value={form.address_postal_code ?? ""} onChange={set("address_postal_code")}
                placeholder="00000-000"
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white" />
            </div>
          </div>
        </section>

        {/* Envio */}
        <section className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-gray-dark">
          <h2 className="mb-1 text-base font-semibold text-dark dark:text-white">Cobertura de Envio</h2>
          <p className="mb-4 text-xs text-gray-500 dark:text-gray-400">
            Define para quais regiões sua loja aceita pedidos.
          </p>
          <div className="space-y-3">
            {(["state", "national", "international"] as const).map((opt) => (
              <label
                key={opt}
                className={`flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition ${
                  form.shipping_coverage === opt
                    ? "border-primary bg-primary/5"
                    : "border-stroke dark:border-strokedark"
                }`}
              >
                <input
                  type="radio" name="shipping_coverage" value={opt}
                  checked={form.shipping_coverage === opt}
                  onChange={() => setForm((f) => ({ ...f, shipping_coverage: opt }))}
                  className="mt-0.5 accent-primary"
                />
                <div>
                  <p className="text-sm font-medium text-dark dark:text-white">{COVERAGE_LABELS[opt]}</p>
                  <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    {opt === "state" && "Pedidos aceitos somente dentro do seu estado (UF acima)."}
                    {opt === "national" && "Entrega para qualquer CEP brasileiro."}
                    {opt === "international" && "Entrega nacional + envio para o exterior."}
                  </p>
                </div>
              </label>
            ))}
          </div>
        </section>

        {success && (
          <div className="rounded-lg bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">{success}</div>
        )}
        {error && (
          <div className="rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">{error}</div>
        )}

        <div className="flex justify-end">
          <button
            type="submit" disabled={saving}
            className="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white transition hover:bg-opacity-90 disabled:opacity-60"
          >
            {saving ? "Salvando…" : "Salvar configurações"}
          </button>
        </div>
      </form>
    </div>
  );
}
