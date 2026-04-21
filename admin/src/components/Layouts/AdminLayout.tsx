"use client";

import { useAuth } from "@/contexts/AuthContext";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, type PropsWithChildren } from "react";
import { Header } from "./header";
import { Sidebar } from "./sidebar";

export function AdminLayout({ children }: PropsWithChildren) {
  const { user, loading } = useAuth();
  const router = useRouter();
  const pathname = usePathname();

  const isAuthPage = pathname.startsWith("/auth");

  useEffect(() => {
    if (!loading && !user && !isAuthPage) {
      router.replace("/auth/sign-in");
    }
  }, [loading, user, isAuthPage, router]);

  // Páginas de auth: layout limpo, sem sidebar nem header
  if (isAuthPage) {
    return <>{children}</>;
  }

  // Aguardando validação do token — tela de carregamento para evitar flash
  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-2 dark:bg-[#020d1a]">
        <div className="flex flex-col items-center gap-4">
          <div className="h-10 w-10 animate-spin rounded-full border-4 border-primary border-t-transparent" />
          <p className="text-sm text-gray-500 dark:text-gray-400">Verificando sessão...</p>
        </div>
      </div>
    );
  }

  // Não autenticado — retorna null enquanto o redirect acontece
  if (!user) return null;

  // Autenticado — layout completo do admin
  return (
    <div className="flex min-h-screen">
      <Sidebar />
      <div className="w-full bg-gray-2 dark:bg-[#020d1a]">
        <Header />
        <main className="isolate mx-auto w-full max-w-screen-2xl overflow-hidden p-4 md:p-6 2xl:p-10">
          {children}
        </main>
      </div>
    </div>
  );
}
