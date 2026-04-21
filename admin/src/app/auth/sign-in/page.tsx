"use client";

import { useAuth } from "@/contexts/AuthContext";
import Image from "next/image";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

export default function SignIn() {
  const { login, user, loading } = useAuth();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!loading && user) router.replace("/");
  }, [loading, user, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSubmitting(true);
    try {
      await login(email, password);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : "Erro ao fazer login.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-2 dark:bg-[#020d1a]">
      <div className="w-full max-w-md rounded-[10px] bg-white p-8 shadow-1 dark:bg-gray-dark dark:shadow-card">
        <div className="mb-8 flex justify-center">
          <Image src="/images/logo/logo-dark.svg" alt="Logo" width={150} height={30} className="hidden dark:block" />
          <Image src="/images/logo/logo.svg" alt="Logo" width={150} height={30} className="dark:hidden" />
        </div>

        <h1 className="mb-2 text-center text-2xl font-bold text-dark dark:text-white">
          Painel Administrativo
        </h1>
        <p className="mb-8 text-center text-sm text-dark-4 dark:text-dark-6">
          Acesso restrito a administradores
        </p>

        {error && (
          <div className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="mb-2 block text-sm font-medium text-dark dark:text-white">
              E-mail
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              placeholder="admin@exemplo.com"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-dark dark:text-white">
              Senha
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            disabled={submitting}
            className="w-full rounded-lg bg-primary py-3 text-sm font-medium text-white transition hover:bg-opacity-90 disabled:opacity-60"
          >
            {submitting ? "Entrando..." : "Entrar"}
          </button>
        </form>
      </div>
    </div>
  );
}
