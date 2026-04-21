"use client";

import { useAuth } from "@/contexts/AuthContext";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import toast from "react-hot-toast";

export default function EntrarPage() {
  const { user, loading, login } = useAuth();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!loading && user) router.push("/conta/pedidos");
  }, [loading, user, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      await login(email, password);
    } catch (err: unknown) {
      toast.error(err instanceof Error ? err.message : "Credenciais inválidas.");
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return null;

  return (
    <section className="pb-12.5 pt-32.5 lg:pb-25 lg:pt-45 xl:pb-30 xl:pt-50">
      <div className="relative z-1 mx-auto max-w-c-1016 px-7.5 pb-7.5 pt-10 lg:px-15 lg:pt-15 xl:px-20 xl:pt-20">
        <div className="absolute left-0 top-0 -z-1 h-2/3 w-full rounded-lg bg-gradient-to-t from-transparent to-[#dee7ff47] dark:to-[#252A42]" />

        <div className="rounded-lg bg-white px-7.5 pt-7.5 shadow-solid-8 dark:border dark:border-strokedark dark:bg-black xl:px-15 xl:pt-15">
          <h2 className="mb-10 text-center text-3xl font-semibold text-black dark:text-white xl:text-sectiontitle2">
            Entrar na sua conta
          </h2>

          <form onSubmit={handleSubmit}>
            <div className="mb-7.5 flex flex-col gap-7.5 lg:mb-12.5 lg:flex-row lg:justify-between lg:gap-14">
              <input
                type="email"
                placeholder="E-mail"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full border-b border-stroke bg-white! pb-3.5 focus:border-waterloo focus:placeholder:text-black focus-visible:outline-hidden dark:border-strokedark dark:bg-black! dark:focus:border-manatee dark:focus:placeholder:text-white lg:w-1/2"
              />
              <input
                type="password"
                placeholder="Senha"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full border-b border-stroke bg-white! pb-3.5 focus:border-waterloo focus:placeholder:text-black focus-visible:outline-hidden dark:border-strokedark dark:bg-black! dark:focus:border-manatee dark:focus:placeholder:text-white lg:w-1/2"
              />
            </div>

            <div className="flex items-center justify-end">
              <button
                type="submit"
                disabled={submitting}
                className="inline-flex items-center gap-2.5 rounded-full bg-black px-6 py-3 font-medium text-white duration-300 ease-in-out hover:bg-blackho disabled:opacity-60 dark:bg-btndark dark:hover:bg-blackho"
              >
                {submitting ? "Entrando..." : "Entrar"}
                <svg className="fill-white" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10.4767 6.16664L6.00668 1.69664L7.18501 0.518311L13.6667 6.99998L7.18501 13.4816L6.00668 12.3033L10.4767 7.83331H0.333344V6.16664H10.4767Z" fill="" />
                </svg>
              </button>
            </div>

            <div className="mt-12.5 border-t border-stroke py-5 text-center dark:border-strokedark">
              <p className="text-black dark:text-white">
                Não tem uma conta?{" "}
                <Link href="/auth/registrar" className="text-primary hover:underline">
                  Cadastre-se
                </Link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </section>
  );
}
