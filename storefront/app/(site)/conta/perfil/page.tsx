"use client";

import { addressesApi, authApi, type Address } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import toast from "react-hot-toast";

const EMPTY_ADDRESS: Omit<Address, "id"> = {
  type: "shipping", street: "", number: "", complement: "",
  neighborhood: "", city: "", state: "", postal_code: "", country: "BR", is_default: false,
};

export default function PerfilPage() {
  const { user, loading, logout } = useAuth();
  const router = useRouter();

  // Profile form
  const [profile, setProfile] = useState({ name: "", email: "", phone: "", current_password: "", password: "", password_confirmation: "" });
  const [savingProfile, setSavingProfile] = useState(false);

  // Addresses
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [editingAddress, setEditingAddress] = useState<Partial<Address> | null>(null);
  const [savingAddress, setSavingAddress] = useState(false);

  useEffect(() => {
    if (!loading && !user) router.push("/auth/entrar");
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    setProfile((p) => ({ ...p, name: user.name, email: user.email, phone: user.phone ?? "" }));
    addressesApi.list().then((r) => setAddresses(r.data));
  }, [user]);

  const handleSaveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    if (profile.password && profile.password !== profile.password_confirmation) {
      toast.error("As senhas não coincidem.");
      return;
    }
    setSavingProfile(true);
    try {
      const payload: Record<string, string> = { name: profile.name, email: profile.email, phone: profile.phone };
      if (profile.password) {
        payload.current_password = profile.current_password;
        payload.password = profile.password;
        payload.password_confirmation = profile.password_confirmation;
      }
      await authApi.updateProfile(payload);
      toast.success("Perfil atualizado!");
      setProfile((p) => ({ ...p, current_password: "", password: "", password_confirmation: "" }));
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Erro ao salvar.");
    } finally {
      setSavingProfile(false);
    }
  };

  const handleSaveAddress = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingAddress) return;
    setSavingAddress(true);
    try {
      if (editingAddress.id) {
        const r = await addressesApi.update(editingAddress.id, editingAddress);
        setAddresses((prev) => prev.map((a) => (a.id === editingAddress.id ? r.data : a)));
      } else {
        const r = await addressesApi.create(editingAddress as Omit<Address, "id">);
        setAddresses((prev) => [...prev, r.data]);
      }
      toast.success("Endereço salvo!");
      setEditingAddress(null);
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Erro ao salvar endereço.");
    } finally {
      setSavingAddress(false);
    }
  };

  const handleDeleteAddress = async (id: number) => {
    if (!confirm("Remover este endereço?")) return;
    try {
      await addressesApi.delete(id);
      setAddresses((prev) => prev.filter((a) => a.id !== id));
      toast.success("Endereço removido.");
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Erro ao remover.");
    }
  };

  if (loading || !user) return null;

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <h1 className="mb-8 text-3xl font-bold text-black dark:text-white">Meu Perfil</h1>

      <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
        {/* Profile form */}
        <div className="rounded-xl border border-stroke bg-white p-6 dark:border-strokedark dark:bg-blacksection">
          <h2 className="mb-6 font-semibold text-black dark:text-white">Dados pessoais</h2>
          <form onSubmit={handleSaveProfile} className="space-y-4">
            <div>
              <label className="mb-1 block text-sm font-medium text-black dark:text-white">Nome</label>
              <input
                type="text" value={profile.name} onChange={(e) => setProfile({ ...profile, name: e.target.value })} required
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-black dark:text-white">E-mail</label>
              <input
                type="email" value={profile.email} onChange={(e) => setProfile({ ...profile, email: e.target.value })} required
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-black dark:text-white">Telefone</label>
              <input
                type="text" value={profile.phone} onChange={(e) => setProfile({ ...profile, phone: e.target.value })}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>

            <hr className="border-stroke dark:border-strokedark" />
            <p className="text-xs text-waterloo dark:text-manatee">Preencha abaixo apenas se quiser alterar a senha</p>

            <div>
              <label className="mb-1 block text-sm font-medium text-black dark:text-white">Senha atual</label>
              <input
                type="password" value={profile.current_password} onChange={(e) => setProfile({ ...profile, current_password: e.target.value })}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-black dark:text-white">Nova senha</label>
              <input
                type="password" value={profile.password} onChange={(e) => setProfile({ ...profile, password: e.target.value })} minLength={8}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-black dark:text-white">Confirmar nova senha</label>
              <input
                type="password" value={profile.password_confirmation} onChange={(e) => setProfile({ ...profile, password_confirmation: e.target.value })}
                className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
              />
            </div>

            <div className="flex gap-3 pt-2">
              <button
                type="submit"
                disabled={savingProfile}
                className="rounded-full bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90 disabled:opacity-60"
              >
                {savingProfile ? "Salvando..." : "Salvar"}
              </button>
              <button
                type="button"
                onClick={() => logout()}
                className="rounded-full border border-red-300 px-6 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
              >
                Sair
              </button>
            </div>
          </form>
        </div>

        {/* Addresses */}
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="font-semibold text-black dark:text-white">Endereços</h2>
            <button
              onClick={() => setEditingAddress({ ...EMPTY_ADDRESS })}
              className="rounded-full bg-primary px-4 py-1.5 text-sm text-white hover:bg-opacity-90"
            >
              + Adicionar
            </button>
          </div>

          {addresses.length === 0 && !editingAddress && (
            <p className="text-sm text-waterloo dark:text-manatee">Nenhum endereço cadastrado.</p>
          )}

          {addresses.map((addr) => (
            <div key={addr.id} className="rounded-xl border border-stroke bg-white p-4 dark:border-strokedark dark:bg-blacksection">
              <div className="flex items-start justify-between gap-2">
                <div className="text-sm">
                  <p className="font-medium text-black dark:text-white">
                    {addr.street}, {addr.number}
                    {addr.complement && ` — ${addr.complement}`}
                  </p>
                  <p className="text-waterloo">{addr.neighborhood} — {addr.city}/{addr.state}</p>
                  <p className="text-waterloo">CEP {addr.postal_code}</p>
                  <div className="mt-1 flex gap-2">
                    <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700 dark:text-white">
                      {addr.type === "shipping" ? "Entrega" : "Cobrança"}
                    </span>
                    {addr.is_default && (
                      <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs text-primary">Padrão</span>
                    )}
                  </div>
                </div>
                <div className="flex shrink-0 gap-2">
                  <button
                    onClick={() => setEditingAddress({ ...addr })}
                    className="text-sm text-primary hover:underline"
                  >
                    Editar
                  </button>
                  <button
                    onClick={() => handleDeleteAddress(addr.id)}
                    className="text-sm text-red-400 hover:text-red-600"
                  >
                    Remover
                  </button>
                </div>
              </div>
            </div>
          ))}

          {/* Address form */}
          {editingAddress && (
            <div className="rounded-xl border border-primary bg-white p-6 dark:bg-blacksection">
              <h3 className="mb-4 font-semibold text-black dark:text-white">
                {editingAddress.id ? "Editar endereço" : "Novo endereço"}
              </h3>
              <form onSubmit={handleSaveAddress} className="space-y-3">
                <div className="grid grid-cols-2 gap-3">
                  <div className="col-span-2">
                    <select
                      value={editingAddress.type}
                      onChange={(e) => setEditingAddress({ ...editingAddress, type: e.target.value as "shipping" | "billing" })}
                      className="w-full rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white"
                    >
                      <option value="shipping">Entrega</option>
                      <option value="billing">Cobrança</option>
                    </select>
                  </div>
                  {(["street", "number", "complement", "neighborhood", "city", "state", "postal_code"] as const).map((field) => (
                    <input
                      key={field}
                      type="text"
                      placeholder={{ street: "Rua", number: "Número", complement: "Complemento", neighborhood: "Bairro", city: "Cidade", state: "Estado (UF)", postal_code: "CEP" }[field]}
                      value={(editingAddress[field] as string) ?? ""}
                      onChange={(e) => setEditingAddress({ ...editingAddress, [field]: e.target.value })}
                      required={field !== "complement"}
                      className={`rounded-lg border border-stroke bg-transparent px-4 py-2.5 text-sm outline-none focus:border-primary dark:border-strokedark dark:text-white ${field === "street" || field === "neighborhood" || field === "city" ? "col-span-2" : ""}`}
                    />
                  ))}
                  <label className="col-span-2 flex items-center gap-2 text-sm text-black dark:text-white">
                    <input
                      type="checkbox"
                      checked={editingAddress.is_default ?? false}
                      onChange={(e) => setEditingAddress({ ...editingAddress, is_default: e.target.checked })}
                      className="accent-primary"
                    />
                    Definir como padrão
                  </label>
                </div>
                <div className="flex gap-3 pt-1">
                  <button
                    type="submit"
                    disabled={savingAddress}
                    className="rounded-full bg-primary px-6 py-2 text-sm font-medium text-white hover:bg-opacity-90 disabled:opacity-60"
                  >
                    {savingAddress ? "Salvando..." : "Salvar"}
                  </button>
                  <button
                    type="button"
                    onClick={() => setEditingAddress(null)}
                    className="rounded-full border border-stroke px-6 py-2 text-sm dark:border-strokedark dark:text-white"
                  >
                    Cancelar
                  </button>
                </div>
              </form>
            </div>
          )}
        </div>
      </div>
    </main>
  );
}
